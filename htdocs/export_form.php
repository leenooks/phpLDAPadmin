<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/export_form.php,v 1.26.2.1 2008/01/13 05:37:01 wurley Exp $

/**
 * export_form.php
 * Html form to choose an export format(ldif,...)
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

require LIBDIR.'export_functions.php';

$entry['format'] = get_request('format','GET',false,get_line_end_format());
$entry['scope'] = get_request('scope','GET',false,'base');
$entry['id'] = get_request('exporter_id','GET',false,0);
$entry['dn'] = get_request('dn','GET');
$entry['filter'] = get_request('filter','GET',false,'(objectClass=*)');
$entry['attr'] = get_request('attributes','GET',false,'*');
$entry['sys_attr'] = get_request('sys_attr','GET') ? true: false;

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

printf('<h3 class="title">%s</h3>',_('Export'));
echo '<br />';
echo '<center>';
echo '<form name="export_form" action="cmd.php" method="post">';
echo '<input type="hidden" name="cmd" value="export" />';
echo '<table class="forminput">';
echo '<tr>';
echo '<td>';

echo '<fieldset>';
printf('<legend>%s</legend>',_('Export'));

echo '<table>';
printf('<tr><td>%s</td><td>%s</td></tr>',_('Server'),server_select_list($ldapserver->server_id));

echo '<tr>';
printf('<td style="white-space:nowrap">%s</td>',_('Base DN'));
printf('<td><span style="white-space: nowrap;"><input type="text" name="dn" id="dn" style="width:230px" value="%s" />&nbsp;',htmlspecialchars($entry['dn']));
draw_chooser_link('export_form.dn');
echo '</span></td>';
echo '</tr>';

echo '<tr>';
printf('<td><span style="white-space: nowrap">%s</span></td>',_('Search Scope'));

echo '<td>';

foreach ($available_scopes as $id => $desc)
	printf('<input type="radio" name="scope" value="%s" id="%s"%s /><label for="%s">%s</label><br />',
		htmlspecialchars($id),htmlspecialchars($id),($id == $entry['scope']) ? 'checked="true"' : '',
		htmlspecialchars($id),htmlspecialchars($desc));

echo '</td>';
echo '</tr>';

printf('<tr><td>%s</td><td><input type="text" name="filter" style="width:300px" value="%s" /></td></tr>',
	_('Search Filter'),htmlspecialchars($entry['filter']));

printf('<tr><td>%s</td><td><input type="text" name="attributes" style="width:300px" value="%s" /></td></tr>',
	_('Show Attributtes'),htmlspecialchars($entry['attr']));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" name="sys_attr" id="sys_attr" %s/> <label for="sys_attr">%s</label></td></tr>',
	$entry['sys_attr'] ? 'checked="true" ' : '',_('Include system attributes'));

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
		htmlspecialchars($index),htmlspecialchars($index),($index==$entry['id']) ? ' checked="true"' : '');
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
		htmlspecialchars($id),htmlspecialchars($id),($entry['format']==$id) ? ' checked="true"' : '',
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
