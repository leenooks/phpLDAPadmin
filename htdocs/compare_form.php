<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/compare_form.php,v 1.2.4.3 2006/04/29 03:15:34 wurley Exp $

/**
 * Compares to DN entries side by side.
 *
 *  - dn (rawurlencoded)
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = (isset($_GET['dn']) ? $_GET['dn'] : '');
$rdn = get_rdn($dn);
$select_server_html = server_select_list($ldapserver->server_id,true,'server_id_dst');

include './header.php';

echo '<body>';

printf('<h3 class="title">%s %s</h3>',_('Compare another DN with'),htmlspecialchars($rdn));
printf('<h3 class="subtitle">%s: <b>%s</b>',_('Server'),$ldapserver->name);
if ($dn)
	printf('&nbsp;&nbsp;&nbsp;%s: <b>%s</b>',_('Distinguished Name'),htmlspecialchars($dn));
echo '</h3>';
echo "\n";

echo '<center>';
printf('%s <b>%s</b> %s<br />',_('Compare'),htmlspecialchars($rdn),_('with '));

echo '<form action="compare.php" method="post" name="compare_form">';
printf('<input type="hidden" name="server_id_src" value="%s" />',$ldapserver->server_id);
echo "\n";

echo '<table style="border-spacing: 10px">';
echo "\n";
echo '<tr><td>';

if (! $dn) {
	printf('<acronym title="%s">%s</acronym>:',_('Compare this DN with another'),_('Source DN'));
	echo '</td><td>';
	printf('<input type="text" name="dn_src" size="45" value="%s" />',htmlspecialchars($dn));
	draw_chooser_link('compare_form.dn_src','true',$rdn);

} else
	printf('<input type="hidden" name="dn_src" value="%s" />',htmlspecialchars($dn));

echo '</td></tr>';
echo "\n";

echo '<tr>';
printf('<td><acronym title="%s">%s</acronym>:</td>',_('Compare this DN with another'),_('Destination DN'));
echo '<td>';
echo '<input type="text" name="dn_dst" size="45" value="" />';
draw_chooser_link('compare_form.dn_dst','true','');
echo '</td>';
echo '</tr>';
echo "\n";

printf('<tr><td>%s:</td><td>%s</td></tr>',_('Destination Server'),$select_server_html);
echo "\n";

printf('<tr><td colspan="2" align="right"><input type="submit" value="%s" /></td></tr>',_('Compare'));
echo "\n";

echo '</table>';
echo '</form>';
echo '</center>';
echo '</body>';
echo '</html>';
?>
