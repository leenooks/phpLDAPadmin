<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_attr.php,v 1.15.2.4 2005/10/22 14:22:47 wurley Exp $

/**
 * Adds an attribute/value pair to an object
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - dn
 *  - attr
 *  - val
 *  - binary
 *
 * @package phpLDAPadmin
 * @todo: For boolean attributes, convert the response to TRUE/FALSE.
 */
/**
 */

require './common.php';
require TMPLDIR.'template_config.php';

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$attr = $_POST['attr'];
$val  = isset( $_POST['val'] ) ? $_POST['val'] : false;;
$dn = $_POST['dn'] ;
$is_binary_val = isset( $_POST['binary'] ) ? true : false;

$encoded_dn = rawurlencode( $dn );
$encoded_attr = rawurlencode( $attr );

if( ! $is_binary_val && $val == "" ) {
	pla_error( $lang['left_attr_blank'] );
}

// special case for binary attributes (like jpegPhoto and userCertificate):
// we must go read the data from the file and override $val with the binary data
// Secondly, we must check if the ";binary" option has to be appended to the name
// of the attribute.

// Check to see if this is a unique Attribute
if( $badattr = checkUniqueAttr( $ldapserver, $dn, $attr, array($val) ) ) {
	$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',$ldapserver->server_id,$attr,$badattr);
	pla_error(sprintf( $lang['unique_attr_failed'],$attr,$badattr,$dn,$search_href ) );
}

if( $is_binary_val ) {
	if( 0 == $_FILES['val']['size'] )
		pla_error( $lang['file_empty'] );

	if( ! is_uploaded_file( $_FILES['val']['tmp_name'] ) ) {

		if( isset( $_FILES['val']['error'] ) )

			switch($_FILES['val']['error']) {
				case 0: //no error; possible file attack!
					pla_error( $lang['invalid_file'] );
					break;

				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
					pla_error( $lang['uploaded_file_too_big'] );
					break;

				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive specified in the html form
					pla_error( $lang['uploaded_file_too_big'] );
					break;

				case 3: //uploaded file was only partially uploaded
					pla_error( $lang['uploaded_file_partial'] );
					break;

				case 4: //no file was uploaded
					pla_error( $lang['left_attr_blank'] );
					break;

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

	if( is_binary_option_required( $ldapserver, $attr ) )
		$attr .= ";binary";
}

/* Automagically hash new userPassword attributes according to the
   chosen in config.php. */
if( 0 == strcasecmp( $attr, 'userpassword' ) ) {
	if (trim($ldapserver->default_hash) != '' ) {
		$enc_type = $ldapserver->default_hash;
		$val = password_hash( $val, $enc_type );
	}
}

elseif (strcasecmp($attr,'sambaNTPassword') == 0) {
	$sambapassword = new smbHash;
	$val = $sambapassword->nthash($val);
}

elseif (strcasecmp($attr,'sambaLMPassword') == 0) {
	$sambapassword = new smbHash;
	$val = $sambapassword->lmhash($val);
}

$new_entry = array( $attr => $val );
$result = @ldap_mod_add( $ldapserver->connect(), $dn, $new_entry );

if ($result)
	header(sprintf('Location: edit.php?server_id=%s&dn=%s&modified_attrs[]=%s',
		$ldapserver->server_id,$encoded_dn,$encoded_attr));

else
	pla_error( $lang['failed_to_add_attr'],$ldapserver->error(),$ldapserver->errno() );

/**
 * Check if we need to append the ;binary option to the name
 * of some binary attribute
 *
 * @param object $ldapserver Server Object that the attribute is in.
 * @param attr $attr Attribute to test to see if it requires ;binary added to it.
 * @return bool
 */

function is_binary_option_required( $ldapserver, $attr ) {

	// list of the binary attributes which need the ";binary" option
	$binary_attributes_with_options = array(
		// Superior: Ldapv3 Syntaxes (1.3.6.1.4.1.1466.115.121.1)
		'1.3.6.1.4.1.1466.115.121.1.8' => "userCertificate",
		'1.3.6.1.4.1.1466.115.121.1.8' => "caCertificate",
		'1.3.6.1.4.1.1466.115.121.1.10' => "crossCertificatePair",
		'1.3.6.1.4.1.1466.115.121.1.9' => "certificateRevocationList",
		'1.3.6.1.4.1.1466.115.121.1.9' => "authorityRevocationList",
		// Superior: Netscape Ldap attributes types (2.16.840.1.113730.3.1)
		'2.16.840.1.113730.3.1.40'      =>  "userSMIMECertificate"
	);

	// quick check by attr name (short circuits the schema check if possible)
	//foreach( $binary_attributes_with_options as $oid => $name )
	//if( 0 == strcasecmp( $attr, $name ) )
        //return true;

	$schema_attr = $ldapserver->getSchemaAttribute($attr);
	if( ! $schema_attr )
		return false;

	$syntax = $schema_attr->getSyntaxOID();
	if( isset( $binary_attributes_with_options[ $syntax ] ) )
		return true;

	return false;
}
?>
