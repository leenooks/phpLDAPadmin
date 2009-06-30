<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/index.php,v 1.42.2.10 2006/03/08 22:49:27 wurley Exp $

/**
 * @package phpLDAPadmin
 */

/*******************************************
<pre>

If you are seeing this in your browser,
PHP is not installed on your web server!!!

</pre>
*******************************************/

/**
 * We will perform some sanity checking here, since this file is normally loaded first when users
 * first setup PLA.
 */
define('LIBDIR','../lib/');
ini_set('display_errors',1);
error_reporting(E_ALL);

# General functions needed to proceed.
ob_start();
if (! file_exists(LIBDIR.'functions.php')) {
	ob_end_clean();
	die("Fatal error: Required file 'functions.php' does not exist.");
}

if (! is_readable(LIBDIR.'functions.php')) {
	ob_end_clean();
	die("Cannot read the file 'functions.php' its permissions are too strict.");
}

require LIBDIR.'functions.php';
$config_file = CONFDIR.'config.php';
ob_end_clean();

# Make sure this PHP install has gettext, we use it for language translation
if (! extension_loaded('gettext'))
	die('Your install of PHP appears to be missing GETTEXT support. GETTEXT is used for language translation. Please install GETTEXT support before using phpLDAPadmin. (Dont forget to restart your web server afterwards)');

/* Helper functions.
 * Our required helper functions are defined in functions.php
 */
foreach ($pla_function_files as $file_name ) {
	if (! file_exists($file_name))
		pla_error(sprintf('Fatal error: Required file "%s" does not exist.',$file_name));

	if (! is_readable($file_name))
		pla_error(sprintf('Fatal error: Cannot read the file "%s", its permissions are too strict.',$file_name));

	ob_start();
	require $file_name;
	ob_end_clean();
}

# Configuration File check
if (! file_exists($config_file)) {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"';
	echo '"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';

	echo '<html>';
	echo '<head>';
	printf('<title>phpLDAPadmin - %s</title>',pla_version());
	echo '<link type="text/css" rel="stylesheet" href="css/style.css" />';
	echo '</head>';

	echo '<body>';
	echo '<h3 class="title">Configure phpLDAPadmin</h3>';
	echo '<br /><br />';

	echo '<center>';
	printf(_('You need to configure phpLDAPadmin. Edit the file "%s" to do so. An example config file is provided in "%s.example".'),$config_file,$config_file);
	echo '</center>';

	echo '</body>';
	echo '</html>';
	die();

} elseif (! is_readable($config_file)) {
	pla_error(sprintf('Fatal error: Cannot read your configuration file "%s", its permissions are too strict.',$config_file));
}

if (! check_config()) {
	exit;
}

echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"'."\n";
echo '  "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">'."\n";
echo "\n";

echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="no-NO">';

if ($pagetitle = $config->GetValue('appearance','page_title'))
	printf('<head><title>phpLDAPadmin (%s) - %s</title></head>',pla_version(),$pagetitle);
else
	printf('<head><title>phpLDAPadmin - %s</title></head>',pla_version());

printf('<frameset cols="%s,*">',$config->GetValue('appearance','tree_width'));
echo '<frame src="tree.php" name="left_frame" id="left_frame" />';
echo '<frame src="welcome.php" name="right_frame" id="right_frame" />';
echo '</frameset>';

echo '</html>';

/*
 * Makes sure that the config file is properly setup and
 * that your install of PHP can handle LDAP stuff.
 */
function check_config() {
	global $config_file,$config;

	/* Check for syntax errors in config.php
	   As of php 4.3.5, this NO longer catches fatal errors :( */
	ob_start();
	include $config_file;
	$str = ob_get_contents();
	ob_end_clean();

	if ($str) {
		$str = strip_tags($str);
		$matches = array();
		preg_match('/(.*):\s+(.*):.*\s+on line (\d+)/',$str,$matches);
		$error_type = $matches[1];
		$error = $matches[2];
		$line_num = $matches[3];

		$file = file($config_file);

		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"'."\n";
		echo '  "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">'."\n";
		echo "\n";

		echo '<html>';
		echo '<head>';
		echo '<title>phpLDAPadmin Config File Error</title>';
		echo '<link type="text/css" rel="stylesheet" href="css/style.css" />';
		echo '</head>';

		echo '<body>';
		echo '<h3 class="title">Config File ERROR</h3>';
		printf('<h3 class="subtitle">%s (%s) on line %s</h3>',$error_type,$error,$line_num);

		echo '<center>';
		printf('Looks like your config file has an ERROR on line %s.<br />',$line_num);
		echo 'Here is a snippet around that line <br />';
		echo '<br />'."\n";

		echo '<div style="text-align: left; font-family: monospace; margin-left: 80px; margin-right: 80px; border: 1px solid black; padding: 10px;">';

		for ($i = $line_num-9; $i<$line_num+5; $i++) {
			if ($i+1 == $line_num)
				echo '<div style="color:red;background:#fdd">';

			if ($i < 0)
				continue;

			printf('<b>%s</b>: %s<br />',$i+1,htmlspecialchars($file[$i]));

			if ($i+1 == $line_num)
				echo '</div>';
		}

		echo '</div>';
		echo '<br />';
		echo 'Hint: Sometimes these errors are caused by lines <b>preceding</b> the line reported.';
		echo '</center>';
		echo '</body>';
		echo '</html>';

		return false;
	}

	# Now read in config_default.php, which also reads in config.php
	require LIBDIR.'config_default.php';

	# Make sure their PHP version is current enough
	if (strcmp(phpversion(),REQUIRED_PHP_VERSION) < 0) {
		pla_error(sprintf('phpLDAPadmin requires PHP version %s or greater. You are using %s',
			REQUIRED_PHP_VERSION,phpversion()));
	}

	# Make sure this PHP install has all our required extensions
	if (! extension_loaded('ldap')) {
		pla_error('Your install of PHP appears to be missing LDAP support. Please install LDAP support before using phpLDAPadmin. (Dont forget to restart your web server afterwards)');
		return false;
	}

	# Make sure that we have php-xml loaded.
	if (! function_exists('xml_parser_create')) {
		pla_error('Your install of PHP appears to be missing XML support. Please install XML support before using phpLDAPadmin. (Dont forget to restart your web server afterwards)');
		return false;
	}

	# Make sure their session save path is writable, if they are using a file system session module, that is.
	if ( ! strcasecmp('Files',session_module_name() && ! is_writable(realpath(session_save_path())))) {
		pla_error('Your PHP session configuration is incorrect. Please check the value of session.save_path
			in your php.ini to ensure that the directory specified there exists and is writable.
			The current setting of "'.session_save_path().'" is un-writable by the web server.');
		return false;
	}

	if (! isset($ldapservers) || count($ldapservers->GetServerList()) == 0) {
		pla_error('Your config.php is missing Server Definitions.
			Please see the sample file config/config.php.example.',false);
		return false;
	}

	return true;
}
?>
