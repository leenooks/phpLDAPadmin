<?php

/*
 * common.php
 * Contains code to be executed at the top of each phpLDAPadmin page.
 * include this file at the top of every PHP file.
 */

// Turn on all notices and warnings. This helps us write cleaner code (we hope at least)
error_reporting( E_ALL );

// We require this version or newer (use @ to surpress errors if we are included twice)
@define( 'REQUIRED_PHP_VERSION', '4.1.0' );
@define( 'HTTPS_PORT', 443 );

// config.php might not exist (if the user hasn't configured PLA yet)
// Only include it if it does exist.
if( file_exists( realpath( 'config.php' ) ) ) {
	ob_start();
	is_readable( realpath( 'config.php' ) ) or pla_error( "Could not read config.php, its permissions are too strict." );
	require realpath( 'config.php' );
	ob_end_clean();
}

// General functions (pla_ldap_search(), pla_error(), get_object_attrs(), etc.)
is_readable( realpath( 'functions.php' ) ) 
	or pla_error( "Cannot read the file 'functions.php' its permissions are too strict." );
ob_start();
require_once realpath( 'functions.php' );
ob_end_clean();

// Functions for reading the server schema (get_schema_object_classes(), etc.)
is_readable( realpath( 'schema_functions.php' ) ) 
	or pla_error( "Cannot read the file 'schema_functions.php' its permissions are too strict." );
ob_start();
require_once realpath( 'schema_functions.php' );
ob_end_clean();

// Functions that can be defined by the user (preEntryDelete(), postEntryDelete(), etc.)
is_readable( realpath( 'custom_functions.php' ) ) 
	or pla_error( "Cannot read the file 'custom_functions.php' its permissions are too strict." );
ob_start();
require_once realpath( 'custom_functions.php' );
ob_end_clean();

// Our custom error handler receives all error notices that pass the error_reporting()
// level set above.
set_error_handler( 'pla_error_handler' );

// Creates the language array which will be populated with localized strings
// based on the user-configured language.
$lang = array();

// Little bit of sanity checking
if( ! file_exists( realpath( 'lang/recoded' ) ) ) {
	pla_error( "Your install of phpLDAPadmin is missing the 'lang/recoded' directory. This should not happen. You can try running 'make' in the lang directory" );
}

// use English as a base-line (in case the selected language is missing strings)
if( file_exists( realpath( 'lang/recoded/en.php' ) ) )
	include realpath( 'lang/recoded/en.php' );
else
	pla_error( "Error! Missing recoded English language file. Run 'make' in the lang/ directory." );

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
					include realpath( "lang/recoded/$HTTP_LANG.php" );
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
				include realpath( "lang/recoded/$language.php" );
			} else { 
				pla_error( "Could not read language file 'lang/recoded/$language.php'. Either the file 
						does not exist, or permissions do not allow phpLDAPadmin to read it." );
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
	if( ! function_exists( "array_stripslashes" ) ) {
		function array_stripslashes(&$array) {
			if( is_array( $array ) )
				while ( list( $key ) = each( $array ) ) 
					if ( is_array( $array[$key] ) && $key != $array ) 
						array_stripslashes( $array[$key] );
					else 
						$array[$key] = stripslashes( $array[$key] );
		}
	}

	array_stripslashes($_GET);
	array_stripslashes($_POST);
	array_stripslashes($_COOKIE);
	$slashes_stripped = true;
}

?>
