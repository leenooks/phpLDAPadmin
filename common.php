<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/common.php,v 1.49 2004/05/10 12:28:07 uugdave Exp $

/*
 * common.php
 * Contains code to be executed at the top of each phpLDAPadmin page.
 * include this file at the top of every PHP file.
 */

// Turn on all notices and warnings. This helps us write cleaner code (we hope at least)
error_reporting( E_ALL );

/** The minimum version of PHP required to run phpLDAPadmin. */
@define( 'REQUIRED_PHP_VERSION', '4.1.0' );
/** The default setting for $search_deref if unspecified or misconfigured by user. */
@define( 'DEFAULT_SEARCH_DEREF_SETTING', LDAP_DEREF_ALWAYS );
/** The default setting for $tree_deref if unspecified or misconfigured by user. */
@define( 'DEFAULT_TREE_DEREF_SETTING',   LDAP_DEREF_NEVER  );
/** The default setting for $export_deref if unspecified or misconfigured by user. */
@define( 'DEFAULT_EXPORT_DEREF_SETTING', LDAP_DEREF_NEVER  );
/** The default setting for $view_deref if unspecified or misconfigured by user. */
@define( 'DEFAULT_VIEW_DEREF_SETTING', LDAP_DEREF_NEVER  );

// General functions needed to proceed (pla_ldap_search(), pla_error(), get_object_attrs(), etc.)
ob_start();
if( ! file_exists( realpath( './functions.php' ) ) ) {
    ob_end_clean();
    die( "Fatal error: Required file 'functions.php' does not exist." );
} 
if( ! is_readable( realpath( './functions.php' ) ) ) {
    ob_end_clean();
    die( "Cannot read the file 'functions.php' its permissions are too strict." );
}
require_once realpath( './functions.php' );
ob_end_clean();

// Our custom error handler receives all error notices that pass the error_reporting()
// level set above.
set_error_handler( 'pla_error_handler' );

// Creates the language array which will be populated with localized strings
// based on the user-configured language.
$lang = array();

// config.php might not exist (if the user hasn't configured PLA yet)
// Only include it if it does exist.
if( file_exists( realpath( './config.php' ) ) ) {
	ob_start();
	is_readable( realpath( './config.php' ) ) or pla_error( "Could not read config.php, its permissions are too strict." );
	include realpath( './config.php' );
	ob_end_clean();
}

$required_files = array( 
    // Functions that can be defined by the user (preEntryDelete(), postEntryDelete(), etc.)
    './session_functions.php', 
    // Functions for reading the server schema (get_schema_object_classes(), etc.)
    './schema_functions.php', 
    // Functions that can be defined by the user (preEntryDelete(), postEntryDelete(), etc.)
    './custom_functions.php',
    // Functions for hashing passwords with OpenSSL binary (only if mhash not present)
    './emuhash_functions.php',
    // The base English language strings
    './lang/recoded/en.php' );


// Include each required file and check for sanity.
foreach( $required_files as $file_name ) {
    file_exists( realpath( $file_name ) )
        or pla_error( "Fatal error: Required file '$file_name' does not exist." );
    is_readable( realpath( $file_name ) ) 
        or pla_error( "Fatal error: Cannot read the file '$file_name', its permissions are too strict." );
    ob_start();
    require_once realpath( $file_name );
    ob_end_clean();
}

pla_session_start();

// Language configuration. Auto or specified?
// Shall we attempt to auto-determine the language?
if( isset( $language ) ) {
	if( 0 == strcasecmp( $language, "auto" ) ) {

		// Make sure their browser correctly reports language. If not, skip this.
		if( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {

			// get the languages which are spetcified in the HTTP header
			$HTTP_LANGS1 = preg_split ("/[;,]+/", $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			$HTTP_LANGS2 = preg_split ("/[;,]+/", $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			foreach( $HTTP_LANGS2 as $key => $value ) {
					$value=preg_split ("/[-]+/", $value );
					$HTTP_LANGS2[$key]=$value[0];
			}
		
			$HTTP_LANGS = array_merge ($HTTP_LANGS1, $HTTP_LANGS2);
			foreach( $HTTP_LANGS as $HTTP_LANG) {
				// try to grab one after the other the language file
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
		// grab the language file configured in config.php
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
}

// If config.php doesn't create the templates array, create it here.
if( ! isset( $templates ) || ! is_array( $templates ) )
	$templates = array();

// Always including the 'custom' template (the most generic and flexible)
$templates['custom'] = 
        array(  'desc'    => 'Custom',
                'icon'    => 'images/object.png',
                'handler' => 'custom.php' );

// Strip slashes from GET, POST, and COOKIE variables if this
// PHP install is configured to automatically addslashes()
if ( get_magic_quotes_gpc() && ( ! isset( $slashes_stripped ) || ! $slashes_stripped ) ) {
	array_stripslashes($_GET);
	array_stripslashes($_POST);
	array_stripslashes($_COOKIE);
	array_stripslashes($_FILES);
	$slashes_stripped = true;
}

?>
