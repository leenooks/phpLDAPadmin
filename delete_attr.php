<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/delete_attr.php,v 1.11 2005/03/05 06:27:06 wurley Exp $
 
/**
 *  Deletes an attribute from an entry with NO confirmation.
 *
 *  On success, redirect to edit.php
 *  On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = (isset($_POST['server_id']) ? $_POST['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$dn = $_POST['dn'] ;
$attr = $_POST['attr'];

$encoded_dn = rawurlencode( $dn );

if( is_attr_read_only( $ldapserver, $attr ) )
	pla_error( sprintf( $lang['attr_is_read_only'], htmlspecialchars( $attr ) ) );

if( ! $attr ) pla_error( $lang['no_attr_specified'] );
if( ! $dn ) pla_error( $lang['no_dn_specified'] );

$update_array = array();
$update_array[$attr] = array();

$res = @ldap_modify( $ldapserver->connect(), $dn, $update_array );
if( $res ) {
	$redirect_url = "edit.php?server_id=$server_id&dn=$encoded_dn";

	foreach( $update_array as $attr => $junk )
		$redirect_url .= "&modified_attrs[]=$attr";

	header( "Location: $redirect_url" );

} else {
	pla_error( $lang['could_not_perform_ldap_modify'], ldap_error( $ldapserver->connect() ), ldap_errno( $ldapserver->connect() ) );
}
?>
