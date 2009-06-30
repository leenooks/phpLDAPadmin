<?php 

/*
 * login.php
 * For servers whose auth_type is set to 'form'. Pass me the login info
 * and I'll write two cookies, pla_login_dn_X and pla_pass_X
 * where X is the server_id. The cookie_time comes from config.php
 *
 * Note: this file uses ldap_connect() and ldap_bind() only for purposes
 *       of verifying the user-supplied DN and Password.
 *
 * Variables that come in as POST vars:
 *  - login_dn
 *  - login_pass 
 *  - server_id
 */

require 'common.php';

$server_id = $_POST['server_id'];
$dn = isset( $_POST['login_dn'] ) ? $_POST['login_dn'] : null;
$uid = isset( $_POST['uid'] ) ? $_POST['uid'] : null;
$pass = isset( $_POST['login_pass'] ) ? $_POST['login_pass'] : null;
$redirect = isset( $_POST['redirect'] ) ? rawurldecode( $_POST['redirect'] ) : null;
$anon_bind = isset( $_POST['anonymous_bind'] ) && $_POST['anonymous_bind'] == 'on' ? true : false;
check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );

if( ! $anon_bind ) {
	strlen($pass) or pla_error( "You left the password blank." );
}

if( $anon_bind ) {
	$dn = null;
	$pass = null;
}

$host = $servers[$server_id]['host'];
$port = $servers[$server_id]['port'];

if ( 	isset( $servers[$server_id]['login_attr'] ) &&
	$servers[$server_id]['login_attr'] != "dn" && 
	$servers[$server_id]['login_attr'] != "") {

	// search for the "uid" first
	$ds = ldap_connect ( $host, $port );
	$ds or pla_error( "Could not contact '" . htmlspecialchars( $host ) . "' on port '" . htmlentities( $port ) . "'" );
	@ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 );
	@ldap_bind ($ds) or pla_error( "Could not bind anonymously to server. " .
				"Unless your server accepts anonymous binds, " .
				"the login_attr feature will not work properly.");
	$sr=@ldap_search($ds,$servers[$server_id]['base'],$servers[$server_id]['login_attr'] ."=". $uid, array("dn"), 0, 1);
	$result = @ldap_get_entries($ds,$sr);
	$dn = $result[0]["dn"];
	@ldap_unbind ($ds);
}

// verify that the login is good 
$ds = @ldap_connect( $host, $port );
$ds or pla_error( "Could not connect to '" . htmlspecialchars( $host ) . "' on port '" . htmlentities( $port ) . "'" );

// go with LDAP version 3 if possible 
@ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 );

$bind_result = @ldap_bind( $ds, $dn, $pass );

if( ! $bind_result )
	pla_error( "Bad username/password. Try again" );

set_cookie_login_dn( $server_id, $dn, $pass, $anon_bind ) or pla_error( "Could not set cookie!" );

// Clear out any pre-existing tree data in the session for this server
session_start();
if( session_is_registered( 'tree' ) )
	if( isset( $_SESSION['tree'][$server_id] ) )
		unset( $_SESSION['tree'][$server_id] );
if( session_is_registered( 'tree_icons' ) )
	if( isset( $_SESSION['tree_icons'][$server_id] ) )
		unset( $_SESSION['tree_icons'][$server_id] );
session_write_close();

?>

<html>
<head>
<script language="javascript">
	parent.left_frame.location.reload();
	<?php if( $redirect ) { ?>
		location.href='<?php echo $redirect; ?>';
	<?php } ?>
</script>
<link rel="stylesheet" href="style.css" />

<?php if( $redirect ) { ?>

	<meta http-equiv="refresh" content="0;<?php echo $redirect; ?>" />

<?php } ?>

</head>
<body>

<?php if( $redirect ) { ?>

	Redirecting... Click <a href="<?php echo $redirect; ?>">here</a> if nothing happens.<br />

<?php } else { ?>

	<center>
	<br />
	<br />
	<br />
	Successfully logged in to server <b><?php echo htmlspecialchars($servers[$server_id]['name']); ?></b><br />
	<?php if( $anon_bind ) { ?>
		(anonymous bind)	
	<?php } ?>
	<br />
	</center>

<?php } ?>

</body>
</html>

