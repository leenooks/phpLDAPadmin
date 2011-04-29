<?php
/**
 * Displays a form for adding an attribute/value to an LDAP entry.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DN we are working with
$request = array();
$request['dn'] = get_request('dn','GET');

# Check if the entry exists.
if (! $request['dn'] || ! $app['server']->dnExists($request['dn']))
	error(sprintf(_('The entry (%s) does not exist.'),$request['dn']),'error','index.php');

$request['page'] = new TemplateRender($app['server']->getIndex(),get_request('template','REQUEST',false,null));
$request['page']->setDN($request['dn']);
$request['page']->accept(true);
$request['template'] = $request['page']->getTemplate();

# Render the form
if (get_request('meth','REQUEST') != 'ajax') {
	$request['page']->drawTitle(sprintf('%s <b>%s</b>',_('Add new attribute'),get_rdn($request['dn'])));
	$request['page']->drawSubTitle();

	echo '<div style="text-align: center;">';
	if (count($request['template']->getAvailAttrs())) {
		# If we have more than the configured entries, we'll separate our input to the old ways.
		if (count($request['template']->getAvailAttrs()) > $_SESSION[APPCONFIG]->getValue('appearance','max_add_attrs')) {
			$attr = array();
			$attr['avail'] = array();
			$attr['binary'] = array();

			foreach ($request['template']->getAvailAttrs() as $attribute)
				if ($app['server']->isAttrBinary($attribute->getName()))
					array_push($attr['binary'],$attribute);
				else
					array_push($attr['avail'],$attribute);

			if (count($attr['avail']) > 0) {
				echo '<br />';
				echo _('Add new attribute');
				echo '<br />';
				echo '<br />';

				echo '<form action="cmd.php" method="post">';
				echo '<div>';

				if ($_SESSION[APPCONFIG]->getValue('confirm','update'))
					echo '<input type="hidden" name="cmd" value="update_confirm" />';
				else
					echo '<input type="hidden" name="cmd" value="update" />';

				printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
				printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));

				echo '<select name="single_item_attr">';

				foreach ($attr['avail'] as $attribute) {
					# Is there a user-friendly translation available for this attribute?
					if ($attribute->haveFriendlyName())
						$attr_display = sprintf('%s (%s)',$attribute->getFriendlyName(),$attribute->getName(false));
					else
						$attr_display = $attribute->getName(false);

					printf('<option value="%s">%s</option>',htmlspecialchars($attribute->getName()),$attr_display);
				}

				echo '</select>';

				echo '<input type="text" name="single_item_value" size="20" />';
				printf('<input type="submit" name="submit" value="%s" class="update_dn" />',_('Add'));
				echo '</div>';
				echo '</form>';

			} else {
				echo '<br />';
				printf('<small>(%s)</small>',_('no new attributes available for this entry'));
			}

			if (count($attr['binary']) > 0) {
				echo '<br />';
				echo _('Add new binary attribute');
				echo '<br />';
				echo '<br />';

				echo '<!-- Form to add a new BINARY attribute to this entry -->';
				echo '<form action="cmd.php" method="post" enctype="multipart/form-data">';
				echo '<div>';

				if ($_SESSION[APPCONFIG]->getValue('confirm','update'))
					echo '<input type="hidden" name="cmd" value="update_confirm" />';
				else
					echo '<input type="hidden" name="cmd" value="update" />';

				printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
				printf('<input type="hidden" name="dn" value="%s" />',$request['dn']);
				echo '<input type="hidden" name="binary" value="true" />';

				echo '<select name="single_item_attr">';

				foreach ($attr['binary'] as $attribute) {
					# Is there a user-friendly translation available for this attribute?
					if ($attribute->haveFriendlyName())
						$attr_display = sprintf('%s (%s)',$attribute->getFriendlyName(),$attribute->getName(false));
					else
						$attr_display = $attribute->getName(false);

					printf('<option value="%s">%s</option>',htmlspecialchars($attribute->getName()),$attr_display);
				}

				echo '</select>';

				echo '<input type="file" name="single_item_value" size="20" />';
				printf('<input type="submit" name="submit" value="%s" class="update_dn" />',_('Add'));

				if (! ini_get('file_uploads'))
					printf('<br /><small><b>%s</b></small><br />',
						_('Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.'));

				else
					printf('<br /><small><b>%s: %s</b></small><br />',_('Maximum file size'),ini_get('upload_max_filesize'));

				echo '</div>';
				echo '</form>';

			} else {
				echo '<br />';
				printf('<small>(%s)</small>',_('no new binary attributes available for this entry'));
			}

		} else {
			echo '<br />';

			$request['page']->drawFormStart();
			printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
			printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));

			echo '<table class="entry" cellspacing="0" align="center" border="0">';

			foreach ($request['template']->getAvailAttrs() as $attribute)
				$request['page']->draw('Template',$attribute);

			$request['page']->drawFormSubmitButton();
			echo '</table>';

			$request['page']->drawFormEnd();
		}

	} else {
		printf('<small>(%s)</small>',_('no new attributes available for this entry'));
	}

	echo '</div>';

# The ajax addition (it is going into an existing TemplateRendered page
} else {
	# Put our DIV there for the callback
	echo '<fieldset>';
	printf('<legend>%s</legend>',_('Add Attribute'));
	echo '<div id="ajADDATTR">';
	echo '<table class="entry" cellspacing="0" align="center" border="0">';
	echo '<td valign="top" align="center">';

	printf('<select name="attr" onchange="ajDISPLAY(\'%s\',\'cmd=add_value_form&server_id=%s&dn=%s&attr=\'+this.value,\'%s\',\'append\');">',
		'ADDATTR',$app['server']->getIndex(),$request['template']->getDNEncode(),_('Please Wait'));

	printf('<option value="%s">%s</option>','','');
	foreach ($request['template']->getAvailAttrs() as $attribute)
		printf('<option value="%s">%s</option>',htmlspecialchars($attribute->getName()),$attribute->getFriendlyName());

	echo '</select>';

	echo '</td>';
	echo '</table>';
	echo '</div>';
	echo '</fieldset>';
}
?>
