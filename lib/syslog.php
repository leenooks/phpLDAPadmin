<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/syslog.php,v 1.11.4.1 2005/12/09 14:32:13 wurley Exp $

/**
 * Functions related to syslog logging.
 *
 * @author Benjamin Drieu <benjamin.drieu@fr.alcove.com> and Alcôve
 * @package phpLDAPadmin
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

# If config_default.php hasnt been called yet, then return.
if (! isset($config))
	return;

/* Initialize syslog */
if ($config->GetValue('debug','syslog')) {
	define_syslog_variables();
	openlog('phpldapadmin', LOG_ODELAY, LOG_DAEMON );
}

/**
 * Verify that syslog logging is activated in the config via the
 * debug->syslog variable and does a call to the syslog() function is it
 * is true.
 *
 * @param emergency	Syslog emergency.
 * @param log_string	String to log.
 */
function syslog_msg ( $emergency, $log_string, $ldapserver=null ) {
	global $config;

	if (isset($config) && $config->GetValue('debug','syslog')) {

		if (isset($ldapserver->server_id))
			$log_string = sprintf('(%s) %s',$ldapserver->getLoggedInDN(),$log_string);

		syslog ( $emergency, $log_string );
	}

	return true;
}

/**
 * Issue an error message via syslog.
 *
 * @param log_string	Log message to send to syslog.
 * @param server_id	If set, print the logged user as well.
 *
 * @return true on success.
 */
function syslog_err ( $log_string, $ldapserver=null ) {
	return syslog_msg ( LOG_ERR, $log_string, $ldapserver );
}

/**
 * Issue a warning message via syslog.
 *
 * @param log_string	Log message to send to syslog.
 * @param server_id	If set, print the logged user as well.
 *
 * @return true on success.
 */
function syslog_warning ( $log_string, $ldapserver=null ) {
	return syslog_msg ( LOG_WARNING, $log_string, $ldapserver );
}

/**
 * Issue a notice message via syslog.
 *
 * @param log_string	Log message to send to syslog.
 * @param server_id	If set, print the logged user as well.
 *
 * @return true on success.
 */
function syslog_notice ( $log_string, $ldapserver=null ) {
	return syslog_msg ( LOG_NOTICE, $log_string, $ldapserver );
}

/**
 * Issue a debug message via syslog, only if $log_level is set to
 * 'debug' from the config file.
 *
 * @param log_string	Log message to send to syslog.
 * @param server_id	If set, print the logged user as well.
 *
 * @return true on success or if debug log is not activated.
 */
function syslog_debug ( $log_string, $ldapserver=null ) {
	return syslog_msg ( LOG_DEBUG, $log_string, $ldapserver );
}
?>
