<?php 

/*
 * copy.php
 * Copies a given object to create a new one.
 *
 * Vars that come in as POST vars
 *  - source_dn (rawurlencoded)
 *  - new_dn (form element)
 *  - server_id
 */

require 'common.php';

session_start();

$source_dn = rawurldecode( $_POST['old_dn'] );
$dest_dn = utf8_encode( $_POST['new_dn'] );
$encoded_dn = rawurlencode( $old_dn );
$source_server_id = $_POST['server_id'];
$dest_server_id = $_POST['dest_server_id'];
$do_recursive = ( isset( $_POST['recursive'] ) && $_POST['recursive'] == 'on' ) ? true : false;

if( is_server_read_only( $dest_server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $source_server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $source_server_id ) );
have_auth_info( $source_server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
check_server_id( $dest_server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $dest_server_id ) );
have_auth_info( $dest_server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

include 'header.php';

/* Error checking */
if( 0 == strlen( trim( $dest_dn ) ) )
	pla_error( "You left the destination DN blank." );

if( strcasecmp( $source_dn,$dest_dn ) == 0 && $source_server_id == $dest_server_id )
	pla_error( "The source and destination DN are the same." );

if( $do_recursive ) {
	// build a tree similar to that of the tree browser to give to r_copy_dn
	$snapshot_tree = array();
	include 'header.php';
	echo "<body>\n";
	echo "<h3 class=\"title\">Copying " . htmlspecialchars( $source_dn ) . "</h3>\n";
	echo "<h3 class=\"subtitle\">Recursive copy progress</h3>\n";
	echo "<br /><br />";
	echo "<small>\n";
	echo "Building snapshot of tree to copy... ";
	flush();
	build_tree( $source_server_id, $source_dn, $snapshot_tree );
	echo " <span style=\"color:green\">Success</span><br />\n";
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
	if( session_is_registered( 'tree' ) )
	{
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
		Copy successful! Would you like to <a href="<?php echo $edit_url; ?>">view the new entry</a>?
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

function r_copy_dn( $source_server_id, $dest_server_id, &$tree, $root_dn, $dest_dn )
{
	echo "<nobr>Copying " . htmlspecialchars( $root_dn ) . "...";
	flush();
	$copy_result = copy_dn( $source_server_id, $root_dn, $dest_server_id, $dest_dn );
	
	if( ! $copy_result ) {
		global $R_COPY_ERROR;
		return false;
	}

	echo "<span style=\"color:green\">Success</span></nobr><br />\n";
	flush();

	$children = $tree[ $root_dn ];
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
	global $ds;
	$ds = pla_ldap_connect( $dest_server_id ) or pla_error( "Could not connect to LDAP server" );
	$attrs = get_object_attrs( $source_server_id, $source_dn );
	$new_entry = $attrs;
	// modify the prefix-value (ie "bob" in cn=bob) to match the destination DN's value.
	$rdn_attr = substr( $dest_dn, 0, strpos( $dest_dn, '=' ) );
	$rdn_value = get_rdn( $dest_dn );
	$rdn_value = substr( $rdn_value, strpos( $rdn_value, '=' ) + 1 );
	$new_entry[ $rdn_attr ] = $rdn_value;
	// don't need a dn attribute in the new entry
	unset( $new_entry['dn'] );
	$add_result = @ldap_add( $ds, $dest_dn, $new_entry );
	if( ! $add_result ) {
		echo "</small><br /><br />";
		pla_error( "Failed to copy $source_dn (server: $source_server_id) to " . 
				"$dest_dn (server: $dest_server_id)", ldap_error( $ds ), ldap_errno( $ds ) );
	}

	return $add_result;
}

function build_tree( $source_server_id, $root_dn, &$tree )
{
	$children = get_container_contents( $source_server_id, $root_dn );
	if( is_array( $children ) && count( $children ) > 0 )
	{
		$tree[ $root_dn ] = $children;
		foreach( $children as $child_dn )
			build_tree( $source_server_id, $child_dn, $tree );
	}

}
