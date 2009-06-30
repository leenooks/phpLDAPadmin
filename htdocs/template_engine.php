<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/template_engine.php,v 1.22 2005/09/25 16:11:44 wurley Exp $

/**
 * Template render engine.
 * @param dn $dn DN of the object being edited. (For editing existing entries)
 * @param dn $container DN where the new object will be created. (For creating new entries)
 * @param string $template to use for new entry. (For creating new entries)
 * @todo schema attr keys should be in lowercase.
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */
/**
 */

require "./common.php";

$friendly_attrs = process_friendly_attr_table(); // @todo might not need this.

# REMOVE THSE @todo
$today = date('U');
$shadow_before_today_attrs = arrayLower(array('shadowLastChange','shadowMin'));
$shadow_after_today_attrs = arrayLower(array('shadowMax','shadowExpire','shadowWarning','shadowInactive'));
$shadow_format_attrs = array_merge($shadow_before_today_attrs, $shadow_after_today_attrs);
# END REMOVE

# If we have a DN, then this is to edit the entry.
if (isset($_REQUEST['dn'])) {

	if( ! $ldapserver->haveAuthInfo())
		pla_error( $lang['not_enough_login_info'] );

	dn_exists( $ldapserver, $dn )
		or pla_error( sprintf( $lang['no_such_entry'], pretty_print_dn( $dn ) ) );

	$rdn = get_rdn($dn);
	$attrs = get_object_attrs( $ldapserver, $dn, false, $config->GetValue('deref','view'));

	$modified_attrs = isset( $_REQUEST['modified_attrs'] ) ? $_REQUEST['modified_attrs'] : false;
	$show_internal_attrs = isset( $_REQUEST['show_internal_attrs'] ) ? true : false;

	# If an entry has more children than this, stop searching and display this amount with a '+'
	$max_children = 100;

} else {

	$dn = '';
	$rdn = '';
	$encoded_dn = '';

	isset($_REQUEST['template']) or die(); // pla_error( $lang['must_choose_template'] );

	if ($_REQUEST['template'] == 'custom') {

		include TMPLDIR.'template_header.php';
		require TMPLDIR.'creation/custom.php';
		die();

	} else {
		$templates = new Templates($ldapserver->server_id);
		$template = $templates->GetTemplate($_REQUEST['template']);
	}
}

include TMPLDIR.'template_header.php';

	/*
	 * When we get here, (either a new entry, or modifying an existing entry), if the
	 * empty_attrs array has content, then we need to ask the user for this information.
	 */

	if (isset($template['empty_attrs'])) {
		masort($template['empty_attrs'],'page,order',1);

		# What page are we working on.
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
?>

	<center><h2><?php echo $template['description']; ?></h2></center>
	<center>
	<table class="confirm" border=0>

<?php if (isset($_REQUEST['nextpage']) && ! $_REQUEST['nextpage']) {
		$new_dn = sprintf('%s=%s,%s',$template['rdn'],$_REQUEST['form'][$template['rdn']],$_REQUEST['container']); ?>

	<form action="create.php" method="post">
	<input type="hidden" name="new_dn" value="<?php echo $new_dn; ?>" />

<?php } else { ?>
	<form action="template_engine.php" method="post" id="template_form" name="template_form">
<?php } ?>

<?php
if (isset($_REQUEST['form']))
	foreach ($_REQUEST['form'] as $attr => $value) {

		# Check for any with post actions.
		if (isset($template['attribute'][$attr]['post']) && $_REQUEST['page'] == $template['attribute'][$attr]['page']+1) {
			if (preg_match('/^=php\.(\w+)\((.*)\)$/',$template['attribute'][$attr]['post'],$matches)) {
				switch ($matches[1]) {
					case 'Password' : 
						preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$matches[2],$matchall);

						$value = password_hash( $_REQUEST['form'][$matchall[1][1]], $_REQUEST[$matchall[1][0]] );
						$_REQUEST['form'][$attr] = $value;
						break;

					default:
						#@todo: Error, unknown post funciton.
				}
			}
		}

		if (is_array($value))
			foreach ($value as $item)
				printf('<input type="hidden" name="form[%s][]" value="%s" />',$attr,$item);
		else 
			printf('<input type="hidden" name="form[%s]" value="%s" />',$attr,$value);
	}
?>
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
	<input type="hidden" name="template" value="<?php echo $_REQUEST['template']; ?>" />

	<input type="hidden" name="object_classes" value="<?php echo rawurlencode(serialize(array_values($template['objectclass']))); ?>" />

	<input type="hidden" name="page" value="<?php echo $page + 1; ?>" />

	<tr class="spacer"><td colspan="3"></td></tr>
	<tr>

<?php
	if (isset($template['askcontainer']) && $template['askcontainer'] && $page == 1) {
		if (! (isset($template['regexp']) && isset($template['regexp']))) {
?>
		<td></td>
		<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
		<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $_REQUEST['container'] ); ?>" />
			<?php draw_chooser_link( 'template_form.container' ); ?>
		</td>
		<tr class="spacer"><td colspan="3"></td></tr>

		<?php } else { ?>
		<td></td>
		<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
		<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $_REQUEST['container'] ); ?>" disabled />
		</td>
		<input type="hidden" name="container" value="<?php echo $_REQUEST['container']; ?>" />
		<tr class="spacer"><td colspan="3"></td></tr>
		<?php } ?>

	<?php } else { ?>
		<input type="hidden" name="container" value="<?php echo $_REQUEST['container']; ?>" />
	<?php }

		$count = 0;
		$nextpage = 0;
		$mustitems = 0;
		foreach ($template['empty_attrs'] as $attr => $detail) {

				$mustitem = false;
				$verifyitem = false;
				$onChange = '';
				$onBlur = '';
				$type = isset($detail['type']) ? $detail['type'] : 'text';

				if (! isset($detail['page'])) $detail['page'] = 1;
				$size = isset($detail['size']) ? $detail['size'] : 20;
				$maxlength = isset($detail['maxlength']) ? $detail['maxlength'] : null;

				# Check that the page number is correct.
				if ($detail['page'] < $page && ! isset($attr[$attr])) {
					# ERROR: This attribute should be set by now.
					print "We should have set [$attr] by now.<BR>";

				} elseif ($detail['page'] == $page) {

					$count++;
					print '</tr>';

					# Some conditional checking.
					# $detail['must'] & $detail['disable'] cannot be set at the same time.
					if (isset($detail['must']) && $detail['must'] && isset($detail['disable']) && $detail['disable']) {
						# @todo: Need to make this a proper error message.
						print "ERROR: Attribute is a MUST attribute, so it cannot be disabled.";
					}

					# If this attribute is disabled, go to the next one.
					if (isset($detail['disable']) && $detail['disable'])
						continue;

					# Evaluate our Default Value, if its a function call result.
					if (isset($detail['default'])) {

						if (is_array($detail['default'])) {

							# If default is an array, then it must a select list.
							$type = 'select';
							$defaultresult = sprintf('<select name="form[%s]" id="%%s" %%s %%s/>',$attr);
							foreach ($detail['default'] as $default) {
								$defaultresult .= sprintf('<option name="%s" value="%s">%s</option>',$default,$default,$default);
							}
							$defaultresult .= '</select>';
							$detail['default'] = $defaultresult;

						} else {
							$detail['default'] = $templates->EvaluateDefault($ldapserver,$detail['default'],$_REQUEST['container']);
						}

						#if the default has a select list, then change the type to select
						if (preg_match('/<select .*>/i',$detail['default']))
							$type = 'select';
					}

					# @todo: $detail['must'] && $detail['hidden'] must have $detail['default'] (with a value).
					# @todo: if default is a select list, then it cannot be hidden.

					# If this is a hidden attribute, then set its value.
					if (isset($detail['hidden']) && $detail['hidden']) {
						printf('<input type="%s" name="form[%s]" id="%s" value="%s"/>','hidden',$attr,$attr,$detail['default']);
						continue;
					}

					# This is a displayed attribute.
					# Flag it as a must attribute so that we do get a value.
					if (isset($detail['must']) && $detail['must'] && ! isset($detail['presubmit'])) {
						$mustitems++;
						$mustitem = true;
						$onBlur .= sprintf('reduceMust(this.form,%s,\'%s\');',$attr,$attr);
					}

					# Display the icon if one is required.
					if (isset($detail['icon']))
						printf('<td><img src="%s"></td>',$detail['icon']);
					else
						printf('<td></td>');

					print '<td class="heading">';

					# Display the label.
					if (isset($detail['description']) && (trim($detail['description'])))
						printf('<acronym title="%s">%s</acronym>:',$detail['description'],$detail['display']);
					else
						printf('%s:',$detail['display']);

					print '</td>';

					# Calculate the events.
					# @todo: Need to change js so that if a must attr is auto populated, it decrements the total and enables the submit.
					if (isset($detail['onchange'])) {
						if (is_array($detail['onchange'])) {
							foreach ($detail['onchange'] as $value) {
								$onChange .= sprintf('%s;',$templates->OnChangeAdd($value));
							}
						} else {
							$onChange .= sprintf('%s;',$templates->OnChangeAdd($detail['onchange']));
						}
					}

					# Display the input box.
					print '<td>';

					if (in_array($type,array('text','password'))) {
						printf('<input type="%s" size="%s" name="form[%s]%s" id="%s" value="%s" %s %s %s/>',
							$type,$size,$attr,(isset($detail['array']) && ($detail['array'] > 1) ? '[]' : ''),$attr,
							(isset($detail['default']) ? $detail['default'] : ''),
							($onChange ? sprintf('onChange="%s"',$onChange) : '').($onBlur ? sprintf(' onBlur="%s"',$onBlur) : ''),
							(isset($detail['disable']) ? 'disabled' : ''),
							(isset($detail['maxlength']) ? sprintf(' maxlength="%s" ',$maxlength) : ''));

					} else if ($type == 'select') {
						printf($detail['default'],$attr,
							($onChange ? sprintf('onChange="%s"',$onChange) : '').($onBlur ? sprintf(' onBlur="%s"',$onBlur) : ''),
							(isset($detail['disable']) ? 'disabled' : ''));
					}

					# Disabled items dont get submitted.
					# @todo need to add some js to enable them on submit, or add them as hidden items.

					if ($mustitem) {
						print '&nbsp;*';
					}

					# Do we have a helper, and is it configured for the side.
					if (isset($detail['helper']) && isset($detail['helper']['location'])
						&& $detail['helper']['location'] == 'side' && isset($detail['helper']['value'])) {

						printf('&nbsp;%s',$templates->HelperValue($detail['helper']['value'],
							(isset($detail['helper']['id']) ? $detail['helper']['id'] : ''),
							$_REQUEST['container'],$ldapserver,null,
							isset($detail['helper']['default']) ? $detail['helper']['default'] : ''));
					}

					if (isset($detail['hint']) && (trim($detail['hint'])))
						printf('&nbsp;<span class="hint">(hint: %s)</span></td>',$detail['hint']);
					else
						print '</td>';

					print '</tr>';

					# Do we have a verify attribute?
					if (isset($detail['verify']) && ($detail['verify'])) {

						$verifyitems = true;

						print '<tr><td>&nbsp;</td><td class="heading">';

						# Display the label.
						if (isset($detail['description']) && (trim($detail['description'])))
							printf('<acronym title="%s">%s %s</acronym>:',$lang['t_verify'],$detail['description'],$detail['display']);
						else
							printf('%s %s:',$lang['t_verify'],$detail['display']);

						print '</td><td>';

						if (in_array($type,array('text','password'))) {
							printf('<input type="%s" name="%s" id="%s" value="%s" %s/>',
								$type,$attr."V",$attr."V",(isset($detail['default']) ? $detail['default'] : ''),
								sprintf('onBlur="check(form.%s,form.%sV)"',$attr,$attr));
						}

						print '</td></tr>';
					}

					# Is this a multiarray input?
					if (isset($detail['array']) && ($detail['array'])) {
						for ($i=2; $i <= $detail['array']; $i++) {
							print '<tr><td>&nbsp;</td><td>&nbsp;</td>';

							printf('<td><input type="%s" name="form[%s][]" id="%s" value="%s" %s %s/>',
								$type,$attr,$attr.$i,
								(isset($detail['default']) ? $detail['default'] : ''),
								($onChange ? sprintf('onChange="%s"',$onChange) : '').($onBlur ? sprintf(' onBlur="%s"',$onBlur) : ''),
								isset($detail['disable']) ? 'disabled' : '');

							if (isset($detail['helper']) && isset($detail['helper']['location'])
								&& $detail['helper']['location'] == 'side' && isset($detail['helper']['value'])) {

								printf('&nbsp;%s',$templates->HelperValue($detail['helper']['value'],
									(isset($detail['helper']['id']) ? $detail['helper']['id'] : ''),$_REQUEST['container'],$ldapserver,$i));
							}
							print '</td></tr>';
						}
					}

					# Do we have a helper.
					# Side helpers are handled above.
					# @todo: Helpers must have an onchange or onsubmit.
					# @todo: Helpers must have an id field.
					# @todo: Helpers must have an post field.

					if (isset($detail['helper']) && (! isset($detail['helper']['location']) || $detail['helper']['location'] != 'side')) {

						print '<tr><td>&nbsp;</td>';
						print '<td class="heading">';

						# Display the label.
						if (isset($detail['helper']['description']) && (trim($detail['helper']['description'])))
							printf('<acronym title="%s">%s</acronym>:',$detail['helper']['description'],$detail['helper']['display']);
						else
							printf('%s:',$detail['helper']['display']);

						print '</td>';

						printf('<td>%s</td>',$templates->HelperValue($detail['helper']['value'],$detail['helper']['id']));
					}

					if (isset($detail['spacer']) && $detail['spacer'])
						print '<tr class="spacer"><td colspan="3"></td></tr>';

				# See if there are any future ones - if there are and we dont ask any this round, then thats an error.
				} elseif ($detail['page'] > $page) {
					$nextpage++;
				}
		}

		# @todo: Proper error message required.
		if ($nextpage && ! $count)
			print "ERROR: We are missing a page for [$nextpage] attributes.<BR>";

		# If there is no count, display the summary
		if (! $count) {
			printf('<tr><td><img src="%s"></td><td><span class="x-small">%s :</span></td><td><b>%s</b></td></tr>',
				$template['icon'],$lang['createf_create_object'],htmlspecialchars($new_dn));

			print '<tr class="spacer"><td colspan="3"></td></tr>';

			$counter = 0;
			foreach ($_REQUEST['form'] as $attr => $value) {

				# Remove blank attributes.
				if (! $_REQUEST['form'][$attr]) {
					unset($_REQUEST['form'][$attr]);
					continue;
				}

				$attrs[] = $attr;
				printf('<input type="hidden" name="attrs[]" value="%s" />',$attr);
				if (is_array($value))
					foreach ($value as $item) {
						if ($item && ! isset($unique[$item])) {
							$unique[$item] = 1;
							printf('<tr class="%s"><td colspan=2>%s</td><td><b>%s</b></td></tr>',
								($counter++%2==0?'even':'odd'),$attr,htmlspecialchars($item));
							printf('<input type="hidden" name="vals[%s][]" value="%s" />',array_search($attr,$attrs),$item);
						}
					}

				else {
					$display = $value;
					if (isset($template['attribute'][$attr]['type']) && $template['attribute'][$attr]['type'] == 'password')
						if (obfuscate_password_display($_REQUEST['enc']))
							$display = '********';

					printf('<tr class="%s"><td colspan=2>%s</td><td><b>%s</b></td></tr>',
						($counter++%2==0?'even':'odd'),$attr,htmlspecialchars($display));
					printf('<input type="hidden" name="vals[]" value="%s" />',$value);
				}

			}

		} ?>

	<input type="hidden" name="nextpage" value="<?php echo $nextpage; ?>" />

	<tr class="spacer"><td colspan="3"></td></tr>
	<tr>

<?php
if (! $nextpage && isset($_REQUEST['nextpage']) && ! $_REQUEST['nextpage']) {

	# Look for any presubmit functions.
	foreach ($template['empty_attrs'] as $attr => $detail) {
		if (isset($template['attribute'][$attr]['presubmit']) && ! isset($_REQUEST['form'][$attr])) {
			printf('<tr class="%s"><td colspan=2>%s</td><td><b>%s</b></td></tr>',
				($counter++%2==0?'even':'odd'),$attr,htmlspecialchars($lang['t_auto_submit']));
			printf('<input type="hidden" name="presubmit[]" value="%s" />',$attr);
		}
	}
?>
		<td colspan="3"><center><br /><input type="submit" name='submit' value="<?php echo $lang['createf_create_object']; ?>" <?php echo $mustitems ? 'disabled' : '' ?>/></center></td>

<?php } elseif ($nextpage) { ?>
		<td colspan="3"><center><br /><input type="submit" name='submit' value="<?php echo $lang['next_page']; ?>" <?php echo $mustitems ? 'disabled' : '' ?>/></center></td>

<?php } else { ?>
		<td colspan="3"><center><br /><input type="submit" name='submit' value="<?php echo $lang['proceed_gt']; ?>" <?php echo $mustitems ? 'disabled' : '' ?>/></center></td>
<?php } ?>

<?php if ($mustitems) { ?>
	<input type="hidden" name="mustitems" value="<?php echo $mustitems; ?>" />
<?php } ?>

	</tr>
	</form>
	</table>
	</center>
	<span class="hint">This is the template engine.</span><br>
	<span class="hint"><?php printf($lang['page_n'], $page); ?>.</span>

<?php
		if ($mustitems) {
			print '<script language="javascript">';
			print '	var reduced = new Array();';
			print 'function reduceMust(form,attr,attrname){';
			print '	if (attr.value.length > 0) {';
			print '		if (! reduced[attrname]) {';
			print '			reduced[attrname] = 1;';
			print '			form.mustitems.value--;';
			print '		}';
			print '';		
			print '		if (form.mustitems.value < 0) {';
			print '			form.mustitems.value = 0;';
			print '		}';
			print '';		
			print '		if (form.mustitems.value == 0) {';
			print '			form.submit.disabled = false;';
			print '		}';
			print '	} else {';
			print '		if (reduced[attrname]) {';
			print '			reduced[attrname] = 0;';
			print '			form.mustitems.value++;';
			print '		}';
			print '		if (form.mustitems.value > 0) {';
			print '			form.submit.disabled = true;';
			print '		}';
			print '	}';
			print '}';
			print '</script>';
		}

		if (isset($verifyitems) && $verifyitems) {
			//@todo: Return focus to the first item.
			print '<script language="javascript">';
			print 'function check(a,b){';
			print '	if (a.value != b.value){';
			print '		alert(\'Values dont compare\')';
			print '	}';
			print '}';
			print '</script>';
		}

		if ($templates->OnChangeDisplay()) {
			print '<script language="javascript">';
			print $templates->OnChangeDisplay();
			print '</script>';
		}

		# User needs to submit form to continue.
		die();
	}

if (! isset($template))
	$template['attrs'] = $attrs;

# If we get here - we are displaying/editing the entry.
# Sort these entries.
uksort( $template['attrs'], 'sortAttrs' );

foreach( $template['attrs'] as $attr => $vals ) {

	flush();

	$schema_attr = get_schema_attribute( $ldapserver, $attr, $dn );
	if( $schema_attr )
		$attr_syntax = $schema_attr->getSyntaxOID();
	else
		$attr_syntax = null;

	if( ! strcasecmp( $attr, 'dn' ) )
		continue;

	// has the config.php specified that this attribute is to be hidden or shown?
	if( is_attr_hidden( $ldapserver, $attr))
		continue;

	// Setup the $attr_note, which will be displayed to the right of the attr name (if any)
	$attr_note = '';

	// is there a user-friendly translation available for this attribute?
	if( isset( $friendly_attrs[ strtolower( $attr ) ] ) ) {
		$attr_display = $friendly_attrs[ strtolower( $attr ) ];
		$attr_note = "<acronym title=\"" . sprintf( $lang['alias_for'], $attr_display, $attr ) . "\">alias</acronym>";

	} else {
		$attr_display = $attr;
	}

	// is this attribute required by an objectClass?
	$required_by = '';
	if( $schema_attr )
		foreach( $schema_attr->getRequiredByObjectClasses() as $required )
			if( isset($attrs['objectClass']) && in_array( strtolower( $required ), arrayLower( $attrs['objectClass'] ) ) )
				$required_by .= $required . ' ';

			// It seems that some LDAP servers (Domino) returns attributes in lower case?
			elseif( isset($attrs['objectclass']) && in_array( strtolower( $required ), arrayLower( $attrs['objectclass'] ) ) )
				$required_by .= $required . ' ';

	if( $required_by ) {
		if( trim( $attr_note ) )
			$attr_note .= ', ';

		$attr_note .= "<acronym title=\"" . sprintf( $lang['required_for'], $required_by ) . "\">" . $lang['required'] . "</acronym>&nbsp;";
	}

	// is this attribute required because its the RDN
	if (preg_match("/^${attr}=/",$rdn)) {
		if( trim( $attr_note ) )
			$attr_note .= ', ';

		$attr_note .= "&nbsp;<acronym title=\"" . $lang['required_by_entry'] . "\">" . 'rdn' . "</acronym>&nbsp;";
	}

	if( is_array( $modified_attrs ) && in_array( strtolower($attr), $modified_attrs ) )
		$is_modified_attr = true;
	else
		$is_modified_attr = false;

	if( $is_modified_attr ) { ?>
	<tr class="updated_attr">

	<?php } else { ?>

	<tr>

	<?php } ?>

		<td class="attr">

	<?php $schema_href = sprintf("schema.php?server_id=%s&view=attributes&viewvalue=%s",
		$ldapserver->server_id,real_attr_name($attr)); ?>

			<b><a title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ) ?>" href="<?php echo $schema_href; ?>"><?php echo $attr_display; ?></a></b>
		</td>

		<td class="attr_note">
			<sup><small><?php echo $attr_note; ?></small></sup>

	<?php if( is_attr_read_only( $ldapserver, $attr ) ) { ?>

			<small>(<acronym title="<?php echo $lang['read_only_tooltip']; ?>"><?php echo $lang['read_only']; ?></acronym>)</small>

	<?php } ?>
		</td>
	</tr>

	<?php if( $is_modified_attr ) { ?>

	<tr class="updated_attr">

	<?php } else { ?>

	<tr>
	<?php } ?>

		<td class="val" colspan="2">

	<?php

	/*
	 * Is this attribute a jpegPhoto?
	 */
	if( is_jpeg_photo( $ldapserver, $attr ) ) {

		// Don't draw the delete buttons if there is more than one jpegPhoto
		// 	(phpLDAPadmin can't handle this case yet)
		if( $ldapserver->isReadOnly() || is_attr_read_only( $ldapserver, $attr ) )
			draw_jpeg_photos( $ldapserver, $dn, $attr, false );
		else
			draw_jpeg_photos( $ldapserver, $dn, $attr, true );

		// proceed to the next attribute
		echo "</td></tr>\n";

		if( $is_modified_attr )
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this attribute binary?
	 */
	if( is_attr_binary( $ldapserver, $attr ) ) {

		$href = sprintf('download_binary_attr.php?server_id=%s&amp;dn=%s&amp;attr=%s',
			$ldapserver->server_id,$encoded_dn,$attr);
?>

			<small>

		<?php echo $lang['binary_value'];

		if (! strcasecmp( $attr, 'objectSid' ) ) {
			printf(' (%s)',binSIDtoText($vals[0]));
		} ?>

		<br />

		<?php if( count( $vals ) > 1 ) {
			for( $i=1; $i<=count($vals); $i++ ) { ?>

			<a href="<?php echo $href . "&amp;value_num=$i"; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?>(<?php echo $i; ?>)</a><br />

			<?php }

		} else { ?>

			<a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a><br />

		<?php } ?>

		<?php if( ! $ldapserver->isReadOnly() && ! is_attr_read_only( $ldapserver, $attr ) ) { ?>

		<a href="javascript:deleteAttribute( '<?php echo $attr; ?>' );" style="color:red;"><img src="images/trash.png" /> <?php echo $lang['delete_attribute']; ?></a>

		<?php } ?>

			</small>
		</td>
	</tr>

		<?php if( $is_modified_attr )

			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Note: at this point, the attribute must be text-based (not binary or jpeg)
	 */

	// If this is the userPassword attribute, add the javascript so we can call check password later.
	if (! strcasecmp( $attr, 'userPassword' ) ) {
		$user_password = $vals[0]; ?>

	<script language="javascript">
	<!--
		function passwordComparePopup() {
			mywindow = open( 'password_checker.php', 'myname', 'resizable=no,width=450,height=200,scrollbars=1' );
			mywindow.location.href = 'password_checker.php?hash=<?php echo base64_encode($user_password); ?>&base64=true';
			if( mywindow.opener == null ) mywindow.opener = self;
		}
	-->
	</script>

	<?php }

	/*
	 * If this server is in read-only mode or this attribute is configured as read_only,
	 * simply draw the attribute values and continue.
	 */
	if( $ldapserver->isReadOnly() || is_attr_read_only( $ldapserver, $attr ) || (preg_match("/^${attr}=/",$rdn)) ) {
		if( is_array( $vals ) ) {
			foreach( $vals as $i => $val ) {
				if( trim( $val ) == "" )
					echo "<span style=\"color:red\">[" . $lang['empty'] . "]</span><br />\n";

				elseif( ! strcasecmp( $attr, 'userPassword' ) && $config->GetValue('appearance','obfuscate_password_display'))
					echo preg_replace( '/./', '*', $val ) . "<br />";

				elseif( in_array(strtolower($attr), $shadow_format_attrs) ) {
					$shadow_date = shadow_date( $attrs, $attr);
					echo htmlspecialchars($val)."&nbsp;";
					echo "<small>";

					if( ($today < $shadow_date) && in_array(strtolower($attr), $shadow_before_today_attrs) )
						echo '<span style="color:red">'.htmlspecialchars("(".strftime($config->GetValue('appearance','date'),$shadow_date).")").'</span>';
					elseif( $today > $shadow_date && in_array(strtolower($attr), $shadow_after_today_attrs) )
						echo '<span style="color:red">'.htmlspecialchars("(".strftime($config->GetValue('appearance','date'),$shadow_date).")").'</span>';
					else
						echo htmlspecialchars("(".strftime($config->GetValue('appearance','date'),shadow_date( $attrs, $attr)).")");

					echo "</small>";

				} else
					echo htmlspecialchars( $val ) . "<br />";
			}

		} else {

debug_dump($_REQUEST,1);
			if( ! strcasecmp( $attr, 'userPassword' ) && obfuscate_password_display())
				echo preg_replace( '/./', '*', $vals ) . "<br />";
			else
				echo $vals . "<br />";

		}

		if (! strcasecmp( $attr, 'userPassword' ) ) {?>

			<small><a href="javascript:passwordComparePopup()"><?php echo $lang['t_check_pass']; ?></a></small>

		<?php }

		if( preg_match("/^${attr}=/",$rdn) ) {?>
			<small>(<a href="<?php echo $rename_href; ?>"><?php echo $lang['rename_lower']; ?></a>)</small>

		<?php } ?>

		</td>
	</tr>

		<?php if( $is_modified_attr )
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this a userPassword attribute?
	 */
	if( 0 == strcasecmp( $attr, 'userpassword' ) ) {
		$user_password = $vals[0];

	$enc_type = get_enc_type( $user_password );

	// Set the default hashing type if the password is blank (must be newly created)
	if( $user_password == '' )
		$enc_type = get_default_hash( $ldapserver->server_id ); ?>

			<input type="hidden" name="old_values[userpassword]" value="<?php echo htmlspecialchars($user_password); ?>" />

			<!-- Special case of enc_type to detect changes when user changes enc_type but not the password value -->
			<input size="38" type="hidden" name="old_enc_type" value="<?php echo ($enc_type==''?'clear':$enc_type); ?>" />

	<?php if (obfuscate_password_display($enc_type)) {
		echo htmlspecialchars(preg_replace("/./","*",$user_password));

	} else {
		echo htmlspecialchars($user_password);
	} ?>

			<br />
			<input style="width: 260px" type="password" name="new_values[userpassword]" value="<?php echo htmlspecialchars( $user_password ); ?>" />

	<?php echo enc_type_select_list($enc_type); ?>

			<br />

			<small><a href="javascript:passwordComparePopup()"><?php echo $lang['t_check_pass']; ?></a></small>

		</td>
	</tr>

	<?php if( $is_modified_attr )
		echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this a boolean attribute?
	 */
	if( is_attr_boolean( $ldapserver, $attr) ) {
		$val = $vals[0]; ?>

			<input type="hidden" name="old_values[<?php echo htmlspecialchars( $attr ); ?>]" value="<?php echo htmlspecialchars($val); ?>" />

			<select name="new_values[<?php echo htmlspecialchars( $attr ); ?>]">
				<option value="TRUE"<?php echo ($val=='TRUE' ? ' selected' : ''); ?>>
					<?php echo $lang['true']; ?></option>
				<option value="FALSE"<?php echo ($val=='FALSE' ? ' selected' : ''); ?>>
					<?php echo $lang['false']; ?></option>
				<option value="">(<?php echo $lang['none_remove_value']; ?>)</option>
			</select>
		</td>
	</tr>

		<?php if( $is_modified_attr )
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * End of special case attributes (non plain text).
	 */


	/*
	 * This is a plain text attribute, to be displayed and edited in plain text.
	 */
	foreach( $vals as $i => $val ) {

		$input_name = "new_values[" . htmlspecialchars( $attr ) . "][$i]";
		// We smack an id="..." tag in here that doesn't have [][] in it to allow the
		// draw_chooser_link() to identify it after the user clicks.
		$input_id = "new_values_" . htmlspecialchars($attr) . "_" . $i; ?>

		<!-- The old_values array will let update.php know if the entry contents changed
		     between the time the user loaded this page and saved their changes. -->
		<input type="hidden" name="old_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($val); ?>" />

		<?php // Is this value is a structural objectClass, make it read-only
		if( ! strcasecmp( $attr, 'objectClass' ) ) { ?>

		<a title="<?php echo $lang['view_schema_for_oclass']; ?>" href="schema.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;view=objectClasses&amp;viewvalue=<?php echo htmlspecialchars( $val ); ?>"><img src="images/info.png" /></a>

			<?php $schema_object = get_schema_objectclass( $ldapserver, $val);

			if ($schema_object->type == 'structural') {
				echo "$val <small>(<acronym title=\"" .
				sprintf( $lang['structural_object_class_cannot_remove'] ) . "\">" .
				$lang['structural'] . "</acronym>)</small><br />"; ?>

		<input type="hidden" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>" value="<?php echo htmlspecialchars($val); ?>" />

				<?php continue;
			}
		}

		if( is_dn_string( $val ) || is_dn_attr( $ldapserver, $attr ) ) { ?>

		<a title="<?php echo sprintf( $lang['go_to_dn'], htmlspecialchars($val) ); ?>" href="edit.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;dn=<?php echo rawurlencode($val); ?>"><img style="vertical-align: top" src="images/go.png" /></a>

		<?php } elseif( is_mail_string( $val ) ) { ?>

		<a href="mailto:<?php echo htmlspecialchars($val); ?>"><img style="vertical-align: center" src="images/mail.png" /></a>

		<?php } elseif( is_url_string( $val ) ) { ?>

		<a href="<?php echo htmlspecialchars($val); ?>" target="new"><img style="vertical-align: center" src="images/dc.png" /></a>

		<?php }

		if (is_multi_line_attr($attr,$val,$ldapserver->server_id)) { ?>

		<textarea class="val" rows="3" cols="50" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>"><?php echo htmlspecialchars($val); ?></textarea>

		<?php } else { ?>

		<input type="text" class="val" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>" value="<?php echo htmlspecialchars($val); ?>" />

		<?php }

		print "<br />";

		// draw a link for popping up the entry browser if this is the type of attribute
		// that houses DNs.
		if( is_dn_attr( $ldapserver, $attr ) )
			draw_chooser_link( "edit_form.$input_id", false );

		// If this is a gidNumber on a non-PosixGroup entry, lookup its name and description for convenience
		if( ! strcasecmp( $attr, 'gidNumber' ) &&
			! in_array_ignore_case( 'posixGroup', get_object_attr( $ldapserver, $dn, 'objectClass' ) ) ) {

			$gid_number = $val;
			$search_group_filter = "(&(objectClass=posixGroup)(gidNumber=$val))";
			$group = pla_ldap_search( $ldapserver, $search_group_filter, null, array( 'dn', 'description' ) );

			if( count( $group ) > 0 ) {
				echo "<br />";
				$group = array_pop( $group );
				$group_dn = $group['dn'];
				$group_name = explode( '=', get_rdn( $group_dn ) );
				$group_name = $group_name[1];
				$href = sprintf('edit.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,urlencode($group_dn));
				echo "<small>";
				echo "<a href=\"$href\">" . htmlspecialchars($group_name) . "</a>";
				$description = isset( $group['description'] ) ? $group['description'] : null;

				if( $description ) echo " (" . htmlspecialchars( $description ) . ")";
				echo "</small>";
			}
		}

		// Show the dates for all the shadow attributes.
		if( in_array(strtolower($attr), $shadow_format_attrs) ) {
			if( ( $shadow_date = shadow_date( $attrs, $attr) ) !== false ) {
				echo "<br />";
				echo "<small>";

				if( ($today < $shadow_date) && in_array(strtolower($attr), $shadow_before_today_attrs) )
					echo '<span style="color:red">'.htmlspecialchars(strftime($config->GetValue('appearance','date'),$shadow_date)).'</span>';
				elseif( $today > $shadow_date && in_array(strtolower($attr), $shadow_after_today_attrs) )
					echo '<span style="color:red">'.htmlspecialchars(strftime($config->GetValue('appearance','date'),$shadow_date)).'</span>';
				else
					echo htmlspecialchars(strftime($config->GetValue('appearance','date'),$shadow_date));

				echo "</small>";
			}
		}

	} /* end foreach value */

	/* Draw the "add value" link under the list of values for this attributes */

	if(	! $ldapserver->isReadOnly() &&
		( $schema_attr = get_schema_attribute( $ldapserver, $attr, $dn ) ) &&
		! $schema_attr->getIsSingleValue() ) {

		$add_href = sprintf('add_value_form.php?server_id=%s&amp;dn=%s&amp;attr=%s',
			$ldapserver->server_id,$encoded_dn,rawurlencode($attr));

		printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',
			$add_href,sprintf( $lang['add_value_tooltip'], $attr ),$lang['add_value']);
	} ?>

	</td>
	</tr>

	<?php if( $is_modified_attr ) { ?>

		<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>

	<?php }

	flush();

} /* End foreach( $attrs as $attr => $vals ) */

if (! $ldapserver->isReadOnly()) { ?>

	<tr><td colspan="2"><center><input type="submit" value="<?php echo $lang['save_changes']; ?>" /></center></td></tr></form>

<?php } ?>

</table>

<?php /* If this entry has a binary attribute, we need to provide a form for it to submit when deleting it. */ ?>

<script language="javascript">
<!--
function deleteAttribute( attrName )
{
	if( confirm( "<?php echo $lang['really_delete_attribute']; ?> '" + attrName + "'?" ) ) {
		document.delete_attribute_form.attr.value = attrName;
		document.delete_attribute_form.submit();
	}
}
-->
</script>

<!-- This form is submitted by JavaScript when the user clicks "Delete attribute" on a binary attribute -->
<form name="delete_attribute_form" action="delete_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
	<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
</form>

<?php
echo "</body>\n</html>";
?>
