<?php 

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

require 'config.php';
require_once 'functions.php';

$new_dn = stripslashes( $_POST['new_dn'] );
$new_dn = utf8_encode( $new_dn );
$encoded_dn = rawurlencode( $new_dn );
$server_id = $_POST['server_id'];
$vals = $_POST['vals'];
$attrs = $_POST['attrs'];
$required_attrs = $_POST['required_attrs'];
$object_classes = unserialize( rawurldecode( $_POST['object_classes'] ) );
$container = get_container( $new_dn );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

// build the new entry
$new_entry = array();
if( isset( $required_attrs ) && is_array( $required_attrs ) )
{
	foreach( $required_attrs as $attr => $val ) 
	{
		if( $val == '' )
			pla_error( "Error, you left the value for required attribute <b>" .
					htmlspecialchars( $attr ) . "</b> blank." );

		$attr = stripslashes( $attr );
		$val  = stripslashes( $val );
		$new_entry[ $attr ][] = utf8_encode( $val );
	}
}

if( isset( $vals ) && is_array( $vals ) )
{
	foreach( $vals as $i => $val )
	{
		$val = stripslashes( $val );
		$attr = $attrs[$i];
		$attr = stripslashes( $attr );
		if( trim($val) )
			$new_entry[ $attr ][] = utf8_encode( $val );
	}
}

$new_entry['objectClass'] = $object_classes;
if( ! in_array( 'top', $new_entry['objectClass'] ) )
	$new_entry['objectClass'][] = 'top';

$ds = pla_ldap_connect( $server_id );
$add_result = @ldap_add( $ds, $new_dn, $new_entry );
if( $add_result )
{
	$edit_url="edit.php?server_id=$server_id&dn=" . rawurlencode( $new_dn );

	// update the session tree to reflect the change
	session_start();
	if( session_is_registered( 'tree' ) )
	{
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];

		if( isset( $tree[$server_id][$container] ) ) {
			$tree[$server_id][$container][] = $new_dn;
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
		</script>

		<?php } ?>

		<meta http-equiv="refresh" content="0; url=<?php echo $edit_url; ?>" />
	</head>
	<body>
	Redirecting... <a href="<?php echo $edit_url; ?>">here</a>.
	</body>
	</html>
	<?php 
}
else
{
	pla_error( "Could not add the object to the LDAP server.", ldap_error( $ds ), ldap_errno( $ds ) );
}

?>
