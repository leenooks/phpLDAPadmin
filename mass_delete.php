<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/mass_delete.php,v 1.8 2004/03/19 20:13:08 i18phpldapadmin Exp $


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
	pla_error( $lang['no_deletes_in_read_only'] );
have_auth_info( $server_id ) or 
	pla_error( $lang['no_enough_login_info'] );
pla_ldap_connect( $server_id ) or 
	pla_error( $lang['could_not_connect'] );
$confirmed = isset( $_POST['confirmed'] ) ? true : false;
isset( $_POST['mass_delete'] ) or 
	pla_error( $lang['error_calling_mass_delete'] );
$mass_delete = $_POST['mass_delete'];
is_array( $mass_delete ) or 
	pla_error( $lang['mass_delete_not_array'] );
$ds = pla_ldap_connect( $server_id );
mass_delete_enabled( $server_id ) or 
	pla_error( $lang['mass_delete_not_enabled'] );

$server_name = $servers[ $server_id ][ 'name' ];

require realpath( 'header.php' );


echo "<body>\n";
echo "<h3 class=\"title\">" . $lang['mass_deleting'] . "</h3>\n";

if( $confirmed == true ) {
		echo "<h3 class=\"subtitle\">" . sprintf( $lang['mass_delete_progress'], $server_name ) . "</h3>\n";
		echo "<blockquote>";
		echo "<small>\n";

		$successfully_delete_dns = array();
		$failed_dns = array();

		if( ! is_array( $mass_delete ) )
				pla_error( $lang['malformed_mass_delete_array'] );
		if( count( $mass_delete ) == 0 ) {
				echo "<br />";
				echo "<center>" . $lang['no_entries_to_delete'] . "</center>";
				die();
		}

		foreach( $mass_delete as $dn => $junk ) {

				echo sprintf( $lang['deleting_dn'], htmlspecialchars($dn) );
				flush();

				if( true === preEntryDelete( $server_id, $dn ) ) {
						$success = @ldap_delete( $ds, $dn );
						if( $success ) {
								postEntryDelete( $server_id, $dn );
								echo " <span style=\"color:green\">" . $lang['success'] . "</span>.<br />\n";
								$successfully_delete_dns[] = $dn;
						}
						else {
								echo " <span style=\"color:red\">" . $lang['failed'] . "</span>.\n";
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
			echo sprintf( $lang['total_entries_failed'], $failed_count, $total_count ) . "</span>\n";
		} else {
			echo "<span style=\"color: green; font-weight: bold;\">\n";
			echo $lang['all_entries_successful'] . "</span>\n";
		}

	// kill the deleted DNs from the tree browser session variable and
	// refresh the tree viewer frame (left_frame)
	if( array_key_exists( 'tree', $_SESSION ) )
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
	echo "<h3 class=\"subtitle\">" . sprintf( $lang['confirm_mass_delete'], $n, $server_name ) . "</h3>\n";
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

	<input class="scary" type="submit" value="  <?php echo $lang['yes_delete']; ?>  " /></center>

	</form>

	<?php
}

?>
