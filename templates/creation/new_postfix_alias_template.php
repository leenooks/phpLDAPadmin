<?php
// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Change this to suit your needs
$default_number_of_users = 10;

$step = 1;
if( isset($_POST['step']) )
    $step = $_POST['step'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

?>

<center><h2>New Postfix Alias</h2></center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" name="posix_group_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr>
	<td></td>
	<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
	<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
		<?php draw_chooser_link( 'posix_group_form.container' ); ?>
	</td>
</tr>
<tr>
	<td></td>
	<td class="heading">Mail:</td>
	<td><input type="text" name="mail" id="mail" value="" /></td>
</tr>
<tr>
        <td></td>
        <td class="heading">Maildrop:</td>
        <td><input type="text" name="maildrop" id="maildrop" value="" /></td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></center></td>
</tr>
</table>
</center>
</form>

<?php } elseif( $step == 2 ) {

	$container = trim( $_POST['container'] );
	$mail = trim( $_POST['mail'] );
        $maildrop = trim( $_POST['maildrop'] );
	
	dn_exists( $ldapserver, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	?>
	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'mail='.$mail.','.$container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'couriermailalias' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
        <input type="hidden" name="attrs[]" value="mail" />
                <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($mail);?>" />
        <input type="hidden" name="attrs[]" value="maildrop" />
                <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($maildrop);?>" />


	<center>
	Really create this new Alias entry?<br />
	<br />

	<table class="confirm">
	<tr class="odd"><td>Container</td><td><b><?php echo htmlspecialchars( $container ); ?></b></td></tr>
	<tr class="even"><td>mail</td><td><b><?php echo htmlspecialchars( $mail ); ?></b></td></tr>
        <tr class="odd"><td>maildrop</td><td><b><?php echo htmlspecialchars( $maildrop ); ?></b></td></tr>
	</table>
	<br /><input type="submit" value="Create Alias" />
	</center>
	</form>

<?php } ?>

