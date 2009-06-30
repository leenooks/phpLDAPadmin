<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rename.php,v 1.29.2.4 2008/11/28 14:21:37 wurley Exp $

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
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = ($_POST['dn']);
if (! $ldapserver->isBranchRenameEnabled()) {
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

$success = run_hook('pre_rename_entry',array('server_id'=>$ldapserver->server_id,'old_dn'=>dn_escape($dn),'new_dn'=>dn_escape($new_dn_value)));

if ($success) {
	$success = false;

	$deleteoldrdn = $old_dn_attr == $new_dn_attr;
	$success = $ldapserver->rename(dn_escape($dn),dn_escape($new_rdn),$container,$deleteoldrdn);

} else {
	pla_error(_('Could not rename the entry') );
}

if ($success) {
	run_hook('post_rename_entry',array('server_id'=>$ldapserver->server_id,'old_dn'=>$dn,'new_dn'=>$new_dn_value));

	$edit_url = sprintf('template_engine.php?server_id=%s&dn=%s',$ldapserver->server_id,rawurlencode($new_dn));

	echo '<html><head>';
	echo '<!-- refresh the tree view (with the new DN renamed) and redirect to the edit_dn page -->';
	printf('<script language="javascript">parent.left_frame.location.reload();location.href="%s";</script>',$edit_url);
	echo "<!-- If the JavaScript didn't work, here's a meta tag to do the job -->";
	printf('<meta http-equiv="refresh" content="0; url=%s" />',$edit_url);
	echo '</head><body>';

	printf('%s <a href="%s">%s</a>',_('Redirecting...'),$edit_url,_('here'));
	echo '</body></html>';
}
?>
