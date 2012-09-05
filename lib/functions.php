<?php
/**
 * A collection of common generic functions used throughout the application.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * @package phpLDAPadmin
 * @subpackage Functions
 */

/**
 */
define('HTDOCDIR',sprintf('%s/',realpath(LIBDIR.'../htdocs/')));
define('LANGDIR',sprintf('%s/',realpath(LIBDIR.'../locale/')));
define('CONFDIR',sprintf('%s/',realpath(LIBDIR.'../config')));
define('QUERYDIR',sprintf('%s/',realpath(LIBDIR.'../queries/')));
define('TMPLDIR',sprintf('%s/',realpath(LIBDIR.'../templates/')));
define('DOCDIR',sprintf('%s/',realpath(LIBDIR.'../doc/')));
define('HOOKSDIR',sprintf('%s/',realpath(LIBDIR.'../hooks/')));
define('JSDIR','js/');

/**
 * Supplimental functions
 * This list is a list of supplimental functions that are used throughout
 * PLA. The order here IS important - so that files that refer to
 * functions defined in other files need to be listed after those files.
 */
$app['function_files'] = array(
	# Functions for managing the session (app_session_start(), etc.)
	LIBDIR.'session_functions.php',
	# Functions for reading the server schema
	LIBDIR.'schema_functions.php',
	# Functions for template manipulation.
	LIBDIR.'template_functions.php',
	# Functions for hashing passwords with OpenSSL binary (only if mhash not present)
	LIBDIR.'emuhash_functions.php',
	# Functions for creating Samba passwords
	LIBDIR.'createlm.php',
	# Datasource functions
	LIBDIR.'ds.php',
	# Functions for rendering the page
	LIBDIR.'page.php'
);

if (file_exists(LIBDIR.'functions.custom.php'))
	array_push($app['function_files'],LIBDIR.'functions.custom.php');

/**
 * Loads class definition
 */
function __autoload($className) {
	if (file_exists(HOOKSDIR."classes/$className.php"))
		require_once(HOOKSDIR."classes/$className.php");
	elseif (file_exists(LIBDIR."$className.php"))
		require_once(LIBDIR."$className.php");
	elseif (file_exists(LIBDIR."ds_$className.php"))
		require_once(LIBDIR."ds_$className.php");
	else
		system_message(array(
			'title'=>_('Generic Error'),
			'body'=>sprintf('%s: %s [%s]',
				__METHOD__,_('Called to load a class that cant be found'),$className),
			'type'=>'error'));
}

/**
 * Strips all slashes from the specified array in place (pass by ref).
 * @param Array The array to strip slashes from, typically one of
 *                     $_GET, $_POST, or $_COOKIE.
 */
function array_stripslashes(&$array) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (is_array($array))
		while (list($key) = each($array))
			if (is_array($array[$key]) && $key != $array)
				array_stripslashes($array[$key]);
			else
				$array[$key] = stripslashes($array[$key]);
}

/**
 * Compatibility Functions
 * These functions exist, so that a standard function can be used in new applications, and they
 * map to already defined functions in older applications.
 */

/**
 * If gettext is not available in PHP, then this will provide compatibility for it.
 */
if (! function_exists('_')) {
	function _($msg) {
		return $msg;
	}
}

/**
 * Generic Utility Functions
 */

/**
 * Custom error handling function.
 * When a PHP error occurs, PHP will call this function rather than printing
 * the typical PHP error string. This provides the application the ability to
 * format an error message so that it looks better.
 * Optionally, it can present a link so that a user can search/submit bugs.
 * This function is not to be called directly. It is exclusively for the use of
 * PHP internally. If this function is called by PHP from within a context
 * where error handling has been disabled (ie, from within a function called
 * with "@" prepended), then this function does nothing.
 *
 * @param int The PHP error number that occurred (ie, E_ERROR, E_WARNING, E_PARSE, etc).
 * @param string The PHP error string provided (ie, "Warning index "foo" is undefined)
 * @param string The file in which the PHP error ocurred.
 * @param int The line number on which the PHP error ocurred
 * @see set_error_handler
 */
function app_error_handler($errno,$errstr,$file,$lineno) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	/**
	 * error_reporting will be 0 if the error context occurred
	 * within a function call with '@' preprended (ie, @ldap_bind() );
	 * So, don't report errors if the caller has specifically
	 * disabled them with '@'
	 */
	if (ini_get('error_reporting') == 0 || error_reporting() == 0)
		return;

	$file = basename($file);
	$caller = basename($_SERVER['PHP_SELF']);
	$errtype = '';

	switch ($errno) {
		case E_STRICT: $errtype = 'E_STRICT'; break;
		case E_ERROR: $errtype = 'E_ERROR'; break;
		case E_WARNING: $errtype = 'E_WARNING'; break;
		case E_PARSE: $errtype = 'E_PARSE'; break;
		case E_NOTICE: $errtype = 'E_NOTICE'; break;
		case E_CORE_ERROR: $errtype = 'E_CORE_ERROR'; break;
		case E_CORE_WARNING: $errtype = 'E_CORE_WARNING'; break;
		case E_COMPILE_ERROR: $errtype = 'E_COMPILE_ERROR'; break;
		case E_COMPILE_WARNING: $errtype = 'E_COMPILE_WARNING'; break;
		case E_USER_ERROR: $errtype = 'E_USER_ERROR'; break;
		case E_USER_WARNING: $errtype = 'E_USER_WARNING'; break;
		case E_USER_NOTICE: $errtype = 'E_USER_NOTICE'; break;
		case E_ALL: $errtype = 'E_ALL'; break;

		default: $errtype = sprintf('%s: %s',_('Unrecognized error number'),$errno);
	}

	# Take out extra spaces in error strings.
	$errstr = preg_replace('/\s+/',' ',$errstr);

	if ($errno == E_NOTICE) {
		$body = '<table class="notice">';
		$body .= sprintf('<tr><td>%s:</td><td><b>%s</b> (<b>%s</b>)</td></tr>',_('Error'),$errstr,$errtype);
		$body .= sprintf('<tr><td>%s:</td><td><b>%s</b> %s <b>%s</b>, %s <b>%s</b></td></tr>',
			_('File'),$file,_('line'),$lineno,_('caller'),$caller);
		$body .= sprintf('<tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b></td></tr>',
			app_version(),phpversion(),php_sapi_name());
		$body .= sprintf('<tr><td>Web server:</td><td><b>%s</b></td></tr>',isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'SCRIPT');

		if (function_exists('get_href'))
			$body .= sprintf('<tr><td colspan="2"><a href="%s" onclick="target=\'_blank\';"><center>%s.</center></a></td></tr>',
				get_href('search_bug',"&summary_keyword=".rawurlencode($errstr)),
				_('Please check and see if this bug has been reported'));
		$body .= '</table>';

		system_message(array(
			'title'=>_('You found a non-fatal phpLDAPadmin bug!'),
			'body'=>$body,
			'type'=>'error'));

		return;
	}

	# If this is a more serious error, call the error call.
	error(sprintf('%s: %s',$errtype,$errstr),'error',null,true,true);
}

/**
 * Returns the application name.
 */
function app_name() {
	return 'phpLDAPadmin';
}

/**
 * Returns the application version currently running. The version
 * is read from the file named VERSION.
 *
 * @return string The current version as read from the VERSION file.
 */
function app_version() {
	static $CACHE = null;

	if ($CACHE)
		return $CACHE;

	$version_file = realpath(LIBDIR.'../VERSION');
	if (! file_exists($version_file))
		$CACHE = 'UNKNOWN';

	else {
		$version = rtrim(file_get_contents($version_file));

		$CACHE = preg_replace('/^RELEASE-([0-9\.]+(-.*)*)$/','$1',$version);

		# Check if we are a CVS copy.
		if (preg_match('/^(DEVEL)?$/',$CACHE))
			$CACHE = 'DEVEL';

		# Check if we are special DEVEL version
		elseif (preg_match('/^DEVEL-([0-9\.]+)+$/',$CACHE)) {}

		# If return is still the same as version, then the tag is not one we expect.
		elseif ($CACHE == $version)
			$CACHE = 'UNKNOWN';
	}

	return $CACHE;
}

/**
 * This function will convert the browser two character language into the
 * default 5 character language, where the country portion should NOT be
 * assumed to be upper case characters of the first two characters.
 */
function auto_lang($lang) {
	switch ($lang) {
		case 'ja': return 'ja_JP';
		case 'cs': return 'cs_CZ';
		default: return sprintf('%s_%s',$lang,strtoupper($lang));
	}
}

/**
 * Makes sure that the config file is properly setup.
 */
function check_config($config_file) {
	# Read in config_default.php
	require_once LIBDIR.'config_default.php';

	# Make sure their PHP version is current enough
	if (strcmp(phpversion(),REQUIRED_PHP_VERSION) < 0)
		system_message(array(
			'title'=>_('Incorrect version of PHP'),
			'body'=>sprintf('phpLDAPadmin requires PHP version %s or greater.<br /><small>(You are using %s)</small>',
				REQUIRED_PHP_VERSION,phpversion()),
			'type'=>'error'));

	$config = new Config;

	if (file_exists(LIBDIR.'config_custom.php') && is_readable(LIBDIR.'config_custom.php'))
		include LIBDIR.'config_custom.php';

	ob_start();
	require $config_file;
	$str = '';
	if (ob_get_level()) {
		$str = ob_get_contents();
		ob_end_clean();
	}

	if ($str) {
		$str = strip_tags($str);
		$matches = array();
		preg_match('/(.*):\s+(.*):.*\s+on line (\d+)/',$str,$matches);

		if (isset($matches[1]) && isset($matches[2]) && isset($matches[3])) {
			$error_type = $matches[1];
			$error = $matches[2];
			$line_num = $matches[3];

			$file = file($config_file);

			$body = '<h3 class="title">Config file ERROR</h3>';
			$body .= sprintf('<h3 class="subtitle">%s (%s) on line %s</h3>',$error_type,$error,$line_num);

			$body .= '<center>';
			$body .= sprintf('Looks like your config file has an ERROR on line %s.<br />',$line_num);
			$body .= 'Here is a snippet around that line <br />';
			$body .= '<br />'."\n";

			$body .= '<div style="text-align: left; font-family: monospace; margin-left: 80px; margin-right: 80px; border: 1px solid black; padding: 10px;">';

			for ($i = $line_num-9; $i<$line_num+5; $i++) {
				if ($i+1 == $line_num)
					$body .= '<div style="color:red;background:#fdd">';

				if ($i < 0)
					continue;

				$body .= sprintf('<b>%s</b>: %s<br />',$i+1,$file[$i]);

				if ($i+1 == $line_num)
					$body .= '</div>';
			}

			$body .= '</div>';
			$body .= '<br />';
			$body .= 'Hint: Sometimes these errors are caused by lines <b>preceding</b> the line reported.';
			$body .= '</center>';

			$block = new block();
			$block->SetBody($body);
			$www['page'] = new page();
			$www['page']->block_add('body',$block);
			$www['page']->display();

			die();
		}
	}

	# Check for server definitions.
	if (! isset($servers) || count($servers->GetServerList()) == 0)
		error(_('Your config.php is missing Server Definitions. Please see the sample file config/config.php.example.'),'error','index.php',true);

	$config->setServers($servers);

	# Check the memory limit parameter.
	if ((ini_get('memory_limit') > -1) && ini_get('memory_limit') < $config->getValue('session','memorylimit'))
		system_message(array(
			'title'=>_('Memory Limit low.'),
			'body'=>sprintf('Your php memory limit is low - currently %s, you should increase it to atleast %s. This is normally controlled in /etc/php.ini.',
				ini_get('memory_limit'),$config->getValue('session','memorylimit')),
			'type'=>'error'));

	return $config;
}

/**
 * Commands available in the control_panel of the page
 *
 * @return array
 */
function cmd_control_pane($type) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	switch ($type) {
		case 'main' :
			return array(
				'home'=>array(
					'title'=>_('Home'),
					'enable'=>true,
					'link'=>sprintf('href="index.php" title="%s"',_('Home')),
					'image'=>sprintf('<img src="%s/home-big.png" alt="%s" />',IMGDIR,_('Home'))),

				'purge'=>array(
					'title'=>_('Purge caches'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('script','purge_cache') : false,
					'link'=>sprintf('href="cmd.php?cmd=purge_cache" onclick="return ajDISPLAY(\'BODY\',\'cmd=purge_cache\',\'%s\');" title="%s"',
						_('Clearing cache'),_('Purge caches')),
					'image'=>sprintf('<img src="%s/trash-big.png" alt="%s" />',IMGDIR,_('Purge caches'))),

				'hide_debug_info'=>array(
					'title'=>_('Show Cache'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('script','show_cache') : false,
					'link'=>sprintf('href="cmd.php?cmd=show_cache" onclick="return ajDISPLAY(\'BODY\',\'cmd=show_cache\',\'%s\');" title="%s"',
						_('Loading'),_('Show Cache'),_('Show Cache')),
					'image'=>sprintf('<img src="%s/debug-cache.png" alt="%s" />',IMGDIR,_('Show Cache'))),
			);

			break;

		case 'top' :
			return array(
				'forum'=>array(
					'title'=>_('Forum'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('cmd','oslinks') : true,
					'link'=>sprintf('href="%s" title="%s" onclick="target=\'_blank\';"',get_href('forum'),_('Forum')),
					'image'=>sprintf('<img src="%s/forum-big.png" alt="%s" />',IMGDIR,_('Forum'))),

				'feature'=>array(
					'title'=>_('Request feature'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('cmd','oslinks') : true,
					'link'=>sprintf('href="%s" title="%s" onclick="target=\'_blank\';"',get_href('add_rfe'),_('Request feature')),
					'image'=>sprintf('<img src="%s/request-feature-big.png" alt="%s" />',IMGDIR,_('Request feature'))),

				'bug'=>array(
					'title'=>_('Report a bug'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('cmd','oslinks') : true,
					'link'=>sprintf('href="%s" title="%s" onclick="target=\'_blank\';"',get_href('add_bug'),_('Report a bug')),
					'image'=>sprintf('<img src="%s/bug-big.png" alt="%s" />',IMGDIR,_('Report a bug'))),

				'donation'=>array(
					'title'=>_('Donate'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('cmd','oslinks') : true,
					'link'=>sprintf('href="%s" title="%s" onclick="target=\'_blank\';"',get_href('donate'),_('Donate')),
					'image'=>sprintf('<img src="%s/smile-big.png" alt="%s" />',IMGDIR,_('Donate'))),

				'help'=>array(
					'title'=>_('Help'),
					'enable'=>isset($_SESSION[APPCONFIG]) ? $_SESSION[APPCONFIG]->isCommandAvailable('cmd','oslinks') : true,
					'link'=>sprintf('href="%s" title="%s" onclick="target=\'_blank\';"',get_href('documentation'),_('Help')),
					'image'=>sprintf('<img src="%s/help-big.png" alt="%s" />',IMGDIR,_('Help')))
			);

			break;
	}
}

/**
 * This function dumps the $variable for debugging purposes
 *
 * @param string|array Variable to dump
 * @param boolean Whether to stop execution or not.
 */
function debug_dump($variable,$die=false,$onlydebugaddr=false) {
	if ($onlydebugaddr &&
		isset($_SESSION[APPCONFIG]) && $_SESSION[APPCONFIG]->getValue('debug','addr') &&
		$_SERVER['HTTP_X_FORWARDED_FOR'] != $_SESSION[APPCONFIG]->getValue('debug','addr') &&
		$_SERVER['REMOTE_ADDR'] != $_SESSION[APPCONFIG]->getValue('debug','addr'))
		return;

	$backtrace = debug_backtrace();
	$caller['class'] = isset($backtrace[0]['class']) ? $backtrace[0]['class'] : 'N/A';
	$caller['function'] = isset($backtrace[0]['function']) ? $backtrace[0]['function'] : 'N/A';
	$caller['file'] = isset($backtrace[0]['file']) ? $backtrace[0]['file'] : 'N/A';
	$caller['line'] = isset($backtrace[0]['line']) ? $backtrace[0]['line'] : 'N/A';
	$caller['debug'] = $variable;

	print '<PRE>';
	print_r($caller);
	print '</PRE>';

	if ($die)
		die();
}

/**
 * This function generates a backtrace
 *
 * @param boolean Whether to stop execution or not.
 */
function debug_dump_backtrace($msg='Calling BackTrace',$die=false) {
	error($msg,'note',null,$die,true);
}

/**
 * Send a debug as a sys message
 */
function debug_sysmsg($msg) {
	system_message(array('title'=>_('Debug'),'body'=>$msg,'type'=>'debug'));
}

/**
 * Debug Logging
 *
 * The global debug level is turned on in your configuration file by setting:
 * <code>
 *	$config->custom->debug['level'] = 255;
 * </code>
 * together with atleast one output direction (currently file and syslog are supported).
 * <code>
 *	$config->custom->debug['file'] = '/tmp/app_debug.log';
 *	$config->custom->debug['syslog'] = true;
 * </code>
 *
 * The debug level is turned into binary, then if the message levels bit is on
 * the message will be sent to the debug log. (Thus setting your debug level to 255,
 * all bits on, will results in all messages being printed.)
 *
 * The message level bits are defined here.
 *  0(  1) = Entry/Return results from function calls.
 *  1(  2) = Configuration Processing
 *  2(  4) = Template Processing
 *  3(  8) = Schema Processing
 *  4( 16) = LDAP Server Communication
 *  5( 32) = Tree Processing
 *  7( 64) = Other non generic messages
 *  8(128) = Page Processing
 *  9(256) = Hooks Processing
 * @param string Message to send to syslog
 * @param int Log bit number for this message.
 * @see syslog.php
 */
function debug_log($msg,$level,$indent) {
	static $debug_file;

	# In case we are called before we are fully initialised or if debugging is not set.
	if (! isset($_SESSION[APPCONFIG])
		|| ! ($_SESSION[APPCONFIG]->getValue('debug','file') || $_SESSION[APPCONFIG]->getValue('debug','syslog')))
		return;

	$debug_level = $_SESSION[APPCONFIG]->getValue('debug','level');
	if (! $debug_level || (! ($level & $debug_level)))
		return;

	if ($_SESSION[APPCONFIG]->getValue('debug','addr'))
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $_SESSION[APPCONFIG]->getValue('debug','addr'))
			$debugaddr = true;
		elseif ($_SERVER['REMOTE_ADDR'] == $_SESSION[APPCONFIG]->getValue('debug','addr'))
			$debugaddr = true;
		else
			$debugaddr = false;

	else
		$debugaddr = true;

	if (! $debugaddr)
		return;

	# If we are limiting debug to a browser, then check that
	$caller = basename($_SERVER['PHP_SELF']);

	$args = func_get_args();
	# Discard our first three arguments.
	array_shift($args);
	array_shift($args);
	array_shift($args);

	# Pull the file/line/method
	if (is_string($args[0]) && preg_match('/.php$/',$args[0])) {
		$file = preg_replace('/.php$/','',array_shift($args));
		$line = array_shift($args);
		$method = array_shift($args);

	} else {
		$file = 'UNKNOWN';
		$line = 'UNKNOWN';
		$method = 'UNKNOWN';
	}

	# TEMP: New debuglog format
	if (preg_match('/%%/',$msg) && $args[0] != 'NOARGS')
		$args = array_shift($args);

	$fargs = array();
	foreach ($args as $key) {
		if (is_array($key))
			array_push($fargs,serialize($key));
		elseif (is_object($key))
			array_push($fargs,sprintf('OBJECT:%s',get_class($key)));
		else
			array_push($fargs,$key);
	}

	if (preg_match('/%%/',$msg))
		$msg = preg_replace('/%%/',join('|',$fargs),$msg);
	else
		$msg = vsprintf($msg,array_values($fargs));

	if (function_exists('stopwatch'))
		$timer = stopwatch();
	else
		$timer = null;

	$debug_message = sprintf('[%2.3f] %15s(%04s-%03s): %s%s: %s',$timer,basename($file),$line,$level,str_repeat('.',$indent),$method,substr($msg,0,200));

	if ($debug_file || $_SESSION[APPCONFIG]->getValue('debug','file')) {
		if (! $debug_file)
			$debug_file = fopen($_SESSION[APPCONFIG]->getValue('debug','file'),
				$_SESSION[APPCONFIG]->getValue('debug','append') ? 'a' : 'w');

		fwrite($debug_file,$debug_message."\n");
	}

	if ($_SESSION[APPCONFIG]->getValue('debug','syslog') && function_exists('syslog_notice'))
		syslog_notice($debug_message);
}

/**
 * Display an error message in the system message panel of the page.
 */
function error($msg,$type='note',$redirect=null,$fatal=false,$backtrace=false) {
	global $www;
	static $counter;

	# Just a check to see that we are called right.
	if (! isset($www['page']) && ! $fatal)
		die("Function error called incorrectly [$msg]");

	# If the error is fatal, we'll need to stop here.
	if (! isset($www['page']))
		$www['page'] = new page();

	if ($fatal)
		$www['page']->setsysmsg(array('title'=>_('Error'),'body'=>$msg,'type'=>$type));
	else
		system_message(array('title'=>_('Error'),'body'=>$msg,'type'=>$type),$redirect);

	# Spin loop detection
	if ($counter++ > 20) {
		debug_dump('Spin loop detection.');
		debug_dump(array('msg'=>$msg,'session'=>$_SESSION['sysmsg'],'www'=>$www),1);
	}

	# Do we have a backtrace to display?
	if ($backtrace) {
		$backtraceblock = new block();
		$backtraceblock->SetTitle('PHP Debug Backtrace');

		$body = '<table class="result_table">';
		$body .= "\n";

		foreach (debug_backtrace() as $error => $line) {
			$_SESSION['backtrace'][$error]['file'] = isset($line['file']) ? $line['file'] : 'unknown';
			$_SESSION['backtrace'][$error]['line'] = isset($line['line']) ? $line['line'] : 'unknown';
			$body .= sprintf('<tr class="hightlight"><td colspan="2"><b><small>%s</small></b></td><td>%s (%s)</td></tr>',
				_('File'),isset($line['file']) ? $line['file'] : $last['file'],isset($line['line']) ? $line['line'] : '');

			$_SESSION['backtrace'][$error]['function'] = $line['function'];
			$body .= sprintf('<tr><td>&nbsp;</td><td><b><small>%s</small></b></td><td><small>%s',
				_('Function'),$line['function']);

			if (isset($line['args'])) {
				$display = strlen(serialize($line['args'])) < 50 ? htmlspecialchars(serialize($line['args'])) : htmlspecialchars(substr(serialize($line['args']),0,50)).'...<TRUNCATED>';
				$_SESSION['backtrace'][$error]['args'] = $line['args'];
				if (file_exists(LIBDIR.'../tools/unserialize.php'))
					$body .= sprintf('&nbsp;(<a href="%s?index=%s" onclick="target=\'backtrace\';">%s</a>)',
						'../tools/unserialize.php',$error,$display);
				else
					$body .= sprintf('&nbsp;(%s)',$display);
			}
			$body .= '</small></td></tr>';
			$body .= "\n";

			if (isset($line['file']))
				$last['file'] = $line['file'];
		}

		$body .= '</table>';
		$body .= "\n";
		$backtraceblock->SetBody($body);

		$www['page']->block_add('body',$backtraceblock);
	}

	if ($fatal) {
		$www['page']->display(array('tree'=>false));
		die();
	}
}

/**
 * Return the result of a form variable, with optional default
 *
 * @return The form GET/REQUEST/SESSION/POST variable value or its default
 */
function get_request($attr,$type='POST',$die=false,$default=null) {
	switch($type) {
		case 'GET':
			$value = isset($_GET[$attr]) ? (is_array($_GET[$attr]) ? $_GET[$attr] : (empty($_GET['nodecode'][$attr]) ? rawurldecode($_GET[$attr]) : $_GET[$attr])) : $default;
			break;

		case 'REQUEST':
			$value = isset($_REQUEST[$attr]) ? (is_array($_REQUEST[$attr]) ? $_REQUEST[$attr] : (empty($_REQUEST['nodecode'][$attr]) ? rawurldecode($_REQUEST[$attr]) : $_REQUEST[$attr])) : $default;
			break;

		case 'SESSION':
			$value = isset($_SESSION[$attr]) ? (is_array($_SESSION[$attr]) ? $_SESSION[$attr] : (empty($_SESSION['nodecode'][$attr]) ? rawurldecode($_SESSION[$attr]) : $_SESSION[$attr])) : $default;
			break;

		case 'POST':
		default:
			$value = isset($_POST[$attr]) ? (is_array($_POST[$attr]) ? $_POST[$attr] : (empty($_POST['nodecode'][$attr]) ? rawurldecode($_POST[$attr]) : $_POST[$attr])) : $default;
			break;
	}

	if ($die && is_null($value))
		system_message(array(
			'title'=>_('Generic Error'),
			'body'=>sprintf('%s: Called "%s" without "%s" using "%s"',
				basename($_SERVER['PHP_SELF']),get_request('cmd','REQUEST'),$attr,$type),
			'type'=>'error'),
			'index.php');

	return $value;
}

/**
 * Record a system message.
 * This function can be used as an alternative to generate a system message, if page hasnt yet been defined.
 */
function system_message($msg,$redirect=null) {
	if (! is_array($msg))
		return null;

	if (! isset($msg['title']) && ! isset($msg['body']))
		return null;

	if (! isset($msg['type']))
		$msg['type'] = 'info';

	if (! isset($_SESSION['sysmsg']) || ! is_array($_SESSION['sysmsg']))
		$_SESSION['sysmsg'] = array();

	# Try and detect if we are in a redirect loop
	if (get_request('redirect','GET') && $msg['type'] != 'debug') {
		foreach ($_SESSION['sysmsg'] as $detail) {
			if ($msg == $detail && ! isset($detail['special'])) {
				debug_dump(array('Incoming MSG'=>$msg,'existing'=>$_SESSION['sysmsg']));
				debug_dump_backtrace('Redirect Loop Detected',true);
			}
		}
	}

	array_push($_SESSION['sysmsg'],$msg);

	if ($redirect) {
		if (preg_match('/\?/',$redirect))
			$redirect .= '&';
		else
			$redirect .= '?';
		$redirect .= 'redirect=true';

		# Check if we were an ajax request, and only render the ajax message
		if (get_request('meth','REQUEST') == 'ajax')
			$redirect .= '&meth=ajax';

		header("Location: $redirect");
		die();
	}
}

/**
 * Other Functions
 */

/**
 * Encryption using blowfish algorithm
 *
 * @param string Original data
 * @param string The secret
 * @return string The encrypted result
 * @author lem9 (taken from the phpMyAdmin source)
 */
function blowfish_encrypt($data,$secret=null) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# If our secret is null or blank, get the default.
	if ($secret === null || ! trim($secret))
		$secret = $_SESSION[APPCONFIG]->getValue('session','blowfish') ? $_SESSION[APPCONFIG]->getValue('session','blowfish') : session_id();

	# If the secret isnt set, then just return the data.
	if (! trim($secret))
		return $data;

	if (function_exists('mcrypt_module_open') && ! empty($data)) {
		$td = mcrypt_module_open(MCRYPT_BLOWFISH,'',MCRYPT_MODE_ECB,'');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_DEV_URANDOM);
		mcrypt_generic_init($td,substr($secret,0,mcrypt_enc_get_key_size($td)),$iv);
		$encrypted_data = base64_encode(mcrypt_generic($td,$data));
		mcrypt_generic_deinit($td);

		return $encrypted_data;
	}

	if (file_exists(LIBDIR.'blowfish.php'))
		require_once LIBDIR.'blowfish.php';
	else
		return $data;

	$pma_cipher = new Horde_Cipher_blowfish;
	$encrypt = '';

	for ($i=0; $i<strlen($data); $i+=8) {
		$block = substr($data, $i, 8);

		if (strlen($block) < 8)
			$block = full_str_pad($block,8,"\0", 1);

		$encrypt .= $pma_cipher->encryptBlock($block, $secret);
	}

	return base64_encode($encrypt);
}

/**
 * Decryption using blowfish algorithm
 *
 * @param string Encrypted data
 * @param string The secret
 * @return string Original data
 * @author lem9 (taken from the phpMyAdmin source)
 */
function blowfish_decrypt($encdata,$secret=null) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# This cache gives major speed up for stupid callers :)
	static $CACHE = array();

	if (isset($CACHE[$encdata]))
		return $CACHE[$encdata];

	# If our secret is null or blank, get the default.
	if ($secret === null || ! trim($secret))
		$secret = $_SESSION[APPCONFIG]->getValue('session','blowfish') ? $_SESSION[APPCONFIG]->getValue('session','blowfish') : session_id();

	# If the secret isnt set, then just return the data.
	if (! trim($secret))
		return $encdata;

	if (function_exists('mcrypt_module_open') && ! empty($encdata)) {
		$td = mcrypt_module_open(MCRYPT_BLOWFISH,'',MCRYPT_MODE_ECB,'');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_DEV_URANDOM);
		mcrypt_generic_init($td,substr($secret,0,mcrypt_enc_get_key_size($td)),$iv);
		$decrypted_data = trim(mdecrypt_generic($td,base64_decode($encdata)));
		mcrypt_generic_deinit($td);

		return $decrypted_data;
	}

	if (file_exists(LIBDIR.'blowfish.php'))
		require_once LIBDIR.'blowfish.php';
	else
		return $encdata;

	$pma_cipher = new Horde_Cipher_blowfish;
	$decrypt = '';
	$data = base64_decode($encdata);

	for ($i=0; $i<strlen($data); $i+=8)
		$decrypt .= $pma_cipher->decryptBlock(substr($data, $i, 8), $secret);

	// Strip off our \0's that were added.
	$return = preg_replace("/\\0*$/",'',$decrypt);
	$CACHE[$encdata] = $return;
	return $return;
}

/**
 * String padding
 *
 * @param string Input string
 * @param integer Length of the result
 * @param string The filling string
 * @param integer Padding mode
 * @return string The padded string
 */
function full_str_pad($input,$pad_length,$pad_string='',$pad_type=0) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$str = '';
	$length = $pad_length - strlen($input);

	if ($length > 0) { // str_repeat doesn't like negatives
		if ($pad_type == STR_PAD_RIGHT) { // STR_PAD_RIGHT == 1
			$str = $input.str_repeat($pad_string, $length);
		} elseif ($pad_type == STR_PAD_BOTH) { // STR_PAD_BOTH == 2
			$str = str_repeat($pad_string, floor($length/2));
			$str .= $input;
			$str .= str_repeat($pad_string, ceil($length/2));
		} else { // defaults to STR_PAD_LEFT == 0
			$str = str_repeat($pad_string, $length).$input;
		}

	} else { // if $length is negative or zero we don't need to do anything
		$str = $input;
	}
	return $str;
}

/**
 * Returns the cached array of LDAP resources.
 *
 * Note that internally, this function utilizes a two-layer cache,
 * one in memory using a static variable for multiple calls within
 * the same page load, and one in a session for multiple calls within
 * the same user session (spanning multiple page loads).
 *
 * @return Returns the cached attributed requested,
 *         or null if there is nothing cached..
 */
function get_cached_item($index,$item,$subitem='null') {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# Set default return
	$return = null;

	# Check config to make sure session-based caching is enabled.
	if ($_SESSION[APPCONFIG]->getValue('cache',$item) && isset($_SESSION['cache'][$index][$item][$subitem]))
		$return = $_SESSION['cache'][$index][$item][$subitem];

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Caches the specified $item for the specified $index.
 *
 * Returns true on success of false on failure.
 */
function set_cached_item($index,$item,$subitem='null',$data) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# Check config to make sure session-based caching is enabled.
	if ($_SESSION[APPCONFIG]->getValue('cache',$item)) {
		global $CACHE;

		$CACHE[$index][$item][$subitem] = $data;
		$_SESSION['cache'][$index][$item][$subitem] = $data;

		return true;

	} else
		return false;
}

/**
 * Deletes the cache for a specified $item for the specified $index
 */
function del_cached_item($index,$item,$subitem='null') {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	global $CACHE;

	# Check config to make sure session-based caching is enabled.
	if (isset($_SESSION['cache'][$index][$item][$subitem]))
		unset($_SESSION['cache'][$index][$item][$subitem]);

	if (isset($CACHE[$index][$item][$subitem]))
		unset($CACHE[$index][$item][$subitem]);
}

/**
 * Utility wrapper for setting cookies, which takes into consideration
 * application configuration values. On success, true is returned. On
 * failure, false is returned.
 *
 * @param string The name of the cookie to set.
 * @param string The value of the cookie to set.
 * @param int (optional) The duration in seconds of this cookie. If unspecified, $cookie_time is used from config.php
 * @param string (optional) The directory value of this cookie (see php.net/setcookie)
 * @return boolean
 */
function set_cookie($name,$val,$expire=null,$dir=null) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# Set default return
	$return = false;

	if ($expire == null) {
		$cookie_time = $_SESSION[APPCONFIG]->getValue('session','cookie_time');
		$expire = $cookie_time == 0 ? null : time() + $cookie_time;
	}

	if ($dir == null)
		$dir = dirname($_SERVER['PHP_SELF']);

	if (@setcookie($name,$val,$expire,$dir)) {
		$_COOKIE[$name] = $val;
		$return = true;
	}

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Get a customized file for a server
 * We don't need any caching, because it's done by PHP
 *
 * @param int The ID of the server
 * @param string The requested filename
 *
 * @return string The customized filename, if exists, or the standard one
 */
function get_custom_file($index,$filename,$path) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# Set default return
	$return = $path.$filename;
	$server = $_SESSION[APPCONFIG]->getServer($index);

	$custom = $server->getValue('custom','pages_prefix');
	if (! is_null($custom) && is_file(realpath($path.$custom.$filename)))
		$return = $path.$custom.$filename;

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Sort a multi dimensional array.
 *
 * @param array Multi demension array passed by reference
 * @param string Comma delimited string of sort keys.
 * @param boolean Whether to reverse sort.
 * @return array Sorted multi demension array.
 */
function masort(&$data,$sortby,$rev=0) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# if the array to sort is null or empty, or if we have some nasty chars
	if (! preg_match('/^[a-zA-Z0-9_]+(\([a-zA-Z0-9_,]*\))?$/',$sortby) || ! $data)
		return;

	static $CACHE = array();

	if (empty($CACHE[$sortby])) {
		$code = "\$c=0;\n";

		foreach (explode(',',$sortby) as $key) {
			$code .= "if (is_object(\$a) || is_object(\$b)) {\n";

			$code .= "	if (is_array(\$a->$key)) {\n";
			$code .= "		asort(\$a->$key);\n";
			$code .= "		\$aa = array_shift(\$a->$key);\n";
			$code .= "	} else\n";
			$code .= "		\$aa = \$a->$key;\n";

			$code .= "	if (is_array(\$b->$key)) {\n";
			$code .= "		asort(\$b->$key);\n";
			$code .= "		\$bb = array_shift(\$b->$key);\n";
			$code .= "	} else\n";
			$code .= "		\$bb = \$b->$key;\n";

			$code .= "	if (\$aa != \$bb)";
			if ($rev)
				$code .= "	return (\$aa < \$bb ? 1 : -1);\n";
			else
				$code .= "	return (\$aa > \$bb ? 1 : -1);\n";

			$code .= "} else {\n";

			$code .= "	\$a = array_change_key_case(\$a);\n";
			$code .= "	\$b = array_change_key_case(\$b);\n";

			$key = strtolower($key);

			$code .= "	if ((! isset(\$a['$key'])) && isset(\$b['$key'])) return 1;\n";
			$code .= "	if (isset(\$a['$key']) && (! isset(\$b['$key']))) return -1;\n";

			$code .= "	if ((isset(\$a['$key'])) && (isset(\$b['$key']))) {\n";
			$code .= "		if (is_array(\$a['$key'])) {\n";
			$code .= "			asort(\$a['$key']);\n";
			$code .= "			\$aa = array_shift(\$a['$key']);\n";
			$code .= "		} else\n";
			$code .= "			\$aa = \$a['$key'];\n";

			$code .= "		if (is_array(\$b['$key'])) {\n";
			$code .= "			asort(\$b['$key']);\n";
			$code .= "			\$bb = array_shift(\$b['$key']);\n";
			$code .= "		} else\n";
			$code .= "			\$bb = \$b['$key'];\n";

			$code .= "		if (\$aa != \$bb)\n";
			$code .= "			if (is_numeric(\$aa) && is_numeric(\$bb)) {\n";

			if ($rev)
				$code .= "				return (\$aa < \$bb ? 1 : -1);\n";
			else
				$code .= "				return (\$aa > \$bb ? 1 : -1);\n";

			$code .= "			} else {\n";

			if ($rev)
				$code .= "				if ( (\$c = strcasecmp(\$bb,\$aa)) != 0 ) return \$c;\n";
			else
				$code .= "				if ( (\$c = strcasecmp(\$aa,\$bb)) != 0 ) return \$c;\n";

			$code .= "		}\n";
			$code .= "	}\n";
			$code .= "}\n";
		}

		$code .= 'return $c;';

		$CACHE[$sortby] = create_function('$a, $b',$code);
	}

	uasort($data,$CACHE[$sortby]);
}

/**
 * Is compression enabled for output
 */
function isCompress() {
	return (isset($_SESSION[APPCONFIG]) && $_SESSION[APPCONFIG]->getValue('appearance','compress')
		&& ! ini_get('zlib.output_compression')
		&& preg_match('/gzip/',$_SERVER['HTTP_ACCEPT_ENCODING']));
}

/**
 * PLA specific Functions
 */

/**
 * Fetches whether the user has configured phpLDAPadmin to obfuscate passwords
 * with "*********" when displaying them.
 *
 * This is configured in config.php thus:
 * <code>
 *  $config->custom->appearance['obfuscate_password_display'] = true;
 * </code>
 *
 * Or if it is OK to show encrypted passwords but not clear text passwords
 * <code>
 *  $config->custom->appearance['show_clear_password'] = false;
 * </code>
 *
 * @param string Password encoding type
 * @return boolean
 */
function obfuscate_password_display($enc=null) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if ($_SESSION[APPCONFIG]->getValue('appearance','obfuscate_password_display'))
		$return = true;

	elseif (! $_SESSION[APPCONFIG]->getValue('appearance','show_clear_password') && (is_null($enc) || $enc == 'clear'))
		$return = true;

	else
		$return = false;

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Returns an HTML-beautified version of a DN.
 * Internally, this function makes use of pla_explode_dn() to break the
 * the DN into its components. It then glues them back together with
 * "pretty" HTML. The returned HTML is NOT to be used as a real DN, but
 * simply displayed.
 *
 * @param string The DN to pretty-print.
 * @return string
 */
function pretty_print_dn($dn) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$dn_save = $dn;
	$dn = pla_explode_dn($dn);

	if (! $dn)
		return $dn_save;

	foreach ($dn as $i => $element) {
		$element = htmlspecialchars($element);
		$element = explode('=',$element,2);
		$element = implode('<span style="color: blue; font-family: courier; font-weight: bold">=</span>',$element);
		$dn[$i] = $element;
	}

	$dn = implode('<span style="color:red; font-family:courier; font-weight: bold;">,</span>',$dn);

	return $dn;
}

/**
 * Given a string, this function returns true if the string has the format
 * of a DN (ie, looks like "cn=Foo,dc=example,dc=com"). Returns false otherwise.
 * The purpose of this function is so that developers can examine a string and
 * know if it looks like a DN, and draw a hyperlink as needed.
 *
 * (See unit_test.php for test cases)
 *
 * @param string The attribute to examine for "DNness"
 * @return boolean
 */
function is_dn_string($str) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	/* Try to break the string into its component parts if it can be done
	   ie, "uid=Manager" "dc=example" and "dc=com" */
	$parts = pla_explode_dn($str);
	if (! is_array($parts) || ! count($parts))
		return false;

	/* Foreach of the "parts", look for an "=" character,
	   and make sure neither the left nor the right is empty */
	foreach ($parts as $part) {
		if (! strpos($part,"="))
			return false;

		$sub_parts = explode('=',$part,2);
		$left = $sub_parts[0];
		$right = $sub_parts[1];

		if ( ! strlen(trim($left)) || ! strlen(trim($right)))
			return false;

		if (strpos($left,'#') !== false)
			return false;
	}

	# We survived the above rigor. This is a bonified DN string.
	return true;
}

/**
 * Get whether a string looks like an email address (user@example.com).
 *
 * @param string The string to analyze.
 * @return boolean Returns true if the specified string looks like an email address or false otherwise.
 */
function is_mail_string($str) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$mail_regex = "/^[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*$/";

	if (preg_match($mail_regex,$str))
		return true;
	else
		return false;
}

/**
 * Get whether a string looks like a web URL (http://www.example.com/)
 *
 * @param string The string to analyze.
 * @return boolean Returns true if the specified string looks like a web URL or false otherwise.
 */
function is_url_string($str) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$url_regex = '/^(ftp|https?):\/\/+[\w\.\-\/\?\=\&]*\w+/';

	if (preg_match($url_regex,$str))
		return true;
	else
		return false;
}

/**
 * Compares 2 DNs. If they are equivelant, returns 0, otherwise,
 * returns their sorting order (similar to strcmp()):
 *      Returns < 0 if dn1 is less than dn2.
 *      Returns > 0 if dn1 is greater than dn2.
 *
 * The comparison is performed starting with the top-most element
 * of the DN. Thus, the following list:
 *    <code>
 *       ou=people,dc=example,dc=com
 *       cn=Admin,ou=People,dc=example,dc=com
 *       cn=Joe,ou=people,dc=example,dc=com
 *       dc=example,dc=com
 *       cn=Fred,ou=people,dc=example,dc=org
 *       cn=Dave,ou=people,dc=example,dc=org
 *    </code>
 * Will be sorted thus using usort( $list, "pla_compare_dns" ):
 *    <code>
 *       dc=com
 *       dc=example,dc=com
 *       ou=people,dc=example,dc=com
 *       cn=Admin,ou=People,dc=example,dc=com
 *       cn=Joe,ou=people,dc=example,dc=com
 *       cn=Dave,ou=people,dc=example,dc=org
 *       cn=Fred,ou=people,dc=example,dc=org
 *    </code>
 *
 * @param string The first of two DNs to compare
 * @param string The second of two DNs to compare
 * @return int
 */
function pla_compare_dns($dn1,$dn2) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# If pla_compare_dns is passed via a tree, then we'll just get the DN part.
	if (is_array($dn1))
		if (isset($dn1['dn']))
			$dn1 = $dn1['dn'];
		else
			$dn1 = implode('+',$dn1);
	if (is_array($dn2))
		if (isset($dn2['dn']))
			$dn2 = $dn2['dn'];
		else
			$dn2 = implode('+',$dn2);

	# If they are obviously the same, return immediately
	if (! strcasecmp($dn1,$dn2))
		return 0;

	$dn1_parts = pla_explode_dn(pla_reverse_dn($dn1));
	$dn2_parts = pla_explode_dn(pla_reverse_dn($dn2));
	assert(is_array($dn1_parts));
	assert(is_array($dn2_parts));

	# Foreach of the "parts" of the smaller DN
	for ($i=0; $i < count($dn1_parts) && $i < count($dn2_parts); $i++) {
		/* dnX_part is of the form: "cn=joe" or "cn = joe" or "dc=example"
		   ie, one part of a multi-part DN. */
		$dn1_part = $dn1_parts[$i];
		$dn2_part = $dn2_parts[$i];

		/* Each "part" consists of two sub-parts:
		   1. the attribute (ie, "cn" or "o")
		   2. the value (ie, "joe" or "example") */
		$dn1_sub_parts = explode('=',$dn1_part,2);
		$dn2_sub_parts = explode('=',$dn2_part,2);

		$dn1_sub_part_attr = trim($dn1_sub_parts[0]);
		$dn2_sub_part_attr = trim($dn2_sub_parts[0]);

		if (0 != ($cmp = strcasecmp($dn1_sub_part_attr,$dn2_sub_part_attr)))
			return $cmp;

		$dn1_sub_part_val = trim($dn1_sub_parts[1]);
		$dn2_sub_part_val = trim($dn2_sub_parts[1]);
		if (0 != ($cmp = strcasecmp($dn1_sub_part_val,$dn2_sub_part_val)))
			return $cmp;
	}

	/* If we iterated through all entries in the smaller of the two DNs
	   (ie, the one with fewer parts), and the entries are different sized,
	   then, the smaller of the two must be "less than" than the larger. */
	if (count($dn1_parts) > count($dn2_parts)) {
		return 1;

	} elseif (count($dn2_parts) > count($dn1_parts)) {
		return -1;

	} else {
		return 0;
	}
}

/**
 * For LDAP servers with auto_number enabled, this function will get the next
 * available number using the host's preferred mechanism (pool or search).
 *
 * This is configured in config.php by server:
 *
 * <code>
 *   $servers->setValue('auto_number','enable',true|false);
 * </code>
 *
 * The available mechanisms are:
 * pool:
 *   The pool mechanism uses a user-configured entry in the LDAP server to
 *   store the last used "number". This mechanism simply fetches and increments
 *   and returns that value.
 *
 * search:
 *   The search mechanism will search the LDAP server that has the attribute
 *   set. It will then find the smallest value and "fills in the gaps" by
 *   incrementing the smallest attribute until an unused value is found.
 *
 * NOTE: Both mechanisms do NOT prevent race conditions or toe-stomping, so
 * care must be taken when actually creating the entry to check that the number
 * returned here has not been used in the mean time. Note that the two different
 * mechanisms may (will!) return different values as they use different algorithms
 * to arrive at their result. Do not be alarmed if (when!) this is the case.
 *
 * See config.php.example for more notes on the two mechanisms.
 *
 * @param string Base to start the search from
 * @param string Attribute to query
 * @param boolean Increment the result (for pool searches)
 * @param string LDAP filter to use (for pool searches)
 * @return int
 */
function get_next_number($base,$attr,$increment=false,$filter=false,$startmin=null) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$server = $_SESSION[APPCONFIG]->getServer(get_request('server_id','REQUEST'));
	$attr = strtolower($attr);
	$query = array();

	if (! $server->getValue('auto_number','enable')) {
		system_message(array(
			'title'=>_('AUTO_NUMBER is disabled for this server'),
			'body'=>sprintf('%s (<b>%s</b>)',_('A call was made to get_next_number(), however, it is disabled for this server'),$attr),
			'type'=>'warn'));

		return false;
	}

	# Check see and use our alternate uid_dn and password if we have it.
	if (! $server->login($server->getValue('auto_number','dn'),$server->getValue('auto_number','pass'),'auto_number')) {
		system_message(array(
			'title'=>_('AUTO_NUMBER invalid login/password'),
			'body'=>sprintf('%s (<b>%s</b>)',_('Unable to connect to LDAP server with the auto_number login/password, please check your configuration.'),
				$server->getName()),
			'type'=>'warn'));

		return false;
	}

	# Some error checking
	if (! $base) {
		$query['base'] = $server->getValue('auto_number','search_base');

		if (! trim($query['base'])) {
			system_message(array(
				'title'=>_('No AUTO_NUMBER search_base configured for this server'),
				'body'=>_('A call was made to get_next_number(), however, the base to search is empty.'),
				'type'=>'warn'));

			return false;
		}

	} else
		$query['base'] = $base;

	if (! $server->dnExists($query['base'])) {
		system_message(array(
			'title'=>_('No AUTO_NUMBER search_base exists for this server'),
			'body'=>sprintf('%s (<b>%s</b>)',_('A call was made to get_next_number(), however, the base to search does not exist for this server.'),$query['base']),
			'type'=>'warn'));

		return false;
	}

	if (! is_string($attr) || ! $server->getSchemaAttribute($attr)) {
		system_message(array(
			'title'=>_('AUTO_NUMBER search attribute invalid'),
			'body'=>sprintf('%s (<b>%s</b>)',_('The search attribute for AUTO_NUMBER is invalid, expecting a single valid attribute.'),$attr),
			'type'=>'warn'));

		return false;
	}

	$query['attrs'] = array($attr);

	# Based on the configured mechanism, go get the next available uidNumber!
	switch ($server->getValue('auto_number','mechanism')) {
		case 'search':
			$query['filter'] = sprintf('(%s=*)',$attr);
			$search = $server->query($query,'auto_number');

			# Construct a list of used numbers
			$autonum = array(0);

			foreach ($search as $dn => $values) {
				$values = array_change_key_case($values);
				foreach ($values[$attr] as $value)
					array_push($autonum,$value);
			}

			$autonum = array_unique($autonum);
			sort($autonum);

			# Start with the least existing autoNumber and add 1
			$minNumber = is_null($startmin) ? intval($autonum[0])+1 : $startmin;

			# Override our minNumber by the configuration if it exists.
			if (count($server->getValue('auto_number','min'))) {
				$min = array_change_key_case($server->getValue('auto_number','min'));

				if (isset($min[$attr]))
					$minNumber = $min[$attr] > $minNumber ? $min[$attr] : $minNumber;
			}

			for ($i=0;$i<count($autonum);$i++) {
				$num = $autonum[$i] < $minNumber ? $minNumber : $autonum[$i];

				/* If we're at the end of the list, or we've found a gap between this number and the
				   following, use the next available number in the gap. */
				if ($i+1 == count($autonum) || $autonum[$i+1] > $num+1)
					return $autonum[$i] >= $num ? $num+1 : $num;
			}

			# If we didnt find a suitable gap and are all above the minNumber, we'll just return the $minNumber
			return $minNumber;

			break;

		case 'pool':
			switch ($attr) {
				case 'gidnumber':
					$query['filter'] = '(objectClass=gidPool)';

					break;

				case 'uidnumber':
					$query['filter'] = '(objectClass=uidPool)';

					break;
			}

			# If we are called with a filter, we'll use the one from the configuration.
			if (! empty($filter))
				$query['filter'] = $filter;

			$search = $server->query($query,'auto_number');

			switch (count($search)) {
				case '1':
					break;

				case '0':
					system_message(array(
						'title'=>_('AUTO_NUMBER pool filter didnt return any DNs'),
						'body'=>sprintf('%s (<b>%s</b>)',_('Please change your filter parameter, or check your auto_number,search_base configuration'),$query['filter']),
						'type'=>'warn'));

					return false;

				default:
					system_message(array(
					'title'=>_('AUTO_NUMBER pool filter returned too many DNs'),
						'body'=>sprintf('%s (<b>%s</b>)',_('Please change your filter parameter, or check your auto_number,search_base configuration'),$query['filter']),
						'type'=>'warn'));

					return false;
			}

			# This should only iterate once.
			foreach ($search as $dn => $values) {
				$values = array_change_key_case($values);

				$autonum = $values[$attr][0];
				$poolDN = $values['dn'];
			}

			if ($increment) {
				$updatedattr = array($attr=>$autonum+1);
				$server->modify($poolDN,$updatedattr);
			}

			return $autonum;

		# No other cases allowed. The user has an error in the configuration
		default:
			system_message(array(
				'title'=>_('Invalid AUTO_NUMBER mechanism'),
				'body'=>sprintf('%s (<b>%s</b>)',_('Your config file specifies an unknown AUTO_NUMBER search mechanism.'),$server->getValue('auto_number','mechanism')),
				'type'=>'warn'));

			return false;
	}
}

/**
 * Given a DN and server ID, this function reads the DN's objectClasses and
 * determines which icon best represents the entry. The results of this query
 * are cached in a session variable so it is not run every time the tree
 * browser changes, just when exposing new DNs that were not displayed
 * previously. That means we can afford a little bit of inefficiency here
 * in favor of coolness. :)
 *
 * This function returns a string like "country.png". All icon files are assumed
 * to be contained in the /images/ directory of phpLDAPadmin.
 *
 * Developers are encouraged to add new icons to the images directory and modify
 * this function as needed to suit their types of LDAP entries. If the modifications
 * are general to an LDAP audience, the phpLDAPadmin team will gladly accept them
 * as a patch.
 *
 * @param string The DN of the entry whose icon you wish to fetch.
 * @return string
 */
function get_icon($server_id,$dn,$object_classes=array()) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$server = $_SESSION[APPCONFIG]->getServer($server_id);

	# Fetch and lowercase all the objectClasses in an array
	if (! count($object_classes))
		$object_classes = $server->getDNAttrValue($dn,'objectClass');

	foreach ($object_classes as $index => $value)
		$object_classes[$index] = strtolower($value);

	$rdn = get_rdn($dn);
	$rdn_parts = explode('=',$rdn,2);
	$rdn_value = isset($rdn_parts[0]) ? $rdn_parts[0] : null;
	$rdn_attr = isset($rdn_parts[1]) ? $rdn_parts[1] : null;
	unset($rdn_parts);

	# Return icon filename based upon objectClass value
	if (in_array('sambaaccount',$object_classes) &&
		'$' == $rdn{ strlen($rdn) - 1 })
		return 'nt_machine.png';

	if (in_array('sambaaccount',$object_classes))
		return 'nt_user.png';

	elseif (in_array('person',$object_classes) ||
		in_array('organizationalperson',$object_classes) ||
		in_array('inetorgperson',$object_classes) ||
		in_array('account',$object_classes) ||
		in_array('posixaccount',$object_classes))

		return 'ldap-user.png';

	elseif (in_array('organization',$object_classes))
		return 'ldap-o.png';

	elseif (in_array('organizationalunit',$object_classes))
		return 'ldap-ou.png';

	elseif (in_array('organizationalrole',$object_classes))
		return 'ldap-uid.png';

	elseif (in_array('dcobject',$object_classes) ||
		in_array('domainrelatedobject',$object_classes) ||
		in_array('domain',$object_classes) ||
		in_array('builtindomain',$object_classes))

		return 'ldap-dc.png';

	elseif (in_array('alias',$object_classes))
		return 'ldap-alias.png';

	elseif (in_array('room',$object_classes))
		return 'door.png';

	elseif (in_array('iphost',$object_classes))
		return 'host.png';

	elseif (in_array('device',$object_classes))
		return 'device.png';

	elseif (in_array('document',$object_classes))
		return 'document.png';

	elseif (in_array('country',$object_classes)) {
		$tmp = pla_explode_dn($dn);
		$cval = explode('=',$tmp[0],2);
		$cval = isset($cval[1]) ? $cval[1] : false;
		if ($cval && false === strpos($cval,'..') &&
			file_exists(realpath(sprintf('%s/../countries/%s.png',IMGDIR,strtolower($cval)))))

			return sprintf('../countries/%s.png',strtolower($cval));

		else
			return 'country.png';
	}

	elseif (in_array('jammvirtualdomain',$object_classes))
		return 'mail.png';

	elseif (in_array('locality',$object_classes))
		return 'locality.png';

	elseif (in_array('posixgroup',$object_classes) ||
		in_array('groupofnames',$object_classes) ||
		in_array('group',$object_classes))

		return 'ldap-ou.png';

	elseif (in_array('applicationprocess',$object_classes))
		return 'process.png';

	elseif (in_array('groupofuniquenames',$object_classes))
		return 'ldap-uniquegroup.png';

	elseif (in_array('nlsproductcontainer',$object_classes))
		return 'n.png';

	elseif (in_array('ndspkikeymaterial',$object_classes))
		return 'lock.png';

	elseif (in_array('server',$object_classes))
		return 'server-small.png';

	elseif (in_array('volume',$object_classes))
		return 'hard-drive.png';

	elseif (in_array('ndscatcatalog',$object_classes))
		return 'catalog.png';

	elseif (in_array('resource',$object_classes))
		return 'n.png';

	elseif (in_array('ldapgroup',$object_classes))
		return 'ldap-server.png';

	elseif (in_array('ldapserver',$object_classes))
		return 'ldap-server.png';

	elseif (in_array('nisserver',$object_classes))
		return 'ldap-server.png';

	elseif (in_array('rbscollection',$object_classes))
		return 'ldap-ou.png';

	elseif (in_array('dfsconfiguration',$object_classes))
		return 'nt_machine.png';

	elseif (in_array('applicationsettings',$object_classes))
		return 'server-settings.png';

	elseif (in_array('aspenalias',$object_classes))
		return 'mail.png';

	elseif (in_array('container',$object_classes))
		return 'folder.png';

	elseif (in_array('ipnetwork',$object_classes))
		return 'network.png';

	elseif (in_array('samserver',$object_classes))
		return 'server-small.png';

	elseif (in_array('lostandfound',$object_classes))
		return 'find.png';

	elseif (in_array('infrastructureupdate',$object_classes))
		return 'server-small.png';

	elseif (in_array('filelinktracking',$object_classes))
		return 'files.png';

	elseif (in_array('automountmap',$object_classes) ||
		in_array('automount',$object_classes))

		return 'hard-drive.png';

	elseif (strpos($rdn_value,'ipsec') === 0 ||
		strcasecmp($rdn_value,'IP Security') == 0||
		strcasecmp($rdn_value,'MSRADIUSPRIVKEY Secret') == 0 ||
		strpos($rdn_value,'BCKUPKEY_') === 0)

		return 'lock.png';

	elseif (strcasecmp($rdn_value,'MicrosoftDNS') == 0)
		return 'ldap-dc.png';

	# Oh well, I don't know what it is. Use a generic icon.
	else
		return 'ldap-default.png';
}

/**
 * Appends a servers base to a "sub" dn or returns the base.
 *
 * @param string The baseDN to be added if the DN is relative
 * @param string The DN to be made absolute
 * @return string|null Returns null if both base is null and sub_dn is null or empty
 */
function expand_dn_with_base($base,$sub_dn) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$empty_str = (is_null($sub_dn) || (($len=strlen(trim($sub_dn))) == 0));

	if ($empty_str)
		return $base;

	# If we have a string which doesn't need a base
	elseif ($sub_dn[$len-1] != ',')
		return $sub_dn;

	else
		return sprintf('%s%s',$sub_dn,$base);
}

/**
 * Used to generate a random salt for crypt-style passwords. Salt strings are used
 * to make pre-built hash cracking dictionaries difficult to use as the hash algorithm uses
 * not only the user's password but also a randomly generated string. The string is
 * stored as the first N characters of the hash for reference of hashing algorithms later.
 *
 * @param int The length of the salt string to generate.
 * @return string The generated salt string.
 */
function random_salt($length) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$possible = '0123456789'.
		'abcdefghijklmnopqrstuvwxyz'.
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
		'./';
	$str = '';
	mt_srand((double)microtime() * 1000000);

	while (strlen($str) < $length)
		$str .= substr($possible,(rand()%strlen($possible)),1);

	return $str;
}

/**
 * Given a DN string, this returns the 'RDN' portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'cn=Manager' (it is really the exact opposite of ds_ldap::getContainer()).
 *
 * @param string The DN whose RDN to return.
 * @param boolean If true, include attributes in the RDN string. See http://php.net/ldap_explode_dn for details
 * @return string The RDN
 */
function get_rdn($dn,$include_attrs=0,$decode=false) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (is_null($dn))
		return null;

	$rdn = pla_explode_dn($dn,$include_attrs);
	if (! count($rdn) || ! isset($rdn[0]))
		return $dn;

	if ($decode)
		$rdn = dn_unescape($rdn[0]);
	else
		$rdn = $rdn[0];

	return $rdn;
}

/**
 * Split an RDN into its attributes
 */
function rdn_explode($rdn) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# Setup to work out our RDN.
	$rdnarray = explode('\+',$rdn);

	# Capture items that have +, but are not an attribute
	foreach ($rdnarray as $index => $val) {
		if (preg_match('/=/',$val))
			$validindex = $index;

		if (! preg_match('/=/',$val)) {
			$rdnarray[$validindex] .= '+'.$val;
			unset($rdnarray[$index]);
		}
	}

	return $rdnarray;
}

/**
 * Given an LDAP error number, returns a verbose description of the error.
 * This function parses ldap_error_codes.txt and looks up the specified
 * ldap error number, and returns the verbose message defined in that file.
 *
 * <code>
 *  Array (
 *    [title] => "Invalid Credentials"
 *    [description] => "An invalid username and/or password was supplied to the LDAP server."
 *  )
 * </code>
 *
 * @param string The hex error number (ie, "0x42") of the LDAP error of interest.
 * @return array An associative array contianing the error title and description like so:
 */
function pla_verbose_error($key) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	static $CACHE = array();

	if (! count($CACHE)) {
		$source_file = LIBDIR.'ldap_error_codes.txt';

		if (! file_exists($source_file) || ! is_readable($source_file) || ! ($f = fopen($source_file,'r')))
			return false;

		$contents = fread($f,filesize($source_file));
		fclose($f);
		$entries = array();
		preg_match_all("/0x[A-Fa-f0-9][A-Za-z0-9]\s+[0-9A-Za-z_]+\s+\"[^\"]*\"\n/",
				$contents,$entries);

		foreach ($entries[0] as $values) {
			$entry = array();
			preg_match("/(0x[A-Za-z0-9][A-Za-z0-9])\s+([0-9A-Za-z_]+)\s+\"([^\"]*)\"/",$values,$entry);

			$hex_code = isset($entry[1]) ? $entry[1] : null;
			$title = isset($entry[2]) ? $entry[2] : null;
			$desc = isset($entry[3]) ? $entry[3] : null;
			$desc = preg_replace('/\s+/',' ',$desc);
			$CACHE[$hex_code] = array('title'=>$title,'desc'=>$desc);
		}
	}

	if (isset($CACHE[$key]))
		return $CACHE[$key];
	else
		return array('title' => null,'desc' => null);
}

/**
 * Given an LDAP OID number, returns a verbose description of the OID.
 * This function parses ldap_supported_oids.txt and looks up the specified
 * OID, and returns the verbose message defined in that file.
 *
 * <code>
 *  Array (
 *    [title] => All Operational Attribute
 *    [ref] => RFC 3673
 *    [desc] => An LDAP extension which clients may use to request the return of all operational attributes.
 *  )
 * </code>
 *
 * @param string The OID number (ie, "1.3.6.1.4.1.4203.1.5.1") of the OID of interest.
 * @return array An associative array contianing the OID title and description like so:
 */
function support_oid_to_text($key) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	static $CACHE = array();

	$unknown = array();
	$unknown['desc'] = 'We have no description for this OID, if you know what this OID provides, please let us know. Please also include an RFC reference if it is available.';
	$unknown['title'] = 'Can you help with this OID info?';

	if (! count($CACHE)) {
		$source_file = LIBDIR.'ldap_supported_oids.txt';

		if (! file_exists($source_file) || ! is_readable($source_file) || ! ($f = fopen($source_file,'r')))
			return false;

		$contents = fread($f,filesize($source_file));
		fclose($f);
		$entries = array();
		preg_match_all("/[0-9]\..+\s+\"[^\"]*\"\n/",$contents,$entries);

		foreach ($entries[0] as $values) {
			$entry = array();
			preg_match("/([0-9]\.([0-9]+\.)*[0-9]+)(\s+\"([^\"]*)\")?(\s+\"([^\"]*)\")?(\s+\"([^\"]*)\")?/",$values,$entry);
			$oid_id = isset($entry[1]) ? $entry[1] : null;

			if ($oid_id) {
				$CACHE[$oid_id]['title'] = isset($entry[4]) ? $entry[4] : null;
				$CACHE[$oid_id]['ref'] = isset($entry[6]) ? $entry[6] : null;
				$desc = isset($entry[8]) ? $entry[8] : sprintf('<acronym title="%s">%s</acronym>',$unknown['desc'],$unknown['title']);
				$CACHE[$oid_id]['desc'] = preg_replace('/\s+/',' ',$desc);
			}
		}
	}

	if (isset($CACHE[$key]))
		return $CACHE[$key];
	else
		return array(
			'title'=>$key,
			'ref'=>null,
			'desc'=>sprintf('<acronym title="%s">%s</acronym>',$unknown['desc'],$unknown['title']));
}

/**
 * Print an LDAP error message
 */
function ldap_error_msg($msg,$errnum) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$body = '<table border="0">';

	$errnum = ('0x'.str_pad(dechex($errnum),2,0,STR_PAD_LEFT));
	$verbose_error = pla_verbose_error($errnum);

	$body .= sprintf('<tr><td><b>%s</b>:</td><td>%s</td></tr>',_('LDAP said'),$msg);

	if ($verbose_error) {
		$body .= sprintf('<tr><td><b>%s</b>:</td><td>%s (%s)</td></tr>',_('Error number'),$errnum,$verbose_error['title']);
		$body .= sprintf('<tr><td><b>%s</b>:</td><td>%s</td></tr>',_('Description'),$verbose_error['desc']);

	} else {
		$body .= sprintf('<tr><td><b>%s</b>:</td><td>%s</td></tr>',_('Error number'),$errnum);
		$body .= sprintf('<tr><td><b>%s</b>:</td><td>(%s)</td></tr>',_('Description'),_('no description available'));
	}

	$body .= '</table>';

	return $body;
}

/**
 * Draw the jpegPhoto image(s) for an entry wrapped in HTML. Many options are available to
 * specify how the images are to be displayed.
 *
 * Usage Examples:
 *  <code>
 *   draw_jpeg_photo(0,'cn=Bob,ou=People,dc=example,dc=com',"jpegPhoto",0,true,array('img_opts'=>"border: 1px; width: 150px"));
 *   draw_jpeg_photo(1,'cn=Fred,ou=People,dc=example,dc=com',null,1);
 *  </code>
 *
 * @param object The Server to get the image from.
 * @param string The DN of the entry that contains the jpeg attribute you want to draw.
 * @param string The name of the attribute containing the jpeg data (usually 'jpegPhoto').
 * @param int Index of the attribute to draw
 * @param boolean If true, draws a button beneath the image titled 'Delete' allowing the user
 *                to delete the jpeg attribute by calling JavaScript function deleteAttribute() provided
 *                in the default modification template.
 * @param array Specifies optional image and CSS style attributes for the table tag. Supported keys are
 *                fixed_width, fixed_height, img_opts.
 */
function draw_jpeg_photo($server,$dn,$attr_name='jpegphoto',$index,$draw_delete_buttons=false,$options=array()) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$fixed = array();
	$fixed['width'] = isset($options['fixed_width']) ? $options['fixed_width'] : false;
	$fixed['height'] = isset($options['fixed_height']) ? $options['fixed_height'] : false;

	if (is_null($server))
		$jpeg_data = $_SESSION['tmp'];
	else
		$jpeg_data = $server->getDNAttrValues($dn,null,LDAP_DEREF_NEVER,array($attr_name));

	if (! isset($jpeg_data[$attr_name][$index]) || ! $jpeg_data[$attr_name][$index]) {
		system_message(array(
			'title'=>_('Unable to retrieve image'),
			'body'=>sprintf('%s %s',
				_('Could not fetch jpeg data for attribute'),$attr_name),
			'type'=>'warn'));

		# This should atleast generate some text that says "Image not available"
		printf('<img src="view_jpeg_photo.php?location=session&attr=%s" alt="Photo" />',$attr_name);

		return;
	}

	$width = 0;
	$height = 0;

	if (function_exists('getimagesize')) {
		$jpeg_temp_dir = realpath($_SESSION[APPCONFIG]->getValue('jpeg','tmpdir').'/');
		if (! is_writable($jpeg_temp_dir))
			system_message(array(
				'title'=>_('Unable to write to jpeg tmp directory'),
				'body'=>_('Please set jpeg,tmpdir to a writable directory in the phpLDAPadmin config.php'),
				'type'=>'warn'));

		else {
			# We have an image to display
			$jpeg_filename = tempnam($jpeg_temp_dir.'/','pla');
			$outjpeg = @file_put_contents($jpeg_filename,$jpeg_data[$attr_name][$index]);

			if (! $outjpeg) {
				system_message(array(
					'title'=>_('Error writing to jpeg tmp directory'),
					'body'=>sprintf(_('Please check jpeg,tmpdir is a writable directory in the phpLDAPadmin config.php'),$jpeg_temp_dir),
					'type'=>'warn'));

			} elseif ($outjpeg < 6) {
				system_message(array(
					'title'=>sprintf('%s %s',$attr_name,_('contains errors')),
					'body'=>_('It appears that the jpeg image may not be a jpeg image'),
					'type'=>'warn'));

			} else {
				$jpeg_dimensions = getimagesize($jpeg_filename);
				$width = $jpeg_dimensions[0];
				$height = $jpeg_dimensions[1];
			}

			unlink($jpeg_filename);
		}
	}

	if ($width > 300) {
		$scale_factor = 300 / $width;
		$img_width = 300;
		$img_height = intval($height * $scale_factor);

	} else {
		$img_width = $width;
		$img_height = $height;
	}

	$href = sprintf('view_jpeg_photo.php?dn=%s&index=%s&attr=%s',rawurlencode($dn),$index,$attr_name);

	printf('<acronym title="%s %s. %s x %s %s.">',number_format($outjpeg),_('bytes'),$width,$height,_('pixels'));

	printf('<img src="%s&amp;%s" alt="Photo" %s%s%s />',
		htmlspecialchars($href),
		is_null($server) ? 'location=session' : sprintf('server_id=%s',$server->getIndex()),
		(! $img_width || $fixed['width'] ? '' : sprintf('width="%s"',$img_width)),
		(! $img_height || $fixed['height'] ? '' : sprintf('height="%s"',$img_height)),
		(isset($options['img_opts']) ? $options['img_opts'] : ''));

	echo '</acronym>';

	if ($draw_delete_buttons)
		# <!-- JavaScript function deleteJpegPhoto() to be defined later by calling script -->
		printf('<br/><a href="javascript:deleteAttribute(\'%s\');" style="color:red; font-size: 75%%">%s</a>',
			$attr_name,_('Delete photo'));
}

/**
 * Return the list of available password types
 *
 * @todo Dynamically work this list out so we only present hashes that we can encrypt
 */
function password_types() {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	return array(
		''=>'clear',
		'blowfish'=>'blowfish',
		'crypt'=>'crypt',
		'ext_des'=>'ext_des',
		'md5'=>'md5',
		'k5key'=>'k5key',
		'md5crypt'=>'md5crypt',
		'sha'=>'sha',
		'smd5'=>'smd5',
		'ssha'=>'ssha',
		'sha512'=>'sha512',
	);
}

/**
 * Hashes a password and returns the hash based on the specified enc_type.
 *
 * @param string The password to hash in clear text.
 * @param string Standard LDAP encryption type which must be one of
 *        crypt, ext_des, md5crypt, blowfish, md5, sha, smd5, ssha, sha512, or clear.
 * @return string The hashed password.
 */
function password_hash($password_clear,$enc_type) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$enc_type = strtolower($enc_type);

	switch($enc_type) {
		case 'blowfish':
			if (! defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH == 0)
				error(_('Your system crypt library does not support blowfish encryption.'),'error','index.php');

			# Hardcoded to second blowfish version and set number of rounds
			$new_value = sprintf('{CRYPT}%s',crypt($password_clear,'$2a$12$'.random_salt(13)));

			break;

		case 'crypt':
			if ($_SESSION[APPCONFIG]->getValue('password', 'no_random_crypt_salt'))
				$new_value = sprintf('{CRYPT}%s',crypt($password_clear,substr($password_clear,0,2)));
			else
				$new_value = sprintf('{CRYPT}%s',crypt($password_clear,random_salt(2)));

			break;

		case 'ext_des':
			# Extended des crypt. see OpenBSD crypt man page.
			if (! defined('CRYPT_EXT_DES') || CRYPT_EXT_DES == 0)
				error(_('Your system crypt library does not support extended DES encryption.'),'error','index.php');

			$new_value = sprintf('{CRYPT}%s',crypt($password_clear,'_'.random_salt(8)));

			break;

		case 'k5key':
			$new_value = sprintf('{K5KEY}%s',$password_clear);

			system_message(array(
				'title'=>_('Unable to Encrypt Password'),
				'body'=>'phpLDAPadmin cannot encrypt K5KEY passwords',
				'type'=>'warn'));

			break;

		case 'md5':
			$new_value = sprintf('{MD5}%s',base64_encode(pack('H*',md5($password_clear))));
			break;

		case 'md5crypt':
			if (! defined('CRYPT_MD5') || CRYPT_MD5 == 0)
				error(_('Your system crypt library does not support md5crypt encryption.'),'error','index.php');

			$new_value = sprintf('{CRYPT}%s',crypt($password_clear,'$1$'.random_salt(9)));

			break;

		case 'sha':
			# Use php 4.3.0+ sha1 function, if it is available.
			if (function_exists('sha1'))
				$new_value = sprintf('{SHA}%s',base64_encode(pack('H*',sha1($password_clear))));
			elseif (function_exists('mhash'))
				$new_value = sprintf('{SHA}%s',base64_encode(mhash(MHASH_SHA1,$password_clear)));
			else
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');

			break;

		case 'ssha':
			if (function_exists('mhash') && function_exists('mhash_keygen_s2k')) {
				mt_srand((double)microtime()*1000000);
				$salt = mhash_keygen_s2k(MHASH_SHA1,$password_clear,substr(pack('h*',md5(mt_rand())),0,8),4);
				$new_value = sprintf('{SSHA}%s',base64_encode(mhash(MHASH_SHA1,$password_clear.$salt).$salt));

			} else {
				error(_('Your PHP install does not have the mhash() or mhash_keygen_s2k() function. Cannot do S2K hashes.'),'error','index.php');
			}

			break;

		case 'smd5':
			if (function_exists('mhash') && function_exists('mhash_keygen_s2k')) {
				mt_srand((double)microtime()*1000000);
				$salt = mhash_keygen_s2k(MHASH_MD5,$password_clear,substr(pack('h*',md5(mt_rand())),0,8),4);
				$new_value = sprintf('{SMD5}%s',base64_encode(mhash(MHASH_MD5,$password_clear.$salt).$salt));

			} else {
				error(_('Your PHP install does not have the mhash() or mhash_keygen_s2k() function. Cannot do S2K hashes.'),'error','index.php');
			}

			break;

		case 'sha512':
			if (function_exists('openssl_digest') && function_exists('base64_encode')) {
				$new_value = sprintf('{SHA512}%s', base64_encode(openssl_digest($password_clear, 'sha512', true)));

			} else {
				error(_('Your PHP install doest not have the openssl_digest() or base64_encode() function. Cannot do SHA512 hashes. '),'error','index.php');
			}

			break;

		case 'clear':
		default:
			$new_value = $password_clear;
	}

	return $new_value;
}

/**
 * Given a clear-text password and a hash, this function determines if the clear-text password
 * is the password that was used to generate the hash. This is handy to verify a user's password
 * when all that is given is the hash and a "guess".
 * @param String The hash.
 * @param String The password in clear text to test.
 * @return Boolean True if the clear password matches the hash, and false otherwise.
 */
function password_check($cryptedpassword,$plainpassword,$attribute='userpassword') {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (in_array($attribute,array('sambalmpassword','sambantpassword'))) {
		$smb = new smbHash;

		switch($attribute) {
			case 'sambalmpassword':
				if (strcmp($smb->lmhash($plainpassword),strtoupper($cryptedpassword)) == 0)
					return true;
				else
					return false;

			case 'sambantpassword':
				if (strcmp($smb->nthash($plainpassword),strtoupper($cryptedpassword)) == 0)
					return true;
				else
					return false;
		}

		return false;
	}

	if (preg_match('/{([^}]+)}(.*)/',$cryptedpassword,$matches)) {
		$cryptedpassword = $matches[2];
		$cypher = strtolower($matches[1]);

	} else {
		$cypher = null;
	}

	switch($cypher) {
		# SSHA crypted passwords
		case 'ssha':
			# Check php mhash support before using it
			if (function_exists('mhash')) {
				$hash = base64_decode($cryptedpassword);

				# OpenLDAP uses a 4 byte salt, SunDS uses an 8 byte salt - both from char 20.
				$salt = substr($hash,20);
				$new_hash = base64_encode(mhash(MHASH_SHA1,$plainpassword.$salt).$salt);

				if (strcmp($cryptedpassword,$new_hash) == 0)
					return true;
				else
					return false;

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
			}

			break;

		# Salted MD5
		case 'smd5':
			# Check php mhash support before using it
			if (function_exists('mhash')) {
				$hash = base64_decode($cryptedpassword);
				$salt = substr($hash,16);
				$new_hash = base64_encode(mhash(MHASH_MD5,$plainpassword.$salt).$salt);

				if (strcmp($cryptedpassword,$new_hash) == 0)
					return true;
				else
					return false;

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
			}

			break;

		# SHA crypted passwords
		case 'sha':
			if (strcasecmp(password_hash($plainpassword,'sha'),'{SHA}'.$cryptedpassword) == 0)
				return true;
			else
				return false;

			break;

		# MD5 crypted passwords
		case 'md5':
			if( strcasecmp(password_hash($plainpassword,'md5'),'{MD5}'.$cryptedpassword) == 0)
				return true;
			else
				return false;

			break;

		# Crypt passwords
		case 'crypt':
			# Check if it's blowfish crypt
			if (preg_match('/^\\$2+/',$cryptedpassword)) {

				# Make sure that web server supports blowfish crypt
				if (! defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH == 0)
					error(_('Your system crypt library does not support blowfish encryption.'),'error','index.php');

				list($version,$rounds,$salt_hash) = explode('$',$cryptedpassword);

				if (crypt($plainpassword,'$'.$version.'$'.$rounds.'$'.$salt_hash) == $cryptedpassword)
					return true;
				else
					return false;
			}

			# Check if it's an crypted md5
			elseif (strstr($cryptedpassword,'$1$')) {

				# Make sure that web server supports md5 crypt
				if (! defined('CRYPT_MD5') || CRYPT_MD5 == 0)
					error(_('Your system crypt library does not support md5crypt encryption.'),'error','index.php');

				list($dummy,$type,$salt,$hash) = explode('$',$cryptedpassword);

				if (crypt($plainpassword,'$1$'.$salt) == $cryptedpassword)
					return true;
				else
					return false;
			}

			# Check if it's extended des crypt
			elseif (strstr($cryptedpassword,'_')) {

				# Make sure that web server supports ext_des
				if (! defined('CRYPT_EXT_DES') || CRYPT_EXT_DES == 0)
					error(_('Your system crypt library does not support extended DES encryption.'),'error','index.php');

				if (crypt($plainpassword,$cryptedpassword) == $cryptedpassword)
					return true;
				else
					return false;
			}

			# Password is plain crypt
			else {

				if (crypt($plainpassword,$cryptedpassword) == $cryptedpassword)
					return true;
				else
					return false;
			}

			break;

		# SHA512 crypted passwords
		case 'sha512':
			if (strcasecmp(password_hash($plainpassword,'sha512'),'{SHA512}'.$cryptedpassword) == 0)
				return true;
			else
				return false;

			break;

		# No crypt is given assume plaintext passwords are used
		default:
			if ($plainpassword == $cryptedpassword)
				return true;
			else
				return false;
	}
}

/**
 * Detects password encryption type
 *
 * Returns crypto string listed in braces. If it is 'crypt' password,
 * returns crypto detected in password hash. Function should detect
 * md5crypt, blowfish and extended DES crypt. If function fails to detect
 * encryption type, it returns NULL.
 * @param string Hashed password
 * @return string
 */
function get_enc_type($user_password) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# Capture the stuff in the { } to determine if this is crypt, md5, etc.
	$enc_type = null;

	if (preg_match('/{([^}]+)}/',$user_password,$enc_type))
		$enc_type = strtolower($enc_type[1]);
	else
		return null;

	# Handle crypt types
	if (strcasecmp($enc_type,'crypt') == 0) {

		# No need to check for standard crypt, because enc_type is already equal to 'crypt'.
		if (preg_match('/{[^}]+}\\$1\\$+/',$user_password))
			$enc_type = 'md5crypt';

		elseif (preg_match('/{[^}]+}\\$2+/',$user_password))
			$enc_type = 'blowfish';

		elseif (preg_match('/{[^}]+}_+/',$user_password))
			$enc_type = 'ext_des';
	}

	return $enc_type;
}

/**
 * Draws an HTML browse button which, when clicked, pops up a DN chooser dialog.
 * @param string The name of the form element to which this chooser
 *         dialog will publish the user's choice. The form element must be a member
 *         of a form with the "name" or "id" attribute set in the form tag, and the element
 *         must also define "name" or "id" for JavaScript to uniquely identify it.
 *         Example $form_element values may include "creation_form.container" or
 *         "edit_form.member_uid". See /templates/modification/default.php for example usage.
 * @param boolean (optional) If true, the function draws the localized text "choose" to the right of the button.
 */
function draw_chooser_link($form,$element,$include_choose_text=true,$rdn='none') {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$href = sprintf("javascript:dnChooserPopup('%s','%s','%s');",$form,$element,$rdn == 'none' ? '' : rawurlencode($rdn));
	$title = _('Click to popup a dialog to select an entry (DN) graphically');

	printf('<a href="%s" title="%s"><img class="chooser" src="%s/find.png" alt="Find" /></a>',$href,$title,IMGDIR);

	if ($include_choose_text)
		printf('<span class="x-small"><a href="%s" title="%s">%s</a></span>',$href,$title,_('browse'));
}

/**
 * Explode a DN into an array of its RDN parts.
 *
 * NOTE: When a multivalue RDN is passed to ldap_explode_dn, the results returns with 'value + value';
 *
 * <code>
 *  Array (
 *    [0] => uid=ppratt
 *    [1] => ou=People
 *    [2] => dc=example
 *    [3] => dc=com
 *  )
 * </code>
 *
 * @param string The DN to explode.
 * @param int (optional) Whether to include attribute names (see http://php.net/ldap_explode_dn for details)
 * @return array An array of RDN parts of this format:
 */
function pla_explode_dn($dn,$with_attributes=0) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	global $CACHE;

	if (isset($CACHE['explode'][$dn][$with_attributes])) {
		if (DEBUG_ENABLED)
			debug_log('Return CACHED result (%s) for (%s)',1,0,__FILE__,__LINE__,__METHOD__,
				$CACHE['explode'][$dn][$with_attributes],$dn);

		return $CACHE['explode'][$dn][$with_attributes];
	}

	$dn = addcslashes($dn,'<>+";');

	# split the dn
	$result[0] = ldap_explode_dn(dn_escape($dn),0);
	$result[1] = ldap_explode_dn(dn_escape($dn),1);
	if (! $result[$with_attributes]) {
		if (DEBUG_ENABLED)
			debug_log('Returning NULL - NO result.',1,0,__FILE__,__LINE__,__METHOD__);

		return array();
	}

	# Remove our count value that ldap_explode_dn returns us.
	unset($result[0]['count']);
	unset($result[1]['count']);

	# Record the forward and reverse entries in the cache.
	foreach ($result as $key => $value) {
		# translate hex code into ascii for display
		$result[$key] = dn_unescape($value);

		$CACHE['explode'][implode(',',$result[0])][$key] = $result[$key];
		$CACHE['explode'][implode(',',array_reverse($result[0]))][$key] = array_reverse($result[$key]);
	}

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$result[$with_attributes]);

	return $result[$with_attributes];
}

/**
 * Parse a DN and escape any special characters
 */
function dn_escape($dn) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$olddn = $dn;

	# Check if the RDN has a comma and escape it.
	while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/',$dn))
		$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,',$dn);

	$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3',$dn);

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$dn);

	return $dn;
}

/**
 * Parse a DN and unescape any special characters
 */
function dn_unescape($dn) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (is_array($dn)) {
		$a = array();

		foreach ($dn as $key => $rdn)
			$a[$key] = preg_replace('/\\\([0-9A-Fa-f]{2})/e',"''.chr(hexdec('\\1')).''",$rdn);

		return $a;

	} else {
		return preg_replace('/\\\([0-9A-Fa-f]{2})/e',"''.chr(hexdec('\\1')).''",$dn);
	}
}

/**
 * Fetches the URL for the specified item. This is a convenience function for
 * fetching project HREFs (like bugs)
 *
 * @param string One of "open_bugs", "add_bug", "donate", or "add_rfe"
 *               (rfe = request for enhancement)
 * @return string The URL to the requested item.
 */
function get_href($type,$extra_info='') {
	$sf = 'https://sourceforge.net';
	$pla = 'http://phpldapadmin.sourceforge.net';
	$group_id = '61828';
	$bug_atid = '498546';
	$rfe_atid = '498549';
	$forum_id = 'phpldapadmin-users';

	switch($type) {
		case 'add_bug':
			return sprintf('%s/tracker/?func=add&amp;group_id=%s&amp;atid=%s',$sf,$group_id,$bug_atid);
		case 'add_rfe':
			return sprintf('%s/tracker/?func=add&amp;group_id=%s&amp;atid=%s',$sf,$group_id,$rfe_atid);
		case 'credits':
			return sprintf('%s/Credits',$pla);
		case 'documentation':
			return sprintf('%s/Documentation',$pla);
		case 'donate':
			return sprintf('%s/donate/index.php?group_id=%s',$sf,$group_id);
		case 'forum':
			return sprintf('%s/mailarchive/forum.php?forum_name=%s',$sf,$forum_id);
		case 'logo':
			if (! isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on')
				$proto = 'http';
			else
				$proto = 'https';

			return isset($_SESSION) && ! $_SESSION[APPCONFIG]->getValue('appearance','remoteurls') ? '' : sprintf('%s://sflogo.sourceforge.net/sflogo.php?group_id=%s&amp;type=10',$proto,$group_id);
		case 'sf':
			return sprintf('%s/projects/phpldapadmin',$sf);
		case 'web':
			return sprintf('%s',$pla);
		default:
			return null;
	}
}

/**
 * Returns the current time as a double (including micro-seconds).
 *
 * @return double The current time in seconds since the beginning of the UNIX epoch (Midnight Jan. 1, 1970)
 */
function utime() {
	$time = explode(' ',microtime());
	$usec = (double)$time[0];
	$sec = (double)$time[1];
	return $sec + $usec;
}

/**
 * Converts an array to a query-string with the option to exclude certain variables
 * from the returned query string. This is convenient if callers want to convert the
 * current GET query string or POST array into a string and replace certain
 * variables with their own.
 *
 * @param array The associate array to convert whose form is such that the keys are the
 *              names of the variables and the values are said variables' values like this:
 *              <code>
 *               Array (
 *                 [server_id] = 0,
 *                 [dn] = "dc=example,dc=com",
 *                 [attr] = "sn"
 *               )
 *              </code>
 *              This will produce a string like this: "server_id=0&dn=dc=example,dc=com&attr=sn"
 * @param array (optional) An array of variables to exclude in the resulting string
 * @return string The string created from the array.
 */
function array_to_query_string($array,$exclude_vars=array()) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (! is_array($array) || ! count($array))
		return '';

	$str = '';
	$i = 0;
	foreach ($array as $name => $val)
		if (! in_array($name,$exclude_vars))
			if (is_array($val))
				foreach ($val as $v) {
					if ($i++ > 0)
						$str .= '&';

					$str .= sprintf('%s[]=%s',rawurlencode($name),rawurlencode($v));
				}

			else {
				if ($i++ > 0)
					$str .= '&';

				$str .= sprintf('%s=%s',rawurlencode($name),rawurlencode($val));
			}

	return $str;
}

/**
 * Reverses a DN such that the top-level RDN is first and the bottom-level RDN is last
 * For example:
 * <code>
 *   cn=Brigham,ou=People,dc=example,dc=com
 * </code>
 * Becomes:
 * <code>
 *   dc=com,dc=example,ou=People,cn=Brigham
 * </code>
 * This makes it possible to sort lists of DNs such that they are grouped by container.
 *
 * @param string The DN to reverse
 * @return string The reversed DN
 *
 * @see pla_compare_dns
 * @see pla_explode_dns
 */
function pla_reverse_dn($dn) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	return (implode(',',array_reverse(pla_explode_dn($dn))));
}

/**
 * Attribute sorting
 */
function sortAttrs($a,$b) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if ($a == $b)
		return 0;

	$server = $_SESSION[APPCONFIG]->getServer(get_request('server_id','REQUEST'));
	$attrs_display_order = arrayLower($_SESSION[APPCONFIG]->getValue('appearance','attr_display_order'));

	# Check if $a is in $attrs_display_order, get its key
	$a_key = array_search($a->getName(),$attrs_display_order);
	$b_key = array_search($b->getName(),$attrs_display_order);

	if ((! $a_key) && ($a_key !== 0))
		if ((! $a_key = array_search(strtolower($a->getFriendlyName()),$attrs_display_order)) && ($a_key !== 0))
			$a_key = count($attrs_display_order)+1;

	if ((! $b_key) && ($b_key !== 0))
		if ((! $b_key = array_search(strtolower($b->getFriendlyName()),$attrs_display_order)) && ($b_key !== 0))
			$b_key = count($attrs_display_order)+1;

	# Case where neither $a, nor $b are in $attrs_display_order, $a_key = $b_key = one greater than num elements.
	# So we sort them alphabetically
	if ($a_key === $b_key)
		return strcasecmp($a->getFriendlyName(),$b->getFriendlyName());

	# Case where at least one attribute or its friendly name is in $attrs_display_order
	# return -1 if $a before $b in $attrs_display_order
	return ($a_key < $b_key) ? -1 : 1;
}

/**
 * Reads an array and returns the array values back in lower case
 *
 * @param array $array The array to convert the values to lowercase.
 * @returns array Array with values converted to lowercase.
 */
function arrayLower($array) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (! is_array($array))
		return $array;

	$newarray = array();
	foreach ($array as $key => $value)
		$newarray[$key] = strtolower($value);

	return $newarray;
}

/**
 * Checks if a string exists in an array, ignoring case.
 *
 * @param string What you are looking for
 * @param array The array that you think it is in.
 * @return boolean True if its there, false if its not.
 */
function in_array_ignore_case($needle,$haystack) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (! is_array($haystack))
		return false;

	if (! is_string($needle))
		return false;

	$return = false;

	foreach ($haystack as $element) {
		if (is_string($element) && (strcasecmp($needle,$element) == 0)) {
			$return = true;
			break;
		}
	}

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Gets a DN string using the user-configured tree_display_format string to format it.
 */
function draw_formatted_dn($server,$entry) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$dn = $entry->getDn();

	$formats = $_SESSION[APPCONFIG]->getValue('appearance','tree_display_format');

	foreach ($formats as $format) {
		$has_none = false;
		preg_match_all('/%[a-zA-Z_0-9]+/',$format,$tokens);
		$tokens = $tokens[0];

		if (DEBUG_ENABLED)
			debug_log('The tokens are (%s)',1,0,__FILE__,__LINE__,__METHOD__,$tokens);

		foreach ($tokens as $token) {
			if (strcasecmp($token,'%dn') == 0)
				$format = str_replace($token,pretty_print_dn($dn),$format);

			elseif (strcasecmp($token,'%rdn') == 0)
				$format = str_replace($token,pretty_print_dn($entry->getRDN()),$format);

			elseif (strcasecmp($token,'%rdnvalue') == 0) {
				$rdn = get_rdn($dn,0,true);
				$rdn_value = explode('=',$rdn,2);
				$rdn_value = $rdn_value[1];
				$format = str_replace($token,$rdn_value,$format);

			} else {
				$attr_name = str_replace('%','',$token);
				$attr_values = $server->getDNAttrValue($dn,$attr_name);

				if (is_null($attr_values) || (count($attr_values) <= 0)) {
					$display = '&lt;'._('none').'&gt;';
					$has_none = true;

				} elseif (is_array($attr_values))
					$display = implode(', ',$attr_values);

				else
					$display = $attr_values;

				$format = str_replace($token,$display,$format);
			}
		}

		# If this format has all values available, use it. Otherwise, try the next one
		if (!$has_none)
			return $format;
	}

	return $format;
}

/**
 * Server html select list
 */
function server_select_list($selected=null,$logged_on=false,$name='index',$isVisible=true,$js=null) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$count = 0;
	$server_menu_html = sprintf('<select name="%s" id="%s" %s>',$name,$name,$js);

	foreach ($_SESSION[APPCONFIG]->getServerList($isVisible) as $index => $server) {
		if ($server->isVisible()) {

			if ($logged_on && ! $server->isLoggedIn())
				continue;

			$count++;
			$server_menu_html .= sprintf('<option value="%s" %s>%s</option>',
				$server->getIndex(),($server->getIndex() == $selected ? 'selected="selected"' : ''),$server->getName());

			# We will set this variable, in case there is only 1 hit.
			$selected_server = $server;
		}
	}

	$server_menu_html .= '</select>';

	if ($count > 1)
		return $server_menu_html;

	elseif ($count)
		return sprintf('%s <input type="hidden" name="%s" value="%s" />',
			$selected_server->getName(),$name,$selected_server->getIndex());

	else
		return '';
}

/**
 * Converts a little-endian hex-number to one, that 'hexdec' can convert
 */
function littleEndian($hex) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$result = '';

	for ($x=strlen($hex)-2;$x>= 0;$x=$x-2)
		$result .= substr($hex,$x,2);

	return $result;
}

function binSIDtoText($binsid) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$hex_sid = bin2hex($binsid);
	$rev = hexdec(substr($hex_sid,0,2)); // Get revision-part of SID
	$subcount = hexdec(substr($hex_sid,2,2)); // Get count of sub-auth entries
	$auth = hexdec(substr($hex_sid,4,12)); // SECURITY_NT_AUTHORITY

	$result = "$rev-$auth";

	for ($x=0;$x<$subcount;$x++) {
		$subauth[$x] = hexdec(littleEndian(substr($hex_sid,16+($x*8),8))); // get all SECURITY_NT_AUTHORITY
		$result .= sprintf('-%s',$subauth[$x]);
	}

	return $result;
}

/**
 * Query LDAP and return a hash.
 *
 * @param string The base DN to use.
 * @param string LDAP Query filter.
 * @param string LDAP attribute to use as key.
 * @param array Attributes to use as values.
 * @param boolean Specify false to not sort results by DN
 *                or true to have the returned array sorted by DN (uses ksort)
 *                or an array of attribute names to sort by attribute values
 * @return array Array of values keyed by $key.
 */
function return_ldap_hash($base,$filter,$key,$attrs,$sort=true) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$server = $_SESSION[APPCONFIG]->getServer(get_request('server_id','REQUEST'));
	$key = strtolower($key);

	$query = array();
	$query['base'] = $base;
	$query['filter'] = $filter;
	$query['attrs'] = $attrs;
	$search = $server->query($query,null);

	$results = array();

	foreach ($search as $dn => $values)
		if (isset($values[$key]))
			if (is_array($values[$key]))
				foreach ($values[$key] as $i => $k)
					foreach ($attrs as $attr) {
						$lattr = strtolower($attr);
						if (isset($values[$lattr])) {
							$v = '';

							if (is_array($values[$lattr]) && isset($values[$lattr][$i]))
								$v = $values[$lattr][$i];

							if (is_string($v) && (strlen($v) > 0))
								$results[$k][$attr] = $v;
						}
					}

			else
				foreach ($attrs as $attr) {
					$lattr = strtolower($attr);
					if (isset($values[$lattr]))
						$results[$values[$key]][$attr] = $values[$lattr];
				}

	if ($sort)
		masort($results,is_array($sort) ? implode(',',$sort) : 'dn');

	return $results;
}

/**
 * This function returns a string automatically generated
 * based on the criteria defined in the array $criteria in config.php
 */
function password_generate() {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$no_use_similiar = ! $_SESSION[APPCONFIG]->getValue('password','use_similar');
	$lowercase = $_SESSION[APPCONFIG]->getValue('password','lowercase');
	$uppercase = $_SESSION[APPCONFIG]->getValue('password','uppercase');
	$digits = $_SESSION[APPCONFIG]->getValue('password','numbers');
	$punctuation = $_SESSION[APPCONFIG]->getValue('password','punctuation');
	$length = $_SESSION[APPCONFIG]->getValue('password','length');

	$outarray = array();

	if ($no_use_similiar) {
		$raw_lower = 'a b c d e f g h k m n p q r s t u v w x y z';
		$raw_numbers = '2 3 4 5 6 7 8 9';
		$raw_punc = '# $ % ^ & * ( ) _ - + = . , [ ] { } :';

	} else {
		$raw_lower = 'a b c d e f g h i j k l m n o p q r s t u v w x y z';
		$raw_numbers = '1 2 3 4 5 6 7 8 9 0';
		$raw_punc = '# $ % ^ & * ( ) _ - + = . , [ ] { } : |';
	}

	$llower = explode(' ',$raw_lower);
	shuffle($llower);
	$lupper = explode(' ',strtoupper($raw_lower));
	shuffle($lupper);
	$numbers = explode(' ',$raw_numbers);
	shuffle($numbers);
	$punc = explode(' ',$raw_punc);
	shuffle($punc);

	if ($lowercase > 0)
		$outarray = array_merge($outarray,a_array_rand($llower,$lowercase));

	if ($uppercase > 0)
		$outarray = array_merge($outarray,a_array_rand($lupper,$uppercase));

	if ($digits > 0)
		$outarray = array_merge($outarray,a_array_rand($numbers,$digits));

	if ($punctuation > 0)
		$outarray = array_merge($outarray,a_array_rand($punc,$punctuation));

	$num_spec = $lowercase + $uppercase + $digits + $punctuation;

	if ($num_spec < $length) {
		$leftover = array();
		if ($lowercase > 0)
			$leftover = array_merge($leftover,$llower);
		if ($uppercase > 0)
			$leftover = array_merge($leftover,$lupper);
		if ($digits > 0)
			$leftover = array_merge($leftover,$numbers);
		if ($punctuation > 0)
			$leftover = array_merge($leftover,$punc);

		if (count($leftover) == 0)
			$leftover = array_merge($leftover,$llower,$lupper,$numbers,$punc);

		shuffle($leftover);
		$outarray = array_merge($outarray,a_array_rand($leftover,$length-$num_spec));
	}

	shuffle($outarray);
	$return = implode('',$outarray);

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * This function returns an array of $num_req values
 * randomly picked from the $input array
 *
 * @param array Array of values
 * @param integer Number of values in returned array
 * @return string The padded string
 */
function a_array_rand($input,$num_req) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (count($input) == 0)
		return array();

	if ($num_req < 1)
		return array();

	$return = array();
	if ($num_req > count($input)) {
		for($i = 0; $i < $num_req; $i++) {
			$idx = array_rand($input,1);
			$return[] = $input[$idx];
		}

	} else {
		$idxlist = array_rand($input,$num_req);
		if ($num_req == 1)
			$idxlist = array($idxlist);

		for($i = 0; $i < count($idxlist); $i++)
			$return[] = $input[$idxlist[$i]];
	}

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * This is for Opera. By putting "random junk" in the query string, it thinks
 * that it does not have a cached version of the page, and will thus
 * fetch the page rather than display the cached version
 */
function random_junk() {
	$time = gettimeofday();
	return md5(strtotime('now').$time['usec']);
}

/**
 * Returns a HTML id that can be used in the URL after the #.
 *
 * @param string The DN to pretty-print.
 * @return string
 */
function htmlid($sid,$dn) {
	return sprintf('SID%s:%s',$sid,preg_replace('/[\ =,]/','_',$dn));
}

/**
 * Is PLA configured for AJAX display
 */
function isAjaxEnabled() {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	if (isset($_SESSION[APPCONFIG]))
		return ($_SESSION[APPCONFIG]->getValue('appearance','tree') == 'AJAXTree');
	else
		return false;
}
?>
