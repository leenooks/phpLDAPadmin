<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/Attic/new_postfix_account_template.php,v 1.2 2004/10/28 13:37:40 uugdave Exp $


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

<center><h2>New Postfix Mail Account<br />
<small>(CourierMailAccount)</small></h2>
</center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" id="address_form" name="address_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td width="32"><img src="images/uid.png" /></td>
	<td class="heading">uid:</td>
	<td><input type="text" name="uid" id="uid" value="" /></td>
	</tr>
<tr>
	<td></td>
	<td class="heading">Home Directory:</td>
	<td><input type="text" name="home_directory" id="home_directory" value="/home/" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Mailbox:</td>
	<td><input type="text" name="mailbox" id="mailbox" value="" /></td>
</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td width="32"><img src="images/mail.png" /></td>
	<td class="heading">Email:</td>
	<td><input type="text" name="email_address" id="email_address2" value="" /></td>
	</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/lock.png" /></td>
	<td class="heading">Password:</td>
	<td><input type="password" name="user_pass1" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Password:</td>
	<td><input type="password" name="user_pass2" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Encryption:</td>
	<td><select name="encryption">
		<option>clear</option>
		<option>md5</option>
		<option>smd5</option>
		<option>crypt</option>
		<option>sha</option>
		<option>ssha</option>
	    </select></td>
</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td></td>
	<td class="heading">Container:</td>
	<td><input type="text" name="container" size="40"
		value="<?php if( isset( $container ) )
				echo htmlspecialchars( $container );
			     else
				echo htmlspecialchars( $default_container . ',' . $servers[$server_id]['base'] ); ?>" />
		<?php draw_chooser_link( 'address_form.container' ); ?></td>
	</tr>
<tr>
	<td colspan="3" style="text-align: center"><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
</table>
</center>
</form>

<?php } elseif( $step == 2 ) {

	$uid = trim( $_POST['uid'] );
	$home_directory = trim( $_POST['home_directory'] );
	$mailbox = trim( $_POST['mailbox'] );
	$email_address = trim( $_POST['email_address'] );
	$password1 = $_POST['user_pass1'];
	$password2 = $_POST['user_pass2'];
	$encryption = $_POST['encryption'];
	$container = trim( $_POST['container'] );

	/* Critical assertions */
	$password1 == $password2 or
		pla_error( "Your passwords don't match. Please go back and try again." );

	$password = password_hash( $password1, $encryption );
	?>
	<center><h3>Confirm entry creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'uid=' . $uid . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'courierMailAccount' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="uid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($uid);?>" />
	<input type="hidden" name="attrs[]" value="homeDirectory" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($home_directory);?>" />
	<input type="hidden" name="attrs[]" value="mailbox" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($mailbox);?>" />
	<input type="hidden" name="attrs[]" value="mail" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($email_address);?>" />
	<input type="hidden" name="attrs[]" value="userPassword" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($password);?>" />


	<center>
	<table class="confirm">
	<tr class="even">
		<td class="heading">uid:</td>
		<td><?php echo htmlspecialchars( $uid ); ?></td>
	</tr>
	<tr class="odd">
		<td class="heading">Home Directory:</td>
		<td><?php echo htmlspecialchars( $home_directory ); ?></td>
	</tr>
	<tr class="even">
		<td class="heading">Mailbox:</td>
		<td><?php echo htmlspecialchars( $mailbox ); ?></td>
	</tr>
	</tr>
	<tr class="odd">
		<td class="heading">Email:</td>
		<td><?php echo htmlspecialchars( $email_address ); ?></td>
	</tr>
	<tr class="even">
		<td class="heading">Password:</td>
		<td>[secret]</td>
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
