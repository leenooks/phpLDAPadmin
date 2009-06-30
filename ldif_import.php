<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/ldif_import.php,v 1.23 2004/04/18 19:17:43 xrenard Exp $
 

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
$continuous_mode =  isset( $_POST['continuous_mode'] ) ?1:0;
$server_name = $servers[$server_id]['name'];
$file = $_FILES['ldif_file']['tmp_name'];
$remote_file = $_FILES['ldif_file']['name'];
$file_len = $_FILES['ldif_file']['size'];

is_array( $_FILES['ldif_file'] ) or pla_error( $lang['missing_uploaded_file'] );
file_exists( $file ) or pla_error( $lang['no_ldif_file_specified'] );
$file_len > 0 or pla_error( $lang['ldif_file_empty'] );
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

include 'header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['import_ldif_file_title']; ?></h3>
<h3 class="subtitle">
	<?php echo $lang['server']; ?>: <b><?php echo htmlspecialchars( $server_name ); ?></b>
	<?php echo $lang['file']; ?>": <b><?php echo htmlspecialchars( $remote_file ); ?>
	(<?php echo sprintf( $lang['number_bytes'], number_format( $file_len ) ); ?>)</b>
</h3>

<br />
<br />

<?php 
include("ldif_functions.php");	
@set_time_limit( 0 );

// String associated to the operation on the ldap server
$actionString = array();
$actionString['add'] = $lang['add_action'];
$actionString['delete'] = $lang['delete_action'];
$actionString['modrdn'] = $lang['rename_action'];
$actionString['moddn'] = $lang['rename_action'];
$actionString['modify'] = $lang['modify_action'];


// String associated with error
$actionErrorMsg =array();
$actionErrorMsg['add'] = $lang['ldif_could_not_add_object'];
$actionErrorMsg['delete']= $lang['ldif_could_not_delete_object'];
$actionErrorMsg['modrdn']= $lang['ldif_could_not_rename_object'];
$actionErrorMsg['moddn']= $lang['ldif_could_not_rename_object'];
$actionErrorMsg['modify']= $lang['ldif_could_not_modify_object'];

// get the connection
$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );

//instantiate the reader
$ldifReader = new LdifReader($file,$continuous_mode);

//instantiate the writer
$ldapWriter = new LdapWriter($ds);

// if ldif file has no version number, just display a warning
if(!$ldifReader->hasVersionNumber()){
  display_warning($ldifReader->getWarningMessage());
}
$i=0;
// if .. else not mandatory but should be easier to maintain
if( $continuous_mode ){
    while( $ldifReader->readEntry() ){
         $i++;  
       // get the entry. 
       $currentEntry = $ldifReader->getCurrentEntry();
       $changeType = $currentEntry->getChangeType();
       echo "<small>".$actionString[$changeType]." ".$currentEntry->dn;

       if($ldifReader->hasRaisedException()){
          echo " <span style=\"color:red;\">".$lang['failed']."</span></small><br>";
    $exception = $ldifReader->getLdapLdifReaderException();
             echo "<small><span style=\"color:red;\">".$lang['ldif_line_number'].": ".$exception->lineNumber."</span></small><br />";
echo "<small><span style=\"color:red;\">".$lang['ldif_line'].": ".$exception->currentLine."</span></small><br />";   
echo "<small><span style=\"color:red;\">".$lang['desc'].": ".$exception->message."</span></small><br />";
}
       else{
         if($ldapWriter->ldapModify($currentEntry))
             echo " <span style=\"color:green;\">".$lang['success']."</span></small><br>";
          else{
             echo " <span style=\"color:red;\">".$lang['failed']."</span></small><br>";
             echo "<small><span style=\"color:red;\">Error Code: ".ldap_errno($ds)."</span></small><br />";
             echo "<small><span style=\"color:red;\">".$lang['desc'].": ".ldap_error($ds)."</span></small><br />";
          }
      }
    if( 0 == $i % 5 )
      flush();
   }// end while
}
else{
//while we have a valid entry, 
while($entry = $ldifReader->readEntry()){
  $i++;
  $changeType = $entry->getChangeType();

  echo "<small>".$actionString[$changeType]." ".$entry->dn;
  if($ldapWriter->ldapModify($entry)){
    echo " <span style=\"color:green;\">".$lang['success']."</span></small><br>";
    if( 0 == $i % 5 )
    flush();
  }
  else{
    echo " <span style=\"color:red;\">".$lang['failed']."</span></small><br><br>";
    reload_left_frame();
    pla_error( $actionErrorMsg[$changeType]. " " . htmlspecialchars( $entry->dn  ), ldap_error( $ds ), ldap_errno( $ds ) );
  }
}

// if any errors occurs during reading file ,"catch" the exception and display it here.
  if($ldifReader->hasRaisedException()){
    //get the entry which raise the exception,quick hack here 
    $currentEntry = $ldifReader->getCurrentEntry();

    if($currentEntry->dn !=""){
      echo "<small>".$actionString[$currentEntry->getChangeType()]." ".$currentEntry->dn." <span style=\"color:red;\">".$lang['failed']."</span></small><br>";
    }
    //get the exception wich was raised
    $exception = $ldifReader->getLdapLdifReaderException();
echo "<br />";
echo "<br />";
display_pla_parse_error($exception,$currentEntry);
  }
}
// close the file
$ldifReader->done();

//close the ldap connection
$ldapWriter->ldapClose();

reload_left_frame();


function reload_left_frame(){
  global $server_id;
  
  echo "<script>\r\n";
  echo "parent.left_frame.document.location='refresh.php?server_id=".$server_id."';\r\n";
  echo "</script>\r\n";
}

function display_error_message($error_message){
  echo "<div style=\"color:red;\"><small>".$error_message."</small></div>";
}
function display_warning($warning){
  echo "<div style=\"color:orange\"><small>".$warning."</small></div>";
}

function display_pla_parse_error($exception,$faultyEntry){
  global $lang;
  global $actionErrorMsg;
  $errorMessage =  $actionErrorMsg[$faultyEntry->getChangeType()];

  print("<center>");
  print("<table class=\"error\"><tr><td class=\"img\"><img src=\"images/warning.png\" /></td>");
  print("<td><center><h2>".$lang['ldif_parse_error']."</h2></center>");
  print("<br />");
  print($errorMessage." ". $faultyEntry->dn);
  print("<p>");
  print("<b>".$lang['desc']."</b>: ".$exception->message);
  print("</p>");
  print("<p>");
  print("<b>".$lang['ldif_line']."</b>: ".$exception->currentLine);
  print("</p>");
  print("<p>");
  print("<b>".$lang['ldif_line_number']."</b>: ".$exception->lineNumber);
  print("</p>");
  print("<br />");
  print("<p>\r\n");
  print("<center>");
  print("<small>");
  print(sprintf($lang['ferror_submit_bug'] , get_href( 'add_bug' )));

 print("</small></center></p>");
 print("<td>");
 print("</tr>");
 print("<center>");

}

?>
</body>
</html>


