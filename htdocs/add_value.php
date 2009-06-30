<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_value.php,v 1.18.2.1 2005/10/09 09:07:21 wurley Exp $

/**
 * Adds a value to an attribute for a given dn.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value
 *  - new_value (form element)
 *  - binary
 *
 * On success, redirect to the edit_dn page. On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$attr = $_POST['attr'];
$new_value = $_POST['new_value'];
$dn = rawurldecode( $_POST['dn'] );
$is_binary_val = isset( $_POST['binary'] ) ? true : false;

$encoded_dn = rawurlencode( $dn );
$encoded_attr = rawurlencode( $attr );

if( is_attr_read_only( $ldapserver, $attr ) )
	pla_error(sprintf($lang['attr_is_read_only'],htmlspecialchars( $attr )));

// special case for binary attributes:
// we must go read the data from the file.
if( $is_binary_val ) {
	$file = $_FILES['new_value']['tmp_name'];

	$f = fopen( $file, 'r' );
	$binary_value = fread( $f, filesize( $file ) );
	fclose( $f );

	$new_value = $binary_value;
}

$new_entry = array( $attr => $new_value );

// Check to see if this is a unique Attribute
if( $badattr = checkUniqueAttr( $ldapserver, $dn, $attr, $new_entry ) ) {
	$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',$ldapserver->server_id,$attr,$badattr);
	pla_error(sprintf( $lang['unique_attr_failed'],$attr,$badattr,$dn,$search_href ) );
}

// Call the custom callback for each attribute modification
// and verify that it should be modified.
if( run_hook ( 'pre_attr_add', array ( 'server_id' => $ldapserver->server_id, 'dn' => $dn, 'attr_name' => $attr,
	'new_value' => $new_entry ) ) ) {

	$add_result = @ldap_mod_add( $ldapserver->connect(), $dn, $new_entry );

	if (! $add_result)
		pla_error($lang['could_not_perform_ldap_mod_add'],
			  $ldapserver->error(),$ldapserver->errno());
}

header(sprintf('Location: edit.php?server_id=%s&dn=%s&modified_attrs[]=%s',
	$ldapserver->server_id,$encoded_dn,$encoded_attr));
?>
