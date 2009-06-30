<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_value.php,v 1.21.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * Adds a value to an attribute for a given dn.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value
 *  - new_value (form element)
 *  - binary
 *
 * On success, redirect to the edit_dn page. On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

if (! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('add attribute value')),'error','index.php');

# The DN and ATTR we are working with.
$entry = array();
$entry['dn']['encode'] = get_request('dn','POST',true);
$entry['dn']['string'] = rawurldecode($entry['dn']['encode']);
$entry['attr']['encode'] = get_request('attr','POST',true);
$entry['attr']['string'] = rawurldecode($entry['attr']['encode']);
$entry['attr']['html'] = htmlspecialchars($entry['attr']['string']);

$entry['value']['string'] = get_request('new_value','POST',true);
$entry['value']['bin'] = get_request('binary','POST') ? true : false;

if ($ldapserver->isAttrReadOnly($entry['attr']['string']))
	error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),$entry['attr']['html']),'error','index.php');

/*
 * Special case for binary attributes:
 * we must go read the data from the file.
 */
if ($entry['value']['bin']) {
	$binaryfile['name'] = $_FILES['new_value']['tmp_name'];
	$binaryfile['handle'] = fopen($binaryfile['name'],'r');
	$binaryfile['data'] = fread($binaryfile['handle'],filesize($binaryfile['name']));
	fclose($binaryfile['handle']);

	$entry['value']['string'] = $binaryfile['data'];
}

$new_entry = array($entry['attr']['string']=>$entry['value']['string']);

# Check to see if this is a unique Attribute
if ($badattr = $ldapserver->checkUniqueAttr($entry['dn']['string'],$entry['attr']['string'],$new_entry)) {
	$href = htmlspecialchars(sprintf('cmd.php?cmd=search&search=true&form=advanced&server_id=%s&filter=%s=%s',
		$ldapserver->server_id,$entry['attr']['string'],$badattr));

	error(sprintf(_('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.'),$entry['attr']['string'],$badattr,$entry['dn']['string'],$href),'error','index.php');
}

# Call the custom callback for each attribute modification and verify that it should be modified.
if (run_hook('pre_attr_add',
	array('server_id'=>$ldapserver->server_id,'dn'=> $entry['dn']['string'],'attr_name'=>$entry['attr']['string'],'new_value'=>$new_entry))) {

	if (run_hook('pre_attr_modify',
		array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'attr_name'=>$entry['attr']['string'],'new_value'=>$new_entry))) {

		$add_result = $ldapserver->attrModify($entry['dn']['string'],$new_entry);

		if (! $add_result) {
			system_message(array(
				'title'=>_('Could not perform ldap_mod_add operation.'),
				'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
				'type'=>'error'));

		} else {
			run_hook('post_attr_modify',
				array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'attr_name'=>$entry['attr']['string'],'new_value'=>$new_entry));
		}
	}
}

header(sprintf('Location: cmd.php?cmd=template_engine&server_id=%s&dn=%s&modified_attrs[]=%s',
	$ldapserver->server_id,$entry['dn']['encode'],$entry['attr']['encode']));
die();
?>
