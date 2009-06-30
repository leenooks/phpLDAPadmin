<?php

/*
 * mass_delete.php
 *
 * Enables user to mass delete multiple entries using checkboxes.
 *
 * Variables that come in as GET vars:
 *  - mass_delete - an array of DNs to delete in this form:
 *      Array (
 *          [o=myorg,dc=example,dc=com] => on
 *          [cn=bob,dc=example,dc=com] => on
 *          etc.
 *  - server_id
 */

require realpath( 'common.php' );

$server_id = $_POST['server_id'];

check_server_id( $server_id ) or 
	pla_error( $lang['bad_server_id'] );
if( is_server_read_only( $server_id ) )
	pla_error( "Deletes not allowed in read only mode." );
have_auth_info( $server_id ) or 
	pla_error( $lang['no_enough_login_info'] );
pla_ldap_connect( $server_id ) or 
	pla_error( $lang['could_not_connect'] );
$confirmed = isset( $_POST['confirmed'] ) ? true : false;
isset( $_POST['mass_delete'] ) or 
	pla_error( "Error in calling mass_delete.php. Missing mass_delete in POST vars." );
$mass_delete = $_POST['mass_delete'];
is_array( $mass_delete ) or 
	pla_error( "mass_delete variable is not an array in POST vars!" );
$ds = pla_ldap_connect( $server_id );
mass_delete_enabled( $server_id ) or 
	pla_error( "Mass deletion is not enabled. Please enable it in config.php before proceeding. " );

$server_name = $servers[ $server_id ][ 'name' ];
session_start();

require realpath( 'header.php' );


echo "<body>\n";
echo "<h3 class=\"title\">Mass Deleting</h3>\n";

if( $confirmed == true ) {
		echo "<h3 class=\"subtitle\">Deletion progress on server '$server_name'</h3>\n";
		echo "<blockquote>";
		echo "<small>\n";

		$successfully_delete_dns = array();
		$failed_dns = array();

		if( ! is_array( $mass_delete ) )
				pla_error( "Malformed mass_delete array" );
		if( count( $mass_delete ) == 0 ) {
				echo "<br />";
				echo "<center>You did not select any entries to delete.</center>";
				die();
		}

		foreach( $mass_delete as $dn => $junk ) {

				echo "Deleting <b>" . htmlspecialchars($dn) . "</b>... ";
				flush();

				if( true === preEntryDelete( $server_id, $dn ) ) {
						$success = @ldap_delete( $ds, $dn );
						if( $success ) {
								postEntryDelete( $server_id, $dn );
								echo "<span style=\"color:green\">success</span>.<br />\n";
								$successfully_delete_dns[] = $dn;
						}
						else {
								echo "<span style=\"color:red\">failed</span>.\n";
								echo "(" . ldap_error( $ds ) . ")<br />\n";
								$failed_dns[] = $dn;
						}
				}

				flush();
		}

		echo "<blockquote>";
		echo "</small>\n";

		$failed_count = count( $failed_dns );
		$total_count = count( $mass_delete );
		if( $failed_count > 0 ) { 
			echo "<span style=\"color: red; font-weight: bold;\">\n";
			echo "$failed_count of $total_count entries failed to be deleted.</span>\n";
		} else {
			echo "<span style=\"color: green; font-weight: bold;\">\n";
			echo "All entries deleted successfully!</span>\n";
		}

	// kill the deleted DNs from the tree browser session variable and
	// refresh the tree viewer frame (left_frame)
	if( session_is_registered( 'tree' ) )
	{
		$tree = $_SESSION['tree'];
		foreach( $successfully_delete_dns as $dn ) {
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

	}

} else {
	$n = count( $mass_delete );
	echo "<h3 class=\"subtitle\">Confirm mass delete of $n entries on server '$server_name'</h3>\n";
	?>
	<center>
	
	Do you really want to delete 
	<?php echo ($n==1?'this':'these') . ' ' . $n . ' ' . ($n==1?'entry':'entries'); ?>?

	<form action="mass_delete.php" method="post">
	<input type="hidden" name="confirmed" value="true" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />

	<table><tr><td>
	<ol>
	<?php foreach( $mass_delete as $dn => $foo ) {
		echo "<input type=\"hidden\" name=\"mass_delete[" . htmlspecialchars($dn) . "]\" value=\"on\" />\n";
		echo "<li>" . htmlspecialchars( $dn ) . "</li>\n";
	} ?>
	</ol>
	</td></tr></table>

	<input class="scary" type="submit" value="  Yes, delete!  " /></center>

	</form>

	<?php
}

?>
