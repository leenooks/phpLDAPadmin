<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/update.php,v 1.15 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/* 
 *  update.php
 *  Updates or deletes a value from a specified 
 *  attribute for a specified dn.
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
 * On success, redirect to edit.php
 * On failure, echo an error.
 */

require realpath( 'common.php' );


$server_id = $_POST['server_id'];

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

$dn = $_POST['dn'];
$encoded_dn = rawurlencode( $dn );
$update_array = $_POST['update_array'];

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
is_array( $update_array ) or pla_error( $lang['update_array_malformed'] );
pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );

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
foreach( $update_array as $attr_name => $val )
		if( true !== preAttrModify( $server_id, $dn, $attr_name, $val ) )
			unset( $update_array[ $attr_name ] );
		elseif( is_attr_read_only( $attr ) )
	pla_error( sprintf( $lang['attr_is_read_only'], htmlspecialchars( $attr_name ) ) );

$ds = pla_ldap_connect( $server_id );
$res = @ldap_modify( $ds, $dn, $update_array );
if( $res )
{
	// Fire the post modification event to the user's custom
	// callback function.
	foreach( $update_array as $attr_name => $val ) {
		postAttrModify( $server_id, $dn, $attr_name, $val );

		// Was this a user's password modification who is currently
		// logged in? If so, they need to logout and log back in
		// with the new password.
		if( 0 === strcasecmp( $attr_name, 'userPassword' ) &&
			check_server_id( $server_id ) &&
			isset( $servers[ $server_id ][ 'auth_type' ] ) && 
			( $servers[ $server_id ][ 'auth_type' ] == 'cookie' ||
			$servers[ $server_id ][ 'auth_type' ] == 'session' ) &&
			0 === pla_compare_dns( get_logged_in_dn( $server_id ), $dn ) )
		{
			unset_login_dn( $server_id );
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

	$redirect_url = "edit.php?server_id=$server_id&dn=$encoded_dn";
	foreach( $update_array as $attr => $junk )
		$redirect_url .= "&modified_attrs[]=$attr";
	header( "Location: $redirect_url" );
}
else
{
	pla_error( $lang['could_not_perform_ldap_modify'], ldap_error( $ds ), ldap_errno( $ds ) );
}

?>
