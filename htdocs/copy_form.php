<?php
/**
 * Copies a given object to create a new one.
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

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->setDN($request['dn']);
$request['page']->accept();

# Render the form
$request['page']->drawTitle(sprintf('%s <b>%s</b>',_('Copy'),get_rdn($request['dn'])));
$request['page']->drawSubTitle();

printf('<script type="text/javascript" src="%sdnChooserPopup.js"></script>',JSDIR);
echo '<div style="text-align: center;">';
printf('%s <b>%s</b> %s:<br /><br />',_('Copy'),get_rdn($request['dn']),_('to a new object'));
echo '</div>';

echo '<form action="cmd.php" method="post" id="copy_form">';
echo '<div>';
echo '<input type="hidden" name="cmd" value="copy" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
printf('<input type="hidden" name="server_id_src" value="%s" />',$app['server']->getIndex());
printf('<input type="hidden" name="dn_src" value="%s" />',htmlspecialchars($request['dn']));
echo '</div>';
echo "\n";

echo '<table border="0" style="border-spacing: 10px; margin-left: auto; margin-right: auto;">';

echo '<tr>';
printf('<td><acronym title="%s">%s</acronym>:</td>',
	_('The full DN of the new entry to be created when copying the source entry'),_('Destination DN'));
echo '<td>';
printf('<input type="text" name="dn_dst" size="45" value="%s" />',htmlspecialchars($request['dn']));
draw_chooser_link('copy_form','dn_dst','true',get_rdn($request['dn']));
echo '</td>';
echo '</tr>';
echo "\n";

printf('<tr><td>%s:</td><td>%s</td></tr>',_('Destination Server'),server_select_list($app['server']->getIndex(),true,'server_id_dst'));
echo "\n";

# We search all children, not only the visible children in the tree
$request['children'] = $app['server']->getContainerContents($request['dn']);

if (count($request['children']) > 0) {
	echo '<tr>';
	printf('<td><label for="recursive">%s</label>:</td>',_('Recursive copy'));
	echo '<td><input type="checkbox" id="recursive" name="recursive" onclick="copy_field_toggle(this)" />';
	printf('<small>(%s)</small></td>',_('Recursively copy all children of this object as well.'));
	echo '</tr>';
	echo "\n";

	echo '<tr>';
	printf('<td><acronym title="%s">%s</acronym>:</td>',
		_('When performing a recursive copy, only copy those entries which match this filter'),_('Filter'));
	echo '<td><input type="text" name="filter" value="(objectClass=*)" size="45" disabled />';
	echo '</tr>';
	echo "\n";

	echo '<tr>';
	printf('<td>%s</td>',_('Delete after copy (move):'));
	echo '<td><input type="checkbox" name="remove" value="yes" disabled />';
	printf('<small>(%s)</small)</td>',_('Make sure your filter (above) will select all child records.'));
	echo '</tr>';
	echo "\n";

} else {
	printf('<tr><td>%s</td><td><input type="checkbox" name="remove" value="yes"/></td></tr>',_('Delete after copy (move):'));
}
echo "\n";

printf('<tr><td colspan="2" style="text-align: center;"><input type="submit" value="%s" /></td></tr>',_('Copy '));
echo "\n";

echo '</table>';
echo '</form>';

if ($_SESSION[APPCONFIG]->getValue('appearance','show_hints'))
	printf('<div style="text-align: center;"><small><img src="%s/light.png" alt="Light" /><span class="hint">%s</span></small></div>',
		IMGDIR,_('Hint: Copying between different servers only works if there are no schema violations'));


# Draw the javascrpt to enable/disable the filter field if this may be a recursive copy
if (count($request['children']) > 0)
	printf('<script type="text/javascript" src="%sform_field_toggle_enable.js"></script>',JSDIR);
?>
