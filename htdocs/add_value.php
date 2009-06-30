<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_value.php,v 1.19.2.5 2005/12/09 23:32:37 wurley Exp $

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
	pla_error( _('You cannot perform updates while server is in read-only mode') );
if( ! $ldapserver->haveAuthInfo())
	pla_error( _('Not enough information to login to server. Please check your configuration.') );

$attr = $_POST['attr'];
$new_value = $_POST['new_value'];
$dn = rawurldecode( $_POST['dn'] );
$is_binary_val = isset( $_POST['binary'] ) ? true : false;

$encoded_dn = rawurlencode( $dn );
$encoded_attr = rawurlencode( $attr );

if ($ldapserver->isAttrReadOnly($attr))
	pla_error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),htmlspecialchars( $attr )));

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
if ($badattr = $ldapserver->checkUniqueAttr($dn,$attr,$new_entry)) {
	$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',$ldapserver->server_id,$attr,$badattr);
	pla_error(sprintf( _('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.'),$attr,$badattr,$dn,$search_href ) );
}

// Call the custom callback for each attribute modification
// and verify that it should be modified.
if( run_hook ( 'pre_attr_add', array ( 'server_id' => $ldapserver->server_id, 'dn' => $dn, 'attr_name' => $attr,
	'new_value' => $new_entry ) ) ) {

	$add_result = $ldapserver->attrModify($dn,$new_entry);

	if (! $add_result)
		pla_error(_('Could not perform ldap_mod_add operation.'),
			  $ldapserver->error(),$ldapserver->errno());
}

header(sprintf('Location: template_engine.php?server_id=%s&dn=%s&modified_attrs[]=%s',
	$ldapserver->server_id,$encoded_dn,$encoded_attr));
?>
