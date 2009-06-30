<?php
/*
 * add_oclass.php
 * Adds an objectClass to the specified dn.
 * Variables that come in as POST vars:
 *
 * Note, this does not do any schema violation checking. That is
 * performed in add_oclass_form.php.
 *
 * Vars that come in as POST:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - new_oclass
 *  - new_attrs (array, if any)
 */

require 'config.php';
require 'functions.php';

$dn = stripslashes( rawurldecode( $_POST['dn'] ) );
$encoded_dn = rawurlencode( $dn );
$new_oclass = stripslashes( $_POST['new_oclass'] );
$server_id = $_POST['server_id'];
$new_attrs = $_POST['new_attrs'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

$new_entry = array();
$new_entry['objectClass'] = $new_oclass;

$new_attrs_entry = array();
$new_oclass_entry = array( 'objectClass' => $new_oclass );

if( is_array( $new_attrs ) && count( $new_attrs ) > 0 )
	foreach( $new_attrs as $attr => $val )
		$new_entry[ $attr ] = $val;

//echo "<pre>"; 
//print_r( $new_entry );
//exit;

$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server." );
$add_res = @ldap_mod_add( $ds, $dn, $new_entry );

if( ! $add_res )
{
	pla_error( "Could not perform ldap_mod_add operation", ldap_error( $ds ), ldap_errno( $ds ) );
}
else
{
	header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn" );
}

?>
