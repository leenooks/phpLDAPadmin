<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rdelete.php,v 1.28.2.1 2007/12/26 09:26:32 wurley Exp $

/**
 * Recursively deletes the specified DN and all of its children
 *
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

if (! $_SESSION[APPCONFIG]->isCommandAvailable('entry_delete', 'simple_delete'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete entry')));

$entry['dn'] = $_POST['dn'];
if (! $entry['dn'])
	pla_error(_('You must specify a DN'));

if (! $ldapserver->dnExists($entry['dn']))
	pla_error(sprintf(_('No such entry: %s'),htmlspecialchars($entry['dn'])));

printf('<h3 class="title">'._('Deleting %s').'</h3>',htmlspecialchars(get_rdn($entry['dn'])));
printf('<h3 class="subtitle">%s</h3>',_('Recursive delete progress'));
echo '<br /><br />';
echo '<small>';

# Prevent script from bailing early on a long delete
@set_time_limit(0);

$result = pla_rdelete($ldapserver,$entry['dn']);
echo '</small><br />';

if ($result) {
	printf(_('Entry %s and sub-tree deleted successfully.'),'<b>'.htmlspecialchars($entry['dn']).'</b>');

} else {
	pla_error(sprintf(_('Could not delete the entry: %s'),htmlspecialchars($entry['dn'])),
		$ldapserver->error(),$ldapserver->errno());
}

function pla_rdelete($ldapserver,$dn) {
	# we delete all children, not only the visible children in the tree
	$children = $ldapserver->getContainerContents($dn);

	if (! is_array($children) || count($children) == 0) {
		printf('<span style="white-space: nowrap;">%s %s...',_('Deleting'),htmlspecialchars($dn));

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
