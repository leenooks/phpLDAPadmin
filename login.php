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
$dn = $_POST['login_dn'];
$uid = $_POST['uid'];
$pass = $_POST['login_pass'];
$redirect = rawurldecode( $_POST['redirect'] );
$anon_bind = $_POST['anonymous_bind'] == 'on' ? true : false;
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

// go with LDAP version 3 if possible (needed for renaming and Novell schema fetching)
@ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 );

$bind_result = @ldap_bind( $ds, $dn, $pass );

if( ! $bind_result )
	pla_error( "Bad username/password. Try again" );

if( ! isset( $cookie_time ) )
	$cookie_time = 0;
$expire = $cookie_time == 0 ? null : time()+$cookie_time;
if( $anon_bind ) {
	// we set the cookie val to 0 for anonymous binds.
	$res1 = setcookie( "pla_login_dn_$server_id", '0', $expire, dirname( $_SERVER['PHP_SELF'] ) );
	$res2 = setcookie( "pla_pass_$server_id", '0', $expire, dirname( $_SERVER['PHP_SELF'] ) );
} else {
	$res1 = setcookie( "pla_login_dn_$server_id", $dn, $expire, dirname( $_SERVER['PHP_SELF'] ) );
	$res2 = setcookie( "pla_pass_$server_id", $pass, $expire, dirname( $_SERVER['PHP_SELF'] ) );
}
if( ! $res1 || ! $res2 )
	pla_error( "Could not set cookie!" );
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
	Logged in to <b><?php echo htmlspecialchars($servers[$server_id]['name']); ?></b><br />
	<?php if( $anon_bind ) { ?>
		(anonymous bind)	
	<?php } ?>
	<br />
	<br />
	<br />
	Click <a href="search.php?server_id=<?php echo $server_id?>">here</a> to go to the search form.
	</center>

<?php } ?>

</body>
</html>

