<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/logout.php,v 1.9 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/*
 * logout.php
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me 
 * the server_id and I will log out the user (delete the cookie)
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require realpath( 'common.php' );

$server_id = $_GET['server_id'];
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['no_one_logged_in'] );

if( ! isset( $servers[ $server_id ][ 'auth_type' ] ) )
	return false;
$auth_type = $servers[ $server_id ][ 'auth_type' ]; 
if( 'cookie' == $auth_type || 'session' == $auth_type )
	unset_login_dn( $server_id ) or pla_error( $lang['could_not_logout'] );
else
	pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $auth_type ) ) );

include realpath( 'header.php' );

?>

<script language="javascript">
	parent.left_frame.location.reload();
</script>

	<center>
	<br />
	<br />
	<?php echo sprintf( $lang['logged_out_successfully'], htmlspecialchars($servers[$server_id]['name']) ); ?><br />
	</center>

</body>
</html>

