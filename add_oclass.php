<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/add_oclass.php,v 1.14 2005/03/20 04:43:36 wurley Exp $

/**
 * Adds an objectClass to the specified dn.
 *
 * Note, this does not do any schema violation checking. That is
 * performed in add_oclass_form.php.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - new_oclass
 *  - new_attrs (array, if any)
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

$dn = rawurldecode( $_POST['dn'] );
$new_oclass = $_POST['new_oclass'];
$new_attrs = $_POST['new_attrs'];

$encoded_dn = rawurlencode( $dn );

if( is_attr_read_only( $ldapserver, 'objectClass' ) )
	pla_error( "ObjectClasses are flagged as read only in the phpLDAPadmin configuration." );

$new_entry = array();
$new_entry['objectClass'] = $new_oclass;

$new_attrs_entry = array();
$new_oclass_entry = array( 'objectClass' => $new_oclass );

if( is_array( $new_attrs ) && count( $new_attrs ) > 0 )
	foreach( $new_attrs as $attr => $val ) {

		// Check to see if this is a unique Attribute
		if( $badattr = checkUniqueAttr( $ldapserver, $dn, $attr, array($val) ) ) {
			$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',$server_id,$attr,$badattr);
			pla_error(sprintf( $lang['unique_attr_failed'],$attr,$badattr,$dn,$search_href ) );
		}

		$new_entry[ $attr ] = $val;
	}

//echo "<pre>";
//print_r( $new_entry );
//exit;

$add_res = @ldap_mod_add( $ldapserver->connect(), $dn, $new_entry );

if( ! $add_res ) {
	pla_error( $lang['could_not_perform_ldap_mod_add'],ldap_error($ldapserver->connect()),ldap_errno($ldapserver->connect()) );

} else {
	header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&modified_attrs[]=objectclass" );
}
?>
