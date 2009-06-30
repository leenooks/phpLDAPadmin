<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/common.php,v 1.80 2007/12/15 07:50:32 wurley Exp $

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

# Catch any scripts that are called directly.
foreach (array('cmd.php','index.php','view_jpeg_photo.php','entry_chooser.php','password_checker.php', 'download_binary_attr.php') as $script) {
	$scriptOK = false;

	if (preg_match('/'.$script.'$/',$_SERVER['SCRIPT_NAME'])) {
		$scriptOK = true;
		break;
	}
}

if (! $scriptOK) {
	if ($_REQUEST['server_id'])
		header(sprintf('Location: index.php?server_id=%s',$_REQUEST['server_id']));
	else
		header('Location: index.php');
	die();
}

/**
 * Timer stopwatch, used to instrument PLA
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

# For compatability - if common has been sourced, then return to the calling script.
} else {
	return;
}

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('UTC');
$timer = stopwatch();

if (! defined('LIBDIR'))
	define('LIBDIR','../lib/');

# For PHP5 backward/forward compatibility
if (! defined('E_STRICT'))
	define('E_STRICT',2048);

# General functions needed to proceed.
ob_start();
require_once realpath(LIBDIR.'functions.php');
if (ob_get_level()) ob_end_clean();

/* Turn on all notices and warnings. This helps us write cleaner code (we hope at least)
 * Our custom error handler receives all error notices that pass the error_reporting()
 * level set above.
 */
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

# Now read in config_default.php
require_once realpath(LIBDIR.'config_default.php');
if (ob_get_level()) ob_end_clean();

# We are now ready for error reporting.
error_reporting(E_ALL);

pla_session_start();

# Check we have the correct version of the SESSION cache
if (isset($_SESSION['cache'])) {
	if (!is_array($_SESSION[pla_session_id_init])) $_SESSION[pla_session_id_init] = array();

	if (!isset($_SESSION[pla_session_id_init]['version']) || !isset($_SESSION[pla_session_id_init]['config'])
	    || $_SESSION[pla_session_id_init]['version'] !== pla_version()
	    || $_SESSION[pla_session_id_init]['config'] != filemtime(CONFDIR.'config.php')) {
	
		$_SESSION[pla_session_id_init]['version'] = pla_version();
		$_SESSION[pla_session_id_init]['config'] = filemtime(CONFDIR.'config.php');

		unset($_SESSION['cache']);
		unset($_SESSION['plaConfig']);

		# Our configuration information has changed, so we'll redirect to index.php to get it reloaded again.
		system_message(array(
			'title'=>_('Configuration cache stale.'),
			'body'=>_('Your configuration has been automatically refreshed.'),
			'type'=>'info'));

		$config_file = CONFDIR.'config.php';
		check_config($config_file);

	} else {
		# Sanity check, specially when upgrading from a previous release.
		foreach (array_keys($_SESSION['cache']) as $id)
			if (isset($_SESSION['cache'][$id]['tree']['null']) && ! is_object($_SESSION['cache'][$id]['tree']['null']))
				unset($_SESSION['cache'][$id]);
	}
}

# If we came via index.php, then set our $config.
if (! isset($_SESSION['plaConfig']) && isset($config))
	$_SESSION['plaConfig'] = $config;

# If we get here, and plaConfig is not set, then redirect the user to the index.
if (! isset($_SESSION['plaConfig'])) {
	header('Location: index.php');
	die();

} else {
	# Check our custom variables.
	# @todo: Change this so that we dont process a cached session.
	$_SESSION['plaConfig']->CheckCustom();
}

# If we are here, $_SESSION is set - so enabled DEBUGing if it has been configured.
if (($_SESSION['plaConfig']->GetValue('debug','syslog') || $_SESSION['plaConfig']->GetValue('debug','file'))
	&& $_SESSION['plaConfig']->GetValue('debug','level'))
	define('DEBUG_ENABLED',1);
else
	define('DEBUG_ENABLED',0);

# Since DEBUG_ENABLED is set later, as $config may not be set, we'll 
if (DEBUG_ENABLED)
	debug_log('PLA (%s) initialised and starting with (%s).',1,pla_version(),$_REQUEST);

# Set our PHP timelimit.
if ($_SESSION['plaConfig']->GetValue('session','timelimit'))
	set_time_limit($_SESSION['plaConfig']->GetValue('session','timelimit'));

# If debug mode is set, increase the time_limit, since we probably need it.
if (DEBUG_ENABLED && $_SESSION['plaConfig']->GetValue('session','timelimit'))
	set_time_limit($_SESSION['plaConfig']->GetValue('session','timelimit') * 5);

# @todo: Change this so that we dont process a cached session.
$_SESSION['plaConfig']->friendly_attrs = process_friendly_attr_table();

/*
 * Language configuration. Auto or specified?
 * Shall we attempt to auto-determine the language?
 */

$language = $_SESSION['plaConfig']->GetValue('appearance','language');

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
		header('Content-type: text/html; charset=UTF-8',true);
	}
}

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

if (isset($_REQUEST['server_id'])) {
	$ldapserver = $_SESSION['plaConfig']->ldapservers->Instance($_REQUEST['server_id']);
} else {
	$ldapserver = $_SESSION['plaConfig']->ldapservers->Instance(null);
}

# Test to see if we should log out the user due to the timeout.
if ($ldapserver->haveAuthInfo() && $ldapserver->auth_type != 'config') {
	/* If time out value has been reached:
	   - log out user
	   - put $server_id in array of recently timed out servers */
	if (session_timed_out($ldapserver)) {
		$timeout_url = 'cmd.php?cmd=timeout&server_id='.$ldapserver->server_id;
		echo '<script type="text/javascript" language="javascript">location.href=\''.$timeout_url.'\'</script>';
		die();
	}
}

/*
 * Update $_SESSION['activity']
 * for timeout and automatic logout feature
 */
if ($ldapserver->haveAuthInfo())
	set_lastactivity($ldapserver);

/**
 * At this point we have read all our additional function PHP files and our configuration.
 */
run_hook('post_session_init',array());

?>
