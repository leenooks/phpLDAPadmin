<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/logout.php,v 1.20 2007/12/15 07:50:30 wurley Exp $

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
	pla_error(_('No one is logged in to that server.'));

if (in_array($ldapserver->auth_type, array('cookie','session','http'))) {
	syslog_notice (sprintf('Logout for %s',$ldapserver->getLoggedInDN()));
	if($ldapserver->auth_type!='http')
		$ldapserver->unsetLoginDN() or pla_error(_('Could not logout.'));
	unset_lastactivity($ldapserver);

	@session_destroy();

} else
	pla_error(sprintf(_('Unknown auth_type: %s'), htmlspecialchars($ldapserver->auth_type)));

system_message(array(
	'title'=>_('Logout'),
	'body'=>('Logged out successfully from server.'),
	'type'=>'info'),
	'index.php');
?>
