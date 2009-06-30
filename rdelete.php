<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/rdelete.php,v 1.14 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/*
 * rdelete.php
 *
 * Recursively deletes the specified DN and all of its children
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 */

require realpath( 'common.php' );

$dn = $_POST['dn'];
$encoded_dn = rawurlencode( $dn );
$server_id = $_POST['server_id'];
$rdn = get_rdn( $dn );

if( ! $dn )
	pla_error( $lang['you_must_specify_a_dn'] );

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
dn_exists( $server_id, $dn ) or pla_error( sprintf( $lang['no_such_entry'], htmlspecialchars( $dn ) ) );

include 'header.php';
echo "<body>\n";
echo "<h3 class=\"title\">" . sprintf( $lang['deleting_dn'], htmlspecialchars($rdn) ) . "</h3>\n";
echo "<h3 class=\"subtitle\">" . $lang['recursive_delete_progress'] . "</h3>";
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

	if( array_key_exists( 'tree', $_SESSION ) )
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

	<?php 

	echo sprintf( $lang['entry_and_sub_tree_deleted_successfully'], '<b>' . htmlspecialchars( $dn ) . '</b>' );

} else {
	pla_error( sprintf( $lang['could_not_delete_entry'], htmlspecialchars( $dn ) ), ldap_error( $ds ), ldap_errno( $ds ) );
}


exit;


function pla_rdelete( $server_id, $dn )
{
	global $lang;
	$children = get_container_contents( $server_id, $dn );
	global $ds;
	$ds = pla_ldap_connect( $server_id );

	if( ! is_array( $children ) || count( $children ) == 0 ) {
		echo "<nobr>" . sprintf( $lang['deleting_dn'], htmlspecialchars( $dn ) ) . "...";
		flush();
		if( true === preEntryDelete( $server_id, $dn ) )
				if( @ldap_delete( $ds, $dn ) ) {
						postEntryDelete( $server_id, $dn );
						echo " <span style=\"color:green\">" . $lang['success'] . "</span></nobr><br />\n";
						return true;
				} else {
						pla_error( sprintf( $lang['failed_to_delete_entry'], htmlspecialchars( $dn ) ),
								ldap_error( $ds ), ldap_errno( $ds ) );
				}
	} else {
		foreach( $children as $child_dn ) {
			pla_rdelete( $server_id, $child_dn );
		}
		echo "<nobr>" . sprintf( $lang['deleting_dn'], htmlspecialchars( $dn ) ) . "...";
		flush();
		if( true === preEntryDelete( $server_id, $dn ) )
				if( @ldap_delete( $ds, $dn ) ) {
						postEntryDelete( $server_id, $dn );
						echo " <span style=\"color:green\">" . $lang['success'] . "</span></nobr><br />\n";
						return true;
				} else {
						pla_error( sprintf( $lang['failed_to_delete_entry'], htmlspecialchars( $dn ) ),
								ldap_error( $ds ), ldap_errno( $ds ) );
				}
	}

}
