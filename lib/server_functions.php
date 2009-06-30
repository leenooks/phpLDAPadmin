<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/server_functions.php,v 1.27.2.8 2005/11/12 02:37:19 wurley Exp $ */

/**
 * Classes and functions for LDAP server configuration and capability
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * @package phpLDAPadmin
 */
class LDAPserver {
	/** Server ID as defined in config.php */
	var $server_id;
	/** Server Name as defined in config.php */
	var $name;
	/** Server Hostname as defined in config.php */
	var $host;
	/** Server Port as defined in config.php */
	var $port;
	/** Server Authentication method as defined in config.php */
	var $auth_type;
	/** Server Authentication Login DN as defined in config.php */
	var $login_dn;
	/** Server Authentication Password as defined in config.php */
	var $login_pass;
	/** Array of our connections to this LDAP server */
	var $connections = array();
	/** Server Base Dn */
	var $_baseDN;
	/** Schema DN */
	var $_schemaDN = null;
	/** Raw Schema entries */
	var $_schema_entries = null;
 
	/** Default constructor.
	 * @param int $server_id the server_id of the LDAP server as defined in config.php
	 */
	function LDAPserver($server_id) {
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with (%s)',2,get_class($this),$server_id);

		$this->_baseDN = null;
		$this->server_id = $server_id;
	}

	/**
	 * Checks the specified server id for sanity. Ensures that the server is indeed in the configured
	 * list and active. This is used by many many scripts to ensure that valid server ID values
	 * are passed in POST and GET.
	 *
	 * @param int $server_id the server_id of the LDAP server as defined in config.php
	 * @return bool
	 */
	function isValidServer() {
		if (DEBUG_ENABLED)
			debug_log('%s::isValidServer(): Entered with ()',2,get_class($this));

		if (trim($this->host))
			return true;
		else
			return false;
	}

	/**
	 * Check if there is sufficent information to Authenticate to the LDAP server.
	 *
	 * Given a server, returns whether or not we have enough information
	 * to authenticate against the server. For example, if the user specifies
	 * auth_type of 'cookie' in the config for that server, it checks the $_COOKIE array to
	 * see if the cookie username and password is set for the server. If the auth_type
	 * is 'session', the $_SESSION array is checked.
	 *
	 * There are three cases for this function depending on the auth_type configured for
	 * the specified server. If the auth_type is session or cookie, then get_logged_in_dn() is
	 * called to verify that the user has logged in. If the auth_type is config, then the
	 * $ldapservers configuration in config.php is checked to ensure that the user has specified
	 * login information. In any case, if phpLDAPadmin has enough information to login
	 * to the server, true is returned. Otherwise false is returned.
	 *
	 * @return bool
	 * @see get_logged_in_dn
	 */
	function haveAuthInfo() {
		if (DEBUG_ENABLED) {
			debug_log('%s::haveAuthInfo(): Entered with ()',2,get_class($this));
			debug_log('%s::haveAuthInfo(): We are a (%s) auth_type',9,get_class($this),$this->auth_type);
		}

		# Set default return
		$return = false;

		# For session or cookie auth_types, we check the session or cookie to see if a user has logged in.
		if (in_array($this->auth_type,array('session','cookie'))) {

			/* we don't look at get_logged_in_pass() cause it may be null for anonymous binds
			   get_logged_in_dn() will never return null if someone is really logged in. */
			if (get_logged_in_dn($this))
				$return = true;
			else
				$return = false;

		/* whether or not the login_dn or pass is specified, we return
		   true here. (if they are blank, we do an anonymous bind anyway) */
		} elseif ($this->auth_type == 'config') {
			$return = true;

		} else {
			global $lang;
			pla_error(sprintf($lang['error_auth_type_config'],htmlspecialchars($this->auth_type)));
		}

		if (DEBUG_ENABLED)
			debug_log('%s::haveAuthInfo(): Returning (%s)',1,get_class($this),$return);

		return $return;
	}

	function _connect($connect_num=0) {
		if (DEBUG_ENABLED)
			debug_log('%s::_connect(): Entered with (%s)',2,get_class($this),$connect_num);

		if (isset($this->connections[$connect_num]['resource'])) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): Returning CACHED connection resource [%s](%s)',3,
					get_class($this),$this->connections[$connect_num]['resource'],$connect_num);

			return $this->connections[$connect_num]['resource'];

		}

		return false;
	}

	/**
	 * Connect to the LDAP server.

	 * @param bool $anonymous Connect as the anonymous user.
	 * @param bool $reconnect Use a cached connetion, or create a new one.
	 * @returns resource|false Connection resource to LDAP server, or false if no connection made.
	 * @todo Need to make changes so that multiple connects can be made.
	 */
	function connect($process_error=true,$anonymous=false,$reconnect=false,$connect_num=0) {
		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Entered with (%s,%s,%s,%s)',2,
				get_class($this),$process_error,$anonymous,$reconnect,$connect_num);

		$resource = $this->_connect($connect_num);
		if ($resource && ! $reconnect) {
			return $resource;
		}
 
		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Creating new connection #[%s] for Server ID [%s]',3,
				get_class($this),$connect_num,$this->server_id);

		// grab the auth info based on the auth_type for this server
		if ($anonymous == true) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): This IS an anonymous login',4,get_class($this));

			$this->connections[$connect_num]['login_dn'] = null;
			$this->connections[$connect_num]['login_pass'] = null;

		} elseif ($this->auth_type == 'config') {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): This IS a "config" login',9,get_class($this));

			if (! $this->login_dn) {
				if (DEBUG_ENABLED)
					debug_log('%s::connect(): No login_dn, so well do anonymous',9,get_class($this));

				$anonymous = true;

 			} else {
				$this->connections[$connect_num]['login_dn'] = $this->login_dn;
				$this->connections[$connect_num]['login_pass'] = $this->login_pass;

				// Multiple base strings mean we can't do this properly
				// Could just take the first entry or return an array rather than a string
				// Ignore for now
				//$this->connections[$connect_num]['login_dn'] = expand_dn_with_base( $this, $this->connections[$connect_num]['login_dn'], false );
				if (DEBUG_ENABLED)
					debug_log('%s::connect(): Config settings, DN [%s], PASS [%s]',9,
						get_class($this),$this->connections[$connect_num]['login_dn'],
						$this->connections[$connect_num]['login_pass'] ? md5($this->connections[$connect_num]['login_pass']) : '');
 			}
 
		} else {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): This IS some other login',9,get_class($this));

			$this->connections[$connect_num]['login_dn'] = get_logged_in_dn( $this );
			$this->connections[$connect_num]['login_pass'] = get_logged_in_pass( $this );

			if (DEBUG_ENABLED)
				debug_log('%s::connect(): Config settings, DN [%s], PASS [%s]',9,
					get_class($this),$this->connections[$connect_num]['login_dn'],
					$this->connections[$connect_num]['login_pass'] ? md5($this->connections[$connect_num]['login_pass']) : '');

			// Was this an anonyous bind (the cookie stores 0 if so)?
			if( 'anonymous' == $this->connections[$connect_num]['login_dn'] ) {
				$this->connections[$connect_num]['login_dn'] = null;
				$this->connections[$connect_num]['login_pass'] = null;
				$anonymous = true;
 			}
		}
 
		if (! $anonymous && ! $this->connections[$connect_num]['login_dn'] && ! $this->connections[$connect_num]['login_pass']) {

			if (DEBUG_ENABLED)
				debug_log('%s::connect(): We dont have enough auth info for server [%s]',9,get_class($this),$this->server_id);

			return false;
		}
 
		run_hook ( 'pre_connect', array ( 'server_id' => $this->server_id,
						  'connect_num' => $connect_num,
						  'anonymous' => $anonymous ) );
 
		if ($this->port)
		        $resource = @ldap_connect( $this->host, $this->port );
		else
		        $resource = @ldap_connect( $this->host );
 
		if (DEBUG_ENABLED)
			debug_log('%s::connect(): LDAP Resource [%s], Host [%s], Port [%s]',9,
				get_class($this),$resource,$this->host,$this->port);

		// go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
		@ldap_set_option( $resource, LDAP_OPT_PROTOCOL_VERSION, 3 );

		// Disabling this makes it possible to browse the tree for Active Directory, and seems
		// to not affect other LDAP servers (tested with OpenLDAP) as phpLDAPadmin explicitly
		// specifies deref behavior for each ldap_search operation.
		@ldap_set_option( $resource, LDAP_OPT_REFERRALS, 0);

		// try to fire up TLS is specified in the config
		if( $this->isTLSEnabled() ) {
		        global $lang;
			function_exists( 'ldap_start_tls' ) or pla_error( $lang['php_install_not_supports_tls'] );
			@ldap_start_tls( $resource ) or pla_error( $lang['could_not_start_tls'], ldap_error($resource));
		}
 
		$bind_result = @ldap_bind( $resource, $this->connections[$connect_num]['login_dn'],
					   $this->connections[$connect_num]['login_pass'] );
 
		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Resource [%s], Bind Result [%s]',9,get_class($this),$resource,$bind_result);

		if( ! $bind_result ) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): Leaving with FALSE, bind FAILed',9,get_class($this));

			if ($process_error) {
			        global $lang;
				switch( ldap_errno($resource) ) {
				case 0x31:
				        pla_error( $lang['bad_user_name_or_password'] );
					break;
				case 0x32:
				        pla_error( $lang['insufficient_access_rights'] );
					break;
				case -1:
				        pla_error( sprintf($lang['could_not_connect_to_host_on_port'],$this->host,$this->port) );
					break;
				default:
				        pla_error( $lang['could_not_bind'], ldap_err2str( $resource ), $resource );
 				}
 			} else {
			        return false; // ldap_errno( $resource );
 			}
 		}
 
		if (is_resource($resource) && ($bind_result)) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): Bind successful',9,get_class($this));

			$this->connections[$connect_num]['connected'] = true;
			$this->connections[$connect_num]['resource'] = $resource;
		}

		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Leaving with Connect #[%s], Resource [%s]',9,
				get_class($this),$connect_num,$this->connections[$connect_num]['resource']);
		
 		return $this->connections[$connect_num]['resource'];
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
	 * @return array dn|null The root DN of the server on success (string) or null on error.
	 * @todo Sort the entries, so that they are in the correct DN order.
	 */
	function getBaseDN() {
		if (DEBUG_ENABLED)
			debug_log('%s::getBaseDN(): Entered with ()',2,get_class($this));

		global $ldapservers;

		# Return the cached entry if we've been here before.
		if (! is_null($this->_baseDN)) {
		        debug_log('%s::getBaseDN(): Return CACHED BaseDN [%s]',3,get_class($this),implode('|',$this->_baseDN));
			return $this->_baseDN;
		}

		if (DEBUG_ENABLED)
			debug_log('%s::getBaseDN(): Checking config for BaseDN',9,get_class($this));

		# If the base is set in the configuration file, then just return that.
		if (count($ldapservers->GetValue($this->server_id,'server','base')) > 0) {
			$this->_baseDN = $ldapservers->GetValue($this->server_id,'server','base');

			if (DEBUG_ENABLED)
				debug_log('%s::getBaseDN(): Return BaseDN from Config [%s]',4,get_class($this),implode('|',$this->_baseDN));

			return $this->_baseDN;

		# We need to figure it out.
		} else {
			if (DEBUG_ENABLED)
				debug_log('%s::getBaseDN(): Connect to LDAP to find BaseDN',9,get_class($this));

			if ($this->connect()) {
				$r = @ldap_read($this->connect(),'','objectClass=*',array('namingContexts'));

				if (DEBUG_ENABLED)
					debug_log('%s::getBaseDN(): Search Results [%s], Resource [%s], Msg [%s]',9,
						get_class($this),$r,$this->connect(),ldap_error($this->connect()));

				if (! $r)
					return array('');

				$r = @ldap_get_entries($this->connect(false),$r);

				if (isset($r[0]['namingcontexts'])) {

					# If we have a count key, delete it - dont need it.
					if (isset($r[0]['namingcontexts']['count']))
						unset($r[0]['namingcontexts']['count']);

					if (DEBUG_ENABLED)
						debug_log('%s::getBaseDN(): LDAP Entries:%s',5,get_class($this),implode('|',$r[0]['namingcontexts']));

					$this->_baseDN = $r[0]['namingcontexts'];

					return $this->_baseDN;

				} else {
					return array('');
				}

			} else {
				return array('');
			}
		}
	}

	/**
	 * Returns true if the specified server is configured to be displayed
	 * in read only mode.
	 *
	 * If a user has logged in via anonymous bind, and config.php specifies
	 * anonymous_bind_implies_read_only as true, then
	 * this also returns true. Servers can be configured read-only in
	 * config.php thus:
	 * <code>
	 *  $server[$i]['read_only'] = true;
	 * </code>
	 *
	 * @return bool
	 */
	function isReadOnly() {
		global $config;

		# Set default return
		$return = false;

		if ($this->read_only == true)
			$return = true;

		elseif (get_logged_in_dn($this) === "anonymous" &&
			($config->GetValue('appearance','anonymous_bind_implies_read_only') === true))

			$return = true;

		if (DEBUG_ENABLED)
			debug_log('%s::isReadOnly(): Entered with (), Returning (%s)',1,get_class($this),$return);

		return $return;
	}

	/**
	 * Returns true if the user has configured the specified server to enable mass deletion.
	 *
	 * Mass deletion is enabled in config.php this:
	 * <code>
	 *   $config->custom->appearance['mass_delete'] = true;
	 * </code>
	 * Notice that mass deletes are not enabled on a per-server basis, but this
	 * function checks that the server is not in a read-only state as well.
	 *
	 * @return bool
	 */
	function isMassDeleteEnabled() {
		if (DEBUG_ENABLED)
			debug_log('%s::isMassDeleteEnabled(): Entered with ()',2,get_class($this));

		global $config;

		if ($this->connect(false) &&
			$this->haveAuthInfo() &&
			! $this->isReadOnly() &&
			$config->GetValue('appearance','mass_delete') === true)
			return true;

		else
			return false;
	}

	/**
	 * Gets whether the admin has configured phpLDAPadmin to show the "Create New" link in the tree viewer.
	 *
	 * <code>
	 *  $ldapservers->SetValue($i,'appearance','show_create','true|false');
	 * </code>
	 * If NOT set, then default to show the Create New item.
	 * If IS set, then return the value (it should be true or false).
	 *
	 * @default true
	 * @return bool True if the feature is enabled and false otherwise.
	 */
	function isShowCreateEnabled() {
		if (DEBUG_ENABLED)
			debug_log('%s::isShowCreateEnabled(): Entered with ()',2,get_class($this));

		return $this->show_create;
	}

	/**
	 * Fetch whether the user has configured a certain server as "low bandwidth".
	 *
	 * Users may choose to configure a server as "low bandwidth" in config.php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'server','low_bandwidth','true|false');
	 * </code>
	 *
	 * @default false
	 * @return bool
	 */
	function isLowBandwidth() {
		if (DEBUG_ENABLED)
			debug_log('%s::isLowBandwidth(): Entered with ()',2,get_class($this));

		return $this->low_bandwidth;
	}

	/**
	 * Should this LDAP server be shown in the tree?
	 *
	 * <code>
	 *	$ldapservers->SetValue($i,'server','visible','true|false');
	 * </code>
	 *
	 * @default true
	 * @return bool True if the feature is enabled and false otherwise.
	 */
	function isVisible() {
		if ($this->isValidServer() && $this->visible)
			$return = true;
		else
			$return = false;

		if (DEBUG_ENABLED)
	        debug_log('%s::isVisible(): Entered with (), Returning (%s)',1,get_class($this),$return);

		return $return;
	}

	/**
	 * This function will query the ldap server and request the subSchemaSubEntry which should be the Schema DN.
	 *
	 * If we cant connect to the LDAP server, we'll return false.
	 * If we can connect but cant get the entry, then we'll return null.
	 *
	 * @return array|false Schema if available, null if its not or false if we cant connect.
	 */
	function getSchemaDN($dn='') {
		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Entered with (%s)',2,get_class($this),$dn);

		# If we already got the SchemaDN, then return it.
		if ($this->_schemaDN)
			return $this->_schemaDN;

		if (! $this->connect())
			return false;

		$search = @ldap_read($this->connect(),$dn,'objectClass=*',array('subschemaSubentry'));

		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Search returned (%s)',4,get_class($this),is_resource($search));

		# Fix for broken ldap.conf configuration.
		if (! $search && ! $dn) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Trying to find the DN for "broken" ldap.conf',1,get_class($this));

			if (isset($this->_baseDN)) {
				foreach ($this->_baseDN as $base) {
					$search = @ldap_read($this->connect(),$base,'objectClass=*',array('subschemaSubentry'));

					if (DEBUG_ENABLED)
						debug_log('%s::getSchemaDN(): Search returned (%s) for base (%s)',4,
							get_class($this),is_resource($search),$base);

					if ($search)
						break;
				}
			}
		}

		if (! $search)
			return null;

		if (! @ldap_count_entries($this->connect(),$search)) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Search returned 0 entries. Returning NULL',4,get_class($this));

			return null;
		}

		$entries = @ldap_get_entries($this->connect(),$search);

		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Search returned [%s]',4,get_class($this),serialize($entries));

		if (! $entries || ! is_array($entries))
			return null;

		$entry = isset($entries[0]) ? $entries[0] : false;
		if (! $entry) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Entry is false, Returning NULL',4,get_class($this));

			return null;
		}

		$sub_schema_sub_entry = isset($entry[0]) ? $entry[0] : false;
		if (! $sub_schema_sub_entry) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Sub Entry is false, Returning NULL',4,get_class($this));

			return null;
		}

		$this->_schemaDN = isset($entry[$sub_schema_sub_entry][0]) ? $entry[$sub_schema_sub_entry][0] : false;

		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Returning (%s)',1,get_class($this),$this->_schemaDN);

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
	 * @param string $schema_to_fetch - A string indicating which type of schema to
	 *		fetch. Five valid values: 'objectclasses', 'attributetypes',
	 *		'ldapsyntaxes', 'matchingruleuse', or 'matchingrules'.
	 *		Case insensitive.
	 * @param dn $dn (optional) This paremeter is the DN of the entry whose schema you
	 * 		would like to fetch. Entries have the option of specifying
	 * 		their own subschemaSubentry that points to the DN of the system
	 * 		schema entry which applies to this attribute. If unspecified,
	 *		this will try to retrieve the schema from the RootDSE subschemaSubentry.
	 *		Failing that, we use some commonly known schema DNs. Default
	 *		value is the Root DSE DN (zero-length string)
	 * @return array an array of strings of this form:
	 *    Array (
	 *      [0] => "(1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
	 *      [1] => "(1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
	 *      etc.
	 */
	function getRawSchema($schema_to_fetch,$dn='') {
		if (DEBUG_ENABLED)
			debug_log('%s::getRawSchema(): Entered with (%s,%s)',2,get_class($this),$schema_to_fetch,$dn);

		$valid_schema_to_fetch = array('objectclasses','attributetypes','ldapsyntaxes','matchingrules','matchingruleuse');

		if (! $this->connect())
			return false;

		# error checking
		$schema_to_fetch = strtolower($schema_to_fetch);
		
		if (!is_null($this->_schema_entries) && isset($this->_schema_entries[$schema_to_fetch])) {
		        $schema = $this->_schema_entries[$schema_to_fetch];
			unset($schema['count']);

			if (DEBUG_ENABLED)
				debug_log('%s::getRawSchema(): Returning (%s)',1,get_class($this),serialize($schema));

			return $schema;
		}
		
		# This error message is not localized as only developers should ever see it
		if (! in_array($schema_to_fetch,$valid_schema_to_fetch))
			pla_error(sprintf('Bad parameter provided to function to %s::getRawSchema(). "%s" is not valid for the schema_to_fetch parameter.',
					  get_class($this),htmlspecialchars($schema_to_fetch)));

		# Try to get the schema DN from the specified entry.
		$schema_dn = $this->getSchemaDN($dn);

		# Do we need to try again with the Root DSE?
		if (! $schema_dn)
			$schema_dn = $this->getSchemaDN('');

		# Store the eventual schema retrieval in $schema_search
		$schema_search = null;

		if ($schema_dn) {
			if (DEBUG_ENABLED)
				debug_log('%s::getRawSchema(): Using Schema DN (%s)',4,get_class($this),$schema_dn);

			foreach (array('(objectClass=*)','(objectClass=subschema)') as $schema_filter) {
				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Looking for schema with Filter (%s)',4,get_class($this),$schema_filter);

				$schema_search = @ldap_read($this->connect(),$schema_dn,$schema_filter,array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS);

				if (is_null($schema_search))
				        continue;

				$schema_entries = @ldap_get_entries($this->connect(),$schema_search);

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Search returned [%s]',5,get_class($this),serialize($schema_entries));

				if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Found schema with filter of (%s)',4,get_class($this),$schema_filter);

					break;
				}

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Didnt find schema with filter (%s)',5,get_class($this),$schema_filter);

				unset($schema_entries);
				$schema_search = null;
			}
		}

		/* Second chance: If the DN or Root DSE didn't give us the subschemaSubentry, ie $schema_search
		   is still null, use some common subSchemaSubentry DNs as a work-around. */
		if (is_null($schema_search)) {
			if (DEBUG_ENABLED)
			  debug_log('%s::getRawSchema(): Attempting work-arounds for "broken" LDAP servers...',5,get_class($this));

			foreach ($this->getBaseDN() as $base) {
				$ldap['W2K3 AD'][expand_dn_with_base($base,'cn=Aggregate,cn=Schema,cn=configuration,')] = '(objectClass=*)';
				$ldap['W2K AD'][expand_dn_with_base($base,'cn=Schema,cn=configuration,')] = '(objectClass=*)';
				$ldap['W2K AD'][expand_dn_with_base($base,'cn=Schema,ou=Admin,')] = '(objectClass=*)';
			}
			// OpenLDAP and Novell
			$ldap['OpenLDAP']['cn=subschema'] = '(objectClass=*)';

			foreach ($ldap as $ldap_server_name => $ldap_options) {
				foreach ($ldap_options as $ldap_dn => $ldap_filter) {
					if (DEBUG_ENABLED)
						debug_log("%s::getRawSchema(): Attempting [%s] (%s) (%s)<BR>",5,
							get_class($this),$ldap_server_name,$ldap_dn,$ldap_filter);

					$schema_search = @ldap_read($this->connect(), $ldap_dn, $ldap_filter,
								      array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
					if (is_null($schema_search))
					        continue;
					$schema_entries = @ldap_get_entries($this->connect(),$schema_search);

					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Search returned [%s]',5,get_class($this),serialize($schema_entries));

					if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
						if (DEBUG_ENABLED)
							debug_log('%s::getRawSchema(): Found schema with filter of (%s)',4,get_class($this),$ldap_filter);

						break;
					}

					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Didnt find schema with filter (%s)', 5,get_class($this),$ldap_filter);

					unset($schema_entries);
					$schema_search = null;
				}
				if ($schema_search)
					break;
			}
		}

		if (is_null($schema_search)) {
			// Still cant find the schema, try with the RootDSE
			// Attempt to pull schema from Root DSE with scope "base", or
			// Attempt to pull schema from Root DSE with scope "one" (work-around for Isode M-Vault X.500/LDAP)
			foreach (array('base','one') as $ldap_scope) {
				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Attempting to find schema with scope (%s), filter (objectClass=*) and a blank base.',
						5,get_class($this),$ldap_scope);

				switch ($ldap_scope) {
					case 'base':
						$schema_search = @ldap_read($this->connect(),'','(objectClass=*)',array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS);
						break;

					case 'one':
						$schema_search = @ldap_list($this->connect(),'','(objectClass=*)',array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS);
						break;
				}

				if (is_null($schema_search))
				        continue;

				$schema_entries = @ldap_get_entries($this->connect(),$schema_search);
				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Search returned [%s]',5,get_class($this),serialize($schema_entries));

				if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Found schema with filter of (%s)',4,get_class($this),'(objectClass=*)');

					break;
				}

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Didnt find schema with filter (%s)',5,get_class($this),'(objectClass=*)');

				unset($schema_entries);
				$schema_search = null;
			}
		}

		$schema_error_message = 'Please contact the phpLDAPadmin developers and let them know:<ul><li>Which LDAP server you are running, including which version<li>What OS it is running on<li>Which version of PHP<li>As well as a link to some documentation that describes how to obtain the SCHEMA information</ul><br />We\'ll then add support for your LDAP server in an upcoming release.';
		$schema_error_message_array = array('objectclasses','attributetypes');

		# Shall we just give up?
		if (is_null($schema_search)) {

			# We need to have objectclasses and attribues, so display an error, asking the user to get us this information.
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" have FAILED.<br /><br />%s',$schema_to_fetch,$schema_error_message));

			} else {
				$return = false;

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Returning because schema_search is NULL (%s)',1,get_class($this),$return);

				return $return;
			}
		}

		# Did we get something unrecognizable?
		if (gettype($schema_search) != 'resource') {
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" has return EXPECTED results.<br /><br /><small>(We expected a "resource" for $schema_search, instead, we got (%s))</small><br /><br />%s<br /><br />Dump of $schema_search:<hr /><pre><small>%s</small></pre>',
					$schema_to_fetch,gettype($schema_search),$schema_error_message,serialize($schema_search)));

			} else {
				$return = false;

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Returning because schema_search type is not a resource (%s)',1,
						get_class($this),$return);

				return $return;
			}
		}

		if (! $schema_entries) {
			$return = false;
			if (DEBUG_ENABLED)
				debug_log('%s::getRawSchema(): Returning false since ldap_get_entries() returned false.',1,
					get_class($this),$return);

			return $return;
		}

		if(! isset($schema_entries[0][$schema_to_fetch])) {
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" has return EXPECTED results.<br /><br /><small>(We expected a "%s" in the $schema array but it wasnt there.)</small><br /><br />%s<br /><br />Dump of $schema_search:<hr /><pre><small>%s</small></pre>',
					$schema_to_fetch,gettype($schema_search),$schema_error_message,serialize($schema_entries)));

			} else {
				$return = false;

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Returning because (%s) isnt in the schema array. (%s)',1,
						get_class($this),$schema_to_fetch,$return);

				return $return;
			}
		}

		/* Make a nice array of this form:
		   Array (
		      [0] => "( 1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ... )"
		      [1] => "( 1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ... )"
		      etc.  ) */

		$this->_schema_entries = $schema_entries[0];

		$schema = $schema_entries[0][$schema_to_fetch];
		unset($schema['count']);

		if (DEBUG_ENABLED)
			debug_log('%s::getRawSchema(): Returning (%s)',1,get_class($this),serialize($schema));

		return $schema;
	}

	/**
	 * Fetches whether the login_attr feature is enabled for a specified server.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'login','attr','<ldap attr>');
	 * </code>
	 *
	 * By virtue of the fact that the login_attr is not blank and not 'dn', the
	 * feature is configured to be enabled.
	 *
	 * @default uid
	 * @return bool
	 */
	function isLoginAttrEnabled() {
		if ((strcasecmp($this->login_attr,'dn') != 0) && trim($this->login_attr))
			$return = true;
		else
			$return = false;

		if (DEBUG_ENABLED)
			debug_log('%s::isLoginAttrEnabled(): Entered with (), Returning (%s)',1,get_class($this),$return);

		return $return;
	}

	/**
	 * Fetches whether the login_attr feature is enabled for a specified server.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'login','attr','string');
	 * </code>
	 *
	 * @return bool
	 */
	function isLoginStringEnabled() {
		if (DEBUG_ENABLED)
			debug_log('%s::isLoginStringEnabled(): login_attr is [%s]',9,get_class($this),$this->login_attr);

		if (! strcasecmp($this->login_attr,'string'))
			$return = true;
		else
			$return = false;

		if (DEBUG_ENABLED)
			debug_log('%s::isLoginStringEnabled(): Entered with (), Returning (%s)',1,get_class($this),$return);

		return $return;
	}

	/**
	 * Fetches the login_attr string if enabled for a specified server.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'login','login_string','uid=<username>,ou=People,dc=example,dc=com');
	 * </code>
	 *
	 * @return string|false
	 */
	function getLoginString() {
		if (DEBUG_ENABLED)
			debug_log('%s::getLoginString(): Entered with ()',2,get_class($this));

		return $this->login_string;
	}

	/**
	 * Fetch whether the user has configured a certain server login to be non anonymous
	 *
	 * <code>
	 *	$ldapservers->SetValue($i,'login','anon_bind','true|false');
	 * </code>
	 *
	 * @default true
	 * @return bool
	 */
	function isAnonBindAllowed() {
		global $ldapservers;

		// If only_login_allowed_dns is set, then we cant have anonymous.
		if (count($ldapservers->GetValue($this->server_id,'login','allowed_dns')) > 0)
			$return = false;
		else
			$return = $ldapservers->GetValue($this->server_id,'login','anon_bind');

		if (DEBUG_ENABLED)
		        debug_log('%s::isAnonBindAllowed(): Entered with (), Returning (%s)',1,get_class($this),$return);

		return $return;
	}

	/**
	 * Fetches whether TLS has been configured for use with a certain server.
	 *
	 * Users may configure phpLDAPadmin to use TLS in config,php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'login','tls','true|false');
	 * </code>
	 *
	 * @default false
	 * @return bool
	 */
	function isTLSEnabled() {
		if (DEBUG_ENABLED)
			debug_log('%s::isTLSEnabled(): Entered with ()',2,get_class($this));

		return $this->tls;
	}

	/**
	 * Returns true if the user has configured the specified server to enable branch (non-leaf) renames.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'server','branch_rename','true|false');
	 * </code>
	 *
	 * @default false
	 * @param int $server_id The ID of the server of interest from config.php.
	 * @return bool
	 */
	function isBranchRenameEnabled() {
		debug_log(sprintf('%s::isBranchRenameEnabled(): Entered with (), Returning (%s).',
			get_class($this),$this->branch_rename),2);

 		return $this->branch_rename;
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
	function SchemaObjectClasses($dn='') {
		debug_log(sprintf('%s::SchemaObjectClasses(): Entered with (%s)',get_class($this),$dn),2);
	
		# Set default return
		$return = null;
	
		if ($return = get_cached_item($this->server_id,'schema','objectclasses')) {
			debug_log(sprintf('%s::SchemaObjectClasses(): Returning CACHED [%s] (%s)',
				get_class($this),$this->server_id,'objectclasses'),3);
	
			return $return;
		}
	
		$raw_oclasses = $this->getRawSchema('objectclasses',$dn);
	
		if ($raw_oclasses) {
			# build the array of objectClasses
			$return = array();
	
			foreach ($raw_oclasses as $class_string) {
				if (is_null($class_string) || ! strlen($class_string))
					continue;
	
				$object_class = new ObjectClass($class_string);
				$return[strtolower($object_class->getName())] = $object_class;
			}
	
			ksort($return);
	
			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->server_id,'schema','objectclasses',$return);
		}
	
		debug_log(sprintf('%s::SchemaObjectClasses(): Returning (%s)',get_class($this),serialize($return)),1);
		return $return;
	}

	/**
	 * Gets a single ObjectClass object specified by name.
	 *
	 * @param string $oclass_name The name of the objectClass to fetch.
	 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
	 *             which defines the subschemaSubEntry attribute (all entries should).
	 *
	 * @return ObjectClass The specified ObjectClass object or false on error.
	 *
	 * @see ObjectClass
	 * @see SchemaObjectClasses
	 */
	function getSchemaObjectClass($oclass_name,$dn='') {
		$oclass_name = strtolower($oclass_name);
		$oclasses = $this->SchemaObjectClasses($dn);
	
		# Default return value
		$return = false;
	
		if (isset($oclasses[$oclass_name]))
			$return = $oclasses[$oclass_name];
	
		debug_log(sprintf('%s::getSchemaObjectClass(): Entered with (%s,%s), Returning (%s).',
			get_class($this),$oclass_name,$dn,serialize($return)),2);
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
	function getSchemaAttribute($attr_name,$dn=null) {
		$attr_name = real_attr_name(strtolower($attr_name));
		$schema_attrs = $this->SchemaAttributes($dn);

		# Default return value
		$return = false;
	
		if (isset($schema_attrs[$attr_name]))
			$return = $schema_attrs[$attr_name];
	
		debug_log(sprintf('%s::getSchemaAttribute(): Entered with (%s,%s), Returning (%s).',
			get_class($this),$attr_name,$dn,serialize($return)),2);
		return $return;
	}

	/**
	 * Gets an associative array of AttributeType objects for the specified
	 * server. Each array entry's key is the name of the attributeType
	 * in lower-case and the value is an AttributeType object.
	 *
	 * @param int $server_id The ID of the server whose AttributeTypes to fetch
	 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
	 *             which defines the subschemaSubEntry attribute (all entries should).
	 *
	 * @return array An array of AttributeType objects.
	 */
	function SchemaAttributes($dn=null) {
		debug_log(sprintf('%s::SchemaAttributes(): Entered with (%s)',get_class($this),$dn),2);
	
		# Set default return
		$return = null;
	
		if ($return = get_cached_item($this->server_id,'schema','attributes')) {
			debug_log(sprintf('%s::SchemaAttributes(): Returning CACHED [%s] (%s)',
				get_class($this),$this->server_id,'attributes'),3);
			return $return;
		}
	
		$raw_attrs = $this->getRawSchema('attributeTypes', $dn);
		if ($raw_attrs) {
			# build the array of attribueTypes
			$syntaxes = $this->SchemaSyntaxes($dn);
			$attrs = array();
	
			/**
			 * bug 856832: create two arrays - one indexed by name (the standard
			 * $attrs array above) and one indexed by oid (the new $attrs_oid array
			 * below). This will help for directory servers, like IBM's, that use OIDs
			 * in their attribute definitions of SUP, etc
			 */
			$attrs_oid = array();
			foreach ($raw_attrs as $attr_string) {
				if (is_null($attr_string) || ! strlen($attr_string))
					continue;
	
				$attr = new AttributeType($attr_string);
				if (isset($syntaxes[$attr->getSyntaxOID()])) {
					$syntax = $syntaxes[$attr->getSyntaxOID()];
					$attr->setType($syntax->getDescription());
				}
				$attrs[strtolower($attr->getName())] = $attr;
	
				/**
				 * bug 856832: create an entry in the $attrs_oid array too. This
				 * will be a ref to the $attrs entry for maintenance and performance
				 * reasons
				 */
				$attrs_oid[$attr->getOID()] = &$attrs[strtolower($attr->getName())];
			}
	
			# go back and add data from aliased attributeTypes
			foreach ($attrs as $name => $attr) {
				$aliases = $attr->getAliases();

				if (is_array($aliases) && count($aliases) > 0) {
					/* foreach of the attribute's aliases, create a new entry in the attrs array
					   with its name set to the alias name, and all other data copied.*/
					foreach ($aliases as $alias_attr_name) {
						# clone is a PHP5 function and must be used.
						if (function_exists('clone'))
							$new_attr = clone($attr);
						else
							$new_attr = $attr;
						$new_attr->setName($alias_attr_name);
						$new_attr->addAlias($attr->getName());
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
					   but just in case we get carried away, stop at 100. This shouldn't happen, but for
					   some weird reason, we have had someone report that it has happened. Oh well.*/
					$i = 0;
					while($i++ < 100 /** 100 == INFINITY ;) */) {
			
						/**
						 * Bug 856832: check if sup is indexed by OID. If it is,
						 * replace the OID with the appropriate name. Then reset
						 * $sup_attr_name to the name instead of the OID. This will
						 * make all the remaining code in this function work as
						 * expected.
						 */
						if (isset($attrs_oid[$sup_attr_name])) {
							$attr->setSupAttribute($attrs_oid[$sup_attr_name]->getName());
							$sup_attr_name = $attr->getSupAttribute();
						}
			
						if (! isset($attrs[strtolower($sup_attr_name)])){
							pla_error(sprintf('Schema error: attributeType "%s" inherits from "%s", but attributeType "%s" does not exist.',
								$attr->getName(),$sup_attr_name,$sup_attr_name));
							return;
						}
			
						$sup_attr = $attrs[strtolower($sup_attr_name)];
						$sup_attr_name = $sup_attr->getSupAttribute();
			
						# Does this superior attributeType not have a superior attributeType?
						if (is_null($sup_attr_name) || strlen(trim($sup_attr_name)) == 0) {
			
							/* Since this attribute's superior attribute does not have another superior
							   attribute, clone its properties for this attribute. Then, replace
							   those cloned values with those that can be explicitly set by the child
							   attribute attr). Save those few properties which the child can set here:*/
							$tmp_name = $attr->getName();
							$tmp_oid = $attr->getOID();
							$tmp_sup = $attr->getSupAttribute();
							$tmp_aliases = $attr->getAliases();
							$tmp_single_val = $attr->getIsSingleValue();
			
							/* clone the SUP attributeType and populate those values
							   that were set by the child attributeType */
							# clone is a PHP5 function and must be used.
							if (function_exists('clone'))
								$attr = clone($sup_attr);
							else
								$attr = $sup_attr;
							$attr->setOID($tmp_oid);
							$attr->setName($tmp_name);
							$attr->setSupAttribute($tmp_sup);
							$attr->setAliases($tmp_aliases);
			
							/* only overwrite the SINGLE-VALUE property if the child explicitly sets it
							   (note: All LDAP attributes default to multi-value if not explicitly set SINGLE-VALUE) */
							if (true == $tmp_single_val)
								$attr->setIsSingleValue(true);
			
							/* replace this attribute in the attrs array now that we have populated
								 new values therein */
							$attrs[$key] = $attr;
			
							# very important: break out after we are done with this attribute
							$sup_attr_name = null;
							$sup_attr = null;
							break;
			
						} else {
							# do nothing, move on down the chain of inheritance...
						}
					}
				}
			}
			
			ksort($attrs);
	
			# Add the used in and required_by values.
			$schema_object_classes = $this->SchemaObjectClasses();
			if (! is_array($schema_object_classes))
				return array();
	
			foreach ($schema_object_classes as $object_class) {
				$must_attrs = $object_class->getMustAttrNames($schema_object_classes);
				$may_attrs = $object_class->getMayAttrNames($schema_object_classes);
				$oclass_attrs = array_unique(array_merge($must_attrs,$may_attrs));
	
				# Add Used In.
				foreach ($oclass_attrs as $attr_name) {
					if (isset($attrs[strtolower($attr_name)]))
						$attrs[strtolower($attr_name)]->addUsedInObjectClass($object_class->getName());
	
					else {
						#echo "Warning, attr not set: $attr_name<br />";
					}
				}
	
				# Add Required By.
				foreach ($must_attrs as $attr_name) {
					if (isset($attrs[strtolower($attr_name)]))
						$attrs[strtolower($attr_name)]->addRequiredByObjectClass($object_class->getName());
	
					else {
						#echo "Warning, attr not set: $attr_name<br />";
					}
				}
	
			}
	
			$return = $attrs;
			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->server_id,'schema','attributes',$return);
		}
	
		debug_log(sprintf('%s::SchemaAttributes(): Returning (%s)',get_class($this),serialize($return)),1);
		return $return;
	}

	/**
	 * Returns an array of MatchingRule objects for the specified server.
	 * The key of each entry is the OID of the matching rule.
	 */
	function MatchingRules($dn=null) {
		# Set default return
		$return = null;
	
		if ($return = get_cached_item($this->server_id,'schema','matchingrules')) {
			debug_log(sprintf('%s::MatchingRules(): Returning CACHED [%s] (%s).',
				get_class($this),$this->server_id,'matchingrules'),3);
			return $return;
		}
	
		# build the array of MatchingRule objects
		$raw_matching_rules = $this->getRawSchema('matchingRules', $dn);

		if ($raw_matching_rules) {
			$rules = array();

			foreach ($raw_matching_rules as $rule_string) {
				if (is_null($rule_string) || 0 == strlen($rule_string))
					continue;

				$rule = new MatchingRule($rule_string);
				$key = strtolower($rule->getName());
				$rules[$key] = $rule;
			}
	
			ksort($rules);
	
			/* For each MatchingRuleUse entry, add the attributes who use it to the
			   MatchingRule in the $rules array.*/
			$raw_matching_rule_use = $this->getRawSchema('matchingRuleUse');

			if ($raw_matching_rule_use != false) {
				foreach ($raw_matching_rule_use as $rule_use_string) {
					if ($rule_use_string == null || 0 == strlen($rule_use_string))
						continue;
					$rule_use = new MatchingRuleUse($rule_use_string);
					$key = strtolower($rule_use->getName());
					if (isset($rules[$key]))
						$rules[$key]->setUsedByAttrs($rule_use->getUsedByAttrs());
				}

			} else {
				/* No MatchingRuleUse entry in the subschema, so brute-forcing
				   the reverse-map for the "$rule->getUsedByAttrs()" data.*/
				$attrs = $this->SchemaAttributes($dn);
				if (is_array($attrs))
					foreach ($attrs as $attr) {
						$rule_key = strtolower($attr->getEquality());
						if (isset($rules[$rule_key]))
							$rules[$rule_key]->addUsedByAttr($attr->getName());
					}
			}
	
			$return = $rules;

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->server_id,'schema','matchingrules',$return);
		}
	
		debug_log(sprintf('%s::MatchingRules(): Entered with (%s), Returning (%s).',
			get_class($this),$dn,serialize($return)),2);
		return $return;
	}
	
	/**
	 * Returns an array of Syntax objects that this LDAP server uses mapped to
	 * their descriptions. The key of each entry is the OID of the Syntax.
	 */
	function SchemaSyntaxes($dn=null) {
		# Set default return
		$return = null;
	
		if ($return = get_cached_item($this->server_id,'schema','syntaxes')) {
			debug_log(sprintf('%s::SchemaSyntaxes(): Returning CACHED [%s] (%s).',
			get_class($this),$this->server_id,'syntaxes'),3);
			return $return;
		}
	
		$raw_syntaxes = $this->getRawSchema('ldapSyntaxes', $dn);
		if ($raw_syntaxes) {
			# build the array of attributes
			$return = array();

			foreach ($raw_syntaxes as $syntax_string) {
				$syntax = new Syntax($syntax_string);
				$key = strtolower(trim($syntax->getOID()));
				if (! $key) continue;
				$return[$key] = $syntax;
			}
	
			ksort($return);
	
			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->server_id,'schema','syntaxes',$return);
		}
	
		debug_log(sprintf('%s::SchemaSyntaxes(): Entered with (%s), Returning (%s).',
			get_class($this),$dn,serialize($return)),2);
		return $return;
	}

	/**
	 * Modify objects
	 *
	 */
	function modify($dn, $update_array) {
		return @ldap_modify($this->connect(),$dn,$update_array);
	}

	/**
	 * Return error from last operation
	 *
	 */
	function error() {
		return ldap_error($this->connect());
	}

	/**
	 * Return errno from last operation
	 *
	 */
	function errno() {
		return ldap_errno($this->connect());
	}
}

class LDAPservers {
	/* All LDAP servers */
	var $_ldapservers;

	/* Default settings for a new LDAP server configuration. */
	var $default;

	function LDAPservers() {
		$this->default = new StdClass;

		$this->default->server['name'] = array(
			'desc'=>'Server name',
			'var'=>'name',
			'default'=>'LDAP Server');

		$this->default->server['host'] = array(
			'desc'=>'Host Name',
			'var'=>'host',
			'default'=>'127.0.0.1');

		$this->default->server['port'] = array(
			'desc'=>'Port Number',
			'var'=>'port',
			'default'=>'389');

		$this->default->server['base'] = array(
			'desc'=>'Base DN',
			'default'=>array());

		$this->default->server['tls'] = array(
			'desc'=>'Connect using TLS',
			'var'=>'tls',
			'default'=>false);

		$this->default->server['auth_type'] = array(
			'desc'=>'Authentication Type',
			'var'=>'auth_type',
			'default'=>'cookie');

		$this->default->server['low_bandwidth'] = array(
			'desc'=>'Enable LOW Bandwidth optimisations',
			'var'=>'low_bandwidth',
			'default'=>false);

		$this->default->server['read_only'] = array(
			'desc'=>'Server is in READ ONLY mode',
			'var'=>'read_only',
			'default'=>false);

		$this->default->server['branch_rename'] = array(
			'desc'=>'Permit renaming branches',
			'var'=>'branch_rename',
			'default'=>false);

		$this->default->login['dn'] = array(
			'desc'=>'User Login DN',
			'var'=>'login_dn',
			'default'=>'');

		$this->default->login['pass'] = array(
			'desc'=>'User Login Password',
			'var'=>'login_pass',
			'default'=>'');

		$this->default->login['attr'] = array(
			'desc'=>'Attribute to use to find the users DN',
			'var'=>'login_attr',
			'default'=>'dn');

		$this->default->login['class'] = array(
			'desc'=>'Strict login to users containing a specific objectClass',
			'default'=>null);

		$this->default->login['string'] = array(
			'desc'=>'Login string if using auth_type=string',
			'var'=>'login_string',
			'default'=>null);

		$this->default->login['anon_bind'] = array(
			'desc'=>'Whether to allow anonymous binds',
			'default'=>true);

		$this->default->login['allowed_dns'] = array(
			'desc'=>'Limit logins to users who match any of the following LDAP filters',
			'default'=>array());

		$this->default->login['timeout'] = array(
			'desc'=>'Session timout in seconds',
			'var'=>'session_timeout',
			'default'=>session_cache_expire()-1);

		$this->default->appearance['password_hash'] = array(
			'desc'=>'Default HASH to use for passwords',
			'var'=>'default_hash',
			'default'=>'md5');

		$this->default->appearance['show_create'] = array(
			'desc'=>'Show CREATE options in the tree',
			'var'=>'show_create',
			'default'=>true);

		$this->default->appearance['visible'] = array(
			'desc'=>'Whether this LDAP server is visible in the tree',
			'var'=>'visible',
			'default'=>true);

		$this->default->auto_number['enable'] = array(
			'desc'=>'Enable the AUTO UID feature',
			'default'=>true);

		$this->default->auto_number['mechanism'] = array(
			'desc'=>'Mechanism to use to search for automatic numbers',
			'default'=>'search');

		$this->default->auto_number['search_base'] = array(
			'desc'=>'Base DN to use for search mechanisms',
			'default'=>null);

		$this->default->auto_number['min'] = array(
			'desc'=>'Minimum UID number to start with',
			'default'=>1000);

		$this->default->auto_number['dn'] = array(
			'desc'=>'DN to use when evaluating numbers',
			'default'=>null);

		$this->default->auto_number['pass'] = array(
			'desc'=>'Password for DN to use when evaluating numbers',
			'default'=>null);

		$this->default->unique_attrs['dn'] = array(
			'desc'=>'DN to use when evaluating uniqueness',
			'default'=>null);

		$this->default->unique_attrs['pass'] = array(
			'desc'=>'Password for DN to use when evaluating uniqueness',
			'default'=>null);

		$this->default->custom['pages_prefix'] = array(
			'desc'=>'Path to custom pages',
			'default'=>null);
		
	}

	function SetValue($server_id,$key,$index,$value) {
		if (defined('DEBUG_ENABLED') && (DEBUG_ENABLED))
			debug_log('%s::SetValue(): Entered with (%s,%s,%s,%s)',2,
				get_class($this),$server_id,$key,$index,(is_array($value) ? 'Array:'.count($value) : $value));

		if (! isset($this->default->$key))
			# @todo: Display an error, it should be predefined.
			pla_error("ERROR: Setting a key [$key] that isnt predefined.");
		else
			$default = $this->default->$key;

		if (! isset($default[$index]))
			# @todo: Display an error, it should be predefined.
			pla_error("ERROR: Setting a index [$index] that isnt predefined.");
		else
			$default = $default[$index];

		# Test if its should be an array or not.
		if (is_array($default['default']) && ! is_array($value))
			pla_error("Error in configuration file, {$key}['$index'] SHOULD be an array of values.");

		if (! is_array($default['default']) && is_array($value))
			pla_error("Error in configuration file, {$key}['$index'] should NOT be an array of values.");

		# Some special processing.
		if ($key == 'server') {
			switch ($index) {
				case 'host' :
					if (strstr($value,"ldapi://"))
						$this->_ldapservers[$server_id][$key]['port'] = false;
					break;
			}
		}
		$this->_ldapservers[$server_id][$key][$index] = $value;
	}

	function GetValue($server_id,$key,$index) {
		if (isset($this->_ldapservers[$server_id][$key][$index]))
			$return = $this->_ldapservers[$server_id][$key][$index];
		else
			$return = $this->default->{$key}[$index]['default'];

		if (DEBUG_ENABLED)
			debug_log('%s::GetValue(): Entered with (%s,%s,%s), Returning (%s)',1,
				get_class($this),$server_id,$key,$index,(is_array($return) ? 'Array:'.serialize($return) : $return));

		return $return;
	}

	function GetServerList() {
		return count($this->_ldapservers) ? array_keys($this->_ldapservers) : null;
	}

	function Instance($server_id) {
		$instance = new LDAPserver($server_id);

		foreach ($this->default as $key => $details) {
			foreach ($details as $index => $value) {
				if (isset($value['var']))
					$instance->{$value['var']} = $this->GetValue($server_id,$key,$index);
			}
		}

		return $instance;
	}
}
?>
