<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete.php,v 1.24.2.3 2005/12/11 08:21:03 wurley Exp $

/**
 * Deletes a DN and presents a "job's done" message.
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

if (is_null($dn))
	pla_error(_('You must specify a DN'));

if (! $ldapserver->dnExists($dn))
	pla_error(sprintf(_('No such entry: %s'),'<b>'.pretty_print_dn($dn).'</b>'));

# Check the user-defined custom callback first.
if (run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn)))
	$del_result = $ldapserver->delete($dn);
else
	pla_error(sprintf(_('Could not delete the entry: %s'),'<b>'.pretty_print_dn($dn).'</b>'));

if ($del_result) {
	# Custom callback
	run_hook('post_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn));

	include './header.php';
	echo '<body>';

	echo '<script type="text/javascript" language="javascript">parent.left_frame.location.reload();</script>';
	echo '<br /><br />';
	printf('<center>'._('Entry %s deleted successfully.').'</center>','<b>'.pretty_print_dn($dn).'</b>');
	echo '</body>';

} else {
	pla_error(sprintf(_('Could not delete the entry: %s'),'<b>'.pretty_print_dn($dn).'</b>'),
		  $ldapserver->error(),$ldapserver->errno());
}
echo '</html>';
?>
