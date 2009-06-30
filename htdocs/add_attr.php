<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_attr.php,v 1.20.2.1 2007/12/26 09:26:32 wurley Exp $

/**
 * Adds an attribute/value pair to an object
 *
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

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));

if (! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('add attribute')));

$entry['val'] = get_request('val','POST');
$entry['binary'] = get_request('binary','POST');

$entry['dn']['string'] = get_request('dn','POST');
$entry['dn']['encode'] = rawurlencode($entry['dn']['string']);

$entry['attr']['string'] = get_request('attr','POST');
$entry['attr']['encode'] = rawurlencode($entry['attr']['string']);

if ((strlen($entry['binary']) <= 0) && (strlen($entry['val']) <= 0))
	pla_error(_('You left the attribute value blank. Please go back and try again.'));

/*
 * Special case for binary attributes (like jpegPhoto and userCertificate):
 * we must go read the data from the file and override $val with the binary data
 * Secondly, we must check if the ";binary" option has to be appended to the name
 * of the attribute.
 */

# Check to see if this is a unique Attribute
if ($badattr = $ldapserver->checkUniqueAttr($entry['dn']['string'],$entry['attr']['string'],array($entry['val']))) {
	$href = htmlspecialchars(sprintf('cmd.php?cmd=search&search=true&form=advanced&server_id=%s&filter=%s=%s',
		$ldapserver->server_id,$entry['attr']['string'],$badattr));

	pla_error(sprintf(_('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.'),$entry['attr']['string'],$badattr,$entry['dn']['string'],$href));
}

if (strlen($entry['binary']) > 0) {
	if ($_FILES['val']['size'] == 0)
		pla_error(_('The file you chose is either empty or does not exist. Please go back and try again.'));

	if (! is_uploaded_file($_FILES['val']['tmp_name'])) {
		if (isset($_FILES['val']['error']))

			switch($_FILES['val']['error']) {
				case 0: # No error; possible file attack!
					pla_error(_('Security error: The file being uploaded may be malicious.'));
					break;

				case 1: # Uploaded file exceeds the upload_max_filesize directive in php.ini
					pla_error(_('The file you uploaded is too large. Please check php.ini, upload_max_size setting'));
					break;

				case 2: # Uploaded file exceeds the MAX_FILE_SIZE directive specified in the html form
					pla_error(_('The file you uploaded is too large. Please check php.ini, upload_max_size setting'));
					break;

				case 3: # Uploaded file was only partially uploaded
					pla_error(_('The file you selected was only partially uploaded, likley due to a network error.'));
					break;

				case 4: # No file was uploaded
					pla_error(_('You left the attribute value blank. Please go back and try again.'));
					break;

				default: # A default error, just in case!  :)
					pla_error(_('Security error: The file being uploaded may be malicious.'));
					break;
			}

		else
			pla_error(_('Security error: The file being uploaded may be malicious.'));
	}

	$binaryfile['name'] = $_FILES['val']['tmp_name'];
	$binaryfile['handle'] = fopen($binaryfile['name'],'r');
	$binaryfile['data'] = fread($binaryfile['handle'],filesize($binaryfile['name']));
	fclose($binaryfile['handle']);

	$entry['val'] = $binaryfile['data'];

	if (is_binary_option_required($ldapserver,$entry['attr']['string']))
		$entry['attr']['string'] .= ';binary';
}

/* Automagically hash new userPassword attributes according to the
   chosen in config.php. */
if (strcasecmp($entry['attr']['string'],'userpassword') == 0) {
	if (trim($ldapserver->default_hash) != '' ) {
		$enc_type = $ldapserver->default_hash;
		$entry['val'] = password_hash($entry['val'],$enc_type);
	}

} elseif (strcasecmp($entry['attr']['string'],'sambaNTPassword') == 0) {
	$sambapassword = new smbHash;
	$entry['val'] = $sambapassword->nthash($entry['val']);

} elseif (strcasecmp($entry['attr']['string'],'sambaLMPassword') == 0) {
	$sambapassword = new smbHash;
	$entry['val'] = $sambapassword->lmhash($entry['val']);
}

$new_entry = array($entry['attr']['string'] => $entry['val']);
$result = $ldapserver->attrModify($entry['dn']['string'],$new_entry);

if ($result) {
	header(sprintf('Location: cmd.php?cmd=template_engine&server_id=%s&dn=%s&modified_attrs[]=%s',
		$ldapserver->server_id,$entry['dn']['encode'],$entry['attr']['encode']));
	die();

} else {
	pla_error(_('Failed to add the attribute.'),$ldapserver->error(),$ldapserver->errno());
}

/**
 * Check if we need to append the ;binary option to the name
 * of some binary attribute
 *
 * @param object $ldapserver Server Object that the attribute is in.
 * @param attr $attr Attribute to test to see if it requires ;binary added to it.
 * @return bool
 */

function is_binary_option_required($ldapserver,$attr) {
	# List of the binary attributes which need the ";binary" option
	$binary_attributes_with_options = array(
		# Superior: Ldapv3 Syntaxes (1.3.6.1.4.1.1466.115.121.1)
		'1.3.6.1.4.1.1466.115.121.1.8' => 'userCertificate',
		'1.3.6.1.4.1.1466.115.121.1.8' => 'caCertificate',
		'1.3.6.1.4.1.1466.115.121.1.10' => 'crossCertificatePair',
		'1.3.6.1.4.1.1466.115.121.1.9' => 'certificateRevocationList',
		'1.3.6.1.4.1.1466.115.121.1.9' => 'authorityRevocationList',
		# Superior: Netscape Ldap attributes types (2.16.840.1.113730.3.1)
		'2.16.840.1.113730.3.1.40' => 'userSMIMECertificate'
	);

	$schema_attr = $ldapserver->getSchemaAttribute($attr);
	if (! $schema_attr)
		return false;

	$syntax = $schema_attr->getSyntaxOID();
	if (isset($binary_attributes_with_options[$syntax]))
		return true;

	return false;
}
?>
