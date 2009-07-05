<?php
// $Header$

/**
 * Classes and functions for communication of Data Stores
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * This abstract class provides the basic variables and methods.
 *
 * @package phpLDAPadmin
 * @subpackage DataStore
 */
abstract class DS {
	# ID of this db.
	protected $index;

	# Configuration paramters.
	protected $default;
	protected $custom;
	protected $type;

	abstract function __construct($index);

	/**
	 * This will make the connection to the datasource
	 */
	abstract protected function connect($method,$debug=false);

	/**
	 * Login to the datastore
	 *  method: default = anon, connect to ds using bind_id not auth_id.
	 *  method: 'user', connect with auth_id
	 *  method: '<freetext>', any custom extra connection to ds.
	 */
	abstract public function login($user=null,$pass=null,$method=null);

	/**
	 * Query the datasource
	 */
	abstract public function query($query,$method,$index=null,$debug=false);

	/**
	 * Return error details from previous operation
	 */
	abstract protected function getErrorMessage();
	abstract protected function getErrorNum();

	/**
	 * Functions that set and verify object configuration details
	 */
	public function setDefaults($defaults) {
		foreach ($defaults as $key => $details)
			foreach ($details as $setting => $value)
				$this->default->{$key}[$setting] = $value;
	}

	public function isDefaultKey($key) {
		return isset($this->default->$key);
	}

	public function isDefaultSetting($key,$setting) {
		return array_key_exists($setting,$this->default->{$key});
	}

	/**
	 * Return a configuration value
	 */
	public function getValue($key,$setting,$fatal=true) {
		if (isset($this->custom->{$key}[$setting]))
			return $this->custom->{$key}[$setting];

		elseif (isset($this->default->{$key}[$setting]) && array_key_exists('default',$this->default->{$key}[$setting]))
			return $this->default->{$key}[$setting]['default'];

		elseif ($fatal)
			debug_dump_backtrace("Error trying to get a non-existant value ($key,$setting)",1);

		else
			return null;
	}

	/**
	 * Set a configuration value
	 */
	public function setValue($key,$setting,$value) {
		if (isset($this->custom->{$key}[$setting]))
			system_message(array(
				'title'=>_('Configuration setting already defined.'),
				'body'=>sprintf('A call has been made to reset a configuration value (%s,%s,%s)',
					$key,$setting,$value),
				'type'=>'info'));

		$this->custom->{$key}[$setting] = $value;
	}

	/**
	 * Return the untested config items
	 */
	public function untested() {
		$result = array();

		foreach ($this->default as $option => $details)
			foreach ($details as $param => $values)
				if (isset($values['untested']) && $values['untested'])
					array_push($result,sprintf('%s.%s',$option,$param));

		return $result;
	}

	/**
	 * Get the name of this datastore
	 */
	public function getName() {
		return $this->getValue('server','name');
	}

	/**
	 * Functions that enable login and logout of the application
	 */
	/**
	 * Return the authentication type for this object
	 */
	public function getAuthType() {
		switch ($this->getValue('login','auth_type')) {
			case 'config':
			case 'session':
				return $this->getValue('login','auth_type');

			default:
				die(sprintf('Error: <b>%s</b> hasnt been configured for auth_type <b>%s</b>',__METHOD__,
					$this->getValue('login','auth_type')));
		}
	}

	/**
	 * Get the login name of the user logged into this datastore's connection method
	 * If this returns null, we are not logged in.
	 * If this returns '', we are logged in with anonymous
	 */
	public function getLogin($method=null) {
		$method = $this->getMethod($method);

		if ($method == 'unauth')
			return '';

		switch ($this->getAuthType()) {
			case 'config':
				if (! isset($_SESSION['USER'][$this->index][$method]['name']))
					return $this->getValue('login','bind_id');
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['name']);

			case 'session':
				if (! isset($_SESSION['USER'][$this->index][$method]['name']))
					return null;
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['name']);

			default:
				die(sprintf('Error: %s hasnt been configured for auth_type %s',__METHOD__,$this->getAuthType()));
		}
	}

	/**
	 * Set the login details of the user logged into this datastore's connection method
	 */
	protected function setLogin($user,$pass,$method=null) {
		$method = $this->getMethod($method);

		switch ($this->getAuthType()) {
			case 'config':
			case 'session':
				$_SESSION['USER'][$this->index][$method]['name'] = blowfish_encrypt($user);
				$_SESSION['USER'][$this->index][$method]['pass'] = blowfish_encrypt($pass);

				return true;

			default:
				die(sprintf('Error: %s hasnt been configured for auth_type %s',__METHOD__,$this->getAuthType()));
		}
	}

	/**
	 * Get the login password of the user logged into this datastore's connection method
	 */
	protected function getPassword($method=null) {
		$method = $this->getMethod($method);

		if ($method == 'unauth')
			return '';

		switch ($this->getAuthType()) {
			case 'config':
				if (! isset($_SESSION['USER'][$this->index][$method]['pass']))
					return $this->getValue('login','bind_pass');
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['pass']);

			case 'session':
				if (! isset($_SESSION['USER'][$this->index][$method]['pass']))
					return null;
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['pass']);

			default:
				die(sprintf('Error: %s hasnt been configured for auth_type %s',__METHOD__,$this->getAuthType()));
		}
	}

	/**
	 * Return if this datastore's connection method has been logged into
	 */
	public function isLoggedIn($method=null) {
		$method = $this->getMethod($method);

		return is_null($this->getLogin($method)) ? false : true;
	}

	/**
	 * Logout of this datastore's connection method
	 */
	public function logout($method=null) {
		$method = $this->getMethod($method);

		switch ($this->getAuthType()) {
			case 'config':
				if (isset($_SESSION['USER'][$this->index][$method]))
					unset($_SESSION['USER'][$this->index][$method]);

				return true;

			case 'session':
				if (isset($_SESSION['USER'][$this->index][$method]))
					unset($_SESSION['USER'][$this->index][$method]);

				return true;

			default:
				die(sprintf('Error: %s hasnt been configured for auth_type %s',__METHOD__,$this->getAuthType()));
		}
	}

	/**
	 * Functions that return the condition of the datasource
	 */
	public function isVisible() {
		return $this->getValue('server','visible');
	}

	public function isReadOnly() {
		if (! trim($this->getLogin(null)) && $_SESSION[APPCONFIG]->getValue('appearance','anonymous_bind_implies_read_only'))
			return true;
		else
			return $this->getValue('server','read_only');
	}

	public function getIndex() {
		return $this->index;
	}

	/** 
	 * Work out which connection method to use.
	 * If a method is passed, then it will be passed back. If no method is passed, then we'll
	 * check to see if the user is logged in. If they are, then 'user' is used, otherwise
	 * 'anon' is used.
	 *
	 * @param int Server ID
	 * @return string Connection Method
	 */
	protected function getMethod($method=null) {
		# Immediately return if method is set.
		if (! is_null($method))
			return $method;

		if ($this->isLoggedIn('user'))
			return 'user';
		else
			return 'anon';
	}
}

/**
 * The list of database sources
 *
 * @package phpLDAPadmin
 * @subpackage DataStore
 */
class Datastore {
	# Out DS index id
	private $index;
	# List of all the objects
	private $objects = array();
	# Default settings
	private $default;

	public function __construct() {
		$this->default = new StdClass;

		$this->default->server['id'] = array(
			'desc'=>'Server ID',
			'default'=>null);

		$this->default->server['name'] = array(
			'desc'=>'Server name',
			'default'=>null);

		# Connectivity Info
		$this->default->server['host'] = array(
			'desc'=>'Host Name',
			'default'=>'127.0.0.1');

		$this->default->server['port'] = array(
			'desc'=>'Port Number',
			'default'=>null);

		# Read or write only access
		$this->default->server['read_only'] = array(
			'desc'=>'Server is in READ ONLY mode',
			'default'=>false);

		$this->default->server['visible'] = array(
			'desc'=>'Whether this server is visible',
			'default'=>true);

		# Authentication Information
		$this->default->login['auth_type'] = array(
			'desc'=>'Authentication Type',
			'default'=>'session');

/*
		/* ID to login to this application, this assumes that there is
		 * application authentication on top of authentication required to
		 * access the data source **
		$this->default->login['auth_id'] = array(
			'desc'=>'User Login ID to login to this DS',
			'untested'=>true,
			'default'=>null);

		$this->default->login['auth_pass'] = array(
			'desc'=>'User Login Password to login to this DS',
			'untested'=>true,
			'default'=>null);
*/

		$this->default->login['auth_text'] = array(
			'desc'=>'Text to show at the login prompt',
			'default'=>null);

		$this->default->login['bind_id'] = array(
			'desc'=>'User Login ID to bind to this DS',
			'default'=>null);

		$this->default->login['bind_pass'] = array(
			'desc'=>'User Login Password to bind to this DS',
			'default'=>null);

		$this->default->login['timeout'] = array(
			'desc'=>'Session timout in seconds',
			'default'=>session_cache_expire()-1);

		# Prefix for custom pages
		$this->default->custom['pages_prefix'] = array(
			'desc'=>'Prefix name for custom pages',
			'default'=>'custom_');
	}

	/**
	 * Create a new database object
	 */
	public function newServer($type) {
		if (class_exists($type)) {
			$this->index = count($this->objects)+1;
			$this->objects[$this->index] = new $type($this->index);

			$this->objects[$this->index]->setDefaults($this->default);
			return $this->index;

		} else {
			printf('ERROR: Class [%s] doesnt exist',$type);
			die();
		}
	}

	/**
	 * Set values for a database object.
	 */
	public function setValue($key,$setting,$value) {
		if (defined('DEBUG_ENABLED') && (DEBUG_ENABLED))
			debug_log('Entered with (%s,%s,%s)',3,__FILE__,__LINE__,__METHOD__,
				$key,$setting,$value);

		if (! $this->objects[$this->index]->isDefaultKey($key))
			error("ERROR: Setting a key [$key] that isnt predefined.",'error',true);

		if (! $this->objects[$this->index]->isDefaultSetting($key,$setting))
			error("ERROR: Setting a index [$key,$setting] that isnt predefined.",'error',true);

		# Test if its should be an array or not.
		if (is_array($this->objects[$this->index]->getValue($key,$setting)) && ! is_array($value))
			error("Error in configuration file, {$key}['$setting'] SHOULD be an array of values.",'error',true);

		if (! is_array($this->objects[$this->index]->getValue($key,$setting)) && is_array($value))
			error("Error in configuration file, {$key}['$setting'] should NOT be an array of values.",'error',true);

		# Store the value in the object.
		$this->objects[$this->index]->setValue($key,$setting,$value);
	}

	/**
	 * Get a list of all the configured servers.
	 *
	 * @param boolean Only show visible servers.
	 * @return array list of all configured servers.
	 */
	public function getServerList($isVisible=true) {
		static $CACHE;

		if (isset($CACHE[$isVisible]))
			return $CACHE[$isVisible];

		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with (%s)',3,__FILE__,__LINE__,__METHOD__,$isVisible);

		$CACHE[$isVisible] = array();

		# Debugging incase objects is not set.
		if (! $this->objects) {
			print "<PRE>";
			debug_print_backtrace();
			die();
		}

		foreach ($this->objects as $id => $server)
			if (! $isVisible || ($isVisible && $server->getValue('server','visible')))
				$CACHE[$isVisible][$id] = $server;

		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with (%s), Returning (%s)',3,__FILE__,__LINE__,__METHOD__,
				$isVisible,$CACHE);

		return $CACHE[$isVisible];
	}

	/**
	 * Return an object Instance of a configured database.
	 *
	 * @param int Index
	 * @return object Datastore instance object.
	 */
	public function Instance($index=null) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with (%s)',3,__FILE__,__LINE__,__METHOD__,$index);

		# If no index defined, then pick the lowest one.
		if (is_null($index))
			$index = min($this->GetServerList())->getIndex();

		if (! isset($this->objects[$index]))
			debug_dump_backtrace("Error: Datastore instance [$index] doesnt exist?",1);

		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Returning instance of database (%s)',3,__FILE__,__LINE__,__METHOD__,$index);

		return $this->objects[$index];
	}

	/**
	 * Return an object Instance of a configured database.
	 *
	 * @param string Name of the instance to retrieve
	 * @return object Datastore instance object.
	 */
	public function InstanceName($name=null) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',3,__FILE__,__LINE__,__METHOD__,$name);

		foreach ($this->getServerList(false) as $index)
			if ($this->objects[$index]->getName() == $name)
				return $this->objects[$index];

		# If we get here, then no object with the name exists.
		return null;
	}

	/**
	 * Return an object Instance of a configured database.
	 *
	 * @param string ID of the instance to retrieve
	 * @return object Datastore instance object.
	 */
	public function InstanceId($id=null) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',3,__FILE__,__LINE__,__METHOD__,$id);

		foreach ($this->getServerList(false) as $index)
			if ($this->objects[$index->getIndex()]->getValue('server','id') == $id)
				return $this->objects[$index->getIndex()];

		# If we get here, then no object with the name exists.
		return null;
	}
}
?>
