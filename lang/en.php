<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/en.php,v 1.133.2.5 2005/10/22 14:22:48 wurley Exp $


/*        ---   INSTRUCTIONS FOR TRANSLATORS   ---
 *
 * If you want to write a new language file for your language,
 * please submit the file on SourceForge:
 *
 *   https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498548
 *
 * Use the option "Check to Upload and Attach a File" at the bottom
 *
 * Read the doc/README-translation.txt for translation instructions.
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
$lang['base_dn'] = 'Base DN';
$lang['search_scope'] = 'Search Scope';
$lang['show_attributes'] = 'Show Attributtes';
$lang['Search'] = 'Search';
$lang['predefined_search_str'] = 'Select a predefined search';
$lang['predefined_searches'] = 'Predefined Searches';
$lang['no_predefined_queries'] = 'No queries have been defined in config.php.';
$lang['export_results'] = 'export results';
$lang['unrecoginzed_search_result_format'] = 'Unrecognized search result format: %s';
$lang['format'] = 'Format';
$lang['list'] = 'list';
$lang['table'] = 'table';
$lang['bad_search_display'] = 'Your config.php specifies an invalid value for $default_search_display: %s. Please fix it';
$lang['page_n'] = 'Page %d';
$lang['next_page'] = 'Next Page';
$lang['no_results'] = 'The search found no results.';

// Tree browser
$lang['request_new_feature'] = 'Request feature';
$lang['report_bug'] = 'Report a bug';
$lang['schema'] = 'schema';
$lang['search'] = 'search';
$lang['create'] = 'create';
$lang['info'] = 'info';
$lang['import'] = 'import';
$lang['refresh'] = 'refresh';
$lang['logout'] = 'logout';
$lang['create_new'] = 'Create new entry here';
$lang['view_schema_for'] = 'View schema for';
$lang['refresh_expanded_containers'] = 'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'Create a new entry on';
$lang['new'] = 'new';
$lang['view_server_info'] = 'View server-supplied information';
$lang['import_from_ldif'] = 'Import entries from an LDIF file';
$lang['logout_of_this_server'] = 'Logout of this server';
$lang['logged_in_as'] = 'Logged in as: ';
$lang['this_base_dn_is_not_valid'] = 'This is not a valid DN.';
$lang['this_base_dn_does_not_exist'] = 'This entry does not exist.';
$lang['read_only'] = 'read only';
$lang['read_only_tooltip'] = 'This attribute has been flagged as read only by the phpLDAPadmin administrator';
$lang['could_not_determine_root'] = 'Could not determine the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'It appears that the LDAP server has been configured to not reveal its root.';
$lang['please_specify_in_config'] = 'Please specify it in config.php';
$lang['create_new_entry_in'] = 'Create a new entry in';
$lang['login_link'] = 'Login...';
$lang['login'] = 'login';
$lang['base_entry_does_not_exist'] = 'This base entry does not exist.';
$lang['create_it'] = 'Create it?';

// Entry display
$lang['delete_this_entry'] = 'Delete this entry';
$lang['delete_this_entry_tooltip'] = 'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'Copy or move this entry';
$lang['copy_this_entry_tooltip'] = 'Copy this object to another location, a new DN, or another server';
$lang['export'] = 'Export';
$lang['export_lcase'] = 'export';
$lang['export_tooltip'] = 'Save a dump of this object';
$lang['export_subtree_tooltip'] = 'Save a dump of this object and all of its children';
$lang['export_subtree'] = 'Export subtree';
$lang['create_a_child_entry'] = 'Create a child entry';
$lang['rename_entry'] = 'Rename Entry';
$lang['rename'] = 'Rename';
$lang['rename_lower'] = 'rename';
$lang['add'] = 'Add';
$lang['view'] = 'View';
$lang['view_one_child'] = 'View 1 child';
$lang['view_children'] = 'View %s children';
$lang['add_new_attribute'] = 'Add new attribute';
$lang['add_new_objectclass'] = 'Add new ObjectClass';
$lang['hide_internal_attrs'] = 'Hide internal attributes';
$lang['show_internal_attrs'] = 'Show internal attributes';
$lang['attr_name_tooltip'] = 'Click to view the schema defintion for attribute type \'%s\'';
$lang['none'] = 'none';
$lang['no_internal_attributes'] = 'No internal attributes';
$lang['no_attributes'] = 'This entry has no attributes';
$lang['save_changes'] = 'Save Changes';
$lang['add_value'] = 'add value';
$lang['add_value_tooltip'] = 'Add an additional value to attribute \'%s\'';
$lang['refresh_entry'] = 'Refresh';
$lang['refresh_this_entry'] = 'Refresh this entry';
$lang['delete_hint'] = 'Hint: To delete an attribute, empty the text field and click save.';
$lang['attr_schema_hint'] = 'Hint: To view the schema for an attribute, click the attribute name.';
$lang['attrs_modified'] = 'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'Viewing entry in read-only mode.';
$lang['no_new_attrs_available'] = 'no new attributes available for this entry';
$lang['no_new_binary_attrs_available'] = 'no new binary attributes available for this entry';
$lang['binary_value'] = 'Binary value';
$lang['add_new_binary_attr'] = 'Add new binary attribute';
$lang['alias_for'] = 'Note: \'%s\' is an alias for \'%s\'';
$lang['required_for'] = 'Required attribute for objectClass(es) %s';
$lang['required_by_entry'] = 'This attribute is required for the RDN.';
$lang['download_value'] = 'download value';
$lang['delete_attribute'] = 'delete attribute';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = 'none, remove value';
$lang['really_delete_attribute'] = 'Really delete attribute';
$lang['add_new_value'] = 'Add New Value';

// Schema browser
$lang['schema_retrieve_error_1']='The server does not fully support the LDAP protocol.';
$lang['schema_retrieve_error_2']='Your version of PHP does not correctly perform the query.';
$lang['schema_retrieve_error_3']='phpLDAPadmin doesn\'t know how to fetch the schema for your server.';
$lang['schema_retrieve_error_4']='Or lastly, your LDAP server doesnt provide this information.';
$lang['jump_to_objectclass'] = 'Jump to an objectClass';
$lang['view_schema_for_oclass'] = 'View the schema description for this objectClass';
$lang['jump_to_attr'] = 'Jump to an attribute type';
$lang['jump_to_matching_rule'] = 'Jump to a matching rule';
$lang['schema_for_server'] = 'Schema for server';
$lang['required_attrs'] = 'Required Attributes';
$lang['required'] = 'required';
$lang['optional_attrs'] = 'Optional Attributes';
$lang['optional_binary_attrs'] = 'Optional Binary Attributes';
$lang['OID'] = 'OID';
$lang['aliases']='Aliases';
$lang['desc'] = 'Description';
$lang['no_description']='no description';
$lang['name'] = 'Name';
$lang['equality']='Equality';
$lang['is_obsolete'] = 'This objectClass is obsolete.';
$lang['inherits'] = 'Inherits from';
$lang['inherited_from'] = 'Inherited from';
$lang['parent_to'] = 'Parent to';
$lang['jump_to_this_oclass'] = 'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'Matching Rule OID';
$lang['syntax_oid'] = 'Syntax OID';
$lang['not_applicable'] = 'not applicable';
$lang['not_specified'] = 'not specified';
$lang['character']='character'; 
$lang['characters']='characters';
$lang['used_by_objectclasses']='Used by objectClasses';
$lang['used_by_attributes']='Used by Attributes';
$lang['maximum_length']='Maximum Length';
$lang['attribute_types']='Attribute Types';
$lang['syntaxes']='Syntaxes';
$lang['matchingrules']='Matching Rules';
$lang['oid']='OID';
$lang['obsolete']='Obsolete';
$lang['ordering']='Ordering';
$lang['substring_rule']='Substring Rule';
$lang['single_valued']='Single Valued';
$lang['collective']='Collective';
$lang['user_modification']='User Modification';
$lang['usage']='Usage';
$lang['could_not_retrieve_schema_from']='Could not retrieve schema from';
$lang['type']='Type';
$lang['no_such_schema_item'] = 'No such schema item: "%s"';
$lang['random_password'] = 'A random password was generated for you';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Entry %s deleted successfully.';
$lang['you_must_specify_a_dn'] = 'You must specify a DN';
$lang['could_not_delete_entry'] = 'Could not delete the entry: %s';
$lang['no_such_entry'] = 'No such entry: %s';
$lang['delete_dn'] = 'Delete %s';
$lang['permanently_delete_children'] = 'Permanently delete all children also?';
$lang['entry_is_root_sub_tree'] = 'This entry is the root of a sub-tree containing %s entries.';
$lang['view_entries'] = 'view entries';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?';
$lang['confirm_recursive_delete_note'] = 'Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.';
$lang['delete_all_x_objects'] = 'Delete all %s objects';
$lang['recursive_delete_progress'] = 'Recursive delete progress';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Entry %s and sub-tree deleted successfully.';
$lang['failed_to_delete_entry'] = 'Failed to delete entry %s';
$lang['list_of_entries_to_be_deleted'] = 'List of entries to be deleted:';
$lang['sure_permanent_delete_object']='Are you sure you want to permanently delete this object?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.';
$lang['no_attr_specified'] = 'No attribute name specified.';
$lang['no_dn_specified'] = 'No DN specified';

// Adding attributes
$lang['left_attr_blank'] = 'You left the attribute value blank. Please go back and try again.';
$lang['failed_to_add_attr'] = 'Failed to add the attribute.';
$lang['file_empty'] = 'The file you chose is either empty or does not exist. Please go back and try again.';
$lang['invalid_file'] = 'Security error: The file being uploaded may be malicious.';
$lang['warning_file_uploads_disabled'] = 'Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.';
$lang['uploaded_file_too_big'] = 'The file you uploaded is too large. Please check php.ini, upload_max_size setting';
$lang['uploaded_file_partial'] = 'The file you selected was only partially uploaded, likley due to a network error.';
$lang['max_file_size'] = 'Maximum file size: %s';

// Updating values
$lang['modification_successful'] = 'Modification successful!';
$lang['change_password_new_login'] = 'Since you changed your password, you must now login again with your new password.';

// Adding objectClass form
$lang['new_required_attrs'] = 'New Required Attributes';
$lang['requires_to_add'] = 'This action requires you to add';
$lang['new_attributes'] = 'new attributes';
$lang['new_required_attrs_instructions'] = 'Instructions: In order to add these objectClass(es) to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'Add ObjectClass and Attributes';
$lang['objectclasses'] = 'ObjectClasses';

// General
$lang['chooser_link_tooltip'] = 'Click to popup a dialog to select an entry (DN) graphically';
$lang['date_selector_link'] = 'Click to popup a dialog to select a date graphically';
$lang['no_updates_in_read_only_mode'] = 'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = 'Bad server id';
$lang['not_enough_login_info'] = 'Not enough information to login to server. Please check your configuration.';
$lang['could_not_connect'] = 'Could not connect to LDAP server.';
$lang['could_not_connect_to_host_on_port'] = 'Could not connect to "%s" on port "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Could not perform ldap_mod_add operation.';
$lang['home'] = 'Home';
$lang['help'] = 'Help';
$lang['success'] = 'Success';
$lang['server_colon_pare'] = 'Server: ';
$lang['look_in'] = 'Looking in: ';
$lang['missing_dn_in_query_string'] = 'No DN specified in query string!';
$lang['back_up_p'] = 'Back Up...';
$lang['no_entries'] = 'no entries';
$lang['could_not_det_base_dn'] = 'Could not determine base DN';
$lang['reasons_for_error']='This could happen for several reasons, the most probable of which are:';
$lang['yes']='Yes';
$lang['no']='No';
$lang['go']='Go';
$lang['delete']='Delete';
$lang['back']='Back';
$lang['object']='object';
$lang['delete_all']='Delete all';
$lang['hint'] = 'hint';
$lang['bug'] = 'bug';
$lang['warning'] = 'warning';
$lang['light'] = 'light'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Proceed &gt;&gt;';
$lang['jpeg_dir_not_writable'] = 'Please set $jpeg_temp_dir to a writable directory in the phpLDAPadmin config.php';
$lang['jpeg_dir_not_writable_error'] = 'Could not write to the $jpeg_temp_dir directory %s. Please verify that your web server can write files there.';
$lang['jpeg_unable_toget'] = 'Could not fetch jpeg data from LDAP server for attribute %s.';
$lang['jpeg_delete'] = 'Delete photo';

// Add value form
$lang['add_new'] = 'Add new';
$lang['value_to'] = 'value to';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Current list of';
$lang['values_for_attribute'] = 'values for attribute';
$lang['inappropriate_matching_note'] = 'Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Note: You may be required to enter new attributes that these objectClass(es) require';
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
$lang['template'] = 'Template';
$lang['must_choose_template'] = 'You must choose a template';
$lang['invalid_template'] = '%s is an invalid template';
$lang['using_template'] = 'using template';
$lang['go_to_dn'] = 'Go to %s';
$lang['structural_object_class_cannot_remove'] = 'This is a structural ObjectClass and cannot be removed.';
$lang['structural'] = 'structural';

//copy_form.php
$lang['copyf_title_copy'] = 'Copy ';
$lang['copyf_to_new_object'] = 'to a new object';
$lang['copyf_dest_dn'] = 'Destination DN';
$lang['copyf_dest_dn_tooltip'] = 'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'Destination Server';
$lang['copyf_note'] = 'Hint: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = 'Recursively copy all children of this object as well.';
$lang['recursive_copy'] = 'Recursive copy';
$lang['filter'] = 'Filter';
$lang['filter_tooltip'] = 'When performing a recursive copy, only copy those entries which match this filter';
$lang['delete_after_copy'] = 'Delete after copy (move):';
$lang['delete_after_copy_warn'] = 'Make sure your filter (above) will select all child records.';

//create.php
$lang['create_required_attribute'] = 'You left the value blank for required attribute (%s).';
$lang['redirecting'] = 'Redirecting...';
$lang['here'] = 'here';
$lang['create_could_not_add'] = 'Could not add the object to the LDAP server.';

//create_form.php
$lang['createf_create_object'] = 'Create Object';
$lang['createf_choose_temp'] = 'Choose a template';
$lang['createf_select_temp'] = 'Select a template for the creation process';
$lang['save_as_file'] = 'Save as file';
$lang['rdn_field_blank'] = 'You left the RDN field blank.';
$lang['container_does_not_exist'] = 'The container you specified (%s) does not exist. Please try again.';
$lang['no_objectclasses_selected'] = 'You did not select any ObjectClasses for this object. Please go back and do so.';
$lang['hint_structural_oclass'] = 'Hint: You must choose exactly one structural objectClass (shown in bold above)';
$lang['template_restricted'] = 'This template is not allowed in this container.';
$lang['template_invalid'] = 'This template has been disabled, possibly due to missing schema or missing template XML fields.';

//creation_template.php
$lang['ctemplate_on_server'] = 'On server';
$lang['ctemplate_no_template'] = 'No template specified in POST variables.';
$lang['template_not_readable'] = 'Your config specifies a handler of "%s" for this template but this file is not readable because the permissions are too strict.';
$lang['template_does_not_exist'] = 'Your config specifies a handler of "%s" for this template but this handler does not exist in the templates/creation directory.';
$lang['create_step1'] = 'Step 1 of 2: Name and ObjectClass(es)';
$lang['create_step2'] = 'Step 2 of 2: Specify attributes and values';
$lang['relative_distinguished_name'] = 'Relative Distinguished Name';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(example: cn=MyNewPerson)';
$lang['container'] = 'Container';

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

// search_form_simple.php
$lang['equals'] = 'equals';
$lang['starts with'] = 'starts with';
$lang['contains'] = 'contains';
$lang['ends with'] = 'ends with';
$lang['sounds like'] = 'sounds like';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Could not retrieve LDAP information from the server. This may be due to a <a href="http://bugs.php.net/bug.php?id=29587">bug</a> in your version of PHP or perhaps your LDAP server has access control specified that prevents LDAP clients from accessing the RootDSE.';
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
$lang['credits'] = 'Credits';
$lang['changelog'] = 'ChangeLog';
$lang['documentation'] = 'Documentation';
$lang['donate'] = 'Donate';
$lang['pla_logo'] = 'phpLDAPadmin logo';

// Donate.php
$lang['donation_instructions'] = 'To donate funds to the phpLDAPadmin project, use one of the PayPal buttons below.';
$lang['donate_amount'] = 'Donate %s';

$lang['purge_cache'] = 'Purge caches';
$lang['no_cache_to_purge'] = 'No cache to purge.';
$lang['done_purging_caches'] = 'Purged %s bytes of cache.';
$lang['purge_cache_tooltip'] = 'Purge all cached data in phpLDAPadmin, including server schemas.';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Unsafe file name: ';
$lang['no_such_file'] = 'No such file: ';

//function.php
$lang['auto_update_not_setup'] = 'You have enabled auto_uid_numbers for <b>%s</b> in your configuration,
                                  but you have not specified the auto_uid_number_mechanism. Please correct
                                  this problem.';
$lang['uidpool_not_set'] = 'You specified the "auto_uid_number_mechanism" as "uidpool"
                            in your configuration for server <b>%s</b>, but you did not specify the
                            audo_uid_number_uid_pool_dn. Please specify it before proceeding.';
$lang['uidpool_not_exist'] = 'It appears that the uidPool you specified in your configuration ("%s")
                              does not exist.';
$lang['specified_uidpool'] = 'You specified the "auto_uid_number_mechanism" as "search" in your
                              configuration for server <b>%s</b>, but you did not specify the
                              "auto_uid_number_search_base". Please specify it before proceeding.';
$lang['auto_uid_invalid_credential'] = 'Unable to bind to <b>%s</b> with your with auto_uid credentials. Please check your configuration file.'; 
$lang['bad_auto_uid_search_base'] = 'Your phpLDAPadmin configuration specifies an invalid auto_uid_search_base for server %s';
$lang['auto_uid_invalid_value'] = 'You specified an invalid value for auto_uid_number_mechanism ("%s")
                                   in your configration. Only "uidpool" and "search" are valid.
                                   Please correct this problem.';
$lang['error_auth_type_config'] = 'Error: You have an error in your config file. The only three allowed values
                                    for auth_type in the $servers section are \'session\', \'cookie\', and \'config\'. You entered \'%s\',
                                    which is not allowed. ';
$lang['unique_attrs_invalid_credential'] = 'Unable to bind to <b>%s</b> with your with unique_attrs credentials. Please check your configuration file.'; 
$lang['unique_attr_failed'] = 'Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.';
$lang['php_install_not_supports_tls'] = 'Your PHP install does not support TLS.';
$lang['could_not_start_tls'] = 'Could not start TLS. Please check your LDAP server configuration.';
$lang['could_not_bind_anon'] = 'Could not bind anonymously to server.';
$lang['could_not_bind'] = 'Could not bind to the LDAP server.';
$lang['anonymous_bind'] = 'Anonymous Bind';
$lang['bad_user_name_or_password'] = 'Bad username or password. Please try again.';
$lang['successfully_logged_in_to_server'] = 'Successfully logged into server <b>%s</b>';
$lang['could_not_set_cookie'] = 'Could not set cookie.';
$lang['ldap_said'] = 'LDAP said: %s';
$lang['ferror_error'] = 'Error';
$lang['fbrowse'] = 'browse';
$lang['delete_photo'] = 'Delete Photo';
$lang['install_not_support_ext_des'] = 'Your system crypt library does not support extended DES encryption.';
$lang['install_not_support_blowfish'] = 'Your system crypt library does not support blowfish encryption.';
$lang['install_not_support_md5crypt'] = 'Your system crypt library does not support md5crypt encryption.';
$lang['install_no_mash'] = 'Your PHP install does not have the mhash() function. Cannot do SHA hashes.';
$lang['jpeg_contains_errors'] = 'jpegPhoto contains errors<br />';
$lang['ferror_number'] = 'Error number: %s (%s)';
$lang['ferror_discription'] = 'Description: %s <br /><br />';
$lang['ferror_number_short'] = 'Error number: %s<br /><br />';
$lang['ferror_discription_short'] = 'Description: (no description available)<br />';
$lang['ferror_submit_bug'] = 'Is this a phpLDAPadmin bug? If so, please <a href=\'%s\'>report it</a>.';
$lang['ferror_unrecognized_num'] = 'Unrecognized error number: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td>
             <td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr>
	<tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Please check and see if this bug has been reported here</a>.</center></td></tr>
	<tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>If it hasnt been reported, you may report this bug by clicking here</a>.</center></td></tr>
	</table></center><br />';
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
$lang['dont_stop_on_errors'] = 'Don\'t stop on errors';

//ldif_import
$lang['add_action'] = 'Adding...';
$lang['delete_action'] = 'Deleting...';
$lang['rename_action'] = 'Renaming...';
$lang['modify_action'] = 'Modifying...';
$lang['warning_no_ldif_version_found'] = 'No version found. Assuming 1.';
$lang['valid_dn_line_required'] = 'A valid dn line is required.';
$lang['missing_uploaded_file'] = 'Missing uploaded file.';
$lang['no_ldif_file_specified'] = 'No LDIF file specified. Please try again.';
$lang['ldif_file_empty'] = 'Uploaded LDIF file is empty.';
$lang['empty'] = 'empty';
$lang['file'] = 'File';
$lang['number_bytes'] = '%s bytes';
$lang['php_upload'] = 'Your PHP.INI does not have file_uploads = ON. Please enable file uploads in PHP.';

$lang['failed'] = 'Failed';
$lang['ldif_parse_error'] = 'LDIF Parse Error';
$lang['ldif_could_not_add_object'] = 'Could not add object:';
$lang['ldif_could_not_rename_object'] = 'Could not rename object:';
$lang['ldif_could_not_delete_object'] = 'Could not delete object:';
$lang['ldif_could_not_modify_object'] = 'Could not modify object:';
$lang['ldif_line_number'] = 'Line Number:';
$lang['ldif_line'] = 'Line:';

// Exports
$lang['export_format'] = 'Export format';
$lang['line_ends'] = 'Line ends';
$lang['must_choose_export_format'] = 'You must choose an export format.';
$lang['invalid_export_format'] = 'Invalid export format';
$lang['no_exporter_found'] = 'No available exporter found.';
$lang['error_performing_search'] = 'Encountered an error while performing search.';
$lang['showing_results_x_through_y'] = 'Showing results %s through %s.';
$lang['searching'] = 'Searching...';
$lang['size_limit_exceeded'] = 'Notice, search size limit exceeded.';
$lang['entry'] = 'Entry';
$lang['ldif_export_for_dn'] = 'LDIF Export for: %s';
$lang['generated_on_date'] = 'Generated by phpLDAPadmin ( http://phpldapadmin.sourceforge.net/ ) on %s';
$lang['total_entries'] = 'Total Entries';
$lang['dsml_export_for_dn'] = 'DSLM Export for: %s';
$lang['include_system_attrs'] = 'Include system attributes';
$lang['csv_spreadsheet'] = 'CSV (Spreadsheet)';

// logins
$lang['password_blank'] = 'You left the password blank.';
$lang['no_one_logged_in'] = 'No one is logged in to that server.';
$lang['could_not_logout'] = 'Could not logout.';
$lang['unknown_auth_type'] = 'Unknown auth_type: %s';
$lang['logged_out_successfully'] = 'Logged out successfully from server <b>%s</b>';
$lang['authenticate_to_server'] = 'Authenticate to server %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Warning: This web connection is unencrypted.';
$lang['not_using_https'] = 'You are not using \'https\'. Web browser will transmit login information in clear text.';
$lang['login_dn'] = 'Login DN';
$lang['user_name'] = 'User name';
$lang['password'] = 'Password';
$lang['authenticate'] = 'Authenticate';
$lang['login_not_allowed'] = 'Sorry, you are not allowed to use phpLDAPadmin with this LDAP server.';

// Entry browser
$lang['entry_chooser_title'] = 'Entry Chooser';

// Index page
$lang['need_to_configure'] = 'You need to configure phpLDAPadmin. Edit the file \'config.php\' to do so. An example config file is provided in \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Deletes not allowed in read only mode.';
$lang['error_calling_mass_delete'] = 'Error calling mass_delete.php. Missing mass_delete in POST vars.';
$lang['mass_delete_not_array'] = 'mass_delete POST var is not an array.';
$lang['mass_delete_not_enabled'] = 'Mass deletion is not enabled. Please enable it in config.php before proceeding.';
$lang['mass_deleting'] = 'Mass Deleting';
$lang['mass_delete_progress'] = 'Deletion progress on server "%s"';
$lang['malformed_mass_delete_array'] = 'Malformed mass_delete array.';
$lang['no_entries_to_delete'] = 'You did not select any entries to delete.';
$lang['deleting_dn'] = 'Deleting %s';
$lang['total_entries_failed'] = '%s of %s entries failed to be deleted.';
$lang['all_entries_successful'] = 'All entries deleted successfully.';
$lang['confirm_mass_delete'] = 'Confirm mass delete of %s entries on server %s';
$lang['yes_delete'] = 'Yes, delete!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'You cannot rename an entry which has children entries (eg, the rename operation is not allowed on non-leaf entries)';
$lang['no_rdn_change'] = 'You did not change the RDN';
$lang['invalid_rdn'] = 'Invalid RDN value';
$lang['could_not_rename'] = 'Could not rename the entry';

// Password checker
$lang['passwords_match'] = 'Passwords match!';
$lang['passwords_do_not_match'] = 'Passwords do not match!';
$lang['password_checker_tool'] = 'Password Checker Tool';
$lang['to'] = 'To';

// Templates
$lang['using'] = 'Using the';
$lang['switch_to'] = 'You may switch to the ';
$lang['default_template'] = 'default template';

// template_config
$lang['user_account'] = 'User Account (posixAccount)';
$lang['address_book_inet'] = 'Address Book Entry (inetOrgPerson)';
$lang['address_book_moz'] = 'Address Book Entry (mozillaOrgPerson)';
$lang['kolab_user'] = 'Kolab User Entry';
$lang['organizational_unit'] = 'Organisational Unit';
$lang['new_organizational_unit'] = 'New Organisational Unit';
$lang['organizational_role'] = 'Organisational Role';
$lang['posix_group'] = 'Posix Group';
$lang['samba_machine'] = 'Samba NT Machine';
$lang['samba3_machine'] = 'Samba 3 NT Machine';
$lang['samba_user'] = 'Samba User';
$lang['samba3_user'] = 'Samba 3 User';
$lang['samba3_group'] = 'Samba 3 Group Mapping';
$lang['dns_entry'] = 'DNS Entry';
$lang['simple_sec_object'] = 'Simple Security Object';
$lang['courier_mail_account'] = 'Courier Mail Account';
$lang['courier_mail_alias'] = 'Courier Mail Alias';
$lang['ldap_alias'] = 'LDAP Alias';
$lang['sendmail_cluster'] = 'Sendmail Cluster';
$lang['sendmail_domain'] = 'Sendmail Domain';
$lang['sendmail_alias'] = 'Sendmail Alias';
$lang['sendmail_virt_dom'] = 'Sendmail Virtual Domain';
$lang['sendmail_virt_users'] = 'Sendmail Virtual Users';
$lang['sendmail_relays'] = 'Sendmail Relays';
$lang['custom'] = 'Custom';
$lang['samba_domain_name'] = 'My Samba domain Name';
$lang['administrators'] = 'Administrators';
$lang['users'] = 'Users';
$lang['guests'] = 'Guests';
$lang['power_users'] = 'Power Users';
$lang['account_ops'] = 'Account Operators';
$lang['server_ops'] = 'Server Operators';
$lang['print_ops'] = 'Print Operators';
$lang['backup_ops'] = 'Backup Operators';
$lang['replicator'] = 'Replicator';
$lang['unable_smb_passwords'] = ' Unable to create the Samba passwords. Please, check the configuration in template_config.php';
$lang['err_smb_conf'] = 'Error: You have an error in your samba confguration.';
$lang['err_smb_no_name_sid'] = 'Error: A name and a sid for your samba domain need to be provided.';
$lang['err_smb_no_name'] = 'Error: No name provided for samba domain.';
$lang['err_smb_no_sid'] = 'Error: No sid provided for samba domain.';

// Samba Account Template
$lang['samba_account'] = 'Samba Account';
$lang['samba_account_lcase'] = 'samba account';

// New User (Posix) Account
$lang['t_new_user_account'] = 'New User Account';
$lang['t_hint_customize'] = 'Hint: To customize this template, edit the file templates/creation/new_user_template.php';
$lang['t_name'] = 'Name';
$lang['t_first_name'] = 'First name';
$lang['t_last_name'] = 'Last name';
$lang['t_first'] = 'first';
$lang['t_last'] = 'last';
$lang['t_state'] = 'State';
$lang['t_common_name'] = 'Common name';
$lang['t_user_name'] = 'User Name';
$lang['t_password'] = 'Password';
$lang['t_encryption'] = 'Encryption';
$lang['t_login_shell'] = 'Login Shell';
$lang['t_home_dir'] = 'Home Directory';
$lang['t_uid_number'] = 'UID Number';
$lang['t_auto_det'] = '(automatically determined)';
$lang['t_group'] = 'Group';
$lang['t_gid_number'] = 'GID Number';
$lang['t_uid'] = 'User ID';
$lang['t_err_passwords'] = 'Your passwords don\'t match. Please go back and try again.';
$lang['t_err_field_blank'] = 'You cannot leave the %s blank. Please go back and try again.';
$lang['t_err_field_num'] = 'You can only enter numeric values for the %s field. Please go back and try again.';
$lang['t_err_bad_container'] = 'The container you specified (%s) does not exist. Please go back and try again.';
$lang['t_confirm_account_creation'] = 'Confirm account creation';
$lang['t_secret'] = '[secret]';
$lang['t_create_account'] = 'Create Account';
$lang['t_verify'] = 'Verify';

// New Group (Posix)
$lang['t_new_posixgroup'] = 'New Posix Group';

// New Address Template
$lang['t_new_address'] = 'New Address Book Entry';
$lang['t_organization'] = 'Organisation';
$lang['t_address'] = 'Address';
$lang['t_city'] = 'City';
$lang['t_postal_code'] = 'Postal code';
$lang['t_street'] = 'Street';
$lang['t_work_phone'] = 'Work phone';
$lang['t_fax'] = 'Fax';
$lang['t_mobile'] = 'Mobile';
$lang['t_email'] = 'Email';
$lang['t_container'] = 'Container';
$lang['t_err_cn_blank'] = 'You cannot leave the Common Name blank. Please go back and try again.';
$lang['t_confim_creation'] = 'Confirm entry creation:';
$lang['t_create_address'] = 'Create Address';

// default template
$lang['t_check_pass'] = 'Check password...';
$lang['t_auto_submit'] = '(Auto evalutated on submission.)';

// compare form
$lang['compare'] = 'Compare';
$lang['comparing'] = 'Comparing the following DNs';
$lang['compare_dn'] = 'Compare another DN with';
$lang['with'] = 'with ';
$lang['compf_source_dn'] = 'Source DN';
$lang['compf_dn_tooltip'] = 'Compare this DN with another';
$lang['switch_entry'] = 'Switch Entry';
$lang['no_value'] = 'No Value';
$lang['compare_with'] = 'Compare with another entry';
$lang['need_oclass'] = 'You need one of the following ObjectClass(es) to add this attribute %s.';

// Time out page
$lang['session_timed_out_1'] = 'Your Session timed out after';
$lang['session_timed_out_2'] = 'min. of inactivity. You have been automatically logged out.';
$lang['log_back_in'] = 'To log back in please click on the following link:';
$lang['session_timed_out_tree'] = '(Session timed out. Automatically logged out.)';
$lang['timeout_at'] = 'Inactivity will log you off at %s';

$lang['template_mustattr'] = 'Attribute [%s] is a MUST attribute, so it cannot be disabled.';
$lang['template_nextpage'] = 'We are missing a page for [%s] attributes.';
?>
