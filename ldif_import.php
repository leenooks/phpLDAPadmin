<?php 

/*
 * ldif_import.php
 * Imports an LDIF file to the specified server_id.
 *
 * Variables that come in as POST vars:
 *  - ldif_file (as an uploaded file)
 *  - server_id
 */

require 'common.php';

$debug = true;

$server_id = $_POST['server_id'];
$server_name = $servers[$server_id]['name'];
$file = $_FILES['ldif_file']['tmp_name'];
$remote_file = $_FILES['ldif_file']['name'];
$file_len = $_FILES['ldif_file']['size'];

is_array( $_FILES['ldif_file'] ) or pla_error( "Missing uploaded file." );
file_exists( $file ) or pla_error( "No LDIF file specified. Please try again." );
$file_len > 0 or pla_error( "Uploaded file is empty." );
check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

include 'header.php'; ?>

<body>

<h3 class="title">Import LDIF File</h3>
<h3 class="subtitle">
	Server: <b><?php echo htmlspecialchars( $server_name ); ?></b>
	File: <b><?php echo htmlspecialchars( $remote_file ); ?>
	(<?php echo number_format( $file_len ); ?> bytes)</b>
</h3>

<br />
<br />
<center><i>This is an experimental and untested feature. Proceed at your own risk.</i><br />
<i>The add and delete operations are the only operations currently supported.</i>
</center>
<br />

<?php 
include("ldif_functions.php");	
@set_time_limit( 0 );
ldif_open_file($file);

$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server" );
if(!ldif_check_version()){
  display_warning(ldif_warning_message());
}
while($dn_entry= ldif_fetch_dn_entry() ){
   $action = ldif_get_action();
    if($action=="add"){
      echo "Adding dn:".utf8_decode($dn_entry) ."... ";
      flush();
      if($attributes = ldif_fetch_attributes_for_entry()){
	if(@ldap_add($ds,$dn_entry,$attributes)){
	  echo "<span style=\"color:green;\">Success</span><br>";
	}
	else{
	  echo "<span style=\"color:red;\">failed</span><br><br>";
	  pla_error( "Could not add object: " . htmlspecialchars( utf8_decode( $dn ) ), ldap_error( $ds ), ldap_errno( $ds ) );
	}
      }
      else{
	echo "<span style=\"color:red;\">failed</span><br><br>";
	echo "<div style=\"color:red\">".display_error_message(ldif_error_message())."</div>";
	flush();
      }
    }
    elseif($action=="delete"){
      echo "Deleting dn: ".$dn_entry." ";
      if(@ldap_delete($ds,$dn_entry)){
	echo "<span style=\"color:green;\">Success</span><br>";
	flush();
      }
      else{
	echo "<span style=\"color:red;\">Failed</span><br><br>";
	flush();
	pla_error( "Could not delete object: " . htmlspecialchars( utf8_decode( $dn ) ), ldap_error( $ds ), ldap_errno( $ds ) );
	
      }
    }

 }


reload_left_frame();


function reload_left_frame(){
  global $server_id;
  
  echo "<script>\r\n";
  echo "parent.left_frame.document.location='refresh.php?server_id=".$server_id."';\r\n";
  echo "</script>\r\n";
}

function display_error_message($error_message){
  echo "<div style=\"color:red;\">".$error_message."</div>";
}
function display_warning($warning){
  echo "<div style=\"color:orange\">".$warning."</div>";
}

?>


</script>


</body>
</html>


