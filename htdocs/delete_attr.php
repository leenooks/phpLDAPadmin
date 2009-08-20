<?php
/**
 *  Deletes an attribute from an entry with NO confirmation.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','REQUEST',true);
$request['attr'] = get_request('attr','REQUEST',true);
$request['index'] = get_request('index','REQUEST',true);

if ($app['server']->isAttrReadOnly($request['attr']))
	error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),$request['attr']),'error','index.php');

$update_array = array();
$update_array[$request['attr']] = $app['server']->getDNAttrValue($request['dn'],$request['attr']);

$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
	$app['server']->getIndex(),rawurlencode($request['dn']));

if (! isset($update_array[$request['attr']][$request['index']]))
	system_message(array(
		'title'=>_('Could not delete attribute value.'),
		'body'=>sprintf('%s. %s/%s',_('The attribute value does not exist'),$request['attr'],$request['index']),
		'type'=>'warn'),$redirect_url);

else {
	unset($update_array[$request['attr']][$request['index']]);
	foreach ($update_array as $key => $values)
		$update_array[$key] = array_values($values);

	$result = $app['server']->modify($request['dn'],$update_array);

	if ($result) {
		foreach ($update_array as $attr => $junk)
			$redirect_url .= sprintf('&modified_attrs[]=%s',$attr);

		header("Location: $redirect_url");
		die();
	}
}
?>
