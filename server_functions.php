<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/server_functions.php,v 1.27 2005/09/17 19:57:27 wurley Exp $ */

/**
 * Classes and functions for LDAP server configuration and capability
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * @package phpLDAPadmin
 */
class LDAPServer {
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
        
	/** Default constructor.
	 * @param int $server_id the server_id of the LDAP server as defined in config.php
	 */
	function LDAPServer($server_id,$ignore=true) {
		debug_log(sprintf('%s::init(): Entered with (%s,%s)',get_class($this),$server_id,$ignore),2);

		# Other Internal Values
		$this->_schemaDN = null;
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
		debug_log(sprintf('%s::isValidServer(): Entered with ()',get_class($this)),2);

		if( trim($this->host) == '' )
			return false;

		else
			return true;
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
		debug_log(sprintf('%s::haveAuthInfo(): Entered with ()',get_class($this)),2);

		debug_log(sprintf('%s::haveAuthInfo(): We are a (%s) auth_type',get_class($this),$this->auth_type),9);

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
		} elseif ($this->auth_type == 'config' ) {
			$return = true;

		} else {
			global $lang;
			pla_error(sprintf($lang['error_auth_type_config'],htmlspecialchars($this->auth_type)) );
		}

		debug_log(sprintf('%s::haveAuthInfo(): Returning (%s)',get_class($this),$return),1);
		return $return;
	}

	/**
	 * Connect to the LDAP server.

	 * @param bool $anonymous Connect as the anonymous user.
	 * @param bool $reconnect Use a cached connetion, or create a new one.
	 * @returns resource|false Connection resource to LDAP server, or false if no connection made.
	 * @todo Need to make changes so that multiple connects can be made.
	 */
	function connect($process_error=true,$anonymous=false,$reconnect=false,$connect_num=0) {
		debug_log(sprintf('%s::connect(): Entered with (%s,%s,%s,%s)',
			get_class($this),$process_error,$anonymous,$reconnect,$connect_num),2);

		if (isset($this->connections[$connect_num]['resource']) && ! $reconnect) {
			debug_log(sprintf('%s::connect(): Returning CACHED connection resource [%s](%s)',
				get_class($this),$this->connections[$connect_num]['resource'],$connect_num),3);

			return $this->connections[$connect_num]['resource'];

		} else {

			debug_log(sprintf('%s::connect(): Creating new connection #[%s] for Server ID [%s]',
				get_class($this),$connect_num,$this->server_id),3);

			global $lang;

			if ($anonymous == true) {
				debug_log(sprintf('%s::connect(): This IS an anonymous login',get_class($this)),4);
				$this->connections[$connect_num]['login_dn'] = null;
				$this->connections[$connect_num]['login_pass'] = null;
			} // grab the auth info based on the auth_type for this server

			elseif ($this->auth_type == 'config') {
				debug_log(sprintf('%s::connect(): This IS a "config" login',get_class($this)),9);

				if (! $this->login_dn) {
					debug_log(sprintf('%s::connect(): No login_dn, so well do anonymous',get_class($this)),9);
					$anonymous = true;

				} else {
					$this->connections[$connect_num]['login_dn'] = $this->login_dn;
					$this->connections[$connect_num]['login_pass'] = $this->login_pass;
					$this->connections[$connect_num]['login_dn'] = expand_dn_with_base( $this, $this->connections[$connect_num]['login_dn'], false );
					debug_log(sprintf('%s::connect(): Config settings, DN [%s], PASS [%s]',get_class($this),
						$this->connections[$connect_num]['login_dn'],
					$this->connections[$connect_num]['login_pass'] ? md5($this->connections[$connect_num]['login_pass']) : ''),9);
				}

			} else {
				debug_log(sprintf('%s::connect(): This IS some other login',get_class($this)),9);
				$this->connections[$connect_num]['login_dn'] = get_logged_in_dn( $this );
				$this->connections[$connect_num]['login_pass'] = get_logged_in_pass( $this );
				debug_log(sprintf('%s::connect(): Config settings, DN [%s], PASS [%s]',get_class($this),
					$this->connections[$connect_num]['login_dn'],
					$this->connections[$connect_num]['login_pass'] ? md5($this->connections[$connect_num]['login_pass']) : ''),9);

				// Was this an anonyous bind (the cookie stores 0 if so)?
				if( 'anonymous' == $this->connections[$connect_num]['login_dn'] ) {
					$this->connections[$connect_num]['login_dn'] = null;
					$this->connections[$connect_num]['login_pass'] = null;
					$anonymous = true;
				}
			}

			if (! $anonymous && ! $this->connections[$connect_num]['login_dn'] && ! $this->connections[$connect_num]['login_pass']) {
				debug_log(sprintf('%s::connect(): We dont have enough auth info for server [%s]',get_class($this),$this->server_id),9);
				return false;
			}

			run_hook ( 'pre_connect', array ( 'server_id' => $this->server_id,
				'connect_num' => $connect_num,
				'anonymous' => $anonymous ) );

			if ($this->port)
				$resource = @ldap_connect( $this->host, $this->port );

			else
				$resource = @ldap_connect( $this->host );

			debug_log(sprintf('%s::connect(): LDAP Resource [%s], Host [%s], Port [%s]',get_class($this),$resource,$this->host,$this->port),9);

			// go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
			@ldap_set_option( $resource, LDAP_OPT_PROTOCOL_VERSION, 3 );

			// Disabling this makes it possible to browse the tree for Active Directory, and seems
			// to not affect other LDAP servers (tested with OpenLDAP) as phpLDAPadmin explicitly
			// specifies deref behavior for each ldap_search operation.
			@ldap_set_option( $resource, LDAP_OPT_REFERRALS, 0);

			// try to fire up TLS is specified in the config
			if( $this->isTLSEnabled() ) {
				function_exists( 'ldap_start_tls' ) or pla_error( $lang['php_install_not_supports_tls'] );
				@ldap_start_tls( $resource ) or pla_error( $lang['could_not_start_tls'], ldap_error($resource));
			}

			$bind_result = @ldap_bind( $resource, $this->connections[$connect_num]['login_dn'],
				$this->connections[$connect_num]['login_pass'] );

			debug_log(sprintf('%s::connect(): Resource [%s], Bind Result [%s]',
				get_class($this),$resource,$bind_result),9);

			if( ! $bind_result ) {
				debug_log(sprintf('%s::connect(): Leaving with FALSE, bind FAILed',get_class($this)),9);

				if ($process_error) {
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
				debug_log(sprintf('%s::connect(): Bind successful',get_class($this)),9);
				$this->connections[$connect_num]['connected'] = true;
				$this->connections[$connect_num]['resource'] = $resource;

/*
			} else {
				if ($process_error) {
					debug_log(sprintf('Server: [%s], Username: [%s], Password: [%s]',
						$this->server_id,
						$this->connections[$connect_num]['login_dn'],
						$this->connections[$connect_num]['login_pass']),9);

					if( is_numeric( $resource ) ) {
						switch( $resource ) {
							case -1: pla_error( $lang['bad_server_id'] ); break;
							case -2: pla_error( $lang['not_enough_login_info'] ); break;
							default: pla_error( $lang['ferror_error'] ); break;
						}
						// return true; Do we get here?
					}

					switch( $resource ) {
						case 0x31:
							pla_error( $lang['bad_user_name_or_password'] );
							break;
						case 0x32:
							pla_error( $lang['insufficient_access_rights'] );
							break;
						case 0x5b:
							pla_error( $lang['could_not_connect'] );
							break;
						default:
							pla_error( $lang['could_not_bind'], ldap_err2str( $this->connections[$connect_num]['resource'] ), $this->connections[$connect_num]['resource'] );
					}

				}
				debug_log(sprintf('%s::connect(): Leaving with FALSE',get_class($this)),9);
				return false;
*/
			}
		}
		debug_log(sprintf('%s::connect(): Leaving with Connect #[%s], Resource [%s]',get_class($this),$connect_num,$this->connections[$connect_num]['resource']),9);

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
		debug_log(sprintf('%s::getBaseDN(): Entered with ()',get_class($this)),2);

		global $ldapservers;

		# Return the cached entry if we've been here before.
		if (! is_null($this->_baseDN)) {
			debug_log(sprintf('%s::getBaseDN(): Return CACHED BaseDN [%s]',get_class($this),implode('|',$this->_baseDN)),3);
			return $this->_baseDN;
		}

		debug_log(sprintf('%s::getBaseDN(): Checking config for BaseDN',get_class($this)),9);

		# If the base is set in the configuration file, then just return that.
		if (count($ldapservers->GetValue($this->server_id,'server','base')) > 0) {
			$this->_baseDN = $ldapservers->GetValue($this->server_id,'server','base');

			debug_log(sprintf('%s::getBaseDN(): Return BaseDN from Config [%s]',get_class($this),implode('|',$this->_baseDN)),4);
			return $this->_baseDN;

		# We need to figure it out.
		} else {
			debug_log(sprintf('%s::getBaseDN(): Connect to LDAP to find BaseDN',get_class($this)),9);

			if ($this->connect()) {
				$r = @ldap_read($this->connect(),'','objectClass=*',array('namingContexts'));
				debug_log(sprintf('%s::getBaseDN(): Search Results [%s], Resource [%s], Msg [%s]',
					get_class($this),$r,$this->connect(),ldap_error($this->connect())),9);

				if (! $r)
					return array('');

				$r = @ldap_get_entries($this->connect(false),$r);

				if (isset($r[0]['namingcontexts'])) {

					# If we have a count key, delete it - dont need it.
					if (isset($r[0]['namingcontexts']['count']))
						unset($r[0]['namingcontexts']['count']);

					debug_log(sprintf('%s::getBaseDN(): LDAP Entries:%s',get_class($this),implode('|',$r[0]['namingcontexts'])),5);
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
		debug_log(sprintf('%s::isReadOnly(): Entered with ()',get_class($this)),2);

		global $config;

		# Set default return
		$return = false;

		if ($this->read_only == true)
			$return = true;

		elseif (get_logged_in_dn($this) === "anonymous" &&
			($config->GetValue('appearance','anonymous_bind_implies_read_only') === true))

			$return = true;

		debug_log(sprintf('%s::isReadOnly(): Returning (%s)',get_class($this),$return),1);
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
		debug_log(sprintf('%s::isMassDeleteEnabled(): Entered with ()',get_class($this)),2);

		global $config;

		if( $this->connect(false) &&
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
		debug_log(sprintf('%s::isShowCreateEnabled(): Entered with ()',get_class($this)),2);

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
		debug_log(sprintf('%s::isLowBandwidth(): Entered with ()',get_class($this)),2);

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
		debug_log(sprintf('%s::isVisible(): Entered with ()',get_class($this)),2);

		if( $this->isValidServer() && $this->visible )
			$return = true;
		else
			$return = false;

		debug_log(sprintf('%s::isVisible(): Returning (%s)',get_class($this),$return),1);
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
		debug_log(sprintf('%s::getSchemaDN(): Entered with (%s)',get_class($this),$dn),2);

		# If we already got the SchemaDN, then return it.
		if ($this->_schemaDN)
			return $this->_schemaDN;

		if (! $this->connect())
			return false;

		$search = @ldap_read($this->connect(),$dn,'objectClass=*',array('subschemaSubentry'));
		debug_log(sprintf('%s::getSchemaDN(): Search returned (%s)',get_class($this),is_resource($search)),4);

		# Fix for broken ldap.conf configuration.
		if (! $search && ! $dn) {
			debug_log(sprintf('%s::getSchemaDN(): Trying to find the DN for "broken" ldap.conf',get_class($this)),1);

			if (isset($this->_baseDN)) {
				foreach ($this->_baseDN as $base) {
					$search = @ldap_read($this->connect(),$base,'objectClass=*',array('subschemaSubentry'));
					debug_log(sprintf('%s::getSchemaDN(): Search returned (%s) for base (%s)',get_class($this),is_resource($search),$base),4);

				if ($search)
					break;
				}
			}
		}

		if (! $search)
			return null;

		if (! @ldap_count_entries($this->connect(),$search)) {
			debug_log(sprintf('%s::getSchemaDN(): Search returned 0 entries. Returning NULL',get_class($this)),4);
			return null;
		}

		$entries = @ldap_get_entries($this->connect(),$search);
		debug_log(sprintf('%s::getSchemaDN(): Search returned [%s]',get_class($this),serialize($entries)),4);
		if (! $entries || ! is_array($entries))
			return null;

		$entry = isset($entries[0]) ? $entries[0] : false;
		if (! $entry) {
			debug_log(sprintf('%s::getSchemaDN(): Entry is false, Returning NULL',get_class($this)),4);
			return null;
		}

		$sub_schema_sub_entry = isset($entry[0]) ? $entry[0] : false;
		if (! $sub_schema_sub_entry) {
			debug_log(sprintf('%s::getSchemaDN(): Sub Entry is false, Returning NULL',get_class($this)),4);
			return null;
		}

		$this->_schemaDN = isset($entry[$sub_schema_sub_entry][0]) ? $entry[$sub_schema_sub_entry][0] : false;

		debug_log(sprintf('%s::getSchemaDN(): Returning (%s)',get_class($this),$this->_schemaDN),1);
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
	 *      [0] => "( 1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
	 *      [1] => "( 1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
	 *      etc.
	 */
	function getRawSchema($schema_to_fetch,$dn='') {
		debug_log(sprintf('%s::getRawSchema(): Entered with (%s,%s)',get_class($this),$schema_to_fetch,$dn),2);

		global $lang;
		$valid_schema_to_fetch = array('objectclasses','attributetypes','ldapsyntaxes','matchingrules','matchingruleuse');

		if (! $this->connect())
			return false;

		# error checking
		$schema_to_fetch = strtolower($schema_to_fetch);

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
			debug_log(sprintf('%s::getRawSchema(): Using Schema DN (%s)',get_class($this),$schema_dn),4);

			foreach (array('(objectClass=*)','(objectClass=subschema)') as $schema_filter) {
				debug_log(sprintf('%s::getRawSchema(): Looking for schema with Filter (%s)',get_class($this),$schema_filter),4);

				$schema_search = @ldap_read($this->connect(),$schema_dn,$schema_filter,array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS);

				# Were we not able to fetch the schema from the $schema_dn?
				$schema_entries = @ldap_get_entries($this->connect(),$schema_search);
				debug_log(sprintf('%s::getRawSchema(): Search returned [%s]',get_class($this),serialize($schema_entries)),5);

				if ($schema_search && @ldap_count_entries($this->connect(),$schema_search) && isset($schema_entries[0][$schema_to_fetch])) {
					debug_log(sprintf('%s::getRawSchema(): Found schema with filter of (%s)',get_class($this),$schema_filter),4);
					break;
				}

				debug_log(sprintf('%s::getRawSchema(): Didnt find schema with filter (%s)',get_class($this),$schema_filter),5);
				unset($schema_entries);
				$schema_search = null;
			}
		}

		/* Second chance: If the DN or Root DSE didn't give us the subschemaSubentry, ie $schema_search
		   is still null, use some common subSchemaSubentry DNs as a work-around. */

		/** @todo: This is broken now, so we'll need some test cases to fix it up again **/
		/*
		if (! $schema_search) {
			debug_log(sprintf('%s::getRawSchema(): Attempting work-arounds for 'broken' LDAP servers...',get_class($this)),5);

			// OpenLDAP and Novell
			// @todo Fix expand_dn_with_base - no longer works since getBaseDN is now an arrya.
			$ldap['OpenLDAP']['cn=subschema'] = '(objectClass=*)';
//			$ldap['W2K3 AD'][expand_dn_with_base($this,'cn=Aggregate,cn=Schema,cn=configuration,',true)] = '(objectClass=*)';
//			$ldap['W2K AD'][expand_dn_with_base($this,'cn=Schema,cn=configuration,',true)] = '(objectClass=*)';
//			$ldap['W2K AD'][expand_dn_with_base($this,'cn=Schema,ou=Admin,',true)] = '(objectClass=*)';

			foreach ($ldap as $ldap_server_name => $ldap_options) {
				foreach ($ldap_options as $ldap_dn => $ldap_filter) {
					if( $debug ) printf("%s::getRawSchema(): Attempting [%s] (%s) (%s)<BR>",get_class($this),$ldap_server_name,$ldap_dn,$ldap_filter);

					$schema_search = @ldap_read($this->connect(), $ldap_dn, $ldap_filter,
						array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
				}

				if ($schema_search)
					break;
			}

			// Still cant find the schema, try with the RootDSE
			// Attempt to pull schema from Root DSE with scope "base", or
			// Attempt to pull schema from Root DSE with scope "one" (work-around for Isode M-Vault X.500/LDAP)
		}
		*/

		if (is_null($schema_search)) {
			foreach (array('base','one') as $ldap_scope) {
				debug_log(sprintf('%s::getRawSchema(): Attempting to find schema with scope (%s), filter (objectClass=*) and a blank base.',
					get_class($this),$ldap_scope),5);

				switch ($ldap_scope) {
					case 'base':
						$schema_search = @ldap_read($this->connect(),'','(objectClass=*)',array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS );
						break;

					case 'one':
						$schema_search = @ldap_list($this->connect(),'','(objectClass=*)',array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS );
						break;
				}

				$schema_entries = @ldap_get_entries($this->connect(),$schema_search);
				debug_log(sprintf('%s::getRawSchema(): Search returned [%s]',get_class($this),serialize($schema_entries)),5);

				if (! isset($schema_entries[0][$schema_to_fetch]))
					$schema_search = null;

				else
					break;
			}
		}

		$schema_error_message = 'Please contant the phpLDAPadmin developers and let them know:<ul><li>Your LDAP server, including version<li>OS its running on<li>Version of PHP<li>A link to some documentation that describes how to obtain the SCHEMA information</ul><br />We\'ll then add support for your LDAP server in an upcoming release.';
		$schema_error_message_array = array('objectclasses','attributetypes');

		# Shall we just give up?
		if (is_null($schema_search)) {

			# We need to have objectclasses and attribues, so display an error, asking the user to get us this information.
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" have FAILED.<br /><br />%s',$schema_to_fetch,$schema_error_message));

			} else {
				$return = false;
				debug_log(sprintf('%s::getRawSchema(): Returning because schema_search is NULL (%s)',get_class($this),$return),1);
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
				debug_log(sprintf('%s::getRawSchema(): Returning because schema_search type is not a resource (%s)',get_class($this),$return),1);
				return $return;
			}
		}

		$schema = @ldap_get_entries($this->connect(),$schema_search);
		if (! $schema) {
			$return = false;
			debug_log(sprintf('%s::getRawSchema(): Returning false since ldap_get_entries() returned false.',get_class($this),$return),1);
			return $return;
		}

		if(! isset($schema[0][$schema_to_fetch])) {
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" has return EXPECTED results.<br /><br /><small>(We expected a "%s" in the $schema array but it wasnt there.)</small><br /><br />%s<br /><br />Dump of $schema_search:<hr /><pre><small>%s</small></pre>',
					$schema_to_fetch,gettype($schema_search),$schema_error_message,serialize($schema)));

			} else {
				$return = false;
				debug_log(sprintf('%s::getRawSchema(): Returning because (%s) isnt in the schema array. (%s)',get_class($this),$schema_to_fetch,$return),1);
				return $return;
			}
		}

		/* Make a nice array of this form:
		   Array (
		      [0] => "( 1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
		      [1] => "( 1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
		      etc. */

		$schema = $schema[0][$schema_to_fetch];
		unset($schema['count']);

		debug_log(sprintf('%s::getRawSchema(): Returning (%s)',get_class($this),serialize($schema)),1);
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
		debug_log(sprintf('%s::isLoginAttrEnabled(): Entered with ()',get_class($this)),2);

		if ((strcasecmp($this->login_attr,'dn') != 0) && trim($this->login_attr))
			$return = true;
		else
			$return = false;

		debug_log(sprintf('%s::isLoginAttrEnabled(): Returning (%s)',get_class($this),$return),1);
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
		debug_log(sprintf('%s::isLoginStringEnabled(): Entered with ()',get_class($this)),2);

		debug_log(sprintf('%s::isLoginStringEnabled(): login_attr is [%s]',get_class($this),$this->login_attr),9);
		if (! strcasecmp($this->login_attr,'string'))
			$return = true;
		else
			$return = false;

		debug_log(sprintf('%s::isLoginStringEnabled(): Returning (%s)',get_class($this),$return),1);
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
		debug_log(sprintf('%s::getLoginString(): Entered with ()',get_class($this)),2);

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
		debug_log(sprintf('%s::isAnonBindAllowed(): Entered with ()',get_class($this)),2);

		global $ldapservers;

		// If only_login_allowed_dns is set, then we cant have anonymous.
		if (count($ldapservers->GetValue($this->server_id,'login','allowed_dns')) > 0)
			$return = false;
		else
			$return = $ldapservers->GetValue($this->server_id,'login','anon_bind');

		debug_log(sprintf('%s::isAnonBindAllowed(): Returning (%s)',get_class($this),$return),1);
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
		debug_log(sprintf('%s::isTLSEnabled(): Entered with ()',get_class($this)),2);

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
		global $ldapservers;
 		return $ldapservers->GetValue($this->server_id,'server','branch_rename');
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
			'default'=>'uid');

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
		debug_log(sprintf('%s::SetValue(): Entered with (%s,%s,%s,%s)',
			get_class($this),$server_id,$key,$index,(is_array($value) ? 'Array:'.count($value) : $value)),2);

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
		debug_log(sprintf('%s::GetValue(): Entered with (%s,%s,%s)',get_class($this),$server_id,$key,$index),2);

		if (isset($this->_ldapservers[$server_id][$key][$index]))
			$return = $this->_ldapservers[$server_id][$key][$index];
		else
			$return = $this->default->{$key}[$index]['default'];

		debug_log(sprintf('%s::GetValue(): Returning (%s)',get_class($this),(is_array($return) ? 'Array:'.serialize($return) : $return)),1);
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
