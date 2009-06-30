<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/add_attr.php,v 1.8 2004/04/26 22:58:00 xrenard Exp $
 

/*
 * add_attr.php
 * Adds an attribute/value pair to an object
 *
 * Variables that come in as POST vars:
 *  - dn
 *  - server_id
 *  - attr
 *  - val
 *  - binary
 */

require 'common.php';
require 'templates/template_config.php';

$server_id = $_POST['server_id'];
$attr = $_POST['attr'];
$val  = isset( $_POST['val'] ) ? $_POST['val'] : false;;
$dn = $_POST['dn'] ;
$encoded_dn = rawurlencode( $dn );
$encoded_attr = rawurlencode( $attr );
$is_binary_val = isset( $_POST['binary'] ) ? true : false;

if( ! $is_binary_val && $val == "" ) {
	pla_error( $lang['left_attr_blank'] );
}

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

// special case for binary attributes (like jpegPhoto and userCertificate): 
// we must go read the data from the file and override $val with the binary data
// Secondly, we must check if the ";binary" option has to be appended to the name
// of the attribute.

if( $is_binary_val ) {
    if( 0 == $_FILES['val']['size'] )
        pla_error( $lang['file_empty'] );
    if( ! is_uploaded_file( $_FILES['val']['tmp_name'] ) ) {
        if( isset( $_FILES['val']['error'] ) )
            switch($_FILES['val']['error']){
                case 0: //no error; possible file attack!
                    pla_error( $lang['invalid_file'] );
                case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
                    pla_error( $lang['uploaded_file_too_big'] );
                case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
                    pla_error( $lang['uploaded_file_too_big'] );
                case 3: //uploaded file was only partially uploaded
                    pla_error( $lang['uploaded_file_partial'] );
                case 4: //no file was uploaded
                    pla_error( $lang['left_attr_blank'] );
                default: //a default error, just in case!  :)
                    pla_error( $lang['invalid_file'] );
                    break;
            }
        else
            pla_error( $lang['invalid_file'] );
    }
	$file = $_FILES['val']['tmp_name'];
    $f = fopen( $file, 'r' );
    $binary_data = fread( $f, filesize( $file ) );
    fclose( $f );
    $val = $binary_data;

	if( is_binary_option_required( $server_id, $attr ) )
	  $attr .=";binary";
}

// Automagically hash new userPassword attributes according to the 
// chosen in config.php. 
if( 0 == strcasecmp( $attr, 'userpassword' ) )
{
	if( isset( $servers[$server_id]['default_hash'] ) &&
		$servers[$server_id]['default_hash'] != '' )
	{
		$enc_type = $servers[$server_id]['default_hash'];
		$val = password_hash( $val, $enc_type );
	}
}
elseif( ( 0 == strcasecmp( $attr , 'sambantpassword' ) || 0 == strcasecmp( $attr , 'sambalmpassword') ) ){
    $mkntPassword = new MkntPasswdUtil();
    $mkntPassword->createSambaPasswords( $val );
    $val = $mkntPassword->valueOf($attr);
}

$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
$new_entry = array( $attr => $val );
$result = @ldap_mod_add( $ds, $dn, $new_entry );

if( $result )
     header( "Location: edit.php?server_id=$server_id&dn=$encoded_dn&modified_attrs[]=$encoded_attr" );
else
	pla_error( $lang['failed_to_add_attr'], ldap_error( $ds ) , ldap_errno( $ds ) );

// check if we need to append the ;binary option to the name 
// of some binary attribute

function is_binary_option_required( $server_id, $attr ){

  // list of the binary attributes which need the ";binary" option
  $binary_attributes_with_options = array(
      // Superior: Ldapv3 Syntaxes (1.3.6.1.4.1.1466.115.121.1)
      '1.3.6.1.4.1.1466.115.121.1.8'  =>  "userCertificate",
      '1.3.6.1.4.1.1466.115.121.1.8'  =>  "caCertificate",
      '1.3.6.1.4.1.1466.115.121.1.10' =>  "crossCertificatePair",
      '1.3.6.1.4.1.1466.115.121.1.9'  =>  "certificateRevocationList",
      '1.3.6.1.4.1.1466.115.121.1.9'  =>  "authorityRevocationList",
      // Superior: Netscape Ldap attributes types (2.16.840.1.113730.3.1)
      '2.16.840.1.113730.3.1.40'      =>  "userSMIMECertificate" 
  );
  
  // quick check by attr name (short circuits the schema check if possible)
  //foreach( $binary_attributes_with_options as $oid => $name )
    //if( 0 == strcasecmp( $attr, $name ) )
        //return true;

  $schema_attr = get_schema_attribute( $server_id, $attr );
  if( ! $schema_attr )
    return false;

  $syntax = $schema_attr->getSyntaxOID();
  if( isset( $binary_attributes_with_options[ $syntax ] ) )
    return true;

  return false;
}

?>
