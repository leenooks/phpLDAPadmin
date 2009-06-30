<?php 

/* 
 *  delete_attr.php
 *  Deletes an attribute from an entry with NO confirmation.
 *
 *  On success, redirect to edit.php
 *  On failure, echo an error.
 */

require 'common.php';

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

$server_id = $_POST['server_id'];
$dn = rawurldecode( $_POST['dn'] );
$encoded_dn = rawurlencode( $dn );
$attr = $_POST['attr'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
if( ! $attr ) pla_error( "No attribute name specified in POST variables" );
if( ! $dn ) pla_error( "No DN name specified in POST variables" );

$update_array = array();
$update_array[$attr] = array();
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
