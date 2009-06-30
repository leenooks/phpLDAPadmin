<?php 

/*
 * new_attr.php
 * Adds an attribute/value pair to an object
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - attr
 *  - val
 */

require 'config.php';
require_once 'functions.php';

$dn = stripslashes( rawurldecode( $_POST['dn'] ) );
$server_id = $_POST['server_id'];
$attr = stripslashes( $_POST['attr'] );
$val  = stripslashes( $_POST['val']  );
$val = utf8_encode( $val );
$encoded_dn = rawurlencode( $dn );
$encoded_attr = rawurlencode( $attr );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

// special case for jpegPhoto attributes: 
// we must go read the data from the file.
if( 0 == strcasecmp( $attr, 'jpegPhoto' ) )
{
	$file = $_FILES['jpeg_photo_file']['tmp_name'];
	$f = fopen( $file, 'r' );
	$jpeg_data = fread( $f, filesize( $file ) );
	fclose( $f );
	$val = $jpeg_data;
}

$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );
$new_entry = array( $attr => $val );
$result = @ldap_mod_add( $ds, $dn, $new_entry );

if( $result )
	header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&updated_attr=$encoded_attr" );
else
	pla_error( "Failed to add the attribute.", ldap_error( $ds ) , ldap_errno( $ds ) );
