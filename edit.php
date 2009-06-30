<?php 

/*
 * edit.php
 * Displays the specified dn from the specified server for editing
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - modified_attrs (optional) an array of attributes to highlight as 
 *                              they were changed by the last operation
 */

/** If an entry has more children than this, stop searching and display this amount with a '+' */
$max_children = 100;

require 'common.php';

$dn= $_GET['dn'];
$decoded_dn = rawurldecode( $dn );
$encoded_dn = rawurlencode( $decoded_dn );
$modified_attrs = isset( $_GET['modified_attrs'] ) ? $_GET['modified_attrs'] : false;
$server_id = $_GET['server_id'];
$show_internal_attrs = isset( $_GET['show_internal_attrs'] ) ? true : false;
$rdn = pla_explode_dn( $dn );
$rdn = $rdn[0];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
pla_ldap_connect( $server_id ) or pla_error( "Coult not connect to LDAP server." );
$friendly_attrs = process_friendly_attr_table();
$attrs = get_object_attrs( $server_id, $dn );

pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );
$system_attrs = get_entry_system_attrs( $server_id, $dn );
if( ! $attrs )
	pla_error( "No such dn, " . htmlspecialchars( utf8_decode( $dn ) ) );

$server_name = $servers[$server_id]['name'];

// build a list of attributes available for this object based on its objectClasses
$oclasses = get_object_attr( $server_id, $dn, 'objectClass' );
if( ! is_array( $oclasses ) )
	$oclasses = array( $oclasses );
$avail_attrs = array();
$schema_oclasses = get_schema_objectclasses( $server_id, true );
$schema_attrs = get_schema_attributes( $server_id );
foreach( $oclasses as $oclass ) {
	$avail_attrs = array_merge(
		$schema_oclasses[ strtolower( $oclass ) ]['must_attrs'],
		$schema_oclasses[ strtolower( $oclass ) ]['may_attrs'],
		$avail_attrs );
}
$avail_attrs = array_unique( $avail_attrs );
$avail_attrs = array_filter( $avail_attrs, "not_an_attr" );
sort( $avail_attrs );

$avail_binary_attrs = array();
foreach( $avail_attrs as $i => $attr ) {
	if( is_attr_binary( $server_id, $attr ) ) {
		$avail_binary_attrs[] = $attr;
		unset( $avail_attrs[ $i ] );
	}
}

?>

<?php include 'header.php'; ?>
<body>

<h3 class="title"><?php echo htmlspecialchars( utf8_decode( $rdn ) ); ?></h3>
<h3 class="subtitle">Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; Distinguished Name: <b><?php echo htmlspecialchars( utf8_decode( $dn ) ); ?></b></h3>

<table class="edit_dn_menu">

<tr>
	<?php  $time = gettimeofday(); $random_junk = md5( strtotime( 'now' ) . $time['usec'] ); ?>
	<td><img src="images/refresh.png" /></td>
	<td><a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>&amp;random=<?php
			echo $random_junk; ?>"
	       title="<?php echo $lang['refresh_this_entry']; ?>"><?php echo $lang['refresh']; ?></a></td>
</tr>

<?php if( ! is_server_read_only( $server_id ) && 0 != strcasecmp( $dn, $servers[$server_id]['base'] ) ) { ?>
<?php /* We won't allow them to delete the base dn of the server */ ?>
<tr>
	<td><img src="images/trash.png" /></td>
	<td><a href="delete_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"
	       title="<?php echo $lang['delete_this_entry_tooltip']; ?>"><?php echo $lang['delete_this_entry']; ?></a></td>
</tr> 
<?php } ?>

<tr>
	<td><img src="images/cut.png" /></td>
	<td><a href="copy_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn?>"
	     title="<?php echo $lang['copy_this_entry_tooltip']; ?>"><?php echo $lang['copy_this_entry']; ?></a></td>
</tr> 
<tr>
	<td><img src="images/save.png" /></td>
	<?php $ldif_url = "ldif_export.php?server_id=$server_id&amp;dn=$encoded_dn&amp;scope=base"; ?>
	<td><a href="<?php echo $ldif_url; ?>" title="<?php echo $lang['export_to_ldif_tooltip']; ?>"><?php echo $lang['export_to_ldif']; ?></a> 
		(<a href="<?php echo $ldif_url; ?>&amp;format=mac" 
			title="<?php echo $lang['export_to_ldif_mac']; ?>">mac</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=win" 
			title="<?php echo $lang['export_to_ldif_win']; ?>">win</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=unix" 
			title="<?php echo $lang['export_to_ldif_unix']; ?>">unix</a>)
	</td>
</tr>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
<tr>
	<td><img src="images/star.png" /></td>
	<td><a href="<?php echo "create_form.php?server_id=$server_id&amp;container=$encoded_dn"; ?>"><?php echo $lang['create_a_child_entry']; ?></a></td>
</tr>
<?php } ?>

<?php flush(); ?>
<?php $children = get_container_contents( $server_id, $dn, $max_children ); 

if( ($children_count = count( $children ) ) > 0 ) { 
	if( $children_count == $max_children )
		$children_count = $children_count . '+';

?>

<tr>
	<td><img src="images/children.png" /></td>
	<td><a href="search.php?search=true&amp;server_id=<?php echo $server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo $encoded_dn; ?>&amp;form=advanced&amp;scope=one"><?php echo $lang['view']; ?> <?php echo $children_count; ?> <?php echo ($children_count==1?'child':'children');?></a></td>
</tr>

<?php } ?>

<?php if( $children_count > 0 ) { ?>
<tr>
	<td><img src="images/save.png" /></td>
	<?php $ldif_url = "ldif_export.php?server_id=$server_id&amp;dn=$encoded_dn&amp;scope=sub"; ?>
	<td><a href="<?php echo $ldif_url; ?>" 
	       title="<?php echo $lang['export_subtree_to_ldif_tooltip']; ?>"><?php echo $lang['export_subtree_to_ldif']; ?></a> 
		(<a href="<?php echo $ldif_url; ?>&amp;format=mac" title="<?php echo $lang['export_to_ldif_mac'];?>">mac</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=win" title="<?php echo $lang['export_to_ldif_win'];?>">win</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=unix" title="<?php echo $lang['export_to_ldif_unix'];?>">unix</a>)
	</td>
</tr>
<?php } ?>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
<tr>
	<td><img src="images/light.png" /></td>
	<td><?php echo $lang['delete_hint']; ?></td>
</tr>
<?php } ?>

<?php if( is_server_read_only( $server_id ) ) { ?>
<tr>
	<td><img src="images/light.png" /></td>
	<td><?php echo $lang['viewing_read_only']; ?></td>
</tr>
<?php } ?>

</table>
<br />

<table class="edit_dn" cellspacing="0">

<?php if( ! is_server_read_only( $server_id ) ) { ?>
	<!-- Form to rename this entry -->
	<tr class="row1">
	<td class="heading"><acronym title="<?php echo $lang['change_entry_rdn']; ?> "><?php echo $lang['rename_entry']; ?></acronym></td>
	<td class="heading" align="right">
	<nobr>
	<form action="rename.php" method="post" class="edit_dn" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="text" name="new_rdn" size="30" value="<?php echo htmlspecialchars( utf8_decode( $rdn ) ); ?>" />
	<input class="update_dn" type="submit" value="<?php echo $lang['rename']; ?>" />
	</form>
	</nobr>
	</td>
<?php } ?>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
	<!-- Form to add a new attribute to this entry -->
	<tr class="spacer"><td colspan="2"></td></tr>
	<form action="new_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<tr class="row1">
	<td class="heading">
		<nobr>
		<acronym title="<?php echo $lang['add_new_attribute_tooltip']; ?>"><?php echo $lang['add_new_attribute']; ?></acronym>
		</nobr>
	</td>
	<td class="heading" align="right"><nobr>

	<?php if( is_array( $avail_attrs ) && count( $avail_attrs ) > 0 ) { ?>

	<select name="attr">
	<?php  foreach( $avail_attrs as $a ) { 
		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
			$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" . 
			htmlspecialchars($a) . ")";
		} else {
			$attr_display = htmlspecialchars( $a );
		}

		echo $attr_display;
		$attr_select_html .= "<option>$attr_display</option>\n";
		echo "<option value=\"" . htmlspecialchars($a) . "\">$attr_display</option>";
	} ?>
	</select>
	<input type="text" name="val" size="20" />
	<input type="submit" name="submit" value="<?php echo $lang['add']; ?>" class="update_dn" />
	
	<?php } else { ?>
	
		<small>(<?php echo $lang['no_new_attrs_available']; ?>)</small>
	
	<?php } ?>
</nobr></td>
</form>
</tr>
<?php } ?>

<?php flush(); ?>

<?php if( ! is_server_read_only( $server_id ) && count( $avail_binary_attrs ) > 0 ) { ?>
	<!-- Form to add a new BINARY attribute to this entry -->
	<tr class="spacer"><td colspan="2"></td></tr>
	<form action="new_attr.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="binary" value="true" />
	<tr class="row1">
	<td class="heading">
		<nobr>
		<acronym title="<?php echo $lang['add_new_binary_attr_tooltip']; ?>">
			<?php echo $lang['add_new_binary_attr']; ?></acronym>
		</nobr>
	</td>
	<td class="heading" align="right"><nobr>

	<select name="attr">
	<?php  foreach( $avail_binary_attrs as $a ) { 
		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $a ) ] ) ) {
			$attr_display = htmlspecialchars( $friendly_attrs[ strtolower( $a ) ] ) . " (" . 
			htmlspecialchars($a) . ")";
		} else {
			$attr_display = htmlspecialchars( $a );
		}

		echo $attr_display;
		$attr_select_html .= "<option>$attr_display</option>\n";
		echo "<option value=\"" . htmlspecialchars($a) . "\">$attr_display</option>";
	} ?>
	</select>
	<input type="file" name="val" size="20" />
	<input type="submit" name="submit" value="<?php echo $lang['add']; ?>" class="update_dn" />
	
</nobr></td>
</form>
</tr>
<?php } ?>

<tr class="spacer"><td colspan="2"></td></tr>
<tr class="row1">
<td class="heading" colspan="2">
<nobr>
<?php if( $show_internal_attrs ) { ?>

<a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"
><img src="images/minus.png" title="<?php echo $lang['hide_internal_attrs']; ?>" /></a>
<acronym title="<?php echo $lang['internal_attrs_tooltip'];?>"><?php echo $lang['internal_attributes']; ?></acronym>

<?php } else { ?>

<a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>&amp;show_internal_attrs=true">
<img src="images/plus.png" title="<?php echo $lang['show_internal_attrs']; ?>" /></a>
<acronym title="<?php echo $lang['internal_attrs_tooltip']; ?> (<?php echo $lang['click_to_display']; ?>)"><?php echo $lang['internal_attributes']; ?></acronym>
<small>(<?php echo $lang['hidden']; ?>)</small>

<?php } ?>

</nobr>
</td>
</tr>
<?php
if( $show_internal_attrs ) {
	$counter = 0;
	foreach( get_entry_system_attrs( $server_id, $dn ) as $attr => $vals ) {
		$counter++
		?>
		<tr class="<?php echo ($counter%2==0?'row1':'row2');?>">
		<td class="attr"><b><?php echo htmlspecialchars( $attr ); ?></b></td>
		<td class="val">
		<?php foreach( $vals as $v ) {?>
			<?php echo htmlspecialchars( $v ); ?><br />
		<?php } ?>
		</td>
		</tr>
	<?php } 
	if( $counter == 0 )
		echo "<tr class=\"row2\"><td colspan=\"2\"><center>(" . $lang['none'] . ")</center></td></tr>\n";
}

?>

<?php flush(); ?>
<tr class="spacer"><td colspan="2"></td></tr>

<!-- Table of attributes/values to edit -->
<tr class="row1">
<td class="heading" colspan="2">
	<nobr><?php echo $lang['entry_attributes']; ?></nobr>
</td>
</tr>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
	<form action="update_confirm.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
<?php } ?>

<?php $counter=0; ?>

<?php 	/* Prepare the hidden_attrs array by lower-casing it. */
	if( isset( $hidden_attrs ) && is_array( $hidden_attrs ) && count( $hidden_attrs ) > 0 )
		foreach( $hidden_attrs as $i => $attr_name )
			$hidden_attrs[$i] = strtolower( $attr_name );
	else
		$hidden_attrs = array();
?>

<?php foreach( $attrs as $attr => $vals ) { 

	if( isset( $schema_attrs[ strtolower($attr) ] ) )
		$attr_syntax = $schema_attrs[ strtolower( $attr ) ]->getSyntaxOID();
	flush();
	if( 0 == strcasecmp( $attr, 'dn' ) )
		continue;

	// has the config.php specified that this attribute is to be hidden?
	if( in_array( strtolower( $attr ), $hidden_attrs ) )
		continue;

	// is there a user-friendly translation available for this attribute?
	if( isset( $friendly_attrs[ strtolower( $attr ) ] ) ) {
		$attr_display = "<acronym title=\"" . $lang['alias_for'] . "$attr\">" . 
				$friendly_attrs[ strtolower( $attr ) ] . "</acronym>";
	} else {
		$attr_display = $attr;
	}

	?>

	<?php  if( is_array( $modified_attrs ) && in_array( $attr, $modified_attrs ) ) { ?>
		<tr class="updated_attr">
	<?php  } else { ?>
		<?php  if( $counter++ % 2 == 0 ) { ?>
			<tr class="row2">
		<?php  } else { ?>
			<tr class="row1">
		<?php  } ?>
	<?php  } ?>

	<?php 
	if( ! is_server_read_only( $server_id ) ) { 
	$add_href = "add_value_form.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=" . rawurlencode( $attr ); 
	} ?>

	<td class="attr">
		<b><?php echo $attr_display; ?></b><br />

		<?php if( ! is_server_read_only( $server_id ) ) { ?>
			<small>(<a href="<?php echo $add_href; ?>"
			   title="<?php echo $lang['add_value_tooltip']; ?>"><?php echo $lang['add_value']; ?></a>)</small>
		<?php } ?>
	</td>

	<td class="val">

	<?php 
	
	/*
	 * Is this attribute a jpegPhoto? 
	 */
	if( is_jpeg_photo( $server_id, $attr ) ) {
		
		// Don't draw the delete buttons if there is more than one jpegPhoto
		// 	(phpLDAPadmin can't handle this case yet)
		if( is_server_read_only( $server_id ) )
			draw_jpeg_photos( $server_id, $dn, false );
		else
			draw_jpeg_photos( $server_id, $dn, true );
			
		// proceed to the next attribute
		continue;
	} 


	/*
	 * Is this attribute binary?
	 */
	if( is_attr_binary( $server_id, $attr ) ) {
		$href = "download_binary_attr.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=$attr";
		?>

		<small>
		<?php echo $lang['binary_value']; ?><br />
		<?php if( count( $vals ) > 1 ) { for( $i=1; $i<=count($vals); $i++ ) { ?>
			<a href="<?php echo $href . "&amp;value_num=$i"; ?>"><img 
				src="images/save.png" /> <?php echo $lang['download_value']; ?>(<?php echo $i; ?>)</a><br />
		<?php } } else { ?>
			<a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a><br />
		<?php } ?>

		<?php if( ! is_server_read_only( $server_id ) ) { ?>
		<a href="javascript:deleteAttribute( '<?php echo $attr; ?>' );"
			style="color:red;"><img src="images/trash.png" /> <?php echo $lang['delete_attribute']; ?></a>
		<?php } ?>

		</small>
		</td>

		<?php continue; 
	}

	/*
	 * Note: at this point, the attribute must be text-based (not binary or jpeg)
	 */

	/*
	 * If we are in read-only mode, simply draw the attribute values and continue.
	 */
	if( is_server_read_only( $server_id ) ) {
		if( is_array( $vals ) ) { 
			foreach( $vals as $i => $val ) {
				$val = utf8_decode( $val );
				echo $val . "<br />";
			}
		} else {
			echo utf8_decode( $vals ) . "<br />";
		}
		continue;
	}
	
	/*
	 * Is this a userPassword attribute?
	 */
	if( 0 == strcasecmp( $attr, 'userpassword' ) ) { 
		$user_password = $vals[0];
		
		/* Capture the stuff in the { } to determine if this is crypt, md5, etc. */
		preg_match( "/{([^}]+)}/", $user_password, $enc_type); 
		$enc_type = strtolower($enc_type[1]); 

		// Set the default hashing type if the password is blank (must be newly created)
		if( $val == '' ) {
			$enc_type = $servers[$server_id]['default_hash'];
		} ?>

		<?php  /* handle crypt types */
		if($enc_type == "crypt") {
			preg_match( "/{[^}]+}\\$(.)\\$/", $user_password, $salt);
			switch( $salt[1] ) {
				case '':   // CRYPT_STD_DES
					$enc_type = "crypt";
					break;
				case '1':   // CRYPT_MD5
					$enc_type = "md5crypt";
					break;
				case '2':   // CRYPT_BLOWFISH
					$enc_type = "blowfish";
					break;
				default:
					$enc_type = "crypt";
			}
		} ?>

		<input type="hidden"
		       name="old_values[userpassword]" 
		       value="<?php echo htmlspecialchars($user_password); ?>" />

		<!-- Special case of enc_type to detect changes when user changes enc_type but not the password value -->
		<input size="38"
		       type="hidden"
		       name="old_enc_type"
		       value="<?php echo ($enc_type==''?'clear':$enc_type); ?>" />

		<input size="38"
		       type="text"
		       name="new_values[userpassword]"
		       value="<?php echo htmlspecialchars($user_password); ?>" />

		<select name="enc_type">
			<option>clear</option>
			<option<?php echo $enc_type=='crypt'?' selected':''; ?>>crypt</option>
			<option<?php echo $enc_type=='md5'?' selected':''; ?>>md5</option>
			<option<?php echo $enc_type=='md5crypt'?' selected':''; ?>>md5crypt</option>
			<option<?php echo $enc_type=='blowfish'?' selected':''; ?>>blowfish</option>
			<option<?php echo $enc_type=='sha'?' selected':''; ?>>sha</option>
			</select>

		<?php continue; 
	} 
	
	/*
	 * Is this a boolean attribute? 
	 */
	if( 0 == strcasecmp( 'boolean', $schema_attrs[ strtolower($attr) ]->getType() ) ) { 
		$val = $vals[0];
		?>

		<input type="hidden"
		       name="old_values[<?php echo htmlspecialchars( $attr ); ?>]" 
		       value="<?php echo htmlspecialchars($val); ?>" />

			<select name="new_values[<?php echo htmlspecialchars( $attr ); ?>]">
			<option value="TRUE"<?php echo ($val=='TRUE' ?  ' selected' : ''); ?>>
				<?php echo $lang['true']; ?></option>
			<option value="FALSE"<?php echo ($val=='FALSE' ? ' selected' : ''); ?>>
				<?php echo $lang['false']; ?></option>
			<option value="">(<?php echo $lang['none_remove_value']; ?>)</option>
		</select>

		<?php 
		continue; 
	}

	/*
	 * End of special case attributes.
	 */

	/*
	 * This is a normal attribute, to be displayed and edited in plain text.
	 */
	foreach( $vals as $i => $val ) {
		$val = utf8_decode( $val ); ?>

		<nobr>
		<!-- The old_values array will let update.php know if the entry contents changed
		     between the time the user loaded this page and saved their changes. -->
		<input type="hidden"
		       name="old_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]" 
		       value="<?php echo htmlspecialchars($val); ?>" />
			       
		<?php if( $attr_syntax == '1.3.6.1.4.1.1466.115.121.1.40' ) { ?>
			<textarea
		       	cols="37" rows="3"
		       	name="new_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]"
			><?php echo htmlspecialchars($val); ?></textarea><br />
		<?php } else { ?>
			<input type="text"
		       	size="50"
		       	name="new_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]"
		       	value="<?php echo htmlspecialchars($val); ?>" /></nobr><br />
	       <?php } ?>
	<?php  } /* end foreach value */ ?>

	</td>
	</tr>

<?php  } /* End foreach( $attrs as $attr => $vals ) */ ?>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
	<tr><td colspan="2"><center><input type="submit" value="<?php echo $lang['save_changes']; ?>" /></center></form></td></tr>
<?php } ?>
	
<?php 
?>


</table>

<?php /* If this entry has a binary attribute, we need to provide a form for it to submit when deleting it. */ ?>
<script language="javascript">
//<!--
function deleteAttribute( attrName )
{
	if( confirm( "<?php echo $lang['really_delete_attribute']; ?> '" + attrName + "'?" ) ) {
		document.delete_attribute_form.attr.value = attrName;
		document.delete_attribute_form.submit();
	}
}
//-->
</script>

<!-- This form is submitted by JavaScript when the user clicks "Delete attribute" on a binary attribute -->
<form name="delete_attribute_form" action="delete_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
</form>

<?php 

/**
 * Given an attribute $x, this returns true if it is NOT already specified
 * in the current entry, returns false otherwise.
 */
function not_an_attr( $x )
{
	global $attrs;
	//return ! isset( $attrs[ strtolower( $x ) ] );
	foreach( $attrs as $attr => $values )
		if( 0 == strcasecmp( $attr, $x ) )
			return false;
	return true;
}

?>
