<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rename_form.php,v 1.8.4.3 2006/04/29 03:29:19 wurley Exp $

/**
 * Displays a form for renaming an LDAP entry.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
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

$dn = $_GET['dn'];
$rdn = get_rdn($dn);

include './header.php';

echo '<body>';
printf('<h3 class="title">%s <b>%s</b></h3>',_('Rename Entry'),htmlspecialchars($rdn));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));

echo '<br /><center><form action="rename.php" method="post" class="edit_dn" />';
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));
printf('<input type="text" name="new_rdn" size="30" value="%s" />',htmlspecialchars($rdn));
printf('<input class="update_dn" type="submit" value="%s" />',_('Rename'));
echo '</form></center>';
echo '</body></html>';
?>
