<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_oclass_form.php,v 1.20.2.3 2005/10/22 11:55:31 wurley Exp $

/**
 * This page may simply add the objectClass and take you back to the edit page,
 * but, in one condition it may prompt the user for input. That condition is this:
 *
 *    If the user has requested to add an objectClass that requires a set of
 *    attributes with 1 or more not defined by the object. In that case, we will
 *    present a form for the user to add those attributes to the object.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as REQUEST vars:
 *  - dn (rawurlencoded)
 *  - new_oclass
 *
 * @package phpLDAPadmin
 * @todo If an attribute expects a DN, show the dn browser.
 */
/**
 */
require './common.php';

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

if (! isset($_POST['new_oclass']))
        pla_error( $lang['no_objectclasses_selected']);

$new_oclass = $_REQUEST['new_oclass'];
$dn = rawurldecode( $_REQUEST['dn'] );
$encoded_dn = rawurlencode( $dn );

/* Ensure that the object has defined all MUST attrs for this objectClass.
 * If it hasn't, present a form to have the user enter values for all the
 * newly required attrs. */

$entry = get_object_attrs( $ldapserver, $dn, true );

$current_attrs = array();
foreach( $entry as $attr => $junk )
	$current_attrs[] = strtolower($attr);

// grab the required attributes for the new objectClass
$schema_oclasses = $ldapserver->SchemaObjectClasses();
$must_attrs = array();
foreach( $new_oclass as $oclass_name ) {
	$oclass = $ldapserver->getSchemaObjectClass($oclass_name);
	if( $oclass )
		$must_attrs = array_merge( $must_attrs, $oclass->getMustAttrNames( $schema_oclasses ) );
}
$must_attrs = array_unique( $must_attrs );

// We don't want any of the attr meta-data, just the string
//foreach( $must_attrs as $i => $attr )
	//$must_attrs[$i] = $attr->getName();

// build a list of the attributes that this new objectClass requires,
// but that the object does not currently contain
$needed_attrs = array();
foreach( $must_attrs as $attr ) {
	$attr = $ldapserver->getSchemaAttribute($attr);

	//echo "<pre>"; var_dump( $attr ); echo "</pre>";

	// First, check if one of this attr's aliases is already an attribute of this entry
	foreach( $attr->getAliases() as $alias_attr_name )
		if( in_array( strtolower( $alias_attr_name ), $current_attrs ) )

		// Skip this attribute since it's already in the entry
			continue;

	if( in_array( strtolower($attr->getName()), $current_attrs ) )
		continue;

	// We made it this far, so the attribute needs to be added to this entry in order
	// to add this objectClass
	$needed_attrs[] = $attr;
}

if( count( $needed_attrs ) > 0 ) {
	include './header.php'; ?>
	<body>

	<h3 class="title"><?php echo $lang['new_required_attrs']; ?></h3>
	<h3 class="subtitle"><?php echo $lang['requires_to_add'] . ' ' . count($needed_attrs) .
		' ' . $lang['new_attributes']; ?></h3>

	<small>

	<?php echo $lang['new_required_attrs_instructions'];
	echo ' ' . count( $needed_attrs ) . ' ' . $lang['new_attributes'] . ' ';
	echo $lang['that_this_oclass_requires']; ?>

	</small>

	<br />
	<br />

	<form action="add_oclass.php" method="post">
	<input type="hidden" name="new_oclass" value="<?php echo rawurlencode( serialize( $new_oclass ) ); ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />

	<table class="edit_dn" cellspacing="0">
	<tr><th colspan="2"><?php echo $lang['new_required_attrs']; ?></th></tr>

	<?php foreach( $needed_attrs as $count => $attr ) { ?>

        <tr><td class="attr"><b><?php echo htmlspecialchars($attr->getName()); ?></b></td></tr>
	<tr><td class="val"><input type="text" name="new_attrs[<?php echo htmlspecialchars($attr->getName()); ?>]" value="" size="40" /></tr>
	<?php } ?>

	</table>
	<br />
	<br />
	<center><input type="submit" value="<?php echo $lang['add_oclass_and_attrs']; ?>" /></center>
	</form>

	</body>
	</html>

<?php } else {

	$add_res = @ldap_mod_add( $ldapserver->connect(), $dn, array( 'objectClass' => $new_oclass ) );
	if (! $add_res)
		pla_error("Could not perform ldap_mod_add operation.",
			  $ldapserver->error(),$ldapserver->errno());
	else
		header(sprintf('Location: edit.php?server_id=%s&dn=%s&modified_attrs[]=objectClass',
			$ldapserver->server_id,$encoded_dn));

}
?>
