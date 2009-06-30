<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/server_functions.php,v 1.10 2005/04/05 07:46:24 wurley Exp $ */

/**
 * Classes and functions for LDAP server configuration and capability
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * @package phpLDAPadmin
 */
class LDAPServer
{
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
	 * @todo Need some error checking here in case the config is screwed.
	 */
	function LDAPServer($server_id,$ignore=true)
	{
		global $servers, $lang;

		// Other Internal Values
		$this->_baseDN = null;
		$this->_schemaDN = null;

		$this->server_id = $server_id;
		$this->name = isset( $servers[$server_id]['name'] ) ? $servers[$server_id]['name'] : '';
		$this->auth_type = isset( $servers[$server_id]['auth_type'] ) ? $servers[$server_id]['auth_type'] : '';

		if ($this->isValidServer($server_id)) {
			$this->host = isset( $servers[$server_id]['host'] ) ? $servers[$server_id]['host'] : '';
			$this->port = isset( $servers[$server_id]['port'] ) ? $servers[$server_id]['port'] : '';

		} else {

			if (! $ignore)
				pla_error ( $lang['bad_server_id']." ($server_id)",null,-1,true );

			else
				return null;
		}

		$this->login_dn = isset( $servers[$server_id]['login_dn'] ) ? $servers[$server_id]['login_dn'] : false;
		$this->login_pass = isset( $servers[$server_id]['login_pass'] ) ? $servers[$server_id]['login_pass'] : false;
	}

	/**
	 * Checks the specified server id for sanity. Ensures that the server is indeed in the configured
	 * list and active. This is used by many many scripts to ensure that valid server ID values
	 * are passed in POST and GET.
	 *
	 * @param int $server_id the server_id of the LDAP server as defined in config.php
	 * @return bool
	 */
	function isValidServer( $server_id )
	{
		global $servers;

		if( ! is_numeric( $server_id )
			|| ! isset( $servers[$server_id] )
			|| ! isset( $servers[$server_id]['host'] )
			|| trim($servers[$server_id]['host']) == '' )
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
	 * $servers array in config.php is checked to ensure that the user has specified
	 * login information. In any case, if phpLDAPadmin has enough information to login
	 * to the server, true is returned. Otherwise false is returned.
	 *
	 * @return bool
	 * @see get_logged_in_dn
	 */
	function haveAuthInfo()
	{
		global $servers;

		// For session or cookie auth_types, we check the session or cookie to see if a user has logged in.
		if( in_array( $this->auth_type, array( 'session', 'cookie' ) ) ) {

			// we don't look at get_logged_in_pass() cause it may be null for anonymous binds
			// get_logged_in_dn() will never return null if someone is really logged in.
	                if( get_logged_in_dn( $this ) )
				return true;
			else
				return false;
		}

		// whether or not the login_dn or pass is specified, we return
		// true here. (if they are blank, we do an anonymous bind anyway)
		elseif( ! isset( $server['auth_type'] ) || $server['auth_type'] == 'config' ) {
			return true;

		} else {
			global $lang;
			pla_error( sprintf($lang['error_auth_type_config'],htmlspecialchars($server['auth_type'])) );
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
	function connect($process_error=true,$anonymous=false,$reconnect=false,$connect_num=0)
	{
		debug_log(sprintf('%s::connect(): Starting with (%s,%s,%s,%s)',get_class($this),$process_error,$anonymous,$reconnect,$connect_num),8);

		if (isset($this->connections[$connect_num]['resource']) && ! $reconnect) {
			debug_log(sprintf('%s::connect(): Returning CACHED connection resource [%s](%s)',get_class($this),$this->connections[$connect_num]['resource'
],$connect_num),7);
			return $this->connections[$connect_num]['resource'];

		} else {

			debug_log(sprintf('%s::connect(): Creating new connection #[%s] for Server ID [%s]',get_class($this),$connect_num,$this->server_id),9);

			global $servers, $lang;

			if( $anonymous == true ) {
				debug_log(sprintf('%s::connect(): This IS an anonymous login',get_class($this)),9);
				$this->connections[$connect_num]['login_dn'] = null;
				$this->connections[$connect_num]['login_pass'] = null;
			} // grab the auth info based on the auth_type for this server

			elseif( $this->auth_type == 'config' ) {
				debug_log(sprintf('%s::connect(): This IS a "config" login',get_class($this)),9);
				$this->connections[$connect_num]['login_dn'] = $servers[$this->server_id]['login_dn'];
				$this->connections[$connect_num]['login_pass'] = $servers[$this->server_id]['login_pass'];
				$this->connections[$connect_num]['login_dn'] = expand_dn_with_base( $this, $this->connections[$connect_num]['login_dn'], false );
				debug_log(sprintf('%s::connect(): Config settings, DN [%s], PASS [%s]',get_class($this),
					$this->connections[$connect_num]['login_dn'],
					$this->connections[$connect_num]['login_pass'] ? md5($this->connections[$connect_num]['login_pass']) : ''),9);

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

			if( $this->port )
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
	function getBaseDN()
	{
		global $servers;

		debug_log(sprintf('%s::getBaseDN(): Looking for BaseDN',get_class($this)),8);
		// Return the cached entry if we've been here before.
		if (! is_null($this->_baseDN)) {
			debug_log(sprintf('%s::getBaseDN(): Return CACHED BaseDN [%s]',get_class($this),implode('|',$this->_baseDN)),7);
			return $this->_baseDN;
		}

		debug_log(sprintf('%s::getBaseDN(): Checking config for BaseDN',get_class($this)),9);
		// If the base is set in the configuration file, then just return that.
		// @todo Do we need to test if the config array is blank?
		if (isset($servers[$this->server_id]['base']) && $servers[$this->server_id]['base']) {
			$this->_baseDN = is_array($servers[$this->server_id]['base']) ? $servers[$this->server_id]['base'] : array($servers[$this->server_id]['base']);
			debug_log(sprintf('%s::getBaseDN(): Return BaseDN from Config [%s]',get_class($this),implode('|',$this->_baseDN)),4);
			return $this->_baseDN;

		// We need to figure it out.
		} else {

			debug_log(sprintf('%s::getBaseDN(): Connect to LDAP to find BaseDN',get_class($this)),9);
			// Are we connected
			// @todo This bit needs to be more robust.
			if ($this->connect()) {
				$r = @ldap_read( $this->connect(), "", 'objectClass=*', array( 'namingContexts' ) );
				debug_log(sprintf('%s::getBaseDN(): Search Results [%s], Resource [%s], Msg [%s]',get_class($this),$r,$this->connect(),ldap_error($this->connect())),9);
				if( ! $r )
					return array('');

				$r = @ldap_get_entries( $this->connect(false), $r );
				if( isset( $r[0]['namingcontexts'] ) ) {

					// If we have a count key, delete it - dont need it.
					if (isset($r[0]['namingcontexts']['count']))
						unset($r[0]['namingcontexts']['count']);

					debug_log(sprintf('%s::getBaseDN(): LDAP Entries:%s',get_class($this),implode('|',$r[0]['namingcontexts'])),5);
					$this->_baseDN = $r[0]['namingcontexts'];
					// @todo Do we need this session?
					//$_SESSION[ "pla_root_dn_$this->server_id" ] = $this->_baseDN;
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
	function isReadOnly()
	{
		global $servers;

		if( isset( $servers[$this->server_id]['read_only'] ) &&
			$servers[$this->server_id]['read_only'] == true )
				return true;

		global $anonymous_bind_implies_read_only;

		if( "anonymous" == get_logged_in_dn( $this ) &&
			isset( $anonymous_bind_implies_read_only ) &&
			$anonymous_bind_implies_read_only == true )
				return true;

		return false;
	}

	/**
	 * Returns true if the user has configured the specified server to enable mass deletion.
	 *
	 * Mass deletion is enabled in config.php this:
	 * <code>
	 *   $enable_mass_delete = true;
	 * </code>
	 * Notice that mass deletes are not enabled on a per-server basis, but this
	 * function checks that the sever is not in a read-only state as well.
	 *
	 * @return bool
	 */
	function isMassDeleteEnabled()
	{
		global $enable_mass_delete;

		if( $this->connect(false) &&
			$this->haveAuthInfo() &&
			! $this->isReadOnly() &&
			isset( $enable_mass_delete ) &&
			true === $enable_mass_delete )
			return true;
		else
			return false;
	}

	/**
	 * Gets whether the admin has configured phpLDAPadmin to show the "Create New" link in the tree viewer.
	 *
	 * <code>
	 *  $server[$i]['show_create'] = true|false;
	 * </code>
	 * If $servers[$server_id]['show_create'] is NOT set, then default to show the Create New item.
	 * If $servers[$server_id]['show_create'] IS set, then return the value (it should be true or false).
	 *
	 * @return bool True if the feature is enabled and false otherwise.
	 */
	function isShowCreateEnabled()
	{
		global $servers;

		if( isset( $servers[$this->server_id]['show_create'] ))
			return $servers[$this->server_id]['show_create'];
		else
			return true;
	}

	/**
	 * Fetch whether the user has configured a certain server as "low bandwidth".
	 *
	 * Users may choose to configure a server as "low bandwidth" in config.php thus:
	 * <code>
	 *   $servers[$i]['low_bandwidth'] = true;
	 * </code>
	 * @return bool
	 */
	function isLowBandwidth()
	{
		global $servers;

		if( isset( $servers[$this->server_id]['low_bandwidth'] ) && true == $servers[$this->server_id]['low_bandwidth'] )
			return true;
		else
			return false;
	}

	/**
	 * Should this LDAP server be shown in the tree?
	 *
	 * <code>
	 *  $server[$i]['visible'] = true;
	 * </code>
	 * @return bool True if the feature is enabled and false otherwise.
	 */
	function isVisible()
	{
		global $servers;

		$is_visible = ( ! isset( $servers[$this->server_id]['visible'] )
	                || ( $servers[$this->server_id]['visible'] === true ) );

		if( $this->isValidServer($this->server_id) && $is_visible )
			return true;
		else
			return false;
	}

	/**
	 * This function will query the ldap server and request the subSchemaSubEntry which should be the Schema DN.
	 *
	 * If we cant connect to the LDAP server, we'll return false.
	 * If we can connect but cant get the entry, then we'll return null.
	 *
	 * @return array|false Schema if available, null if its not or false if we cant connect.
	 * @param bool $debug Switch to true to see some nice and copious output. :)
	 */
	function getSchemaDN($dn='', $debug = false)
	{
		// If we already got the SchemaDN, then return it.
		if ($this->_schemaDN)
			return $this->_schemaDN;

		if( $debug ) echo "<pre>";

		if (! $this->connect())
			return false;

		$search = @ldap_read( $this->connect(), $dn, 'objectClass=*', array( 'subschemaSubentry' ) );
		if( $debug ) { printf("%s::getSchemaDN(): ldap_read: ",get_class($this)); var_dump( $search ); echo "\n"; }
		if( ! $search ) {
			if( $debug ) printf("%s::getSchemaDN(): returning null. (search result is blank)<BR>",get_class($this));
			return null;
		}

		if( @ldap_count_entries( $this->connect(), $search ) == 0 ) {
			if( $debug ) printf("%s::getSchemaDN(): returning null. (ldap_count_entries() == 0)<BR>",get_class($this));
			return null;
		}

		$entries = @ldap_get_entries( $this->connect(), $search );
		if( $debug ) { echo "Entries (ldap_get_entries): "; var_dump( $entries ); echo "\n"; }
		if( ! $entries || ! is_array( $entries ) ) {
			if( $debug ) printf("%s::getSchemaDN(): returning null. (Bad entries, false or not array)<BR>",get_class($this));
			return null;
		}

		$entry = isset( $entries[0] ) ? $entries[0] : false;
		if( ! $entry ) {
			if( $debug ) printf("%s::getSchemaDN(): returning null. (entry is false)<BR>",get_class($this));
			return null;
		}

		$sub_schema_sub_entry = isset( $entry[0] ) ? $entry[0] : false;
		if( ! $sub_schema_sub_entry ) {
			if( $debug ) printf("%s::getSchemaDN(): returning null. (sub_schema_sub_entry val is false)<BR>",get_class($this));
			return null;
		}

		$this->_schemaDN = isset( $entry[ $sub_schema_sub_entry ][0] ) ?  $entry[ $sub_schema_sub_entry ][0] : false;

		if( $debug ) printf("%s::getSchemaDN(): returning (%s)<BR>",get_class($this),$this->_schemaDN);
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
	function getRawSchema($schema_to_fetch, $dn='')
	{
		global $lang;

		// Swith to true to enable verbose output of schema fetching progress
		$debug = false;

		if (! $this->connect())
			return false;

		// error checking
		$schema_to_fetch = strtolower( $schema_to_fetch );
		$valid_schema_to_fetch = array( 'objectclasses', 'attributetypes', 'ldapsyntaxes',
			'matchingrules', 'matchingruleuse' );

		if( ! in_array( $schema_to_fetch, $valid_schema_to_fetch ) )
		// This error message is not localized as only developers should ever see it
		pla_error( "Bad parameter provided to function to ". get_class($this) ."::getRawSchema(). '"
			. htmlspecialchars( $schema_to_fetch ) . "' is not valid for the schema_to_fetch parameter." );

		// Try to get the schema DN from the specified entry.
		$schema_dn = $this->getSchemaDN($dn,$debug);

		// Do we need to try again with the Root DSE?
		if( ! $schema_dn )
			$schema_dn = $this->getSchemaDN('', $debug);

		// Store the eventual schema retrieval in $schema_search
		$schema_search = null;

		if( $schema_dn ) {
			if( $debug ) { printf("%s::getRawSchema(): Found the Schema DN:<BR>",get_class($this)); var_dump( $schema_dn ); echo "\n"; }
			$schema_search = @ldap_read( $this->connect(), $schema_dn, '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );

			// Were we not able to fetch the schema from the $schema_dn?
			$schema_entries = @ldap_get_entries( $this->connect(), $schema_search );
			if( $schema_search === false ||
				0 == @ldap_count_entries( $this->connect(), $schema_search ) ||
				! isset( $schema_entries[0][$schema_to_fetch] ) ) {

				// Try again with a different filter (some servers require (objectClass=subschema) like M-Vault)
				if( $debug ) printf("%s::getRawSchema(): Did not find the schema with (objectClass=*). Attempting with (objetClass=subschema):<BR>",get_class($this));

				$schema_search = @ldap_read( $this->connect(), $schema_dn, '(objectClass=subschema)',
					array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );

				$schema_entries = @ldap_get_entries( $this->connect(), $schema_search );

		                // Still didn't get it?
				if( $schema_search === false ||
					0 == @ldap_count_entries( $this->connect(), $schema_search ) ||
					! isset( $schema_entries[0][$schema_to_fetch] ) ) {

					if( $debug ) printf("%s::getRawSchema(): Did not find the schema with DN: %s (with objectClass=* nor objectClass=subschema)<BR>",get_class($this),$schema_dn);

					unset( $schema_entries );
					$schema_search = null;
				} else {
					if( $debug ) printf("%s::getRawSchema(): Found the schema at DN: %s (objectClass=subschema)<BR>",get_class($this),$schema_dn);
				}
			} else {
				if( $debug ) printf("%s::getRawSchema(): Found the schema at DN: %s (objectClass=*)<BR>",get_class($this),$schema_dn);
			}
		}

		// Second chance: If the DN or Root DSE didn't give us the subschemaSubentry, ie $schema_search
		// is still null, use some common subSchemaSubentry DNs as a work-around.

		if ($schema_search == null) {
			if( $debug )
				printf("%s::getRawSchema(): Attempting work-arounds for 'broken' LDAP servers...<BR>",get_class($this));

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

		if ($schema_search == null) {
			foreach (array('base','one') as $ldap_scope) {
				if( $debug ) printf("%s::getRawSchema(): Attempting [%s] (%s) (%s) - %s<BR>",get_class($this),'RootDSE','','(objectClass=*)',$ldap_scope);

				if ($ldap_scope == 'base')
					$schema_search = @ldap_read($this->connect(), '', '(objectClass=*)',
						array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );

				else
					$schema_search = @ldap_list($this->connect(), '', '(objectClass=*)',
						array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );

				$schema_entries = @ldap_get_entries( $this->connect(), $schema_search );
				if( ! isset( $schema_entries[0][$schema_to_fetch] ) )
					$schema_search = null;

				else
					break;
			}
		}

		// Shall we just give up?
		if( $schema_search == null ) {
			if( $debug ) printf("%s::getRawSchema(): Returning false since schema_search came back null",get_class($this));
			set_schema_cache_unavailable( $this->server_id, $schema_to_fetch );
			return false;
		}

		// Did we get something unrecognizable?
		if( 'resource' != gettype( $schema_search ) ) {
			if( $debug ) printf("%s::getRawSchema(): Returning false since schema_search is not of type 'resource', here is a dump:",get_class($this));
			if( $debug ) var_dump( $schema_search );
			if( $debug ) echo "</pre>";
			set_schema_cache_unavailable( $this->server_id, $schema_to_fetch );
			return false;
		}

		$schema = @ldap_get_entries( $this->connect(), $schema_search );
		if( $schema == false ) {
			if( $debug ) printf("%s::getRawSchema(): Returning false since ldap_get_entries() returned false.</pre>",get_class($this));
			set_schema_cache_unavailable( $this->server_id, $schema_to_fetch );
			return false;
		}

		if( ! isset( $schema[0][$schema_to_fetch] ) ) {
			if( $debug ) printf("%s::getRawSchema(): Returning false since '%s' isn't in the schema array. Showing schema array:\n",get_class($this),$schema_to_fetch);
			if( $debug ) var_dump( $schema );
			if( $debug ) echo "</pre>";
			set_schema_cache_unavailable( $this->server_id,$schema_to_fetch );
			return false;
		}

		// Make a nice array of this form:
		// Array (
		//    [0] => "( 1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
		//    [1] => "( 1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
		//    etc.
		$schema = $schema[0][$schema_to_fetch];
		unset( $schema['count'] );

		if( $debug ) var_dump( $schema );
		if( $debug ) echo "</pre>";
		return $schema;
	}

	/**
	 * Fetches whether the login_attr feature is enabled for a specified server.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *  $servers[$server_id]['login_attr'] = 'uid';
	 * </code>
	 * By virtue of the fact that the login_attr is not blank and not 'dn', the
	 * feature is configured to be enabled.
	 *
	 * @return bool
	 */
	function isLoginAttrEnabled()
	{
		global $servers;

		if( isset( $servers[$this->server_id]['login_attr'] ) &&
			0 != strcasecmp( $servers[$this->server_id]['login_attr'], "dn" ) &&
			trim( $servers[$this->server_id]['login_attr'] != "" ) )
			return true;
		else
			return false;
	}

	/**
	 * Fetches whether the login_attr feature is enabled for a specified server.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *  $servers[$server_id]['login_attr'] = 'uid';
	 * </code>
	 *
	 * @return bool
	 */
	function isLoginStringEnabled()
	{
		global $servers;

		if( isset( $servers[$this->server_id]['login_attr'] ) &&
			0 == strcasecmp( $servers[$this->server_id]['login_attr'], "string" ) )
			return true;
		else
			return false;
	}

	/**
	 * Fetches the login_attr string if enabled for a specified server.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *  $servers[$server_id]['login_attr'] = 'uid';
	 * </code>
	 *
	 * @return string|false
	 */
	function getLoginString()
	{
		global $servers;

		if( isset( $servers[$this->server_id]['login_string'] ) )
			return $servers[$this->server_id]['login_string'];
		else
			return false;
	}

	/**
	 * Fetch whether the user has configured a certain server login to be non anonymous
	 *
	 * <code>
	 *   $servers[$i]['disable_anon_bind'] = true;
	 * </code>
	 * @return bool
	 */
	function isAnonBindAllowed()
	{
		global $servers;

		// If only_login_allowed_dns is set, then we cant have anonymous.
		if ( isset( $servers[$this->server_id]['only_login_allowed_dns'] )
			&& is_array( $servers[$this->server_id]['only_login_allowed_dns'] )
			&& count($servers[$this->server_id]['only_login_allowed_dns'] ) > 0 )
			return false;

		else
			return ( ! isset( $servers[$this->server_id]['disable_anon_bind'] )
			|| false == $servers[$this->server_id]['disable_anon_bind'] )
			? true
			: false;
	}

	/**
	 * Fetches whether TLS has been configured for use with a certain server.
	 *
	 * Users may configure phpLDAPadmin to use TLS in config,php thus:
	 * <code>
	 *   $servers[$i]['tls'] = true;
	 * </code>
	 * @param int $server_id The ID of the server of interest from config.php.
	 * @return bool
	 */
	function isTLSEnabled()
	{
		global $servers;
		if( isset( $servers[$this->server_id]['tls'] ) && true == $servers[$this->server_id]['tls'] )
			return true;
		else
			return false;
	}

}
?>
