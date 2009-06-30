<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/template_engine.php,v 1.26.2.40 2008/11/28 14:21:37 wurley Exp $

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

require './common.php';

if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$friendly_attrs = process_friendly_attr_table(); // @todo might not need this.
$pjs = array();

# REMOVE THSE @todo
$today = date('U');
$shadow_before_today_attrs = arrayLower(array('shadowLastChange','shadowMin'));
$shadow_after_today_attrs = arrayLower(array('shadowMax','shadowExpire','shadowWarning','shadowInactive'));
$shadow_format_attrs = array_merge($shadow_before_today_attrs,$shadow_after_today_attrs);
# END REMOVE

# If we have a DN, then this is to edit the entry.
if (isset($_REQUEST['dn'])) {

	$dn = $_GET['dn'];
	$decoded_dn = rawurldecode($dn);
	$encoded_dn = rawurlencode($decoded_dn);

	if (! $ldapserver->haveAuthInfo())
		pla_error(_('Not enough information to login to server. Please check your configuration.'));

	$ldapserver->dnExists(dn_escape($dn))
		or pla_error(sprintf(_('No such entry: %s'),pretty_print_dn($dn)));

	$rdn = get_rdn($dn);
	$attrs = $ldapserver->getDNAttrs($dn,false,$config->GetValue('deref','view'));

	$modified_attrs = isset($_REQUEST['modified_attrs']) ? $_REQUEST['modified_attrs'] : false;
	$show_internal_attrs = isset($_REQUEST['show_internal_attrs']) ? true : false;

	# If an entry has more children than this, stop searching and display this amount with a '+'
	$max_children = 100;

} else {

	$dn = '';
	$rdn = '';
	$encoded_dn = '';

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

	printf('<center><h2>%s</h2></center>',$template['description']);
	echo "\n\n";

	if (isset($_REQUEST['nextpage']) && ! $_REQUEST['nextpage']) {
		$new_dn = sprintf('%s=%s,%s',$template['rdn'],$_REQUEST['form'][$template['rdn']],$_REQUEST['container']);

		echo '<form action="create.php" method="post">';
		printf('<input type="hidden" name="new_dn" value="%s" />',htmlspecialchars($new_dn));

	} else {
		echo '<form action="template_engine.php" method="post" id="template_form" name="template_form" enctype="multipart/form-data">';
	}

	if (isset($_REQUEST['form'])) {
		foreach ($_REQUEST['form'] as $attr => $value) {

			# Check for any with post actions.
			if (isset($template['attribute'][$attr]['post']) && $_REQUEST['page'] == $template['attribute'][$attr]['page']+1) {
				if (preg_match('/^=php\.(\w+)\((.*)\)$/',$template['attribute'][$attr]['post'],$matches)) {
					switch ($matches[1]) {
						case 'Password' :
							preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$matches[2],$matchall);

							$enc = $_REQUEST[$matchall[1][0]];
							$password = $_REQUEST['form'][$matchall[1][1]];
							if (trim($password)) {
								$value = password_hash($password,$enc);
								$_REQUEST['form'][$attr] = $value;
							}
							break;

						case 'SambaPassword' :
							$matchall = explode(',',$matches[2]);
							$attr = preg_replace('/%/','',$matchall[1]);

							# If we have no password, then dont hash nothing!
							if (! trim($_REQUEST['form'][$attr]))
								break;

							$sambapassword = new smbHash;

							switch ($matchall[0]) {
								case 'LM' : $value = $sambapassword->lmhash($_REQUEST['form'][$attr]);
									break;
								case 'NT' : $value = $sambapassword->nthash($_REQUEST['form'][$attr]);
									break;
								default :
									$value = null;
							}

							$_REQUEST['form'][$attr] = $value;
							break;

						case 'Join' :
							preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$matches[2],$matchall);
							$matchattrs = explode(',',$matches[2]);
							$char = $matchattrs[0];

							$values = array();
							foreach ($matchall[1] as $joinattr) {
								if (isset($_REQUEST['form'][$joinattr]))
									$values[] = $_REQUEST['form'][$joinattr];

								else if (isset($_REQUEST[$joinattr]))
									$values[] = $_REQUEST[$joinattr];

								else
									pla_error(sprintf(_('Your template is missing variable (%s)'),$joinattr));
							}

							$value = implode($char,$values);
							$_REQUEST['form'][$attr] = $value;
							break;

						default:
							pla_error(sprintf(_('Your template has an unknown post function (%s).'),$matches[1]));
					}
				}
			}

			if (is_array($value))
				foreach ($value as $item)
					printf('<input type="hidden" name="form[%s][]" value="%s" />',$attr,$item);
			else
				printf('<input type="hidden" name="form[%s]" value="%s" />',$attr,$value);
		}

		# Have we got a Binary Attribute?
		if (isset($_FILES['form']['name']) && is_array($_FILES['form']['name'])) {
			foreach ($_FILES['form']['name'] as $attr => $details) {
				if (is_uploaded_file($_FILES['form']['tmp_name'][$attr])) {
					$file = $_FILES['form']['tmp_name'][$attr];
					$f = fopen($file,'r');
					$binary_data = fread($f,filesize($file));
					fclose($f);

					// @todo: This may need to be implemented.
					//if (is_binary_option_required($ldapserver,$attr))
					//	$attr .= ';binary';

					$_SESSION['submitform'][$attr] = $binary_data;
					printf('<input type="hidden" name="form[%s]" value="" />',$attr);
				}
			}
		}
	}

	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="template" value="%s" />',htmlspecialchars($_REQUEST['template']));
	printf('<input type="hidden" name="object_classes" value="%s" />',rawurlencode(serialize(array_values($template['objectclass']))));
	printf('<input type="hidden" name="page" value="%s" />',$page+1);

	echo "\n\n";
	echo '<center>';
	echo '<table class="confirm" border="0">';

	echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
	echo "\n\n";
	echo '<tr>';

	if (isset($template['askcontainer']) && $template['askcontainer'] && $page == 1) {
		if (! (isset($template['regexp']) && isset($template['regexp']))) {

			echo '<td>&nbsp;</td>';
			echo '<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>';
			printf('<td><input type="text" name="container" size="40" value="%s" />&nbsp;',
				htmlspecialchars($_REQUEST['container']));
			draw_chooser_link('template_form.container');
			echo '</td></tr>';

			echo '<tr class="spacer"><td colspan="3"></td></tr>';

		} else {
			echo '<td>&nbsp;</td>';
			echo '<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>';
			printf('<td><input type="text" name="container" size="40" value="%s" disabled />',
				htmlspecialchars($_REQUEST['container']));
			printf('<input type="hidden" name="container" value="%s" /></td></tr>',htmlspecialchars($_REQUEST['container']));
			echo '<tr class="spacer"><td colspan="3"></td></tr>';
		}

	} else {
		printf('<td><input type="hidden" name="container" value="%s" /></td></tr>',htmlspecialchars($_REQUEST['container']));
	}

	$count = 0;
	$nextpage = 0;
	$mustitems = 0;

	foreach ($template['empty_attrs'] as $attr => $detail) {

		$mustitem = false;
		$verifyitem = false;
		$type = isset($detail['type']) ? $detail['type'] : 'text';

		if (! isset($detail['page']))
			$detail['page'] = 1;

		$size = isset($detail['size']) ? $detail['size'] : 20;
		$maxlength = isset($detail['maxlength']) ? $detail['maxlength'] : null;
		$rows = isset($detail['rows']) ? $detail['rows'] : null;
		$cols = isset($detail['cols']) ? $detail['cols'] : null;

		# Check that the page number is correct.
		if ($detail['page'] < $page && ! isset($attr[$attr])) {
			# ERROR: This attribute should be set by now.
			print "We should have set [$attr] by now.<BR>";

		} elseif ($detail['page'] == $page) {

			$count++;
			echo '<tr>';

			# Some conditional checking.
			# $detail['must'] & $detail['disable'] cannot be set at the same time.
			if (isset($detail['must']) && $detail['must'] && isset($detail['disable']) && $detail['disable'])
				pla_error(sprintf(_('Attribute [%s] is a MUST attribute, so it cannot be disabled.'),$attr));

			# If this attribute is disabled, go to the next one.
			if (isset($detail['disable']) && $detail['disable'])
				continue;

			# Evaluate our Default Value, if its a function call result.
			if (isset($detail['value'])) {

				if (is_array($detail['value'])) {

					# If value is an array, then it must a select list.
					$type = 'select';
					$defaultresult = sprintf('<select name="form[%s]" id="%%s" %%s %%s>',$attr);

					foreach ($detail['value'] as $key => $value) {
						if (preg_match('/^_KEY:/',$key))
							$key = preg_replace('/^_KEY:/','',$key);
						else
							$key = $value;

						$defaultresult .= sprintf('<option name="%s" value="%s" %s>%s</option>',$value,$key,
							((isset($detail['default']) && $detail['default'] == $key) ? 'selected' : ''),$value);
					}

					$defaultresult .= '</select>';
					$detail['value'] = $defaultresult;

				} else {
					$detail['value'] = $templates->EvaluateDefault($ldapserver,$detail['value'],$_REQUEST['container'],null,
						(isset($detail['default']) ? $detail['default'] : null));
				}

				#if the default has a select list, then change the type to select
				if (preg_match('/<select .*>/i',$detail['value']))
					$type = 'select';
			}

			# @todo: if value is a select list, then it cannot be hidden.

			# If this is a hidden attribute, then set its value.
			if (isset($detail['hidden']) && $detail['hidden']) {
				if (isset($detail['value'])) {
					printf('<input type="%s" name="form[%s]" id="%s" value="%s"/>','hidden',$attr,$attr,$detail['value']);
					continue;

				} else {
					pla_error(sprintf(_('Attribute [%s] is a HIDDEN attribute, however, it is missing a VALUE in your template.'),$attr));
				}
			}

			# This is a displayed attribute.
			# Flag it as a must attribute so that we do get a value.
			if (isset($detail['must']) && $detail['must'] &&
				! isset($detail['presubmit']) &&
				$type != 'select') {

				$mustitems++;
				$mustitem = true;
			}

			# Display the icon if one is required.
			if (isset($detail['icon']) && trim($detail['icon']))
				printf('<td><img src="%s" alt="Icon" /></td>',$detail['icon']);
			else
				printf('<td>&nbsp;</td>');

			echo '<td class="heading">';

			# Display the label.
			if (isset($detail['description']) && (trim($detail['description'])))
				printf('<acronym title="%s">%s</acronym>:',$detail['description'],$detail['display']);

			elseif (isset($detail['display']))
				printf('%s:',$detail['display']);

			else
				printf('%s:',_('No DISPLAY/DESCRIPTION attribute in template file'));

			echo '</td>';

			# Calculate the events.
			# @todo: Need to change js so that if a must attr is auto populated, it decrements the total and enables the submit.
			if (isset($detail['onchange'])) {
				if (is_array($detail['onchange'])) {
					foreach ($detail['onchange'] as $value)
						$templates->OnChangeAdd($ldapserver,$attr,$value);
				} else {
					$templates->OnChangeAdd($ldapserver,$attr,$detail['onchange']);
				}
			}

			# Display the input box.
			echo '<td>';

			# Is this a binary attribute
			if ($ldapserver->isAttrBinary($attr)) {
				printf('<input type="file" name="form[%s]" size="20" />',$attr);

			if (! ini_get('file_uploads'))
				printf('<br /><small><b>%s</b></small><br />',
					_('Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.'));

			else
				printf('<br /><small><b>%s: %s</b></small><br />',
					_('Maximum file size'),ini_get('upload_max_filesize'));

			} elseif (in_array($type,array('text','password'))) {
				printf('<input type="%s" size="%s" name="form[%s]%s" id="%s" value="%s" %s%s%s />',
					$type,$size,$attr,(isset($detail['array']) && ($detail['array'] > 1) ? '[]' : ''),$attr,
					(isset($detail['value']) ? $detail['value'] : ''),
					"onBlur=\"fill('$attr', this.value);\"",
					(isset($detail['disable']) ? 'disabled' : ''),
					($maxlength ? sprintf(' maxlength="%s" ',$maxlength) : ''));

			} elseif ($type == 'textarea') {
				printf('<textarea size="%s" name="form[%s]%s" id="%s" value="%s" cols="%s" rows="%s" %s%s ></textarea>',
					$size,$attr,(isset($detail['array']) && ($detail['array'] > 1) ? '[]' : ''),$attr,
					(isset($detail['value']) ? $detail['value'] : ''),
					($cols ? $cols : 35),
					($rows ? $rows : 4),
					"onBlur=\"fill('$attr', this.value);\"",
					(isset($detail['disable']) ? 'disabled' : ''));

			} elseif ($type == 'select') {
				printf($detail['value'],$attr,
					"onBlur=\"fill('$attr', this.value);\"",
					(isset($detail['disable']) ? 'disabled' : ' '));
			}

			# Disabled items dont get submitted.
			# @todo need to add some js to enable them on submit, or add them as hidden items.

			if ($mustitem)
				echo '&nbsp;*';

			# Do we have a helper, and is it configured for the side.
			if (isset($detail['helper']) && isset($detail['helper']['location'])
				&& $detail['helper']['location'] == 'side' && isset($detail['helper']['value'])) {

				printf('&nbsp;%s',$templates->HelperValue($detail['helper']['value'],
					(isset($detail['helper']['id']) ? $detail['helper']['id'] : ''),$_REQUEST['container'],$ldapserver,null,
					isset($detail['helper']['default']) ? $detail['helper']['default'] : ''));
			}

			if (isset($detail['hint']) && (trim($detail['hint'])))
				printf('&nbsp;<span class="hint">(hint: %s)</span></td>',$detail['hint']);
			else
				echo '</td>';

			echo '</tr>'."\n";

			# Do we have a verify attribute?
			if (isset($detail['verify']) && ($detail['verify'])) {

				$verifyitems = true;

				echo '<tr><td>&nbsp;</td><td class="heading">';

				# Display the label.
				if (isset($detail['description']) && (trim($detail['description'])))
					printf('<acronym title="%s">%s %s</acronym>:',_('Verify'),$detail['description'],$detail['display']);
				else
					printf('%s %s:',_('Verify'),$detail['display']);

				echo '</td><td>';

				if (in_array($type,array('text','password'))) {
					printf('<input type="%s" name="%s" id="%s" value="%s" %s/>',
						$type,$attr."V",$attr."V",(isset($detail['value']) ? $detail['value'] : ''),
						sprintf('onBlur="check(form.%s,form.%sV)"',$attr,$attr));
				}

				echo '</td></tr>'."\n";
			}

			# Is this a multiarray input?
			if (isset($detail['array']) && ($detail['array'])) {
				for ($i=2; $i <= $detail['array']; $i++) {
					echo '<tr><td>&nbsp;</td><td>&nbsp;</td>';

					printf('<td><input type="%s" name="form[%s][]" id="%s" value="%s" %s %s />',
						$type,$attr,$attr.$i,(isset($detail['value']) ? $detail['value'] : ''),
						"onBlur=\"fill('$attr', this.value);\"",
						isset($detail['disable']) ? 'disabled' : '');

					if (isset($detail['helper']) && isset($detail['helper']['location'])
						&& $detail['helper']['location'] == 'side' && isset($detail['helper']['value'])) {

						printf('&nbsp;%s',$templates->HelperValue($detail['helper']['value'],
							(isset($detail['helper']['id']) ? $detail['helper']['id'] : ''),$_REQUEST['container'],$ldapserver,$i));
					}
					echo '</td></tr>'."\n";
				}
			}

			# Do we have a helper.
			# Side helpers are handled above.
			# @todo: Helpers must have an onchange or onsubmit.
			# @todo: Helpers must have an id field.
			# @todo: Helpers must have an post field.

			if (isset($detail['helper']) && (! isset($detail['helper']['location']) || $detail['helper']['location'] != 'side')) {

				echo '<tr><td>&nbsp;</td>';
				echo '<td class="heading">';

				# Display the label.
				if (isset($detail['helper']['description']) && (trim($detail['helper']['description'])))
					printf('<acronym title="%s">%s</acronym>:',$detail['helper']['description'],$detail['helper']['display']);
				else
					printf('%s:',$detail['helper']['display']);

				echo '</td>';

				printf('<td>%s</td>',$templates->HelperValue($detail['helper']['value'],$detail['helper']['id']));
			}

			if (isset($detail['spacer']) && $detail['spacer'])
				echo '<tr class="spacer"><td colspan="3"></td></tr>';

		# See if there are any future ones - if there are and we dont ask any this round, then thats an error.
		} elseif ($detail['page'] > $page) {
			$nextpage++;
		}
	}

	# @todo: Proper error message required.
	if ($nextpage && ! $count)
		pla_error(sprintf(_('We are missing a page for [%s] attributes.'),$nextpage));

	# If there is no count, display the summary
	if (! $count) {
			printf('<tr><td><img src="%s" alt="Create" /></td><td><span class="x-small">%s :</span></td><td><b>%s</b></td></tr>',
				$template['icon'],_('Create Object'),htmlspecialchars($new_dn));

			echo '<tr class="spacer"><td colspan="3"></td></tr>';

			$counter = 0;
			foreach ($_REQUEST['form'] as $attr => $value) {

				# Remove blank attributes.
				if (! is_array($_REQUEST['form'][$attr]) && trim($_REQUEST['form'][$attr]) == '') {
					unset($_REQUEST['form'][$attr]);
					continue;
				}

				$attrs[] = $attr;
				printf('<tr class="%s"><td colspan=2>',($counter++%2==0?'even':'odd'));
				printf('<input type="hidden" name="attrs[]" value="%s" />',$attr);

				if (is_array($value))
					foreach ($value as $item) {
						if ($item && ! isset($unique[$item])) {
							$unique[$item] = 1;
							printf('<input type="hidden" name="vals[%s][]" value="%s" />',
								array_search($attr,$attrs),$item);
							printf('%s</td><td><b>%s</b></td></tr>',$attr,htmlspecialchars($item));
						}
					}

				else {
					$display = $value;
					if (isset($template['attribute'][$attr]['type']) && $template['attribute'][$attr]['type'] == 'password') {
						$enc = (isset($_REQUEST['enc'])) ? $_REQUEST['enc'] : get_enc_type($value);
						if (obfuscate_password_display($enc))
							$display = '********';
					}

					printf('<input type="hidden" name="vals[]" value="%s" />',$value);
					printf('%s</td><td><b>%s</b></td></tr>',$attr,htmlspecialchars($display));
				}

			}

			if (isset($_SESSION['submitform'])) {
				echo '<tr class="spacer"><td colspan="3"></td></tr>';
				foreach (array_keys($_SESSION['submitform']) as $attr) {

					printf('<tr class="%s"><td colspan=2>%s</td><td><b>%s</b>',
						($counter++%2==0?'even':'odd'),$attr,_('Binary value not displayed'));
					printf('<input type="hidden" name="attrs[]" value="%s" /></td></tr>',$attr);
				}
			}
	}

	echo '<tr class="spacer"><td colspan="3"></td></tr>';

	if (! $nextpage && isset($_REQUEST['nextpage']) && ! $_REQUEST['nextpage']) {

		# Look for any presubmit functions.
		foreach ($template['empty_attrs'] as $attr => $detail) {
			if (isset($template['attribute'][$attr]['presubmit']) && ! isset($_REQUEST['form'][$attr])) {
				printf('<tr class="%s"><td colspan=2>%s</td><td><b>%s</b></td></tr>',
					($counter++%2==0?'even':'odd'),$attr,htmlspecialchars(_('(Auto evaluated on submission.)')));
				printf('<input type="hidden" name="presubmit[]" value="%s" />',$attr);
			}
		}

		printf('<tr><td colspan="3"><center><br /><input type="submit" name="submit" value="%s" %s /></center></td></tr>',
			_('Create Object'),$mustitems ? 'disabled' : '');

	} elseif ($nextpage) {
		printf('<tr><td colspan="3"><center><br /><input type="submit" name="submit" value="%s" %s /></center></td></tr>',
			_('Next Page'),$mustitems ? 'disabled' : '');

	} else {
		printf('<tr><td colspan="3"><center><br /><input type="submit" name="submit" value="%s" %s /></center></td></tr>',
			_('Proceed >>'),$mustitems ? 'disabled' : '');
	}

	echo '</table>';
	echo '</center>';

	if ($mustitems)
		printf('<input type="hidden" name="mustitems" value="%s" />',$mustitems);

	printf('<input type="hidden" name="nextpage" value="%s" />',$nextpage);
	echo '</form>'."\n\n";
	printf('<span class="hint">'._('Page %d').'</span>',$page);
	echo "\n\n";

	if ($mustitems) {
		$jstext = '
<script type="text/javascript" language="javascript">
	var reduced = new Array();
	var form = document.getElementById("template_form");

	function reduceMust(attrname){
		attr = document.getElementById(attrname);
		if (attr.value.length > 0) {
			if (! reduced[attrname]) {
				reduced[attrname] = 1;
				form.mustitems.value--;
			}
			if (form.mustitems.value < 0) {
				form.mustitems.value = 0;
			}

			if (form.mustitems.value == 0) {
				form.submit.disabled = false;
			}
		} else {
			if (reduced[attrname]) {
				reduced[attrname] = 0;
				form.mustitems.value++;
			}
			if (form.mustitems.value > 0) {
				form.submit.disabled = true;
			}
		}
	}

	var attrTrace;

	function fill(id, value) {
		attrTrace = new Array();
		fillRec(id, value);
	}

	function fillRec(id, value) {
	if (attrTrace[id] == 1)
		return;
	else {
		attrTrace[id] = 1;
		document.getElementById(id).value = value;
		// here comes template-specific implementation, generated by php
		if (false) {}';

		foreach ($template['empty_attrs'] as $attr => $detail) {
			$jstext .= "\t\t\telse if (id == '$attr') {\n";
			if (isset($detail['must']))
				$jstext .= "\t\t\t\treduceMust('$attr');\n";
			$hash = $templates->getJsHash();
			if (isset($hash['autoFill'.$attr])) {
				$jstext .= $hash['autoFill'.$attr];
			}
			$jstext .= "\t\t\t}\n";
		}
		$jstext .= '}}</script>';
		$pjs[] = $jstext;
	}

	if (isset($verifyitems) && $verifyitems) {
		//@todo: Return focus to the first item.
		$pjs[] = '
<script type="text/javascript" language="javascript">
function check(a,b){
	if (a.value != b.value){
		alert(\'Values dont compare\')
	}
}
</script>';
	}

	# User needs to submit form to continue.
	foreach ($pjs as $script)
		echo $script;

	die();
}

if (! isset($template))
	$template['attrs'] = $attrs;

# If we get here - we are displaying/editing the entry.
# Sort these entries.
uksort($template['attrs'],'sortAttrs');

$js_date_attrs = $config->GetValue('appearance','date_attrs');
$js[] = sprintf('<script type="text/javascript" language="javascript">var defaults = new Array();var default_date_format = "%s";</script>',$config->GetValue('appearance','date'));

foreach ($template['attrs'] as $attr => $vals) {
	if (! is_array($vals))
		$vals = array($vals);

	flush();

	$schema_attr = $ldapserver->getSchemaAttribute($attr,$dn);
	if ($schema_attr)
		$attr_syntax = $schema_attr->getSyntaxOID();
	else
		$attr_syntax = null;

	if (! strcasecmp($attr,'dn'))
		continue;

	# has the config.php specified that this attribute is to be hidden or shown?
	if ($ldapserver->isAttrHidden($attr))
		continue;

	# Setup the $attr_note, which will be displayed to the right of the attr name (if any)
	$attr_note = '';

	# is there a user-friendly translation available for this attribute?
	if (isset($friendly_attrs[ strtolower($attr) ])) {
		$attr_display = $friendly_attrs[ strtolower($attr) ];
		$attr_note = "<acronym title=\"" . sprintf(_('Note: \'%s\' is an alias for \'%s\''),$attr_display,$attr) . "\">alias</acronym>";

	} else {
		$attr_display = $attr;
	}

	# is this attribute required by an objectClass?
	$required_by = '';
	if ($schema_attr)
		foreach ($schema_attr->getRequiredByObjectClasses() as $required) {
			if (isset($attrs['objectClass']) && ! is_array($attrs['objectClass']))
				$attrs['objectClass'] = array($attrs['objectClass']);

			if (isset($attrs['objectClass']) && in_array(strtolower($required),arrayLower($attrs['objectClass'])))
				$required_by .= $required . ' ';

			# It seems that some LDAP servers (Domino) returns attributes in lower case?
			elseif (isset($attrs['objectclass']) && in_array(strtolower($required),arrayLower($attrs['objectclass'])))
				$required_by .= $required . ' ';
		}

	if ($required_by) {
		if (trim($attr_note))
			$attr_note .= ', ';

		$attr_note .= "<acronym title=\"" . sprintf(_('Required attribute for objectClass(es) %s'),$required_by) . "\">" . _('required') . "</acronym>&nbsp;";
	}

	# is this attribute required because its the RDN
	if (preg_match("/^${attr}=/",$rdn)) {
		if (trim($attr_note))
			$attr_note .= ', ';

		$attr_note .= "&nbsp;<acronym title=\"" . _('This attribute is required for the RDN.') . "\">" . 'rdn' . "</acronym>&nbsp;";
	}

	if (is_array($modified_attrs) && in_array($attr,$modified_attrs))
		$is_modified_attr = true;
	else
		$is_modified_attr = false;

	if ($is_modified_attr)
		echo '<tr class="updated_attr">';
	else
		echo '<tr>';

	echo '<td class="attr">';

	$schema_href = sprintf('schema.php?server_id=%s&amp;view=attributes&amp;viewvalue=%s',
		$ldapserver->server_id,real_attr_name($attr));

	printf('<b><a title="'._('Click to view the schema definition for attribute type \'%s\'').'" href="%s">%s</a></b>',$attr,$schema_href,$attr_display);
	echo '</td>';

	echo '<td class="attr_note">';
	if ($attr_note)
		printf('<sup><small>%s</small></sup>',$attr_note);

	if ($ldapserver->isAttrReadOnly($attr))
		printf('<small>(<acronym title="%s">%s</acronym>)</small>',_('This attribute has been flagged as read only by the phpLDAPadmin administrator'),_('read only'));

	echo '</td>';
	echo '</tr>';

	if ($is_modified_attr)
		echo '<tr class="updated_attr">';
	else
		echo '<tr>';

	echo '<td class="val" colspan="2">';

	/*
	 * Is this attribute a jpegPhoto?
	 */
	if ($ldapserver->isJpegPhoto($attr)) {

		/* Don't draw the delete buttons if there is more than one jpegPhoto
		   (phpLDAPadmin can't handle this case yet) */
		if ($ldapserver->isReadOnly() || $ldapserver->isAttrReadOnly($attr))
			draw_jpeg_photos($ldapserver,$dn,$attr,false);
		else
			draw_jpeg_photos($ldapserver,$dn,$attr,true);

		# proceed to the next attribute
		echo '</td></tr>';

		if ($is_modified_attr)
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this attribute binary?
	 */
	if ($ldapserver->isAttrBinary($attr)) {
		$href = sprintf('download_binary_attr.php?server_id=%s&amp;dn=%s&amp;attr=%s',
			$ldapserver->server_id,$encoded_dn,$attr);

		echo '<small>';
		echo _('Binary value');

		if (! strcasecmp($attr,'objectSid'))
			printf(' (%s)',binSIDtoText($vals[0]));

		echo '<br />';

		if (count($vals) > 1) {
			for ($i=1; $i<=count($vals); $i++)
				printf('<a href="%s&amp;value_num=%s"><img src="images/save.png" alt="Save" /> %s(%s)</a><br />',
					$href,$i,_('download value'),$i);

		} else {
			printf('<a href="%s"><img src="images/save.png" alt="Save" /> %s</a><br />',$href,_('download value'));
		}

		if (! $ldapserver->isReadOnly() && ! $ldapserver->isAttrReadOnly($attr))
			printf('<a href="javascript:deleteAttribute(\'%s\');" style="color:red;"><img src="images/trash.png" alt="Trash" /> %s</a>',
				$attr,_('delete attribute'));

		echo '</small>';
		echo '</td>';
		echo '</tr>';

		if ($is_modified_attr)
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Note: at this point,the attribute must be text-based (not binary or jpeg)
	 */

	# If this is the userPassword attribute, add the javascript so we can call check password later.
	if (! strcasecmp($attr,'userPassword')) {
		$js[] = '
	<script type="text/javascript" language="javascript">
	<!--
		function passwordComparePopup(hash) {
			mywindow = open(\'password_checker.php\',\'myname\',\'resizable=no,width=450,height=200,scrollbars=1\');
			mywindow.location.href = \'password_checker.php?hash=\'+hash+\'&base64=true\';
			if (mywindow.opener == null) mywindow.opener = self;
		}
	-->
	</script>';

	 }

	/*
	 * If this server is in read-only mode or this attribute is configured as read_only,
	 * simply draw the attribute values and continue.
	 */
	if ($ldapserver->isReadOnly() || $ldapserver->isAttrReadOnly($attr) || (preg_match("/^${attr}=/",$rdn))) {
		if (is_array($vals)) {
			foreach ($vals as $i => $val) {
				if (trim($val) == '')
					printf('<span style="color:red">[%s]</span><br />',_('empty'));

				elseif (! strcasecmp($attr,'userPassword') && $config->GetValue('appearance','obfuscate_password_display')) {
					$user_password = $val;
					echo preg_replace('/./','*',$val).'<br />';

				} elseif (in_array(strtolower($attr),$shadow_format_attrs)) {
					$shadow_date = shadow_date($attrs,$attr);
					echo htmlspecialchars($val).'&nbsp;';
					echo '<small>';

					if (($today < $shadow_date) && in_array(strtolower($attr),$shadow_before_today_attrs))
						echo '<span style="color:red">'.htmlspecialchars("(".strftime($config->GetValue('appearance','date'),$shadow_date).")").'</span>';
					elseif ($today > $shadow_date && in_array(strtolower($attr),$shadow_after_today_attrs))
						echo '<span style="color:red">'.htmlspecialchars("(".strftime($config->GetValue('appearance','date'),$shadow_date).")").'</span>';
					else
						echo htmlspecialchars("(".strftime($config->GetValue('appearance','date'),shadow_date($attrs,$attr)).")");

					echo '</small>';

				} else {
		if (is_dn_string($val) || $ldapserver->isDNAttr($attr))

			if ($ldapserver->dnExists($val)) {
				printf('<a title="'._('Go to %s').
					'" href="template_engine.php?server_id=%s&amp;dn=%s"><img '.
					'style="vertical-align: top" src="images/go.png" alt="Go" '.
					'/>&nbsp;%s</a>&nbsp;',
					htmlspecialchars($val),$ldapserver->server_id,
					rawurlencode($val),dn_unescape($val));
			} else {
				printf('<a title="'._('DN not available %s').'"><img '.
					'style="vertical-align: top" src="images/nogo.png" alt="N/E" '.
					'/>&nbsp;%s</a>&nbsp;',
					htmlspecialchars($val),$ldapserver->server_id,
					rawurlencode($val),dn_unescape($val));
			}

		elseif (is_mail_string($val))
			printf('<img style="vertical-align: center" src="images/mail.png"'.
				' alt="Mail" />&nbsp;<a href="mailto:%s">%s</a>&nbsp;',
			 	htmlspecialchars($val),$val);

		elseif (is_url_string($val))
			printf('<img style="vertical-align: center" src="images/dc.png" '.
				'alt="URL" />&nbsp;<a href="%s" target="new">%s</a>&nbsp;',
				htmlspecialchars($val),$val);

		else
					echo htmlspecialchars($val).'<br />';

				}
			}
		}

		if (! strcasecmp($attr,'userPassword') && isset($user_password))
			printf('<small><a href="javascript:passwordComparePopup(\'%s\')">%s</a></small>',base64_encode($user_password),_('Check password...'));

		if (preg_match("/^${attr}=/",$rdn) &&
		 !($ldapserver->isReadOnly() || $ldapserver->isAttrReadOnly($attr)))
			printf('<small>(<a href="%s">%s</a>)</small>',$rename_href,_('rename'));

		echo '</td>';
		echo '</tr>';

		if ($is_modified_attr)
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this a userPassword attribute?
	 */
	if (0 == strcasecmp($attr,'userpassword')) {
		foreach ($vals as $user_password) {
			$enc_type = get_enc_type($user_password);

			# Set the default hashing type if the password is blank (must be newly created)
			if ($user_password == '')
				$enc_type = get_default_hash($ldapserver->server_id);

				printf('<input type="hidden" name="old_values[userpassword][]" value="%s" />',htmlspecialchars($user_password));
				echo '<!-- Special case of enc_type to detect changes when user changes enc_type but not the password value -->';
				printf('<input size="38" type="hidden" name="old_enc_type[]" value="%s" />',($enc_type == '' ? 'clear' : $enc_type));

			if (obfuscate_password_display($enc_type))
				echo htmlspecialchars(preg_replace('/./','*',$user_password));
			else
				echo htmlspecialchars($user_password);

			echo '<br />';
			printf('<input style="width: 260px" type="%s" name="new_values[userpassword][]" value="%s" />',
				(obfuscate_password_display($enc_type) ? 'password' : 'text'),htmlspecialchars($user_password));

			echo enc_type_select_list($enc_type);

			echo '<br />';
			printf('<small><a href="javascript:passwordComparePopup(\'%s\')">%s</a></small>',base64_encode($user_password),_('Check password...'));
			echo '<br />';
		}

		/* Draw the "add value" link under the list of values for this attributes */
		if (! $ldapserver->isReadOnly() && ($schema_attr = $ldapserver->getSchemaAttribute($attr,$dn)) &&
			! $schema_attr->getIsSingleValue()) {

			$add_href = sprintf('add_value_form.php?server_id=%s&amp;dn=%s&amp;attr=%s',
				$ldapserver->server_id,$encoded_dn,rawurlencode($attr));

			printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',
				$add_href,sprintf(_('Add an additional value to attribute \'%s\''),$attr),_('add value'));
		}

		echo '</td>';
		echo '</tr>';

		if ($is_modified_attr)
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this a boolean attribute?
	 */
	if ($ldapserver->isAttrBoolean($attr)) {
		$val = $vals[0];

		printf('<input type="hidden" name="old_values[%s][]" value="%s" />',htmlspecialchars($attr),htmlspecialchars($val));

		printf('<select name="new_values[%s][]">',htmlspecialchars($attr));
		printf('<option value="TRUE" %s>%s</option>',($val=='TRUE' ? ' selected' : ''),_('true'));
		printf('<option value="FALSE" %s>%s</option>',($val=='FALSE' ? ' selected' : ''),_('false'));
		printf('<option value="">(%s)</option>',_('none, remove value'));
		echo '</select>';
		echo '</td>';
		echo '</tr>';

		if ($is_modified_attr)
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * Is this a date type attribute?
	 */
	if (in_array_ignore_case($attr,array_keys($js_date_attrs))) {
		$val = $vals[0];

		printf('<input type="hidden" name="old_values[%s][]" value="%s" />',htmlspecialchars($attr),htmlspecialchars($val));
		printf('<span style="white-space: nowrap;"><input type="text" size="30" id="f_date_%s" name="new_values[%s][0]" value="%s" />&nbsp;',
			$attr,htmlspecialchars($attr),htmlspecialchars($val));
		draw_date_selector_link($attr);
		echo '</span></td>';
		echo '</tr>';
		$js[] = sprintf('<script type="text/javascript" language="javascript">defaults[\'f_date_%s\'] = \'%s\';</script>',$attr,$js_date_attrs[$attr]);

		if ($is_modified_attr)
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

		continue;
	}

	/*
	 * End of special case attributes (non plain text).
	 */

	/*
	 * This is a plain text attribute, to be displayed and edited in plain text.
	 */
	foreach ($vals as $i => $val) {

		$input_name = sprintf('new_values[%s][%s]',htmlspecialchars($attr),$i);
		/* We smack an id="..." tag in here that doesn't have [][] in it to allow the
		   draw_chooser_link() to identify it after the user clicks. */
		$input_id = sprintf('new_values_%s_%s',htmlspecialchars($attr),$i);

		/* The old_values array will let update.php know if the entry contents changed
		   between the time the user loaded this page and saved their changes. */

		printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />',
			htmlspecialchars($attr),$i,htmlspecialchars($val));

		# Is this value is a structural objectClass, make it read-only
		if (! strcasecmp($attr,'objectClass')) {

			printf('<a title="%s" href="schema.php?server_id=%s&amp;view=objectClasses&amp;viewvalue=%s"><img src="images/info.png" alt="Info" /></a>&nbsp;',
				_('View the schema description for this objectClass'),$ldapserver->server_id,strtolower(htmlspecialchars($val)));

			$schema_object = $ldapserver->getSchemaObjectClass($val);

			# This should be an object, but we'll test it anyway
			if (is_object($schema_object) && $schema_object->getType() == 'structural') {
				printf(' %s <small>(<acronym title="%s">%s</acronym>)</small><br />',
					$val,_('This is a structural ObjectClass and cannot be removed.'),_('structural'));
				printf('<input type="hidden" name="%s" id="%s" value="%s" />',$input_name,$input_id,htmlspecialchars($val));

				continue;
			}
		}

		if (is_dn_string($val) || $ldapserver->isDNAttr($attr))

			if ($ldapserver->dnExists($val)) {
				printf('<a title="'._('Go to %s').'" href="template_engine.php?server_id=%s&amp;dn=%s"><img style="vertical-align: top" src="images/go.png" alt="Go" /></a>&nbsp;',
					htmlspecialchars($val),$ldapserver->server_id,rawurlencode($val));
			} else {
				printf('<a title="'._('DN not available %s').'"><img style="vertical-align: top" src="images/nogo.png" alt="N/E" /></a>&nbsp;',
					htmlspecialchars($val),$ldapserver->server_id,rawurlencode($val));
			}

		elseif (is_mail_string($val))
			printf('<a href="mailto:%s"><img style="vertical-align: center" src="images/mail.png" alt="Mail" /></a>&nbsp;',htmlspecialchars($val));

		elseif (is_url_string($val))
			printf('<a href="%s" target="new"><img style="vertical-align: center" src="images/dc.png" alt="URL" /></a>&nbsp;',htmlspecialchars($val));

		if ($ldapserver->isMultiLineAttr($attr,$val))
			printf('<textarea class="val" rows="3" cols="50" name="%s" id="%s">%s</textarea>',$input_name,$input_id,htmlspecialchars(dn_unescape($val)));
		else
			printf('<input type="text" class="val" name="%s" id="%s" value="%s" />&nbsp;',$input_name,$input_id,htmlspecialchars(dn_unescape($val)));

		/* draw a link for popping up the entry browser if this is the type of attribute
		   that houses DNs. */
		if ($ldapserver->isDNAttr($attr))
			draw_chooser_link("edit_form.$input_id",false);

		echo '<br />';

		# If this is a gidNumber on a non-PosixGroup entry, lookup its name and description for convenience
		if (! strcasecmp($attr,'gidNumber') &&
			! in_array_ignore_case('posixGroup',$ldapserver->getDNAttr($dn,'objectClass'))) {

			$gid_number = $val;
			$search_group_filter = "(&(objectClass=posixGroup)(gidNumber=$val))";
			$group = $ldapserver->search(null,null,$search_group_filter,array('dn','description'));

			if (count($group) > 0) {
				echo '<br />';

				$group = array_pop($group);
				$group_dn = $group['dn'];
				$group_name = explode('=',get_rdn($group_dn));
				$group_name = $group_name[1];
				$href = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,urlencode($group_dn));

				echo '<small>';
				printf('<a href="%s">%s</a>',$href,htmlspecialchars($group_name));

				$description = isset($group['description']) ? $group['description'] : null;

				if (is_array($description)) {
					foreach ($description as $item)
						printf(' (%s)',htmlspecialchars($item));
				} else {
					printf(' (%s)',htmlspecialchars($description));
				}

				echo '</small>';
			}
		}

		# Show the dates for all the shadow attributes.
		if (in_array(strtolower($attr),$shadow_format_attrs)) {
			if (($shadow_date = shadow_date($attrs,$attr)) !== false) {
				echo '<br />';
				echo '<small>';

				if (($today < $shadow_date) && in_array(strtolower($attr),$shadow_before_today_attrs))
					echo '<span style="color:red">'.htmlspecialchars(strftime($config->GetValue('appearance','date'),$shadow_date)).'</span>';
				elseif ($today > $shadow_date && in_array(strtolower($attr),$shadow_after_today_attrs))
					echo '<span style="color:red">'.htmlspecialchars(strftime($config->GetValue('appearance','date'),$shadow_date)).'</span>';
				else
					echo htmlspecialchars(strftime($config->GetValue('appearance','date'),$shadow_date));

				echo '</small>';
			}
		}

	} /* end foreach value */

	/* Draw the "add value" link under the list of values for this attributes */
	if (! $ldapserver->isReadOnly() && ($schema_attr = $ldapserver->getSchemaAttribute($attr,$dn)) &&
		! $schema_attr->getIsSingleValue()) {

		$add_href = sprintf('add_value_form.php?server_id=%s&amp;dn=%s&amp;attr=%s',
			$ldapserver->server_id,$encoded_dn,rawurlencode($attr));

		printf('<div class="add_value">(<a href="%s" title="%s">%s</a>)</div>',
			$add_href,sprintf(_('Add an additional value to attribute \'%s\''),$attr),_('add value'));
	}

	echo '</td>';
	echo '</tr>';

	if ($is_modified_attr)
		echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';

	echo "\n";
	flush();

} /* End foreach ($attrs as $attr => $vals) */

if (! $ldapserver->isReadOnly())
	printf('<tr><td colspan="2"><center><input type="submit" value="%s" /></center></td></tr></table></form>',
		_('Save Changes'));
else
	printf('</table>');
?>

<!-- This form is submitted by JavaScript when the user clicks "Delete attribute" on a binary attribute -->
<form name="delete_attribute_form" action="delete_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
	<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
</form>


<?php
	foreach ($js as $script)
		echo $script;
?>
<!-- If this entry has a binary attribute, we need to provide a form for it to submit when deleting it. -->

<script type="text/javascript" language="javascript">
<!--
function deleteAttribute(attrName)
{
	if (confirm("<?php echo _('Really delete attribute'); ?> '" + attrName + "'?")) {
		document.delete_attribute_form.attr.value = attrName;
		document.delete_attribute_form.submit();
	}
}
-->
</script>
</body>
</html>
