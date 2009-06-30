<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_attr.php,v 1.16.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 *  Deletes an attribute from an entry with NO confirmation.
 *
 *  On success, redirect to template_engine.php
 *  On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

if (! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete attribute')),'error','index.php');

$entry = array();
$entry['dn']['string'] = get_request('dn');
$entry['dn']['encode'] = rawurlencode($entry['dn']['string']);
$entry['attr'] = get_request('attr');

if (! $entry['dn']['string'])
	error(_('No DN specified'),'error','index.php');

if (! $entry['attr'])
	error(_('No attribute name specified.'),'error','index.php');

if ($ldapserver->isAttrReadOnly($entry['attr']))
	error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),htmlspecialchars($entry['attr'])),'error','index.php');

$update_array = array();
$update_array[$entry['attr']] = array();

$result = $ldapserver->modify($entry['dn']['string'],$update_array);
if ($result) {
	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$ldapserver->server_id,$entry['dn']['encode']);

	foreach ($update_array as $attr => $junk)
		$redirect_url .= "&modified_attrs[]=$attr";

	header("Location: $redirect_url");
	die();

} else {
	system_message(array(
		'title'=>_('Could not perform ldap_modify operation.'),
		'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
		'type'=>'error'));
}
?>
