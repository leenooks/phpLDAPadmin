<?php
/**
 * This script deletes the session variable 'tree', which will result in re-querying
 * the LDAP server to grab the contents of all LDAP entries starting from the base.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

if (get_request('purge','REQUEST')) {
	$tree = get_cached_item($app['server']->getIndex(),'tree');
	del_cached_item($app['server']->getIndex(),'tree');

	if ($tree)
		$openDNs = $tree->listOpenItems();
	else
		$openDNs = array();

	$tree = Tree::getInstance($app['server']->getIndex());

	foreach ($openDNs as $value) {
		$entry = $tree->getEntry($value);
		if (! $entry) {
			$tree->addEntry($value);
			$entry = $tree->getEntry($value);
		}

		$tree->readChildren($value,true);
		$entry->open();
	}

	set_cached_item($app['server']->getIndex(),'tree','null',$tree);
}

if (get_request('meth','REQUEST') == 'ajax') 
	header(sprintf('Location: cmd.php?cmd=draw_tree_node&noheader=%s&server_id=%s&meth=ajax&frame=TREE',get_request('noheader','REQUEST',false,0),$app['server']->getIndex()));
else
	header(sprintf('Location: cmd.php?server_id=%s',$app['server']->getIndex()));

die();
?>
