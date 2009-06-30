<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/functions.php,v 1.303.2.41 2008/12/12 12:52:26 wurley Exp $

/**
 * A collection of common generic functions used throughout the application.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

define('HTDOCDIR',sprintf('%s/',realpath(LIBDIR.'../htdocs/')));
define('LANGDIR',sprintf('%s/',realpath(LIBDIR.'../locale/')));
define('CONFDIR',sprintf('%s/',realpath(LIBDIR.'../config')));
define('TMPLDIR',sprintf('%s/',realpath(LIBDIR.'../templates/')));
define('DOCDIR',sprintf('%s/',realpath(LIBDIR.'../doc/')));
define('HOOKSDIR',sprintf('%s/',realpath(LIBDIR.'../hooks/')));
define('CSSDIR','css/');
define('IMGDIR','images/');
define('JSDIR','js/');

/**
 * Supplimental functions
 * This list is a list of supplimental functions that are used throughout
 * PLA. The order here IS important - so that files that refer to
 * functions defined in other files need to be listed after those files.
 */
$app['function_files'] = array(
	# Functions for talking to LDAP servers.
	LIBDIR.'server_functions.php',
	# Functions for sending syslog messages
	LIBDIR.'syslog.php',
	# Functions for managing the session (pla_session_start(), etc.)
	LIBDIR.'session_functions.php',
	# Functions for reading the server schema
	LIBDIR.'schema_functions.php',
	# Functions for template manipulation.
	LIBDIR.'template_functions.php',
	# Functions for hashing passwords with OpenSSL binary (only if mhash not present)
	LIBDIR.'emuhash_functions.php',
	# Functions for creating Samba passwords
	LIBDIR.'createlm.php',
	# Functions for timeout and automatic logout feature
	LIBDIR.'timeout_functions.php',
	# Functions for rendering the page
	LIBDIR.'page.php'
);

/**
 * Loads class definition
 */
function __autoload($className) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
		debug_log('Call to autoload (%s)',1,__FILE__,__LINE__,__METHOD__,$className);

	if (file_exists(HOOKSDIR."classes/$className.php"))
		require_once(HOOKSDIR."classes/$className.php");
	else if (file_exists(LIBDIR."$className.php"))
		require_once(LIBDIR."$className.php");
	else
		system_message(array(
			'title'=>_('Generic Error'),
			'body'=>sprintf('%s: %s [%s]',
				__METHOD__,_('Called to load a class that cant be found'),$className),
			'type'=>'error'));
}

/**
 * If gettext is not installed, we will emulate it here.
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
 * @param int $errno The PHP error number that occurred (ie, E_ERROR, E_WARNING, E_PARSE, etc).
 * @param string $errstr The PHP error string provided (ie, "Warning index "foo" is undefined)
 * @param string $file The file in which the PHP error ocurred.
 * @param int $lineno The line number on which the PHP error ocurred
 *
 * @see set_error_handler
 */
function pla_error_handler($errno,$errstr,$file,$lineno) {
	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$errno,$errstr,$file,$lineno);

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
			pla_version(),phpversion(),php_sapi_name());
		$body .= sprintf('<tr><td>Web server:</td><td><b>%s</b></td></tr>',$_SERVER['SERVER_SOFTWARE']);

		if (function_exists('get_href'))
			$body .= sprintf('<tr><td colspan="2"><a target="new" href="%s"><center>%s.</center></a></td></tr>',
				get_href('search_bug',"&summary_keyword=".htmlspecialchars($errstr)),
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
 * Returns the phpLDAPadmin version currently running. The version
 * is read from the file named VERSION.
 *
 * @return string The current version as read from the VERSION file.
 */
function pla_version() {
	static $return = null;

	if ($return)
		return $return;

	$version_file = realpath(LIBDIR.'../VERSION');
	if (! file_exists($version_file))
		$return = 'UNKNOWN';

	else {
		$f = fopen($version_file,'r');
		$version = trim(fread($f, filesize($version_file)));
		fclose($f);

		# We use cvs_prefix, because CVS will translate this on checkout otherwise.
		$cvs_prefix = '\$Name:';

		$return = preg_replace('/^'.$cvs_prefix.' RELEASE-([0-9_]+)\s*\$$/','$1',$version);
		$return = preg_replace('/_/','.',$return);

		# Check if we are a CVS copy.
		if (preg_match('/^'.$cvs_prefix.'?\s*\$$/',$return))
			$return = 'CVS';

		# Check if we are special CVS branch
		elseif (preg_match('/^'.$cvs_prefix.'?\s*([a-zA-Z]+)?\s*\$$/',$return,$match))
			$return = $match[1];

		# If return is still the same as version, then the tag is not one we expect.
		elseif ($return == $version)
			$return = 'UNKNOWN';
	}

	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
		debug_log('Entered with (), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
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
 * Makes sure that the config file is properly setup and
 * that your install of PHP can handle LDAP stuff.
 */
function check_config($config_file) {
	global $config,$ldapservers;
	global $queries, $attrs_display_order;
	global $hidden_attrs, $hidden_except_dn, $hidden_attrs_ro;
	global $read_only_attrs, $read_only_except_dn, $unique_attrs;

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

				$body .= sprintf('<b>%s</b>: %s<br />',$i+1,htmlspecialchars($file[$i]));

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

	# Process our friendly attributes.
	if (isset($friendly_attrs))
		$config->getFriendlyAttrs($friendly_attrs);

	# Check for server definitions.
	if (! isset($ldapservers) || count($ldapservers->GetServerList()) == 0)
		system_message(array(
			'title'=>_('Missing required configuration'),
			'body'=> 'Your config.php is missing Server Definitions. Please see the sample file config/config.php.example.',
			'type'=>'error'));

	$config->ldapservers = $ldapservers;

	if (isset($queries)) $config->queries = $queries;
	if (isset($attrs_display_order)) $config->attrs_display_order = $attrs_display_order;
	if (isset($hidden_attrs)) $config->hidden_attrs = $hidden_attrs;
	if (isset($hidden_except_dn)) $config->hidden_except_dn = $hidden_except_dn;
	if (isset($hidden_attrs_ro)) $config->hidden_attrs_ro = $hidden_attrs_ro;
	if (isset($read_only_attrs)) $config->read_only_attrs = $read_only_attrs;
	if (isset($read_only_except_dn)) $config->read_only_except_dn = $read_only_except_dn;
	if (isset($unique_attrs)) $config->unique_attrs = $unique_attrs;

	# Check the memory limit parameter.
	if (ini_get('memory_limit') < $config->GetValue('session','memorylimit'))
		system_message(array(
			'title'=>_('Memory Limit low.'),
			'body'=> sprintf('Your php memory limit is low - currently %s',ini_get('memory_limit'),false),
			'type'=>'error'));

	# if $_SESSION['sysmsg'] is set, then there were problems.
	if (isset($_SESSION['sysmsg']))
		return false;

	return true;
}

/**
 * Commands available in the control_panel of the page
 *
 * @return array
 */
function cmd_control_pane() {
	return array(
		'home'=>array('link'=>sprintf('<a href="cmd.php?cmd=welcome" title="%s"><img src="%s/home-big.png" alt="%s" /><br />%s</a>',_('Home'),IMGDIR,_('Home'),_('Home'))),
		'purge'=>array('link'=>sprintf('<a href="cmd.php?cmd=purge_cache" title="%s"><img src="%s/trash-big.png" alt="%s" /><br />%s</a>',
			_('Purge caches'),IMGDIR,_('Purge all cached data in phpLDAPadmin, including server schemas.'),_('Purge caches'))),
		'external_links:feature'=>array('link'=>sprintf('<a href="%s" target="new" title="%s"><img src="%s/request-feature.png" alt="%s" /><br />%s</a>',
			get_href('add_rfe'),_('Request feature'),IMGDIR,_('light'),_('Request feature'))),
		'external_links:bug'=>array('link'=>sprintf('<a href="%s" target="new" title="%s"><img src="%s/bug-big.png" alt="%s" /><br />%s</a>',
			get_href('add_bug'),_('Report a bug'),IMGDIR,_('bug'),_('Report a bug'))),
		'external_links:donation'=>array('link'=>sprintf('<a href="%s" target="new" title="%s"><img src="%s/smile-big.png" alt="%s" /><br />%s</a>',
			get_href('donate'),_('Donate'),IMGDIR,_('Donate'),_('Donate'))),
		'appearance:hide_debug_info'=>array('link'=>sprintf('<a href="cmd.php?cmd=show_cache" title="%s"><img src="%s/debug-cache.png" alt="%s" /><br />%s</a>',
			_('Show Cache'),IMGDIR,_('Show Cache'),_('Show Cache'))),
		'external_links:help'=>array('link'=>sprintf('<a href="%s" target="new" title="%s"><img src="%s/help-big.png" alt="%s" /><br />%s</a>',
			get_href('documentation'),_('Help'),IMGDIR,_('Help'),_('Help')))
	);
}

/**
 * This function dumps the $variable for debugging purposes
 * @param string|array Variable to dump
 * @param boolean Whether to stop execution or not.
 */
function debug_dump($variable,$die=false,$onlydebugaddr=false) {
	if ($onlydebugaddr &&
		isset($_SESSION[APPCONFIG]) && $_SESSION[APPCONFIG]->GetValue('debug','addr') &&
		$_SERVER['HTTP_X_FORWARDED_FOR'] != $_SESSION[APPCONFIG]->GetValue('debug','addr') &&
		$_SERVER['REMOTE_ADDR'] != $_SESSION[APPCONFIG]->GetValue('debug','addr'))
		return;

	print '<PRE>';
	print_r($variable);
	print '</PRE>';

	if ($die)
		die();
}

/**
 * This function generates a backtrace
 * @param boolean Whether to stop execution or not.
 */
function debug_dump_backtrace($msg='Calling BackTrace',$die=false) {
	error($msg,'note',null,$die,true);
}

/**
 * Debug Logging to Syslog
 *
 * The global debug level is turned on in your configuration file by setting:
 * <code>
 *	$config->custom->debug['level'] = 255;
 * </code>
 * together with atleast one output direction (currently file and syslog are supported).
 * <code>
 *	$config->custom->debug['file'] = '/tmp/pla_debug.log';
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
 * @param string $msg Message to send to syslog
 * @param int $level Log bit number for this message.
 * @see syslog.php
 */

function debug_log($msg,$level=0) {
	global $debug_file,$timer;

	# Temporary, to catch when these are not set in the function arguments.
	$file = __FILE__;
	$line = __LINE__;
	$method = __METHOD__;

	# In case we are called before we are fully initialised or if debugging is not set.
	if (! isset($_SESSION[APPCONFIG]) || ! ($_SESSION[APPCONFIG]->GetValue('debug','file')
		|| $_SESSION[APPCONFIG]->GetValue('debug','syslog')))
		return false;

	$debug_level = $_SESSION[APPCONFIG]->GetValue('debug','level');
	if (! $debug_level || (! ($level & $debug_level)))
		return;

	$debugaddr = false;
	if ($_SESSION[APPCONFIG]->GetValue('debug','addr')) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $_SESSION[APPCONFIG]->GetValue('debug','addr'))
			$debugaddr = true;

		elseif ($_SERVER['REMOTE_ADDR'] == $_SESSION[APPCONFIG]->GetValue('debug','addr'))
			$debugaddr = true;
	} else $debugaddr = true;

	if (! $debugaddr)
		return;

	# If we are limiting debug to a browser, then check that
	$caller = basename( $_SERVER['PHP_SELF'] );

	if (func_num_args() > 2) {
		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		# This is temporary, until we change all the debug_log statements.
		if (is_string($args[0]) && preg_match('/.php$/',$args[0])) {
			$file = array_shift($args);
			$line = array_shift($args);
			$method = array_shift($args);
		}

		$fargs = array();
		foreach ($args as $key) {
			if (is_array($key) || is_object($key))
				array_push($fargs,serialize($key));
			else
				array_push($fargs,$key);
		}
		$msg = vsprintf($msg, array_values($fargs));
	}

	if (function_exists('stopwatch'))
		$timer = stopwatch();
	else
		$timer = null;

	$debug_message = sprintf('[%2.3f] %3s-%s(%04s): %s: %s',$timer,$level,basename($file),$line,$method,substr($msg,0,200));

	if ($debug_file || $_SESSION[APPCONFIG]->GetValue('debug','file')) {
		if (! $debug_file)
			$debug_file = fopen($_SESSION[APPCONFIG]->GetValue('debug','file'),
				$_SESSION[APPCONFIG]->GetValue('debug','append') ? 'a' : 'w');

		fwrite($debug_file,$debug_message."\n");
	}

	if ($_SESSION[APPCONFIG]->GetValue('debug','syslog'))
		syslog_notice($debug_message);

	return syslog_notice( sprintf('%s(%s): %s',$caller,$level,$msg) );
}

/**
 * Display an error message in the system message panel of the page.
 */
function error($msg,$type='note',$redirect=null,$fatal=false,$backtrace=false) {
	global $www;
	global $counter;

	# Just a check to see that we are called right.
	if (! isset($www['page']) && ! $fatal)
		die("Function error called incorrectly [$msg]");

	# if the error is fatal, we'll need to stop here.
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
			$_SESSION['backtrace'][$error]['file'] = $line['file'];
			$_SESSION['backtrace'][$error]['line'] = $line['line'];
			$body .= sprintf('<tr class="hightlight"><td colspan="2"><b><small>%s</small></b></td><td>%s (%s)</td></tr>',
				_('File'),isset($line['file']) ? $line['file'] : $last['file'],isset($line['line']) ? $line['line'] : '');

			$_SESSION['backtrace'][$error]['function'] = $line['function'];
			$body .= sprintf('<tr><td>&nbsp;</td><td><b><small>%s</small></b></td><td><small>%s',
				_('Function'),$line['function']);

			if (isset($line['args'])) {
				$display = strlen(serialize($line['args'])) < 50 ? serialize($line['args']) : substr(serialize($line['args']),0,50).'...<TRUNCATED>';
				$_SESSION['backtrace'][$error]['args'] = $line['args'];
				if (file_exists(LIBDIR.'../tools/unserialize.php'))
					$body .= sprintf('&nbsp;(<a href="%s?index=%s" target="backtrace">%s</a>)',
						'../tools/unserialize.php',$error,htmlspecialchars($display));
				else
					$body .= sprintf('&nbsp;(%s)',htmlspecialchars($display));
			}
			$body .= '</small></td></tr>';
			$body .= "\n";

			if ($line['file'])
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
 */
function get_request($attr,$type=null,$die=false,$default=null) {
	switch($type) {
		case 'GET':
			$value = isset($_GET[$attr]) ? $_GET[$attr] : $default;
			break;

		case 'REQUEST':
			$value = isset($_REQUEST[$attr]) ? $_REQUEST[$attr] : $default;
			break;

		case 'SESSION':
			$value = isset($_SESSION[$attr]) ? $_SESSION[$attr] : $default;
			break;

		case 'POST':
		default:
			$value = isset($_POST[$attr]) ? $_POST[$attr] : $default;
			break;
	}

	if (! is_array($value) && trim($value) == "")
		$value = $default;

	if ($die && is_null($value))
	   system_message(array(
			'title'=>_('Generic Error'),
			'body'=>sprintf('%s: Called "%s" without "%s" using "%s"',
				basename($_SERVER['PHP_SELF']),get_request('cmd','REQUEST'),$attr,$type),
			'type'=>'error'),
			sprintf('index.php?server_id=%s',$ldapserver->server_id));

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

	$_SESSION['sysmsg'][] = $msg;

	if (get_request('redirect','GET'))
		debug_dump_backtrace('Redirect Loop Detected',true);

	if ($redirect) {
		if (preg_match('/\?/',$redirect))
			$redirect .= '&';
		else
			$redirect .= '?';
		$redirect .= 'redirect=true';

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
 * @param   string  original data
 * @param   string  the secret
 *
 * @return  string  the encrypted result
 *
 * @access  public
 *
 * @author  lem9 (taken from the phpMyAdmin source)
 */
function pla_blowfish_encrypt($data,$secret=null) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$data,$secret);

	# If our secret is null or blank, get the default.
	if ($secret === null || ! trim($secret))
		$secret = $_SESSION[APPCONFIG]->GetValue('session','blowfish');

	# If the secret isnt set, then just return the data.
	if (! trim($secret))
		return $data;

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
 * @param   string  encrypted data
 * @param   string  the secret
 *
 * @return  string  original data
 *
 * @access  public
 *
 * @author  lem9 (taken from the phpMyAdmin source)
 */
function pla_blowfish_decrypt($encdata,$secret=null) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$encdata,$secret);

	# This cache gives major speed up for stupid callers :)
	static $CACHE = array();

	if (isset($CACHE[$encdata]))
		return $CACHE[$encdata];

	# If our secret is null or blank, get the default.
	if ($secret === null || ! trim($secret))
		$secret = $_SESSION[APPCONFIG]->GetValue('session','blowfish');

	# If the secret isnt set, then just return the data.
	if (! trim($secret))
		return $encdata;

	if (file_exists(LIBDIR.'blowfish.php'))
		require_once LIBDIR.'blowfish.php';
	else
		return $data;

	$pma_cipher = new Horde_Cipher_blowfish;
	$decrypt = '';
	$data = base64_decode($encdata);

	for ($i=0; $i<strlen($data); $i+=8)
		$decrypt .= $pma_cipher->decryptBlock(substr($data, $i, 8), $secret);

	$return = trim($decrypt);
	$CACHE[$encdata] = $return;
	return $return;
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
function get_cached_item($server_id,$item,$subitem='null') {
	# Set default return
	$return = null;

	# Check config to make sure session-based caching is enabled.
	if ($_SESSION[APPCONFIG]->GetValue('cache',$item)) {

		global $CACHE;
		if (isset($CACHE[$server_id][$item][$subitem])) {
			if (DEBUG_ENABLED)
				debug_log('Returning MEMORY cached [%s] (%s)',1,__FILE__,__LINE__,__METHOD__,
					$item,$subitem);

			$return = $CACHE[$server_id][$item][$subitem];

		} elseif (isset($_SESSION['cache'][$server_id][$item][$subitem])) {
			if (DEBUG_ENABLED)
				debug_log('Returning SESSION cached [%s] (%s)',1,__FILE__,__LINE__,__METHOD__,
					$item,$subitem);

			$return = $_SESSION['cache'][$server_id][$item][$subitem];
			$CACHE[$server_id][$item][$subitem] = $return;

		}
	}

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$server_id,$item,$subitem,$return);

	return $return;
}

/**
 * Caches the specified $item for the specified $server_id.
 *
 * Returns true on success of false on failure.
 */
function set_cached_item($server_id,$item,$subitem='null',$data) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$server_id,$item,$subitem,$data);

	# Check config to make sure session-based caching is enabled.
	if ($_SESSION[APPCONFIG]->GetValue('cache',$item)) {
		global $CACHE;

		$CACHE[$server_id][$item][$subitem] = $data;
		$_SESSION['cache'][$server_id][$item][$subitem] = $data;
		return true;

	} else
		return false;
}

/**
 * Deletes the cache for a specified $item for the specified $server_id
 *
 * @return: void
 */
function del_cached_item($server_id,$item,$subitem='null') {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$server_id,$item,$subitem);

	# Check config to make sure session-based caching is enabled.
	if (isset($_SESSION['cache'][$server_id][$item][$subitem]))
		unset($_SESSION['cache'][$server_id][$item][$subitem]);
}

/**
 * Utility wrapper for setting cookies, which takes into consideration
 * phpLDAPadmin configuration values. On success, true is returned. On
 * failure, false is returned.
 *
 * @param string $name The name of the cookie to set.
 * @param string $val The value of the cookie to set.
 * @param int $expire (optional) The duration in seconds of this cookie. If unspecified, $cookie_time
 *            is used from config.php
 * @param string $dir (optional) The directory value of this cookie (see php.net/setcookie)
 *
 * @see setcookie
 * @return bool
 */
function pla_set_cookie($name,$val,$expire=null,$dir=null) {
	# Set default return
	$return = false;

	if ($expire == null) {
		$cookie_time = $_SESSION[APPCONFIG]->GetValue('session','cookie_time');
		$expire = $cookie_time == 0 ? null : time() + $cookie_time;
	}

	if ($dir == null)
		$dir = dirname($_SERVER['PHP_SELF']);

	if (@setcookie($name,$val,$expire,$dir)) {
		$_COOKIE[$name] = $val;
		$return = true;
	}

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$name,$val,$expire,$dir,$return);

	return $return;
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
 *  $obfuscate_password_display = true;
 * </code>
 *
 * @param string $enc Password encoding type
 * @return bool
 */
function obfuscate_password_display($enc=null) {
	if ($_SESSION[APPCONFIG]->GetValue('appearance','obfuscate_password_display'))
		$return = true;

	elseif (! $_SESSION[APPCONFIG]->GetValue('appearance','show_clear_password') && (is_null($enc) || $enc == 'clear'))
		$return = true;

	else
		$return = false;

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$enc,$return);

	return $return;
}

/**
 * Returns an HTML-beautified version of a DN.
 * Internally, this function makes use of pla_explode_dn() to break the
 * the DN into its components. It then glues them back together with
 * "pretty" HTML. The returned HTML is NOT to be used as a real DN, but
 * simply displayed.
 *
 * @param string $dn The DN to pretty-print.
 * @return string
 */
function pretty_print_dn($dn) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

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
 * @param string $attr The attribute to examine for "DNness"
 * @see unit_test.php
 * @return bool
 */
function is_dn_string($str) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$str);

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
 * @param string $str The string to analyze.
 * @return bool Returns true if the specified string looks like
 *   an email address or false otherwise.
 */
function is_mail_string($str) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$str);

	$mail_regex = "/^[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*$/";

	if (preg_match($mail_regex,$str))
		return true;
	else
		return false;
}

/**
 * Get whether a string looks like a web URL (http://www.example.com/)
 *
 * @param string $str The string to analyze.
 * @return bool Returns true if the specified string looks like
 *   a web URL or false otherwise.
 */
function is_url_string($str) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$str);

	$url_regex = '/(ftp|https?):\/\/+[\w\.\-\/\?\=\&]*\w+/';

	if (preg_match($url_regex,$str))
		return true;
	else
		return false;
}

/**
 * Get a customized file for a server
 * We don't need any caching, because it's done by PHP
 *
 * @param int $server_id The ID of the server
 * @param string $filename The requested filename
 *
 * @return string The customized filename, if exists, or the standard one
 */
function get_custom_file($server_id,$filename,$path) {
	# Set default return
	$return = $path.$filename;

	$custom = $_SESSION[APPCONFIG]->ldapservers->GetValue($server_id,'custom','pages_prefix');
	if (! is_null($custom) && is_file(realpath($path.$custom.$filename)))
		$return = $path.$custom.$filename;

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$server_id,$filename,$path,$return);

	return $return;
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
 * @param string $dn1 The first of two DNs to compare
 * @param string $dn2 The second of two DNs to compare
 * @return int
 */
function pla_compare_dns($dn1,$dn2) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,$dn1,$dn2);

	# If pla_compare_dns is passed via a tree, then we'll just get the DN part.
	if (is_array($dn1))
		$dn1 = $dn1['dn'];
	if (is_array($dn2))
		$dn2 = $dn2['dn'];

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
 * Prunes off anything after the ";" in an attr name. This is useful for
 * attributes that may have ";binary" appended to their names. With
 * real_attr_name(), you can more easily fetch these attributes' schema
 * with their "real" attribute name.
 *
 * @param string $attr_name The name of the attribute to examine.
 * @return string
 */
function real_attr_name($attr_name) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$attr_name);

	$attr_name = preg_replace('/;.*$/U','',$attr_name);
	return $attr_name;
}

/**
 * For hosts who have 'enable_auto_uid_numbers' set to true, this function will
 * get the next available uidNumber using the host's preferred mechanism
 * (uidpool or search). The uidpool mechanism uses a user-configured entry in
 * the LDAP server to store the last used uidNumber. This mechanism simply fetches
 * and increments and returns that value. The search mechanism is more complicated
 * and slow. It searches all entries that have uidNumber set, finds the smalles and
 * "fills in the gaps" by incrementing the smallest uidNumber until an unused value
 * is found. Both mechanisms do NOT prevent race conditions or toe-stomping, so
 * care must be taken when actually creating the entry to check that the uidNumber
 * returned here has not been used in the mean time. Note that the two different
 * mechanisms may (will!) return different values as they use different algorithms
 * to arrive at their result. Do not be alarmed if (when!) this is the case.
 *
 * Also note that both algorithms are susceptible to a race condition. If two admins
 * are adding users simultaneously, the users may get identical uidNumbers with this
 * function.
 *
 * See config.php.example for more notes on the two auto uidNumber mechanisms.
 *
 * @param object $ldapserver The LDAP Server Object of interest.
 * @return int
 *
 * @todo Must turn off auto_uid|gid in template if config is disabled.
 */
function get_next_number(&$ldapserver,$startbase='',$type='uid',$increment=false,$filter=false) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver->server_id,$startbase,$type,$increment,$filter);

	if (! $_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','enable'))
		return false;

	# Based on the configured mechanism, go get the next available uidNumber!
	$mechanism = $_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','mechanism');

	switch ($mechanism) {

		case 'search' :
			if (! $startbase) {
				$base_dn = $_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','search_base');

				if (is_null($base_dn))
					error(sprintf(_('You specified the "auto_uid_number_mechanism" as "search" in your
						configuration for server <b>%s</b>, but you did not specify the
						"auto_uid_number_search_base". Please specify it before proceeding.'),$ldapserver->name),'error','index.php');

			} else {
				$base_dn = $startbase;
			}

			if (! $ldapserver->dnExists($base_dn))
				error(sprintf(_('Your phpLDAPadmin configuration specifies an invalid auto_uid_search_base for server %s'),
					$ldapserver->name),'error','index.php');

			$filter = '(|(uidNumber=*)(gidNumber=*))';
			$results = array();

			# Check see and use our alternate uid_dn and password if we have it.
			$con = $ldapserver->connect(false,'auto_search',false,true,
				$_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','dn'),
				$_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','pass'));

			if (! $con)
				error(sprintf(_('Unable to bind to <b>%s</b> with your with auto_uid credentials. Please check your configuration file.'),$ldapserver->name),
					'error','index.php');

			$search = $ldapserver->search($con,$base_dn,$filter,array('uidNumber','gidNumber'),'sub',false,$_SESSION[APPCONFIG]->GetValue('deref','search'));

			if (! is_array($search))
				error(_('Untrapped error.'),'error','index.php');

			foreach ($search as $dn => $attrs) {
				$attrs = array_change_key_case($attrs);
				$entry = array();

				switch ($type) {
					case 'uid' :
						if (isset($attrs['uidnumber'])) {
							$entry['dn'] = $attrs['dn'];
							$entry['uniqnumber'] = $attrs['uidnumber'];
							$results[] = $entry;
						}
						break;

					case 'gid' :
						if (isset($attrs['gidnumber'])) {
							$entry['dn'] = $attrs['dn'];
							$entry['uniqnumber'] = $attrs['gidnumber'];
							$results[] = $entry;
						}
						break;
					default :
						error(sprintf('Unknown type [%s] in search',$type),'error','index.php');
				}
			}

			# construct a list of used numbers
			$autonum = array();
			foreach ($results as $result)
				if (isset($result['uniqnumber']))
					$autonum[] = $result['uniqnumber'];

			$autonum = array_unique($autonum);
			sort($autonum);

			foreach ($autonum as $uid)
				$uid_hash[$uid] = 1;

			# start with the least existing autoNumber and add 1
			if ($_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','min'))
				$minNumber = $_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','min');
			else
				$minNumber = intval($autonum[0]) + 1;

			# this loop terminates as soon as we encounter the next available minNumber
			while (isset($uid_hash[$minNumber]))
				$minNumber++;

			return $minNumber;

			break;

		case 'uidpool':
			$con = $ldapserver->connect(false,'auto_search',false,true,
				$_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','dn'),
				$_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','pass'));

			if (! $con)
				error(sprintf(_('Unable to bind to <b>%s</b> with your with auto_uid credentials. Please check your configuration file.'),$ldapserver->name),
					'error','index.php');

			# assume that uidpool dn is set in config file if no filter given
			if (empty($filter)) {
				$uidpool_dn = $_SESSION[APPCONFIG]->ldapservers->GetValue($ldapserver->server_id,'auto_number','uidpool_dn');
				$filter = '(objectclass=*)';

			} else {
				$filter = str_replace(array('&amp;',':::'),array('&',','),$filter);
				$dns = $ldapserver->search($con,$startbase,$filter,array('dn'),'sub');

				switch (count($dns)) {
					case '1':
						break;

					case '0':
						error(_('Uidpool dn not found, please change filter parameter'),'error','index.php');

					default:
						error(_('There is more than one dn for uidpool,please change filter parameter'),'error','index.php');
				}

				list ($key,$attrs) = each($dns);
				$attrs = array_change_key_case($attrs);
				$uidpool_dn = $attrs['dn'];
			}

			# Check that the UIDPOOL DN exists.
			if (empty($uidpool_dn) || (! $ldapserver->dnExists($uidpool_dn)))
				error(_('uidpool_dn not found. Please check filter (arg 3) or set up uidpool_dn in config file'),'error','index.php');

			switch ($type) {
				case 'uid' : $attr = 'uidnumber';
					break;
				case 'gid' : $attr = 'gidnumber';
					break;
				default :
					error(_('Unknown uidpool type.'),'error','index.php');
			}

			$number = $ldapserver->search($con,$uidpool_dn,$filter,array($attr),'base');
			$numbers = array_change_key_case($number[$uidpool_dn]);

			if (! isset($numbers[$attr]))
				error(_('A query on the uidpool_dn did return a valid uidNumber.'),'error','index.php');
			else

			if ($increment) {
				$updatedattr = array($attr => $numbers[$attr] + 1);
				$ldapserver->modify($uidpool_dn,$updatedattr);
			}

			return $numbers[$attr];
			break;

		# No other cases allowed. The user has an error in the configuration
		default :
			error(sprintf(_('You specified an invalid value for auto_uid_number_mechanism ("%s") in your configration. Only "uidpool" and "search" are valid.
				Please correct this problem.'),$mechanism),'error','index.php');
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
 * @param int $server_id The ID of the LDAP server housing the DN of interest.
 * @param string $dn The DN of the entry whose icon you wish to fetch.
 *
 * @return string
 */
function get_icon( $ldapserver, $dn ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver->server_id,$dn);

	// fetch and lowercase all the objectClasses in an array
	$object_classes = $ldapserver->getDNAttr($dn,'objectClass',true);
	if (! is_array($object_classes))
		$object_classes = array($object_classes);

	if( $object_classes === null || $object_classes === false || ! is_array( $object_classes ) )
		$object_classes = array();

	foreach( $object_classes as $i => $class )
		$object_classes[$i] = strtolower( $class );

	$rdn = get_rdn( $dn );
	$rdn_parts = explode( '=', $rdn, 2 );
	$rdn_value = isset( $rdn_parts[0] ) ? $rdn_parts[0] : null;
	$rdn_attr = isset( $rdn_parts[1] ) ? $rdn_parts[1] : null;
	unset( $rdn_parts );

	// return icon filename based upon objectClass value
	if( in_array( 'sambaaccount', $object_classes ) &&
		'$' == $rdn{ strlen($rdn) - 1 } )
		return 'nt_machine.png';

	if( in_array( 'sambaaccount', $object_classes ) )
		return 'nt_user.png';

	elseif( in_array( 'person', $object_classes ) ||
		in_array( 'organizationalperson', $object_classes ) ||
		in_array( 'inetorgperson', $object_classes ) ||
		in_array( 'account', $object_classes ) ||
		in_array( 'posixaccount', $object_classes ) )

		return 'user.png';

	elseif( in_array( 'organization', $object_classes ) )
		return 'o.png';

	elseif( in_array( 'organizationalunit', $object_classes ) )
		return 'ou.png';

	elseif( in_array( 'organizationalrole', $object_classes ) )
		return 'uid.png';

	elseif( in_array( 'dcobject', $object_classes ) ||
		in_array( 'domainrelatedobject', $object_classes ) ||
		in_array( 'domain', $object_classes ) ||
		in_array( 'builtindomain', $object_classes ))

		return 'dc.png';

	elseif( in_array( 'alias', $object_classes ) )
		return 'go.png';

	elseif( in_array( 'room', $object_classes ) )
		return 'door.png';

	elseif( in_array( 'device', $object_classes ) )
		return 'device.png';

	elseif( in_array( 'document', $object_classes ) )
		return 'document.png';

	elseif( in_array( 'country', $object_classes ) ) {
		$tmp = pla_explode_dn( $dn );
		$cval = explode( '=', $tmp[0], 2 );
		$cval = isset( $cval[1] ) ? $cval[1] : false;
		if( $cval && false === strpos( $cval, ".." ) &&
			file_exists(realpath(sprintf('%s/countries/%s.png',IMGDIR,strtolower($cval)))))

			return sprintf("countries/%s.png",strtolower($cval));

		else
			return 'country.png';
	}

	elseif( in_array( 'jammvirtualdomain', $object_classes ) )
		return 'mail.png';

	elseif( in_array( 'locality', $object_classes ) )
		return 'locality.png';

	elseif( in_array( 'posixgroup', $object_classes ) ||
		in_array( 'groupofnames', $object_classes ) ||
		in_array( 'group', $object_classes ) )

		return 'ou.png';

	elseif( in_array( 'applicationprocess', $object_classes ) )
		return 'process.png';

	elseif( in_array( 'groupofuniquenames', $object_classes ) )
		return 'uniquegroup.png';

	elseif( in_array( 'iphost', $object_classes ) )
		return 'host.png';

	elseif( in_array( 'nlsproductcontainer', $object_classes ) )
		return 'n.png';

	elseif( in_array( 'ndspkikeymaterial', $object_classes ) )
		return 'lock.png';

	elseif( in_array( 'server', $object_classes ) )
		return 'server-small.png';

	elseif( in_array( 'volume', $object_classes ) )
		return 'hard-drive.png';

	elseif( in_array( 'ndscatcatalog', $object_classes ) )
		return 'catalog.png';

	elseif( in_array( 'resource', $object_classes ) )
		return 'n.png';

	elseif( in_array( 'ldapgroup', $object_classes ) )
		return 'ldap-server.png';

	elseif( in_array( 'ldapserver', $object_classes ) )
		return 'ldap-server.png';

	elseif( in_array( 'nisserver', $object_classes ) )
		return 'ldap-server.png';

	elseif( in_array( 'rbscollection', $object_classes ) )
		return 'ou.png';

	elseif( in_array( 'dfsconfiguration', $object_classes ) )
		return 'nt_machine.png';

	elseif( in_array( 'applicationsettings', $object_classes ) )
		return 'server-settings.png';

	elseif( in_array( 'aspenalias', $object_classes ) )
		return 'mail.png';

	elseif( in_array( 'container', $object_classes ) )
		return 'folder.png';

	elseif( in_array( 'ipnetwork', $object_classes ) )
		return 'network.png';

	elseif( in_array( 'samserver', $object_classes ) )
		return 'server-small.png';

	elseif( in_array( 'lostandfound', $object_classes ) )
		return 'find.png';

	elseif( in_array( 'infrastructureupdate', $object_classes ) )
		return 'server-small.png';

	elseif( in_array( 'filelinktracking', $object_classes ) )
		return 'files.png';

	elseif( in_array( 'automountmap', $object_classes ) ||
		in_array( 'automount', $object_classes ) )

		return 'hard-drive.png';

	elseif( 0 === strpos( $rdn_value, "ipsec" ) ||
		0 == strcasecmp( $rdn_value, "IP Security" ) ||
		0 == strcasecmp( $rdn_value, "MSRADIUSPRIVKEY Secret" ) ||
		0 === strpos( $rdn_value, "BCKUPKEY_" ) )

		return 'lock.png';

	elseif( 0 == strcasecmp( $rdn_value, "MicrosoftDNS" ) )
		return 'dc.png';

	// Oh well, I don't know what it is. Use a generic icon.
	else
		return 'object.png';
}

/**
 * Appends a servers base to a "sub" dn or returns the base.
 *
 * @param string $base    The baseDN to be added if the DN is relative
 * @param string $sub_dn  The DN to be made absolute
 * @return string|null    Returns null if both base is null and sub_dn is null or empty
 */
function expand_dn_with_base( $base,$sub_dn ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$base,$sub_dn);

	$empty_str = ( is_null($sub_dn) || ( ( $len = strlen( trim( $sub_dn ) ) ) == 0 ) );

	if ( $empty_str ) {
		return $base;

	} elseif ( $sub_dn[$len - 1] != ',' ) {
		// If we have a string which doesn't need a base
		return $sub_dn;
	} else {
		return $sub_dn . $base;
	}
}

/**
 * Reads the query, checks all values and sets defaults.
 *
 * @param int $query_id The ID of the predefined query.
 * @return array The fixed query or null on error
 */
function get_cleaned_up_predefined_search($query_id) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$query_id);

	if (! isset($_SESSION[APPCONFIG]->queries[$query_id]))
		return null;

	$query = $_SESSION[APPCONFIG]->queries[$query_id];

	$base = (isset($query['base'])) ? $query['base'] : null;

	if (isset($query['filter']) && trim($query['filter']))
		$filter = $query['filter'];
	else
		$filter = 'objectclass=*';

	$scope = isset($query['scope']) && (in_array($query['scope'],array('base','sub','one'))) ?
		$query['scope'] : 'sub';

	if (isset($query['attributes']) && trim($query['filter']))
		$attrib = $query['attributes'];
	else
		$attrib = 'dn, cn, sn, objectClass';

	return array('base'=>$base,'filter'=>$filter,'scope'=>$scope,'attributes'=>$attrib);
}

/**
 * Used to generate a random salt for crypt-style passwords. Salt strings are used
 * to make pre-built hash cracking dictionaries difficult to use as the hash algorithm uses
 * not only the user's password but also a randomly generated string. The string is
 * stored as the first N characters of the hash for reference of hashing algorithms later.
 *
 * --- added 20021125 by bayu irawan <bayuir@divnet.telkom.co.id> ---
 * --- ammended 20030625 by S C Rigler <srigler@houston.rr.com> ---
 *
 * @param int $length The length of the salt string to generate.
 * @return string The generated salt string.
 */
function random_salt( $length ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$length);

	$possible = '0123456789'.
		'abcdefghijklmnopqrstuvwxyz'.
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
		'./';
	$str = "";
	mt_srand((double)microtime() * 1000000);

	while( strlen( $str ) < $length )
		$str .= substr( $possible, ( rand() % strlen( $possible ) ), 1 );

	/**
	 * Commented out following line because of problem
	 * with crypt function in update.php
	 * --- 20030625 by S C Rigler <srigler@houston.rr.com> ---
	 */
	//$str = "\$1\$".$str."\$";
	return $str;
}

/**
 * Given a DN string, this returns the 'RDN' portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'cn=Manager' (it is really the exact opposite of get_container()).
 *
 * @param string $dn The DN whose RDN to return.
 * @param bool $include_attrs If true, include attributes in the RDN string.
 *               See http://php.net/ldap_explode_dn for details
 *
 * @return string The RDN
 * @see get_container
 */
function get_rdn($dn,$include_attrs=0,$decode=false) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,$dn,$include_attrs,$decode);

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
 * Given a DN string, this returns the parent container portion of the string.
 * For example. given 'cn=Manager,dc=example,dc=com', this function returns
 * 'dc=example,dc=com'.
 *
 * @param string $dn The DN whose container string to return.
 * @param boolean $lower The DN returned will be in lower case.
 *
 * @return string The container
 * @see get_rdn
 */
function get_container($dn,$lower=true) {
	$parts = pla_explode_dn($dn);

	if (count($parts) <= 1)
		$container = null;

	else {
		$container = $parts[1];
		for ($i=2;$i<count($parts);$i++)
			$container .= ',' . $parts[$i];
	}

	if ($lower)
		$container = strtolower($container);

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$dn,$lower,$container);

	return $container;
}

/**
 * Given an LDAP error number, returns a verbose description of the error.
 * This function parses ldap_error_codes.txt and looks up the specified
 * ldap error number, and returns the verbose message defined in that file.
 *
 * @param string $err_no The hex error number (ie, "0x42") of the LDAP error of interest.
 * @return array An associative array contianing the error title and description like so:
 *   <code>
 *  Array
 *    (
 *       [title] => "Invalid Credentials"
 *       [description] => "An invalid username and/or password was supplied to the LDAP server."
 *    )
 *   </code>
 */
function pla_verbose_error( $err_no ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$err_no);

	static $err_codes;

	if( count($err_codes) <= 0 ) {
		$err_codes_file = LIBDIR.'ldap_error_codes.txt';

		if (! file_exists($err_codes_file) || ! is_readable($err_codes_file) || ! ($f = fopen($err_codes_file,'r')))
			return false;

		$contents = fread( $f, filesize( $err_codes_file ) );
		fclose( $f );
		$entries = array();
		preg_match_all( "/0x[A-Fa-f0-9][A-Za-z0-9]\s+[0-9A-Za-z_]+\s+\"[^\"]*\"\n/",
				$contents, $entries );
		$err_codes = array();
		foreach( $entries[0] as $e ) {
			$entry = array();
			preg_match( "/(0x[A-Za-z0-9][A-Za-z0-9])\s+([0-9A-Za-z_]+)\s+\"([^\"]*)\"/", $e, $entry );
			$hex_code = isset( $entry[1] ) ? $entry[1] : null;
			$title    = isset( $entry[2] ) ? $entry[2] : null;
			$desc     = isset( $entry[3] ) ? $entry[3] : null;
			$desc     = preg_replace( "/\s+/", " ", $desc );
			$err_codes[ "$hex_code" ] = array( 'title' => $title, 'desc' => $desc );
		}
	}

	if( isset( $err_codes[ $err_no ] ) )
		return $err_codes[ $err_no ];
	else
		return array( 'title' => null, 'desc' => null );
}

// @todo: describe this function
function support_oid_to_text($oid_id) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$oid_id);

	static $oid;

	if( count($oid) <= 0 ) {
		$oid_codes_file = LIBDIR.'ldap_supported_oids.txt';

		if(! file_exists($oid_codes_file) || ! is_readable($oid_codes_file) || ! ($f = fopen($oid_codes_file,'r')))
			return false;

		$contents = fread( $f, filesize( $oid_codes_file ) );
		fclose( $f );
		$entries = array();
		preg_match_all( "/[0-9]\..+\s+\"[^\"]*\"\n/", $contents, $entries );
		$err_codes = array();
		foreach( $entries[0] as $e ) {
			$entry = array();
			preg_match( "/([0-9]\.([0-9]+\.)*[0-9]+)(\s+\"([^\"]*)\")?(\s+\"([^\"]*)\")?(\s+\"([^\"]*)\")?/", $e, $entry );
			$oid_id_a = isset( $entry[1] ) ? $entry[1] : null;

			if ($oid_id_a) {
				$oid[$oid_id_a]['title'] = isset( $entry[4] ) ? $entry[4] : null;
				$oid[$oid_id_a]['ref'] = isset( $entry[6] ) ? $entry[6] : null;
				$desc = isset( $entry[8] ) ? $entry[8] : null;
				$oid[$oid_id_a]['desc'] = preg_replace( "/\s+/", " ", $desc );
			}
		}
	}

	if( isset( $oid[ $oid_id ] ) )
		return $oid[ $oid_id ];
	else
		return null;
}

/**
 * Print an LDAP error message
 */
function ldap_error_msg($msg,$errnum) {
	$body = '<table border=0>';

	$errnum = ('0x'.str_pad(dechex($errnum),2,0,STR_PAD_LEFT));
	$verbose_error = pla_verbose_error($errnum);

	$body .= sprintf('<tr><td><b>%s</b>:</td><td>%s</td></tr>',_('LDAP said'),htmlspecialchars($msg));

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
 *    draw_jpeg_photos( 0, "cn=Bob,ou=People,dc=example,dc=com", "jpegPhoto" true, false, "border: 1px; width: 150px" );
 *    draw_jpeg_photos( 1, "cn=Fred,ou=People,dc=example,dc=com" );
 *  </code>
 *
 * @param int $server_id The ID of the server of interest.
 * @param string $dn The DN of the entry that contains the jpeg attribute you want to draw.
 * @param string $attr_name The name of the attribute containing the jpeg data (usually 'jpegPhoto').
 * @param bool $draw_delete_buttons If true, draws a button beneath the image titled 'Delete' allowing the user
 *                  to delete the jpeg attribute by calling JavaScript function deleteAttribute() provided
 *                  in the default modification template.
 * @param bool $draw_bytes_and_size If true, draw text below the image indicating the byte size and dimensions.
 * @param string $table_html_attrs Specifies optional CSS style attributes for the table tag.
 *
 * @return void
 */
function draw_jpeg_photos($ldapserver,$dn,$attr_name='jpegPhoto',$draw_delete_buttons=false,
	$draw_bytes_and_size=true,$table_html_attrs='align="left"',$img_html_attrs='') {

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver->server_id,$dn,$attr_name,$draw_delete_buttons,
			$draw_bytes_and_size,$table_html_attrs,$img_html_attrs);

	$fixed_width = false;
	$fixed_height = false;
	if (eregi(' width',$img_html_attrs) || eregi('^width',$img_html_attrs))
		$fixed_width = true;
	if (eregi(' height=',$img_html_attrs) || eregi('^height=',$img_html_attrs))
		$fixed_height = true;

	if (isset($table_html_attrs) && trim($table_html_attrs) )
		printf('<table %s><tr><td><center>',$table_html_attrs);

	$jpeg_data = $ldapserver->search(null,$dn,'objectClass=*',array($attr_name),'base');
	$jpeg_data = array_pop($jpeg_data);
	if (! $jpeg_data) {
		printf(_('Could not fetch jpeg data from LDAP server for attribute %s.'),htmlspecialchars($attr_name));
		return;
	}

	$jpeg_temp_dir = realpath($_SESSION[APPCONFIG]->GetValue('jpeg','tmpdir').'/');
	if (! is_writable($jpeg_temp_dir))
		error(_('Please set $jpeg_temp_dir to a writable directory in the phpLDAPadmin config.php'),'error','index.php');

	if (! is_array($jpeg_data[$attr_name]))
		$jpeg_data[$attr_name] = array($jpeg_data[$attr_name]);

	foreach ($jpeg_data[$attr_name] as $jpeg) {
		$jpeg_filename = tempnam($jpeg_temp_dir.'/','pla');
		$outjpeg = @fopen($jpeg_filename,'wb');
		if (! $outjpeg)
			error(sprintf(_('Could not write to the $jpeg_temp_dir directory %s. Please verify that your web server can write files there.'),$jpeg_temp_dir),
				'error','index.php');
		fwrite($outjpeg,$jpeg);
		fclose ($outjpeg);

		$jpeg_data_size = filesize($jpeg_filename);
		if ($jpeg_data_size < 6 && $draw_delete_buttons) {
			echo _('jpegPhoto contains errors<br />');
			printf('<a href="javascript:deleteAttribute(\'%s\');" style="color:red; font-size: 75%%">%s</a>',$attr_name,_('Delete Photo'));
			continue;
		}

		if (function_exists('getimagesize')) {
			$jpeg_dimensions = @getimagesize($jpeg_filename);
			$width = $jpeg_dimensions[0];
			$height = $jpeg_dimensions[1];

		} else {
			$width = 0;
			$height = 0;
		}

		if ($width > 300) {
			$scale_factor = 300 / $width;
			$img_width = 300;
			$img_height = intval($height * $scale_factor);

		} else {
			$img_width = $width;
			$img_height = $height;
		}

		printf('<img %s%s%s src="view_jpeg_photo.php?file=%s" alt="Photo" /><br />',
			($fixed_width ? '' : 'width="'.$img_width.'" '),
			($fixed_height ? '' : 'height="'.$img_height.'"'),
			($img_html_attrs ? $img_html_attrs : ''),basename($jpeg_filename));

		if ($draw_bytes_and_size)
			printf('<small>%s %s. %s x %s pixels.<br /></small>',number_format($jpeg_data_size),_('bytes'),$width,$height);

		if ($draw_delete_buttons)
			# <!-- JavaScript function deleteJpegPhoto() to be defined later by calling script -->
			printf('<a href="javascript:deleteAttribute(\'%s\');" style="color:red; font-size: 75%%">%s</a>',
				$attr_name,_('Delete photo'));
	}

	if (isset($table_html_attrs) && trim($table_html_attrs))
		echo '</center></td></tr></table>';

	# Delete old jpeg files.
	$jpegtmp_wildcard = "/^pla/";
	$handle = @opendir($jpeg_temp_dir);
	if ($handle !== false) {
		while (($file = readdir($handle)) != false) {
			if (preg_match($jpegtmp_wildcard,$file)) {
				$file = "$jpeg_temp_dir/$file";
				if ((time() - filemtime($file)) > $_SESSION[APPCONFIG]->GetValue('jpeg','tmp_keep_time'))
					@unlink($file);
			}
		}
		closedir($handle);
	} else {
		error(sprintf('failed to open dir %s : permission denied',$jpeg_temp_dir),'error');
	}
}

/**
 * Hashes a password and returns the hash based on the specified enc_type.
 *
 * @param string $password_clear The password to hash in clear text.
 * @param string $enc_type Standard LDAP encryption type which must be one of
 *        crypt, ext_des, md5crypt, blowfish, md5, sha, smd5, ssha, or clear.
 * @return string The hashed password.
 */
function password_hash( $password_clear, $enc_type ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$password_clear,$enc_type);

	$enc_type = strtolower( $enc_type );

	switch( $enc_type ) {
		case 'crypt':
			if ($_SESSION[APPCONFIG]->GetValue('password', 'no_random_crypt_salt'))
				$new_value = '{CRYPT}' . crypt($password_clear,substr($password_clear,0,2));
			else
				$new_value = '{CRYPT}' . crypt($password_clear,random_salt(2));
			break;

		case 'ext_des':
			// extended des crypt. see OpenBSD crypt man page.
			if ( ! defined( 'CRYPT_EXT_DES' ) || CRYPT_EXT_DES == 0 )
				error(_('Your system crypt library does not support extended DES encryption.'),'error','index.php');

			$new_value = '{CRYPT}' . crypt( $password_clear, '_' . random_salt(8) );
			break;

		case 'md5crypt':
			if( ! defined( 'CRYPT_MD5' ) || CRYPT_MD5 == 0 )
				error(_('Your system crypt library does not support md5crypt encryption.'),'error','index.php');

			$new_value = '{CRYPT}' . crypt( $password_clear , '$1$' . random_salt(9) );
			break;

		case 'blowfish':
			if( ! defined( 'CRYPT_BLOWFISH' ) || CRYPT_BLOWFISH == 0 )
				error(_('Your system crypt library does not support blowfish encryption.'),'error','index.php');

			// hardcoded to second blowfish version and set number of rounds
			$new_value = '{CRYPT}' . crypt( $password_clear , '$2a$12$' . random_salt(13) );
			break;

		case 'md5':
			$new_value = '{MD5}' . base64_encode( pack( 'H*' , md5( $password_clear) ) );
			break;

		case 'sha':
			if( function_exists('sha1') ) {
				// use php 4.3.0+ sha1 function, if it is available.
				$new_value = '{SHA}' . base64_encode( pack( 'H*' , sha1( $password_clear) ) );

			} elseif( function_exists( 'mhash' ) ) {
				$new_value = '{SHA}' . base64_encode( mhash( MHASH_SHA1, $password_clear) );

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
			}
			break;

		case 'ssha':
			if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
				mt_srand( (double) microtime() * 1000000 );
				$salt = mhash_keygen_s2k( MHASH_SHA1, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
				$new_value = "{SSHA}".base64_encode( mhash( MHASH_SHA1, $password_clear.$salt ).$salt );

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
			}
			break;

		case 'smd5':
			if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
				mt_srand( (double) microtime() * 1000000 );
				$salt = mhash_keygen_s2k( MHASH_MD5, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
				$new_value = "{SMD5}".base64_encode( mhash( MHASH_MD5, $password_clear.$salt ).$salt );

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
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
 * @param String $hash The hash.
 * @param String $clear The password in clear text to test.
 * @return Boolean True if the clear password matches the hash, and false otherwise.
 */
function password_check( $cryptedpassword, $plainpassword ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$cryptedpassword,$plainpassword);

	if( preg_match( "/{([^}]+)}(.*)/", $cryptedpassword, $cypher ) ) {
		$cryptedpassword = $cypher[2];
		$_cypher = strtolower($cypher[1]);

	} else {
		$_cypher = NULL;
	}

	switch( $_cypher ) {
		// SSHA crypted passwords
		case 'ssha':
			// check php mhash support before using it
			if( function_exists( 'mhash' ) ) {
				$hash = base64_decode($cryptedpassword);
				# OpenLDAP uses a 4 byte salt, SunDS uses an 8 byte salt - both from char 20.
				$salt = substr($hash,20);
				$new_hash = base64_encode( mhash( MHASH_SHA1, $plainpassword.$salt).$salt );

				if( strcmp( $cryptedpassword, $new_hash ) == 0 )
					return true;
				else
					return false;

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
			}
			break;

		// Salted MD5
		case 'smd5':
			// check php mhash support before using it
			if( function_exists( 'mhash' ) ) {
				$hash = base64_decode($cryptedpassword);
				$salt = substr($hash, -4);
				$new_hash = base64_encode( mhash( MHASH_MD5, $plainpassword.$salt).$salt );

				if( strcmp( $cryptedpassword, $new_hash ) == 0)
					return true;
				else
					return false;

			} else {
				error(_('Your PHP install does not have the mhash() function. Cannot do SHA hashes.'),'error','index.php');
			}
			break;

		// SHA crypted passwords
		case 'sha':
			if( strcasecmp( password_hash($plainpassword,'sha' ), "{SHA}".$cryptedpassword ) == 0 )
				return true;
			else
				return false;
			break;

		// MD5 crypted passwords
		case 'md5':
			if( strcasecmp( password_hash( $plainpassword,'md5' ), "{MD5}".$cryptedpassword ) == 0 )
				return true;
			else
				return false;
			break;

		// Crypt passwords
		case 'crypt':
			// Check if it's blowfish crypt
			if( preg_match("/^\\$2+/",$cryptedpassword ) ) {

				// make sure that web server supports blowfish crypt
				if( ! defined( 'CRYPT_BLOWFISH' ) || CRYPT_BLOWFISH == 0 )
					error(_('Your system crypt library does not support blowfish encryption.'),'error','index.php');

				list(,$version,$rounds,$salt_hash) = explode('$',$cryptedpassword);

				if( crypt( $plainpassword, '$'. $version . '$' . $rounds . '$' .$salt_hash ) == $cryptedpassword )
					return true;
				else
					return false;
			}

			// Check if it's an crypted md5
			elseif( strstr( $cryptedpassword, '$1$' ) ) {

				// make sure that web server supports md5 crypt
				if( ! defined( 'CRYPT_MD5' ) || CRYPT_MD5 == 0 )
					error(_('Your system crypt library does not support md5crypt encryption.'),'error','index.php');

				list(,$type,$salt,$hash) = explode('$',$cryptedpassword);

				if( crypt( $plainpassword, '$1$' .$salt ) == $cryptedpassword )
					return true;
				else
					return false;
			}

			// Check if it's extended des crypt
			elseif (strstr( $cryptedpassword, '_' ) ) {

				// make sure that web server supports ext_des
				if ( ! defined( 'CRYPT_EXT_DES' ) || CRYPT_EXT_DES == 0 )
					error(_('Your system crypt library does not support extended DES encryption.'),'error','index.php');

				if( crypt($plainpassword, $cryptedpassword ) == $cryptedpassword )
					return true;
				else
					return false;
			}

			// Password is plain crypt
			else {

				if( crypt($plainpassword, $cryptedpassword ) == $cryptedpassword )
					return true;
				else
					return false;
			}
			break;

		// No crypt is given assume plaintext passwords are used
		default:
			if( $plainpassword == $cryptedpassword )
				return true;
			else
				return false;

			break;
	}
}

/**
 * Detects password encryption type
 *
 * Returns crypto string listed in braces. If it is 'crypt' password,
 * returns crypto detected in password hash. Function should detect
 * md5crypt, blowfish and extended DES crypt. If function fails to detect
 * encryption type, it returns NULL.
 * @param string hashed password
 * @return string
 */
function get_enc_type( $user_password ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$user_password);

	/* Capture the stuff in the { } to determine if this is crypt, md5, etc. */
	$enc_type = null;

	if( preg_match( "/{([^}]+)}/", $user_password, $enc_type) )
		$enc_type = strtolower( $enc_type[1] );
	else
		return null;

	/* handle crypt types */
	if( strcasecmp( $enc_type, 'crypt') == 0 ) {

		if( preg_match( "/{[^}]+}\\$1\\$+/", $user_password) ) {
			$enc_type = "md5crypt";

		} elseif ( preg_match( "/{[^}]+}\\$2+/", $user_password) ) {
			$enc_type = "blowfish";

		} elseif ( preg_match( "/{[^}]+}_+/", $user_password) ) {
			$enc_type = "ext_des";
		}

		/*
		 * No need to check for standard crypt,
		 * because enc_type is already equal to 'crypt'.
		 */
	}
	return $enc_type;
}

/**
 * Gets the default enc_type configured in config.php for the server indicated by $server_id;
 * @param int $server_id The ID of the server of interest.
 * @return String The enc_type, like 'sha', 'md5', 'ssha', 'md5crypt', for example.
 */
function get_default_hash($server_id) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$server_id);

	return $_SESSION[APPCONFIG]->ldapservers->GetValue($server_id,'appearance','password_hash');
}

/**
 * Draws an HTML browse button which, when clicked, pops up a DN chooser dialog.
 * @param string $form_element The name of the form element to which this chooser
 *         dialog will publish the user's choice. The form element must be a member
 *         of a form with the "name" or "id" attribute set in the form tag, and the element
 *         must also define "name" or "id" for JavaScript to uniquely identify it.
 *         Example $form_element values may include "creation_form.container" or
 *         "edit_form.member_uid". See /templates/modification/default.php for example usage.
 * @param bool $include_choose_text (optional) If true, the function draws the localized text "choose" to the right of the button.
 */
function draw_chooser_link( $form_element, $include_choose_text=true, $rdn="none" ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$form_element,$include_choose_text,$rdn);

	if ($rdn == 'none') {
		$href = "javascript:dnChooserPopup('$form_element','');";

	} else {
		$href = "javascript:dnChooserPopup('$form_element','$rdn');";
	}

	$title = _('Click to popup a dialog to select an entry (DN) graphically');

	printf('<a href="%s" title="%s"><img class="chooser" src="%s/find.png" alt="Find" /></a>',$href,$title,IMGDIR);
	if ($include_choose_text)
		printf('<span class="x-small"><a href="%s" title="%s">%s</a></span>',$href,$title,_('browse'));
}

/**
 * Explode a DN into an array of its RDN parts.
 * @param string $dn The DN to explode.
 * @param int $with_attriutes (optional) Whether to include attribute names (see http://php.net/ldap_explode_dn for details)
 *
 * @return array An array of RDN parts of this format:
 * <code>
 *  Array
 *    (
 *       [0] => uid=ppratt
 *       [1] => ou=People
 *       [2] => dc=example
 *       [3] => dc=com
 *    )
 * </code>
 *
 * NOTE: When a multivalue RDN is passed to ldap_explode_dn, the results returns with 'value + value';
 */
function pla_explode_dn($dn,$with_attributes=0) {
	global $CACHE;

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,$dn,$with_attributes);

	if (isset($CACHE['explode'][$dn][$with_attributes])) {
		if (DEBUG_ENABLED)
			debug_log('Return CACHED result (%s) for (%s)',1,__FILE__,__LINE__,__METHOD__,
				$CACHE['explode'][$dn][$with_attributes],$dn);

		return $CACHE['explode'][$dn][$with_attributes];
	}

	$dn = addcslashes($dn,'<>+";');

	# split the dn
	$result[0] = ldap_explode_dn(dn_escape($dn),0);
	$result[1] = ldap_explode_dn(dn_escape($dn),1);
	if (! $result[$with_attributes]) {
		if (DEBUG_ENABLED)
			debug_log('Returning NULL - NO result.',1,__FILE__,__LINE__,__METHOD__);

		return array();
	}

	# Remove our count value that ldap_explode_dn returns us.
	unset($result[0]['count']);
	unset($result[1]['count']);

	# Record the forward and reverse entries in the cache.
	foreach ($result as $key => $value) {
		# translate hex code into ascii for display
		$result[$key] = dn_unescape($value);

		$CACHE['explode'][join(',',$result[0])][$key] = $result[$key];
		$CACHE['explode'][join(',',array_reverse($result[0]))][$key] = array_reverse($result[$key]);
	}

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$dn,$with_attributes,$result[$with_attributes]);

	return $result[$with_attributes];
}

/**
 * Parse a DN and escape any special characters
 */
function dn_escape($dn) {
	$olddn = $dn;

	# Check if the RDN has a comma and escape it.
	while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/',$dn))
		$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,',$dn);

	$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3',$dn);

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$olddn,$dn);

	return $dn;
}

/**
 * Parse a DN and unescape any special characters
 */
function dn_unescape($dn) {
	if (is_array($dn)) {
		$a = array();
		foreach ($dn as $key => $rdn) {
			$a[$key] = preg_replace('/\\\([0-9A-Fa-f]{2})/e',"''.chr(hexdec('\\1')).''",$rdn);
		}
		return $a;
	} else {
		return preg_replace('/\\\([0-9A-Fa-f]{2})/e',"''.chr(hexdec('\\1')).''",$dn);
	}
}

/**
 * Fetches the URL for the specified item. This is a convenience function for
 * fetching project HREFs (like bugs)
 *
 * @param string $type One of "open_bugs", "add_bug", "donate", or "add_rfe"
 *            (rfe = request for enhancement)
 * @return string The URL to the requested item.
 */
function get_href($type,$extra_info='') {
	$sf = 'https://sourceforge.net';
	$pla = 'http://phpldapadmin.sourceforge.net';
	$group_id = '61828';
	$bug_atid = '498546';
	$rfe_atid = '498549';
	$forum_id = '34809';

	switch($type) {
		case 'add_bug':
			return sprintf('%s/tracker/?func=add&amp;group_id=%s&amp;atid=%s',$sf,$group_id,$bug_atid);
		case 'add_rfe':
			return sprintf('%s/tracker/?func=add&amp;group_id=%s&amp;atid=%s',$sf,$group_id,$rfe_atid);
		case 'credits':
			return sprintf('%s/Credits',$pla);
		case 'documentation':
			return sprintf('%s/Documentation',$pla);
		case 'forum':
			return sprintf('%s/mailarchive/forum.php?forum_id=%s',$sf,$forum_id);
		case 'open_bugs':
			return sprintf('%s/tracker/?group_id=%s&amp;atid=%s',$sf,$group_id,$bug_atid);
		case 'search_bug':
			return sprintf('%s/tracker/?func=search&amp;group_id=%s&amp;atid=%s&amp;set=custom&amp;_status=100&amp;_group=100&amp;order=summary%s',$sf,$group_id,$bug_atid,$extra_info);
		case 'donate':
			return sprintf('%s/donate/index.php?group_id=%s',$sf,$group_id);
		case 'help':
			return sprintf('help.php');
		default:
			return null;
	}
}

/**
 * Returns the current time as a double (including micro-seconds).
 *
 * @return double The current time in seconds since the beginning of the UNIX epoch (Midnight Jan. 1, 1970)
 */
function utime () {
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
 * @param array $array The associate array to convert whose form is such that the keys are the
 *          names of the variables and the values are said variables' values like this:
 *          <code>
 *             Array
 *                (
 *                   [server_id] = 0,
 *                   [dn] = "dc=example,dc=com",
 *                   [attr] = "sn"
 *                 )
 *          </code>
 *          This will produce a string like this: "server_id=0&dn=dc=example,dc=com&attr=sn"
 * @param array $exclude_vars (optional) An array of variables to exclude in the resulting string
 * @param bool $url_encode_ampersands (optional) By default, this function encodes all ampersand-separators
 *             as &amp; but callers may dislabe this by specifying false here. For example, URLs on HTML
 *             pages should encode the ampersands but URLs in header( "Location: http://example.com" ) should
 *             not be encoded.
 * @return string The string created from the array.
 */
function array_to_query_string( $array, $exclude_vars=array(), $url_encode_ampersands=true ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			count($array),count($exclude_vars),$url_encode_ampersands);

	if (! is_array($array))
		return '';
	if (! $array)
		return '';

	$str = '';
	$i = 0;
	foreach($array as $name => $val) {
		if(! in_array($name, $exclude_vars)) {
			if (is_array($val)) {
				foreach($val as $v) {
					if ($i > 0)
						if($url_encode_ampersands) $str .= '&amp;';
						else $str .= '&';
					$str .= rawurlencode($name) . '[]=' . rawurlencode($v);
					$i++;
				}
			} else {
				if ($i > 0)
					if($url_encode_ampersands) $str .= '&amp;';
					else $str .= '&';
				$str .= rawurlencode($name) . '=' . rawurlencode($val);
				$i++;
			}
		}
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
 * @param string $dn The DN to reverse
 * @return string The reversed DN
 *
 * @see pla_compare_dns
 * @see pla_explode_dns
 */
function pla_reverse_dn($dn) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

	return (join(',',array_reverse(pla_explode_dn($dn))));
}

/**
 *
 */
function sortAttrs($a,$b) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,$a,$b);

	$attrs_display_order = isset($_SESSION[APPCONFIG]->attrs_display_order) ? $_SESSION[APPCONFIG]->attrs_display_order : array();

	if ($a == $b)
		return 0;

	# Check if $a is in $attrs_display_order, get its key
	$a_key = array_search($a,$attrs_display_order);

	# If not, check if its friendly name is $attrs_display_order, get its key
	# If not, assign one greater than number of elements.
	if ( $a_key === false ) {
		if ($_SESSION[APPCONFIG]->haveFriendlyName($a)) {
			$a_key = array_search($_SESSION[APPCONFIG]->getFriendlyName($a),$attrs_display_order);
			if ($a_key == '')
				$a_key = count($attrs_display_order)+1;

		} else {
			$a_key = count($attrs_display_order)+1;
		}
	}

	$b_key = array_search($b,$attrs_display_order);
	if ($b_key === false) {
		if ($_SESSION[APPCONFIG]->haveFriendlyName($b)) {
			$b_key = array_search($_SESSION[APPCONFIG]->getFriendlyName($b),$attrs_display_order);
			if ($b_key == '')
				$b_key = count($attrs_display_order)+1;

		} else {
			$b_key = count($attrs_display_order)+1;
		}
	}

	# Case where neither $a, nor $b are in $attrs_display_order, $a_key = $b_key = one greater than num elements.
	# So we sort them alphabetically
	if ($a_key === $b_key) {
		$a = strtolower($_SESSION[APPCONFIG]->getFriendlyName($a));
		$b = strtolower($_SESSION[APPCONFIG]->getFriendlyName($b));
		return strcmp($a,$b);
	}

	# Case where at least one attribute or its friendly name is in $attrs_display_order
	# return -1 if $a before $b in $attrs_display_order
	return ($a_key < $b_key) ? -1 : 1;
}

/**
 * Compare the names of two Atrribute objects
 */
function attrcmp($a1, $a2) {
	return strcmp(strtolower($a1->getFriendlyName()), strtolower($a2->getFriendlyName()));
}

/**
 * Reads an array and returns the array values back in lower case
 * @param array $array The array to convert the values to lowercase.
 * @returns array Array with values converted to lowercase.
 */
function arrayLower($array) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$array);

	if (! is_array($array))
		return $array;

	$newarray = array();
	foreach ($array as $key => $value) {
		$newarray[$key] = strtolower($value);
	}

	return $newarray;
}

/**
 * Strips all slashes from the specified array in place (pass by ref).
 * @param Array $array The array to strip slashes from, typically one of
 *             $_GET, $_POST, or $_COOKIE.
 */
function array_stripslashes(&$array) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$array);

	if (is_array($array))
		while (list($key) = each($array))
			if (is_array($array[$key]) && $key != $array)
				array_stripslashes($array[$key]);
			else
				$array[$key] = stripslashes($array[$key]);
}

/**
 * Gets the USER_AGENT string from the $_SERVER array, all in lower case in
 * an E_NOTICE safe manner.
 * @return string|false The user agent string as reported by the browser.
 */
function get_user_agent_string() {
	if( isset( $_SERVER['HTTP_USER_AGENT'] ) )
		$return = strtolower( $_SERVER['HTTP_USER_AGENT'] );
	else
		$return = false;

	if (DEBUG_ENABLED)
		debug_log('Entered with (), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Determines whether the browser's operating system is UNIX (or something like UNIX).
 * @return boolean True if the brower's OS is UNIX, false otherwise.
 */
function is_browser_os_unix() {
	$agent_strs = array(
		'sunos','sunos 4','sunos 5',
		'i86',
		'irix','irix 5','irix 6','irix6',
		'hp-ux','09.','10.',
		'aix','aix 1','aix 2','aix 3','aix 4',
		'inux',
		'sco',
		'unix_sv','unix_system_v','ncr','reliant','dec','osf1',
		'dec_alpha','alphaserver','ultrix','alphastation',
		'sinix',
		'freebsd','bsd',
		'x11','vax','openvms'
	);

	$return = string_in_array_value(get_user_agent_string(),$agent_strs);

	if (DEBUG_ENABLED)
		debug_log('Entered with (), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Determines whether the browser's operating system is Windows.
 * @return boolean True if the brower's OS is Windows, false otherwise.
 */
function is_browser_os_windows() {
	$agent_strs = array(
		'win','win95','windows 95',
		'win16','windows 3.1','windows 16-bit','windows','win31','win16','winme',
		'win2k','winxp',
		'win98','windows 98','win9x',
		'winnt','windows nt','win32',
		'32bit'
	);

	$return = string_in_array_value(get_user_agent_string(),$agent_strs);

	if (DEBUG_ENABLED)
		debug_log('Entered with (), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Determines whether the browser's operating system is Macintosh.
 * @return boolean True if the brower's OS is mac, false otherwise.
 */
function is_browser_os_mac() {
	$agent_strs = array(
		'mac','68000','ppc','powerpc'
	);

	$return = string_in_array_value(get_user_agent_string(),$agent_strs);

	if (DEBUG_ENABLED)
		debug_log('Entered with (), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$return);

	return $return;
}

/**
 * Checks if a string exists in an array, ignoring case.
 *
 * @param string $needle What you are looking for
 * @param array $haystack The array that you think it is in.
 * @return bool True if its there, false if its not.
 */
function in_array_ignore_case($needle,$haystack) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,$needle,$haystack);

	if (! is_array($haystack))
		return false;

	if (! is_string($needle))
		return false;

	$return = false;
	foreach ($haystack as $element) {
		if (DEBUG_ENABLED)
			debug_log('Comparing (%s) with (%s)',64,__FILE__,__LINE__,__METHOD__,$needle,$element);

		if (is_string($element) && 0 == strcasecmp($needle,$element))
			$return = true;
	}

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$needle,$haystack,$return);

	return $return;
}

/**
 * Checks if a string exists in part in an array value, ignoring case.
 *
 * @param string $needle What you are looking for
 * @param array $haystack The array that you think it is in.
 * @return bool True if its there, false if its not.
 */
function string_in_array_value( $needle, $haystack ) {
	# Set default return
	$return = false;

	if (! is_string($needle)) return $return;
	if (! is_array($haystack)) return $return;

	foreach ($haystack as $element)
		if (is_string($element) && (strpos($needle,$element) !== false)) {
			$return = true;
			break;
		}

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$needle,$haystack,$return);

	return $return;
}

/**
 * String padding
 *
 * @param string input string
 * @param integer length of the result
 * @param string the filling string
 * @param integer padding mode
 *
 * @return string the padded string
 */
function full_str_pad($input, $pad_length, $pad_string = '', $pad_type = 0) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$input,$pad_length,$pad_string,$pad_type);

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
 * Gets a DN string using the user-configured tree_display_format string to format it.
 */
function draw_formatted_dn($ldapserver,$entry) {
	$dn = $entry->getDn();

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver->server_id,$dn);

	$format = $_SESSION[APPCONFIG]->GetValue('appearance','tree_display_format');
	preg_match_all('/%[a-zA-Z_0-9]+/',$format,$tokens);
	$tokens = $tokens[0];

	if (DEBUG_ENABLED)
		debug_log('The tokens are (%s)',1,__FILE__,__LINE__,__METHOD__,$tokens);

	foreach ($tokens as $token) {
		if (strcasecmp($token,'%dn') == 0)
			$format = str_replace($token,pretty_print_dn($dn),$format);

		elseif (strcasecmp($token,'%rdn') == 0)
			$format = str_replace($token,pretty_print_dn($entry->getRdn()),$format);

		elseif (strcasecmp($token,'%rdnvalue') == 0) {
			$rdn = get_rdn($dn,0,true);
			$rdn_value = explode('=',$rdn,2);
			$rdn_value = $rdn_value[1];
			$format = str_replace($token,$rdn_value,$format);

		} else {
			$attr_name = str_replace('%','',$token);
			$attr_values = $ldapserver->getDNAttr($dn,$attr_name);

			if (is_null($attr_values) || (count($attr_values) <= 0))
				$display = '&lt;'._('none').'&gt;';

			elseif (is_array($attr_values))
				$display = htmlspecialchars(implode(', ',$attr_values));

			else
				$display = htmlspecialchars($attr_values);

			$format = str_replace($token,$display,$format);
		}
	}

	return $format;
}

/**
 * Takes a shadow* attribute and returns the date as an integer.
 * @param $attrs array of Attribute objects
 * @param $attr a shadow attribute name
 */
function shadow_date( $attrs, $attr) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$attrs,$attr);

	$shadowlastchange_attribute = null;
	$shadowmax_attribute = null;
	$shadowdate_attribute = null;
	foreach ($attrs as $attribute) {
		if ($attribute->getName() == 'shadowLastChange' || $attribute->getName() == 'shadowlastchange') {
			$shadowlastchange_attribute = $attribute;
		} elseif ($attribute->getName() == 'shadowMax' || $attribute->getName() == 'shadowmax') {
			$shadowmax_attribute = $attribute;
		} elseif ($attribute->getName() == $attr || $attribute->getName() == $attr) {
			$shadowdate_attribute = $attribute;
		}
		if ($shadowlastchange_attribute && $shadowmax_attribute && $shadowdate_attribute) {
			break;
		}
	}

	$shadowLastChange = $shadowlastchange_attribute ? $shadowlastchange_attribute->getValue(0) : null;
	$shadowMax = $shadowmax_attribute ? $shadowmax_attribute->getValue(0) : null;

	if( 0 == strcasecmp( $attr, 'shadowLastChange' ) && $shadowLastChange)
		$shadow_date = $shadowLastChange;

	elseif ( 0 == strcasecmp( $attr, 'shadowMax' ) && ($shadowMax > 0) && $shadowLastChange )
		$shadow_date = $shadowLastChange+$shadowMax;

	elseif (0 == strcasecmp($attr, 'shadowWarning') && ($shadowdate_attribute->getValue(0) > 0)
	        && $shadowLastChange && $shadowMax && $shadowMax > 0)
		$shadow_date = $shadowLastChange+$shadowMax-$shadowdate_attribute->getValue(0);

	elseif (0 == strcasecmp($attr, 'shadowInactive') && ($shadowdate_attribute->getValue(0) > 0)
	        && $shadowLastChange && $shadowMax && $shadowMax > 0)
		$shadow_date = $shadowLastChange+$shadowMax+$shadowdate_attribute->getValue(0);

	elseif (0 == strcasecmp($attr, 'shadowMin') && ($shadowdate_attribute->getValue(0) > 0)
	        && $shadowLastChange)
		$shadow_date = $shadowLastChange+$shadowdate_attribute->getValue(0);

	elseif (0 == strcasecmp($attr, 'shadowExpire') && ($shadowdate_attribute->getValue(0) > 0))
		$shadow_date = $shadowdate_attribute->getValue(0);

	else // Couldn't interpret the shadow date (could be 0 or -1 or something)
		return false;

	return $shadow_date*24*3600;
}

/**
 * This function will clean up the values use during a search - namely, values that have brackets
 * as that messes up the search filter.
 * @param string $val String that will be used in the search filter.
 * @return string $result String that is ready for the search filter.
 */
function clean_search_vals( $val ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$val);

	# Remove any escaped brackets already.
	$val = preg_replace('/\\\\([\(\)])/','$1',$val);

	# The string might be a proper search filter
	if (preg_match('/^\([&\|!]\(/',$val) || (preg_match('/\(([^\(|\)])*\)/',$val)))
		return $val;

	else
		return preg_replace('/([\(\)])/','\\\\$1',$val);
}

/**
 * Server html select list
 */
function server_select_list ($select_id=null,$only_logged_on=true,$select_name='server_id',$js_script=null) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$select_id,$only_logged_on,$select_name,$js_script);

	$count = 0;
	$server_menu_html = sprintf('<select name="%s" id="%s" %s>',$select_name,$select_name,$js_script);

	foreach ($_SESSION[APPCONFIG]->ldapservers->GetServerList() as $id) {

		$ldapserver = $_SESSION[APPCONFIG]->ldapservers->Instance($id);

		if ($ldapserver->isVisible()) {

			if ($only_logged_on && ! $ldapserver->haveAuthInfo())
				continue;

			$count++;
			$server = $ldapserver;

			$server_menu_html .= sprintf('<option value="%s" %s>%s</option>',
				$ldapserver->server_id,($ldapserver->server_id == $select_id ? 'selected' : ''),$ldapserver->name);
		}
	}

	$server_menu_html .= '</select>';

	if ($count > 1)
		return $server_menu_html;

	elseif ($count)
		return sprintf('%s <input type="hidden" name="%s" value="%s" />',
			$server->name,$select_name,$server->server_id);

	else
		return null;
}

function server_info_list($visible=false) {
	$server_info_list = array();

	if (isset($_SESSION[APPCONFIG])) {
		foreach ($_SESSION[APPCONFIG]->ldapservers->GetServerList() as $id) {
			$ldapserver = $_SESSION[APPCONFIG]->ldapservers->Instance($id);

			if (! $ldapserver->isVisible($id))
				continue;

			$server_info_list[$id]['id'] = $id;
			$server_info_list[$id]['name'] = $ldapserver->name;
			$server_info_list[$id]['base_dns'] = $ldapserver->getBaseDN();
		}
	}

	if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
		debug_log('Entered with (%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$visible,$server_info_list);

	return $server_info_list;
}

function enc_type_select_list($enc_type,$id,$attributename,$i) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,$enc_type,$id,$attributename,$i);

	$html = sprintf('<select id="%s_%s_%s" name="%s[%s][%s]">',
		$id,htmlspecialchars($attributename),$i,
		$id,htmlspecialchars($attributename),$i);

	$html .= '<option>clear</option>';
	foreach (array('crypt','ext_des','md5crypt','blowfish','md5','smd5','sha','ssha') as $option)
		$html .= sprintf('<option%s>%s</option>',($enc_type == $option ? ' selected="true"' : ''),$option);

	$html .= "</select>";

	return $html;
}

// Converts a little-endian hex-number to one, that 'hexdec' can convert
function littleEndian($hex) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$hex);

	$result = '';

	for ($x=strlen($hex)-2; $x >= 0; $x=$x-2)
		$result .= substr($hex,$x,2);

	return $result;
}

function binSIDtoText($binsid) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$binsid);

	$hex_sid=bin2hex($binsid);
	$rev = hexdec(substr($hex_sid,0,2)); // Get revision-part of SID
	$subcount = hexdec(substr($hex_sid,2,2)); // Get count of sub-auth entries
	$auth = hexdec(substr($hex_sid,4,12)); // SECURITY_NT_AUTHORITY

	$result = "$rev-$auth";

	for ($x=0;$x < $subcount; $x++) {
		$subauth[$x] =
		hexdec(littleEndian(substr($hex_sid,16+($x*8),8))); // get all SECURITY_NT_AUTHORITY
		$result .= "-".$subauth[$x];
	}

	return $result;
}

/**
 * Sort a multi dimensional array.
 * @param array $data Multi demension array passed by reference
 * @param string $sortby Comma delimited string of sort keys.
 * @param bool $rev Whether to reverse sort.
 * @returnn array $data Sorted multi demension array.
 */
function masort(&$data,$sortby,$rev=0) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$data,$sortby,$rev);

	# if the array to sort is null or empty
	if (! $data) return;

	static $sort_funcs = array();

	if (empty($sort_funcs[$sortby])) {
		$code = "\$c=0;\n";
		foreach (split(',',$sortby) as $key) {
			$code .= "if (is_object(\$a) || is_object(\$b)) {\n";
			$code .= "	if (\$a->$key != \$b->$key)\n";

			if ($rev)
				$code .= "	return (\$a->$key < \$b->$key ? -1 : 1);\n";
			else
				$code .= "	return (\$a->$key > \$b->$key ? -1 : 1);\n";

			$code .= "} else {\n";

			$code .= "if ((! isset(\$a['$key'])) && (! isset(\$b['$key']))) return 0;\n";
			$code .= "if ((! isset(\$a['$key'])) && isset(\$b['$key'])) return -1;\n";
			$code .= "if (isset(\$a['$key']) && (! isset(\$b['$key']))) return 1;\n";


			$code .= "if (is_numeric(\$a['$key']) && is_numeric(\$b['$key'])) {\n";

			$code .= "	if (\$a['$key'] != \$b['$key'])\n";
			if ($rev)
				$code .= "	return (\$a['$key'] < \$b['$key'] ? -1 : 1);\n";
			else
				$code .= "	return (\$a['$key'] > \$b['$key'] ? -1 : 1);\n";

			$code .= "} else {\n";

			if ($rev)
				$code .= "	if ( (\$c = strcasecmp(\$b['$key'],\$a['$key'])) != 0 ) return \$c;\n";
			else
				$code .= "	if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
			$code .= "}}\n";
		}
		$code .= 'return $c;';
		$sort_func = $sort_funcs[$sortby] = create_function('$a, $b',$code);

	} else {
		$sort_func = $sort_funcs[$sortby];
	}

	$sort_func = $sort_funcs[$sortby];
	uasort($data,$sort_func);
}

/**
 * Query LDAP and return a hash.
 * @param object $ldapserver The LDAPServer object of the server which the user hsa logged in.
 * @param string $base_dn The base DN to use.
 * @param string $filter LDAP Query filter.
 * @param string $key LDAP attribute to use as key.
 * @param array $attrs LDAP attributes to use as values.
 * @param bool $sort Specify false to not sort results by DN
 *                   or true to have the returned array sorted by DN (uses ksort)
 *                   or an array of attribute names to sort by attribute values
 * @return array $results Array of values keyed by $key.
 */
function return_ldap_hash($ldapserver,$base_dn,$filter,$key,$attrs,$sort=true) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver->server_id,$base_dn,$filter,$key,$attrs,$sort);

	$ldapquery = $ldapserver->search(null,$base_dn,$filter,$attrs,'sub',$sort,LDAP_DEREF_NEVER,0);

	$results = array();

	foreach ($ldapquery as $dn => $dnattrs) {
		if (isset($dnattrs[$key])) {
			if (is_array($dnattrs[$key])) {
				foreach ($dnattrs[$key] as $i => $k) {
					foreach ($attrs as $attr) {
						if (isset($dnattrs[$attr])) {
							$v = '';
							if (is_array($dnattrs[$attr]) && isset($dnattrs[$attr][$i]))
								$v = $dnattrs[$attr][$i];
							if (is_string($v) && (strlen($v) > 0))
								$results[$k][$attr] = $v;
						}
					}
				}
			} else {
				foreach ($attrs as $attr) {
					if (isset($dnattrs[$attr]))
						$results[$dnattrs[$key]][$attr] = $dnattrs[$attr];
				}
			}
		}
	}
	return $results;
}

/**
 * This function returns a string automatically generated
 * based on the criteria defined in the array $criteria in config.php
 */
function password_generate() {
	$no_use_similiar = ! $_SESSION[APPCONFIG]->GetValue('password','use_similar');
	$lowercase = $_SESSION[APPCONFIG]->GetValue('password','lowercase');
	$uppercase = $_SESSION[APPCONFIG]->GetValue('password','uppercase');
	$digits = $_SESSION[APPCONFIG]->GetValue('password','numbers');
	$punctuation = $_SESSION[APPCONFIG]->GetValue('password','punctuation');
	$length = $_SESSION[APPCONFIG]->GetValue('password','length');

	$outarray = array();

	if ($no_use_similiar) {
		$raw_lower = "a b c d e f g h k m n p q r s t u v w x y z";
		$raw_numbers = "2 3 4 5 6 7 8 9";
		$raw_punc = "# $ % ^ & * ( ) _ - + = . , [ ] { } :";

	} else {
		$raw_lower = "a b c d e f g h i j k l m n o p q r s t u v w x y z";
		$raw_numbers = "1 2 3 4 5 6 7 8 9 0";
		$raw_punc = "# $ % ^ & * ( ) _ - + = . , [ ] { } : |";
	}

	$llower = explode(" ", $raw_lower);
	shuffle($llower);
	$lupper = explode(" ", strtoupper($raw_lower));
	shuffle($lupper);
	$numbers = explode(" ", $raw_numbers);
	shuffle($numbers);
	$punc = explode(" ", $raw_punc);
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
			$leftover = array_merge($leftover, $llower);
		if ($uppercase > 0)
			$leftover = array_merge($leftover, $lupper);
		if ($digits > 0)
			$leftover = array_merge($leftover, $numbers);
		if ($punctuation > 0)
			$leftover = array_merge($leftover, $punc);

		if (count($leftover) == 0)
			$leftover = array_merge($leftover,$llower,$lupper,$numbers,$punc);

		shuffle($leftover);
		$outarray = array_merge($outarray, a_array_rand($leftover,$length-$num_spec));
	}

	shuffle($outarray);
	$return = implode('', $outarray);

	if (DEBUG_ENABLED)
		debug_log('Entered with (), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$return);
	return $return;
}

/**
 * This function returns an array of $num_req values
 * randomly picked from the $input array
 *
 * @param   array of values
 * @param   integer, number of values in returned array
 * @return string the padded string
 */
function a_array_rand($input,$num_req) {
	if (count($input) == 0)
		return array();

	if ($num_req < 1)
		return array();

	$return = array();
	if ($num_req > count($input)) {
		for($i = 0; $i < $num_req; $i++) {
			$idx = array_rand($input, 1);
			$return[] = $input[$idx];
		}

	} else {
		$idxlist = array_rand($input, $num_req);
		if ($num_req == 1)
			$idxlist = array($idxlist);

		for($i = 0; $i < count($idxlist); $i++)
			$return[] = $input[$idxlist[$i]];
	}

	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s), Returning (%s)',1,__FILE__,__LINE__,__METHOD__,
			$input,$num_req,$return);

	return $return;
}

/**
 * Draws an HTML date selector button which, when clicked, pops up a date selector dialog.
 * @param string $attr The name of the date type attribute
 */
function draw_date_selector_link( $attr ) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$attr);

	$href = "javascript:dateSelector('$attr');";
	$title = _('Click to popup a dialog to select a date graphically');
	printf('<a href="%s" title="%s"><img class="chooser" src="%s/calendar.png" id="f_trigger_%s" style="cursor: pointer;" alt="Calendar" /></a>',$href,$title,IMGDIR,$attr);
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
 * @param string $dn The DN to pretty-print.
 * @return string
 */
function htmlid($sid,$dn) {
	return sprintf('SID%s:%s',$sid,preg_replace('/[\ =,]/','_',$dn));
}

/**
 * Is compression enabled for output
 */
function isCompress() {
	return (isset($_SESSION[APPCONFIG]) && $_SESSION[APPCONFIG]->GetValue('appearance','compress')
		&& ! ini_get('zlib.output_compression')
		&& eregi('gzip',$_SERVER['HTTP_ACCEPT_ENCODING']));
}
?>
