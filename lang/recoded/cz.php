<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/cz.php,v 1.2 2004/06/01 19:39:53 i18phpldapadmin Exp $
/**
 * Translated to Czech by Radek "rush" Senfeld <rush@logic.cz>

 *        ---   INSTRUCTIONS FOR TRANSLATORS   ---
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

// Search form
$lang['simple_search_form_str'] = 'Rychlé vyhledávání';
$lang['advanced_search_form_str'] = 'Rozšířené vyhledávání';
$lang['server'] = 'Server';
$lang['search_for_entries_whose'] = 'Vyhledat objekty kde';
$lang['base_dn'] = 'Výchozí <acronym title="Distinguished Name">DN</acronym>';
$lang['search_scope'] = 'Oblast prohledávání';
$lang['show_attributes'] = 'Zobrazovat atributy';
$lang['Search'] = 'Vyhledat';
$lang['predefined_search_str'] = 'Zvolte předdefinované vyhledávání';
$lang['predefined_searches'] = 'Předdefinovaná vyhledávání';
$lang['no_predefined_queries'] = 'V config.php nejsou definovány žádné dotazy.';

// Tree browser
$lang['request_new_feature'] = 'Napište si o novou funkci';
$lang['report_bug'] = 'Nahlásit chybu';
$lang['schema'] = 'schéma';
$lang['search'] = 'vyhledat';
$lang['create'] = 'vytvořit';
$lang['info'] = 'info';
$lang['import'] = 'import';
$lang['refresh'] = 'obnovit';
$lang['logout'] = 'odhlásit se';
$lang['create_new'] = 'Vytvořit nový';
$lang['view_schema_for'] = 'Zobrazit schéma pro';
$lang['refresh_expanded_containers'] = 'Obnovit všechny otevřené složky';
$lang['create_new_entry_on'] = 'Vytvořit nový objekt v';
$lang['new'] = 'nový';
$lang['view_server_info'] = 'Zobrazit serverem poskytované informace';
$lang['import_from_ldif'] = 'Importovat data ze souboru LDIF';
$lang['logout_of_this_server'] = 'Odhlásit se od tohoto serveru';
$lang['logged_in_as'] = 'Přihlášen jako: ';
$lang['read_only'] = 'jen pro čtení';
$lang['read_only_tooltip'] = 'Tento atribut byl administrátorem phpLDAPadminu označen jako "jen pro čtení".';
$lang['could_not_determine_root'] = 'Nepodařilo se zjistit kořen Vašeho LDAP stromu.';
$lang['ldap_refuses_to_give_root'] = 'Zdá se, že LDAP server je nastavený tak, že nezobrazuje svůj kořen.';
$lang['please_specify_in_config'] = 'Nastavte ho prosím v souboru config.php';
$lang['create_new_entry_in'] = 'Vytvořit nový objekt v';
$lang['login_link'] = 'Přihlásit se...';
$lang['login'] = 'přihlásit';

// Entry display
$lang['delete_this_entry'] = 'Smazat tento objekt';
$lang['delete_this_entry_tooltip'] = 'Budete požádáni o potvrzení tohoto rozhodnutí';
$lang['copy_this_entry'] = 'Kopírovat tento objekt';
$lang['copy_this_entry_tooltip'] = 'Okopíruje tento objekt do jiného umístění, nového DN, nebo na jiný server';
$lang['export'] = 'Export';
$lang['export_tooltip'] = 'Uložit přepis objektu';
$lang['export_subtree_tooltip'] = 'Uloží přepis tohoto objektu a všech jeho potomků';
$lang['export_subtree'] = 'Exportovat podstrom';
$lang['create_a_child_entry'] = 'Vytvořit nového potomka';
$lang['rename_entry'] = 'Přejmenovat objekt';
$lang['rename'] = 'Přejmenovat';
$lang['add'] = 'Přidat';
$lang['view'] = 'Zobrazit';
$lang['view_one_child'] = 'Zobrazit potomka';
$lang['view_children'] = 'Zobrazit potomky (%s)';
$lang['add_new_attribute'] = 'Přidat nový atribut';
$lang['add_new_objectclass'] = 'Přidat objectClass';
$lang['hide_internal_attrs'] = 'Schovat interní atributy';
$lang['show_internal_attrs'] = 'Zobrazit interní atributy';
$lang['attr_name_tooltip'] = 'Klepnutím zobrazíte definiční schéma pro atribut typu \'%s\'';
$lang['none'] = 'žádný';
$lang['no_internal_attributes'] = 'Žádné interní atributy';
$lang['no_attributes'] = 'Tento objekt nemá atributy';
$lang['save_changes'] = 'Uložit změny';
$lang['add_value'] = 'přidat hodnotu';
$lang['add_value_tooltip'] = 'Přidá další hodnotu k atributu \'%s\'';
$lang['refresh_entry'] = 'Obnovit';
$lang['refresh_this_entry'] = 'Obnovit tento objekt';
$lang['delete_hint'] = 'Rada: <b>Pro smazání atributu</b> vyprázděte textové políčko a klepněte na Uložit.';
$lang['attr_schema_hint'] = 'Rada: <b>K zobrazení schémata pro atribut</b> klepněte na název atributu.';
$lang['attrs_modified'] = 'Některé atributy (%s) byly modifikováný a jsou zvýrazněny dole.';
$lang['attr_modified'] = 'Atribut (%s) byl změněn a je zvýrazněn dole.';
$lang['viewing_read_only'] = 'Prohlížení objekt v módu "pouze pro čtení".';
$lang['no_new_attrs_available'] = 'nejsou dostupné žádné nové atributy pro tento objekt';
$lang['no_new_binary_attrs_available'] = 'nejsou dostupné žádné nové binární atributy pro tento objekt';
$lang['binary_value'] = 'Binarní hodnota';
$lang['add_new_binary_attr'] = 'Přidat nový binarní atribut';
$lang['alias_for'] = 'Poznámka: \'%s\' je aliasem pro \'%s\'';
$lang['download_value'] = 'stáhnout data';
$lang['delete_attribute'] = 'smazat atribut';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = 'žádný, odebrat hodnotu';
$lang['really_delete_attribute'] = 'Skutečně smazat atribut';
$lang['add_new_value'] = 'Přidat novou hodnotu';

// Schema browser
$lang['the_following_objectclasses'] = 'Následující <b>objectClass</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_attributes'] = 'Následující <b>attributeType</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_matching'] = 'Následující <b>kritéria výběru</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_syntaxes'] = 'Následující <b>syntaxe</b> jsou podporovány tímto LDAP serverem.';
$lang['schema_retrieve_error_1']='Server plně nepodporuje LDAP protocol.';
$lang['schema_retrieve_error_2']='Vaše verze PHP korektně neprovede tento dotaz.';
$lang['schema_retrieve_error_3']='Nebo možná phpLDAPadmin neví jak získat schéma pro Váš server.';
$lang['jump_to_objectclass'] = 'Jdi na objectClass';
$lang['jump_to_attr'] = 'Jdi na typ atributu';
$lang['jump_to_matching_rule'] = 'Jdi na Matching Rule';
$lang['schema_for_server'] = 'Schéma serveru';
$lang['required_attrs'] = 'Vyžadované atributy';
$lang['optional_attrs'] = 'Volitelné atributy';
$lang['optional_binary_attrs'] = 'Volitelné binární atributy';
$lang['OID'] = 'OID';
$lang['aliases']='Aliasy';
$lang['desc'] = 'Popis';
$lang['no_description']='žádný popis';
$lang['name'] = 'Název';
$lang['equality']='Equality';
$lang['is_obsolete'] = 'Tato objectClass je <b>zastaralá</b>';
$lang['inherits'] = 'Odvozeno od objectClass';
$lang['inherited_from'] = 'Odvozeno od objectClass';
$lang['parent_to'] = 'Rodičovská objectClass';
$lang['jump_to_this_oclass'] = 'Jdi na definici této objectClass';
$lang['matching_rule_oid'] = 'Výběrové kritérium OID';
$lang['syntax_oid'] = 'Syntaxe OID';
$lang['not_applicable'] = 'nepoužitelný';
$lang['not_specified'] = 'nespecifikovaný';
$lang['character']='znak'; 
$lang['characters']='znaků';
$lang['used_by_objectclasses']='Používáno těmito objectClass';
$lang['used_by_attributes']='Používají atributy';
$lang['maximum_length']='Maximální délka';
$lang['attributes']='Typy atributů';
$lang['syntaxes']='Syntaxe';
$lang['matchingrules']='Matching Rules';
$lang['oid']='OID';
$lang['obsolete']='Zastaralé';
$lang['ordering']='Řazení';
$lang['substring_rule']='Substring Rule';
$lang['single_valued']='Single Valued';
$lang['collective']='Collective';
$lang['user_modification']='User Modification';
$lang['usage']='Použití';
$lang['could_not_retrieve_schema_from']='Nelze získat schéma z';
$lang['type']='Typ';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Objekt \'%s\' byl úspěšně odstraněn.';
$lang['you_must_specify_a_dn'] = 'Musíte zadat DN';
$lang['could_not_delete_entry'] = 'Nebylo možné odstranit objekt: %s';
$lang['no_such_entry'] = 'Objekt neexistuje: %s';
$lang['delete_dn'] = 'Smazat %s';
$lang['permanently_delete_children'] = 'Odstranit také všechny potomky?';
$lang['entry_is_root_sub_tree'] = 'Tento objekt je kořenem podstromu, který obsahuje %s objektů.';
$lang['view_entries'] = 'zobrazit objekty';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin rekurzivně odstraní tento objekt a všech jeho %s potomků. Pozorně si prohlédněte seznam objektů, které tato operace odstraní. Přejete si pokračovat?';
$lang['confirm_recursive_delete_note'] = 'Poznámka: tato operace může mít fatální následky a nelze ji vrátit zpět. Speciální pozornost věnujte aliasům, odkazům a ostatním věcem, které můžou způsobit problémy.';
$lang['delete_all_x_objects'] = 'Smazat všech %s objektů';
$lang['recursive_delete_progress'] = 'Průběh rekurzivního odstranění';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Objekt %s a jeho podstrom byly úspěšně odstraněny.';
$lang['failed_to_delete_entry'] = 'Nepodařilo se odstranit objekt %s';
$lang['list_of_entries_to_be_deleted'] = 'Seznam objektů k odstranění:';
$lang['sure_permanent_delete_object']='Jste si skutečně jisti, že chcete odstranit tento objekt?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'Atribut "%s" je v konfiguraci phpLDAPadminu označen jako "jen pro čtení".';
$lang['no_attr_specified'] = 'Nebylo zadáno jméno atributu.';
$lang['no_dn_specified'] = 'Nebylo zadáno DN';

// Adding attributes
$lang['left_attr_blank'] = 'Nevyplnili jste hodnotu atributu. Vraťte se zpět a akci opakujte.';
$lang['failed_to_add_attr'] = 'Přidání atributu selhalo.';
$lang['file_empty'] = 'Soubor, který jste zvolili je buď prázdný nebo neexistuje. Vraťte se prosím zpět a akci opakujte.';
$lang['invalid_file'] = 'Bezpečnostní chyba: Soubor, který uploadujete může být závadný.';
$lang['warning_file_uploads_disabled'] = 'V konfiguraci PHP jsou zakázány uploady souborů. Pro pokračování upravte prosím php.ini.';
$lang['uploaded_file_too_big'] = 'Soubor, který se pokoušeli uložit je příliš veliký. Upravte prosím hodnotu upload_max_size v php.ini.';
$lang['uploaded_file_partial'] = 'Při uploadu souboru došlo zřejmě k selhání sítě, neboť se podařilo získat jen část souboru.';
$lang['max_file_size'] = 'Maximální velikost souboru: %s';

// Updating values
$lang['modification_successful'] = 'Úprava proběhla úspěšně!';
$lang['change_password_new_login'] = 'Kvůli změně svého hesla se nyní musíte přihlásit znova - s novým heslem.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nový vyžadovaný atribut';
$lang['requires_to_add'] = 'K provedení této akce musíte přidat';
$lang['new_attributes'] = 'nové atributy';
$lang['new_required_attrs_instructions'] = 'Návod: K přiřazení této objectClass k vybranému objektu musíte zadat';
$lang['that_this_oclass_requires'] = 'atributy, které jsou touto objectClass vyžadovány. Můžete tak učinit v tomto formuláři.';
$lang['add_oclass_and_attrs'] = 'Přidat objectClass a atributy';
$lang['objectclasses'] = 'objectClassy';

// General
$lang['chooser_link_tooltip'] = 'Otevře popup okno, ve kterém zvolíte DN';
$lang['no_updates_in_read_only_mode'] = 'Nelze provádět úpravy dokud je server v módu "pouze pro čtení"';
$lang['bad_server_id'] = 'Špatné ID serveru';
$lang['not_enough_login_info'] = 'Nedostatek informací pro přihlášení k serveru. Ověřte prosím nastavení.';
$lang['could_not_connect'] = 'Nelze se připojit k LDAP serveru.';
$lang['could_not_connect_to_host_on_port'] = 'Nelze se připojit k "%s" na portu "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Nelze provést ldap_mod_add operaci.';
$lang['bad_server_id_underline'] = 'server_id: ';
$lang['success'] = 'Hotovo';
$lang['server_colon_pare'] = 'Server: ';
$lang['look_in'] = 'Prohlížení: ';
$lang['missing_server_id_in_query_string'] = 'V požadavku nebylo uvedeno žádné ID serveru!';
$lang['missing_dn_in_query_string'] = 'V požadavku nebyl uveden žádný DN!';
$lang['back_up_p'] = 'O úroveň výš...';
$lang['no_entries'] = 'žádné objekty';
$lang['not_logged_in'] = 'Nepřihlášen';
$lang['could_not_det_base_dn'] = 'Nelze zjistit výchozí DN';
$lang['please_report_this_as_a_bug']='Nahlašte toto prosím jako chybu.';
$lang['reasons_for_error']='Toto se může přihodit z několika příčin. Nejpravděpodobnější jsou:';
$lang['yes']='Ano';
$lang['no']='Ne';
$lang['go']='Jdi';
$lang['delete']='Odstranit';
$lang['back']='Zpět';
$lang['object']='objekt';
$lang['delete_all']='Odstranit vše';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'rada';
$lang['bug'] = 'chyba';
$lang['warning'] = 'upozornění';
$lang['light'] = 'light'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Proveď &gt;&gt;';

// Add value form
$lang['add_new'] = 'Přidat nový';
$lang['value_to'] = 'hodnota pro';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Současný výpis';
$lang['values_for_attribute'] = 'hodnoty pro atribut';
$lang['inappropriate_matching_note'] = 'Poznámka: Pokud nenastavíte na tomto LDAP serveru pravidlo<br /><tt>EQUALITY</tt> pro tento atribut, dojde k chybě při výběru objektů.';
$lang['enter_value_to_add'] = 'Zadejte hodnotu, kterou chcete přidat:';
$lang['new_required_attrs_note'] = 'Poznámka: Není vyloučené, že budete vyzváni k zadání nových atributů vyžadovaných touto objectClass';
$lang['syntax'] = 'Syntaxe';

//copy.php
$lang['copy_server_read_only'] = 'Nemůžete provádět změny dokud je server v módu "jen pro čtení"';
$lang['copy_dest_dn_blank'] = 'Ponechali jste kolonku cílové DN prázdnou.';
$lang['copy_dest_already_exists'] = 'Objekt (%s) již v cílovém DN existuje.';
$lang['copy_dest_container_does_not_exist'] = 'Cílová složka (%s) neexistuje.';
$lang['copy_source_dest_dn_same'] = 'Zdrojové a cílové DN se shodují.';
$lang['copy_copying'] = 'Kopíruji ';
$lang['copy_recursive_copy_progress'] = 'Průběh rekurzivního kopírování';
$lang['copy_building_snapshot'] = 'Sestavuji obraz stromu ke kopírování... ';
$lang['copy_successful_like_to'] = 'Kopie úspěšně dokončena! Přejete si ';
$lang['copy_view_new_entry'] = 'zobrazit nový objekt';
$lang['copy_failed'] = 'Nepodařilo se okopírovat DN: ';

//edit.php
$lang['missing_template_file'] = 'Upozornění: chybí šablona, ';
$lang['using_default'] = 'Používám výchozí.';
$lang['template'] = 'Šablona';
$lang['must_choose_template'] = 'Musíte zvolit šablonu';
$lang['invalid_template'] = '%s je neplatná šablona';
$lang['using_template'] = 'použítím šablony';
$lang['go_to_dn'] = 'Jdi na %s';

//copy_form.php
$lang['copyf_title_copy'] = 'Kopírovat ';
$lang['copyf_to_new_object'] = 'jako nový objekt';
$lang['copyf_dest_dn'] = 'Cílové DN';
$lang['copyf_dest_dn_tooltip'] = 'Celé DN nového objektu bude vytvořeno kopií zdrojového objektu';
$lang['copyf_dest_server'] = 'Cílový server';
$lang['copyf_note'] = 'Rada: Kopírování mezi servery funguje jedině za předpokladu, že nedojde k neshodě schémat';
$lang['copyf_recursive_copy'] = 'Při kopírování zahrnout všechny potomky tohoto objektu.';
$lang['recursive_copy'] = 'Rekurzivní kopie';
$lang['filter'] = 'Filtr';
$lang['filter_tooltip'] = 'Při rekurzivní kopii pracovat pouze s objekty, které splňují zvolený filtr';

//create.php
$lang['create_required_attribute'] = 'Nevyplnili jste pole pro vyžadovaný atribut <b>%s</b>.';
$lang['redirecting'] = 'Přesměrovávám';
$lang['here'] = 'zde';
$lang['create_could_not_add'] = 'Nelze objekt do LDAP serveru přidat.';

//create_form.php
$lang['createf_create_object'] = 'Vytvořit objekt';
$lang['createf_choose_temp'] = 'Vyberte šablonu';
$lang['createf_select_temp'] = 'Zvolte šablonu pro vytvoření objektu';
$lang['createf_proceed'] = 'Provést';
$lang['rdn_field_blank'] = 'Ponechali jste pole RDN nevyplněné.';
$lang['container_does_not_exist'] = 'Složka (%s) neexistuje. Opakujte prosím akci.';
$lang['no_objectclasses_selected'] = 'Nepřiřadili jste žádné objectClass k tomuto objektu. Vraťte se prosím zpět a akci opakujte.';
$lang['hint_structural_oclass'] = 'Nápověda: Musíte zvolit alespoň jednu structural objectClass.';

//creation_template.php
$lang['ctemplate_on_server'] = 'Na serveru';
$lang['ctemplate_no_template'] = 'V POST požadavku nebyla zaslána žádná šablona.';
$lang['ctemplate_config_handler'] = 'Vaše nastavení uvádí obsluhovač ';
$lang['ctemplate_handler_does_not_exist'] = 'pro tuto šablonu. Ale tento obsluhovač nelze v adresáři templates/creation nalézt.';
$lang['create_step1'] = 'Krok 1 ze 2: Jméno a objectClass(y)';
$lang['create_step2'] = 'Krok 2 ze 2: Atributy a hodnoty';
$lang['relative_distinguished_name'] = 'Relativní Distinguished Name';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(příklad: cn=MujNovyUzivatel)';
$lang['container'] = 'Složka';
$lang['alias_for'] = 'Alias k %s';

// search.php
$lang['you_have_not_logged_into_server'] = 'Nelze provádět vyhledávání na serveru bez předchozího přihlášení.';
$lang['click_to_go_to_login_form'] = 'Klepnutím budete přesměrováni na formulář k přihlášení';
$lang['unrecognized_criteria_option'] = 'Neznámá vyhledávací kritéria: ';
$lang['if_you_want_to_add_criteria'] = 'Pokud si přejete přidat svoje vlastní vyhledávací kritéria, ujistěte se, že jste je přidali do search.php.';
$lang['entries_found'] = 'Nalezené objekty: ';
$lang['filter_performed'] = 'Uplatněný filtr: ';
$lang['search_duration'] = 'Vyhledávání dokončeno za';
$lang['seconds'] = 'sekund';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Oblast vyhledávání';
$lang['scope_sub'] = 'Celý podstrom';
$lang['scope_one'] = 'O jednu úroveň níž';
$lang['scope_base'] = 'Pouze výchozí DN';
$lang['standard_ldap_search_filter'] = 'Standardní LDAP vyhledávací filtr. Přiklad: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Vyhledávací filtr';
$lang['list_of_attrs_to_display_in_results'] = 'Seznam atributů zobrazených ve výsledku hledání (oddělené čárkou)';
$lang['show_attributes'] = 'Zobrazit atributy';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Vyhledat objekty kde';
$lang['equals'] = 'je';
$lang['starts with'] = 'začíná na';
$lang['contains'] = 'obsahuje';
$lang['ends with'] = 'končí na';
$lang['sounds like'] = 'zní jako';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Nelze získat informace ze serveru LDAP';
$lang['server_info_for'] = 'Server info pro: ';
$lang['server_reports_following'] = 'Server o sobě poskytuje následující informace';
$lang['nothing_to_report'] = 'Server neposkytuje žádné informace.';

//update.php
$lang['update_array_malformed'] = 'update_array je poškozené. Může se jednat o chybu v phpLDAPadmin. Prosíme Vás, abyste chybu nahlásili.';
$lang['could_not_perform_ldap_modify'] = 'Nelze provést operaci ldap_modify.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Přejete si provést tyto změny?';
$lang['attribute'] = 'Atribut';
$lang['old_value'] = 'Původní hodnota';
$lang['new_value'] = 'Nová hodnota';
$lang['attr_deleted'] = '[atribut odstraněn]';
$lang['commit'] = 'Odeslat';
$lang['cancel'] = 'Storno';
$lang['you_made_no_changes'] = 'Neprovedli jste žádné změny';
$lang['go_back'] = 'Zpět';

// welcome.php
$lang['welcome_note'] = 'K navigaci použijte prosím menu v levé části obrazovky';
$lang['credits'] = 'Autoři';
$lang['changelog'] = 'ChangeLog';
$lang['donate'] = 'Podpořit projekt';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nebezpečný název souboru: ';
$lang['no_such_file'] = 'Soubor nelze nalézt: ';

//function.php
$lang['auto_update_not_setup'] = 'V konfiguraci jste zapnuli podporu auto_uid_numbers pro <b>%s</b>, ale nespecifikovali jste auto_uid_number_mechanism. Napravte prosím nejprve tento problém.';
$lang['uidpool_not_set'] = 'V konfiguraci serveru <b>%s</b> jste specifikovali <tt>auto_uid_number_mechanism</tt> jako <tt>uidpool</tt>, ale neuvedli jste audo_uid_number_uid_pool_dn. Napravte prosím nejprve tento problém.';
$lang['uidpool_not_exist'] = 'Zdá se, že uidPool uvedený v konfiguraci (<tt>%s</tt>) neexistuje.';
$lang['specified_uidpool'] = 'V konfiguraci serveru <b>%s</b> jste specifikovali <tt>auto_uid_number_mechanism</tt> jako <tt>search</tt>, ale neuvedli jste <tt>auto_uid_number_search_base</tt>. Napravte prosím nejprve tento problém.';
$lang['auto_uid_invalid_credential'] = 'Se zadanými přístupovými oprávněními se nelze připojit k <b>%s</b> a získat auto_uid. Zkontrolujte prosím konfiguraci.'; 
$lang['bad_auto_uid_search_base'] = 'V konfiguraci phpLDAPadminu je uveden neplatný parametr auto_uid_search_base pro server %s';
$lang['auto_uid_invalid_value'] = 'V konfiguraci je uvedena neplatná hodnota auto_uid_number_mechanism (<tt>%s</tt>). Platné hodnoty jsou pouze <tt>uidpool</tt> a <tt>search</tt>. Napravte prosím nejprve tento problém.';
$lang['error_auth_type_config'] = 'Chyba: Ve svém konfiguračním souboru jste u položky $servers[\'auth_type\'] uvedli chybnou hodnotu \'%s\'. Platné hodnoty jsou pouze \'config\' a \'form\'.';
$lang['php_install_not_supports_tls'] = 'Tato instalace PHP neobsahuje podporu pro TLS';
$lang['could_not_start_tls'] = 'Nelze inicializovat TLS.<br />Zkontolujte prosím konfiguraci svého LDAP serveru.';
$lang['could_not_bind_anon'] = 'K serveru se nelze připojit anonymně.';
$lang['could_not_bind'] = 'Nelze se připojit k serveru LDAP.';
$lang['anonymous_bind'] = 'Připojit anonymně';
$lang['bad_user_name_or_password'] = 'Nesprávné jméno nebo heslo. Opakujte přihlášení.';
$lang['redirecting_click_if_nothing_happens'] = 'Přesměrovávám... Klepněte sem, pokud se nic nestane.';
$lang['successfully_logged_in_to_server'] = 'Úspěšně jste se přihlásili k serveru <b>%s</b>';
$lang['could_not_set_cookie'] = 'Cookie nemohla být uložena.';
$lang['ldap_said'] = '<b>Odpověď LDAP serveru</b>: %s<br /><br />';
$lang['ferror_error'] = 'Chyba';
$lang['fbrowse'] = 'procházet';
$lang['delete_photo'] = 'Odstranit fotografii';
$lang['install_not_support_blowfish'] = 'Tato instalace PHP neobsahuje podporu pro šifru Blowfish.';
$lang['install_not_support_md5crypt'] = 'Tato instalace PHP neobsahuje podporu pro šifru md5crypt.';
$lang['install_no_mash'] = 'Tato instalace PHP nepodporuje funkci mhash(). Nelze aplikovat SHA hash.';
$lang['jpeg_contains_errors'] = 'jpegPhoto obsahuje chyby<br />';
$lang['ferror_number'] = '<b>Číslo chyby</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] = '<b>Popis</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Číslo chyby</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Popis</b>: (popis není k dispozici)<br />';
$lang['ferror_submit_bug'] = 'Pokud je toto chyba v phpLDAPadmin, <a href=\'%s\'>napište nám</a> o tom.';
$lang['ferror_unrecognized_num'] = 'Neznámé číslo chyby: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Narazili jste na nezávažnou, droubnou až zanedbatelnou chybu v phpLDAPadmin!</b></td></tr><tr><td>Chyba:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Soubor:</td>
             <td><b>%s</b> řádka <b>%s</b>, voláno z <b>%s</b></td></tr><tr><td>Verze:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Klepnutím prosím ohlášte chybu</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Blahopřejeme! Nalezli jste chybu v phpLDAPadmin. :-)<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Chyba:</td><td><b>%s</b></td></tr>
	     <tr><td>Vážnost:</td><td><b>%s</b></td></tr>
	     <tr><td>Soubor:</td><td><b>%s</b></td></tr>
	     <tr><td>Řádka:</td><td><b>%s</b></td></tr>
	     <tr><td>Voláno z:</td><td><b>%s</b></td></tr>
	     <tr><td>Verze PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Verze PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web server:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Klepnutím dole prosím ohlašte chybu!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importovat soubor LDIF';
$lang['select_ldif_file'] = 'Zvolte soubor LDIF:';
$lang['select_ldif_file_proceed'] = 'Proveď &gt;&gt;';
$lang['dont_stop_on_errors'] = 'Ignorovat chyby';

//ldif_import
$lang['add_action'] = 'Přidávání...';
$lang['delete_action'] = 'Odstraňování...';
$lang['rename_action'] = 'Přejmenovávání...';
$lang['modify_action'] = 'Upravování...';
$lang['warning_no_ldif_version_found'] = 'Nebyla nalezena verze. Předpokládám 1.';
$lang['valid_dn_line_required'] = 'Je vyžadován platný řádek s DN.';
$lang['missing_uploaded_file'] = 'Soubor LDIF nebyl nalezen.';
$lang['no_ldif_file_specified.'] = 'Neuvedli jste LDIF soubor. Opakujte prosím akci.';
$lang['ldif_file_empty'] = 'Soubor LDIF je prázdný.';
$lang['empty'] = 'prázdný';
$lang['file'] = 'Soubor';
$lang['number_bytes'] = '%s bajtů';

$lang['failed'] = 'selhal';
$lang['ldif_parse_error'] = 'Chyba v souboru LDIF';
$lang['ldif_could_not_add_object'] = 'Nelze přidat objekt:';
$lang['ldif_could_not_rename_object'] = 'Nelze přejmenovat objekt:';
$lang['ldif_could_not_delete_object'] = 'Nelze odstranit objekt:';
$lang['ldif_could_not_modify_object'] = 'Nelze upravit objekt:';
$lang['ldif_line_number'] = 'Číslo řádku:';
$lang['ldif_line'] = 'Řádek:';

// Exports
$lang['export_format'] = 'Formát exportu';
$lang['line_ends'] = 'Konce řádků';
$lang['must_choose_export_format'] = 'Musíte zvolit exportní formát.';
$lang['invalid_export_format'] = 'Neplatný exportní formát';
$lang['no_exporter_found'] = 'Nebyla nalezena žádná aplikace pro export.';
$lang['error_performing_search'] = 'Během vyhledávání došlo k chybě.';
$lang['showing_results_x_through_y'] = 'Výsledky od %s do %s.';
$lang['searching'] = 'Vyhledávám...';
$lang['size_limit_exceeded'] = 'Byl překročen limitní parametr pro vyhledávání.';
$lang['entry'] = 'Objekt';
$lang['ldif_export_for_dn'] = 'LDIF Export objektu: %s';
$lang['generated_on_date'] = 'Generováno phpLDAPadminem dne %s';
$lang['total_entries'] = 'Celkem objektů';
$lang['dsml_export_for_dn'] = 'DSLM Export objektu: %s';

// logins
$lang['could_not_find_user'] = 'Nelze nalézt uživatele "%s"';
$lang['password_blank'] = 'Nezadali jste uživatelské heslo.';
$lang['login_cancelled'] = 'Přihlašování zrušeno.';
$lang['no_one_logged_in'] = 'Nikdo není přihlášen k tomuto serveru.';
$lang['could_not_logout'] = 'Nelze se odhlásit.';
$lang['unknown_auth_type'] = 'Neznámý auth_type: %s';
$lang['logged_out_successfully'] = 'Odhlášení od serveru <b>%s</b> proběhlo úspěšně.';
$lang['authenticate_to_server'] = 'Ověření vůči serveru %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Upozornění: Toto spojení není šifrováné.';
$lang['not_using_https'] = 'Nepřipojujete se pomocí \'https\'. Prohlížeč odešle Vaše přihlášení v nešifrované podobě.';
$lang['login_dn'] = 'Přihlašovací DN';
$lang['user_name'] = 'Uživatel';
$lang['password'] = 'Heslo';
$lang['authenticate'] = 'Přihlásit';

// Entry browser
$lang['entry_chooser_title'] = 'Zvolit objekt';

// Index page
$lang['need_to_configure'] = 'Nejprve je třeba phpLDAPadmin nakonfigurovat. Toho docílíte upravou souboru \'config.php\'. Ukázková konfigurace je k nalezení v souboru \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Mazání je v režimu "jen pro čtení" zakázáno.';
$lang['error_calling_mass_delete'] = 'Chyba při volání mass_delete.php. V POSTu chybí proměnná mass_delete.';
$lang['mass_delete_not_array'] = 'mass_delete v POSTu není pole.';
$lang['mass_delete_not_enabled'] = 'Hromadý výmaz není umožněn. Můžete ho povolit v souboru config.php.';
$lang['mass_deleting'] = 'Hromadný výmaz';
$lang['mass_delete_progress'] = 'Průběh odstraňování na serveru "%s"';
$lang['malformed_mass_delete_array'] = 'Zdeformované pole mass_delete.';
$lang['no_entries_to_delete'] = 'Nevybrali jste žádné objekty k odstranění.';
$lang['deleting_dn'] = 'Odstraňuji %s';
$lang['total_entries_failed'] = '%s z %s objektů se nepodařilo odstranit.';
$lang['all_entries_successful'] = 'Všechny objekty byly úspěšně odstraněny.';
$lang['confirm_mass_delete'] = 'Potvďte hromadný výmaz v počtu %s objektů na serveru %s';
$lang['yes_delete'] = 'Ano, odstranit!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Nelze přejmenovat objekt, který má potomky. Toto se například vztahuje na všechny objekty, které nejsou typu "list".';
$lang['no_rdn_change'] = 'Nezměnili jste RDN';
$lang['invalid_rdn'] = 'Neplatná hodnota RDN';
$lang['could_not_rename'] = 'Objekt nelze přejmenovat';

?>
