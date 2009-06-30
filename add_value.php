<?php 

/*
 * add_value.php
 * Adds a value to an attribute for a given dn.
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value 
 *  - server_id
 *  - new_value (form element)
 *
 * On success, redirect to the edit_dn page.
 * On failure, echo an error.
 */

require 'config.php';
require_once 'functions.php';

$dn = stripslashes( rawurldecode( $_POST['dn'] ) );
$encoded_dn = rawurlencode( $dn );
$attr = stripslashes( $_POST['attr'] );
$encoded_attr = rawurlencode( $attr );
$server_id = $_POST['server_id'];
$new_value = stripslashes( $_POST['new_value'] );
$new_value = utf8_encode($new_value);

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );

// special case for jpegPhoto attributes: 
// we must go read the data from the file.
if( 0 == strcasecmp( $attr, 'jpegPhoto' ) )
{
	$file = $_FILES['jpeg_photo_file']['tmp_name'];
	$f = fopen( $file, 'r' );
	$jpeg_data = fread( $f, filesize( $file ) );
	fclose( $f );
	$new_value = $jpeg_data;
}

$new_entry = array( $attr => $new_value );

$add_result = @ldap_mod_add( $ds, $dn, $new_entry );

if( ! $add_result )
	pla_error( "Could not perform ldap_mod_add operation.", ldap_error( $ds ), ldap_errno( $ds ) );

header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&updated_attr=$encoded_attr" );

?>
