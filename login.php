<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/login.php,v 1.49 2005/09/25 16:11:44 wurley Exp $

/**
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me the login info
 * and I'll write two cookies, pla_login_dn_X and pla_pass_X
 * where X is the server_id. The cookie_time comes from config.php
 *
 * Note: this file uses ldap_connect() and ldap_bind() only for purposes
 *       of verifying the user-supplied DN and Password.
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
	header("Location: index.php");

$dn = isset( $_POST['login_dn'] ) ? $_POST['login_dn'] : null;
$uid = isset( $_POST['uid'] ) ? $_POST['uid'] : null;
$pass = isset( $_POST['login_pass'] ) ? $_POST['login_pass'] : null;
if ($ldapserver->isAnonBindAllowed())
	$anon_bind = isset( $_POST['anonymous_bind'] ) && $_POST['anonymous_bind'] == 'on' ? true : false;
else
	$anon_bind = false;

if( ! $anon_bind )
	strlen($pass) or pla_error( $lang['password_blank'] );

$save_auth_type = $ldapserver->auth_type;

if ($anon_bind) {
	debug_log(sprintf('Anonymous Login was posted [%s].',$anon_bind),4);
	$dn = null;
	$pass = null;

/* Checks if the login_attr option is enabled for this host,
   which allows users to login with a simple username like 'jdoe' rather
   than the fully qualified DN, 'uid=jdoe,ou=people,,dc=example,dc=com'. */
} elseif ($ldapserver->isLoginAttrEnabled()) {

	# Is this a login string (printf-style)
	if( $ldapserver->isLoginStringEnabled() ) {
		$dn = str_replace( '<username>', $uid, $ldapserver->getLoginString() );
		debug_log(sprintf('LoginStringDN: [%s]',$dn),3);

	} else {
		# This is a standard login_attr

		/* Fake the auth_type of config to do searching. This way, the admin can specify
		   the DN to use when searching for the login_attr user. */
		$ldapserver->auth_type = 'config';

		set_error_handler( 'temp_login_error_handler' );
		if ($ldapserver->login_dn)
			$ldapserver->connect(true,false);
		else
			$ldapserver->connect(true,true);
		restore_error_handler();

		if (! empty($ldapserver->login_class))
			$filter = sprintf('(&(objectClass=%s)(%s=%s))',$ldapserver->login_class,$ldapserver->login_attr,$uid);
		else
			$filter = sprintf('%s=%s',$ldapserver->login_attr,$uid);

		# Got through each of the BASE DNs and test the login.
		foreach ($ldapserver->getBaseDN() as $base_dn) {
			debug_log(sprintf('Searching LDAP with base [%s]',$base_dn),9);

			$sr = @ldap_search($ldapserver->connect(false), $base_dn, $filter, array('dn'), 0, 1);
			$result = @ldap_get_entries($ldapserver->connect(false), $sr);
			$dn = isset( $result[0]['dn'] ) ? $result[0]['dn'] : false;

			if ($dn) {
				debug_log(sprintf('Got DN [%s] for user ID [%s]',$dn,$uid),5);
				break;
			}
		}


		# If we got here then we werent able to find a DN for the login filter.
		if (! $dn)
			pla_error($lang['bad_user_name_or_password']);

		# restore the original auth_type
		$ldapserver->auth_type = $save_auth_type;
	}
}

# We fake a 'config' server auth_type to omit duplicated code
debug_log(sprintf('Setting login type to CONFIG with DN [%s]',$dn),9);

$save_auth_type = $ldapserver->auth_type;
$ldapserver->auth_type = 'config';
$ldapserver->login_dn = $dn;
$ldapserver->login_pass = $pass;

# Verify that dn is allowed to login
if (! userIsAllowedLogin($ldapserver,$dn))
	pla_error( $lang['login_not_allowed'] );

debug_log(sprintf('User is not prohibited from logging in - now bind with DN [%s]',$dn),9);

# verify that the login is good
if( is_null($dn) && is_null($pass))
	$ds = $ldapserver->connect(true,true,true);
else
	$ds = $ldapserver->connect(true,false,true);

debug_log(sprintf('Connection returned [%s]',$ds),9);

if (! is_resource($ds)) {
	if ($anon_bind)
		pla_error($lang['could_not_bind_anon']);
	else
		pla_error($lang['bad_user_name_or_password']);

	syslog_notice("Authentification FAILED for $dn");
}

$ldapserver->auth_type = $save_auth_type;
set_login_dn($ldapserver,$dn,$pass,$anon_bind) or pla_error( $lang['could_not_set_cookie'] );
set_lastactivity($ldapserver);

initialize_session_tree();
$_SESSION['tree'][$ldapserver->server_id] = array();
$_SESSION['tree_icons'][$ldapserver->server_id] = array();

if(! $anon_bind) {
	syslog_notice("Authentification successful for $dn");
}

session_write_close();

include './header.php';
?>

<body>

<script language="javascript">
	<?php if ($anon_bind && $config->GetValue('appearance','anonymous_bind_redirect_no_tree')) { ?>
		parent.location.href='search.php?server_id=<?php echo $ldapserver->server_id; ?>'
	<?php } else { ?>
		parent.left_frame.location.reload();
	<?php } ?>
</script>

<center>
<br />
<br />
<br />
<?php echo sprintf($lang['successfully_logged_in_to_server'],htmlspecialchars($ldapserver->name)); ?><br />
<?php if($anon_bind) { ?>
	(<?php echo $lang['anonymous_bind']; ?>)
<?php } ?>
<br />
</center>

</body>
</html>

<?php
/**
 * Only gets called when we fail to login.
 */
function temp_login_error_handler($errno,$errstr,$file,$lineno) {
	global $lang;
	if (ini_get('error_reporting') == 0 || error_reporting() == 0)
		return;

	pla_error($lang['could_not_connect']."<br /><br />".htmlspecialchars($errstr));
}
?>
