<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/add_oclass_form.php,v 1.15 2004/10/22 13:58:59 uugdave Exp $
 

/*
 * add_oclass_form.php
 * This page may simply add the objectClass and take you back to the edit page,
 * but, in one condition it may prompt the user for input. That condition is this:
 *
 *    If the user has requested to add an objectClass that requires a set of
 *    attributes with 1 or more not defined by the object. In that case, we will
 *    present a form for the user to add those attributes to the object.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - new_oclass
 */

require './common.php';

$dn = rawurldecode( $_POST['dn'] );
$encoded_dn = rawurlencode( $dn );
$new_oclass = $_POST['new_oclass'];
$server_id = $_POST['server_id'];

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

/* Ensure that the object has defined all MUST attrs for this objectClass.
 * If it hasn't, present a form to have the user enter values for all the
 * newly required attrs. */

$entry = get_object_attrs( $server_id, $dn, true );
$current_attrs = array();
foreach( $entry as $attr => $junk )
	$current_attrs[] = strtolower($attr);

// grab the required attributes for the new objectClass
$oclass = get_schema_objectclass( $server_id, $new_oclass );
if( $oclass )
	$must_attrs = $oclass->getMustAttrs();
else
	$must_attrs = array();

// We don't want any of the attr meta-data, just the string
//foreach( $must_attrs as $i => $attr )
	//$must_attrs[$i] = $attr->getName();

// build a list of the attributes that this new objectClass requires,
// but that the object does not currently contain
$needed_attrs = array();
foreach( $must_attrs as $attr ) {
    $attr = get_schema_attribute( $server_id, $attr->getName() );
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

if( count( $needed_attrs ) > 0 )
{
	include './header.php'; ?>
	<body>
	
	<h3 class="title"><?php echo $lang['new_required_attrs']; ?></h3>
	<h3 class="subtitle"><?php echo $lang['requires_to_add'] . ' ' . count($needed_attrs) . 
					' ' . $lang['new_attributes']; ?></h3>

	<small>
	<?php 
	echo $lang['new_required_attrs_instructions'];
	echo ' ' . count( $needed_attrs ) . ' ' . $lang['new_attributes'] . ' ';
	echo $lang['that_this_oclass_requires']; ?>
	</small>

	<br />
	<br />
	
	<form action="add_oclass.php" method="post">
	<input type="hidden" name="new_oclass" value="<?php echo htmlspecialchars( $new_oclass ); ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	
	<table class="edit_dn" cellspacing="0">
	<tr><th colspan="2"><?php echo $lang['new_required_attrs']; ?></th></tr>

	<?php foreach( $needed_attrs as $count => $attr ) { ?>
        <tr><td class="attr"><b><?php echo htmlspecialchars($attr->getName()); ?></b></td></tr>
		<tr><td class="val"><input type="text" name="new_attrs[<?php echo htmlspecialchars($attr->getName()); ?>]" value="" size="40" /></tr>
	<?php  } ?>

	</table>
	<br />
	<br />
	<center><input type="submit" value="<?php echo $lang['add_oclass_and_attrs']; ?>" /></center>
	</form>

	</body>
	</html>

	<?php
}
else
{
	$ds = pla_ldap_connect( $server_id );
	pla_ldap_connection_is_error( $ds );
	$add_res = @ldap_mod_add( $ds, $dn, array( 'objectClass' => $new_oclass ) );
	if( ! $add_res )
		pla_error( "Could not perform ldap_mod_add operation.", ldap_error( $ds ), ldap_errno( $ds ) );
	else
		header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&modified_attrs[]=objectClass" );

}

?>
