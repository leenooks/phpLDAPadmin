<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/collapse.php,v 1.12 2005/02/25 13:44:05 wurley Exp $

/**
 * This script alters the session variable 'tree', collapsing it
 * at the dn specified in the query string.
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *
 * Note: this script is equal and opposite to expand.php
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
//$ldapserver = new LDAPServer($server_id);

//if( ! $ldapserver->haveAuthInfo())
//	pla_error( $lang['not_enough_login_info'] );

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );

initialize_session_tree();

if( array_key_exists( $dn, $_SESSION['tree'][$server_id] ) )
	unset( $_SESSION['tree'][$server_id][$dn] );

// This is for Opera. By putting "random junk" in the query string, it thinks
// that it does not have a cached version of the page, and will thus
// fetch the page rather than display the cached version
$time = gettimeofday();
$random_junk = md5( strtotime( 'now' ) . $time['usec'] );

// If cookies were disabled, build the url parameter for the session id.
// It will be append to the url to be redirect
$id_session_param = "";
if (SID != "")
	$id_session_param = "&".session_name()."=".session_id();

header( "Location:tree.php?foo=$random_junk#{$server_id}_{$encoded_dn}$id_session_param" );
?>
