<?php
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
$isHTTPS = false;

# Check if the current connection is encrypted
if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
        $isHTTPS = true;
}
# Check if a proxy server downstream does encryption for us
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL'])
== 'on') {
        $isHTTPS = true;
}

if (!$isHTTPS) {
	echo '<div style="text-align: center; color:red">';
	printf('<acronym title="%s"><b>%s: %s.</b></acronym>',
		_('You are not using \'https\'. Web browser will transmit login information in clear text.'),
		_('Warning'),_('This web connection is unencrypted'));
	echo '</div>';

	echo '<br />';
}
unset($isSecure);

# HTTP Basic Auth Form.
if ($app['server']->getAuthType() == 'http') {
	ob_end_clean();

	# When we pop up the basic athentication, we come back to this script, so try the login again.
	if ($app['server']->isLoggedIn('user')) {
		system_message(array(
			'title'=>_('Authenticate to server'),
			'body'=>_('Successfully logged into server.'),
			'type'=>'info'),
			sprintf('cmd.php?server_id=%s&refresh=SID_%s',$app['server']->getIndex(),$app['server']->getIndex()));

		die();
	}

	header(sprintf('WWW-Authenticate: Basic realm="%s"',$_SESSION[APPCONFIG]->getValue('session','http_realm')));

	if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0')
		header('HTTP/1.0 401 Unauthorized'); // http 1.0 method
	else
		header('Status: 401 Unauthorized'); // http 1.1 method

	return;

# HTML Login Form
} else {
	echo '<form action="cmd.php" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="cmd" value="login" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	echo '<input type="hidden" name="nodecode[login_pass]" value="1" />';

	if (get_request('redirect','GET',false,false))
		printf('<input type="hidden" name="redirect" value="%s" />',rawurlencode(get_request('redirect','GET')));

	echo '</div>';

	echo '<table class="forminput" style="margin-left: auto; margin-right: auto;">';

	printf('<tr><td><b>%s:</b></td></tr>',
		$app['server']->getValue('login','auth_text') ? $app['server']->getValue('login','auth_text') :
			($app['server']->getValue('login','attr') == 'dn' ? _('Login DN') : $_SESSION[APPCONFIG]->getFriendlyName($app['server']->getValue('login','attr'))));

	printf('<tr><td><input type="text" id="login" name="login" size="40" value="%s" /></td></tr>',
		$app['server']->getValue('login','attr',false) == 'dn' ? $app['server']->getValue('login','bind_id') : '');

	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	printf('<tr><td><b>%s:</b></td></tr>',_('Password'));
	echo '<tr><td><input type="password" id="password" size="40" value="" name="login_pass" /></td></tr>';
	echo '<tr><td colspan="2">&nbsp;</td></tr>';

	# If Anon bind allowed, then disable the form if the user choose to bind anonymously.
	if ($app['server']->isAnonBindAllowed())
		printf('<tr><td colspan="2"><small><b>%s</b></small> <input type="checkbox" name="anonymous_bind" onclick="form_field_toggle_enable(this,[\'login\',\'password\'],\'login\')" id="anonymous_bind_checkbox" /></td></tr>',
			_('Anonymous'));

	printf('<tr><td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="%s" /></td></tr>',
		_('Authenticate'));

	echo '</table>';
	echo '</form>';

	echo '<br/>';

	echo '<script type="text/javascript">document.getElementById("login").focus()</script>';

	if ($app['server']->isAnonBindAllowed())
		printf('<script type="text/javascript" src="%sform_field_toggle_enable.js"></script>',JSDIR);
}
?>
