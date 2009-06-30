<?php

// customize this to your needs
$default_container = "";

// Common to all templates
$container = isset( $_POST['container'] ) ? $_POST['container'] : null;
$server_id = isset( $_POST['server_id'] ) ? $_POST['server_id'] : false;

// Modify this array and add/remove the corresponding attributes below
$object_classes = array( 'top', 'sendmailMTAClass' );

// Get default cluster from container
$dn_array = ldap_explode_dn($container, 0);
$cluster_name = "";
$host_name = "";
foreach ($dn_array as $attr) {
	if (preg_match('/(.+)=(.+)/', $attr, $match)) {
		$attr = $match[1];
		$value = $match[2];

		if (preg_match('/Cluster/i', $attr)) {
			$cluster_name = $value;
		}
		if (preg_match('/Host/i', $attr)) {
			$host_name = $value;
		}
	}
}


// A list of default attributes/values to create with this new user
$default_attributes = array( 
			'sendmailMTAClassName' => 'w', 
			);

// Unique to this template
$step = 1;
if( isset($_POST['step']) )
    $step = $_POST['step'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
?>

<?php if ($step == 1) { ?>

<center>
<h2 style="margin:0px">New Sendmail Domain</h2>
<br />
</center>

<form action="creation_template.php" method="post" name="sendmail_domain_form">
<input type="hidden" name="step" value="<?php echo $step + 1; ?>" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/object.png" /></td>
	<td class="heading">Sendmail cluster  name:</td>
	<td><input type="text" name="sendmailMTACluster" id="sendmailMTACluster" value="<?php echo $cluster_name; ?>" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Sendmail host  name:</td>
	<td><input type="text" name="sendmailMTAHost" id="sendmailMTAHost" value="<?php echo $host_name; ?>" /><small><i>Leave blank</i></small></td>
</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/mail.png" /></td>
	<td class="heading">Email domain:</td>
	<td><input type="text" name="sendmailMTAClassValue" id="sendmailMTAClassValue" value="" /></td>
</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['container']; ?></td>
	<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
		<?php draw_chooser_link( 'sendmail_domain_form.container' ); ?>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed" /></center></td>
</tr>
</table>
</center>
</form>

<?php } elseif( $step == 2 ) {

	/* Critical assertions */
	if( ! trim( $_REQUEST['sendmailMTACluster'] )  && ! trim($_REQUEST['sendmailMTAHost'])) {
		pla_error("Either Cluster name or Host name need to be specified");
	}
	if (!trim($_REQUEST['sendmailMTAClassValue']) ) {
		pla_error("Email domain is blank and must be specified");
	}

	?>
	<center><h3>Confirm sendmail domain creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="step" value="<?php echo $step + 1; ?>"\>
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'sendmailMTAClassValue=' . $_REQUEST['sendmailMTAClassValue'] ) . "," . $_REQUEST['container']; ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( $object_classes ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="sendmailMTACluster" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($_REQUEST['sendmailMTACluster']);?>" />

	<input type="hidden" name="attrs[]" value="sendmailMTAHost" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($_REQUEST['sendmailMTAHost']);?>" />

	<input type="hidden" name="attrs[]" value="sendmailMTAClassValue" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($_REQUEST['sendmailMTAClassValue']);?>" />

	<?php foreach( $default_attributes as $default_attr => $default_val ) { ?>
		<!-- default attribute, auto-added based on $default_attributes array specified in new_user_template.php -->
		<input type="hidden" name="attrs[]" value="<?php echo htmlspecialchars($default_attr); ?>" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($default_val);?>" />

	<?php } ?>

	<center>
	<table class="confirm">
	<tr class="even"><td class="heading">Sendmail cluster name:</td><td><b><?php echo htmlspecialchars( $_REQUEST['sendmailMTACluster'] ); ?></b></td></tr>
	<tr class="odd"><td class="heading">Sendmail host name:</td><td><b><?php echo htmlspecialchars( $_REQUEST['sendmailMTAHost'] ); ?></b></td></tr>
	<tr class="even"><td class="heading">Email domain:</td><td><b><?php echo htmlspecialchars( $_REQUEST['sendmailMTAClassValue'] ); ?></b></td></tr>
	</table>
	<br /><input type="submit" value="Create Domain" />
	</center>

<?php } ?>
