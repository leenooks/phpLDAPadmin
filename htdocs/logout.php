<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/logout.php,v 1.17.4.4 2005/12/16 11:33:07 wurley Exp $

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
	pla_error(_('No one is logged in to that server.'));

if (in_array($ldapserver->auth_type, array('cookie','session'))) {
	syslog_notice (sprintf('Logout for %s',$ldapserver->getLoggedInDN()));
	$ldapserver->unsetLoginDN() or pla_error(_('Could not logout.'));
	unset_lastactivity($ldapserver);

	if (isset($_SESSION['cache'][$ldapserver->server_id]['tree'])) {
		unset($_SESSION['cache'][$ldapserver->server_id]['tree']);
	}
	pla_session_close();

} else
	pla_error(sprintf(_('Unknown auth_type: %s'), htmlspecialchars($ldapserver->auth_type)));

include './header.php';
?>

<body>
<script type="text/javascript" language="javascript">
	parent.left_frame.location.reload();
</script>

	<center>
	<br />
	<br />
	<?php echo sprintf(_('Logged out successfully from server <b>%s</b>'),htmlspecialchars($ldapserver->name)); ?><br />
	</center>

</body>
</html>
