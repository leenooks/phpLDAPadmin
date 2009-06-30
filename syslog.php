<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/syslog.php,v 1.5 2005/04/05 07:34:23 wurley Exp $

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

?>
