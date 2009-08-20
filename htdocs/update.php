<?php
/**
 * Updates or deletes a value from a specified attribute for a specified dn.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 * @see update_confirm.php
 */

/**
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','REQUEST',true);

# If cancel was submited, got back to the edit display.
if (get_request('cancel','REQUEST')) {
	header(sprintf('Location: cmd.php?cmd=template_engine&server_id=%s&dn=%s',
		$app['server']->getIndex(),rawurlencode($request['dn'])));

	die();
}

if (! $request['dn'] || ! $app['server']->dnExists($request['dn']))
	error(sprintf(_('The entry (%s) does not exist.'),$request['dn']),'error','index.php');

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->setDN($request['dn']);
$request['page']->accept();
$request['template'] = $request['page']->getTemplate();

# Perform the modification
$result = $app['server']->modify($request['dn'],$request['template']->getLDAPmodify());

if ($result) {
	# Fire the post modification event to the user's custom callback function.
	$mustRelogin = false;

	foreach ($request['template']->getLDAPmodify() as $attr_name => $val) {
		/* Was this a user's password modification who is currently
		 * logged in? If so, they need to logout and log back in
		 * with the new password. */
		if (($attr_name == 'userpassword') &&
			in_array($app['server']->getValue('login','auth_type'),array('cookie','session')) &&
			pla_compare_dns($app['server']->getLogin(),$request['dn']) === 0)

			$mustRelogin = true;
	}

	# If the user password was changed, not tell the to relogin.
	if ($mustRelogin) {
			$app['server']->logout('user');
			unset_lastactivity($app['server']);
			echo '<body>';

			echo '<br />';
			echo '<center>';
			printf('<b>%s</b>',_('Modification successful!'));
			echo '<br /><br />';
			echo _('Since you changed your password, you must now login again with your new password.');
			echo '<br />';
			printf('<a href="cmd.php?cmd=login_form&server_id=%s">%s...</a>',$app['server']->getIndex(), _('Login'));
			echo '</center>';
			echo '</body>';
			echo '</html>';

			exit;
	}

	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
		$app['server']->getIndex(),rawurlencode($request['dn']));

	foreach ($request['template']->getLDAPmodify() as $attr => $junk)
		$redirect_url .= sprintf('&modified_attrs[]=%s',$attr);

	header("Location: $redirect_url");
	die();
}
?>
