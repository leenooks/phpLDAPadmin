<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/cz.php,v 1.1 2004/05/04 19:09:34 i18phpldapadmin Exp $
/**
 * Translated to Czech by Radek Senfeld

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
$lang['search_ filter'] = 'Filtr';
$lang['show_attributes'] = 'Zobrazovat atributy';
$lang['Search'] = 'Vyhledat';
$lang['equals'] = 'je';
$lang['starts_with'] = 'začíná na';
$lang['contains'] = 'obsahuje';
$lang['ends_with'] = 'končí na';
$lang['sounds_like'] = 'zní jako';

// Tree browser
$lang['request_new_feature'] = 'Napište si o novou funkci';
$lang['see_open_requests'] = 'zobrazit seznam požadavků';
$lang['report_bug'] = 'Nahlásit chybu';
$lang['see_open_bugs'] = 'zobrazit seznam chyb';
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
$lang['view_server_info'] = 'Zobrazit serverem poskytované informace';
$lang['import_from_ldif'] = 'Importovat data ze souboru LDIF';
$lang['logout_of_this_server'] = 'Odhlásit se od tohoto serveru';
$lang['logged_in_as'] = 'Přihlášen jako: ';
$lang['read_only'] = 'jen pro čtení';
$lang['could_not_determine_root'] = 'Nepodařilo se zjistit kořen Vašeho LDAP stromu.';
$lang['ldap_refuses_to_give_root'] = 'Zdá se, že LDAP server je nastavený tak, že nezobrazuje svůj kořen.';
$lang['please_specify_in_config'] = 'Nastavte ho prosím v souboru config.php';
$lang['create_new_entry_in'] = 'Vytvořit nový objekt v';
$lang['login_link'] = 'Přihlásit se...';

// Entry display
$lang['delete_this_entry'] = 'Smazat tento objekt';
$lang['delete_this_entry_tooltip'] = 'Budete požádáni o potvrzení tohoto rozhodnutí';
$lang['copy_this_entry'] = 'Kopírovat tento objekt';
$lang['copy_this_entry_tooltip'] = 'Okopíruje tento objekt do jiného umístění, nového DN, nebo na jiný server';
$lang['export_to_ldif'] = 'Exportovat do LDIF';
$lang['export_to_ldif_tooltip'] = 'Uložit LDIF přepis tohoto objektu';
$lang['export_subtree_to_ldif_tooltip'] = 'Uloží LDIF přepis tohoto objektu a všech jeho potomků';
$lang['export_subtree_to_ldif'] = 'Exportovat podstrom do LDIF';
$lang['export_to_ldif_mac'] = 'Macintosh styl odřádkování';
$lang['export_to_ldif_win'] = 'Windows styl odřádkování';
$lang['export_to_ldif_unix'] = 'Unix styl odřádkování';
$lang['create_a_child_entry'] = 'Vytvořit nového potomka';
$lang['add_a_jpeg_photo'] = 'Přidat jpegPhoto';
$lang['rename_entry'] = 'Přejmenovat objekt';
$lang['rename'] = 'Přejmenovat';
$lang['add'] = 'Přidat';
$lang['view'] = 'Zobrazit';
$lang['add_new_attribute'] = 'Přidat nový atribut';
$lang['add_new_attribute_tooltip'] = 'Přidá nový atribut/hodnotu tomuto objektu';
$lang['internal_attributes'] = 'Interní atributy';
$lang['hide_internal_attrs'] = 'Schovat interní atributy';
$lang['show_internal_attrs'] = 'Zobrazit interní atributy';
$lang['internal_attrs_tooltip'] = 'Atributy nastavené systémem automaticky';
$lang['entry_attributes'] = 'Seznam atributů';
$lang['attr_name_tooltip'] = 'Klepnutím zobrazíte definiční schéma pro atribut typu \'%s\'';
$lang['click_to_display'] = 'pro zobrazení klepněte na \'+\'';
$lang['hidden'] = 'skrytý';
$lang['none'] = 'žádný';
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
$lang['change_entry_rdn'] = 'Změnit RDN tohoto objektu';
$lang['no_new_attrs_available'] = 'nejsou dostupné žádné nové atributy pro tento objekt';
$lang['binary_value'] = 'Binarní hodnota';
$lang['add_new_binary_attr'] = 'Přidat nový binarní atribut';
$lang['add_new_binary_attr_tooltip'] = 'Přidat nový binarní atribut/hodnotu ze souboru';
$lang['alias_for'] = 'Poznámka: \'%s\' je aliasem pro \'%s\'';
$lang['download_value'] = 'stáhnout data';
$lang['delete_attribute'] = 'smazat atribut';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = 'žádný, odebrat hodnotu';
$lang['really_delete_attribute'] = 'Skutečně smazat atribut';

// Schema browser
$lang['the_following_objectclasses'] = 'Následující <b>objectClass</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_attributes'] = 'Následující <b>attributeType</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_matching'] = 'Následující <b>kritéria výběru</b> jsou podporovány tímto LDAP serverem.';
$lang['the_following_syntaxes'] = 'Následující <b>syntaxe</b> jsou podporovány tímto LDAP serverem.';
$lang['jump_to_objectclass'] = 'Jdi na objectClass';
$lang['jump_to_attr'] = 'Jdi na typ atributu';
$lang['schema_for_server'] = 'Schéma serveru';
$lang['required_attrs'] = 'Vyžadované atributy';
$lang['optional_attrs'] = 'Volitelné atributy';
$lang['OID'] = 'OID';
$lang['desc'] = 'Popis';
$lang['name'] = 'Název';
$lang['is_obsolete'] = 'Tato objectClass je <b>zastaralá</b>';
$lang['inherits'] = 'Dědí';
$lang['jump_to_this_oclass'] = 'Jdi na definici této objectClass';
$lang['matching_rule_oid'] = 'Výběrové kritérium OID';
$lang['syntax_oid'] = 'Syntaxe OID';
$lang['not_applicable'] = 'nepoužitelný';
$lang['not_specified'] = 'nespecifikovaný';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Objekt \'%s\' byl úspěšně odstraněn.';
$lang['you_must_specify_a_dn'] = 'Musíte zadat DN';
$lang['could_not_delete_entry'] = 'Nebylo možné odstranit objekt: %s';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nový vyžadovaný atribut';
$lang['requires_to_add'] = 'K provedení této akce musíte přidat';
$lang['new_attributes'] = 'nové atributy';
$lang['new_required_attrs_instructions'] = 'Návod: K přiřazení této objectClass k vybranému objektu musíte zadat';
$lang['that_this_oclass_requires'] = 'atributy, které jsou touto objectClass vyžadovány. Můžete tak učinit v tomto formuláři.';
$lang['add_oclass_and_attrs'] = 'Přidat objectClass a atributy';

// General
$lang['chooser_link_tooltip'] = 'Klepněte pro popup okno, ve kterém zvolíte DN';
$lang['no_updates_in_read_only_mode'] = 'Nelze provádět úpravy dokud je server v módu "pouze pro čtení"';
$lang['bad_server_id'] = 'Špatné ID serveru';
$lang['not_enough_login_info'] = 'Nedostatek informací pro přihlášení k serveru. Ověřte prosím nastavení.';
$lang['could_not_connect'] = 'Nelze se připojit k LDAP serveru.';
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

// Add value form
$lang['add_new'] = 'Přidat nový';
$lang['value_to'] = 'hodnota pro';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Současný výpis';
$lang['values_for_attribute'] = 'hodnoty pro atribut';
$lang['inappropriate_matching_note'] = 'Poznámka: Pokud nenastavíte na tomto LDAP serveru pravidlo<br />'.
			'<tt>EQUALITY</tt> pro tento atribut, dojde k chybě při výběru objektů.';
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

//copy_form.php
$lang['copyf_title_copy'] = 'Kopírovat ';
$lang['copyf_to_new_object'] = 'jako nový objekt';
$lang['copyf_dest_dn'] = 'Cílové DN';
$lang['copyf_dest_dn_tooltip'] = 'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'Cílový server';
$lang['copyf_note'] = 'Rada: Kopírování mezi servery funguje jedině za předpokladu, že nedojde k neshodě schémat';
$lang['copyf_recursive_copy'] = 'Při kopírování zahrnout všechny potomky tohoto objektu.';

//create.php
$lang['create_required_attribute'] = 'Nevyplnili jste pole pro vyžadovaný atribut <b>%s</b>.';
$lang['create_redirecting'] = 'Přesměrovávám';
$lang['create_here'] = 'zde';
$lang['create_could_not_add'] = 'Nelze objekt do LDAP serveru přidat.';

//create_form.php
$lang['createf_create_object'] = 'Vytvořit objekt';
$lang['createf_choose_temp'] = 'Vyberte šablonu';
$lang['createf_select_temp'] = 'Zvolte šablonu pro vytvoření objektu';
$lang['createf_proceed'] = 'Provést';

//creation_template.php
$lang['ctemplate_on_server'] = 'Na serveru';
$lang['ctemplate_no_template'] = 'V POST požadavku nebyla zaslána žádná šablona.';
$lang['ctemplate_config_handler'] = 'Vaše nastavení uvádí obsluhovač ';
$lang['ctemplate_handler_does_not_exist'] = 'pro tuto šablonu. Ale tento obsluhovač nelze v adresáři templates/creation nalézt.';

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
$lang['could_not_fetch_server_info'] = 'Nelze získat informace z LDAP serveru';
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
$lang['welcome_note'] = 'K navigaci použijte prosím menu na levé straně obrazovky';
$lang['credits'] = 'Autoři';
$lang['changelog'] = 'ChangeLog';
$lang['documentation'] = 'Dokumentace';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nebezpečný název souboru: ';
$lang['no_such_file'] = 'Soubor nelze nalézt: ';

//function.php
$lang['auto_update_not_setup'] = 'V konfiguraci jste zapnuli podporu auto_uid_numbers pro <b>%s</b>, ale nespecifikovali jste auto_uid_number_mechanism. Napravte prosím nejprve tento problém.';
$lang['uidpool_not_set'] = 'V konfiguraci serveru <b>%s</b> jste specifikovali <tt>auto_uid_number_mechanism</tt> jako <tt>uidpool</tt>, ale neuvedli jste audo_uid_number_uid_pool_dn. Napravte prosím nejprve tento problém.';
$lang['uidpool_not_exist'] = 'Zdá se, že uidPool, uvedený v konfiguraci (<tt>%s</tt>) neexistuje.';
$lang['specified_uidpool'] = 'V konfiguraci serveru <b>%s</b> jste specifikovali <tt>auto_uid_number_mechanism</tt> jako <tt>search</tt>, ale neuvedli jste <tt>auto_uid_number_search_base</tt>. Napravte prosím nejprve tento problém.';
$lang['auto_uid_invalid_value'] = 'V konfiguraci je uvedena neplatná hodnota auto_uid_number_mechanism (<tt>%s</tt>). Platné hodnoty jsou pouze <tt>uidpool</tt> a <tt>search</tt>. Napravte prosím nejprve tento problém.';
$lang['error_auth_type_config'] = 'Chyba: Ve svém konfiguračním souboru jste u položky $servers[\'auth_type\'] uvedli chybnou hodnotu \'%s\'. Platné hodnoty jsou pouze \'config\' a \'form\'.';
$lang['php_install_not_supports_tls'] = 'Tato instalace PHP neobsahuje podporu pro TLS';
$lang['could_not_start_tls'] = 'Nelze inicializovat TLS.<br />Zkontolujte prosím konfiguraci svého LDAP serveru.';
$lang['auth_type_not_valid'] = 'V konfigurační souboru byla nalezena chyba. Hodnota \'%s\' není pro parametr auth_type přípustná.';
$lang['ldap_said'] = '<b>Odpověď LDAP serveru</b>: %s<br /><br />';
$lang['ferror_error'] = 'Chyba';
$lang['fbrowse'] = 'procházet';
$lang['delete_photo'] = 'Odstranit fotografii';
$lang['install_not_support_blowfish'] = 'Tato instalace PHP neobsahuje podporu pro Blowfish.';
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

//ldif_import
$lang['add_action'] = 'Přidávání...';
$lang['delete_action'] = 'Odstraňování...';
$lang['rename_action'] = 'Přejmenovávání...';
$lang['modify_action'] = 'Upravování...';

$lang['failed'] = 'selhal';
$lang['ldif_parse_error'] = 'Chyba v souboru LDIF';
$lang['ldif_could_not_add_object'] = 'Nelze přidat objekt:';
$lang['ldif_could_not_rename_object'] = 'Nelze přejmenovat objekt:';
$lang['ldif_could_not_delete_object'] = 'Nelze odstranit objekt:';
$lang['ldif_could_not_modify_object'] = 'Nelze upravit objekt:';
$lang['ldif_line_number'] = 'Číslo řádku:';
$lang['ldif_line'] = 'Řádek:';
?>
