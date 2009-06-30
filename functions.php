<?php

/* 
 * functions.php
 * A collection of functions used throughout phpLDAPadmin.
 */

@include 'config.php';

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
	require_once realpath( 'schema_functions.php' );
	$schema_attrs = get_schema_attributes( $server_id );

	if( 0 == strcasecmp( substr( $attr_name, strlen( $attr_name ) - 7 ), ";binary" ) )
		return true;
	if( isset( $schema_attrs[ strtolower( $attr_name ) ] ) ) {
		$type = $schema_attrs[ strtolower( $attr_name ) ]->getType();
		$syntax = $schema_attrs[ strtolower( $attr_name ) ]->getSyntaxOID();
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
	if( 0 == strcasecmp( "anonymous", get_logged_in_dn( $server_id ) ) &&
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
		return 'nt.png';
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
	elseif( in_array( 'posixgroup', $object_classes ) )
		return 'ou.png';
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
	$cookie_name = 'pla_login_dn_' . $server_id; 
	if( isset( $_COOKIE[ $cookie_name ] ) )
		$dn = $_COOKIE[ $cookie_name ];
	else
		return false;

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
	$count = $attrs['count'];
	unset( $attrs['count'] );
	//echo "<pre>"; print_r( $attrs );
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

	$search = @ldap_read( $conn, $dn, '(objectClass=*)', array( ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	
	if( ! $search )
		return false;

	$entry = ldap_first_entry( $conn, $search );
	$attrs = ldap_get_attributes( $conn, $entry );

	if( ! $attrs || $attrs['count'] == 0 ) {
		return false;
	}

	$num_attrs = $attrs['count'];
	unset( $attrs['count'] );

	// strip numerical inices
	for( $i=0; $i<$num_attrs; $i++ )
		unset( $attrs[$i] );

	$return_array = array();
        foreach( $attrs as $attr => $vals ) {
		if( $lower_case_attr_names )
			$attr = strtolower( $attr );
		$count = $vals['count'];
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
			$search = @ldap_read( $ds, $base_dn, $filter, $attrs, 0, 200, 0, LDAP_DEREF_ALWAYS );
			break;
		case 'one':
			$search = @ldap_list( $ds, $base_dn, $filter, $attrs, 0, 200, 0, LDAP_DEREF_ALWAYS );
			break;
		case 'sub':
		default:
			$search = @ldap_search( $ds, $base_dn, $filter, $attrs, 0, 200, 0, LDAP_DEREF_ALWAYS );
			break;
	}

	if( ! $search )
		return array();

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

	if( ! file_exists( realpath( $err_codes_file ) ) )
		return false;
	if( ! is_readable( realpath( $err_codes_file ) ) ) 
		return false;
	if( ! ($f = fopen( realpath( $err_codes_file ), 'r' ) ) )
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

	?>
	<center>
	<table class="error"><tr><td class="img"><img src="images/warning.png" /></td>
	<td><center><h2>Error</h2></center>
	<?php echo $msg; ?>
	<br />
	<?php

	if( $ldap_err_msg )
		echo "<b>LDAP said</b>: " . htmlspecialchars( $ldap_err_msg ) . "<br /><br />\n";

	if( $ldap_err_no != -1 ) {
		$ldap_err_no = ( '0x' . str_pad( dechex( $ldap_err_no ), 2, 0, STR_PAD_LEFT ) );
		$verbose_error = pla_verbose_error( $ldap_err_no );

		if( $verbose_error ) {
			echo "<b>Error number</b>: $ldap_err_no <small>(" .
				$verbose_error['title'] . ")</small><br /><br />\n";
			echo "<b>Description</b>: " . $verbose_error['desc'] . "<br /><br />\n\n";
		} else {
			echo "<b>Error number</b>: $ldap_err_no<br /><br />\n";
			echo "<b>Description</b>: (no description available)<br />\n\n";
		}
	}
	?>
	<br />
	<br />
	<center>
	<small>
		Is this a phpLDAPadmin bug? If so, please 
		<a href="<?php echo get_href( 'add_bug' ); ?>">report it</a>.
	</small>
	</center>
	</td></tr></table>
	</center>
	<?php
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
		$jpeg_filename = realpath( $jpeg_filename );
		$outjpeg = fopen($jpeg_filename, "wb");
		fwrite($outjpeg, $jpeg_data[$i]);
		fclose ($outjpeg);
		$jpeg_data_size = filesize( $jpeg_filename );
		if( $jpeg_data_size < 6 ) {
			echo "jpegPhoto contains errors<br />";
			echo '<a href="javascript:deleteAttribute( \'jpegPhoto\' );" style="color:red; font-size: 75%">Delete Photo</a>';
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
			if( ! defined( 'CRYPT_MD5' ) || 0 == CRYPT_MD5 )
				pla_error( "Your PHP install does not support blowfish encryption." );
			$new_value = '{crypt}' . crypt( $password_clear , '$1$' . random_salt(9) );
			break;
		case 'blowfish':
			if( ! defined( 'CRYPT_BLOWFISH' ) || 0 == CRYPT_BLOWFISH )
				pla_error( "Your PHP install does not support blowfish encryption." );
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
	echo "<a href=\"$href\" title=\"$title\">browse</a>\n";
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

?>
