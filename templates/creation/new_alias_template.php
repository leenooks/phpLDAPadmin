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

<center><h2>New Alias</h2></center>

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
	<td class="heading">Alias To:</td>
	<td><input type="text" name="alias" size="40" value="" />
		<?php draw_chooser_link( 'posix_group_form.alias' ); ?>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></center></td>
</tr>
</table>
</center>
</form>

<?php } elseif( $step == 2 ) {

	$container = trim( $_POST['container'] );
	$alias = trim( $_POST['alias'] );
	//$alias_slashed = ereg_replace(",", "\\,", $alias);
	$attribute_parts = explode(',', $alias);
	$attribute_name1 = $attribute_parts[0];
	$attribute_parts = explode('=', $attribute_name1);
	$attribute_type = $attribute_parts[0];
	$attribute_name = $attribute_parts[1];
	$alias_slashed = str_replace(',', '\,', $alias);
	
	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	?>
	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( $attribute_name1.','.$container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'alias', 'extensibleObject' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="aliasedObjectName" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($alias);?>" />

	<input type="hidden" name="attrs[]" value="<?php echo htmlspecialchars($attribute_type); ?>" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($attribute_name); ?>" />
		
	<center>
	Really create this new Alias entry?<br />
	<br />

	<table class="confirm">
	<tr class="odd"><td>Container</td><td><b><?php echo htmlspecialchars( $container ); ?></b></td></tr>
	<tr class="even"><td>alias</td><td><b><?php echo htmlspecialchars( $alias ); ?></b></td></tr>
	</table>
	<br /><input type="submit" value="Create Alias" />
	</center>
	</form>

<?php } ?>

