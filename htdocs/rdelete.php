<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rdelete.php,v 1.28.2.4 2009/06/20 07:14:20 wurley Exp $

/**
 * Recursively deletes the specified DN and all of its children
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

if (! $_SESSION[APPCONFIG]->isCommandAvailable('entry_delete','simple_delete'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete entry')),'error','index.php');

$entry = array();
$entry['dn'] = get_request('dn');
if (! $entry['dn'])
	error(_('You must specify a DN'),'error','index.php');

if (! $ldapserver->dnExists($entry['dn']))
	error(sprintf('%s (%s)',_('No such entry.'),htmlspecialchars($entry['dn'])),'error','index.php');

printf('<h3 class="title">'._('Deleting %s').'</h3>',htmlspecialchars(get_rdn($entry['dn'])));
printf('<h3 class="subtitle">%s</h3>',_('Recursive delete progress'));
echo '<br /><br />';
echo '<small>';

# Prevent script from bailing early on a long delete
@set_time_limit(0);

$result = pla_rdelete($ldapserver,$entry['dn']);
echo '</small><br />';

if ($result) {
	printf(_('Entry %s and sub-tree deleted successfully.'),'<b>'.htmlspecialchars($entry['dn']).'</b>');

} else {
	system_message(array(
		'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($entry['dn'])),
		'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
		'type'=>'error'));
}

function pla_rdelete($ldapserver,$dn) {
	# we delete all children, not only the visible children in the tree
	$children = $ldapserver->getContainerContents($dn,0,'(objectclass=*)',LDAP_DEREF_NEVER);

	if (! is_array($children) || count($children) == 0) {
		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),htmlspecialchars($dn));

		if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn)))
			if ($ldapserver->delete($dn)) {
				run_hook('post_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn));
				printf(' <span style="color:green">%s</span></span><br />',_('Success'));
				return true;

			} else {
				system_message(array(
					'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($entry['dn'])),
					'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
					'type'=>'error'));
			}

	} else {
		foreach ($children as $child_dn)
			pla_rdelete($ldapserver,$child_dn);

		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),htmlspecialchars($dn));

		if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn)))
			if ($ldapserver->delete($dn)) {
				run_hook('post_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn));
				printf(' <span style="color:green">%s</span></span><br />',_('Success'));
				return true;

			} else {
				system_message(array(
					'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($entry['dn'])),
					'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
					'type'=>'error'));
			}
	}
}
?>
