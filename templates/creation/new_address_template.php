<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_address_template.php,v 1.9 2004/05/05 12:47:54 uugdave Exp $


// customize this to your needs
$default_container = "ou=Addresses";

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = isset( $_POST['step'] ) ? $_POST['step'] : 1;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

?>

<script language="javascript">
<!--

/*
 * Populates the common name field based on the last 
 * name concatenated with the first name, separated
 * by a blank
 */
function autoFillCommonName( form )
{
	var first_name;
	var last_name;
	var common_name;

        first_name = form.first_name.value;
        last_name = form.last_name.value;

	if( last_name == '' ) {
		return false;
	}

	common_name = last_name + ' ' + first_name;
	form.common_name.value = common_name;
}

-->
</script>

<center><h2>New Address Book Entry<br />
<small>(InetOrgPerson)</small></h2>
</center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" id="address_form" name="address_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/uid.png" /></td>
	<td class="heading">Name:</td>
	<td>
		<input type="text" name="first_name" 
			id="first_name" value="first" onChange="autoFillCommonName(this.form)" />
		<input type="text" name="last_name" 
			id="last_name" value="last" onChange="autoFillCommonName(this.form)" />
	</td>
</tr>
<tr>
	<td></td>
	<td class="heading">Common name:</td>
	<td><input type="text" name="common_name" id="common_name" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Organization:</td>
	<td><input type="text" name="organization" id="organization" value="" /></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/mail.png" /></td>
	<td class="heading">Address:</td>
	<td><input type="text" name="street" id="street" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">City:</td>
	<td><input type="text" name="city" id="city" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Postal code:</td>
	<td><input type="text" name="postal_code" id="postal_code" value="" /></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/phone.png" /></td>
	<td class="heading">Work phone:</td>
	<td><input type="text" name="telephone_number" id="telephone_number" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Fax:</td>
	<td><input type="text" name="fax_number" id="fax_number" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Mobile:</td>
	<td><input type="text" name="mobile_number" id="mobile_number" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Email:</td>
	<td><input type="text" name="email_address" id="email_address" value="" /></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td></td>
	<td class="heading">Container:</td>
	<td><input type="text" name="container" size="40"
		value="<?php if( isset( $container ) )
				echo htmlspecialchars( $container );
			     else
				echo htmlspecialchars( $default_container . ',' . $servers[$server_id]['base'] ); ?>" />
		<?php draw_chooser_link( 'address_form.container' ); ?></td>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
</table>
</center>

<?php } elseif( $step == 2 ) {

	$common_name = trim( $_POST['common_name'] );
	$first_name = trim( $_POST['first_name'] );
	$last_name = trim( $_POST['last_name'] );
	$organization = trim( $_POST['organization'] );
	$city = trim( $_POST['city'] );
	$postal_code = trim( $_POST['postal_code'] );
	$street = trim( $_POST['street'] );
	$telephone_number = trim( $_POST['telephone_number'] );
	$fax_number = trim( $_POST['fax_number'] );
	$mobile_number = trim( $_POST['mobile_number'] );
	$email_address = trim( $_POST['email_address'] );
	$container = trim( $_POST['container'] );

	/* Critical assertions */
	0 != strlen( $common_name ) or
		pla_error( "You cannot leave the Common Name blank. Please go back and try again." );

	?>
	<center><h3>Confirm entry creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'cn=' . $common_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'inetOrgPerson' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($common_name);?>" />
	<input type="hidden" name="attrs[]" value="givenName" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($first_name);?>" />
	<input type="hidden" name="attrs[]" value="sn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($last_name);?>" />
	<input type="hidden" name="attrs[]" value="o" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($organization);?>" />
	<input type="hidden" name="attrs[]" value="l" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($city);?>" />
	<input type="hidden" name="attrs[]" value="postalCode" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($postal_code);?>" />
	<input type="hidden" name="attrs[]" value="street" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($street);?>" />
	<input type="hidden" name="attrs[]" value="telephoneNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($telephone_number);?>" />
	<input type="hidden" name="attrs[]" value="facsimileTelephoneNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($fax_number);?>" />
	<input type="hidden" name="attrs[]" value="mobile" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($mobile_number);?>" />
	<input type="hidden" name="attrs[]" value="mail" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($email_address);?>" />

	<center>
	<table class="confirm">
	<tr class="even">
		<td class="heading">Common name:</td>
		<td><b><?php echo htmlspecialchars( $common_name ); ?></b></td>
	</tr>
	<tr class="odd">
		<td class="heading">First name:</td>
		<td><b><?php echo htmlspecialchars( $first_name ); ?></b></td>
	</tr>
	<tr class="even">
		<td class="heading">Last name:</td>
		<td><b><?php echo htmlspecialchars( $last_name ); ?></b></td>
	</tr>
	<tr class="odd">
		<td class="heading">Organization:</td>
		<td><?php echo htmlspecialchars( $organization ); ?></td>
	</tr>
	<tr class="even">
		<td class="heading">City:</td>
		<td><?php echo htmlspecialchars( $city ); ?></td>
	</tr>
	<tr class="odd">
		<td class="heading">Postal code:</td>
		<td><?php echo htmlspecialchars( $postal_code ); ?></td>
	</tr>
	<tr class="even">
		<td class="heading">Street:</td>
		<td><?php echo htmlspecialchars( $street ); ?></td>
	</tr>
	<tr class="odd">
		<td class="heading">Work phone:</td>
		<td><?php echo htmlspecialchars( $telephone_number ); ?></td>
	</tr>
	<tr class="even">
		<td class="heading">Fax:</td>
		<td><?php echo htmlspecialchars( $fax_number ); ?></td>
	</tr>
	<tr class="odd">
		<td class="heading">Mobile:</td>
		<td><?php echo htmlspecialchars( $mobile_number ); ?></td>
	</tr>
	<tr class="even">
		<td class="heading">Email:</td>
		<td><?php echo htmlspecialchars( $email_address ); ?></td>
	</tr>
	<tr class="odd">
		<td class="heading">Container:</td>
		<td><?php echo htmlspecialchars( $container ); ?></td>
	</tr>
	</table>
	<br /><input type="submit" value="Create Address" />
	</center>
	</form>

<?php } ?>

</body>
</html>
