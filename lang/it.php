<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/it.php,v 1.5 2004/03/19 20:13:09 i18phpldapadmin Exp $


// Search form
$lang['simple_search_form_str'] = 'Modulo di Ricerca Semplice';
$lang['advanced_search_form_str'] = 'Modulo di Ricerca Avanzato';
$lang['server'] = 'Server';
$lang['search_for_entries_whose'] = 'Cerca per voci che';
$lang['base_dn'] = 'DN Base';
$lang['search_scope'] = 'Campo di Ricerca';
$lang['search_ filter'] = 'Filtro di Ricerca';
$lang['show_attributes'] = 'Mostra gli Attributi';
$lang['Search'] = 'Cerca';
$lang['equals'] = 'equivale';
$lang['starts_with'] = 'inizia con';
$lang['contains'] = 'contiene';
$lang['ends_with'] = 'finisce con';
$lang['sounds_like'] = 'suona come';

// Tree browser
$lang['request_new_feature'] = 'Richiedi una nuova funzionalità';
$lang['see_open_requests'] = 'guarda le richieste pendenti';
$lang['report_bug'] = 'Riporta un baco';
$lang['see_open_bugs'] = 'guarda i bachi pendenti';
$lang['schema'] = 'schema';
$lang['search'] = 'cerca';
$lang['refresh'] = 'aggiorna';
$lang['create'] = 'crea';
$lang['info'] = 'info';
$lang['import'] = 'importa';
$lang['logout'] = 'esci';
$lang['create_new'] = 'Crea Nuovo';
$lang['view_schema_for'] = 'Guarda schema per';
$lang['refresh_expanded_containers'] = 'Aggiorna tutti i contenitori aperti per';
$lang['create_new_entry_on'] = 'Crea una nuova voce su';
$lang['view_server_info'] = 'Guarda le informazioni fornite dal Server';
$lang['import_from_ldif'] = 'Importa voci da un file LDIF';
$lang['logout_of_this_server'] = 'Esci da questo Server';
$lang['logged_in_as'] = 'Collegato come: ';
$lang['read_only'] = 'sola lettura';
$lang['could_not_determine_root'] = 'Non posso determinare la radice del tuo albero LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Sembra che il server LDAP sia stato configurato per non rivelare la sua radice.'; 
$lang['please_specify_in_config'] = 'Per piacere specificare nel config.php';
$lang['create_new_entry_in'] = 'Crea una nuova voce in';

// Entry display
$lang['delete_this_entry'] = 'Cancella questa voce';
$lang['delete_this_entry_tooltip'] = 'Ti sarà richiesto di confermare questa decisione';
$lang['copy_this_entry'] = 'Copia questa voce';
$lang['copy_this_entry_tooltip'] = 'Copia questo oggetto in un\'altra posizione, un nuovo DN od un altro server';
$lang['export_to_ldif'] = 'Esporta in un LDIF';
$lang['export_to_ldif_tooltip'] = 'Salva un formato LDIF di questo oggetto';
$lang['export_subtree_to_ldif_tooltip'] = 'Salva un formato LDIF di questo oggetto e di tutti i suoi figli';
$lang['export_subtree_to_ldif'] = 'Esporta il ramo in un LDIF';
$lang['export_mac'] = 'Fine riga in formato Macintosh';
$lang['export_win'] = 'Fine riga in formato Windows';
$lang['export_unix'] = 'Fine riga in formato Unix';
$lang['create_a_child_entry'] = 'Crea una voce figlia';
$lang['add_a_jpeg_photo'] = 'Aggiungi una jpegPhoto';
$lang['rename_entry'] = 'Rinomina la Voce';
$lang['rename'] = 'Rinomina';
$lang['add'] = 'Aggiungi';
$lang['view'] = 'Guarda';
$lang['add_new_attribute'] = 'Aggiungi un nuovo attributo';
$lang['add_new_attribute_tooltip'] = 'Aggiungi un nuovo attributo/valore a questa voce';
$lang['internal_attributes'] = 'Attributi Interni';
$lang['hide_internal_attrs'] = 'Nascondi gli attributi interni';
$lang['show_internal_attrs'] = 'Mostra gli attributi interni';
$lang['internal_attrs_tooltip'] = 'Attributi settati automaticamente dal sistema';
$lang['entry_attributes'] = 'Attributi della Voce'; 
$lang['click_to_display'] = 'clicca per mostrare'; 
$lang['hidden'] = 'nascosto'; 
$lang['none'] = 'nessuno'; 
$lang['save_changes'] = 'Salva i Cambiamenti';
$lang['add_value'] = 'aggiungi un valore';
$lang['add_value_tooltip'] = 'Aggiungi un\'altrovalore a questo attributo';
$lang['refresh'] = 'aggiorna';
$lang['refresh_this_entry'] = 'Aggiorna questa voce';
$lang['delete_hint'] = 'Consiglio: <b>Per cancellare un attributo</b>, svuota il campo testo e clicca salva.';
$lang['viewing_read_only'] = 'Stai guardando la voce in modalità sola-lettura.';
$lang['change_entry_rdn'] = 'Cambia l\' RDN di questa voce';
$lang['no_new_attrs_available'] = 'nessun nuovo attributo disponibile per questa voce';
$lang['binary_value'] = 'Valore binario';
$lang['add_new_binary_attr'] = 'Aggiungi un Nuovo Attributo Binario';
$lang['add_new_binary_attr_tooltip'] = 'Aggiungi un nuovo attributo/valore binario da un file';
$lang['alias_for'] = 'Alias per';
$lang['download_value'] = 'valore del download';
$lang['delete_attribute'] = 'cancella l\'attributo';
$lang['true'] = 'vero';
$lang['false'] = 'falso';
$lang['none_remove_value'] = 'nessuno, rimuovi il valore';
$lang['really_delete_attribute'] = 'Cancella definitivamente il valore';

// Schema browser
$lang['the_following_objectclasses'] = 'Le seguenti <b>objectClasses</b> sono supportate da questo server LDAP';
$lang['the_following_attributes'] = 'Le seguenti <b>attributeTypes</b> sono supportate da questo server LDAP';
$lang['the_following_matching'] = 'Le seguenti <b>matching rules</b> sono supportate da questo server LDAP';
$lang['the_following_syntaxes'] = 'Le seguenti <b>syntaxes</b> sono supportate da questo server LDAP';
$lang['jump_to_objectclass'] = 'Vai a una objectClass';
$lang['jump_to_attr'] = 'Vai a un attributo';
$lang['schema_for_server'] = 'Schema per il server';
$lang['required_attrs'] = 'Attributi Richiesti';
$lang['optional_attrs'] = 'Attributi Opzionali';
$lang['OID'] = 'OID';
$lang['desc'] = 'Descrizione';
$lang['is_obsolete'] = 'Questa objectClass è <b>obsoleta</b>';
$lang['inherits'] = 'Eredita da';
$lang['jump_to_this_oclass'] = 'Vai a questa definizione della objectClass';
$lang['matching_rule_oid'] = 'Regola Corrispondente OID';
$lang['syntax_oid'] = 'Sintassi OID';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nuovi Attributi Richiesti';
$lang['requires_to_add'] = 'Questa azione richiede che tu aggiunga';
$lang['new_attributes'] = 'nuovi attributi';
$lang['new_required_attrs_instructions'] = 'Istruzioni: Per aggiungere questa objectClass a questa voce, devi specificare:';
$lang['that_this_oclass_requires'] = 'che questa objectClass richiede. Puoi farlo in questo modulo.';
$lang['add_oclass_and_attrs'] = 'Aggiungi ObjectClass e Attributi';

// General
$lang['chooser_link_tooltip'] = 'Clicca per aprire una finestra di dialogo per la selezione grafica di una voce (DN)';
$lang['no_updates_in_read_only_mode'] = 'Non puoi operare aggiornamenti mentre il server è in modalità sola-lettura';
$lang['bad_server_id'] = 'Server id errata';
$lang['not_enough_login_info'] = 'Non abbastanza informazioni per collegarsi al server. Per piacere controlla la tua configurazione.';
$lang['could_not_connect'] = 'Non ho potuto collegarmi al server LDAP.';
$lang['could_not_perform_ldap_mod_add'] = 'Non ho potuto eseguire l\'operazione ldap_mod_add.';

// Add value form
$lang['add_new'] = 'Aggiungi nuovo';
$lang['value_to'] = 'valore a';
$lang['server'] = 'Server';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Lista corrente di';
$lang['values_for_attribute'] = 'valori per l\'attributo';
$lang['inappropriate_matching_note'] = 'Nota: Tu riceverai un errore "corrispondenza inappropiata" se non hai<br />una regola <tt>EQUALITY</tt> per questo attributo sul tuo server LDAP.';
$lang['enter_value_to_add'] = 'Inserisci il valore che vorresti aggiungere:';
$lang['new_required_attrs_note'] = 'Nota: ti potrebbe essere chiesto di inserire nuovi attributi<br />che questa objectClass richiede.'; 
$lang['syntax'] = 'Sintassi';

?>
