<?php
// $Header$

/**
 * Recursively deletes the specified DN and all of its children
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

if (! $_SESSION[APPCONFIG]->isCommandAvailable('entry_delete','simple_delete'))
	error(sprintf('%s: %s',_('This operation is not permitted by the configuration'),_('delete entry')),'error','index.php');

$request = array();
$request['dn'] = get_request('dn','REQUEST',true);

if (! $app['server']->dnExists($request['dn']))
	error(sprintf('%s (%s)',_('No such entry.'),$request['dn']),'error','index.php');

printf('<h3 class="title">%s %s</h3>',_('Deleting'),get_rdn($request['dn']));
printf('<h3 class="subtitle">%s</h3>',_('Recursive delete progress'));

# Prevent script from bailing early on a long delete
@set_time_limit(0);

echo '<br /><br />';
echo '<small>';
$result = pla_rdelete($app['server'],$request['dn']);
echo '</small><br />';

if ($result) {
	printf(_('Entry %s and sub-tree deleted successfully.'),'<b>'.$request['dn'].'</b>');

} else {
	system_message(array(
		'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($request['dn'])),
		'body'=>ldap_error_msg($app['server']->getErrorMessage(null),$app['server']->getErrorNum(null)),
		'type'=>'error'));
}

function pla_rdelete($server,$dn) {
	# We delete all children, not only the visible children in the tree
	$children = $server->getContainerContents($dn,null,0,'(objectClass=*)',LDAP_DEREF_NEVER);

	if (! is_array($children) || count($children) == 0) {
		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),$dn);

		if ($server->delete($dn)) {
			printf(' <span style="color:green">%s</span></span><br />',_('Success'));
			return true;

		} else {
			system_message(array(
				'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($request['dn'])),
				'body'=>ldap_error_msg($server->getErrorMessage(null),$server->getErrorNum(null)),
				'type'=>'error'));
		}

	} else {
		foreach ($children as $child_dn)
			pla_rdelete($server,$child_dn);

		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),$dn);

		if ($server->delete($dn)) {
			printf(' <span style="color:green">%s</span></span><br />',_('Success'));
			return true;

		} else {
			system_message(array(
				'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($request['dn'])),
				'body'=>ldap_error_msg($server->getErrorMessage(null),$server->getErrorNum(null)),
				'type'=>'error'));
		}
	}
}
?>
