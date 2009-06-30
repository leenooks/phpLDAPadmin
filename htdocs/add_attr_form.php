<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_attr_form.php,v 1.13.2.2 2005/12/09 14:27:32 wurley Exp $

/**
 * Displays a form for adding an attribute/value to an LDAP entry.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if( $ldapserver->isReadOnly() )
	pla_error( _('You cannot perform updates while server is in read-only mode') );
if( ! $ldapserver->haveAuthInfo())
	pla_error( _('Not enough information to login to server. Please check your configuration.') );

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );
$rdn = get_rdn( $dn );

$friendly_attrs = process_friendly_attr_table();

include './header.php'; ?>

<body>

<h3 class="title"><?php echo sprintf( _('Add new attribute'), htmlspecialchars( $rdn ) ); ?></b></h3>
<h3 class="subtitle"><?php echo _('Server'); ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo _('Distinguished Name'); ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<?php $attrs = $ldapserver->getDNAttrs($dn);

$oclasses = $ldapserver->getDNAttr($dn,'objectClass');
if( ! is_array( $oclasses ) )
	$oclasses = array( $oclasses );

$avail_attrs = array();

$schema_oclasses = $ldapserver->SchemaObjectClasses($dn);
foreach( $oclasses as $oclass ) {
	$schema_oclass = $ldapserver->getSchemaObjectClass($oclass,$dn);

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

	if ($ldapserver->isAttrBinary($attr)) {
		$avail_binary_attrs[] = $attr;
		unset( $avail_attrs[ $i ] );
	}
}
?>

<br />

<center>

<?php echo _('Add new attribute');

if( is_array( $avail_attrs ) && count( $avail_attrs ) > 0 ) { ?>

	<br />
	<br />

	<form action="add_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
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
	<input type="submit" name="submit" value="<?php echo _('Add'); ?>" class="update_dn" />
	</form>

<?php } else { ?>

	<br />
	<br />
	<small>(<?php echo _('no new attributes available for this entry'); ?>)</small>
	<br />
	<br />

<?php } ?>

<?php echo _('Add new binary attribute');
if( count( $avail_binary_attrs ) > 0 ) { ?>

	<!-- Form to add a new BINARY attribute to this entry -->
	<form action="add_attr.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
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
	<input type="submit" name="submit" value="<?php echo _('Add'); ?>" class="update_dn" />

	<?php if( ! ini_get( 'file_uploads' ) )
		echo "<br><small><b>" . _('Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.') . "</b></small><br>";

	else
		echo "<br><small><b>" . sprintf( _('Maximum file size: %s'), ini_get( 'upload_max_filesize' ) ) . "</b></small><br>";
	?>

	</form>

<?php } else { ?>

	<br />
	<br />
	<small>(<?php echo _('no new binary attributes available for this entry'); ?>)</small>

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
