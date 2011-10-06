<?php
/**
 * Classes and functions for the template engines.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**/
# To make it easier to debug this script, define these constants, which will add some __METHOD__ location displays to the rendered text.
define('DEBUGTMP',0);
define('DEBUGTMPSUB',0);

/**
 * Abstract Visitor class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
abstract class Visitor {
	# The server that was used to configure the templates
	protected $server_id;

	public function __call($method,$args) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! in_array($method,array('get','visit','draw')))
			debug_dump_backtrace(sprintf('Incorrect use of method loading [%s]',$method),1);

		$methods = array();

		$fnct = array_shift($args);

		$object = $args[0];
		$class = get_class($object);

		$call = "$method$fnct$class";

		array_push($methods,$call);

		while ($class && ! method_exists($this,$call)) {
			if (defined('DEBUGTMP') && DEBUGTMP)
				printf('<font size=-2><i>Class (%s): Method doesnt exist (%s,%s)</i></font><br />',$class,get_class($this),$call);

			$class = get_parent_class($class);
			$call = "$method$fnct$class";
			array_push($methods,$call);
		}

		if (defined('DEBUGTMP') && DEBUGTMP)
			printf('<font size=-2><i>Calling Methods: %s</i></font><br />',implode('|',$methods));

		if (defined('DEBUGTMP') && DEBUGTMP && method_exists($this,$call))
			printf('<font size=-2>Method Exists: %s::%s (%s)</font><br />',get_class($this),$call,$args);

		if (method_exists($this,$call)) {
			$r = call_user_func_array(array($this,$call),$args);

			if (isset($r))
				return $r;
			else
				return;

		} elseif (DEBUG_ENABLED) {
			debug_log('Doesnt exist param (%s,%s)',1,0,__FILE__,__LINE__,__METHOD__,$method,$fnct);
		}

		printf('<font size=-2><i>NO Methods: %s</i></font><br />',implode('|',$methods));
	}

	/**
	 * Return the LDAP server ID
	 *
	 * @return int Server ID
	 */
	public function getServerID() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->server_id);

		if (isset($this->server_id))
			return $this->server_id;
		else
			return null;
	}

	/**
	 * Return this LDAP Server object
	 *
	 * @return object DataStore Server
	 */
	protected function getServer() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $_SESSION[APPCONFIG]->getServer($this->getServerID());
	}
}
?>
