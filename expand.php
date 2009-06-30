<?php 

/*
 * expand.php
 * This script alters the session variable 'tree', expanding it
 * at the dn specified in the query string. 
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *
 * Note: this script is equal and opposite to collapse.php
 */

require 'config.php';
require_once 'functions.php';

// no expire header stuff
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$dn = stripslashes( $_GET['dn'] );
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

session_start();

session_is_registered( 'tree' ) or pla_error( "Your session tree is not registered. That's weird. Shouldn't ever happen".
							". Just go back and it should be fixed automagically." );
$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );
$contents = get_container_contents( $server_id, $dn );

sort( $contents );
$tree[$server_id][$dn] = $contents;

foreach( $contents as $dn )
	$tree_icons[$server_id][$dn] = get_icon( $server_id, $dn );

$_SESSION['tree'] = $tree;
$_SESSION['tree_icons'] = $tree_icons;
session_write_close();

// This is for Opera. By putting "random junk" in the query string, it thinks
// that it does not have a cached version of the page, and will thus
// fetch the page rather than display the cached version
$time = gettimeofday();
$random_junk = md5( strtotime( 'now' ) . $time['usec'] );

header( "Location: tree.php?foo=$random_junk#{$server_id}_{$encoded_dn}" );

?>
