<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/download_binary_attr.php,v 1.15.2.1 2007/12/26 09:26:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * Variables that come in via common.php
 *  - server_id
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = rawurldecode($_GET['dn']);
$attr = $_GET['attr'];

# if there are multiple values in this attribute, which one do you want to see?
$value_num = isset($_GET['value_num']) ? $_GET['value_num'] : null;

if (! $ldapserver->dnExists($dn))
	pla_error(sprintf(_('No such entry: %s'),pretty_print_dn($dn)));

$search = $ldapserver->search(null,$dn,'(objectClass=*)',array($attr),'base',false,$_SESSION[APPCONFIG]->GetValue('deref','view'));

# Dump the binary data to the browser
if (ob_get_level()) ob_end_clean();
header('Content-type: octet-stream');
header("Content-disposition: attachment; filename=$attr");
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
if ($value_num && is_array($search[$attr][$dn]))
	echo $search[$dn][$attr][$value_num];
else
	echo $search[$dn][$attr];
?>
