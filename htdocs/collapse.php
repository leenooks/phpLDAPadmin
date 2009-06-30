<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/collapse.php,v 1.16 2007/12/15 07:50:30 wurley Exp $

/**
 * This script alters the session variable 'tree', collapsing it
 * at the dn specified in the query string.
 *
 * Note: this script is equal and opposite to expand.php
 * @package phpLDAPadmin
 * @see expand.php
 */
/**
 */

require './common.php';

$dn = get_request('dn','GET',true);
$tree = get_cached_item($ldapserver->server_id,'tree');
$entry = $tree->getEntry($dn);
$entry->close();
set_cached_item($ldapserver->server_id,'tree','null',$tree);

header(sprintf('Location:index.php?server_id=%s&junk=%s#%s%s',
	$ldapserver->server_id,random_junk(),htmlid($ldapserver->server_id,$dn),pla_session_param()));
die();
?>
