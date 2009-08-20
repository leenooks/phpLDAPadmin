<?php
/**
 * Download a binary value attribute to the user.
 * A server ID, DN and Attribute must be provided in the GET attributes.
 * Optionally an index, type and filename can be supplied.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','GET');
$request['attr'] = strtolower(get_request('attr','GET',true));
$request['index'] = get_request('index','GET',false,0);
$request['type'] = get_request('type','GET',false,'octet-stream');
$request['filename'] = get_request('filename','GET',false,sprintf('%s:%s.bin',get_rdn($request['dn'],true),$request['attr']));

if (! $app['server']->dnExists($request['dn']))
	error(sprintf(_('The entry (%s) does not exist.'),$request['dn']),'error','index.php');

$search = $app['server']->getDNAttrValues($request['dn'],null,LDAP_DEREF_NEVER,array($request['attr']));

# Dump the binary data to the browser
$obStatus = ob_get_status();
if (isset($obStatus['type']) && $obStatus['type'] && $obStatus['status']) 
	ob_end_clean();

if (! isset($search[$request['attr']][$request['index']])) {
	# We cant display an error, but we can set a system message, which will be display on the next page render.
	system_message(array(
		'title'=>_('No binary data available'),
		'body'=>sprintf(_('Could not fetch binary data from LDAP server for attribute [%s].'),$request['attr']),
		'type'=>'warn'));

	die();
}

header(sprintf('Content-type: %s',$request['type']));
header(sprintf('Content-disposition: attachment; filename="%s"',$request['filename']));
header(sprintf('Expires: Mon, 26 Jul 1997 05:00:00 GMT',gmdate('r')));
header(sprintf('Last-Modified: %s',gmdate('r')));
echo $search[$request['attr']][$request['index']];
die();
?>
