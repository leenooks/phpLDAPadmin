<?php 

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

require 'common.php';

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

$server_id = $_POST['server_id'];
$dn = rawurldecode( $_POST['dn'] );
$encoded_dn = rawurlencode( $dn );
$update_array = $_POST['update_array'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
is_array( $update_array ) or pla_error( "update_array is malformed. This might be a phpLDAPadmin bug. Please report it." );

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

$ds = pla_ldap_connect( $server_id );
$res = @ldap_modify( $ds, $dn, $update_array );
if( $res )
{
	$redirect_url = "edit.php?server_id=$server_id&dn=$encoded_dn";
	foreach( $update_array as $attr => $junk )
		$redirect_url .= "&modified_attrs[]=$attr";
	header( "Location: $redirect_url" );
}
else
{
	pla_error( "Could not perform ldap_modify operation.", ldap_error( $ds ), ldap_errno( $ds ) );
}

?>
