<?php

/*
 * common.php
 * Contains code to be executed at the top of each phpLDAPadmin page.
 * include this file at the top of every PHP file.
 */

if( file_exists( realpath( 'config.php' ) ) ) {
	require realpath( 'config.php' );
}
require_once realpath( 'functions.php' );
require_once realpath( 'schema_functions.php' );

// grab the language file configured in config.php
if( ! isset( $language ) )
	$language = 'english';
if( file_exists( realpath( "lang/$language.php" ) ) )
	include realpath( "lang/$language.php" );

// Turn off notices about referencing arrays and such, but leave everything else on.
error_reporting( E_ALL ^ E_NOTICE );

if( ! isset( $templates ) || ! is_array( $templates ) )
	$tempaltes = array();

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

	array_stripslashes($_POST);
	array_stripslashes($_GET);
	array_stripslashes($_COOKIES);
	$slashes_stripped = true;
}

?>
