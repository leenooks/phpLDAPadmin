<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/common.php,v 1.68 2005/09/25 16:11:44 wurley Exp $

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

@DEFINE(LIBDIR,'./');

# Turn on all notices and warnings. This helps us write cleaner code (we hope at least)
if (phpversion() >= "5") {
	# Work-around to get PLA to work in PHP5
	ini_set( "zend.ze1_compatibility_mode", 1 );

	# E_DEBUG is PHP5 specific and prevents warnings about using 'var' to declar class members
	error_reporting( 'E_DEBUG' );
} else
	# For PHP4
	error_reporting( E_ALL );

# For PHP5 backward/forward compatibility
if (! defined('E_STRICT'))
	define('E_STRICT',2048);

# General functions needed to proceed (pla_ldap_search(), pla_error(), get_object_attrs(), etc.)
ob_start();
require_once realpath(LIBDIR.'functions.php');
ob_end_clean();

/* Our custom error handler receives all error notices that pass the error_reporting()
   level set above. */
set_error_handler('pla_error_handler');

/* Creates the language array which will be populated with localized strings
   based on the user-configured language. */
$lang = array();

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
if ($language == "auto") {

	# Make sure their browser correctly reports language. If not, skip this.
	if( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {

		# get the languages which are spetcified in the HTTP header
		$HTTP_LANGS1 = preg_split ("/[;,]+/", $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		$HTTP_LANGS2 = preg_split ("/[;,]+/", $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		foreach( $HTTP_LANGS2 as $key => $value ) {
				$value=preg_split ("/[-]+/", $value );
				$HTTP_LANGS2[$key]=$value[0];
		}

		$HTTP_LANGS = array_merge ($HTTP_LANGS1, $HTTP_LANGS2);
		foreach( $HTTP_LANGS as $HTTP_LANG) {
			# try to grab one after the other the language file
			if( file_exists( realpath( "lang/recoded/$HTTP_LANG.php" ) ) &&
				is_readable( realpath( "lang/recoded/$HTTP_LANG.php" ) ) ) {
				ob_start();
				include realpath( "lang/recoded/$HTTP_LANG.php" );
				ob_end_clean();
				break;
			}
		}
	}

} else {
	# grab the language file configured in config.php
	if( $language != null ) {
		if( 0 == strcmp( $language, 'english' ) )
			$language = 'en';
		if( file_exists( realpath( "lang/recoded/$language.php" ) ) &&
			is_readable( realpath( "lang/recoded/$language.php" ) ) ) {
			ob_start();
			include realpath( "lang/recoded/$language.php" );
			ob_end_clean();
		} else {
			pla_error( "Could not read language file 'lang/recoded/$language.php'. Either the file
					does not exist, or its permissions do not allow phpLDAPadmin to read it." );
		}
	}
}

# If config.php doesn't create the templates array, create it here.
if (! isset($templates) || ! is_array($templates))
	$templates = array();

# Always including the 'custom' template (the most generic and flexible)
$templates['custom'] =
	array('desc' => 'Custom',
		'icon' => 'images/object.png',
		'handler' => 'custom.php' );

/*
 * Strip slashes from GET, POST, and COOKIE variables if this
 * PHP install is configured to automatically addslashes()
 */
if (get_magic_quotes_gpc() && (! isset($slashes_stripped) || ! $slashes_stripped)) {
	array_stripslashes($_REQUEST);
	array_stripslashes($_GET);
	array_stripslashes($_POST);
	array_stripslashes($_COOKIE);
	array_stripslashes($_FILES);
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
?>
