<?php 

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

require 'config.php';
require_once 'functions.php';

$dn = stripslashes( rawurldecode( $_POST['dn'] ) );
$encoded_dn = rawurlencode( $dn );
$new_oclass = stripslashes( $_POST['new_oclass'] );
$server_id = $_POST['server_id'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

/* Ensure that the object has defined all MUST attrs for this objectClass.
 * If it hasn't, present a form to have the user enter values for all the
 * newly required attrs. */

$entry = get_object_attrs( $server_id, $dn, true );
$current_attrs = array();
foreach( $entry as $attr => $junk )
	$current_attrs[] = strtolower($attr);
// grab the required attributes for the new objectClass
$must_attrs = get_schema_objectclasses( $server_id );
$must_attrs = $must_attrs[ strtolower($new_oclass) ]['must_attrs'];
sort( $must_attrs );
// build a list of the attributes that this new objectClass requires,
// but that the object does not currently contain
$needed_attrs = array();
foreach( $must_attrs as $attr )
	if( ! in_array( strtolower($attr), $current_attrs ) )
		$needed_attrs[] = $attr;

if( count( $needed_attrs ) > 0 )
{
	?>


	<?php include 'header.php'; ?>
	<body>
	
	<h3 class="title">New Required Attributes</h3>
	<h3 class="subtitle">This action requires you to add <?php echo count($needed_attrs); ?> new attribute<?php echo (count($needed_attrs)>1?'s':''); ?></h3>

	<small>
	Instrucitons: In order to add the objectClass <b><?php echo $new_oclass; ?></b> to the object <b><?php echo htmlspecialchars($dn); ?></b>,
	you must specify <?php echo count( $needed_attrs ); ?> new attribute<?php echo (count($needed_atts)>1?'s':''); ?> that this
	objectClass requires. You can do so in this form.</small>

	<br />
	<br />
	
	<form action="add_oclass.php" method="post">
	<input type="hidden" name="new_oclass" value="<?php echo htmlspecialchars( $new_oclass ); ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	
	<table class="edit_dn" cellspacing="0">
	<tr><th colspan="2">New Required Attributes</th></tr>

	<?php foreach( $needed_attrs as $count => $attr ) { ?>
		<?php  if( $count % 2 == 0 ) { ?>
			<tr class="row1">
		<?php  } else { ?>
			<tr class="row2">
		<?php  } ?>
		<td class="attr"><b><?php echo htmlspecialchars($attr); ?></b></td>
		<td class="val"><input type="text" name="new_attrs[<?php echo htmlspecialchars($attr); ?>]" value="" size="40" />
	</tr>
	<?php  } ?>

	</table>
	<br />
	<br />
	<center><input type="submit" value="Add ObjectClass and Attributes" /></center>
	</form>

	</body>
	</html>

	<?php
}
else
{
	$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server." );
	$add_res = @ldap_mod_add( $ds, $dn, array( 'objectClass' => $new_oclass ) );
	if( ! $add_res )
		pla_error( "Could not perform ldap_mod_add operation.", ldap_error( $ds ), ldap_errno( $ds ) );
	else
		header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn" );

}

?>
