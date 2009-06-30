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
 *  - binary
 */

require 'common.php';

$dn = rawurldecode( $_POST['dn'] );
$server_id = $_POST['server_id'];
$attr = $_POST['attr'];
$val  = $_POST['val'];
$val = utf8_encode( $val );
$encoded_dn = rawurlencode( $dn );
$encoded_attr = rawurlencode( $attr );
$is_binary_val = isset( $_POST['binary'] ) ? true : false;

if( ! $is_binary_val && $val == "" ) {
	pla_error( "You left the attribute value blank. Please go back and try again." );
}

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

// special case for binary attributes (like jpegPhoto and userCertificate): 
// we must go read the data from the file and override $val with the binary data
if( $is_binary_val ) {
	$file = $_FILES['val']['tmp_name'];
	$f = fopen( $file, 'r' );
	$binary_data = fread( $f, filesize( $file ) );
	fclose( $f );
	$val = $binary_data;
}

// Automagically hash new userPassword attributes according to the 
// chosen in config.php. 
if( 0 == strcasecmp( $attr, 'userpassword' ) )
{
	if( $servers[$server_id]['default_hash'] != '' ) {
		$enc_type = $servers[$server_id]['default_hash'];
		$new_val = password_hash( $new_val, $enc_type );
		$val = $new_val;
	}
}


$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );
$new_entry = array( $attr => $val );
$result = @ldap_mod_add( $ds, $dn, $new_entry );

if( $result )
	header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&updated_attr=$encoded_attr" );
else
	pla_error( "Failed to add the attribute.", ldap_error( $ds ) , ldap_errno( $ds ) );
