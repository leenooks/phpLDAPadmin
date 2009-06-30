<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete.php,v 1.27.2.2 2007/12/26 09:26:32 wurley Exp $

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
	pla_error(_('You cannot perform updates while server is in read-only mode'));

if (! $_SESSION[APPCONFIG]->isCommandAvailable('entry_delete', 'simple_delete'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete entry')));

$entry['dn'] = get_request('dn');

if (! $entry['dn'])
	pla_error(_('You must specify a DN'));

if (! $ldapserver->dnExists($entry['dn']))
	pla_error(sprintf(_('No such entry: %s'),'<b>'.pretty_print_dn($entry['dn']).'</b>'));

# Check the user-defined custom callback first.
if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn'])))
	$result = $ldapserver->delete($entry['dn']);
else
	pla_error(sprintf(_('Could not delete the entry: %s'),'<b>'.pretty_print_dn($entry['dn']).'</b>'));

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
	pla_error(sprintf(_('Could not delete the entry: %s'),'<b>'.pretty_print_dn($entry['dn']).'</b>'),
	  $ldapserver->error(),$ldapserver->errno());
}
?>
