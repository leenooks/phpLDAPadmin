<?php
/**
 * Classes and functions for communication of Data Stores
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * This abstract class provides the basic variables and methods for LDAP datastores
 *
 * @package phpLDAPadmin
 * @subpackage DataStore
 */
class ldap extends DS {
	# If we fail to connect, set this to true
	private $noconnect = false;
	# Raw Schema entries
	private $_schema_entries = null;
	# Schema DN
	private $_schemaDN = null;

	public function __construct($index) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->index = $index;
		$this->type = 'ldap';

		# Additional values that can go in our config.php
		$this->custom = new StdClass;
		$this->default = new StdClass;

/*
 * Not used by PLA
		# Database Server Variables
		$this->default->server['db'] = array(
			'desc'=>'Database Name',
			'untested'=>true,
			'default'=>null);
*/

		/* This was created for IDS - since it doesnt present STRUCTURAL against objectClasses
		 * definitions when reading the schema.*/
		$this->default->server['schema_oclass_default'] = array(
			'desc'=>'When reading the schema, and it doesnt specify objectClass type, default it to this',
			'default'=>null);

		$this->default->server['base'] = array(
			'desc'=>'LDAP Base DNs',
			'default'=>array());

		$this->default->server['tls'] = array(
			'desc'=>'Connect using TLS',
			'default'=>false);

		# Login Details
		$this->default->login['attr'] = array(
			'desc'=>'Attribute to use to find the users DN',
			'default'=>'dn');

		$this->default->login['anon_bind'] = array(
			'desc'=>'Enable anonymous bind logins',
			'default'=>true);

		$this->default->login['allowed_dns'] = array(
			'desc'=>'Limit logins to users who match any of the following LDAP filters',
			'default'=>array());

		$this->default->login['base'] = array(
			'desc'=>'Limit logins to users who are in these base DNs',
			'default'=>array());

		$this->default->login['class'] = array(
			'desc'=>'Strict login to users containing a specific objectClasses',
			'default'=>array());

		$this->default->proxy['attr'] = array(
			'desc'=>'Attribute to use to find the users DN for proxy based authentication',
			'default'=>array());

		# SASL configuration
		$this->default->sasl['mech'] = array(
			'desc'=>'SASL mechanism used while binding LDAP server',
			'default'=>'GSSAPI');

		$this->default->sasl['realm'] = array(
			'desc'=>'SASL realm name',
			'untested'=>true,
			'default'=>null);

		$this->default->sasl['authz_id'] = array(
			'desc'=>'SASL authorization id',
			'untested'=>true,
			'default'=>null);

		$this->default->sasl['authz_id_regex'] = array(
			'desc'=>'SASL authorization id PCRE regular expression',
			'untested'=>true,
			'default'=>null);

		$this->default->sasl['authz_id_replacement'] = array(
			'desc'=>'SASL authorization id PCRE regular expression replacement string',
			'untested'=>true,
			'default'=>null);

		$this->default->sasl['props'] = array(
			'desc'=>'SASL properties',
			'untested'=>true,
			'default'=>null);
	}

	/**
	 * Required ABSTRACT functions
	 */
	/**
	 * Connect and Bind to the Database
	 *
	 * @param string Which connection method resource to use
	 * @return resource|null Connection resource if successful, null if not.
	 */
	protected function connect($method,$debug=false,$new=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE = array();

		$method = $this->getMethod($method);
		$bind = array();

		if (isset($CACHE[$this->index][$method]) && $CACHE[$this->index][$method])
			return $CACHE[$this->index][$method];

		# Check if we have logged in and therefore need to use those details as our bind.
		$bind['id'] = is_null($this->getLogin($method)) && $method != 'anon' ? $this->getLogin('user') : $this->getLogin($method);
		$bind['pass'] = is_null($this->getPassword($method)) && $method != 'anon' ? $this->getPassword('user') : $this->getPassword($method);

		# If our bind id is still null, we are not logged in.
		if (is_null($bind['id']) && ! in_array($method,array('anon','login')))
			return null;

		# If we bound to the LDAP server with these details for a different connection, return that resource
		if (isset($CACHE[$this->index]) && ! $new)
			foreach ($CACHE[$this->index] as $cachedmethod => $resource) {
				if (($this->getLogin($cachedmethod) == $bind['id']) && ($this->getPassword($cachedmethod) == $bind['pass'])) {
					$CACHE[$this->index][$method] = $resource;

					return $CACHE[$this->index][$method];
				}
			}

		$CACHE[$this->index][$method] = null;

		# No identifiable connection exists, lets create a new one.
		if (DEBUG_ENABLED)
			debug_log('Creating NEW connection [%s] for index [%s]',16,0,__FILE__,__LINE__,__METHOD__,
				$method,$this->index);

		if (function_exists('run_hook'))
			run_hook('pre_connect',array('server_id'=>$this->index,'method'=>$method));

		if ($this->getValue('server','port'))
			$resource = ldap_connect($this->getValue('server','host'),$this->getValue('server','port'));
		else
			$resource = ldap_connect($this->getValue('server','host'));

		$CACHE[$this->index][$method] = $resource;

		if (DEBUG_ENABLED)
			debug_log('LDAP Resource [%s], Host [%s], Port [%s]',16,0,__FILE__,__LINE__,__METHOD__,
				$resource,$this->getValue('server','host'),$this->getValue('server','port'));

		if (! is_resource($resource))
			debug_dump_backtrace('UNHANDLED, $resource is not a resource',1);

		# Go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
		ldap_set_option($resource,LDAP_OPT_PROTOCOL_VERSION,3);

		/* Disabling this makes it possible to browse the tree for Active Directory, and seems
		 * to not affect other LDAP servers (tested with OpenLDAP) as phpLDAPadmin explicitly
		 * specifies deref behavior for each ldap_search operation. */
		ldap_set_option($resource,LDAP_OPT_REFERRALS,0);

		# Try to fire up TLS is specified in the config
		if ($this->isTLSEnabled())
			$this->startTLS($resource);

		# If SASL has been configured for binding, then start it now.
		if ($this->isSASLEnabled())
			$bind['result'] = $this->startSASL($resource,$method);

		# Normal bind...
		else
			$bind['result'] = @ldap_bind($resource,$bind['id'],$bind['pass']);

		if ($debug)
			debug_dump(array('method'=>$method,'bind'=>$bind,'USER'=>$_SESSION['USER']));

		if (DEBUG_ENABLED)
			debug_log('Resource [%s], Bind Result [%s]',16,0,__FILE__,__LINE__,__METHOD__,$resource,$bind);

		if (! $bind['result']) {
			if (DEBUG_ENABLED)
				debug_log('Leaving with FALSE, bind FAILed',16,0,__FILE__,__LINE__,__METHOD__);

			$this->noconnect = true;

			system_message(array(
				'title'=>sprintf('%s %s',_('Unable to connect to LDAP server'),$this->getName()),
				'body'=>sprintf('<b>%s</b>: %s (%s) for <b>%s</b>',_('Error'),$this->getErrorMessage($method),$this->getErrorNum($method),$method),
				'type'=>'error'));

			$CACHE[$this->index][$method] = null;

		} else {
			$this->noconnect = false;

			# If this is a proxy session, we need to switch to the proxy user
			if ($this->isProxyEnabled() && $bind['id'] && $method != 'anon')
				if (! $this->startProxy($resource,$method)) {
					$this->noconnect = true;
					$CACHE[$this->index][$method] = null;
				}
		}

		if (function_exists('run_hook'))
			run_hook('post_connect',array('server_id'=>$this->index,'method'=>$method,'id'=>$bind['id']));

		if ($debug)
			debug_dump(array($method=>$CACHE[$this->index][$method]));

		return $CACHE[$this->index][$method];
	}

	/**
	 * Login to the database with the application user/password
	 *
	 * @return boolean true|false for successful login.
	 */
	public function login($user=null,$pass=null,$method=null,$new=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$userDN = null;

		# Get the userDN from the username.
		if (! is_null($user)) {
			# If login,attr is set to DN, then user should be a DN
			if (($this->getValue('login','attr') == 'dn') || $method != 'user')
				$userDN = $user;
			else
				$userDN = $this->getLoginID($user,'login');

			if (! $userDN && $this->getValue('login','fallback_dn') && strpos($user, '='))
				$userDN = $user;

			if (! $userDN)
				return false;

		} else {
			if (in_array($method,array('user','anon'))) {
				$method = 'anon';
				$userDN = '';
				$pass = '';

			} else {
				$userDN = $this->getLogin('user');
				$pass = $this->getPassword('user');
			}
		}

		if (! $this->isAnonBindAllowed() && ! trim($userDN))
			return false;

		# Temporarily set our user details
		$this->setLogin($userDN,$pass,$method);

		$connect = $this->connect($method,false,$new);

		# If we didnt log in...
		if (! is_resource($connect) || $this->noconnect || ! $this->userIsAllowedLogin($userDN)) {
			$this->logout($method);

			return false;

		} else
			return true;
	}

	/**
	 * Perform a query to the Database
	 *
	 * @param string query to perform
	 *	$query['base']
	 *	$query['filter']
	 *	$query['scope']
	 *	$query['attrs'] = array();
	 *	$query['deref']
	 * @param string Which connection method resource to use
	 * @param string Index items according to this key
	 * @param boolean Enable debugging output
	 * @return array|null Results of query.
	 */
	public function query($query,$method,$index=null,$debug=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attrs_only = 0;

		# Defaults
		if (! isset($query['attrs']))
			$query['attrs'] = array();
		else
			# Re-index the attrs, PHP throws an error if the keys are not sequential from 0.
			$query['attrs'] = array_values($query['attrs']);

		if (! isset($query['base'])) {
			$bases = $this->getBaseDN();
			$query['base'] = array_shift($bases);
		}

		if (! isset($query['deref']))
			$query['deref'] = $_SESSION[APPCONFIG]->getValue('deref','search');
		if (! isset($query['filter']))
			$query['filter'] = '(&(objectClass=*))';
		if (! isset($query['scope']))
			$query['scope'] = 'sub';
		if (! isset($query['size_limit']))
			$query['size_limit'] = 0;
		if (! isset($query['time_limit']))
			$query['time_limit'] = 0;

		if ($query['scope'] == 'base' && ! isset($query['baseok']))
			system_message(array(
				'title'=>sprintf('Dont call %s',__METHOD__),
				'body'=>sprintf('Use getDNAttrValues for base queries [%s]',$query['base']),
				'type'=>'info'));

		if (is_array($query['base'])) {
			system_message(array(
				'title'=>_('Invalid BASE for query'),
				'body'=>_('The query was cancelled because of an invalid base.'),
				'type'=>'error'));

			return array();
		}

		if (DEBUG_ENABLED)
			debug_log('%s search PREPARE.',16,0,__FILE__,__LINE__,__METHOD__,$query['scope']);

		if ($debug)
			debug_dump(array('query'=>$query,'server'=>$this->getIndex(),'con'=>$this->connect($method)));

		$resource = $this->connect($method,$debug);

		switch ($query['scope']) {
			case 'base':
				$search = @ldap_read($resource,$query['base'],$query['filter'],$query['attrs'],$attrs_only,$query['size_limit'],$query['time_limit'],$query['deref']);
				break;

			case 'one':
				$search = @ldap_list($resource,$query['base'],$query['filter'],$query['attrs'],$attrs_only,$query['size_limit'],$query['time_limit'],$query['deref']);
				break;

			case 'sub':
			default:
				$search = @ldap_search($resource,$query['base'],$query['filter'],$query['attrs'],$attrs_only,$query['size_limit'],$query['time_limit'],$query['deref']);
				break;
		}

		if ($debug)
			debug_dump(array('method'=>$method,'search'=>$search,'error'=>$this->getErrorMessage()));

		if (DEBUG_ENABLED)
			debug_log('Search scope [%s] base [%s] filter [%s] attrs [%s] COMPLETE (%s).',16,0,__FILE__,__LINE__,__METHOD__,
				$query['scope'],$query['base'],$query['filter'],$query['attrs'],is_null($search));

		if (! $search)
			return array();

		$return = array();

		# Get the first entry identifier
		if ($entries = ldap_get_entries($resource,$search)) {
			# Remove the count
			if (isset($entries['count']))
				unset($entries['count']);

			# Iterate over the entries
			foreach ($entries as $a => $entry) {
				if (! isset($entry['dn']))
					debug_dump_backtrace('No DN?',1);

				# Remove the none entry references.
				if (! is_array($entry)) {
					unset($entries[$a]);
					continue;
				}

				$dn = $entry['dn'];
				unset($entry['dn']);

				# Iterate over the attributes
				foreach ($entry as $b => $attrs) {
					# Remove the none entry references.
					if (! is_array($attrs)) {
						unset($entry[$b]);
						continue;
					}

					# Remove the count
					if (isset($entry[$b]['count']))
						unset($entry[$b]['count']);
				}

				# Our queries always include the DN (the only value not an array).
				$entry['dn'] = $dn;
				$return[$dn] = $entry;
			}

			# Sort our results
			foreach ($return as $key=> $values)
				ksort($return[$key]);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Get the last error string
	 *
	 * @param string Which connection method resource to use
	 */
	public function getErrorMessage($method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return ldap_error($this->connect($method));
	}

	/**
	 * Get the last error number
	 *
	 * @param string Which connection method resource to use
	 */
	public function getErrorNum($method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return ldap_errno($this->connect($method));
	}

	/**
	 * Additional functions
	 */
	/**
	 * Get a user ID
	 *
	 * @param string Which connection method resource to use
	 */
	public function getLoginID($user,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$query['filter'] = sprintf('(&(%s=%s)%s)',
			$this->getValue('login','attr'),$user,
			$this->getLoginClass() ? sprintf('(objectclass=%s)',join(')(objectclass=',$this->getLoginClass())) : '');
		$query['attrs'] = array('dn');

		$result = array();
		foreach ($this->getLoginBaseDN() as $base) {
			$query['base'] = $base;
			$result = $this->query($query,$method);

			if (count($result) == 1)
				break;
		}

		if (count($result) != 1)
			return null;

		$detail = array_shift($result);

		if (! isset($detail['dn']))
			die('ERROR: DN missing?');
		else
			return $detail['dn'];
	}

	/**
	 * Return the login base DNs
	 * If no login base DNs are defined, then the LDAP server Base DNs are used.
	 */
	private function getLoginBaseDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,1,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->getValue('login','base'))
			return $this->getValue('login','base');
		else
			return $this->getBaseDN();
	}

	/**
	 * Return the login classes that a user must have to login
	 */
	private function getLoginClass() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,1,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->getValue('login','class');
	}

	/**
	 * Return if anonymous bind is allowed in the configuration
	 */
	public function isAnonBindAllowed() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->getValue('login','anon_bind');
	}

	/**
	 * Fetches whether TLS has been configured for use with a certain server.
	 *
	 * Users may configure phpLDAPadmin to use TLS in config,php thus:
	 * <code>
	 *	$servers->setValue('server','tls',true|false);
	 * </code>
	 *
	 * @return boolean
	 */
	private function isTLSEnabled() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->getValue('server','tls') && ! function_exists('ldap_start_tls')) {
				error(_('TLS has been enabled in your config, but your PHP install does not support TLS. TLS will be disabled.'),'warn');
			return false;

		} else
			return $this->getValue('server','tls');
	}

	/**
	 * If TLS is configured, then start it
	 */
	private function startTLS($resource) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! $this->getValue('server','tls') || (function_exists('ldap_start_tls') && ! @ldap_start_tls($resource))) {
			system_message(array(
				'title'=>sprintf('%s (%s)',_('Could not start TLS.'),$this->getName()),
				'body'=>sprintf('<b>%s</b>: %s',_('Error'),_('Could not start TLS. Please check your LDAP server configuration.')),
				'type'=>'error'));

			return false;

		} else
			return true;
	}

	/**
	 * Fetches whether SASL has been configured for use with a certain server.
	 *
	 * Users may configure phpLDAPadmin to use SASL in config,php thus:
	 * <code>
	 *	$servers->setValue('login','auth_type','sasl');
	 * </code>
	 *
	 * @return boolean
	 */
	private function isSASLEnabled() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->getValue('login','auth_type') != 'sasl')
			return false;

		if (! function_exists('ldap_sasl_bind')) {
			error(_('SASL has been enabled in your config, but your PHP install does not support SASL. SASL will be disabled.'),'warn');

			return false;
		}

		# If we get here, SASL must be configured.
		return true;
	}

	/**
	 * If SASL is configured, then start it
	 * To be able to use SASL, PHP should have been compliled with --with-ldap-sasl=DIR
	 *
	 * @todo This has not been tested, please let the developers know if this function works as expected.
	 */
	private function startSASL($resource,$method) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE = array();

		# We shouldnt be doing SASL binds for anonymous queries?
		if ($method == 'anon')
			return false;

		# At the moment, we have only implemented GSSAPI
		if (! in_array(strtolower($this->getValue('sasl','mech')),array('gssapi'))) {
			system_message(array(
				'title'=>_('SASL Method not implemented'),
				'body'=>sprintf('<b>%s</b>: %s %s',_('Error'),$this->getValue('sasl','mech'),_('has not been implemented yet')),
				'type'=>'error'));

			return false;
		}

		if (! isset($CACHE['login_dn']))
			$CACHE['login_dn'] = is_null($this->getLogin($method)) ? $this->getLogin('user') : $this->getLogin($method);

		$CACHE['authz_id'] = '';

		/*
		# Do we need to rewrite authz_id?
		if (! isset($CACHE['authz_id']))
			if (! trim($this->getValue('sasl','authz_id')) && strtolower($this->getValue('sasl','mech')) != 'gssapi') {
				if (DEBUG_ENABLED)
					debug_log('Rewriting bind DN [%s] -> authz_id with regex [%s] and replacement [%s].',9,0,__FILE__,__LINE__,__METHOD__,
						$CACHE['login_dn'],
						$this->getValue('sasl','authz_id_regex'),
						$this->getValue('sasl','authz_id_replacement'));

				$CACHE['authz_id'] = @preg_replace($this->getValue('sasl','authz_id_regex'),
					$this->getValue('sasl','authz_id_replacement'),$CACHE['login_dn']);

				# Invalid regex?
				if (is_null($CACHE['authz_id']))
					error(sprintf(_('It seems that sasl_authz_id_regex "%s" contains invalid PCRE regular expression. The error is "%s".'),
						$this->getValue('sasl','authz_id_regex'),(isset($php_errormsg) ? $php_errormsg : '')),
						'error','index.php');

				if (DEBUG_ENABLED)
					debug_log('Resource [%s], SASL OPTIONS: mech [%s], realm [%s], authz_id [%s], props [%s]',9,0,__FILE__,__LINE__,__METHOD__,
						$resource,
						$this->getValue('sasl','mech'),
						$this->getValue('sasl','realm'),
						$CACHE['authz_id'],
						$this->getValue('sasl','props'));

			} else
				$CACHE['authz_id'] = $this->getValue('sasl','authz_id');
		*/

		# @todo this function is different in PHP5.1 and PHP5.2
		return @ldap_sasl_bind($resource,NULL,'',
			$this->getValue('sasl','mech'),
			$this->getValue('sasl','realm'),
			$CACHE['authz_id'],
			$this->getValue('sasl','props'));
	}

	/**
	 * Fetches whether PROXY AUTH has been configured for use with a certain server.
	 *
	 * Users may configure phpLDAPadmin to use PROXY AUTH in config,php thus:
	 * <code>
	 *	$servers->setValue('login','auth_type','proxy');
	 * </code>
	 *
	 * @return boolean
	 */
	private function isProxyEnabled() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->getValue('login','auth_type') == 'proxy' ? true : false;
	}

	/**
	 * If PROXY AUTH is configured, then start it
	 */
	private function startProxy($resource,$method) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$rootdse = $this->getRootDSE();

		if (! (isset($rootdse['supportedcontrol']) && in_array('2.16.840.1.113730.3.4.18',$rootdse['supportedcontrol']))) {
			system_message(array(
				'title'=>sprintf('%s %s',_('Unable to start proxy connection'),$this->getName()),
				'body'=>sprintf('<b>%s</b>: %s',_('Error'),_('Your LDAP server doesnt seem to support this control')),
				'type'=>'error'));

			return false;
		}

		$filter = '(&';
		$dn = '';

		$missing = false;
		foreach ($this->getValue('proxy','attr') as $attr => $var) {
			if (! isset($_SERVER[$var])) {
				system_message(array(
					'title'=>sprintf('%s %s',_('Unable to start proxy connection'),$this->getName()),
					'body'=>sprintf('<b>%s</b>: %s (%s)',_('Error'),_('Attribute doesnt exist'),$var),
					'type'=>'error'));

				$missing = true;

			} else {
				if ($attr == 'dn') {
					$dn = $var;

					break;

				} else
					$filter .= sprintf('(%s=%s)',$attr,$_SERVER[$var]);
			}
		}

		if ($missing)
			return false;

		$filter .= ')';

		if (! $dn) {
			$query['filter'] = $filter;

			foreach ($this->getBaseDN() as $base) {
				$query['base'] = $base;

				if ($search = $this->query($query,$method))
					break;
			}

			if (count($search) != 1) {
				system_message(array(
					'title'=>sprintf('%s %s',_('Unable to start proxy connection'),$this->getName()),
					'body'=>sprintf('<b>%s</b>: %s (%s)',_('Error'),_('Search for DN returned the incorrect number of results'),count($search)),
					'type'=>'error'));

				return false;
			}

			$search = array_pop($search);
			$dn = $search['dn'];
		}

		$ctrl = array(
			'oid'=>'2.16.840.1.113730.3.4.18',
			'value'=>sprintf('dn:%s',$dn),
			'iscritical' => true);

		if (! ldap_set_option($resource,LDAP_OPT_SERVER_CONTROLS,array($ctrl))) {
			system_message(array(
				'title'=>sprintf('%s %s',_('Unable to start proxy connection'),$this->getName()),
				'body'=>sprintf('<b>%s</b>: %s (%s) for <b>%s</b>',_('Error'),$this->getErrorMessage($method),$this->getErrorNum($method),$method),
				'type'=>'error'));

			return false;
		}

		$_SESSION['USER'][$this->index][$method]['proxy'] = blowfish_encrypt($dn);

		return true;
	}

	/**
	 * Modify attributes of a DN
	 */
	public function modify($dn,$attrs,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# We need to supress the error here - programming should detect and report it.
		return @ldap_mod_replace($this->connect($method),$dn,$attrs);
	}

	/**
	 * Gets the root DN of the specified LDAPServer, or null if it
	 * can't find it (ie, the server won't give it to us, or it isnt
	 * specified in the configuration file).
	 *
	 * Tested with OpenLDAP 2.0, Netscape iPlanet, and Novell eDirectory 8.7 (nldap.com)
	 * Please report any and all bugs!!
	 *
	 * Please note: On FC systems, it seems that php_ldap uses /etc/openldap/ldap.conf in
	 * the search base if it is blank - so edit that file and comment out the BASE line.
	 *
	 * @param string Which connection method resource to use
	 * @return array dn|null The root DN of the server on success (string) or null on error.
	 * @todo Sort the entries, so that they are in the correct DN order.
	 */
	public function getBaseDN($method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE;

		$method = $this->getMethod($method);
		$result = array();

		if (isset($CACHE[$this->index][$method]))
			return $CACHE[$this->index][$method];

		# If the base is set in the configuration file, then just return that.
		if (count($this->getValue('server','base'))) {
			if (DEBUG_ENABLED)
				debug_log('Return BaseDN from Config [%s]',17,0,__FILE__,__LINE__,__METHOD__,implode('|',$this->getValue('server','base')));

			$CACHE[$this->index][$method] = $this->getValue('server','base');

		# We need to figure it out.
		} else {
			if (DEBUG_ENABLED)
				debug_log('Connect to LDAP to find BaseDN',80,0,__FILE__,__LINE__,__METHOD__);

			# Set this to empty, in case we loop back here looking for the baseDNs
			$CACHE[$this->index][$method] = array();

			$results = $this->getDNAttrValues('',$method);

			if (isset($results['namingcontexts'])) {
				if (DEBUG_ENABLED)
					debug_log('LDAP Entries:%s',80,0,__FILE__,__LINE__,__METHOD__,implode('|',$results['namingcontexts']));

				$result = $results['namingcontexts'];
			}

			$CACHE[$this->index][$method] = $result;
		}

		return $CACHE[$this->index][$method];
	}

	/**
	 * Gets whether an entry exists based on its DN. If the entry exists,
	 * returns true. Otherwise returns false.
	 *
	 * @param string The DN of the entry of interest.
	 * @param string Which connection method resource to use
	 * @return boolean
	 */
	public function dnExists($dn,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$results = $this->getDNAttrValues($dn,$method);

		if ($results)
			return $results;
		else
			return false;
	}

	/**
	 * Given a DN string, this returns the top container portion of the string.
	 *
	 * @param string The DN whose container string to return.
	 * @return string The container
	 */
	public function getContainerTop($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$return = $dn;

		foreach ($this->getBaseDN() as $base) {
			if (preg_match("/${base}$/i",$dn)) {
				$return = $base;
				break;
			}
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Given a DN string and a path like syntax, this returns the parent container portion of the string.
	 *
	 * @param string The DN whose container string to return.
	 * @param string Either '/', '.' or something like '../../<rdn>'
	 * @return string The container
	 */
	public function getContainerPath($dn,$path='..') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$top = $this->getContainerTop($dn);

		if ($path[0] == '/') {
			$dn = $top;
			$path = substr($path,1);

		} elseif ($path == '.') {
			return $dn;
		}

		$parenttree = explode('/',$path);

		foreach ($parenttree as $key => $value) {
			if ($value == '..') {
				if ($this->getContainer($dn))
					$dn = $this->getContainer($dn);

				if ($dn == $top)
					break;

			} elseif($value)
				$dn = sprintf('%s,%s',$value,$dn);

			else
				break;
		}

		if (! $dn) {
			debug_dump(array(__METHOD__,'dn'=>$dn,'path'=>$path));
			debug_dump_backtrace('Container is empty?',1);
		}

		return $dn;
	}

	/**
	 * Given a DN string, this returns the parent container portion of the string.
	 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
	 * 'dc=example,dc=com'.
	 *
	 * @param string The DN whose container string to return.
	 * @return string The container
	 */
	public function getContainer($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$parts = $this->explodeDN($dn);

		if (count($parts) <= 1)
			$return = null;

		else {
			$return = $parts[1];

			for ($i=2;$i<count($parts);$i++)
				$return .= sprintf(',%s',$parts[$i]);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Gets a list of child entries for an entry. Given a DN, this function fetches the list of DNs of
	 * child entries one level beneath the parent. For example, for the following tree:
	 *
	 * <code>
	 *	dc=example,dc=com
	 *		ou=People
	 *			cn=Dave
	 *			cn=Fred
	 *			cn=Joe
	 *		ou=More People
	 *			cn=Mark
	 *			cn=Bob
	 * </code>
	 *
	 * Calling <code>getContainerContents("ou=people,dc=example,dc=com")</code>
	 * would return the following list:
	 *
	 * <code>
	 *	cn=Dave
	 *	cn=Fred
	 *	cn=Joe
	 *	ou=More People
	 * </code>
	 *
	 * @param string The DN of the entry whose children to return.
	 * @param string Which connection method resource to use
	 * @param int (optional) The maximum number of entries to return.
	 *            If unspecified, no limit is applied to the number of entries in the returned.
	 * @param string (optional) An LDAP filter to apply when fetching children, example: "(objectClass=inetOrgPerson)"
	 * @param constant (optional) The LDAP deref setting to use in the query
	 * @return array An array of DN strings listing the immediate children of the specified entry.
	 */
	public function getContainerContents($dn,$method=null,$size_limit=0,$filter='(objectClass=*)',$deref=LDAP_DEREF_NEVER) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$return = array();

		$query = array();
		$query['base'] = $this->escapeDN($dn);
		$query['attrs'] = array('dn');
		$query['filter'] = $filter;
		$query['deref'] = $deref;
		$query['scope'] = 'one';
		$query['size_limit'] = $size_limit;
		$results = $this->query($query,$method);

		if ($results) {
			foreach ($results as $index => $entry) {
				$child_dn = $entry['dn'];
				array_push($return,$child_dn);
			}
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$return);

		# Sort the results
		asort($return);

		return $return;
	}

	/**
	 * Explode a DN into an array of its RDN parts.
	 *
	 * @param string The DN to explode.
	 * @param int (optional) Whether to include attribute names (see http://php.net/ldap_explode_dn for details)
	 *
	 * @return array An array of RDN parts of this format:
	 * <code>
	 *	Array
	 *		(
	 *			[0] => uid=ppratt
	 *			[1] => ou=People
	 *			[2] => dc=example
	 *			[3] => dc=com
	 *		)
	 * </code>
	 *
	 * NOTE: When a multivalue RDN is passed to ldap_explode_dn, the results returns with 'value + value';
	 */
	private function explodeDN($dn,$with_attributes=0) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE;

		if (isset($CACHE['explode'][$dn][$with_attributes])) {
			if (DEBUG_ENABLED)
				debug_log('Return CACHED result (%s) for (%s)',1,0,__FILE__,__LINE__,__METHOD__,
					$CACHE['explode'][$dn][$with_attributes],$dn);

			return $CACHE['explode'][$dn][$with_attributes];
		}

		$dn = addcslashes($dn,'<>+";');

		# split the dn
		$result[0] = ldap_explode_dn($this->escapeDN($dn),0);
		$result[1] = ldap_explode_dn($this->escapeDN($dn),1);
		if (! $result[$with_attributes]) {
			if (DEBUG_ENABLED)
				debug_log('Returning NULL - NO result.',1,0,__FILE__,__LINE__,__METHOD__);

			return array();
		}

		# Remove our count value that ldap_explode_dn returns us.
		unset($result[0]['count']);
		unset($result[1]['count']);

		# Record the forward and reverse entries in the cache.
		foreach ($result as $key => $value) {
			# translate hex code into ascii for display
			$result[$key] = $this->unescapeDN($value);

			$CACHE['explode'][implode(',',$result[0])][$key] = $result[$key];
			$CACHE['explode'][implode(',',array_reverse($result[0]))][$key] = array_reverse($result[$key]);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$result[$with_attributes]);

		return $result[$with_attributes];
	}

	/**
	 * Parse a DN and escape any special characters
	 */
	protected function escapeDN($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! trim($dn))
			return $dn;

		# Check if the RDN has a comma and escape it.
		while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/',$dn))
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,',$dn);

		$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3',$dn);

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$dn);

		return $dn;
	}

	/**
	 * Parse a DN and unescape any special characters
	 */
	private function unescapeDN($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (is_array($dn)) {
			$a = array();
			foreach ($dn as $key => $rdn) {
				$a[$key] = preg_replace_callback('/\\\([0-9A-Fa-f]{2})/', function($m) { return "''.chr(hexdec('${m[1]}')).''"; }, $rdn);
			}
			return $a;

		} else {
			return preg_replace_callback('/\\\([0-9A-Fa-f]{2})/', function($m) { return "''.chr(hexdec('${m[1]}')).''"; }, $dn);
		}
	}

	public function getRootDSE($method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$query = array();
		$query['base'] = '';
		$query['scope'] = 'base';
		$query['attrs'] = $this->getValue('server','root_dse_attributes');
		$query['baseok'] = true;
		$results = $this->query($query,$method);

		if (is_array($results) && count($results) == 1)
			return array_change_key_case(array_pop($results));
		else
			return array();
	}

	/** Schema Methods **/
	/**
	 * This function will query the ldap server and request the subSchemaSubEntry which should be the Schema DN.
	 *
	 * If we cant connect to the LDAP server, we'll return false.
	 * If we can connect but cant get the entry, then we'll return null.
	 *
	 * @param string Which connection method resource to use
	 * @param dn The DN to use to obtain the schema
	 * @return array|false Schema if available, null if its not or false if we cant connect.
	 */
	private function getSchemaDN($method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If we already got the SchemaDN, then return it.
		if ($this->_schemaDN)
			return $this->_schemaDN;

		if (! $this->connect($method))
			return false;

		$search = @ldap_read($this->connect($method),$dn,'objectclass=*',array('subschemaSubentry'),false,0,10,LDAP_DEREF_NEVER);

		if (DEBUG_ENABLED)
			debug_log('Search returned (%s)',24,0,__FILE__,__LINE__,__METHOD__,is_resource($search));

		# Fix for broken ldap.conf configuration.
		if (! $search && ! $dn) {
			if (DEBUG_ENABLED)
				debug_log('Trying to find the DN for "broken" ldap.conf',80,0,__FILE__,__LINE__,__METHOD__);

			if (isset($this->_baseDN)) {
				foreach ($this->_baseDN as $base) {
					$search = @ldap_read($this->connect($method),$base,'objectclass=*',array('subschemaSubentry'),false,0,10,LDAP_DEREF_NEVER);

					if (DEBUG_ENABLED)
						debug_log('Search returned (%s) for base (%s)',24,0,__FILE__,__LINE__,__METHOD__,
							is_resource($search),$base);

					if ($search)
						break;
				}
			}
		}

		if (! $search)
			return null;

		if (! @ldap_count_entries($this->connect($method),$search)) {
			if (DEBUG_ENABLED)
				debug_log('Search returned 0 entries. Returning NULL',25,0,__FILE__,__LINE__,__METHOD__);

			return null;
		}

		$entries = @ldap_get_entries($this->connect($method),$search);

		if (DEBUG_ENABLED)
			debug_log('Search returned [%s]',24,0,__FILE__,__LINE__,__METHOD__,$entries);

		if (! $entries || ! is_array($entries))
			return null;

		$entry = isset($entries[0]) ? $entries[0] : false;
		if (! $entry) {
			if (DEBUG_ENABLED)
				debug_log('Entry is false, Returning NULL',80,0,__FILE__,__LINE__,__METHOD__);

			return null;
		}

		$sub_schema_sub_entry = isset($entry[0]) ? $entry[0] : false;
		if (! $sub_schema_sub_entry) {
			if (DEBUG_ENABLED)
				debug_log('Sub Entry is false, Returning NULL',80,0,__FILE__,__LINE__,__METHOD__);

			return null;
		}

		$this->_schemaDN = isset($entry[$sub_schema_sub_entry][0]) ? $entry[$sub_schema_sub_entry][0] : false;

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$this->_schemaDN);

		return $this->_schemaDN;
	}

	/**
	 * Fetches the raw schema array for the subschemaSubentry of the server. Note,
	 * this function has grown many hairs to accomodate more LDAP servers. It is
	 * needfully complicated as it now supports many popular LDAP servers that
	 * don't necessarily expose their schema "the right way".
	 *
	 * Please note: On FC systems, it seems that php_ldap uses /etc/openldap/ldap.conf in
	 * the search base if it is blank - so edit that file and comment out the BASE line.
	 *
	 * @param string Which connection method resource to use
	 * @param string A string indicating which type of schema to
	 *		fetch. Five valid values: 'objectclasses', 'attributetypes',
	 *		'ldapsyntaxes', 'matchingruleuse', or 'matchingrules'.
	 *		Case insensitive.
	 * @param dn (optional) This paremeter is the DN of the entry whose schema you
	 * 		would like to fetch. Entries have the option of specifying
	 * 		their own subschemaSubentry that points to the DN of the system
	 * 		schema entry which applies to this attribute. If unspecified,
	 *		this will try to retrieve the schema from the RootDSE subschemaSubentry.
	 *		Failing that, we use some commonly known schema DNs. Default
	 *		value is the Root DSE DN (zero-length string)
	 * @return array an array of strings of this form:
	 *	Array (
	 *		[0] => "(1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
	 *		[1] => "(1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
	 *	etc.
	 */
	private function getRawSchema($method,$schema_to_fetch,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$valid_schema_to_fetch = array('objectclasses','attributetypes','ldapsyntaxes','matchingrules','matchingruleuse');

		if (! $this->connect($method) || $this->noconnect)
			return false;

		# error checking
		$schema_to_fetch = strtolower($schema_to_fetch);

		if (! is_null($this->_schema_entries) && isset($this->_schema_entries[$schema_to_fetch])) {
			$schema = $this->_schema_entries[$schema_to_fetch];

			if (DEBUG_ENABLED)
				debug_log('Returning CACHED (%s)',25,0,__FILE__,__LINE__,__METHOD__,$schema);

			return $schema;
		}

		# This error message is not localized as only developers should ever see it
		if (! in_array($schema_to_fetch,$valid_schema_to_fetch))
			error(sprintf('Bad parameter provided to function to %s::getRawSchema(). "%s" is not valid for the schema_to_fetch parameter.',
					get_class($this),$schema_to_fetch),'error','index.php');

		# Try to get the schema DN from the specified entry.
		$schema_dn = $this->getSchemaDN($method,$dn);

		# Do we need to try again with the Root DSE?
		if (! $schema_dn && trim($dn))
			$schema_dn = $this->getSchemaDN($method,'');

		# Store the eventual schema retrieval in $schema_search
		$schema_search = null;

		if ($schema_dn) {
			if (DEBUG_ENABLED)
				debug_log('Using Schema DN (%s)',24,0,__FILE__,__LINE__,__METHOD__,$schema_dn);

			foreach (array('(objectClass=*)','(objectClass=subschema)') as $schema_filter) {
				if (DEBUG_ENABLED)
					debug_log('Looking for schema with Filter (%s)',24,0,__FILE__,__LINE__,__METHOD__,$schema_filter);

				$schema_search = @ldap_read($this->connect($method),$schema_dn,$schema_filter,array($schema_to_fetch),false,0,10,LDAP_DEREF_NEVER);

				if (is_null($schema_search))
					continue;

				$schema_entries = @ldap_get_entries($this->connect($method),$schema_search);

				if (DEBUG_ENABLED)
					debug_log('Search returned [%s]',24,0,__FILE__,__LINE__,__METHOD__,$schema_entries);

				if (is_array($schema_entries) && isset($schema_entries['count']) && $schema_entries['count']) {
					if (DEBUG_ENABLED)
						debug_log('Found schema with (DN:%s) (FILTER:%s) (ATTR:%s)',24,0,__FILE__,__LINE__,__METHOD__,
							$schema_dn,$schema_filter,$schema_to_fetch);

					break;
				}

				if (DEBUG_ENABLED)
					debug_log('Didnt find schema with filter (%s)',24,0,__FILE__,__LINE__,__METHOD__,$schema_filter);

				unset($schema_entries);
				$schema_search = null;
			}
		}

		/* Second chance: If the DN or Root DSE didn't give us the subschemaSubentry, ie $schema_search
		 * is still null, use some common subSchemaSubentry DNs as a work-around. */
		if (is_null($schema_search)) {
			if (DEBUG_ENABLED)
				debug_log('Attempting work-arounds for "broken" LDAP servers...',24,0,__FILE__,__LINE__,__METHOD__);

			foreach ($this->getBaseDN() as $base) {
				$ldap['W2K3 AD'][expand_dn_with_base($base,'cn=Aggregate,cn=Schema,cn=configuration,')] = '(objectClass=*)';
				$ldap['W2K AD'][expand_dn_with_base($base,'cn=Schema,cn=configuration,')] = '(objectClass=*)';
				$ldap['W2K AD'][expand_dn_with_base($base,'cn=Schema,ou=Admin,')] = '(objectClass=*)';
			}

			# OpenLDAP and Novell
			$ldap['OpenLDAP']['cn=subschema'] = '(objectClass=*)';

			foreach ($ldap as $ldap_server_name => $ldap_options) {
				foreach ($ldap_options as $ldap_dn => $ldap_filter) {
					if (DEBUG_ENABLED)
						debug_log('Attempting [%s] (%s) (%s)<BR>',24,0,__FILE__,__LINE__,__METHOD__,
							$ldap_server_name,$ldap_dn,$ldap_filter);

					$schema_search = @ldap_read($this->connect($method),$ldap_dn,$ldap_filter,array($schema_to_fetch),false,0,10,LDAP_DEREF_NEVER);
					if (is_null($schema_search))
						continue;

					$schema_entries = @ldap_get_entries($this->connect($method),$schema_search);

					if (DEBUG_ENABLED)
						debug_log('Search returned [%s]',24,0,__FILE__,__LINE__,__METHOD__,$schema_entries);

					if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
						if (DEBUG_ENABLED)
							debug_log('Found schema with filter of (%s)',24,0,__FILE__,__LINE__,__METHOD__,$ldap_filter);

						break;
					}

					if (DEBUG_ENABLED)
						debug_log('Didnt find schema with filter (%s)',24,0,__FILE__,__LINE__,__METHOD__,$ldap_filter);

					unset($schema_entries);
					$schema_search = null;
				}
				if ($schema_search)
					break;
			}
		}

		# Option 3: try cn=config
		$olc_schema = 'olc'.$schema_to_fetch;
		$olc_schema_found = false;
		if (is_null($schema_search)) {
			if (DEBUG_ENABLED)
				debug_log('Attempting cn=config work-around...',24,0,__FILE__,__LINE__,__METHOD__);

			$ldap_dn = 'cn=schema,cn=config';
			$ldap_filter = '(objectClass=*)';

			$schema_search = @ldap_search($this->connect($method),$ldap_dn,$ldap_filter,array($olc_schema),false,0,10,LDAP_DEREF_NEVER);

			if (! is_null($schema_search)) {
				$schema_entries = @ldap_get_entries($this->connect($method),$schema_search);

				if (DEBUG_ENABLED)
					debug_log('Search returned [%s]',24,0,__FILE__,__LINE__,__METHOD__,$schema_entries);

				if ($schema_entries) {
					if (DEBUG_ENABLED)
						debug_log('Found schema with filter of (%s) and attribute filter (%s)',24,0,__FILE__,__LINE__,__METHOD__,$ldap_filter,$olc_schema);

					$olc_schema_found = true;

				} else {
					if (DEBUG_ENABLED)
						debug_log('Didnt find schema with filter (%s) and attribute filter (%s)',24,0,__FILE__,__LINE__,__METHOD__,$ldap_filter,$olc_schema);

					unset($schema_entries);
					$schema_search = null;
				}
			}
		}

		if (is_null($schema_search)) {
			/* Still cant find the schema, try with the RootDSE
			 * Attempt to pull schema from Root DSE with scope "base", or
			 * Attempt to pull schema from Root DSE with scope "one" (work-around for Isode M-Vault X.500/LDAP) */
			foreach (array('base','one') as $ldap_scope) {
				if (DEBUG_ENABLED)
					debug_log('Attempting to find schema with scope (%s), filter (objectClass=*) and a blank base.',24,0,__FILE__,__LINE__,__METHOD__,
						$ldap_scope);

				switch ($ldap_scope) {
					case 'base':
						$schema_search = @ldap_read($this->connect($method),'','(objectClass=*)',array($schema_to_fetch),false,0,10,LDAP_DEREF_NEVER);
						break;

					case 'one':
						$schema_search = @ldap_list($this->connect($method),'','(objectClass=*)',array($schema_to_fetch),false,0,10,LDAP_DEREF_NEVER);
						break;
				}

				if (is_null($schema_search))
					continue;

				$schema_entries = @ldap_get_entries($this->connect($method),$schema_search);
				if (DEBUG_ENABLED)
					debug_log('Search returned [%s]',24,0,__FILE__,__LINE__,__METHOD__,$schema_entries);

				if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
					if (DEBUG_ENABLED)
						debug_log('Found schema with filter of (%s)',24,0,__FILE__,__LINE__,__METHOD__,'(objectClass=*)');

					break;
				}

				if (DEBUG_ENABLED)
					debug_log('Didnt find schema with filter (%s)',24,0,__FILE__,__LINE__,__METHOD__,'(objectClass=*)');

				unset($schema_entries);
				$schema_search = null;
			}
		}

		$schema_error_message = 'Please contact the phpLDAPadmin developers and let them know:<ul><li>Which LDAP server you are running, including which version<li>What OS it is running on<li>Which version of PHP<li>As well as a link to some documentation that describes how to obtain the SCHEMA information</ul><br />We\'ll then add support for your LDAP server in an upcoming release.';
		$schema_error_message_array = array('objectclasses','attributetypes');

		# Shall we just give up?
		if (is_null($schema_search)) {
			# We need to have objectclasses and attribues, so display an error, asking the user to get us this information.
			if (in_array($schema_to_fetch,$schema_error_message_array))
				system_message(array(
					'title'=>sprintf('%s (%s)',_('Our attempts to find your SCHEMA have failed'),$schema_to_fetch),
					'body'=>sprintf('<b>%s</b>: %s',_('Error'),$schema_error_message),
					'type'=>'error'));
			else
				if (DEBUG_ENABLED)
					debug_log('Returning because schema_search is NULL ()',25,0,__FILE__,__LINE__,__METHOD__);

			# We'll set this, so if we return here our cache will return the known false.
			$this->_schema_entries[$schema_to_fetch] = false;
			return false;
		}

		if (! $schema_entries) {
			$return = false;
			if (DEBUG_ENABLED)
				debug_log('Returning false since ldap_get_entries() returned false.',25,0,__FILE__,__LINE__,__METHOD__,$return);

			return $return;
		}

		if ($olc_schema_found) {
			unset ($schema_entries['count']);

			foreach ($schema_entries as $entry) {
				if (isset($entry[$olc_schema])) {
					unset($entry[$olc_schema]['count']);

					foreach ($entry[$olc_schema] as $schema_definition)
						/* Schema definitions in child nodes prefix the schema entries with "{n}"
						  the preg_replace call strips out this prefix. */
						$schema[] = preg_replace('/^\{\d*\}\(/','(',$schema_definition);
				}
			}

			if (isset($schema)) {
				$this->_schema_entries[$olc_schema] = $schema;

				if (DEBUG_ENABLED)
					debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$schema);

				return $schema;

			} else
				return null;
		}

		if (! isset($schema_entries[0][$schema_to_fetch])) {
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				error(sprintf('Our attempts to find your SCHEMA for "%s" have return UNEXPECTED results.<br /><br /><small>(We expected a "%s" in the $schema array but it wasnt there.)</small><br /><br />%s<br /><br />Dump of $schema_search:<hr /><pre><small>%s</small></pre>',
					$schema_to_fetch,gettype($schema_search),$schema_error_message,serialize($schema_entries)),'error','index.php');

			} else {
				$return = false;

				if (DEBUG_ENABLED)
					debug_log('Returning because (%s) isnt in the schema array. (%s)',25,0,__FILE__,__LINE__,__METHOD__,$schema_to_fetch,$return);

				return $return;
			}
		}

		/* Make a nice array of this form:
			Array (
				[0] => "(1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...)"
				[1] => "(1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...)"
			etc.) */

		$schema = $schema_entries[0][$schema_to_fetch];
		unset($schema['count']);
		$this->_schema_entries[$schema_to_fetch] = $schema;

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$schema);

		return $schema;
	}

	/**
	 * Gets a single ObjectClass object specified by name.
	 *
	 * @param string $oclass_name The name of the objectClass to fetch.
	 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
	 *               which defines the subschemaSubEntry attribute (all entries should).
	 *
	 * @return ObjectClass The specified ObjectClass object or false on error.
	 *
	 * @see ObjectClass
	 * @see SchemaObjectClasses
	 */
	public function getSchemaObjectClass($oclass_name,$method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$oclass_name = strtolower($oclass_name);
		$socs = $this->SchemaObjectClasses($method,$dn);

		# Default return value
		$return = false;

		if (isset($socs[$oclass_name]))
			$return = $socs[$oclass_name];

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Gets a single AttributeType object specified by name.
	 *
	 * @param string $oclass_name The name of the AttributeType to fetch.
	 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
	 *             which defines the subschemaSubEntry attribute (all entries should).
	 *
	 * @return AttributeType The specified AttributeType object or false on error.
	 *
	 * @see AttributeType
	 * @see SchemaAttributes
	 */
	public function getSchemaAttribute($attr_name,$method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attr_name = strtolower($attr_name);
		$sattrs = $this->SchemaAttributes($method,$dn);

		# Default return value
		$return = false;

		if (isset($sattrs[$attr_name]))
			$return = $sattrs[$attr_name];

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Gets an associative array of ObjectClass objects for the specified
	 * server. Each array entry's key is the name of the objectClass
	 * in lower-case and the value is an ObjectClass object.
	 *
	 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
	 *               which defines the subschemaSubEntry attribute (all entries should).
	 *
	 * @return array An array of ObjectClass objects.
	 *
	 * @see ObjectClass
	 * @see getSchemaObjectClass
	 */
	public function SchemaObjectClasses($method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Set default return
		$return = null;

		if ($return = get_cached_item($this->index,'schema','objectclasses')) {
			if (DEBUG_ENABLED)
				debug_log('Returning CACHED [%s] (%s)',25,0,__FILE__,__LINE__,__METHOD__,$this->index,'objectclasses');

			return $return;
		}

		$raw = $this->getRawSchema($method,'objectclasses',$dn);

		if ($raw) {
			# Build the array of objectClasses
			$return = array();

			foreach ($raw as $line) {
				if (is_null($line) || ! strlen($line))
					continue;

				$object_class = new ObjectClass($line,$this);
				$return[$object_class->getName()] = $object_class;
			}

			# Now go through and reference the parent/child relationships
			foreach ($return as $oclass)
				foreach ($oclass->getSupClasses() as $parent_name)
					if (isset($return[strtolower($parent_name)]))
						$return[strtolower($parent_name)]->addChildObjectClass($oclass->getName(false));

			ksort($return);

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->index,'schema','objectclasses',$return);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Gets an associative array of AttributeType objects for the specified
	 * server. Each array entry's key is the name of the attributeType
	 * in lower-case and the value is an AttributeType object.
	 *
	 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
	 *             which defines the subschemaSubEntry attribute (all entries should).
	 *
	 * @return array An array of AttributeType objects.
	 */
	public function SchemaAttributes($method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Set default return
		$return = null;

		if ($return = get_cached_item($this->index,'schema','attributes')) {
			if (DEBUG_ENABLED)
				debug_log('(): Returning CACHED [%s] (%s)',25,0,__FILE__,__LINE__,__METHOD__,$this->index,'attributes');

			return $return;
		}

		$raw = $this->getRawSchema($method,'attributeTypes',$dn);

		if ($raw) {
			# build the array of attribueTypes
			$syntaxes = $this->SchemaSyntaxes($method,$dn);
			$attrs = array();

			/**
			 * bug 856832: create two arrays - one indexed by name (the standard
			 * $attrs array above) and one indexed by oid (the new $attrs_oid array
			 * below). This will help for directory servers, like IBM's, that use OIDs
			 * in their attribute definitions of SUP, etc
			 */
			$attrs_oid = array();
			foreach ($raw as $line) {
				if (is_null($line) || ! strlen($line))
					continue;

				$attr = new AttributeType($line);
				if (isset($syntaxes[$attr->getSyntaxOID()])) {
					$syntax = $syntaxes[$attr->getSyntaxOID()];
					$attr->setType($syntax->getDescription());
				}
				$attrs[$attr->getName()] = $attr;

				/**
				 * bug 856832: create an entry in the $attrs_oid array too. This
				 * will be a ref to the $attrs entry for maintenance and performance
				 * reasons
				 */
				$attrs_oid[$attr->getOID()] = &$attrs[$attr->getName()];
			}

			# go back and add data from aliased attributeTypes
			foreach ($attrs as $name => $attr) {
				$aliases = $attr->getAliases();

				if (is_array($aliases) && count($aliases) > 0) {
					/* foreach of the attribute's aliases, create a new entry in the attrs array
					 * with its name set to the alias name, and all other data copied.*/
					foreach ($aliases as $alias_attr_name) {
						$new_attr = clone $attr;

						$new_attr->setName($alias_attr_name);
						$new_attr->addAlias($attr->getName(false));
						$new_attr->removeAlias($alias_attr_name);
						$new_attr_key = strtolower($alias_attr_name);
						$attrs[$new_attr_key] = $new_attr;
					}
				}
			}

			# go back and add any inherited descriptions from parent attributes (ie, cn inherits name)
			foreach ($attrs as $key => $attr) {
				$sup_attr_name = $attr->getSupAttribute();
				$sup_attr = null;

				if (trim($sup_attr_name)) {

					/* This loop really should traverse infinite levels of inheritance (SUP) for attributeTypes,
					 * but just in case we get carried away, stop at 100. This shouldn't happen, but for
					 * some weird reason, we have had someone report that it has happened. Oh well.*/
					$i = 0;
					while ($i++<100 /** 100 == INFINITY ;) */) {

						if (isset($attrs_oid[$sup_attr_name])) {
							$attr->setSupAttribute($attrs_oid[$sup_attr_name]->getName());
							$sup_attr_name = $attr->getSupAttribute();
						}

						if (! isset($attrs[strtolower($sup_attr_name)])){
							error(sprintf('Schema error: attributeType "%s" inherits from "%s", but attributeType "%s" does not exist.',
								$attr->getName(),$sup_attr_name,$sup_attr_name),'error','index.php');
							return;
						}

						$sup_attr = $attrs[strtolower($sup_attr_name)];
						$sup_attr_name = $sup_attr->getSupAttribute();

						# Does this superior attributeType not have a superior attributeType?
						if (is_null($sup_attr_name) || strlen(trim($sup_attr_name)) == 0) {

							/* Since this attribute's superior attribute does not have another superior
							 * attribute, clone its properties for this attribute. Then, replace
							 * those cloned values with those that can be explicitly set by the child
							 * attribute attr). Save those few properties which the child can set here:*/
							$tmp_name = $attr->getName(false);
							$tmp_oid = $attr->getOID();
							$tmp_sup = $attr->getSupAttribute();
							$tmp_aliases = $attr->getAliases();
							$tmp_single_val = $attr->getIsSingleValue();
							$tmp_desc = $attr->getDescription();

							/* clone the SUP attributeType and populate those values
							 * that were set by the child attributeType */
							$attr = clone $sup_attr;

							$attr->setOID($tmp_oid);
							$attr->setName($tmp_name);
							$attr->setSupAttribute($tmp_sup);
							$attr->setAliases($tmp_aliases);
							$attr->setDescription($tmp_desc);

							/* only overwrite the SINGLE-VALUE property if the child explicitly sets it
							 * (note: All LDAP attributes default to multi-value if not explicitly set SINGLE-VALUE) */
							if ($tmp_single_val)
								$attr->setIsSingleValue(true);

							/* replace this attribute in the attrs array now that we have populated
								 new values therein */
							$attrs[$key] = $attr;

							# very important: break out after we are done with this attribute
							$sup_attr_name = null;
							$sup_attr = null;
							break;
						}
					}
				}
			}

			ksort($attrs);

			# Add the used in and required_by values.
			$socs = $this->SchemaObjectClasses($method);
			if (! is_array($socs))
				return array();

			foreach ($socs as $object_class) {
				$must_attrs = $object_class->getMustAttrNames();
				$may_attrs = $object_class->getMayAttrNames();
				$oclass_attrs = array_unique(array_merge($must_attrs,$may_attrs));

				# Add Used In.
				foreach ($oclass_attrs as $attr_name)
					if (isset($attrs[strtolower($attr_name)]))
						$attrs[strtolower($attr_name)]->addUsedInObjectClass($object_class->getName(false));

				# Add Required By.
				foreach ($must_attrs as $attr_name)
					if (isset($attrs[strtolower($attr_name)]))
						$attrs[strtolower($attr_name)]->addRequiredByObjectClass($object_class->getName(false));

				# Force May
				foreach ($object_class->getForceMayAttrs() as $attr_name)
					if (isset($attrs[strtolower($attr_name->name)]))
						$attrs[strtolower($attr_name->name)]->setForceMay();
			}

			$return = $attrs;

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->index,'schema','attributes',$return);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Returns an array of MatchingRule objects for the specified server.
	 * The key of each entry is the OID of the matching rule.
	 */
	public function MatchingRules($method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Set default return
		$return = null;

		if ($return = get_cached_item($this->index,'schema','matchingrules')) {
			if (DEBUG_ENABLED)
				debug_log('Returning CACHED [%s] (%s).',25,0,__FILE__,__LINE__,__METHOD__,$this->index,'matchingrules');

			return $return;
		}

		# build the array of MatchingRule objects
		$raw = $this->getRawSchema($method,'matchingRules',$dn);

		if ($raw) {
			$rules = array();

			foreach ($raw as $line) {
				if (is_null($line) || ! strlen($line))
					continue;

				$rule = new MatchingRule($line);
				$key = $rule->getName();
				$rules[$key] = $rule;
			}

			ksort($rules);

			/* For each MatchingRuleUse entry, add the attributes who use it to the
			 * MatchingRule in the $rules array.*/
			$raw = $this->getRawSchema($method,'matchingRuleUse');

			if ($raw != false) {
				foreach ($raw as $line) {
					if (is_null($line) || ! strlen($line))
						continue;

					$rule_use = new MatchingRuleUse($line);
					$key = $rule_use->getName();

					if (isset($rules[$key]))
						$rules[$key]->setUsedByAttrs($rule_use->getUsedByAttrs());
				}

			} else {
				/* No MatchingRuleUse entry in the subschema, so brute-forcing
				 * the reverse-map for the "$rule->getUsedByAttrs()" data.*/
				$sattrs = $this->SchemaAttributes($method,$dn);
				if (is_array($sattrs))
					foreach ($sattrs as $attr) {
						$rule_key = strtolower($attr->getEquality());

						if (isset($rules[$rule_key]))
							$rules[$rule_key]->addUsedByAttr($attr->getName(false));
					}
			}

			$return = $rules;

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->index,'schema','matchingrules',$return);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Returns an array of Syntax objects that this LDAP server uses mapped to
	 * their descriptions. The key of each entry is the OID of the Syntax.
	 */
	public function SchemaSyntaxes($method=null,$dn='') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',25,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Set default return
		$return = null;

		if ($return = get_cached_item($this->index,'schema','syntaxes')) {
			if (DEBUG_ENABLED)
				debug_log('Returning CACHED [%s] (%s).',25,0,__FILE__,__LINE__,__METHOD__,$this->index,'syntaxes');

			return $return;
		}

		$raw = $this->getRawSchema($method,'ldapSyntaxes',$dn);

		if ($raw) {
			# build the array of attributes
			$return = array();

			foreach ($raw as $line) {
				if (is_null($line) || ! strlen($line))
					continue;

				$syntax = new Syntax($line);
				$key = strtolower(trim($syntax->getOID()));

				if (! $key)
					continue;

				$return[$key] = $syntax;
			}

			ksort($return);

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->index,'schema','syntaxes',$return);
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',25,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * This function determines if the specified attribute is contained in the force_may list
	 * as configured in config.php.
	 *
	 * @return boolean True if the specified attribute is configured to be force as a may attribute
	 */
	function isForceMay($attr_name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return in_array($attr_name,unserialize(strtolower(serialize($this->getValue('server','force_may')))));
	}

	/**
	 * Much like getDNAttrValues(), but only returns the values for
	 * one attribute of an object. Example calls:
	 *
	 * <code>
	 *	print_r(getDNAttrValue('cn=Bob,ou=people,dc=example,dc=com','sn'));
	 *	Array (
	 *		[0] => Smith
	 *	)
	 *
	 * print_r(getDNAttrValue('cn=Bob,ou=people,dc=example,dc=com','objectClass'));
	 *	Array (
	 *		[0] => top
	 *		[1] => person
	 *	)
	 * </code>
	 *
	 * @param string The distinguished name (DN) of the entry whose attributes/values to fetch.
	 * @param string The attribute whose value(s) to return (ie, "objectClass", "cn", "userPassword")
	 * @param string Which connection method resource to use
	 * @param constant For aliases and referrals, this parameter specifies whether to
	 *            follow references to the referenced DN or to fetch the attributes for
	 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
	 *            options.
	 * @return array
	 * @see getDNAttrValues
	 * @todo Caching these values may be problematic with multiple calls and different deref values.
	 */
	public function getDNAttrValue($dn,$attr,$method=null,$deref=LDAP_DEREF_NEVER) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Ensure our attr is in lowercase
		$attr = strtolower($attr);

		$values = $this->getDNAttrValues($dn,$method,$deref);

		if (isset($values[$attr]))
			return $values[$attr];
		else
			return array();
	}

	/**
	 * Gets the attributes/values of an entry. Returns an associative array whose
	 * keys are attribute value names and whose values are arrays of values for
	 * said attribute.
	 *
	 * Optionally, callers may specify true for the parameter
	 * $lower_case_attr_names to force all keys in the associate array (attribute
	 * names) to be lower case.
	 *
	 * Example of its usage:
	 * <code>
	 * print_r(getDNAttrValues('cn=Bob,ou=pepole,dc=example,dc=com')
	 *	Array (
	 *		[objectClass] => Array (
	 *			[0] => person
	 *			[1] => top
	 *		)
	 *		[cn] => Array (
	 *			[0] => Bob
	 *		)
	 *		[sn] => Array (
	 *			[0] => Jones
	 *		)
	 *		[dn] => Array (
	 *			[0] => cn=Bob,ou=pepole,dc=example,dc=com
	 *		)
	 *	)
	 * </code>
	 *
	 * @param string The distinguished name (DN) of the entry whose attributes/values to fetch.
	 * @param string Which connection method resource to use
	 * @param constant For aliases and referrals, this parameter specifies whether to
	 *            follow references to the referenced DN or to fetch the attributes for
	 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
	 *            options.
	 * @return array
	 * @see getDNSysAttrs
	 * @see getDNAttrValue
	 */
	public function getDNAttrValues($dn,$method=null,$deref=LDAP_DEREF_NEVER,$attrs=array('*','+'),$nocache=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $CACHE;

		$cacheindex = null;
		$method = $this->getMethod($method);

		if (in_array('*',$attrs) && in_array('+',$attrs))
			$cacheindex = '&';
		elseif (in_array('+',$attrs))
			$cacheindex = '+';
		elseif (in_array('*',$attrs))
			$cacheindex = '*';

		if (! $nocache && ! is_null($cacheindex) && isset($CACHE[$this->index][$method][$dn][$cacheindex])) {
			$results = $CACHE[$this->index][$method][$dn][$cacheindex];

			if (DEBUG_ENABLED)
				debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$results);

		} else {
			$query = array();
			$query['base'] = $this->escapeDN($dn);
			$query['scope'] = 'base';
			$query['deref'] = $deref;
			$query['attrs'] = $attrs;
			$query['baseok'] = true;
			$results = $this->query($query,$method);

			if (count($results))
				$results = array_pop($results);

			$results = array_change_key_case($results);

			# Covert all our result key values to an array
			foreach ($results as $key => $values)
				if (! is_array($results[$key]))
					$results[$key] = array($results[$key]);

			# Finally sort the results
			ksort($results);

			if (! is_null($cacheindex) && count($results))
				$CACHE[$this->index][$method][$dn][$cacheindex] = $results;
		}

		return $results;
	}

	/**
	 * Returns true if the attribute specified is required to take as input a DN.
	 * Some examples include 'distinguishedName', 'member' and 'uniqueMember'.
	 *
	 * @param string $attr_name The name of the attribute of interest (case insensitive)
	 * @return boolean
	 */
	function isDNAttr($attr_name,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Simple test first
		$dn_attrs = array('aliasedObjectName');
		foreach ($dn_attrs as $dn_attr)
			if (strcasecmp($attr_name,$dn_attr) == 0)
				return true;

		# Now look at the schema OID
		$sattr = $this->getSchemaAttribute($attr_name);
		if (! $sattr)
			return false;

		$syntax_oid = $sattr->getSyntaxOID();
		if ('1.3.6.1.4.1.1466.115.121.1.12' == $syntax_oid)
		 	return true;
		if ('1.3.6.1.4.1.1466.115.121.1.34' == $syntax_oid)
			return true;

		$syntaxes = $this->SchemaSyntaxes($method);
		if (! isset($syntaxes[$syntax_oid]))
			return false;

		$syntax_desc = $syntaxes[ $syntax_oid ]->getDescription();
		if (strpos(strtolower($syntax_desc),'distinguished name'))
			return true;

		return false;
	}

	/**
	 * Used to determine if the specified attribute is indeed a jpegPhoto. If the
	 * specified attribute is one that houses jpeg data, true is returned. Otherwise
	 * this function returns false.
	 *
	 * @param string $attr_name The name of the attribute to test.
	 * @return boolean
	 * @see draw_jpeg_photo
	 */
	function isJpegPhoto($attr_name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# easy quick check
		if (! strcasecmp($attr_name,'jpegPhoto') || ! strcasecmp($attr_name,'photo'))
			return true;

		# go to the schema and get the Syntax OID
		$sattr = $this->getSchemaAttribute($attr_name);
		if (! $sattr)
			return false;

		$oid = $sattr->getSyntaxOID();
		$type = $sattr->getType();

		if (! strcasecmp($type,'JPEG') || ($oid == '1.3.6.1.4.1.1466.115.121.1.28'))
			return true;

		return false;
	}

	/**
	 * Given an attribute name and server ID number, this function returns
	 * whether the attrbiute contains boolean data. This is useful for
	 * developers who wish to display the contents of a boolean attribute
	 * with a drop-down.
	 *
	 * @param string $attr_name The name of the attribute to test.
	 * @return boolean
	 */
	function isAttrBoolean($attr_name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$type = ($sattr = $this->getSchemaAttribute($attr_name)) ? $sattr->getType() : null;

		if (! strcasecmp('boolean',$type) ||
			! strcasecmp('isCriticalSystemObject',$attr_name) ||
			! strcasecmp('showInAdvancedViewOnly',$attr_name))
			return true;

		else
			return false;
	}

	/**
	 * Given an attribute name and server ID number, this function returns
	 * whether the attribute may contain binary data. This is useful for
	 * developers who wish to display the contents of an arbitrary attribute
	 * but don't want to dump binary data on the page.
	 *
	 * @param string $attr_name The name of the attribute to test.
	 * @return boolean
	 *
	 * @see isJpegPhoto
	 */
	function isAttrBinary($attr_name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		/**
		 * Determining if an attribute is binary can be an expensive operation.
		 * We cache the results for each attr name on each server in the $attr_cache
		 * to speed up subsequent calls. The $attr_cache looks like this:
		 *
		 * Array
		 * 0 => Array
		 *	'objectclass' => false
		 *	'cn' => false
		 *	'usercertificate' => true
		 * 1 => Array
		 *	'jpegphoto' => true
		 *	'cn' => false
		 */

		static $attr_cache;

		$attr_name = strtolower($attr_name);

		if (isset($attr_cache[$this->index][$attr_name]))
			return $attr_cache[$this->index][$attr_name];

		if ($attr_name == 'userpassword') {
			$attr_cache[$this->index][$attr_name] = false;
			return false;
		}

		# Quick check: If the attr name ends in ";binary", then it's binary.
		if (strcasecmp(substr($attr_name,strlen($attr_name) - 7),';binary') == 0) {
			$attr_cache[$this->index][$attr_name] = true;
			return true;
		}

		# See what the server schema says about this attribute
		$sattr = $this->getSchemaAttribute($attr_name);
		if (! is_object($sattr)) {

			/* Strangely, some attributeTypes may not show up in the server
			 * schema. This behavior has been observed in MS Active Directory.*/
			$type = null;
			$syntax = null;

		} else {
			$type = $sattr->getType();
			$syntax = $sattr->getSyntaxOID();
		}

		if (strcasecmp($type,'Certificate') == 0 ||
			strcasecmp($type,'Binary') == 0 ||
			strcasecmp($attr_name,'usercertificate') == 0 ||
			strcasecmp($attr_name,'usersmimecertificate') == 0 ||
			strcasecmp($attr_name,'networkaddress') == 0 ||
			strcasecmp($attr_name,'objectGUID') == 0 ||
			strcasecmp($attr_name,'objectSID') == 0 ||
			strcasecmp($attr_name,'auditingPolicy') == 0 ||
			strcasecmp($attr_name,'jpegPhoto') == 0 ||
			strcasecmp($attr_name,'krbExtraData') == 0 ||
			strcasecmp($attr_name,'krbPrincipalKey') == 0 ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.10' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.28' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.5' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.8' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.9'
		) {

			$attr_cache[$this->index][$attr_name] = true;
			return true;

		} else {
			$attr_cache[$this->index][$attr_name] = false;
			return false;
		}
	}

	/**
	 * This function will test if a user is a member of a group.
	 *
	 * Inputs:
	 * @param string $user membership value that is being checked
	 * @param dn $group DN to see if user is a member
	 * @return bool true|false
	 */
	function userIsMember($user,$group) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$user = strtolower($user);
		$group = $this->getDNAttrValues($group);

		# If you are using groupOfNames objectClass
		if (array_key_exists('member',$group) && ! is_array($group['member']))
			$group['member'] = array($group['member']);

		if (array_key_exists('member',$group) &&
			in_array($user,arrayLower($group['member'])))

			return true;

		# If you are using groupOfUniqueNames objectClass
		if (array_key_exists('uniquemember',$group) && ! is_array($group['uniquemember']))
			$group['uniquemember'] = array($group['uniquemember']);

		if (array_key_exists('uniquemember',$group) &&
			in_array($user,arrayLower($group['uniquemember'])))

			return true;

		return false;
	}

	/**
	 * This function will determine if the user is allowed to login based on a filter
	 */
	protected function userIsAllowedLogin($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$dn = trim(strtolower($dn));

		if (! $this->getValue('login','allowed_dns'))
			return true;

        foreach ($this->getValue('login','allowed_dns') as $login_allowed_dn) {
            if (DEBUG_ENABLED)
                debug_log('Working through (%s)',80,0,__FILE__,__LINE__,__METHOD__,$login_allowed_dn);

            /* Check if $login_allowed_dn is an ldap search filter
             * Is first occurence of 'filter=' (case ensitive) at position 0 ? */
            if (preg_match('/^\([&|]\(/',$login_allowed_dn)) {
				$query = array();
                $query['filter'] = $login_allowed_dn;
				$query['attrs'] = array('dn');

                foreach($this->getBaseDN() as $base_dn) {
					$query['base'] = $base_dn;

                    $results = $this->query($query,null);

                    if (DEBUG_ENABLED)
                        debug_log('Search, Filter [%s], BaseDN [%s] Results [%s]',16,0,__FILE__,__LINE__,__METHOD__,
                            $query['filter'],$query['base'],$results);

                    if ($results) {
                    	$dn_array = array();

                        foreach ($results as $result)
                            array_push($dn_array,$result['dn']);

                        $dn_array = array_unique($dn_array);

                        if (count($dn_array))
                            foreach ($dn_array as $result_dn) {
                                if (DEBUG_ENABLED)
                                    debug_log('Comparing with [%s]',80,0,__FILE__,__LINE__,__METHOD__,$result_dn);

                                # Check if $result_dn is a user DN
                                if (strcasecmp($dn,trim(strtolower($result_dn))) == 0)
                                    return true;

                                # Check if $result_dn is a group DN
                                if ($this->userIsMember($dn,$result_dn))
                                    return true;
                        }
                    }
                }
            }

            # Check if $login_allowed_dn is a user DN
            if (strcasecmp($dn,trim(strtolower($login_allowed_dn))) == 0)
                return true;

            # Check if $login_allowed_dn is a group DN
            if ($this->userIsMember($dn,$login_allowed_dn))
                return true;
        }

        return false;
	}
}
?>
