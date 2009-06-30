<?php

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$verify = isset( $_POST['verify'] ) ? $_POST['verify'] : false;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

?>
<center><h2>New Organizational Role<br />
<small>(organizationalRole)</small></h2></center>
<?php

if ( !$verify ) {

?>
<form action="creation_template.php" method="post" name="or_form">
<input type="hidden" name="verify" value="true" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="6" /></tr>
<tr>
	<td class="icon"><img src="images/uid.png" /></td>
	<td class="heading">Role <acronym title="Common Name">CN</acronym>:</td>
	<td colspan="4"><input type="text" name="or_name" /> <span class="hint">(hint: don't include "cn=")</span></td>
</tr>
<tr class="spacer"><td colspan="6" /></tr>
<tr>
	<td class="icon"><img src="images/phone.png" /></td>
	<td class="heading">Phone:</td>
	<td><input type="text" name="phone" id="phone" /></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Fax:</td>
	<td><input type="text" name="fax" id="fax" /></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr class="spacer"><td colspan="6" /></tr>
<tr>
	<td class="icon"><img src="images/light.png" /></td>
	<td class="heading">Comments:</td>
	<td colspan="4"><textarea cols="40" name="description" id="description"></textarea></td>
</tr>
<tr class="spacer"><td colspan="6" /></tr>
<tr>
	<td class="icon"><img src="images/object.png" /></td>
	<td class="heading">Occupant <acronym title="Distinguished Name">DN</acronym>:</td>
	<td colspan="4"><table class="browse"><tr><td>
		<input type="text" name="occupant" id="occupant" size="40" value="<?php echo htmlspecialchars( get_logged_in_dn($server_id) ); ?>" /></td><td><center><?php draw_chooser_link( 'or_form.occupant' ); ?></center></td></tr></table></td>
</tr>
<tr><th colspan="6"><b>Street Address</b></th></tr>
<tr>
	<td class="icon"><img src="images/mail.png" /></td>
	<td class="heading">Address:</td>
	<td><input type="text" name="street1" id="street1" /></td>
	<td class="icon"><img src="images/locality.png" /></td>
	<td class="heading">City:</td>
	<td><input type="text" name="city" id="city" /></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td><input type="text" name="street2" id="street2" /></td>
	<td></td>
	<td class="heading">State:</td>
	<td><input type="text" name="state" id="state" /></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td class="heading">ZIP:</td>
	<td><input type="text" name="zip" id="zip" /></td>
</tr>
<tr><th colspan="6"><b>Mailing Address</b></th></tr>
<tr>
	<td class="icon"><img src="images/mail.png" /></td>
	<td class="heading">Address:</td>
	<td><input type="text" name="mail_street1" id="mail_street1" /></td>
	<td class="icon"><img src="images/locality.png" /></td>
	<td class="heading">City:</td>
	<td><input type="text" name="mail_city" id="mail_city" /></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td><input type="text" name="mail_street2" id="mail_street2" /></td>
	<td></td>
	<td class="heading">State:</td>
	<td><input type="text" name="mail_state" id="mail_state" /></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td class="heading">ZIP:</td>
	<td><input type="text" name="mail_zip" id="mail_zip" /></td>
</tr>
<tr><th colspan="6"><b>Registered Mail Address</b></th></tr>
<tr>
	<td class="icon"><img src="images/mail.png" /></td>
	<td class="heading">Address:</td>
	<td><input type="text" name="reg_street1" id="reg_street1" /></td>
	<td class="icon"><img src="images/locality.png" /></td>
	<td class="heading">City:</td>
	<td><input type="text" name="reg_city" id="reg_city" /></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td><input type="text" name="reg_street2" id="reg_street2" /></td>
	<td></td>
	<td class="heading">State:</td>
	<td><input type="text" name="reg_state" id="reg_state" /></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td class="heading">ZIP:</td>
	<td><input type="text" name="reg_zip" id="reg_zip" /></td>
</tr>
<tr class="spacer"><td colspan="6" /></tr>
<tr>
	<td class="icon"><img src="images/folder.png" /></td>
	<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
	<td colspan="4"><table class="browse"><tr><td><input type="text" name="container" id="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" /></td><td><center><?php draw_chooser_link( 'or_form.container' ); ?></center></td></tr></table></td>
</tr>
<tr>
	<td colspan="6"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></center></td>
</tr>
</table>
</center>
</form>
<?php

} elseif ( $verify ) {

	$or_name = trim( $_POST['or_name'] );
	$phone = trim( $_POST['phone'] );
	$fax = trim( $_POST['fax'] );
	$description = trim( $_POST['description'] );
	$occupant = trim( $_POST['occupant'] );
	$street1 = trim( $_POST['street1'] );
	$street2 = trim( $_POST['street2'] );
	$city = trim( $_POST['city'] );
	$state = trim( $_POST['state'] );
	$zip = trim( $_POST['zip'] );
	$mail_street1 = trim( $_POST['mail_street1'] );
	$mail_street2 = trim( $_POST['mail_street2'] );
	$mail_city = trim( $_POST['mail_city'] );
	$mail_state = trim( $_POST['mail_state'] );
	$mail_zip = trim( $_POST['mail_zip'] );
	$reg_street1 = trim( $_POST['reg_street1'] );
	$reg_street2 = trim( $_POST['reg_street2'] );
	$reg_city = trim( $_POST['reg_city'] );
	$reg_state = trim( $_POST['reg_state'] );
	$reg_zip = trim( $_POST['reg_zip'] );
	$container = trim( $_POST['container'] );

	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
			   "Please go back and try again." );
	dn_exists( $server_id, $occupant ) or
		pla_error( "The occupant you specified (" . htmlspecialchars( $occupant ) . ") does not exist. " .
			   "Please go back and try again." );
	0 != strlen( $or_name ) or
		pla_error( "You cannot leave the Organization Name blank. Please go back and try again." );

?>
	<center><h3>Confirm entry creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'cn=' . $or_name . ',' . $container ); ?>" />
	<!-- objectClasses -->
<?php
	$object_classes = rawurlencode( serialize( array( 'organizationalRole' ) ) );
?>
	<input type="hidden" name="object_classes" value="<?php echo $object_classes?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($or_name); ?>" />
	<input type="hidden" name="attrs[]" value="telephoneNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($phone); ?>" />
	<input type="hidden" name="attrs[]" value="facsimileTelephoneNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($fax); ?>" />
	<input type="hidden" name="attrs[]" value="description" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($description); ?>" />
	<input type="hidden" name="attrs[]" value="roleOccupant" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($occupant); ?>" />
	<input type="hidden" name="attrs[]" value="street" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars( $street1 . ((!empty($street2))?'$'.$street2:'') ); ?>" />
	<input type="hidden" name="attrs[]" value="l" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($city); ?>" />
	<input type="hidden" name="attrs[]" value="st" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($state); ?>" />
	<input type="hidden" name="attrs[]" value="postalCode" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($zip); ?>" />
	<input type="hidden" name="attrs[]" value="postalAddress" />
		<input type="hidden" name="vals[]" value="<?php echo  (!empty($mail_street1)) ? htmlspecialchars( $mail_street1 . ((!empty($mail_street2))?'$'.$mail_street2:'') . ((!empty($mail_city)||!empty($mail_state)||!empty($mail_zip))?'$':'') . ((!empty($mail_city))?$mail_city:'') . ((!empty($mail_state))?', '.$mail_state:'') . ((!empty($mail_zip))?' '.$mail_zip:'') ) : '' ?>" />
	<input type="hidden" name="attrs[]" value="registeredAddress" />
		<input type="hidden" name="vals[]" value="<?php echo  (!empty($reg_street1)) ? htmlspecialchars( $reg_street1 . ((!empty($reg_street2))?'$'.$reg_street2:'') . ((!empty($reg_city)||!empty($reg_state)||!empty($reg_zip))?'$':'') . ((!empty($reg_city))?$reg_city:'') . ((!empty($reg_state))?', '.$reg_state:'') . ((!empty($reg_zip))?' '.$reg_zip:'') ) : '' ?>" />

	<center>
	<table class="confirm">
	<tr class="even"><td class="heading">Role <acronym title="Common Name">CN</acronym>:</td><td><b><?php echo htmlspecialchars($or_name); ?></b></td></tr>
	<tr class="odd"><td class="heading">Phone:</td><td><?php echo htmlspecialchars($phone); ?></td></tr>
	<tr class="even"><td class="heading">Fax:</td><td><?php echo htmlspecialchars($fax); ?></td></tr>
	<tr class="odd"><td class="heading">Comments:</td><td><?php echo htmlspecialchars($description); ?></td></tr>
	<tr class="even"><td class="heading">Occupant <acronym title="Distinguished Name">DN</acronym>:</td><td><?php echo htmlspecialchars($occupant); ?></td></tr>
	<tr class="odd"><td class="heading">Street:</td><td><?php echo htmlspecialchars($street1) . ((!empty($street2))?'<br />'.htmlspecialchars($street2):''); ?></td></tr>
	<tr class="even"><td class="heading">City:</td><td><?php echo htmlspecialchars($city); ?></td></tr>
	<tr class="odd"><td class="heading">State:</td><td><?php echo htmlspecialchars($state); ?></td></tr>
	<tr class="even"><td class="heading">Mailing Address:</td><td><?php echo (!empty($mail_street1)) ? htmlspecialchars($mail_street1) . ((!empty($mail_street2))?'<br />'.htmlspecialchars($mail_street2):'') . ((!empty($mail_city)||!empty($mail_state)||!empty($mail_zip))?'<br />':'') . ((!empty($mail_city))?htmlspecialchars($mail_city):'') . ((!empty($mail_state))?', '.htmlspecialchars($mail_state):'') . ((!empty($mail_zip))?' '.htmlspecialchars($mail_zip):'') : '' ?></td></tr>
	<tr class="odd"><td class="heading">Registered Address:</td><td><?php echo (!empty($reg_street1)) ? htmlspecialchars($reg_street1) . ((!empty($reg_street2))?'<br />'.htmlspecialchars($reg_street2):'') . ((!empty($reg_city)||!empty($reg_state)||!empty($reg_zip))?'<br />':'') . ((!empty($reg_city))?htmlspecialchars($reg_city):'') . ((!empty($reg_state))?', '.htmlspecialchars($reg_state):'') . ((!empty($reg_zip))?' '.htmlspecialchars($reg_zip):'') : '' ?></td></tr>
	<tr class="even"><td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td><td><?php echo htmlspecialchars($container); ?></td></tr>
	</table>
	<br /><input type="submit" value="Create Organizational Role" />
	</center>
<?php
}
?>
