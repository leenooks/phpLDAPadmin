<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/syslog.php,v 1.7 2005/04/18 21:24:44 wurley Exp $

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

// require_once 'config.php';

/* Initialize syslog */
if ( isset($use_syslog) && $use_syslog != false ) {
	define_syslog_variables();
	openlog('phpldapadmin', LOG_ODELAY, LOG_DAEMON );
}

/**
 * Verify that syslog logging is activated in the config via the
 * $use_syslog variable and does a call to the syslog() function is it
 * is true.
 *
 * @param emergency	Syslog emergency.
 * @param log_string	String to log.
 */
function syslog_msg ( $emergency, $log_string, $ldapserver=null ) {
	global $use_syslog;

	if ( isset($ldapserver->server_id) ) {
		$log_string = "(" . get_logged_in_dn( $ldapserver->ldapserver ) . ") " . $log_string;
	}

	if ( isset($use_syslog) && $use_syslog != false) {
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
function syslog_error ( $log_string, $ldapserver=null ) {
	global $use_syslog;

	if ( isset($use_syslog) && $use_syslog != false)
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
	global $use_syslog;

	if ( isset($use_syslog) && $use_syslog != false)
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
	global $use_syslog;

	if ( isset($use_syslog) && $use_syslog != false)
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
	global $log_level;

	if ( isset($log_level) and $log_level == 'debug' )
		return syslog_msg ( LOG_DEBUG, $log_string, $ldapserver );

	return true;
}
?>
