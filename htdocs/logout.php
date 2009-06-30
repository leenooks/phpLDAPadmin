<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/logout.php,v 1.17 2005/09/25 16:11:44 wurley Exp $

/**
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me
 * the server_id and I will log out the user (delete the cookie)
 *
 * Variables that come in via common.php
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $ldapserver->haveAuthInfo())
	pla_error($lang['no_one_logged_in']);

if (in_array($ldapserver->auth_type, array('cookie','session'))) {
	syslog_notice (sprintf("Logout for %s",get_logged_in_dn($ldapserver)));
	unset_login_dn($ldapserver) or pla_error($lang['could_not_logout']);
	unset_lastactivity($ldapserver);

} else
	pla_error(sprintf($lang['unknown_auth_type'], htmlspecialchars($ldapserver->auth_type)));

include './header.php';
?>

<script language="javascript">
	parent.left_frame.location.reload();
</script>

	<center>
	<br />
	<br />
	<?php echo sprintf($lang['logged_out_successfully'],htmlspecialchars($ldapserver->name)); ?><br />
	</center>

</body>
</html>
