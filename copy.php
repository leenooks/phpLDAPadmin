<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/copy.php,v 1.31 2005/03/14 11:46:24 wurley Exp $

/**
 * Copies a given object to create a new one.
 *
 * Vars that come in as POST vars
 *  - source_dn (rawurlencoded)
 *  - new_dn (form element)
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require realpath( 'common.php' );

$source_server_id = (isset($_POST['server_id']) ? $_POST['server_id'] : '');
$dest_server_id = (isset($_POST['dest_server_id']) ? $_POST['dest_server_id'] : '');

$ldapserver_source = new LDAPServer($source_server_id);
$ldapserver_dest = new LDAPServer($dest_server_id);

if( $ldapserver_dest->isReadOnly() )
	pla_error( $lang['copy_server_read_only'] );

if( ! $ldapserver_source->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );
if( ! $ldapserver_dest->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$source_dn =  $_POST['old_dn'];
$dest_dn = $_POST['new_dn'];
$do_recursive = ( isset( $_POST['recursive'] ) && $_POST['recursive'] == 'on' ) ? true : false;
$remove = ( isset( $_POST['remove'] ) && $_POST['remove'] == 'yes' ) ? true : false;
$encoded_dn = rawurlencode( $source_dn );

include './header.php';

/* Error checking */
if( 0 == strlen( trim( $dest_dn ) ) )
	pla_error( $lang['copy_dest_dn_blank'] );

if( pla_compare_dns( $source_dn,$dest_dn ) == 0 && $source_server_id == $dest_server_id )
	pla_error( $lang['copy_source_dest_dn_same'] );

if( dn_exists( $ldapserver_dest, $dest_dn ) )
	pla_error( sprintf( $lang['copy_dest_already_exists'], pretty_print_dn( $dest_dn ) ) );

if( ! dn_exists( $ldapserver_dest, get_container( $dest_dn ) ) )
	pla_error( sprintf( $lang['copy_dest_container_does_not_exist'], pretty_print_dn( get_container($dest_dn) ) ) );

if( $do_recursive ) {
	$filter = isset( $_POST['filter'] ) ? $_POST['filter'] : '(objectClass=*)';

	// build a tree similar to that of the tree browser to give to r_copy_dn
	$snapshot_tree = array();
	echo "<body>\n";
	echo "<h3 class=\"title\">". $lang['copy_copying'] . htmlspecialchars( $source_dn ) . "</h3>\n";
	echo "<h3 class=\"subtitle\">" . $lang['copy_recursive_copy_progress'] ."</h3>\n";
	echo "<br /><br />";
	echo "<small>\n";
	echo $lang['copy_building_snapshot'];

	flush();

	build_tree( $ldapserver_source, $source_dn, $snapshot_tree, $filter );
	echo " <span style=\"color:green\">" . $lang['success'] . "</span><br />\n";
	flush();

	// prevent script from bailing early on a long delete
	@set_time_limit( 0 );

	$copy_result = r_copy_dn( $ldapserver_source, $ldapserver_dest, $snapshot_tree, $source_dn, $dest_dn );
	echo "</small>\n";

} else {
	$copy_result = copy_dn( $ldapserver_source, $source_dn, $ldapserver_dest, $dest_dn );
}

if( $copy_result ) {
	$edit_url="edit.php?server_id=$dest_server_id&dn=" . rawurlencode( $dest_dn );
	$new_rdn = get_rdn( $dest_dn );
	$container = get_container( $dest_dn );

	if( array_key_exists( 'tree', $_SESSION ) ) {
	        // do we not have a tree and tree icons yet? Build a new ones.
		initialize_session_tree();
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];

		if( isset( $tree[$dest_server_id][$container] ) ) {
			$tree[$dest_server_id][$container][] = $dest_dn;
			sort( $tree[ $dest_server_id ][ $container ] );
			$tree_icons[$dest_server_id][$dest_dn] = get_icon( $ldapserver_dest, $dest_dn );

			$_SESSION['tree'] = $tree;
			$_SESSION['tree_icons'] = $tree_icons;
			session_write_close();
		}
	} ?>

	<center>
	<?php echo $lang['copy_successful_like_to']. "<a href=\"$edit_url\">" . $lang['copy_view_new_entry'] ."</a>"?>
	</center>
	<!-- refresh the tree view (with the new DN renamed)
	and redirect to the edit_dn page -->
	<script language="javascript">
		parent.left_frame.location.reload();
	</script>
	</body>
	</html>

	<?php if ($remove) {
		sleep(2);
		$delete_url = "delete_form.php?server_id=$dest_server_id&dn=" .rawurlencode( $source_dn ); ?>

		<!-- redirect to the delete form -->
		<script language="javascript">
			parent.right_frame.location="<?php echo $delete_url; ?>"
		</script>
	<?php }

} else {
	exit;
}

function r_copy_dn( $ldapserver_source, $ldapserver_dest, $tree, $root_dn, $dest_dn ) {
        global $lang;
	echo "<nobr>". $lang['copy_copying'] . htmlspecialchars( $root_dn ) . "...";
	flush();

	$copy_result = copy_dn( $ldapserver_source, $root_dn, $ldapserver_dest, $dest_dn );

	if( ! $copy_result )
		return false;

	echo "<span style=\"color:green\">".$lang['success']."</span></nobr><br />\n";
	flush();

	$children = isset( $tree[ $root_dn ] ) ? $tree[ $root_dn ] : null;
	if( is_array( $children ) && count( $children ) > 0 ) {
		foreach( $children as $child_dn ) {
			$child_rdn = get_rdn( $child_dn );
			$new_dest_dn = $child_rdn . ',' . $dest_dn;
			r_copy_dn( $ldapserver_source, $ldapserver_dest, $tree, $child_dn, $new_dest_dn );
		}

	} else {
		return true;
	}

	return true;
}

function copy_dn( $ldapserver_source, $source_dn, $ldapserver_dest, $dest_dn ) {
	global $lang;

	$attrs = get_object_attrs( $ldapserver_source, $source_dn );

	$new_entry = $attrs;
	// modify the prefix-value (ie "bob" in cn=bob) to match the destination DN's value.
	$rdn_attr = substr( $dest_dn, 0, strpos( $dest_dn, '=' ) );
	$rdn_value = get_rdn( $dest_dn );
	$rdn_value = substr( $rdn_value, strpos( $rdn_value, '=' ) + 1 );
	$new_entry[ $rdn_attr ] = $rdn_value;
	// don't need a dn attribute in the new entry
	unset( $new_entry['dn'] );

	// Check the user-defined custom call back first
	if( true === run_hook ( 'pre_entry_create', 
		array ( 'server_id' => $ldapserver_dest->server_id, 'dn' => $dest_dn, 'attrs' => $new_entry ) ) ) {

		$add_result = @ldap_add( $ldapserver_dest->connect(), $dest_dn, $new_entry );
		if( ! $add_result ) {
			run_hook ( 'post_entry_create', array ( 'server_id' => $ldapserver_dest->server_id,
				'dn' => $dest_dn, 'attrs' => $new_entry ) );

			echo "</small><br /><br />";
			pla_error( $lang['copy_failed'] . $dest_dn, ldap_error( $ldapserver_dest->connect() ), ldap_errno( $ldapserver_dest->connect() ) );
		}

		return $add_result;

	} else {
		return false;
	}
}

/**
 * @param object $ldapserver
 * @param dn $root_dn
 * @param unknown $tree
 * @param string $filter
 */
function build_tree( $ldapserver, $root_dn, &$tree, $filter='(objectClass=*)' )
{
	$children = get_container_contents( $ldapserver, $root_dn, 0, $filter );

	if( is_array( $children ) && count( $children ) > 0 ) {
		$tree[ $root_dn ] = $children;
		foreach( $children as $child_dn )
			build_tree( $ldapserver, $child_dn, $tree, $filter );
	}
}
?>
