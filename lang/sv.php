<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/sv.php,v 1.2 2004/03/19 20:13:09 i18phpldapadmin Exp $

/*
 * Translated by Gunnar Nystrom <gunnar (dot) a (dot) nystrom (at) telia (dot)  com>
 * based on 0.9.3 Version
 
*/

// Search form
$lang['simple_search_form_str'] = 'Enkel sökning';//'Simple Search Form';
$lang['advanced_search_form_str'] = 'Expertsökning';//'Advanced Search Form';
$lang['server'] = 'Server';//'Server';
$lang['search_for_entries_whose'] = 'Sök efter rader som';//'Search for entries whose';
$lang['base_dn'] = 'Base DN';//'Base DN';
$lang['search_scope'] = 'Sökomfång';//Search Scope';
$lang['search_ filter'] = 'Sökfilter';//'Search Filter';
$lang['show_attributes'] = 'Visa attribut';//'Show Attributtes';
$lang['Search'] = 'Sök';// 'Search';
$lang['equals'] = 'lika med';//'equals';
$lang['starts_with'] = 'Börjar med';//'starts with';
$lang['contains'] = 'innehåller';//'contains';
$lang['ends_with'] = 'slutar med';//'ends with';
$lang['sounds_like'] = 'låter som';//'sounds like';

// Tree browser
$lang['request_new_feature'] = 'Begär en ny funktion';//'Request a new feature';
$lang['see_open_requests'] = 'Se öppna förfrågningar';//'see open requests';
$lang['report_bug'] = 'Rapportera ett fel';//'Report a bug';
$lang['see_open_bugs'] = 'Se öppna felrapporter';//'see open bugs';
$lang['schema'] = 'schema';//'schema';
$lang['search'] = 'sökning';//'search';
$lang['create'] = 'skapa';//'create';
$lang['info'] = 'information';//'info';
$lang['import'] = 'importera';//'import';
$lang['refresh'] = 'uppdatera';//'refresh';
$lang['logout'] = 'logga ut';//'logout';
$lang['create_new'] = 'Skapa ny';//'Create New';
$lang['view_schema_for'] = 'Titta på schema för';//'View schema for';
$lang['refresh_expanded_containers'] = 'Uppdatera alla öpnna behållare för';//'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'Skapa en ny post för';//'Create a new entry on';
$lang['view_server_info'] = 'Titta på information som servern tillhandahållit';//'View server-supplied information';
$lang['import_from_ldif'] = 'Importera rader från LDIF file';//'Import entries from an LDIF file';
$lang['logout_of_this_server'] = 'Logga ut från den här servern';//'Logout of this server';
$lang['logged_in_as'] = '/Inloggad som';//'Logged in as: ';
$lang['read_only'] = 'Enbart läsning';//'read only';
$lang['could_not_determine_root'] = 'Kan inte bestämma roten för ditt LDAP träd';//'Could not determine the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'Det ser ut som om LDAP-servern har konfigurerats att inte avslöja sin rot.';//'It appears that the LDAP server has been configured to not reveal its root.';
$lang['please_specify_in_config'] = 'Var snäll och specificera i config.php';//'Please specify it in config.php';
$lang['create_new_entry_in'] = 'Skapa en ny post i';//'Create a new entry in';
$lang['login_link'] = 'Logga in...';//'Login...';

// Entry display
$lang['delete_this_entry'] = 'Ta bort den här posten';//'Delete this entry';
$lang['delete_this_entry_tooltip'] = 'Du kommer att bli tillfrågad för att konfirmera det här beslutet';//'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'Kopiera den här posten';//'Copy this entry';
$lang['copy_this_entry_tooltip'] = 'Kopiera det här objektet till en annan plats, ett nytt DN, eller en annan server';//'Copy this object to another location, a new DN, or another server';
$lang['export_to_ldif'] = 'Exportera till LDIF';//'Export to LDIF';
$lang['export_to_ldif_tooltip'] = 'Spara en LDIF kopia av detta objekt';//'Save an LDIF dump of this object';
$lang['export_subtree_to_ldif_tooltip'] = 'Spara en LDIF kopia av detta objekt och alla dess underobjekt';//'Save an LDIF dump of this object and all of its children';
$lang['export_subtree_to_ldif'] = 'Exportera subträdet till LDIF';//'Export subtree to LDIF';
$lang['export_to_ldif_mac'] = 'Radslut enligt Macintosh-standard';// 'Macintosh style line ends';
$lang['export_to_ldif_win'] = 'Radslut enligt Windows-standard';//'Windows style line ends';
$lang['export_to_ldif_unix'] = 'Radslut enligt Unix-standard';//'Unix style line ends';
$lang['create_a_child_entry'] = 'Skapa en subpost';//'Create a child entry';
$lang['add_a_jpeg_photo'] = 'Lägg till ett JPEG-foto';//'Add a jpegPhoto';
$lang['rename_entry'] = 'Döp om posten';//'Rename Entry';
$lang['rename'] = 'Döp om ';//'Rename';
$lang['add'] = 'Lägg till';//'Add';
$lang['view'] = 'Titta';//'View';
$lang['add_new_attribute'] = 'Lägg till ett nytt attribut';//'Add New Attribute';
$lang['add_new_attribute_tooltip'] = 'Lägg till ett nytt attribut/värde till denna post';//'Add a new attribute/value to this entry';
$lang['internal_attributes'] = 'Interna attribut';//'Internal Attributes';
$lang['hide_internal_attrs'] = 'Göm interna attribut';//'Hide internal attributes';
$lang['show_internal_attrs'] ='Visa interna attribut';// 'Show internal attributes';
$lang['internal_attrs_tooltip'] = 'Attribut som sätts automatiskt av systemet';//'Attributes set automatically by the system';
$lang['entry_attributes'] = 'Ingångsattribut';//'Entry Attributes';
$lang['attr_name_tooltip'] = 'Klicka för att titta på schemadefinitionen för attributtyp \'%s\'';//'Click to view the schema defintion for attribute type \'%s\'';
$lang['click_to_display'] = 'klicka\'+\' för att visa';// 'click \'+\' to display';
$lang['hidden'] = 'gömda';//'hidden';
$lang['none'] = 'inget';//'none';
$lang['save_changes'] = 'Spara ändringar';//'Save Changes';
$lang['add_value'] = 'lägg till värde';//'add value';
$lang['add_value_tooltip'] = 'Lägg till ett ytterligare värde till attribut \'%s\''; // 'Add an additional value to attribute \'%s\'';
$lang['refresh_entry'] = 'Uppdatera';//'Refresh';
$lang['refresh_this_entry'] = 'Uppdatera denna post';//'Refresh this entry';
$lang['delete_hint'] = 'Tips: <b>För att ta bort ett attribut</b>, ta bort all text i textfältet och klicka \'Spara ändringar\'.'; 'Hint: <b>To delete an attribute</b>, empty the text field and click save.';
$lang['attr_schema_hint'] = 'Tips: <b>För att titta på ett attributs schema</b>, klicka på attributnamnet';//'Hint: <b>To view the schema for an attribute</b>, click the attribute name.';
$lang['attrs_modified'] = 'Några attribut var ändrade och är markerade nedan.';//'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'Ett attribut var ändrat och är markerat nedan.';//An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'Titta på en post med enbart lästiilstånd';//'Viewing entry in read-only mode.';
$lang['change_entry_rdn'] = 'ändra denna posts RDN';//'Change this entry\'s RDN';
$lang['no_new_attrs_available'] = 'inga nya attribut tillgängliga för denna post';//'no new attributes available for this entry';
$lang['binary_value'] = 'Binärt värde';//'Binary value';
$lang['add_new_binary_attr'] = 'Lägg till nytt binärt attribut';//'Add New Binary Attribute';
$lang['add_new_binary_attr_tooltip'] = 'Lägg till nytt binärt attribut/värde från en fil';//'Add a new binary attribute/value from a file';
$lang['alias_for'] = 'Observera: \'%s\' är ett alias for \'%s\'';//'Note: \'%s\' is an alias for \'%s\'';
$lang['download_value'] = 'ladda ner värde';//'download value';
$lang['delete_attribute'] = 'ta bort attribut';//'delete attribute';
$lang['true'] = 'Sant';//'true';
$lang['false'] = 'Falskt';//'false';
$lang['none_remove_value'] = 'inget, ta bort värdet';//'none, remove value';
$lang['really_delete_attribute'] = 'Ta definitivt bort värdet';//'Really delete attribute';

// Schema browser
$lang['the_following_objectclasses'] = 'Följande <b>objektklasser</b> stöds av denna LDAP server.';//'The following <b>objectClasses</b> are supported by this LDAP server.';
$lang['the_following_attributes'] = 'Följande <b>attributtyper</b> stöds av denna LDAP server.';//'The following <b>attributeTypes</b> are supported by this LDAP server.';
$lang['the_following_matching'] = 'Följande <b>matchningsregler</b> stöds av denna LDAP server.';//'The following <b>matching rules</b> are supported by this LDAP server.';
$lang['the_following_syntaxes'] = 'Följande <b>syntax</b> stöds av denna LDAP server.';//'The following <b>syntaxes</b> are supported by this LDAP server.';
$lang['jump_to_objectclass'] = 'Välj en objectClass';//'Jump to an objectClass';
$lang['jump_to_attr'] = 'Välj en attributtyp';//'Jump to an attribute type';
$lang['schema_for_server'] = 'Schema för servern';//'Schema for server';
$lang['required_attrs'] = 'Nödvändiga attribut';//'Required Attributes';
$lang['optional_attrs'] = 'Valfria attribut';//'Optional Attributes';
$lang['OID'] = 'OID';//'OID';
$lang['desc'] = 'Beskrivning';//'Description';
$lang['name'] = 'Namn';//'Name';
$lang['is_obsolete'] = 'Denna objectClass är <b>föråldrad</b>';//'This objectClass is <b>obsolete</b>';
$lang['inherits'] = 'ärver';//'Inherits';
$lang['jump_to_this_oclass'] = 'Gå till definitionen av denna objectClass';//'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'Matchande regel-OID';//'Matching Rule OID';
$lang['syntax_oid'] = 'Syntax-OID';//'Syntax OID';
$lang['not_applicable'] = 'inte tillämplig';//'not applicable';
$lang['not_specified'] = 'inte specificerad';//'not specified';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Borttagning av posten  \'%s\' lyckades';//'Entry \'%s\' deleted successfully.';
$lang['you_must_specify_a_dn'] = 'Du måste specificera ett DN';//'You must specify a DN';
$lang['could_not_delete_entry'] = 'Det gick inte att ta bort posten';//'Could not delete the entry: %s';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nya nödvändiga attribut';//'New Required Attributes';
$lang['requires_to_add'] = 'Den här åtgärden kräver att du lägger till';//'This action requires you to add';
$lang['new_attributes'] = 'nya attribut';//'new attributes';
$lang['new_required_attrs_instructions'] = 'Instruktioner: För att kunna lägga till objektklassen till denna post, måste du specificera';//'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'att objektklassen kräver. Det kan göras i detta formulär.';//'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'Lägg till objektklass och attribut';//'Add ObjectClass and Attributes';

// General
$lang['chooser_link_tooltip'] = 'Klicka för att öppna ett fönster för att välja ett <DN> grafiskt.';//'Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'Du kan inte göra uppdateringar medan servern är i lästillstånd';//'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = 'Felaktigt server-id';//'Bad server id';
$lang['not_enough_login_info'] = 'Det saknas information för att logga in på servern. Var vänlig och kontrollera din konfiguration.';//'Not enough information to login to server. Please check your configuration.';
$lang['could_not_connect'] = 'Det gick inte att ansluta till LDAP-servern.';//'Could not connect to LDAP server.';
$lang['could_not_perform_ldap_mod_add'] = 'Det gick inte att utföra ldap_mod_add operationen.';//''Could not perform ldap_mod_add operation.';
$lang['bad_server_id_underline'] = 'Felaktigt server_id';//'Bad server_id: ';
$lang['success'] = 'Det lyckades';//'Success';
$lang['server_colon_pare'] = 'Server';//'Server: ';
$lang['look_in'] = 'Tittar in';//'Looking in: ';
$lang['missing_server_id_in_query_string'] = 'Inget server-ID specificerat i frågesträgen!';//'No server ID specified in query string!';
$lang['missing_dn_in_query_string'] = 'Inget DN specificerat i frågesträgen!';//'No DN specified in query string!';
$lang['back_up_p'] = 'Tillbaka';//'Back Up...';
$lang['no_entries'] = 'inga poster';//'no entries';
$lang['not_logged_in'] = 'Inte inloggad';//'Not logged in';
$lang['could_not_det_base_dn'] = 'Det gick inte att bestämma \'base DN\'';//'Could not determine base DN';

// Add value form
$lang['add_new'] = 'Lägg till nytt';//'Add new';
$lang['value_to'] = 'värde till';//'value to';
$lang['distinguished_name'] =  'Distinguished Name';//'Distinguished Name';
$lang['current_list_of'] = 'Aktuell lista av';//'Current list of';
$lang['values_for_attribute'] = 'attributvärden';//'values for attribute';
$lang['inappropriate_matching_note'] = 'Observera: Du kommer att få ett \'inappropriate matching\'-fel om du inte har<br />' .
                        'satt upp en <tt>EQUALITY</tt>-regel på din LDAP-server för detta attribut.';//  'Note: You will get an "inappropriate matching" error if you have not<br />' .
			'setup an <tt>EQUALITY</tt> rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Skriv in värdet du vill lägga till';//'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Observera: Du kan bli tvungen att skriva in de nya attribut som denna objectClass behöver';//'Note: you may be required to enter new attributes that this objectClass requires';
$lang['syntax'] = 'Syntax';//'Syntax';

//copy.php
$lang['copy_server_read_only'] = 'Du kan inte göra uppdateringar medan servern är i lästillstånd';//'You cannot perform updates while server is in read-only mode';
$lang['copy_dest_dn_blank'] = 'Du lämnade destinations-DN tomt';//'You left the destination DN blank.';
$lang['copy_dest_already_exists'] = 'Destinationen finns redan';//'The destination entry (%s) already exists.';
$lang['copy_dest_container_does_not_exist'] = 'Destinations-behållaren (%s) finns inte';// 'The destination container (%s) does not exist.';
$lang['copy_source_dest_dn_same'] = 'Käll- och destinations-DN är samma.';//'The source and destination DN are the same.';
$lang['copy_copying'] = 'Kopierar';//'Copying ';
$lang['copy_recursive_copy_progress'] = 'Rekursiv kopiering pågår';//'Recursive copy progress';
$lang['copy_building_snapshot'] = 'Bygger en ögonblicksbild av det träd som ska kopieras';//'Building snapshot of tree to copy... ';
$lang['copy_successful_like_to'] = 'Kopieringen lyckades! Vill du';//'Copy successful! Would you like to ';
$lang['copy_view_new_entry'] = 'titta på den nya posten';//'view the new entry';
$lang['copy_failed'] = 'Kopiering av DN misslyckades';//'Failed to copy DN: ';

//edit.php
$lang['missing_template_file'] = 'Varning! mall-filen saknas,';//'Warning: missing template file, ';
$lang['using_default'] = 'använder default.'; //'Using default.';

//copy_form.php
$lang['copyf_title_copy'] = 'Kopiera';//'Copy ';
$lang['copyf_to_new_object'] = 'till ett nytt objekt';//'to a new object';
$lang['copyf_dest_dn'] =  'Destinations-DN';//'Destination DN';
$lang['copyf_dest_dn_tooltip'] = 'Den nya postens fullständiga DN skapas när källposten kopieras';//'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'Destinations-server';//'Destination Server';
$lang['copyf_note'] = 'Tips: Kopiering mellan olika servrar fungerar bara om det inte finns några brott mot schemorna.';// 'Hint: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = 'Kopiera även rekursivt alla underobjekt till detta objekt.';//'Recursively copy all children of this object as well.';

//create.php
$lang['create_required_attribute'] = 'Du lämnade ett värde tomt för ett nödvändigt attribut <b>%s</b>.';//'You left the value blank for required attribute <b>%s</b>.';
$lang['create_redirecting'] = 'Omstyrning';//'Redirecting';
$lang['create_here'] = 'här';//'here';
$lang['create_could_not_add'] = 'Det gick inte att lägga till objektet till LDAP-servern.';//'Could not add the object to the LDAP server.';

//create_form.php
$lang['createf_create_object'] = 'Skapa objekt';//'Create Object';
$lang['createf_choose_temp'] = 'Välj en mall';//'Choose a template';
$lang['createf_select_temp'] = 'Välj en mall för att skapa objekt';//'Select a template for the creation process';
$lang['createf_proceed'] = 'Fortsätt';//'Proceed';

//creation_template.php
$lang['ctemplate_on_server'] = 'På servern';//'On server';
$lang['ctemplate_no_template'] = 'Ingen mall specificerad i POST variablerna.';//'No template specified in POST variables.';
$lang['ctemplate_config_handler'] = 'Din konfiguration specificerar en hanterare';//'Your config specifies a handler of';
$lang['ctemplate_handler_does_not_exist'] = 'för denna mall, men hanteraren finns inte i templates/creation-katalogen';//'for this template. But, this handler does not exist in the templates/creation directory.';

// search.php
$lang['you_have_not_logged_into_server'] = 'Du har inte loggat in till den valda servern ännu, så du kan inte göra sökningar på den.';//'You have not logged into the selected server yet, so you cannot perform searches on it.';
$lang['click_to_go_to_login_form'] = 'Klicka här för att komma till login-formuläret';//'Click here to go to the login form';
$lang['unrecognized_criteria_option'] = 'Känner inte till detta urvals-kriterium';//'Unrecognized criteria option: ';
$lang['if_you_want_to_add_criteria'] = 'Om du vill lägga till ditt eget kriterium till listan, kom ihåg att ändra search.php för att hantera det. Avslutar.';//'If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.';
$lang['entries_found'] = 'Poster funna:';//'Entries found: ';
$lang['filter_performed'] = 'Filtrering utförd: ';//'Filter performed: ';
$lang['search_duration'] = 'Sökning utförd av phpLDAPadmin på';//'Search performed by phpLDAPadmin in';
$lang['seconds'] = 'sekunder';//'seconds';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Sökomfattning';//'The scope in which to search';
$lang['scope_sub'] = 'Sub (Base DN och hela trädet under)';//'Sub (entire subtree)';
$lang['scope_one'] = 'One (en nivå under Base DN)';//One (one level beneath base)';
$lang['scope_base'] = 'Base (endast Base DN)';//'Base (base dn only)';
$lang['standard_ldap_search_filter'] = 'Standard LDAP sökfilter. Exempel: (&(sn=Smith)(givenname=David))';//'Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Sökfilter';//'Search Filter';
$lang['list_of_attrs_to_display_in_results'] = 'En lista med attribut att visa i resultatet (komma-separerad)';// 'A list of attributes to display in the results (comma-separated)';
$lang['show_attributes'] = 'Visa attribut';//'Show Attributes';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Sök efter poster som:';//'Search for entries whose:';
$lang['equals'] = 'är lika med';//'equals';
$lang['starts with'] = 'börjar med';//'starts with';
$lang['contains'] = 'innehåller';//'contains';
$lang['ends with'] = 'slutar med';//'ends with';
$lang['sounds like'] = 'låter som';//'sounds like';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Det gick inte att hämta LDAP information från servern.';//'Could not retrieve LDAP information from the server';
$lang['server_info_for'] = 'Serverinformation för';//'Server info for: ';
$lang['server_reports_following'] = 'Servern rapporterar följande information om sig själv';//'Server reports the following information about itself';
$lang['nothing_to_report'] = 'Servern har inget att rapportera';//'This server has nothing to report.';

//update.php
$lang['update_array_malformed'] = 'update_array är felaktig. Detta kan vara ett phpLDAPadmin-fel. Var vänlig och rapportera det.';// 'update_array is malformed. This might be a phpLDAPadmin bug. Please report it.';
$lang['could_not_perform_ldap_modify'] = 'Det gick inte att utföra operationen ldap_modify.';//'Could not perform ldap_modify operation.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Vill du göra dessa ändringar?';//'Do you want to make these changes?';
$lang['attribute'] = 'Attribut';//'Attribute';
$lang['old_value'] = 'Föregående värde';//'Old Value';
$lang['new_value'] = 'Nytt värde';//'New Value';
$lang['attr_deleted'] = '[attributet borttaget]';//'[attribute deleted]';
$lang['commit'] = 'Bekräfta';//'Commit';
$lang['cancel'] = 'ångra';//'Cancel';
$lang['you_made_no_changes'] = 'Du gjorde inga ändringar';//'You made no changes';
$lang['go_back'] = 'Gå tillbaka';//'Go back';

// welcome.php
$lang['welcome_note'] = 'Navigera med hjälp av menyn till vänster';//'Use the menu to the left to navigate';
$lang['credits'] = 'Tack till';//'Credits';
$lang['changelog'] = 'ändringslogg';//'ChangeLog';
$lang['documentation'] = 'Dokumentation';//'Documentation';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Osäkert filnamn';//'Unsafe file name: ';
$lang['no_such_file'] = 'Filen finns inte';//'No such file: ';

//function.php
$lang['auto_update_not_setup'] = 'Du har slagit på auto_uid_numbers för <b>%s</b> i din konfiguration, 
                                  men du har inte specificerat auto_uid_number_mechanism. Var vänlig och rätta till 
                                  detta problem.'; 
                                  //'You have enabled auto_uid_numbers for <b>%s</b> in your configuration,
                                  //but you have not specified the auto_uid_number_mechanism. Please correct
                                  //this problem.';
$lang['uidpool_not_set'] = 'Du har specificerat <tt>auto_uid_number_mechanism</tt> som <tt>uidpool</tt> 
                            i din konfiguration för server<b>%s</b>, men du specificerade inte 
                            audo_uid_number_uid_pool_dn. Var vänlig och specificera den innan du fortsätter.';
                            //'You specified the <tt>auto_uid_number_mechanism</tt> as <tt>uidpool</tt>
                            //in your configuration for server <b>%s</b>, but you did not specify the
                            //audo_uid_number_uid_pool_dn. Please specify it before proceeding.';
$lang['uidpool_not_exist'] = 'Det ser ut som om den uidPool du specificerade i din konfiguration (<tt>%s</tt>) 
                             inte existerar.';
                             // 'It appears that the uidPool you specified in your configuration (<tt>%s</tt>)
                             // does not exist.';
$lang['specified_uidpool'] = 'Du specificerade <tt>auto_uid_number_mechanism</tt> som <tt>search</tt> i din 
                             konfiguration för server<b>%s</b>, men du specificerade inte 
                             <tt>auto_uid_number_search_base</tt>. Var vänlig och specificera den innan du fortsätter.';
                             // 'You specified the <tt>auto_uid_number_mechanism</tt> as <tt>search</tt> in your
                             //configuration for server <b>%s</b>, but you did not specify the
                             //<tt>auto_uid_number_search_base</tt>. Please specify it before proceeding.';
$lang['auto_uid_invalid_value'] = 'Du specificerade ett ogiltigt värde för auto_uid_number_mechanism (<tt>%s</tt>) 
                                   i din konfiguration. Endast <tt>uidpool</tt> och <tt>search</tt> are giltiga. 
                                   Var vänlig och rätta till detta problem.';
                                   //'You specified an invalid value for auto_uid_number_mechanism (<tt>%s</tt>)
                                   //in your configration. Only <tt>uidpool</tt> and <tt>search</tt> are valid.
                                   //Please correct this problem.';
$lang['error_auth_type_config'] = 'Fel: Du har ett fel i din konfigurationsfil. De enda tillåtna värdena 
                                   för auth_type i $servers-sektionen är \'config\' and \'form\'. Du skrev in \'%s\', 
                                   vilket inte är tillåtet. ';
                                   //'Error: You have an error in your config file. The only two allowed values
                                   //for auth_type in the $servers section are \'config\' and \'form\'. You entered \'%s\',
                                   //which is not allowed. ';
$lang['php_install_not_supports_tls'] = 'Din PHP-installation stödjer inte TLS';//'Your PHP install does not support TLS';
$lang['could_not_start_tls'] = 'Det gick inte att starta TLS.<br />Var vänlig och kontrollera din LDAP-serverkonfiguration.';//'Could not start TLS.<br />Please check your LDAP server configuration.';
$lang['auth_type_not_valid'] = 'Du har ett fel i din konfigurationsfil. auth_type %s är inte tillåten.';//'You have an error in your config file. auth_type of %s is not valid.';
$lang['ldap_said'] = '<b>LDAP sa</b>: %s<br /><br />';//'<b>LDAP said</b>: %s<br /><br />';
$lang['ferror_error'] = 'Fel';'Error';
$lang['fbrowse'] = 'titta';//'browse';
$lang['delete_photo'] = 'Ta bort foto';//'Delete Photo';
$lang['install_not_support_blowfish'] = 'Din PHP-installation stödjer inte blowfish-kryptering.';// 'Your PHP install does not support blowfish encryption.';
$lang['install_no_mash'] = 'Din PHP-installation har inte funktionen mash(). Det går inte att göra SHA hashes.';//'Your PHP install does not have the mhash() function. Cannot do SHA hashes.';
$lang['jpeg_contains_errors'] = 'JPEG-fotot innehåller fel<br />';//'jpegPhoto contains errors<br />';
$lang['ferror_number'] = '<b>Felnummer</b>: %s <small>(%s)</small><br /><br />';//'<b>Error number</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] ='<b>Beskrivning</b>: %s <br /><br />';//'<b>Description</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Felnummer</b>: %s<br /><br />';//'<b>Error number</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Beskrivning</b>: (ingen beskrivning tillgänglig)<br />';//'<b>Description</b>: (no description available)<br />';
$lang['ferror_submit_bug'] = 'är det här ett phpLDAPadmin-fel? Om så är fallet, var vänlig och <a href=\'%s\'>rapportera det</a>.';
//'Is this a phpLDAPadmin bug? If so, please <a href=\'%s\'>report it</a>.';
$lang['ferror_unrecognized_num'] = 'Okänt felnummer';//'Unrecognized error number: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Du har hittat en icke-kritisk phpLDAPadmin bug!</b></td></tr><tr><td>Fel:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Fil:</td>
             <td><b>%s</b> rad <b>%s</b>, anropande <b>%s</b></td></tr><tr><td>Versioner:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Var vänlig och rapportera felet genom att klicka här</a>.</center></td></tr></table></center><br />';

             //'<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             //<b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td>
             //<td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             //</td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             //Please report this bug by clicking here</a>.</center></td></tr></table></center><br />';

$lang['ferror_congrats_found_bug'] = 'Gratulerar! Du har hittat en bug i phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Fel:</td><td><b>%s</b></td></tr>
	     <tr><td>Nivå:</td><td><b>%s</b></td></tr>
	     <tr><td>Fil:</td><td><b>%s</b></td></tr>
	     <tr><td>Rad:</td><td><b>%s</b></td></tr>
		 <tr><td>Anropare:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web server:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Var vänlig och rapportera den här buggen genom att klicak här nedan!';

//'Congratulations! You found a bug in phpLDAPadmin.<br /><br />
//<table class=\'bug\'>
//<tr><td>Error:</td><td><b>%s</b></td></tr>
//<tr><td>Level:</td><td><b>%s</b></td></tr>
//<tr><td>File:</td><td><b>%s</b></td></tr>
//<tr><td>Line:</td><td><b>%s</b></td></tr>
//<tr><td>Caller:</td><td><b>%s</b></td></tr>
//<tr><td>PLA Version:</td><td><b>%s</b></td></tr>
//<tr><td>PHP Version:</td><td><b>%s</b></td></tr>
//<tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
//<tr><td>Web server:</td><td><b>%s</b></td></tr>
//</table>
//<br />
//Please report this bug by clicking below!';


//ldif_import_form
$lang['import_ldif_file_title'] = 'Importera LDIF-fil';//'Import LDIF File';
$lang['select_ldif_file'] = 'Välj en LDIF-fil:';//'Select an LDIF file:';
$lang['select_ldif_file_proceed'] = 'Fortsätt &gt;&gt;';//'Proceed &gt;&gt;';

//ldif_import
$lang['add_action'] = 'Lägger till...';//'Adding...';
$lang['delete_action'] = 'Tar bort...';//'Deleting...';
$lang['rename_action'] = 'Döper om...';//''Renaming...';
$lang['modify_action'] = 'ändrar...';//'Modifying...';

$lang['failed'] = 'misslyckades';//'failed';
$lang['ldif_parse_error'] = 'LDIF parsningsfel';//'LDIF Parse Error';
$lang['ldif_could_not_add_object'] = 'Det gick inte att lägga till objekt';//'Could not add object:';
$lang['ldif_could_not_rename_object'] = 'Det gick inte att lägga döpa om objekt';//'Could not rename object:';
$lang['ldif_could_not_delete_object'] = 'Det gick inte att ta bort objekt';//'Could not delete object:';
$lang['ldif_could_not_modify_object'] = 'Det gick inte att ändra objekt';//'Could not modify object:';
$lang['ldif_line_number'] = 'Radnummer';//'Line Number:';
$lang['ldif_line'] = 'Rad:';//'Line:';
?>
