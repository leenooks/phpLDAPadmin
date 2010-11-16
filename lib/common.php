<?php
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
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * @package phpLDAPadmin
 * @subpackage Functions
 */

/* Initialize the app array. The app array is initialised each invocation of a PLA script and therefore
   has no state between invocations.*/
$app = array();

/** The index we will store our config in $_SESSION */
if (! defined('APPCONFIG'))
	define('APPCONFIG','plaConfig');

/**
 * Catch any scripts that are called directly.
 * If they are called directly, then they should be routed back through index.php
 */
$app['direct_scripts'] = array('cmd.php','index.php',
	'view_jpeg_photo.php','entry_chooser.php',
	'password_checker.php','download_binary_attr.php',
	'unserialize.php'
	);

# Which script was invoked.
$app['script_running'] = $_SERVER['SCRIPT_NAME'];

foreach ($app['direct_scripts'] as $script) {
	$app['scriptOK'] = false;

	if (preg_match('/'.$script.'$/',$app['script_running'])) {
		$app['scriptOK'] = true;
		break;
	}
}

# Anything in the tools dir or cron dir can be executed directly.
if ((! $app['scriptOK'] && preg_match('/^\/[cron|tools]/',$app['script_running'])) || ! isset($_SERVER['SERVER_SOFTWARE']))
	$app['scriptOK'] = true;

if (! $app['scriptOK']) {
	if (isset($_REQUEST['server_id']))
		header(sprintf('Location: index.php?server_id=%s',$_REQUEST['server_id']));
	else
		header('Location: index.php');
	die();
}

/**
 * All commands are disabled in read-only unless specified here
 */
$app['readwrite_cmds'] = array(
	'collapse','draw_tree_node','expand',
	'compare_form','compare',
	'download_binary_attr','view_jpeg_photo',
	'entry_chooser',
	'export_form','export',
	'login_form','login','logout',
	'monitor',
	'password_checker',
	'purge_cache',
	'refresh','schema','query_engine','server_info','show_cache','template_engine',
	'welcome'
	);

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
if (function_exists('app_error_handler'))
	set_error_handler('app_error_handler');

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
	foreach ($app['function_files'] as $script)
		require_once realpath($script);

# Now read in config_default.php
require_once realpath(LIBDIR.'config_default.php');
if (ob_get_level())
	ob_end_clean();

# We are now ready for error reporting.
error_reporting(E_ALL);

# Start our session.
app_session_start();

# See if we have a session, we can then get our theme out
$app['theme'] = 'default';
if (isset($_SESSION[APPCONFIG]))
	if (is_dir(realpath(sprintf('images/%s',$_SESSION[APPCONFIG]->getValue('appearance','theme'))))
		&& is_file(realpath(sprintf('css/%s/%s',$_SESSION[APPCONFIG]->getValue('appearance','theme'),$_SESSION[APPCONFIG]->getValue('appearance','stylesheet')))))

		$app['theme'] = $_SESSION[APPCONFIG]->getValue('appearance','theme');

define('CSSDIR',sprintf('css/%s',$app['theme']));
define('IMGDIR',sprintf('images/%s',$app['theme']));

# Initialise the hooks
if (file_exists(LIBDIR.'hooks.php'))
	require_once LIBDIR.'hooks.php';

# If we get here, and $_SESSION[APPCONFIG] is not set, then redirect the user to the index.
if (isset($_SERVER['SERVER_SOFTWARE']) && ! isset($_SESSION[APPCONFIG])) {
	if ($_SERVER['QUERY_STRING'])
		header(sprintf('Location: index.php?URI=%s',base64_encode($_SERVER['QUERY_STRING'])));
	else
		header('Location: index.php');

	die();

} else {
	# SF Bug #1903987
	if (! method_exists($_SESSION[APPCONFIG],'CheckCustom'))
		error('Unknown situation, $_SESSION[APPCONFIG] exists, but method CheckCustom() does not','error',null,true,true);

	# Check our custom variables.
	# @todo Change this so that we dont process a cached session.
	$_SESSION[APPCONFIG]->CheckCustom();
}

# Check for safe mode.
if (@ini_get('safe_mode') && ! get_request('cmd','GET'))
	system_message(array(
	'title'=>_('PHP Safe Mode'),
	'body'=>_('You have PHP Safe Mode enabled. This application may work unexpectedly in Safe Mode.'),
	'type'=>'info'));

# Set our timezone, if it is specified in config.php
if ($_SESSION[APPCONFIG]->getValue('appearance','timezone'))
	date_default_timezone_set($_SESSION[APPCONFIG]->getValue('appearance','timezone'));

# If we are here, $_SESSION is set - so enabled DEBUGing if it has been configured.
if (($_SESSION[APPCONFIG]->getValue('debug','syslog') || $_SESSION[APPCONFIG]->getValue('debug','file'))
	&& $_SESSION[APPCONFIG]->getValue('debug','level'))
	define('DEBUG_ENABLED',1);
else
	define('DEBUG_ENABLED',0);

if (DEBUG_ENABLED)
	debug_log('Application (%s) initialised and starting with (%s).',1,0,__FILE__,__LINE__,__METHOD__,
		app_version(),$_REQUEST);

# Set our PHP timelimit.
if ($_SESSION[APPCONFIG]->getValue('session','timelimit') && ! @ini_get('safe_mode'))
	set_time_limit($_SESSION[APPCONFIG]->getValue('session','timelimit'));

# If debug mode is set, increase the time_limit, since we probably need it.
if (DEBUG_ENABLED && $_SESSION[APPCONFIG]->getValue('session','timelimit') && ! @ini_get('safe_mode'))
	set_time_limit($_SESSION[APPCONFIG]->getValue('session','timelimit') * 5);

/**
 * Language configuration. Auto or specified?
 * Shall we attempt to auto-determine the language?
 */
# If we are in safe mode, and LANG is not in the allowed vars, display an error.
if (@ini_get('safe_mode') && ! in_array('LANG',explode(',',@ini_get('safe_mode_allowed_env_vars'))))
	error('You are running in SAFE_MODE, but LANG is not in the safe_mode_allowed_env_vars. Please add LANG to safe_mode_allowed_env_vars','error',true,false);

$app['language'] = $_SESSION[APPCONFIG]->getValue('appearance','language');

if ($app['language'] == 'auto') {

	# Make sure their browser correctly reports language. If not, skip this.
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

		# Get the languages which are spetcified in the HTTP header
		$app['lang_http'] = preg_split ('/[;,]+/',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach ($app['lang_http'] as $key => $value) {
			if (substr($value,0,2) == 'q=') {
				unset($app['lang_http'][$key]);
				continue;
			}

			$value = preg_split('/[-]+/',$value);
			if (sizeof($value) == 2)
				$app['lang_http'][$key] = strtolower($value[0]).'_'.strtoupper($value[1]);
			else
				$app['lang_http'][$key] = auto_lang(strtolower($value[0]));
		}

		$app['lang_http'] = array_unique($app['lang_http']);

		foreach ($app['lang_http'] as $lang) {
			$app['language_dir'] = LANGDIR.$lang;

			if ((substr($lang,0,2) == 'en') ||
				(file_exists($app['language_dir']) && is_readable($app['language_dir']))) {

				# Set language
				putenv('LANG='.$lang); # e.g. LANG=de_DE
				$lang .= '.UTF-8';
				setlocale(LC_ALL,$lang); # set LC_ALL to de_DE
				bindtextdomain('messages',LANGDIR);
				bind_textdomain_codeset('messages','UTF-8');
				textdomain('messages');
				header('Content-type: text/html; charset=UTF-8',true);
				break;
			}
		}
		#todo Generate an error if language doesnt exist.
	}

} else {
	# Grab the language file configured in config.php
	#todo Generate an error if language doesnt exist.
	if ($app['language'] != null) {
		if (strcmp($app['language'],'english') == 0)
			$app['language'] = 'en_GB';

		# Set language
		putenv('LANG='.$app['language']); # e.g. LANG=de_DE
		$app['language'] .= '.UTF-8';
		setlocale(LC_ALL,$app['language']); # set LC_ALL to de_DE
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
if (@get_magic_quotes_gpc() && (! isset($slashes_stripped) || ! $slashes_stripped)) {
	array_stripslashes($_REQUEST);
	array_stripslashes($_GET);
	array_stripslashes($_POST);
	array_stripslashes($_COOKIE);
	$slashes_stripped = true;
}

# Create our application repository variable.
$app['server'] = $_SESSION[APPCONFIG]->getServer(get_request('server_id','REQUEST'));

/**
 * Look/evaluate our timeout
 */
if (! $app['server']->isSessionValid()) {
	system_message(array(
		'title'=>_('Session Timed Out'),
		'body'=>sprintf('%s %s %s',
			_('Your Session timed out after'),$app['server']->getValue('login','timeout'),
			_('min. of inactivity. You have been automatically logged out.')),
		'type'=>'info'),sprintf('index.php?server_id=%s&refresh=SID_%s',$app['server']->getIndex(),$app['server']->getIndex()));

	die();
}

# If syslog is enabled, we need to include the supporting file.
if ($_SESSION[APPCONFIG]->getValue('debug','syslog'))
	require LIBDIR.'syslog.php';

/**
 * At this point we have read all our additional function PHP files and our configuration.
 * If we are using hooks, run the session_init hook.
 */
if (function_exists('run_hook'))
	run_hook('post_session_init',array());
?>
