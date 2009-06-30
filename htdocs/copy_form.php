<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/copy_form.php,v 1.24.4.5 2006/04/29 03:27:41 wurley Exp $

/**
 * Copies a given object to create a new one.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in via GET variables
 *  - dn (rawurlencoded)
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

$dn = $_GET['dn'] ;
$rdn = get_rdn($dn);
$attrs = $ldapserver->getDNAttrs($dn);
$select_server_html = server_select_list($ldapserver->server_id,true,'dest_server_id');
$children = $ldapserver->getContainerContents($dn);

include './header.php';

# Draw some javaScrpt to enable/disable the filter field if this may be a recursive copy
if (is_array($children) && count($children) > 0) { ?>

	<script type="text/javascript" language="javascript">
	//<!--
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
	//-->
	</script>

<?php
}

echo '<body>';

printf('<h3 class="title">%s %s</h3>',_('Copy'),htmlspecialchars($rdn));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',_('Server'),$ldapserver->name,
	_('Distinguished Name'),htmlspecialchars($dn));
echo "\n";

echo '<center>';
printf('%s <b>%s</b> %s:<br /><br />',_('Copy'),htmlspecialchars($rdn),_('to a new object'));

echo '<form action="copy.php" method="post" name="copy_form">';
printf('<input type="hidden" name="old_dn" value="%s" />',htmlspecialchars($dn));
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
echo "\n";

echo '<table style="border-spacing: 10px">';
echo "\n";

echo '<tr>';
printf('<td><acronym title="%s">%s</acronym>:</td>',
	_('The full DN of the new entry to be created when copying the source entry'),_('Destination DN'));
printf('<td><input type="text" name="new_dn" size="45" value="%s" />',htmlspecialchars($dn));
draw_chooser_link('copy_form.new_dn','true',htmlspecialchars($rdn));
echo '</td></tr>';
echo "\n";

printf('<tr><td>%s</td><td>%s</td></tr>',_('Destination Server'),$select_server_html);
echo "\n";

if (is_array($children) && count($children) > 0) {
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
	echo '<td><input type="checkbox" name="remove" value="yes"/ disabled>';
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

echo '<script type="text/javascript" language="javascript">';
echo '<!--';
echo '/* If the user uses the back button, this way we draw the filter field properly. */';
echo 'toggle_disable_filter_field(document.copy_form.recursive);';
echo '//-->';
echo '</script>';

if ($config->GetValue('appearance','show_hints'))
	printf('<small><img src="images/light.png" alt="Light" /><span class="hint">%s</span></small>',_('Hint: Copying between different servers only works if there are no schema violations'));

echo '</center></body></html>';
?>
