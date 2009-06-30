<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/login.php,v 1.51.2.10 2007/01/27 13:03:56 wurley Exp $

/**
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me the
 * login info and I'll write two cookies, pla_login_dn_X and pla_pass_X where X
 * is the server_id. The cookie_time comes from config.php
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - login_dn
 *  - login_pass
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

# Prevents users from coming here without going through the proper channels
if (! isset($ldapserver))
	header('Location: index.php');

$dn = isset($_POST['login_dn']) ? $_POST['login_dn'] : null;
$pass = isset($_POST['login_pass']) ? $_POST['login_pass'] : null;
$uid = isset($_POST['uid']) ? $_POST['uid'] : null;

if ($ldapserver->isAnonBindAllowed())
	$anon_bind = isset($_POST['anonymous_bind']) && $_POST['anonymous_bind'] == 'on' ? true : false;
else
	$anon_bind = false;

if (! $anon_bind)
	strlen($pass) or pla_error(_('You left the password blank.'));

$save_auth_type = $ldapserver->auth_type;

if ($anon_bind) {
	if (DEBUG_ENABLED)
		debug_log('Anonymous Login was posted [%s].',64,$anon_bind);

	$dn = null;
	$pass = null;

/* Checks if the login_attr option is enabled for this host,
   which allows users to login with a simple username like 'jdoe' rather
   than the fully qualified DN, 'uid=jdoe,ou=people,,dc=example,dc=com'. */
} elseif ($ldapserver->isLoginAttrEnabled()) {

	# Is this a login string (printf-style)
	if ($ldapserver->isLoginStringEnabled()) {
		$dn = str_replace('<username>',$uid,$ldapserver->getLoginString());

		if (DEBUG_ENABLED)
			debug_log('LoginStringDN: [%s]',64,$dn);

	} else {
		# This is a standard login_attr

		/* Fake the auth_type of config to do searching. This way, the admin can specify
		   the DN to use when searching for the login_attr user. */
		$ldapserver->auth_type = 'config';

		set_error_handler('temp_login_error_handler');
		if ($ldapserver->login_dn)
			$ldapserver->connect(true,'user');
		else
			$ldapserver->connect(true,'anonymous');

		restore_error_handler();

		if (! empty($ldapserver->login_class))
			$filter = sprintf('(&(objectClass=%s)(%s=%s))',$ldapserver->login_class,$ldapserver->login_attr,$uid);
		else
			$filter = sprintf('%s=%s',$ldapserver->login_attr,$uid);

		# Got through each of the BASE DNs and test the login.
		foreach ($ldapserver->getBaseDN() as $base_dn) {
			if (DEBUG_ENABLED)
				debug_log('Searching LDAP with base [%s]',64,$base_dn);

			$result = $ldapserver->search(null,$base_dn,$filter,array('dn'));
			$result = array_pop($result);
			$dn = $result['dn'];

			if ($dn) {
				if (DEBUG_ENABLED)
					debug_log('Got DN [%s] for user ID [%s]',64,$dn,$uid);
				break;
			}
		}


		# If we got here then we werent able to find a DN for the login filter.
		if (! $dn)
			pla_error(_('Bad username or password. Please try again.'));

		# restore the original auth_type
		$ldapserver->auth_type = $save_auth_type;
	}
}

# We fake a 'config' server auth_type to omit duplicated code
if (DEBUG_ENABLED)
	debug_log('Setting login type to CONFIG with DN [%s]',64,$dn);

$save_auth_type = $ldapserver->auth_type;
$ldapserver->auth_type = 'config';
$ldapserver->login_dn = $dn;
$ldapserver->login_pass = $pass;

# Verify that dn is allowed to login
if (! $ldapserver->userIsAllowedLogin($dn))
	pla_error(_('Sorry, you are not allowed to use phpLDAPadmin with this LDAP server.'));

if (DEBUG_ENABLED)
	debug_log('User is not prohibited from logging in - now bind with DN [%s]',64,$dn);

# verify that the login is good
if (is_null($dn) && is_null($pass))
	$ds = $ldapserver->connect(true,'anonymous',true);
else
	$ds = $ldapserver->connect(true,'user',true);

if (DEBUG_ENABLED)
	debug_log('Connection returned [%s]',64,$ds);

if (! is_resource($ds)) {
	if ($anon_bind)
		pla_error(_('Could not bind anonymously to server.'),null,null,true);
	else
		pla_error(_('Bad username or password. Please try again.'),null,null,true);

	syslog_notice("Authentification FAILED for $dn");
}

$ldapserver->auth_type = $save_auth_type;
$ldapserver->setLoginDN($dn,$pass,$anon_bind) or pla_error(_('Could not set cookie.'));
set_lastactivity($ldapserver);

if (! $anon_bind) {
	syslog_notice("Authentification successful for $dn");
}

pla_session_close();

include './header.php';
echo '<body>';

echo '<script type="text/javascript" language="javascript">';
if ($anon_bind && $config->GetValue('appearance','anonymous_bind_redirect_no_tree'))
	printf("parent.location.href='search.php?server_id=%s'",$ldapserver->server_id);
else
	echo 'parent.left_frame.location.reload();';
echo '</script>';

echo '<center><br /><br /><br />';
printf(_('Successfully logged into server <b>%s</b>').'<br />',htmlspecialchars($ldapserver->name));

if ($anon_bind)
	printf('(%s)',_('Anonymous Bind'));

echo '<br /></center>';
echo '</body></html>';

/**
 * Only gets called when we fail to login.
 */
function temp_login_error_handler($errno,$errstr,$file,$lineno) {
	if (ini_get('error_reporting') == 0 || error_reporting() == 0)
		return;

	pla_error(_('Could not connect to LDAP server.').'<br /><br />'.htmlspecialchars($errstr));
}
?>
