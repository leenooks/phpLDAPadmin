<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/expand.php,v 1.22 2005/07/22 05:47:44 wurley Exp $

/**
 * This script alters the session variable 'tree', expanding it
 * at the dn specified in the query string.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * Note: this script is equal and opposite to collapse.php
 * @package phpLDAPadmin
 * @see collapse.php
 */
/**
 */

require './common.php';

if (! $ldapserver->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

# no expire header stuff
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

# This allows us to display large sub-trees without running out of time.
@set_time_limit( 0 );

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );

initialize_session_tree();

$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

$contents = get_container_contents( $ldapserver, $dn, 0, '(objectClass=*)', $config->GetValue('deref','tree'));

usort( $contents, 'pla_compare_dns' );
$tree[$ldapserver->server_id][$dn] = $contents;

foreach( $contents as $dn )
	$tree_icons[$ldapserver->server_id][$dn] = get_icon( $ldapserver, $dn );

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
if( SID != "" )
	$id_session_param = "&".session_name()."=".session_id();

session_write_close();

header(sprintf('Location:tree.php?foo=%s#%s_%s%s',$random_junk,$ldapserver->server_id,$encoded_dn,$id_session_param));
?>
