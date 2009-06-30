<?php

require 'common.php';

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];


// Unique to this template
$step = $_POST['step'];
if( ! $step )
	$step = 1;

// A little config for this template
$default_gid_number = 30000;
$default_acct_flags = '[W          ]';
$default_cn = 'Root User';
$default_home_dir = '/dev/null';

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

?>

<center><h2>New Samba NT Machine</h2></center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" name="machine_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo $_POST['template']; ?>" />

<center>
<table class="confirm">
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td><img src="images/server.png" /></td>
	<td class="heading">Machine Name:</td>
	<td><input type="text" name="machine_name" value="" /> <small>(hint: don't include "$" at the end)</small></td>
</tr>
<tr>
	<td></td>
	<td class="heading">UID Number:</td>
	<td><input type="text" name="uid_number" value="" /></td>
</tr>
<tr>
	<td></td>
	<td class="heading">Container:</td>
	<td><input type="text" size="40" name="container" value="<?php echo htmlspecialchars( $container ); ?>" />
		<?php draw_chooser_link( 'machine_form.container' ); ?></td>
	</td>
</tr>
<tr>
	<td colspan="3"><center><br /><input type="submit" value="Proceed &gt;&gt;" />
		<br /><br /><br /><br /><br /><br /></td>
</tr>

<tr class="spacer"><td colspan="3"></td></tr>

<tr>
	<td colspan="3">
		This will create a new NT machine with:<br />
		<small>
		<ul>	
			<li>gidNumber <b><?php echo htmlspecialchars( $default_gid_number ); ?></b></li>
			<li>acctFlags <b><?php echo str_replace(' ', "&nbsp;", htmlspecialchars($default_acct_flags)); ?></b></li>
			<li>cn <b><?php echo htmlspecialchars($default_cn); ?></b></li>
			<li>in container <b><?php echo htmlspecialchars( $container ); ?></b></li>
		</ul>
		To change these values, edit the template file: 
			<code>templates/creation/new_nt_machine.php</code><br />
		Note: You must have the samba schema installed on your LDAP server.
		</small>
	</td>
</tr>

</table>
</center>

<?php } elseif( $step == 2 ) {

	$machine_name = trim( $_POST['machine_name'] );
	$uid_number = trim( $_POST['uid_number'] );

	dn_exists( $server_id, $container ) or
		pla_error( "The container you specified (" . htmlspecialchars( $container ) . ") does not exist. " .
	       		       "Please go back and try again." );
	?>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'uid=' . $machine_name . '$,' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'sambaAccount', 'posixAccount', 'account' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />
		
	<!-- The array of attributes/values -->
	<input type="hidden" name="attrs[]" value="gidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($default_gid_number);?>" />
	<input type="hidden" name="attrs[]" value="uidNumber" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($uid_number);?>" />
	<input type="hidden" name="attrs[]" value="uid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($machine_name . '$');?>" />
	<input type="hidden" name="attrs[]" value="rid" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars( decoct($uid_number));?>" />
	<input type="hidden" name="attrs[]" value="acctFlags" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($default_acct_flags);?>" />
	<input type="hidden" name="attrs[]" value="cn" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($default_cn);?>" />
	<input type="hidden" name="attrs[]" value="homeDirectory" />
		<input type="hidden" name="vals[]" value="<?php echo htmlspecialchars($default_home_dir);?>" />

	<center>
	Realy create this new Samba machine?<br />
	<br />
	<table class="confirm">
	<tr class="even"><td>Name</td><td><b><?php echo htmlspecialchars($machine_name); ?></b></td></tr>
	<tr class="odd"><td>UID</td><td><b><?php echo htmlspecialchars($uid_number); ?></b></td></tr>
	<tr class="even"><td>Container</td><td><b><?php echo htmlspecialchars( $container ); ?></b></td></tr>
	</table>
	<br /><input type="submit" value="Create Machine" />
	</center>

<?php } ?>
