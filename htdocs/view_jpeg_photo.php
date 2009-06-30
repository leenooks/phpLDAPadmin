<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/view_jpeg_photo.php,v 1.11.2.3 2008/12/12 12:20:22 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$file = array();
$file['name'] = get_request('file','GET');

/* Security check (we don't want anyone tryting to get at /etc/passwd or something)
 * Slashes and dots are not permitted in these names.
 */
if (! preg_match('/^pla/',$file['name']) || preg_match('/[\.\/\\\\]/',$file['name']))
	error(sprintf('%s: %s',_('Unsafe file name'),htmlspecialchars($file['name'])),'error','index.php');

/* Little security measure here (prevents users from accessing
   files, like /etc/passwd for example).*/
$file['name'] = basename(addcslashes($file['name'],'/\\'));
$file['name'] = sprintf('%s/%s',$_SESSION[APPCONFIG]->GetValue('jpeg','tmpdir'),$file['name']);
if (! file_exists($file['name']))
	error(sprintf('%s%s %s',_('No such file'),_(':'),htmlspecialchars($file['name'])),'error','index.php');

$file['handle'] = fopen($file['name'],'r');
$file['data'] = fread($file['handle'],filesize($file['name']));
fclose($file['handle']);

$obStatus = ob_get_status();
if (isset($obStatus['type']) && $obStatus['type'] && $obStatus['status'])
	ob_end_clean();

Header('Content-type: image/jpeg');
Header('Content-disposition: inline; filename=jpeg_photo.jpg');
echo $file['data'];
?>
