<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/header.php,v 1.19.2.2 2005/10/25 20:21:15 wurley Exp $

/**
 * @package phpLDAPadmin
 */

// We want to get $language into scope in case we were included
// from within a function
global $config;
$language = isset($config) ? $language =  $config->GetValue('appearance','language') : 'auto';

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
<?php if (isset($config) && $pagetitle = $config->GetValue('appearance','page_title')) { ?>
	<title>phpLDAPadmin - <?php echo $pagetitle; ?></title>
<?php } else { ?>
	<title>phpLDAPadmin</title>
<?php } ?>
	<link rel="stylesheet" href="<?php echo CSSDIR ?>style.css" media="screen" />

<?php if( isset( $server_id ) ) {
	$custom_file = get_custom_file( $server_id, 'style.css',CSSDIR );

	if( strcmp( $custom_file, 'style.css' ) != 0 ) { ?>
	<link rel="stylesheet" href="<?php echo $custom_file ?>" media="screen" />
<?php }
} ?>
	<script src="<?php echo JSDIR; ?>entry_chooser.js" type="text/javascript"></script>
	<script src="<?php echo JSDIR; ?>ie_png_work_around.js" type="text/javascript"></script>
	<script src="<?php echo JSDIR; ?>search_util.js" type="text/javascript"></script>
	<script src="<?php echo JSDIR; ?>generic_utils.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="blue" />
	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
	<script type="text/javascript" src="<?php echo JSDIR; ?>date_selector.js"></script>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
