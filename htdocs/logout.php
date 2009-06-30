<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/logout.php,v 1.20.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me
 * the server_id and I will log out the user (delete the cookie)
 *
 * Variables that come in via common.php
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $ldapserver->haveAuthInfo())
	error(_('No one is logged in to that server.'),'error','index.php');

if (in_array($ldapserver->auth_type, array('cookie','session','http'))) {
	syslog_notice (sprintf('Logout for %s',$ldapserver->getLoggedInDN()));
	if($ldapserver->auth_type!='http')
		$ldapserver->unsetLoginDN() or error(_('Could not logout.'),'error','index.php');
	unset_lastactivity($ldapserver);

	@session_destroy();

} else
	error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($ldapserver->auth_type)),'error','index.php');

system_message(array(
	'title'=>_('Logout'),
	'body'=>('Logged out successfully from server.'),
	'type'=>'info'),
	sprintf('index.php?server_id=%s',$ldapserver->server_id));
?>
