<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/login_form.php,v 1.18 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/*
 * login_form.php
 * Displays the login form for a server for users who specify
 * 'cookie' or 'session' for their auth_type.
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require 'common.php';

$server_id = isset( $_GET['server_id'] ) ? $_GET['server_id'] : null;

if( $server_id != null ) {
		check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
}

$server = $servers[$server_id];

isset( $servers[ $server_id ][ 'auth_type' ] ) or pla_error( $lang['error_auth_type_config'] );
$auth_type = $servers[ $server_id ][ 'auth_type' ];
if( $auth_type != 'cookie' && $auth_type != 'session' )
    pla_error( sprintf( $lang['unknown_auth_type'], htmlspecialchars( $auth_type ) ) );

include 'header.php'; ?>

<body>

<script language="javascript">
<!--
	function toggle_disable_login_fields( anon_checkbox )
	{
		if( anon_checkbox.checked ) {
			anon_checkbox.form.<?php echo login_attr_enabled( $server_id ) ? 'uid' : 'login_dn'; ?>.disabled = true;
			anon_checkbox.form.login_pass.disabled = true;
		} else {
			anon_checkbox.form.<?php echo login_attr_enabled( $server_id ) ? 'uid' : 'login_dn'; ?>.disabled = false;
			anon_checkbox.form.login_pass.disabled = false;
		}
	}
-->
</script>

<center>
<h3 class="title"><?php echo sprintf( $lang['authenticate_to_server'], $servers[$server_id]['name'] ); ?></b></h3>
<br />

<?php if( ! isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] != 'on' ) { ?>

<center>
<span style="color:red">
	<acronym title="<?php echo $lang['not_using_https']; ?>">
		<?php echo $lang['warning_this_web_connection_is_unencrypted']; ?>
	</acronym>
 </span>
 <br />
 </center>

<?php  } ?>

<br />

<form action="login.php" method="post" name="login_form">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<?php if( isset( $_GET['redirect'] ) ) { ?>
	<input type="hidden" name="redirect" value="<?php echo rawurlencode( $_GET['redirect'] ) ?>" />
<?php } ?>
<center>
<table class="login">
<tr>
</tr>
<tr>
	<td colspan="2"><small><label for="anonymous_bind_checkbox"><?php echo $lang['anonymous_bind']; ?></label></small> <input type="checkbox" name="anonymous_bind" onclick="toggle_disable_login_fields(this)" id="anonymous_bind_checkbox"/></td>
</tr>
<tr>
<td><small><?php 
		if ( login_attr_enabled( $server_id ) ) 
			echo $lang['user_name'];
		else
			echo $lang['login_dn'];
	?></small></td>
<td><input type="text" name="<?php echo login_attr_enabled( $server_id ) ? 'uid' : 'login_dn'; ?>" size="40" value="<?php echo login_attr_enabled( $server_id ) ? '' : $servers[$server_id]['login_dn']; ?>" /></td>
</tr>
<tr>
	<td><small><?php echo $lang['password']; ?></small></td>
	<td><input type="password" name="login_pass" size="40" value="" name="login_pass" /></td>
</tr>
<tr>
	<td colspan="2"><center><input type="submit" name="submit" value="<?php echo $lang['authenticate']; ?>" /></center></td>
</tr>
</table>
</form>
</center>
