<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_smb3_user_template.php,v 1.20 2004/05/08 11:41:04 xrenard Exp $

$samba3_domains = get_samba3_domains();

$default_container = "ou=Users";
$default_home = "/home";

// Common to all templates
$server_id = $_POST['server_id'];

$now = time();
$step = 1;
if( isset($_POST['step']) )
     $step = $_POST['step'];
     
     //check if the sambaSamAccount objectClass is availaible
     if( get_schema_objectclass( $server_id, 'sambaSamAccount' ) == null )
     pla_error( "You LDAP server does not have schema support for the sambaSamAccount objectClass. Cannot continue." );
     
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
  
  //check if the UID Number is a number
  if(!isNaN(form.uid_number.value)){
    uidNumber = form.uid_number.value;
    sambaRID = (2*uidNumber)+1000;
    form.samba3_user_rid.value = sambaRID;
  }
  // otherwise (re)set the samba rid value to an empty string
  else{
    form.samba3_user_rid.value = "";
  }
}
function autoFillSambaGroupRID( form ){
  var gidNumber;

   gidNumber = form.gid_number.value;
   if(  form.samba_group[0].checked ){
     form.custom_rid.value = "";
   }
   else {
    form.custom_rid.value = (2*gidNumber)+1001;
   }
}
-->
</script>

<center><h2>New Samba3 User Account</h2></center>

<?php if( $step == 1 ) { 
 $base_dn = null;
 
  if( isset( $samba_base_groups ) )
    $base_dn = $samba_base_groups;
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
	<td></td>
	<td class="heading">UID Number:</td>
        <?php $next_uid_number = get_next_uid_number( $server_id ); ?>
	<td><input type="text" name="uid_number" value="<?php echo $next_uid_number; ?>"  onChange="autoFillSambaRID(this.form)" />
	   <?php if( false !== $next_uid_number ) echo "<small>(automatically determined)</small>"; ?>
       </td>
</tr>
<tr>
	<td></td>
	<td class="heading"><i>Samba SID:</i></td>
	<td><select name="samba3_domain_sid">
<?php foreach($samba3_domains as $samba3_domain) ?>
      <option value="<?php echo $samba3_domain['sid'] ?>"><?php echo $samba3_domain['sid'] ?></option>
</select> - <input type="text" name="samba3_user_rid" id="samba3_user_rid" value="" size="7" onfocus="autoFillSambaRID(this.form)"/></td>
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
		onChange="autoFillHomeDir(this.form)" onExit="autoFillHomeDir(this.form,this)" /></td>
</tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
	<td><img src="images/lock.png" /></td>
	<td class="heading">User Password:</td>
	<td><input type="password" name="user_pass1" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">User Password:</td>
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
<tr valign="top">
	<td></td>
	<td class="heading">Samba Password:</td>
	<td>
            <div style="font-size: 90%;">
             <div>
               <input type="radio" name="samba_pass_mode" checked value="1" />
               <span style="font-size: 90%;">Use Unix Password</span>
             </div>
             <div>
               <input type="radio" name="samba_pass_mode" value="2" />
               <span style="font-size: 90%;">Null Password</span> 
             </div>
	<div>
               <input type="radio" name="samba_pass_mode" value="3" />
               <span style="font-size: 90%;">No Password</span> 
             </div>
             <div>
		   <table>
                     <tr valign="top">
         <td><input type="radio" name="samba_pass_mode" style="margin-left:0px;" value="4" />              <span style="font-size: 90%;">New Password :</span> </td>
        <td>
         <div><input type="password" name="samba_pass1" /></div>
<div style="padding-top:3px;" ><input type="password" name="samba_pass2"  /></div>
</td>
</tr>
</table>
</div>
   <div>
    <table>
       <tr valign="top" colspan="2">
         <td><input type="radio" name="samba_pass_mode" style="margin-left:0px;" value="5" />              <span style="font-size: 90%;">Existing Password :</span> </td>
      </tr>
      <tr valign="top">
        <td colspan="2" style="padding-left:25px;">
         <div>
          <span style="font-size:90%;">LM Password: </span>
          <span style="padding-left:20px;"><input type="text" name="lmpassword" /></span>
         </div>
          <div style="padding-top:3px;" >
          <span style="font-size: 90%;">NT Password: </span>
          <span style="padding-left:20px;"><input type="text" name="ntpassword"  /></span>
        </div>
       </td>
      </tr>
   </table>
</div>
 </div>
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
	    <?php $posix_groups_found = ( is_array( $posix_groups )?1:0 );?>
	<td class="heading"><?php echo $posix_groups_found?"Unix Group":"GID Number"?>:</td>
	    <td>
	    <?php if( $posix_groups_found ){?>
	       <select name="gid_number" onchange="autoFillSambaGroupRID( this.form )">
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
<tr valign="top">
	<td></td>
	<td class="heading">Windows Group:</td>
	<td>
          <div style="font-size: 90%">
            <div>
              <div><input type="radio" name="samba_group" value="1" checked onchange="autoFillSambaGroupRID( this.form )" /> <span style="text-decoration:underline;">Built-In:</span></div>
               <div style="padding-top: 3px;">
                 <select name="builtin_sid">
                     <optgroup label="Local Group">
   <?php foreach( $built_in_local_groups as $sid => $name ){ ?>
                         <option value="<?php echo $sid; ?>"><?php echo $name; ?> (<?php echo $sid; ?>)</option>  <?php  } ?>
	              </optgroup>
                      <optgroup  label="Global Groups">
			<?php foreach($samba3_domains as $samba3_domain) { ?>
			      <option value="<?php echo $samba3_domain['sid']; ?>-512">Domain Admins (<?php echo $samba3_domain['sid']; ?>-512)</option>
			      <option value="<?php echo $samba3_domain['sid']; ?>-513">Domain Users  (<?php echo $samba3_domain['sid']; ?>-513)</option>
			      <option value="<?php echo $samba3_domain['sid']; ?>-514">Domain Guests (<?php echo $samba3_domain['sid']; ?>-514)</option>
			<?php } ?>
                       </optgroup>
	         </select>
               </div>
            </div>
            <div style="padding-top:10px;">
               <div><input type="radio" name="samba_group" value="2" onchange="autoFillSambaGroupRID( this.form )"> <span style="text-decoration:underline;">Custom:</span></div>
               <div style="padding-top:3px;">
                  <select name="custom_domain_sid">
<?php foreach($samba3_domains as $samba3_domain) { ?>
                	<option value="<?php echo $samba3_domain['sid']; ?>"><?php echo $samba3_domain['sid']; ?></option>
<?php } ?>
                   </select>
                   <input type="text" name="custom_rid" size="15" />
                </div>
            </div>
         </div>
    </td>
</tr>
<tr>
	<td></td>
	<td class="heading">Home Directory:</td>
	<td><input type="text" name="home_dir" value="" id="home_dir" /></td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
<tr height="10"><td colspan="3"></tr>
<tr class="spacer"><td colspan="3"></tr>
<tr>
<td><small><b>Note: </b></small></td>
<td colspan="2"><small>To change the value(s) of the samba domain sid, please edit the file :<br /> <code>templates/template_config.php</small></code></td>
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
	$samba3_primary_group_sid = NULL;
	$samba3_group = isset( $_POST['samba_group'] )? $_POST['samba_group'] : 0 ;
	switch($samba3_group){
	case 1:
	  isset( $_POST['builtin_sid'] ) or pla_error("No built-in group selected. Please go back and try again" );
	  $samba3_primary_group_sid = $_POST['builtin_sid'];
	  break;
	case 2:
	  ! empty( $_POST['custom_rid'] ) or pla_error( "The value of the samba RID was not specified. Please go back and try again" );
	  $samba3_primary_group_sid = $_POST['custom_domain_sid'] . "-" .$_POST['custom_rid'];
	  break;
	default:
	  pla_error( "No samba group select. Please go back and try again" );
	}

	

	
	$samba3_user_rid = trim( $_POST['samba3_user_rid'] );
      	$samba3_domain_sid = trim( $_POST['samba3_domain_sid'] );
	
	$samba_password_mode = trim( $_POST['samba_pass_mode'] );

	
	$clearSambaPassword = "";
	$sambaLMPassword = "";
	$sambaNTPassword = "";
	$accountFlag= "[U          ]";
	$sambaPasswordRequired = 0;
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

	switch($samba_password_mode){
	case 1:
	  $clearSambaPassword = trim( $password1 );
	  $sambaPasswordRequired = 1;
	  break;
	case 2:
	  $accountFlag= "[NU         ]";
	  break;
	case 3:
	  // do nothing
	  break;
	case 4:
	  $_POST["samba_pass1"] == $_POST["samba_pass2"] or pla_error ( "Yours samba passwords don't match. Please go back and try again" );
	  $clearSambaPassword = trim($_POST["samba_pass1"]);
	  $sambaPasswordRequired = 1;
	  break;
	case 5:
	  if( $_POST["ntpassword"] != "" && $_POST["lmpassword"] !=""){
	    $sambaLMPassword = $_POST["lmpassword"];
	    $sambaNTPassword = $_POST["ntpassword"];
	    $smb_passwd_creation_success = 1;
	  }
	  break;
	}
	$password = password_hash( $password1, $encryption );
	
	if ( $sambaPasswordRequired ){
	  $mkntPassword = new MkntPasswdUtil();
	  if( $mkntPassword->createSambaPasswords( $clearSambaPassword ) ){
	    $sambaLMPassword = $mkntPassword->getsambaLMPassword();
	    $sambaNTPassword = $mkntPassword->getsambaNTPassword();
	    $smb_passwd_creation_success = 1;
	  }
	}

	?>
	<center><h3>Confirm account creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'uid=' . $user_name . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'account', 'posixAccount', 'shadowAccount' , 'sambaSamAccount' ) ) ); ?>

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
	<input type="hidden" name="attrs[]" value="sambaAcctFlags" />
		<input type="hidden" name="vals[]" value="<?php echo $accountFlag;?>" />
	<input type="hidden" name="attrs[]" value="sambaPrimaryGroupSID" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($samba3_primary_group_sid);?>" />
	<input type="hidden" name="attrs[]" value="sambaSID" />
		<input type="hidden" name="vals[]" value="<?php echo $samba3_domain_sid."-".$samba3_user_rid; ?>" />
	<input type="hidden" name="attrs[]" value="shadowLastChange" />
		<input type="hidden" name="vals[]" value="11778" />
	<input type="hidden" name="attrs[]" value="uid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($user_name);?>" />
	<input type="hidden" name="attrs[]" value="uidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($uid_number);?>" />
	<input type="hidden" name="attrs[]" value="userPassword" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($password);?>" />
	   <?php if( $smb_passwd_creation_success ){?>
         <input type="hidden" name="attrs[]" value="sambaLMPassword" />
               <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($sambaLMPassword);?>" />
	       <input type="hidden" name="attrs[]" value="sambaNTPassword" />
	       <input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($sambaNTPassword);?>" />
               <input type="hidden" name="attrs[]" value="sambaPwdCanChange" />
	       <input type="hidden" name="vals[]" value="<?php echo $now ?>" />
	       <input type="hidden" name="attrs[]" value="sambaPwdLastSet" />
	       <input type="hidden" name="vals[]" value="<?php echo $now ?>" />
	       <input type="hidden" name="attrs[]" value="sambaPwdMustChange" />
	       <input type="hidden" name="vals[]" value="2147483647" />

	   <?php } ?>

	<center>
	<table class="confirm">
	<tr class="even"><td class="heading">User name:</td><td><b><?php echo htmlspecialchars( $user_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading">First name:</td><td><b><?php echo htmlspecialchars( $first_name ); ?></b></td></tr>
	<tr class="even"><td class="heading">Last name:</td><td><b><?php echo htmlspecialchars( $last_name ); ?></b></td></tr>
	<tr class="odd"><td class="heading">Login Shell:</td><td><?php echo htmlspecialchars( $login_shell); ?></td></tr>
        <tr class="even"><td class="heading">UID Number:</td><td><?php echo htmlspecialchars( $uid_number ); ?></td></tr>
	<tr class="odd"><td class="heading">Samba SID:</td><td><?php echo htmlspecialchars( $samba3_domain_sid."-".$samba3_user_rid ); ?></td></tr>
	<tr class="even"><td class="heading">GID Number:</td><td><?php echo htmlspecialchars( $gid_number ); ?></td></tr>
	<tr class="odd"><td class="heading">Samba Group Sid:</td><td><?php echo htmlspecialchars( $samba3_primary_group_sid ); ?></td></tr>
	<tr class="even"><td class="heading">Container:</td><td><?php echo htmlspecialchars( $container ); ?></td></tr>
	<tr class="odd"><td class="heading">Home dir:</td><td><?php echo htmlspecialchars( $home_dir ); ?></td></tr>
	<tr class="even"><td class="heading">User Password:</td><td>[secret]</td></tr>
        <?php if( $smb_passwd_creation_success ){ ?>
	<tr class="odd"><td class="heading">Samba Password:</td><td>[secret]</td></tr>
	  <tr class="even"><td class="heading">Password Last Set:</td><td><?php echo $now ?></td></tr>
	  <tr class="odd"><td class="heading">Password Can Change:</td><td><?php echo $now ?></td></tr>
	  <tr class="even"><td class="heading">Password Must Change:</td><td><?php echo "2147483647" ?></td></tr>
	<?php } ?>
        </table>
	<br /><input type="submit" value="Create Samba Account" />
	</center>

<?php } ?>
