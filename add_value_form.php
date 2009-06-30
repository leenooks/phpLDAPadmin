<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/add_value_form.php,v 1.23 2004/04/23 12:15:29 uugdave Exp $
 

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

require 'common.php';

$dn = isset( $_GET['dn'] ) ? $_GET['dn'] : null;
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
if( null != $dn ) {
	$rdn = get_rdn( $dn );
} else {
	$rdn = null;
}
$server_name = $servers[$server_id]['name'];
$attr = $_GET['attr'];
$encoded_attr = rawurlencode( $attr );
$current_values = get_object_attr( $server_id, $dn, $attr );
$num_current_values = ( is_array($current_values) ? count($current_values) : 1 );
$is_object_class = ( 0 == strcasecmp( $attr, 'objectClass' ) ) ? true : false;
$is_jpeg_photo = is_jpeg_photo( $server_id, $attr ); //( 0 == strcasecmp( $attr, 'jpegPhoto' ) ) ? true : false;

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );


if( $is_object_class ) { 
	// fetch all available objectClasses and remove those from the list that are already defined in the entry
	$schema_oclasses = get_schema_objectclasses( $server_id );
	foreach( $current_values as $oclass )
		unset( $schema_oclasses[ strtolower( $oclass ) ] );
} else {
	$schema_attr = get_schema_attribute( $server_id, $attr );
}

include 'header.php'; ?>

<body>

<h3 class="title">
	<?php echo $lang['add_new']; ?>
	<b><?php echo htmlspecialchars($attr); ?></b> 
	<?php echo $lang['value_to']; ?>
	<b><?php echo htmlspecialchars($rdn); ?></b></h3>
<h3 class="subtitle">
	<?php echo $lang['server']; ?>: 
	<b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; 
	<?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( $dn ); ?></b></h3>

<?php echo $lang['current_list_of']; ?> <b><?php echo $num_current_values; ?></b> 
<?php echo $lang['values_for_attribute']; ?> <b><?php echo htmlspecialchars($attr); ?></b>:
		
<?php if( $is_jpeg_photo ) { ?>
	
	<table><td>
	<?php draw_jpeg_photos( $server_id, $dn, $attr, false ); ?>
	</td></table>

	<!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->	
		<p><small>
		<?php echo $lang['inappropriate_matching_note']; ?>
		</small></p>
	<!-- End of temporary warning -->
	
<?php } else if( is_attr_binary( $server_id, $attr ) ) { ?>
	<ul>
	<?php if( is_array( $vals ) ) { for( $i=1; $i<=count($vals); $i++ ) { 
		$href = "download_binary_attr.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=$attr&amp;value_num=" . ($i-1); ?>
		<li><a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value'] . ' ' .  $i; ?>)</a></li>
	<?php } } else { 
		$href = "download_binary_attr.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=$attr"; ?>
		<li><a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a></li>
	<?php } ?>
	</ul>
	<!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->	
		<p><small>
		<?php echo $lang['inappropriate_matching_note']; ?>
		</small></p>
	<!-- End of temporary warning -->
	
<?php } else { ?>

<ul class="current_values">
	<?php  if( is_array( $current_values ) ) /*$num_current_values > 1 )*/  {
		 foreach( $current_values as $val ) { ?>

			<li><nobr><?php echo htmlspecialchars(($val)); ?></nobr></li>

		<?php  } ?>
	<?php  } else { ?>

		<li><nobr><?php echo htmlspecialchars(($current_values)); ?></nobr></li>

	<?php  } ?>
</ul>

<?php } ?>

<?php echo $lang['enter_value_to_add']; ?>
<br />
<br />

<?php if( $is_object_class ) { ?>

	<form action="add_oclass_form.php" method="post" class="new_value">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<select name="new_oclass">

	<?php foreach( $schema_oclasses as $name => $oclass ) { ?>

		<option value="<?php echo $oclass->getName(); ?>"><?php echo $oclass->getName(); ?></option>

	<?php } ?>

	</select> <input type="submit" value="<?php echo $lang['add_new_objectclass']; ?>" />
		
	<br />
	<?php if( show_hints() ) { ?>
	<small>
		<br />
		<img src="images/light.png" /><span class="hint"><?php echo $lang['new_required_attrs_note']; ?></span>
	</small>
	<?php } ?>

<?php } else { ?>

	<form action="add_value.php" method="post" class="new_value" name="new_value_form"<?php 
		if( is_attr_binary( $server_id, $attr ) ) echo "enctype=\"multipart/form-data\""; ?>>
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="attr" value="<?php echo $encoded_attr; ?>" />

	<?php if( is_attr_binary( $server_id, $attr ) ) { ?>
		<input type="file" name="new_value" />
		<input type="hidden" name="binary" value="true" />
	<?php } else { ?>
        <?php if( is_multi_line_attr( $attr, $server_id ) ) { ?>
            <textarea name="new_value" rows="3" cols="30"></textarea>
        <?php } else { ?>
		<input type="text" <?php 
				if( $schema_attr->getMaxLength() ) 
					echo "maxlength=\"" . $schema_attr->getMaxLength() . "\" "; 
				?>name="new_value" size="40" value="" /><?php 
                    // draw the "browse" button next to this input box if this attr houses DNs:
                    if( is_dn_attr( $server_id, $attr ) ) draw_chooser_link( "new_value_form.new_value", false ); ?>
        <?php } ?>
	<?php } ?>

	<input type="submit" name="submit" value="<?php echo $lang['add_new_value']; ?>" />
	<br />

	<?php if( $schema_attr->getDescription() ) { ?>
		<small><b><?php echo $lang['desc']; ?>:</b> <?php echo $schema_attr->getDescription(); ?></small><br />
	<?php } ?>

	<?php if( $schema_attr->getType() ) { ?>
		<small><b><?php echo $lang['syntax']; ?>:</b> <?php echo $schema_attr->getType(); ?></small><br />
	<?php } ?>

	<?php if( $schema_attr->getMaxLength() ) { ?>
		<small><b><?php echo $lang['maximum_length']; ?>:</b> <?php echo number_format( $schema_attr->getMaxLength() ); ?>	<?php echo $lang['characters']; ?></small><br />
	<?php } ?>

	</form>

<?php } ?>

</body>
</html>
