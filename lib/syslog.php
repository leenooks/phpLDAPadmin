<?php
/**
 * Functions related to syslog logging.
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
 *
 * @author Benjamin Drieu <benjamin.drieu@fr.alcove.com> and Alcôve
 * @package phpLDAPadmin
 */

# If config_default.php hasnt been called yet, then return.
if (! defined('APPCONFIG') || ! isset($_SESSION[APPCONFIG]))
	return;

# Initialize syslog
if ($_SESSION[APPCONFIG]->getValue('debug','syslog') && function_exists('syslog')) {
	openlog('phpldapadmin',LOG_ODELAY,LOG_DAEMON);
}

/**
 * Verify that syslog logging is activated in the config via the
 * debug->syslog variable and does a call to the syslog() function is it
 * is true.
 *
 * @param emergency	Syslog emergency.
 * @param log_string String to log.
 */
function syslog_msg($emergency,$log_string) {
	if (! function_exists('syslog') || ! isset($_SESSION[APPCONFIG]) || ! $_SESSION[APPCONFIG]->getValue('debug','syslog'))
		return;

	return syslog($emergency,$log_string);
}

/**
 * Issue an error message via syslog.
 *
 * @param log_string Log message to send to syslog.
 * @return true on success.
 */
function syslog_err($log_string) {
	return syslog_msg(LOG_ERR,$log_string);
}

/**
 * Issue a warning message via syslog.
 *
 * @param log_string Log message to send to syslog.
 * @return true on success.
 */
function syslog_warning($log_string) {
	return syslog_msg(LOG_WARNING,$log_string);
}

/**
 * Issue a notice message via syslog.
 *
 * @param log_string Log message to send to syslog.
 * @return true on success.
 */
function syslog_notice($log_string) {
	return syslog_msg(LOG_NOTICE,$log_string);
}

/**
 * Issue a debug message via syslog, only if $log_level is set to
 * 'debug' from the config file.
 *
 * @param log_string Log message to send to syslog.
 * @return true on success or if debug log is not activated.
 */
function syslog_debug($log_string) {
	return syslog_msg(LOG_DEBUG,$log_string);
}
?>
