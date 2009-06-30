<?php

$lang = array();

// Search form
$lang['simple_search_form_str'] = 'Simple Search Form';
$lang['advanced_search_form_str'] = 'Advanced Search Form';
$lang['server'] = 'Server';
$lang['search_for_entries_whose'] = 'Search for entries whose';
$lang['base_dn'] = 'Base DN';
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
$lang['refresh'] = 'refresh';
$lang['create'] = 'create';
$lang['info'] = 'info';
$lang['import'] = 'import';
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
$lang['could_not_determine_root'] = 'Could not determin the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'It appears that the LDAP server has been configured to not reveal its root.'; 
$lang['please_specify_in_config'] = 'Please specify it in config.php';
$lang['create_new_entry_in'] = 'Create a new entry in';

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
$lang['click_to_display'] = 'click to display'; 
$lang['hidden'] = 'hidden'; 
$lang['none'] = 'none'; 
$lang['save_changes'] = 'Save Changes';
$lang['add_value'] = 'add value';
$lang['add_value_tooltip'] = 'Add an additional value to this attribute';
$lang['refresh'] = 'refresh';
$lang['refresh_this_entry'] = 'Refresh this entry';
$lang['delete_hint'] = 'Hint: <b>To delete an attribute</b>, empty the text field and click save.';
$lang['viewing_read_only'] = 'Viewing entry in read-only mode.';
$lang['change_entry_rdn'] = 'Change this entry\'s RDN';
$lang['no_new_attrs_available'] = 'no new attributes available for this entry';
$lang['binary_value'] = 'Binary value';
$lang['add_new_binary_attr'] = 'Add New Binary Attribute';
$lang['add_new_binary_attr_tooltip'] = 'Add a new binary attribute/value from a file';
$lang['alias_for'] = 'Alias for';
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
$lang['is_obsolete'] = 'This objectClass is <b>obsolete</b>';
$lang['inherits'] = 'Inherits';
$lang['jump_to_this_oclass'] = 'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'Matching Rule OID';
$lang['syntax_oid'] = 'Syntax OID';

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

// Add value form
$lang['add_new'] = 'Add new';
$lang['value_to'] = 'value to';
$lang['server'] = 'Server';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Current list of';
$lang['values_for_attribute'] = 'values for attribute';
$lang['inappropriate_matching_note'] = 'Note: You will get an "inappropriate matching" error if you have not<br />' .
			'setup an <tt>EQUALITY</tt> rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Note: you may be required to enter new attributes<br />that this objectClass requires.'; 
$lang['syntax'] = 'Syntax';

?>
