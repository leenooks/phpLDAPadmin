<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_user_template.php,v 1.17 2004/05/08 13:31:04 uugdave Exp $

/*
 * TODO Add a check: If the server is configured to use auto_uid_numbers AND the 
 * mechanism is uidpool, update the uidpool when creating the entry. This may
 * need to be added to create.php (scary). Perahsp this could be a candidate
 * for the post-update event handler.
 */

// customize this to your needs
$default_container = "ou=People";

// Common to all templates
$container = isset( $_POST['container'] ) ? $_POST['container'] : null;
$server_id = isset( $_POST['server_id'] ) ? $_POST['server_id'] : false;

// Modify this array and add/remove the corresponding attributes below
$object_classes = array( 'top', 'person', 'posixAccount', 'shadowAccount' );

// A list of default attributes/values to create with this new user
$default_attributes = array( 
						'shadowMin' => -1, 
						'shadowMax' => 999999, 
						'shadowWarning' => 7, 
						'shadowInactive' => -1, 
						'shadowExpire' => -1, 
						'shadowFlag' => 0 
					);

// Unique to this template
$step = 1;
if( isset($_POST['step']) )
    $step = $_POST['step'];

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
 * Populates the home directory field based on the username provided
 */
function autoFillHomeDir( form )
{
	var user_name;
	var home_dir;

	user_name = form.user_name.value.toLowerCase();

	home_dir = '/home/';
	home_dir += user_name;

	form.home_dir.value = home_dir;	
	
}

-->
</script>

<center>
<h2 style="margin:0px">New User Account</h2>
<?php if( show_hints() ) { ?>
<small><img src="images/light.png" />Hint: To customize this template, edit the file templates/creation/new_user_template.php<br />
<?php } ?>
<br />
</center>

<?php if( $step == 1 ) { 
  $base_dn = null;
  if( isset( $base_posix_groups ) )
    $base_dn = $base_posix_groups;
  $posix_groups = get_posix_groups( $server_id , $base_dn );
?>

<form action="creation_template.php" method="post" id="user_form" name="user_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/uid.png" /></td>
	<td class="heading">First name:</td>
	<td><input type="text" name="first_name" id="first_name" value=""  onChange="autoFillUserName(this.form)" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Last name:</td>
	<td><input type="text" name="last_name" id="last_name" value="" onChange="autoFillUserName(this.form)" /></td>
</tr>
<tr>
	<td></td>
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
		<option>smd5</option>
		<option>crypt</option>
		<option>sha</option>
		<option>ssha</option>
	    </select></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/terminal.png" /></td>
	<td class="heading">Login Shell:</td>
	<!--<td><input type="text" name="login_shell" value="/bin/bash" /></td>-->
	<td>
		<select name="login_shell">
		<option>/bin/bash</option>
		<option>/bin/csh</option>
		<option>/bin/ksh</option>
		<option>/bin/tcsh</option>
		<option>/bin/zsh</option>
		<option>/bin/sh</option>
		</select>
	</td>
</tr>
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
	<td></td>
	<td class="heading">Home Directory:</td>
	<td><input type="text" name="home_dir" value="/home/" id="home_dir" /></td>
</tr>
<?php 
	// determining the next available uidNumber may take a moment. 
	// give them something to look at in the mean time
	flush(); 
?>
<tr>
	<td></td>
	<td class="heading">UID Number:</td>
	<?php $next_uid_number = ( auto_uid_numbers_enabled( $server_id ) ? get_next_uid_number( $server_id ) : false ); ?>
	<td><input type="text" name="uid_number" value="<?php echo $next_uid_number ?>" />
	<?php if( false !== $next_uid_number ) echo "<small>(automatically determined)</small>"; ?>
	</td>
</tr>
<tr>
	<td></td>
	    <?php $posix_groups_found = ( is_array( $posix_groups )?1:0 );?>
	<td class="heading"><?php echo $posix_groups_found?"Group":"GID Number"?>:</td>
	    <td>
	    <?php if( $posix_groups_found ){?>
	       <select name="gid_number">
	       <?php foreach ( $posix_groups as $dn => $attrs ){
		 $group_name = ereg_replace('^.*=',"",get_rdn($dn));
	        ?>
		 <option value="<?php echo $attrs['gidNumber'];?>"><?php echo $group_name;?> </option> 
		 <?php } ?>
	       </select>
		   <?php }else{ ?><input type="text" name="gid_number"><?php } ?>
<br />
</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed" /></td>
</tr>
</table>
</center>

<?php } elseif( $step == 2 ) {

	$user_name = trim( $_POST['user_name'] );
	$first_name = trim( $_POST['first_name'] );
	$last_name = trim( $_POST['last_name'] );
	$password1 = $_POST['user_pass1'];
	$password2 = $_POST['user_pass2'];
	$encryption = $_POST['encryption'];
	$login_shell = trim( $_POST['login_shell'] );
	$uid_number = trim( $_POST['uid_number'] );
	$gid_number = trim( $_POST['gid_number'] );
	$container = trim( $_POST['container'] );
	$home_dir = trim( $_POST['home_dir'] );

	/* Critical assertions */
    if( ! trim( $user_name ) )
        pla_error( "You left the user name field blank" );
	$password1 == $password2 or
		pla_error( "Your passwords don't match. Please go back and try again." );
	0 != strlen( $uid_number ) or
		pla_error( "You cannot leave the UID number blank. Please go back and try again." );
	is_numeric( $uid_number ) or
		pla_error( "You can only enter numeric values for the UID number field. Please go back and try again." );
	is_numeric( $gid_number ) or
		pla_error( "You can only enter numeric values for the GID number field. Please go back and try again." );
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
	<?php $object_classes = rawurlencode( serialize( $object_classes ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="uid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($user_name);?>" />
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($first_name);?>" />
	<input type="hidden" name="attrs[]" value="sn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($last_name);?>" />
	<input type="hidden" name="attrs[]" value="userPassword" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($password);?>" />
	<input type="hidden" name="attrs[]" value="loginShell" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($login_shell);?>" />
	<input type="hidden" name="attrs[]" value="uidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($uid_number);?>" />
	<input type="hidden" name="attrs[]" value="gidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($gid_number);?>" />
	<input type="hidden" name="attrs[]" value="homeDirectory" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($home_dir);?>" />

	<?php foreach( $default_attributes as $default_attr => $default_val ) { ?>

		<!-- default attribute, auto-added based on $default_attributes array specified in new_user_template.php -->
		<input type="hidden" name="attrs[]" value="<?php echo htmlspecialchars($default_attr); ?>" />
			<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($default_val);?>" />

	<?php } ?>

	<center>
	<table class="confirm">
	<tr class="even"><td class="heading">User name:</td><td><b><?php echo htmlspecialchars( $user_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading">First name:</td><td><b><?php echo htmlspecialchars( $first_name ); ?></b></td></tr>
	<tr class="even"><td class="heading">Last name:</td><td><b><?php echo htmlspecialchars( $last_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading">Password:</td><td>[secret]</td></tr>
	<tr class="even"><td class="heading">Login Shell:</td><td><?php echo htmlspecialchars( $login_shell); ?></td></tr>
	<tr class="odd"><td class="heading">UID Number:</td><td><?php echo htmlspecialchars( $uid_number ); ?></td></tr>
	<tr class="even"><td class="heading">GID Number:</td><td><?php echo htmlspecialchars( $gid_number ); ?></td></tr>
	<tr class="odd"><td class="heading">Container:</td><td><?php echo htmlspecialchars( $container ); ?></td></tr>
	<tr class="even"><td class="heading">Home dir:</td><td><?php echo htmlspecialchars( $home_dir ); ?></td></tr>
	</table>
	<br /><input type="submit" value="Create Account" />
	</center>

<?php } ?>
