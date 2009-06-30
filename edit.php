<?php 

/*
 * edit.php
 * Displays the specified dn from the specified server for editing
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 */

/** If an entry has more children than this, stop searching and display this amount with a '+' */
$max_children = 100;

require 'config.php';
require_once 'functions.php';

$dn = stripslashes( rawurldecode( $_GET['dn'] ) );
$encoded_dn = rawurlencode( $dn );
$updated_attr = stripslashes( $_GET['updated_attr'] );
$server_id = $_GET['server_id'];
$show_internal_attrs = isset( $_GET['show_internal_attrs'] ) ? true : false;
$rdn = ldap_explode_dn( $dn, 0 );
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
foreach( $oclasses as $oclass )
	$avail_attrs = array_merge( $schema_oclasses[ strtolower( $oclass ) ]['must_attrs'],
				    $schema_oclasses[ strtolower( $oclass ) ]['may_attrs'],
				    $avail_attrs );

$avail_attrs = array_unique( $avail_attrs );
$avail_attrs = array_filter( $avail_attrs, "not_an_attr" );
 
sort( $avail_attrs );

/* A boolean flag to indicate whether this entry has a jpegPhoto associated with it.
 * TODO If it does, the jpegPhotos will be drawn at the bottom of the form */
$has_jpeg_photo = false;

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
	       title="Refresh this entry">Refresh</a></td>
</tr>

<?php if( 0 != strcasecmp( $dn, $servers[$server_id]['base'] ) ) { ?>
<?php /* We won't allow them to delete the base dn of the server */ ?>
<tr>
	<td><img src="images/trash.png" /></td>
	<td><a href="delete_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"
	       title="You will be prompted to confirm this decision">Delete this entry</a></td>
</tr> 
<?php } ?>

<tr>
	<td><img src="images/cut.png" /></td>
	<td><a href="copy_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn?>"
	     title="Copy this object to another location, a new DN, or another server">Copy this entry</a></td>
</tr> 
<tr>
	<td><img src="images/save.png" /></td>
	<?php $ldif_url = "ldif_export.php?server_id=$server_id&amp;dn=$encoded_dn&amp;scope=base"; ?>
	<td><a href="<?php echo $ldif_url; ?>" title="Save an LDIF dump of this object">Export to LDIF</a> 
		(<a href="<?php echo $ldif_url; ?>&amp;format=mac" title="Macintosh style carriage returns">mac</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=win" title="Windows style carriage returns">win</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=unix" title="Unix style carriage returns">unix</a>)
	</td>
</tr>
<tr>
	<td><img src="images/star.png" /></td>
	<td><a href="<?php echo "create_form.php?server_id=$server_id&amp;container=$encoded_dn"; ?>">Create a child entry</a></td>
</tr>

<?php flush(); ?>
<?php $children = get_container_contents( $server_id, $dn, $max_children ); 

if( ($children_count = count( $children ) ) > 0 ) { 
	if( $children_count == $max_children )
		$children_count = $children_count . '+';

?>

<tr>
	<td><img src="images/children.png" /></td>
	<td><a href="search.php?search=true&amp;server_id=<?php echo $server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo $encoded_dn; ?>&amp;form=advanced&amp;scope=one">View <?php echo $children_count; ?> <?php echo ($children_count==1?'child':'children');?></a></td>
</tr>

<?php } ?>

<?php if( $children_count > 0 ) { ?>

<tr>
	<td><img src="images/save.png" /></td>
	<?php $ldif_url = "ldif_export.php?server_id=$server_id&amp;dn=$encoded_dn&amp;scope=sub"; ?>
	<td><a href="<?php echo $ldif_url; ?>" title="Save an LDIF dump of this object and all of its children">Export subtree to LDIF</a> 
		(<a href="<?php echo $ldif_url; ?>&amp;format=mac" title="Macintosh style carriage returns">mac</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=win" title="Windows style carriage returns">win</a>)
		(<a href="<?php echo $ldif_url; ?>&amp;format=unix" title="Unix style carriage returns">unix</a>)
	</td>
</tr>
<?php } ?>

<?php if( in_array( 'jpegPhoto', $avail_attrs ) ) { ?>

<?php $new_jpeg_href = "new_jpeg_photo_form.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=jpegPhoto"; ?>
<tr>
	<td><img src="images/photo.png" /></td>
	<td><a href="<?php echo $new_jpeg_href; ?>">Add a jpegPhoto</a></td>
</tr>

<?php } ?>
</table>
<br />

<table class="edit_dn" cellspacing="0">


<!-- Form to rename this entry -->
<tr class="row1">
<td class="heading"><acronym title="Change this entry's RDN">Rename Entry</acronym></td>
<td class="heading" align="right">
	<nobr>
	<form action="rename.php" method="post" class="edit_dn" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="text" name="new_rdn" size="40" value="<?php echo htmlspecialchars( utf8_decode( $rdn ) ); ?>" />
	<input class="update_dn" type="submit" value="Rename" />
	</form>
	</nobr>
</td>

<tr class="spacer"><td colspan="2"></td></tr>

<form action="new_attr.php" method="post">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />

<!-- Form to add a new attribute to this entry -->
<tr class="row1">
<td class="heading">
	<nobr>
		<acronym title="Add a new attribute/value to this entry">Add New Attribute</acronym>
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
	<input type="submit" name="submit" value="Add" class="update_dn" />
	
<?php } else { ?>
	
	<small>(no new attributes available for this entry)</small>
	
<?php }  ?>
</nobr></td>
</form>
</tr>

<?php flush(); ?>
<tr class="spacer"><td colspan="2"></td></tr>

<tr class="row1">
<td class="heading" colspan="2">
<nobr>
<?php if( $show_internal_attrs ) { ?>

<a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"
><img src="images/minus.png" title="Hide internal attributes" /></a>
<acronym title="Attributes set automatically by the system">Internal Attriubtes</acronym>

<?php } else { ?>

<a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>&amp;show_internal_attrs=true">
<img src="images/plus.png" title="Show internal attributes" /></a>
<acronym title="Attributes set automatically by the system (click + to display)">Internal Attriubtes</acronym>
<small>(hidden)</small>

<?php } ?>

</nobr>
</td>
</tr>
<?php
if( $show_internal_attrs ) {
	$counter = 0;
	foreach( get_entry_system_attrs( $server_id, $dn ) as $attr => $val ) {
	$counter++
	?>
	<tr class="<?php echo ($counter%2==0?'row1':'row2');?>">
	<td class="attr"><b><?php echo htmlspecialchars( $attr ); ?></b></td>
	<td class="val"><?php echo htmlspecialchars( $val ); ?></td>
	</tr>
	<?php } 
	if( $counter == 0 )
		echo "<tr class=\"row2\"><td colspan=\"2\"><center>(none)</center></td></tr>\n";
}

?>

<?php flush(); ?>
<tr class="spacer"><td colspan="2"></td></tr>

<!-- Table of attributes/values to edit -->
<tr class="row1">
<td class="heading" colspan="2">
	<nobr>
		<acronym title="Edit the contents of the form below and click Save.">Modify Attributes</acronym>
	</nobr>
</td>
</tr>

<form action="update_confirm.php" method="post">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo rawurlencode($dn); ?>" />

<?php if( $edit_dn_schema_lookup ) $schema_attrs = get_schema_attributes( $server_id ); ?>

<?php $counter=0; ?>
<?php foreach( $attrs as $attr => $vals ) { 
		flush();
		if( $attr == 'dn' )
			continue;

		// is there a user-friendly translation available for this attribute?
		if( isset( $friendly_attrs[ strtolower( $attr ) ] ) ) {
			$attr_display = "<acronym title=\"Alias for $attr\">" . 
					$friendly_attrs[ strtolower( $attr ) ] . "</acronym>";
		} else {
			$attr_display = $attr;
		}

	?>

	<?php  if( $attr == $updated_attr ) { ?>
		<tr class="updated_attr">
	<?php  } else { ?>
		<?php  if( $counter++ % 2 == 0 ) { ?>
			<tr class="row2">
		<?php  } else { ?>
			<tr class="row1">
		<?php  } ?>
	<?php  } ?>

	<?php $add_href = "add_value_form.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=" . rawurlencode( $attr ); ?>

	<td class="attr">
		<b><?php echo $attr_display; ?></b><br />
		<small>(<a href="<?php echo $add_href; ?>"
			   title="Add an additional value to this attribute">add value</a>)</small>
	</td>

	<td class="val">

	<?php if( 0==strcasecmp( $attr, 'jpegPhoto' ) ) {
		
		$has_jpeg_photo = true;

		// Don't draw the delete buttons if there is more than one jpegPhoto
		// 	(phpLDAPAdmin can't handle this case yet)
		if( is_array( $vals ) )
			draw_jpeg_photos( $server_id, $dn, false );
		else
			draw_jpeg_photos( $server_id, $dn, true );
			
		// proceed to the next attribute
		continue;

	} ?>

	<?php	/*
		 * This is next IF statement is a KLUGE!! If anyone knows a better way to check for
		 * binary data that works with UTF-8 encoded strings, please help
		 */
	?>

	<?php if( 0==strcasecmp( $attr, 'networkAddress' ) ) { ?>

		<small>This attribute contains binary data,<br />
		which cannot be safely displayed<br />
		or edited in a web-browser.</small>
		</td>

		<?php continue; ?>

	<?php } ?>

	<?php /* is this a  multi-valued attribute? */ ?>
	<?php  if( is_array( $vals ) ) { ?>
		<?php  foreach( $vals as $i => $val ) { ?>

			<?php $val = utf8_decode( $val ); ?>

			<nobr>
			<!-- The old_values array will let update.php know if the entry contents changed
			     between the time the user loaded this page and saved their changes. -->
			<input type="hidden"
			       name="old_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]" 
			       value="<?php echo htmlspecialchars($val); ?>" />
				       
			<input type="text"
			       size="60"
			       name="new_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]"
			       value="<?php echo htmlspecialchars($val); ?>" /></nobr><br />
		<?php  } ?>
	<?php /* this a single-valued attribute */ ?>
	<?php  } else { ?>
		<?php  $val = $vals; ?>

		<?php $val = utf8_decode( $val ); ?>

		<nobr>
		<?php /* This series of if/elseif/else is for special cases of attributes (userPassword, boolean, etc) */ ?>
		<?php  if( 0 == strcasecmp( $attr, 'userpassword' ) ) { ?>

			<?php	/* Capture the stuff in the { } if any */
				preg_match( "/{([^}]+)}/", $val, $enc_type); $enc_type = strtolower($enc_type[1]); ?>

                        <?php  /* handle crypt types */
                                if($enc_type == "crypt") {
                                    preg_match( '/{[^}]+}\$(.)\$/', $val, $salt);
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
			       value="<?php echo htmlspecialchars($val); ?>" />

			<input size="48"
			       type="text"
			       name="new_values[userpassword]"
			       value="<?php echo htmlspecialchars($val); ?>" />
				       
			<select name="enc_type">
				<option>clear</option>
				<option<?php echo $enc_type=='crypt'?' selected':''; ?>>crypt</option>
				<option<?php echo $enc_type=='md5'?' selected':''; ?>>md5</option>
				<option<?php echo $enc_type=='md5crypt'?' selected':''; ?>>md5crypt</option>
				<option<?php echo $enc_type=='blowfish'?' selected':''; ?>>blowfish</option>
				<option<?php echo $enc_type=='sha'?' selected':''; ?>>sha</option>
			</select>

		<?php } elseif( $edit_dn_schema_lookup && 
				'Boolean' == $schema_attrs[ strtolower($attr) ]['type'] ) { ?>

			<input type="hidden"
			       name="old_values[<?php echo htmlspecialchars( $attr ); ?>]" 
			       value="<?php echo htmlspecialchars($val); ?>" />

			<select name="new_values[<?php echo htmlspecialchars( $attr ); ?>]">
				<option value="TRUE"<?php echo ($val=='TRUE' ?  ' selected' : ''); ?>>TRUE</option>
				<option value="FALSE"<?php echo ($val=='FALSE' ? ' selected' : ''); ?>>FALSE</option>
				<option value="">(none -- remove value)</option>
			</select>

		<?php  } else { ?>

			<input type="hidden"
			       name="old_values[<?php echo htmlspecialchars( $attr ); ?>]" 
			       value="<?php echo htmlspecialchars($val); ?>" />

			<input size="60"
			       type="text"
			       name="new_values[<?php echo htmlspecialchars( $attr ); ?>]"
			       value="<?php echo htmlspecialchars($val); ?>" />

		<?php  } ?>
		</nobr>

	<?php  } ?>

	</td>
	</tr>

<?php  } ?>

<tr><td colspan="2"><center><input type="submit" value="Save Changes" /></center></form></td></tr>
	
<?php 
?>


</table>

<?php /* If this entry has a jpegPhoto, we need to provide a form for it to submit when deleting it. */ ?>
<?php if( $has_jpeg_photo ) { ?>
	<script language="javascript">
	<!--
	function deleteJpegPhoto()
	{
		if( confirm( "Really delete jpegPhoto?" ) )
			document.delete_jpeg_photo_form.submit();
	}
	
	-->
	</script>
	<!-- TODO: Go to update_confirm.php instead of directly to update.php -->
	<form name="delete_jpeg_photo_form" action="update.php" method="post">
		<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
		<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
		<input type="hidden" name="update_array[jpegPhoto]" value="" />
	</form>
<?php } ?>

<?php 

function not_an_attr( $x )
{
	global $attrs;
	return ! isset( $attrs[ strtolower( $x ) ] );
}

?>
