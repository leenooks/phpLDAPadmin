<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/header.php,v 1.21.2.4 2006/04/29 03:14:45 wurley Exp $

/**
 * @package phpLDAPadmin
 */

/* We want to get $language into scope in case we were included
   from within a function */
$language = isset($config) ? $language = $config->GetValue('appearance','language') : 'auto';

# text/xml won't work with MSIE, but is very useful for debugging xhtml code.
# header('Content-type: text/xml; charset="UTF-8"');
@header('Content-type: text/html; charset="UTF-8"');

# XML version and encoding for well-behaved browsers
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n";
echo '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";

printf('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="%s" lang="%s" dir="ltr">',$language,$language);
echo "\n\n";

echo '<head>';

if (isset($config) && $pagetitle = $config->GetValue('appearance','page_title'))
	printf('<title>phpLDAPadmin - %s</title>',$pagetitle);
else
	echo '<title>phpLDAPadmin</title>';

printf('<link type="text/css" rel="stylesheet" href="%sstyle.css" media="screen" />',CSSDIR);

if (isset($server_id)) {
	$custom_file = get_custom_file($server_id,'style.css',CSSDIR);

	if (strcmp($custom_file,'style.css') != 0)
		printf('<link type="text/css" rel="stylesheet" href="%s" media="screen" />',$custom_file);
}

printf('<script type="text/javascript" src="%sentry_chooser.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%sie_png_work_around.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%ssearch_util.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%sgeneric_utils.js"></script>',JSDIR);
printf('<link type="text/css" rel="stylesheet" media="all" href="%s/jscalendar/calendar-blue.css" title="blue" />',JSDIR);
printf('<script type="text/javascript" src="%sjscalendar/calendar.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%sjscalendar/lang/calendar-en.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%sjscalendar/calendar-setup.js"></script>',JSDIR);
printf('<script type="text/javascript" src="%sdate_selector.js"></script>',JSDIR);
printf('<link type="text/css" rel="stylesheet" href="%s/phplayersmenu/layerstreemenu.css"></link>',JSDIR);

if (isset($meta_refresh_variable))
	printf('<meta http-equiv="refresh" content="%s" />',$meta_refresh_variable);

echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
echo '</head>';
echo "\n\n";
?>
