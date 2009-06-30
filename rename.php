<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/rename.php,v 1.19 2004/04/07 12:45:02 uugdave Exp $
 

/*
 * rename.php
 * Renames a DN to a different name. 
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - new_rdn
 */

require realpath( 'common.php' );

$dn = ( $_POST['dn'] );
$server_id = $_POST['server_id'];
$new_rdn = ( $_POST['new_rdn'] );


if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

$children = get_container_contents( $server_id, $dn, 1 );
if( count( $children ) > 0 )
	pla_error( $lang['non_leaf_nodes_cannot_be_renamed'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
 
$container = get_container( $dn );
$new_dn = $new_rdn . ',' . $container;

if( $new_dn  == $dn )
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

// attempt to add the new DN attr value (if we can't, die a silent death)
$add_dn_attr_success = @ldap_mod_add( $ds, $dn, $add_new_dn_attr );
if( ! @ldap_rename( $ds, $dn, $new_rdn, $container, false ) )
{
	pla_error( $lang['could_not_rename'], ldap_error( $ds ), ldap_errno( $ds ), false );

	// attempt to undo our changes to the DN attr
	if( $add_dn_attr_success )
		@ldap_mod_del( $ds, $dn, $add_new_dn_attr );
}
else
{
	// attempt to remove the old DN attr value (if we can't, die a silent death)
	@ldap_mod_del( $ds, $new_dn, $remove_old_dn_attr );

	if( array_key_exists( 'tree', $_SESSION ) )
	{
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];
		$old_dn = $dn;

		// gotta search the whole tree for the entry (must be a leaf node since RDN changes
		// cannot occur on parents)
		foreach( $tree[$server_id] as $parent_dn => $children ) {
			foreach( $children as $i => $child_dn ) {
				if( 0 == strcasecmp( $child_dn, $old_dn ) ) {
					$tree[$server_id][$parent_dn][$i] = $new_dn;
				}
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

		<?php 

	}
}
