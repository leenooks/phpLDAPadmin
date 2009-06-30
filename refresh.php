<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/refresh.php,v 1.13 2005/03/05 09:41:01 wurley Exp $
 
/**
 * This script alters the session variable 'tree', by re-querying
 * the LDAP server to grab the contents of every expanded container.
 *
 * Variables that come in as GET vars:
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( ! $ldapserver->haveAuthInfo() || ! array_key_exists( 'tree', $_SESSION ))
	header( "Location: tree.php" );

$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

// Get the icon for the base object(s) for this server
foreach ($ldapserver->getBaseDN() as $base_dn)
	$tree_icons[$server_id][ $base_dn ] = get_icon( $ldapserver, $base_dn );

// get all the icons and container contents for all expanded entries
if( isset($tree[$server_id]) && is_array( $tree[$server_id] ) ) {
	foreach( $tree[$server_id] as $dn => $children ) {
		$tree[$server_id][$dn] = get_container_contents( $ldapserver, $dn, 0, '(objectClass=*)', get_tree_deref_setting() );

		if( is_array( $tree[$server_id][$dn] ) ) {
			foreach( $tree[$server_id][$dn] as $child_dn )
				$tree_icons[$server_id][$child_dn] = get_icon( $ldapserver, $child_dn );

			sort( $tree[ $server_id ][ $dn ] );	
		}
	}

} else {
	header( "Location: tree.php#$server_id" );
}

$_SESSION['tree'] = $tree;
$_SESSION['tree_icons'] = $tree_icons;
session_write_close();

header( "Location: tree.php#$server_id" );
?>
