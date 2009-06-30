<?php

require 'common.php';

$file = $_GET['file'];

// Security check (we don't want anyone tryting to get at /etc/passwd or something)
preg_match( "/^djp/", $file ) or 
	pla_error( "Unsafe file name: " . htmlspecialchars( $file ) );

$file = $jpeg_temp_dir . '/' . $file;
file_exists( $file ) or
	pla_error( "No such file: " . htmlspecialchars( $file ) );

// little security measure here (prevents users from accessing
// files, like /etc/passwd for example)
$file = basename( $file );
$file = addcslashes( $file, '/\\' );
$f = fopen( "$jpeg_temp_dir/$file", 'r' );
$jpeg = fread( $f, filesize( "$jpeg_temp_dir/$file" ) );
fclose( $f );

Header( "Content-type: image/jpeg" );
Header( "Content-disposition: inline; filename=jpeg_photo.jpg" );
echo $jpeg;

?>
