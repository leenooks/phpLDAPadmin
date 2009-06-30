<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/update_confirm.php,v 1.49.2.4 2008/12/12 12:20:22 wurley Exp $

/**
 * Takes the results of clicking "Save" in template_engine.php and determines which
 * attributes need to be updated (ie, which ones actually changed). Then,
 * we present a confirmation table to the user outlining the changes they
 * are about to make. That form submits directly to update.php, which
 * makes the change.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

/***************/
/* get entry   */ 
/***************/

$entry = array();
$entry['dn']['string'] = get_request('dn');
$entry['dn']['encode'] = rawurlencode($entry['dn']['string']);

if (! $entry['dn']['string'] || ! $ldapserver->dnExists($entry['dn']['string']))
	error(sprintf(_('The entry (%s) does not exist.'),htmlspecialchars($entry['dn']['string'])),'error','index.php');

$tree = get_cached_item($ldapserver->server_id,'tree');
$entry['ldap'] = null;
if ($tree) {
	$entry['ldap'] = $tree->getEntry($entry['dn']['string']);

	if (! $entry['ldap'])
		$tree->addEntry($entry['dn']['string']);

	$entry['ldap'] = $tree->getEntry($entry['dn']['string']);
}

if (! $entry['ldap'] || $entry['ldap']->isReadOnly())
	error(sprintf(_('The entry (%s) is in readonly mode.'),htmlspecialchars($entry['dn']['string'])),'error','index.php');

/***************/
/* old values  */ 
/***************/

$entry['values']['old'] = array();
foreach ($entry['ldap']->getAttributes() as $old_attr) {
	$name = $old_attr->getName();
	$entry['values']['old'][$name] = array();

	foreach ($old_attr->getValues() as $old_val) {
		if (strlen($old_val) > 0)
			$entry['values']['old'][$name][] = $old_val;
	}
}

/***************/
/* new values  */ 
/***************/

eval('$reader = new '.$_SESSION[APPCONFIG]->GetValue('appearance','entry_reader').'($ldapserver);');
$entry['ldap']->accept($reader);

$entry['values']['new'] = array();
foreach ($entry['ldap']->getAttributes() as $new_attr) {
	if ($new_attr->hasBeenModified()) {
		$name = $new_attr->getName();

		if (!isset($entry['values']['old'][$name]))
			$entry['values']['old'][$name] = array();

		$entry['values']['new'][$name] = array();

		foreach ($new_attr->getValues() as $i => $new_val) {
			if ($new_attr instanceof BinaryAttribute) {
				$n = $new_attr->getFileName($i);
				$p = $new_attr->getFilePath($i);
				$new_val = md5("$n|$p");
			}

			if (strlen($new_val) > 0)
				$entry['values']['new'][$name][] = $new_val;
		}
	}
}

/************************/
/* objectClass deletion */
/************************/

$oc_to_delete = array();
$attr_to_delete = array();

// if objectClass attribute is modified
if (isset($entry['values']['new']['objectClass'])) {
	if (!isset($entry['values']['old']['objectClass']))
		error(_('An entry should have one structural objectClass.'),'error','index.php');

	// deleted objectClasses
	foreach ($entry['values']['old']['objectClass'] as $oldOC) {
		if (!in_array($oldOC, $entry['values']['new']['objectClass'])) {
			$oc_to_delete[] = $oldOC;
		}
	}
	// search the attributes used by each deleted objecClass
	// we must maybe delete these attributes
	foreach ($oc_to_delete as $oc) {
		$soc = $ldapserver->getSchemaObjectClass($oc);
		if ($soc) {
			$ocs = $ldapserver->SchemaObjectClasses();
			$ma = $soc->getMustAttrs($ocs);
			foreach ($ma as $a) {
				if (!isset($attr_to_delete[$a->getName()])) {
					$attr_to_delete[$a->getName()] = $a;
				}
			}
			$ma = $soc->getMayAttrs($ocs);
			foreach ($ma as $a) {
				if (!isset($attr_to_delete[$a->getName()])) {
					$attr_to_delete[$a->getName()] = $a;
				}
			}
		}
	}
	// if an attribute is still used by an objectClass we don't delete,
	// we don't delete this attribute
	foreach ($attr_to_delete as $name => $ad) {
		$found = false;
		$at = $ldapserver->getSchemaAttribute($name);
		foreach ($at->getUsedInObjectClasses() as $oc) {
			if (in_array($oc, $entry['values']['new']['objectClass'])) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			foreach ($at->getRequiredByObjectClasses() as $oc) {
				if (in_array($oc, $entry['values']['new']['objectClass'])) {
					$found = true;
					break;
				}
			}
		}
		if ($found) {
			unset($attr_to_delete[$name]);
		} else {
			if (isset($entry['values']['old'][$name]) && (count($entry['values']['old'][$name]) > 0)) {
				$found = true;
			} else {
				foreach ($entry['values']['new'] as $attr_name => $attr_values) {
					if ($name == $attr_name) {
						$found = true;
						break;
					}
				}
			}
			if (!$found) {
				unset($attr_to_delete[$name]);
			} else {
				$entry['values']['new'][$name] = array();
				$attr_to_delete[$name] = $name;
			}
		}
	}
}

/****************/
/* update array */ 
/****************/

eval('$writer = new '.$_SESSION[APPCONFIG]->GetValue('appearance','entry_writer').'($ldapserver);');
$writer->draw('Title',$entry['ldap']);
$writer->draw('Subtitle',$entry['ldap']);

echo "\n\n";

run_hook('pre_update_array_processing',
	array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'old_values'=>$entry['values']['old'],'new_values'=>$entry['values']['new']));

/***************/
/* confirm     */ 
/***************/
	
if (count($entry['values']['new']) > 0) {
	echo '<br />';
	echo '<center>';
	echo _('Do you want to make these changes?');
	echo '<br /><br />';

	# <!-- Commit button and acompanying form -->
	echo "\n\n";
	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="update" />';
	echo "\n";
	echo '<table class="result_table">';
	echo "\n";

	printf('<tr class="heading"><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
		_('Attribute'),_('Old Value'),_('New Value'),_('Skip'));

	echo "\n\n";
	$counter = 0;

	foreach ($entry['values']['new'] as $attr => $new_val) {
		$counter++;

		printf('<tr class="%s">',$counter%2 ? 'even' : 'odd');
		echo '<td><b>';
		echo $_SESSION[APPCONFIG]->getFriendlyHTML($attr);
		echo '</b></td>';
		echo '<td><span style="white-space: nowrap;">';

		if (strcasecmp($attr,'userPassword') == 0) {
			foreach ($entry['values']['old'][$attr] as $key => $value) {
				if (obfuscate_password_display(get_enc_type($entry['values']['old'][$attr][$key])))
					echo preg_replace('/./','*',$entry['values']['old'][$attr][$key]).'<br />';
				else
					echo nl2br(htmlspecialchars($entry['values']['old'][$attr][$key])).'<br />';
			}

		} elseif (is_array($entry['values']['old'][$attr]))
			foreach ($entry['values']['old'][$attr] as $v)
				echo nl2br(htmlspecialchars($v)).'<br />';

		else
			echo nl2br(htmlspecialchars($entry['values']['old'][$attr])).'<br />';

		echo '</span></td>';
		echo '<td><span style="white-space: nowrap;">';

		# Is this a multi-valued attribute?
		if (is_array($new_val)) {
			if (strcasecmp($attr,'userPassword') == 0) {
				foreach ($entry['values']['new'][$attr] as $key => $value) {
					if (isset($new_val[$key])) {
						if (obfuscate_password_display(get_enc_type($new_val[$key])))
							echo preg_replace('/./','*',$new_val[$key]).'<br />';
						else
							echo htmlspecialchars($new_val[$key]).'<br />';
					}
				}

			} else {

				foreach ($new_val as $i => $v) {
						echo nl2br(htmlspecialchars($v)).'<br />';
				}
			}

			if (! $new_val) {
				printf('<span style="color: red">%s</span>',_('[attribute deleted]'));
			}

		} elseif ($new_val == '')
				printf('<span style="color: red">%s</span>',_('[attribute deleted]'));

		echo '</span></td>';

		$input_disabled = '';
		if (in_array($attr, $attr_to_delete)) $input_disabled = 'disabled="disabled"';
		$input_onclick = '';
		if ($attr == 'objectClass' && (count($attr_to_delete) > 0)) {
			$input_onclick = 'onclick="if (this.checked) {';
			foreach ($attr_to_delete as $ad_name) {
				$input_onclick .= "document.forms[0].elements['skip_array[$ad_name]'].disabled = false;";
				$input_onclick .= "document.forms[0].elements['skip_array[$ad_name]'].checked = true;";
			}
			$input_onclick .= '} else {';
			foreach ($attr_to_delete as $ad_name) {
				$input_onclick .= "document.forms[0].elements['skip_array[$ad_name]'].checked = false;";
				$input_onclick .= "document.forms[0].elements['skip_array[$ad_name]'].disabled = true;";
			}
			$input_onclick .= '}"';
		}
 		printf('<td><input name="skip_array[%s]" type="checkbox" %s %s/></td>',htmlspecialchars($attr),$input_disabled,$input_onclick);
		echo '</tr>'."\n\n";
	}
	echo '</table>';

	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',$entry['dn']['string']);

	foreach ($entry['values']['new'] as $attr => $val) {
		if (count($val) > 0) {
			if (is_array($val)) {
				foreach($val as $i => $v)
					printf('<input type="hidden" name="update_array[%s][%s]" value="%s" />',
						htmlspecialchars($attr),$i,htmlspecialchars($v));
			} else {
				printf('<input type="hidden" name="update_array[%s]" value="%s" />',
					htmlspecialchars($attr),htmlspecialchars($val));
			}
		} else {
			printf('<input type="hidden" name="update_array[%s]" value="" />',
				htmlspecialchars($attr));
		}
	}

	echo '<br />';
	printf('<input type="submit" value="%s" />',_('Commit'));
	printf('<input type="submit" name="cancel" value="%s" />',_('Cancel'));
	echo '</form>';

	if (count($attr_to_delete) > 0) {
		echo '<table class="result_table"><tr>';
		printf('<td class="heading">%s%s</td>',_('The deletion of objectClass(es)'),_(':'));
		printf('<td class="value"><b>%s</b></td>',implode('</b>, <b>', $oc_to_delete));
		echo '</tr><tr>';
		printf('<td class="heading">%s%s</td>',_('will delete the attribute(s)'),_(':'));
		echo '<td class="value"><b>';
		$i = 0;
		foreach ($attr_to_delete as $attr) {
			if ($i++ != 0) echo '</b>, <b>';
			echo $_SESSION[APPCONFIG]->getFriendlyHTML($attr);
		}
		echo '</b></td></tr></table>';
	}

	echo '</center>';

} else {
	echo '<center>';
	echo _('You made no changes');
	$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
		 $ldapserver->server_id,$entry['dn']['encode']);

	printf(' <a href="%s">%s</a>.',htmlspecialchars($href),_('Go back'));
	echo '</center>';
}
?>
