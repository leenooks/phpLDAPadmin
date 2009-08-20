<?php
/**
 * Log the user out of the application.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

if ($app['server']->logout())
	system_message(array(
		'title'=>_('Authenticate to server'),
		'body'=>_('Successfully logged out of server.'),
		'type'=>'info'),
		sprintf('index.php?server_id=%s',$app['server']->getIndex()));
else
	system_message(array(
		'title'=>_('Failed to Logout of server'),
		'body'=>_('Please report this error to the admins.'),
		'type'=>'error'),
		sprintf('index.php?server_id=%s',$app['server']->getIndex()));
?>
