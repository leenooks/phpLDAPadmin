<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/login_form.php,v 1.25.4.3 2008/11/28 14:21:37 wurley Exp $

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
	pla_error(_('Error: You have an error in your config file. The only three allowed values
                                    for auth_type in the $servers section are \'session\', \'cookie\', and \'config\'. You entered \'%s\',
                                    which is not allowed. '));
if (! in_array($ldapserver->auth_type, array('cookie','session')))
	pla_error(sprintf(_('Unknown auth_type: %s'),htmlspecialchars($ldapserver->auth_type)));

include './header.php'; ?>

<body>
<?php if( $ldapserver->isAnonBindAllowed() ) { ?>
<script type="text/javascript" language="javascript">
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

<h3 class="title"><?php printf(_('Authenticate to server %s'),$ldapserver->name); ?></h3>
<br />

<?php if (! isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') { ?>
<center>
<span style="color:red">
	<acronym title="<?php echo _('You are not using \'https\'. Web browser will transmit login information in clear text.'); ?>">
		<?php echo _('Warning: This web connection is unencrypted.'); ?>
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

<tr>
<td><small>
<?php
if ($ldapserver->isLoginAttrEnabled())
	echo _('User name');
else
	echo _('Login DN');
?>
</small></td>

<td><input type="text" name="<?php echo $ldapserver->isLoginAttrEnabled() ? 'uid' : 'login_dn'; ?>" size="40" value="<?php echo $ldapserver->isLoginAttrEnabled() ? '' : $ldapserver->login_dn; ?>" /></td>
</tr>

<tr>
	<td><small><?php echo _('Password'); ?></small></td>
	<td><input type="password" size="40" value="" name="login_pass" /></td>
</tr>

<tr>
	<td colspan="2" align="center" valign="bottom">
	<input type="submit" name="submit" value="<?php echo _('Authenticate'); ?>" />
<?php if( $ldapserver->isAnonBindAllowed() ) { ?>
	&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="anonymous_bind" onclick="toggle_disable_login_fields(this)" 
	id="anonymous_bind_checkbox"/>&nbsp;
	<small><label for="anonymous_bind_checkbox"><?php echo _('Anonymous Bind'); ?></label></small>
<?php } ?>
	</td>
</tr>
</table>
</center>
</form>
</body>
</html>
