<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/server_functions.php,v 1.34.2.33 2008/11/28 14:21:37 wurley Exp $ */

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
			debug_log('%s::__construct(): Entered with (%s)',17,get_class($this),$server_id);

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
			debug_log('%s::isValidServer(): Entered with ()',17,get_class($this));

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
	 * the specified server. If the auth_type is session or cookie, then getLoggedInDN() is
	 * called to verify that the user has logged in. If the auth_type is config, then the
	 * $ldapservers configuration in config.php is checked to ensure that the user has specified
	 * login information. In any case, if phpLDAPadmin has enough information to login
	 * to the server, true is returned. Otherwise false is returned.
	 *
	 * @return bool
	 * @see getLoggedInDN
	 */
	function haveAuthInfo() {
		if (DEBUG_ENABLED) {
			debug_log('%s::haveAuthInfo(): Entered with ()',17,get_class($this));
			debug_log('%s::haveAuthInfo(): We are a (%s) auth_type',80,get_class($this),$this->auth_type);
		}

		# Set default return
		$return = false;

		# For session or cookie auth_types, we check the session or cookie to see if a user has logged in.
		if (in_array($this->auth_type,array('session','cookie'))) {

			/* we don't look at getLoggedInPass() cause it may be null for anonymous binds
			   getLoggedInDN() will never return null if someone is really logged in. */
			if ($this->getLoggedInDN())
				$return = true;
			else
				$return = false;

		/* whether or not the login_dn or pass is specified, we return
		   true here. (if they are blank, we do an anonymous bind anyway) */
		} elseif ($this->auth_type == 'config') {
			$return = true;

		} else {
			pla_error(sprintf(_('Error: You have an error in your config file. The only three allowed values for auth_type in the $servers section are \'session\', \'cookie\', and \'config\'. You entered \'%s\', which is not allowed.'),htmlspecialchars($this->auth_type)));
		}

		if (DEBUG_ENABLED)
			debug_log('%s::haveAuthInfo(): Returning (%s)',17,get_class($this),$return);

		return $return;
	}

	function _connect($connect_name) {
		if (DEBUG_ENABLED)
			debug_log('%s::_connect(): Entered with (%s)',17,get_class($this),$connect_name);

		if (isset($this->connections[$connect_name]['resource'])) {
			if (DEBUG_ENABLED)
				debug_log('%s::_connect(): Returning CACHED connection resource [%s](%s)',16,
					get_class($this),$this->connections[$connect_name]['resource'],$connect_name);

			return $this->connections[$connect_name]['resource'];

		}

		return false;
	}

	/**
	 * Connect to the LDAP server.
	 *
	 * @param bool $process_error Whether to call an error page, if the connection fails
	 * @param bool $connect_id The ID for this connection
	 * @param bool $reconnect Use a cached connetion, or create a new one.
	 * @returns resource|false Connection resource to LDAP server, or false if no connection made.
	 */
	function connect($process_error=true,$connect_id='user',$reconnect=false,$dn=null,$pass=null) {
		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Entered with (%s,%s,%s)',17,
				get_class($this),$process_error,$connect_id,$reconnect);

		# Quick return if we have already connected.
		$resource = $this->_connect($connect_id);
		if (is_resource($resource) && ! $reconnect)
			return $resource;

		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Creating new connection [%s] for Server ID [%s]',16,
				get_class($this),$connect_id,$this->server_id);

		# grab the auth info based on the auth_type for this server
		if ($connect_id == 'anonymous') {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): This IS an anonymous login',80,get_class($this));

			$this->connections[$connect_id]['login_dn'] = null;
			$this->connections[$connect_id]['login_pass'] = null;

		} elseif ($this->auth_type == 'config') {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): This IS a "config" login',80,get_class($this));

			if (! $this->login_dn) {
				if (DEBUG_ENABLED)
					debug_log('%s::connect(): No login_dn, so well do anonymous',80,get_class($this));

				$connect_id = 'anonymous';

 			} else {
				$this->connections[$connect_id]['login_dn'] = $this->login_dn;
				$this->connections[$connect_id]['login_pass'] = $this->login_pass;

				if (DEBUG_ENABLED)
					debug_log('%s::connect(): Config settings, DN [%s], PASS [%s]',80,
						get_class($this),$this->connections[$connect_id]['login_dn'],
						$this->connections[$connect_id]['login_pass'] ? md5($this->connections[$connect_id]['login_pass']) : '');
 			}

		} else {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): This IS some other login',80,get_class($this));

			# Did we pass the dn/pass to this function?
			if ($dn) {
				$this->connections[$connect_id]['login_dn'] = $dn;
				$this->connections[$connect_id]['login_pass'] = $pass;

			# Was this an anonyous bind (the cookie stores 0 if so)?
			} elseif ($this->getLoggedInDN() == 'anonymous') {
				$connect_id = 'anonymous';
				$this->connections[$connect_id]['login_dn'] = null;
				$this->connections[$connect_id]['login_pass'] = null;

 			} else {
				$this->connections[$connect_id]['login_dn'] = $this->getLoggedInDN();
				$this->connections[$connect_id]['login_pass'] = $this->getLoggedInPass();
			}
		}

		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Config settings, DN [%s], PASS [%s]',80,
				get_class($this),$this->connections[$connect_id]['login_dn'],
				$this->connections[$connect_id]['login_pass'] ? md5($this->connections[$connect_id]['login_pass']) : '');


		if ($connect_id != 'anonymous' && ! $this->connections[$connect_id]['login_dn'] && ! $this->connections[$connect_id]['login_pass']) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): We dont have enough auth info for server [%s]',80,get_class($this),$this->server_id);

			return false;
		}

		# Now that we have worked out the connect_id, lets just check and see if we have already connected.
		$resource = $this->_connect($connect_id);
		if (is_resource($resource) && ! $reconnect)
			return $resource;

		run_hook('pre_connect',array('server_id'=>$this->server_id,'connect_id'=>$connect_id));

		if ($this->port)
			$resource = @ldap_connect($this->host,$this->port);
		else
			$resource = @ldap_connect($this->host);

		if (DEBUG_ENABLED)
			debug_log('%s::connect(): LDAP Resource [%s], Host [%s], Port [%s]',16,
				get_class($this),$resource,$this->host,$this->port);

		# Go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
		@ldap_set_option($resource,LDAP_OPT_PROTOCOL_VERSION,3);

		/* Disabling this makes it possible to browse the tree for Active Directory, and seems
		   to not affect other LDAP servers (tested with OpenLDAP) as phpLDAPadmin explicitly
		   specifies deref behavior for each ldap_search operation. */
		@ldap_set_option($resource,LDAP_OPT_REFERRALS,0);

		# Try to fire up TLS is specified in the config
		if ($this->isTLSEnabled()) {
			function_exists('ldap_start_tls') or pla_error(_('Your PHP install does not support TLS.'));
			@ldap_start_tls($resource) or pla_error(_('Could not start TLS. Please check your LDAP server configuration.'),ldap_error($resource));
		}

		$bind_result = @ldap_bind($resource,$this->connections[$connect_id]['login_dn'],
			$this->connections[$connect_id]['login_pass']);

		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Resource [%s], Bind Result [%s]',16,get_class($this),$resource,$bind_result);

		if (! $bind_result) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): Leaving with FALSE, bind FAILed',16,get_class($this));

			if ($process_error) {
				switch (ldap_errno($resource)) {
					case 0x31:
						pla_error(_('Bad username or password. Please try again.'));
						break;
					case 0x32:
						pla_error(_('Insufficient access rights.'));
						break;
					case -1:
						pla_error(sprintf(_('Could not connect to "%s" on port "%s"'),$this->host,$this->port));
						break;
					default:
						pla_error(_('Could not bind to the LDAP server.'),ldap_err2str($resource),$resource);
 				}

 			} else {
				return false;
 			}
 		}

		if (is_resource($resource) && ($bind_result)) {
			if (DEBUG_ENABLED)
				debug_log('%s::connect(): Bind successful',16,get_class($this));

			$this->connections[$connect_id]['resource'] = $resource;
		}

		if (DEBUG_ENABLED)
			debug_log('%s::connect(): Leaving with Connect [%s], Resource [%s]',16,
				get_class($this),$connect_id,$this->connections[$connect_id]['resource']);

 		return $this->connections[$connect_id]['resource'];
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
			debug_log('%s::getBaseDN(): Entered with ()',17,get_class($this));

		global $ldapservers;

		# Return the cached entry if we've been here before.
		if (! is_null($this->_baseDN)) {
			debug_log('%s::getBaseDN(): Return CACHED BaseDN [%s]',17,get_class($this),implode('|',$this->_baseDN));
			return $this->_baseDN;
		}

		if (DEBUG_ENABLED)
			debug_log('%s::getBaseDN(): Checking config for BaseDN',80,get_class($this));

		# If the base is set in the configuration file, then just return that.
		if (count($ldapservers->GetValue($this->server_id,'server','base')) > 0) {
			$this->_baseDN = $ldapservers->GetValue($this->server_id,'server','base');

			if (DEBUG_ENABLED)
				debug_log('%s::getBaseDN(): Return BaseDN from Config [%s]',17,get_class($this),implode('|',$this->_baseDN));

			return $this->_baseDN;

		# We need to figure it out.
		} else {
			if (DEBUG_ENABLED)
				debug_log('%s::getBaseDN(): Connect to LDAP to find BaseDN',80,get_class($this));

			if ($this->connect()) {
				$r = $this->search(null,'','objectClass=*',array('namingContexts'),'base');
				$r = array_pop($r);
				if (is_array($r))
						$r = array_change_key_case($r);

				if (isset($r['namingcontexts'])) {
					if (! is_array($r['namingcontexts']))
						$r['namingcontexts'] = array($r['namingcontexts']);

					if (DEBUG_ENABLED)
						debug_log('%s::getBaseDN(): LDAP Entries:%s',80,get_class($this),implode('|',$r['namingcontexts']));

					$this->_baseDN = $r['namingcontexts'];

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
	 * <code>
	 *	$config->custom->appearance['anonymous_bind_implies_read_only'] = true;
	 * </code>
	 * then this also returns true. Servers can be configured read-only in
	 * config.php thus:
	 * <code>
	 *	$ldapservers->SetValue($i,'server','read_only',false);
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

		elseif ($this->getLoggedInDN() === 'anonymous' &&
			($config->GetValue('appearance','anonymous_bind_implies_read_only') === true))

			$return = true;

		if (DEBUG_ENABLED)
			debug_log('%s::isReadOnly(): Entered with (), Returning (%s)',17,get_class($this),$return);

		return $return;
	}

	/**
	 * Returns true if the user has configured the specified server to enable mass deletion.
	 *
	 * Mass deletion is enabled in config.php this:
	 * <code>
	 *	$config->custom->appearance['mass_delete'] = true;
	 * </code>
	 * Notice that mass deletes are not enabled on a per-server basis, but this
	 * function checks that the server is not in a read-only state as well.
	 *
	 * @return bool
	 */
	function isMassDeleteEnabled() {
		if (DEBUG_ENABLED)
			debug_log('%s::isMassDeleteEnabled(): Entered with ()',17,get_class($this));

		global $config;

		if ($this->connect(false) && $this->haveAuthInfo() && ! $this->isReadOnly() &&
			! $config->GetValue('appearance','tree_plm') && 
			$config->GetValue('appearance','mass_delete') === true)

			return true;

		else
			return false;
	}

	/**
	 * Gets whether the admin has configured phpLDAPadmin to show the "Create New" link in the tree viewer.
	 * <code>
	 *	$ldapservers->SetValue($i,'appearance','show_create','true|false');
	 * </code>
	 * If NOT set, then default to show the Create New item.
	 * If IS set, then return the value (it should be true or false).
	 *
	 * @default true
	 * @return bool True if the feature is enabled and false otherwise.
	 */
	function isShowCreateEnabled() {
		if (DEBUG_ENABLED)
			debug_log('%s::isShowCreateEnabled(): Entered with ()',17,get_class($this));

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
			debug_log('%s::isLowBandwidth(): Entered with ()',17,get_class($this));

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
			debug_log('%s::isVisible(): Entered with (), Returning (%s)',17,get_class($this),$return);

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
			debug_log('%s::getSchemaDN(): Entered with (%s)',25,get_class($this),$dn);

		# If we already got the SchemaDN, then return it.
		if ($this->_schemaDN)
			return $this->_schemaDN;

		if (! $this->connect())
			return false;

		$search = @ldap_read($this->connect(),$dn,'objectClass=*',array('subschemaSubentry'));

		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Search returned (%s)',24,get_class($this),is_resource($search));

		# Fix for broken ldap.conf configuration.
		if (! $search && ! $dn) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Trying to find the DN for "broken" ldap.conf',80,get_class($this));

			if (isset($this->_baseDN)) {
				foreach ($this->_baseDN as $base) {
					$search = @ldap_read($this->connect(),$base,'objectClass=*',array('subschemaSubentry'));

					if (DEBUG_ENABLED)
						debug_log('%s::getSchemaDN(): Search returned (%s) for base (%s)',24,
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
				debug_log('%s::getSchemaDN(): Search returned 0 entries. Returning NULL',25,get_class($this));

			return null;
		}

		$entries = @ldap_get_entries($this->connect(),$search);

		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Search returned [%s]',24,get_class($this),$entries);

		if (! $entries || ! is_array($entries))
			return null;

		$entry = isset($entries[0]) ? $entries[0] : false;
		if (! $entry) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Entry is false, Returning NULL',80,get_class($this));

			return null;
		}

		$sub_schema_sub_entry = isset($entry[0]) ? $entry[0] : false;
		if (! $sub_schema_sub_entry) {
			if (DEBUG_ENABLED)
				debug_log('%s::getSchemaDN(): Sub Entry is false, Returning NULL',80,get_class($this));

			return null;
		}

		$this->_schemaDN = isset($entry[$sub_schema_sub_entry][0]) ? $entry[$sub_schema_sub_entry][0] : false;

		if (DEBUG_ENABLED)
			debug_log('%s::getSchemaDN(): Returning (%s)',25,get_class($this),$this->_schemaDN);

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
	 *	Array (
	 *		[0] => "(1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
	 *		[1] => "(1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
	 *	etc.
	 */
	function getRawSchema($schema_to_fetch,$dn='') {
		if (DEBUG_ENABLED)
			debug_log('%s::getRawSchema(): Entered with (%s,%s)',25,get_class($this),$schema_to_fetch,$dn);

		$valid_schema_to_fetch = array('objectclasses','attributetypes','ldapsyntaxes','matchingrules','matchingruleuse');

		if (! $this->connect())
			return false;

		# error checking
		$schema_to_fetch = strtolower($schema_to_fetch);

		if (!is_null($this->_schema_entries) && isset($this->_schema_entries[$schema_to_fetch])) {
			$schema = $this->_schema_entries[$schema_to_fetch];

			if (DEBUG_ENABLED)
				debug_log('%s::getRawSchema(): Returning CACHED (%s)',25,get_class($this),$schema);

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
				debug_log('%s::getRawSchema(): Using Schema DN (%s)',24,get_class($this),$schema_dn);

			foreach (array('(objectClass=*)','(objectClass=subschema)') as $schema_filter) {
				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Looking for schema with Filter (%s)',24,get_class($this),$schema_filter);

				$schema_search = @ldap_read($this->connect(),$schema_dn,$schema_filter,array($schema_to_fetch),0,0,0,LDAP_DEREF_ALWAYS);

				if (is_null($schema_search))
					continue;

				$schema_entries = @ldap_get_entries($this->connect(),$schema_search);

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Search returned [%s]',24,get_class($this),$schema_entries);

				if (is_array($schema_entries) && isset($schema_entries['count'])) {
					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Found schema with filter of (%s)',24,get_class($this),$schema_filter);

					break;
				}

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Didnt find schema with filter (%s)',24,get_class($this),$schema_filter);

				unset($schema_entries);
				$schema_search = null;
			}
		}

		/* Second chance: If the DN or Root DSE didn't give us the subschemaSubentry, ie $schema_search
		   is still null, use some common subSchemaSubentry DNs as a work-around. */
		if (is_null($schema_search)) {
			if (DEBUG_ENABLED)
				debug_log('%s::getRawSchema(): Attempting work-arounds for "broken" LDAP servers...',24,get_class($this));

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
						debug_log('%s::getRawSchema(): Attempting [%s] (%s) (%s)<BR>',24,
							get_class($this),$ldap_server_name,$ldap_dn,$ldap_filter);

					$schema_search = @ldap_read($this->connect(),$ldap_dn,$ldap_filter,
						array($schema_to_fetch), 0, 0, 0, LDAP_DEREF_ALWAYS);
					if (is_null($schema_search))
						continue;

					$schema_entries = @ldap_get_entries($this->connect(),$schema_search);

					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Search returned [%s]',24,get_class($this),$schema_entries);

					if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
						if (DEBUG_ENABLED)
							debug_log('%s::getRawSchema(): Found schema with filter of (%s)',24,get_class($this),$ldap_filter);

						break;
					}

					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Didnt find schema with filter (%s)',24,get_class($this),$ldap_filter);

					unset($schema_entries);
					$schema_search = null;
				}
				if ($schema_search)
					break;
			}
		}

		if (is_null($schema_search)) {
			/* Still cant find the schema, try with the RootDSE
			   Attempt to pull schema from Root DSE with scope "base", or
			   Attempt to pull schema from Root DSE with scope "one" (work-around for Isode M-Vault X.500/LDAP) */
			foreach (array('base','one') as $ldap_scope) {
				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Attempting to find schema with scope (%s), filter (objectClass=*) and a blank base.',24,
						get_class($this),$ldap_scope);

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
					debug_log('%s::getRawSchema(): Search returned [%s]',24,get_class($this),$schema_entries);

				if ($schema_entries && isset($schema_entries[0][$schema_to_fetch])) {
					if (DEBUG_ENABLED)
						debug_log('%s::getRawSchema(): Found schema with filter of (%s)',24,get_class($this),'(objectClass=*)');

					break;
				}

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Didnt find schema with filter (%s)',24,get_class($this),'(objectClass=*)');

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
					debug_log('%s::getRawSchema(): Returning because schema_search is NULL (%s)',25,get_class($this),$return);

				return $return;
			}
		}

		# Did we get something unrecognizable?
		if (gettype($schema_search) != 'resource') {
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" has return UNEXPECTED results.<br /><br /><small>(We expected a "resource" for $schema_search, instead, we got (%s))</small><br /><br />%s<br /><br />Dump of $schema_search:<hr /><pre><small>%s</small></pre>',
					$schema_to_fetch,gettype($schema_search),$schema_error_message,serialize($schema_search)));

			} else {
				$return = false;

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Returning because schema_search type is not a resource (%s)',25,
						get_class($this),$return);

				return $return;
			}
		}

		if (! $schema_entries) {
			$return = false;
			if (DEBUG_ENABLED)
				debug_log('%s::getRawSchema(): Returning false since ldap_get_entries() returned false.',25,
					get_class($this),$return);

			return $return;
		}

		if(! isset($schema_entries[0][$schema_to_fetch])) {
			if (in_array($schema_to_fetch,$schema_error_message_array)) {
				pla_error(sprintf('Our attempts to find your SCHEMA for "%s" has return UNEXPECTED results.<br /><br /><small>(We expected a "%s" in the $schema array but it wasnt there.)</small><br /><br />%s<br /><br />Dump of $schema_search:<hr /><pre><small>%s</small></pre>',
					$schema_to_fetch,gettype($schema_search),$schema_error_message,serialize($schema_entries)));

			} else {
				$return = false;

				if (DEBUG_ENABLED)
					debug_log('%s::getRawSchema(): Returning because (%s) isnt in the schema array. (%s)',25,
						get_class($this),$schema_to_fetch,$return);

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
			debug_log('%s::getRawSchema(): Returning (%s)',25,get_class($this),$schema);

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
			debug_log('%s::isLoginAttrEnabled(): Entered with (), Returning (%s)',17,get_class($this),$return);

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
			debug_log('%s::isLoginStringEnabled(): login_attr is [%s]',80,get_class($this),$this->login_attr);

		if (! strcasecmp($this->login_attr,'string'))
			$return = true;
		else
			$return = false;

		if (DEBUG_ENABLED)
			debug_log('%s::isLoginStringEnabled(): Entered with (), Returning (%s)',17,get_class($this),$return);

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
			debug_log('%s::getLoginString(): Entered with ()',17,get_class($this));

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
			debug_log('%s::isAnonBindAllowed(): Entered with (), Returning (%s)',17,get_class($this),$return);

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
			debug_log('%s::isTLSEnabled(): Entered with ()',17,get_class($this));

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
		debug_log('%s::isBranchRenameEnabled(): Entered with (), Returning (%s).',17,
			get_class($this),$this->branch_rename);

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
		debug_log('%s::SchemaObjectClasses(): Entered with (%s)',25,get_class($this),$dn);

		# Set default return
		$return = null;

		if ($return = get_cached_item($this->server_id,'schema','objectclasses')) {
			debug_log('%s::SchemaObjectClasses(): Returning CACHED [%s] (%s)',25,
				get_class($this),$this->server_id,'objectclasses');

			return $return;
		}

		$raw_oclasses = $this->getRawSchema('objectclasses',$dn);

		if ($raw_oclasses) {
			# build the array of objectClasses
			$return = array();

			foreach ($raw_oclasses as $class_string) {
				if (is_null($class_string) || ! strlen($class_string))
					continue;

				$object_class = new ObjectClass($class_string,$this);
				$return[strtolower($object_class->getName())] = $object_class;
			}

			# Now go through and reference the parent/child relationships
			foreach ($return as $oclass) {
				foreach ($oclass->getSupClasses() as $parent_name) {
					if (isset($return[strtolower($parent_name)]))
						$return[strtolower($parent_name)]->addChildObjectClass($oclass->getName());
				}
			}

			ksort($return);

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->server_id,'schema','objectclasses',$return);
		}

		debug_log('%s::SchemaObjectClasses(): Returning (%s)',25,get_class($this),$return);
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

		debug_log('%s::getSchemaObjectClass(): Entered with (%s,%s), Returning (%s).',25,
			get_class($this),$oclass_name,$dn,$return);
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

		debug_log('%s::getSchemaAttribute(): Entered with (%s,%s), Returning (%s).',25,
			get_class($this),$attr_name,$dn,$return);
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
		debug_log('%s::SchemaAttributes(): Entered with (%s)',25,get_class($this),$dn);

		# Set default return
		$return = null;

		if ($return = get_cached_item($this->server_id,'schema','attributes')) {
			debug_log('%s::SchemaAttributes(): Returning CACHED [%s] (%s)',25,
				get_class($this),$this->server_id,'attributes');
			return $return;
		}

		$raw_attrs = $this->getRawSchema('attributeTypes',$dn);
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
						if (version_compare(PHP_VERSION,'5.0') > 0 )
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
					while ($i++<100 /** 100 == INFINITY ;) */) {

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

		debug_log('%s::SchemaAttributes(): Returning (%s)',25,get_class($this),$return);
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
			debug_log('%s::MatchingRules(): Returning CACHED [%s] (%s).',25,
				get_class($this),$this->server_id,'matchingrules');
			return $return;
		}

		# build the array of MatchingRule objects
		$raw_matching_rules = $this->getRawSchema('matchingRules',$dn);

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

		debug_log('%s::MatchingRules(): Entered with (%s), Returning (%s).',25,get_class($this),$dn,$return);
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
			debug_log('%s::SchemaSyntaxes(): Returning CACHED [%s] (%s).',25,
				get_class($this),$this->server_id,'syntaxes');
			return $return;
		}

		$raw_syntaxes = $this->getRawSchema('ldapSyntaxes',$dn);

		if ($raw_syntaxes) {
			# build the array of attributes
			$return = array();

			foreach ($raw_syntaxes as $syntax_string) {
				$syntax = new Syntax($syntax_string);
				$key = strtolower(trim($syntax->getOID()));

				if (! $key)
					continue;

				$return[$key] = $syntax;
			}

			ksort($return);

			# cache the schema to prevent multiple schema fetches from LDAP server
			set_cached_item($this->server_id,'schema','syntaxes',$return);
		}

		debug_log('%s::SchemaSyntaxes(): Entered with (%s), Returning (%s).',25,
			get_class($this),$dn,$return);
		return $return;
	}

	/**
	 * Add objects
	 *
	 */
	function add($dn,$entry_array) {
		$result = @ldap_add($this->connect(),dn_escape($dn),$entry_array);

		if ($result) {
			$tree = get_cached_item($this->server_id,'tree');
			$tree['browser'][$dn]['icon'] = get_icon($this,$dn);

			# If this DN is in our miss list, we can remove it now.
			if (isset($tree['misses'][$dn]))
				unset($tree['misses'][$dn]);

			# Update this DN's parent's children list as well.
			$parent = get_container($dn);
			$tree['browser'][$parent]['children'][] = $dn;
			usort($tree['browser'][$parent]['children'],'pla_compare_dns');

			set_cached_item($this->server_id,'tree','null',$tree);
		}

		return $result;
	}

	/**
	 * Modify objects
	 */
	function modify($dn,$update_array) {
		return @ldap_modify($this->connect(),dn_escape($dn),$update_array);
	}

	/**
	 * Modify attributes
	 */
	function attrModify($dn,$update_array) {
		return @ldap_mod_add($this->connect(),dn_escape($dn),$update_array);
	}

	function attrDelete($dn,$update_array) {
		return @ldap_mod_del($this->connect(),dn_escape($dn),$update_array);
	}

	function attrReplace($dn,$update_array) {
		return @ldap_mod_replace($this->connect(),dn_escape($dn),$update_array);
	}

	/**
	 * Delete objects
	 */
	function delete($dn) {
		$result = @ldap_delete($this->connect(),dn_escape($dn));

		if ($result) {
			$tree = get_cached_item($this->server_id,'tree');
			unset($tree['browser'][$dn]);

			# Delete entry from parent's children as well.
			$parent = get_container($dn);

			# If the parent hasnt been opened in the tree, then there wont be any children.
			if (isset($tree['browser'][$parent]['children'])) {
				$index = array_search($dn,$tree['browser'][$parent]['children']);
				unset($tree['browser'][$parent]['children'][$index]);
			}

			# Might be worthwhile adding it to our miss list, while we are here.
			$tree['misses'][$dn] = true;

			set_cached_item($this->server_id,'tree','null',$tree);
		}

		return $result;
	}

	/**
	 * Rename objects
	 *
	 */
	function rename($dn,$new_rdn,$container,$deleteoldrdn) {
		if (! @ldap_rename($this->connect(),$dn,$new_rdn,$container,$deleteoldrdn)) {
			pla_error(_('Could not rename the entry'),$this->error(),$this->errno(),false);

		} else {
			# Update the tree
			$tree = get_cached_item($this->server_id,'tree');
			$newdn = sprintf('%s,%s',$new_rdn,$container);
			$tree['browser'][$newdn] = $tree['browser'][$dn];
			unset($tree['browser'][$dn]);

			# Might be worthwhile adding it to our miss list, while we are here.
			$tree['misses'][$dn] = true;
			if (isset($tree['misses'][$newdn]))
				unset($tree['misses'][$newdn]);

			# Update the parent's children
			$parent = get_container($dn);
			$index = array_search($dn,$tree['browser'][$parent]['children']);
			$tree['browser'][$parent]['children'][$index] = $newdn;

			usort($tree['browser'][$parent]['children'],'pla_compare_dns');
			set_cached_item($this->server_id,'tree','null',$tree);

			return true;
		}
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

	/**
	 * Gets whether an entry exists based on its DN. If the entry exists,
	 * returns true. Otherwise returns false.
	 *
	 * If we are not aware of the dn, and a read results in a hit, then
	 * we'll update the info for the tree.
	 *
	 * @param string $dn The DN of the entry of interest.
	 * @return bool
	 */
	function dnExists($dn) {
		# Set default return
		$return = false;

		$tree = get_cached_item($this->server_id,'tree');

		if (isset($tree['browser'][$dn])) {
			debug_log('%s::dnExists(): Returning CACHED HIT (%s)',17,
				get_class($this),$this->server_id,$dn);
			return true;

		} elseif (isset($tree['misses'][$dn])) {
			debug_log('%s::dnExists(): Returning CACHED MISS (%s)',17,
				get_class($this),$this->server_id,$dn);
			return false;

		# We havent looked for this dn.
		} else {
			if (DEBUG_ENABLED)
				debug_log('%s::dnExists(): Search for (%s) [%s]',16,get_class($this),$this->server_id,$dn);

			$search_result = @ldap_read($this->connect(false),dn_escape($dn),'objectClass=*',array('dn'));

			if ($search_result) {
				$num_entries = ldap_count_entries($this->connect(false),$search_result);

				if ($num_entries > 0) {
					$return = true;
					$tree['browser'][$dn]['icon'] = get_icon($this,$dn);

				} else {
					$return = false;
					$tree['misses'][$dn] = true;
				}

			} else {
				$return = false;
				$tree['misses'][$dn] = true;
			}
			set_cached_item($this->server_id,'tree','null',$tree);
		}

		if (DEBUG_ENABLED)
			debug_log('%s::dnExists(): Entered with (%s,%s), Returning (%s)',17,
				get_class($this),$this->server_id,$dn,$return);

		return $return;
	}

	/**
	 * Gets a list of child entries for an entry. Given a DN, this function fetches the list of DNs of
	 * child entries one level beneath the parent. For example, for the following tree:
	 *
	 * <code>
	 * dc=example,dc=com
	 *   ou=People
	 *      cn=Dave
	 *      cn=Fred
	 *      cn=Joe
	 *      ou=More People
	 *         cn=Mark
	 *         cn=Bob
	 * </code>
	 *
	 * Calling <code>getContainerContents("ou=people,dc=example,dc=com")</code>
	 * would return the following list:
	 *
	 * <code>
	 *  cn=Dave
	 *  cn=Fred
	 *  cn=Joe
	 *  ou=More People
	 * </code>
	 *
	 * @param string $dn The DN of the entry whose children to return.
	 * @param int $size_limit (optional) The maximum number of entries to return.
	 *            If unspecified, no limit is applied to the number of entries in the returned.
	 * @param string $filter (optional) An LDAP filter to apply when fetching children, example: "(objectClass=inetOrgPerson)"
	 * @return array An array of DN strings listing the immediate children of the specified entry.
	 */

	function getContainerContents($dn,$size_limit=0,$filter='(objectClass=*)',$deref=LDAP_DEREF_ALWAYS) {
		$tree = get_cached_item($this->server_id,'tree');

		if (isset($tree['browser'][$dn]['children']) && $filter == '(objectClass=*)')
			if (! isset($tree['browser'][$dn]['size_limited']) || ! $tree['browser'][$dn]['size_limited'])
				return $tree['browser'][$dn]['children'];

		$return = array();
		$search = $this->search(null,dn_escape($dn),$filter,array('dn'),'one',true,$deref,($size_limit > 0 ? $size_limit+1 : $size_limit));

		if (! $search) {
			$tree['browser'][$dn]['children'] = array();

		} else {
			foreach ($search as $searchdn => $entry) {
				$child_dn = dn_unescape($entry['dn']);
				$tree['browser'][$child_dn]['icon'] = get_icon($this,$child_dn);
				$return[] = $child_dn;
			}

			usort($return,'pla_compare_dns');
			$tree['browser'][$dn]['children'] = $return;

			if ($size_limit > 0 && count($tree['browser'][$dn]['children']) > $size_limit)
				$tree['browser'][$dn]['size_limited'] = true;
			else
				if (isset($tree['browser'][$dn]['size_limited']))
					unset($tree['browser'][$dn]['size_limited']);
		}

		set_cached_item($this->server_id,'tree','null',$tree);

		if (DEBUG_ENABLED)
			debug_log('%s::getContainerContents(): Entered with (%s,%s,%s,%s), Returning (%s)',17,
				get_class($this),$dn,$size_limit,$filter,$deref,$return);

		return $tree['browser'][$dn]['children'];
	}

	/**
	 * A handy ldap searching function very similar to PHP's ldap_search() with the
	 * following exceptions: Callers may specify a search scope and the return value
	 * is an array containing the search results rather than an LDAP result resource.
	 *
	 * Example usage:
	 * <code>
	 * $samba_users = $ldapserver->search(null,"ou=People,dc=example,dc=com",
	 *	"(&(objectClass=sambaAccount)(objectClass=posixAccount))",
	 *	array("uid","homeDirectory"));
	 * print_r( $samba_users );
	 *
	 * // prints (for example):
	 * //  Array
	 * //    (
	 * //       [uid=jsmith,ou=People,dc=example,dc=com] => Array
	 * //           (
	 * //               [dn] => "uid=jsmith,ou=People,dc=example,dc=com"
	 * //               [uid] => "jsmith"
	 * //               [homeDirectory] => "\\server\jsmith"
	 * //           )
	 * //       [uid=byoung,ou=People,dc=example,dc=com] => Array
	 * //           (
	 * //               [dn] => "uid=byoung,ou=Samba,ou=People,dc=example,dc=com"
	 * //               [uid] => "byoung"
	 * //               [homeDirectory] => "\\server\byoung"
	 * //           )
	 * //    )
	 * </code>
	 *
	 * WARNING: This function will use a lot of memory on large searches since the entire result set is
	 * stored in a single array. For large searches, you should consider sing the less memory intensive
	 * PHP LDAP API directly (ldap_search(), ldap_next_entry(), ldap_next_attribute(), etc).
	 *
	 * @param resource $resource If an existing LDAP results should be used.
	 * @param string $filter The LDAP filter to use when searching (example: "(objectClass=*)") (see RFC 2254)
	 * @param string $base_dn The DN of the base of search.
	 * @param array $attrs An array of attributes to include in the search result (example: array( "objectClass", "uid", "sn" )).
	 * @param string $scope The LDAP search scope. Must be one of "base", "one", or "sub". Standard LDAP search scope.
	 * @param bool $sort_results Specify false to not sort results by DN or true to have the
	 *  returned array sorted by DN (uses ksort)
	 * @param int $deref When handling aliases or referrals, this specifies whether to follow referrals. Must be one of
	 *  LDAP_DEREF_ALWAYS, LDAP_DEREF_NEVER, LDAP_DEREF_SEARCHING, or LDAP_DEREF_FINDING. See the PHP LDAP API for details.
	 * @param int $size_limit Size limit for search
	 * @todo: Add entries to tree cache.
	 */
	function search($resource=null,$base_dn=null,$filter,$attrs=array(),$scope='sub',$sort_results=false,$deref=LDAP_DEREF_NEVER,$size_limit=0) {
		if (DEBUG_ENABLED)
			debug_log('%s::search(): Entered with (%s,%s,%s,%s,%s,%s,%s)',17,
				get_class($this),is_resource($this),$base_dn,$filter,$attrs,$scope,$sort_results,$deref);

		# If we dont have a resource, we'll connect with default settings
		if (! is_resource($resource))
			$resource = $this->connect(false);

		# If the baseDN is null, we'll just search the first DN.
		if (is_null($base_dn))
			foreach ($this->getBaseDN() as $baseDN) {
				$base_dn = $baseDN;
				break;
			}

		if (DEBUG_ENABLED)
			debug_log('%s::search(): %s search PREPARE.',16,get_class($this),$scope);

		switch ($scope) {
			case 'base':
				$search = @ldap_read($resource,dn_escape($base_dn),$filter,$attrs,0,$size_limit,0,$deref);
				break;

			case 'one':
				$search = @ldap_list($resource,dn_escape($base_dn),$filter,$attrs,0,$size_limit,0,$deref);
				break;

			case 'sub':
			default:
				$search = @ldap_search($resource,dn_escape($base_dn),$filter,$attrs,0,$size_limit,0,$deref);
				break;
		}

		if (DEBUG_ENABLED)
			debug_log('%s::search(): %s search from base [%s] with [%s] for [%s] COMPLETE ().',16,
				get_class($this),$scope,$base_dn,$filter,$attrs,is_null($search));

		if (! $search)
			return array();

		$return = array();

		if ($sort_results && is_array($return))
			if (version_compare(phpversion(),'4.2.0','>='))
				ldap_sort($resource, $search,$sort_results);

		# Get the first entry identifier
		if ($entry_id = ldap_first_entry($resource,$search))

			# Iterate over the entries
			while ($entry_id) {

				# Get the distinguished name of the entry
				$dn = ldap_get_dn($resource,$entry_id);
				$return[$dn]['dn'] = $dn;

				# Get the attributes of the entry
				$attrs = ldap_get_attributes($resource,$entry_id);

				# Get the first attribute of the entry
				if ($attr = ldap_first_attribute($resource,$entry_id,$attrs))

					# Iterate over the attributes
					while ($attr) {
						if ($this->isAttrBinary($attr))
							$values = ldap_get_values_len($resource,$entry_id,$attr);
						else
							$values = ldap_get_values($resource,$entry_id,$attr);

						# Get the number of values for this attribute
						$count = $values['count'];
						unset($values['count']);

						if ($count == 1)
							$return[$dn][$attr] = $values[0];
						else
							$return[$dn][$attr] = $values;

						$attr = ldap_next_attribute($resource,$entry_id,$attrs);
					} # end while attr

				$entry_id = ldap_next_entry($resource,$entry_id);

			} # End while entry_id


		if (DEBUG_ENABLED)
			debug_log('%s::search(): Returning (%s)',17,get_class($this),$return);

		return $return;
	}

	/**
	 * Determines if an attribute's value can contain multiple lines. Attributes that fall
	 * in this multi-line category may be configured in config.php. Hence, this function
	 * accesses the global variable $config->custom->appearance['multi_line_attributes'];
	 *
	 * Usage example:
	 * <code>
	 *  if ($ldapserver->isMultiLineAttr('postalAddress'))
	 *      echo "<textarea name=\"postalAddress\"></textarea>";
	 *  else
	 *      echo "<input name=\"postalAddress\" type=\"text\">";
	 * </code>
	 *
	 * @param string $attr_name The name of the attribute of interestd (case insensivite)
	 * @param string $val (optional) The current value of the attribute (speeds up the
	 *               process by searching for carriage returns already in the attribute value)
	 * @return bool
	 */
	function isMultiLineAttr($attr_name,$val=null) {
		global $config;

		# Set default return
		$return = false;

		# First, check the optional val param for a \n or a \r
		if (! is_null($val) && (strpos($val,"\n") || strpos($val,"\r")))
			$return = true;

		# Next, compare strictly by name first
		else
			foreach ($config->GetValue('appearance','multi_line_attributes') as $multi_line_attr_name)
				if (strcasecmp($multi_line_attr_name,$attr_name) == 0) {
					$return = true;
					break;
				}

		# If unfound, compare by syntax OID
		if (! $return) {
			$schema_attr = $this->getSchemaAttribute($attr_name);

			if ($schema_attr) {
				$syntax_oid = $schema_attr->getSyntaxOID();

				if ($syntax_oid)
					foreach ($config->GetValue('appearance','multi_line_syntax_oids') as $multi_line_syntax_oid)
						if ($multi_line_syntax_oid == $syntax_oid) {
							$return = true;
							break;
						}
			}
		}

		if (DEBUG_ENABLED)
			debug_log('%s:isMultiLineAttr(): Entered with (%s,%s), Returning (%s)',17,
				get_class($this),$attr_name,$val,$return);

		return $return;
	}

	/**
	 * Returns true if the attribute specified is required to take as input a DN.
	 * Some examples include 'distinguishedName', 'member' and 'uniqueMember'.
	 * @param string $attr_name The name of the attribute of interest (case insensitive)
	 * @return bool
	 */
	function isDNAttr($attr_name) {
		if (DEBUG_ENABLED)
			debug_log('%s:isDNAttr(): Entered with (%s)',17,get_class($this),$attr_name);

		# Simple test first
		$dn_attrs = array('aliasedObjectName');
		foreach ($dn_attrs as $dn_attr)
			if (strcasecmp($attr_name,$dn_attr) == 0)
				return true;

		# Now look at the schema OID
		$attr_schema = $this->getSchemaAttribute($attr_name);
		if (! $attr_schema)
			return false;

		$syntax_oid = $attr_schema->getSyntaxOID();
		if ('1.3.6.1.4.1.1466.115.121.1.12' == $syntax_oid)
		 	return true;
		if ('1.3.6.1.4.1.1466.115.121.1.34' == $syntax_oid)
			return true;

		$syntaxes = $this->SchemaSyntaxes();
		if (! isset($syntaxes[$syntax_oid]))
			return false;

		$syntax_desc = $syntaxes[ $syntax_oid ]->getDescription();
		if (strpos(strtolower($syntax_desc),'distinguished name'))
			return true;

		return false;
	}

	/**
	 * Responsible for setting two cookies/session-vars to indicate that a user has logged in,
	 * one for the logged in DN and one for the logged in password.
	 *
	 * This function is only used if 'auth_type' is set to 'cookie' or 'session'. The values
	 * written have the name "pla_login_dn_X" and "pla_login_pass_X" where X is the
	 * ID of the server to which the user is attempting login.
	 *
	 * Note that as with all cookie/session operations this function must be called BEFORE
	 * any output is sent to the browser.
	 *
	 * On success, true is returned. On failure, false is returned.
	 *
	 * @param string $dn The DN with which the user has logged in.
	 * @param string $password The password of the user logged in.
	 * @param bool $anon_bind Indicates that this is an anonymous bind such that
	 *             a password of "0" is stored.
	 * @return bool
	 * @see unsetLoginDN
	 */
	function setLoginDN($dn,$password,$anon_bind) {
		if (DEBUG_ENABLED)
			debug_log('%s:setLoginDN(): Entered with (%s,%s,%s)',17,get_class($this),$dn,$password,$anon_bind);

		if (! $this->auth_type)
			return false;

		switch ($this->auth_type) {
			case 'cookie':
				$cookie_dn_name = sprintf('pla_login_dn_%s',$this->server_id);
				$cookie_pass_name = sprintf('pla_login_pass_%s',$this->server_id);

				# we set the cookie password to 0 for anonymous binds.
				if ($anon_bind) {
					$dn = 'anonymous';
					$password = '0';
				}

				$res1 = pla_set_cookie($cookie_dn_name,pla_blowfish_encrypt($dn));
				$res2 = pla_set_cookie($cookie_pass_name,pla_blowfish_encrypt($password));
				if ($res1 && $res2)
					return true;
				else
					return false;

				break;

			case 'session':
				$sess_var_dn_name = sprintf('pla_login_dn_%s',$this->server_id);
				$sess_var_pass_name = sprintf('pla_login_pass_%s',$this->server_id);

				# we set the cookie password to 0 for anonymous binds.
				if ($anon_bind) {
					$dn = 'anonymous';
					$password = '0';
				}

				$_SESSION[$sess_var_dn_name] = pla_blowfish_encrypt($dn);
				$_SESSION[$sess_var_pass_name] = pla_blowfish_encrypt($password);
				return true;

				break;

			default:
				pla_error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($this->auth_type)));
				break;
		}
	}

	/**
	 * Log a user out of the LDAP server.
	 *
	 * Removes the cookies/session-vars set by setLoginDN()
	 * after a user logs out using "auth_type" of "session" or "cookie".
	 * Returns true on success, false on failure.
	 *
	 * @return bool True on success, false on failure.
	 * @see setLoginDN
	 */
	function unsetLoginDN() {
		if (DEBUG_ENABLED)
			debug_log('%s:unsetLoginDN(): Entered with ()',17,get_class($this));

		if (! $this->auth_type)
			return false;

		switch ($this->auth_type) {
			case 'cookie':
				$logged_in_dn = $this->getLoggedInDN();
				if (! $logged_in_dn)
					return false;

				$logged_in_pass = $this->getLoggedInPass();
				$anon_bind = $logged_in_dn == 'anonymous' ? true : false;

				# set cookie with expire time already passed to erase cookie from client
				$expire = time()-3600;
				$cookie_dn_name = sprintf('pla_login_dn_%s',$this->server_id);
				$cookie_pass_name = sprintf('pla_login_pass_%s',$this->server_id);

				if ($anon_bind) {
					$res1 = pla_set_cookie($cookie_dn_name,'anonymous',$expire);
					$res2 = pla_set_cookie($cookie_pass_name,'0',$expire);

				} else {
					$res1 = pla_set_cookie($cookie_dn_name,pla_blowfish_encrypt($logged_in_dn),$expire);
					$res2 = pla_set_cookie($cookie_pass_name,pla_blowfish_encrypt($logged_in_pass),$expire);
				}

				# Need to unset the cookies too, since they are still set if further processing occurs (eg: Timeout)
				unset($_COOKIE[$cookie_dn_name]);
				unset($_COOKIE[$cookie_pass_name]);

				if (! $res1 || ! $res2)
					return false;
				else
					return true;

				break;

			case 'session':
				# unset session variables
				$session_var_dn_name = sprintf('pla_login_dn_%s',$this->server_id);
				$session_var_pass_name = sprintf('pla_login_pass_%s',$this->server_id);

				if (array_key_exists($session_var_dn_name,$_SESSION))
					unset($_SESSION[$session_var_dn_name]);

				if (array_key_exists($session_var_pass_name,$_SESSION))
					unset($_SESSION[$session_var_pass_name]);

				return true;

				break;

			default:
				pla_error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($auth_type)));
				break;
		}
	}

	/**
	 * Used to determine if the specified attribute is indeed a jpegPhoto. If the
	 * specified attribute is one that houses jpeg data, true is returned. Otherwise
	 * this function returns false.
	 *
	 * @param string $attr_name The name of the attribute to test.
	 * @return bool
	 * @see draw_jpeg_photos
	 */
	function isJpegPhoto($attr_name) {
		if (DEBUG_ENABLED)
			debug_log('%s:isJpegPhoto(): Entered with (%s)',17,get_class($this),$attr_name);

		# easy quick check
		if (! strcasecmp($attr_name,'jpegPhoto') || ! strcasecmp($attr_name,'photo'))
			return true;

		# go to the schema and get the Syntax OID
		$schema_attr = $this->getSchemaAttribute($attr_name);
		if (! $schema_attr)
			return false;

		$oid = $schema_attr->getSyntaxOID();
		$type = $schema_attr->getType();

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
	 * @return bool
	 */
	function isAttrBoolean($attr_name) {
		if (DEBUG_ENABLED)
			debug_log('%s:isAttrBoolean(): Entered with (%s)',17,get_class($this),$attr_name);

		$type = ($schema_attr = $this->getSchemaAttribute($attr_name)) ? $schema_attr->getType() : null;

		if (! strcasecmp('boolean',$type ) ||
			! strcasecmp('isCriticalSystemObject',$attr_name) ||
			! strcasecmp('showInAdvancedViewOnly',$attr_name))
			return true;

		else
			return false;
	}

	/**
	 * Given an attribute name and server ID number, this function returns
	 * whether the attrbiute may contain binary data. This is useful for
	 * developers who wish to display the contents of an arbitrary attribute
	 * but don't want to dump binary data on the page.
	 *
	 * @param string $attr_name The name of the attribute to test.
	 * @return bool
	 *
	 * @see isJpegPhoto
	 */
	function isAttrBinary($attr_name) {
		if (DEBUG_ENABLED)
			debug_log('%s:isAttrBinary(): Entered with (%s)',17,get_class($this),$attr_name);

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

		if (isset($attr_cache[$this->server_id][$attr_name]))
			return $attr_cache[$this->server_id][$attr_name];

		if ($attr_name == 'userpassword') {
			$attr_cache[$this->server_id][$attr_name] = false;
			return false;
		}

		# Quick check: If the attr name ends in ";binary", then it's binary.
		if (strcasecmp(substr($attr_name,strlen($attr_name) - 7),';binary') == 0) {
			$attr_cache[$this->server_id][$attr_name] = true;
			return true;
		}

		# See what the server schema says about this attribute
		$schema_attr = $this->getSchemaAttribute($attr_name);
		if (! $schema_attr) {

			/* Strangely, some attributeTypes may not show up in the server
			   schema. This behavior has been observed in MS Active Directory.*/
			$type = null;
			$syntax = null;

		} else {
			$type = $schema_attr->getType();
			$syntax = $schema_attr->getSyntaxOID();
		}

		if (strcasecmp($type,'Certificate') == 0 ||
			strcasecmp($type,'Binary') == 0 ||
			strcasecmp($attr_name,'usercertificate') == 0 ||
			strcasecmp($attr_name,'usersmimecertificate') == 0 ||
			strcasecmp($attr_name,'networkaddress') == 0 ||
			strcasecmp($attr_name,'objectGUID') == 0 ||
			strcasecmp($attr_name,'objectSID') == 0 ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.10' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.28' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.5' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.8' ||
			$syntax == '1.3.6.1.4.1.1466.115.121.1.9'
		) {

			$attr_cache[$this->server_id][$attr_name] = true;
			return true;

		} else {
			$attr_cache[$this->server_id][$attr_name] = false;
			return false;
		}
	}

	/**
	 * Returns true if the specified attribute is configured as read only
	 * in config.php with the $read_only_attrs array.
	 * Attributes are configured as read-only in config.php thus:
	 * <code>
	 *  $read_only_attrs = array( "objectClass", "givenName" );
	 * </code>
	 *
	 * @param string $attr The name of the attribute to test.
	 * @return bool
	 */
	function isAttrReadOnly($attr) {
		if (DEBUG_ENABLED)
			debug_log('%s:isAttrReadOnly(): Entered with (%s)',17,get_class($this),$attr);

		global $read_only_attrs, $read_only_except_dn;

		$attr = trim($attr);
		if (! $attr)
			return false;

		if (! isset($read_only_attrs))
			return false;

		if (! is_array($read_only_attrs))
			return false;

		# Is the user excluded?
		if (isset($read_only_except_dn) && $this->userIsMember($this->getLoggedInDN(),$read_only_except_dn))
			return false;

		foreach ($read_only_attrs as $attr_name)
			if (strcasecmp($attr,trim($attr_name)) == 0)
				return true;

		return false;
	}

	/**
	 * Returns true if the specified attribute is configured as hidden
	 * in config.php with the $hidden_attrs array or the $hidden_attrs_ro
	 * array.
	 * Attributes are configured as hidden in config.php thus:
	 * <code>
	 *  $hidden_attrs = array( "objectClass", "givenName" );
	 * </code>
	 * or
	 * <code>
	 *  $hidden_attrs_ro = array( "objectClass", "givenName", "shadowWarning",
	 *                     "shadowLastChange", "shadowMax", "shadowFlag",
	 *                     "shadowInactive", "shadowMin", "shadowExpire" );
	 * </code>
	 *
	 * @param string $attr The name of the attribute to test.
	 * @return bool
	 */
	function isAttrHidden($attr) {
		if (DEBUG_ENABLED)
			debug_log('%s:isAttrHidden(): Entered with (%s)',17,get_class($this),$attr);

		global $hidden_attrs, $hidden_attrs_ro, $hidden_except_dn;

		$attr = trim($attr);
		if (! $attr)
			return false;

		if (! isset($hidden_attrs))
			return false;

		if (! is_array($hidden_attrs))
			return false;

		if (! isset($hidden_attrs_ro))
			$hidden_attrs_ro = $hidden_attrs;

		if (! is_array($hidden_attrs_ro))
			$hidden_attrs_ro = $hidden_attrs;

		# Is the user excluded?
		if (isset($hidden_except_dn) && $this->userIsMember($this->getLoggedInDN(),$hidden_except_dn))
			return false;

		if ($this->isReadOnly()) {
			foreach ($hidden_attrs_ro as $attr_name)
				if (strcasecmp($attr,trim($attr_name)) == 0)
					return true;

		} else {
			foreach ($hidden_attrs as $attr_name)
				if (strcasecmp($attr,trim($attr_name)) == 0)
					return true;
		}

		return false;
	}

	/**
	 * Fetches the password of the currently logged in user (for auth_types "cookie" and "session" only)
	 * or false if the current login is anonymous.
	 *
	 * @return string
	 * @see have_auth_info
	 * @see getLoggedInDN
	 */
	function getLoggedInPass() {
		if (DEBUG_ENABLED)
			debug_log('%s::getLoggedInPass(): Entered with ()',17,get_class($this));

		if (! $this->auth_type)
			return false;

		switch ($this->auth_type) {
			case 'cookie':
				$cookie_name = sprintf('pla_login_pass_%s',$this->server_id);
				$pass = isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : false;

				if ($pass == '0')
					return null;
				else
					return pla_blowfish_decrypt($pass);

				break;

			case 'session':
				$session_var_name = sprintf('pla_login_pass_%s',$this->server_id);
				$pass = isset($_SESSION[$session_var_name]) ? $_SESSION[$session_var_name] : false;

				if ($pass == '0')
					return null;
				else
					return pla_blowfish_decrypt($pass);
				break;

			case 'config':
				return $this->login_pass;
				break;

			default:
				pla_error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($this->auth_type)));
		}
	}

	/**
	 * Returns the DN who is logged in currently to the given server, which may
	 * either be a DN or the string 'anonymous'. This applies only for auth_types
	 * "cookie" and "session".
	 *
	 * One place where this function is used is the tree viewer:
	 * After a user logs in, the text "Logged in as: " is displayed under the server
	 * name. This information is retrieved from this function.
	 *
	 * @return string
	 * @see have_auth_info
	 * @see getLoggedInPass
	 */
	function getLoggedInDN() {
		# Set default return
		$return = false;

		if (DEBUG_ENABLED)
			debug_log('%s::getLoggedInDN(): auth_type is [%s]',66,get_class($this),$this->auth_type);

		if ($this->auth_type) {
			switch ($this->auth_type) {
				case 'cookie':
					$cookie_name = sprintf('pla_login_dn_%s',$this->server_id);

					if (isset($_COOKIE[$cookie_name]))
						$return = pla_blowfish_decrypt($_COOKIE[$cookie_name]);
					else
						$return = false;

					break;

				case 'session':
					$session_var_name = sprintf('pla_login_dn_%s',$this->server_id);

					if (isset($_SESSION[$session_var_name]))
						$return = pla_blowfish_decrypt($_SESSION[$session_var_name]);
					else
						$return = false;

					break;

				case 'config':
					$return = $this->login_dn;
					break;

				default:
					pla_error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($auth_type)));
			}
		}

		if (DEBUG_ENABLED)
			debug_log('%s::getLoggedInDN(): Entered with (), Returning (%s)',17,get_class($this),$return);

		return $return;
	}

	/**
	 * Gets the operational attributes for an entry. Given a DN, this function fetches that entry's
	 * operational (ie, system or internal) attributes. These attributes include "createTimeStamp",
	 * "creatorsName", and any other attribute that the LDAP server sets automatically. The returned
	 * associative array is of this form:
	 * <code>
	 *  Array
	 *  (
	 *    [creatorsName] => Array
	 *        (
	 *           [0] => "cn=Admin,dc=example,dc=com"
	 *        )
	 *    [createTimeStamp]=> Array
	 *        (
	 *           [0] => "10401040130"
	 *        )
	 *    [hasSubordinates] => Array
	 *        (
	 *           [0] => "FALSE"
	 *        )
	 *  )
	 * </code>
	 *
	 * @param string $dn The DN of the entry whose interal attributes are desired.
	 * @param int $deref For aliases and referrals, this parameter specifies whether to
	 *            follow references to the referenced DN or to fetch the attributes for
	 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
	 *            options.
	 * @return array An associative array whose keys are attribute names and whose values
	 *              are arrays of values for the aforementioned attribute.
	 */
	function getDNSysAttrs($dn,$deref=LDAP_DEREF_NEVER) {
		if (DEBUG_ENABLED)
			debug_log('%s:getDNSysAttrs(): Entered with (%s,%s)',17,get_class($this),$dn,$deref);

		$attrs = array('creatorsname','createtimestamp','modifiersname',
			'structuralObjectClass','entryUUID','modifytimestamp',
			'subschemaSubentry','hasSubordinates','+');

		$search = $this->search(null,$dn,'(objectClass=*)',$attrs,'base',false,$deref);
		foreach ($search as $dn => $attrs)
			foreach ($attrs as $attr => $value) {
				$return_attrs[$attr][] = $value;
			}

		return $return_attrs;
	}

	/**
	 * Gets the attributes/values of an entry. Returns an associative array whose
	 * keys are attribute value names and whose values are arrays of values for
	 * said attribute. Optionally, callers may specify true for the parameter
	 * $lower_case_attr_names to force all keys in the associate array (attribute
	 * names) to be lower case.
	 *
	 * Sample return value of <code>getDNAttrs( 0, "cn=Bob,ou=pepole,dc=example,dc=com" )</code>
	 *
	 * <code>
	 * Array
	 *  (
	 *   [objectClass] => Array
	 *       (
	 *           [0] => person
	 *           [1] => top
	 *       )
	 *   [cn] => Array
	 *       (
	 *           [0] => Bob
	 *       )
	 *   [sn] => Array
	 *       (
	 *           [0] => Jones
	 *       )
	 *   [dn] => Array
	 *       (
	 *            [0] => cn=Bob,ou=pepole,dc=example,dc=com
	 *       )
	 *  )
	 * </code>
	 *
	 * @param string $dn The distinguished name (DN) of the entry whose attributes/values to fetch.
	 * @param bool $lower_case_attr_names (optional) If true, all keys of the returned associative
	 *              array will be lower case. Otherwise, they will be cased as the LDAP server returns
	 *              them.
	 * @param int $deref For aliases and referrals, this parameter specifies whether to
	 *            follow references to the referenced DN or to fetch the attributes for
	 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
	 *            options.
	 * @return array
	 * @see getDNSysAttrs
	 * @see getDNAttr
	 */
	function getDNAttrs($dn,$lower_case_attr_names=false,$deref=LDAP_DEREF_NEVER) {
		if (DEBUG_ENABLED)
			debug_log('%s:getDNAttrs(): Entered with (%s,%s,%s)',17,
				get_class($this),$dn,$lower_case_attr_names,$deref);

		$attrs = $this->search(null,dn_escape($dn),'(objectClass=*)',array(),'base',false,$deref);
		$attrs = array_pop($attrs);

		if (is_array($attrs)) {
			if ($lower_case_attr_names)
				$attrs = array_change_key_case($attrs);

			ksort($attrs);
		}

		return $attrs;
	}

	/**
	 * Much like getDNAttrs(), but only returns the values for
	 * one attribute of an object. Example calls:
	 *
	 * <code>
	 * print_r( getDNAttr( 0, "cn=Bob,ou=people,dc=example,dc=com", "sn" ) );
	 * // prints:
	 * //  Array
	 * //    (
	 * //       [0] => "Smith"
	 * //    )
	 *
	 * print_r( getDNAttr( 0, "cn=Bob,ou=people,dc=example,dc=com", "objectClass" ) );
	 * // prints:
	 * //  Array
	 * //    (
	 * //       [0] => "top"
	 * //       [1] => "person"
	 * //    )
	 * </code>
	 *
	 * @param string $dn The distinguished name (DN) of the entry whose attributes/values to fetch.
	 * @param string $attr The attribute whose value(s) to return (ie, "objectClass", "cn", "userPassword")
	 * @param bool $lower_case_attr_names (optional) If true, all keys of the returned associative
	 *              array will be lower case. Otherwise, they will be cased as the LDAP server returns
	 *              them.
	 * @param int $deref For aliases and referrals, this parameter specifies whether to
	 *            follow references to the referenced DN or to fetch the attributes for
	 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
	 *            options.
	 * @see getDNAttrs
	 */
	function getDNAttr($dn,$attr,$lower_case_attr_names=false,$deref=LDAP_DEREF_NEVER) {
		if (DEBUG_ENABLED)
			debug_log('%s:getDNAttr(): Entered with (%s,%s,%s,%s)',17,
				get_class($this),$dn,$attr,$lower_case_attr_names,$deref);

		if ($lower_case_attr_names)
			$attr = strtolower($attr);

		$attrs = $this->getDNAttrs($dn,$lower_case_attr_names,$deref);

		if (isset($attrs[$attr]))
			return $attrs[$attr];
		else
			return false;
	}

	/**
	 * Given a DN string, this returns the top container portion of the string.
	 * @param string $dn The DN whose container string to return.
	 * @return string The container
	 * @see get_rdn
	 * @see get_container
	 */
	function getContainerTop($dn) {
		foreach ($this->getBaseDN() as $base_dn) {
			if (preg_match("/${base_dn}$/",$dn)) {
				$return = $base_dn;
				break;
			}
		}

		debug_log('%s:getContainerTop(): Entered with (%s), Returning (%s)',17,get_class($this),$dn,$return);
		return $return;
	}

	/**
	 * Given a DN string and a path like syntax, this returns the parent container portion of the string.
	 * @param string $dn The DN whose container string to return.
	 * @param string $path Either '/', '.' or a series of '../'
	 * @return string The container
	 * @see get_rdn
	 * @see get_container
	 */
	function getContainerParent($container,$path) {
		if (DEBUG_ENABLED)
			debug_log('%s:getContainerParent(): Entered with (%s,%s)',17,get_class($this),$container,$path);

		$top = $this->getContainerTop($container);

		if ($path == '/') {
			return $top;

		} elseif ($path == '.') {
			return $container;

		} else {
			$parenttree = explode('/',$path);

			foreach ($parenttree as $index => $value) {
				if ($value == '..') {
					if (get_container($container))
						$container = get_container($container);

					if ($container == $top)
						break;

				} else {
					break;
				}
			}

			return $container;
		}
	}

	/**
	 * Show friendly attribute.
	 */
	function showFriendlyAttr($attr) {
		if (DEBUG_ENABLED)
			debug_log('%s:showFriendlyAttr(): Entered with (%s)',17,get_class($this),$attr);

		$friendly_attrs = process_friendly_attr_table();

		if (isset($friendly_attrs[strtolower($attr)]))
			$return = $friendly_attrs[strtolower($attr)];
		else
			$return = $attr;

		if (DEBUG_ENABLED)
			debug_log('%s:showFriendlyAttr(): Returning (%s)',17,get_class($this),$return);
		return $return;
	}

	/**
	 * Determins if the specified attribute is contained in the $unique_attrs list
	 * configured in config.php.
	 * @return bool True if the specified attribute is in the $unique_attrs list and false
	 *                  otherwise.
	 */
	function isUniqueAttr($attr_name) {
		if (DEBUG_ENABLED)
			debug_log('%s:isUniqueAttr(): Entered with (%s)',17,get_class($this),$attr_name);

		global $unique_attrs;

		if (isset($unique_attrs) && is_array($unique_attrs))
			foreach ($unique_attrs as $attr)
				if (strcasecmp($attr_name,$attr) == 0)
					return true;

		return false;
	}

	/**
	 * This function will check whether the value for an attribute being changed
	 * is already assigned to another DN.
	 *
	 * Inputs:
	 * @param dn $dn DN that is being changed
	 * @param string $attr_name Attribute being changed
	 * @param string|array $new values New values for the attribute
	 *
	 * Returns the bad value, or null if all values are OK
	 */
	function checkUniqueAttr($dn,$attr_name,$new_value) {
		if (DEBUG_ENABLED)
			debug_log('%s:checkUniqueAttr(): Entered with (%s,%s,%s)',17,
				get_class($this),$dn,$attr_name,count($new_value));

		global $ldapservers;

		# Is this attribute in the unique_attrs list?
		if ($this->isUniqueAttr($attr_name)) {

			$con = $this->connect(false,'unique_attr',false,
				$ldapservers->GetValue($this->server_id,'unique_attrs','dn'),
				$ldapservers->GetValue($this->server_id,'unique_attrs','pass'));

			if (! $con)
				pla_error(sprintf(_('Unable to bind to <b>%s</b> with your with unique_attrs credentials. Please check your configuration file.'),$this->name));

			# Build our search filter to double check each attribute.
			$searchfilter = '(|';

			if (is_array($new_value))
				foreach ($new_value as $val)
					$searchfilter .= sprintf('(%s=%s)',$attr_name,clean_search_vals($val));

			elseif ($new_value)
				$searchfilter .= sprintf('(%s=%s)',$attr_name,clean_search_vals($new_value));

			$searchfilter .= ')';

			# Do we need a sanity check to just in case $new_value was null and hence the search string is bad?
			foreach ($this->getBaseDN() as $base_dn) {

				# Do the search
				$search = $this->search($con,$base_dn,$searchfilter,array('dn',$attr_name),'sub',false,LDAP_DEREF_ALWAYS);

				foreach ($search as $searchdn => $result)

					# If one of the attributes is owned to somebody else, then we may as well die here.
					if ($result['dn'] != $dn)
						if (is_array($result[$attr_name])) {
							foreach ($result[$attr_name] as $attr)
								foreach ($new_value as $new_value_attr)
									if ($new_value_attr == $attr)
										return $attr;

						} else {
							foreach ($new_value as $new_value_attr)
								if ($new_value_attr == $result[$attr_name])
									return $result[$attr_name];
						}
			}

			# If we get here, then it must be OK?
			return;

		} else {
			return;
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
		if (DEBUG_ENABLED)
			debug_log('%s:userIsMember(): Entered with (%s,%s)',17,get_class($this),$user,$group);

		$user = strtolower($user);
		$group = $this->getDNAttrs($group,false,$deref=LDAP_DEREF_NEVER);

		if (is_array($group)) {
			$group = array_change_key_case($group);

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
	}

	/**
	 */
	function userIsAllowedLogin($user) {
		if (DEBUG_ENABLED)
			debug_log('%s:userIsAllowedLogin(): Entered with (%s)',17,get_class($this),$user);

		global $ldapservers;

		$user = trim(strtolower($user));

		if (! $ldapservers->GetValue($this->server_id,'login','allowed_dns'))
			return true;

		foreach ($ldapservers->GetValue($this->server_id,'login','allowed_dns') as $login_allowed_dn) {
			if (DEBUG_ENABLED)
				debug_log('%s:userIsAllowedLogin: Working through (%s)',80,get_class($this),$login_allowed_dn);

			/* Check if $login_allowed_dn is an ldap search filter
			   Is first occurence of 'filter=' (case ensitive) at position 0 ? */
			if (preg_match('/^\([&|]\(/',$login_allowed_dn)) {
				$filter = $login_allowed_dn;

				foreach($this->getBaseDN() as $base_dn) {
					$dn_array = array();

					$results = $this->search(null,$base_dn,$filter,array('dn'));

					if (DEBUG_ENABLED)
						debug_log('%s:userIsAllowedLogin: Search, Filter [%s], BaseDN [%s] Results [%s]',16,
							get_class($this),$filter, $base_dn,$results);

					if ($results) {
						foreach ($results as $result)
							$dn_array[] = $result['dn'];

						$dn_array = array_unique($dn_array);

						if (count($dn_array) !== 0)
							foreach ($dn_array as $result_dn) {
								if (DEBUG_ENABLED)
									debug_log('%s:userIsAllowedLogin: Comparing with [%s]',80,
										get_class($this),$result_dn);

								# Check if $result_dn is a user DN
								if (strcasecmp($user,trim(strtolower($result_dn))) == 0)
									return true;

								# Check if $result_dn is a group DN
								if ($this->userIsMember($user,$result_dn))
									return true;
						}
					}
				}
			}

			# Check if $login_allowed_dn is a user DN
			if (strcasecmp($user,trim(strtolower($login_allowed_dn))) == 0)
				return true;

			# Check if $login_allowed_dn is a group DN
			if ( $this->userIsMember($user,$login_allowed_dn) )
				return true;
		}
		return false;
	}

	/**
	 * Get the LDAP base DN for a named DN.
	 *
	 * @param string $dn DN in question
	 * @return string $base_dn
	 */
	function getDNBase($dn) {
		foreach ($this->getBaseDN() as $base_dn) {
			if (preg_match('/'.$base_dn.'$/',$dn))
				return $base_dn;
		}

		return null;
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

		/* This was created for IDS - since it doesnt present STRUCTURAL against objectClasses
		   definitions when reading the schema.*/
		$this->default->server['schema_oclass_default'] = array(
			'desc'=>'When reading the schema, and it doesnt specify objectClass type, default it to this',
			'var'=>'schema_oclass_default',
			'default'=>null);

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
			debug_log('%s::SetValue(): Entered with (%s,%s,%s,%s)',3,
				get_class($this),$server_id,$key,$index,$value);

		if (! isset($this->default->$key))
			pla_error("ERROR: Setting a key [$key] that isnt predefined.");
		else
			$default = $this->default->$key;

		if (! isset($default[$index]))
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
			debug_log('%s::GetValue(): Entered with (%s,%s,%s), Returning (%s)',3,
				get_class($this),$server_id,$key,$index,$return);

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
