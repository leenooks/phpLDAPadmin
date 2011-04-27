<?php
/**
 * Check the password used by an entry.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$www['page'] = new page();

$request = array();
$request['componentid'] = get_request('componentid','REQUEST');
$request['hash'] = get_request('hash','REQUEST');
$request['password'] = get_request('check_password','REQUEST');
$request['action'] = get_request('action','REQUEST');
$request['attribute'] = get_request('attr','REQUEST');

if (get_request('base64','REQUEST')) {
	$request['hash'] = base64_decode($request['hash']);
	$request['password'] = base64_decode($request['password']);
}

$request['enc_type'] = get_enc_type($request['hash']);

printf('<h3 class="subtitle">%s</h3>',_('Password Checker Tool'));

echo '<form action="password_checker.php" method="post">';
echo '<input type="hidden" name="action" value="compare" />';
printf('<input type="hidden" name="attr" value="%s" />',$request['attribute']);

echo '<table class="forminput" width="100%" border="0">';

echo '<tr>';
printf('<td class="heading">%s</td>',_('Compare'));
printf('<td><input type="%s" name="hash" id="hash" value="%s" /></td>',
	(obfuscate_password_display($request['enc_type']) ? 'password' : 'text'),htmlspecialchars($request['hash']));
echo '</tr>';

echo '<tr>';
printf('<td class="heading">%s</td>',_('To'));
printf('<td><input type="password" name="check_password" value="%s" /></td>',
	htmlspecialchars($request['password']));
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';

echo '<td><input type="submit" value="Compare" />';

if ($request['action'] == 'compare') {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;<b>';

	if (password_check($request['hash'],$request['password'],$request['attribute']))
		printf('<span class="good">%s</span>',_('Passwords match!'));
	else
		printf('<span class="bad">%s</span>',_('Passwords do not match!'));

	echo '</b>';
}

echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';

# Pull our password from the form that opened this window.
if ($request['componentid']) {
	echo '<script type="text/javascript">';
	printf('var c = window.opener.document.getElementById("%s");',$request['componentid']);
	printf('var h = document.getElementById("%s");','hash');
	echo 'if (c && h) { h.value = c.value; }';
	echo '</script>';
}

# Capture the output and put into the body of the page.
$www['body'] = new block();
$www['body']->SetBody(ob_get_contents());
$www['page']->block_add('body',$www['body']);
ob_end_clean();

# Render the popup.
$www['page']->display(array('CONTROL'=>false,'FOOT'=>false,'HEAD'=>false,'TREE'=>false));
?>
