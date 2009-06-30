<?php 

/*
 * add_value.php
 * Adds a value to an attribute for a given dn.
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value 
 *  - server_id
 *  - new_value (form element)
 *  - binary 
 *
 * On success, redirect to the edit_dn page.
 * On failure, echo an error.
 */

require 'common.php';

$dn = rawurldecode( $_POST['dn'] );
$encoded_dn = rawurlencode( $dn );
$attr = $_POST['attr'];
$encoded_attr = rawurlencode( $attr );
$server_id = $_POST['server_id'];
$new_value = $_POST['new_value'];
$new_value = utf8_encode($new_value);
$is_binary_val = isset( $_POST['binary'] ) ? true : false;

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );

// special case for binary attributes: 
// we must go read the data from the file.
if( $is_binary_val )
{
	$file = $_FILES['new_value']['tmp_name'];
	$f = fopen( $file, 'r' );
	$binary_value = fread( $f, filesize( $file ) );
	fclose( $f );
	$new_value = $binary_value;
}

$new_entry = array( $attr => $new_value );

$add_result = @ldap_mod_add( $ds, $dn, $new_entry );

if( ! $add_result )
	pla_error( $lang['could_not_perform_ldap_mod_add'], ldap_error( $ds ), ldap_errno( $ds ) );

header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&updated_attr=$encoded_attr" );

?>
