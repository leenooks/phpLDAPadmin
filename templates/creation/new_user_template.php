<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_user_template.php,v 1.26 2005/03/05 06:27:07 wurley Exp $

/*
 * TODO Add a check: If the server is configured to use auto_uid_numbers AND the 
 * mechanism is uidpool, update the uidpool when creating the entry. This may
 * need to be added to create.php (scary). Perahsp this could be a candidate
 * for the post-update event handler.
 */

// customize this to your needs

// CHANGES MK
// - unlink first name and attribute cn ("common name"), link it to givenname
// - add field common name, to be filled from first and last name, link to cn
// - to allow givenname, I added the objectClass 'inetOrgPerson'

$default_container = "ou=People";

// Common to all templates
$container = isset( $_REQUEST['container'] ) ? $_REQUEST['container'] : null;
$server_id = isset( $_REQUEST['server_id'] ) ? $_REQUEST['server_id'] : false;

// Set $redirect to pass this to create.php to have it redirect when done.
$redirect = null;
// For example, if you want to redirect back to the user creation form, set $redirect to this:
//$redirect = "creation_template.php?server_id=$server_id&template=$template_id&container=" . urlencode( $container );

// Modify this array and add/remove the corresponding objectClasses below
$object_classes = array( 'top', 'person', 'posixAccount', 'shadowAccount', 'inetOrgPerson');

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

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] . ": " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
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
	form.common_name.value = form.first_name.value + " " + form.last_name.value;
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
<h2 style="margin:0px"><?php echo $lang['t_new_user_account']; ?></h2>
<?php if( show_hints() ) { ?>
<small><img src="images/light.png" /><?php echo $lang['t_hint_customize']; ?></small><br />
<?php } ?>
<br />
</center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" id="user_form" name="user_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_REQUEST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/uid.png" /></td>
	<td class="heading"><?php echo $lang['t_first_name']; ?>:</td>
	<td><input type="text" name="first_name" id="first_name" value=""  onChange="autoFillUserName(this.form)" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_last_name']; ?>:</td>
	<td><input type="text" name="last_name" id="last_name" value="" onChange="autoFillUserName(this.form)" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_common_name']; ?>:</td>
	<td><input type="text" name="common_name" id="common_name" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_user_name']; ?>:</td>
	<td><input type="text" name="user_name" id="user_name" value=""
		onChange="autoFillHomeDir(this.form)" onExit="autoFillHomeDir(this.form)" /></td>
</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/lock.png" /></td>
	<td class="heading"><?php echo $lang['t_password']; ?>:</td>
	<td><input type="password" name="user_pass1" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_password']; ?>:</td>
	<td><input type="password" name="user_pass2" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_encryption']; ?>:</td>
	<td><select name="encryption">
		<option>clear</option>
		<option>md5</option>
		<option>smd5</option>
		<option>crypt</option>
		<option>sha</option>
		<option>ssha</option>
	    </select></td>
</tr>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/terminal.png" /></td>
	<td class="heading"><?php echo $lang['t_login_shell']; ?>:</td>
	<!--<td><input type="text" name="login_shell" value="/bin/bash" /></td>-->
	<td>
		<select name="login_shell">
		<option>/bin/bash</option>
		<option>/bin/csh</option>
		<option>/bin/ksh</option>
		<option>/bin/tcsh</option>
		<option>/bin/zsh</option>
		<option>/bin/sh</option>
		<option>/bin/rssh</option>
		<option value="/bin/false">/bin/false (no login)</option>
		</select>
	</td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_container']; ?>:</td>
	<td><input type="text" name="container" size="40"
		value="<?php if( isset( $container ) )
				echo htmlspecialchars( $container );
			     else
				echo htmlspecialchars( $default_container . ',' . $servers[$server_id]['base'] ); ?>" />
		<?php draw_chooser_link( 'user_form.container' ); ?>
	</td>
</tr>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_home_dir']; ?>:</td>
	<td><input type="text" name="home_dir" value="/home/" id="home_dir" /></td>
</tr>
<?php 
	// determining the next available uidNumber may take a moment. 
	// give them something to look at in the mean time
	flush(); 
?>
<tr>
	<td></td>
	<td class="heading"><?php echo $lang['t_uid_number']; ?>:</td>
	<?php $next_uid_number = ( auto_uid_numbers_enabled( $server_id ) ? get_next_uid_number( $ldapserver ) : false ); ?>
	<td><input type="text" name="uid_number" value="<?php echo $next_uid_number ?>" />
	<?php if( false !== $next_uid_number ) echo "<small>" . $lang['t_auto_det'] . "</small>"; ?>
	</td>
</tr>
<tr>
	<td></td>
	<?php 
    $base_dn = null;
    if( isset( $base_posix_groups ) )
        $base_dn = $base_posix_groups;
    $posix_groups = get_posix_groups( $ldapserver, $base_dn );
    $posix_groups_found = ( count( $posix_groups ) ? true : false ); ?>
	<td class="heading"><?php echo $posix_groups_found ? $lang['t_group'] : $lang['t_gid_number'] ?>:</td>
	<td>
	<?php if( $posix_groups_found ){?>
	   <select name="gid_number">
	   <?php foreach ( $posix_groups as $dn => $attrs ){
	        $group_name = ereg_replace('^.*=',"",get_rdn($dn));
            $gid_number = $attrs['gidNumber'];
	        ?>
	        <option value="<?php echo $gid_number; ?>">
                <?php echo htmlspecialchars($group_name) . " ($gid_number)"; ?>
            </option> 
       <?php } ?>
	   </select>
    <?php } else { ?><input type="text" name="gid_number" /><?php } ?>
<br />
</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="<?php echo $lang['proceed_gt']; ?>" /></center></td>
</tr>
</table>
</center>
</form>


<?php } elseif( $step == 2 ) {

	$user_name = trim( $_POST['user_name'] );
	$first_name = trim( $_POST['first_name'] );
	$common_name = trim( $_POST['common_name'] );
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
        pla_error( sprintf( $lang['t_err_field_blank'], $lang['t_user_name'] ) );
	$password1 == $password2 or
		pla_error( $lang['t_err_passwords'] );
	0 != strlen( $uid_number ) or
		pla_error( sprintf( $lang['t_err_field_blank'], $lang['t_uid_number'] ) );
	is_numeric( $uid_number ) or
		pla_error( sprintf( $lang['t_err_field_num'], $lang['t_uid_number'] ) );
	is_numeric( $gid_number ) or
		pla_error( sprintf( $lang['t_err_field_num'], $lang['t_gid_number'] ) );
	dn_exists( $ldapserver, $container ) or
		pla_error( sprintf( $lang['t_err_bad_container'], htmlspecialchars( $container ) ) );

	$password = password_hash( $password1, $encryption );


	?>
	<center><h3><?php echo $lang['t_confirm_account_creation']; ?>:</h3></center>

	<form action="create.php" method="post">
    <?php if( $redirect ) { ?>
	<input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>" />
    <?php } ?>
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'uid=' . $user_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( $object_classes ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="uid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($user_name);?>" />
	<input type="hidden" name="attrs[]" value="gn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($first_name);?>" />
	<input type="hidden" name="attrs[]" value="sn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($last_name);?>" />
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($common_name);?>" />
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
	<tr class="even"><td class="heading"><?php echo $lang['t_user_name']; ?></td><td><b><?php echo htmlspecialchars( $user_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading"><?php echo $lang['t_first_name']; ?>:</td><td><b><?php echo htmlspecialchars( $first_name ); ?></b></td></tr>
	<tr class="even"><td class="heading"><?php echo $lang['t_last_name']; ?>:</td><td><b><?php echo htmlspecialchars( $last_name ); ?></b></td></tr>
	<tr class="even"><td class="heading"><?php echo $lang['t_common_name']; ?>:</td><td><b><?php echo htmlspecialchars( $common_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading"><?php echo $lang['t_password']; ?>:</td><td><?php echo $lang['t_secret']; ?></td></tr>
	<tr class="even"><td class="heading"><?php echo $lang['t_login_shell']; ?>:</td><td><?php echo htmlspecialchars( $login_shell); ?></td></tr>
	<tr class="odd"><td class="heading"><?php echo $lang['t_uid_number']; ?>:</td><td><?php echo htmlspecialchars( $uid_number ); ?></td></tr>
	<tr class="even"><td class="heading"><?php echo $lang['t_gid_number']; ?>:</td><td><?php echo htmlspecialchars( $gid_number ); ?></td></tr>
	<tr class="odd"><td class="heading"><?php echo $lang['t_container']; ?>:</td><td><?php echo htmlspecialchars( $container ); ?></td></tr>
	<tr class="even"><td class="heading"><?php echo $lang['t_home_dir']; ?>:</td><td><?php echo htmlspecialchars( $home_dir ); ?></td></tr>
	</table>
	<br /><input type="submit" value="<?php echo $lang['t_create_account']; ?>" />
	</center>
	</form>

<?php } ?>
