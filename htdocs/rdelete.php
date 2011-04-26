<?php
/**
 * Recursively deletes the specified DN and all of its children
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','REQUEST',true);

if (! is_array($request['dn']))
	$request['dn'] = array($request['dn']);

$request['parent'] = array();
foreach ($request['dn'] as $dn)
	if (! $app['server']->dnExists($dn))
		system_message(array(
			'title'=>_('Entry does not exist'),
			'body'=>sprintf('%s (%s)',_('Unable to delete entry, it does not exist'),$dn),
			'type'=>'error'));
	else
		array_push($request['parent'],$dn);

printf('<h3 class="title">%s</h3>',_('Delete LDAP entries'));
printf('<h3 class="subtitle">%s</h3>',_('Recursive delete progress'));

# Prevent script from bailing early on a long delete
@set_time_limit(0);

foreach ($request['parent'] as $dn) {
	echo '<br /><br />';
	echo '<small>';
	$result = pla_rdelete($app['server'],$dn);
	echo '</small><br />';

	if ($result) {
		printf(_('Entry %s and sub-tree deleted successfully.'),'<b>'.$dn.'</b>');

	} else {
		system_message(array(
			'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($request['dn'])),
			'body'=>ldap_error_msg($app['server']->getErrorMessage(null),$app['server']->getErrorNum(null)),
			'type'=>'error'));
	}
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
				'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($dn)),
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
				'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($dn)),
				'body'=>ldap_error_msg($server->getErrorMessage(null),$server->getErrorNum(null)),
				'type'=>'error'));
		}
	}
}
?>
