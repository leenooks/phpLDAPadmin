<?php 

/* 
 * functions.php
 * A collection of functions used throughout phpLDAPAdmin.
 */

@include 'config.php';

/*
 * Given a DN and server ID, this function reads the DN's objectClasses and 
 * determines which icon best represents the entry.
 */
function get_icon( $server_id, $dn )
{
	// fetch and lowercase all the objectClasses in an array
	$object_classes = get_object_attr( $server_id, $dn, 'objectClass' );

	if( $object_classes === null )
		return 'object.png';

	// If there is only one objectClass, make it an array with one element instead	
	if( ! is_array( $object_classes ) )
		$object_classes = array( $object_classes );
	
	foreach( $object_classes as $i => $class )
		$object_classes[$i] = strtolower( $class );

	// get the prefix (ie: dc, ou, cn, uid)
	$exploded_dn = ldap_explode_dn( $dn, 0 );
	$rdn = $dn[0];
	$prefix = explode( '=', $rdn );
	$prefix = $prefix[0];

	// Is it a person or some type of account/user?
	if( in_array( 'person', $object_classes ) || 
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
	// Oh well, I don't know what it is. Use a generic icon.
	else
		return 'object.png';
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

	if( $server['auth_type'] == 'form' )
	{
		global $_COOKIE;
		if( isset( $_COOKIE[ 'pla_login_dn_' . $server_id ] ) &&
		    isset( $_COOKIE[ 'pla_pass_' . $server_id ] ) )
			return true;
		else
			return false;
	}
	// whether or not the login_dn or pass is specified, we return 
	// true here. (if they are blank, we do an anonymous bind anyway)
	elseif( $server['auth_type'] == 'config' )
	{
		return true;
	}
	else
	{
		pla_error( "Error: You have an error in your config file. The only two allowed 
			values for 'auth_type' in the $servers section are 'config' and
			'form'. You entered '" . htmlspecialchars($server['auth_type']) . "', which 
			is not allowed. " );
	}
}

function get_logged_in_pass( $server_id )
{
	global $_COOKIE;
	$pass = $_COOKIE[ 'pla_login_pass_' . $server_id ];

	if( $pass == '0' )
		return false;
	else
		return $pass;
}
function get_logged_in_dn( $server_id )
{
	global $_COOKIE;
	$dn = $_COOKIE[ 'pla_login_dn_' . $server_id ];

	if( $dn == '0' )
		return 'Anonymous';
	else
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
	if( $conns[$server_id] )
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
		function_exists( 'ldap_start_tls' ) or pla_error( "Your PHP install does not support TLS" );
		@ldap_start_tls( $conn ) or pla_error( "Could not start TLS.<br />Please check your ".
							   "LDAP server configuration." );
	}

	// grab the auth info based on the auth_type for this server
	if( $servers[ $server_id ][ 'auth_type' ] == 'config' ) {
		$login_dn = $servers[$server_id]['login_dn'];
		$login_pass = $servers[$server_id]['login_pass'];
	} elseif( $servers[ $server_id ][ 'auth_type' ] == 'form' ) {
		global $_COOKIE;
		$login_dn = $_COOKIE['pla_login_dn_' . $server_id ];
		$login_pass = $_COOKIE['pla_pass_' . $server_id ];

		// Was this an anonyous bind (the cookie stores 0 if so)?
		if( '0' == $login_dn ) {
			$login_dn = null;
			$login_pass = null;
		}
	} else {
		pla_error( "You have an error in your config file. auth_type of " .
				htmlspecialchars( $servers[ $server_id ][ 'auth_type' ] ) .
				" is not valid." );
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

	for( $i=0; $i<$search['count']; $i++ )
	{
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
			/*
			$root_dn = try_to_get_root_dn( $id );
			echo "Root is $root_dn<br />";
			if( $root_dn )
				$tree[$id][$root_dn] = array();
			*/
		}

		$dn = $server['base'];		
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

	$search = @ldap_read( $conn, $dn, '(objectClass=*)', array("+"), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( ! $search )
		return false;

	$entry = ldap_first_entry( $conn, $search );
	$attrs = ldap_get_attributes( $conn, $entry );
	foreach( $attrs as $name => $vals )
		if( is_numeric( $name ) || $name == 'count' )
			unset( $attrs[$name] );
		else
			$attrs[$name] = $vals[0];
	return $attrs;
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

	$search = @ldap_read( $conn, $dn, '(objectClass=*)' );

	if( ! $search )
		return false;

	$entry = ldap_first_entry( $conn, $search );
	$attrs = ldap_get_attributes( $conn, $entry );
	//$attrs = ldap_get_entries( $conn, $search );

	if( ! $attrs || $attrs['count'] == 0 ) 
		return false;

	//$attrs = $attrs[0];
	$num_attrs = $attrs['count'];
	unset( $attrs['count'] );

	for( $i=0; $i<$num_attrs; $i++ )
		unset( $attrs[$i] );

	$return_array = array();
        foreach( $attrs as $attr => $vals ) {
		if( $lower_case_attr_names )
			$attr = strtolower( $attr );
		$count = $vals['count'];
		unset( $vals['count'] );
                if( $count == 1 )
                        $return_array[ $attr ] = $vals[0];
                else
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
 * Returns true if $var is not white space only, and false otherwise.
 */
function not_white( $var )
{
	return trim($var) != "" ? true : false;
}

/* 
 * Returns an associative array of objectClasses for the specified 
 * $server_id. Each array entry's key is the name of the objectClass
 * in lower-case. 
 * The sub-entries consist of sub-arrays called 'must_attrs' and 
 * 'may_attrs', and sub-entries called 'oid', 'name' and 'description'.
 *
 * The bulk of this function came from the good code in the 
 * GPL'ed LDAP Explorer project. Thank you.
 */
function get_schema_objectclasses( $server_id )
{
	$ds = pla_ldap_connect( $server_id );
	
	if( ! $ds )
		return false;
	
	// get all the objectClasses
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'objectclasses' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'objectclasses' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( ! $result ) return false;
	if( $result ) $raw_oclasses = ldap_get_entries($ds,$result );
	
	// build the array of objectClasses
	$oclasses = array();
	for( $att=0; $att < count( $raw_oclasses[0]["objectclasses"] ); $att++ )
	{
		$class = $raw_oclasses[0]["objectclasses"][$att];

		preg_match( "/[\s]+NAME[\s'\(]+([a-zA-Z0-9\-_]+)[\s'\)]/" , $class, $name);
		preg_match( "/[\s]+([\d\.]+)[\s]+NAME/", $class, $oid );
		preg_match( "/[\s]+DESC[\s]+'([a-zA-Z0-9\-_ ]+)'/", $class, $description );
		preg_match( "/[\s]+SUP[\s]+([a-zA-Z0-9\-_]+)[\s]/", $class, $sup );

		$key = strtolower( trim( $name[1] ) );
		$oclass_name = trim( $name[1] );
		if( ! $key ) continue;

		$oclasses[$key] = array();
		$oclasses[$key]['oid'] = trim( $oid[1] );
		$oclasses[$key]['description'] = trim( $description[1] );
		$oclasses[$key]['sup'] = trim( $sup[1] );       
		
		unset( $name );
		unset( $syntax );
		unset( $desription );

		// get all the required attributes
		preg_match( "/MUST[\s\(]+([a-zA-Z0-9\s$]+)(MAY|\))/" , $class, $must_attrs );
		$must_attrs = str_replace( ' ', '', $must_attrs[1] );
		$oclasses[$key]['must_attrs'] = array_filter( explode( '$', $must_attrs ), "not_white" );

		// get all the optional attributes
		preg_match( "/MAY[\s\(]+([a-zA-Z0-9\s$]+)(MUST|\))/" , $class, $may_attrs );
		$may_attrs = str_replace( ' ', '', $may_attrs[1] );
		$oclasses[$key]['may_attrs'] = array_filter( array_merge( $oclasses[$key]['must_attrs'], explode( '$', $may_attrs) ), "not_white" );
		unset( $must_attrs );
		unset( $may_attrs );

		$oclasses[$key]['name'] = $oclass_name;
	}

	// go back and add any inherited MUST/MAY attrs to each objectClass
	foreach( $oclasses as $oclass => $attrs )
	{
		$new_must = $attrs['must_attrs'];
		$new_may = $attrs['may_attrs'];
		$sup_attr = $attrs['sup'];      

		while( $sup_attr && $sup_attr != "top" ) {
			$new_must = array_merge( $new_must, $oclasses[strtolower($sup_attr)]['must_attrs'] );
			$new_may = array_merge( $new_may, $oclasses[strtolower($sup_attr)]['may_attrs'] );
			$sup_attr = $oclasses[strtolower($sup_attr)]['sup'];
		}

		$oclasses[$oclass]['must_attrs'] = array_unique( $new_must );
		$oclasses[$oclass]['may_attrs'] = array_unique( $new_may );
	}

	ksort( $oclasses );

	return $oclasses;

}

/* 
 * Returns an associate array of the server's schema matching rules
 */
function get_schema_matching_rules( $server_id )
{
	static $cache;

	if( isset( $cache[$server_id] ) )
		return $cache[$server_id];

	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	// get all the attributeTypes
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'matchingRules', 'matchingRuleUse' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'matchingRules', 'matchingRuleUse' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( $result )
		$raw = ldap_get_entries( $ds, $result );
	else
		return( array() );

	// build the array of attributes
	$rules = array();
	for( $i=0; $i < $raw[0]['matchingrules']['count']; $i++ )
	{
		$rule = $raw[0]['matchingrules'][$i];
		preg_match( "/[\s]+([\d\.]+)[\s]+/", $rule, $oid);
		preg_match( "/[\s]+NAME[\s]+'([\)\(:?\.a-zA-Z0-9\-_ ]+)'/", $rule, $name );

		$key = strtolower( trim( $oid[1] ) );
		if( ! $key ) continue;

		$rules[$key] = $name[1];
		//$rules[$key]['name'] = $name[1];
	}

	ksort( $rules );
	$cache[$server_id] = $rules;
	return $rules;
}


/* 
 * Returns an associate array of the syntax OIDs that this LDAP server uses mapped to
 * their descriptions.
 */
function get_schema_syntaxes( $server_id )
{
	static $cache;

	if( isset( $cache[$server_id] ) )
		return $cache[$server_id];

	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	// get all the attributeTypes
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'ldapSyntaxes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'ldapSyntaxes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( $result )
		$raw = ldap_get_entries( $ds, $result );
	else
		return( array() );

	// build the array of attributes
	$syntaxes = array();
	for( $i=0; $i < $raw[0]['ldapsyntaxes']['count']; $i++ )
	{
		$syntax = $raw[0]['ldapsyntaxes'][$i];
		preg_match( "/[\s]+([\d\.]+)[\s]+/", $syntax, $oid);
		preg_match( "/[\s]+DESC[\s]+'([\)\(:?\.a-zA-Z0-9\-_ ]+)'/", $syntax, $description );

		$key = strtolower( trim( $oid[1] ) );
		if( ! $key ) continue;

		$syntaxes[$key] = array();
		$syntaxes[$key]['description'] = $description[1];
	}

	ksort( $syntaxes );

	$cache[$server_id] = $syntaxes;

	return $syntaxes;
}

/* 
 * Returns an associative array of attributes for the specified 
 * $server_id. Each array entry's key is the name of the attribute,
 * in lower-case.
 * The sub-entries are 'oid', 'syntax', 'equality', 'substr', 'name',
 * and 'single_value'.
 *
 * The bulk of this function came from the good code in the 
 * GPL'ed LDAP Explorer project. Thank you. It was extended
 * considerably for application here.
 */
function get_schema_attributes( $server_id )
{
	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	// get all the attributeTypes
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'attributeTypes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'attributeTypes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( $result )
		$raw_attrs = ldap_get_entries( $ds, $result );
	else
		$raw_attrs = array();
	$syntaxes = get_schema_syntaxes( $server_id );

	// build the array of attributes
	$attrs = array();
	for( $i=0; $i < $raw_attrs[0]['attributetypes']['count']; $i++ )
	{
		$attr = $raw_attrs[0]['attributetypes'][$i];
		
		preg_match( "/[\s]+NAME[\s'\(]+([a-zA-Z0-9\-_]+)[\s'\)]/" , $attr, $name);
		preg_match( "/\s+NAME\s+'([a-zA-Z0-9\-_]+)'\s/" , $attr, $name);
		preg_match( "/[\s]+([\d\.]+)[\s]+NAME/", $attr, $oid );
		preg_match( "/[\s]+DESC[\s]+'([\)\(:?\.a-zA-Z0-9\-_ ]+)'/", $attr, $description );
		preg_match( "/[\s]+SYNTAX[\s]+([\d\.]+)/", $attr, $syntax);
		preg_match( "/[\s]+EQUALITY[\s]+([a-zA-Z]+)/", $attr, $equality);
		preg_match( "/[\s]+SUBSTR[\s]+([a-zA-Z]+)/", $attr, $substr);
		preg_match( "/[\s]+SUP[\s]+([a-zA-Z0-9\-_]+)/", $attr, $sup );

		if( preg_match( "/[\s]+SINGLE-VALUE[\s]+/", $attr, $single_value ) )
			$single_value = 'Yes';
		else
			$single_value = 'No';

		// If this schema attribute has multiple names (like: "NAME ( 'uid' 'userid' )"), then we need
		// to create a matching attribute entry for each name it bares.
		if( preg_match( "/\s+NAME\s+\(\s*['\sa-zA-Z0-9\-_]+\s*\)/", $attr, $multi_name ) ) {
			$multi_name = $multi_name[0];
			preg_match_all( "/'([a-zA-Z0-9\-_]+)'/", $multi_name, $multiple_names );
			$multiple_names = $multiple_names[1];
			//print_r( $multiple_names );
			
			foreach( $multiple_names as $name ) {
				$key = strtolower( trim( $name ) );
				$attr_name = trim( $name );
				if( ! $key ) continue;

				$attrs[$key] = array();
				$attrs[$key]['oid'] = trim( $oid[1] );
				$attrs[$key]['description'] = trim( $description[1] );
				$attrs[$key]['syntax'] = trim( $syntax[1] );
				$attrs[$key]['type'] = $syntaxes[ trim($syntax[1]) ]['description'];
				$attrs[$key]['equality'] = trim( $equality[1] );
				$attrs[$key]['substr'] = trim( $substr[1] );
				$attrs[$key]['single_value'] = $single_value;
				$attrs[$key]['sup'] = trim( $sup[1] );
				$attrs[$key]['name'] = $attr_name;
			
				$count = 1;
				for( $j=0; $j<count($multiple_names); $j++ ) {
					$alias_name = $multiple_names[$j];
					if( $alias_name != $name ) {
						$attrs[$key]['alias' . $count] = $alias_name;
						$count++;
					}
				}


			}
		} else { 
			// this attribute bares only a single name. 
			$key = strtolower( trim( $name[1] ) );
			$attr_name = trim( $name[1] );
			if( ! $key ) continue;

			$attrs[$key] = array();
			$attrs[$key]['oid'] = trim( $oid[1] );
			$attrs[$key]['description'] = trim( $description[1] );
			$attrs[$key]['syntax'] = trim( $syntax[1] );
			$attrs[$key]['type'] = $syntaxes[ trim($syntax[1]) ]['description'];
			$attrs[$key]['equality'] = trim( $equality[1] );
			$attrs[$key]['substr'] = trim( $substr[1] );
			$attrs[$key]['single_value'] = $single_value;
			$attrs[$key]['sup'] = trim( $sup[1] );
			$attrs[$key]['name'] = $attr_name;

		}
	}

	// go back and add any inherited descriptions from parent attributes (ie, cn inherits name)
	foreach( $attrs as $attr => $desc )
	{
		$sup_attr = $desc['sup'];      
		while( $sup_attr ) {
			if( ! $attrs[ $sup_attr	]['sup'] )  {
				$attrs[ $attr ][ 'syntax' ]  = $attrs[ $sup_attr ]['syntax'];
				$attrs[ $attr ][ 'equality' ]  = $attrs[ $sup_attr ]['equality'];
				$attrs[ $attr ][ 'substr' ]  = $attrs[ $sup_attr ]['substr'];
				$attrs[ $attr ][ 'single_value' ]  = $attrs[ $sup_attr ]['single_value'];
				break;
			} else {
				$sup_attr = $attrs[ $sup_attr ]['sup'];
			}
		}
	}

	ksort( $attrs );

	return $attrs;
}

/* 
 * A wrapper function to save you from having to call get_schema_objectclasses()
 * and get_schema_attributes(). Returns an array with two indexes: 'oclasses'
 * and 'attributes', as defined by their respective functions above.
 */
function get_schema( $server_id )
{
	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	$attrs = get_schema_attributes($server_id, $lower_case_all );
	$oclasses = get_schema_objectclasses($server_id, $lower_case_all );

	if( ! $oclasses )
		return false;

	$schema = array( 'attrs' => $attrs,
			 'oclasses' => $oclasses );
	return $schema;
}

/* 
 * A do-it-all ldap_search function. You can even specify the search scope. Other than
 * that, it's pretty much the same as the PHP ldap_search() call, except it returns
 * an array of results, rather than an LDAP result resource.
 */
function pla_ldap_search( $server_id, $filter, $base_dn=null, $attrs=array(), $scope='sub', $sort_results=true )
{
	global $servers;

	if( ! isset($servers[$server_id]) || $servers[$server_id]['host'] == '' )
		return false;

	if( $base_dn == null )
		$base_dn = $servers[$server_id]['base'];
	
	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;
	
	switch( $scope ) {
		case 'base':
			$search = @ldap_read( $ds, $base_dn, $filter, $attrs );
			break;
		case 'one':
			$search = @ldap_list( $ds, $base_dn, $filter, $attrs );
			break;
		case 'sub':
		default:
			$search = @ldap_search( $ds, $base_dn, $filter, $attrs );
			break;
	}

	if( ! $search )
		return array();

	$search = ldap_get_entries( $ds, $search );

	$return = array();
	foreach( $search as $id => $attrs ) {
		if( ! is_array( $attrs ) )
			continue;
        	for( $i=0; $i<$attrs['count']; $i++ )
			unset( $attrs[$i] );
		$dn = $attrs['dn'];
		foreach( $attrs as $attr => $vals ) {
			$count = $vals['count'];
			unset( $vals['count'] );
			if( $count == 1 )
				$return[$dn][$attr] = $vals[0];
			else
				$return[$dn][$attr] = $vals;
		}
	}

	if( $sort_results ) ksort( $return );
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

	global $search_criteria_options;
	$search_criteria_options= explode( ",", $search_criteria_options);
	array_walk( $search_criteria_options, "trim_it" );
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
function get_rdn( $dn )
{
	$rdn = ldap_explode_dn( $dn, 0 );
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
	$rdn = ldap_explode_dn( $dn, 0 );
	$container = $rdn[ 1 ];
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

	if( ! file_exists( $err_codes_file ) )
		return false;
	if( ! is_readable( $err_codes_file ) ) 
		return false;
	if( ! ($f = fopen( $err_codes_file, 'r' )) )
		return false;

	$contents = fread( $f, filesize( $err_codes_file ) );
	preg_match_all( "/0x[A-Fa-f0-9][A-Za-z0-9]\s+[0-9A-Za-z_]+\s+\"[^\"]*\"\n/", $contents, $entries );
	$err_codes = array();
	foreach( $entries[0] as $e )
	{
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
function pla_error( $msg, $ldap_err_msg=null, $ldap_err_no=-1 )
{
	include_once 'header.php';

	echo "<center>";
	echo "<div class=\"error\">\n\n";
	echo "<center><h3>Error</h3></center>\n\n";
	echo "<center>$msg</center>";
	echo "<br /><br />\n";

	if( $ldap_err_msg )
		echo "LDAP Server Said: <tt>" . htmlspecialchars( $ldap_err_msg ) . "</tt><br /><br />\n";

	if( $ldap_err_no != -1 ) {
		$ldap_err_no = ( '0x' . str_pad( dechex( $ldap_err_no ), 2, 0, STR_PAD_LEFT ) );
		$verbose_error = pla_verbose_error( $ldap_err_no );

		if( $verbose_error ) {
			echo "Error number: <tt>$ldap_err_no (" .
				$verbose_error['title'] . ")</tt><br /><br />\n";
			echo "Description: <tt>" . $verbose_error['desc'] . "</tt><br /><br />\n\n";
		} else {
			echo "Error number: <tt>$ldap_err_no</tt><br /><br />\n";
			echo "Description: (no description available)<br />\n\n";
		}
	}
	
	echo "</div>\n";
	echo "</center>";
	die();
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

	$conn = pla_ldap_connect( $server_id );
	$search_result = ldap_search( $conn, $dn, 'objectClass=*', array( 'jpegPhoto' ) );
	$entry = ldap_first_entry( $conn, $search_result );

	echo "<table align=\"right\"><td><center>\n\n";
	// for each jpegPhoto in the entry, draw it (there may be only one, and that's okay)
	$jpeg_data = ldap_get_values_len( $conn, $entry, "jpegphoto");
	for( $i=0; $i<$jpeg_data['count']; $i++ ) 
	{
		$jpeg_filename = $jpeg_temp_dir . '/' . basename( tempnam ('.', 'djp') );
		$outjpeg = fopen($jpeg_filename, "wb");
		fwrite($outjpeg, $jpeg_data[$i]);
		fclose ($outjpeg);
		$jpeg_data_size = filesize( $jpeg_filename );
		if( $jpeg_data_size < 6 ) {
			echo "jpegPhoto contains errors<br />";
			echo '<a href="javascript:deleteJpegPhoto();" style="color:red; font-size: 75%">Delete Photo</a>';
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
			<a href="javascript:deleteJpegPhoto();" style="color:red; font-size: 75%">Delete Photo</a>
		<?php }
	}
	echo "</center></td></table>\n\n";

	// If they have misconfigured their config.php, use default values
	if( ! isset( $jpeg_tmp_keep_time ) )
		$jpeg_tmp_keep_time = 120;

	if( $jpeg_tmp_keep_time == 0 ) 
		$jpeg_tmp_keep_time = 10;

	// delete old jpeg files.
	$jpegtmp_wildcard = "djp.*";
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
 * crypt, md5, sha, or clear.
 */
function password_hash( $password_clear, $enc_type )
{
	switch( $enc_type )
	{
		case 'crypt':
			$new_value = '{crypt}' . crypt( $password_clear, random_salt(2) );
			break;
		case 'md5':
			$new_value = '{md5}' . base64_encode( pack( 'H*' , md5( $password_clear) ) );
			break;
		case 'md5crypt':
			$new_value = '{crypt}' . crypt( $password_clear , '$1$' . random_salt(9) );
			break;
		case 'blowfish':
			$new_value = '{crypt}' . crypt( $password_clear , '$2$' . random_salt(13) );
			break;
		case 'sha':
			if( function_exists( 'mhash' ) ) {
				$new_value = '{sha}' . base64_encode( mhash( MHASH_SHA1, $password_clear) );
			} else {
				pla_error( "Your PHP install does not have the mhash() function." . 
				" Cannot do SHA hashes." );
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
	if( ! file_exists( 'VERSION' ) )
		return 'unknown version';

	$f = fopen( 'VERSION', 'r' );
	$version = fread( $f, filesize( 'VERSION' ) );
	fclose( $f );
	return $version;
}

function draw_chooser_link( $form_element )
{
	$href = "javascript:dnChooserPopup('$form_element');";
	echo "<a href=\"$href\"><img src=\"images/find.png\" /></a>";
	echo "<a href=\"$href\">browse</a>\n";
}

?>
