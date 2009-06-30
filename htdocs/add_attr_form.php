<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_attr_form.php,v 1.15 2006/10/28 07:22:39 wurley Exp $

/**
 * Displays a form for adding an attribute/value to an LDAP entry.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = $_GET['dn'];
$encoded_dn = rawurlencode($dn);
$rdn = get_rdn($dn);

$friendly_attrs = process_friendly_attr_table();

include './header.php';

echo '<body>';

printf('<h3 class="title">%s <b>%s</b></h3>',_('Add new attribute'),htmlspecialchars($rdn));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));

$attrs = $ldapserver->getDNAttrs($dn);

$oclasses = $ldapserver->getDNAttr($dn,'objectClass');
if (! is_array($oclasses))
	$oclasses = array($oclasses);

$avail_attrs = array();

if (array_search('extensibleObject',$oclasses) !== FALSE) {
	$schema_attrs = $ldapserver->SchemaAttributes();

	foreach ($schema_attrs as $attr)
		$avail_attrs[]=$attr->getName();

} else {
	$schema_oclasses = $ldapserver->SchemaObjectClasses($dn);

	foreach ($oclasses as $oclass) {
		$schema_oclass = $ldapserver->getSchemaObjectClass($oclass,$dn);

		if ($schema_oclass && strcasecmp('objectclass',get_class($schema_oclass)) == 0)
			$avail_attrs = array_merge($schema_oclass->getMustAttrNames($schema_oclasses),
				$schema_oclass->getMayAttrNames($schema_oclasses),
				$avail_attrs);
	}
}

$avail_attrs = array_unique($avail_attrs);
$avail_attrs = array_filter($avail_attrs,'not_an_attr');
sort($avail_attrs);

$avail_binary_attrs = array();

foreach ($avail_attrs as $i => $attr) {

	if ($ldapserver->isAttrBinary($attr)) {
		$avail_binary_attrs[] = $attr;
		unset($avail_attrs[$i]);
	}
}

echo '<center>';

if (is_array($avail_attrs) && count($avail_attrs) > 0) {
	echo '<br />';
	echo _('Add new attribute');
	echo '<br />';
	echo '<br />';

	echo '<form action="add_attr.php" method="post">';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));

	echo '<select name="attr">';

	$attr_select_html = '';
	usort($avail_attrs,'sortAttrs');

	foreach ($avail_attrs as $a) {

		# is there a user-friendly translation available for this attribute?
		if (isset($friendly_attrs[strtolower($a)])) {
			$attr_display = sprintf('%s (%s)',
				htmlspecialchars($friendly_attrs[strtolower($a)]),
				htmlspecialchars($a));

		} else {
			$attr_display = htmlspecialchars($a);
		}

		printf('<option value="%s">%s</option>',htmlspecialchars($a),$attr_display);
	}

	echo '</select>';

	echo '<input type="text" name="val" size="20" />';
	printf('<input type="submit" name="submit" value="%s" class="update_dn" />',_('Add'));
	echo '</form>';

} else {
	echo '<br />';
	printf('<small>(%s)</small>',_('no new attributes available for this entry'));
}

if (count($avail_binary_attrs) > 0) {
	echo '<br />';
	echo _('Add new binary attribute');
	echo '<br />';
	echo '<br />';

	echo '<!-- Form to add a new BINARY attribute to this entry -->';
	echo '<form action="add_attr.php" method="post" enctype="multipart/form-data">';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',$dn);
	echo '<input type="hidden" name="binary" value="true" />';

	echo '<select name="attr">';

	$attr_select_html = '';
	usort($avail_binary_attrs,'sortAttrs');

	foreach ($avail_binary_attrs as $a) {

		# is there a user-friendly translation available for this attribute?
		if (isset($friendly_attrs[strtolower($a)])) {
			$attr_display = sprintf('%s (%s)',
				htmlspecialchars($friendly_attrs[strtolower($a)]),
				htmlspecialchars($a));

		} else {
			$attr_display = htmlspecialchars($a);
		}

		printf('<option value="%s">%s</option>',htmlspecialchars($a),$attr_display);
	}

	echo '</select>';

	echo '<input type="file" name="val" size="20" />';
	printf('<input type="submit" name="submit" value="%s" class="update_dn" />',_('Add'));

	if (! ini_get('file_uploads'))
		printf('<br /><small><b>%s</b></small><br />',
			_('Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.'));

	else
		printf('<br /><small><b>%s: %s</b></small><br />',_('Maximum file size'),ini_get('upload_max_filesize'));

	echo '</form>';

} else {
	echo '<br />';
	printf('<small>(%s)</small>',_('no new binary attributes available for this entry'));
}

echo '</center>';
echo '</body>';
echo '</html>';

/**
 * Given an attribute $x, this returns true if it is NOT already specified
 * in the current entry, returns false otherwise.
 *
 * @param attr $x
 * @return bool
 * @ignore
 */
function not_an_attr($x) {
	global $attrs;

	foreach($attrs as $attr => $values)
		if (strcasecmp($attr,$x) == 0)
			return false;

	return true;
}
?>
