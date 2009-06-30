<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/rename.php,v 1.27 2005/09/25 16:11:44 wurley Exp $

/**
 * Renames a DN to a different name.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - new_rdn
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error($lang['no_updates_in_read_only_mode']);
if (! $ldapserver->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

$dn = ($_POST['dn']);
$new_rdn = ($_POST['new_rdn']);

if (! $ldapserver->isBranchRenameEnabled()) {
	$children = get_container_contents($ldapserver,$dn,1);
	if (count($children) > 0)
		pla_error($lang['non_leaf_nodes_cannot_be_renamed']);
}

$container = get_container($dn);
$new_dn = sprintf('%s,%s',$new_rdn,$container);

if ($new_dn == $dn)
	pla_error($lang['no_rdn_change']);

$old_dn_attr = explode('=',$dn);
$old_dn_attr = $old_dn_attr[0];

$old_dn_value = pla_explode_dn($dn);
$old_dn_value = explode('=',$old_dn_value[0],2);
$old_dn_value = $old_dn_value[1];

$new_dn_value = explode('=',$new_rdn,2);

if (count($new_dn_value) != 2 || ! isset($new_dn_value[1]))
	pla_error($lang['invalid_rdn']);

$new_dn_attr = $new_dn_value[0];
$new_dn_value = $new_dn_value[1];

$success = run_hook ('pre_rename_entry', array ('server_id' => $ldapserver->server_id,
	'old_dn' => $dn, 'new_dn' => $new_dn_value ) );

if ($success) {
	$success = false;

	$deleteoldrdn = $old_dn_attr == $new_dn_attr;

	if (! @ldap_rename($ldapserver->connect(), $dn, $new_rdn, $container, $deleteoldrdn ) ) {
		pla_error($lang['could_not_rename'], ldap_error($ldapserver->connect() ),
			ldap_errno($ldapserver->connect() ), false );

	} else
		$success = true;

} else {
	pla_error($lang['could_not_rename'] );
}

if ($success ) {
	run_hook ('post_rename_entry', array ('server_id' => $ldapserver->server_id, 'old_dn' => $dn,
		'new_dn' => $new_dn_value ) );

	if (array_key_exists('tree', $_SESSION ) ) {
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];
		$old_dn = $dn;

		// gotta search the whole tree for the entry (must be a leaf node since RDN changes
		// cannot occur on parents)
		foreach ($tree[$ldapserver->server_id] as $parent_dn => $children ) {
			foreach ($children as $i => $child_dn ) {
				if (0 == strcasecmp($child_dn, $old_dn ) )
					$tree[$ldapserver->server_id][$parent_dn][$i] = $new_dn;
			}
		}

		// Update the icon tree to reflect the change (remove the old DN and add the new one)
		$tree_icons[ $ldapserver->server_id ][ $new_dn ] = $tree_icons[ $ldapserver->server_id ][ $old_dn ];
		unset($tree_icons[ $ldapserver->server_id ][ $old_dn ] );

		$_SESSION['tree'] = $tree;
		$_SESSION['tree_icons'] = $tree_icons;
		session_write_close();

		$edit_url = sprintf('edit.php?server_id=%s&dn=%s',$ldapserver->server_id,rawurlencode("$new_rdn,$container"));
?>

		<html>
		<head>
			<!-- refresh the tree view (with the new DN renamed)
			     and redirect to the edit_dn page -->
			<script language="javascript">
			parent.left_frame.location.reload();
			location.href='<?php echo $edit_url; ?>';
			</script>

			<!-- If the JavaScript didn't work, here's a meta tag to do the job -->
			<meta http-equiv="refresh" content="0; url=<?php echo $edit_url; ?>" />
		</head>
		<body>
		<?php echo $lang['redirecting']; ?> <a href="<?php echo $edit_url; ?>"><?php echo $lang['here']; ?></a>
		</body>
		</html>

	<?php }
}
?>
