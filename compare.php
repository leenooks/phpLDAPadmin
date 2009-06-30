<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/compare.php,v 1.10 2005/09/25 16:11:44 wurley Exp $

/**
 * Compare two DNs - the destination DN is editable.
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$dn_src = isset($_POST['dn_src']) ? $_POST['dn_src'] : null;
$dn_dst = isset($_POST['dn_dst']) ? $_POST['dn_dst'] : null;

$encoded_dn_src = rawurlencode($dn_src);
$encoded_dn_dst = rawurlencode($dn_dst);

$server_id_src = (isset($_POST['server_id_src']) ? $_POST['server_id_src'] : '');
$server_id_dst = (isset($_POST['server_id_dst']) ? $_POST['server_id_dst'] : '');

$ldapserver_src = $ldapservers->Instance($server_id_src);
if ( ! $ldapserver_src->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

$ldapserver_dst = $ldapservers->Instance($server_id_dst);
if ( ! $ldapserver_src->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

dn_exists($ldapserver_src,$dn_src) or pla_error(sprintf($lang['no_such_entry'],pretty_print_dn($dn_src)));
dn_exists($ldapserver_dst,$dn_dst) or pla_error(sprintf($lang['no_such_entry'],pretty_print_dn($dn_dst)));

$friendly_attrs = process_friendly_attr_table();

$attrs_src = get_object_attrs($ldapserver_src,$dn_src,false,$config->GetValue('deref','view'));
$attrs_dst = get_object_attrs($ldapserver_dst,$dn_dst,false,$config->GetValue('deref','view'));

# Get a list of all attributes.
$attrs_all = array_keys($attrs_src);
foreach ($attrs_dst as $key => $val)
	if (! in_array($key,$attrs_all))
		$attrs_all[] = $key;

include './header.php';
?>

<body>

<table class="comp_dn" border=0>
	<tr><td colspan=6>
		<h3 class="title"><?php echo $lang['comparing']; ?></h3>
	</td></tr>

	<tr>
		<td colspan=2 width=20%>
			<h3 class="subtitle"><?php echo $lang['attribute']; ?><br />&nbsp;</h3>
		</td>
		<td colspan=2 width=40%>
			<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver_src->name; ?></b><br /><?php echo $lang['distinguished_name'];?>: <b><?php echo htmlspecialchars(($dn_src)); ?></b></h3>
		</td>
		<td colspan=2 width=40%>
			<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver_dst->name; ?></b><br /><?php echo $lang['distinguished_name'];?>: <b><?php echo htmlspecialchars(($dn_dst)); ?></b></h3>
		</td>
	</tr>
	<tr>
		<td colspan=6 align=right>
			<form action="compare.php" method="post" name="compare_form">
			<input type="hidden" name="server_id_src" value="<?php echo $ldapserver_dst->server_id; ?>" />
			<input type="hidden" name="server_id_dst" value="<?php echo $ldapserver_src->server_id; ?>" />
			<input type="hidden" name="dn_src" value="<?php echo htmlspecialchars( $dn_dst ); ?>" />
			<input type="hidden" name="dn_dst" value="<?php echo htmlspecialchars( $dn_src ); ?>" />
			<input type="submit" value="<?php echo $lang['switch_entry']; ?>" />
			</form>
		</td>
	</tr>

<?php
if(! $attrs_all || ! is_array($attrs_all)) {
	printf('<tr><td colspan="2">(%s)</td></tr>',$lang['no_attributes']);
	print '</table>';
	print '</html>';
	die();
}

sort($attrs_all);

# Work through each of the attributes.
foreach ($attrs_all as $attr) {
	flush();

	# If this is the DN, get the next attribute.
	if (! strcasecmp($attr,'dn'))
		continue;

	# Has the config.php specified that this attribute is to be hidden or shown?
	if (is_attr_hidden($ldapserver_src,$attr) || is_attr_hidden($ldapserver_dst,$attr))
		continue;
?>

	<!-- Begin Attribute -->
	<tr>

	<?php foreach (array('src','dst') as $side) { ?>

		<?php
		if ($side == 'dst' && ! $ldapserver_dst->isReadOnly()) { ?>

	<form action="update_confirm.php" method="post" name="edit_form">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver_dst->server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn_dst; ?>" />

		<?php }

		$schema_attr_src = get_schema_attribute($ldapserver_src,$attr,$dn_src);
		$schema_attr_dst = get_schema_attribute($ldapserver_dst,$attr,$dn_dst);

		# Setup the $attr_note, which will be displayed to the right of the attr name (if any)
		$attr_note = '';
		$required_note = '';

		# is there a user-friendly translation available for this attribute?
		if (isset($friendly_attrs[strtolower($attr)])) {
			$attr_display = $friendly_attrs[strtolower($attr)];
			$attr_note = sprintf('<acronym title="%s">alias</acronym>',sprintf($lang['alias_for'],$attr_display,$attr));

		} else {
			$attr_note = '';
			$attr_display = $attr;
		}

		# is this attribute required by an objectClass?
		$required_by = '';
		switch ($side) {
			case 'src':
				$ldapserver = $ldapserver_src;
				if ($schema_attr_src)
					foreach ($schema_attr_src->getRequiredByObjectClasses() as $required)
						if (isset($attrs_src['objectClass']) && in_array(strtolower($required),arrayLower($attrs_src['objectClass'])))
							$required_by .= $required . ' ';

				# It seems that some LDAP servers (Domino) returns attributes in lower case?
				elseif( isset($attrs_src['objectclass']) && in_array(strtolower($required),arrayLower($attrs_src['objectclass'])))
					$required_by .= $required . ' ';

				break;

			case 'dst':
				$ldapserver = $ldapserver_dst;
				if ($schema_attr_dst)
					foreach ($schema_attr_dst->getRequiredByObjectClasses() as $required)
						if (isset($attrs_dst['objectClass']) && in_array(strtolower($required),arrayLower($attrs_dst['objectClass'])))
							$required_by .= $required . ' ';

				# It seems that some LDAP servers (Domino) returns attributes in lower case?
				elseif (isset($attrs_dst['objectclass']) && in_array(strtolower($required),arrayLower($attrs_dst['objectclass'])))
					$required_by .= $required . ' ';
				break;
		}

		if ($side == 'src') { ?>
			<td class="attr">
				<?php $schema_href="schema.php?server_id=$server_id_src&amp;view=attributes&amp;viewvalue=".real_attr_name($attr); ?>
				<b><a title="<?php echo sprintf($lang['attr_name_tooltip'],$attr) ?>" href="<?php echo $schema_href; ?>"><?php echo $attr_display; ?></a></b>
			</td>

			<td class="attr_note">
				<sup><small><?php echo $attr_note; ?></small></sup>
			</td>
		<?php }

		if ($required_by) {
			$required_note .= sprintf('<acronym title="%s">%s</acronym>',sprintf($lang['required_for'],$required_by),$lang['required']);
		?>
			<td colspan=2 class="attr_note">
				<sup><small><?php echo $required_note; ?></small></sup>
			</td>
		<?php } else { ?>
			<td colspan=2 class="attr_note">&nbsp;</td>
		<?php } ?>

		<?php if (is_attr_read_only($ldapserver,$attr)) { ?>
				<small>(<acronym title="<?php echo $lang['read_only_tooltip']; ?>"><?php echo $lang['read_only']; ?></acronym>)</small>
		<?php } ?>
			</td>

	<?php } ?>

	</tr>
	<!-- End of Attribute -->

	<!-- Begin Values -->
	<tr>

	<?php
	foreach (array('src','dst') as $side) {
		$vals = null; ?>


		<?php
		# If this attribute isnt set, then show a blank.
		$toJump = 0;
		switch ($side) {
			case 'src':
				print '<td colspan=2>&nbsp</td><td class="attr">';

				if (! isset($attrs_src[$attr])) {
					echo "<small>&lt;". $lang['no_value']."&gt;</small></td>";
					$toJump = 1;
					continue;
				} else
					$vals = $attrs_src[$attr];

				$ldapserver = $ldapserver_src;
				break;

			case 'dst':
				print '<td colspan=2>&nbsp</td><td class="val">';

				if (! isset($attrs_dst[$attr])) {
					echo "<small>&lt;". $lang['no_value']."&gt;</small></td>";
					$toJump = 1;
					continue;
				} else
					$vals = $attrs_dst[$attr];

				$ldapserver = $ldapserver_dst;
				break;
		}

		if ($toJump) continue;

		/*
		 * Is this attribute a jpegPhoto?
		 */
		if (is_jpeg_photo($ldapserver,$attr)) {

			switch ($side) {
				case 'src':
					// Don't draw the delete buttons if there is more than one jpegPhoto
					// 	(phpLDAPadmin can't handle this case yet)
					draw_jpeg_photos( $ldapserver, $dn_src, $attr, false );
					break;

				case 'dst':
					if( $ldapserver_dst->isReadOnly() || is_attr_read_only( $ldapserver_dst, $attr ) )
						draw_jpeg_photos( $ldapserver, $dn_dst, $attr, false );
					else
						draw_jpeg_photos( $ldapserver, $dn_dst, $attr, true );

					break;
			}

			// proceed to the next attribute
			echo "</td>\n";
			continue;
		}

		/*
		 * Is this attribute binary?
		 */
		if( is_attr_binary( $ldapserver, $attr ) ) {
			switch ($side) {
				case 'src':
					$href = sprintf("download_binary_attr.php?server_id=%s&dn=%s&attr=%s",$ldapserver->server_id,$encoded_dn_src,$attr);
					break;

				case 'dst':
					$href = sprintf("download_binary_attr.php?server_id=%s&dn=%s&attr=%s",$ldapserver->server_id,$encoded_dn_dst,$attr);
					break;
			}
			?>

			<small>

			<?php echo $lang['binary_value']; ?><br />

			<?php if( count( $vals ) > 1 ) { for( $i=1; $i<=count($vals); $i++ ) { ?>
				<a href="<?php echo $href . "&amp;value_num=$i"; ?>"><img
					src="images/save.png" /> <?php echo $lang['download_value']; ?>(<?php echo $i; ?>)</a><br />

			<?php } } else { ?>
				<a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a><br />

			<?php }

			if( $side == 'dst' && ! $ldapserver_dst->isReadOnly() && ! is_attr_read_only( $ldapserver, $attr ) ) { ?>

				<a href="javascript:deleteAttribute( '<?php echo $attr; ?>' );" style="color:red;"><img src="images/trash.png" /> <?php echo $lang['delete_attribute']; ?></a>

			<?php } ?>

			</small>
		</td>

			<?php continue;
		}

		/*
		 * Note: at this point, the attribute must be text-based (not binary or jpeg)
		 */

		/*
		 * If this server is in read-only mode or this attribute is configured as read_only,
		 * simply draw the attribute values and continue.
		 */

		if( $side == 'dst' && ($ldapserver->isReadOnly() || is_attr_read_only( $ldapserver, $attr )) ) {
			if( is_array( $vals ) ) {
				foreach( $vals as $i => $val ) {
					if( trim( $val ) == "" )
						echo "<span style=\"color:red\">[" . $lang['empty'] . "]</span><br />\n";

					elseif( 0 == strcasecmp( $attr, 'userPassword' ) && $config->GetValue('appearance','obfuscate_password_display'))
						echo preg_replace( '/./', '*', $val ) . "<br />";

					else
						echo htmlspecialchars( $val ) . "<br />";
				}

			} else {
				if( 0 == strcasecmp( $attr, 'userPassword' ) && $config->GetValue('appearance','obfuscate_password_display'))
					echo preg_replace( '/./', '*', $vals ) . "<br />";
				else
					echo $vals . "<br />";
			}
			echo "</td>";
			continue;
		}

		/*
		 * Is this a userPassword attribute?
		 */
		if( ! strcasecmp( $attr, 'userpassword' ) ) {
			$user_password = $vals[0];
			$enc_type = get_enc_type( $user_password );

			// Set the default hashing type if the password is blank (must be newly created)
			if( $user_password == '' ) {
				$enc_type = get_default_hash( $server_id );
			}

			if ($side == 'dst') { ?>

				<input type="hidden" name="old_values[userpassword]" value="<?php echo htmlspecialchars($user_password); ?>" />

				<!-- Special case of enc_type to detect changes when user changes enc_type but not the password value -->
				<input size="38" type="hidden" name="old_enc_type" value="<?php echo ($enc_type==''?'clear':$enc_type); ?>" />

			<?php }

			if( $config->GetValue('appearance','obfuscate_password_display') || is_null( $enc_type ) )  {
				echo htmlspecialchars( preg_replace( "/./", "*", $user_password ) );
			} else {
				echo htmlspecialchars( $user_password );
			} ?>

			<br />

			<?php if ($side == 'dst') { ?>

				<input style="width: 260px" type="password" name="new_values[userpassword]" value="<?php echo htmlspecialchars( $user_password ); ?>" />

				<?php echo enc_type_select_list($enc_type);

			} ?>

				<br />

				<script language="javascript">
				<!--
				function passwordComparePopup()
				{
					mywindow = open( 'password_checker.php', 'myname', 'resizable=no,width=450,height=200,scrollbars=1' );
					mywindow.location.href = 'password_checker.php?hash=<?php echo base64_encode($user_password); ?>&base64=true';
					if( mywindow.opener == null )
					  mywindow.opener = self;
				}
				-->
				</script>

				<small><a href="javascript:passwordComparePopup()"><?php echo $lang['t_check_pass']; ?></a></small>

			</td>

			<?php continue;
		}

		/*
		 * Is this a boolean attribute?
		 */
		if( is_attr_boolean( $ldapserver, $attr) ) {
			$val = $vals[0];

			if ($side = 'dst') {?>

				<input type="hidden" name="old_values[<?php echo htmlspecialchars( $attr ); ?>]" value="<?php echo htmlspecialchars($val); ?>" />

				<select name="new_values[<?php echo htmlspecialchars( $attr ); ?>]">
					<option value="TRUE"<?php echo ($val=='TRUE' ? ' selected' : ''); ?>><?php echo $lang['true']; ?></option>
					<option value="FALSE"<?php echo ($val=='FALSE' ? ' selected' : ''); ?>><?php echo $lang['false']; ?></option>
					<option value="">(<?php echo $lang['none_remove_value']; ?>)</option>
				</select>

			<?php } ?>

			</td>

			<?php continue;
		}

		/*
		 * End of special case attributes (non plain text).
		 */

		foreach( $vals as $i => $val ) {

			if ($side == 'dst') {
				$input_name = "new_values[" . htmlspecialchars( $attr ) . "][$i]";
				// We smack an id="..." tag in here that doesn't have [][] in it to allow the
				// draw_chooser_link() to identify it after the user clicks.
				$input_id = "new_values_" . htmlspecialchars($attr) . "_" . $i; ?>

				<!-- The old_values array will let update.php know if the entry contents changed
				     between the time the user loaded this page and saved their changes. -->
				<input type="hidden" name="old_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($val); ?>" />
			<?php }

			// Is this value is a structural objectClass, make it read-only
			if( 0 == strcasecmp( $attr, 'objectClass' ) ) { ?>

				<a title="<?php echo $lang['view_schema_for_oclass']; ?>" href="schema.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;view=objectClasses&amp;viewvalue=<?php echo htmlspecialchars( $val ); ?>"><img src="images/info.png" /></a>

				<?php $schema_object = get_schema_objectclass( $ldapserver, $val);

				if ($schema_object->type == 'structural') {
					echo "$val <small>(<acronym title=\"" . sprintf( $lang['structural_object_class_cannot_remove'] ) . "\">" . $lang['structural'] . "</acronym>)</small><br />";

					if ($side == 'dst') {?>

				<input type="hidden" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>" value="<?php echo htmlspecialchars($val); ?>" />

					<?php }
				continue;
				}
			}

			if( is_dn_string( $val ) || is_dn_attr( $ldapserver, $attr ) ) { ?>

				<a title="<?php echo sprintf( $lang['go_to_dn'], htmlspecialchars($val) ); ?>" href="edit.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;dn=<?php echo rawurlencode($val); ?>"><img style="vertical-align: top" src="images/go.png" /></a>

			<?php } elseif( is_mail_string( $val ) ) { ?>

				<a href="mailto:<?php echo htmlspecialchars($val); ?>"><img style="vertical-align: center" src="images/mail.png" /></a>

			<?php } elseif( is_url_string( $val ) ) { ?>

				<a href="<?php echo htmlspecialchars($val); ?>" target="new"><img style="vertical-align: center" src="images/dc.png" /></a>

			<?php }

			if( is_multi_line_attr( $attr, $val, $ldapserver->server_id ) ) {

				if ($side == 'dst') {?>
				<textarea class="val" rows="3" cols="30" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>"><?php echo htmlspecialchars($val); ?></textarea>

				<?php } else {
					echo htmlspecialchars($val);
				}

			} else {

				if ($side == 'dst') {?>

				<input type="text" class="val" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>" value="<?php echo htmlspecialchars($val); ?>" />

				<?php } else {
					echo htmlspecialchars($val);
				}
			}

			// draw a link for popping up the entry browser if this is the type of attribute
			// that houses DNs.
			if( is_dn_attr( $ldapserver, $attr ) )
				draw_chooser_link( "edit_form.$input_id", false ); ?>

			<br />
		<?php } ?>

		</td>

	<?php } /* end foreach value */ ?>

	</tr>

		<?php
		/* Draw the "add value" link under the list of values for this attributes */

		if (! $ldapserver_dst->isReadOnly()) {

			// First check if the required objectClass is in this DN
			$isOK = 0;
			$src_oclass = array();
			$attr_object = get_schema_attribute( $ldapserver_dst, $attr, $dn_dst );
			foreach ($attr_object->used_in_object_classes as $oclass) {
				if (in_array(strtolower($oclass),arrayLower($attrs_dst['objectClass']))) {
					$isOK = 1;
					break;
				} else {
					// Find oclass that the source has that provides this attribute.
					if (in_array($oclass,$attrs_src['objectClass']))
						$src_oclass[] = $oclass;
				}
			}

			print "<tr><td colspan=2></td><td colspan=2>&nbsp</td><td>&nbsp;</td><td>";
			if (! $isOK) {

				if (count($src_oclass) == 1) {
					$add_href = "add_oclass_form.php?server_id=$ldapserver_dst->server_id&dn=$encoded_dn_dst&new_oclass=$src_oclass[0]";
				} else {
					$add_href = "add_value_form.php?server_id=$ldapserver_dst->server_id&dn=$encoded_dn_dst&attr=objectClass";
				}

				if ($attr == 'objectClass')
					printf('<div class="add_oclass">(<a href="%s" title="%s">%s</a>)</div>',$add_href,$lang['add_oclass_and_attrs'],$lang['add_value']);
				else
					printf('<div class="add_oclass">(<a href="%s" title="%s">%s</a>)</div>',$add_href,sprintf($lang['need_oclass'], implode(" ",$src_oclass)),$lang['add_new_objectclass']);

			} else {
				if(! $schema_attr_dst->getIsSingleValue() || (! isset($vals))) {

					$add_href = "add_value_form.php?server_id=$ldapserver_dst->server_id&dn=$encoded_dn_dst&attr=" . rawurlencode( $attr );

					printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',
						$add_href,sprintf($lang['add_value_tooltip'],$attr),$lang['add_value']);
				}
			}
		}

		print "</td></tr>"; ?>

		</td>

	<?php flush(); ?>

	</tr>

<?php } /* End foreach( $attrs as $attr => $vals ) */

if( ! $ldapserver_dst->isReadOnly( ) ) { ?>

	<td colspan="2">&nbsp</td><td colspan=2><center><input type="submit" value="<?php echo $lang['save_changes']; ?>" /></center></td></tr></form>

<?php } ?>

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
	<input type="hidden" name="server_id" value="<?php echo $ldapserver_dst->server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn_dst; ?>" />
	<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
</form>

</body>
</html>
