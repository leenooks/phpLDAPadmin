<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/ldif_import_form.php,v 1.22.2.3 2008/12/12 12:20:22 wurley Exp $
 
/**
 * Displays a form to allow the user to upload and import
 * an LDIF file.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! ini_get('file_uploads'))
	error(_('Your PHP.INI does not have file_uploads = ON. Please enable file uploads in PHP.'),'error','index.php');

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

printf('<h3 class="title" colspan=0>%s</h3>',_('Import LDIF File'));
printf('<h3 class="subtitle" colspan=0>%s: <b>%s</b></h3>',_('Server'),htmlspecialchars($ldapserver->name));
echo '<center>';
echo '<form action="cmd.php" method="post" class="new_value" enctype="multipart/form-data">';
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
echo '<input type="hidden" name="cmd" value="ldif_import" />';

echo '<table class="forminput" border=0>';

echo '<tr><td colspan=2>&nbsp;</td></tr>';
echo '<tr>';
printf('<td>%s</td>',_('Select an LDIF file'));
echo '<td>';
echo '<input type="file" name="ldif_file" />';
echo '</td></tr>';

printf('<tr><td>&nbsp;</td><td class="small"><b>%s %s</b></td></tr>',_('Maximum file size'),ini_get('upload_max_filesize'));

echo '<tr><td colspan=2>&nbsp;</td></tr>';
printf('<tr><td>%s</td></tr>',_('Or paste your LDIF here'));
echo '<tr><td colspan=2><textarea name="ldif" rows="20" cols="100"></textarea></td></tr>';
echo '<tr><td colspan=2>&nbsp;</td></tr>';
printf('<tr><td>&nbsp;</td><td class="small"><input type="checkbox" name="continuous_mode" value="1" />%s</td></tr>',
	_("Don't stop on errors"));
printf('<tr><td>&nbsp;</td><td><input type="submit" value="%s" /></td></tr>',_('Proceed >>'));
echo '</table>';
echo '</form>';
echo '</center>';
?>
