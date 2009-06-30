<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/collapse.php,v 1.13.4.2 2007/03/18 03:16:05 wurley Exp $

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
$tree = get_cached_item($ldapserver->server_id,'tree');
$tree['browser'][$dn]['open'] = false;
set_cached_item($ldapserver->server_id,'tree','null',$tree);

/* If cookies were disabled, build the url parameter for the session id.
   It will be append to the url to be redirect */
$id_session_param = '';
if (SID != '')
	$id_session_param = sprintf('&%s=%s',session_name(),session_id());

header(sprintf('Location:tree.php?foo=%s#%s_%s%s',random_junk(),$ldapserver->server_id,rawurlencode($dn),$id_session_param));
?>
