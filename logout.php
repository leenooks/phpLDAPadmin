<?php 

/*
 * logout.php
 * For servers whose auth_type is set to 'form'. Pass me 
 * the server_id and I will log out the user (delete the cookie)
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require 'config.php';
require_once 'functions.php';

$server_id = $_GET['server_id'];
check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "No one is logged in to that server." );

$logged_in_dn = get_logged_in_dn( $server_id );
$logged_in_pass = get_logged_in_pass( $server_id );
$anon_bind = $logged_in_dn == 'Anonymous' ? true : false;

$expire = time()-3600;
if( $anon_bind ) {
	$res1 = setcookie( "pla_login_dn_$server_id", '0', $expire, dirname( $_SERVER['PHP_SELF'] ) );
	$res2 = setcookie( "pla_pass_$server_id", '0', $expire, dirname( $_SERVER['PHP_SELF'] ) );
} else {
	$res1 = setcookie( "pla_login_dn_$server_id", $logged_in_dn, $expire, dirname( $_SERVER['PHP_SELF'] ) );
	$res2 = setcookie( "pla_pass_$server_id", $logged_in_pass, $expire, dirname( $_SERVER['PHP_SELF'] ) );
}

if( ! $res1 || ! $res2 )
	pla_error( "Could not delete cookie!" );
?>

<html>
<head>
<script language="javascript">
	parent.left_frame.location.reload();
</script>
<link rel="stylesheet" href="style.css" />

</head>
<body>

	<center>
	<br />
	<br />
	Logged out successfully from <b><?php echo htmlspecialchars($servers[$server_id]['name']); ?></b><br />
	</center>

</body>
</html>

