<?php
/**
 * Log the user in.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$user = array();
$user['login'] = get_request('login');
$user['password'] = get_request('login_pass');

if ($user['login'] && ! strlen($user['password']))
	system_message(array(
		'title'=>_('Authenticate to server'),
		'body'=>_('You left the password blank.'),
		'type'=>'warn'),
		sprintf('cmd.php?cmd=login_form&server_id=%s',get_request('server_id','REQUEST')));

if ($app['server']->login($user['login'],$user['password'],'user'))
	system_message(array(
		'title'=>_('Authenticate to server'),
		'body'=>_('Successfully logged into server.'),
		'type'=>'info'),
		sprintf('cmd.php?server_id=%s',get_request('server_id','REQUEST')));
else
	system_message(array(
		'title'=>_('Failed to Authenticate to server'),
		'body'=>_('Invalid Username or Password.'),
		'type'=>'error'),
		sprintf('cmd.php?cmd=login_form&server_id=%s',get_request('server_id','REQUEST')));
?>
