<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/logout.php,v 1.14 2005/03/16 11:20:25 wurley Exp $

/**
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me
 * the server_id and I will log out the user (delete the cookie)
 *
 * Variables that come in as GET vars:
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require realpath( 'common.php' );

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
$ldapserver = new LDAPServer ($server_id);

if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['no_one_logged_in'] );

if( in_array($ldapserver->auth_type, array('cookie','session')) ) {
        syslog_msg ( LOG_NOTICE,"Logout for " . get_logged_in_dn( $ldapserver ) );
	unset_login_dn( $ldapserver ) or pla_error( $lang['could_not_logout'] );
	unset_lastactivity( $ldapserver );
} else
	pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $ldapserver->auth_type ) ) );

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
