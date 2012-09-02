<?php
/**
 * Displays a form to allow the user to enter a new value to add
 * to the existing list of values for a multi-valued attribute.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DN and ATTR we are working with.
$request = array();
$request['dn'] = get_request('dn','GET',true);
$request['attr'] = get_request('attr','GET',true);

# Check if the entry exists.
if (! $request['dn'] || ! $app['server']->dnExists($request['dn']))
	error(sprintf(_('The entry (%s) does not exist.'),$request['dn']),'error','index.php');

$request['page'] = new TemplateRender($app['server']->getIndex(),get_request('template','REQUEST',false,null));
$request['page']->setDN($request['dn']);
$request['page']->accept(true);
$request['template'] = $request['page']->getTemplate();

/*
if ($request['attribute']->isReadOnly())
	error(sprintf(_('The attribute (%s) is in readonly mode.'),$request['attr']),'error','index.php');
*/

# Render the form
if (! strcasecmp($request['attr'],'objectclass') || get_request('meth','REQUEST') != 'ajax') {
	# Render the form.
	$request['page']->drawTitle(sprintf('%s <b>%s</b> %s <b>%s</b>',_('Add new'),htmlspecialchars($request['attr']),_('value to'),htmlspecialchars(get_rdn($request['dn']))));
	$request['page']->drawSubTitle();

	if (! strcasecmp($request['attr'],'objectclass')) {
		echo '<form action="cmd.php" method="post" class="new_value" id="entry_form">';
		echo '<div>';
		echo '<input type="hidden" name="cmd" value="add_oclass_form" />';

	} else {
		echo '<form action="cmd.php" method="post" class="new_value" id="entry_form" enctype="multipart/form-data" onsubmit="return submitForm(this)">';
		echo '<div>';
		if ($_SESSION[APPCONFIG]->getValue('confirm','update'))
			echo '<input type="hidden" name="cmd" value="update_confirm" />';
		else
			echo '<input type="hidden" name="cmd" value="update" />';
	}

	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	echo '</div>';

	echo '<table class="forminput" border="0" style="margin-left: auto; margin-right: auto;">';
	echo '<tr>';

	$request['attribute'] = $request['template']->getAttribute($request['attr']);
	$request['count'] = $request['attribute']->getValueCount();

	if ($request['count']) {
		printf('<td class="top">%s <b>%s</b> %s <b>%s</b>:</td>',
			_('Current list of'),$request['count'],_('values for attribute'),$request['attribute']->getFriendlyName());

		echo '<td>';

		# Display current attribute values
		echo '<table border="0"><tr><td>';
		for ($i=0;$i<$request['count'];$i++) {
			if ($i > 0)
				echo '<br/>';
			$request['page']->draw('CurrentValue',$request['attribute'],$i);
			$request['page']->draw('HiddenValue',$request['attribute'],$i);
		}
		echo '</td></tr></table>';

		echo '</td>';

	} else {
		printf('<td>%s <b>%s</b>.</td>',
			_('No current value for attribute'),$request['attribute']->getFriendlyName());
		echo '<td><br /><br /></td>';
	}

	echo '</tr>';

	echo '<tr>';
	printf('<td class="top">%s</td>',_('Enter the value(s) you would like to add:'));
	echo '<td>';

	if (! strcasecmp($request['attr'],'objectclass')) {
		# If our attr is an objectClass, fetch all available objectClasses and remove those from the list that are already defined in the entry
		$socs = $app['server']->SchemaObjectClasses();

		foreach ($request['attribute']->getValues() as $oclass)
			unset($socs[strtolower($oclass)]);

		# Draw objectClass selection
		echo '<table border="0">';
		echo '<tr><td>';
		echo '<select name="new_values[objectclass][]" multiple="multiple" size="15">';
		foreach ($socs as $name => $oclass) {
			# Exclude any structural ones, that are not in the heirachy, as they'll only generate an LDAP_OBJECT_CLASS_VIOLATION
			if (($oclass->getType() == 'structural') && ! $oclass->isRelated($request['attribute']->getValues()))
				continue; 

			printf('<option value="%s">%s</option>',$oclass->getName(false),$oclass->getName(false));
		}
		echo '</select>';
		echo '</td></tr><tr><td>';

		echo '<br />';
		printf('<input id="save_button" type="submit" value="%s" %s />',
			_('Add new ObjectClass'),
			(isAjaxEnabled() ? sprintf('onclick="return ajSUBMIT(\'BODY\',document.getElementById(\'entry_form\'),\'%s\');"',_('Updating Object')) : ''));
		echo '</td></tr></table>';
		echo '</td>';
		echo '</tr>';

		if ($_SESSION[APPCONFIG]->getValue('appearance','show_hints'))
			printf('<tr><td colspan="2"><small><br /><img src="%s/light.png" alt="Hint" /><span class="hint">%s</span></small></td></tr>',
				IMGDIR,_('Note: You may be required to enter new attributes that these objectClass(es) require'));

		echo '</table>';
		echo '</form>';

	} else {
		# Draw a blank field
		echo '<table border="0"><tr><td>';
		$request['page']->draw('FormValue',$request['attribute'],$request['count']);
		echo '</td></tr><tr><td>';

		$sattr = $app['server']->getSchemaAttribute($request['attr']);

		if ($sattr->getDescription())
			printf('<small><b>%s:</b> %s</small><br />',_('Description'),$sattr->getDescription());

		if ($sattr->getType())
			printf('<small><b>%s:</b> %s</small><br />',_('Syntax'),$sattr->getType());

		if ($sattr->getMaxLength())
			printf('<small><b>%s:</b> %s %s</small><br />',
				_('Maximum Length'),number_format($sattr->getMaxLength()),_('characters'));

		echo '<br />';
		printf('<input type="submit" id="save_button" name="submit" value="%s" />',_('Add New Value'));
		echo '</td></tr></table>';

		echo '</td></tr>';
		echo '</table>';
		echo '</form>';
	}

} else {
	if (is_null($attribute = $request['template']->getAttribute($request['attr']))) {
		$request['template']->addAttribute($request['attr'],array('values'=>array()));
		$attribute = $request['template']->getAttribute($request['attr']);
		$attribute->show();

		echo '<table class="entry" cellspacing="0" align="center" border="0">';
		$request['page']->draw('Template',$attribute);
		$request['page']->draw('Javascript',$attribute);
		echo '</table>';

	} else {
		$request['count'] = $attribute->getValueCount();
		$request['page']->draw('FormReadWriteValue',$attribute,$request['count']);
	}
}
?>
