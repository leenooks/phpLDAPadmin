<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/session_functions.php,v 1.7 2004/05/10 12:25:58 uugdave Exp $

/**
 * A collection of functions to handle sessions throughout phpLDAPadmin.
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/** The session ID that phpLDAPadmin will use for all sessions */
@define( 'PLA_SESSION_ID', 'PLASESSID' );
/** Enables session paranoia, which causes SIDs to change each page load (EXPERIMENTAL!) */
define('pla_session_id_paranoid', false);
/** Flag to indicate whether the session has already been initialized (this constant gets stored in $_SESSION) */
define('pla_session_id_init', 'pla_initialized');
/** The minimum first char value IP in hex for IP hashing. */
define('pla_session_id_ip_min', 8);
/** The maximum first char value of the IP in hex for IP hashing. */
define('pla_session_id_ses_max', 36);

/**
 * Creates a new session id, which includes an IP hash.
 *
 * @return string the new session ID string
 */
function pla_session_get_id()
{
	$id_md5 = md5(rand(1,1000000));
	$ip_md5 = md5($_SERVER['REMOTE_ADDR']);
	$id_hex = hexdec($id_md5[0]) + 1;
	$ip_hex = hexdec($ip_md5[0]);
	if ($ip_hex <= pla_session_id_ip_min)
		$ip_len = pla_session_id_ip_min;
	else
		$ip_len = $ip_hex - 1;

	$new_id = substr($id_md5, 0, $id_hex) . 
		substr($ip_md5, $ip_hex, $ip_len) .
		substr($id_md5, $id_hex, pla_session_id_ses_max - ($id_hex + $ip_len));
		
	return $new_id;
}

/**
 * Checks if the session belongs to an IP
 *
 * @return bool True, if the session is valid
 */
function pla_session_verify_id()
{
	$check_id = session_id();
	$ip_md5 = md5($_SERVER['REMOTE_ADDR']);
	$id_hex = hexdec($check_id[0]) + 1;
	$ip_hex = hexdec($ip_md5[0]);
	if ($ip_hex <= pla_session_id_ip_min)
		$ip_len = pla_session_id_ip_min;
	else
		$ip_len = $ip_hex - 1;
	
	$ip_ses = substr($check_id, $id_hex, $ip_len);
	$ip_ver = substr($ip_md5, $ip_hex, $ip_len);

	return ($ip_ses == $ip_ver);
}

/**
 * The only function which should be called by a user
 *
 * @see common.php
 * @see PLA_SESSION_ID
 * @return bool Returns true if the session was started the first time
 */
function pla_session_start()
{
    // If session.auto_start is on in the server's PHP configuration (php.ini), then
    // we will have problems loading our schema cache since the session will have started
    // prior to loading the SchemaItem (and descedants) class. Destroy the auto-started
    // session to prevent this problem.
    if( ini_get( 'session.auto_start' ) )
        @session_destroy();

	// Do we already have a session?
    if( session_id() ) {
        return;
    }

    session_name( PLA_SESSION_ID );
    session_start();

	// Do we have a valid session?
	$is_initialized = array_key_exists( pla_session_id_init, $_SESSION );
	if( ! $is_initialized ) {
		if( pla_session_id_paranoid ) {
			ini_set('session.use_trans_sid', 0);
			session_destroy();
		        session_id(pla_session_get_id());
			session_start();
			ini_set('session.use_trans_sid', 1);
		}
		$_SESSION[pla_session_id_init] = true;
	}

	header("Cache-control: private"); // IE 6 Fix

	if( pla_session_id_paranoid && ! pla_session_verify_id() )
		pla_error("Session inconsistent or session timeout");

	return ( ! $is_initialized ) ? true : false;
}

?>
