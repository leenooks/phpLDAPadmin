<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/create.php,v 1.43.2.1 2005/10/09 09:07:21 wurley Exp $

/**
 * Creates a new object.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - new_dn
 *  - attrs (an array of attributes)
 *  - vals (an array of values for the above attrs)
 *  - required_attrs (an array with indices being the attributes,
 *		      and the values being their respective values)
 *  - object_classes (rawurlencoded, and serialized array of objectClasses)
 *
 * @package phpLDAPadmin
 */
/**
 * @todo: posixgroup with empty memberlist generates an error.
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error($lang['no_updates_in_read_only_mode']);
if (! $ldapserver->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

$new_dn = isset( $_POST['new_dn'] ) ? $_POST['new_dn'] : null;
$required_attrs = isset( $_POST['required_attrs'] ) ? $_POST['required_attrs'] : false;
$object_classes = unserialize( rawurldecode( $_POST['object_classes'] ) );
$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : false;

$encoded_dn = rawurlencode( $new_dn );
$container = get_container( $new_dn );

// See if there are any presubmit values to work out.
if (isset($_POST['presubmit']) && count($_POST['presubmit']) && isset($_POST['template'])) {
	$templates = new Templates($ldapserver->server_id);
	$template = $templates->GetTemplate($_POST['template']);

	foreach ($_POST['presubmit'] as $attr) {
		$_POST['attrs'][] = $attr;
		$_POST['form'][$attr] = $templates->EvaluateDefault($ldapserver,$template['attribute'][$attr]['presubmit'],$_POST['container']);
		$_POST['vals'][] = $_POST['form'][$attr];
	}

	# @todo: This section needs to be cleaned up, and will be when the old templates are removed. In the mean time...
	# Rebuild the $_POST['attrs'] & $_POST['vals'], as they can be inconsistent.
	unset($_POST['attrs']);
	unset($_POST['vals']);
	foreach ($_POST['form'] as $attr => $val) {
		$_POST['attrs'][] = $attr;
		$_POST['vals'][] = $val;
	}
}

$vals = isset( $_POST['vals'] ) ? $_POST['vals'] : array();
$attrs = isset( $_POST['attrs'] ) ? $_POST['attrs'] : array();

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
		if( is_attr_binary( $ldapserver, $attr ) ) {
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
			if (is_array($vals[$i])) {

				# If the array has blank entries, then ignore them.
				foreach ($vals[$i] as $value) {
#				$new_entry[$attr] = $vals[$i];
					if (trim($value))
						$new_entry[$attr][] = $value;
				}
			} else {
				$val = isset( $vals[$i] ) ? $vals[$i] : '';

				if( '' !== trim($val) )
					$new_entry[ $attr ][] = $val;
			}
		}
	}
}

$new_entry['objectClass'] = $object_classes;
if( ! in_array( 'top', $new_entry['objectClass'] ) )
	$new_entry['objectClass'][] = 'top';

foreach( $new_entry as $attr => $vals ) {

	// Check to see if this is a unique Attribute
	if( $badattr = checkUniqueAttr( $ldapserver, $new_dn, $attr, $vals ) ) {
		$search_href = sprintf('search.php?search=true&amp;form=advanced&amp;server_id=%s&amp;filter=%s=%s',
			$ldapserver->server_id,$attr,$badattr);
		pla_error(sprintf( $lang['unique_attr_failed'],$attr,$badattr,$new_dn,$search_href ) );
	}

	if( ! is_attr_binary( $ldapserver, $attr ) )
		if( is_array( $vals ) )
			foreach( $vals as $i => $v )
				$new_entry[ $attr ][ $i ] = $v;

		else
			$new_entry[ $attr ] = $vals;
}

//echo "<pre>"; var_dump( $new_dn );print_r( $new_entry ); echo "</pre>";

// Check the user-defined custom call back first
if( true === run_hook ( 'pre_entry_create', array ( 'server_id' => $ldapserver->server_id,'dn' => $new_dn,'attrs' => $new_entry ) ) )
	$add_result = @ldap_add( $ldapserver->connect(), $new_dn, $new_entry );

else {
	pla_error( $lang['create_could_not_add'] );
	exit;
}

if( $add_result ) {
	run_hook ( 'post_entry_create', array ( 'server_id' => $ldapserver->server_id, 'dn' => $new_dn, 'attrs' => $new_entry ) );

	if ($redirect)
		$redirect_url = $redirect;

	else
		$redirect_url = sprintf('edit.php?server_id=%s&dn=%s',$ldapserver->server_id,rawurlencode($new_dn));

	if( array_key_exists( 'tree', $_SESSION ) ) {
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];

		if( isset( $tree[$ldapserver->server_id][$container] ) ) {
			$tree[$ldapserver->server_id][$container][] = $new_dn;
			sort( $tree[$ldapserver->server_id][$container] );
			$tree_icons[$ldapserver->server_id][$new_dn] = get_icon( $ldapserver, $new_dn );
		}

		$_SESSION['tree'] = $tree;
		$_SESSION['tree_icons'] = $tree_icons;
		session_write_close();
	}
	?>

	<html>
	<head>

	<?php if (isset($tree[$ldapserver->server_id][$container]) || in_array($new_dn,$ldapserver->getBaseDN())) { ?>

	<!-- refresh the tree view (with the new DN renamed)
	     and redirect to the edit_dn page -->
	<script language="javascript">
		parent.left_frame.location.reload();
		location.href='<?php echo $redirect_url; ?>';
	</script>

	<?php } ?>

	<meta http-equiv="refresh" content="0; url=<?php echo $redirect_url; ?>" />
	</head>
	<body>

	<?php echo $lang['redirecting'] ?> <a href="<?php echo $redirect_url; ?>"><?php echo $lang['here']?></a>.

	</body>
	</html>

<?php } else {
	pla_error( $lang['create_could_not_add'], $ldapserver->error(), $ldapserver->errno() );
}
?>
