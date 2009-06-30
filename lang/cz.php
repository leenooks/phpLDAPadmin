<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/cz.php,v 1.2 2004/06/01 19:39:52 i18phpldapadmin Exp $
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
$lang['advanced_search_form_str'] = 'Roz¹íøené vyhledávání';
$lang['server'] = 'Server';
$lang['search_for_entries_whose'] = 'Vyhledat objekty kde';
$lang['base_dn'] = 'Výchozí <acronym title="Distinguished Name">DN</acronym>';
$lang['search_scope'] = 'Oblast prohledávání';
$lang['show_attributes'] = 'Zobrazovat atributy';
$lang['Search'] = 'Vyhledat';
$lang['predefined_search_str'] = 'Zvolte pøeddefinované vyhledávání';
$lang['predefined_searches'] = 'Pøeddefinovaná vyhledávání';
$lang['no_predefined_queries'] = 'V config.php nejsou definovány ¾ádné dotazy.';

// Tree browser
$lang['request_new_feature'] = 'Napi¹te si o novou funkci';
$lang['report_bug'] = 'Nahlásit chybu';
$lang['schema'] = 'schéma';
$lang['search'] = 'vyhledat';
$lang['create'] = 'vytvoøit';
$lang['info'] = 'info';
$lang['import'] = 'import';
$lang['refresh'] = 'obnovit';
$lang['logout'] = 'odhlásit se';
$lang['create_new'] = 'Vytvoøit nový';
$lang['view_schema_for'] = 'Zobrazit schéma pro';
$lang['refresh_expanded_containers'] = 'Obnovit v¹echny otevøené slo¾ky';
$lang['create_new_entry_on'] = 'Vytvoøit nový objekt v';
$lang['new'] = 'nový';
$lang['view_server_info'] = 'Zobrazit serverem poskytované informace';
$lang['import_from_ldif'] = 'Importovat data ze souboru LDIF';
$lang['logout_of_this_server'] = 'Odhlásit se od tohoto serveru';
$lang['logged_in_as'] = 'Pøihlá¹en jako: ';
$lang['read_only'] = 'jen pro ètení';
$lang['read_only_tooltip'] = 'Tento atribut byl administrátorem phpLDAPadminu oznaèen jako "jen pro ètení".';
$lang['could_not_determine_root'] = 'Nepodaøilo se zjistit koøen Va¹eho LDAP stromu.';
$lang['ldap_refuses_to_give_root'] = 'Zdá se, ¾e LDAP server je nastavený tak, ¾e nezobrazuje svùj koøen.';
$lang['please_specify_in_config'] = 'Nastavte ho prosím v souboru config.php';
$lang['create_new_entry_in'] = 'Vytvoøit nový objekt v';
$lang['login_link'] = 'Pøihlásit se...';
$lang['login'] = 'pøihlásit';

// Entry display
$lang['delete_this_entry'] = 'Smazat tento objekt';
$lang['delete_this_entry_tooltip'] = 'Budete po¾ádáni o potvrzení tohoto rozhodnutí';
$lang['copy_this_entry'] = 'Kopírovat tento objekt';
$lang['copy_this_entry_tooltip'] = 'Okopíruje tento objekt do jiného umístìní, nového DN, nebo na jiný server';
$lang['export'] = 'Export';
$lang['export_tooltip'] = 'Ulo¾it pøepis objektu';
$lang['export_subtree_tooltip'] = 'Ulo¾í pøepis tohoto objektu a v¹ech jeho potomkù';
$lang['export_subtree'] = 'Exportovat podstrom';
$lang['create_a_child_entry'] = 'Vytvoøit nového potomka';
$lang['rename_entry'] = 'Pøejmenovat objekt';
$lang['rename'] = 'Pøejmenovat';
$lang['add'] = 'Pøidat';
$lang['view'] = 'Zobrazit';
$lang['view_one_child'] = 'Zobrazit potomka';
$lang['view_children'] = 'Zobrazit potomky (%s)';
$lang['add_new_attribute'] = 'Pøidat nový atribut';
$lang['add_new_objectclass'] = 'Pøidat objectClass';
$lang['hide_internal_attrs'] = 'Schovat interní atributy';
$lang['show_internal_attrs'] = 'Zobrazit interní atributy';
$lang['attr_name_tooltip'] = 'Klepnutím zobrazíte definièní schéma pro atribut typu \'%s\'';
$lang['none'] = '¾ádný';
$lang['no_internal_attributes'] = '®ádné interní atributy';
$lang['no_attributes'] = 'Tento objekt nemá atributy';
$lang['save_changes'] = 'Ulo¾it zmìny';
$lang['add_value'] = 'pøidat hodnotu';
$lang['add_value_tooltip'] = 'Pøidá dal¹í hodnotu k atributu \'%s\'';
$lang['refresh_entry'] = 'Obnovit';
$lang['refresh_this_entry'] = 'Obnovit tento objekt';
$lang['delete_hint'] = 'Rada: <b>Pro smazání atributu</b> vyprázdìte textové políèko a klepnìte na Ulo¾it.';
$lang['attr_schema_hint'] = 'Rada: <b>K zobrazení schémata pro atribut</b> klepnìte na název atributu.';
$lang['attrs_modified'] = 'Nìkteré atributy (%s) byly modifikováný a jsou zvýraznìny dole.';
$lang['attr_modified'] = 'Atribut (%s) byl zmìnìn a je zvýraznìn dole.';
$lang['viewing_read_only'] = 'Prohlí¾ení objekt v módu "pouze pro ètení".';
$lang['no_new_attrs_available'] = 'nejsou dostupné ¾ádné nové atributy pro tento objekt';
$lang['no_new_binary_attrs_available'] = 'nejsou dostupné ¾ádné nové binární atributy pro tento objekt';
$lang['binary_value'] = 'Binarní hodnota';
$lang['add_new_binary_attr'] = 'Pøidat nový binarní atribut';
$lang['alias_for'] = 'Poznámka: \'%s\' je aliasem pro \'%s\'';
$lang['download_value'] = 'stáhnout data';
$lang['delete_attribute'] = 'smazat atribut';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = '¾ádný, odebrat hodnotu';
$lang['really_delete_attribute'] = 'Skuteènì smazat atribut';
$lang['add_new_value'] = 'Pøidat novou hodnotu';

// Schema browser
$lang['the_following_objectclasses'] = 'Následující <b>objectClass</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_attributes'] = 'Následující <b>attributeType</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_matching'] = 'Následující <b>kritéria výbìru</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_syntaxes'] = 'Následující <b>syntaxe</b> jsou podporovány tímto LDAP serverem.';
$lang['schema_retrieve_error_1']='Server plnì nepodporuje LDAP protocol.';
$lang['schema_retrieve_error_2']='Va¹e verze PHP korektnì neprovede tento dotaz.';
$lang['schema_retrieve_error_3']='Nebo mo¾ná phpLDAPadmin neví jak získat schéma pro Vá¹ server.';
$lang['jump_to_objectclass'] = 'Jdi na objectClass';
$lang['jump_to_attr'] = 'Jdi na typ atributu';
$lang['jump_to_matching_rule'] = 'Jdi na Matching Rule';
$lang['schema_for_server'] = 'Schéma serveru';
$lang['required_attrs'] = 'Vy¾adované atributy';
$lang['optional_attrs'] = 'Volitelné atributy';
$lang['optional_binary_attrs'] = 'Volitelné binární atributy';
$lang['OID'] = 'OID';
$lang['aliases']='Aliasy';
$lang['desc'] = 'Popis';
$lang['no_description']='¾ádný popis';
$lang['name'] = 'Název';
$lang['equality']='Equality';
$lang['is_obsolete'] = 'Tato objectClass je <b>zastaralá</b>';
$lang['inherits'] = 'Odvozeno od objectClass';
$lang['inherited_from'] = 'Odvozeno od objectClass';
$lang['parent_to'] = 'Rodièovská objectClass';
$lang['jump_to_this_oclass'] = 'Jdi na definici této objectClass';
$lang['matching_rule_oid'] = 'Výbìrové kritérium OID';
$lang['syntax_oid'] = 'Syntaxe OID';
$lang['not_applicable'] = 'nepou¾itelný';
$lang['not_specified'] = 'nespecifikovaný';
$lang['character']='znak'; 
$lang['characters']='znakù';
$lang['used_by_objectclasses']='Pou¾íváno tìmito objectClass';
$lang['used_by_attributes']='Pou¾ívají atributy';
$lang['maximum_length']='Maximální délka';
$lang['attributes']='Typy atributù';
$lang['syntaxes']='Syntaxe';
$lang['matchingrules']='Matching Rules';
$lang['oid']='OID';
$lang['obsolete']='Zastaralé';
$lang['ordering']='Øazení';
$lang['substring_rule']='Substring Rule';
$lang['single_valued']='Single Valued';
$lang['collective']='Collective';
$lang['user_modification']='User Modification';
$lang['usage']='Pou¾ití';
$lang['could_not_retrieve_schema_from']='Nelze získat schéma z';
$lang['type']='Typ';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Objekt \'%s\' byl úspì¹nì odstranìn.';
$lang['you_must_specify_a_dn'] = 'Musíte zadat DN';
$lang['could_not_delete_entry'] = 'Nebylo mo¾né odstranit objekt: %s';
$lang['no_such_entry'] = 'Objekt neexistuje: %s';
$lang['delete_dn'] = 'Smazat %s';
$lang['permanently_delete_children'] = 'Odstranit také v¹echny potomky?';
$lang['entry_is_root_sub_tree'] = 'Tento objekt je koøenem podstromu, který obsahuje %s objektù.';
$lang['view_entries'] = 'zobrazit objekty';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin rekurzivnì odstraní tento objekt a v¹ech jeho %s potomkù. Pozornì si prohlédnìte seznam objektù, které tato operace odstraní. Pøejete si pokraèovat?';
$lang['confirm_recursive_delete_note'] = 'Poznámka: tato operace mù¾e mít fatální následky a nelze ji vrátit zpìt. Speciální pozornost vìnujte aliasùm, odkazùm a ostatním vìcem, které mù¾ou zpùsobit problémy.';
$lang['delete_all_x_objects'] = 'Smazat v¹ech %s objektù';
$lang['recursive_delete_progress'] = 'Prùbìh rekurzivního odstranìní';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Objekt %s a jeho podstrom byly úspì¹nì odstranìny.';
$lang['failed_to_delete_entry'] = 'Nepodaøilo se odstranit objekt %s';
$lang['list_of_entries_to_be_deleted'] = 'Seznam objektù k odstranìní:';
$lang['sure_permanent_delete_object']='Jste si skuteènì jisti, ¾e chcete odstranit tento objekt?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'Atribut "%s" je v konfiguraci phpLDAPadminu oznaèen jako "jen pro ètení".';
$lang['no_attr_specified'] = 'Nebylo zadáno jméno atributu.';
$lang['no_dn_specified'] = 'Nebylo zadáno DN';

// Adding attributes
$lang['left_attr_blank'] = 'Nevyplnili jste hodnotu atributu. Vra»te se zpìt a akci opakujte.';
$lang['failed_to_add_attr'] = 'Pøidání atributu selhalo.';
$lang['file_empty'] = 'Soubor, který jste zvolili je buï prázdný nebo neexistuje. Vra»te se prosím zpìt a akci opakujte.';
$lang['invalid_file'] = 'Bezpeènostní chyba: Soubor, který uploadujete mù¾e být závadný.';
$lang['warning_file_uploads_disabled'] = 'V konfiguraci PHP jsou zakázány uploady souborù. Pro pokraèování upravte prosím php.ini.';
$lang['uploaded_file_too_big'] = 'Soubor, který se pokou¹eli ulo¾it je pøíli¹ veliký. Upravte prosím hodnotu upload_max_size v php.ini.';
$lang['uploaded_file_partial'] = 'Pøi uploadu souboru do¹lo zøejmì k selhání sítì, nebo» se podaøilo získat jen èást souboru.';
$lang['max_file_size'] = 'Maximální velikost souboru: %s';

// Updating values
$lang['modification_successful'] = 'Úprava probìhla úspì¹nì!';
$lang['change_password_new_login'] = 'Kvùli zmìnì svého hesla se nyní musíte pøihlásit znova - s novým heslem.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nový vy¾adovaný atribut';
$lang['requires_to_add'] = 'K provedení této akce musíte pøidat';
$lang['new_attributes'] = 'nové atributy';
$lang['new_required_attrs_instructions'] = 'Návod: K pøiøazení této objectClass k vybranému objektu musíte zadat';
$lang['that_this_oclass_requires'] = 'atributy, které jsou touto objectClass vy¾adovány. Mù¾ete tak uèinit v tomto formuláøi.';
$lang['add_oclass_and_attrs'] = 'Pøidat objectClass a atributy';
$lang['objectclasses'] = 'objectClassy';

// General
$lang['chooser_link_tooltip'] = 'Otevøe popup okno, ve kterém zvolíte DN';
$lang['no_updates_in_read_only_mode'] = 'Nelze provádìt úpravy dokud je server v módu "pouze pro ètení"';
$lang['bad_server_id'] = '©patné ID serveru';
$lang['not_enough_login_info'] = 'Nedostatek informací pro pøihlá¹ení k serveru. Ovìøte prosím nastavení.';
$lang['could_not_connect'] = 'Nelze se pøipojit k LDAP serveru.';
$lang['could_not_connect_to_host_on_port'] = 'Nelze se pøipojit k "%s" na portu "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Nelze provést ldap_mod_add operaci.';
$lang['bad_server_id_underline'] = 'server_id: ';
$lang['success'] = 'Hotovo';
$lang['server_colon_pare'] = 'Server: ';
$lang['look_in'] = 'Prohlí¾ení: ';
$lang['missing_server_id_in_query_string'] = 'V po¾adavku nebylo uvedeno ¾ádné ID serveru!';
$lang['missing_dn_in_query_string'] = 'V po¾adavku nebyl uveden ¾ádný DN!';
$lang['back_up_p'] = 'O úroveò vý¹...';
$lang['no_entries'] = '¾ádné objekty';
$lang['not_logged_in'] = 'Nepøihlá¹en';
$lang['could_not_det_base_dn'] = 'Nelze zjistit výchozí DN';
$lang['please_report_this_as_a_bug']='Nahla¹te toto prosím jako chybu.';
$lang['reasons_for_error']='Toto se mù¾e pøihodit z nìkolika pøíèin. Nejpravdìpodobnìj¹í jsou:';
$lang['yes']='Ano';
$lang['no']='Ne';
$lang['go']='Jdi';
$lang['delete']='Odstranit';
$lang['back']='Zpìt';
$lang['object']='objekt';
$lang['delete_all']='Odstranit v¹e';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'rada';
$lang['bug'] = 'chyba';
$lang['warning'] = 'upozornìní';
$lang['light'] = 'light'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Proveï &gt;&gt;';

// Add value form
$lang['add_new'] = 'Pøidat nový';
$lang['value_to'] = 'hodnota pro';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Souèasný výpis';
$lang['values_for_attribute'] = 'hodnoty pro atribut';
$lang['inappropriate_matching_note'] = 'Poznámka: Pokud nenastavíte na tomto LDAP serveru pravidlo<br /><tt>EQUALITY</tt> pro tento atribut, dojde k chybì pøi výbìru objektù.';
$lang['enter_value_to_add'] = 'Zadejte hodnotu, kterou chcete pøidat:';
$lang['new_required_attrs_note'] = 'Poznámka: Není vylouèené, ¾e budete vyzváni k zadání nových atributù vy¾adovaných touto objectClass';
$lang['syntax'] = 'Syntaxe';

//copy.php
$lang['copy_server_read_only'] = 'Nemù¾ete provádìt zmìny dokud je server v módu "jen pro ètení"';
$lang['copy_dest_dn_blank'] = 'Ponechali jste kolonku cílové DN prázdnou.';
$lang['copy_dest_already_exists'] = 'Objekt (%s) ji¾ v cílovém DN existuje.';
$lang['copy_dest_container_does_not_exist'] = 'Cílová slo¾ka (%s) neexistuje.';
$lang['copy_source_dest_dn_same'] = 'Zdrojové a cílové DN se shodují.';
$lang['copy_copying'] = 'Kopíruji ';
$lang['copy_recursive_copy_progress'] = 'Prùbìh rekurzivního kopírování';
$lang['copy_building_snapshot'] = 'Sestavuji obraz stromu ke kopírování... ';
$lang['copy_successful_like_to'] = 'Kopie úspì¹nì dokonèena! Pøejete si ';
$lang['copy_view_new_entry'] = 'zobrazit nový objekt';
$lang['copy_failed'] = 'Nepodaøilo se okopírovat DN: ';

//edit.php
$lang['missing_template_file'] = 'Upozornìní: chybí ¹ablona, ';
$lang['using_default'] = 'Pou¾ívám výchozí.';
$lang['template'] = '©ablona';
$lang['must_choose_template'] = 'Musíte zvolit ¹ablonu';
$lang['invalid_template'] = '%s je neplatná ¹ablona';
$lang['using_template'] = 'pou¾ítím ¹ablony';
$lang['go_to_dn'] = 'Jdi na %s';

//copy_form.php
$lang['copyf_title_copy'] = 'Kopírovat ';
$lang['copyf_to_new_object'] = 'jako nový objekt';
$lang['copyf_dest_dn'] = 'Cílové DN';
$lang['copyf_dest_dn_tooltip'] = 'Celé DN nového objektu bude vytvoøeno kopií zdrojového objektu';
$lang['copyf_dest_server'] = 'Cílový server';
$lang['copyf_note'] = 'Rada: Kopírování mezi servery funguje jedinì za pøedpokladu, ¾e nedojde k neshodì schémat';
$lang['copyf_recursive_copy'] = 'Pøi kopírování zahrnout v¹echny potomky tohoto objektu.';
$lang['recursive_copy'] = 'Rekurzivní kopie';
$lang['filter'] = 'Filtr';
$lang['filter_tooltip'] = 'Pøi rekurzivní kopii pracovat pouze s objekty, které splòují zvolený filtr';

//create.php
$lang['create_required_attribute'] = 'Nevyplnili jste pole pro vy¾adovaný atribut <b>%s</b>.';
$lang['redirecting'] = 'Pøesmìrovávám';
$lang['here'] = 'zde';
$lang['create_could_not_add'] = 'Nelze objekt do LDAP serveru pøidat.';

//create_form.php
$lang['createf_create_object'] = 'Vytvoøit objekt';
$lang['createf_choose_temp'] = 'Vyberte ¹ablonu';
$lang['createf_select_temp'] = 'Zvolte ¹ablonu pro vytvoøení objektu';
$lang['createf_proceed'] = 'Provést';
$lang['rdn_field_blank'] = 'Ponechali jste pole RDN nevyplnìné.';
$lang['container_does_not_exist'] = 'Slo¾ka (%s) neexistuje. Opakujte prosím akci.';
$lang['no_objectclasses_selected'] = 'Nepøiøadili jste ¾ádné objectClass k tomuto objektu. Vra»te se prosím zpìt a akci opakujte.';
$lang['hint_structural_oclass'] = 'Nápovìda: Musíte zvolit alespoò jednu structural objectClass.';

//creation_template.php
$lang['ctemplate_on_server'] = 'Na serveru';
$lang['ctemplate_no_template'] = 'V POST po¾adavku nebyla zaslána ¾ádná ¹ablona.';
$lang['ctemplate_config_handler'] = 'Va¹e nastavení uvádí obsluhovaè ';
$lang['ctemplate_handler_does_not_exist'] = 'pro tuto ¹ablonu. Ale tento obsluhovaè nelze v adresáøi templates/creation nalézt.';
$lang['create_step1'] = 'Krok 1 ze 2: Jméno a objectClass(y)';
$lang['create_step2'] = 'Krok 2 ze 2: Atributy a hodnoty';
$lang['relative_distinguished_name'] = 'Relativní Distinguished Name';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(pøíklad: cn=MujNovyUzivatel)';
$lang['container'] = 'Slo¾ka';
$lang['alias_for'] = 'Alias k %s';

// search.php
$lang['you_have_not_logged_into_server'] = 'Nelze provádìt vyhledávání na serveru bez pøedchozího pøihlá¹ení.';
$lang['click_to_go_to_login_form'] = 'Klepnutím budete pøesmìrováni na formuláø k pøihlá¹ení';
$lang['unrecognized_criteria_option'] = 'Neznámá vyhledávací kritéria: ';
$lang['if_you_want_to_add_criteria'] = 'Pokud si pøejete pøidat svoje vlastní vyhledávací kritéria, ujistìte se, ¾e jste je pøidali do search.php.';
$lang['entries_found'] = 'Nalezené objekty: ';
$lang['filter_performed'] = 'Uplatnìný filtr: ';
$lang['search_duration'] = 'Vyhledávání dokonèeno za';
$lang['seconds'] = 'sekund';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Oblast vyhledávání';
$lang['scope_sub'] = 'Celý podstrom';
$lang['scope_one'] = 'O jednu úroveò ní¾';
$lang['scope_base'] = 'Pouze výchozí DN';
$lang['standard_ldap_search_filter'] = 'Standardní LDAP vyhledávací filtr. Pøiklad: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Vyhledávací filtr';
$lang['list_of_attrs_to_display_in_results'] = 'Seznam atributù zobrazených ve výsledku hledání (oddìlené èárkou)';
$lang['show_attributes'] = 'Zobrazit atributy';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Vyhledat objekty kde';
$lang['equals'] = 'je';
$lang['starts with'] = 'zaèíná na';
$lang['contains'] = 'obsahuje';
$lang['ends with'] = 'konèí na';
$lang['sounds like'] = 'zní jako';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Nelze získat informace ze serveru LDAP';
$lang['server_info_for'] = 'Server info pro: ';
$lang['server_reports_following'] = 'Server o sobì poskytuje následující informace';
$lang['nothing_to_report'] = 'Server neposkytuje ¾ádné informace.';

//update.php
$lang['update_array_malformed'] = 'update_array je po¹kozené. Mù¾e se jednat o chybu v phpLDAPadmin. Prosíme Vás, abyste chybu nahlásili.';
$lang['could_not_perform_ldap_modify'] = 'Nelze provést operaci ldap_modify.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Pøejete si provést tyto zmìny?';
$lang['attribute'] = 'Atribut';
$lang['old_value'] = 'Pùvodní hodnota';
$lang['new_value'] = 'Nová hodnota';
$lang['attr_deleted'] = '[atribut odstranìn]';
$lang['commit'] = 'Odeslat';
$lang['cancel'] = 'Storno';
$lang['you_made_no_changes'] = 'Neprovedli jste ¾ádné zmìny';
$lang['go_back'] = 'Zpìt';

// welcome.php
$lang['welcome_note'] = 'K navigaci pou¾ijte prosím menu v levé èásti obrazovky';
$lang['credits'] = 'Autoøi';
$lang['changelog'] = 'ChangeLog';
$lang['donate'] = 'Podpoøit projekt';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nebezpeèný název souboru: ';
$lang['no_such_file'] = 'Soubor nelze nalézt: ';

//function.php
$lang['auto_update_not_setup'] = 'V konfiguraci jste zapnuli podporu auto_uid_numbers pro <b>%s</b>, ale nespecifikovali jste auto_uid_number_mechanism. Napravte prosím nejprve tento problém.';
$lang['uidpool_not_set'] = 'V konfiguraci serveru <b>%s</b> jste specifikovali <tt>auto_uid_number_mechanism</tt> jako <tt>uidpool</tt>, ale neuvedli jste audo_uid_number_uid_pool_dn. Napravte prosím nejprve tento problém.';
$lang['uidpool_not_exist'] = 'Zdá se, ¾e uidPool uvedený v konfiguraci (<tt>%s</tt>) neexistuje.';
$lang['specified_uidpool'] = 'V konfiguraci serveru <b>%s</b> jste specifikovali <tt>auto_uid_number_mechanism</tt> jako <tt>search</tt>, ale neuvedli jste <tt>auto_uid_number_search_base</tt>. Napravte prosím nejprve tento problém.';
$lang['auto_uid_invalid_credential'] = 'Se zadanými pøístupovými oprávnìními se nelze pøipojit k <b>%s</b> a získat auto_uid. Zkontrolujte prosím konfiguraci.'; 
$lang['bad_auto_uid_search_base'] = 'V konfiguraci phpLDAPadminu je uveden neplatný parametr auto_uid_search_base pro server %s';
$lang['auto_uid_invalid_value'] = 'V konfiguraci je uvedena neplatná hodnota auto_uid_number_mechanism (<tt>%s</tt>). Platné hodnoty jsou pouze <tt>uidpool</tt> a <tt>search</tt>. Napravte prosím nejprve tento problém.';
$lang['error_auth_type_config'] = 'Chyba: Ve svém konfiguraèním souboru jste u polo¾ky $servers[\'auth_type\'] uvedli chybnou hodnotu \'%s\'. Platné hodnoty jsou pouze \'config\' a \'form\'.';
$lang['php_install_not_supports_tls'] = 'Tato instalace PHP neobsahuje podporu pro TLS';
$lang['could_not_start_tls'] = 'Nelze inicializovat TLS.<br />Zkontolujte prosím konfiguraci svého LDAP serveru.';
$lang['could_not_bind_anon'] = 'K serveru se nelze pøipojit anonymnì.';
$lang['could_not_bind'] = 'Nelze se pøipojit k serveru LDAP.';
$lang['anonymous_bind'] = 'Pøipojit anonymnì';
$lang['bad_user_name_or_password'] = 'Nesprávné jméno nebo heslo. Opakujte pøihlá¹ení.';
$lang['redirecting_click_if_nothing_happens'] = 'Pøesmìrovávám... Klepnìte sem, pokud se nic nestane.';
$lang['successfully_logged_in_to_server'] = 'Úspì¹nì jste se pøihlásili k serveru <b>%s</b>';
$lang['could_not_set_cookie'] = 'Cookie nemohla být ulo¾ena.';
$lang['ldap_said'] = '<b>Odpovìï LDAP serveru</b>: %s<br /><br />';
$lang['ferror_error'] = 'Chyba';
$lang['fbrowse'] = 'procházet';
$lang['delete_photo'] = 'Odstranit fotografii';
$lang['install_not_support_blowfish'] = 'Tato instalace PHP neobsahuje podporu pro ¹ifru Blowfish.';
$lang['install_not_support_md5crypt'] = 'Tato instalace PHP neobsahuje podporu pro ¹ifru md5crypt.';
$lang['install_no_mash'] = 'Tato instalace PHP nepodporuje funkci mhash(). Nelze aplikovat SHA hash.';
$lang['jpeg_contains_errors'] = 'jpegPhoto obsahuje chyby<br />';
$lang['ferror_number'] = '<b>Èíslo chyby</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] = '<b>Popis</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Èíslo chyby</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Popis</b>: (popis není k dispozici)<br />';
$lang['ferror_submit_bug'] = 'Pokud je toto chyba v phpLDAPadmin, <a href=\'%s\'>napi¹te nám</a> o tom.';
$lang['ferror_unrecognized_num'] = 'Neznámé èíslo chyby: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Narazili jste na nezáva¾nou, droubnou a¾ zanedbatelnou chybu v phpLDAPadmin!</b></td></tr><tr><td>Chyba:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Soubor:</td>
             <td><b>%s</b> øádka <b>%s</b>, voláno z <b>%s</b></td></tr><tr><td>Verze:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Klepnutím prosím ohlá¹te chybu</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Blahopøejeme! Nalezli jste chybu v phpLDAPadmin. :-)<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Chyba:</td><td><b>%s</b></td></tr>
	     <tr><td>Vá¾nost:</td><td><b>%s</b></td></tr>
	     <tr><td>Soubor:</td><td><b>%s</b></td></tr>
	     <tr><td>Øádka:</td><td><b>%s</b></td></tr>
	     <tr><td>Voláno z:</td><td><b>%s</b></td></tr>
	     <tr><td>Verze PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Verze PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web server:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Klepnutím dole prosím ohla¹te chybu!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importovat soubor LDIF';
$lang['select_ldif_file'] = 'Zvolte soubor LDIF:';
$lang['select_ldif_file_proceed'] = 'Proveï &gt;&gt;';
$lang['dont_stop_on_errors'] = 'Ignorovat chyby';

//ldif_import
$lang['add_action'] = 'Pøidávání...';
$lang['delete_action'] = 'Odstraòování...';
$lang['rename_action'] = 'Pøejmenovávání...';
$lang['modify_action'] = 'Upravování...';
$lang['warning_no_ldif_version_found'] = 'Nebyla nalezena verze. Pøedpokládám 1.';
$lang['valid_dn_line_required'] = 'Je vy¾adován platný øádek s DN.';
$lang['missing_uploaded_file'] = 'Soubor LDIF nebyl nalezen.';
$lang['no_ldif_file_specified.'] = 'Neuvedli jste LDIF soubor. Opakujte prosím akci.';
$lang['ldif_file_empty'] = 'Soubor LDIF je prázdný.';
$lang['empty'] = 'prázdný';
$lang['file'] = 'Soubor';
$lang['number_bytes'] = '%s bajtù';

$lang['failed'] = 'selhal';
$lang['ldif_parse_error'] = 'Chyba v souboru LDIF';
$lang['ldif_could_not_add_object'] = 'Nelze pøidat objekt:';
$lang['ldif_could_not_rename_object'] = 'Nelze pøejmenovat objekt:';
$lang['ldif_could_not_delete_object'] = 'Nelze odstranit objekt:';
$lang['ldif_could_not_modify_object'] = 'Nelze upravit objekt:';
$lang['ldif_line_number'] = 'Èíslo øádku:';
$lang['ldif_line'] = 'Øádek:';

// Exports
$lang['export_format'] = 'Formát exportu';
$lang['line_ends'] = 'Konce øádkù';
$lang['must_choose_export_format'] = 'Musíte zvolit exportní formát.';
$lang['invalid_export_format'] = 'Neplatný exportní formát';
$lang['no_exporter_found'] = 'Nebyla nalezena ¾ádná aplikace pro export.';
$lang['error_performing_search'] = 'Bìhem vyhledávání do¹lo k chybì.';
$lang['showing_results_x_through_y'] = 'Výsledky od %s do %s.';
$lang['searching'] = 'Vyhledávám...';
$lang['size_limit_exceeded'] = 'Byl pøekroèen limitní parametr pro vyhledávání.';
$lang['entry'] = 'Objekt';
$lang['ldif_export_for_dn'] = 'LDIF Export objektu: %s';
$lang['generated_on_date'] = 'Generováno phpLDAPadminem dne %s';
$lang['total_entries'] = 'Celkem objektù';
$lang['dsml_export_for_dn'] = 'DSLM Export objektu: %s';

// logins
$lang['could_not_find_user'] = 'Nelze nalézt u¾ivatele "%s"';
$lang['password_blank'] = 'Nezadali jste u¾ivatelské heslo.';
$lang['login_cancelled'] = 'Pøihla¹ování zru¹eno.';
$lang['no_one_logged_in'] = 'Nikdo není pøihlá¹en k tomuto serveru.';
$lang['could_not_logout'] = 'Nelze se odhlásit.';
$lang['unknown_auth_type'] = 'Neznámý auth_type: %s';
$lang['logged_out_successfully'] = 'Odhlá¹ení od serveru <b>%s</b> probìhlo úspì¹nì.';
$lang['authenticate_to_server'] = 'Ovìøení vùèi serveru %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Upozornìní: Toto spojení není ¹ifrováné.';
$lang['not_using_https'] = 'Nepøipojujete se pomocí \'https\'. Prohlí¾eè ode¹le Va¹e pøihlá¹ení v ne¹ifrované podobì.';
$lang['login_dn'] = 'Pøihla¹ovací DN';
$lang['user_name'] = 'U¾ivatel';
$lang['password'] = 'Heslo';
$lang['authenticate'] = 'Pøihlásit';

// Entry browser
$lang['entry_chooser_title'] = 'Zvolit objekt';

// Index page
$lang['need_to_configure'] = 'Nejprve je tøeba phpLDAPadmin nakonfigurovat. Toho docílíte upravou souboru \'config.php\'. Ukázková konfigurace je k nalezení v souboru \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Mazání je v re¾imu "jen pro ètení" zakázáno.';
$lang['error_calling_mass_delete'] = 'Chyba pøi volání mass_delete.php. V POSTu chybí promìnná mass_delete.';
$lang['mass_delete_not_array'] = 'mass_delete v POSTu není pole.';
$lang['mass_delete_not_enabled'] = 'Hromadý výmaz není umo¾nìn. Mù¾ete ho povolit v souboru config.php.';
$lang['mass_deleting'] = 'Hromadný výmaz';
$lang['mass_delete_progress'] = 'Prùbìh odstraòování na serveru "%s"';
$lang['malformed_mass_delete_array'] = 'Zdeformované pole mass_delete.';
$lang['no_entries_to_delete'] = 'Nevybrali jste ¾ádné objekty k odstranìní.';
$lang['deleting_dn'] = 'Odstraòuji %s';
$lang['total_entries_failed'] = '%s z %s objektù se nepodaøilo odstranit.';
$lang['all_entries_successful'] = 'V¹echny objekty byly úspì¹nì odstranìny.';
$lang['confirm_mass_delete'] = 'Potvïte hromadný výmaz v poètu %s objektù na serveru %s';
$lang['yes_delete'] = 'Ano, odstranit!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Nelze pøejmenovat objekt, který má potomky. Toto se napøíklad vztahuje na v¹echny objekty, které nejsou typu "list".';
$lang['no_rdn_change'] = 'Nezmìnili jste RDN';
$lang['invalid_rdn'] = 'Neplatná hodnota RDN';
$lang['could_not_rename'] = 'Objekt nelze pøejmenovat';

?>
