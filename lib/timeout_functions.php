<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/timeout_functions.php,v 1.10.2.4 2008/12/13 08:57:09 wurley Exp $

/**
 * A collection of functions used throughout phpLDAPadmin for the timeout and automatic logout feature
 * @author Samuel Tran
 * @package phpLDAPadmin
 *
 */

/**
 * Responsible for setting/updating two session-vars that are used for the timeout and auto logout feature:
 *	- "activity" var records the server last activity.
 * where X is the * ID of the server which the user is working on
 *
 * @param object $ldapserver The LDAPServer object of the server which the user has logged in.
 * @return bool
 */
function set_lastactivity($ldapserver) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$ldapserver->server_id);

	$_SESSION['activity']['server'][$ldapserver->server_id] = time();
	return true;
}

/**
 * Remove the session-var "lastactivity_X" set by update_lastactivity()
 * where X is the * ID of the server
 *
 * @param object $ldapserver The LDAPServer object of the server which the user has logged in.
 */
function unset_lastactivity($ldapserver) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$ldapserver->server_id);

	if (isset($_SESSION['activity']['server'][$ldapserver->server_id]))
		unset($_SESSION['activity']['server'][$ldapserver->server_id]);
}

/**
 * Check if custom session timeout has been reached for server $ldapserver.
 * If it has:
 * 	- automatically log out user by calling $ldapserver->unsetLoginDN()
 *	- return true
 *
 * @param object $ldapserver The LDAPServer object of the server which the user has logged in.
 * @return bool true on success, false on failure.
 */
function session_timed_out($ldapserver) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$ldapserver->server_id);

	# If session hasn't expired yet
	if (isset($_SESSION['activity']['server'][$ldapserver->server_id])) {

		# If $session_timeout not defined, use (session_cache_expire() - 1)
		if (! isset($ldapserver->session_timeout))
			$session_timeout = session_cache_expire()-1;
		else
			$session_timeout = $ldapserver->session_timeout;

		# Get the $last_activity value
		$last_activity = $_SESSION['activity']['server'][$ldapserver->server_id];

		# If diff between current time and last activity greater than $session_timeout, log out user
		if ((time()-$last_activity) > ($session_timeout*60)) {

			if (in_array($ldapserver->auth_type,array('cookie','session'))) {
				syslog_notice('Logout for '.$ldapserver->getLoggedInDN());
				$ldapserver->unsetLoginDN() or error(_('Could not logout.'),'error','index.php');
			}

			return true;

		}
	}

	return false;
}
?>
