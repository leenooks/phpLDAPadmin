<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/draw_tree_node.php,v 1.2.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * @package phpLDAPadmin
 */

$entry = array();
$entry['dn'] = get_request('dn','REQUEST');
$entry['server_id'] = get_request('server_id','REQUEST');
$entry['code'] = get_request('code','REQUEST');
$entry['action'] = get_request('action','REQUEST');

$tree = Tree::getInstance($entry['server_id']);
if (! $tree)
	die();

$dnentry = $tree->getEntry($entry['dn']);
if (! $dnentry) {
	$tree->addEntry($entry['dn']);
	$dnentry = $this->getEntry($entry['dn']);
}

if (! $dnentry)
	die();

if ($entry['action'] == 0) {
	$dnentry->close();

} elseif ($entry['action'] == 2) {
	$dnentry->open();

} else {
	$dnentry->open();
	if ($entry['dn']) {
		echo $tree->draw_children($dnentry,$entry['code']);
	} else {
		$tree->draw(true);
	}
}
die();
?>
