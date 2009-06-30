<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/nl.php,v 1.11 2004/03/19 20:13:09 i18phpldapadmin Exp $


/*
 * Vertaling door Richard Lucassen <spamtrap@lucassen.org>
 * Commentaar gaarne naar bovenstaand adres sturen a.u.b.
 */

// Search form
$lang['simple_search_form_str'] = 'eenvoudig zoeken';//'Simple Search Form';
$lang['advanced_search_form_str'] = 'uitgebreid zoeken';//'Advanced Search Form';
$lang['server'] = 'server';//'Server';
$lang['search_for_entries_whose'] = 'zoek naar velden die';//'Search for entries whose';
$lang['base_dn'] = 'base DN';//'Base DN';
$lang['search_scope'] = 'zoekbereik';//'Search Scope';
$lang['search_ filter'] = 'zoekfilter';//'Search Filter';
$lang['show_attributes'] = 'laat Attributen zien';//'Show Attributtes';
$lang['Search'] = 'zoeken';//'Search';
$lang['equals'] = 'gelijk aan';//'equals';
$lang['starts_with'] = 'begint met';//'starts with';
$lang['contains'] = 'bevat';//'contains';
$lang['ends_with'] = 'eindigt met';//'ends with';
$lang['sounds_like'] = 'klinkt als';//'sounds like';

// Tree browser
$lang['request_new_feature'] = 'verzoek nieuwe feature';//'Request a new feature';
$lang['see_open_requests'] = 'feature verzoeken inzien';//'see open requests';
$lang['report_bug'] = 'rapporteer een bug';//'Report a bug';
$lang['see_open_bugs'] = 'buglijst inzien';//'see open bugs';
$lang['schema'] = 'schema';//'schema';
$lang['search'] = 'zoeken';//'search';
$lang['refresh'] = 'vernieuwen';//'refresh';
$lang['create'] = 'aanmaken';//'create';
$lang['info'] = 'info';//'info';
$lang['import'] = 'importeer';//'import';
$lang['logout'] = 'uitloggen';//'logout';
$lang['create_new'] = 'aanmaken';//'Create New';
$lang['view_schema_for'] = 'schema inzien voor';//'View schema for';
$lang['refresh_expanded_containers'] = 'vernieuw alle uitgeklapte containers voor';//'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'nieuw veld aanmaken in';//'Create a new entry on';
$lang['view_server_info'] = 'server informatie inzien';//'View server-supplied information';
$lang['import_from_ldif'] = 'importeer LDIF bestand';//'Import entries from an LDIF file';
$lang['logout_of_this_server'] = 'bij deze server uitloggen';//'Logout of this server';
$lang['logged_in_as'] = 'ingelogd als: ';//'Logged in as: ';
$lang['read_only'] = 'alleen lezen';//'read only';
$lang['could_not_determine_root'] = 'kan de root van LDAP structuur niet bepalen.';//'Could not determin the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'kan de root van LDAP structuur niet bepalen';//'It appears that the LDAP server has been configured to not reveal its root.';
$lang['please_specify_in_config'] = 'in config.php aangeven a.u.b.';//'Please specify it in config.php';
$lang['create_new_entry_in'] = 'nieuw veld aanmaken in';//'Create a new entry in';

// Entry display
$lang['delete_this_entry'] = 'veld verwijderen';//'Delete this entry';
$lang['delete_this_entry_tooltip'] = 'bevestiging zal worden gevraagd voor deze beslissing';//'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'dit veld kopieren';//'Copy this entry';
$lang['copy_this_entry_tooltip'] = 'kopieer dit object naar een andere plaats, een niuewe DN of naar een andere server';//'Copy this object to another location, a new DN, or another server';
$lang['export_to_ldif'] = 'exporteren naar LDIF';//'Export to LDIF';
$lang['export_to_ldif_tooltip'] = 'maak LDIF dump van dit object';//'Save an LDIF dump of this object';
$lang['export_subtree_to_ldif_tooltip'] = 'maak LDIF dump van dit object plus alle onderliggende objecten';//'Save an LDIF dump of this object and all of its children';
$lang['export_subtree_to_ldif'] = 'exporteer deze subvelden naar LDIF';//'Export subtree to LDIF';
$lang['export_mac'] = 'Macintosh regeleinden';//'Macintosh style line ends';
$lang['export_win'] = 'Windows regeleinden';//'Windows style line ends';
$lang['export_unix'] = 'Unix regeleinden';//'Unix style line ends';
$lang['create_a_child_entry'] = 'subveld aanmaken';//'Create a child entry';
$lang['add_a_jpeg_photo'] = 'jpeg foto toevoegen';//'Add a jpegPhoto';
$lang['rename_entry'] = 'veld hernoemen';//'Rename Entry';
$lang['rename'] = 'hernoemen';//'Rename';
$lang['add'] = 'toevoegen';//'Add';
$lang['view'] = 'inzien';//'View';
$lang['add_new_attribute'] = 'attribuut toevoegen';//'Add New Attribute';
$lang['add_new_attribute_tooltip'] = 'nieuw attribuut toevoegen/waarde toekennen';// 'Add a new attribute/value to this entry';
$lang['internal_attributes'] = 'interne attributen';//'Internal Attributes';
$lang['hide_internal_attrs'] = 'interne attributen verbergen';//'Hide internal attributes';
$lang['show_internal_attrs'] = 'interne attributen laten zien';//'Show internal attributes';
$lang['internal_attrs_tooltip'] = 'automatisch ingestelde attributen';//'Attributes set automatically by the system';
$lang['entry_attributes'] = 'attributen veld';//'Entry Attributes';
$lang['attr_name_tooltip'] = 'klik hier om de schemadefinitie van attribuuttype \'%s\' te bekijken';//'Click to view the schema defintion for attribute type \'%s\'';
$lang['click_to_display'] = 'klik om te bekijken';//'click to display';
$lang['hidden'] = 'verborgen';//'hidden'; 
$lang['none'] = 'geen';//'none';
$lang['save_changes'] = 'veranderingen opslaan';//'Save Changes';
$lang['add_value'] = 'waarde toevoegen';//'add value';
$lang['add_value_tooltip'] = 'voeg een extra waarde toe aan dit attribuut';//'Add an additional value to this attribute';
$lang['refresh_entry'] = 'vernieuwen';// 'Refresh';
$lang['refresh_this_entry'] = 'dit veld vernieuwen';//'Refresh this entry';
$lang['delete_hint'] = 'tip: <b>om een attribuut te verwijderen</b>, maak deze leeg en sla hem op';//'Hint: <b>To delete an attribute</b>, empty the text field and click save.';
$lang['attr_schema_hint'] = 'Tip: <b>om het schema voor een attribuut te bekijken,</b> klik op de attribuutnaam';//'Hint: <b>To view the schema for an attribute</b>, click the attribute name.';
$lang['attrs_modified'] = 'sommige attributen (%s) zijn gewijzigd en worden ge-highlight weergegeven.';//'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'een attribuut (%s) is gewijzigd en wordt ge-highlight weergegeven';//'An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'veld bekijken (alleen-lezen)';//'Viewing entry in read-only mode.';
$lang['change_entry_rdn'] = 'verander de RDN van dit veld';//'Change this entry\'s RDN';
$lang['no_new_attrs_available'] = 'geen nieuwe attributen beschikbaar voor dit veld';//'no new attributes available for this entry';
$lang['binary_value'] = 'binaire waarde';//'Binary value';
$lang['add_new_binary_attr'] = 'nieuwe binair attribuut toevoegen';//'Add New Binary Attribute';
$lang['add_new_binary_attr_tooltip'] = 'lees binair attribuut in vanuit een bestand';//'Add a new binary attribute/value from a file';
$lang['alias_for'] = 'alias voor';//'Alias for';
$lang['download_value'] = 'waarde downloaden';//'download value';
$lang['delete_attribute'] = 'attribuut verwijderen';//'delete attribute';
$lang['true'] = 'waar';//'true';
$lang['false'] = 'onwaar';//'false';
$lang['none_remove_value'] = 'niets, verwijder waarde';//?? //'none, remove value';
$lang['really_delete_attribute'] = 'dit attribuut echt verwijderen';//'Really delete attribute';

// Schema browser
$lang['the_following_objectclasses'] = 'Deze LDAP server ondersteunt de volgende <b>objectClasses</b>.';//'The following <b>objectClasses</b> are supported by this LDAP server.';
$lang['the_following_attributes'] = 'Deze LDAP server ondersteunt de volgende <b>attributeTypes</b>.';//'The following <b>attributeTypes</b> are supported by this LDAP server.';
$lang['the_following_matching'] = 'Deze LDAP server ondersteunt de volgende <b>zoekregels</b>.';//'The following <b>matching rules</b> are supported by this LDAP server.';
$lang['the_following_syntaxes'] = 'Deze LDAP server ondersteunt de volgende <b>syntaxen</b>.';//'The following <b>syntaxes</b> are supported by this LDAP server.';
$lang['jump_to_objectclass'] = 'ga naar objectClass';//'Jump to an objectClass';
$lang['jump_to_attr'] = 'ga naar een attribuut';//'Jump to an attribute';
$lang['schema_for_server'] = 'schema voor server';//'Schema for server';
$lang['required_attrs'] = 'vereiste attributen';//'Required Attributes';
$lang['optional_attrs'] = 'niet vereiste attributen';//'Optional Attributes';
$lang['OID'] = 'OID';//'OID';
$lang['desc'] = 'omschrijving';//'Description';
$lang['name'] = 'naam';//'Name';
$lang['is_obsolete'] = 'deze objectClass is <b>verouderd</b>';//'This objectClass is <b>obsolete</b>';
$lang['inherits'] = 'afgeleid van';//'Inherits';
$lang['jump_to_this_oclass'] = 'ga naar objectClass definitie';//'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'overeenkomen met OID regel';//'Matching Rule OID';
$lang['syntax_oid'] = 'Syntax OID';//'Syntax OID';
$lang['not_applicable'] = 'niet van toepassing';//'not applicable';
$lang['not_specified'] = 'niet gespecificeerd';//not specified';

// Deleting entries
$lang['entry_deleted_successfully'] = 'dit veld \'%s\' succesvol verwijderd';//'Entry \'%s\' deleted successfully.';
$lang['you_must_specify_a_dn'] = 'U moet een DN aangeven.';//'You must specify a DN';
$lang['could_not_delete_entry'] = 'kan dit veld niet verwijderen: %s';//'Could not delete the entry: %s';


// Adding objectClass form
$lang['new_required_attrs'] = 'nieuwe benodigde attributen';//'New Required Attributes';
$lang['requires_to_add'] = 'voor deze actie moet worden toegevoegd:';//'This action requires you to add';
$lang['new_attributes'] = 'nieuw attribuut';//'new attributes';
$lang['new_required_attrs_instructions'] = 'Instructies: om deze objectClass toe te voegen, moet u nog specificeren ';//'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'dat deze objectClass nodig heeft. U kunt dit in dit formulier doen.';//'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'objectClass en attributen toevoegen';//'Add ObjectClass and Attributes';

// General
$lang['chooser_link_tooltip'] = 'klik om grafisch een veld te kiezen (DN)';//"Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'U kunt niet opslaan als de server alleen lezen is';//'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = 'ongeldig server ID';//'Bad server id';
$lang['not_enough_login_info'] = 'Onvoldoende informatie om in te kunnen loggen. Controleer de configuratie.';//'Not enough information to login to server. Please check your configuration.';
$lang['could_not_connect'] = 'Kan LDAP server niet vinden.';//'Could not connect to LDAP server.';
$lang['could_not_perform_ldap_mod_add'] = 'Kan geen ldap_mod_add uitvoeren.';//'Could not perform ldap_mod_add operation.';
$lang['bad_server_id_underline'] = 'ongeldig server_ID:';//"Bad server_id: ';
$lang['success'] = 'succes';//"Success';
$lang['server_colon_pare'] = 'server: ';//"Server: ';
$lang['look_in'] = 'bekijken: ';//"Looking in: ';
$lang['missing_server_id_in_query_string'] = 'geen server ID meegegeven';//'No server ID specified in query string!';
$lang['missing_dn_in_query_string'] = 'geen DN meegeven';//'No DN specified in query string!';
$lang['back_up_p'] = 'backup...';//"Back Up...';
$lang['no_entries'] = 'geen velden';//"no entries';
$lang['not_logged_in'] = 'niet ingelogd';//"Not logged in';
$lang['could_not_det_base_dn'] = 'kan de basis-DN niet bepalen';//"Could not determine base DN';


// Add value form
$lang['add_new'] = 'nieuw toevoegen';//'Add new';
$lang['value_to'] = 'waarde';//'value to';
$lang['server'] = 'server';//'Server';
//also used in copy_form.php
$lang['distinguished_name'] = 'Distinguished Name';// 'Distinguished Name';
$lang['current_list_of'] = 'huidige lijst van';//'Current list of';
$lang['values_for_attribute'] = 'waarden voor attributen';//'values for attribute';
$lang['inappropriate_matching_note'] = 'Info: U zult een "inappropriate matching" melding krijgen, indien u niet<br />' . //'Note: You will get an "inappropriate matching" error if you have not<br />' .
'een <tt>EQUALITY</tt> regel op de LDAP Server voor dit attribuut ingesteld heeft.';//'setup an <tt>EQUALITY</tt> rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'geef de waarde die u wilt toevoegen:';//'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Info: U kunt verzocht worden nieuwe attributen, die voor deze objectClass verplicht zijn, in te voeren.';//'Note: you may be required to enter new attributes<br />that this objectClass requires.';
$lang['syntax'] = 'syntax';//'Syntax';

//Copy.php
$lang['copy_server_read_only'] = 'U kunt niet opslaan als de server alleen lezen is';//"You cannot perform updates while server is in read-only mode';
$lang['copy_dest_dn_blank'] = 'de bestemmings DN is leeg';//"You left the destination DN blank.';
$lang['copy_dest_already_exists'] = 'het veld (%s) bestaat al.';//"The destination entry (%s) already exists.';
$lang['copy_dest_container_does_not_exist'] = 'het doel-veld (%s) bestaat niet.';//'The destination container (%s) does not exist.';
$lang['copy_source_dest_dn_same'] = 'origineel DN en doel DN zijn hetzelfde';//"The source and destination DN are the same.';
$lang['copy_copying'] = 'kopieren';//"Copying ';
$lang['copy_recursive_copy_progress'] = 'bezig met recursief kopieren';//"Recursive copy progress';
$lang['copy_building_snapshot'] = 'bezig met het aanmaken van een snapshot van de boomstructuur... ';//"Building snapshot of tree to copy... ';
$lang['copy_successful_like_to'] = 'Kopieren succesvol! Wit u dan';//"Copy successful! Would you like to ';
$lang['copy_view_new_entry'] = 'het nieuwe veld bekijken';//"view the new entry';
$lang['copy_failed'] = 'Kopieren van DN mislukt: ';//'Failed to copy DN: ';


//edit.php
$lang['missing_template_file'] = 'Waarschuwing: kan de template file niet vinden';//'Warning: missing template file, ';
$lang['using_default'] = 'standaardinstelling gebruiken';//'Using default.';

//copy_form.php
$lang['copyf_title_copy'] = 'Kopieren';//"Copy ';
$lang['copyf_to_new_object'] = 'naar een nieuw Objekt';//"to a new object';
$lang['copyf_dest_dn'] = 'doel DN';//"Destination DN';
$lang['copyf_dest_dn_tooltip'] = 'De complete DN die aangemaakt wordt bij het kopieren van het bron-veld.';//'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'bestemmings server';//"Destination Server';
$lang['copyf_note'] = 'Info: kopieren tussen twee servers gaat alleen maar goped als er geen schema problemen zijn';//"Note: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = 'Recursief kopieren van alle sub-velden';//"Recursively copy all children of this object as well.';

//create.php
$lang['create_required_attribute'] = 'Fout. U heeft een verplicht veld leeggelaten.';//"Error, you left the value blank for required attribute ';
$lang['create_redirecting'] = 'omleiden';//"Redirecting';
$lang['create_here'] = 'hier';//"here';
$lang['create_could_not_add'] = 'kan het object niet toevoegen op de LDAP server.';//"Could not add the object to the LDAP server.';

//create_form.php
$lang['createf_create_object'] = 'object aanmaken';//"Create Object';
$lang['createf_choose_temp'] = 'kies een template';//"Choose a template';
$lang['createf_select_temp'] = 'kies een template voor dit object';//"Select a template for the creation process';
$lang['createf_proceed'] = 'verder';//"Proceed';

//creation_template.php
$lang['ctemplate_on_server'] = 'op server';//"On server';
$lang['ctemplate_no_template'] = 'geen template gespecifieerd in de POST variabelen';//"No template specified in POST variables.';
$lang['ctemplate_config_handler'] = 'uw configuratie specificeert een routine';//"Your config specifies a handler of';
$lang['ctemplate_handler_does_not_exist'] = 'in deze template. Maar deze routine bestaat niet in de \'templates/creation\' directory.';//"for this template. But, this handler does not exist in the 'templates/creation' directory.';

// search.php
$lang['you_have_not_logged_into_server'] = 'u bent nog niet ingelogd op de geselecteerde server, dus u kunt geen zoekopdrachten geven.';//'You have not logged into the selected server yet, so you cannot perform searches on it.';
$lang['click_to_go_to_login_form'] = 'Klik hier om in te loggen';//'Click here to go to the login form';
$lang['unrecognized_criteria_option'] = 'Unrecognized criteria option: ';
$lang['if_you_want_to_add_criteria'] = 'Als u uw eigen crteria toe wilt voegen aan de lijst, dient u search.php te bewerken. Sluiten.';//'If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.';
$lang['entries_found'] = 'gevonden velden: ';//'Entries found: ';
$lang['filter_performed'] = 'toegepast filter: ';//'Filter performed: ';
$lang['search_duration'] = 'zoeken door phpLDAPadmin duurde';//'Search performed by phpLDAPadmin in';
$lang['seconds'] = 'seconden';//'seconds';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'zoekbereik';//'The scope in which to search';
$lang['scope_sub'] = 'Sub (de hele tak)';//'Sub (entire subtree)';
$lang['scope_one'] = 'One (een laag diep)';//'One (one level beneath base)';
$lang['scope_base'] = 'Base (alleen de basis)';//'Base (base dn only)';
$lang['standard_ldap_search_filter'] = 'Standard LDAP zoekfilter. Voorbeeld.: (&(sn=Smith)(givenname=David))';//'Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'zoekfilter';//'Search Filter';
$lang['list_of_attrs_to_display_in_results'] = 'komma gescheiden lijst van de attributen.';//'A list of attributes to display in the results (comma-separated)';
$lang['show_attributes'] = 'attributen laten zien';//'Show Attributes';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'zoek naar velden waarvoor:';//'Search for entries whose:';
$lang['equals'] = 'gelijk is aan';//'equals';
$lang['starts with'] = 'begint met';//'starts with';
$lang['contains'] = 'bevat';//'contains';
$lang['ends with'] = 'eindigt met';//'ends with';
$lang['sounds like'] = 'klinkt als';//'sounds like';
$lang['predefined_search_str'] = 'of een van deze lijst uitlezen';//'or select a predefined search';

// server_info.php
$lang['could_not_fetch_server_info'] = 'kan geen LDAP van de server krijgen';//'Could not retrieve LDAP information from the server';
$lang['server_info_for'] = 'Server info voor: ';//'Server info for: ';
$lang['server_reports_following'] = 'De server geeft de volgende informatie over zichzelf';//'Server reports the following information about itself';
$lang['nothing_to_report'] = 'De server heeft niets te melden';//'This server has nothing to report.';

//update.php
$lang['update_array_malformed'] = 'De update_array is niet goed. Dat kan een phpLDAPadmin bug zijn. Laat het ons weten!';//'update_array is malformed. This might be a phpLDAPadmin bug. Please report it.';
$lang['could_not_perform_ldap_modify'] = 'Kan ldap_modify niet uitvoeren.';//'Could not perform ldap_modify operation.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Deze veranderingen doorvoeren?';//'Do you want to make these changes?';
$lang['attribute'] = 'attribuut';//'Attribute';
$lang['old_value'] = 'oude waarde';//'Old Value';
$lang['new_value'] = 'nieuwe waarde';//'New Value';
$lang['attr_deleted'] = '[attribuut verwijderd]';//'[attribute deleted]';
$lang['commit'] = 'uitvoeren';//'Commit';
$lang['cancel'] = 'annuleren';//'Cancel';
$lang['you_made_no_changes'] = 'U heeft geen veranderingen gemaakt.';//'You made no changes';
$lang['go_back'] = 'terug';//'Go back';

// welcome.php
$lang['welcome_note'] = 'Gebruik het linkermenu om te navigeren.';//'Use the menu to the left to navigate';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'onveilige bestandsnaam: ';//'Unsafe file name: ';
$lang['no_such_file'] = 'Bestand bestaat niet: ';//'No such file: ';

//function.php
$lang['auto_update_not_setup'] = '<tt>auto_uid_numbers</tt> is geactiveerd (<b>%s</b>, maar niet het mechanisme (auto_uid_number_mechanism). U dient dit alsnog te doen.';//"You have enabled auto_uid_numbers for <b>%s</b> in your configuration, but you have not specified the auto_uid_number_mechanism. Please correct this problem.';
$lang['uidpool_not_set'] = 'Het mechanisme <tt>auto_uid_number_mechanism</tt> is als <tt>uidpool</tt> voor server <b>%s</b> vastgelegd, maar niet de <tt>auto_uid_number_uid_pool_dn</tt>. U dient dit alsnog te doen.';//"You specified the <tt>auto_uid_number_mechanism</tt> as <tt>uidpool</tt> in your configuration for server <b>%s</b>, but you did not specify the audo_uid_number_uid_pool_dn. Please specify it before proceeding.';

$lang['uidpool_not_exist'] = 'De <tt>uidPool</tt> die gespecificeerd is in de configuratie bestaat niet.';//"It appears that the uidPool you specified in your configuration (<tt>%s</tt>) does not exist.';

$lang['specified_uidpool'] = 'De <tt>auto_uid_number_mechanism</tt> is als <tt>search</tt> in de configuratie voor de server <b>%s</b> bepaald, maar de waarde <tt>auto_uid_number_search_base</tt> niet. U dient dit alsnog te doen.';//"You specified the <tt>auto_uid_number_mechanism</tt> as <tt>search</tt> in your configuration for server <b>%s</b>, but you did not specify the <tt>auto_uid_number_search_base</tt>. Please specify it before proceeding.';

$lang['auto_uid_invalid_value'] = 'Ongeldige waarde voor <tt>auto_uid_number_mechanism</tt>(%s). Alleen <tt>uidpool</tt> und <tt>search</tt> zijn geldig. Gaarne de fout herstellen ';//"You specified an invalid value for auto_uid_number_mechanism (<tt>%s</tt>) in your configration. Only <tt>uidpool</tt> and <tt>search</tt> are valid. Please correct this problem.';

$lang['error_auth_type_config'] = 'Fout: Er zit een fout inde configuratiefile (config.php). De enige twee waarden voor \'auth_type\' in de $servers sectie zijn: <b>\'config\'</b> of <b>\'form\'</b>. U heeft er nu <b>%s</b> in staan en dat kan niet.';//"Error: You have an error in your config file. The only two allowed values for 'auth_type' in the $servers section are 'config' and 'form'. You entered '%s', which is not allowed. ';

$lang['php_install_not_supports_tls'] = 'Uw installatie ondersteunt geen TLS.';//"Your PHP install does not support TLS';
$lang['could_not_start_tls'] = 'Kan TLS niet starten.<br/>Controleer de LDAP-Server-configuratie.';//"Could not start TLS.<br />Please check your LDAP server configuration.';
$lang['auth_type_not_valid'] = 'Fout in de configuratiefile: auth_type %s is niet geldig'; //"You have an error in your config file. auth_type of %s is not valid.';
$lang['ldap_said'] = '<b>LDAP zegt</b>: %s<br/><br/>';//"<b>LDAP said</b>: %s<br /><br />';
$lang['ferror_error'] = 'Fout';//"Error';
$lang['fbrowse'] = 'navigeer';//"browse';
$lang['delete_photo'] = 'Foto verwijderen';//"Delete Photo';
$lang['install_not_support_blowfish'] = 'Uw PHP-Versie ondersteunt geen Blowfish versleuteling.';//"Your PHP install does not support blowfish encryption.';
$lang['install_no_mash'] = 'Uw PHP-Versie ondersteunt de functie mhash() niet, dus de SHA-hash is niet mogelijk.';// "Your PHP install does not have the mhash() function. Cannot do SHA hashes.';
$lang['jpeg_contains_errors'] = 'Foto (jpg) bevat fouten';//"jpegPhoto contains errors<br />';
$lang['ferror_number'] = '<b>Foutnummer:</b> %s<small>(%s)</small><br/><br/>';//"<b>Error number</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] ='<b>Omschrijving:</b> %s<br/><br/>';// "<b>Description</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Foutnummer:</b>%s<br/><br/>';//"<b>Error number</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Omschrijving:</b> (geen omschrijving beschikbaar)<br/>';//"<b>Description</b>: (no description available)<br />';
$lang['ferror_submit_bug'] = 'Is het een phpLDAPadmin fout? Als dat zo is, dan gaarne een <a href=\'%s\'>bugreport</a> invullen';//"Is this a phpLDAPadmin bug? If so, please <a href=\'%s\'>report it</a>.';
$lang['ferror_unrecognized_num'] = 'Onbekend foutnummer:';//"Unrecognized error number: ';

$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' /><b>Een niet fatale fout in phpLDAPadmin gevonden!</b></td></tr><tr><td>Fout:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Bestand:</td><td><b>%s</b>Regel:<b>%s</b>, aangeroepen door <b>%s</b></td></tr><tr><td>Versie:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b></td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center>Graag een <a target=\'new\' href=\'%s\'>bugreport</a> invullen.</center></td></tr></table></center><br />';//"<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' /><b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td><td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b></td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Please report this bug by clicking here</a>.</center></td></tr></table></center><br />';

$lang['ferror_congrats_found_bug'] = '<center><table class=\'notice\'>
			<tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' /><b>Gefeliciteerd! Een fout in phpLDAPadmin gevonden!</b></td></tr>
			<tr><td>Fout:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Bestand:</td><td><b>%s</b>, aangeroepen door <b>%s</b></td></tr>
			<tr><td>Versie:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b></td></tr>
			<tr><td>Web server:</td><td><b>%s</b></td></tr>
			<tr><td colspan=\'2\'><center>Graag een bugreport invullen.</center></td></tr>
			</table></center><br />';//"Congratulations! You found a bug in phpLDAPadmin.<br /><br /><table class=\'bug\'><tr><td>Error:</td><td><b>%s</b></td></tr><tr><td>Level:</td><td><b>%s</b></td></tr><tr><td>File:</td><td><b>%s</b></td></tr><tr><td>Line:</td><td><b>%s</b></td></tr><tr><td>Caller:</td><td><b>%s</b></td></tr><tr><td>PLA Version:</td><td><b>%s</b></td></tr><tr><td>PHP Version:</td><td><b>%s</b></td></tr><tr><td>PHP SAPI:</td><td><b>%s</b></td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr></table><br /> Please report this bug by clicking below!';

// extra strings:

$lang['login_link'] = 'Login...';
$lang['import_ldif_file_title'] = 'Import file from LDIF';
$lang['select_ldif_file'] = 'Selecteer een LDIF file:';
$lang['select_ldif_file_proceed']= 'Ga door &gt;&gt;';
$lang['add_action'] =  'Toevoegen...';
$lang['delete_action'] = 'Verwijderen...';
$lang['rename_action'] = 'Hernoemen...';
$lang['modify_action'] = 'Veranderen...';
$lang['failed'] = 'mislukt';
$lang['ldif_parse_error'] = 'LDIF inleesfout';
$lang['ldif_could_not_add_object'] = 'Kan object niet toevoegen:';
$lang['ldif_could_not_rename_object'] = 'Kan object niet hernoemen';
$lang['ldif_could_not_delete_object'] = 'Kan object niet verwijderen';
$lang['ldif_could_not_modify_object'] = 'Kan object niet wijzigen';
$lang['ldif_line_number'] = 'regelnummer: ';
$lang['ldif_line'] = 'regel: ';

$lang['credits'] = 'Credits';//'Credits';
$lang['changelog'] = 'Changelog';//'ChangeLog';
$lang['documentation'] = 'Documentatie';// 'Documentation';


?>
