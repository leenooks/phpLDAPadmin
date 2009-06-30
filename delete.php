<?php 

/*
 * delete.php
 * Deletes a DN and presents a "job's done" message.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'common.php';

$encoded_dn = $_POST['dn'];
$dn = rawurldecode( $encoded_dn );
$server_id = $_POST['server_id'];

if( $dn === null )
	pla_error( "You must specify a DN." );

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );
$del_result = @ldap_delete( $ds, $dn );

if( $del_result )
{
	// kill the DN from the tree browser session variable and
	// refresh the tree viewer frame (left_frame)

	session_start();
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

	Object deleted successfully.

	<?php 


} else {
	pla_error( "Could not delete the object: " . htmlspecialchars( utf8_decode( $dn ) ), ldap_error( $ds ), ldap_errno( $ds ) );
}
