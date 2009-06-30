<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/ldif_import.php,v 1.33.2.3 2005/12/10 06:55:52 wurley Exp $
 
/**
 * Imports an LDIF file to the specified server_id.
 *
 * Variables that come in as POST vars:
 *  - ldif_file (as an uploaded file)
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$continuous_mode = isset($_POST['continuous_mode']) ? 1 : 0;

if (isset($_REQUEST['ldif']) && trim($_REQUEST['ldif'])) {
	$textarealdif = $_REQUEST['ldif'];
	$remote_file = 'STDIN';
	$file_len = strlen($textarealdif);

} elseif (isset($_FILES['ldif_file'])) {
	$file = $_FILES['ldif_file']['tmp_name'];
	$remote_file = $_FILES['ldif_file']['name'];
	$file_len = $_FILES['ldif_file']['size'];

	is_array($_FILES['ldif_file']) or pla_error(_('Missing uploaded file.'));
	file_exists($file) or pla_error(_('No LDIF file specified. Please try again.'));
	$file_len > 0 or pla_error(_('Uploaded LDIF file is empty.'));

} else {
	pla_error(_('You must either upload a file or provide an LDIF in the text box.'));
}

include './header.php';

echo '<body>';
printf('<h3 class="title">%s</h3>',_('Import LDIF File'));
printf('<h3 class="subtitle">%s: <b>%s</b> %s: <b>%s (%s %s)</b></h3>',
	_('Server'),htmlspecialchars($ldapserver->name),
	_('File'),htmlspecialchars($remote_file),number_format($file_len),_('bytes'));
echo '<br /><br />';

require LIBDIR.'ldif_functions.php';
@set_time_limit(0);

# String associated to the operation on the ldap server
$actionString = array();
$actionString['add'] = _('Adding...');
$actionString['delete'] = _('Deleting...');
$actionString['modrdn'] = _('Renaming...');
$actionString['moddn'] = _('Renaming...');
$actionString['modify'] = _('Modifying...');

# String associated with error
$actionErrorMsg =array();
$actionErrorMsg['add'] = _('Could not add object:');
$actionErrorMsg['delete']= _('Could not delete object:');
$actionErrorMsg['modrdn']= _('Could not rename object:');
$actionErrorMsg['moddn']= _('Could not rename object:');
$actionErrorMsg['modify']= _('Could not modify object:');

# instantiate the reader
if (isset($textarealdif))
	$ldifReader = new LdifReaderStdIn($textarealdif,$continuous_mode);
else
	$ldifReader = new LdifReader($file,$continuous_mode);

# instantiate the writer
$ldapWriter = new LdapWriter($ldapserver);

# if ldif file has no version number, just display a warning
if (!$ldifReader->hasVersionNumber())
	display_warning($ldifReader->getWarningMessage());

$i=0;
# if .. else not mandatory but should be easier to maintain
if ($continuous_mode) {
	while ($ldifReader->readEntry()) {
		$i++;

		# get the entry. 
		$currentEntry = $ldifReader->fetchEntryObject();
		$edit_href = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,
			rawurlencode($currentEntry->dn));
		$changeType = $currentEntry->getChangeType();
		printf('<small>%s <a href="%s">%s</a>',$actionString[$changeType],$edit_href,$entry->dn);

		if ($ldifReader->hasRaisedException()) {
			printf(' <span style="color:red;">%s</span></small><br />',_('Failed'));
			$exception = $ldifReader->getLdapLdifReaderException();
			printf(' <small><span style="color:red;">%s: %s</span></small><br />',
				_('Line Number'),$exception->lineNumber);
			printf(' <small><span style="color:red;">%s: %s</span></small><br />',
				_('Line'),$exception->currentLine);
			printf(' <small><span style="color:red;">%s: %s</span></small><br />',
				_('Description'),$exception->message);

		} else {
			if ($ldapWriter->ldapModify($currentEntry))
				printf(' <span style="color:green;">%s</span></small><br />',_('Success'));
			else {
				printf('<span style="color:red;">%s</span></small><br />',_('Failed'));
				printf('<small><span style="color:red;">%s: %s</span></small><br />',
					_('Error code'),$ldapserver->errno());
				printf('<small><span style="color:red;">%s: %s</span></small><br />',
					_('Description'),$ldapserver->error());
			}
		}

		if ($i % 5 == 0)
			flush();
	} # end while

} else {
	# while we have a valid entry, 
	while ($entry = $ldifReader->readEntry()) {
		$i++;

		$edit_href = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,
			rawurlencode($entry->dn));
		$changeType = $entry->getChangeType();
		printf('<small>%s <a href="%s">%s</a>',$actionString[$changeType],$edit_href,$entry->dn);

		if ($ldapWriter->ldapModify($entry)) {
			printf(' <span style="color:green;">%s</span></small><br />',_('Success'));

			if ($i % 5 == 0)
				flush();

		} else {
			printf(' <span style="color:red;">%s</span></small><br /><br />',_('Failed'));
			reload_left_frame();
			pla_error($actionErrorMsg[$changeType].' '.htmlspecialchars($entry->dn),
				$ldapserver->error(),$ldapserver->errno());
		}
	}

	# if any errors occurs during reading file ,"catch" the exception and display it here.
	if ($ldifReader->hasRaisedException()) {
		# get the entry which raise the exception,quick hack here 
		$currentEntry = $ldifReader->fetchEntryObject();

		if ($currentEntry->dn != '') {
			printf('<small>%s %s <span style="color:red;">%s</span></small><br />',
				$actionString[$currentEntry->getChangeType()],$currentEntry->dn,_('Failed'));
		}

		# get the exception wich was raised
		$exception = $ldifReader->getLdapLdifReaderException();
		echo '<br /><br />';
		display_pla_parse_error($exception,$currentEntry);
	}
}

# close the file
$ldifReader->done();
reload_left_frame();

function reload_left_frame(){
	echo '<script type="text/javascript" language="javascript">parent.left_frame.location.reload();</script>';
}

function display_error_message($error_message){
	printf('<div style="color:red;"><small>%s</small></div>',$error_message);
}

function display_warning($warning){
	printf('<div style="color:orange"><small>%s</small></div>',$warning);
}

function display_pla_parse_error($exception,$faultyEntry){
	global $actionErrorMsg;

	$errorMessage = $actionErrorMsg[$faultyEntry->getChangeType()];

	echo '<center>';
	echo '<table class="error"><tr><td class="img"><img src="images/warning.png" /></td>';
	echo '<td>';
	printf('<center><h2>%s</h2></center>',_('LDIF Parse Error'));
	echo '<br />';
	printf('%s %s',$errorMessage,$faultyEntry->dn);
	printf('<p><b>%s</b>: %s</p>',_('Description'),$exception->message);
	printf('<p><b>%s</b>: %s</p>',_('Line'),$exception->currentLine);
	printf('<p><b>%s</b>: %s</p>',_('Line Number'),$exception->lineNumber);
	echo '<br />';
	printf('<p><center><small>%s %s</small></center></p>',
		 _('Is this a phpLDAPadmin bug?'),sprintf(_('If so, please <a href="%s">report it</a>.'),get_href('add_bug')));

	echo '</td>';
	echo '</tr>';
	echo '<center>';
}

echo '</body></html>';
?>
