<?php
/**
 * Displays a form to allow the user to upload and import
 * an LDIF file.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

if (! ini_get('file_uploads'))
	error(_('Your PHP.INI does not have file_uploads = ON. Please enable file uploads in PHP.'),'error','index.php');

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->drawTitle(sprintf('<b>%s</b>',_('Import')));
$request['page']->drawSubTitle(sprintf('%s: <b>%s</b>',_('Server'),$app['server']->getName()));

echo '<form action="cmd.php" method="post" class="new_value" enctype="multipart/form-data">';
echo '<div>';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
echo '<input type="hidden" name="cmd" value="import" />';
echo '</div>';

echo '<table class="forminput" border="0" style="margin-left: auto; margin-right: auto;">';

echo '<tr><td colspan="2">&nbsp;</td></tr>';
echo '<tr>';
printf('<td>%s</td>',_('Select an LDIF file'));
echo '<td>';
echo '<input type="file" name="ldif_file" />';
echo '</td></tr>';

printf('<tr><td>&nbsp;</td><td class="small"><b>%s %s</b></td></tr>',_('Maximum file size'),ini_get('upload_max_filesize'));

echo '<tr><td colspan="2">&nbsp;</td></tr>';
printf('<tr><td>%s</td></tr>',_('Or paste your LDIF here'));
echo '<tr><td colspan="2"><textarea name="ldif" rows="20" cols="100"></textarea></td></tr>';
echo '<tr><td colspan="2">&nbsp;</td></tr>';
printf('<tr><td>&nbsp;</td><td class="small"><input type="checkbox" name="continuous_mode" value="1" />%s</td></tr>',
	_("Don't stop on errors"));
printf('<tr><td>&nbsp;</td><td><input type="submit" value="%s" /></td></tr>',_('Proceed >>'));
echo '</table>';
echo '</form>';
?>
