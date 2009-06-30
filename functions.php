<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/functions.php,v 1.181 2004/05/10 12:30:06 uugdave Exp $


/**
 * A collection of functions used throughout phpLDAPadmin.
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 *
 * @todo move functions that are only used by one script into said script (if any)
 */

/**
 * Determines if an attribute's value can contain multiple lines. Attributes that fall
 * in this multi-line category may be configured in config.php. Hence, this function
 * accesses the global variable $multi_line_attributes;
 *
 * Usage example:
 * <code>
 *  if( is_muli_line_attr( "postalAddress" ) )
 *      echo "<textarea name=\"postalAddress\"></textarea>";
 *  else 
 *      echo "<input name=\"postalAddress\" type=\"text\">";
 * </code>
 *
 * @param string $attr_name The name of the attribute of interestd (case insensivite)
 * @param string $val (optional) The current value of the attribute (speeds up the 
 *               process by searching for carriage returns already in the attribute value)
 * @param int $server_id (optional) The ID of the server of interest. If specified, 
 *               is_multi_line_attr() will read the schema from the server to determine if 
 *               the attr is multi-line capable. (note that schema reads can be expensive, 
 *               but that impact is lessened due to PLA's new caching mechanism)
 * @return bool
 */
function is_multi_line_attr( $attr_name, $val=null, $server_id=null )
{
    // First, check the optional val param for a \n or a \r
    if( null != $val && 
        ( false !== strpos( $val, "\n" ) || 
          false !== strpos( $val, "\r" ) ) )
        return true;

    // Next, compare strictly by name first
    global $multi_line_attributes;
    if( isset( $multi_line_attributes ) && is_array( $multi_line_attributes ) )
        foreach( $multi_line_attributes as $multi_line_attr_name )
            if( 0 == strcasecmp( $multi_line_attr_name, $attr_name ) )
                return true;

    // If unfound, compare by syntax OID
    if( null !== $server_id ) {
        global $multi_line_syntax_oids;
        if( isset( $multi_line_syntax_oids ) && is_array( $multi_line_syntax_oids ) ) {
            $schema_attr = get_schema_attribute( $server_id, $attr_name );
            if( ! $schema_attr )
                return false;
            $syntax_oid = $schema_attr->getSyntaxOID();
            if( ! $syntax_oid )
                return false;
            foreach( $multi_line_syntax_oids as $multi_line_syntax_oid )
                if( $multi_line_syntax_oid == $syntax_oid )
                    return true;
        }
    }

    return false;

}

/**
 * Fetches the user setting for $search_deref from config.php. The returned value
 * will be one of the four LDAP_DEREF_* constancts defined by the PHP LDAP API. If
 * the user has failed to configure this setting or configured an inappropriate
 * value, the constant DEFAULT_SEARCH_DEREF_SETTING is returned.
 *
 * @see DEFAULT_SEARCH_DEREF_SETTING
 * @see is_valid_deref_setting()
 * @return int
 */
function get_search_deref_setting()
{
    global $search_deref;
    if( ! isset( $search_deref ) || ! is_valid_deref_setting( $search_deref ) )
        return DEFAULT_SEARCH_DEREF_SETTING;
    else
        return $search_deref;
}

/**
 * Fetches the user setting for $tree_deref from config.php. The returned value
 * will be one of the four LDAP_DEREF_* constancts defined by the PHP LDAP API. If
 * the user has failed to configure this setting or configured an inappropriate
 * value, the constant DEFAULT_TREE_DEREF_SETTING is returned.
 *
 * @see DEFAULT_TREE_DEREF_SETTING
 * @see is_valid_deref_setting()
 * @return int
 */
function get_tree_deref_setting()
{
    global $tree_deref;
    if( ! isset( $tree_deref ) || ! is_valid_deref_setting( $tree_deref ) )
        return DEFAULT_TREE_DEREF_SETTING;
    else
        return $tree_deref;
}

/**
 * Fetches the user setting for $export_deref from config.php. The returned value
 * will be one of the four LDAP_DEREF_* constancts defined by the PHP LDAP API. If
 * the user has failed to configure this setting or configured an inappropriate
 * value, the constant DEFAULT_EXPORT_DEREF_SETTING is returned.
 *
 * @see DEFAULT_EXPORT_DEREF_SETTING
 * @see is_valid_deref_setting()
 * @return int
 */
function get_export_deref_setting()
{
    global $export_deref;
    if( ! isset( $export_deref ) || ! is_valid_deref_setting( $export_deref ) )
        return DEFAULT_EXPORT_DEREF_SETTING;
    else
        return $export_deref;
}

/**
 * Fetches the user setting for $view_deref from config.php. The returned value
 * will be one of the four LDAP_DEREF_* constancts defined by the PHP LDAP API. If
 * the user has failed to configure this setting or configured an inappropriate
 * value, the constant DEFAULT_VIEW_DEREF_SETTING is returned.
 *
 * @see DEFAULT_VIEW_DEREF_SETTING
 * @see is_valid_deref_setting()
 * @return int
 */
function get_view_deref_setting()
{
    global $view_deref;
    if( ! isset( $view_deref ) || ! is_valid_deref_setting( $view_deref ) )
        return DEFAULT_VIEW_DEREF_SETTING;
    else
        return $view_deref;
}

/** 
 * Checks the user-configured parameter for sanity. For the various *_deref settings, users
 * may only use one of LDAP_DEREF_NEVER, LDAP_DEREF_SEARCHING, LDAP_DEREF_FINDING, or
 * LDAP_DEREF_ALWAYS. This function can be used to conveniently enforce this.
 *
 * @param int $deref_setting The deref setting to validate.
 * @return bool
 */
function is_valid_deref_setting( $deref_setting )
{
    if( $deref_setting == LDAP_DEREF_NEVER ||
        $deref_setting == LDAP_DEREF_SEARCHING ||
        $deref_setting == LDAP_DEREF_FINDING ||
        $deref_setting == LDAP_DEREF_ALWAYS )
        return true;
    else
        return false;

}

/**
 * Fetch whether the user has configured a certain server as "low bandwidth". Users may
 * choose to configure a server as "low bandwidth" in config.php thus:
 * <code>
 *   $servers[$i]['low_bandwidth'] = true;
 * </code>
 * @param int $server_id The ID of the server of interest from config.php.
 * @return bool
 */
function is_server_low_bandwidth( $server_id )
{
    global $servers;
    if( isset( $servers[$server_id]['low_bandwidth'] ) && true == $servers[$server_id]['low_bandwidth'] )
        return true;
    else
        return false;
}

/** 
 * Fetches whether TLS has been configured for use with a certain server.
 * Users may configure phpLDAPadmin to use TLS in config,php thus:
 * <code>
 *   $servers[$i]['tls'] = true;
 * </code>
 * @param int $server_id The ID of the server of interest from config.php.
 * @return bool
 */
function tls_enabled( $server_id )
{
    global $servers;
    if( isset( $servers[$server_id]['tls'] ) && true == $servers[$server_id]['tls'] )
        return true;
    else
        return false;
}

/**
 * Fetches whether phpLDAPadmin has been configured to redirect anonymously bound users
 * to a search form with no tree displayed.
 * This is configured in config.php thus:
 * <code>
 *  $anonymous_bind_redirect_no_tree = true;
 * </code>
 * @return bool
 */
function anon_bind_tree_disabled()
{
	global $anonymous_bind_redirect_no_tree;
	if( isset( $anonymous_bind_redirect_no_tree ) && true == $anonymous_bind_redirect_no_tree )
		return true;
	else
		return false;
}

/** 
 * Fetches whether the user has configured phpLDAPadmin to obfuscate passwords
 * with "*********" when displaying them. This is configured in config.php thus:
 * <code>
 *  $obfuscate_password_display = true;
 * </code>
 * @return bool
 */
function obfuscate_password_display()
{
	global $obfuscate_password_display;
	if( isset( $obfuscate_password_display ) && true == $obfuscate_password_display )
		return true;
	else
		return false;
}

/** 
 * Fetches whether the login_attr feature is enabled for a specified server. 
 * This is configured in config.php thus:
 * <code>
 *  $servers[$server_id]['login_attr'] = 'uid';
 * </code>
 * By virtue of the fact that the login_attr is not blank and not 'dn', the 
 * feature is configured to be enabled.
 *
 * @return bool
 */
function login_attr_enabled( $server_id )
{
	global $servers;
	if(	isset( $servers[$server_id]['login_attr'] ) && 
		0 != strcasecmp( $servers[$server_id]['login_attr'], "dn" ) && 
		trim( $servers[$server_id]['login_attr'] != "" ) )
		return true;
	else
		return false;
}

/** 
 * Returns an HTML-beautified version of a DN.
 * Internally, this function makes use of pla_explode_dn() to break the 
 * the DN into its components. It then glues them back together with
 * "pretty" HTML. The returned HTML is NOT to be used as a real DN, but 
 * simply displayed.
 * 
 * @param string $dn The DN to pretty-print.
 * @return string
 */
function pretty_print_dn( $dn )
{
	$dn = pla_explode_dn( $dn );
	foreach( $dn as $i => $element ) {
		$element = htmlspecialchars( $element );
		$element = explode( '=', $element, 2 );
		$element = implode( '<span style="color: blue; font-family: courier; font-weight: bold">=</span>', $element );
		$dn[$i] = $element;
	}
	$dn = implode( '<span style="color:red; font-family:courier; font-weight: bold;">,</span>', $dn );

	return $dn;
}

/**
 * Returns true if the attribute specified is required to take as input a DN.
 * Some examples include 'distinguishedName', 'member' and 'uniqueMember'.
 * @param int $server_id The ID of the server of interest 
 *            (required since this operation demands a schema lookup)
 * @param string $attr_name The name of the attribute of interest (case insensitive)
 * @return bool
 */
function is_dn_attr( $server_id, $attr_name )
{
    // Simple test first
    $dn_attrs = array( "aliasedObjectName" );
    foreach( $dn_attrs as $dn_attr )
        if( 0 == strcasecmp( $attr_name, $dn_attr ) )
            return true;

    // Now look at the schema OID
	$attr_schema = get_schema_attribute( $server_id, $attr_name );
	if( ! $attr_schema )
		return false;
	$syntax_oid = $attr_schema->getSyntaxOID();
	if( '1.3.6.1.4.1.1466.115.121.1.12' == $syntax_oid )
		return true;
	if( '1.3.6.1.4.1.1466.115.121.1.34' == $syntax_oid )
		return true;
	$syntaxes = get_schema_syntaxes( $server_id );
	if( ! isset( $syntaxes[ $syntax_oid ] ) )
		return false;
	$syntax_desc = $syntaxes[ $syntax_oid ]->getDescription();
	if( false !== strpos( strtolower($syntax_desc), 'distinguished name' ) )
		return true;
	return false;
}

/**
 * Given a string, this function returns true if the string has the format
 * of a DN (ie, looks like "cn=Foo,dc=example,dc=com"). Returns false otherwise.
 * The purpose of this function is so that developers can examine a string and
 * know if it looks like a DN, and draw a hyperlink as needed.
 *
 * (See unit_test.php for test cases)
 *
 * @param string $attr The attribute to examine for "DNness"
 * @see unit_test.php
 * @return bool
 */
function is_dn_string( $str )
{
    // Try to break the string into its component parts if it can be done
    // ie, "uid=Manager" "dc=example" and "dc=com"
    $parts = pla_explode_dn( $str );
    if( ! is_array( $parts ) )
        return false;
    if( 0 == count( $parts ) )
        return false;

    // Foreach of the "parts", look for an "=" character,
    // and make sure neither the left nor the right is empty
    foreach( $parts as $part ) {
        if( false === strpos( $part, "=" ) )
            return false;
        $sub_parts = explode( "=", $part, 2 );
        $left = $sub_parts[0];
        $right = $sub_parts[1];
        if( 0 == strlen( trim( $left ) ) || 0 == strlen( trim( $right ) ) )
            return false;
        if( false !== strpos( $left, '#' ) )
            return false;
    }

    // We survived the above rigor. This is a bonified DN string.
    return true;
}

/** 
 * Get whether a string looks like an email address (user@example.com).
 * 
 * @param string $str The string to analyze.
 * @return bool Returns true if the specified string looks like 
 *   an email address or false otherwise.
 */
function is_mail_string( $str )
{
    $mail_regex = "/^[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*$/";
    if( preg_match( $mail_regex, $str ) )
        return true;
    else
        return false;
}

/** 
 * Get whether a string looks like a web URL (http://www.example.com/)
 * 
 * @param string $str The string to analyze.
 * @return bool Returns true if the specified string looks like 
 *   a web URL or false otherwise.
 */
function is_url_string( $str )
{
    $url_regex = '/(ftp|https?):\/\/+[\w\.\-\/\?\=\&]*\w+/';
    if( preg_match( $url_regex, $str ) )
        return true;
    else
        return false;

}


/**
 * Utility wrapper for setting cookies, which takes into consideration
 * phpLDAPadmin configuration values. On success, true is returned. On 
 * failure, false is returned.
 *
 * @param string $name The name of the cookie to set.
 * @param string $val The value of the cookie to set.
 * @param int $expire (optional) The duration in seconds of this cookie. If unspecified, $cookie_time 
 *            is used from config.php
 * @param string $dir (optional) The directory value of this cookie (see php.net/setcookie)
 *
 * @see setcookie
 * @return bool
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
		$dir = dirname( $_SERVER['PHP_SELF'] );
	}

	if( @setcookie( $name, $val, $expire, $dir ) ) {
		$_COOKIE[ $name ] = $val;
		return true;
	} else {
		return false;
	}
}

/**
 * Responsible for setting two cookies/session-vars to indicate that a user has logged in,
 * one for the logged in DN and one for the logged in password. Cookies  
 * are stored unencrypted in the client's browser's cookie cache. Use caution!
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
 * @param int $server_id The ID of the server to which the user is logged in.
 * @param string $dn The DN with which the user has logged in. 
 * @param string $password The password of the user logged in.
 * @param bool $anon_bind Indicates that this is an anonymous bind such that
 *             a password of "0" is stored.
 * @return bool
 * @see unset_login_dn
 */
function set_login_dn( $server_id, $dn, $password, $anon_bind )
{
	global $servers;

	if( ! check_server_id( $server_id ) )
		return false;
	if( ! isset( $servers[ $server_id ][ 'auth_type' ] ) )
		return false;
	$auth_type = $servers[ $server_id ][ 'auth_type' ]; 

    switch( $auth_type ) 
    {
        case 'cookie':
            $cookie_dn_name = "pla_login_dn_$server_id";
            $cookie_pass_name = "pla_login_pass_$server_id";
            if( $anon_bind ) {
                // we set the cookie password to 0 for anonymous binds.
                $res1 = pla_set_cookie( $cookie_dn_name, 'anonymous' );
                $res2 = pla_set_cookie( $cookie_pass_name, '0' );
            } else {
                $res1 = pla_set_cookie( $cookie_dn_name, $dn );
                $res2 = pla_set_cookie( $cookie_pass_name, $password );
            }
            if( $res1 && $res2 )
                return true;
            else
                return false;
            break;
        case 'session':
            $sess_var_dn_name = "pla_login_dn_$server_id";
            $sess_var_pass_name = "pla_login_pass_$server_id";
            if( $anon_bind ) {
                $_SESSION[ $sess_var_dn_name ] = 'anonymous';
                $_SESSION[ $sess_var_pass_name ] = 0;
            } else {
                $_SESSION[ $sess_var_dn_name ] = $dn;
                $_SESSION[ $sess_var_pass_name ] = $password;
            }
            return true;
            break;
        default:
            global $lang;
			pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $auth_type ) ) );
            break;
    }
}

/**
 * Effectively logs a user out from a server. 
 * Removes the cookies/session-vars set by set_login_dn() 
 * after a user logs out using "auth_type" of "session" or "cookie".
 * Returns true on success, false on failure.
 *
 * @param int $server_id The ID of the server from which the user is logging out.
 * @return bool True on success, false on failure.
 * @see set_login_dn
 */
function unset_login_dn( $server_id )
{
	global $servers;

	if( ! check_server_id( $server_id ) )
		return false;
	if( ! isset( $servers[ $server_id ][ 'auth_type' ] ) )
		return false;
	$auth_type = $servers[ $server_id ][ 'auth_type' ]; 
    switch( $auth_type ) 
    {
        case 'cookie':
            $logged_in_dn = get_logged_in_dn( $server_id );
            if( ! $logged_in_dn )
                return false;
            $logged_in_pass = get_logged_in_pass( $server_id );
            $anon_bind = $logged_in_dn == 'anonymous' ? true : false;

            // set cookie with expire time already passed to erase cookie from client
            $expire = time()-3600;
            $cookie_dn_name = "pla_login_dn_$server_id";
            $cookie_pass_name = "pla_login_pass_$server_id";
            if( $anon_bind ) {
                $res1 = pla_set_cookie( $cookie_dn_name, 'anonymous', $expire );
                $res2 = pla_set_cookie( $cookie_pass_name, '0', $expire );
            } else {
                $res1 = pla_set_cookie( $cookie_dn_name, $logged_in_dn, $expire );
                $res2 = pla_set_cookie( $cookie_pass_name, $logged_in_pass, $expire );
            }

            if( ! $res1 || ! $res2 )
                return false;
            else
                return true;
            break;
       case 'session':
            // unset session variables
            $session_var_dn_name = "pla_login_dn_$server_id";
            $session_var_pass_name = "pla_login_pass_$server_id";
            if( array_key_exists( $session_var_dn_name, $_SESSION ) )
                unset( $_SESSION[ $session_var_dn_name ] );
            if( array_key_exists( $session_var_pass_name, $_SESSION ) )
                unset( $_SESSION[ "$session_var_pass_name" ] );
            session_write_close();
            return true;
            break;
      default:
            global $lang;
			pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $auth_type ) ) );
            break;
    }
}

/**
 * Compares 2 DNs. If they are equivelant, returns 0, otherwise,
 * returns their sorting order (similar to strcmp()):
 *      Returns < 0 if dn1 is less than dn2.
 *      Returns > 0 if dn1 is greater than dn2.
 *
 * The comparison is performed starting with the top-most element 
 * of the DN. Thus, the following list:
 *    <code>
 *       ou=people,dc=example,dc=com
 *       cn=Admin,ou=People,dc=example,dc=com
 *       cn=Joe,ou=people,dc=example,dc=com
 *       dc=example,dc=com
 *       cn=Fred,ou=people,dc=example,dc=org
 *       cn=Dave,ou=people,dc=example,dc=org
 *    </code>
 * Will be sorted thus using usort( $list, "pla_compare_dns" ):
 *    <code>
 *       dc=com
 *       dc=example,dc=com
 *       ou=people,dc=example,dc=com
 *       cn=Admin,ou=People,dc=example,dc=com
 *       cn=Joe,ou=people,dc=example,dc=com
 *       cn=Dave,ou=people,dc=example,dc=org
 *       cn=Fred,ou=people,dc=example,dc=org
 *    </code>
 *
 * @param string $dn1 The first of two DNs to compare
 * @param string $dn2 The second of two DNs to compare
 * @return int
 */
function pla_compare_dns( $dn1, $dn2 )
{
	// If they are obviously the same, return immediately
	if( 0 === strcasecmp( $dn1, $dn2 ) )
		return 0;
	
	$dn1_parts = pla_explode_dn( pla_reverse_dn($dn1) );
	$dn2_parts = pla_explode_dn( pla_reverse_dn($dn2) );
	assert( is_array( $dn1_parts ) );
	assert( is_array( $dn2_parts ) );
	
	// Foreach of the "parts" of the smaller DN
	for( $i=0; $i<count( $dn1_parts ) && $i<count( $dn2_parts ); $i++ )
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

    // If we iterated through all entries in the smaller of the two DNs
    // (ie, the one with fewer parts), and the entries are different sized,
    // then, the smaller of the two must be "less than" than the larger.
    if( count($dn1_parts) > count($dn2_parts) ) {
        return 1;
    } elseif( count( $dn2_parts ) > count( $dn1_parts ) ) {
        return -1;
    } else {
        return 0;
    }
}

/** 
 * Prunes off anything after the ";" in an attr name. This is useful for
 * attributes that may have ";binary" appended to their names. With 
 * real_attr_name(), you can more easily fetch these attributes' schema
 * with their "real" attribute name.
 *
 * @param string $attr_name The name of the attribute to examine.
 * @return string
 */
function real_attr_name( $attr_name )
{
	$attr_name = preg_replace( "/;.*$/U", "", $attr_name );
	return $attr_name;
}

/**
 * Returns true if the user has configured the specified
 * server to enable mass deletion. Mass deletion is enabled in config.php this:
 * <code>
 *   $enable_mass_delete = true;
 * </code>
 * Notice that mass deletes are not enabled on a per-server basis, but this
 * function checks that the sever is not in a read-only state as well.
 *
 * @param int $server_id The ID of the server of interest.
 * @return bool
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

/**
 * Returns true if the user has configured PLA to show
 * helpful hints with the $show_hints setting.
 * This is configured in config.php thus:
 * <code>
 *  $show_hints = true;
 * </code>
 *
 * @return bool
 */
function show_hints()
{
	global $show_hints;
	if( isset( $show_hints ) && $show_hints === true )
		return true;
}

/**
 * Determines if the user has enabled auto uidNumbers for the specified server ID.
 *
 * @param int $server_id The id of the server of interest.
 * @return bool True if auto uidNumbers are enabled, false otherwise.
 */
function auto_uid_numbers_enabled( $server_id ) 
{
    global $servers;
    if( isset( $servers[$server_id]['enable_auto_uid_numbers'] ) &&
        true == $servers[$server_id]['enable_auto_uid_numbers'] )
        return true;
    else
        return false;
}

/**
 * For hosts who have 'enable_auto_uid_numbers' set to true, this function will
 * get the next available uidNumber using the host's preferred mechanism
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
 * 
 * Also note that both algorithms are susceptible to a race condition. If two admins
 * are adding users simultaneously, the users may get identical uidNumbers with this
 * function.
 *
 * See config.php.example for more notes on the two auto uidNumber mechanisms.
 *
 * @param int $server_id The ID of the server of interest.
 * @return int
 *
 * @todo eliminate race condition at create time by re-running this function.
 */
function get_next_uid_number( $server_id )
{
	global $servers, $lang;
	// Some error checking
	if( ! check_server_id( $server_id ) )
		return false;
    if( ! auto_uid_numbers_enabled( $server_id ) )
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

		// Check see and use our alternate uid_dn and password if we have it.
		if ( isset( $servers[ $server_id ][ 'auto_uid_number_search_dn' ] ) &&
			isset( $servers[ $server_id ][ 'auto_uid_number_search_dn_pass' ] ) ) {
			$con = @ldap_connect( $servers[$server_id]['host'], $servers[$server_id]['port'] );
            @ldap_set_option( $con, LDAP_OPT_PROTOCOL_VERSION, 3 );
            @ldap_set_option( $con, LDAP_OPT_REFERRALS, 0);

			// Bind with the alternate ID.
			$res = @ldap_bind( $con,
				$servers[ $server_id ][ 'auto_uid_number_search_dn' ],
				$servers[ $server_id ][ 'auto_uid_number_search_dn_pass' ] );
			if (! $res) pla_error( sprintf( $lang['auto_uid_invalid_credential'] , $server_name ) );
			$search = @ldap_search( $con, $base_dn, $filter, array('uidNumber'), 0, 0, 0, get_search_deref_setting() );
            if( ! $search ) pla_error( sprintf( $lang['bad_auto_uid_search_base'], $server_name ) );
			$search = @ldap_get_entries( $con, $search );
			$res = @ldap_unbind( $con );

			$results = array();
			for( $i=0; $i<$search['count']; $i++ ) {
				$entry = $search[$i];
				$dn['dn'] = $entry['dn'];
				$dn['uidnumber'] = $entry['uidnumber'][0];
				$results[] = $dn;
			}

		} else {
			$results = pla_ldap_search( $server_id, $filter, $base_dn, array('uidNumber'));
		}
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

/**
 * Used to determine if the specified attribute is indeed a jpegPhoto. If the
 * specified attribute is one that houses jpeg data, true is returned. Otherwise
 * this function returns false.
 *
 * @param int $server_id The ID of the server hosuing the attribute of interest
 * @param string $attr_name The name of the attribute to test.
 * @return bool
 * @see draw_jpeg_photos
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

/**
 * Given an attribute name and server ID number, this function returns
 * whether the attrbiute contains boolean data. This is useful for 
 * developers who wish to display the contents of a boolean attribute
 * with a drop-down.
 * 
 * @param int $server_id The ID of the server of interest (required since 
 *            this action requires a schema lookup on the server)
 * @param string $attr_name The name of the attribute to test.
 * @return bool
 */
function is_attr_boolean( $server_id, $attr_name )
{
    $type = ( $schema_attr = get_schema_attribute( $server_id, $attr_name ) ) ? 
        $schema_attr->getType() : 
        null;
    if( 0 == strcasecmp( 'boolean', $type ) ||
        0 == strcasecmp( 'isCriticalSystemObject', $attr_name ) ||
        0 == strcasecmp( 'showInAdvancedViewOnly', $attr_name ) )
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
 * @param int $server_id The ID of the server of interest (required since 
 *            this action requires a schema lookup on the server)
 * @param string $attr_name The name of the attribute to test.
 * @return bool
 *
 * @see is_jpeg_photo
 */
function is_attr_binary( $server_id, $attr_name )
{
    $attr_name = strtolower( $attr_name );
    /** Determining if an attribute is binary can be an expensive
       operation. We cache the results for each attr name on each
       server in the $attr_cache to speed up subsequent calls. 
       The $attr_cache looks like this:
       Array 
        0 => Array 
              'objectclass' => false
              'cn' => false
              'usercertificate' => true
        1 => Array 
              'jpegphoto' => true 
              'cn' => false
    */

    static $attr_cache;
    if( isset( $attr_cache[ $server_id ][ $attr_name ] ) )
        return $attr_cache[ $server_id ][ $attr_name ];

    if( $attr_name == 'userpassword' ) {
        $attr_cache[ $server_id ][ $attr_name ] = false;
        return false;
    }

    // Quick check: If the attr name ends in ";binary", then it's binary.
	if( 0 == strcasecmp( substr( $attr_name, strlen( $attr_name ) - 7 ), ";binary" ) ) {
        $attr_cache[ $server_id ][ $attr_name ] = true;
		return true;
    }

    // See what the server schema says about this attribute
	$schema_attr = get_schema_attribute( $server_id, $attr_name );
	if( ! $schema_attr ) {
        // Strangely, some attributeTypes may not show up in the server
        // schema. This behavior has been observed in MS Active Directory.
        $type = null;
        $syntax = null;
    } else {
        $type = $schema_attr->getType();
        $syntax = $schema_attr->getSyntaxOID();
    }

	if(	0 == strcasecmp( $type, 'Certificate' ) ||
		0 == strcasecmp( $type, 'Binary' ) ||
		0 == strcasecmp( $attr_name, 'usercertificate' ) ||
		0 == strcasecmp( $attr_name, 'usersmimecertificate' ) ||
		0 == strcasecmp( $attr_name, 'networkaddress' ) ||
		0 == strcasecmp( $attr_name, 'objectGUID' ) ||
		0 == strcasecmp( $attr_name, 'objectSID' ) ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.10' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.28' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.5' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.8' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.9' ||
		$syntax == '1.3.6.1.4.1.1466.115.121.1.40' ) {
            $attr_cache[ $server_id ][ $attr_name ] = true;
			return true;
    } else {
            $attr_cache[ $server_id ][ $attr_name ] = false;
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
function is_attr_read_only( $attr )
{
	global $read_only_attrs;
	$attr = trim( $attr );
	if( '' ===  $attr )
		return false;
	if( ! isset( $read_only_attrs ) ) 
		return false;
	if( ! is_array( $read_only_attrs) )
		return false;
	foreach( $read_only_attrs as $attr_name )
		if( 0 == strcasecmp( $attr, trim($attr_name) ) )
			return true;
	return false;
}

/**
 * Returns true if the specified server is configured to be displayed
 * in read only mode. If a user has logged in via anonymous bind, and
 * config.php specifies anonymous_bind_implies_read_only as true, then
 * this also returns true. Servers can be configured read-only in
 * config.php thus:
 * <code>
 *  $server[$i]['read_only'] = true;
 * </code>
 *
 * @param int $server_id The ID of the server of interest from the $servers array in config.php
 * @return bool
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

/**
 * Given a DN and server ID, this function reads the DN's objectClasses and
 * determines which icon best represents the entry. The results of this query
 * are cached in a session variable so it is not run every time the tree
 * browser changes, just when exposing new DNs that were not displayed
 * previously. That means we can afford a little bit of inefficiency here
 * in favor of coolness. :)
 *
 * This function returns a string like "country.png". All icon files are assumed
 * to be contained in the /images/ directory of phpLDAPadmin.
 *
 * Developers are encouraged to add new icons to the images directory and modify
 * this function as needed to suit their types of LDAP entries. If the modifications
 * are general to an LDAP audience, the phpLDAPadmin team will gladly accept them
 * as a patch.
 * 
 * @param int $server_id The ID of the LDAP server housing the DN of interest.
 * @param string $dn The DN of the entry whose icon you wish to fetch.
 *
 * @return string
 */
function get_icon( $server_id, $dn )
{
	// fetch and lowercase all the objectClasses in an array
	$object_classes = get_object_attr( $server_id, $dn, 'objectClass', true );

	if( $object_classes === null || $object_classes === false || ! is_array( $object_classes ) )
		$object_classes = array();

	foreach( $object_classes as $i => $class )
		$object_classes[$i] = strtolower( $class );

	$rdn = get_rdn( $dn );
    $rdn_parts = explode( '=', $rdn, 2 );
    $rdn_value = isset( $rdn_parts[0] ) ? $rdn_parts[0] : null;
    $rdn_attr = isset( $rdn_parts[1] ) ? $rdn_parts[1] : null;
    unset( $rdn_parts );

	// return icon filename based upon objectClass value
	if( in_array( 'sambaaccount', $object_classes ) &&
		'$' == $rdn{ strlen($rdn) - 1 } )
		return 'nt_machine.png';
	if( in_array( 'sambaaccount', $object_classes ) )
		return 'nt_user.png';
	elseif( in_array( 'person', $object_classes ) ||
	    in_array( 'organizationalperson', $object_classes ) ||
	    in_array( 'inetorgperson', $object_classes ) ||
	    in_array( 'account', $object_classes ) ||
   	    in_array( 'posixaccount', $object_classes )  )
		return 'user.png';
	elseif( in_array( 'organization', $object_classes ) )
		return 'o.png';
	elseif( in_array( 'organizationalunit', $object_classes ) )
		return 'ou.png';
	elseif( in_array( 'organizationalrole', $object_classes ) )
		return 'uid.png';
	elseif( in_array( 'dcobject', $object_classes ) ||
		in_array( 'domainrelatedobject', $object_classes ) ||
		in_array( 'domain', $object_classes ) ||
        in_array( 'builtindomain', $object_classes )) 
		return 'dc.png';
	elseif( in_array( 'country', $object_classes ) )
		return 'country.png';
	elseif( in_array( 'jammvirtualdomain', $object_classes ) )
		return 'mail.png';
	elseif( in_array( 'locality', $object_classes ) )
		return 'locality.png';
	elseif( in_array( 'posixgroup', $object_classes ) ||
		in_array( 'groupofnames', $object_classes ) ||
		in_array( 'group', $object_classes ) )
		return 'ou.png';
	elseif( in_array( 'applicationprocess', $object_classes ) )
		return 'process.png';
	elseif( in_array( 'groupofuniquenames', $object_classes ) )
		return 'uniquegroup.png';
	elseif( in_array( 'iphost', $object_classes ) )
		return 'host.png';
	elseif( in_array( 'nlsproductcontainer', $object_classes ) )
        return 'n.png';
	elseif( in_array( 'ndspkikeymaterial', $object_classes ) )
        return 'lock.png';
	elseif( in_array( 'server', $object_classes ) )
        return 'server-small.png';
	elseif( in_array( 'volume', $object_classes ) )
        return 'hard-drive.png';
	elseif( in_array( 'ndscatcatalog', $object_classes ) )
        return 'catalog.png';
	elseif( in_array( 'resource', $object_classes ) )
        return 'n.png';
	elseif( in_array( 'ldapgroup', $object_classes ) )
        return 'ldap-server.png';
	elseif( in_array( 'ldapserver', $object_classes ) )
        return 'ldap-server.png';
	elseif( in_array( 'nisserver', $object_classes ) )
        return 'ldap-server.png';
	elseif( in_array( 'rbscollection', $object_classes ) )
        return 'ou.png';
	elseif( in_array( 'dfsconfiguration', $object_classes ) )
        return 'nt_machine.png';
	elseif( in_array( 'applicationsettings', $object_classes ) )
        return 'server-settings.png';
	elseif( in_array( 'container', $object_classes ) )
        return 'folder.png';
	elseif( in_array( 'samServer', $object_classes ) )
        return 'server-small.png';
	elseif( in_array( 'lostandfound', $object_classes ) )
        return 'find.png';
	elseif( in_array( 'infrastructureupdate', $object_classes ) )
        return 'server-small.png';
	elseif( in_array( 'filelinktracking', $object_classes ) )
        return 'files.png';
	elseif( in_array( 'automountmap', $object_classes ) )
        return 'hard-drive.png';
    elseif( 0 === strpos( $rdn_value, "ipsec" ) || 
            0 == strcasecmp( $rdn_value, "IP Security" ) ||
            0 == strcasecmp( $rdn_value, "MSRADIUSPRIVKEY Secret" ) ||
            0 === strpos( $rdn_value, "BCKUPKEY_" ) )
        return 'lock.png';
	elseif( 0 == strcasecmp( $rdn_value, "MicrosoftDNS" ) )
        return 'dc.png';
	// Oh well, I don't know what it is. Use a generic icon.
	else
		return 'object.png';
}

/**
 * Does the same thing as get_icon(), but it tries to fetch the icon name from the
 * tree_icons session variable first. If not found, resorts to get_icon() and stores
 * the icon nmae in the tree_icons session before returing the icon.
 *
 * @param int $server_id The ID of the server housing the DN of interest.
 * @param string $dn The DN of the entry of interest.
 *
 * @return string
 *
 * @see get_icon
 */
function get_icon_use_cache( $server_id, $dn )
{
	if( array_key_exists( 'tree_icons', $_SESSION ) ) {
		if( array_key_exists( $server_id, $_SESSION['tree_icons'] ) &&
			array_key_exists( $dn, $_SESSION['tree_icons'][$server_id] ) )
		{
			return $_SESSION['tree_icons'][ $server_id ][ $dn ];
		} else {
			$icon = get_icon( $server_id, $dn );
			$_SESSION['tree_icons'][ $server_id ][ $dn ] = $icon;
			return $icon;
		}
	}
}

/**
 * Given a server_id, returns whether or not we have enough information
 * to authenticate against the server. For example, if the user specifies
 * auth_type of 'cookie' in the config for that server, it checks the $_COOKIE array to
 * see if the cookie username and password is set for the server. If the auth_type
 * is 'session', the $_SESSION array is checked.
 *
 * There are three cases for this function depending on the auth_type configured for
 * the specified server. If the auth_type is form or http, then get_logged_in_dn() is 
 * called to verify that the user has logged in. If the auth_type is config, then the
 * $servers array in config.php is checked to ensure that the user has specified
 * login information. In any case, if phpLDAPadmin has enough information to login
 * to the server, true is returned. Otherwise false is returned.
 *
 * @param int $server_id
 * @return bool
 * @see get_logged_in_dn
 */
function have_auth_info( $server_id )
{
	global $servers;

	if( ! check_server_id( $server_id ) )
		return false;

	$server = $servers[$server_id];

    // For session or cookie auth_types, we check the session or cookie to see if a user has logged in.
	if( isset( $server['auth_type'] ) && ( $server['auth_type'] == 'session' || $server['auth_type'] == 'cookie' ) ) {
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

/**
 * Fetches the password of the currently logged in user (for auth_types "form" and "http" only)
 * or false if the current login is anonymous.
 *
 * @param int $server_id The ID of the server of interest.
 *
 * @return string
 *
 * @see have_auth_info
 * @see get_logged_in_dn
 */
function get_logged_in_pass( $server_id )
{
	global $servers;
	if( ! is_numeric( $server_id ) )
		return false;
	if( ! isset( $servers[ $server_id ][ 'auth_type' ] ) )
		return false;
	$auth_type = $servers[ $server_id ][ 'auth_type' ];
	switch( $auth_type ) {
		case 'cookie':
			$cookie_name = 'pla_login_pass_' . $server_id;
			$pass = isset( $_COOKIE[ $cookie_name ] ) ? $_COOKIE[ $cookie_name ] : false;
			if( $pass == '0' )
				return null;
			else
				return $pass;
			break;
        case 'session':
			$session_var_name = 'pla_login_pass_' . $server_id;
			$pass = isset( $_SESSION[ $session_var_name ] ) ? $_SESSION[ $session_var_name ] : false;
			if( $pass == '0' )
				return null;
			else
				return $pass;
			break;
		case 'config':
			return false;
			break;
		default:
            global $lang;
			pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $auth_type ) ) );
	}
}

/**
 * Returns the DN who is logged in currently to the given server, which may 
 * either be a DN or the string 'anonymous'. This applies only for auth_types
 * "form" and "http". 
 
 * One place where this function is used is the tree viewer:
 * After a user logs in, the text "Logged in as: " is displayed under the server
 * name. This information is retrieved from this function.
 *
 * @param int $server_id The ID of the server of interest.
 *
 * @return string
 *
 * @see have_auth_info
 * @see get_logged_in_pass
 */
function get_logged_in_dn( $server_id )
{
	global $servers;
	if( ! is_numeric( $server_id ) )
		return false;
	if( ! isset( $servers[ $server_id ][ 'auth_type' ] ) )
		return false;
	$auth_type = $servers[ $server_id ][ 'auth_type' ];
	switch( $auth_type ) {
		case 'cookie':
			$cookie_name = 'pla_login_dn_' . $server_id;
			if( isset( $_COOKIE[ $cookie_name ] ) ) {
				$dn = $_COOKIE[ $cookie_name ];
			} else {
				return false;
			}
			return $dn;
			break;
		case 'session':
			$session_var_name = 'pla_login_dn_' . $server_id;
			if( isset( $_SESSION[ $session_var_name ] ) ) {
				$dn = $_SESSION[ $session_var_name ];
                return $dn;
			} else {
				return false;
			}
			break;
		case 'config':
			return false;
			break;
		default:
            global $lang;
			pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $auth_type ) ) );
	}
}

/**
 * Appends a servers base to a "sub" dn or returns the base.
 * If $get_base is true, return at least the base, otherwise null.
 */
function expand_dn_with_base( $server_id, $sub_dn, $conn = null, $get_base = true )
{
	global $servers;

	if( ! check_server_id( $server_id ) )
		return false;

	$empty_str = ( is_null($sub_dn) || ( ( $len = strlen( trim( $sub_dn ) ) ) == 0 ) );

	if ( $empty_str ) {
		// If we have no string and want not base
		if ( ! $get_base )
			return null;
	} // If we have a string which doesn't need a base
	elseif ( $sub_dn[$len - 1] != ',' )
		return $sub_dn;

	if( ( $empty_str && $get_base ) || ! $empty_str )
	{
		if( isset($servers[$server_id]['base']) ) {
			$base = $servers[$server_id]['base'];
			if ( strlen( trim( $base ) ) == 0 )
				$base = try_to_get_root_dn( $server_id, $conn );
		} else { $base = try_to_get_root_dn( $server_id, $conn ); }

		if ( $base )
			return ( ! $empty_str ) ? $sub_dn . $base : $base;
	}
	return null;
}

/**
 * Logs into the specified server using the auth_type configured for that server using
 * ldap_connect() and ldap_bind() from the PHP LDAP API.
 * If anonymous is true bind information (user / pass) is ignored (= anonymous).
 * If anonymous is null a new bind is done (i.e. user changed).
 * Returns true on success or false on failure.
 * 
 * @param int $server_id The ID of the server of interest.
 * @param bool $anonymous Set to override server config
 * @return bool
 *
 * @see get_logged_in_dn
 * @see get_logged_in_pass
 * @see have_auth_info
 * @see check_server_id
 */
function pla_ldap_connect( $server_id, $anonymous = false )
{
	if( ! check_server_id( $server_id ) )
		return false;

	if( ! $anonymous && ! have_auth_info( $server_id ) )
		return false;

	global $servers, $lang;

	// cache the connection, so if we are called multiple
	// times, we don't have to reauthenticate with the LDAP server
	static $conns;

	// We can reuse connections for multiple binds if we don't ldap_unbind
	if( isset( $conns[$server_id] ) ) {
		$conn = $conns[$server_id]['conn'];
		$status = $conns[$server_id]['stat'];

		// Status tells us, if we can use the same bind
		if( !is_null($status) && $status == $anonymous )
			return $conn;
	} else {
	$host = $servers[$server_id]['host'];

	$port = isset( $servers[$server_id]['port'] ) ? $servers[ $server_id ][ 'port' ] : false;

    if( $port )
        $conn = @ldap_connect( $host, $port );
    else
        $conn = @ldap_connect( $host );

		$conn or pla_error( sprintf( $lang['could_not_connect_to_host_on_port'], htmlspecialchars( $host ),  htmlspecialchars( $port ) ) );

	// go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
	@ldap_set_option( $conn, LDAP_OPT_PROTOCOL_VERSION, 3 );
    // Disabling this makes it possible to browse the tree for Active Directory, and seems
    // to not affect other LDAP servers (tested with OpenLDAP) as phpLDAPadmin explicitly 
    // specifies deref behavior for each ldap_search operation.
	@ldap_set_option( $conn, LDAP_OPT_REFERRALS, 0);

	// try to fire up TLS is specified in the config
    if( tls_enabled( $server_id ) ) {
		global $lang;
		function_exists( 'ldap_start_tls' ) or pla_error( $lang['php_install_not_supports_tls'] );
		@ldap_start_tls( $conn ) or pla_error( $lang['could_not_start_tls']);
	}

		// store the cached connection resource
		$conns[$server_id]['conn'] = $conn;
		$conns[$server_id]['stat'] = null;
	}

	if( $anonymous == true ) {
		$login_dn = null;
		$login_pass = null;
	} // grab the auth info based on the auth_type for this server
	elseif( $servers[ $server_id ][ 'auth_type' ] == 'config' ) {
		$login_dn = $servers[$server_id]['login_dn'];
		$login_pass = $servers[$server_id]['login_pass'];
		$login_dn = expand_dn_with_base( $server_id, $login_dn, $conn, false );
	} else {
		$login_dn = get_logged_in_dn( $server_id );
		$login_pass = get_logged_in_pass( $server_id );

		// Was this an anonyous bind (the cookie stores 0 if so)?
		if( 'anonymous' == $login_dn ) {
			$login_dn = null;
			$login_pass = null;
		}
	} 

	$res = @ldap_bind( $conn, $login_dn, $login_pass );

	if( ! $res ) return false;

	// store the bind status
	$conns[$server_id]['stat'] = $anonymous;

	return $conn;
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
 * Calling <code>get_container_contents( $server_id, "ou=people,dc=example,dc=com" )</code>
 * would return the following list:
 * 
 * <code>
 *  cn=Dave
 *  cn=Fred
 *  cn=Joe
 *  ou=More People
 * </code>
 * 
 * @param int $server_id The ID of the server housing the entry of interest
 * @param string $dn The DN of the entry whose children to return.
 * @param int $size_limit (optional) The maximum number of entries to return. 
 *             If unspecified, no limit is applied to the number of entries in the returned.
 * @param string $filter (optional) An LDAP filter to apply when fetching children, example: "(objectClass=inetOrgPerson)"
 * @return array An array of DN strings listing the immediate children of the specified entry.
 */
function get_container_contents( $server_id, $dn, $size_limit=0, $filter='(objectClass=*)', $deref=LDAP_DEREF_ALWAYS )
{
	$con = pla_ldap_connect( $server_id );
	if( ! $con ) return false;

//	echo "get_container_contents( $server_id, $dn, $size_limit, $filter, $deref )\n";
	$search = @ldap_list( $con, $dn, $filter, array( 'dn' ), 1, $size_limit, 0, $deref );
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

/**
 * Builds the initial tree that is stored in the session variable 'tree'.
 * Simply returns an array with an entry for each active server in
 * config.php. The structure of the returned array is simple, and looks like
 * this:
 * <code>
 *   Array ( 
 *      0 => Array (
 *            dc=example,dc=com => "dcobject.png"
 *          ),
 *     1 => Array (
 *            o=Corporation => "o.png"
 *          )
 *   )
 * </code>
 * This function is not meant as a user callable function, but rather a convenient, automated method for 
 * setting up the initial data structure for the tree viewer's icon cache.
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

/**
 * Builds the initial array that stores the icon-lookup for each server's DN in the tree browser. The returned
 * array is then stored in the current session. The structure of the returned array is simple, and looks like
 * this:
 * <code>
 *   Array 
 *    ( 
 *      [0] => Array 
            (
 *             [dc=example,dc=com] => "dcobject.png"
 *          )
 *      [1] => Array 
            (
 *            [o=Corporation] => "o.png"
 *          )
 *     )
 * </code>
 * This function is not meant as a user-callable function, but rather a convenient, automated method for 
 * setting up the initial data structure for the tree viewer's icon cache.
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

/*
 * Checks and fixes an initial session's tree cache if needed.
 *
 * This function is not meant as a user-callable function, but rather a convenient,
 * automated method for checking the initial data structure of the session.
 */
function initialize_session_tree()
{
	// From the PHP manual: If you use $_SESSION don't use
	// session_register(), session_is_registered() or session_unregister()!
	if( ! array_key_exists( 'tree',  $_SESSION ) )
		$_SESSION['tree'] = build_initial_tree();
	if( ! array_key_exists( 'tree_icons', $_SESSION ) )
		$_SESSION['tree_icons'] = build_initial_tree_icons();

    // Make sure that the tree index is indeed well formed.
    if( ! is_array( $_SESSION['tree'] ) )
		$_SESSION['tree'] = build_initial_tree();
    if( ! is_array( $_SESSION['tree_icons'] ) )
		$_SESSION['tree_icons'] = build_initial_tree_icons();
        
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
 * @param int $server_id the ID of the server of interest.
 * @param string $dn The DN of the entry whose interal attributes are desired.
 * @param int $deref For aliases and referrals, this parameter specifies whether to 
 *            follow references to the referenced DN or to fetch the attributes for
 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
 *            options.
 * @return array An associative array whose keys are attribute names and whose values
 *              are arrays of values for the aforementioned attribute.
 */
function get_entry_system_attrs( $server_id, $dn, $deref=LDAP_DEREF_NEVER )
{
	$conn = pla_ldap_connect( $server_id );
	if( ! $conn ) return false;

	$attrs = array( 'creatorsname', 'createtimestamp', 'modifiersname', 
			'structuralObjectClass', 'entryUUID',  'modifytimestamp', 
			'subschemaSubentry', 'hasSubordinates', '+' );
	$search = @ldap_read( $conn, $dn, '(objectClass=*)', $attrs, 0, 0, 0, $deref );
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

/**
 * Gets the attributes/values of an entry. Returns an associative array whose
 * keys are attribute value names and whose values are arrays of values for
 * said attribute. Optionally, callers may specify true for the parameter 
 * $lower_case_attr_names to force all keys in the associate array (attribute 
 * names) to be lower case. 
 * 
 * Sample return value of <code>get_object_attrs( 0, "cn=Bob,ou=pepole,dc=example,dc=com" )</code>
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
 * @param int $server_id The ID of the server of interest
 * @param string $dn The distinguished name (DN) of the entry whose attributes/values to fetch.
 * @param bool $lower_case_attr_names (optional) If true, all keys of the returned associative
 *              array will be lower case. Otherwise, they will be cased as the LDAP server returns
 *              them.
 * @param int $deref For aliases and referrals, this parameter specifies whether to 
 *            follow references to the referenced DN or to fetch the attributes for
 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
 *            options.
 * @return array
 * @see get_entry_system_attrs
 * @see get_object_attr
 */
function get_object_attrs( $server_id, $dn, $lower_case_attr_names=false, $deref=LDAP_DEREF_NEVER )
{
	//echo "get_object_attrs( $server_id, $dn, $lower_case_attr_names )<br />";
	$conn = pla_ldap_connect( $server_id );
	if( ! $conn ) return false;

	$search = @ldap_read( $conn, $dn, '(objectClass=*)', array( ), 0, 0, 0, $deref );

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
		if( is_attr_binary( $server_id, $attr ) )
			$vals = ldap_get_values_len( $conn, $entry, $attr );
		unset( $vals['count'] );
		$return_array[ $attr ] = $vals;
	}

	ksort( $return_array );

	return $return_array;
}

/**
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

/**
 * Much like get_object_attrs(), but only returns the values for
 * one attribute of an object. Example calls:
 *
 * <code>
 * print_r( get_object_attr( 0, "cn=Bob,ou=people,dc=example,dc=com", "sn" ) );
 * // prints:
 * //  Array 
 * //    ( 
 * //       [0] => "Smith"
 * //    )
 *
 * print_r( get_object_attr( 0, "cn=Bob,ou=people,dc=example,dc=com", "objectClass" ) );
 * // prints:
 * //  Array 
 * //    ( 
 * //       [0] => "top"
 * //       [1] => "person"
 * //    )
 * </code>
 * 
 * @param int $server_id The ID of the server of interest
 * @param string $dn The distinguished name (DN) of the entry whose attributes/values to fetch.
 * @param string $attr The attribute whose value(s) to return (ie, "objectClass", "cn", "userPassword")
 * @param bool $lower_case_attr_names (optional) If true, all keys of the returned associative
 *              array will be lower case. Otherwise, they will be cased as the LDAP server returns
 *              them.
 * @param int $deref For aliases and referrals, this parameter specifies whether to 
 *            follow references to the referenced DN or to fetch the attributes for
 *            the referencing DN. See http://php.net/ldap_search for the 4 valid
 *            options.
 * @see get_object_attrs
 */
function get_object_attr( $server_id, $dn, $attr, $deref=LDAP_DEREF_NEVER )
{
	/**
	$attr = strtolower( $attr );
	$attrs = get_object_attrs( $server_id, $dn, true );
	if( isset( $attrs[$attr] ) )
		return $attrs[$attr];
	else
		return false;
		*/

	//echo "get_object_attr( $server_id, $dn, $attr )<br />";
	$conn = pla_ldap_connect( $server_id );
	if( ! $conn ) return false;

	$search = @ldap_read( $conn, $dn, '(objectClass=*)', array( $attr ), 0, 0, 0, $deref );

	if( ! $search )
		return false;

	$entry = ldap_first_entry( $conn, $search );

	if( ! $entry )
		return false;
	
	$attrs = ldap_get_attributes( $conn, $entry );

	if( ! $attrs || $attrs['count'] == 0 )
		return false;

	if( is_attr_binary( $server_id, $attr ) )
		$vals = ldap_get_values_len( $conn, $entry, $attr );
	else
		$vals = ldap_get_values( $conn, $entry, $attr );
	unset( $vals['count'] );
	return $vals;
}

/**
 * A handy ldap searching function very similar to PHP's ldap_search() with the 
 * following exceptions: Callers may specify a search scope and the return value 
 * is an array containing the search results rather than an LDAP result resource.
 *
 * Example usage:
 * <code>
 * $samba_users = ldap_search( 0, "(&(objcetClass=sambaAccount)(objectClass=posixAccount))", 
 *                              "ou=People,dc=example,dc=com", array( "uid", "homeDirectory" ) );
 * print_r( $samba_users );
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
 * @param int $server_id The ID of the server to search on.
 * @param string $filter The LDAP filter to use when searching (example: "(objectClass=*)") (see RFC 2254)
 * @param string $base_dn The DN of the base of search. 
 * @param array $attrs An array of attributes to include in the search result (example: array( "objectClass", "uid", "sn" )).
 * @param string $scope The LDAP search scope. Must be one of "base", "one", or "sub". Standard LDAP search scope.
 * @param bool $sort_results Specify false to not sort results by DN or true to have the 
 *                  returned array sorted by DN (uses ksort)
 * @param int $deref When handling aliases or referrals, this specifies whether to follow referrals. Must be one of 
 *                  LDAP_DEREF_ALWAYS, LDAP_DEREF_NEVER, LDAP_DEREF_SEARCHING, or LDAP_DEREF_FINDING. See the PHP LDAP API for details.
 */
function pla_ldap_search( $server_id, $filter, $base_dn=null, $attrs=array(), $scope='sub', $sort_results=true, $deref=LDAP_DEREF_ALWAYS )
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
			$search = @ldap_read( $ds, $base_dn, $filter, $attrs, 0, 0, 0, $deref );
			break;
		case 'one':
			$search = @ldap_list( $ds, $base_dn, $filter, $attrs, 0, 0, 0, $defef );
			break;
		case 'sub':
		default:
			$search = @ldap_search( $ds, $base_dn, $filter, $attrs, 0, 0, 0, $deref );
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

/**
 * Reads the query, checks all values and sets defaults.
 *
 * @param int $query_id The ID of the predefined query.
 *
 * @return array The fixed query or null on error
 */
function get_fixed_query($query_id)
{
	global $queries;

	if( ! isset( $queries[$query_id] ) )
		return null;

	$query = $queries[$query_id];

	if( isset( $query['server'] ) && ( is_numeric( $query['server'] ) ) )
		$server_id = $query['server'];
	else $server_id = 0;

	$base = ( isset( $query['base'] ) ) ? $query['base'] : null;
	$base = expand_dn_with_base( $server_id, $base );

	if( isset( $query['filter'] ) && strlen( trim( $query['filter'] ) ) > 0 )
		$filter = $query['filter'];
	else $filter = 'objectclass=*';

	$scope = isset( $query['scope'] )
		&& ( in_array( $query['scope'], array( 'base', 'sub', 'one' ) ) )
		? $query['scope'] : 'sub';

	if( isset( $query['attributes'] ) && strlen( trim( $query['filter'] ) ) > 0 )
		$attrib = $query['attributes'];
	else $attrib = "dn, cn";

	return array ( 'server' => $server_id, 'base' => $base,
		'filter' => $filter, 'scope' => $scope, 'attributes' => $attrib );
}

/**
 * Transforms the user-configured search lists into arrays for use by other components of phpLDAPadmin. 
 * This may seem a little strange, and that's because it is strange.
 * 
 * The function takes the comma-separated lists (like the search result attribute list) in config.php
 * and turns them into arrays. Only call this ONCE per script. Any subsequent call will
 * mess up the arrays. This function operates on global variables defined in config.php and is currently
 * only used by search_form_simple.php
 * 
 * For more details, just read the function's code. It's short and pretty straightforward.
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

/**
 * Trim a string in place (call by reference) Used to filter empty entries out of the arrays
 * that we generate in process_config().
 */
function trim_it( &$str )
{
	$str = trim($str);
}

/**
 * Checks the specified server id for sanity. Ensures that the server is indeed in the configured 
 * list and active. This is used by many many scripts to ensure that valid server ID values 
 * are passed in POST and GET.
 */
function check_server_id( $server_id )
{
	global $servers;
	if( ! is_numeric( $server_id ) || ! isset( $servers[$server_id] ) || ! isset( $servers[$server_id]['host'] ) || $servers[$server_id]['host'] == '' )
		return false;
	else
		return true;
}

/**
 * Used to generate a random salt for crypt-style passwords. Salt strings are used 
 * to make pre-built hash cracking dictionaries difficult to use as the hash algorithm uses 
 * not only the user's password but also a randomly generated string. The string is 
 * stored as the first N characters of the hash for reference of hashing algorithms later.
 *
 * --- added 20021125 by bayu irawan <bayuir@divnet.telkom.co.id> ---
 * --- ammended 20030625 by S C Rigler <srigler@houston.rr.com> ---
 *
 * @param int $length The length of the salt string to generate.
 * @return string The generated salt string.
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
	/**
	 * Commented out following line because of problem
	 * with crypt function in update.php
	 * --- 20030625 by S C Rigler <srigler@houston.rr.com> ---
	 */
        //$str = "\$1\$".$str."\$";
        return $str;
}

/**
 * Goes through the user-configured server list and looks for an available server_id,
 * one that has specified enough information to login. This is for choosing the
 * server to display in the drop-down box in search.php.
 *
 * @return int The first available server ID found.
 */
function get_avail_server_id()
{
	global $servers;

	for( $i=0; $i<count($servers); $i++ )
		if( check_server_id( $i ) &&  have_auth_info( $i ) )
			return $i;
	return false;
}

/**
 * Given a DN string, this returns the 'RDN' portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'cn=Manager' (it is really the exact opposite of get_container()).
 *
 * @param string $dn The DN whose RDN to return.
 * @param bool $include_attrs If true, include attributes in the RDN string. 
 *               See http://php.net/ldap_explode_dn for details
 *
 * @return string The RDN
 * @see get_container
 */
function get_rdn( $dn, $include_attrs=0 )
{
	if( $dn == null )
		return null;
	$rdn = pla_explode_dn( $dn, $include_attrs );
	if( 0 == count($rdn) )
		return $dn;
	if( ! isset( $rdn[0] ) )
		return $dn;
	$rdn = $rdn[0];
	return $rdn;
}

/**
 * Given a DN string, this returns the parent container portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'dc=example,dc=com'.
 * 
 * @param string $dn The DN whose container string to return.
 *
 * @return string The container
 * @see get_rdn
 */
function get_container( $dn )
{
	$parts = pla_explode_dn( $dn );
    if( count( $parts ) <= 1 )
        return null;
	$container = $parts[1];
	for( $i=2; $i<count($parts); $i++ )
		$container .= ',' . $parts[$i];
	return $container;
}

/**
 * Given an LDAP error number, returns a verbose description of the error.
 * This function parses ldap_error_codes.txt and looks up the specified
 * ldap error number, and returns the verbose message defined in that file.
 *
 * @param string $err_no The hex error number (ie, "0x42") of the LDAP error of interest.
 * @return array An associative array contianing the error title and description like so:
 *   <code>
 *  Array 
 *    (
 *       [title] => "Invalid Credentials"
 *       [description] => "An invalid username and/or password was supplied to the LDAP server."
 *    )
 *   </code>
 */
function pla_verbose_error( $err_no )
{
	static $err_codes;
	if( count($err_codes) > 0 ) {
        if( isset( $err_codes[ $err_no ] ) )
            return $err_codes[ $err_no ];
        else
            return array( 'title' => null, 'desc' => null );
	} 

	$err_codes_file = 'ldap_error_codes.txt';

	if( ! file_exists( realpath( $err_codes_file ) ) )
		return false;
	if( ! is_readable( realpath( $err_codes_file ) ) )
		return false;
	if( ! ($f = fopen( realpath( $err_codes_file ), 'r' ) ) )
		return false;

	$contents = fread( $f, filesize( $err_codes_file ) );
    fclose( $f );
	$entries = array();
	preg_match_all( "/0x[A-Fa-f0-9][A-Za-z0-9]\s+[0-9A-Za-z_]+\s+\"[^\"]*\"\n/", $contents, $entries );
	$err_codes = array();
	foreach( $entries[0] as $e ) {
		$entry = array();
		preg_match( "/(0x[A-Za-z0-9][A-Za-z0-9])\s+([0-9A-Za-z_]+)\s+\"([^\"]*)\"/", $e, $entry );
		$hex_code = isset( $entry[1] ) ? $entry[1] : null;
		$title    = isset( $entry[2] ) ? $entry[2] : null;
		$desc     = isset( $entry[3] ) ? $entry[3] : null;
		$desc     = preg_replace( "/\s+/", " ", $desc );
		$err_codes[ "$hex_code" ] = array( 'title' => $title, 'desc' => $desc );
	}

    // Sanity check
    if( isset( $err_codes[ $err_no ] ) )
    	return $err_codes[ $err_no ];
    else
        return array( 'title' => null, 'desc' => null );
}

/**
 * Prints an HTML-formatted error string. If you specify the optional
 * parameters $ldap_err_msg and $ldap_err_no, this function will 
 * lookup the error number and display a verbose message in addition 
 * to the message you pass it.
 * 
 * @param string $msg The error message to display.
 * @param string $ldap_err_msg (optional) The error message supplied by the LDAP server 
 * @param string $ldap_err_no (optional) The hexadecimal error number string supplied by the LDAP server 
 * @param bool $fatal (optional) If true, phpLDAPadmin will terminate execution with the PHP die() function.
 *
 * @see die
 * @see ldap_errno
 * @see pla_verbose_error
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

	if( $ldap_err_msg ) {
		echo sprintf($lang['ldap_said'], htmlspecialchars( $ldap_err_msg ));
		echo '<br />';
		}

	if( $ldap_err_no != -1 ) {
		$ldap_err_no = ( '0x' . str_pad( dechex( $ldap_err_no ), 2, 0, STR_PAD_LEFT ) );
		$verbose_error = pla_verbose_error( $ldap_err_no );

		if( $verbose_error ) {
			echo sprintf( $lang['ferror_number'], $ldap_err_no, $verbose_error['title']);
			echo '<br />';
			echo sprintf( $lang['ferror_discription'], $verbose_error['desc']);
		} else {
			echo sprintf($lang['ferror_number_short'], $ldap_err_no);
			echo '<br />';
			echo $lang['ferror_discription_short'];
		}
	}
	?>
	<br />
	<!-- Commented out due to too many false bug reports. :)
	<br />
	<center>
	<small>
		<?php echo sprintf($lang['ferror_submit_bug'] , get_href( 'add_bug' ));?>
	</small>
	</center>
	-->
	</td></tr></table>
	</center>
	<?php

	if( $fatal )
		die();
}

/**
 * phpLDAPadmin's custom error handling function. When a PHP error occurs,
 * PHP will call this function rather than printing the typical PHP error string. 
 * This provides phpLDAPadmin the ability to format an error message more "pretty"
 * and provide a link for users to submit a bug report. This function is not to
 * be called by users. It is exclusively for the use of PHP internally. If this
 * function is called by PHP from within a context where error handling has been
 * disabled (ie, from within a function called with "@" prepended), then this
 * function does nothing.
 *
 * @param int $errno The PHP error number that occurred (ie, E_ERROR, E_WARNING, E_PARSE, etc).
 * @param string $errstr The PHP error string provided (ie, "Warning index "foo" is undefined)
 * @param string $file The file in which the PHP error ocurred.
 * @param int $lineno The line number on which the PHP error ocurred
 *
 * @see set_error_handler
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

	$server = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'undefined';
	$phpself = isset( $_SERVER['PHP_SELF'] ) ? basename( $_SERVER['PHP_SELF'] ) : 'undefined';
	pla_error( sprintf($lang['ferror_congrats_found_bug'], $errstr, $errtype, $file, 
							$lineno, $phpself, pla_version(), 
							phpversion(), php_sapi_name(), $server ));
}

/**
 * Reads the friendly_attrs array as defined in config.php and lower-cases all
 * the keys. Will return an empty array if the friendly_attrs array is not defined
 * in config.php. This is simply used so we can more easily lookup user-friendly
 * attributes configured by the admin.
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

/**
 * Gets whether an entry exists based on its DN. If the entry exists, 
 * returns true. Otherwise returns false.
 *
 * @param int $server_id The ID of the server of interest
 * @param string $dn The DN\of the entry of interest.
 *
 * @return bool
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

/**
 * Draw the jpegPhoto image(s) for an entry wrapped in HTML. Many options are available to 
 * specify how the images are to be displayed.
 *
 * Usage Examples:
 *  <code>
 *    draw_jpeg_photos( 0, "cn=Bob,ou=People,dc=example,dc=com", "jpegPhoto" true, false, "border: 1px; width: 150px" );
 *    draw_jpeg_photos( 1, "cn=Fred,ou=People,dc=example,dc=com" );
 *  </code>
 *
 * @param int $server_id The ID of the server of interest.
 * @param string $dn The DN of the entry that contains the jpeg attribute you want to draw.
 * @param string $attr_name The name of the attribute containing the jpeg data (usually 'jpegPhoto').
 * @param bool $draw_delete_buttons If true, draws a button beneath the image titled 'Delete' allowing the user
 *                  to delete the jpeg attribute by calling JavaScript function deleteAttribute() provided
 *                  in the default modification template.
 * @param bool $draw_bytes_and_size If true, draw text below the image indicating the byte size and dimensions.
 * @param string $table_html_attrs Specifies optional CSS style attributes for the table tag.
 *
 * @return void
 */
function draw_jpeg_photos( $server_id, $dn, $attr_name='jpegPhoto', $draw_delete_buttons=false,
				$draw_bytes_and_size=true, $table_html_attrs='align="right"')
{
	global $jpeg_temp_dir;
	global $jpeg_tmp_keep_time;
	global $lang;

	$conn = pla_ldap_connect( $server_id );
	$search_result = ldap_read( $conn, $dn, 'objectClass=*', array( $attr_name ) );
	$entry = ldap_first_entry( $conn, $search_result );

	echo "<table $table_html_attrs><td><center>\n\n";
	// for each jpegPhoto in the entry, draw it (there may be only one, and that's okay)
	$jpeg_data = ldap_get_values_len( $conn, $entry, $attr_name );
	for( $i=0; $i<$jpeg_data['count']; $i++ )
	{
		// ensures that the photo is written to the specified jpeg_temp_dir
		$jpeg_temp_dir = realpath($jpeg_temp_dir.'/');
        if( ! is_writable( $jpeg_temp_dir ) )
            pla_error( 'Please set $jpeg_temp_dir to a writable directory in the phpLDAPadmin config.php' );
		$jpeg_filename = tempnam($jpeg_temp_dir.'/', 'pla');
		$outjpeg = @fopen($jpeg_filename, "wb");
        if( ! $outjpeg )
            pla_error( 'Could not write to the $jpeg_temp_dir directory (' . $jpeg_temp_dir . '). Please verify that your web server can write files there.' );
		fwrite($outjpeg, $jpeg_data[$i]);
		fclose ($outjpeg);
		$jpeg_data_size = filesize( $jpeg_filename );
		if( $jpeg_data_size < 6 && $draw_delete_buttons ) {
			echo $lang['jpeg_contains_errors'];
			echo '<a href="javascript:deleteAttribute( \'' . $attr_name . '\' );" style="color:red; font-size: 75%">'. $lang['delete_photo'] .'</a>';
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
		if( $draw_bytes_and_size ) {
			echo "<small>" . number_format($jpeg_data_size) . " bytes. ";
			echo "$width x $height pixels.<br /></small>\n\n";
		}

		if( $draw_delete_buttons )
		{ ?>
			<!-- JavaScript function deleteJpegPhoto() to be defined later by calling script -->
			<a href="javascript:deleteAttribute( '<?php echo $attr_name; ?>' );" style="color:red; font-size: 75%">Delete Photo</a>
		<?php }
	}
	echo "</center></td></table>\n\n";

	// If they have misconfigured their config.php, use default values
	if( ! isset( $jpeg_tmp_keep_time ) )
		$jpeg_tmp_keep_time = 120;

	// If they set keep time to 0, we up it to 10 to allow the browser to fetch it before it is deleted.
	if( $jpeg_tmp_keep_time == 0 )
		$jpeg_tmp_keep_time = 10;

	// delete old jpeg files.
	$jpegtmp_wildcard = "/^pla/";
	$handle = opendir($jpeg_temp_dir);
	while( ($file = readdir($handle) ) != false ) {
		if( preg_match( $jpegtmp_wildcard, $file ) ) {
			$file = "$jpeg_temp_dir/$file";
			if( (time() - filemtime($file) ) > $jpeg_tmp_keep_time )
				@unlink( $file );
		}
	}
	closedir($handle);
}

/**
 * Gets the root DN of the specified server_id, or false if it
 * can't find it (ie, the server won't give it to us). This is 
 * used when the user leaves the $servers[$i]['base'] value empty
 * to auto-determine the root DN.
 *
 * Tested with OpenLDAP 2.0, Netscape iPlanet, and Novell eDirectory 8.7 (nldap.com)
 * Please report any and all bugs!!
 *
 * @param int $server_id The ID of the server whose root DN to fetch.
 * @return mixed The root DN of the server on success (string) or false on error.
 */
function try_to_get_root_dn( $server_id, $ds = null )
{
	if( ! check_server_id( $server_id ) )
		return false;

	if( isset( $_SESSION[ "pla_root_dn_$server_id" ] ) )
		return $_SESSION[ "pla_root_dn_$server_id" ];

	if ( $ds == null ) {
	if( ! have_auth_info( $server_id ) )
		return false;

	$ds = pla_ldap_connect( $server_id );
		if ( ! $ds )
		return false;
	}

	$r = @ldap_read( $ds, '', 'objectClass=*', array( 'namingContexts' ) );
	if( ! $r )
		return false;

	$r = @ldap_get_entries( $ds, $r );
	if( isset( $r[0]['namingcontexts'][0] ) ) {
		$root_dn = $r[0]['namingcontexts'][0];
		$_SESSION[ "pla_root_dn_$server_id" ] = $root_dn;
		return $root_dn;
	} else {
		return false;
	}
}

/**
 * Hashes a password and returns the hash based on the specified enc_type. 
 *
 * @param string $password_clear The password to hash in clear text.
 * @param string $enc_type Standard LDAP encryption type which must be one of
 *          crypt, md5, md5crypt, sha, smd5, ssha, or clear.
 * @return string The hashed password.
 */
function password_hash( $password_clear, $enc_type )
{
	global $lang;
    $enc_type = strtolower( $enc_type );
    switch( $enc_type )
    {
        case 'crypt':
            $new_value = '{CRYPT}' . crypt( $password_clear, random_salt(2) );
            break;
        case 'md5':
            $new_value = '{MD5}' . base64_encode( pack( 'H*' , md5( $password_clear) ) );
            break;
        case 'md5crypt':
            if( ! defined( 'CRYPT_MD5' ) || 0 == CRYPT_MD5 )
                pla_error( $lang['install_not_support_md5crypt'] );
            $new_value = '{CRYPT}' . crypt( $password_clear , '$1$' . random_salt(9) );
            break;
        case 'blowfish':
            if( ! defined( 'CRYPT_BLOWFISH' ) || 0 == CRYPT_BLOWFISH )
                pla_error( $lang['install_not_support_blowfish'] );
            $new_value = '{CRYPT}' . crypt( $password_clear , '$2$' . random_salt(13) );
            break;
        case 'sha':
            if( function_exists( 'mhash' ) ) {
                $new_value = '{SHA}' . base64_encode( mhash( MHASH_SHA1, $password_clear) );
            } else {
                pla_error( $lang['install_no_mash'] );
            }
            break;
        case 'ssha':
            if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
                mt_srand( (double) microtime() * 1000000 );
                $salt = mhash_keygen_s2k( MHASH_SHA1, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
                $new_value = "{SSHA}".base64_encode( mhash( MHASH_SHA1, $password_clear.$salt ).$salt );
            } else {
                pla_error( $lang['install_no_mash'] );
            }
            break;
        case 'smd5':
            if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
                mt_srand( (double) microtime() * 1000000 );
                $salt = mhash_keygen_s2k( MHASH_MD5, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
                $new_value = "{SMD5}".base64_encode( mhash( MHASH_MD5, $password_clear.$salt ).$salt );
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

/**
 * Returns the phpLDAPadmin version currently running. The version
 * is read from the file named VERSION.
 *
 * @return string The current version as read from the VERSION file.
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

/** 
 * Draws an HTML browse button which, when clicked, pops up a DN chooser dialog.
 * @param string $form_element The name of the form element to which this chooser
 *         dialog will publish the user's choice. The form element must be a member
 *         of a form with the "name" or "id" attribute set in the form tag, and the element
 *         must also define "name" or "id" for JavaScript to uniquely identify it.
 *         Example $form_element values may include "creation_form.container" or 
 *         "edit_form.member_uid". See /templates/modification/default.php for example usage.
 * @param bool $include_choose_text (optional) If true, the function draws the localized text "choose" to the right of the button.
 */
function draw_chooser_link( $form_element, $include_choose_text=true )
{
	global $lang;
	$href = "javascript:dnChooserPopup('$form_element');";
	$title = $lang['chooser_link_tooltip'];
	echo "<nobr><a href=\"$href\" title=\"$title\"><img class=\"chooser\" src=\"images/find.png\" /></a>";
	if( $include_choose_text )
		echo "<span class=\"x-small\"><a href=\"$href\" title=\"$title\">". $lang['fbrowse'] ."</a></span>";
    echo "</nobr>";
}

/**
 * Explode a DN into an array of its RDN parts. This function is UTF-8 safe
 * and replaces the buggy PHP ldap_explode_dn() which does not properly
 * handle UTF-8 DNs and also causes segmentation faults with some inputs.
 *
 * @param string $dn The DN to explode.
 * @param int $with_attriutes (optional) Whether to include attribute names (see http://php.net/ldap_explode_dn for details)
 *
 * @return array An array of RDN parts of this format:
 * <code>
 *   Array
 *    (
 *       [0] => uid=ppratt
 *       [1] => ou=People
 *       [2] => dc=example
 *       [3] => dc=com
 *    )
 * </code>
 */
function pla_explode_dn( $dn, $with_attributes=0 )
{
  // replace "\," with the hexadecimal value for safe split
  $var = preg_replace("/\\\,/","\\\\\\\\2C",$dn);

  // split the dn
  $result = explode(",",$var);
  
  //translate hex code into ascii for display
  foreach( $result as $key => $value )
    $result[$key] = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $value);
  
  return $result;
}

/**
 * Fetches the URL for the specified item. This is a convenience function for 
 * fetching project HREFs (like bugs)
 *
 * @param string $type One of "open_bugs", "add_bug", "open_rfes" 
 *            (rfe = request for enhancement), or "add_rfe".
 * @return string The URL to the requested item.
 */
function get_href( $type )
{
	$group_id = "61828";
	$bug_atid = "498546";
	$rfe_atid = "498549";
	switch( $type ) {
	case 'open_bugs': return "http://sourceforge.net/tracker/?group_id=$group_id&amp;atid=$bug_atid";
	case 'add_bug': return "http://sourceforge.net/tracker/?func=add&amp;group_id=$group_id&amp;atid=$bug_atid";
	case 'open_rfes': return "http://sourceforge.net/tracker/?atid=$rfe_atid&group_id=$group_id&amp;func=browse";
	case 'add_rfe': return "http://sourceforge.net/tracker/?func=add&amp;group_id=$group_id&amp;atid=$rfe_atid";
    case 'donate': return "https://sourceforge.net/donate/index.php?group_id=$group_id";
	default: return null;
	}
}

/**
 * Returns the current time as a double (including micro-seconds).
 *
 * @return double The current time in seconds since the beginning of the UNIX epoch (Midnight Jan. 1, 1970)
 */
function utime ()
{
	$time = explode( " ", microtime());
 	$usec = (double)$time[0];
 	$sec = (double)$time[1];
 	return $sec + $usec;
}

/**
 * Converts an array to a query-string with the option to exclude certain variables 
 * from the returned query string. This is convenient if callers want to convert the
 * current GET query string or POST array into a string and replace certain 
 * variables with their own.
 * 
 * @param array $array The associate array to convert whose form is such that the keys are the 
 *          names of the variables and the values are said variables' values like this:
 *          <code>
 *             Array
 *                (
 *                   [server_id] = 0,
 *                   [dn] = "dc=example,dc=com",
 *                   [attr] = "sn"
 *                 )
 *          </code>
 *          This will produce a string like this: "server_id=0&dn=dc=example,dc=com&attr=sn"
 * @param array $exclude_vars (optional) An array of variables to exclude in the resulting string
 * @param bool $url_encode_ampersands (optional) By default, this function encodes all ampersand-separators
 *             as &amp; but callers may dislabe this by specifying false here. For example, URLs on HTML
 *             pages should encode the ampersands but URLs in header( "Location: http://example.com" ) should
 *             not be encoded.
 * @return string The string created from the array.
 */
function array_to_query_string( $array, $exclude_vars=array(), $url_encode_ampersands=true )
{
	if( ! is_array( $array ) )
		return '';
	if( ! $array )
		return '';
	$str = '';
	$i=0;
	foreach( $array as $name => $val ) {
		if( ! in_array( $name, $exclude_vars ) ) {
			if( $i>0 )
				if( $url_encode_ampersands )
					$str .= '&amp;';
				else
					$str .= '&';
			$str .= urlencode( $name ) . '=' . urlencode( $val );
			$i++;
		}
	}
	return $str;
}

/**
 * Gets whether the admin has configured phpLDAPadmin to show the "Create New" link in the tree viewer.
 * If $servers[$server_id]['show_create'] is NOT set, then default to show the Create New item.
 * If $servers[$server_id]['show_create'] IS set, then return the value (it should be true or false).
 *
 * @param int $server_id The ID of the server of interest
 * @return bool True if the feature is enabled and false otherwise.
 */
function show_create_enabled( $server_id )
{
	global $servers;
	if( isset( $servers[$server_id]['show_create'] )) 
		return $servers[$server_id]['show_create'];
	else
		return true;
}

/**
 * Reverses a DN such that the top-level RDN is first and the bottom-level RDN is last
 * For example:
 * <code>
 *   cn=Brigham,ou=People,dc=example,dc=com
 * </code>
 * Becomes: 
 * <code>
 *   dc=com,dc=example,ou=People,cn=Brigham
 * </code>
 * This makes it possible to sort lists of DNs such that they are grouped by container.
 *
 * @param string $dn The DN to reverse
 *
 * @return string The reversed DN
 *
 * @see pla_compare_dns
 */
function pla_reverse_dn($dn)
{
	foreach (pla_explode_dn($dn) as $key => $branch) {

		// pla_expode_dn returns the array with an extra count attribute, we can ignore that.
		if ( $key === "count" ) continue;

		if (isset($rev)) {
			$rev = $branch.",".$rev;
		} else {
			$rev = $branch;
		}
	}
	return $rev;
}

/**
 * Strips all slashes from the specified array in place (pass by ref).
 * @param Array $array The array to strip slashes from, typically one of 
 *             $_GET, $_POST, or $_COOKIE.
 */
function array_stripslashes(&$array)
{
    if( is_array( $array ) )
        while ( list( $key ) = each( $array ) ) 
            if ( is_array( $array[$key] ) && $key != $array ) 
                array_stripslashes( $array[$key] );
            else 
                $array[$key] = stripslashes( $array[$key] );
}

?>
