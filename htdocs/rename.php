<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rename.php,v 1.33 2007/12/15 07:50:30 wurley Exp $

/**
 * Renames a DN to a different name.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - new_rdn
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));

if (! $_SESSION['plaConfig']->isCommandAvailable('entry_rename'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('rename entry')));

$dn = ($_POST['dn']);
if (! $ldapserver->isBranchRenameEnabled()) {
	# we search all children, not only the visible children in the tree
	$children = $ldapserver->getContainerContents($dn);
	if (count($children) > 0)
		pla_error(_('You cannot rename an entry which has children entries (eg, the rename operation is not allowed on non-leaf entries)'));
}

$new_rdn = ($_POST['new_rdn']);
$container = get_container($dn);
$new_dn = sprintf('%s,%s',$new_rdn,$container);

if ($new_dn == $dn)
	pla_error(_('You did not change the RDN'));

$old_dn_attr = explode('=',$dn);
$old_dn_attr = $old_dn_attr[0];

$new_dn_value = explode('=',$new_rdn,2);

if (count($new_dn_value) != 2 || ! isset($new_dn_value[1]))
	pla_error(_('Invalid RDN value'));

$new_dn_attr = $new_dn_value[0];
$new_dn_value = $new_dn_value[1];

$success = run_hook('pre_rename_entry',array('server_id'=>$ldapserver->server_id,'old_dn'=>$dn,'new_dn'=>$new_dn_value));

if ($success) {
	$success = false;

	$deleteoldrdn = $old_dn_attr == $new_dn_attr;
	$success = $ldapserver->rename($dn,$new_rdn,$container,$deleteoldrdn);

} else {
	pla_error(_('Could not rename the entry') );
}

if ($success) {
	run_hook('post_rename_entry',array('server_id'=>$ldapserver->server_id,'old_dn'=>$dn,'new_dn'=>$new_dn_value));

	$rename_message = sprintf('%s',_('Rename successful!'));
	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$ldapserver->server_id,rawurlencode($new_dn));

	system_message(array(
		'title'=>_('Rename Entry'),
		'body'=>$rename_message,
		'type'=>'info'),
		$redirect_url);
}
?>
