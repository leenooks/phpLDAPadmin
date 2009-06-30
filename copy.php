<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/copy.php,v 1.22 2004/04/23 12:21:53 uugdave Exp $


/*
 * copy.php
 * Copies a given object to create a new one.
 *
 * Vars that come in as POST vars
 *  - source_dn (rawurlencoded)
 *  - new_dn (form element)
 *  - server_id
 */

require realpath( 'common.php' );

$source_dn =  $_POST['old_dn'];
$dest_dn = $_POST['new_dn'];
$encoded_dn = rawurlencode( $source_dn );
$source_server_id = $_POST['server_id'];
$dest_server_id = $_POST['dest_server_id'];
$do_recursive = ( isset( $_POST['recursive'] ) && $_POST['recursive'] == 'on' ) ? true : false;

if( is_server_read_only( $dest_server_id ) )
	pla_error( $lang['copy_server_read_only'] );

check_server_id( $source_server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $source_server_id ) or pla_error( $lang['not_enough_login_info'] );
check_server_id( $dest_server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $dest_server_id ) or pla_error( $lang['not_enough_login_info'] );

include 'header.php';

/* Error checking */
if( 0 == strlen( trim( $dest_dn ) ) )
	pla_error( $lang['copy_dest_dn_blank'] );
if( pla_compare_dns( $source_dn,$dest_dn ) == 0 && $source_server_id == $dest_server_id )
	pla_error( $lang['copy_source_dest_dn_same'] );
if( dn_exists( $dest_server_id, $dest_dn ) )
	pla_error( sprintf( $lang['copy_dest_already_exists'], pretty_print_dn( $dest_dn ) ) );
if( ! dn_exists( $dest_server_id, get_container( $dest_dn ) ) )
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
	build_tree( $source_server_id, $source_dn, $snapshot_tree, $filter );
	echo " <span style=\"color:green\">" . $lang['success'] . "</span><br />\n";
	flush();

	// prevent script from bailing early on a long delete
	@set_time_limit( 0 );

	$copy_result = r_copy_dn( $source_server_id, $dest_server_id, $snapshot_tree, $source_dn, $dest_dn );
	echo "</small>\n";
} else {
	$copy_result = copy_dn( $source_server_id, $source_dn, $dest_server_id, $dest_dn );
}

if( $copy_result )
{
	$edit_url="edit.php?server_id=$dest_server_id&dn=" . rawurlencode( $dest_dn );
	$new_rdn = get_rdn( $dest_dn );
	$container = get_container( $dest_dn );

	if( array_key_exists( 'tree', $_SESSION ) )
	{
        // do we not have a tree and tree icons yet? Build a new ones.
        initialize_session_tree();
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];
		if( isset( $tree[$dest_server_id][$container] ) )
		{
			$tree[$dest_server_id][$container][] = $dest_dn;
			sort( $tree[ $dest_server_id ][ $container ] );
			$tree_icons[$dest_server_id][$dest_dn] = get_icon( $dest_server_id, $dest_dn );
			$_SESSION['tree'] = $tree;
			$_SESSION['tree_icons'] = $tree_icons;
			session_write_close();
		}
	}

	?>
		<!-- refresh the tree view (with the new DN renamed)
		and redirect to the edit_dn page -->
		<script language="javascript">
			parent.left_frame.location.reload();
		</script>
		<br />
		<center>
		<?php echo $lang['copy_successful_like_to']. "<a href=\"$edit_url\">" . $lang['copy_view_new_entry'] ."</a>?"?>
		</center>
        <br />
		<br />
		<br />
		<br />
		</body>
		</html>
		<?php
}
else
{
	exit;
}

function r_copy_dn( $source_server_id, $dest_server_id, $tree, $root_dn, $dest_dn )
{
        global $lang;
	echo "<nobr>". $lang['copy_copying'] . htmlspecialchars( $root_dn ) . "...";
	flush();
	$copy_result = copy_dn( $source_server_id, $root_dn, $dest_server_id, $dest_dn );

	if( ! $copy_result ) {
		return false;
	}

	echo "<span style=\"color:green\">".$lang['success']."</span></nobr><br />\n";
	flush();

	$children = isset( $tree[ $root_dn ] ) ? $tree[ $root_dn ] : null;
	if( is_array( $children ) && count( $children ) > 0 )
	{
		foreach( $children as $child_dn ) {
			$child_rdn = get_rdn( $child_dn );
			$new_dest_dn = $child_rdn . ',' . $dest_dn;
			r_copy_dn( $source_server_id, $dest_server_id, $tree, $child_dn, $new_dest_dn );
		}
	}
	else
	{
		return true;
	}

	return true;
}

function copy_dn( $source_server_id, $source_dn, $dest_server_id, $dest_dn )
{
	global $ds, $lang;
	$ds = pla_ldap_connect( $dest_server_id ) or pla_error( $lang['could_not_connect'] );
	$attrs = get_object_attrs( $source_server_id, $source_dn );
	$new_entry = $attrs;
	// modify the prefix-value (ie "bob" in cn=bob) to match the destination DN's value.
	$rdn_attr = substr( $dest_dn, 0, strpos( $dest_dn, '=' ) );
	$rdn_value = get_rdn( $dest_dn );
	$rdn_value = substr( $rdn_value, strpos( $rdn_value, '=' ) + 1 );
	$new_entry[ $rdn_attr ] = $rdn_value;
	// don't need a dn attribute in the new entry
	unset( $new_entry['dn'] );

	// Check the user-defined custom call back first
	if( true === preEntryCreate( $dest_server_id, $dest_dn, $new_entry ) ) {
			$add_result = @ldap_add( $ds, $dest_dn, $new_entry );
			if( ! $add_result ) {
					postEntryCreate( $dest_server_id, $dest_dn, $new_entry );
					echo "</small><br /><br />";
					pla_error( $lang['copy_failed'] . $dest_dn, ldap_error( $ds ), ldap_errno( $ds ) );
			}

			return $add_result;
	} else {
			return false;
	}
}

function build_tree( $source_server_id, $root_dn, &$tree, $filter='(objectClass=*)' )
{
	$children = get_container_contents( $source_server_id, $root_dn, 0, $filter );
	if( is_array( $children ) && count( $children ) > 0 )
	{
		$tree[ $root_dn ] = $children;
		foreach( $children as $child_dn )
			build_tree( $source_server_id, $child_dn, $tree, $filter );
	}
}
