<?php
/**
 * Deletes a DN and presents a "job's done" message.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DNs we are working with
$request = array();
$request['dn'] = get_request('dn','REQUEST',true);

if (! $app['server']->dnExists($request['dn']))
	error(sprintf('%s (%s)',_('No such entry.'),'<b>'.pretty_print_dn($request['dn']).'</b>'),'error','index.php');

# Delete the entry.
$result = $app['server']->delete($request['dn']);

if ($result) {
	$redirect_url = '';

	if (isAjaxEnabled())
		$redirect_url .= sprintf('&refresh=SID_%s_nodes&noheader=1',$app['server']->getIndex());

	system_message(array(
		'title'=>_('Delete DN'),
		'body'=>_('Successfully deleted DN ').sprintf('<b>%s</b>',$request['dn']),
		'type'=>'info'),
		sprintf('index.php?server_id=%s%s',$app['server']->getIndex(),$redirect_url));
} else
	system_message(array(
		'title'=>_('Could not delete the entry.').sprintf(' (%s)',pretty_print_dn($request['dn'])),
		'body'=>ldap_error_msg($app['server']->getErrorMessage(null),$app['server']->getErrorNum(null)),
		'type'=>'error'));
?>
