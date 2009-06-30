<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/view_jpeg_photo.php,v 1.9 2005/07/16 03:13:54 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$file = $_GET['file'];

// Security check (we don't want anyone tryting to get at /etc/passwd or something)
preg_match( "/^pla/", $file ) or 
	pla_error( $lang['unsafe_file_name'] . htmlspecialchars( $file ) );

// Slashes and dots are not permitted in these names:
if( preg_match( "/[\.\/\\\\]/", $file ) )
	pla_error( $lang['unsafe_file_name'] . htmlspecialchars( $file ) );

// little security measure here (prevents users from accessing
// files, like /etc/passwd for example)
$file = basename( $file );
$file = addcslashes( $file, '/\\' );
$file = sprintf('%s/%s',$config->GetValue('jpeg','tmpdir'),$file);
file_exists( $file ) or
	pla_error( $lang['no_such_file'] . htmlspecialchars( $_GET['file'] ) );

$f = fopen( $file, 'r' );
$jpeg = fread( $f, filesize( $file ) );
fclose( $f );

Header( "Content-type: image/jpeg" );
Header( "Content-disposition: inline; filename=jpeg_photo.jpg" );
echo $jpeg;
?>
