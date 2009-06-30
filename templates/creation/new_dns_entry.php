<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_dns_entry.php,v 1.8 2004/05/05 12:47:54 uugdave Exp $

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = 1;
if( isset($_POST['step']) )
    $step = $_POST['step'];

	check_server_id( $server_id ) or die( "Bad server_id: " . htmlspecialchars( $server_id ) );
	have_auth_info( $server_id ) or die( "Not enough information to login to server. Please check your configuration." );

	?>

	<center><h2>New DNS Entry</h2></center>

	<?php if( $step == 1 ) { ?>

	<form action="creation_template.php" method="post" name="dns_form">
	<input type="hidden" name="step" value="2" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

	<center>
	<table class="confirm">
	<tr>
		<td></td>
		<td class="heading"><acronym title="Domain Component">DC</acronym> Name:</td>
		<td><input type="text" name="dc_name" value="" /> <small>(hint: don't include "dc=")</small></td>
	</tr>
	<tr>
		<td></td>
		<td class="heading">Associated Domain:</td>
		<td><input type="text" name="associateddomain" value="" /></td>
	<tr>
	<tr>
		<td></td>
		<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
		<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
		<?php draw_chooser_link( 'dns_form.container' ); ?></td>
		</td>
	</tr>
	<tr>
		<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
	</tr>
	</table>
	</center>

<?php } elseif( $step == 2 ) {

	$dc_name = trim( $_POST['dc_name'] );
	$container = trim( $_POST['container'] );
	$associateddomain = trim( $_POST['associateddomain'] );

	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );
?>
	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'dc=' . $dc_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'dnsdomain', 'domainRelatedObject') ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
	
	<input type="hidden" name="attrs[]" value="associatedDomain" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($associateddomain);?>" />
	<input type="hidden" name="attrs[]" value="objectClass" />
		<input type="hidden" name="vals[]" value="top" />
	<input type="hidden" name="attrs[]" value="domainComponent" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($dc_name);?>" />

	<center>
	Really create this new <acronym title="Domain Component">DC</acronym> entry?<br />
	<br />
	
	<table class="confirm">
	<tr class="even"><td>Name</td><td><b><?php echo htmlspecialchars($dc_name); ?></b></td></tr>
	<tr class="odd"><td>Domain</td><td><b><?php echo htmlspecialchars($associateddomain); ?></b></td></tr>
	<tr class="even"><td>Container</td><td><b><?php echo htmlspecialchars( $container ); ?></b></td></tr>
	</table>
	<br /><input type="submit" value="Create Entry" />
	</center>

	<?php } ?>
