<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rdelete.php,v 1.23.2.3 2007/03/18 03:16:05 wurley Exp $

/**
 * Recursively deletes the specified DN and all of its children
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
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

$dn = $_POST['dn'];
if (! $dn)
	pla_error(_('You must specify a DN'));

if (! $ldapserver->dnExists($dn))
	pla_error(sprintf(_('No such entry: %s'),htmlspecialchars($dn)));

include './header.php';

echo '<body>';
printf('<h3 class="title">'._('Deleting %s').'</h3>',htmlspecialchars(get_rdn($dn)));
printf('<h3 class="subtitle">%s</h3>',_('Recursive delete progress'));
echo '<br /><br />';
echo '<small>';

flush();

# prevent script from bailing early on a long delete
@set_time_limit(0);

$del_result = pla_rdelete($ldapserver,$dn);
echo '</small><br />';

if ($del_result) {
	echo '<script language="javascript">parent.left_frame.location.reload();</script>';
	printf(_('Entry %s and sub-tree deleted successfully.'),'<b>'.htmlspecialchars($dn).'</b>');

} else {
        pla_error(sprintf(_('Could not delete the entry: %s'),htmlspecialchars($dn)),
		  $ldapserver->error(),$ldapserver->errno());
}

function pla_rdelete($ldapserver,$dn) {
	$children = $ldapserver->getContainerContents($dn);

	if (! is_array($children) || count($children) == 0) {
		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),htmlspecialchars($dn));
		flush();

		if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn)))
			if ($ldapserver->delete($dn)) {
		                run_hook('post_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn));
				printf(' <span style="color:green">%s</span></span><br />',_('Success'));
				return true;

			} else {
			        pla_error(sprintf(_('Failed to delete entry %s'),htmlspecialchars($dn)),
					  $ldapserver->error(),$ldapserver->errno());
			}
	} else {
		foreach ($children as $child_dn)
			pla_rdelete($ldapserver,$child_dn);

		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),htmlspecialchars($dn));
		flush();

		if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn)))
			if ($ldapserver->delete($dn)) {
		                run_hook('post_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn));
				printf(' <span style="color:green">%s</span></span><br />',_('Success'));
				return true;

			} else {
			        pla_error(sprintf(_('Failed to delete entry %s'),htmlspecialchars($dn)),
					  $ldapserver->error(),$ldapserver->errno());
			}
	}
}
?>
