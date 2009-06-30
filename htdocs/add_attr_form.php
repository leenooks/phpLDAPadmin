<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_attr_form.php,v 1.16.2.4 2008/12/12 12:20:22 wurley Exp $

/**
 * Displays a form for adding an attribute/value to an LDAP entry.
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

$entry = array();
$entry['dn']['string'] = get_request('dn','GET');
$entry['rdn'] = get_rdn($entry['dn']['string']);

printf('<h3 class="title">%s <b>%s</b></h3>',_('Add new attribute'),htmlspecialchars($entry['rdn']));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($entry['dn']['string']));

$dn = array();
$dn['attrs'] = $ldapserver->getDNAttrs($entry['dn']['string']);
$dn['oclasses'] = $ldapserver->getDNAttr($entry['dn']['string'],'objectClass');

if (! is_array($dn['oclasses']))
	$dn['oclasses'] = array($dn['oclasses']);

$ldap['attrs']['avail'] = array();

if (array_search('extensibleObject',$dn['oclasses']) !== false) {
	$ldap['attrs']['ldap'] = $ldapserver->SchemaAttributes();

	foreach ($ldap['attrs']['ldap'] as $attr)
		$ldap['attrs']['avail'][] = $attr->getName();

} else {
	$ldap['oclasses'] = $ldapserver->SchemaObjectClasses($entry['dn']['string']);

	foreach ($dn['oclasses'] as $oclass) {
		$ldap['oclass'] = $ldapserver->getSchemaObjectClass($oclass,$entry['dn']['string']);

		if ($ldap['oclass'] && strcasecmp('objectclass',get_class($ldap['oclass'])) == 0)
			$ldap['attrs']['avail'] = array_merge($ldap['oclass']->getMustAttrNames($ldap['oclasses']),
				$ldap['oclass']->getMayAttrNames($ldap['oclasses']),
				$ldap['attrs']['avail']);
	}
}

$ldap['attrs']['avail'] = array_unique($ldap['attrs']['avail']);
$ldap['attrs']['avail'] = array_filter($ldap['attrs']['avail'],'not_an_attr');
sort($ldap['attrs']['avail']);

$ldap['binattrs']['avail'] = array();

foreach ($ldap['attrs']['avail'] as $i => $attr) {
	if ($ldapserver->isAttrBinary($attr)) {
		$ldap['binattrs']['avail'][] = $attr;
		unset($ldap['attrs']['avail'][$i]);
	}
}

echo '<center>';

if (is_array($ldap['attrs']['avail']) && count($ldap['attrs']['avail']) > 0) {
	echo '<br />';
	echo _('Add new attribute');
	echo '<br />';
	echo '<br />';

	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="add_attr" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($entry['dn']['string']));

	echo '<select name="attr">';

	$attr_select_html = '';
	usort($ldap['attrs']['avail'],'sortAttrs');

	foreach ($ldap['attrs']['avail'] as $a) {

		# is there a user-friendly translation available for this attribute?
		if ($_SESSION[APPCONFIG]->haveFriendlyName($a)) {
			$attr_display = sprintf('%s (%s)',
				htmlspecialchars($_SESSION[APPCONFIG]->getFriendlyName($a)),
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

if (count($ldap['binattrs']['avail']) > 0) {
	echo '<br />';
	echo _('Add new binary attribute');
	echo '<br />';
	echo '<br />';

	echo '<!-- Form to add a new BINARY attribute to this entry -->';
	echo '<form action="cmd.php" method="post" enctype="multipart/form-data">';
	echo '<input type="hidden" name="cmd" value="add_attr" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',$entry['dn']['string']);
	echo '<input type="hidden" name="binary" value="true" />';

	echo '<select name="attr">';

	$attr_select_html = '';
	usort($ldap['binattrs']['avail'],'sortAttrs');

	foreach ($ldap['binattrs']['avail'] as $a) {

		# is there a user-friendly translation available for this attribute?
		if ($_SESSION[APPCONFIG]->haveFriendlyName($a)) {
			$attr_display = sprintf('%s (%s)',
				htmlspecialchars($_SESSION[APPCONFIG]->getFriendlyName($a)),
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

/**
 * Given an attribute $x, this returns true if it is NOT already specified
 * in the current entry, returns false otherwise.
 *
 * @param attr $x
 * @return bool
 * @ignore
 */
function not_an_attr($x) {
	global $dn;

	foreach ($dn['attrs'] as $attr => $values)
		if (strcasecmp($attr,$x) == 0)
			return false;

	return true;
}
?>
