<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/ldif_import.php,v 1.35 2007/12/15 07:50:30 wurley Exp $
 
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

if (! $_SESSION['plaConfig']->isCommandAvailable('import'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('import')));

$entry['continuous_mode'] = get_request('continuous_mode') ? true : false;
$entry['ldif'] = get_request('ldif');

if ($entry['ldif']) {
	$entry['remote_file'] = 'STDIN';
	$entry['size'] = strlen($entry['ldif']);

} elseif (isset($_FILES['ldif_file'])) {
	$file = $_FILES['ldif_file']['tmp_name'];
	$entry['remote_file'] = $_FILES['ldif_file']['name'];
	$entry['size'] = $_FILES['ldif_file']['size'];

	if (! is_array($_FILES['ldif_file'])) {
		pla_error(_('Missing uploaded file.'),null,-1,false);
		return;
	}
	if (! file_exists($file)) {
		pla_error(_('No LDIF file specified. Please try again.'),null,-1,false);
		return;
	}
	if ($entry['size'] <= 0) {
		pla_error(_('Uploaded LDIF file is empty.'),null,-1,false);
		return;
	}

} else {
	pla_error(_('You must either upload a file or provide an LDIF in the text box.'),null,-1,false);
	return;
}

printf('<h3 class="title">%s</h3>',_('Import LDIF File'));
printf('<h3 class="subtitle">%s: <b>%s</b> %s: <b>%s (%s %s)</b></h3>',
	_('Server'),htmlspecialchars($ldapserver->name),
	_('File'),htmlspecialchars($entry['remote_file']),number_format($entry['size']),_('bytes'));
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
if (isset($entry['ldif']))
	$ldifReader = new LdifReaderStdIn($entry['ldif'],$entry['continuous_mode']);
else
	$ldifReader = new LdifReader($file,$entry['continuous_mode']);

# instantiate the writer
$ldapWriter = new LdapWriter($ldapserver);

# if ldif file has no version number, just display a warning
if (!$ldifReader->hasVersionNumber())
	display_warning($ldifReader->getWarningMessage());

$i=0;
# if .. else not mandatory but should be easier to maintain
if ($entry['continuous_mode']) {
	while ($ldifReader->readEntry()) {
		$i++;

		# get the entry. 
		$currentEntry = $ldifReader->fetchEntryObject();
		$edit_href = sprintf('cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s',$ldapserver->server_id,
			rawurlencode($currentEntry->dn));
		$changeType = $currentEntry->getChangeType();
		printf('<small>%s <a href="%s">%s</a>',$actionString[$changeType],$edit_href,$currentEntry->dn);

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
	} # end while

} else {
	# while we have a valid entry, 
	while ($entry = $ldifReader->readEntry()) {
		$i++;

		$edit_href = sprintf('cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s',$ldapserver->server_id,
			rawurlencode($entry->dn));
		$changeType = $entry->getChangeType();
		printf('<small>%s <a href="%s">%s</a>',$actionString[$changeType],$edit_href,$entry->dn);

		if ($ldapWriter->ldapModify($entry)) {
			printf(' <span style="color:green;">%s</span></small><br />',_('Success'));

		} else {
			printf(' <span style="color:red;">%s</span></small><br /><br />',_('Failed'));
			$ldap_err_no = ('0x'.str_pad(dechex($ldapserver->errno()),2,0,STR_PAD_LEFT));
			$verbose_error = pla_verbose_error($ldap_err_no);

			$errormsg = sprintf('%s <b>%s</b>',$actionErrorMsg[$changeType],htmlspecialchars($entry->dn));
			$errormsg .= sprintf('<br />%s: <b>%s</b>',_('LDAP said'),$verbose_error['title']);
			$errormsg .= sprintf('<br />%s',$verbose_error['desc']);
			system_message(array(
				'title'=>_('LDIF text import'),
				'body'=>$errormsg,
				'type'=>'warn'));

			break;
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

function display_warning($warning){
	printf('<div style="color:orange"><small>%s</small></div>',$warning);
}

function display_pla_parse_error($exception,$faultyEntry) {
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
?>
