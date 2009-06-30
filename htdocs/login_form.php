<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/login_form.php,v 1.29.2.7 2008/12/12 12:20:22 wurley Exp $

/**
 * Displays the login form for a server for users who specify 'cookie' or 'session' for their auth_type.
 *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @see login.php
 */
/**
 */

require './common.php';

if (! in_array($ldapserver->auth_type, array('cookie','session')))
	error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($ldapserver->auth_type)),'error','index.php');

printf('<h3 class="title">%s %s</h3>',_('Authenticate to server'),$ldapserver->name);

# Check for a secure connection
if (! isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') {
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
echo '<form action="cmd.php" method="post" name="login_form">';
echo '<input type="hidden" name="cmd" value="login" />';
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

if (get_request('redirect','GET',false,false))
	printf('<input type="hidden" name="redirect" value="%s" />',rawurlencode(get_request('redirect','GET')));

echo '<center>';
echo '<table class="forminput">';

printf('<tr><td><b>%s:</b></td></tr>',
	$ldapserver->login_attr == 'dn' ? _('Login DN') : $_SESSION[APPCONFIG]->getFriendlyName($ldapserver->login_attr));

printf('<tr><td><input type="text" id="login" name="%s" size="40" value="%s" /></td></tr>',
	$ldapserver->login_attr,
	$ldapserver->login_attr == 'dn' ? $ldapserver->login_dn : '');

echo '<tr><td colspan=2>&nbsp;</td></tr>';
printf('<tr><td><b>%s:</b></td></tr>',_('Password'));
echo '<tr><td><input type="password" id="password" size="40" value="" name="login_pass" /></td></tr>';
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

echo '<script type="text/javascript" language="javascript">document.getElementById(\'login\').focus()</script>';

if( $ldapserver->isAnonBindAllowed() ) { ?>
<script type="text/javascript" language="javascript">
<!--
	function toggle_disable_login_fields(anon_checkbox) {
		if (anon_checkbox.checked) {
			anon_checkbox.form.<?php echo $ldapserver->login_attr; ?>.disabled = true;
			anon_checkbox.form.login_pass.disabled = true;
		} else {
			anon_checkbox.form.<?php echo $ldapserver->login_attr; ?>.disabled = false;
			anon_checkbox.form.<?php echo $ldapserver->login_attr; ?>.focus();
			anon_checkbox.form.login_pass.disabled = false;
		}
	}
-->
</script>
<?php }
