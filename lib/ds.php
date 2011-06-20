<?php
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
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,1,__FILE__,__LINE__,__METHOD__,$fargs);

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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->getValue('server','name');
	}

	/**
	 * Functions that enable login and logout of the application
	 */
	/**
	 * Return the authentication type for this object
	 */
	public function getAuthType() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		switch ($this->getValue('login','auth_type')) {
			case 'cookie':
			case 'config':
			case 'http':
			case 'proxy':
			case 'session':
			case 'sasl':
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$method = $this->getMethod($method);

		# For anonymous binds
		if ($method == 'anon')
			if (isset($_SESSION['USER'][$this->index][$method]['name']))
				return '';
			else
				return null;

		switch ($this->getAuthType()) {
			case 'cookie':
				if (! isset($_COOKIE[$method.'-USER']))
					# If our bind_id is set, we'll pass that back for logins.
					return (! is_null($this->getValue('login','bind_id')) && $method == 'login') ? $this->getValue('login','bind_id') : null;
				else
					return blowfish_decrypt($_COOKIE[$method.'-USER']);

			case 'config':
				if (! isset($_SESSION['USER'][$this->index][$method]['name']))
					return $this->getValue('login','bind_id');
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['name']);

			case 'proxy':
				if (! isset($_SESSION['USER'][$this->index][$method]['proxy']))
					return $this->getValue('login','bind_id');
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['proxy']);

			case 'http':
			case 'session':
			case 'sasl':
				if (! isset($_SESSION['USER'][$this->index][$method]['name']))
					# If our bind_id is set, we'll pass that back for logins.
					return (! is_null($this->getValue('login','bind_id')) && $method == 'login') ? $this->getValue('login','bind_id') : null;
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$method = $this->getMethod($method);

		switch ($this->getAuthType()) {
			case 'cookie':
				set_cookie($method.'-USER',blowfish_encrypt($user),NULL,'/');
				set_cookie($method.'-PASS',blowfish_encrypt($pass),NULL,'/');
				return true;

			case 'config':
				return true;

			case 'proxy':
				if (isset($_SESSION['USER'][$this->index][$method]['proxy']))
					unset($_SESSION['USER'][$this->index][$method]['proxy']);

			case 'http':
			case 'session':
			case 'sasl':
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$method = $this->getMethod($method);

		# For anonymous binds
		if ($method == 'anon')
			if (isset($_SESSION['USER'][$this->index][$method]['name']))
				return '';
			else
				return null;

		switch ($this->getAuthType()) {
			case 'cookie':
				if (! isset($_COOKIE[$method.'-PASS']))
					# If our bind_id is set, we'll pass that back for logins.
					return (! is_null($this->getValue('login','bind_pass')) && $method == 'login') ? $this->getValue('login','bind_pass') : null;
				else
					return blowfish_decrypt($_COOKIE[$method.'-PASS']);

			case 'config':
			case 'proxy':
				if (! isset($_SESSION['USER'][$this->index][$method]['pass']))
					return $this->getValue('login','bind_pass');
				else
					return blowfish_decrypt($_SESSION['USER'][$this->index][$method]['pass']);

			case 'http':
			case 'session':
			case 'sasl':
				if (! isset($_SESSION['USER'][$this->index][$method]['pass']))
					# If our bind_pass is set, we'll pass that back for logins.
					return (! is_null($this->getValue('login','bind_pass')) && $method == 'login') ? $this->getValue('login','bind_pass') : null;
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE = array();

		$method = $this->getMethod($method);

		if (isset($CACHE[$this->index][$method]) && ! is_null($CACHE[$this->index][$method]))
			return $CACHE[$this->index][$method];

		$CACHE[$this->index][$method] = null;

		# For some authentication types, we need to do the login here
		switch ($this->getAuthType()) {
			case 'config':
				if (! $CACHE[$this->index][$method] = $this->login($this->getLogin($method),$this->getPassword($method),$method))
					system_message(array(
						'title'=>_('Unable to login.'),
						'body'=>_('Your configuration file has authentication set to CONFIG based authentication, however, the userid/password failed to login'),
						'type'=>'error'));

				break;

			case 'http':
				# If our auth vars are not set, throw up a login box.
				if (! isset($_SERVER['PHP_AUTH_USER'])) {
					# If this server is not in focus, skip the basic auth prompt.
					if (get_request('server_id','REQUEST') != $this->getIndex()) {
						$CACHE[$this->index][$method] = false;
						break;
					}

					header(sprintf('WWW-Authenticate: Basic realm="%s %s"',app_name(),_('login')));

					if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0')
						header('HTTP/1.0 401 Unauthorized'); // http 1.0 method
					else
						header('Status: 401 Unauthorized'); // http 1.1 method

					# If we still dont have login details...
					if (! isset($_SERVER['PHP_AUTH_USER'])) {
						system_message(array(
							'title'=>_('Unable to login.'),
							'body'=>_('Your configuration file has authentication set to HTTP based authentication, however, there was none presented'),
							'type'=>'error'));

						$CACHE[$this->index][$method] = false;
					}

				# Check our auth vars are valid.
				} else {
					if (! $this->login($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'],$method)) {
						system_message(array(
							'title'=>_('Unable to login.'),
							'body'=>_('Your HTTP based authentication is not accepted by the LDAP server'),
							'type'=>'error'));

						$CACHE[$this->index][$method] = false;

					} else
						$CACHE[$this->index][$method] = true;
				}

				break;

			case 'proxy':
				$CACHE[$this->index][$method] = $this->login($this->getValue('login','bind_id'),$this->getValue('login','bind_pass'),$method);

				break;

			case 'sasl':
				# Propogate any given Kerberos credential cache location
				if (isset($_ENV['REDIRECT_KRB5CCNAME']))
					putenv(sprintf('KRB5CCNAME=%s',$_ENV['REDIRECT_KRB5CCNAME']));
				elseif (isset($_SERVER['KRB5CCNAME']))
					putenv(sprintf('KRB5CCNAME=%s',$_SERVER['KRB5CCNAME']));

				# Map the SASL auth ID to a DN
				$regex = $this->getValue('login', 'sasl_dn_regex');
				$replacement = $this->getValue('login', 'sasl_dn_replacement');

				if ($regex && $replacement) {
					$userDN = preg_replace($regex, $replacement, $_SERVER['REMOTE_USER']);

					$CACHE[$this->index][$method] = $this->login($userDN, '', $method);

				# Otherwise, use the user name as is
				# For GSSAPI Authentication + mod_auth_kerb and Basic Authentication
				} else
					$CACHE[$this->index][$method] = $this->login(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : '', '', $method);

				break;

			default:
				$CACHE[$this->index][$method] = is_null($this->getLogin($method)) ? false : true;
		}

		return $CACHE[$this->index][$method];
	}

	/**
	 * Logout of this datastore's connection method
	 */
	public function logout($method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$method = $this->getMethod($method);

		unset ($_SESSION['cache'][$this->index]);

		switch ($this->getAuthType()) {
			case 'cookie':
				set_cookie($method.'-USER','',time()-3600,'/');
				set_cookie($method.'-PASS','',time()-3600,'/');

			case 'config':
				return true;

			case 'http':
			case 'proxy':
			case 'session':
			case 'sasl':
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->getValue('server','visible');
	}

	public function isReadOnly() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! trim($this->getLogin(null)) && $_SESSION[APPCONFIG]->getValue('appearance','anonymous_bind_implies_read_only'))
			return true;
		else
			return $this->getValue('server','read_only');
	}

	public function getIndex() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->index);

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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE = array();

		# Immediately return if method is set.
		if (! is_null($method))
			return $method;

		# If we have been here already, then return our result
		if (isset($CACHE[$this->index]) && ! is_null($CACHE))
			return $CACHE[$this->index];

		$CACHE[$this->index] = 'anon';

		if ($this->isLoggedIn('user'))
			$CACHE[$this->index] = 'user';

		return $CACHE[$this->index];
	}

	/**
	 * This method should be overridden in application specific ds files
	 */
	public function isSessionValid() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,1,__FILE__,__LINE__,__METHOD__,$fargs,true);

		return true;
	}

	/**
	 * Return the time left in seconds until this connection times out. If there is not timeout,
	 * this function will return null.
	 */
	public function inactivityTime() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->isLoggedIn() && ! in_array($this->getAuthType(),array('config','http')))
			return time()+($this->getValue('login','timeout')*60);
		else
			return null;
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

		$this->default->server['hide_noaccess_base'] = array(
			'desc'=>'If base DNs are not accessible, hide them instead of showing create',
			'default'=>false);

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

		$this->default->login['sasl_dn_regex'] = array(
			'desc'=>'SASL authorization id to user dn PCRE regular expression',
			'untested'=>true,
			'default'=>null);

		$this->default->login['sasl_dn_replacement'] = array(
			'desc'=>'SASL authorization id to user dn PCRE regular expression replacement string',
			'untested'=>true,
			'default'=>null);

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
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE;

		if (isset($CACHE[$isVisible]))
			return $CACHE[$isVisible];

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

		masort($CACHE[$isVisible],'name');

		return $CACHE[$isVisible];
	}

	/**
	 * Return an object Instance of a configured database.
	 *
	 * @param int Index
	 * @return object Datastore instance object.
	 */
	public function Instance($index=null) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If no index defined, then pick the lowest one.
		if (is_null($index) || ! trim($index) || ! is_numeric($index))
			$index = min($this->GetServerList())->getIndex();

		if (! isset($this->objects[$index]))
			debug_dump_backtrace(sprintf('Error: Datastore instance [%s] doesnt exist?',htmlspecialchars($index)),1);

		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Returning instance of database (%s)',3,0,__FILE__,__LINE__,__METHOD__,$index);

		return $this->objects[$index];
	}

	/**
	 * Return an object Instance of a configured database.
	 *
	 * @param string Name of the instance to retrieve
	 * @return object Datastore instance object.
	 */
	public function InstanceName($name=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->getServerList(false) as $index)
			if ($this->objects[$index->getIndex()]->getValue('server','id') == $id)
				return $this->objects[$index->getIndex()];

		# If we get here, then no object with the name exists.
		return null;
	}
}
?>
