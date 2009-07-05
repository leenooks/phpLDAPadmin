<?php
// $Header$

/**
 * Displays the login form for a server for users who specify 'cookie' or 'session' for their auth_type.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 * @see login.php
 */

/**
 */

require './common.php';

printf('<h3 class="title">%s %s</h3>',_('Authenticate to server'),$app['server']->getName());
echo '<br />';

# Check for a secure connection
if (! isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') {
	echo '<center>';
	echo '<span style="color:red">';
	printf('<acronym title="%s"><b>%s: %s.</b></acronym>',
		_('You are not using \'https\'. Web browser will transmit login information in clear text.'),
		_('Warning'),_('This web connection is unencrypted'));
	echo '</span>';
	echo '</center>';
}
echo '<br />';

# Login form.
echo '<form action="cmd.php" method="post" name="login_form">';
echo '<input type="hidden" name="cmd" value="login" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());

if (get_request('redirect','GET',false,false))
	printf('<input type="hidden" name="redirect" value="%s" />',rawurlencode(get_request('redirect','GET')));

echo '<center>';
echo '<table class="forminput">';

printf('<tr><td><b>%s:</b></td></tr>',
	$app['server']->getValue('login','auth_text') ? $app['server']->getValue('login','auth_text') :
		($app['server']->getValue('login','attr') == 'dn' ? _('Login DN') : $_SESSION[APPCONFIG]->getFriendlyName($app['server']->getValue('login','attr'))));

printf('<tr><td><input type="text" id="login" name="login" size="40" value="%s" /></td></tr>',
	$app['server']->getValue('login','attr',false) == 'dn' ? $app['server']->getValue('login','bind_id') : '');

echo '<tr><td colspan=2>&nbsp;</td></tr>';
printf('<tr><td><b>%s:</b></td></tr>',_('Password'));
echo '<tr><td><input type="password" id="password" size="40" value="" name="login_pass" /></td></tr>';
echo '<tr><td colspan=2>&nbsp;</td></tr>';

# If Anon bind allowed, then disable the form if the user choose to bind anonymously.
if ($app['server']->isAnonBindAllowed())
	printf('<tr><td colspan="2"><small><b>%s</b></small> <input type="checkbox" name="anonymous_bind" onclick="toggle_disable_login_fields(this)" id="anonymous_bind_checkbox" /></td></tr>',
		_('Anonymous'));

printf('<tr><td colspan="2"><center><input type="submit" name="submit" value="%s" /></center></td></tr>',
	_('Authenticate'));

echo '</table>';
echo '</center>';
echo '</form>';

echo '<script type="text/javascript" language="javascript">document.getElementById(\'login\').focus()</script>';

if ($app['server']->isAnonBindAllowed() ) {
?>
<script type="text/javascript" language="javascript">
function toggle_disable_login_fields(anon_checkbox) {
	if (anon_checkbox.checked) {
		anon_checkbox.form.login.disabled = true;
		anon_checkbox.form.password.disabled = true;
	} else {
		anon_checkbox.form.login.disabled = false;
		anon_checkbox.form.login.focus();
		anon_checkbox.form.password.disabled = false;
	}
}
</script>
<?php
}
?>
