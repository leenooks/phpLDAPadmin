<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/login_form.php,v 1.28 2006/10/29 11:44:36 wurley Exp $

/**
 * Displays the login form for a server for users who specify 'cookie' or 'session' for their auth_type.
 *
 * Variables that come in via common.php
 * - server_id
 *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @see login.php
 */
/**
 */

require './common.php';

if (! in_array($ldapserver->auth_type, array('cookie','session')))
	pla_error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($ldapserver->auth_type)));

include './header.php';

echo '<body>';
printf('<h3 class="title">%s %s</h3>',_('Authenticate to server'),$ldapserver->name);

# Check for a secure connection
if (! isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
	echo '<br />';
	echo '<center>';
	echo '<span style="color:red">';
	printf('<acronym title="%s"><b>%s: %s.</b></acronym>',
		_('You are not using \'https\'. Web browser will transmit login information in clear text.'),
		_('Warning'),_('This web connection is unencrypted'));
	echo '</span>';
	echo '</center>';
	echo '<br />';
}

# Login form.
echo '<form action="login.php" method="post" name="login_form">';
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

if (isset($_GET['redirect']))
	printf('<input type="hidden" name="redirect" value="%s" />',rawurlencode($_GET['redirect']));

echo '<center>';
echo '<table class="login">';

printf('<tr><td><b>%s</b></td></tr>',$ldapserver->isLoginAttrEnabled() ? _('Login Name') : _('Login DN'));

printf('<tr><td><input type="text" id="pla_login" name="%s" size="40" value="%s" /></td></tr>',
	$ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn',
	$ldapserver->isLoginAttrEnabled() ? '' : $ldapserver->login_dn);

echo '<tr><td colspan=2>&nbsp;</td></tr>';
printf('<tr><td><b>%s</b></td></tr>',_('Password'));
echo '<tr><td><input type="password" id="pla_pass" size="40" value="" name="login_pass" /></td></tr>';
echo '<tr><td colspan=2>&nbsp;</td></tr>';

# If Anon bind allowed, then disable the form if the user choose to bind anonymously.
if ($ldapserver->isAnonBindAllowed())
	printf('<tr><td colspan="2"><small><b>%s</b></small> <input type="checkbox" name="anonymous_bind" onclick="toggle_disable_login_fields(this)" id="anonymous_bind_checkbox" /></td></tr>',
		_('Anonymous'));

printf('<tr><td colspan="2"><center><input type="submit" name="submit" value="%s" /></center></td></tr>',
	_('Authenticate'));

echo '</table>';
echo '</center>';
echo '</form>';

if( $ldapserver->isAnonBindAllowed() ) { ?>
<script type="text/javascript" language="javascript">
<!--
	function toggle_disable_login_fields(anon_checkbox) {
		if (anon_checkbox.checked) {
			anon_checkbox.form.<?php echo $ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn'; ?>.disabled = true;
			anon_checkbox.form.login_pass.disabled = true;
		} else {
			anon_checkbox.form.<?php echo $ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn'; ?>.disabled = false;
			anon_checkbox.form.login_pass.disabled = false;
		}
	}
-->
</script>
<?php }

echo '</body>';
echo '</html>';
?>
