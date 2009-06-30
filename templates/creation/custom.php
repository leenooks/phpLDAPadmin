<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/custom.php,v 1.29 2004/05/05 12:47:54 uugdave Exp $

// Common to all templates
$rdn = isset( $_POST['rdn'] ) ? $_POST['rdn'] : null;
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = isset( $_POST['step'] ) ? $_POST['step'] : 1;

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

if( $step == 1 )
{
	$oclasses = get_schema_objectClasses( $server_id );
	?>

	<h4><?php echo $lang['create_step1']; ?></h4>

	<form action="creation_template.php" method="post" name="creation_form">
	<input type="hidden" name="step" value="2" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

	<table class="create">
	<tr>
		<td class="heading"><acronym title="<?php echo $lang['relative_distinguished_name']; ?>"><?php echo $lang['rdn']; ?></acronym>:</td>
		<td><input type="text" name="rdn" value="" size="20" /> <?php echo $lang['rdn_example']; ?></td>
	</tr>
	<tr>
		<td class="heading"><?php echo $lang['container']; ?></td>
		<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
			<?php draw_chooser_link( 'creation_form.container' ); ?></td>
	</tr>
	<tr>
		<td class="heading"><?php echo $lang['objectclasses']; ?></td>
		<td>
			<select name="object_classes[]" multiple size="15">
			<?php  foreach( $oclasses as $name => $oclass ) { 
                if( 0 == strcasecmp( "top", $name ) ) continue; ?>
				<option value="<?php echo htmlspecialchars($oclass->getName()); ?>">
					<?php echo htmlspecialchars($oclass->getName()); ?>
				</option>
				<?php  } ?>
			</select>
		</td>
	</tr>

	<?php if( show_hints() ) { ?>
	<tr>
		<td></td>
		<td>
			<small>
			<img src="images/light.png" /><span class="hint"><?php echo $lang['hint_structural_oclass']; ?></span>
			</small>
			<br />
		</td>
	</tr>
	<?php } ?>

	<tr>
		<td></td>
		<td><input type="submit" value="<?php echo $lang['proceed_gt']; ?>" /></td>
	</tr>
	</table>
	</form>
	
	<?php
}
if( $step == 2 )
{
	strlen( trim( $rdn ) ) != 0 or
		pla_error( $lang['rdn_field_blank'] );

	strlen( trim( $container ) ) == 0 or dn_exists( $server_id, $container ) or
		pla_error( sprintf( $lang['container_does_not_exist'],  htmlspecialchars( $container ) ) );

	$friendly_attrs = process_friendly_attr_table();
	$oclasses = $_POST['object_classes'];
	if( count( $oclasses ) == 0 )
		pla_error( $lang['no_objectclasses_selected'] );
	$dn = $rdn . ',' . $container;

	// incrementally build up the all_attrs and required_attrs arrays
	$schema_oclasses = get_schema_objectclasses( $server_id );
	$required_attrs = array();
	$all_attrs = array();
	foreach( $oclasses as $oclass_name ) {
		$oclass = get_schema_objectclass( $server_id, $oclass_name  );
		if( $oclass ) {
			$required_attrs = array_merge( $required_attrs, 
						$oclass->getMustAttrNames( $schema_oclasses ) );
			$all_attrs = array_merge( $all_attrs, 
						$oclass->getMustAttrNames( $schema_oclasses ), 
						$oclass->getMayAttrNames( $schema_oclasses ) );
		} 
	}

	$required_attrs = array_unique( $required_attrs );
	$all_attrs = array_unique( $all_attrs );
	sort( $required_attrs );
	sort( $all_attrs );

    // if for some reason "ObjectClass" ends up in the list of
    // $all_attrs or $required_attrs, remove it! This is a fix
    // for bug 927487 
    foreach( $all_attrs as $i => $attr_name )
        if( 0 == strcasecmp( $attr_name, 'objectClass' ) ) {
            unset( $all_attrs[$i] );
            $all_attrs = array_values( $all_attrs );
            break;
        }
    foreach( $required_attrs as $i => $attr_name )
        if( 0 == strcasecmp( $attr_name, 'objectClass' ) ) {
            unset( $required_attrs[$i] );
            $required_attrs = array_values( $required_attrs );
            break;
        }
	
	// remove binary attributes and add them to the binary_attrs array
	$binary_attrs = array();
	foreach( $all_attrs as $i => $attr_name ) {
		if( is_attr_binary( $server_id, $attr_name )  ) {
			unset( $all_attrs[ $i ] );
			$binary_attrs[] = $attr_name;
		}
	}

    // If we trim any attrs out above, then we will have a gap in the index
    // sequence and will get an "undefined index" error below. This prevents
    // that from happening.
    $all_attrs = array_values( $all_attrs );
	
	// add the required attribute based on the RDN provided by the user
	// (ie, if the user specifies "cn=Bob" for their RDN, make sure "cn" is
       	// in the list of required attributes.
	$rdn_attr = trim( substr( $rdn, 0, strpos( $rdn, '=' ) ) );
	$rdn_value = trim( substr( $rdn, strpos( $rdn, '=' ) + 1 ) );
	if( in_array( $rdn_attr, $all_attrs ) && ! in_array( $rdn_attr, $required_attrs ) )
		$required_attrs[] = $rdn_attr;
	?>

	<h4><?php echo $lang['create_step2']; ?></h4>
	
	<form action="create.php" method="post"  enctype="multipart/form-data">
	<input type="hidden" name="step" value="2" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( $dn ); ?>" />
	<input type="hidden" name="new_rdn" value="<?php echo htmlspecialchars( $rdn ); ?>" />
	<input type="hidden" name="container" value="<?php echo htmlspecialchars( $container ); ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="object_classes" value="<?php echo rawurlencode(serialize($oclasses)); ?>" />
	
	<table class="edit_dn" cellspacing="0">
	<tr><th colspan="2"><?php echo $lang['required_attrs']; ?></th></tr>
	<?php  if( count( $required_attrs ) == 0 ) {
			echo "<tr class=\"row1\"><td colspan=\"2\"><center>(" . $lang['none'] . ")</center></td></tr>\n";
		} else 
	
		foreach( $required_attrs as $count => $attr ) { ?>
			<tr>
		    <td class="attr"><b><?php 
		
			// is there a user-friendly translation available for this attribute?
			if( isset( $friendly_attrs[ strtolower( $attr ) ] ) ) {
				$attr_display = "<acronym title=\"" . sprintf( $lang['alias_for'], htmlspecialchars($attr) ) . "\">" . 
					htmlspecialchars( $friendly_attrs[ strtolower( $attr ) ] ) . "</acronym>";
			} else {
				$attr_display = htmlspecialchars( $attr );
			}

			echo $attr_display;
			
			?></b></td></tr>
            <tr>
		<td class="val"><input 	type="<?php echo (is_attr_binary( $server_id, $attr ) ? "file" : "text"); ?>"
					name="required_attrs[<?php echo htmlspecialchars($attr); ?>]"
					value="<?php echo ($attr == $rdn_attr ? htmlspecialchars($rdn_value) : '')  ?>" size="40" />
	</tr>
	<?php  } ?>
	
	<tr><th colspan="2"><?php echo $lang['optional_attrs']; ?></th></tr>
	
	<?php if( count( $all_attrs ) == 0 ) { ?>
		<tr><td colspan="2"><center>(<?php echo $lang['none']; ?>)</center></td></tr>
	<?php } else { ?>
		<?php  for($i=0; $i<min( count( $all_attrs ), 10 ); $i++ ) { $attr = $all_attrs[$i] ?>
            <tr>
			<td class="attr"><select style="background-color: #ddd; font-weight: bold" name="attrs[<?php echo $i; ?>]"><?php echo get_attr_select_html( $all_attrs, $friendly_attrs, $attr ); ?></select></td>
            </tr>
            <tr>
			<td class="val"><input type="text" name="vals[<?php echo $i; ?>]" value="" size="40" />
    		</tr>
		<?php } ?>
	<?php  } ?>
	
	<?php if( count( $binary_attrs ) > 0 ) { ?>
	<tr><th colspan="2"><?php echo $lang['optional_binary_attrs']; ?></th></tr>
		<?php for( $k=$i; $k<$i+count($binary_attrs); $k++ ) { $attr = $binary_attrs[$k-$i]; ?>
		<tr><td class="attr"><select style="background-color: #ddd; font-weight: bold" name="attrs[<?php echo $k; ?>]"><?php echo get_binary_attr_select_html( $binary_attrs, $friendly_attrs, $attr );?></select></td></tr>
		<tr><td class="val"><input type="file" name="vals[<?php echo $k; ?>]" value="" size="25" /></td></tr>
		<?php } ?>
	<?php } ?>

    <tr><td>
	<center>
		<input type="submit" name="submit" value="<?php echo $lang['createf_create_object']; ?>" />
	</center>
    </td></tr>

	</table>

<?php } 


function get_attr_select_html( $all_attrs, $friendly_attrs, $highlight_attr=null )
{
	$attr_select_html = "";
    if( ! is_array( $all_attrs ) )
        return null;
	foreach( $all_attrs as $a ) {
		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
			$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" . 
				htmlspecialchars($a) . ")";
		} else {
			$attr_display = htmlspecialchars( $a );
		}
		$a = htmlspecialchars( $a );
		$attr_select_html .= "<option value=\"$a\"";
        if( 0 == strcasecmp( $highlight_attr, $a ) )
            $attr_select_html .= " selected";
        $attr_select_html .= ">$attr_display</option>\n";
	}
    return $attr_select_html;
}

function get_binary_attr_select_html( $binary_attrs, $friendly_attrs, $highlight_attr=null )
{
	$binary_attr_select_html = "";
    if( ! is_array( $binary_attrs ) )
        return null;
	if( count( $binary_attrs ) == 0 ) 
        return null;
    foreach( $binary_attrs as $a ) {
        // is there a user-friendly translation available for this attribute?
        if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
            $attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" .
                htmlspecialchars( $a ) . ")";
        } else {
            $attr_display = htmlspecialchars( $a );
        }
        $binary_attr_select_html .= "<option";
        if( 0 == strcasecmp( $highlight_attr, $a ) )
            $binary_attr_select_html .= " selected";
        $binary_attr_select_html .= ">$attr_display</option>\n";
    }
    return $binary_attr_select_html;
}

?>

