<?php 

/* 
 * add_value_form.php
 * Displays a form to allow the user to enter a new value to add
 * to the existing list of values for a multi-valued attribute.
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value 
 *  - server_id
 *
 */

require 'config.php';
require_once 'functions.php';

$dn = stripslashes( $_GET['dn'] );
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
$rdn = ldap_explode_dn( $dn, 0 );
$rdn = $rdn[0];
$server_name = $servers[$server_id]['name'];
$attr = stripslashes( $_GET['attr'] );
$encoded_attr = rawurlencode( $attr );
$current_values = get_object_attr( $server_id, $dn, $attr );
$num_current_values = ( is_array($current_values) ? count($current_values) : 1 );
$is_object_class = ( 0 == strcasecmp( $attr, 'objectClass' ) ) ? true : false;
$is_jpeg_photo = ( 0 == strcasecmp( $attr, 'jpegPhoto' ) ) ? true : false;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

if( $is_object_class ) { 
	// fetch all available objectClasses and remove those from the list that are already defined in the entry
	$schema_oclasses = get_schema_objectclasses( $server_id );
	if( ! is_array( $current_values ) )
		$current_values = array( $current_values );
	foreach( $current_values as $oclass )
		unset( $schema_oclasses[ strtolower( $oclass ) ] );
} else {
	$schema_attrs = get_schema_attributes( $server_id );
}

?>

<?php include 'header.php'; ?>

<body>

<h3 class="title">New <b><?php echo htmlspecialchars($attr); ?></b> value for <b><?php echo htmlentities($rdn); ?></b></h3>
<h3 class="subtitle">Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; Distinguished Name: <b><?php echo $dn; ?></b></h3>

Current list of <b><?php echo $num_current_values; ?></b> value<?php echo $num_current_values>1?'s':''; ?>
	for attribute <b><?php echo htmlspecialchars($attr); ?></b>:
		
<?php if( $is_jpeg_photo ) { ?>
	
	<table><td>
	<?php draw_jpeg_photos( $server_id, $dn ); ?>
	</td></table>

	<!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->	
		<p><small>
		Note: You will get an "inappropriate matching" error if you have not<br />
		setup an <tt>EQUALITY</tt> rule on your LDAP server for <tt>jpegPhoto</tt> attributes.
		</small></p>
	<!-- End of temporary warning -->
	
<?php } else { ?>

<ul class="current_values">
	<?php  if( is_array( $current_values ) ) /*$num_current_values > 1 )*/  {
		 foreach( $current_values as $val ) { ?>

			<li><nobr><?php echo htmlspecialchars(utf8_decode($val)); ?></nobr></li>

		<?php  } ?>
	<?php  } else { ?>

		<li><nobr><?php echo htmlspecialchars(utf8_decode($current_values)); ?></nobr></li>

	<?php  } ?>
</ul>

<?php } ?>

Enter the value you would like to add:<br />
<br />

<?php if( $is_object_class ) { ?>

	<form action="add_oclass_form.php" method="post" class="new_value">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<select name="new_oclass">

	<?php foreach( $schema_oclasses as $oclass => $desc ) { ?>

		<option value="<?php echo $desc['name']; ?>"><?php echo $desc['name']; ?></option>

	<?php } ?>

	</select> <input type="submit" value="Add new objectClass" />
		
	<br /><small>Note: you may be required to enter new attributes<br />
	that this objectClass requires (MUST attrs)</small>

<?php } elseif( $is_jpeg_photo ) { ?>

	<form action="add_value.php" method="post" class="new_value" enctype="multipart/form-data">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="attr" value="<?php echo $encoded_attr; ?>" />
	<input type="file" name="jpeg_photo_file" value="" /><br />
	<br />
	<input type="submit" name="submit" value="Add new jpeg &gt;&gt;" />

<?php } else { ?>

	<form action="add_value.php" method="post" class="new_value">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="attr" value="<?php echo $encoded_attr; ?>" />
	<input type="text" name="new_value" size="40" value="" />
	<input type="submit" name="submit" value="Add New Value" />
	<br />
	<small>Syntax: <?php echo $schema_attrs[ strtolower($attr) ]['type']; ?></small>
	</form>

<?php } ?>

</body>
</html>
