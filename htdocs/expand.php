<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/expand.php,v 1.26 2007/12/15 07:50:30 wurley Exp $

/**
 * This script alters the session variable 'tree', expanding it
 * at the dn specified in the query string.
 *
 * Note: this script is equal and opposite to collapse.php
 * @package phpLDAPadmin
 * @see collapse.php
 */
/**
 */

require './common.php';

$dn = get_request('dn','GET',true);
$tree = get_cached_item($ldapserver->server_id,'tree');
$entry = $tree->getEntry($dn);
$entry->open();
set_cached_item($ldapserver->server_id,'tree','null',$tree);

header(sprintf('Location:index.php?server_id=%s&junk=%s#%s%s',
	$ldapserver->server_id,random_junk(),htmlid($ldapserver->server_id,$dn),pla_session_param()));
die();
?>
