<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/create.php,v 1.21 2004/05/11 12:23:08 uugdave Exp $


/*
 * create.php
 * Creates a new object.
 *
 * Variables that come in as POST vars:
 *  - new_dn
 *  - attrs (an array of attributes)
 *  - vals (an array of values for the above attrs)
 *  - required_attrs (an array with indices being the attributes,
 *		      and the values being their respective values)
 *  - object_classes (rawurlencoded, and serialized array of objectClasses)
 *  - server_id
 */

require realpath( 'common.php' );

$new_dn = isset( $_POST['new_dn'] ) ? $_POST['new_dn'] : null;
$encoded_dn = rawurlencode( $new_dn );
$server_id = $_POST['server_id'];
$vals = $_POST['vals'];
$attrs = $_POST['attrs'];
$required_attrs = isset( $_POST['required_attrs'] ) ? $_POST['required_attrs'] : false;
$object_classes = unserialize( rawurldecode( $_POST['object_classes'] ) );
$container = get_container( $new_dn );

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

// build the new entry
$new_entry = array();
if( isset( $required_attrs ) && is_array( $required_attrs ) ) {
	foreach( $required_attrs as $attr => $val ) {
		if( $val == '' )
			pla_error( sprintf( $lang['create_required_attribute'], htmlspecialchars( $attr ) ) );
		$new_entry[ $attr ][] = $val; 
	}
}

if( isset( $attrs ) && is_array( $attrs ) ) {
	foreach( $attrs as $i => $attr ) {
		if( is_attr_binary( $server_id, $attr ) ) {
			if( isset( $_FILES['vals']['name'][$i] ) && $_FILES['vals']['name'][$i] != '' ) {
				// read in the data from the file
				$file = $_FILES['vals']['tmp_name'][$i];
				$f = fopen( $file, 'r' );
				$binary_data = fread( $f, filesize( $file ) );
				fclose( $f );
				$val = $binary_data;
				$new_entry[ $attr ][] = $val;
			}
		} else {
            $val = isset( $vals[$i] ) ? $vals[$i] : '';
			if( '' !== trim($val) )
			  $new_entry[ $attr ][] = $val;
		}
	}
}

$new_entry['objectClass'] = $object_classes;
if( ! in_array( 'top', $new_entry['objectClass'] ) )
	$new_entry['objectClass'][] = 'top';

foreach( $new_entry as $attr => $vals )
	if( ! is_attr_binary( $server_id, $attr ) )
		if( is_array( $vals ) )
			foreach( $vals as $i => $v )
                           $new_entry[ $attr ][ $i ] = $v; 
			else
			$new_entry[ $attr ] = $vals; 

//echo "<pre>"; var_dump( $new_dn );print_r( $new_entry ); echo "</pre>";

$ds = pla_ldap_connect( $server_id );

// Check the user-defined custom call back first
if( true === preEntryCreate( $server_id, $new_dn, $new_entry ) ) 
	$add_result = @ldap_add( $ds, $new_dn, $new_entry );
else
	exit;
if( $add_result )
{
	postEntryCreate( $server_id, $new_dn, $new_entry );
	$edit_url="edit.php?server_id=$server_id&dn=" . rawurlencode( $new_dn );

	if( array_key_exists( 'tree', $_SESSION ) )
	{
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];

		if( isset( $tree[$server_id][$container] ) ) {
			$tree[$server_id][$container][] = $new_dn;
			sort( $tree[$server_id][$container] );
			$tree_icons[$server_id][$new_dn] = get_icon( $server_id, $new_dn );
		}

		$_SESSION['tree'] = $tree;
		$_SESSION['tree_icons'] = $tree_icons;
		session_write_close();
	}

	?>
	<html>
	<head>
		<?php 	if( isset( $tree[$server_id][$container] ) ) { ?>

		<!-- refresh the tree view (with the new DN renamed)
		     and redirect to the edit_dn page -->
		<script language="javascript">
			parent.left_frame.location.reload();
			location.href='<?php echo $edit_url; ?>';
		</script>

		<?php } ?>

		<meta http-equiv="refresh" content="0; url=<?php echo $edit_url; ?>" />
	</head>
	<body>
	<?php echo $lang['redirecting'] ?> <a href="<?php echo $edit_url; ?>"><?php echo $lang['here']?></a>.
	</body>
	</html>
	<?php
}
else
{
	pla_error( $lang['create_could_not_add'], ldap_error( $ds ), ldap_errno( $ds ) );
}

?>
