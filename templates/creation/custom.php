<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/Attic/custom.php,v 1.43.2.6 2006/03/01 01:04:05 wurley Exp $

$rdn = isset($_POST['rdn']) ? $_POST['rdn'] : null;
$container = $_POST['container'];
$step = isset($_POST['step']) ? $_POST['step'] : 1;

if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

if ($step == 1) {
	$oclasses = $ldapserver->SchemaObjectClasses();

	if (! $oclasses || ! is_array($oclasses))
		pla_error("Unable to retrieve the schema from your LDAP server. Cannot continue with creation.");

	printf('<h4>%s</h4>',_('Step 1 of 2: Name and ObjectClass(es)'));
	echo '<form action="template_engine.php" method="post" name="creation_form">';
	echo '<input type="hidden" name="step" value="2" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="template" value="%s" />',htmlspecialchars($_POST['template']));

	echo '<table class="create">';
	echo '<tr>';
	printf('<td class="heading"><acronym title="%s">%s</acronym>:</td>',_('Relative Distinguished Name'),_('RDN'));
	printf('<td><input type="text" name="rdn" value="%s" size="30" /> %s</td>',htmlspecialchars($rdn),_('(example: cn=MyNewPerson)'));

	echo '</tr><tr>';
	printf('<td class="heading">%s</td>',_('Container'));
	printf('<td><input type="text" name="container" size="40" value="%s" />',htmlspecialchars($container));
	draw_chooser_link('creation_form.container');
	echo '</td>';

	echo '</tr><tr>';
	printf('<td class="heading">%s</td>',_('ObjectClasses'));
	echo '<td><select name="object_classes[]" multiple="true" size="15">';

	foreach ($oclasses as $name => $oclass) {
		if (0 == strcasecmp('top',$name))
			continue;

		printf('<option %s value="%s">%s</option>',
			($oclass->getType() == 'structural') ? 'style="font-weight: bold" ' : '',
			htmlspecialchars($oclass->getName()),htmlspecialchars($oclass->getName()));
	}

	echo '</select>';
	echo '</td>';
	echo '</tr>';

	if ($config->GetValue('appearance','show_hints'))
		printf('<tr><td></td><td><small><img src="images/light.png" /><span class="hint">%s</span></small><br /></td></tr>',
			_('Hint: You must choose exactly one structural objectClass (shown in bold above)'));

	printf('<tr><td></td><td><input type="submit" value="%s" /></td></tr>',_('Proceed &gt;&gt;'));
	echo '</table>';
	echo '</form>';

} elseif ($step == 2) {
	strlen(trim($rdn)) != 0 or
		pla_error(_('You left the RDN field blank.'));

	if (strlen(trim($container)) == 0 or 
		(! $ldapserver->dnExists($container) && ! in_array("$rdn,$container",$ldapserver->getBaseDN())))
		pla_error(sprintf(_('The container you specified (%s) does not exist. Please try again.'),htmlspecialchars($container)));

	$friendly_attrs = process_friendly_attr_table();
	$oclasses = isset($_POST['object_classes']) ? $_POST['object_classes'] : null;
	if (count($oclasses) == 0)
		pla_error(_('You did not select any ObjectClasses for this object. Please go back and do so.'));

	$dn = trim($container) ? $rdn.','.$container : $rdn;

	# incrementally build up the all_attrs and required_attrs arrays
	$schema_oclasses = $ldapserver->SchemaObjectClasses();
	$required_attrs = array();
	$all_attrs = array();

	foreach ($oclasses as $oclass_name) {
		$oclass = $ldapserver->getSchemaObjectClass($oclass_name);
		if ($oclass) {
			$required_attrs = array_merge($required_attrs,$oclass->getMustAttrNames($schema_oclasses));
			$all_attrs = array_merge($all_attrs,$oclass->getMustAttrNames($schema_oclasses),$oclass->getMayAttrNames($schema_oclasses));
		}
	}

	$required_attrs = array_unique($required_attrs);
	$all_attrs = array_unique($all_attrs);
	remove_aliases($required_attrs,$ldapserver);
	remove_aliases($all_attrs,$ldapserver);
	sort($required_attrs);
	sort($all_attrs);

	# remove required attrs from optional attrs
	foreach ($required_attrs as $i => $attr_name) {
		$key = array_search($attr_name,$all_attrs);
		
		if (is_numeric($key))
			unset($all_attrs[$key]);
	}

	# remove binary attributes and add them to the binary_attrs array
	$binary_attrs = array();
	foreach ($all_attrs as $i => $attr_name) {
		if ($ldapserver->isAttrBinary($attr_name)) {
			unset($all_attrs[$i]);
			$binary_attrs[] = $attr_name;
		}
	}

	/* If we trim any attrs out above, then we will have a gap in the index
	   sequence and will get an "undefined index" error below. This prevents
	   that from happening. */
	$all_attrs = array_values($all_attrs);

	/* add the required attribute based on the RDN provided by the user
	   (ie, if the user specifies "cn=Bob" for their RDN, make sure "cn" is
	   in the list of required attributes. */
	$rdn_attr = trim(substr($rdn,0,strpos($rdn,'=')));
	$rdn_value = trim(substr($rdn,strpos($rdn,'=')+1));
	$rdn_value = @pla_explode_dn($rdn);
	$rdn_value = @explode('=',$rdn_value[0],2);
	$rdn_value = @$rdn_value[1];

	if (in_array($rdn_attr,$all_attrs) && ! in_array($rdn_attr,$required_attrs))
		$required_attrs[] = $rdn_attr;

	printf('<h4>%s</h4>',_('Step 2 of 2: Specify attributes and values'));

	echo '<form action="create.php" method="post" enctype="multipart/form-data">';
	echo '<input type="hidden" name="step" value="2" />';
	printf('<input type="hidden" name="new_dn" value="%s" />',htmlspecialchars($dn));
	printf('<input type="hidden" name="new_rdn" value="%s" />',htmlspecialchars($rdn));
	printf('<input type="hidden" name="container" value="%s" />',htmlspecialchars($container));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="object_classes" value="%s" />',rawurlencode(serialize($oclasses)));

	echo '<table class="edit_dn" cellspacing="0">';
	printf('<tr><th colspan="2">%s</th></tr>',_('Required Attributes'));

	if (count($required_attrs) == 0)
		printf('<tr class="row1"><td colspan="2"><center>(%s)</center></td></tr>',_('none'));
	else
		foreach ($required_attrs as $count => $attr) {
			echo '<tr>';

			# is there a user-friendly translation available for this attribute?
			if (isset($friendly_attrs[strtolower($attr)]))
				$attr_display = sprintf('<acronym title="%s: \'%s\' %s \'%s\'">%s</acronym>',
					_('Note'),htmlspecialchars($attr),_('is an alias for'),
					htmlspecialchars($friendly_attrs[strtolower($attr)]),
					htmlspecialchars($friendly_attrs[strtolower($attr)]));
			else
				$attr_display = htmlspecialchars($attr);

			printf('<td class="attr"><b>%s</b></td>',$attr_display);

			echo '</tr><tr>';
			printf('<td class="val"><input type="%s" name="required_attrs[%s]" value="%s" size="40" />',
				($ldapserver->isAttrBinary($attr) ? 'file' : 'text'),
				htmlspecialchars($attr),($attr == $rdn_attr ? htmlspecialchars($rdn_value) : ''));
			echo '</tr>';
		}

	printf('<tr><th colspan="2">%s</th></tr>',_('Optional Attributes'));

	if (count($all_attrs) == 0)
		printf('<tr><td colspan="2"><center>(%s)</center></td></tr>',_('none'));
	else {
		for ($i=0;$i<min(count($all_attrs),10);$i++) {
			$attr = $all_attrs[$i];

			printf('<tr><td class="attr"><select style="background-color: #ddd; font-weight: bold" name="attrs[%s]">%s</select></td></tr>',
				$i,get_attr_select_html($all_attrs,$friendly_attrs,$attr));
			printf('<tr><td class="val"><input type="text" name="vals[%s]" value="" size="40" /></tr>',$i);
		}
	}

	if (count($binary_attrs) > 0) {
		printf('<tr><th colspan="2">%s</th></tr>',_('Optional Binary Attributes'));

		for ($k=$i;$k<$i+count($binary_attrs);$k++) {
			$attr = $binary_attrs[$k-$i];

			printf('<tr><td class="attr"><select style="background-color: #ddd; font-weight: bold" name="attrs[%s]">%s</select></td></tr>',
				$k,get_binary_attr_select_html($binary_attrs,$friendly_attrs,$attr));
			printf('<tr><td class="val"><input type="file" name="vals[%s]" value="" size="25" /></td></tr>',$k);
		}
	}

	printf('<tr><td><center><input type="submit" name="submit" value="%s" /></center></td></tr>',_('Create Object'));
	echo '</table>';
	echo '</form>';
}


function get_attr_select_html($all_attrs,$friendly_attrs,$highlight_attr=null) {
	$attr_select_html = "\n\n";

	if (! is_array($all_attrs))
		return null;

	foreach ($all_attrs as $a) {
		# is there a user-friendly translation available for this attribute?
		if (isset($friendly_attrs[strtolower($a)]))
			$attr_display = sprintf('%s (%s)',htmlspecialchars($friendly_attrs[strtolower($a)]),htmlspecialchars($a));
		else
			$attr_display = htmlspecialchars($a);

		$a = htmlspecialchars($a);
		$attr_select_html .= sprintf('<option value="%s"',$a);

		if (0 == strcasecmp($highlight_attr,$a))
			$attr_select_html .= ' selected';

		$attr_select_html .= sprintf('>%s</option>',$attr_display);
	}

	return $attr_select_html;
}

function get_binary_attr_select_html($binary_attrs,$friendly_attrs,$highlight_attr=null) {
	$binary_attr_select_html = '';

	if (! is_array($binary_attrs))
		return null;

	if (count($binary_attrs) == 0)
		return null;

	foreach ($binary_attrs as $a) {
		# is there a user-friendly translation available for this attribute?
		if (isset($friendly_attrs[strtolower($a)]))
			$attr_display = sprintf('%s (%s)',htmlspecialchars($friendly_attrs[strtolower($a)]),htmlspecialchars($a));
		else
			$attr_display = htmlspecialchars($a);

		$binary_attr_select_html .= '<option';

		if (0 == strcasecmp($highlight_attr,$a))
			$binary_attr_select_html .= ' selected';

		$binary_attr_select_html .= sprintf('>%s</option>',$attr_display);
	}

	return $binary_attr_select_html;
}

/**
 * Removes attributes from the array that are aliases for eachother
 * (just removes the second instance of the aliased attr)
 */
function remove_aliases(&$attribute_list,$ldapserver) {
	# remove aliases from the attribute_list array
	for ($i=0;$i<count($attribute_list);$i++) {
		if (! isset($attribute_list[$i]))
			continue;

		$attr_name1 = $attribute_list[$i];
		$attr1 = $ldapserver->getSchemaAttribute($attr_name1);
		if (is_null($attr1))
			continue;

		for ($k=0;$k<count($attribute_list);$k++) {

			if (! isset($attribute_list[$k]))
				continue;

			if ($i == $k)
				continue;

			$attr_name2 = $attribute_list[$k];

			if ($attr1->isAliasFor($attr_name2))
				unset($attribute_list[$k]);
		}
	}
	$attribute_list = array_values($attribute_list);
}
?>
