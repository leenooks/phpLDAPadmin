<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/hooks.php,v 1.10.2.3 2008/11/28 04:44:54 wurley Exp $

/**
 * Functions related to hooks management.
 *
 * @author Benjamin Drieu <benjamin.drieu@fr.alcove.com> and Alc√?ve
 * @package phpLDAPadmin
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

/**
 * Compares two arrays by numerically comparing their 'prority'
 * value. Standard `cmp-like' function.
 *
 * @param a First element to compare.
 * @param b Second element to compare.
 *
 * @return -1 if priority of first element is smaller than second
 * element priority. 1 otherwise.
 */
function sort_array_by_priority($a,$b) {
	return (($a['priority'] < $b['priority']) ? -1 : 1 );
}

/**
 * Runs procedures attached to a hook.
 *
 * @param hook_name	Name of hook to run.
 * @param args		Array of optional arguments set by
 *			phpldapadmin. It is normally in a form known
 *			by call_user_func_array() :
 * <pre>[ 'server_id' => 0,
 * 'dn' => 'uid=epoussa,ou=tech,o=corp,o=fr' ]</pre>
 *
 * @return true if all procedures returned true, false otherwise.
 */
function run_hook($hook_name,$args) {
	$hooks = isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->hooks : array();

	syslog_debug("Running hook $hook_name.");

	if (! array_key_exists($hook_name,$hooks)) {
		syslog_notice("Hook '$hook_name' not defined !\n");
		return true;
	}

	unset($rollbacks);
	$rollbacks = array ();
	reset($hooks[$hook_name]);

	/* Execution of procedures attached is done using a numeric order
	 * since all procedures have been attached to the hook with a
	 * numerical weight. */
	while (list($key,$hook) = each($hooks[$hook_name])) {
		array_push($rollbacks,$hook['rollback_function']);
		syslog_debug("Calling ".$hook['hook_function']."\n");

		$result = call_user_func_array($hook['hook_function'],$args);
		syslog_notice("Called ".$hook['hook_function']."\n");

		/* If a procedure fails, its optional rollback is executed with
		 * the same arguments. After that, all rollbacks from
		 * previously executed procedures are executed in the reverse
		 * order. */
		if ($result != true) {
			syslog_debug("Function ".$hook['hook_function']." returned $result\n");

			while ($rollbacks) {
				$rollback = array_pop($rollbacks);

				if ($rollback != false) {
					syslog_debug("Executing rollback $rollback\n");
					call_user_func_array($rollback,$args);
				}
			}

			return false;
		}
	}

	return true;
}

/**
 * Adds a procedure to a hook for later execution.
 *
 * @param hook_name		Name of the hook.
 * @param priority		Numeric priority. Lowest means
 *				procedure will be executed before.
 * @param hook_function		Name of the php function called upon
 *				hook trigger.
 * @param rollback_function	Name of the php rollback function
 *				called upon failure.
 */
function add_hook($hook_name,$priority,$hook_function,$rollback_function) {
	if (! array_key_exists($hook_name,$_SESSION[APPCONFIG]->hooks))
		$_SESSION[APPCONFIG]->hooks[$hook_name] = array();

	remove_hook($hook_name,-1,$hook_function,'');

	array_push($_SESSION[APPCONFIG]->hooks[$hook_name],array(
		'priority' => $priority,
		'hook_function' => $hook_function,
		'rollback_function' => $rollback_function));

	uasort($_SESSION[APPCONFIG]->hooks[$hook_name],'sort_array_by_priority');
}

/**
 * Removes a procedure from a hook, based on a filter.
 *
 * @param hook_name		Name of the hook.
 * @param priority		Numeric priority. If set, all
 *				procedures of that priority will be
 *				removed.
 * @param hook_function		Name of the procedure function. If
 *				set, all procedures that call this
 *				function will be removed.
 * @param rollback_function	Name of the php rollback function
 *				called upon failure. If set, all
 *				procedures that call this function
 *				as a rollback will be removed.
 */
function remove_hook($hook_name,$priority,$hook_function,$rollback_function) {
	if (array_key_exists($hook_name,$_SESSION[APPCONFIG]->hooks)) {
		reset($_SESSION[APPCONFIG]->hooks[$hook_name]);

		while (list($key,$hook) = each($_SESSION[APPCONFIG]->hooks[$hook_name])) {
			if (($priority >= 0 && $priority == $hook['priority']) ||
				($hook_function && $hook_function == $hook['hook_function']) ||
				($rollback_function && $rollback_function == $hook['rollback_function'])) {

				unset($_SESSION[APPCONFIG]->hooks[$hook_name][$key]);
			}
		}
	}
}

/**
 * Removes all procedures from a hook.
 *
 * @param hook_name	Name of hook to clear.
 */
function clear_hooks($hook_name) {
	if (array_key_exists($hook_name,$_SESSION[APPCONFIG]->hooks))
		unset($_SESSION[APPCONFIG]->hooks[$hook_name]);
}

# Evaluating user-made hooks
if (is_dir(HOOKSDIR.'functions')) {
	$dir = dir(HOOKSDIR.'functions');

	while (false !== ($hookfile = $dir->read())) {
		$filename = sprintf('%s/%s/%s',HOOKSDIR,'functions',$hookfile);

		if (is_file($filename) and eregi('php[0-9]?$',$hookfile))
			require_once "$filename";
	}

	$dir -> close();
}
?>
