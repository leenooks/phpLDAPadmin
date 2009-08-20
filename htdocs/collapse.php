<?php
/**
 * This script alters the session variable 'tree', collapsing it
 * at the dn specified in the query string.
 *
 * Note: this script is equal and opposite to expand.php
 *
 * @package phpLDAPadmin
 * @subpackage Tree
 * @see expand.php
 */

/**
 */

require './common.php';

$dn = get_request('dn','GET',true);
$tree = get_cached_item($app['server']->getIndex(),'tree');
$entry = $tree->getEntry($dn);
$entry->close();
set_cached_item($app['server']->getIndex(),'tree','null',$tree);

header(sprintf('Location:index.php?server_id=%s&junk=%s#%s%s',
	$app['server']->getIndex(),random_junk(),htmlid($app['server']->getIndex(),$dn),app_session_param()));
die();
?>
