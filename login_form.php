<?php 

/*
 * login_form.php
 * Displays the login form for a server for users who specify
 * 'form' for their auth_type.
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require 'config.php';
require_once 'functions.php';

$server_id = $_GET['server_id'];
$server = $servers[$server_id];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );

?>

<?php include 'header.php'; ?>
<body>

<script language="javascript">
<!--
	function toggle_disable_login_fields( anon_checkbox )
	{
		if( anon_checkbox.checked) {
			anon_checkbox.form.login_dn.disabled = true;
			anon_checkbox.form.login_pass.disabled = true;
		} else {
			anon_checkbox.form.login_dn.disabled = false;
			anon_checkbox.form.login_pass.disabled = false;
		}
	}
-->
</script>

<center>
<h3 class="title">Authenticate to server <b><?php echo $servers[$server_id]['name']; ?></b></h3>
<br />

<?php  if( $_SERVER['SERVER_PORT'] != 443 ) { ?>

<span style="color:red">Warning: this is an insecure (non-SSL) connection!<br />
 SSL is recommended when transmitting sensitive passwords.</span>
<br />

<?php  } ?>

<br />

<form action="login.php" method="post" name="login_form">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<?php if( $_GET['redirect'] ) { ?>
	<input type="hidden" name="redirect" value="<?php echo rawurlencode( $_GET['redirect'] ) ?>" />
<?php } ?>
<center>
<table class="login">
<tr>
</tr>
<tr>
	<td colspan="2"><small>Anonymous Bind</small> <input type="checkbox" name="anonymous_bind" onclick="toggle_disable_login_fields(this)" /></td>
</tr>
<tr>
	<td><small>Login <acronym title="Distinguished Name">DN</acronym></small></td>
	<td><input type="text" name="login_dn" size="40" value="<?php echo $servers[$server_id]['login_dn']; ?>" name="login_dn" /></td>
</tr>
<tr>
	<td><small>Password</small></td>
	<td><input type="password" name="login_pass" size="40" value="" name="login_pass" /></td>
</tr>
<tr>
	<td colspan="2"><center><input type="submit" name="submit" value="Authenticate" /></center></td>
</tr>
</table>
</form>
</center>
