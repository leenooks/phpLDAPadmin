<?php 

/*
 * rdelete.php
 *
 * Recursively deletes the specified DN and all of its children
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'common.php';

$encoded_dn = $_POST['dn'];
$dn = rawurldecode( $encoded_dn );
$server_id = $_POST['server_id'];

if( ! $dn )
	pla_error( "You must specify a DN." );

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );

session_start();
include 'header.php';
echo "<body>\n";
echo "<h3 class=\"title\">Deleting" . htmlspecialchars( $dn) . "</h3>\n";
echo "<h3 class=\"subtitle\">Recursive delete progress</h3>\n";
echo "<br /><br />";
echo "<small>\n";
flush();

// prevent script from bailing early on a long delete
@set_time_limit( 0 );

$del_result = pla_rdelete( $server_id, $dn );
echo "</small><br />\n";
if( $del_result )
{
	// kill the DN from the tree browser session variable and
	// refresh the tree viewer frame (left_frame)

	if( session_is_registered( 'tree' ) )
	{
		$tree = $_SESSION['tree'];

		// does it have children? (it shouldn't, but hey, you never know)	
		if( isset( $tree[$server_id][$dn] ) )
			unset( $tree[$server_id][$dn] );
		
		// search and destroy
		foreach( $tree[$server_id] as $tree_dn => $subtree )
			foreach( $subtree as $key => $sub_tree_dn )
				if( 0 == strcasecmp( $sub_tree_dn, $dn ) ) 
					unset( $tree[$server_id][$tree_dn][$key] );
	}

	$_SESSION['tree'] = $tree;
	session_write_close();

	?>

	<script language="javascript">
		parent.left_frame.location.reload();
	</script>

	Object <b><?php echo htmlspecialchars( $dn ); ?></b> and sub-tree deleted successfully.

	<?php 


} else {
	pla_error( "Could not delete the object: " . htmlspecialchars( utf8_decode( $dn ) ), ldap_error( $ds ), ldap_errno( $ds ) );
}


exit;


function pla_rdelete( $server_id, $dn )
{
	$children = get_container_contents( $server_id, $dn );
	global $ds;
	$ds = pla_ldap_connect( $server_id );

	if( ! is_array( $children ) || count( $children ) == 0 ) {
		echo "<nobr>Deleting " . htmlspecialchars( $dn ) . "...";
		flush();
		if( ldap_delete( $ds, $dn ) ) {
			echo " <span style=\"color:green\">Success</span></nobr><br />\n";
			return true;
		} else {
			pla_error( "Failed to delete dn: " . htmlspecialchars( utf8_decode( $dn ) ),
	       				ldap_error( $ds ), ldap_errno( $ds ) );
		}
	} else {
		foreach( $children as $child_dn ) {
			pla_rdelete( $server_id, $child_dn );
		}
		echo "<nobr>Deleting " . htmlspecialchars( $dn ) . "...";
		flush();
		if( ldap_delete( $ds, $dn ) ) {
			echo " <span style=\"color:green\">Success</span></nobr><br />\n";
			return true;
		} else {
			pla_errror( "Failed to delete dn: " . htmlspecialchars( utf8_decode( $dn ) ),
	       				ldap_error( $ds ), ldap_errno( $ds ) );
		}
	}

}
