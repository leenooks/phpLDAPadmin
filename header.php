<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/header.php,v 1.15 2005/02/25 13:44:06 wurley Exp $

/**
 * @package phpLDAPadmin
 */

// We want to get $language into scope in case we were included
// from within a function
global $language;

// text/xml won't work with MSIE, but is very useful for debugging xhtml code.
//@header( "Content-type: text/xml; charset=\"UTF-8\"" );
@header( "Content-type: text/html; charset=\"UTF-8\"" );

// XML version and encoding for well-behaved browsers
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language; ?>" lang="<?php echo $language; ?>" dir="ltr">
<head>
	<title>phpLDAPadmin</title>
	<link rel="stylesheet" href="style.css" media="screen" />

<?php if( isset( $server_id ) ) {
	$custom_file = get_custom_file( $server_id, 'style.css' );

	if( strcmp( $custom_file, 'style.css' ) != 0 ) { ?>
	<link rel="stylesheet" href="<?php echo $custom_file ?>" media="screen" />
<?php }
} ?>
	<script src="entry_chooser.js" type="text/javascript"></script>
	<script src="ie_png_work_around.js" type="text/javascript"></script>
	<script src="search_util.js" type="text/javascript"></script>
	<script src="generic_utils.js" type="text/javascript"></script>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
