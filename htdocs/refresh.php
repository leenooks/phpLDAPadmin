<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/refresh.php,v 1.15 2005/07/22 05:51:57 wurley Exp $
 
/**
 * This script alters the session variable 'tree', by re-querying
 * the LDAP server to grab the contents of every expanded container.
 *
 * Variables that come in via common.php
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! isset($ldapserver) || ! array_key_exists('tree',$_SESSION))
	header("Location: tree.php");

$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

# Get the icon for the base object(s) for this server
foreach ($ldapserver->getBaseDN() as $base_dn)
	$tree_icons[$ldapserver->server_id][$base_dn] = get_icon($ldapserver,$base_dn);

# get all the icons and container contents for all expanded entries
if (isset($tree[$ldapserver->server_id]) && is_array($tree[$ldapserver->server_id])) {
	foreach ($tree[$ldapserver->server_id] as $dn => $children) {
		$tree[$ldapserver->server_id][$dn] = get_container_contents($ldapserver,$dn,0,'(objectClass=*)',
			$config->GetValue('deref','tree'));

		if (is_array($tree[$ldapserver->server_id][$dn])) {
			foreach ($tree[$ldapserver->server_id][$dn] as $child_dn)
				$tree_icons[$ldapserver->server_id][$child_dn] = get_icon($ldapserver,$child_dn);

			sort($tree[$ldapserver->server_id][$dn]);	
		}
	}

} else {
	header(sprintf('Location: tree.php#%s',$ldapserver->server_id));
}

$_SESSION['tree'] = $tree;
$_SESSION['tree_icons'] = $tree_icons;
session_write_close();

header(sprintf('Location: tree.php#%s',$ldapserver->server_id));
?>
