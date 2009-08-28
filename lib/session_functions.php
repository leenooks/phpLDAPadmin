<?php
/**
 * A collection of functions to handle sessions.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 * @subpackage Session
 */

/** The session ID that this application will use for all sessions */
define('APP_SESSION_ID',md5(app_name()));
/** Enables session paranoia, which causes SIDs to change each page load (EXPERIMENTAL!) */
define('app_session_id_paranoid', false);
/** Flag to indicate whether the session has already been initialized (this constant gets stored in $_SESSION) */
define('app_session_id_init', 'app_initialized');
/** The minimum first char value IP in hex for IP hashing. */
define('app_session_id_ip_min', 8);
/** The maximum first char value of the IP in hex for IP hashing. */
define('app_session_id_ses_max', 36);

/**
 * Creates a new session id, which includes an IP hash.
 *
 * @return string the new session ID string
 */
function app_session_get_id() {
	$id_md5 = md5(rand(1,1000000));
	$ip_md5 = md5($_SERVER['REMOTE_ADDR']);
	$id_hex = hexdec($id_md5[0]) + 1;
	$ip_hex = hexdec($ip_md5[0]);
	if ($ip_hex <= app_session_id_ip_min)
		$ip_len = app_session_id_ip_min;
	else
		$ip_len = $ip_hex - 1;

	$new_id = substr($id_md5, 0, $id_hex) .
		substr($ip_md5, $ip_hex, $ip_len) .
		substr($id_md5, $id_hex, app_session_id_ses_max - ($id_hex + $ip_len));

	return $new_id;
}

/**
 * Checks if the session belongs to an IP
 *
 * @return boolean True, if the session is valid
 */
function app_session_verify_id() {
	$check_id = session_id();
	$ip_md5 = md5($_SERVER['REMOTE_ADDR']);
	$id_hex = hexdec($check_id[0]) + 1;
	$ip_hex = hexdec($ip_md5[0]);
	if ($ip_hex <= app_session_id_ip_min)
		$ip_len = app_session_id_ip_min;
	else
		$ip_len = $ip_hex - 1;

	$ip_ses = substr($check_id, $id_hex, $ip_len);
	$ip_ver = substr($ip_md5, $ip_hex, $ip_len);

	return ($ip_ses == $ip_ver);
}

function app_session_param() {
	/* If cookies were disabled, build the url parameter for the session id.
	 * It will be append to the url to be redirect */
	return (SID != '') ? sprintf('&%s=%s',session_name(),session_id()) : '';
}

/**
 * The only function which should be called by a user
 *
 * @see common.php
 * @see APP_SESSION_ID
 * @return boolean Returns true if the session was started the first time
 */
function app_session_start() {
	$sysmsg = null;

	# If we have a sysmsg before our session has started, then preserve it.
	if (isset($_SESSION['sysmsg']))
		$sysmsg = $_SESSION['sysmsg'];

	/* If session.auto_start is on in the server's PHP configuration (php.ini), then
	 * we will have problems loading our schema cache since the session will have started
	 * prior to loading the SchemaItem (and descedants) class. Destroy the auto-started
	 * session to prevent this problem.
	 */
	if (ini_get('session.auto_start') && ! array_key_exists(app_session_id_init,$_SESSION))
		@session_destroy();

	# Do we already have a session?
	if (@session_id())
		return;

	@session_name(APP_SESSION_ID);
	@session_start();

	# Do we have a valid session?
	$is_initialized = is_array($_SESSION) && array_key_exists(app_session_id_init,$_SESSION);

	if (! $is_initialized) {
		if (app_session_id_paranoid) {
			ini_set('session.use_trans_sid',0);
			@session_destroy();
			@session_id(app_session_get_id());
			@session_start();
			ini_set('session.use_trans_sid',1);
		}

		$_SESSION[app_session_id_init]['name'] = app_name();
		$_SESSION[app_session_id_init]['version'] = app_version();
		$_SESSION[app_session_id_init]['config'] = filemtime(CONFDIR.'config.php');
	}

	@header('Cache-control: private'); // IE 6 Fix

	if (app_session_id_paranoid && ! app_session_verify_id())
		error('Session inconsistent or session timeout','error','index.php');

	# Check we have the correct version of the SESSION cache
	if (isset($_SESSION['cache']) || isset($_SESSION[app_session_id_init])) {
		if (! is_array($_SESSION[app_session_id_init])) $_SESSION[app_session_id_init] = array();

		if (! isset($_SESSION[app_session_id_init]['version']) || ! isset($_SESSION[app_session_id_init]['config']) || ! isset($_SESSION[app_session_id_init]['name'])
			|| $_SESSION[app_session_id_init]['name'] !== app_name()
			|| $_SESSION[app_session_id_init]['version'] !== app_version()
			|| $_SESSION[app_session_id_init]['config'] != filemtime(CONFDIR.'config.php')) {

			$_SESSION[app_session_id_init]['name'] = app_name();
			$_SESSION[app_session_id_init]['version'] = app_version();
			$_SESSION[app_session_id_init]['config'] = filemtime(CONFDIR.'config.php');

			unset($_SESSION['cache']);
			unset($_SESSION[APPCONFIG]);

			# Our configuration information has changed, so we'll redirect to index.php to get it reloaded again.
			system_message(array(
				'title'=>_('Configuration cache stale.'),
				'body'=>_('Your configuration has been automatically refreshed.'),
				'type'=>'info','special'=>true));

			$config_file = CONFDIR.'config.php';
			$config = check_config($config_file);
			if (! $config)
				debug_dump_backtrace('config is empty?',1);

		} else {
			# Sanity check, specially when upgrading from a previous release.
			if (isset($_SESSION['cache']))
				foreach (array_keys($_SESSION['cache']) as $id)
					if (isset($_SESSION['cache'][$id]['tree']['null']) && ! is_object($_SESSION['cache'][$id]['tree']['null']))
						unset($_SESSION['cache'][$id]);
		}
	}

	# If we came via index.php, then set our $config.
	if (! isset($_SESSION[APPCONFIG]) && isset($config))
		$_SESSION[APPCONFIG] = $config;

	# Restore our sysmsg's if there were any.
	if ($sysmsg) {
		if (! isset($_SESSION['sysmsg']) || ! is_array($_SESSION['sysmsg']))
			$_SESSION['sysmsg'] = array();

		$_SESSION['sysmsg'] = array_merge($_SESSION['sysmsg'],$sysmsg);
	}
}

/**
 * Stops the current session.
 */
function app_session_close() {
	@session_write_close();
}
?>
