<?php

require 'common.php';

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = $_POST['step'];
if( ! $step )
	$step = 1;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

?>

<center><h2>New Organizational Unit</h2></center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" name="ou_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo $_POST['template']; ?>" />

<center>
<table class="confirm">
<tr>
	<td></td>
	<td class="heading"><acronym title="Organizational Unit">OU</acronym> Name:</td>
	<td><input type="text" name="ou_name" value="" /> <small>(hint: don't include "ou=")</small></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
	<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
		<?php draw_chooser_link( 'ou_form.container' ); ?></td>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
</table>
</center>

<?php } elseif( $step == 2 ) {

	$ou_name = trim( $_POST['ou_name'] );
	$container = trim( $_POST['container'] );
	
	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	?>
	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'ou=' . $ou_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'organizationalUnit' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="ou" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($ou_name);?>" />
	<input type="hidden" name="attrs[]" value="cn" />

	<center>
	Really create this new <acronym title="Organizational Unit">OU</acronym>?<br />
	<br />

	<table class="confirm">
	<tr class="even"><td>Name</td><td><b><?php echo htmlspecialchars($ou_name); ?></b></td></tr>
	<tr class="odd"><td>Container</td><td><b><?php echo htmlspecialchars( $container ); ?></b></td></tr>
	</table>
	<br /><input type="submit" value="Create OU" />
	</center>

<?php } ?>

