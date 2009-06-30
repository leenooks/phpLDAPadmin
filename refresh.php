<?php 

/*
 * refresh.php
 * This script alters the session variable 'tree', by re-querying
 * the LDAP server to grab the contents of every expanded container.
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require 'common.php';

$server_id = $_GET['server_id'];

if( ! check_server_id( $server_id ) || ! have_auth_info( $server_id ) )
	header( "Location: tree.php" );

session_start();
if( ! session_is_registered( 'tree' ) )
	header( "Location: tree.php" );

$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

// Get the icon for the base object for this server
$base_dn = $servers[ $server_id ][ 'base' ];
$tree_icons[$server_id][ $base_dn ] = get_icon( $server_id, $base_dn );

// get all the icons and container contents for all expanded entries
if( $tree[$server_id] && is_array( $tree[$server_id] ) ) 
{
	foreach( $tree[$server_id] as $dn => $children )
	{
		$tree[$server_id][$dn] = get_container_contents( $server_id, $dn );
		foreach( $tree[$server_id][$dn] as $child_dn )
			$tree_icons[$server_id][$child_dn] = get_icon( $server_id, $child_dn );
		sort( $tree[ $server_id ][ $dn ] );
	}
}
else
{
	header( "Location: tree.php#$server_id" );
}

$_SESSION['tree'] = $tree;
$_SESSION['tree_icons'] = $tree_icons;
session_write_close();

header( "Location: tree.php#$server_id" );


?>
