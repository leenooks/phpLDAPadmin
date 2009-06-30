<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_oclass_form.php,v 1.25.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * This page may simply add the objectClass and take you back to the edit page,
 * but, in one condition it may prompt the user for input. That condition is this:
 *
 *    If the user has requested to add an objectClass that requires a set of
 *    attributes with 1 or more not defined by the object. In that case, we will
 *    present a form for the user to add those attributes to the object.
 *
 * Variables that come in as REQUEST vars:
 *  - dn (rawurlencoded)
 *  - new_oclass
 *
 * @package phpLDAPadmin
 * @todo If an attribute expects a DN, show the dn browser.
 */
/**
 */
require './common.php';

$entry = array();
$entry['oclass']['new'] = get_request('new_oclass','REQUEST');
$entry['dn']['string'] = get_request('dn','REQUEST');

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

if (! $entry['oclass']['new'])
	error(_('You did not select any ObjectClasses for this object. Please go back and do so.'),'error','index.php');

/* Ensure that the object has defined all MUST attrs for this objectClass.
 * If it hasn't, present a form to have the user enter values for all the
 * newly required attrs.
 */

$entry['dn']['attrs'] = $ldapserver->getDNAttrs($entry['dn']['string'],true);

$entry['attrs']['current'] = array();
foreach ($entry['dn']['attrs'] as $attr => $junk)
	$entry['attrs']['current'][] = strtolower($attr);

# Grab the required attributes for the new objectClass
$ldap['oclasses'] = $ldapserver->SchemaObjectClasses();
$ldap['attrs']['must'] = array();
foreach ($entry['oclass']['new'] as $oclass_name) {
	$ldap['oclass'] = $ldapserver->getSchemaObjectClass($oclass_name);

	if ($ldap['oclass'])
		$ldap['attrs']['must'] = array_merge($ldap['attrs']['must'],$ldap['oclass']->getMustAttrNames($ldap['oclasses']));
}
$ldap['attrs']['must'] = array_unique($ldap['attrs']['must']);

/* Build a list of the attributes that this new objectClass requires,
 * but that the object does not currently contain
 */
$ldap['attrs']['need'] = array();
foreach ($ldap['attrs']['must'] as $attr) {
	$attr = $ldapserver->getSchemaAttribute($attr);

	# First, check if one of this attr's aliases is already an attribute of this entry
	foreach ($attr->getAliases() as $alias_attr_name)
		if (in_array(strtolower($alias_attr_name),$entry['attrs']['current']))
			continue;

	if (in_array(strtolower($attr->getName()),$entry['attrs']['current']))
		continue;

	/* We made it this far, so the attribute needs to be added to this entry in order
	 * to add this objectClass */
	$ldap['attrs']['need'][] = $attr;
}

if (count($ldap['attrs']['need']) > 0) {
	printf('<h3 class="title">%s</h3>',_('New Required Attributes'));
	printf('<h3 class="subtitle">%s %s %s</h3>',_('This action requires you to add'),count($ldap['attrs']['need']),_('new attributes'));

	printf('<small><b>%s: </b>%s <b>%s</b> %s %s</small>',
		_('Instructions'),
		_('In order to add these objectClass(es) to this entry, you must specify'),
		count($ldap['attrs']['need']),_('new attributes'),
		_('that this objectClass requires.'));

	echo '<br /><br />';

	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="add_oclass" />';
	printf('<input type="hidden" name="new_oclass" value="%s" />',rawurlencode(serialize($entry['oclass']['new'])));
	printf('<input type="hidden" name="dn" value="%s" />',rawurlencode($entry['dn']['string']));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

	echo '<table class="entry" cellspacing="0">';
	printf('<tr><th colspan="2">%s</th></tr>',_('New Required Attributes'));

	foreach ($ldap['attrs']['need'] as $count => $attr) {
		printf('<tr><td class="title">%s</td></tr>',htmlspecialchars($attr->getName()));
		printf('<tr><td class="value"><input type="text" name="new_attrs[%s]" value="" size="40" /></td></tr>',htmlspecialchars($attr->getName()));
	}

	echo '</table>';

	echo '<br /><br />';

	printf('<center><input type="submit" value="%s" /></center>',_('Add ObjectClass and Attributes'));
	echo '</form>';

} else {
	$result = $ldapserver->attrModify($entry['dn']['string'],array('objectClass'=>$entry['oclass']['new']));

	if (! $result)
		system_message(array(
			'title'=>_('Could not perform ldap_mod_add operation.'),
			'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
			'type'=>'error'));

	else {
		$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s&modified_attrs[]=objectClass',
			$ldapserver->server_id,rawurlencode($entry['dn']['string']));

		header(sprintf('Location: %s',$href));
		die();
	}
}
?>
