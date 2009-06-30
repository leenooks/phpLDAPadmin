<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/common.php,v 1.80.2.8 2008/01/04 12:33:03 wurley Exp $

/**
 * Contains code to be executed at the top of each application page.
 * include this file at the top of every PHP file.
 *
 * This file will "pre-initialise" an application environment so that any PHP file will have a consistent
 * environment with other application PHP files.
 *
 * This code WILL NOT check that all required functions are usable/readable, etc. This process has
 * been moved to index.php (which really is only called once when a browser hits the application for the first time).
 *
 * The list of ADDITIONAL function files is now defined in functions.php.
 *
 * @package phpLDAPadmin
 */

# The index we will store our config in $_SESSION
if (! defined('APPCONFIG'))
	define('APPCONFIG','plaConfig');

/**
 * Catch any scripts that are called directly.
 * If they are called directly, then they should be routed back through index.php
 */
$app['direct_scripts'] = array('cmd.php','index.php',
	'view_jpeg_photo.php','entry_chooser.php',
	'password_checker.php','download_binary_attr.php');

foreach ($app['direct_scripts'] as $script) {
	$scriptOK = false;

	if (preg_match('/'.$script.'$/',$_SERVER['SCRIPT_NAME'])) {
		$scriptOK = true;
		break;
	}
}

# Anything in the tools dir can be executed directly.
if (! $scriptOK && preg_match('/^\/tools/',$_SERVER['SCRIPT_NAME']))
	$scriptOK = true;

if (! $scriptOK) {
	if (isset($_REQUEST['server_id']))
		header(sprintf('Location: index.php?server_id=%s',$_REQUEST['server_id']));
	else
		header('Location: index.php');
	die();
}

/**
 * Timer stopwatch, used to instrument the application
 */
if (! function_exists('stopwatch')) {
	function stopwatch() {
		static $mt_previous = 0;

		list($usec,$sec) = explode(' ',microtime());
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

# For compatability - if common has been sourced a second time, then return to the calling script.
} else {
	return;
}

# Set the defualt time zone, if it isnt set in php.ini
if (function_exists('date_default_timezone_set') && ! ini_get('date.timezone'))
	date_default_timezone_set('UTC');

# Start out instrumentation
$timer = stopwatch();

# If we are called from index.php, LIBDIR will be set, all other calls to common.php dont need to set it.
if (! defined('LIBDIR'))
	define('LIBDIR','../lib/');

# For PHP5 backward/forward compatibility
if (! defined('E_STRICT'))
	define('E_STRICT',2048);

# General functions needed to proceed.
ob_start();
require_once realpath(LIBDIR.'functions.php');
if (ob_get_level())
	ob_end_clean();

/**
 * Turn on all notices and warnings. This helps us write cleaner code (we hope at least)
 * Our custom error handler receives all error notices that pass the error_reporting()
 * level set above.
 */

# Call our custom defined error handler, if it is defined in functions.php
if (function_exists('pla_error_handler'))
	set_error_handler('pla_error_handler');

# Disable error reporting until all our required functions are loaded.
error_reporting(0);

/**
 * functions.php should have defined our $app['function_files'] array, listing all our
 * required functions (order IS important).
 * index.php should have checked they exist and are usable - we'll assume that the user
 * has been via index.php, and fixed any problems already.
 */
ob_start();
if (isset($app['function_files']) && is_array($app['function_files']))
	foreach ($app['function_files'] as $file_name) {
		require_once realpath ($file_name);
	}

# Now read in config_default.php
require_once realpath(LIBDIR.'config_default.php');
if (ob_get_level())
	ob_end_clean();

# We are now ready for error reporting.
error_reporting(E_ALL);

# Start our session.
pla_session_start();

# If we get here, and $_SESSION[APPCONFIG] is not set, then redirect the user to the index.
if (! isset($_SESSION[APPCONFIG])) {
	if (isset($_REQUEST['server_id']))
		header(sprintf('Location: index.php?server_id=%s',$_REQUEST['server_id']));
	else
		header('Location: index.php');
	die();

} else {
	# Check our custom variables.
	# @todo: Change this so that we dont process a cached session.
	$_SESSION[APPCONFIG]->CheckCustom();
}

# If we are here, $_SESSION is set - so enabled DEBUGing if it has been configured.
if (($_SESSION[APPCONFIG]->GetValue('debug','syslog') || $_SESSION[APPCONFIG]->GetValue('debug','file'))
	&& $_SESSION[APPCONFIG]->GetValue('debug','level'))
	define('DEBUG_ENABLED',1);
else
	define('DEBUG_ENABLED',0);

if (DEBUG_ENABLED)
	debug_log('Application (%s) initialised and starting with (%s).',1,__FILE__,__LINE__,__METHOD__,
		pla_version(),$_REQUEST);

# Set our PHP timelimit.
if ($_SESSION[APPCONFIG]->GetValue('session','timelimit'))
	set_time_limit($_SESSION[APPCONFIG]->GetValue('session','timelimit'));

# If debug mode is set, increase the time_limit, since we probably need it.
if (DEBUG_ENABLED && $_SESSION[APPCONFIG]->GetValue('session','timelimit'))
	set_time_limit($_SESSION[APPCONFIG]->GetValue('session','timelimit') * 5);

/**
 * Language configuration. Auto or specified?
 * Shall we attempt to auto-determine the language?
 */
$language = $_SESSION[APPCONFIG]->GetValue('appearance','language');

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
			$language_dir = LANGDIR.$HTTP_LANG;

			if ((substr($HTTP_LANG,0,2) == 'en') ||
				(file_exists($language_dir) && is_readable($language_dir))) {

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
		#todo: Generate an error if language doesnt exist.
	}

} else {
	# Grab the language file configured in config.php
	#todo: Generate an error if language doesnt exist.
	if ($language != null) {
		if (strcmp($language,'english') == 0)
			$language = 'en_GB';

		# Set language
		putenv('LANG='.$language); # e.g. LANG=de_DE
		$language .= '.UTF-8';
		setlocale(LC_ALL,$language); # set LC_ALL to de_DE
		bindtextdomain('messages',LANGDIR);
		bind_textdomain_codeset('messages','UTF-8');
		textdomain('messages');
		header('Content-type: text/html; charset=UTF-8',true);
	}
}

/**
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

/**
 * Create our application repository variable.
 */
if (isset($_REQUEST['server_id'])) {
	$ldapserver = $_SESSION[APPCONFIG]->ldapservers->Instance($_REQUEST['server_id']);
} else {
	if (isset($_SESSION[APPCONFIG]->ldapservers) && is_object($_SESSION[APPCONFIG]->ldapservers))
		$ldapserver = $_SESSION[APPCONFIG]->ldapservers->Instance(null);
}

/**
 * Look/evaluate our timeout
 */
if (isset($ldapserver) && is_object($ldapserver) && method_exists($ldapserver,'haveAuthInfo')) {
	if ($ldapserver->haveAuthInfo() && isset($ldapserver->auth_type) && $ldapserver->auth_type != 'config') {
		/**
		 * If time out value has been reached:
		 * - log out user
		 * - put $server_id in array of recently timed out servers
		 */
		if (function_exists('session_timed_out') && session_timed_out($ldapserver)) {
			$app['url_timeout'] = sprintf('cmd.php?cmd=timeout&server_id=%s',$_REQUEST['server_id']);
			printf('<script type="text/javascript" language="javascript">location.href=\'%s\'</script>',
				htmlspecialchars($app['url_timeout']));
			die();
		}
	}

	# Update $_SESSION['activity'] for timeout and automatic logout feature
	if ($ldapserver->haveAuthInfo() && function_exists('set_lastactivity'))
		set_lastactivity($ldapserver);
}

/**
 * At this point we have read all our additional function PHP files and our configuration.
 * If we are using hooks, run the session_init hook.
 */
if (function_exists('run_hook'))
	run_hook('post_session_init',array());
?>
