<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/password_checker.php,v 1.10.2.1 2008/01/13 05:37:01 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
include HTDOCDIR.'header.php';

echo '<body>';
$entry['hash'] = get_request('hash','REQUEST');
$entry['password'] = get_request('check_password','REQUEST');
$entry['action'] = get_request('action','REQUEST');
$entry['componentid'] = get_request('componentid','REQUEST');

if (get_request('base64','REQUEST')) {
    $entry['hash'] = base64_decode($entry['hash']);
    $entry['password'] = base64_decode($entry['password']);
}

$entry['enc_type'] = get_enc_type($entry['hash']);

echo '<div class="popup">';
printf('<h3 class="subtitle">%s</h3>',_('Password Checker Tool'));

echo '<form action="password_checker.php" method="post">';
echo '<input type="hidden" name="action" value="compare" />';

echo '<table class="forminput" width=100% border=0>';

echo '<tr>';
printf('<td class="heading">%s</td>',_('Compare'));
printf('<td><input type="%s" name="hash" id="hash" value="%s" /></td>',
	$entry['enc_type'] ? 'text' : 'password',htmlspecialchars($entry['hash']));
echo '</tr>';

echo '<tr>';
printf('<td class="heading">%s</td>',_('To'));
printf('<td><input type="password" name="check_password" value="%s" /></td>',
	htmlspecialchars($entry['password']));
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';

echo '<td><input type="submit" value="Compare" />';

if ($entry['action'] == 'compare') {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;<b>';

	if (password_check($entry['hash'],$entry['password']))
		printf('<span class="good">%s</span>',_('Passwords match!'));
	else
		printf('<span class="bad">%s</span>',_('Passwords do not match!'));

	echo '</b>';
}

echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';
echo '</div>';
echo '</body>';

if ($entry['componentid']) {
	echo '<script language="javascript">';
	printf('var c = window.opener.document.getElementById(\'%s\');',$entry['componentid']);
	printf('var h = document.getElementById(\'%s\');', 'hash');
	echo 'if (c && h) { h.value = c.value; }';
	echo '</script>';
}
?>
