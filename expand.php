<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/expand.php,v 1.15 2004/03/19 20:13:08 i18phpldapadmin Exp $


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

require 'common.php';

// no expire header stuff
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// This allows us to display large sub-trees without running out of time.
@set_time_limit( 0 );

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

initialize_session_tree();

$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
$contents = get_container_contents( $server_id, $dn );

usort( $contents, 'pla_compare_dns' );
$tree[$server_id][$dn] = $contents;

//echo "<pre>";
//var_dump( $contents );
//exit;

foreach( $contents as $dn )
	$tree_icons[$server_id][$dn] = get_icon( $server_id, $dn );

$_SESSION['tree'] = $tree;
$_SESSION['tree_icons'] = $tree_icons;

// This is for Opera. By putting "random junk" in the query string, it thinks
// that it does not have a cached version of the page, and will thus
// fetch the page rather than display the cached version
$time = gettimeofday();
$random_junk = md5( strtotime( 'now' ) . $time['usec'] );

// If cookies were disabled, build the url parameter for the session id.
// It will be append to the url to be redirect
$id_session_param="";
if( SID != "" ){
	$id_session_param = "&".session_name()."=".session_id();
}

session_write_close();

header( "Location:tree.php?foo=$random_junk#{$server_id}_{$encoded_dn}$id_session_param" );
?>
