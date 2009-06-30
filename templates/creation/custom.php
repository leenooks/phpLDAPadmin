<?php

require 'common.php';

// Common to all templates
$rdn = isset( $_POST['rdn'] ) ? $_POST['rdn'] : null;
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = isset( $_POST['step'] ) ? $_POST['step'] : null;
if( ! $step )
	$step = 1;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

if( $step == 1 )
{
	$oclasses = get_schema_objectClasses( $server_id );
	?>

	<h4>Step 1 of 2: Name and ObjectClass(es)</h4>

	<form action="creation_template.php" method="post" name="creation_form">
	<input type="hidden" name="step" value="2" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="template" value="<?php echo $_POST['template']; ?>" />

	<table class="create">
	<tr>
		<td class="heading"><acronym title="Relative Distinguished Name">RDN</acronym>:</td>
		<td><input type="text" name="rdn" value="" size="20" /> (example: cn=MyNewObject)</td>
	</tr>
	<tr>
		<td class="heading">Container:</td>
		<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
			<?php draw_chooser_link( 'creation_form.container' ); ?></td>
	</tr>
	<tr>
		<td class="heading">ObjectClass(es):</td>
		<td>
			<select name="object_classes[]" multiple size="15">
			<?php  foreach( $oclasses as $oclass => $attrs ) { ?>
				<option value="<?php echo htmlspecialchars($oclass); ?>">
					<?php echo htmlspecialchars($attrs['name']); ?>
				</option>
				<?php  } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="Proceed >>" /></td>
	</tr>
	</table>
	</form>
	
	<?php
}
if( $step == 2 )
{
	strlen( trim( $rdn ) ) != 0 or
		pla_error( "You left the RDN field blank" );

	strlen( $container ) == 0 or dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	$friendly_attrs = process_friendly_attr_table();
	$oclasses = $_POST['object_classes'];
	if( count( $oclasses ) == 0 )
		pla_error( "You did not select any ObjectClasses for this object. Please go back and do so." );

	// build a list of required attributes:
	$dn = $rdn . ',' . $container;
	//$attrs = get_schema_attributes( $server_id );
	$schema_oclasses = get_schema_objectclasses( $server_id );
	$required_attrs = array();
	$all_attrs = array();
	foreach( $oclasses as $oclass ) {
		$required_attrs = array_merge( $required_attrs, $schema_oclasses[strtolower($oclass)]['must_attrs'] );
		$all_attrs = array_merge( $all_attrs, $schema_oclasses[strtolower($oclass)]['must_attrs'],
				$schema_oclasses[strtolower($oclass)]['may_attrs'] );
	}

	$required_attrs = array_unique( $required_attrs );
	$all_attrs = array_unique( $all_attrs );
	sort( $required_attrs );
	sort( $all_attrs );
	
	// remove binary attributes and add them to the binary_attrs array
	$binary_attrs = array();
	foreach( $all_attrs as $i => $attr_name ) {
		if( is_attr_binary( $server_id, $attr_name )  ) {
			unset( $all_attrs[ $i ] );
			$binary_attrs[] = $attr_name;
		}
	}
	
	$attr_select_html = "";
	foreach( $all_attrs as $a ) {
		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
			$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" . 
				htmlspecialchars($a) . ")";
		} else {
			$attr_display = htmlspecialchars( $a );
		}

		$attr_select_html .= "<option value=\"$a\">$attr_display</option>\n";
	}
	
	$binary_select_html = "";
	if( count( $binary_attrs ) > 0 ) {
		foreach( $binary_attrs as $a ) {
			if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
				$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" .
					htmlspecialchars( $a ) . ")";
			} else {
				$attr_display = htmlspecialchars( $a );
			}

			$binary_attr_select_html .= "<option>$attr_display</option>\n";
		}
	}

	// add the required attribute based on the RDN provided by the user
	// (ie, if the user specifies "cn=Bob" for their RDN, make sure "cn" is
       	// in the list of required attributes.
	$rdn_attr = trim( substr( $rdn, 0, strpos( $rdn, '=' ) ) );
	$rdn_value = trim( substr( $rdn, strpos( $rdn, '=' ) + 1 ) );
	if( in_array( $rdn_attr, $all_attrs ) && ! in_array( $rdn_attr, $required_attrs ) )
		$required_attrs[] = $rdn_attr;
	?>

	<h4>Step 2 of 2: Specify attributes and values</h4>
	
	<small><b>Instructions</b>: 
	Enter values for the <?php echo count($required_attrs); ?> required attributes.<br/>
	Then specify any optional attributes. <?php if( count( $binary_attrs ) > 0 ) { ?>
	Finally, you may<br />specify optional binary attributes from a file if needed. <?php } ?> 
	</small>

	<form action="create.php" method="post"  enctype="multipart/form-data">
	<input type="hidden" name="step" value="2" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( $dn ); ?>" />
	<input type="hidden" name="new_rdn" value="<?php echo htmlspecialchars( $rdn ); ?>" />
	<input type="hidden" name="container" value="<?php echo htmlspecialchars( $container ); ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="object_classes" value="<?php echo rawurlencode(serialize($oclasses)); ?>" />
	
	<table class="edit_dn" cellspacing="0">
	<tr><th colspan="2">Required Attributes</th></tr>
	<?php  if( count( $required_attrs ) == 0 ) {
			echo "<tr class=\"row1\"><td colspan=\"2\"><center>(none)</center></td></tr>\n";
		} else 
	
		foreach( $required_attrs as $count => $attr ) { ?>
		<?php  if( $count % 2 == 0 ) { ?>
			<tr class="row1">
		<?php  } else { ?>
			<tr class="row2">
		<?php  } ?>
		<td class="attr"><b><?php 
		
			// is there a user-friendly translation available for this attribute?
			if( isset( $friendly_attrs[ strtolower( $attr ) ] ) ) {
				$attr_display = "<acronym title=\"Alias for " . htmlspecialchars($attr) . "\">" . 
					htmlspecialchars( $friendly_attrs[ strtolower( $attr ) ] ) . "</acronym>";
			} else {
				$attr_display = htmlspecialchars( $attr );
			}

			echo $attr_display;
			
			?></b></td>
		<td class="val"><input 	type="<?php echo (is_attr_binary( $server_id, $attr ) ? "file" : "text"); ?>"
					name="required_attrs[<?php echo htmlspecialchars($attr); ?>]"
					value="<?php echo $attr == $rdn_attr ? $rdn_value : ''  ?>" size="40" />
	</tr>
	<?php  } ?>
	
	<tr><th colspan="2">Optional Attributes</th></tr>
	
	<?php if( count( $all_attrs ) == 0 ) { ?>
		<tr class="row1"><td colspan="2"><center>(none)</center></td></tr>
	<?php } else { ?>
		<?php  for($i=0; $i<min( count( $all_attrs ), 10 ); $i++ ) { ?>
			<?php  if( $i % 2 == 0 ) { ?>
				<tr class="row1">
			<?php  } else { ?>
				<tr class="row2">
			<?php  } ?>
			<td class="attr"><select name="attrs[<?php echo $i; ?>]"><?php echo $attr_select_html; ?></select></td>
			<td class="val"><input type="text" name="vals[<?php echo $i; ?>]" value="" size="40" />
		</tr>
		<?php } ?>
	<?php  } ?>
	
	<?php if( count( $binary_attrs ) > 0 ) { ?>
	<tr><th colspan="2">Optional Binary Attributes</th></tr>
		<?php for( $k=$i; $k<$i+count($binary_attrs); $k++ ) { $attr = $binary_attrs[$k]; ?>
		<?php  if( $i % 2 == 0 ) { ?>
			<tr class="row1">
		<?php  } else { ?>
			<tr class="row2">
		<?php  } ?>
		<td class="attr"><select name="attrs[<?php echo $k; ?>]"><?php echo $binary_attr_select_html;?></select></td>
		<td class="val"><input type="file" name="vals[<?php echo $k; ?>]" value="" size="40" />
		<?php } ?>
	<?php } ?>
	</table>
	
	<center>
		<input type="submit" name="submit" value="Create Object" />
	</center>

<?php } ?>

