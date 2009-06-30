<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/update.php,v 1.21 2005/03/16 11:20:25 wurley Exp $

/**
 *  Updates or deletes a value from a specified attribute for a specified dn.
 *
 *  Variables that come in on the query string:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - update_array (an array in the form expected by PHP's ldap_modify, except for deletions)
 *     (will never be empty: update_confirm.php ensures that)
 *
 * Attribute deletions:
 * To specify that an attribute is to be deleted (whether multi- or single-valued),
 *  enter that attribute in the update array like this: attr => ''. For example, to
 *  delete the 'sn' attribute from an entry, the update array would look like this:
 *  Array (
 *     sn => ''
 *  )
 *
 * On success, redirect to edit.php. On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

require realpath( 'common.php' );

$server_id = (isset($_POST['server_id']) ? $_POST['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$dn = $_POST['dn'];
$encoded_dn = rawurlencode( $dn );
$update_array = isset( $_POST['update_array'] ) ? $_POST['update_array'] : array();
is_array( $update_array ) or pla_error( $lang['update_array_malformed'] );

$failed_attrs = array();

run_hook ( 'pre_update', array ( 'server_id' => $server_id,
	 'dn' => $dn, 'update_array' => &$update_array) );

// check for delete attributes (indicated by the attribute entry appearing like this: attr => ''
foreach( $update_array as $attr => $val )
	if( ! is_array( $val ) )
		if( $val == '' )
			$update_array[ $attr ] = array();
		else
			$update_array[ $attr ] = $val;
	else
		foreach( $val as $i => $v )
			$update_array[ $attr ][ $i ] = $v;

// Call the custom callback for each attribute modification
// and verify that it should be modified.
foreach( $update_array as $attr_name => $val ) {

	// Check to see if this is a unique Attribute
	if( $badattr = checkUniqueAttr( $ldapserver, $dn, $attr_name, $val ) ) {
		$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',$server_id,$attr_name,$badattr);
		pla_error(sprintf( $lang['unique_attr_failed'] , $attr_name,$badattr,$dn,$search_href ) );
	}

	if ( true !== run_hook ( 'pre_attr_modify', array ( 'server_id' => $server_id,
		'dn' => $dn, 'attr_name' => $attr_name, 'new_value' => $val ) ) ) {

		unset( $update_array[ $attr_name ] );
		$failed_attrs[$attr_name] = $val;
	}

	elseif( is_attr_read_only( $ldapserver, $attr ) )
		pla_error( sprintf( $lang['attr_is_read_only'], htmlspecialchars( $attr_name ) ) );
}

$res = @ldap_modify( $ldapserver->connect(), $dn, $update_array );
if( $res ) {
	// Fire the post modification event to the user's custom
	// callback function.
	foreach( $update_array as $attr_name => $val ) {
		run_hook ( 'post_attr_modify', array('server_id' => $server_id,
		     'dn' => $dn, 'attr_name' => $attr_name, 'new_value' => $val ) );

		// Was this a user's password modification who is currently
		// logged in? If so, they need to logout and log back in
		// with the new password.
		if( 0 === strcasecmp( $attr_name, 'userPassword' ) &&
			in_array($ldapserver->auth_type,array( 'cookie','session' )) &&
			0 === pla_compare_dns( get_logged_in_dn( $ldapserver ), $dn ) ) {

			unset_login_dn( $ldapserver );
			unset_lastactivity( $ldapserver );
			include realpath( 'header.php' );
			?>

			<script language="javascript">
				parent.left_frame.location.reload();
			</script>
			<br />
			<center>
			<b><?php echo $lang['modification_successful']; ?></b><br />
			<br />
			<?php echo $lang['change_password_new_login']; ?> &nbsp;
			<a href="login_form.php?server_id=<?php echo $server_id; ?>"><?php echo $lang['login_link']; ?></a>
			</center>
			</body>
			</html>

			<?php

			exit;
		}
	}

	run_hook ( 'post_update', array ( 'server_id' => $server_id, 'dn' => $dn, 'update_array' => &$update_array) );

	$redirect_url = sprintf("edit.php?server_id=%s&dn=%s",$server_id,$encoded_dn);

	foreach( $update_array as $attr => $junk )
		$redirect_url .= "&modified_attrs[]=$attr";

	foreach( $failed_attrs as $attr => $junk )
		$redirect_url .= "&failed_attrs[]=$attr";

	header( "Location: $redirect_url" );

} else {
	pla_error( $lang['could_not_perform_ldap_modify'], ldap_error( $ldapserver->connect() ), ldap_errno( $ldapserver->connect() ) );
}
?>
