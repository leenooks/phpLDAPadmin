<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_value_form.php,v 1.39 2007/12/15 07:50:30 wurley Exp $

/**
 * Displays a form to allow the user to enter a new value to add
 * to the existing list of values for a multi-valued attribute.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));

# The DN and ATTR we are working with.
$entry['dn']['encode'] = get_request('dn','GET',true);
$entry['dn']['string'] = urldecode($entry['dn']['encode']);
$entry['dn']['html'] = htmlspecialchars($entry['dn']['string']);
$entry['attr']['string'] = get_request('attr','GET',true);
$entry['attr']['encode'] = rawurlencode($entry['attr']['string']);
$entry['attr']['html'] = htmlspecialchars($entry['attr']['string']);

if (! is_null($entry['dn']['string']))
	$entry['rdn']['string'] = get_rdn($entry['dn']['string']);
else
	$entry['rdn']['string'] = null;
$entry['rdn']['html'] = htmlspecialchars($entry['rdn']['string']);

/***************/
/* get entry   */ 
/***************/
	
if (! $entry['dn']['string'] || ! $ldapserver->dnExists($entry['dn']['string']))
	pla_error(sprintf(_('The entry (%s) does not exist.'),$entry['dn']['html']),null,-1,true);

$tree = get_cached_item($ldapserver->server_id,'tree');
$entry['ldap'] = null;
if ($tree) {
	$entry['ldap'] = $tree->getEntry($entry['dn']['string']);

	if (! $entry['ldap'])
		$tree->addEntry($entry['dn']['string']);

	$entry['ldap'] = $tree->getEntry($entry['dn']['string']);
}

// define the template of the entry if possible
eval('$reader = new '.$_SESSION['plaConfig']->GetValue('appearance','entry_reader').'($ldapserver);');
$reader->visit('Start', $entry['ldap']);

if (! $entry['ldap'] || $entry['ldap']->isReadOnly())
	pla_error(sprintf(_('The entry (%s) is in readonly mode.'),$entry['dn']['html']),null,-1,true);

/*********************/
/* attribute values  */ 
/*********************/

eval('$writer = new '.$_SESSION['plaConfig']->GetValue('appearance','entry_writer').'($ldapserver);');

$ldap['attr'] = $entry['ldap']->getAttribute($entry['attr']['string']);
if (!$ldap['attr']) {
	// define a new attribute for the entry
	$attributefactoryclass = $_SESSION['plaConfig']->GetValue('appearance','attribute_factory');
	eval('$attribute_factory = new '.$attributefactoryclass.'();');
	$ldap['attr'] = $attribute_factory->newAttribute($entry['attr']['string'], array());
	$ldap['attr']->setEntry($entry['ldap']);
}
$ldap['count'] = $ldap['attr']->getValueCount();

if ($ldap['attr']->isReadOnly())
	pla_error(sprintf(_('The attribute (%s) is in readonly mode.'),$entry['attr']['html']),null,-1,true);
if (! $_SESSION['plaConfig']->isCommandAvailable('attribute_add_value'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('add attribute value')));
if (($ldap['attr']->getValueCount() == 0) && ! $_SESSION['plaConfig']->isCommandAvailable('attribute_add'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('add attribute')));

/*
$ldap['attrs'] = $ldapserver->getDNAttr($entry['dn']['string'],$entry['attr']['string']);
$ldap['count'] = count($ldap['attrs']);
*/

$entry['attr']['oclass'] = (strcasecmp($entry['attr']['string'],'objectClass') == 0) ? true : false;

if ($entry['attr']['oclass']) {
	# Fetch all available objectClasses and remove those from the list that are already defined in the entry
	$ldap['oclasses'] = $ldapserver->SchemaObjectClasses();

	foreach($ldap['attr']->getValues() as $oclass)
		unset($ldap['oclasses'][strtolower($oclass)]);
} else {
	$ldap['schema'] = $ldapserver->getSchemaAttribute($entry['attr']['string']);
}

printf('<h3 class="title">%s <b>%s</b> %s <b>%s</b></h3>',
	_('Add new'),$entry['attr']['html'],_('value to'),$entry['rdn']['html']);
printf('<h3 class="subtitle">%s <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),$entry['dn']['html']);

if ($ldap['count']) {
	printf('%s <b>%s</b> %s <b>%s</b>%s',
		_('Current list of'),$ldap['count'],_('values for attribute'),$ldap['attr']->getFriendlyName(),_(':'));
} else {
	printf('%s <b>%s</b>.',
		_('No current value for attribute'),$ldap['attr']->getFriendlyName());
}

if ($entry['attr']['oclass']) {
	echo '<form action="cmd.php" method="post" class="new_value" name="entry_form">';
	echo '<input type="hidden" name="cmd" value="add_oclass_form" />';
} else {
	echo '<form action="cmd.php" method="post" class="new_value" name="entry_form" enctype="multipart/form-data" onSubmit="return submitForm(this)">';
	echo '<input type="hidden" name="cmd" value="update_confirm" />';

	//printf('<input type="hidden" name="attr" value="%s" />',$entry['attr']['encode']);

}
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
printf('<input type="hidden" name="dn" value="%s" />',$entry['dn']['encode']);

if ($ldap['count']) {
	// display current attribute values
	echo '<table class="edit_dn" cellspacing="0" cellpadding="0" align="center"><tr><td>';
	for ($i = 0; $i < $ldap['count']; $i++) {
		$writer->draw('OldValue', $ldap['attr'], $i);
		$writer->draw('ReadOnlyValue', $ldap['attr'], $i);
	}
	echo '</td></tr></table>';
	/*
	if ($ldapserver->isJpegPhoto($entry['attr']['string'])) {
		printf('<table><tr><td>%s</td></tr></table>',
			draw_jpeg_photos($ldapserver,$entry['dn']['string'],$entry['attr']['string'],false));

		# <!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->
		printf('<p><small>%s</small></p>',
			_('Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.'));
		# <!-- End of temporary warning -->

	} elseif ($ldapserver->isAttrBinary($entry['attr']['string'])) {
		echo '<ul>';
		for ($i=1; $i<=count($vals); $i++) {
			$href = sprintf('download_binary_attr.php?server_id=%s&amp;dn=%s&amp;attr=%s&amp;value_num=%s',
				$ldapserver->server_id,$entry['dn']['encode'],$entry['attr']['string'],$i-1);

			printf('<li><a href="%s"><img src="images/save.png" alt="Save" />%s (%s)</a></li>',
				$href,_('download value'),$i);
		}
		echo '</ul>';

		# <!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->
		printf('<p><small>%s</small></p>',
			_('Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.'));
		# <!-- End of temporary warning -->

	} else {
		echo '<ul class="current_values">';
		if (strcasecmp($entry['attr']['string'],'userPassword') == 0) {
			foreach ($ldap['attrs'] as $key => $value) {
				if (obfuscate_password_display(get_enc_type($value)))
					echo '<li><span style="white-space: nowrap;">'.preg_replace('/./','*',$value).'<br /></li>';
				else
					echo '<li><span style="white-space: nowrap;">'.htmlspecialchars($value).'<br /></li>';
			}

		} else {
			foreach ($ldap['attrs'] as $val)
				printf('<li><span style="white-space: nowrap;">%s</span></li>',htmlspecialchars($val));
		}
		echo '</ul>';
	}
	*/
} else {
	echo '<br /><br />';
}

echo _('Enter the value you would like to add:');
echo '<br /><br />';

if ($entry['attr']['oclass']) {
	// draw objectClass selection
	echo '<table class="edit_dn" cellspacing="0" cellpadding="0" align="center"><tr><td>';
	echo '<select name="new_oclass[]" multiple="true" size="15">';
	foreach ($ldap['oclasses'] as $name => $oclass) {
		# exclude any structural ones, as they'll only generate an LDAP_OBJECT_CLASS_VIOLATION
		if ($oclass->getType() == 'structural')
			continue; 

		printf('<option value="%s">%s</option>',$oclass->getName(),$oclass->getName());
	}
	echo '</select>';
	echo '</td></tr><tr><td>';

	echo '<br />';
	printf('<input id="save_button" type="submit" value="%s" />',_('Add new ObjectClass'));
	echo '</td></tr></table>';
	echo '<br />';

	if ($_SESSION['plaConfig']->GetValue('appearance','show_hints'))
		printf('<small><br /><img src="images/light.png" alt="Hint" /><span class="hint">%s</span></small>',
			_('Note: You may be required to enter new attributes that these objectClass(es) require'));
	echo '</form>';

} else {
	// draw a blank field
	echo '<table class="edit_dn" cellspacing="0" cellpadding="0" align="center"><tr><td>';
	$writer->draw('BlankValue', $ldap['attr'], $ldap['count']);
	echo '</td></tr><tr><td>';
	/*
	if ($ldapserver->isAttrBinary($entry['attr']['string'])) {
		echo '<input type="file" name="new_value" />';
		echo '<input type="hidden" name="binary" value="true" />';

	} else {
		if ($ldapserver->isMultiLineAttr($entry['attr']['string'])) {
			echo '<textarea name="new_value" rows="3" cols="30"></textarea>';
		} else {
			printf('<input type="text"%s name="new_value" size="40" value="" />',
				($ldap['attr']->getMaxLength() ? sprintf(' maxlength="%s"',$ldap['attr']->getMaxLength()) : ''));

			# Draw the "browse" button next to this input box if this attr houses DNs:
			if ($ldapserver->isDNAttr($entry['attr']['string']))
				draw_chooser_link("new_value_form.new_value", false);
		}
	}
	*/

	if ($ldap['schema']->getDescription())
		printf('<small><b>%s:</b> %s</small><br />',_('Description'),$ldap['schema']->getDescription());

	if ($ldap['schema']->getType())
		printf('<small><b>%s:</b> %s</small><br />',_('Syntax'),$ldap['schema']->getType());

	if ($ldap['schema']->getMaxLength())
		printf('<small><b>%s:</b> %s %s</small><br />',
			_('Maximum Length'),number_format($ldap['schema']->getMaxLength()),_('characters'));

	echo '<br />';
	printf('<input type="submit" id="save_button" name="submit" value="%s" />',_('Add New Value'));
	echo '</td></tr></table>';

	echo '</form>';

	// javascript
	echo '<script type="text/javascript" language="javascript">
	      function pla_getComponentById(id) {
		  return document.getElementById(id);
	      }

	      function pla_getComponentsByName(name) {
		 return document.getElementsByName(name);
	      }

	      function pla_getComponentValue(component) {
		  if (component.type == "checkbox") {
		      if (component.checked) return component.value;
		  } else if (component.type == "select-one") {
		      if (component.selectedIndex >= 0) return component.options[component.selectedIndex].value;
		  } else if (component.type == "select-multiple") {
		      if (component.selectedIndex >= 0) return component.options[component.selectedIndex].value;
		  } else if (component.type == undefined) { // option
		      if (component.selected) return component.value;
		  } else {
		      return component.value;
		  }
		  return "";
	      }

	      function pla_setComponentValue(component, value) {
		  if (component.type == "checkbox") {
		      if (component.value == value) component.checked = true;
		      else component.checked = false;
		  } else if (component.type == "select-one") {
		      for (var i = 0; i < component.options.length; i++) {
			  if (component.options[i].value == value) component.options[i].selected = true;
		      }
		  } else if (component.type == "select-multiple") {
		      for (var i = 0; i < component.options.length; i++) {
			  if (component.options[i].value == value) component.options[i].selected = true;
		      }
		  } else if (component.type == undefined) { // option
		      if (component.value == value) component.selected = true;
		      else component.selected = false;
		  } else { // text, textarea
		      component.value = value;
		  }
	      }</script>';

	echo '<script type="text/javascript" language="javascript">
	      function getAttributeComponents(prefix, name) {
	          var components = new Array();
		  var i = 0;
	          var j = 0;
	          var c = pla_getComponentsByName(prefix + "_values[" + name + "][" + j + "]");
	          while (c && (c.length > 0)) {
	              for (var k = 0; k < c.length; k++) {
			  components[i++] = c[k];
	              }
	              ++j;
	              c = pla_getComponentsByName(prefix + "_values[" + name + "][" + j + "]");
	          }
		  c = pla_getComponentsByName(prefix + "_values[" + name + "][]");
	          if (c && (c.length > 0)) {
	              for (var k = 0; k < c.length; k++) {
			  components[i++] = c[k];
	              }
	          }
		  return components;
              }
	      function getAttributeValues(prefix, name) {
		  var components = getAttributeComponents(prefix, name);
	          var values = new Array();
	          for (var k = 0; k < components.length; k++) {
	              var val = pla_getComponentValue(components[k]);
	              if (val) values[values.length] = val;
	          }
		  return values;
              }</script>';

	echo '<script type="text/javascript" language="javascript">
	      function validateForm(silence) {
		  var i = 0;
		  var valid = true;
		  var components = null;
	          components = getAttributeComponents("new", "'.$ldap['attr']->getName().'");
	          for (i = 0; i < components.length; i++) {
	              if (window.validate_'.$ldap['attr']->getName().') {
	                  valid = (!validate_'.$ldap['attr']->getName().'(components[i], silence) || !valid) ? false : true;
	              }
	          }
	          return valid;
	      }
	      </script>';

	echo '<script type="text/javascript" language="javascript">
	      function submitForm(form) {
	          for (var i = 0; i < form.elements.length; i++) {
	              form.elements[i].blur();
	          }
		  return validateForm(true);
	      }
	      function alertError(err, silence) {
	          if (!silence) alert(err);
	      }
	      </script>';

	echo '<script type="text/javascript" language="javascript">
		var attrTrace;
		function fill(id, value) {
			attrTrace = new Array();
			fillRec(id, value);
		}
		function fillRec(id, value) {
			if (attrTrace[id] == 1) {
				return;
			} else {
				var pre = "";
				var suf = "";
				var i;
				attrTrace[id] = 1;
				pla_setComponentValue(pla_getComponentById(id), value);
				// here comes template-specific implementation, generated by php
				if (false) {}';
	$attr = $ldap['attr']->getName();
	echo "\t\t\telse if ((i = id.indexOf('_".$attr."_')) >= 0) {\n";
	echo "\t\t\t\tpre = id.substring(0, i+1);\n";
	echo "\t\t\t\tsuf = id.substring(i + 1 + '$attr'.length, id.length);\n";
	$writer->draw('FillJavascript', $ldap['attr'], 'id', 'value');
	echo "\t\t\t}\n";
	echo '}}</script>';

	$writer->draw('Javascript', $ldap['attr']);

	echo '<script type="text/javascript" language="javascript">
	      validateForm(true);
	      </script>';
}
?>
