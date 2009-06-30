<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/ca.php,v 1.4 2004/03/19 20:13:09 i18phpldapadmin Exp $


// Search form
// phpldapadmin/lang/ca.php $Revision: 1.4 $
//encoding: ISO-8859-1,ca.php instalació de PHP no té
$lang['simple_search_form_str'] = 'Formulari de recerca sencilla';
$lang['advanced_search_form_str'] = 'Formulari de recerca avançada';
$lang['server'] = 'Servidor';
$lang['search_for_entries_whose'] = 'Buscar objectes els quals';
$lang['base_dn'] = 'Base DN';
$lang['search_scope'] = 'Abast de la recerca';
$lang['search_ filter'] = 'Filtre de Recerca';
$lang['show_attributes'] = 'Mostrar atributs';
$lang['Search'] = 'Buscar';
$lang['equals'] = 'equival';
$lang['starts_with'] = 'comença amb';
$lang['contains'] = 'conté';
$lang['ends_with'] = 'acaba amb';
$lang['sounds_like'] = 'sona com';

// Tree browser
$lang['request_new_feature'] = 'Demanar funcionalitat';
$lang['see_open_requests'] = 'Veure les peticions';
$lang['report_bug'] = 'Reportar una errada';
$lang['see_open_bugs'] = 'Veure les errades';
$lang['schema'] = 'esquema';
$lang['search'] = 'buscar';
$lang['create'] = 'crear';
$lang['info'] = 'info';
$lang['import'] = 'importar';
$lang['refresh'] = 'refrescar';
$lang['logout'] = 'sortir';
$lang['create_new'] = 'Crear Nou Objecte';
$lang['view_schema_for'] = 'Veure esquema per a';
$lang['refresh_expanded_containers'] = 'Refrescar tots els contenidors extés per a';
$lang['create_new_entry_on'] = 'Crear nou objecte a';
$lang['view_server_info'] = 'Veure informació del servidor';
$lang['import_from_ldif'] = 'Importar objectes d\'arxiu LDIF';
$lang['logout_of_this_server'] = 'Sortiu d\'aquest servidor';
$lang['logged_in_as'] = 'Conectat com: ';
$lang['read_only'] = 'inalterable';
$lang['could_not_determine_root'] = 'No s\'ha pogut determinar l\'arrel del servidor LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Sembla que el servidor LDAP s\'ha configurat per no revelar la seva arrel.';
$lang['please_specify_in_config'] = 'Si us plau especifícala a l\'arxiu config.php';
$lang['create_new_entry_in'] = 'Crear un nou objecte a';
$lang['login_link'] = 'Autenticació...';

// Entry display
$lang['delete_this_entry'] = 'Esborrar aquest objecte';
$lang['delete_this_entry_tooltip'] = 'Es tindrá que confirmar aquesta decisió';
$lang['copy_this_entry'] = 'Copiar aquest objecte';
$lang['copy_this_entry_tooltip'] = 'Copiar aquest objecte per una altra localització, DN nou, o per un altre servidor.';
$lang['export_to_ldif'] = 'Exportar arxiu LDIF';
$lang['export_to_ldif_tooltip'] = 'Desar arxiu LDIF d\'aquest objecte';
$lang['export_subtree_to_ldif_tooltip'] = 'Desar arxiu LDIF d\'aquest objecte i tots els seus objectes fills';
$lang['export_subtree_to_ldif'] = 'Exportar arxiu LDIF de sub-estructura';
$lang['export_mac'] = 'Avanç de línia de Macintosh';
$lang['export_win'] = 'Avanç de línia de Windows';
$lang['export_unix'] = 'Avanç de línia de Unix';
$lang['create_a_child_entry'] = 'Crear objecte com a fill';
$lang['add_a_jpeg_photo'] = 'Afegir jpegPhoto';
$lang['rename_entry'] = 'Renombrar objecte';
$lang['rename'] = 'Renombrar';
$lang['add'] = 'Afegir';
$lang['view'] = 'Veure';
$lang['add_new_attribute'] = 'Afegir nou atribut';
$lang['add_new_attribute_tooltip'] = 'Afegir nous atribut/valor a aquest objecte';
$lang['internal_attributes'] = 'Atributs Interns';
$lang['hide_internal_attrs'] = 'ocultar els atributs interns';
$lang['show_internal_attrs'] = 'mostrar els atributs interns';
$lang['internal_attrs_tooltip'] = 'Els atributs fixes automaticament pel servidor';
$lang['entry_attributes'] = 'Atributs de l\'objecte';
$lang['attr_name_tooltip'] = 'Fes click per veure la definició de l\'esquema per tipus d\'atribut \'%s\'';
$lang['click_to_display'] = 'Fer click per a mostrar';
$lang['hidden'] = 'ocultat';
$lang['none'] = 'cap';
$lang['save_changes'] = 'Desar els canvis';
$lang['add_value'] = 'afegir valor';
$lang['add_value_tooltip'] = 'Afegir valor adicional a aquest atribut';
$lang['refresh_entry'] = 'Refrescar';
$lang['refresh_this_entry'] = 'Refrescar aquest objecte';
$lang['delete_hint'] = 'Pista: <b>Per a borrar un atribut</b>, buida el formulari de texte i fes click a Desar.';
$lang['attr_schema_hint'] = 'Pista: <b>Per veure l\'esquema d\'un atribut</b>, fes click al nom de l\'atribut.';
$lang['attrs_modified'] = 'Alguns atributs (%s) foren modificats i estan remarcats més abaix.';
$lang['attr_modified'] = 'Un atribut (%s) fore modificat i està remarcat més abaix.';
$lang['viewing_read_only'] = 'Mostrant l\'objecte en el mode de no alterar.';
$lang['change_entry_rdn'] = 'Modificar el RDN d\'aquest objecte';
$lang['no_new_attrs_available'] = 'No hi han atributs nous disponibles per aquest objecte';
$lang['binary_value'] = 'Valor binari';
$lang['add_new_binary_attr'] = 'Afegir valor binari';
$lang['add_new_binary_attr_tooltip'] = 'Afegir atribut/valor binari d\'un arxiu';
$lang['alias_for'] = 'Sinònim per a';
$lang['download_value'] = 'Descarregar valor';
$lang['delete_attribute'] = 'Esborrar atribut';
$lang['true'] = 'veritat';
$lang['false'] = 'fals';
$lang['none_remove_value'] = 'cap, esborrar valor';
$lang['really_delete_attribute'] = 'Esborrar realment l\'atribut?';

// Schema browser
$lang['the_following_objectclasses'] = 'Les següents <b>ObjectClass</b> són presents en aquest servidor LDAP.';
$lang['the_following_attributes'] = 'Les següents <b>attributeTypes</b> són presents en aquest servidor LDAP.';
$lang['the_following_matching'] = 'Les següents <b>matching rules</b> són presents en aquest servidor LDAP.';
$lang['the_following_syntaxes'] = 'Les següents <b>sintaxis</b> són presents en aquest servidor LDAP.'; 
$lang['jump_to_objectclass'] = 'Saltar a una ObjectClass';
$lang['jump_to_attr'] = 'Saltar a un atribut';
$lang['schema_for_server'] = 'Esquema del servidor ';
$lang['required_attrs'] = 'Atributs Requerits (MUST)';
$lang['optional_attrs'] = 'Atributs Opcionals (MAY)';
$lang['OID'] = 'OID';
$lang['desc'] = 'Descripció';
$lang['name'] = 'Nom';
$lang['is_obsolete'] = 'Aquesta ObjectClass és <b>obsoleta</b>';
$lang['inherits'] = 'Hereda';
$lang['jump_to_this_oclass'] = 'Saltar a aquesta ObjectClass';
$lang['matching_rule_oid'] = 'OID de Matching Rule';
$lang['syntax_oid'] = 'OID de Sintaxi';
$lang['not_applicable'] = 'no es aplicable';
$lang['not_specified'] = 'no especificada';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Entrada \'%s\' esborrada correctament.';
$lang['you_must_specify_a_dn'] = 'Has d\'especificar un DN';
$lang['could_not_delete_entry'] = 'No he pogut esborrar l\'entrada: %s';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nous atributs requerits';
$lang['requires_to_add'] = 'Aquesta acció exigeix que s\'afegeixin';
$lang['new_attributes'] = 'nous atributs';
$lang['new_required_attrs_instructions'] = 'Instruccions: Per afegir aquesta ObjectClass a aquest objecte, s\'ha d\'especificar';
$lang['that_this_oclass_requires'] = 'que aquest ObjectClass requereix. Es pot fer amb aquest formulari.';
$lang['add_oclass_and_attrs'] = 'Afegir ObjectClass i Atributs';

// General
$lang['chooser_link_tooltip'] = 'Fer click per seleccionar un objecte gràficament';
$lang['no_updates_in_read_only_mode'] = 'No es pot modificar l\'objecte si el servidor està operant en mode inalterable.';
$lang['bad_server_id'] = 'L\'identificador de servidor està malament';
$lang['not_enough_login_info'] = 'No tinc suficient informació per conectar al servidor. Si us plau configurar correctament l\'arxiu config.php'; 
$lang['could_not_connect'] = 'No s\'ha pogut conectar al servidor LDAP';
$lang['could_not_perform_ldap_mod_add'] = 'No s\'ha pogut fer l\'operació del ldap_mod_add.';
$lang['bad_server_id_underline'] = 'L\'identificador del servidor està malament: ';
$lang['success'] = 'Exit';
$lang['server_colon_pare'] = 'Servidor: ';
$lang['look_in'] = 'Buscant a: ';
$lang['missing_server_id_in_query_string'] = 'No està present l\'identificador del servidor a la URL';
$lang['missing_dn_in_query_string'] = 'No està present el DN a la URL';
$lang['back_up_p'] = 'Tornar a...';
$lang['no_entries'] = 'no hi han entrades';
$lang['not_logged_in'] = 'No estàs autenticat';
$lang['could_not_det_base_dn'] = 'No he pogut determinar la base DN';

// Add value form
$lang['add_new'] = 'Afegir';
$lang['value_to'] = 'valor a';
$lang['server'] = 'Servidor';
$lang['distinguished_name'] = 'Nom distinguit';
$lang['current_list_of'] = 'La llista actual de';
$lang['values_for_attribute'] = 'valors per a l\'atribut';
$lang['inappropriate_matching_note'] = 'Nota: Sino has configurat una regla \'EQUALITY\' al servidor LDAP, rebràs un error \'inappropriate matching\'';
$lang['enter_value_to_add'] = 'Proveïr el valor per afegir: ';
$lang['new_required_attrs_note'] = 'Nota: Es posible que es requereixi afegir nous atributs per satisfer els requisits d\'aquesta ObjectClass';
$lang['syntax'] = 'Sintaxi';

//copy.php
$lang['copy_server_read_only'] = 'No es poden realitzar les modificacions si el servidor està operant en mode inalterable';
$lang['copy_dest_dn_blank'] = 'No se emplenat el formulari de DN.';
$lang['copy_dest_already_exists'] = 'L\'entrada de destí (%s) encara existeix.';
$lang['copy_dest_container_does_not_exist'] = 'El contenidor de destí (%s) no existeix.';
$lang['copy_source_dest_dn_same'] = 'El DN de la font i el DN de destí son els mateixos.';
$lang['copy_copying'] = 'Copiant ';
$lang['copy_recursive_copy_progress'] = 'El progrés de la còpia recurrent';
$lang['copy_building_snapshot'] = 'Construïnt la \'foto\' de l\'arbre per a copiar... ';
$lang['copy_successful_like_to'] = 'Exit! Desitges ';
$lang['copy_view_new_entry'] = 'Veure el nou objecte?';
$lang['copy_failed'] = 'Fallida al copiar DN: '; 

//edit.php
$lang['missing_template_file'] = 'Error: falta la plantilla, ';
$lang['using_default'] = 'Fent anar l\'arxiu per defecte.';

//copy_form.php
$lang['copyf_title_copy'] = 'Copiar ';
$lang['copyf_to_new_object'] = 'a un objecte nou';
$lang['copyf_dest_dn'] = 'DN de destí';
$lang['copyf_dest_dn_tooltip'] = 'El DN sencer de la nova entrada a ser creada quan es copii l\'entrada font';
$lang['copyf_dest_server'] = 'Servidor de destí';
$lang['copyf_note'] = 'Nota: Copiar entre dos servidor funciona solsament si no hi han violacions de l\'esquema.';
$lang['copyf_recursive_copy'] = 'Esborrar tots els fills recurrentment també?';


//create.php
$lang['create_required_attribute'] = 'T\'has deixar el valor en blanc de l\'atribut requerit <b>%s</b>.';
$lang['create_redirecting'] = 'Redirigint';
$lang['create_here'] = 'aquí';
$lang['create_could_not_add'] = 'No he pogut afegir l\'objecte al servidor LDAP.';

//create_form.php
$lang['createf_create_object'] = 'Crear Objecte';
$lang['createf_choose_temp'] = 'Escull una plantilla';
$lang['createf_select_temp'] = 'Selecciona una plantilla per al procès de creació';
$lang['createf_proceed'] = 'Procedir';

//creation_template.php
$lang['ctemplate_on_server'] = 'Al servidor';
$lang['ctemplate_no_template'] = 'No s\'ha especificat cap plantilla a les variables del POST.';
$lang['ctemplate_config_handler'] = 'La teva configuració especifica un manejador de';
$lang['ctemplate_handler_does_not_exist'] = 'per aquesta plantilla. Però, aquest manejador no existeix al directori \'plantilla/creació\'.';

// search.php
$lang['you_have_not_logged_into_server'] = 'Encara no t\'has autenticat al servidor selectionat, no pots fer cap recerca.';
$lang['click_to_go_to_login_form'] = 'Clica aquí per anar al formulari d\'autenticació';
$lang['unrecognized_criteria_option'] = 'Opció de criteri desconeguda: ';
$lang['if_you_want_to_add_criteria'] = 'Si vols afegir el teu propi criteri a la llista. Estigues segur d\'editar search.php per manejar-lo. Sortint.';
$lang['entries_found'] = 'Entrades trobades: ';
$lang['filter_performed'] = 'Filtre realitzat: ';
$lang['search_duration'] = 'Recerca realitzada per phpLDAPadmin a';
$lang['seconds'] = 'segons';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'L\'abast en el que buscar';
$lang['scope_sub'] = 'Sub (tot el sub-arbre)';
$lang['scope_one'] = 'Un (un nivell per d\'avall de la base)';
$lang['scope_base'] = 'Base (sols base dn)';
$lang['standard_ldap_search_filter'] = 'Filtre de recerca estàndar de LDAP. Exemple: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Filtre de recerca';
$lang['list_of_attrs_to_display_in_results'] = 'Una llista d\'atributs per mostrar als resultats (separats per comes)';
$lang['show_attributes'] = 'Mostrar atributs';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Buscar entrades les quals:';
$lang['equals'] = 'sigui igual';
$lang['starts with'] = 'comenci amb';
$lang['contains'] = 'contingui';
$lang['ends with'] = 'acabi amb';
$lang['sounds like'] = 'soni com';

// server_info.php
$lang['could_not_fetch_server_info'] = 'No s\'ha pogut treure informació LDAP del servidor';
$lang['server_info_for'] = 'Informació del servidor per a: ';
$lang['server_reports_following'] = 'El servidor mostra la següent informació sobre ell mateix';
$lang['nothing_to_report'] = 'Aquest servidor no té res a mostrar.';

//update.php
$lang['update_array_malformed'] = 'l\'update_array està malformat. Aixó podria ser una errada del phpLDAPadmin. Si us plau reportala.';
$lang['could_not_perform_ldap_modify'] = 'No he pogut executar l\'operació ldap_modify.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Vols fer aquests canvis?';
$lang['attribute'] = 'Atribut';
$lang['old_value'] = 'Valor vell';
$lang['new_value'] = 'Valor nou';
$lang['attr_deleted'] = '[atribut esborrat]';
$lang['commit'] = 'Cometre';
$lang['cancel'] = 'Cancel.lar';
$lang['you_made_no_changes'] = 'No has fet cap canvi';
$lang['go_back'] = 'Tornar enrera';

// welcome.php
$lang['welcome_note'] = 'Fes anar el menú de l\'esquerra per a navegar';
$lang['credits'] = "Crèdits";
$lang['changelog'] = "Històric de canvis";
$lang['documentation'] = "Documentació";


// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nom d\'arxiu insegur: ';
$lang['no_such_file'] = 'Arxiu no existent: ';

//function.php
$lang['auto_update_not_setup'] = 'Has activat els auto_uid_numbers per <b>%s</b> a la teva configuració,
                                  pero no has especificat l\'auto_uid_number_mechanism. Si us plau soluciona
                                  aquest problema.';
$lang['uidpool_not_set'] = 'Has especificat l\'<tt>auto_uid_number_mechanism</tt> com <tt>uidpool</tt>
                            a la teva configuració per al servidor <b>%s</b>, pero no has especificat el
                            audo_uid_number_uid_pool_dn. Si us plau especifica\'l avans de procedir.';
$lang['uidpool_not_exist'] = 'Sembla ser que el uidPool que has especificat a la teva configuració (<tt>%s</tt>)
                              no existeix.';
$lang['specified_uidpool'] = 'Has especificat l\'<tt>auto_uid_number_mechanism</tt> com <tt>search</tt> a la teva
                              configuració per al servidor <b>%s</b>, pero no has especificat el
                              <tt>auto_uid_number_search_base</tt>. Si us plau especifica\'l avans de procedir.';
$lang['auto_uid_invalid_value'] = 'Has especificat un valor no vàlid per a l\'auto_uid_number_mechanism (<tt>%s</tt>)
                                   a la teva configuració. Sols <tt>uidpool</tt> i <tt>search</tt> son vàlids.
                                   Si us plau soluciona aquest problema.';
$lang['error_auth_type_config'] = 'Error: Tens un error al teu arxiu de configuració. Els dos únics valors acceptats per
                                    \'auth_type\' a la secció $servers son \'config\' i \'form\'. Tu has ficat \'%s\',
                                    el qual no està acceptat. ';
$lang['php_install_not_supports_tls'] = 'La teva instalació de PHP no soporta TLS';
$lang['could_not_start_tls'] = 'No he pogut iniciar el TLS.<br />Revisa la teva configuració del servidor LDAP.';
$lang['auth_type_not_valid'] = 'Tens un error a l\'arxiu de configuració. auth_type de %s no es vàlid.';
$lang['ldap_said'] = '<b>LDAP diguè</b>: %s<br /><br />';
$lang['ferror_error'] = 'Error';
$lang['fbrowse'] = 'mostrar';
$lang['delete_photo'] = 'Esborrar foto';
$lang['install_not_support_blowfish'] = 'La teva instalació de PHP no soporta el tipus d\'encriptació blowfish.';
$lang['install_no_mash'] = 'La teva instalació de PHP no té la funció mhash(). No puc fer hash SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto conté errors<br />';
$lang['ferror_number'] = '<b>Error número</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] = '<b>Descripció</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Error número</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Descripció</b>: (no hi ha descripció)<br />';
$lang['ferror_submit_bug'] = 'Es una errada del phpLDAPadmin? Si ho és, si us plau <a href=\'%s\'>diguen\'s-ho</a>.';
$lang['ferror_unrecognized_num'] = 'Número d\'error desconegut: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Has trobat un error fatal del phpLDAPadmin!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Arxiu:</td>
             <td><b>%s</b> línia <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Servidor Web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Envía aquesta errada fent click aquí</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Felicitats! Has trobat una errada al phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Error:</td><td><b>%s</b></td></tr>
	     <tr><td>Nivell:</td><td><b>%s</b></td></tr>
	     <tr><td>Arxiu:</td><td><b>%s</b></td></tr>
	     <tr><td>Línia:</td><td><b>%s</b></td></tr>
	     <tr><td>Caller:</td><td><b>%s</b></td></tr>
	     <tr><td>Versió PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Versió PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Servidor Web:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Si us plau envía aquesta errada fent click abaix!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importar arxiu LDIF';
$lang['select_ldif_file'] = 'Selecciona un arxiu LDIF:';
$lang['select_ldif_file_proceed'] = 'Procedir &gt;&gt;';

//ldif_import
$lang['add_action'] = 'Afegint...';
$lang['delete_action'] = 'Esborrant...';
$lang['rename_action'] = 'Renombrant...';
$lang['modify_action'] = 'Modificant...';

$lang['failed'] = 'fallat';
$lang['ldif_parse_error'] = 'Error de parsejat LDIF';
$lang['ldif_could_not_add_object'] = 'No he pogut afegir l\'objecte:';
$lang['ldif_could_not_rename_object'] = 'No he pogut renombrar l\'objecte:';
$lang['ldif_could_not_delete_object'] = 'No he pogut esborrar l\'objecte:';
$lang['ldif_could_not_modify_object'] = 'No he pogut modificar l\'objecte:';
$lang['ldif_line_number'] = 'Línia Número:';
$lang['ldif_line'] = 'Línia:';

?>
