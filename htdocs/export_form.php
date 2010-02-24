<?php
/**
 * Export entries from the LDAP server.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';
require LIBDIR.'export_functions.php';

$request = array();
$request['dn'] = get_request('dn','GET');
$request['format'] = get_request('format','GET',false,get_line_end_format());
$request['scope'] = get_request('scope','GET',false,'base');
$request['exporter_id'] = get_request('exporter_id','GET',false,'LDIF');
$request['filter'] = get_request('filter','GET',false,'(objectClass=*)');
$request['attr'] = get_request('attributes','GET',false,'*');
$request['sys_attr'] = get_request('sys_attr','GET') ? true: false;

$available_formats = array(
	'mac'  => 'Macintosh',
	'unix' => 'UNIX (Linux, BSD)',
	'win'  => 'Windows'
);

$available_scopes = array(
	'base' => _('Base (base dn only)'),
	'one' => _('One (one level beneath base)'),
	'sub' => _('Sub (entire subtree)')
);

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->drawTitle(sprintf('<b>%s</b>',_('Export')));

printf('<script type="text/javascript" src="%sdnChooserPopup.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%sform_field_toggle_enable.js"></script>',JSDIR);

echo '<br />';
echo '<form id="export_form" action="cmd.php" method="post">';
echo '<div>';
echo '<input type="hidden" name="cmd" value="export" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());

echo '<table class="forminput" style="margin-left: auto; margin-right: auto;">';
echo '<tr>';
echo '<td>';

echo '<fieldset>';
printf('<legend>%s</legend>',_('Export'));

echo '<table>';
printf('<tr><td>%s</td><td>%s</td></tr>',_('Server'),$app['server']->getName());

echo '<tr>';
printf('<td style="white-space:nowrap">%s</td>',_('Base DN'));
echo '<td><span style="white-space: nowrap;">';
printf('<input type="text" name="dn" id="dn" style="width:230px" value="%s" />&nbsp;',htmlspecialchars($request['dn']));
draw_chooser_link('export_form','dn');
echo '</span></td>';
echo '</tr>';

echo '<tr>';
printf('<td><span style="white-space: nowrap">%s</span></td>',_('Search Scope'));

echo '<td>';

foreach ($available_scopes as $id => $desc)
	printf('<input type="radio" name="scope" value="%s" id="%s"%s /><label for="%s">%s</label><br />',
		htmlspecialchars($id),$id,($id == $request['scope']) ? 'checked="checked"' : '',
		htmlspecialchars($id),$desc);

echo '</td>';

echo '</tr>';

printf('<tr><td>%s</td><td><input type="text" name="filter" style="width:300px" value="%s" /></td></tr>',
	_('Search Filter'),htmlspecialchars($request['filter']));

printf('<tr><td>%s</td><td><input type="text" name="attributes" style="width:300px" value="%s" /></td></tr>',
	_('Show Attributtes'),htmlspecialchars($request['attr']));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" name="sys_attr" id="sys_attr" %s/> <label for="sys_attr">%s</label></td></tr>',
	$request['sys_attr'] ? 'checked="checked" ' : '',_('Include system attributes'));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" id="save_as_file" name="save_as_file" onclick="export_field_toggle(this)" /> <label for="save_as_file">%s</label></td></tr>',
	_('Save as file'));

printf('<tr><td>&nbsp;</td><td><input type="checkbox" id="compress" name="compress" disabled="disabled" /> <label for="compress">%s</label></td></tr>',
	_('Compress'));

echo '</table>';
echo '</fieldset>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td>';

echo '<table style="width: 100%">';
echo '<tr>';

echo '<td style="width: 50%">';
echo '<fieldset style="height: 100px">';

printf('<legend>%s</legend>',_('Export format'));

foreach (Exporter::types() as $index => $exporter) {
	printf('<input type="radio" name="exporter_id" id="exporter_id_%s" value="%s"%s/>',
		htmlspecialchars($exporter['type']),htmlspecialchars($exporter['type']),($exporter['type'] === $request['exporter_id']) ? ' checked="checked"' : '');

	printf('<label for="exporter_id_%s">%s</label><br />',
		htmlspecialchars($exporter['type']),$exporter['type']);
}

echo '</fieldset>';
echo '</td>';

echo '<td style="width: 50%">';
echo '<fieldset style="height: 100px">';

printf('<legend>%s</legend>',_('Line ends'));
foreach ($available_formats as $id => $desc)
	printf('<input type="radio" name="format" value="%s" id="%s"%s /><label for="%s">%s</label><br />',
		htmlspecialchars($id),htmlspecialchars($id),($request['format']==$id) ? ' checked="checked"' : '',
		htmlspecialchars($id),$desc);

echo '</fieldset>';
echo '</td></tr>';
echo '</table>';
echo '</td>';

echo '</tr>';

printf('<tr><td colspan="2" style="text-align: center;"><input type="submit" name="target" value="%s" /></td></tr>',
	htmlspecialchars(_('Proceed >>')));

echo '</table>';

echo '</div>';
echo '</form>';

/**
 * Helper function for fetching the line end format.
 *
 * @return String 'win', 'unix', or 'mac' based on the user's browser..
 */
function get_line_end_format() {
	if (is_browser('win'))
		return 'win';
	elseif (is_browser('unix'))
		return 'unix';
	elseif (is_browser('mac'))
		return 'mac';
	else
		return 'unix';
}

/**
 * Gets the USER_AGENT string from the $_SERVER array, all in lower case in
 * an E_NOTICE safe manner.
 *
 * @return string|false The user agent string as reported by the browser.
 */
function get_user_agent_string() {
	if (isset($_SERVER['HTTP_USER_AGENT']))
		return strtolower($_SERVER['HTTP_USER_AGENT']);
	else
		return '';
}

/**
 * Determine the OS for the browser
 */
function is_browser($type) {
	$agents = array();

	$agents['unix'] = array(
		'sunos','sunos 4','sunos 5',
		'i86',
		'irix','irix 5','irix 6','irix6',
		'hp-ux','09.','10.',
		'aix','aix 1','aix 2','aix 3','aix 4',
		'inux',
		'sco',
		'unix_sv','unix_system_v','ncr','reliant','dec','osf1',
		'dec_alpha','alphaserver','ultrix','alphastation',
		'sinix',
		'freebsd','bsd',
		'x11','vax','openvms'
	);

	$agents['win'] = array(
		'win','win95','windows 95',
		'win16','windows 3.1','windows 16-bit','windows','win31','win16','winme',
		'win2k','winxp',
		'win98','windows 98','win9x',
		'winnt','windows nt','win32',
		'32bit'
	);

	$agents['mac'] = array(
		'mac','68000','ppc','powerpc'
	);

	if (isset($agents[$type]))
		return in_array(get_user_agent_string(),$agents[$type]);
	else
		return false;
}
?>
