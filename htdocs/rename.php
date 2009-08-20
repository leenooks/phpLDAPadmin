<?php
/**
 * Renames a DN to a different name.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DN we are working with
$request = array();
$request['dnSRC'] = get_request('dn','REQUEST');
$request['rdnDST'] = get_request('new_rdn','REQUEST');
$request['container'] = $app['server']->getContainer($request['dnSRC']);

# Error checking
if (! $app['server']->isBranchRenameEnabled()) {
	# We search all children, not only the visible children in the tree
	$children = $app['server']->getContainerContents($request['dnSRC'],null,0,'(objectClass=*)',LDAP_DEREF_NEVER);

	if (count($children) > 0)
		error(_('You cannot rename an entry which has children entries (eg, the rename operation is not allowed on non-leaf entries)'),'error','index.php');
}

$request['dnDST'] = sprintf('%s,%s',$request['rdnDST'],$request['container']);

if ($request['dnDST'] == $request['dnSRC'])
	error(_('You did not change the RDN'),'error','index.php');

$rdnattr = array();
$rdnattr['SRC'] = explode('=',$request['dnSRC']);
$rdnattr['SRC'] = $rdnattr['SRC'][0];

$new_dn_value = explode('=',$request['rdnDST'],2);
$rdnattr['DST'] = $new_dn_value[0];

if (count($new_dn_value) != 2 || ! isset($new_dn_value[1]))
	error(_('Invalid RDN value'),'error','index.php');

$deleteoldrdn = $rdnattr['SRC'] == $rdnattr['DST'];
$success = $app['server']->rename($request['dnSRC'],$request['rdnDST'],$request['container'],$deleteoldrdn);

if ($success) {
	$rename_message = sprintf('%s',_('Rename successful!'));
	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s&template=%s',
		$app['server']->getIndex(),rawurlencode($request['dnDST']),get_request('template','REQUEST'));

	system_message(array(
		'title'=>_('Rename Entry'),
		'body'=>$rename_message,
		'type'=>'info'),
		$redirect_url);

} else {
	system_message(array(
		'title'=>_('Could not rename the entry.'),
		'body'=>ldap_error_msg($app['server']->getErrorMessage(null),$app['server']->getErrorNum(null)),
		'type'=>'error'));
}
?>
