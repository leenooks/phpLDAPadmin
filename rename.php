<?php 

/*
 * rename.php
 * Renames a DN to a different name. 
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - new_rdn
 */

require 'common.php';

$dn = rawurldecode( $_POST['dn'] );
$server_id = $_POST['server_id'];
$new_rdn = $_POST['new_rdn'];
$new_rdn = utf8_encode($new_rdn);

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP sever" );
 
// build the container string
$old_rdn = pla_explode_dn( $dn );
$container = $old_rdn[ 1 ];
for( $i=2; $i<count($old_rdn)-1; $i++ )
	$container .= ',' . $old_rdn[$i];

if( ! $container )
	pla_error( "Error: Container is null!" );

if( ! ldap_rename( $ds, $dn, $new_rdn, $container, false ) )
{
	pla_error( "Error: Could not rename the object.", ldap_error( $ds ), ldap_errno( $ds ) );
}
else
{
	// update the session tree to reflect the name change
	session_start();
	if( session_is_registered( 'tree' ) )
	{
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];
		$new_dn = $new_rdn . ',' . $container;
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

			<!-- If the JavaScript didn't work, here's a meta tag to the job -->
			<meta http-equiv="refresh" content="0; url=<?php echo $edit_url; ?>" />
		</head>
		<body>
		Redirecting... click <a href="<?php echo $edit_url; ?>">here</a> if you're impatient.
		</body>
		</html>

		<?php 

	}
}
