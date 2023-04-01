<?php
/**
 * Draw a portion of the LDAP tree.
 *
 * @package phpLDAPadmin
 * @subpackage Tree
 */

/**
 */

$request = array();
$request['dn'] = get_request('dn','REQUEST');
$request['server_id'] = get_request('server_id','REQUEST');
$request['code'] = get_request('code','REQUEST');
$request['action'] = get_request('action','REQUEST');
$request['noheader'] = get_request('noheader','REQUEST',false,0);

$tree = Tree::getInstance($request['server_id']);
if (! $tree)
	die();

$treesave = false;

if ($request['dn']) {
	$dnentry = $tree->getEntry($request['dn']);

	if (! $dnentry) {
		$tree->addEntry($request['dn']);
		$dnentry = $tree->getEntry($request['dn']);
		$treesave = true;
	}

	switch ($request['action']) {
		case 0:
			$dnentry->close();

			break;

		case 2:
		default:
			if ($dnentry->isSizeLimited()) {
				$tree->readChildren($request['dn'],true);

				$treesave = true;
			}

			$dnentry->open();
	}
}

if ($treesave)
	set_cached_item($app['server']->getIndex(),'tree','null',$tree);

if ($request['dn'])
	echo $tree->draw_children($dnentry,$request['code']);
else
	$tree->draw($request['noheader']);

die();
?>
