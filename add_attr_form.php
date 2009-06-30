<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/add_attr_form.php,v 1.11 2005/03/05 06:27:06 wurley Exp $

/**
 * Displays a form for adding an attribute/value to an LDAP entry.
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );
$rdn = get_rdn( $dn );

$friendly_attrs = process_friendly_attr_table();

include './header.php'; ?>

<body>

<h3 class="title"><?php echo sprintf( $lang['add_new_attribute'], htmlspecialchars( $rdn ) ); ?></b></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<?php $attrs = get_object_attrs( $ldapserver, $dn );

$oclasses = get_object_attr( $ldapserver, $dn, 'objectClass' );
if( ! is_array( $oclasses ) )
	$oclasses = array( $oclasses );

$avail_attrs = array();

$schema_oclasses = get_schema_objectclasses( $ldapserver, $dn );
foreach( $oclasses as $oclass ) {
	$schema_oclass = get_schema_objectclass( $ldapserver, $oclass, $dn );

	if( $schema_oclass && 0 == strcasecmp( 'objectclass', get_class( $schema_oclass ) ) )
		$avail_attrs = array_merge( $schema_oclass->getMustAttrNames( $schema_oclasses ),
			$schema_oclass->getMayAttrNames( $schema_oclasses ),
			$avail_attrs );
}

$avail_attrs = array_unique( $avail_attrs );
$avail_attrs = array_filter( $avail_attrs, "not_an_attr" );
sort( $avail_attrs );

$avail_binary_attrs = array();
foreach( $avail_attrs as $i => $attr ) {

	if( is_attr_binary( $ldapserver, $attr ) ) {
		$avail_binary_attrs[] = $attr;
		unset( $avail_attrs[ $i ] );
	}
}
?>

<br />

<center>

<?php echo $lang['add_new_attribute'];

if( is_array( $avail_attrs ) && count( $avail_attrs ) > 0 ) { ?>

	<br />
	<br />

	<form action="add_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />

	<select name="attr">

	<?php $attr_select_html = '';
	usort($avail_attrs,"sortAttrs");
	foreach( $avail_attrs as $a ) {

		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
			$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" .
				htmlspecialchars($a) . ")";

		} else {
			$attr_display = htmlspecialchars( $a );
		}

		echo $attr_display;
		$attr_select_html .= "<option>$attr_display</option>\n";
		echo "<option value=\"" . htmlspecialchars($a) . "\">$attr_display</option>";
	} ?>

	</select>

	<input type="text" name="val" size="20" />
	<input type="submit" name="submit" value="<?php echo $lang['add']; ?>" class="update_dn" />
	</form>

<?php } else { ?>

	<br />
	<br />
	<small>(<?php echo $lang['no_new_attrs_available']; ?>)</small>
	<br />
	<br />

<?php } ?>

<?php echo $lang['add_new_binary_attr'];
if( count( $avail_binary_attrs ) > 0 ) { ?>

	<!-- Form to add a new BINARY attribute to this entry -->
	<form action="add_attr.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
	<input type="hidden" name="binary" value="true" />
	<br />

	<select name="attr">

	<?php $attr_select_html = '';

	usort($avail_binary_attrs,"sortAttrs");

	foreach( $avail_binary_attrs as $a ) {

		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
			$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" .
				htmlspecialchars($a) . ")";

		} else {
			$attr_display = htmlspecialchars( $a );
		}

		echo $attr_display;
		$attr_select_html .= "<option>$attr_display</option>\n";
		echo "<option value=\"" . htmlspecialchars($a) . "\">$attr_display</option>";
	} ?>

	</select>

	<input type="file" name="val" size="20" />
	<input type="submit" name="submit" value="<?php echo $lang['add']; ?>" class="update_dn" />

	<?php if( ! ini_get( 'file_uploads' ) )
		echo "<br><small><b>" . $lang['warning_file_uploads_disabled'] . "</b></small><br>";

	else
		echo "<br><small><b>" . sprintf( $lang['max_file_size'], ini_get( 'upload_max_filesize' ) ) . "</b></small><br>";
	?>

	</form>

<?php } else { ?>

	<br />
	<br />
	<small>(<?php echo $lang['no_new_binary_attrs_available']; ?>)</small>

<?php } ?>

</center>
</body>
</html>

<?php

/**
 * Given an attribute $x, this returns true if it is NOT already specified
 * in the current entry, returns false otherwise.
 *
 * @param attr $x
 * @return bool
 * @ignore
 */
function not_an_attr( $x ) {
	global $attrs;

	//return ! isset( $attrs[ strtolower( $x ) ] );
	foreach( $attrs as $attr => $values )
		if( 0 == strcasecmp( $attr, $x ) )
			return false;
	return true;
}
?>
