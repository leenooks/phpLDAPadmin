<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/rename.php,v 1.23 2005/03/05 06:27:06 wurley Exp $

/**
 * Renames a DN to a different name.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - new_rdn
 *
 * @package phpLDAPadmin
 */
/**
 */

require realpath( 'common.php' );

$server_id = (isset($_POST['server_id']) ? $_POST['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$dn = ( $_POST['dn'] );
$new_rdn = ( $_POST['new_rdn'] );

$children = get_container_contents( $ldapserver, $dn, 1 );
if( count( $children ) > 0 )
	pla_error( $lang['non_leaf_nodes_cannot_be_renamed'] );

$container = get_container( $dn );
$new_dn = $new_rdn . ',' . $container;

if( $new_dn == $dn )
	pla_error( $lang['no_rdn_change'] );

$dn_attr = explode( '=', $dn );
$dn_attr = $dn_attr[0];

$old_dn_value = pla_explode_dn( $dn );
$old_dn_value = explode( '=', $old_dn_value[0], 2 );
$old_dn_value = $old_dn_value[1];

$new_dn_value = explode( '=', $new_rdn, 2 );

if( count( $new_dn_value ) != 2 || ! isset( $new_dn_value[1] ) )
     pla_error( $lang['invalid_rdn'] );

$new_dn_value = $new_dn_value[1];

// Add the new DN attr value to the DN attr (ie, add newName to cn)
$add_new_dn_attr = array( $dn_attr => $new_dn_value );

// Remove the old DN attr value
$remove_old_dn_attr = array( $dn_attr => $old_dn_value );

$success = run_hook ( 'pre_rename_entry', array ( 'server_id' => $server_id,
	'old_dn' => $dn, 'new_dn' => $new_dn_value ) );

if ( $success ) {
	$success = false;

	// attempt to add the new DN attr value (if we can't, die a silent death)
	$add_dn_attr_success = @ldap_mod_add( $ldapserver->connect(), $dn, $add_new_dn_attr );

	if( ! @ldap_rename( $ldapserver->connect(), $dn, $new_rdn, $container, false ) ) {
		pla_error( $lang['could_not_rename'], ldap_error( $ldapserver->connect() ),
			ldap_errno( $ldapserver->connect() ), false );

		// attempt to undo our changes to the DN attr
		if( $add_dn_attr_success )
			@ldap_mod_del( $ldapserver->connect(), $dn, $add_new_dn_attr );

	} else
		$success = true;

} else {
	pla_error( $lang['could_not_rename'] );
}

if ( $success ) {
	// attempt to remove the old DN attr value (if we can't, die a silent death)
	@ldap_mod_del( $ldapserver->connect(), $new_dn, $remove_old_dn_attr );

	run_hook ( 'post_rename_entry', array ( 'server_id' => $server_id, 'old_dn' => $dn,
		'new_dn' => $new_dn_value ) );

	if( array_key_exists( 'tree', $_SESSION ) ) {
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];
		$old_dn = $dn;

		// gotta search the whole tree for the entry (must be a leaf node since RDN changes
		// cannot occur on parents)
		foreach( $tree[$server_id] as $parent_dn => $children ) {
			foreach( $children as $i => $child_dn ) {
				if( 0 == strcasecmp( $child_dn, $old_dn ) )
					$tree[$server_id][$parent_dn][$i] = $new_dn;
			}
		}

		// Update the icon tree to reflect the change (remove the old DN and add the new one)
		$tree_icons[ $server_id ][ $new_dn ] = $tree_icons[ $server_id ][ $old_dn ];
		unset( $tree_icons[ $server_id ][ $old_dn ] );

		$_SESSION['tree'] = $tree;
		$_SESSION['tree_icons'] = $tree_icons;
		session_write_close();

		$edit_url="edit.php?server_id=$server_id&dn=" . rawurlencode( "$new_rdn,$container" );
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
