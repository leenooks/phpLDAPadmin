<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/compare.php,v 1.16.2.3 2008/01/13 06:33:50 wurley Exp $

/**
 * Compare two DNs - the destination DN is editable.
 * @package phpLDAPadmin
 */

require_once './common.php';

$dn_src = isset($_POST['dn_src']) ? $_POST['dn_src'] : null;
$dn_dst = isset($_POST['dn_dst']) ? $_POST['dn_dst'] : null;

$encoded_dn_src = rawurlencode($dn_src);
$encoded_dn_dst = rawurlencode($dn_dst);

$server_id_src = (isset($_POST['server_id_src']) ? $_POST['server_id_src'] : '');
$server_id_dst = (isset($_POST['server_id_dst']) ? $_POST['server_id_dst'] : '');

$ldapserver_src = $_SESSION[APPCONFIG]->ldapservers->Instance($server_id_src);
if (! $ldapserver_src->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$ldapserver_dst = $_SESSION[APPCONFIG]->ldapservers->Instance($server_id_dst);
if (! $ldapserver_src->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

if (! $ldapserver_src->dnExists($dn_src))
	pla_error(sprintf(_('No such entry: %s'),pretty_print_dn($dn_src)));
if (! $ldapserver_dst->dnExists($dn_dst))
	pla_error(sprintf(_('No such entry: %s'),pretty_print_dn($dn_dst)));

$attrs_src = $ldapserver_src->getDNAttrs($dn_src,false,$_SESSION[APPCONFIG]->GetValue('deref','view'));
$attrs_dst = $ldapserver_dst->getDNAttrs($dn_dst,false,$_SESSION[APPCONFIG]->GetValue('deref','view'));

# Get a list of all attributes.
$attrs_all = array_keys($attrs_src);
foreach ($attrs_dst as $key => $val)
	if (! in_array($key,$attrs_all))
		$attrs_all[] = $key;

	printf('<h3 class="title">%s</h3>',_('Comparing the following DNs'));

	echo '<table class="entry" width=100% border=0>';
	echo '<tr>';
	printf('<td colspan=2 width=20%%><h3 class="subtitle">%s<br />&nbsp;</h3></td>',_('Attribute'));

	printf('<td colspan=2 width=40%%><h3 class="subtitle">%s: <b>%s</b><br />%s: <b>%s</b></h3></td>',
		_('Server'),$ldapserver_src->name,_('Distinguished Name'),htmlspecialchars($dn_src));

	printf('<td colspan=2 width=40%%><h3 class="subtitle">%s: <b>%s</b><br />%s: <b>%s</b></h3></td>',
		_('Server'),$ldapserver_dst->name,_('Distinguished Name'),htmlspecialchars($dn_dst));

	echo '</tr>';

	echo '<tr>';
	echo '<td colspan=6 align=right>';
	echo '<form action="cmd.php?cmd=compare" method="post" name="compare_form">';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="server_id_src" value="%s" />',$ldapserver_dst->server_id);
	printf('<input type="hidden" name="server_id_dst" value="%s" />',$ldapserver_src->server_id);
	printf('<input type="hidden" name="dn_src" value="%s" />',htmlspecialchars($dn_dst));
	printf('<input type="hidden" name="dn_dst" value="%s" />',htmlspecialchars($dn_src));
	printf('<input type="submit" value="%s" />',_('Switch Entry'));
	echo '</form>';
	echo '</td>';
	echo '</tr>';

if (! $attrs_all || ! is_array($attrs_all)) {
	printf('<tr><td colspan="2">(%s)</td></tr>',_('This entry has no attributes'));
	print '</table>';
	return;
}

sort($attrs_all);
$formdisplayed = false;

# Work through each of the attributes.
foreach ($attrs_all as $attr) {
	# If this is the DN, get the next attribute.
	if (! strcasecmp($attr,'dn'))
		continue;

	# Has the config.php specified that this attribute is to be hidden or shown?
	if ($ldapserver_src->isAttrHidden($attr) || $ldapserver_dst->isAttrHidden($attr))
		continue;

	$schema_attr_src = $ldapserver_src->getSchemaAttribute($attr,$dn_src);
	$schema_attr_dst = $ldapserver_dst->getSchemaAttribute($attr,$dn_dst);

	# Get the values and see if they are the same.
	if (isset($attrs_src[$attr]) && isset($attrs_dst[$attr]) && $attrs_src[$attr] === $attrs_dst[$attr])
		echo '<tr>';
	else
		echo '<tr class="updated">';

	foreach (array('src','dst') as $side) {

		# Setup the $attr_note, which will be displayed to the right of the attr name (if any)
		$attr_note = '&nbsp;';

		# is there a user-friendly translation available for this attribute?
		if ($_SESSION[APPCONFIG]->haveFriendlyName($attr)) {
			$attr_display = $_SESSION[APPCONFIG]->getFriendlyName($attr);
			$attr_note = sprintf('<acronym title="%s: \'%s\' %s \'%s\'">%s</acronym>',_('Note'),$attr_display,_('is an alias for'),$attr,_('alias'));

		} else {
			$attr_display = $attr;
			$attr_note = '&nbsp;';
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
				elseif (isset($attrs_src['objectclass']) && in_array(strtolower($required),arrayLower($attrs_src['objectclass'])))
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

		# If we are on the source side, show the attr
		if ($side == 'src') {
			echo '<td class="title">';
			$schema_href = sprintf('cmd.php?cmd=schema&amp;server_id=%s&amp;view=attributes&amp;viewvalue=%s',$server_id_src,real_attr_name($attr));
			printf('<a title="%s" href="%s">%s</a>',sprintf(_('Click to view the schema definition for attribute type \'%s\''),$attr),$schema_href,$attr_display);
			echo '</td>';

			printf('<td class="note"><sup><small>%s</small></sup></td>',$attr_note);
		}

		echo '<td colspan=2 class="note">';

		# Create our form if the dst is editable.
		if ($side == 'dst' && ! $ldapserver_dst->isReadOnly() && ! $formdisplayed) {
			$formdisplayed = true;
			echo '<form action="cmd.php?cmd=update_confirm" method="post" name="edit_form">';
			printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver_dst->server_id);
			printf('<input type="hidden" name="dn" value="%s" />',$dn_dst);
		}

		if ($required_by)
			printf('<sup><small><acronym title="%s">%s</acronym></small></sup>',sprintf(_('Required attribute for objectClass(es) %s'),$required_by),_('required'));
		echo '</td>';

		if ($ldapserver->isAttrReadOnly($attr))
			printf('<small>(<acronym title="%s">%s</acronym>)</small>',_('This attribute has been flagged as read only by the phpLDAPadmin administrator'),_('read only'));
	}

	echo '</tr>';

	if (isset($attrs_src[$attr]) && isset($attrs_dst[$attr]) && $attrs_src[$attr] === $attrs_dst[$attr])
		echo '<tr>';
	else
		echo '<tr class="updated">';

	foreach (array('src','dst') as $side) {
		$vals = null;

		# If this attribute isnt set, then show a blank.
		$toJump = 0;
		switch ($side) {
			case 'src':
				print '<td colspan=2>&nbsp;</td><td class="value">';

				if (! isset($attrs_src[$attr])) {
					echo "<small>&lt;". _('No Value')."&gt;</small></td>";
					$toJump = 1;
					continue;
				} else
					$vals = $attrs_src[$attr];

				$ldapserver = $ldapserver_src;
				break;

			case 'dst':
				print '<td colspan=2>&nbsp;</td><td class="value">';

				if (! isset($attrs_dst[$attr])) {
					echo "<small>&lt;". _('No Value')."&gt;</small></td>";
					$toJump = 1;
					continue;
				} else
					$vals = $attrs_dst[$attr];

				$ldapserver = $ldapserver_dst;
				break;
		}

		if ($toJump)
			continue;

		if (! is_array($vals))
			$vals = array($vals);

		# Is this attribute a jpegPhoto?
		if ($ldapserver->isJpegPhoto($attr)) {

			switch ($side) {
				case 'src':
					# Don't draw the delete buttons if there is more than one jpegPhoto (phpLDAPadmin can't handle this case yet)
					draw_jpeg_photos($ldapserver,$dn_src,$attr,false);
					break;

				case 'dst':
					if ($ldapserver_dst->isReadOnly() || $ldapserver_dst->isAttrReadOnly($attr))
						draw_jpeg_photos($ldapserver,$dn_dst,$attr,false);
					else
						draw_jpeg_photos($ldapserver,$dn_dst,$attr,true);

					break;
			}

			# proceed to the next attribute
			echo '</td>'."\n";
			continue;
		}

		# Is this attribute binary?
		if ($ldapserver->isAttrBinary($attr)) {
			switch ($side) {
				case 'src':
					$href = sprintf("download_binary_attr.php?server_id=%s&dn=%s&attr=%s",$ldapserver->server_id,$encoded_dn_src,$attr);
					break;

				case 'dst':
					$href = sprintf("download_binary_attr.php?server_id=%s&dn=%s&attr=%s",$ldapserver->server_id,$encoded_dn_dst,$attr);
					break;
			}

			echo '<small>';
			echo _('Binary value');
			echo '<br />';

			if (count($vals) > 1)
				for ($i=1; $i<=count($vals); $i++)
					printf('<a href="%s&amp;value_num=%s"><img src="images/save.png" /> %s(%s)</a><br />',$href,$i,_('download value'),$i);
			else
				printf('<a href="%s"><img src="images/save.png" /> %s</a><br />',$href,_('download value'));

			if ($side == 'dst' && ! $ldapserver_dst->isReadOnly() && ! $ldapserver->isAttrReadOnly($attr))
				printf('<a href="javascript:deleteAttribute(\'%s\');" style="color:red;"><img src="images/trash.png" /> %s</a>',$attr,_('delete attribute'));

			echo '</small>';
			echo '</td>';

			continue;
		}

		# Note: at this point, the attribute must be text-based (not binary or jpeg)

		/*
		 * If this server is in read-only mode or this attribute is configured as read_only,
		 * simply draw the attribute values and continue.
		 */

		if ($side == 'dst' && ($ldapserver->isReadOnly() || $ldapserver->isAttrReadOnly($attr))) {
			if (is_array($vals)) {
				foreach ($vals as $i => $val) {
					if (trim($val) == '')
						printf('<span style="color:red">[%s]</span><br />',_('empty'));

					elseif (strcasecmp($attr,'userPassword') == 0 && $_SESSION[APPCONFIG]->GetValue('appearance','obfuscate_password_display'))
						echo preg_replace('/./','*',$val).'<br />';

					else
						echo htmlspecialchars($val).'<br />';
				}

			# @todo: redundant - $vals is always an array.
			} else {
				if (strcasecmp($attr,'userPassword') == 0 && $_SESSION[APPCONFIG]->GetValue('appearance','obfuscate_password_display'))
					echo preg_replace('/./','*',$vals).'<br />';
				else
					echo $vals.'<br />';
			}
			echo '</td>';
			continue;
		}

		# Is this a userPassword attribute?
		if (! strcasecmp($attr,'userpassword')) {
			$user_password = $vals[0];
			$enc_type = get_enc_type($user_password);

			# Set the default hashing type if the password is blank (must be newly created)
			if ($user_password == '') {
				$enc_type = get_default_hash($server_id);
			}

			if ($side == 'dst') {
				printf('<input type="hidden" name="old_values[userpassword]" value="%s" />',htmlspecialchars($user_password));
				echo '<!-- Special case of enc_type to detect changes when user changes enc_type but not the password value -->';
				printf('<input size="38" type="hidden" name="old_enc_type" value="%s" />',$enc_type == '' ? 'clear' : $enc_type);
			}

			if (obfuscate_password_display($enc_type))
				echo htmlspecialchars(preg_replace('/./','*',$user_password));
			else
				echo htmlspecialchars($user_password);

			echo '<br />';

			if ($side == 'dst') {
				printf('<input style="width: 260px" type="password" name="new_values[userpassword]" value="%s" />',htmlspecialchars($user_password));
				echo enc_type_select_list($enc_type);

			}

			echo '<br />';
			?>
				<script type="text/javascript" language="javascript">
				<!--
				function passwordComparePopup()
				{
					mywindow = open('password_checker.php','myname','resizable=no,width=450,height=200,scrollbars=1');
					mywindow.location.href = 'password_checker.php?hash=<?php echo base64_encode($user_password); ?>&base64=true';
					if (mywindow.opener == null)
					  mywindow.opener = self;
				}
				-->
				</script>
			<?php

			printf('<small><a href="javascript:passwordComparePopup()">%s</a></small>',_('Check password'));

			echo '</td>';
			continue;
		}

		# Is this a boolean attribute?
		if ($ldapserver->isAttrBoolean($attr)) {
			$val = $vals[0];

			if ($side = 'dst') {
				printf('<input type="hidden" name="old_values[%s]" value="%s" />',htmlspecialchars($attr),htmlspecialchars($val));

				printf('<select name="new_values[%s]">',htmlspecialchars($attr));
				printf('<option value="TRUE"%s>%s</option>',$val == 'TRUE' ? ' selected' : '',_('true'));
				printf('<option value="FALSE"%s>%s</option>',$val == 'FALSE' ? ' selected' : '',_('false'));
				printf('<option value="">(%s)</option>',_('none, remove value'));
				echo '</select>';
			}

			echo '</td>';
			continue;
		}

		# End of special case attributes (non plain text).
		foreach ($vals as $i => $val) {

			if ($side == 'dst') {
				$input_name = sprintf('new_values[%s][%s]',htmlspecialchars($attr),$i);

				/* We smack an id="..." tag in here that doesn't have [][] in it to allow the
				* draw_chooser_link() to identify it after the user clicks.*/
				$input_id = sprintf('"new_values_%s_%s',htmlspecialchars($attr),$i);

				echo '<!-- The old_values array will let update.php know if the entry contents changed
				     between the time the user loaded this page and saved their changes. -->';
				printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />',htmlspecialchars($attr),$i,htmlspecialchars($val));
			}

			# Is this value is a structural objectClass, make it read-only
			if (0 == strcasecmp($attr,'objectClass')) {

				printf('<a title="%s" href="cmd.php?cmd=schema&amp;server_id=%s&amp;view=objectClasses&amp;viewvalue=%s"><img src="images/info.png" /></a>',
					_('View the schema description for this objectClass'),$ldapserver->server_id,htmlspecialchars($val));

				$schema_object = $ldapserver->getSchemaObjectClass($val);

				if ($schema_object->getType() == 'structural') {
					printf('%s <small>(<acronym title="%s">%s</acronym>)</small><br />',
						$val,_('This is a structural ObjectClass and cannot be removed.'),_('structural'));

					if ($side == 'dst')
						printf('<input type="hidden" name="%s" id="%s" value="%s" />',$input_name,$input_id,htmlspecialchars($val));

					continue;
				}
			}

			if (is_dn_string($val) || $ldapserver->isDNAttr($attr))
				printf('<a title="%s" href="cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s"><img style="vertical-align: top" src="images/go.png" /></a>',
					sprintf(_('Go to %s'),htmlspecialchars($val)),$ldapserver->server_id,rawurlencode($val));

			elseif (is_mail_string($val))
				printf('<a href="mailto:%s><img style="vertical-align: center" src="images/mail.png" /></a>',htmlspecialchars($val));

			elseif (is_url_string($val))
				printf('<a href="%s" target="new"><img style="vertical-align: center" src="images/dc.png" /></a>',htmlspecialchars($val));

			if ($ldapserver->isMultiLineAttr($attr,$val)) {
				if ($side == 'dst')
					printf('<textarea class="value" rows="3" cols="30" name="%s" id="%s">%s</textarea>',$input_name,$input_id,htmlspecialchars($val));
				else
					echo htmlspecialchars($val);

			} else {
				if ($side == 'dst')
					printf('<input type="text" class="value" name="%s" id="%s" value="%s" />',$input_name,$input_id,htmlspecialchars($val));
				else
					echo htmlspecialchars($val);
			}

			# draw a link for popping up the entry browser if this is the type of attribute that houses DNs.
			if ($ldapserver->isDNAttr($attr))
				draw_chooser_link("edit_form.$input_id",false);

			echo '<br />';
		}

		echo '</td>';
	} /* end foreach value */

	echo '</tr>';

	# Draw the "add value" link under the list of values for this attributes
	if (! $ldapserver_dst->isReadOnly()) {

		# First check if the required objectClass is in this DN
		$isOK = 0;
		$src_oclass = array();
		$attr_object = $ldapserver_dst->getSchemaAttribute($attr,$dn_dst);
		foreach ($attr_object->used_in_object_classes as $oclass) {
			if (in_array(strtolower($oclass),arrayLower($attrs_dst['objectClass']))) {
				$isOK = 1;
				break;

			} else {
				# Find oclass that the source has that provides this attribute.
				if (in_array($oclass,$attrs_src['objectClass']))
					$src_oclass[] = $oclass;
			}
		}

		echo '<tr><td colspan=2></td><td colspan=2>&nbsp;</td><td>&nbsp;</td><td>';
		if (! $isOK) {

			if (count($src_oclass) == 1)
				$add_href = sprintf('cmd.php?cmd=add_oclass_form&amp;server_id=%s&amp;dn=%s&amp;new_oclass=%s',
					$ldapserver_dst->server_id,$encoded_dn_dst,$src_oclass[0]);
			else
				$add_href = sprintf('cmd.php?cmd=add_value_form&amp;server_id=%s&amp;dn=%s&amp;attr=objectClass',
					$ldapserver_dst->server_id,$encoded_dn_dst);

			if ($attr == 'objectClass')
				printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',$add_href,_('Add ObjectClass and Attributes'),_('add value'));
			else
				printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',
					$add_href,sprintf(_('You need one of the following ObjectClass(es) to add this attribute %s.'),implode(" ",$src_oclass)),
					_('Add new ObjectClass'));

		} else {
			if (! $schema_attr_dst->getIsSingleValue() || (! isset($vals))) {

				$add_href = sprintf('cmd.php?cmd=add_value_form&amp;erver_id=%s&amp;dn=%s&amp;attr=%s',
					$ldapserver_dst->server_id,$encoded_dn_dst,rawurlencode($attr));

			printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',
					$add_href,sprintf(_('Add an additional value to attribute \'%s\''),$attr),_('add value'));
			}
		}
	}

	echo '</td></tr>';

	# Get the values and see if they are the same.
	if (isset($attrs_src[$attr]) && isset($attrs_dst[$attr]) && $attrs_src[$attr] === $attrs_dst[$attr])
		echo '<tr>';
	else
		echo '<tr class="updated"><td class="bottom" colspan="0">&nbsp;</td></tr>';

} /* End foreach ($attrs as $attr => $vals) */

if (! $ldapserver_dst->isReadOnly())
	printf('<tr><td colspan=3>&nbsp;</td><td colspan=3><center><input type="submit" value="%s" /></center></form></td></tr>',_('Save Changes'));

echo '</table>';

# If this entry has a binary attribute,we need to provide a form for it to submit when deleting it. */
?>

<script type="text/javascript" language="javascript">
//<!--
function deleteAttribute(attrName)
{
	if (confirm("<?php echo _('Really delete attribute'); ?> '" + attrName + "'?")) {
		document.delete_attribute_form.attr.value = attrName;
		document.delete_attribute_form.submit();
	}
}
//-->
</script>

<!-- This form is submitted by JavaScript when the user clicks "Delete attribute" on a binary attribute -->
<form name="delete_attribute_form" action="cmd.php?cmd=delete_attr" method="post">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver_dst->server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn_dst; ?>" />
	<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
</form>
