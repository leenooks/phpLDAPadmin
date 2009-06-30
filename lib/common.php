<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/common.php,v 1.76.2.8 2007/01/27 13:21:35 wurley Exp $

/**
 * Contains code to be executed at the top of each phpLDAPadmin page.
 * include this file at the top of every PHP file.
 *
 * This file will "pre-initialise" a PLA environment so that any PHP file will have a consistent
 * environment with other PLA PHP files.
 *
 * This code WILL NOT check that all required functions are usable/readable, etc. This process has
 * been moved to index.php (which really is only called once when a browser hits PLA for the first time).
 *
 * The list of ADDITIONAL function files is now defined in functions.php.
 *
 * @package phpLDAPadmin
 */

$timer = stopwatch();

@define('LIBDIR','../lib/');

# For PHP5 backward/forward compatibility
if (! defined('E_STRICT'))
	define('E_STRICT',2048);

# General functions needed to proceed.
ob_start();
require_once realpath(LIBDIR.'functions.php');
ob_end_clean();

/* Our custom error handler receives all error notices that pass the error_reporting()
   level set above. */
set_error_handler('pla_error_handler');
# Disable error reporting until all our required functions are loaded.
error_reporting(0);

/*
 * functions.php should have defined our pla_function_files array, listing all our
 * required functions (order IS important).
 * index.php should have checked they exist and are usable - we'll assume that the user
 * has been via index.php, and fixed any problems already.
 */
ob_start();
foreach ($pla_function_files as $file_name) {
	require_once realpath ($file_name);
}

# Now read in config_default.php, which also reads in config.php
require_once realpath(LIBDIR.'config_default.php');
ob_end_clean();

# We are now ready for error reporting.
# Turn on all notices and warnings. This helps us write cleaner code (we hope at least)
if (phpversion() >= '5') {
	# E_DEBUG is PHP5 specific and prevents warnings about using 'var' to declare class members
	error_reporting(E_DEBUG);
} else
	# For PHP4
	error_reporting(E_ALL);

/**
 * At this point we have read all our additional function PHP files and our configuration.
 */

# Check our custom variables.
$config->CheckCustom();

if (pla_session_start())
	run_hook('post_session_init',array());

/*
 * Language configuration. Auto or specified?
 * Shall we attempt to auto-determine the language?
 */

$language = $config->GetValue('appearance','language');

if ($language == 'auto') {

	# Make sure their browser correctly reports language. If not, skip this.
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

		# Get the languages which are spetcified in the HTTP header
		$HTTP_LANGS = preg_split ('/[;,]+/',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach ($HTTP_LANGS as $key => $value) {
			if (substr($value,0,2) == 'q=') {
				unset($HTTP_LANGS[$key]);
				continue;
			}

			$value = preg_split('/[-]+/',$value);
			if (sizeof($value) == 2)
				$HTTP_LANGS[$key] = strtolower($value[0]).'_'.strtoupper($value[1]);
			else
				$HTTP_LANGS[$key] = auto_lang(strtolower($value[0]));
		}

		$HTTP_LANGS = array_unique($HTTP_LANGS);

		foreach ($HTTP_LANGS as $HTTP_LANG) {
			# Try to grab one after the other the language file
			$language_file = LANGDIR.$HTTP_LANG;

			if ((substr($HTTP_LANG,0,2) == 'en') ||
				(file_exists($language_file) && is_readable($language_file))) {

				# Set language
				putenv('LANG='.$HTTP_LANG); # e.g. LANG=de_DE
				$HTTP_LANG .= '.UTF-8';
				setlocale(LC_ALL,$HTTP_LANG); # set LC_ALL to de_DE
				bindtextdomain('messages',LANGDIR);
				bind_textdomain_codeset('messages','UTF-8');
				textdomain('messages');
				header('Content-type: text/html; charset=UTF-8',true);
				break;
			}
		}
	}

} else {
	# Grab the language file configured in config.php
	if ($language != null) {
		if (strcmp($language,'english') == 0)
			$language = 'en_GB';

		$language_file = LANGDIR.$language;

		# Set language
		putenv('LANG='.$language); # e.g. LANG=de_DE
		$language .= '.UTF-8';
		setlocale(LC_ALL,$language); # set LC_ALL to de_DE
		bindtextdomain('messages',LANGDIR);
		bind_textdomain_codeset('messages','UTF-8');
		textdomain('messages');
		header('Content-type: text/html; charset=UTF-8', true);
	}
}

# If config.php doesn't create the templates array, create it here.
if (! isset($templates) || ! is_array($templates))
	$templates = array();

# Always including the 'custom' template (the most generic and flexible)
$templates['custom'] =
	array('desc' => 'Custom',
		'icon' => 'images/object.png',
		'handler' => 'custom.php');

/*
 * Strip slashes from GET, POST, and COOKIE variables if this
 * PHP install is configured to automatically addslashes()
 */
if (get_magic_quotes_gpc() && (! isset($slashes_stripped) || ! $slashes_stripped)) {
	array_stripslashes($_REQUEST);
	array_stripslashes($_GET);
	array_stripslashes($_POST);
	array_stripslashes($_COOKIE);
	$slashes_stripped = true;
}

/*
 * Update $_SESSION['activity']
 * for timeout and automatic logout feature
 */
if (isset($_REQUEST['server_id'])) {
	$ldapserver = $ldapservers->Instance($_REQUEST['server_id']);
	if ($ldapserver->haveAuthInfo())
		set_lastactivity($ldapserver);
}

/**
 * Timer stopwatch, used to instrument PLA
 */
function stopwatch() {
	static $mt_previous = 0;

	list($usec, $sec) = explode(' ',microtime());
	$mt_current = (float)$usec + (float)$sec;

	if (! $mt_previous) {
		$mt_previous = $mt_current;
		return 0;

	} else {
		$mt_diff = ($mt_current - $mt_previous);
		$mt_previous = $mt_current;
		return sprintf('%.5f',$mt_diff);
	}
}

/**
 * This function will convert the browser two character language into the
 * default 5 character language, where the country portion should NOT be
 * assumed to be upper case characters of the first two characters.
 */
function auto_lang($lang) {
	switch ($lang) {
		case 'ja': return 'ja_JP';
		default: return sprintf('%s_%s',$lang,strtoupper($lang));
	}
}
?>
