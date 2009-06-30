<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_security_object_template.php,v 1.3 2004/05/05 12:47:54 uugdave Exp $

// customize this to your needs
$default_container = "ou=server";

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = isset( $_POST['step'] ) ? $_POST['step'] : 1;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

?>

<script language="javascript">
<!--

/*
 * Pipulates the user name field based on the first letter
 *  of the firsr name concatenated with the last name
 *  all in lower case. 
 */
function autoFillUserName( form )
{
	var first_name;
	var last_name;
	var user_name;

	first_name = form.first_name.value.toLowerCase();
	last_name = form.last_name.value.toLowerCase();

	if( last_name == '' ) {
		return false;
	}

	user_name = first_name.substr( 0,1 ) + last_name;
	form.user_name.value = user_name;
	autoFillHomeDir( form );
}

/*
 * Pipulates the home directory field based on the username provided
 */
function autoFillHomeDir( form )
{
	var user_name;
	var hime_dir;

	user_name = form.user_name.value.toLowerCase();

	home_dir = '/home/';
	home_dir += user_name;

	form.home_dir.value = home_dir;	
	
}

-->
</script>

<center><h2>New Security Object</h2></center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" id="user_form" name="user_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo $_POST['template']; ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/uid.png" /></td>
	<td class="heading">User name:</td>
	<td><input type="text" name="user_name" id="user_name" value=""
		onChange="autoFillHomeDir(this.form)" onExit="autoFillHomeDir(this.form)" /></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/lock.png" /></td>
	<td class="heading">Password:</td>
	<td><input type="password" name="user_pass1" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Password:</td>
	<td><input type="password" name="user_pass2" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Encryption:</td>
	<td><select name="encryption">
		<option>clear</option>
		<option>md5</option>
		<option>crypt</option>
		<option>sha</option>
	    </select></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td></td>
	<td class="heading">Container:</td>
	<td><input type="text" name="container" size="40"
		value="<?php if( isset( $container ) )
				echo htmlspecialchars( $container );
			     else
				echo htmlspecialchars( $default_container . ',' . $servers[$server_id]['base'] ); ?>" />
		<?php draw_chooser_link( 'user_form.container' ); ?></td>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
</table>
</center>

<?php } elseif( $step == 2 ) {

	$user_name = trim( stripslashes( $_POST['user_name'] ) );
//	$first_name = trim( stripslashes( $_POST['first_name'] ) );
//	$last_name = trim( stripslashes( $_POST['last_name'] ) );
	$password1 = stripslashes( $_POST['user_pass1'] );
	$password2 = stripslashes( $_POST['user_pass2'] );
	$encryption = stripslashes( $_POST['encryption'] );
//	$login_shell = trim( stripslashes( $_POST['login_shell'] ) );
//	$uid_number = trim( stripslashes( $_POST['uid_number'] ) );
//	$gid_number = trim( stripslashes( $_POST['group'] ) );
	$container = trim( stripslashes( $_POST['container'] ) );
//	$home_dir = trim( stripslashes( $_POST['home_dir'] ) );

	/* Critical assertions */
	$password1 == $password2 or
		pla_error( "Your passwords don't match. Please go back and try again." );
	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	$password = password_hash( $password1, $encryption );

	?>
	<center><h3>Confirm account creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'uid=' . $user_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'account','simpleSecurityObject' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
        <input type="hidden" name="attrs[]" value="uid" />
                <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($user_name);?>" />
	<input type="hidden" name="attrs[]" value="userPassword" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($password);?>" />

	<center>
	<table class="confirm">
	<tr class="even"><td class="heading">User name:</td><td><b><?php echo htmlspecialchars( $user_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading">Password:</td><td>[secret]</td></tr>
	<tr class="odd"><td class="heading">Container:</td><td><?php echo htmlspecialchars( $container ); ?></td></tr>
	</table>
	<br /><input type="submit" value="Create Account" />
	</center>

<?php } ?>
