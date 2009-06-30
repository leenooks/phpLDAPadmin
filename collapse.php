<?php 

/*
 * collapse.php
 * This script alters the session variable 'tree', collapsing it
 * at the dn specified in the query string. 
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *
 * Note: this script is equal and opposite to expand.php
 */

require 'common.php';

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );

session_start();

// dave commented this out since it was being triggered for weird reasons
//session_is_registered( 'tree' ) or pla_error( "Your session tree is not registered. That's weird. Shouldn't ever happen".
//							". Just go back and it should be fixed automagically." );

$tree = $_SESSION['tree'];

// and remove this instance of the dn as well
unset( $tree[$server_id][$dn] );

$_SESSION['tree'] = $tree;
session_write_close();

// This is for Opera. By putting "random junk" in the query string, it thinks
// that it does not have a cached version of the page, and will thus
// fetch the page rather than display the cached version
$time = gettimeofday();
$random_junk = md5( strtotime( 'now' ) . $time['usec'] );

// If cookies were disabled, build the url parameter for the session id.
// It will be append to the url to be redirect
$id_session_param="";
if(SID != ""){
  $id_session_param = "&".session_name()."=".session_id();
}

header( "Location:tree.php?foo=$random_junk%23{$server_id}_{$encoded_dn}$id_session_param" );
?>
