<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/view_jpeg_photo.php,v 1.9.4.2 2005/12/08 11:58:14 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$file = $_GET['file'];

/* Security check (we don't want anyone tryting to get at /etc/passwd or something)
   Slashes and dots are not permitted in these names.*/
if (! preg_match('/^pla/',$file) || preg_match('/[\.\/\\\\]/',$file))
	pla_error(sprintf('%s %s',_('Unsafe file name: '),htmlspecialchars($file)));

/* Little security measure here (prevents users from accessing
   files, like /etc/passwd for example).*/
$file = basename(addcslashes($file,'/\\'));
$file = sprintf('%s/%s',$config->GetValue('jpeg','tmpdir'),$file);
if (! file_exists($file))
	pla_error(sprintf('%s %s',_('No such file: '),htmlspecialchars($_GET['file'])));

$f = fopen($file,'r');
$jpeg = fread($f,filesize($file));
fclose($f);

Header('Content-type: image/jpeg');
Header('Content-disposition: inline; filename=jpeg_photo.jpg');
echo $jpeg;
?>
