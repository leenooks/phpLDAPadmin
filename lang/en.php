<?php

/*        ---   INSTRUCTIONS FOR TRANSLATORS   ---
 *
 * If you want to write a new language file for your language,
 * please submit the file on SourceForge:
 *
 * https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498548
 *
 * Use the option "Check to Upload and Attach a File" at the bottom
 *
 * Thank you!
 *
 */

/*
 * The $lang array contains all the strings that phpLDAPadmin uses.
 * Each language file simply defines this aray with strings in its
 * language.
 */

// Search form
$lang['simple_search_form_str'] = 'Simple Search Form';
$lang['advanced_search_form_str'] = 'Advanced Search Form';
$lang['server'] = 'Server';
$lang['search_for_entries_whose'] = 'Search for entries whose';
$lang['base_dn'] = 'Base <acronym title="Distinguished Name">DN</acronym>';
$lang['search_scope'] = 'Search Scope';
$lang['search_ filter'] = 'Search Filter';
$lang['show_attributes'] = 'Show Attributtes';
$lang['Search'] = 'Search';
$lang['equals'] = 'equals';
$lang['starts_with'] = 'starts with';
$lang['contains'] = 'contains';
$lang['ends_with'] = 'ends with';
$lang['sounds_like'] = 'sounds like';

// Tree browser
$lang['request_new_feature'] = 'Request a new feature';
$lang['see_open_requests'] = 'see open requests';
$lang['report_bug'] = 'Report a bug';
$lang['see_open_bugs'] = 'see open bugs';
$lang['schema'] = 'schema';
$lang['search'] = 'search';
$lang['create'] = 'create';
$lang['info'] = 'info';
$lang['import'] = 'import';
$lang['refresh'] = 'refresh';
$lang['logout'] = 'logout';
$lang['create_new'] = 'Create New';
$lang['view_schema_for'] = 'View schema for';
$lang['refresh_expanded_containers'] = 'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'Create a new entry on';
$lang['view_server_info'] = 'View server-supplied information';
$lang['import_from_ldif'] = 'Import entries from an LDIF file';
$lang['logout_of_this_server'] = 'Logout of this server';
$lang['logged_in_as'] = 'Logged in as: ';
$lang['read_only'] = 'read only';
$lang['could_not_determine_root'] = 'Could not determine the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'It appears that the LDAP server has been configured to not reveal its root.';
$lang['please_specify_in_config'] = 'Please specify it in config.php';
$lang['create_new_entry_in'] = 'Create a new entry in';
$lang['login_link'] = 'Login...';

// Entry display
$lang['delete_this_entry'] = 'Delete this entry';
$lang['delete_this_entry_tooltip'] = 'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'Copy this entry';
$lang['copy_this_entry_tooltip'] = 'Copy this object to another location, a new DN, or another server';
$lang['export_to_ldif'] = 'Export to LDIF';
$lang['export_to_ldif_tooltip'] = 'Save an LDIF dump of this object';
$lang['export_subtree_to_ldif_tooltip'] = 'Save an LDIF dump of this object and all of its children';
$lang['export_subtree_to_ldif'] = 'Export subtree to LDIF';
$lang['export_to_ldif_mac'] = 'Macintosh style line ends';
$lang['export_to_ldif_win'] = 'Windows style line ends';
$lang['export_to_ldif_unix'] = 'Unix style line ends';
$lang['create_a_child_entry'] = 'Create a child entry';
$lang['add_a_jpeg_photo'] = 'Add a jpegPhoto';
$lang['rename_entry'] = 'Rename Entry';
$lang['rename'] = 'Rename';
$lang['add'] = 'Add';
$lang['view'] = 'View';
$lang['add_new_attribute'] = 'Add New Attribute';
$lang['add_new_attribute_tooltip'] = 'Add a new attribute/value to this entry';
$lang['internal_attributes'] = 'Internal Attributes';
$lang['hide_internal_attrs'] = 'Hide internal attributes';
$lang['show_internal_attrs'] = 'Show internal attributes';
$lang['internal_attrs_tooltip'] = 'Attributes set automatically by the system';
$lang['entry_attributes'] = 'Entry Attributes';
$lang['attr_name_tooltip'] = 'Click to view the schema defintion for attribute type \'%s\'';
$lang['click_to_display'] = 'click \'+\' to display';
$lang['hidden'] = 'hidden';
$lang['none'] = 'none';
$lang['save_changes'] = 'Save Changes';
$lang['add_value'] = 'add value';
$lang['add_value_tooltip'] = 'Add an additional value to attribute \'%s\'';
$lang['refresh_entry'] = 'Refresh';
$lang['refresh_this_entry'] = 'Refresh this entry';
$lang['delete_hint'] = 'Hint: <b>To delete an attribute</b>, empty the text field and click save.';
$lang['attr_schema_hint'] = 'Hint: <b>To view the schema for an attribute</b>, click the attribute name.';
$lang['attrs_modified'] = 'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'Viewing entry in read-only mode.';
$lang['change_entry_rdn'] = 'Change this entry\'s RDN';
$lang['no_new_attrs_available'] = 'no new attributes available for this entry';
$lang['binary_value'] = 'Binary value';
$lang['add_new_binary_attr'] = 'Add New Binary Attribute';
$lang['add_new_binary_attr_tooltip'] = 'Add a new binary attribute/value from a file';
$lang['alias_for'] = 'Note: \'%s\' is an alias for \'%s\'';
$lang['download_value'] = 'download value';
$lang['delete_attribute'] = 'delete attribute';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = 'none, remove value';
$lang['really_delete_attribute'] = 'Really delete attribute';

// Schema browser
$lang['the_following_objectclasses'] = 'The following <b>objectClasses</b> are supported by this LDAP server.';
$lang['the_following_attributes'] = 'The following <b>attributeTypes</b> are supported by this LDAP server.';
$lang['the_following_matching'] = 'The following <b>matching rules</b> are supported by this LDAP server.';
$lang['the_following_syntaxes'] = 'The following <b>syntaxes</b> are supported by this LDAP server.';
$lang['jump_to_objectclass'] = 'Jump to an objectClass';
$lang['jump_to_attr'] = 'Jump to an attribute';
$lang['schema_for_server'] = 'Schema for server';
$lang['required_attrs'] = 'Required Attributes';
$lang['optional_attrs'] = 'Optional Attributes';
$lang['OID'] = 'OID';
$lang['desc'] = 'Description';
$lang['name'] = 'Name';
$lang['is_obsolete'] = 'This objectClass is <b>obsolete</b>';
$lang['inherits'] = 'Inherits';
$lang['jump_to_this_oclass'] = 'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'Matching Rule OID';
$lang['syntax_oid'] = 'Syntax OID';
$lang['not_applicable'] = 'not applicable';
$lang['not_specified'] = 'not specified';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Entry \'%s\' deleted successfully.';
$lang['you_must_specify_a_dn'] = 'You must specify a DN';
$lang['could_not_delete_entry'] = 'Could not delete the entry: %s';

// Adding objectClass form
$lang['new_required_attrs'] = 'New Required Attributes';
$lang['requires_to_add'] = 'This action requires you to add';
$lang['new_attributes'] = 'new attributes';
$lang['new_required_attrs_instructions'] = 'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'Add ObjectClass and Attributes';

// General
$lang['chooser_link_tooltip'] = 'Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = 'Bad server id';
$lang['not_enough_login_info'] = 'Not enough information to login to server. Please check your configuration.';
$lang['could_not_connect'] = 'Could not connect to LDAP server.';
$lang['could_not_perform_ldap_mod_add'] = 'Could not perform ldap_mod_add operation.';
$lang['bad_server_id_underline'] = 'Bad server_id: ';
$lang['success'] = 'Success';
$lang['server_colon_pare'] = 'Server: ';
$lang['look_in'] = 'Looking in: ';
$lang['missing_server_id_in_query_string'] = 'No server ID specified in query string!';
$lang['missing_dn_in_query_string'] = 'No DN specified in query string!';
$lang['back_up_p'] = 'Back Up...';
$lang['no_entries'] = 'no entries';
$lang['not_logged_in'] = 'Not logged in';
$lang['could_not_det_base_dn'] = 'Could not determine base DN';

// Add value form
$lang['add_new'] = 'Add new';
$lang['value_to'] = 'value to';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Current list of';
$lang['values_for_attribute'] = 'values for attribute';
$lang['inappropriate_matching_note'] = 'Note: You will get an "inappropriate matching" error if you have not<br />' .
			'setup an <tt>EQUALITY</tt> rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Note: you may be required to enter new attributes that this objectClass requires';
$lang['syntax'] = 'Syntax';

//copy.php
$lang['copy_server_read_only'] = 'You cannot perform updates while server is in read-only mode';
$lang['copy_dest_dn_blank'] = 'You left the destination DN blank.';
$lang['copy_dest_already_exists'] = 'The destination entry (%s) already exists.';
$lang['copy_dest_container_does_not_exist'] = 'The destination container (%s) does not exist.';
$lang['copy_source_dest_dn_same'] = 'The source and destination DN are the same.';
$lang['copy_copying'] = 'Copying ';
$lang['copy_recursive_copy_progress'] = 'Recursive copy progress';
$lang['copy_building_snapshot'] = 'Building snapshot of tree to copy... ';
$lang['copy_successful_like_to'] = 'Copy successful! Would you like to ';
$lang['copy_view_new_entry'] = 'view the new entry';
$lang['copy_failed'] = 'Failed to copy DN: ';

//edit.php
$lang['missing_template_file'] = 'Warning: missing template file, ';
$lang['using_default'] = 'Using default.';

//copy_form.php
$lang['copyf_title_copy'] = 'Copy ';
$lang['copyf_to_new_object'] = 'to a new object';
$lang['copyf_dest_dn'] = 'Destination DN';
$lang['copyf_dest_dn_tooltip'] = 'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'Destination Server';
$lang['copyf_note'] = 'Hint: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = 'Recursively copy all children of this object as well.';

//create.php
$lang['create_required_attribute'] = 'You left the value blank for required attribute <b>%s</b>.';
$lang['create_redirecting'] = 'Redirecting';
$lang['create_here'] = 'here';
$lang['create_could_not_add'] = 'Could not add the object to the LDAP server.';

//create_form.php
$lang['createf_create_object'] = 'Create Object';
$lang['createf_choose_temp'] = 'Choose a template';
$lang['createf_select_temp'] = 'Select a template for the creation process';
$lang['createf_proceed'] = 'Proceed';

//creation_template.php
$lang['ctemplate_on_server'] = 'On server';
$lang['ctemplate_no_template'] = 'No template specified in POST variables.';
$lang['ctemplate_config_handler'] = 'Your config specifies a handler of';
$lang['ctemplate_handler_does_not_exist'] = 'for this template. But, this handler does not exist in the templates/creation directory.';

// search.php
$lang['you_have_not_logged_into_server'] = 'You have not logged into the selected server yet, so you cannot perform searches on it.';
$lang['click_to_go_to_login_form'] = 'Click here to go to the login form';
$lang['unrecognized_criteria_option'] = 'Unrecognized criteria option: ';
$lang['if_you_want_to_add_criteria'] = 'If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.';
$lang['entries_found'] = 'Entries found: ';
$lang['filter_performed'] = 'Filter performed: ';
$lang['search_duration'] = 'Search performed by phpLDAPadmin in';
$lang['seconds'] = 'seconds';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'The scope in which to search';
$lang['scope_sub'] = 'Sub (entire subtree)';
$lang['scope_one'] = 'One (one level beneath base)';
$lang['scope_base'] = 'Base (base dn only)';
$lang['standard_ldap_search_filter'] = 'Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Search Filter';
$lang['list_of_attrs_to_display_in_results'] = 'A list of attributes to display in the results (comma-separated)';
$lang['show_attributes'] = 'Show Attributes';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Search for entries whose:';
$lang['equals'] = 'equals';
$lang['starts with'] = 'starts with';
$lang['contains'] = 'contains';
$lang['ends with'] = 'ends with';
$lang['sounds like'] = 'sounds like';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Could not retrieve LDAP information from the server';
$lang['server_info_for'] = 'Server info for: ';
$lang['server_reports_following'] = 'Server reports the following information about itself';
$lang['nothing_to_report'] = 'This server has nothing to report.';

//update.php
$lang['update_array_malformed'] = 'update_array is malformed. This might be a phpLDAPadmin bug. Please report it.';
$lang['could_not_perform_ldap_modify'] = 'Could not perform ldap_modify operation.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Do you want to make these changes?';
$lang['attribute'] = 'Attribute';
$lang['old_value'] = 'Old Value';
$lang['new_value'] = 'New Value';
$lang['attr_deleted'] = '[attribute deleted]';
$lang['commit'] = 'Commit';
$lang['cancel'] = 'Cancel';
$lang['you_made_no_changes'] = 'You made no changes';
$lang['go_back'] = 'Go back';

// welcome.php
$lang['welcome_note'] = 'Use the menu to the left to navigate';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Unsafe file name: ';
$lang['no_such_file'] = 'No such file: ';

//function.php
$lang['auto_update_not_setup'] = 'You have enabled auto_uid_numbers for <b>%s</b> in your configuration,
                                  but you have not specified the auto_uid_number_mechanism. Please correct
                                  this problem.';
$lang['uidpool_not_set'] = 'You specified the <tt>auto_uid_number_mechanism</tt> as <tt>uidpool</tt>
                            in your configuration for server <b>%s</b>, but you did not specify the
                            audo_uid_number_uid_pool_dn. Please specify it before proceeding.';
$lang['uidpool_not_exist'] = 'It appears that the uidPool you specified in your configuration (<tt>%s</tt>)
                              does not exist.';
$lang['specified_uidpool'] = 'You specified the <tt>auto_uid_number_mechanism</tt> as <tt>search</tt> in your
                              configuration for server <b>%s</b>, but you did not specify the
                              <tt>auto_uid_number_search_base</tt>. Please specify it before proceeding.';
$lang['auto_uid_invalid_value'] = 'You specified an invalid value for auto_uid_number_mechanism (<tt>%s</tt>)
                                   in your configration. Only <tt>uidpool</tt> and <tt>search</tt> are valid.
                                   Please correct this problem.';
$lang['error_auth_type_config'] = 'Error: You have an error in your config file. The only two allowed values
                                    for auth_type in the $servers section are \'config\' and \'form\'. You entered \'%s\',
                                    which is not allowed. ';
$lang['php_install_not_supports_tls'] = 'Your PHP install does not support TLS';
$lang['could_not_start_tls'] = 'Could not start TLS.<br />Please check your LDAP server configuration.';
$lang['auth_type_not_valid'] = 'You have an error in your config file. auth_type of %s is not valid.';
$lang['ldap_said'] = '<b>LDAP said</b>: %s<br /><br />';
$lang['ferror_error'] = 'Error';
$lang['fbrowse'] = 'browse';
$lang['delete_photo'] = 'Delete Photo';
$lang['install_not_support_blowfish'] = 'Your PHP install does not support blowfish encryption.';
$lang['install_no_mash'] = 'Your PHP install does not have the mhash() function. Cannot do SHA hashes.';
$lang['jpeg_contains_errors'] = 'jpegPhoto contains errors<br />';
$lang['ferror_number'] = '<b>Error number</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] = '<b>Description</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Error number</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Description</b>: (no description available)<br />';
$lang['ferror_submit_bug'] = 'Is this a phpLDAPadmin bug? If so, please <a href=\'%s\'>report it</a>.';
$lang['ferror_unrecognized_num'] = 'Unrecognized error number: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td>
             <td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Please report this bug by clicking here</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Congratulations! You found a bug in phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Error:</td><td><b>%s</b></td></tr>
	     <tr><td>Level:</td><td><b>%s</b></td></tr>
	     <tr><td>File:</td><td><b>%s</b></td></tr>
	     <tr><td>Line:</td><td><b>%s</b></td></tr>
		 <tr><td>Caller:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web server:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Please report this bug by clicking below!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Import LDIF File';
$lang['select_ldif_file'] = 'Select an LDIF file:';
$lang['select_ldif_file_proceed'] = 'Proceed &gt;&gt;';

//ldif_import
$lang['add_action'] = 'Adding...';
$lang['delete_action'] = 'Deleting...';
$lang['rename_action'] = 'Renaming...';
$lang['modify_action'] = 'Modifying...';

$lang['failed'] = 'failed';
$lang['ldif_parse_error'] = 'LDIF Parse Error';
$lang['ldif_could_not_add_object'] = 'Could not add object:';
$lang['ldif_could_not_rename_object'] = 'Could not rename object:';
$lang['ldif_could_not_delete_object'] = 'Could not delete object:';
$lang['ldif_could_not_modify_object'] = 'Could not modify object:';
$lang['ldif_line_number'] = 'Line Number:';
$lang['ldif_line'] = 'Line:';
?>
