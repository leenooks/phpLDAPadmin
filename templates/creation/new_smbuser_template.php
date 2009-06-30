<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_smbuser_template.php,v 1.15 2004/05/05 12:47:54 uugdave Exp $

	$default_container = "ou=Users";
	$default_home = "/home";

	// Common to all templates
	$server_id = $_POST['server_id'];

	$step = 1;
	if( isset($_POST['step']) )
		$step = $_POST['step'];
		
	//check if the sambaSamAccount objectClass is availaible
	if( get_schema_objectclass( $server_id, 'sambaAccount' ) == null )
		pla_error( "You LDAP server does not have schema support for the sambaAccount objectClass. Cannot continue." );

	check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
	have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
?>

<script language="javascript">
	<!--
	function autoFillUserName( form ) {
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
	function autoFillHomeDir( form ){
		var user_name;
		var home_dir;

		user_name = form.user_name.value.toLowerCase();

		home_dir = '<?php echo $default_home; ?>/';
		home_dir += user_name;
		form.home_dir.value = home_dir;

	}
	function autoFillSambaRID( form ){
		var sambaRID;
		var uidNumber;

		// TO DO:need to check if uidNumber is an integer
		uidNumber = form.uid_number.value;
		sambaRID = (2*uidNumber)+1000;
		form.samba_user_rid.value = sambaRID;
	}
	-->
	</script>

<center><h2>New Samba  User Account</h2></center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" id="user_form" name="user_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td></td>
	<td class="heading">UID Number:</td>
	<td><input type="text" name="uid_number" value="" onChange="autoFillSambaRID(this.form)" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><i>RID:</i></td>
	<td><input type="text" name="samba_user_rid" id="samba_user_rid" value="" size="7"/></td>
</tr>
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
	<td>
		<input type="hidden" name="encryption" value="crypt"/>
		<i>crypt</i>
	</td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/terminal.png" /></td>
	<td class="heading">Login Shell:</td>
        <td>
	<select name="login_shell">
           <option value="/bin/bash">/bin/bash</option>
	   <option value="/bin/csh">/bin/csh</option>
	   <option value="/bin/ksh">/bin/ksh</option>
	   <option value="/bin/tcsh">/bin/tcsh</option>
           <option value="/bin/zsh">/bin/zsh</option>
	   <option value="/bin/sh">/bin/sh</option>
	   <option value="/bin/false">/bin/false</option>
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
	<td class="heading">Unix Group:</td>
	<td><select name="group">
		<option value="1000">admins (1000)</option>
		<option value="2000">users (2000)</option>
		<option value="3000">staff (3000)</option>
		<option value="5000">guest (5000)</option>
	    </select></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Primary Group Id:</td>
        <td><input type="text" name="primary_group_id">									   
</tr>
<tr>
	<td></td>
	<td class="heading">Home Directory:</td>
	<td><input type="text" name="home_dir" value="" id="home_dir" /></td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
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
	$gid_number = trim( $_POST['group'] );
	$container = trim( $_POST['container'] );
	$home_dir = trim( $_POST['home_dir'] );
	$samba_user_rid = trim( $_POST['samba_user_rid'] );
	$samba_primary_group_id = trim( $_POST['primary_group_id'] );
	
	$sambaLMPassword="";
	$sambaNTPassword="";
	$smb_passwd_creation_success = 0;

	/* Critical assertions */
	$password1 == $password2 or
		pla_error( "Your passwords don't match. Please go back and try again." );
	0 != strlen( $uid_number ) or
		pla_error( "You cannot leave the UID number blank. Please go back and try again." );
	is_numeric( $uid_number ) or
		pla_error( "You can only enter numeric values for the UID number field. Please go back and try again." );
	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	$password = password_hash( $password1, $encryption );

        //build the mkntpwd command line string
	$sambaPassCommand = $mkntpwdCommand . " " . $password1;

        // execute this command
	$sambaPassCommandOutput = shell_exec($sambaPassCommand);
	if($sambaPassCommandOutput){
	  $sambaLMPassword = substr($sambaPassCommandOutput,0,strPos($sambaPassCommandOutput,':'));
	  $sambaNTPassword = substr($sambaPassCommandOutput,strPos($sambaPassCommandOutput,':')+1);
	  $smb_passwd_creation_success = 1;
	}

	?>
	<center><h3>Confirm account creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'uid=' . $user_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'account', 'posixAccount', 'shadowAccount' , 'sambaAccount' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />

	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($first_name);?>" />
	<input type="hidden" name="attrs[]" value="displayName" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($first_name . ' ' . $last_name);?>" />
	<input type="hidden" name="attrs[]" value="gecos" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($first_name . ' ' . $last_name);?>" />
	<input type="hidden" name="attrs[]" value="gidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($gid_number);?>" />
	<input type="hidden" name="attrs[]" value="homeDirectory" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($home_dir);?>" />
	<input type="hidden" name="attrs[]" value="loginShell" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($login_shell);?>" />
	<input type="hidden" name="attrs[]" value="acctFlags" />
		<input type="hidden" name="vals[]" value="[U          ]" />
	<input type="hidden" name="attrs[]" value="primaryGroupID" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($samba_primary_group_id);?>" />
	<input type="hidden" name="attrs[]" value="rid" />
		<input type="hidden" name="vals[]" value="<?php echo $samba_user_rid; ?>" />
	<input type="hidden" name="attrs[]" value="shadowLastChange" />
		<input type="hidden" name="vals[]" value="11778" />
	<input type="hidden" name="attrs[]" value="uid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($user_name);?>" />
	<input type="hidden" name="attrs[]" value="uidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($uid_number);?>" />
	<input type="hidden" name="attrs[]" value="userPassword" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($password);?>" />
	   <?php if( $smb_passwd_creation_success ){?>
         <input type="hidden" name="attrs[]" value="lmPassword" />
               <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($sambaLMPassword);?>" />
	       <input type="hidden" name="attrs[]" value="ntPassword" />
	       <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($sambaNTPassword);?>" />
	       <!--
               <input type="hidden" name="attrs[]" value="pwdCanChange" />
	       <input type="hidden" name="vals[]" value="0" />
	       <input type="hidden" name="attrs[]" value="pwdLastSet" />
	       <input type="hidden" name="vals[]" value="0" />
	       <input type="hidden" name="attrs[]" value="pwdMustChange" />
	       <input type="hidden" name="vals[]" value="2147483647" />
               -->
	   <?php } ?>

	<center>
	<table class="confirm">
	<tr class="even"><td class="heading">User name:</td><td><b><?php echo htmlspecialchars( $user_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading">First name:</td><td><b><?php echo htmlspecialchars( $first_name ); ?></b></td></tr>
	<tr class="even"><td class="heading">Last name:</td><td><b><?php echo htmlspecialchars( $last_name ); ?></b></td></tr>
        <tr class="odd"><td class="heading">UID Number:</td><td><?php echo htmlspecialchars( $uid_number ); ?></td></tr>
	<tr class="even"><td class="heading">Login Shell:</td><td><?php echo htmlspecialchars( $login_shell); ?></td></tr>
	<tr class="even"><td class="heading">Rid</td><td><?php echo htmlspecialchars( $samba_user_rid ); ?></td></tr>
	<tr class="odd"><td class="heading">GID Number:</td><td><?php echo htmlspecialchars( $gid_number ); ?></td></tr>
	<tr class="even"><td class="heading">Container:</td><td><?php echo htmlspecialchars( $container ); ?></td></tr>
	<tr class="odd"><td class="heading">Home dir:</td><td><?php echo htmlspecialchars( $home_dir ); ?></td></tr>
        <?php if( $smb_passwd_creation_success ){ ?>
	<tr class="even"><td class="heading">Password:</td><td>[secret]</td></tr>
	<?php } ?>
        </table>
	<br /><input type="submit" value="Create Samba Account" />
	</center>

<?php } ?>
