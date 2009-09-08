<?php
/**
 * Functions related to hooks management.
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
 *
 * @author Benjamin Drieu <benjamin.drieu@fr.alcove.com> and Alc√?ve
 * @package phpLDAPadmin
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
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',257,0,__FILE__,__LINE__,__METHOD__,$fargs);

	return (($a['priority'] < $b['priority']) ? -1 : 1 );
}

/**
 * Runs procedures attached to a hook.
 *
 * @param hook_name Name of hook to run.
 * @param args Array of optional arguments set by phpldapadmin. It is normally in a form known by call_user_func_array() :
 *
 * <pre>[ 'server_id' => 0,
 * 'dn' => 'uid=epoussa,ou=tech,o=corp,o=fr' ]</pre>
 *
 * @return true if all procedures returned true, false otherwise.
 */
function run_hook($hook_name,$args) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',257,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$hooks = isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->hooks : array();

	if (! count($hooks) || ! array_key_exists($hook_name,$hooks)) {
		if (DEBUG_ENABLED)
			debug_log('Returning, HOOK not defined (%s)',257,0,__FILE__,__LINE__,__METHOD__,$hook_name);

		return true;
	}

	$rollbacks = array();
	reset($hooks[$hook_name]);

	/* Execution of procedures attached is done using a numeric order
	 * since all procedures have been attached to the hook with a
	 * numerical weight. */
	while (list($key,$hook) = each($hooks[$hook_name])) {
		if (DEBUG_ENABLED)
			debug_log('Calling HOOK Function (%s)(%s)',257,0,__FILE__,__LINE__,__METHOD__,
				$hook['hook_function'],$args);

		array_push($rollbacks,$hook['rollback_function']);

		$result = call_user_func_array($hook['hook_function'],$args);
		if (DEBUG_ENABLED)
			debug_log('Called HOOK Function (%s)',257,0,__FILE__,__LINE__,__METHOD__,
				$hook['hook_function']);

		/* If a procedure fails (identified by a false return), its optional rollback is executed with
		 * the same arguments. After that, all rollbacks from
		 * previously executed procedures are executed in the reverse
		 * order. */
		if (! is_null($result) && $result == false) {
			if (DEBUG_ENABLED)
				debug_log('HOOK Function [%s] return (%s)',257,0,__FILE__,__LINE__,__METHOD__,
					$hook['hook_function'],$result);

			while ($rollbacks) {
				$rollback = array_pop($rollbacks);

				if ($rollback != false) {
					if (DEBUG_ENABLED)
						debug_log('HOOK Function Rollback (%s)',257,0,__FILE__,__LINE__,__METHOD__,
							$rollback);

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
 * @param hook_name Name of the hook.
 * @param hook_function	Name of the php function called upon hook trigger.
 * @param priority Numeric priority. Lowest means procedure will be executed before.
 * @param rollback_function	Name of the php rollback function called upon failure.
 */
function add_hook($hook_name,$hook_function,$priority=0,$rollback_function=null) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',257,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# First, see if the hook function exists.
	if (! function_exists($hook_function)) {
		system_message(array(
			'title'=>_('Hook function does not exist'),
			'body'=>sprintf('Hook name: %s<br/>Hook function: %s',$hook_name,$hook_function),
			'type'=>'warn'));

		return;
	}

	if (! array_key_exists($hook_name,$_SESSION[APPCONFIG]->hooks))
		$_SESSION[APPCONFIG]->hooks[$hook_name] = array();

	remove_hook($hook_name,$hook_function,-1,null);

	array_push($_SESSION[APPCONFIG]->hooks[$hook_name],array(
		'priority' => $priority,
		'hook_function' => $hook_function,
		'rollback_function' => $rollback_function));

	uasort($_SESSION[APPCONFIG]->hooks[$hook_name],'sort_array_by_priority');
}

/**
 * Removes a procedure from a hook, based on a filter.
 *
 * @param hook_name	Name of the hook.
 * @param priority Numeric priority. If set, all procedures of that priority will be removed.
 * @param hook_function Name of the procedure function. If set, all procedures that call this function will be removed.
 * @param rollback_function	Name of the php rollback function called upon failure. If set, all
 *			procedures that call this function as a rollback will be removed.
 */
function remove_hook($hook_name,$hook_function,$priority,$rollback_function) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',257,0,__FILE__,__LINE__,__METHOD__,$fargs);

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
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',257,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (array_key_exists($hook_name,$_SESSION[APPCONFIG]->hooks))
		unset($_SESSION[APPCONFIG]->hooks[$hook_name]);
}

$hooks = array();

# Evaluating user-made hooks
if (is_dir(HOOKSDIR.'functions')) {
	$hooks['dir'] = dir(HOOKSDIR.'functions');

	while ($hooks['file'] = $hooks['dir']->read()) {
		$script = sprintf('%s/%s/%s',HOOKSDIR,'functions',$hooks['file']);

		if (is_file($script) && preg_match('/php[0-9]?$/',$hooks['file']))
			require_once $script;
	}

	$hooks['dir']->close();
}
?>
