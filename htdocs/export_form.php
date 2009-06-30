<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/export_form.php,v 1.22.4.7 2007/03/18 03:16:05 wurley Exp $

/**
 * export_form.php
 * Html form to choose an export format(ldif,...)
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
require LIBDIR.'export_functions.php';

$format = isset($_GET['format']) ? $_GET['format'] : get_line_end_format();
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'base' ;
$exporter_id = isset($_GET['exporter_id']) ? $_GET['exporter_id'] : 0 ;
$dn = isset($_GET['dn']) ? $_GET['dn'] : null;
$filter = isset($_GET['filter']) ? $_GET['filter'] : '(objectClass=*)';
$attributes = isset($_GET['attributes']) ? $_GET['attributes'] : '*';
$sys_attr = isset($_GET['sys_attr']) && $_GET['sys_attr'] == 'true' ? true : false;

$available_formats = array (
	'unix' => 'UNIX (Linux, BSD)',
	'mac'  => 'Macintosh',
	'win'  => 'Windows'
);

$available_scopes = array (
	'base' => _('Base (base dn only)'),
	'one' => _('One (one level beneath base)'),
	'sub' => _('Sub (entire subtree)')
);


include './header.php';

echo '<body>';
printf('<h3 class="title">%s</h3>',_('Export'));
echo '<br />';
echo '<center>';
echo '<form name="export_form" action="export.php" method="post">';
echo '<table class="export_form">';
echo '<tr>';
echo '<td>';

echo '<fieldset>';
printf('<legend>%s</legend>',_('Export'));

echo '<table>';
printf('<tr><td>%s</td><td>%s</td></tr>',_('Server'),server_select_list());

echo '<tr>';
printf('<td style="white-space:nowrap">%s</td>',_('Base DN'));
printf('<td><span style="white-space: nowrap;"><input type="text" name="dn" id="dn" style="width:230px" value="%s" />&nbsp;',htmlspecialchars($dn));
draw_chooser_link('export_form.dn');
echo '</span></td>';
echo '</tr>';

echo '<tr>';
printf('<td><span style="white-space: nowrap">%s</span></td>',_('Search Scope'));

echo '<td>';

foreach ($available_scopes as $id => $desc)
	printf('<input type="radio" name="scope" value="%s" id="%s"%s /><label for="%s">%s</label><br />',
		htmlspecialchars($id),htmlspecialchars($id),($id == $scope) ? 'checked="true"' : '',
		htmlspecialchars($id),htmlspecialchars($desc));

echo '</td>';
echo '</tr>';

printf('<tr><td>%s</td><td><input type="text" name="filter" style="width:300px" value="%s" /></td></tr>',
	_('Search Filter'),htmlspecialchars($filter));

printf('<tr><td>%s</td><td><input type="text" name="attributes" style="width:300px" value="%s" /></td></tr>',
	_('Show Attributtes'),htmlspecialchars($attributes));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" name="sys_attr" id="sys_attr" %s/> <label for="sys_attr">%s</label></td></tr>',
	$sys_attr ? 'checked="true" ' : '',_('Include system attributes'));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" id="save_as_file" name="save_as_file" onclick="toggle_disable_field_saveas(this)" /> <label for="save_as_file">%s</label></td></tr>',
	_('Save as file'));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" id="compress" name="compress" disabled /> <label for="compress">%s</label></td></tr>',
	_('Compress'));

echo '</table>';
echo '</fieldset>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td>';

echo '<table style="width: 100%">';
echo '<tr><td style="width: 50%">';
echo '<fieldset style="height: 100px">';

printf('<legend>%s</legend>',_('Export format'));

foreach ($exporters as $index => $exporter) {
	printf('<input type="radio" name="exporter_id" id="exporter_id_%s" value="%s"%s />',
		htmlspecialchars($index),htmlspecialchars($index),($index==$exporter_id) ? ' checked="true"' : '');
	printf('<label for="%s">%s</label><br />',
		htmlspecialchars($index),htmlspecialchars($exporter['desc']));
}

echo '</fieldset>';
echo '</td>';
echo '<td style="width: 50%">';
echo '<fieldset style="height: 100px">';

printf('<legend>%s</legend>',_('Line ends'));
foreach ($available_formats as $id => $desc)
	printf('<input type="radio" name="format" value="%s" id="%s"%s /><label for="%s">%s</label><br />',
		htmlspecialchars($id),htmlspecialchars($id),($format==$id) ? ' checked="true"' : '',
		htmlspecialchars($id),htmlspecialchars($desc));

echo '</fieldset>';
echo '</td></tr>';
echo '</table>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">';
printf('<center><input type="submit" name="target" value="%s" /></center>',
	htmlspecialchars(_('Proceed >>')));
echo '</td>';
echo '</tr>';
echo '</table>';

echo '</form>';
echo '</center>';

/**
 * Helper functoin for fetching the line end format.
 * @return String 'win', 'unix', or 'mac' based on the user's browser..
 */
function get_line_end_format() {
	if (is_browser_os_windows())
		return 'win';
	elseif (is_browser_os_unix())
		return 'unix';
	elseif (is_browser_os_mac())
		return 'mac';
	else
		return 'unix';
}

?>
<script type="text/javascript" language="javascript">
<!--
        function toggle_disable_field_saveas(id) {
                if (id.checked) {
                        id.form.compress.disabled = false;
                } else {
                        id.form.compress.disabled = true;
                        id.form.compress.checked = false;
                }
        }
-->
</script>
</body>
</html>
