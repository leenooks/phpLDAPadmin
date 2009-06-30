<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/create.php,v 1.44.2.6 2006/02/19 02:57:01 wurley Exp $

/**
 * Creates a new object.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - new_dn
 *  - attrs (an array of attributes)
 *  - vals (an array of values for the above attrs)
 *  - required_attrs (an array with indices being the attributes,
 *		      and the values being their respective values)
 *  - object_classes (rawurlencoded, and serialized array of objectClasses)
 *
 * @package phpLDAPadmin
 */
/**
 * @todo: posixgroup with empty memberlist generates an error.
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$new_dn = isset($_POST['new_dn']) ? $_POST['new_dn'] : null;
$required_attrs = isset($_POST['required_attrs']) ? $_POST['required_attrs'] : false;
$object_classes = unserialize(rawurldecode($_POST['object_classes']));
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : false;

# See if there are any presubmit values to work out.
if (isset($_POST['presubmit']) && count($_POST['presubmit']) && isset($_POST['template'])) {
	$templates = new Templates($ldapserver->server_id);
	$template = $templates->GetTemplate($_POST['template']);

	foreach ($_POST['presubmit'] as $attr) {
		$_POST['attrs'][] = $attr;
		$_POST['form'][$attr] = $templates->EvaluateDefault($ldapserver,$template['attribute'][$attr]['presubmit'],$_POST['container']);
		$_POST['vals'][] = $_POST['form'][$attr];
	}

	# @todo: This section needs to be cleaned up, and will be when the old templates are removed. In the mean time...
	# Rebuild the $_POST['attrs'] & $_POST['vals'], as they can be inconsistent.
	unset($_POST['attrs']);
	unset($_POST['vals']);
	foreach ($_POST['form'] as $attr => $val) {
		$_POST['attrs'][] = $attr;
		$_POST['vals'][] = $val;
	}
}

$vals = isset($_POST['vals']) ? $_POST['vals'] : array();
$attrs = isset($_POST['attrs']) ? $_POST['attrs'] : array();

# build the new entry
$new_entry = array();
if (isset($required_attrs) && is_array($required_attrs)) {
	foreach ($required_attrs as $attr => $val) {
		if ($val == '')
			pla_error(sprintf(_('You left the value blank for required attribute (%s).'),htmlspecialchars($attr)));

		$new_entry[$attr][] = $val;
	}
}

if (isset($attrs) && is_array($attrs)) {
	foreach ($attrs as $i => $attr) {

		if ($ldapserver->isAttrBinary($attr)) {
			if (isset($_FILES['vals']['name'][$i]) && $_FILES['vals']['name'][$i] != '' ) {

				# read in the data from the file
				$file = $_FILES['vals']['tmp_name'][$i];
				$f = fopen($file,'r');
				$binary_data = fread($f,filesize($file));
				fclose($f);

				$val = $binary_data;
				$new_entry[$attr][] = $val;

			} elseif (isset($_SESSION['submitform'][$attr])) {
				$new_entry[$attr][] = $_SESSION['submitform'][$attr];
				unset($_SESSION['submitform'][$attr]);
			}

		} else {
			if (is_array($vals[$i])) {

				# If the array has blank entries, then ignore them.
				foreach ($vals[$i] as $value) {
					if (trim($value))
						$new_entry[$attr][] = $value;
				}

			} else {
				$val = isset($vals[$i]) ? $vals[$i] : '';

				if ('' !== trim($val))
					$new_entry[$attr][] = $val;
			}
		}
	}
}

$new_entry['objectClass'] = $object_classes;
if (! in_array('top',$new_entry['objectClass']))
	$new_entry['objectClass'][] = 'top';

foreach ($new_entry as $attr => $vals) {
	# Check to see if this is a unique Attribute
	if ($badattr = $ldapserver->checkUniqueAttr($new_dn,$attr,$vals)) {
		$search_href = sprintf('search.php?search=true&amp;form=advanced&amp;server_id=%s&amp;filter=%s=%s',
			$ldapserver->server_id,$attr,$badattr);
		pla_error(sprintf(_('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.'),$attr,$badattr,$new_dn,$search_href));
	}

	if (! $ldapserver->isAttrBinary($attr))
		if (is_array($vals))
			foreach ($vals as $i => $v)
				$new_entry[$attr][$i] = $v;
		else
			$new_entry[$attr] = $vals;
}

# Check the user-defined custom call back first
if (run_hook('pre_entry_create',array('server_id'=>$ldapserver->server_id,'dn'=>$new_dn,'attrs'=>$new_entry)))
	$add_result = $ldapserver->add($new_dn,$new_entry);

if ($add_result) {
	run_hook('post_entry_create',array('server_id'=>$ldapserver->server_id,'dn'=>$new_dn,'attrs'=>$new_entry));

	if ($redirect)
		$redirect_url = $redirect;
	else
		$redirect_url = sprintf('template_engine.php?server_id=%s&dn=%s',$ldapserver->server_id,rawurlencode($new_dn));

	echo '<html><head>';
	$tree = get_cached_item($ldapserver->server_id,'tree');
	$container = get_container($new_dn);

	if ((isset($tree['browser'][$container]['open']) && $tree['browser'][$container]['open']) || 
		in_array($new_dn,$ldapserver->getBaseDN())) {

		echo '<!-- refresh the tree view (with the new DN renamed) and redirect to the edit_dn page -->';
		printf('<script language="javascript">parent.left_frame.location.reload();location.href="%s"</script>',$redirect_url);
	}

	printf('<meta http-equiv="refresh" content="0; url=%s" />',$redirect_url);
	echo '</head><body>';
	printf('%s <a href="%s">%s</a>.',_('Redirecting...'),$redirect_url,_('here'));
	echo '</body></html>';

} else {
	pla_error(_('Could not add the object to the LDAP server.'),$ldapserver->error(),$ldapserver->errno());
}
?>
