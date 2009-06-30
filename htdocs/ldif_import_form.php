<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/ldif_import_form.php,v 1.20.2.3 2005/12/10 06:55:52 wurley Exp $
 
/**
 * Displays a form to allow the user to upload and import
 * an LDIF file.
 *
 * Variables expected as GET vars:
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! ini_get('file_uploads'))
	pla_error(_('Your PHP.INI does not have file_uploads = ON. Please enable file uploads in PHP.'));

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

include './header.php';

echo '<body>';

printf('<h3 class="title">%s</h3>',_('Import LDIF File'));
printf('<h3 class="subtitle">%s: <b>%s</b></h3>',_('Server'),htmlspecialchars($ldapserver->name));

echo '<br /><br />';
echo _('Select an LDIF file');
echo '<br /><br />';

echo '<form action="ldif_import.php" method="post" class="new_value" enctype="multipart/form-data">';
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
echo '<input type="file" name="ldif_file" /> <br />';
printf('<div style="margin-top: 5px;"><input type="checkbox" name="continuous_mode" value="1" /><span style="font-size: 90%%;">%s</span></div>',_("Don't stop on errors"));
printf('<div style="margin-top:10px;"><input type="submit" value="%s" /></div>',_('Proceed >>'));
printf('<br /><small><b>%s %s</b></small><br />',_('Maximum file size'),ini_get('upload_max_filesize'));
echo '</form>';

echo '<br /><br />';
echo _('Paste your LDIF here');
echo '<form action="ldif_import.php" method="post" class="new_value" enctype="multipart/form-data">';
echo '<textarea name="ldif" rows="10" cols="60"></textarea>';
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
printf('<div style="margin-top: 5px;"><input type="checkbox" name="continuous_mode" value="1" /><span style="font-size: 90%%;">%s</span></div>',_("Don't stop on errors"));
printf('<div style="margin-top:10px;"><input type="submit" value="%s" /></div>',_('Proceed >>'));
echo '</form>';
echo '</body></html>';
?>
