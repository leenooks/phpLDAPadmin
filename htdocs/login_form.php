<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/login_form.php,v 1.25 2005/07/22 05:47:44 wurley Exp $

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

if (! $ldapserver->auth_type)
	pla_error($lang['error_auth_type_config']);
if (! in_array($ldapserver->auth_type, array('cookie','session')))
	pla_error(sprintf($lang['unknown_auth_type'],htmlspecialchars($ldapserver->auth_type)));

include './header.php'; ?>

<body>
<?php if( $ldapserver->isAnonBindAllowed() ) { ?>
<script language="javascript">
<!--
	function toggle_disable_login_fields( anon_checkbox )
	{
		if( anon_checkbox.checked ) {
			anon_checkbox.form.<?php echo $ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn'; ?>.disabled = true;
			anon_checkbox.form.login_pass.disabled = true;
		} else {
			anon_checkbox.form.<?php echo $ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn'; ?>.disabled = false;
			anon_checkbox.form.login_pass.disabled = false;
		}
	}
-->
</script>
<?php } ?>

<h3 class="title"><?php printf($lang['authenticate_to_server'],$ldapserver->name); ?></h3>
<br />

<?php if (! isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') { ?>
<center>
<span style="color:red">
	<acronym title="<?php echo $lang['not_using_https']; ?>">
		<?php echo $lang['warning_this_web_connection_is_unencrypted']; ?>
	</acronym>
</span>
<br />
</center>
<?php } ?>

<br />

<form action="login.php" method="post" name="login_form">
<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />

<?php if( isset( $_GET['redirect'] ) ) { ?>
<input type="hidden" name="redirect" value="<?php echo rawurlencode( $_GET['redirect'] ) ?>" />
<?php } ?>

<center>
<table class="login">

<?php if( $ldapserver->isAnonBindAllowed() ) { ?>
<tr>
	<td colspan="2"><small><label for="anonymous_bind_checkbox"><?php echo $lang['anonymous_bind']; ?></label></small> <input type="checkbox" name="anonymous_bind" onclick="toggle_disable_login_fields(this)" id="anonymous_bind_checkbox"/></td>
</tr>
<?php } ?>

<tr>
<td><small>
<?php
if ($ldapserver->isLoginAttrEnabled())
	echo $lang['user_name'];
else
	echo $lang['login_dn'];
?>
</small></td>

<td><input type="text" name="<?php echo $ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn'; ?>" size="40" value="<?php echo $ldapserver->isLoginAttrEnabled() ? '' : $ldapserver->login_dn; ?>" /></td>
</tr>

<tr>
	<td><small><?php echo $lang['password']; ?></small></td>
	<td><input type="password" size="40" value="" name="login_pass" /></td>
</tr>

<tr>
	<td colspan="2"><center><input type="submit" name="submit" value="<?php echo $lang['authenticate']; ?>" /></center></td>
</tr>
</table>
</center>
</form>
</body>
</html>
