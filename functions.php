<?php

/*
 * functions.php
 * A collection of functions used throughout phpLDAPadmin.
 */

@include 'config.php';

function login_attr_enabled( $server_id )
{
	global $servers;
	if( 	isset( $servers[$server_id]['login_attr'] ) && 
		0 != strcasecmp( $servers[$server_id]['login_attr'], "dn" ) && 
		$servers[$server_id]['login_attr'] != "" )
		return true;
	else
		return false;
}

/* 
 * Returns an HTML-beautified version of a DN.
 */
function pretty_print_dn( $dn )
{
	$dn = pla_explode_dn( $dn );
	if( isset( $dn['count'] ) )
		unset( $dn['count'] );
	foreach( $dn as $i => $element ) {
		$element = htmlspecialchars( $element );
		$element = explode( '=', $element, 2 );
		$element = implode( '<span style="color: blue; font-family: courier; font-weight: bold">=</span>', $element );
		$dn[$i] = $element;
	}
	$dn = implode( '<span style="color:red; font-family:courier; font-weight: bold;">,</span>', $dn );

	return $dn;
}


/*
 * Responsible for setting the cookie to indicate that a user has logged in.
 */
function set_cookie_login_dn( $server_id, $dn, $password, $anon_bind )
{
		// cookie_time comes from config.php
		if( ! check_server_id( $server_id ) )
			return false;
		if( $anon_bind ) {
				// we set the cookie val to 0 for anonymous binds.
				$res1 = pla_set_cookie( "pla_login_dn_$server_id", 'anonymous' );
				$res2 = pla_set_cookie( "pla_login_pass_$server_id", '0' );
		} else {
				$res1 = pla_set_cookie( "pla_login_dn_$server_id", $dn );
				$res2 = pla_set_cookie( "pla_login_pass_$server_id", $password );
		}
		if( ! $res1 || ! $res2 )
			return false;
		else
			return true;
}

/*
 * PLA-only wrapper for setting cookies, which takes into consideration
 * configuration values.
 */
function pla_set_cookie( $name, $val, $expire=null, $dir=null )
{
	if( $expire == null ) {
		global $cookie_time;
		if( ! isset( $cookie_time ) )
				$cookie_time = 0;
		$expire = $cookie_time == 0 ? null : time() + $cookie_time;
	}

	if( $dir == null ) {
		global $_SERVER;
		$dir = dirname( $_SERVER['PHP_SELF'] );
	}

	if( setcookie( $name, $val, $expire, $dir ) ) {
		global $_COOKIE;
		$_COOKIE[ $name ] = $val;
		return true;
	} else {
		return false;
	}
}

/*
 * Responsible for removing a cookie after a user logs out.
 */
function unset_cookie_login_dn( $server_id )
{
	global $_SERVER;
	if( ! check_server_id( $server_id ) )
		return false;
	$logged_in_dn = get_logged_in_dn( $server_id );
	$logged_in_pass = get_logged_in_pass( $server_id );
	$anon_bind = $logged_in_dn == 'anonymous' ? true : false;

	$expire = time()-3600;
	if( $anon_bind ) {
			$res1 = pla_set_cookie( "pla_login_dn_$server_id", 'anonymous', $expire );
			$res2 = pla_set_cookie( "pla_login_pass_$server_id", '0', $expire );
	} else {
			$res1 = pla_set_cookie( "pla_login_dn_$server_id", $logged_in_dn, $expire );
			$res2 = pla_set_cookie( "pla_login_pass_$server_id", $logged_in_pass, $expire );
	}

	if( ! $res1 || ! $res2 )
		return false;
	else
		return true;
}

/*
 * Compares 2 DNs. If they are equivelant, returns 0, otherwise,
 * returns their sorting order (similar to strcmp()).
 * < 0 if dn1 is less than dn2
 * > 0 if dn1 is greater than dn2
 */
function pla_compare_dns( $dn1, $dn2 )
{
	$dn1_parts = pla_explode_dn( $dn1 );
	$dn2_parts = pla_explode_dn( $dn2 );
	assert( is_array( $dn1_parts ) );
	assert( is_array( $dn2_parts ) );
	
	// If they are obviously the same, return immediately
	if( 0 === strcasecmp( $dn1, $dn2 ) )
		return 0;
	
	// If they are obviously different, immediately
	// do a string comparison rather than continuing
	if( count( $dn1_parts ) != count( $dn2_parts ) )
		return strcasecmp( $dn1, $dn2 );

	// Foreach of the "parts" of the DN
	for( $i=0; $i<count( $dn1_parts ); $i++ )
	{
		// dnX_part is of the form: "cn=joe" or "cn = joe" or "dc=example"
		// ie, one part of a multi-part DN.
		$dn1_part = $dn1_parts[$i];
		$dn2_part = $dn2_parts[$i];
		
		// Each "part" consists of two sub-parts:
		//   1. the attribute (ie, "cn" or "o")
		//   2. the value (ie, "joe" or "example")
		$dn1_sub_parts = explode( '=', $dn1_part, 2 );
		$dn2_sub_parts = explode( '=', $dn2_part, 2 );

		$dn1_sub_part_attr = trim( $dn1_sub_parts[0] );
		$dn2_sub_part_attr = trim( $dn2_sub_parts[0] );
		if( 0 != ( $cmp = strcasecmp( $dn1_sub_part_attr, $dn2_sub_part_attr ) ) )
			return $cmp;

		$dn1_sub_part_val = trim( $dn1_sub_parts[1] );
		$dn2_sub_part_val = trim( $dn2_sub_parts[1] );
		if( 0 != ( $cmp = strcasecmp( $dn1_sub_part_val, $dn2_sub_part_val ) ) )
			return $cmp;
	}

	// If none of the foregoing tests failed, we must have finished
	// examining two equivelane DNs.
	return 0;
}

/** 
 * Prunes off anything after the ";" in an attr name
 */
function real_attr_name( $attr_name )
{
	$attr_name = preg_replace( "/;.*$/U", "", $attr_name );
	return $attr_name;
}

/*
 * Returns true if the user has configured the specified
 * server to enable mass deletion
 */
function mass_delete_enabled( $server_id )
{
	global $enable_mass_delete;
	if( check_server_id( $server_id ) && 
		pla_ldap_connect( $server_id ) &&
		have_auth_info( $server_id ) && 
		! is_server_read_only( $server_id ) && 
		isset( $enable_mass_delete ) && 
		true === $enable_mass_delete )
		return true;
	else
		return false;
}

/*
 * Returns true if the user has configured PLA to show
 * helpful hints with the $show_hints setting.
 */
function show_hints()
{
	global $show_hints;
	if( isset( $show_hints ) && $show_hints === true )
		return true;
}

/*
 * For hosts who have 'enable_auto_uid_numbers' set to true, this function will
 * get the next available uidNumber using the host's preferred  mechanism
 * (uidpool or search). The uidpool mechanism uses a user-configured entry in
 * the LDAP server to store the last used uidNumber. This mechanism simply fetches
 * and increments and returns that value. The search mechanism is more complicated
 * and slow. It searches all entries that have uidNumber set, finds the smalles and
 * "fills in the gaps" by incrementing the smallest uidNumber until an unused value
 * is found. Both mechanisms do NOT prevent race conditions or toe-stomping, so
 * care must be taken when actually creating the entry to check that the uidNumber
 * returned here has not been used in the mean time. Note that the two different
 * mechanisms may (will!) return different values as they use different algorithms
 * to arrive at their result. Do not be alarmed if (when!) this is the case.
 */
function get_next_uid_number( $server_id )
{
	global $servers, $lang;
	// Some error checking
	if( ! check_server_id( $server_id ) )
		return false;
	$server_name = isset( $servers[ $server_id ]['name'] ) ?
		$servers[$server_id]['name'] :
		"Server $server_id";
	if( ! isset( $servers[ $server_id ]['enable_auto_uid_numbers'] ) )
		return false;
	if( ! isset( $servers[ $server_id ]['auto_uid_number_mechanism'] ) )
		pla_error( sprintf($lang['auto_update_not_setup'], $server_name));

	// Based on the configured mechanism, go get the next available uidNumber!
	$mechanism = $servers[$server_id]['auto_uid_number_mechanism'];

	//
	// case 1: uidpool mechanism
	//
	if( 0 == strcasecmp( $mechanism, 'uidpool' ) ) {
		if( ! isset( $servers[ $server_id ][ 'auto_uid_number_uid_pool_dn' ] ) )
			pla_error( sprintf( $lang['uidpool_not_set'], $server_name ) );
		$uid_pool_dn = $servers[ $server_id ][ 'auto_uid_number_uid_pool_dn' ];
		if( ! dn_exists( $server_id, $uid_pool_dn ) )
			pla_error( sprintf( $lang['uidpool_not_exist'] , $uid_pool_dn ) );

		$next_uid_number = get_object_attr( $server_id, $uid_pool_dn, 'uidNumber' );
		$next_uid_number = intval( $next_uid_number[ 0 ] );
		$next_uid_number++;

		return $next_uid_number;

	//
	// case 2: search mechanism
	//
	} elseif( 0 == strcasecmp( $mechanism, 'search' ) ) {
		if( ! isset( $servers[ $server_id ][ 'auto_uid_number_search_base' ] ) )
			pla_error( sprintf( $lang['specified_uidpool'] , $server_name ) );
		$base_dn = $servers[ $server_id ][ 'auto_uid_number_search_base' ];
		$filter = "(uidNumber=*)";
		$results = pla_ldap_search( $server_id, $filter, $base_dn, array('uidNumber'));
		// lower-case all the inices so we can access them by name correctly
		foreach( $results as $dn => $attrs )
			foreach( $attrs as $attr => $vals ) {
				unset( $results[$dn][$attr] );
				$results[$dn][strtolower( $attr )] = $vals;
			}

		// construct a list of used uidNumbers
		$uids = array();
		foreach ($results as $result)
			$uids[] = $result['uidnumber'];
		$uids = array_unique( $uids );
		if( count( $uids ) == 0 )
			return false;
		sort( $uids );
		foreach( $uids as $uid )
			$uid_hash[ $uid ] = 1;
		// start with the least existing uidNumber and add 1
		if (isset($servers[$server_id]['auto_uid_number_min'])) {
			$uidNumber = $servers[$server_id]['auto_uid_number_min'];
		} else {
			$uidNumber = intval( $uids[0] ) + 1;
		}
		// this loop terminates as soon as we encounter the next available uidNumber
		while( isset( $uid_hash[ $uidNumber ] ) )
			$uidNumber++;
		return $uidNumber;
	//
	// No other cases allowed. The user has an error in the configuration
	//
	} else {
		pla_error( sprintf( $lang['auto_uid_invalid_value'] , $mechanism) );
	}
}

/*
 * Used to determine if the specified attribute is indeed a jpegPhoto
 */
function is_jpeg_photo( $server_id, $attr_name )
{
	// easy quick check
	if( 0 == strcasecmp( $attr_name, 'jpegPhoto' ) ||
	    0 == strcasecmp( $attr_name, 'photo' ) )
	    return true;

	// go to the schema and get the Syntax OID
	require_once realpath( 'schema_functions.php' );
	$schema_attr = get_schema_attribute( $server_id, $attr_name );
	if( ! $schema_attr )
		return false;

	$oid = $schema_attr->getSyntaxOID();
	$type = $schema_attr->getType();

	if( 0 == strcasecmp( $type, 'JPEG' ) )
		return true;
	if( $oid == '1.3.6.1.4.1.1466.115.121.1.28' )
		return true;

	return false;
}

/*
 * Given an attribute name and server id number, this function returns
 * whether the attrbiute may contain binary data.
 */
function is_attr_binary( $server_id, $attr_name )
{
	@require_once realpath( 'schema_functions.php' );

	if( 0 == strcasecmp( substr( $attr_name, strlen( $attr_name ) - 7 ), ";binary" ) )
		return true;

	$schema_attr = get_schema_attribute( $server_id, $attr_name );
	if( ! $schema_attr )
		return false;

	$type = $schema_attr->getType();
	$syntax = $schema_attr->getSyntaxOID();

	if(	0 == strcasecmp( substr( $attr_name, strlen( $attr_name ) - 7 ), ";binary" ) ||
		0 == strcasecmp( $type, 'Certificate' ) ||
		0 == strcasecmp( $type, 'Binary' ) ||
		0 == strcasecmp( $attr_name, 'networkAddress' ) ||
		0 == strcasecmp( $attr_name, 'userCertificate' ) ||
		0 == strcasecmp( $attr_name, 'userSMIMECertificate' ) ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.10' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.28' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.5' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.8' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.9' )
			return true;
	else
			return false;
}

/*
 * Returns true if the specified server is configured to be displayed
 * in read only mode. If a user has logged in via anonymous bind, and
 * config.php specifies anonymous_bind_implies_read_only as true, then
 * this also returns true.
 */
function is_server_read_only( $server_id )
{
	global $servers;
	if( isset( $servers[$server_id]['read_only'] ) &&
	    $servers[$server_id]['read_only'] == true )
		return true;

	global $anonymous_bind_implies_read_only;

	if( "anonymous" == get_logged_in_dn( $server_id ) &&
	    isset( $anonymous_bind_implies_read_only ) &&
	    $anonymous_bind_implies_read_only == true )
		return true;

	return false;
}

/*
 * Given a DN and server ID, this function reads the DN's objectClasses and
 * determines which icon best represents the entry. The results of this query
 * are cached in a session variable so it is not run *every* time the tree
 * browser changes, just when exposing new DNs that were not displayed
 * previously. That means we can afford a little bit of inefficiency here
 * in favor of coolness. :)
 */
function get_icon( $server_id, $dn )
{
	// fetch and lowercase all the objectClasses in an array
	$object_classes = get_object_attr( $server_id, $dn, 'objectClass', true );

	if( $object_classes === null || $object_classes === false)
		return 'object.png';

	foreach( $object_classes as $i => $class )
		$object_classes[$i] = strtolower( $class );

	$rdn = get_rdn( $dn );

	// Is it a samba NT machine (is sambaAccount and ends with '$')
	if( in_array( 'sambaaccount', $object_classes ) &&
		'$' == $rdn{ strlen($rdn) - 1 } )
		return 'nt_machine.png';
	// Is it a samba user account?
	if( in_array( 'sambaaccount', $object_classes ) )
		return 'nt_user.png';
	// Is it a person or some type of account/user?
	elseif( in_array( 'person', $object_classes ) ||
	    in_array( 'organizationalperson', $object_classes ) ||
	    in_array( 'inetorgperson', $object_classes ) ||
	    in_array( 'account', $object_classes ) ||
   	    in_array( 'posixaccount', $object_classes )  )
		return 'user.png';
	// Is it an organization?
	elseif ( in_array( 'organization', $object_classes ) )
		return 'o.png';
	// Is it an organizational Unit?
	elseif( in_array( 'organizationalunit', $object_classes ) )
		return 'ou.png';
	// Is it a domain controler (dc)
	elseif( in_array( 'dcobject', $object_classes ) ||
		in_array( 'domainrelatedobject', $object_classes ) )
		return 'dc.png';
	elseif( in_array( 'country', $object_classes ) )
		return 'country.png';
	elseif( in_array( 'jammvirtualdomain', $object_classes ) )
		return 'mail.png';
	elseif( in_array( 'locality', $object_classes ) )
		return 'locality.png';
	elseif( in_array( 'posixgroup', $object_classes ) ||
		in_array( 'groupofnames', $object_classes ) )
		return 'ou.png';
	elseif( in_array( 'applicationprocess', $object_classes ) )
		return 'process.png';
	elseif( in_array( 'groupofuniquenames', $object_classes ) )
		return 'uniquegroup.png';
	elseif( in_array( 'iphost', $object_classes ) )
		return 'host.png';
	// Oh well, I don't know what it is. Use a generic icon.
	else
		return 'object.png';
}

/*
 * Does the same thing as get_icon(), but it tries to fetch the icon name from the
 * tree_icons session variable first. If not found, resorts to get_icon() and stores
 * the icon nmae in the tree_icons session before returing the icon.
 */
function get_icon_use_cache( $server_id, $dn )
{
	@session_start();
	if( session_is_registered( 'tree_icons' ) ) {
		global $_SESSION;
		if( isset( $_SESSION['tree_icons'][ $server_id ][ $dn ] ) ) {
			return $_SESSION['tree_icons'][ $server_id ][ $dn ];
		} else {
			$icon = get_icon( $server_id, $dn );
			$_SESSION['tree_icons'][ $server_id ][ $dn ] = $icon;
			return $icon;
		}
	}
}

/*
 * Given a server_id, returns whether or not we have enough information
 * to authenticate against the server. For example, if the user specifies
 * 'cookie' in the config for that server, it checks the $_COOKIE array to
 * see if the cookie username and password is set for the server.
 */
function have_auth_info( $server_id )
{
	global $servers;

	if( ! is_numeric( $server_id ) || ! isset( $servers[$server_id] ) )
		return false;

	$server = $servers[$server_id];

	if( isset( $server['auth_type'] ) && $server['auth_type'] == 'form' ) {
		// we don't look at get_logged_in_pass() cause it may be null for anonymous binds
		// get_logged_in_dn() will never return null if someone is really logged in.
		if( get_logged_in_dn( $server_id ) )
			return true;
		else
			return false;
	}
	// whether or not the login_dn or pass is specified, we return
	// true here. (if they are blank, we do an anonymous bind anyway)
	elseif( ! isset( $server['auth_type'] ) || $server['auth_type'] == 'config' ) {
		return true;
	}
	else
	{
		global $lang;
		pla_error( sprintf( $lang['error_auth_type_config'], htmlspecialchars($server['auth_type'])) );
	}
}

/*
 * Returns the password of the currently logged in DN (auth_type form only)
 * or false if the current login is anonymous.
 */
function get_logged_in_pass( $server_id )
{
	if( ! is_numeric( $server_id ) )
		return false;
	$cookie_name = 'pla_login_pass_' . $server_id;
	global $_COOKIE;
	$pass = isset( $_COOKIE[ $cookie_name ] ) ? $_COOKIE[ $cookie_name ] : false;

	if( $pass == '0' )
		return null;
	else
		return $pass;
}

/*
 * Returns the DN who is logged in currently to the given server, which may 
 * either be a DN or the string 'anonymous'.
 */
function get_logged_in_dn( $server_id )
{
	if( ! is_numeric( $server_id ) )
		return false;
	$cookie_name = 'pla_login_dn_' . $server_id;
	global $_COOKIE;
	if( isset( $_COOKIE[ $cookie_name ] ) ) {
		$dn = $_COOKIE[ $cookie_name ];
	} else {
		return false;
	}

	return $dn;
}

/*
 * Specify a $server_id (0,1,2...) based on the order it appears in config.php.
 * The first is 0, the second is 1, etc. You rarely will need to consult
 * config.php since those values are usually generated dynamically in hrefs.
 */
function pla_ldap_connect( $server_id )
{
	if( ! check_server_id( $server_id ) )
		return false;

	if( ! have_auth_info( $server_id ) )
		return false;

	global $servers;

	// cache the connection, so if we are called multiple
	// times, we don't have to reauthenticate with the LDAP server

	static $conns;
	if( isset( $conns[$server_id] ) && $conns[$server_id] )
		return $conns[$server_id];

	$host = $servers[$server_id]['host'];
	$port = $servers[$server_id]['port'];
	if( ! $port ) $port = 389;

	$conn = @ldap_connect( $host, $port );

	if( ! $conn ) return false;

	// go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
	@ldap_set_option( $conn, LDAP_OPT_PROTOCOL_VERSION, 3 );

	// try to fire up TLS is specified in the config
	if( $servers[ $server_id ][ 'tls' ] == true ) {
		global $lang;
		function_exists( 'ldap_start_tls' ) or pla_error( $lang['php_install_not_supports_tls'] );
		@ldap_start_tls( $conn ) or pla_error( $lang['could_not_start_tls']);
	}

	// grab the auth info based on the auth_type for this server
	if( $servers[ $server_id ][ 'auth_type' ] == 'config' ) {
		$login_dn = $servers[$server_id]['login_dn'];
		$login_pass = $servers[$server_id]['login_pass'];
	} elseif( $servers[ $server_id ][ 'auth_type' ] == 'form' ) {
		$login_dn = get_logged_in_dn( $server_id );
		$login_pass = get_logged_in_pass( $server_id );

		// Was this an anonyous bind (the cookie stores 0 if so)?
		if( 'anonymous' == $login_dn ) {
			$login_dn = null;
			$login_pass = null;
		}
	} else {
		global $lang;
		pla_error( sprintf( $lang['auth_type_not_valid'],
                           htmlspecialchars( $servers[ $server_id ][ 'auth_type' ] )));
	}

	$res = @ldap_bind( $conn, $login_dn, $login_pass );

	if( ! $res ) return false;

	// store the cached connection resource
	$conns[$server_id] = $conn;

	return $conn;
}

/*
 * Returns an array listing the DNs contained by the specified $dn
 */
function get_container_contents( $server_id, $dn, $size_limit=0 )
{
	$con = pla_ldap_connect( $server_id );
	if( ! $con ) return false;

	$search = @ldap_list( $con, $dn, 'objectClass=*', array( 'dn' ), 1, $size_limit );
	if( ! $search )
		return array();
	$search = ldap_get_entries( $con, $search );

	$return = array();
	for( $i=0; $i<$search['count']; $i++ ) {
		$entry = $search[$i];
		$dn = $entry['dn'];
		$return[] = $dn;
	}

	return $return;
}

/*
 * Builds the initial tree that is stored in the session variable 'tree'.
 * Simply returns an array with an entry for each active server in
 * config.php
 */
function build_initial_tree()
{
	global $servers;
	$tree = array();
	foreach( $servers as $id => $server ) {
		if( $server['host'] == '' ) {
			continue;
		}

		$tree[$id] = array();
	}

	return $tree;
}

/*
 * Builds the initial array that stores the icon-lookup for each DN in the tree browser
 */
function build_initial_tree_icons()
{
	global $servers;
	$tree_icons = array();

	// initialize an empty array for each server
	foreach( $servers as $id => $server ) {
		if( $server['host'] == '' )
			continue;
		$tree_icons[ $id ] = array();
		$tree_icons[ $id ][ $server['base'] ] = get_icon( $id, $server['base'] );
	}

	return $tree_icons;
}

function get_entry_system_attrs( $server_id, $dn )
{
	$conn = pla_ldap_connect( $server_id );
	if( ! $conn ) return false;

	$attrs = array( 'creatorsname', 'createtimestamp', 'modifiersname', 
			'structuralObjectClass', 'entryUUID',  'modifytimestamp', 
			'subschemaSubentry', 'hasSubordinates', '+' );
	$search = @ldap_read( $conn, $dn, '(objectClass=*)', $attrs, 0, 0, 0, LDAP_DEREF_ALWAYS );
	if( ! $search )
		return false;
	$entry = ldap_first_entry( $conn, $search );
	if( ! $entry)
	    return false;
	$attrs = ldap_get_attributes( $conn, $entry );
	if( ! $attrs )
		return false;
	if( ! isset( $attrs['count'] ) )
		return false;
	$count = $attrs['count'];
	unset( $attrs['count'] );
	$return_attrs = array();
	for( $i=0; $i<$count; $i++ ) {
		$attr_name = $attrs[$i];
		unset( $attrs[$attr_name]['count'] );
		$return_attrs[$attr_name] = $attrs[$attr_name];
	}
	return $return_attrs;
}

/*
 * Returns the attribute/value pairs for the given $dn on the given
 * $server_id. If the attribute is single valued, it will return
 * a single value for that attribute. Otherwise, it will return an
 * array of values for that attribute. Here's a sample return value:
 *
 * Array
 * (
 *   [objectclass] => Array
 *       (
 *           [0] => organizationalRole
 *           [1] => krb5principal
 *           [2] => kerberosSecurityObject
 *       )
 *   [cn] => Manager
 *   [krbname] => phpldap@EXAMPLE.COM
 *   [dn] => cn=Manager,dc=example,dc=com
 * )
 */
function get_object_attrs( $server_id, $dn, $lower_case_attr_names = false )
{
	$conn = pla_ldap_connect( $server_id );
	if( ! $conn ) return false;

	$search = @ldap_read( $conn, $dn, '(objectClass=*)', array( ), 0, 0, 0, LDAP_DEREF_ALWAYS );

	if( ! $search )
		return false;

	$entry = ldap_first_entry( $conn, $search );

	if( ! $entry )
		return false;
	
	$attrs = ldap_get_attributes( $conn, $entry );

	if( ! $attrs || $attrs['count'] == 0 )
		return false;

	$num_attrs = $attrs['count'];
	unset( $attrs['count'] );

	// strip numerical inices
	for( $i=0; $i<$num_attrs; $i++ )
		unset( $attrs[$i] );

	$return_array = array();
    foreach( $attrs as $attr => $vals ) {
		if( $lower_case_attr_names )
			$attr = strtolower( $attr );
		unset( $vals['count'] );
		$return_array[ $attr ] = $vals;
	}

	ksort( $return_array );

	return $return_array;
}

/*
 * Returns true if the passed string $temp contains all printable
 * ASCII characters. Otherwise (like if it contains binary data),
 * returns false.
 */
function is_printable_str($temp) {
	$len = strlen($temp);
	for ($i=0; $i<$len; $i++) {
		$ascii_val = ord( substr( $temp,$i,1 ) );
		if( $ascii_val < 32 || $ascii_val > 126 )
			return false;
	}

	return true;
}

/*
 * Much like get_object_attrs(), but only returns the entry for
 * one attribute of an object. Again, if the attribute contains
 * multiple values, returns an array of values. Otherwise, it
 * returns the single attribute value.
 * TODO: Don't call get_object_attrs() and filter. Do the actual ldap_read() ourselves (for efficiencey)
 */
function get_object_attr( $server_id, $dn, $attr )
{
	$attr = strtolower( $attr );
	$attrs = get_object_attrs( $server_id, $dn, true );
	if( isset( $attrs[$attr] ) )
		return $attrs[$attr];
	else
		return false;
}

/*
 * A do-it-all ldap_search function. You can even specify the search scope. Other than
 * that, it's pretty much the same as the PHP ldap_search() call, except it returns
 * an array of results, rather than an LDAP result resource.
 *
 * NOTE: This function will use a lot of memory on large searches. You should consider 
 * using the PHP LDAP API directly for large searches (ldap_next_entry(), ldap_next_attribute(), etc)
 */
function pla_ldap_search( $server_id, $filter, $base_dn=null, $attrs=array(), $scope='sub', $sort_results=true )
{
	global $servers;

	if( ! check_server_id( $server_id ) )
		return false;

	if( $base_dn == null )
		$base_dn = $servers[$server_id]['base'];

	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;

	switch( $scope ) {
		case 'base':
			$search = @ldap_read( $ds, $base_dn, $filter, $attrs, 0, 0, 0, LDAP_DEREF_ALWAYS );
			break;
		case 'one':
			$search = @ldap_list( $ds, $base_dn, $filter, $attrs, 0, 0, 0, LDAP_DEREF_ALWAYS );
			break;
		case 'sub':
		default:
			$search = @ldap_search( $ds, $base_dn, $filter, $attrs, 0, 0, 0, LDAP_DEREF_ALWAYS );
			break;
	}

	if( ! $search )
		return array();

	$return = array();
	//get the first entry identifier
	if( $entry_id = ldap_first_entry($ds,$search) )

		//iterate over the entries
		while($entry_id) {

			//get the distinguished name of the entry
			$dn = ldap_get_dn($ds,$entry_id);

			//get the attributes of the entry
			$attrs = ldap_get_attributes($ds,$entry_id);
			$return[$dn]['dn'] = $dn;

			//get the first attribute of the entry
			if($attr = ldap_first_attribute($ds,$entry_id,$attrs))

				//iterate over the attributes
				while($attr){
				  if( is_attr_binary($server_id,$attr))
						$values = ldap_get_values_len($ds,$entry_id,$attr);
					else
						$values = ldap_get_values($ds,$entry_id,$attr);

					//get the number of values for this attribute
					$count = $values['count'];
					unset($values['count']);
					if($count==1)
						$return[$dn][$attr] = $values[0];
					else
						$return[$dn][$attr] = $values;

					$attr = ldap_next_attribute($ds,$entry_id,$attrs);
				}// end while attr

			$entry_id = ldap_next_entry($ds,$entry_id);

		} // end while entry_id

	if( $sort_results && is_array( $return ) )
		ksort( $return );

	return $return;
}

/*
 * Transforms the user-configured lists into arrays and such. This is a little weird, but
 * it takes the comma-separated lists (like the search result attribute list) in config.php
 * and turns them into arrays. Only call this ONCE per script. Any subsequent call will
 * mess up the arrays. This function operates only on global variables defined in config.php.
 */
function process_config()
{
	global $search_result_attributes;
	$search_result_attributes = explode( ",", $search_result_attributes );
	array_walk( $search_result_attributes, "trim_it" );

	global $search_attributes_display;
	$search_attributes_display = explode( ",", $search_attributes_display );
	array_walk( $search_attributes_display, "trim_it" );

	global $search_attributes;
	$search_attributes= explode( ",", $search_attributes);
	array_walk( $search_attributes, "trim_it" );
}

/*
 * Call-by-reference to trim a string. Used to filter empty entries out of the arrays
 * that we generate in process_config().
 */
function trim_it( &$str )
{
	$str = trim($str);
}

/*
 * Checks the server id for sanity. Ensures that the server is indeed in the configured list and active
 */
function check_server_id( $server_id )
{
	global $servers;
	if( ! is_numeric( $server_id ) || ! isset( $servers[$server_id] ) || $servers[$server_id]['host'] == '' )
		return false;
	else
		return true;
}

/*
 * Used to generate a random salt for crypt-style passwords
 * --- added 20021125 by bayu irawan <bayuir@divnet.telkom.co.id> ---
 * --- ammended 20030625 by S C Rigler <srigler@houston.rr.com> ---
 */
function random_salt( $length )
{
        $possible = '0123456789'.
                        'abcdefghijklmnopqrstuvwxyz'.
                        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                        './';
        $str = "";
        mt_srand((double)microtime() * 1000000);
        while( strlen( $str ) < $length )
        {
                $str .= substr( $possible, ( rand() % strlen( $possible ) ), 1 );
        }
	/*
	 * Commented out following line because of problem
	 * with crypt function in update.php
	 * --- 20030625 by S C Rigler <srigler@houston.rr.com> ---
	 */
        //$str = "\$1\$".$str."\$";
        return $str;
}

/*
 * Goes through the user-configured server list and looks for an available server_id,
 * ie one that has specified enough information to login. This is for choosing the
 * server to display in the drop-down box in search.php mostly.
 */
function get_avail_server_id()
{
	global $servers;

	for( $i=0; $i<count($servers); $i++ )
		if( check_server_id( $i ) &&  have_auth_info( $i ) )
			return $i;
	return false;
}

/*
 * Given a DN string, this returns the 'RDN' portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'cn=Manager' (it is really the exact opposite of get_container()).
 */
function get_rdn( $dn, $include_attrs=0 )
{
	if( $dn == null )
		return null;
	$rdn = pla_explode_dn( $dn, $include_attrs );
	if( $rdn['count'] == 0 )
		return null;
	if( ! isset( $rdn[0] ) )
		return null;
	$rdn = $rdn[0];
	return $rdn;
}

/*
 * Given a DN string, this returns the 'container' portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'dc=example,dc=com'.
 */
function get_container( $dn )
{
	$rdn = pla_explode_dn( $dn );
	$container = @$rdn[ 1 ];
	for( $i=2; $i<count($rdn)-1; $i++ )
		$container .= ',' . $rdn[$i];
	return $container;
}

/*
 * This function parses ldap_error_codes.txt and looks up the specified
 * ldap error number, and returns the verbose message defined in that file.
 */
function pla_verbose_error( $err_no )
{
	static $err_codes;
	if( count($err_codes) > 0 ) {
		return $err_codes[ $err_no ];
	} else {
	}

	$err_codes_file = 'ldap_error_codes.txt';

	if( ! file_exists( realpath( $err_codes_file ) ) )
		return false;
	if( ! is_readable( realpath( $err_codes_file ) ) )
		return false;
	if( ! ($f = fopen( realpath( $err_codes_file ), 'r' ) ) )
		return false;

	$contents = fread( $f, filesize( $err_codes_file ) );
	$entries = array();
	preg_match_all( "/0x[A-Fa-f0-9][A-Za-z0-9]\s+[0-9A-Za-z_]+\s+\"[^\"]*\"\n/", $contents, $entries );
	$err_codes = array();
	foreach( $entries[0] as $e )
	{
		$entry = array();
		preg_match( "/(0x[A-Za-z0-9][A-Za-z0-9])\s+([0-9A-Za-z_]+)\s+\"([^\"]*)\"/", $e, $entry );
		$hex_code = $entry[1];
		$title    = $entry[2];
		$desc     = $entry[3];
		$desc     = preg_replace( "/\s+/", " ", $desc );
		$err_codes[ $hex_code ] = array( 'title' => $title, 'desc' => $desc );
	}

	return $err_codes[ $err_no ];
}

/*
 * Spits out an HTML-formatted error string. If you specify the optional
 * parameters, pla_error will lookup the error number and display a
 * verbose message in addition to the message you pass it.
 */
function pla_error( $msg, $ldap_err_msg=null, $ldap_err_no=-1, $fatal=true )
{
	include_once 'header.php';
	global $lang;

	?>
	<center>
	<table class="error"><tr><td class="img"><img src="images/warning.png" /></td>
	<td><center><h2><?php echo $lang['ferror_error'];?></h2></center>
	<?php echo $msg; ?>
	<br />
	<br />
	<?php

	if( $ldap_err_msg )
		echo sprintf($lang['ldap_said'], htmlspecialchars( $ldap_err_msg ));

	if( $ldap_err_no != -1 ) {
		$ldap_err_no = ( '0x' . str_pad( dechex( $ldap_err_no ), 2, 0, STR_PAD_LEFT ) );
		$verbose_error = pla_verbose_error( $ldap_err_no );

		if( $verbose_error ) {
			echo sprintf( $lang['ferror_number'], $ldap_err_no, $verbose_error['title']);
			echo sprintf( $lang['ferror_discription'], $verbose_error['desc']);
		} else {
			echo sprintf($lang['ferror_number_short'], $ldap_err_no);
			echo $lang['ferror_discription_short'];
		}
	}
	?>
	<br />
	<br />
	<center>
	<small>
		<?php echo sprintf($lang['ferror_submit_bug'] , get_href( 'add_bug' ));?>
	</small>
	</center>
	</td></tr></table>
	</center>
	<?php

	if( $fatal )
		die();
}

/*
 * This is our custom error handling function. When a PHP error occurs,
 * php will call this so we can give the user a link to the bug submission
 * page, where they can report it. Whoohoo.
 */
function pla_error_handler( $errno, $errstr, $file, $lineno )
{
	global $lang;

	// error_reporting will be 0 if the error context occurred
	// within a function call with '@' preprended (ie, @ldap_bind() );
	// So, don't report errors if the caller has specifically
	// disabled them with '@'
	if( 0 == ini_get( 'error_reporting' ) || 0 == error_reporting() )
			return;

	$file = basename( $file );
	$caller = basename( $_SERVER['PHP_SELF'] );
	$errtype = "";
	switch( $errno ) {
		case E_ERROR: $errtype = "E_ERROR"; break;
		case E_WARNING: $errtype = "E_WARNING"; break;
		case E_PARSE: $errtype = "E_PARSE"; break;
		case E_NOTICE: $errtype = "E_NOTICE"; break;
		case E_CORE_ERROR: $errtype = "E_CORE_ERROR"; break;
		case E_CORE_WARNING: $errtype = "E_CORE_WARNING"; break;
		case E_COMPILE_ERROR: $errtype = "E_COMPILE_ERROR"; break;
		case E_COMPILE_WARNING: $errtype = "E_COMPILE_WARNING"; break;
		case E_USER_ERROR: $errtype = "E_USER_ERROR"; break;
		case E_USER_WARNING: $errtype = "E_USER_WARNING"; break;
		case E_USER_NOTICE: $errtype = "E_USER_NOTICE"; break;
		case E_ALL: $errtype = "E_ALL"; break;
		default: $errtype = $lang['ferror_unrecognized_num'] . $errno;
	}

	if( $errno == E_NOTICE ) {
		echo sprintf($lang['ferror_nonfatil_bug'], $errstr, $errtype, $file,
                             $lineno, $caller, pla_version(), phpversion(), php_sapi_name(),
                             $_SERVER['SERVER_SOFTWARE'], get_href('add_bug'));
		return;
	}

	pla_error( sprintf($lang['ferror_congrats_found_bug'], $errstr, $errtype, $file, 
							$lineno, basename($_SERVER['PHP_SELF']), pla_version(), 
							phpversion(), php_sapi_name(), $_SERVER['SERVER_SOFTWARE']));
}

/*
 * Reads the friendly_attrs array as defined in config.php and lower-cases all
 * the keys. Will return an empty array if the friendly_attrs array is not defined
 * in config.php.
 */
function process_friendly_attr_table()
{
	require 'config.php';
	global $friendly_attrs;
	$attrs_table = array();
	if( isset( $friendly_attrs ) && is_array( $friendly_attrs ) )
		foreach( $friendly_attrs as $old_name => $new_name )
			$attrs_table[ strtolower( $old_name ) ] = $new_name;
	else
		return array();

	return $attrs_table;
}

/*
 * Returns true if the specified DN exists on the specified server, or false otherwise
 */
function dn_exists( $server_id, $dn )
{
	if( ! check_server_id( $server_id ) )
		return false;

	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	$search_result = @ldap_read( $ds, $dn, 'objectClass=*', array('dn') );

	if( ! $search_result )
		return false;

	$num_entries = ldap_count_entries( $ds, $search_result );

	if( $num_entries > 0 )
		return true;
	else
		return false;
}

/*
 * Given a DN and server_id, this draws fetches the jpegPhoto binary data and echo's the
 * HTML necesary to draw it. You can optionally have it draw the 'delete' button below
 * each image. This function supports multiple jpegPhotos.
 */
function draw_jpeg_photos( $server_id, $dn, $draw_delete_buttons=false )
{
	global $jpeg_temp_dir;
	global $jpeg_tmp_keep_time;
	global $lang;

	$conn = pla_ldap_connect( $server_id );
	$search_result = ldap_search( $conn, $dn, 'objectClass=*', array( 'jpegPhoto' ) );
	$entry = ldap_first_entry( $conn, $search_result );

	echo "<table align=\"right\"><td><center>\n\n";
	// for each jpegPhoto in the entry, draw it (there may be only one, and that's okay)
	$jpeg_data = ldap_get_values_len( $conn, $entry, "jpegphoto");
	for( $i=0; $i<$jpeg_data['count']; $i++ )
	{
		// ensures that the photo is written to the specified jpeg_temp_dir
		$jpeg_temp_dir = realpath($jpeg_temp_dir.'/');
		$jpeg_filename = tempnam($jpeg_temp_dir.'/', 'pla');
		$outjpeg = fopen($jpeg_filename, "wb");
		fwrite($outjpeg, $jpeg_data[$i]);
		fclose ($outjpeg);
		$jpeg_data_size = filesize( $jpeg_filename );
		if( $jpeg_data_size < 6 ) {
			echo $lang['jpeg_contains_errors'];
			echo '<a href="javascript:deleteAttribute( \'jpegPhoto\' );" style="color:red; font-size: 75%">'. $lang['delete_photo'] .'</a>';
			continue;
		}

		$jpeg_dimensions = getimagesize ($jpeg_filename);
		$width = $jpeg_dimensions[0];
		$height = $jpeg_dimensions[1];
		if( $width > 300 ) {
			$scale_factor = 300 / $width;
			$img_width = 300;
			$img_height = $height * $scale_factor;
		} else {
			$img_width = $width;
			$img_height = $height;
		}
		echo "<img width=\"$img_width\" height=\"$img_height\"
			src=\"view_jpeg_photo.php?file=" . basename($jpeg_filename) . "\" /><br />\n";
		echo "<small>" . number_format($jpeg_data_size) . " bytes. ";
		echo "$width x $height pixels.<br /></small>\n\n";

		if( $draw_delete_buttons )
		{ ?>
			<!-- JavaScript function deleteJpegPhoto() to be defined later by calling script -->
			<a href="javascript:deleteAttribute( 'jpegPhoto' );" style="color:red; font-size: 75%">Delete Photo</a>
		<?php }
	}
	echo "</center></td></table>\n\n";

	// If they have misconfigured their config.php, use default values
	if( ! isset( $jpeg_tmp_keep_time ) )
		$jpeg_tmp_keep_time = 120;

	if( $jpeg_tmp_keep_time == 0 )
		$jpeg_tmp_keep_time = 10;

	// delete old jpeg files.
	$jpegtmp_wildcard = "pla.*";
	$handle = opendir($jpeg_temp_dir);
	while (($file = readdir($handle)) != false)
		if (eregi($jpegtmp_wildcard, $file))
		{
			$file = "$jpeg_temp_dir/$file";
			if ((time() - filemtime($file)) > $jpeg_tmp_keep_time)
				unlink ( $file );
		}
	closedir($handle);

}

/*
 * Returns the root DN of the specified server_id, or false if it
 * can't find it (ie, the server won't give it to us).
 * Tested with OpenLDAP 2.0, Netscape iPlanet, and Novell eDirectory 8.7 (nldap.com)
 * Please report any and all bugs!!
 */
function try_to_get_root_dn( $server_id )
{
	if( ! have_auth_info( $server_id ) )
		return false;

	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;

	$r = @ldap_read( $ds, '', 'objectClass=*', array( 'namingContexts' ) );
	if( ! $r )
		return false;

	$r = @ldap_get_entries( $ds, $r );
	if( isset( $r[0]['namingcontexts'][0] ) ) {
		$root_dn = $r[0]['namingcontexts'][0];
		return $root_dn;
	} else {
		return false;
	}
}

/*
 * Hashes a password and returns the hash based on the enc_type, which can be one of
 * crypt, md5, md5crypt, sha, smd5, ssha, or clear.
 */
function password_hash( $password_clear, $enc_type )
{
	global $lang;
	$enc_type = strtolower( $enc_type );
	switch( $enc_type )
	{
		case 'crypt':
			$new_value = '{crypt}' . crypt( $password_clear, random_salt(2) );
			break;
		case 'md5':
			$new_value = '{md5}' . base64_encode( pack( 'H*' , md5( $password_clear) ) );
			break;
		case 'md5crypt':
			if( ! defined( 'CRYPT_MD5' ) || 0 == CRYPT_MD5 )
				pla_error( $lang['install_not_support_blowfish'] );
			$new_value = '{crypt}' . crypt( $password_clear , '$1$' . random_salt(9) );
			break;
		case 'blowfish':
			if( ! defined( 'CRYPT_BLOWFISH' ) || 0 == CRYPT_BLOWFISH )
				pla_error( $lang['install_not_support_blowfish'] );
			$new_value = '{crypt}' . crypt( $password_clear , '$2$' . random_salt(13) );
			break;
		case 'sha':
			if( function_exists( 'mhash' ) ) {
				$new_value = '{sha}' . base64_encode( mhash( MHASH_SHA1, $password_clear) );
			} else {
				pla_error( $lang['install_no_mash'] );
			}
			break;
		case 'ssha':
			if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
				mt_srand( (double) microtime() * 1000000 );
				$salt = mhash_keygen_s2k( MHASH_SHA1, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
				$new_value = "{ssha}".base64_encode( mhash( MHASH_SHA1, $password_clear.$salt ).$salt );
			} else {
				pla_error( $lang['install_no_mash'] );
			}
			break;
		case 'smd5':
			if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
				mt_srand( (double) microtime() * 1000000 );
				$salt = mhash_keygen_s2k( MHASH_MD5, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
				$new_value = "{smd5}".base64_encode( mhash( MHASH_SHA1, $password_clear.$salt ).$salt );
			} else {
				pla_error( $lang['install_no_mash'] );
			}
			break;
		case 'clear':
		default:
			$new_value = $password_clear;
	}

	return $new_value;
}

/*
 * Returns the version as a string as stored in the VERSION file.
 */
function pla_version()
{
	if( ! file_exists( realpath( 'VERSION' ) ) )
		return 'unknown version';

	$f = fopen( realpath( 'VERSION' ), 'r' );
	$version = fread( $f, filesize( realpath( 'VERSION' ) ) );
	fclose( $f );
	return $version;
}

function draw_chooser_link( $form_element )
{
	global $lang;
	$href = "javascript:dnChooserPopup('$form_element');";
	$title = $lang['chooser_link_tooltip'];
	echo "<a href=\"$href\" title=\"$title\"><img src=\"images/find.png\" /></a>";
	echo "<a href=\"$href\" title=\"$title\">". $lang['fbrowse'] ."</a>\n";
}

function get_values($link_id,$entry_id,$attr){
	if( 0 == strcasecmp( $attr, 'jpegPhoto' ) ) {
		$values = ldap_get_values_len($link_id,$entry_id,$attr);
	} else {
		$values = ldap_get_values($link_id,$entry_id,$attr);
		unset($values['count']);
	}
	return $values;
}

/*
function utf8_decode($str)
{
	global $code_page;
	if( ! $code_page )
		$code_page = "ISO-8859-1";
	return iconv("UTF8", $code_page, $str);
}

function utf8_encode($str)
{
	global $code_page;
	if( ! $code_page )
		$code_page = "ISO-8859-1";
	return iconv( $code_page, "UTF8", $str);
}
*/

function get_code_page()
{
	global $code_page;
	if( ! $code_page )
		$code_page = "ISO-8859-1";
	return $code_page;
}

/**
 * Convert the string to the configured codepage and replace HTML chars
 * with their &-encoded equivelants, then echo to browser.
 */
function pla_echo( $str )
{
	if( function_exists( "iconv" ) )
		$str = iconv( "UTF8", get_code_page(), $str );
	$str = htmlspecialchars( $str );
	echo $str;
}

/*
 * UTF-8 safe method for exploding a DN into its RDN parts.
 */
function pla_explode_dn( $dn, $with_attributes=0 )
{
	// This is a work-around for broken imeplementations of ldap_explode_dn()
	// that ships with some versions of PHP. It has been known to seg-fault
	// when passed the '<' and the '>' characters.
	if( '4.2.2' != phpversion() )
		$dn = addcslashes( $dn, "<>" );

	$result = ldap_explode_dn( $dn, $with_attributes );

	//translate hex code into ascii again
	foreach( $result as $key => $value )
		$result[$key] = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $value);
	return $result;
}

/*
 * Convenience function for fetching project HREFs (like bugs)
 */
function get_href( $type ) {
	$group_id = "61828";
	$bug_atid = "498546";
	$rfe_atid = "498549";
	switch( $type ) {
	case 'open_bugs': return "http://sourceforge.net/tracker/?group_id=$group_id&amp;atid=$bug_atid";
	case 'add_bug': return "http://sourceforge.net/tracker/?func=add&amp;group_id=$group_id&amp;atid=$bug_atid";
	case 'open_rfes': return "http://sourceforge.net/tracker/?atid=$rfe_atid&group_id=$group_id&amp;func=browse";
	case 'add_rfe': return "http://sourceforge.net/tracker/?func=add&amp;group_id=$group_id&amp;atid=$rfe_atid";
	default: return null;
	}
}

/*
 * Returns the current time as a double (including micro-seconds).
 */
function utime ()
{
	$time = explode( " ", microtime());
 	$usec = (double)$time[0];
 	$sec = (double)$time[1];
 	return $sec + $usec;
}

?>
