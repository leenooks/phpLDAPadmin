<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/copy_form.php,v 1.30.2.3 2008/12/12 12:20:22 wurley Exp $

/**
 * Copies a given object to create a new one.
 *
 * Variables that come in via GET variables
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

$entry = array();
$entry['dn'] = get_request('dn','GET');
$entry['rdn'] = get_rdn($entry['dn']);

# We search all children, not only the visible children in the tree
$entry['children'] = $ldapserver->getContainerContents($entry['dn']);

# Draw some javaScrpt to enable/disable the filter field if this may be a recursive copy
if (is_array($entry['children']) && count($entry['children']) > 0) { ?>

	<script type="text/javascript" language="javascript">
	function toggle_disable_filter_field(recursive_checkbox)
	{
		if (recursive_checkbox.checked) {
			recursive_checkbox.form.remove.disabled = false;
			recursive_checkbox.form.filter.disabled = false;
		} else {
			recursive_checkbox.form.remove.disabled = true;
			recursive_checkbox.form.remove.checked = false;
			recursive_checkbox.form.filter.disabled = true;
		}
	}
	</script>

<?php }

printf('<h3 class="title">%s %s</h3>',_('Copy'),htmlspecialchars($entry['rdn']));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',_('Server'),$ldapserver->name,
	_('Distinguished Name'),htmlspecialchars($entry['dn']));
echo "\n";

echo '<center>';
printf('%s <b>%s</b> %s:<br /><br />',_('Copy'),htmlspecialchars($entry['rdn']),_('to a new object'));

echo '<form action="cmd.php" method="post" name="copy_form">';
echo '<input type="hidden" name="cmd" value="copy" />';
printf('<input type="hidden" name="old_dn" value="%s" />',htmlspecialchars($entry['dn']));
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
echo "\n";

echo '<table style="border-spacing: 10px">';
echo "\n";

echo '<tr>';
printf('<td><acronym title="%s">%s</acronym>:</td>',
	_('The full DN of the new entry to be created when copying the source entry'),_('Destination DN'));
printf('<td><input type="text" name="new_dn" size="45" value="%s" />',htmlspecialchars($entry['dn']));
draw_chooser_link('copy_form.new_dn','true',htmlspecialchars($entry['rdn']));
echo '</td></tr>';
echo "\n";

printf('<tr><td>%s</td><td>%s</td></tr>',_('Destination Server'),server_select_list($ldapserver->server_id,true,'dest_server_id'));
echo "\n";

if (is_array($entry['children']) && count($entry['children']) > 0) {
	echo '<tr>';
	printf('<td><label for="recursive">%s</label>:</td>',_('Recursive copy'));
	echo '<td><input type="checkbox" id="recursive" name="recursive" onClick="toggle_disable_filter_field(this)" />';
	printf('<small>(%s)</small></td>',_('Recursively copy all children of this object as well.'));
	echo '</tr>'."\n";

	echo '<tr>';
	printf('<td><acronym title="%s">%s</acronym>:</td>',_('When performing a recursive copy, only copy those entries which match this filter'),_('Filter'));
	echo '<td><input type="text" name="filter" value="(objectClass=*)" size="45" disabled />';
	echo '</tr>'."\n";

	echo '<tr>';
	printf('<td>%s</td>',_('Delete after copy (move):'));
	echo '<td><input type="checkbox" name="remove" value="yes" disabled />';
	printf('<small>(%s)</small)</td>',_('Make sure your filter (above) will select all child records.'));
	echo '</tr>';

} else {
	printf('<tr><td>%s</td><td><input type="checkbox" name="remove" value="yes"/></td></tr>',_('Delete after copy (move):'));
}
echo "\n";

printf('<tr><td colspan="2" align="right"><input type="submit" value="%s" /></td></tr>',_('Copy '));
echo "\n";
echo '</table></form>';
echo "\n";

if ($_SESSION[APPCONFIG]->GetValue('appearance','show_hints'))
	printf('<small><img src="%s/light.png" alt="Light" /><span class="hint">%s</span></small>',IMGDIR,_('Hint: Copying between different servers only works if there are no schema violations'));

echo '</center>';
?>
