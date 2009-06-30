<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_value_form.php,v 1.34.2.5 2007/01/27 12:51:47 wurley Exp $

/**
 * Displays a form to allow the user to enter a new value to add
 * to the existing list of values for a multi-valued attribute.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$attr = $_GET['attr'];
$dn = isset($_GET['dn']) ? $_GET['dn'] : null;
$encoded_dn = rawurlencode($dn);
$encoded_attr = rawurlencode($attr);

if (! is_null($dn))
	$rdn = get_rdn($dn);
else
	$rdn = null;

$current_values = $ldapserver->getDNAttr($dn,$attr);
if ($current_values) {
	if (! is_array($current_values))
		$current_values = array($current_values);

	$num_current_values = count($current_values);

} else {
	$current_values = array();
	$num_current_values = 0;
}

$is_object_class = (strcasecmp($attr, 'objectClass') == 0) ? true : false;

if ($is_object_class) {
	# fetch all available objectClasses and remove those from the list that are already defined in the entry
	$schema_oclasses = $ldapserver->SchemaObjectClasses();

	foreach($current_values as $oclass)
		unset($schema_oclasses[strtolower($oclass)]);

} else {
	$schema_attr = $ldapserver->getSchemaAttribute($attr);
}

include './header.php';

echo '<body>';
printf('<h3 class="title">%s <b>%s</b> %s <b>%s</b></h3>',
	_('Add new'),htmlspecialchars($attr),_('value to'),htmlspecialchars($rdn));
printf('<h3 class="subtitle">%s <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));

printf('%s <b>%s</b> %s <b>%s</b>:',
	_('Current list of'),$num_current_values,_('values for attribute'),htmlspecialchars($attr));

if ($num_current_values) {
	if ($ldapserver->isJpegPhoto($attr)) {

	echo '<table><tr><td>';
	draw_jpeg_photos($ldapserver, $dn, $attr, false);
	echo '</td></tr></table>';

	# <!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->
	printf('<p><small>%s</small></p>',
		_('Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.'));
	# <!-- End of temporary warning -->

	} elseif ($ldapserver->isAttrBinary($attr)) {
		echo '<ul>';

		if (is_array($vals)) {
			for ($i=1; $i<=count($vals); $i++) {
				$href = sprintf('download_binary_attr.php?server_id=%s&amp;dn=%s&amp;attr=%s&amp;value_num=%s',
					$ldapserver->server_id,$encoded_dn,$attr,$i-1);

				printf('<li><a href="%s"><img src="images/save.png" />%s (%s)</a></li>',
					$href,_('download value'),$i);
			}

		} else {
			$href = sprintf('download_binary_attr.php?server_id=%s&amp;dn=%s&amp;attr=%s',
				$ldapserver->server_id,$encoded_dn,$attr);
			printf('<li><a href="%s"><img src="images/save.png" />%s</a></li>',
				$href,_('download value'));
		}

		echo '</ul>';
		# <!-- Temporary warning until we find a way to add jpegPhoto values without an INAPROPRIATE_MATCHING error -->
		printf('<p><small>%s</small></p>',
			_('Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.'));
		# <!-- End of temporary warning -->

	} else {
		echo '<ul class="current_values">';

		if (is_array($current_values)) {
			if (strcasecmp($attr,'userPassword') == 0) {
				foreach ($current_values as $key => $value) {
					if (obfuscate_password_display(get_enc_type($value)))
						echo '<li><span style="white-space: nowrap;">'.preg_replace('/./','*',$value).'<br /></li>';
					else
						echo '<li><span style="white-space: nowrap;">'.htmlspecialchars($value).'<br /></li>';
				}

			} else {
				foreach ($current_values as $val)
					printf('<li><span style="white-space: nowrap;">%s</span></li>',htmlspecialchars($val));
			}

		} else {
			printf('<li><span style="white-space: nowrap;">%s</span></li>',htmlspecialchars($current_values));
		}

		echo '</ul>';
	}
} else {
	echo '<br /><br />';
}

echo _('Enter the value you would like to add:');
echo '<br /><br />';

if ($is_object_class) {
	echo '<form action="add_oclass_form.php" method="post" class="new_value">';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',$encoded_dn);

	echo '<select name="new_oclass[]" multiple="true" size="15">';
	foreach ($schema_oclasses as $name => $oclass) {
		# exclude any structural ones, as they'll only generate an LDAP_OBJECT_CLASS_VIOLATION
		if ($oclass->getType() == 'structural')
			continue; 

		printf('<option value="%s">%s</option>',$oclass->getName(),$oclass->getName());
	}
	echo '</select>';

	echo '<br />';
	printf('<input type="submit" value="%s" />',_('Add new ObjectClass'));
	echo '<br />';

	if ($config->GetValue('appearance','show_hints'))
		printf('<small><br /><img src="images/light.png" /><span class="hint">%s</span></small>',
			_('Note: You may be required to enter new attributes that these objectClass(es) require'));

} else {
	echo '<form action="add_value.php" method="post" class="new_value" name="new_value_form">';

	if ($ldapserver->isAttrBinary($attr))
		echo 'enctype="multipart/form-data"';

	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',$encoded_dn);
	printf('<input type="hidden" name="attr" value="%s" />',$encoded_attr);

	if ($ldapserver->isAttrBinary($attr)) {
		echo '<input type="file" name="new_value" />';
		echo '<input type="hidden" name="binary" value="true" />';

	} else {
		if ($ldapserver->isMultiLineAttr($attr)) {
			echo '<textarea name="new_value" rows="3" cols="30"></textarea>';
		} else {
			printf('<input type="text"%s name="new_value" size="40" value="" />',
				($schema_attr->getMaxLength() ? sprintf(' maxlength="%s"',$schema_attr->getMaxLength()) : ''));

			# draw the "browse" button next to this input box if this attr houses DNs:
			if ($ldapserver->isDNAttr($attr))
				draw_chooser_link("new_value_form.new_value", false);
		}
	}

	printf('<input type="submit" name="submit" value="%s" />',_('Add New Value'));
	echo '<br />';

	if ($schema_attr->getDescription())
		printf('<small><b>%s:</b> %s</small><br />',_('Description'),$schema_attr->getDescription());

	if ($schema_attr->getType())
		printf('<small><b>%s:</b> %s</small><br />',_('Syntax'),$schema_attr->getType());

	if ($schema_attr->getMaxLength())
		printf('<small><b>%s:</b> %s %s</small><br />',
			_('Maximum Length'),number_format($schema_attr->getMaxLength()),_('characters'));

	echo '</form>';
}
echo '</body></html>';
?>
