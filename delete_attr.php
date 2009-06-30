<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/delete_attr.php,v 1.6 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/* 
 *  delete_attr.php
 *  Deletes an attribute from an entry with NO confirmation.
 *
 *  On success, redirect to edit.php
 *  On failure, echo an error.
 */

require 'common.php';

$server_id = $_POST['server_id'];

$dn = $_POST['dn'] ;
$encoded_dn = rawurlencode( $dn );
$attr = $_POST['attr'];

if( is_attr_read_only( $attr ) )
	pla_error( sprintf( $lang['attr_is_read_only'], htmlspecialchars( $attr ) ) );
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
if( ! $attr ) pla_error( $lang['no_attr_specified'] );
if( ! $dn ) pla_error( $lang['no_dn_specified'] );

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
	pla_error( $lang['could_not_perform_ldap_modify'], ldap_error( $ds ), ldap_errno( $ds ) );
}

?>
