<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/Attic/create_form.php,v 1.31.2.5 2005/12/31 04:21:37 wurley Exp $

/**
 * The menu where the user chooses an RDN, Container, and Template for creating a new entry.
 * After submitting this form, the user is taken to their chosen Template handler.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars
 *  - container (rawurlencoded) (optional)
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

$container = $_REQUEST['container'];
$server_menu_html = server_select_list($ldapserver->server_id,true);

include './header.php';

echo '<body>';

printf('<h3 class="title">%s</h3>',_('Create Object'));
printf('<h3 class="subtitle">%s</h3>',_('Choose a template'));
printf('<center><h3>%s</h3></center>',_('Select a template for the creation process'));

echo '<form action="template_engine.php" method="post">';
printf('<input type="hidden" name="container" value="%s" />',htmlspecialchars($container));

echo '<table class="create">';
printf('<tr><td class="heading">%s:</td><td>%s</td></tr>',_('Server'),$server_menu_html);

echo '<tr>';
printf('<td class="heading">%s:</td>',_('Template'));
echo '<td>';

echo '<table class="template_display">';
echo '<tr><td>';

echo '<table class="templates">';

$i = -1;

$template_xml = new Templates($ldapserver->server_id);
$templates = $template_xml->_template;

# Remove non-visable templates.
foreach ($templates as $index => $template)
	if (isset($template['visible']) && (! $template['visible']))
		unset ($templates[$index]);

$templates['custom']['title'] = 'Custom';
$templates['custom']['icon'] = 'images/object.png';

$count = count($templates);
foreach ($templates as $name => $template) {
	$i++;

	# If the template doesnt have a title, we'll use the desc field.
	$template['desc'] = isset($template['title']) ? $template['title'] : $template['desc'];

	# Balance the columns properly
	if ((count($templates) % 2 == 0 && $i == intval($count / 2)) ||
		(count($templates) % 2 == 1 && $i == intval($count / 2) + 1))

		echo '</table></td><td><table class="templates">';

	# Check and see if this template should be shown in the list
	$isValid = false;

	if (isset($template['regexp'])) {
		if (@preg_match('/'.$template['regexp'].'/i',$container))
			$isValid = true;
	} else
		$isValid = true;

	if (isset($template['invalid']) && $template['invalid'])
		$isValid = false;

	echo '<tr>';
	if (! $isValid || (isset($template['handler']) && ! file_exists(TMPLDIR.'creation/'.$template['handler'])))
		echo '<td class="icon"><img src="images/error.png" /></td>';
	else
		printf('<td><input type="radio" name="template" value="%s" id="%s" %s /></td>',
			htmlspecialchars($name),htmlspecialchars($name),
			! $isValid ? 'disabled' : (strcasecmp('Custom',$name) ? '' : 'checked'));

	printf('<td class="icon"><label for="%s"><img src="%s" /></label></td>',
		htmlspecialchars($name),$template['icon']);

	printf('<td class="name"><label for="%s">',
		htmlspecialchars($name));

	if (strcasecmp('Custom', $template['desc']) == 0)
		 echo '<b>';

	if (! $isValid)
		if (isset($template['invalid']) && $template['invalid'])
			printf('<span style="color: gray"><acronym title="%s">',
				isset($template['invalid_reason']) ? $template['invalid_reason'] :
					_('This template has been disabled in the XML file.'));
		else
			printf('<span style="color: gray"><acronym title="%s">',
				_('This template is not allowed in this container.'));

	echo htmlspecialchars($template['desc']);

	if (! $isValid) echo '</acronym></span>';
	if (strcasecmp('Custom', $template['desc']) == 0)
		echo '</b>';

	echo '</label></td></tr>';

}

echo '</table>';
echo '</td></tr></table>';
echo '</td></tr>';

printf('<tr><td colspan="2"><center><input type="submit" name="submit" value="%s" /></center></td></tr>',
	htmlspecialchars(_('Proceed >>')));

echo '</table>';
echo '</form></body></html>';
?>
