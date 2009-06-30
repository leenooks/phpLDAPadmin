<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/new_smbgroup_template.php,v 1.11 2004/05/08 12:27:17 xrenard Exp $

// Common to all templates
$rdn = isset( $_POST['rdn'] ) ? $_POST['rdn'] : null;
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Change this to suit your needs
$default_number_of_members = 4;

// get the available domains (see template_connfig.php for customization)
$samba3_domains = get_samba3_domains();

$step = 1;
if( isset($_POST['step']) )
    $step = $_POST['step'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

if( get_schema_objectclass( $server_id, 'sambaGroupMapping' ) == null )
	pla_error( "You LDAP server does not have schema support for the sambaGroupMapping objectClass. Cannot continue." );

?>
<script language="javascript">
/**
 * Populate the display name field from the group name field
 */

function autoFillDisplayName( form ){
	var unix_group_name
	unix_group_name = form.unix_name.value;
        form.display_name.value = unix_group_name;
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
</script>

<center><h2>New Samba Group Mapping</h2></center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" name="posix_group_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<tr>
	<td></td>
	<td class="heading">Unix Name:</td>
	<td><input type="text" name="unix_name" value="" onChange="autoFillDisplayName(this.form)"/> <small>(example: admins, do not include "cn=")</small></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Windows  Name:</td>
	<td><input type="text" name="display_name" value="" /> </small></td>
</tr>
<tr>
	<td></td>
	<td class="heading"><acronym title="Group Identification">GID</acronym> Number:</td>
	<td><input type="text" name="gid_number" value="" onChange="autoFillSambaGroupRID(this.form)" /> <small>(example: 2000)</small></td>
</tr>
<!--
<tr>
	<td></td>
	<td class="heading"><acronym title="Samba Security  Identifier">SambaSID</acronym></td>
	<td><select name="samba3_domain_sid">
<?php foreach($samba3_domains as $samba3_domain) ?>
      <option value="<?php echo $samba3_domain['sid'] ?>"><?php echo $samba3_domain['sid'] ?></option>
</select> - <input type="text" name="samba3_rid" id="samba3_rid" value="" size="7"/></td>
</tr>
-->
<tr valign="top">
	<td></td>
	<td class="heading">Samba Sid:</td>
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
                      <!--    <optgroup label="- <?php echo $samba3_domain['name'];?>"> -->
			      <option value="<?php echo $samba3_domain['sid']; ?>-512">Domain Admins (<?php echo $samba3_domain['sid']; ?>-512)</option>
			      <option value="<?php echo $samba3_domain['sid']; ?>-513">Domain Users  (<?php echo $samba3_domain['sid']; ?>-513)</option>
			      <option value="<?php echo $samba3_domain['sid']; ?>-514">Domain Guests (<?php echo $samba3_domain['sid']; ?>-514)</option>
			<?php } ?>
                           </optgroup>
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
	<td class="heading">Container <acronym title="Distinguished Name">DN</acronym>:</td>
	<td><input type="text" name="container" size="40" value="<?php echo htmlspecialchars( $container ); ?>" />
		<?php draw_chooser_link( 'posix_group_form.container' ); ?></td>
	</td>
</tr>


<tr>
	<td></td>
	<td class="heading"><acronym title="Samba Group Type">SambaGroupType</acronym> :</td>
	<td>
          <select name="group_type_number">
              <!--  <option value="1">1 - User</option> -->
              <option value="2" selected>2 - Domain Group</option>
              <!-- <option value="3">3 - Domain</option> -->
              <option value="4">4 - Local Group</option>
              <option value="5">5 - Well-known Group</option>
              <!-- <option value="6">6 - Deleted Account</option> 
              <option value="7">7 - Invalid Account</option>
              <option value="8">8 - Unknown</option> -->
          </select>
       </td>
</tr>
<tr>
	<td></td>
	<td class="heading">Description:</td>
	<td><input type="text" name="description" value="" /> </small></td>
</tr>
<tr valign="top">
	<td></td>
	<td class="heading">Members:</td>
	<td><input type="text" name="member_uids[]" value="" /> <small>(example: dsmith)</small><br />
<?php for( $i=1; $i<$default_number_of_members; $i++ ) { ?>
	<input type="text" name="member_uids[]" value="" /><br />
<?php } ?>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
</table>
</center>
</form>


<?php } elseif( $step == 2 ) {
  
	$unix_name = trim( $_POST['unix_name'] );
	$container = trim( $_POST['container'] );
	$gid_number = trim( $_POST['gid_number'] );
	$display_name = trim( $_POST['display_name'] );
	$group_type_number = trim( $_POST['group_type_number'] );
	$description = $_POST['description'];
	$uids = $_POST['member_uids'];
	$samba3_sid = NULL;
	$samba3_group = isset( $_POST['samba_group'] )? $_POST['samba_group'] : 0 ;
	switch($samba3_group){
	case 1:
	  isset( $_POST['builtin_sid'] ) or pla_error("No built-in group selected. Please go back and try again" );
	  $samba3_sid = $_POST['builtin_sid'];
	  break;
	case 2:
	  ! empty( $_POST['custom_rid'] ) or pla_error( "The value of the samba RID was not specified. Please go back and try again" );
	  $samba3_sid = $_POST['custom_domain_sid'] . "-" .$_POST['custom_rid'];
	  break;
	default:
	  pla_error( "No samba group select. Please go back and try again" );
	}



	$member_uids = array();
	foreach( $uids as $uid )
		if( '' != trim( $uid ) && ! in_array( $uid, $member_uids ) )
			$member_uids[] = $uid;
	
	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );

	?>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'cn='.$unix_name.','.$container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'posixGroup','sambaGroupMapping' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($unix_name);?>" />
	<input type="hidden" name="attrs[]" value="gidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($gid_number);?>" />
	<input type="hidden" name="attrs[]" value="displayName" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($display_name);?>" />
	<input type="hidden" name="attrs[]" value="sambaSid" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($samba3_sid);?>" />
	<input type="hidden" name="attrs[]" value="sambaGroupType" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($group_type_number);?>" />
	<input type="hidden" name="attrs[]" value="description" />
	<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($description);?>" />
	
	   <?php foreach( $member_uids as $uid ) { ?>
	<input type="hidden" name="attrs[]" value="memberUid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($uid);?>" />
	<?php } ?>

	<center>
	Really create this new Posix Group entry?<br />
	<br />
<table class="confirm">
	<tr class="even"><td>Common Name</td><td><b><?php echo htmlspecialchars($unix_name); ?></b></td></tr>
	<tr class="odd"><td>Container</td><td><b><?php echo htmlspecialchars( $container ); ?></b></td></tr>
	<tr class="even"><td>display Name</td><td><b><?php echo htmlspecialchars($display_name); ?></b></td></tr>
	<tr class="odd"><td>gidNumber</td><td><b><?php echo htmlspecialchars( $gid_number ); ?></b></td></tr>
	<tr class="even"><td>sambaSID</td><td><b><?php echo htmlspecialchars($samba3_sid); ?></b></td></tr>
	<tr class="odd"><td>sambaGroupType</td><td><b><?php echo htmlspecialchars( $group_type_number ); ?></b></td></tr>
	<tr class="even"><td>description</td><td><b><?php echo htmlspecialchars( $description ); ?></b></td></tr>
	<tr class="odd"><td>Member UIDs</td><td><b>
	<?php foreach( $member_uids as $i => $uid ) 
		echo htmlspecialchars($uid) . "<br />"; ?>
		</b></td></tr>
	</table>
	<br /><input type="submit" value="Create Group" />
	</center>
		    
		    <?php } ?>

</body>
</html>
