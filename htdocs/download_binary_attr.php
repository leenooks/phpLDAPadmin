<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/download_binary_attr.php,v 1.15.2.4 2008/12/12 12:20:22 wurley Exp $

/**
 * @package phpLDAPadmin
 * Variables that come in via common.php
 *  - server_id
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

if (! $ldapserver->haveAuthInfo())
	error(_('Not enough information to login to server. Please check your configuration.'),'error','index.php');

$dn = rawurldecode(get_request('dn','GET'));
$attr = get_request('attr','GET');

# if there are multiple values in this attribute, which one do you want to see?
$value_num = get_request('value_num','GET');

if (! $ldapserver->dnExists($dn))
	error(sprintf('%s (%s)',_('No such entry.'),pretty_print_dn($dn)),'error','index.php');

$search = $ldapserver->search(null,$dn,'(objectClass=*)',array($attr),'base',false,$_SESSION[APPCONFIG]->GetValue('deref','view'));

# Dump the binary data to the browser
$obStatus = ob_get_status();
if (isset($obStatus['type']) && $obStatus['type'] && $obStatus['status']) 
	ob_end_clean();

header('Content-type: octet-stream');
header("Content-disposition: attachment; filename=$attr");
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

if ($value_num && is_array($search[$attr][$dn]))
	echo $search[$dn][$attr][$value_num];
else
	echo $search[$dn][$attr];
?>
