<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/collapse.php,v 1.13 2005/07/22 05:55:19 wurley Exp $

/**
 * This script alters the session variable 'tree', collapsing it
 * at the dn specified in the query string.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * Note: this script is equal and opposite to expand.php
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$dn = $_GET['dn'];
$encoded_dn = rawurlencode($dn);

initialize_session_tree();

if (array_key_exists($dn,$_SESSION['tree'][$ldapserver->server_id]))
	unset($_SESSION['tree'][$ldapserver->server_id][$dn]);

/* This is for Opera. By putting "random junk" in the query string, it thinks
   that it does not have a cached version of the page, and will thus
   fetch the page rather than display the cached version */
$time = gettimeofday();
$random_junk = md5(strtotime('now') . $time['usec']);

/* If cookies were disabled, build the url parameter for the session id.
   It will be append to the url to be redirect */
$id_session_param = "";
if (SID != "")
	$id_session_param = "&".session_name()."=".session_id();

header(sprintf('Location:tree.php?foo=%s#%s_%s%s',$random_junk,$ldapserver->server_id,$encoded_dn,$id_session_param));
?>
