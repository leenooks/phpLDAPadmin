<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete.php,v 1.27.2.3 2008/12/12 12:20:22 wurley Exp $

/**
 * Deletes a DN and presents a "job's done" message.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

if (! $_SESSION[APPCONFIG]->isCommandAvailable('entry_delete', 'simple_delete'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete entry')),'error','index.php');

$entry = array();
$entry['dn'] = get_request('dn');

if (! $entry['dn'])
	error(_('You must specify a DN'),'error','index.php');

if (! $ldapserver->dnExists($entry['dn']))
	error(sprintf('%s (%s)',_('No such entry.'),'<b>'.pretty_print_dn($entry['dn']).'</b>'),'error','index.php');

# Check the user-defined custom callback first.
if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn'])))
	$result = $ldapserver->delete($entry['dn']);
else
	error(sprintf(_('Could not delete the entry: %s'),'<b>'.pretty_print_dn($entry['dn']).'</b>'),'error','index.php');

if ($result) {
	# Custom callback
	run_hook('post_entry_delete',
		array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']));

	system_message(array(
		'title'=>_('Delete DN'),
		'body'=>_('Successfully deleted DN ').sprintf('<b>%s</b>',$entry['dn']),
		'type'=>'info'),
		sprintf('index.php?server_id=%s',$ldapserver->server_id));

} else {
	system_message(array(
		'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($entry['dn'])),
		'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
		'type'=>'error'));
}
?>
