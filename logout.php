<?php 

/*
 * logout.php
 * For servers whose auth_type is set to 'form'. Pass me 
 * the server_id and I will log out the user (delete the cookie)
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require realpath( 'common.php' );

$server_id = $_GET['server_id'];
check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "No one is logged in to that server." );

unset_cookie_login_dn( $server_id ) or pla_error( "Could not delete cookie!" );

include realpath( 'header.php' );

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

