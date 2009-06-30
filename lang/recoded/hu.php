<?php
/*
 * hu.php, based on en.php CVS-Version 1.65
 * hungarian translation by VOROSBARANYI Zoltan <http://vbzo.li/>
 *                       with help from SIPOS Agnes <sa exfructu net>
 * $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/hu.php,v 1.1 2004/05/23 21:12:04 i18phpldapadmin Exp $
 */

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
$lang['simple_search_form_str'] = 'Egyszerű keresési űrlap'; // 'Simple Search Form';
$lang['advanced_search_form_str'] = 'Részletes keresési űrlap'; //'Advanced Search Form';
$lang['server'] = 'Kiszolgáló'; //'Server';
$lang['search_for_entries_whose'] = 'Bejegyzések keresése ahol'; //'Search for entries whose';
$lang['base_dn'] = 'Alap-DN'; //'Base DN';
$lang['search_scope'] = 'A keresés hatásköre'; //'Search Scope';
$lang['show_attributes'] = 'Megjelenítendő attribútumok'; //'Show Attributtes';
$lang['Search'] = 'Keresés'; //'Search';
$lang['predefined_search_str'] = 'Előre definiált keresés kiválasztása'; //'Select a predefined search';
$lang['predefined_searches'] = 'Előre definiált keresések'; //'Predefined Searches';
$lang['no_predefined_queries'] = 'Nincs keresés definiálva a config.php-ben.'; //'No queries have been defined in config.php.';

// Tree browser
$lang['request_new_feature'] = 'Új tulajdonság kérése'; //'Request a new feature';
$lang['report_bug'] = 'Hiba jelentése'; //'Report a bug';
$lang['schema'] = 'séma'; //'schema';
$lang['search'] = 'keresés'; //'search';
$lang['create'] = 'létrehozás'; //'create';
$lang['info'] = 'infó'; //'info';
$lang['import'] = 'import'; //'import';
$lang['refresh'] = 'frissítés'; //'refresh';
$lang['logout'] = 'kilépés'; //'logout';
$lang['create_new'] = 'Új bejegyzés'; //'Create New';
$lang['view_schema_for'] = 'Séma megtekintése:'; //'View schema for';
$lang['refresh_expanded_containers'] = 'Az összes kiterjesztett tároló frissítése:'; //'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'Új bejegyzés létrehozása:'; //'Create a new entry on';
$lang['new'] = 'új'; //'new';
$lang['view_server_info'] = 'A kiszolgáló információinak megtekintése'; //'View server-supplied information';
$lang['import_from_ldif'] = 'LDIF-állományból bejegyzések importálása'; //'Import entries from an LDIF file';
$lang['logout_of_this_server'] = 'Kilépés ebből a kiszolgálóból'; //'Logout of this server';
$lang['logged_in_as'] = 'Belépve mint'; //'Logged in as: ';
$lang['read_only'] = 'csak olvasható'; //'read only';
$lang['read_only_tooltip'] = 'A phpLDAPadmin adminisztrátora ezt az attribútumot csak olvashatóra állította'; //'This attribute has been flagged as read only by the phpLDAPadmin administrator';
$lang['could_not_determine_root'] = 'Nem tudom megállapítani az LDAP-fa gyökerét.'; //'Could not determine the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'Az LDAP-kiszolgálót úgy konfigurálták, hogy ne fedje föl az LDAP-fa gyökerét.'; //'It appears that the LDAP server has been configured to not reveal its root.';
$lang['please_specify_in_config'] = 'Kérem adja meg a config.php-ban'; //'Please specify it in config.php';
$lang['create_new_entry_in'] = 'Új bejegyzés létrehozása:'; //'Create a new entry in';
$lang['login_link'] = 'Belépés...'; //'Login...';
$lang['login'] = 'belépés'; //'login';

// Entry display
$lang['delete_this_entry'] = 'A bejegyzés törlése'; //'Delete this entry';
$lang['delete_this_entry_tooltip'] = 'Ezt a döntést majd még meg kell erősíteni'; //'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'A bejegyzés másolása'; //'Copy this entry';
$lang['copy_this_entry_tooltip'] = 'Az objektum másolása más helyre új DN-nel és/vagy másik kiszolgálóra'; //'Copy this object to another location, a new DN, or another server';
$lang['export'] = 'Exportálás'; //'Export';
$lang['export_tooltip'] = 'Az objektum kiírása'; //'Save a dump of this object';
$lang['export_subtree_tooltip'] = 'Az objektum és az összes gyermekének kiírása'; //'Save a dump of this object and all of its children';
$lang['export_subtree'] = 'Részfa exportálása'; //'Export subtree';
$lang['create_a_child_entry'] = 'Gyermekbejegyzés létrehozása'; //'Create a child entry';
$lang['rename_entry'] = 'A bejegyzés átnevezése'; 'Rename Entry';
$lang['rename'] = 'Átnevezés'; //'Rename';
$lang['add'] = 'Hozzáadás'; //'Add';
$lang['view'] = 'Megtekintés'; //'View';
$lang['view_one_child'] = 'A gyermek megtekintése (1 darab)'; //'View 1 child';
$lang['view_children'] = 'A gyermekek megtekintése (%s darab)'; //'View %s children';
$lang['add_new_attribute'] = 'Új attribútum hozzáadása'; //'Add new attribute';
$lang['add_new_objectclass'] = 'Új objektumosztály hozzáadása'; //'Add new ObjectClass';
$lang['hide_internal_attrs'] = 'Belső attribútumok elrejtése'; //'Hide internal attributes';
$lang['show_internal_attrs'] = 'Belső attribútumok megjelenítése'; //'Show internal attributes';
$lang['attr_name_tooltip'] = 'Kattintással megjelenik a(z) %s attribútumtípus sémabeli definíciója'; //'Click to view the schema defintion for attribute type \'%s\'';
$lang['none'] = 'nincs'; //'none';
$lang['no_internal_attributes'] = 'Nincs belső attribútum'; //'No internal attributes';
$lang['no_attributes'] = 'A bejegyzésnek nincs attribútuma'; //'This entry has no attributes';
$lang['save_changes'] = 'Változások mentése'; //'Save Changes';
$lang['add_value'] = 'érték hozzáadása'; //'add value';
$lang['add_value_tooltip'] = 'Érték hozzáadása a(z) %s attribútumhoz'; //'Add an additional value to attribute \'%s\'';
$lang['refresh_entry'] = 'Frissítés'; //'Refresh';
$lang['refresh_this_entry'] = 'Bejegyzés frissítése'; //'Refresh this entry';
$lang['delete_hint'] = 'Tipp: Az attribútum törléséhez előbb törölje a mezőt, majd kattintson a változtatások mentésére.'; //'Hint: To delete an attribute, empty the text field and click save.';
$lang['attr_schema_hint'] = 'Tipp: Az attribútum sémájának megtekintéséhez kattintson az attribútum nevére.'; //'Hint: To view the schema for an attribute, click the attribute name.';
$lang['attrs_modified'] = 'Módosult néhány attribútum (%s), s ezek kiemelve szerepelnek az alábbiakban.'; //'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'Módosult egy attribútum (%s), s ez kiemelve szerepel az alábbiakban. '; //'An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'A bejegyzés megtekintése csak olvasható üzemmódban.'; //'Viewing entry in read-only mode.';
$lang['no_new_attrs_available'] = 'a bejegyzéshez nem tartozik új attribútum'; //'no new attributes available for this entry';
$lang['no_new_binary_attrs_available'] = 'a bejegyzéshez nem tartozik új bináris attribútum'; //'no new binary attributes available for this entry';
$lang['binary_value'] = 'Bináris érték'; //'Binary value';
$lang['add_new_binary_attr'] = 'Új bináris attribútum hozzáadása'; //'Add new binary attribute';
$lang['alias_for'] = 'Megj.: A(z) %s egy álneve (aliasa) a(z) %s attribútumnak'; //'Note: \'%s\' is an alias for \'%s\'';
$lang['download_value'] = 'érték letöltése'; //'download value';
$lang['delete_attribute'] = 'attribútum törlése'; //'delete attribute';
$lang['true'] = 'igaz'; //'true';
$lang['false'] = 'hamis'; //'false';
$lang['none_remove_value'] = 'nincs, érték törése'; //'none, remove value';
$lang['really_delete_attribute'] = 'Attribútum tényleges törlése'; //'Really delete attribute';
$lang['add_new_value'] = 'Új érték hozzáadása'; //'Add New Value';

// Schema browser
$lang['the_following_objectclasses'] = 'A következő objektumosztályokat (objectClass) támogatja ez a kiszolgáló.'; //'The following objectClasses are supported by this LDAP server.';
$lang['the_following_attributes'] = 'A következő attribútumtípusokat (attributeType) támogatja ez a kiszolgáló.'; //'The following attributeTypes are supported by this LDAP server.';
$lang['the_following_matching'] = 'A következő illesztőszabályokat (matching rule) támogatja ez a kiszolgáló.'; //'The following matching rules are supported by this LDAP server.';
$lang['the_following_syntaxes'] = 'A következő szintaxisokat (syntax) támogatja ez a kiszolgáló.'; //'The following syntaxes are supported by this LDAP server.';
$lang['schema_retrieve_error_1']= 'A kiszolgáló nem beszéli elég jól az LDAP-protokollt'; //'The server does not fully support the LDAP protocol.';
$lang['schema_retrieve_error_2']= 'Ez a PHP-verzió nem tudja szabályosan végrehajtani a keresést.'; //'Your version of PHP does not correctly perform the query.';
$lang['schema_retrieve_error_3']= 'Vagy végül is a phpLDAPadmin nem tudja hogyan kell a sémát letölteni erről a kiszolgálóról.'; //'Or lastly, phpLDAPadmin doesn\'t know how to fetch the schema for your server.';
$lang['jump_to_objectclass'] = 'Ugrás az objektumosztályhoz'; //'Jump to an objectClass';
$lang['jump_to_attr'] = 'Ugrás az attribútumtípushoz'; //'Jump to an attribute type';
$lang['jump_to_matching_rule'] = 'Ugrás az illesztőszabályhoz'; //'Jump to a matching rule';
$lang['schema_for_server'] = 'Séma:'; //'Schema for server';
$lang['required_attrs'] = 'Kötelező attribútumok'; //'Required Attributes';
$lang['optional_attrs'] = 'Opcionális attribútumok'; //'Optional Attributes';
$lang['optional_binary_attrs'] = 'Opcionális bináris attribútumok'; //'Optional Binary Attributes';
$lang['OID'] = 'OID'; //'OID';
$lang['aliases']='Álnevek (alias)'; //'Aliases';
$lang['desc'] = 'Leírás'; //'Description';
$lang['no_description'] = 'nincs leírás'; //'no description';
$lang['name'] = 'Név'; //'Name';
$lang['equality']='Egyenlőség'; //'Equality';
$lang['is_obsolete'] = 'Ez az objektumosztály maradi'; //'This objectClass is obsolete.';
$lang['inherits'] = 'Kitől öröklődik'; //'Inherits from';
$lang['inherited_from'] = 'Kitől örökölte:'; //'Inherited from';
$lang['parent_to'] = 'Kinek a szülője'; //'Parent to';
$lang['jump_to_this_oclass'] = 'Ugrás ehhez az objektumosztály-definícióhoz'; //'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'Illesztőszabály-OID'; //'Matching Rule OID';
$lang['syntax_oid'] = 'Szintaxis-OID'; //'Syntax OID';
$lang['not_applicable'] = 'nem alkalmazható'; //'not applicable';
$lang['not_specified'] = 'nincs megadva'; //'not specified';
$lang['character'] = 'karakter'; //'character'; 
$lang['characters'] = 'karakter'; //'characters';
$lang['used_by_objectclasses'] = 'Mely osztályok használják'; //'Used by objectClasses';
$lang['used_by_attributes'] = 'Mely attribútumok használják'; //'Used by Attributes';
$lang['maximum_length'] = 'Maximális hossz'; //'Maximum Length';
$lang['attributes'] = 'Attribútumtípusok';//'Attribute Types';
$lang['syntaxes'] = 'Szintaxisok'; //'Syntaxes';
$lang['matchingrules'] = 'Illesztőszabályok'; //'Matching Rules';
$lang['oid'] = 'OID'; //'OID';
$lang['obsolete'] = 'Maradi'; //'Obsolete';
$lang['ordering'] = 'Sorbarendezés'; //'Ordering';
$lang['substring_rule'] = 'Alfüzér-szabály'; //'Substring Rule';
$lang['single_valued'] = 'Egyértékű'; //'Single Valued';
$lang['collective'] = 'Kollektív'; //'Collective';
$lang['user_modification'] = 'Felhasználó-módosítás'; //'User Modification';
$lang['usage'] = 'Használat'; //'Usage';
$lang['could_not_retrieve_schema_from'] = 'Nem tudom a sémát elérni,'; //'Could not retrieve schema from';
$lang['type']='Típus'; //'Type';

// Deleting entries
$lang['entry_deleted_successfully'] = 'A(z) %s bejegyzés sikeresen törölve.'; //'Entry %s deleted successfully.';
$lang['you_must_specify_a_dn'] = 'A DN-t meg kell adni'; //'You must specify a DN';
$lang['could_not_delete_entry'] = 'Nem tudom a(z) %s bejegyzést törölni'; //'Could not delete the entry: %s';
$lang['no_such_entry'] = 'Nincs ilyen bejegyzés: %s'; //'No such entry: %s';
$lang['delete_dn'] = 'A(z) %s törlése'; //'Delete %s';
$lang['permanently_delete_children'] = 'Az összes gyermeket is töröljem?'; //'Permanently delete all children also?';
$lang['entry_is_root_sub_tree'] = 'Ez a bejegyzés egy %s bejegyzést tartalmazó részfa gyökere.'; //'This entry is the root of a sub-tree containing %s entries.';
$lang['view_entries'] = 'bejegyzések megtekintése'; //'view entries';
$lang['confirm_recursive_delete'] = 'Rekurzív módon törölhetem ezt a bejegyzést, és mind a(z) %s gyermekét. Lásd alul a bejegyzéseket, amelyeket törölnék. Óhajtja törölni?'; //'phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?';
$lang['confirm_recursive_delete_note'] = 'Megj.: Ez a törlés veszélyes lehet. A műveletet nem lehet visszavonni!'; //'Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.';
$lang['delete_all_x_objects'] = 'Mind a(z) %s objektum törlése'; //'Delete all %s objects';
$lang['recursive_delete_progress'] = 'A rekurzív törlés folyamatban'; //'Recursive delete progress';
$lang['entry_and_sub_tree_deleted_successfully'] = 'A(z) %s bejegyzés és a részfa sikeresen törölve.'; //'Entry %s and sub-tree deleted successfully.';
$lang['failed_to_delete_entry'] = 'A(z) %s bejegyzés törlése sikertelen'; //'Failed to delete entry %s';
$lang['list_of_entries_to_be_deleted'] = 'A törlendő bejegyzések listája:'; //'List of entries to be deleted:';
$lang['sure_permanent_delete_object']='Biztos törölni óhajtja ezt az objektumot?'; //'Are you sure you want to permanently delete this object?';
$lang['dn'] = 'DN'; //'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'A(z) %s attribútum csak olvasható a phpLDAPadmin konfigurációja szerint.'; //'The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.';
$lang['no_attr_specified'] = 'Nincs megadva az attribútumnév.'; //'No attribute name specified.';
$lang['no_dn_specified'] = 'Nincs megadva a DN'; //'No DN specified';

// Adding attributes
$lang['left_attr_blank'] = 'Az attribútumértéket üresen hagyta. Kérem lépjen vissza és próbálja újra.'; //'You left the attribute value blank. Please go back and try again.';
$lang['failed_to_add_attr'] = 'Nem tudtam az attribútumot hozzáadni.'; //'Failed to add the attribute.';
$lang['file_empty'] = 'A kiválasztott állomány vagy üres, vagy nem létezik. Kérem lépjen vissza és próbálja újra.'; //'The file you chose is either empty or does not exist. Please go back and try again.';
$lang['invalid_file'] = 'Biztonsági hiba: a feltöltendő állomány veszélyes elemeket tartalmazhat.'; //'Security error: The file being uploaded may be malicious.';
$lang['warning_file_uploads_disabled'] = 'A PHP-konfiguráció tiltja az állományok feltöltését. Kérem ellenőrizze a php.ini állományt.'; //'Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.';
$lang['uploaded_file_too_big'] = 'A feltöltött állomány túl nagy. Kérem ellenőrizze a php.ini állományban a upload_max_size beállítást.'; //'The file you uploaded is too large. Please check php.ini, upload_max_size setting';
$lang['uploaded_file_partial'] = 'A kiválasztott állomány csak részben töltődött föl valószínűleg hálózati hiba miatt.'; //'The file you selected was only partially uploaded, likley due to a network error.';
$lang['max_file_size'] = 'Maximális állományméret: %s'; //'Maximum file size: %s';

// Updating values
$lang['modification_successful'] = 'A módosítás sikerült!'; //'Modification successful!';
$lang['change_password_new_login'] = 'Mivel megváltoztatta a jelszót most újra be kell jelentkeznie az új jelszóval.'; //'Since you changed your password, you must now login again with your new password.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Új kötelező attribútumok'; //'New Required Attributes';
$lang['requires_to_add'] = 'Ez a művelet megközeveteli hogy hozzáadjon'; //'This action requires you to add';
$lang['new_attributes'] = 'új attribútumo(ka)t'; //'new attributes';
$lang['new_required_attrs_instructions'] = 'Az új objektumosztály hozzáadásához'; //'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'kell megadni ezen az űrlapon.'; //'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'Objektumosztály és attribútumok hozzáadása'; //'Add ObjectClass and Attributes';
$lang['objectclasses'] = 'Objektumosztályok'; //'ObjectClasses';

// General
$lang['chooser_link_tooltip'] = 'Kattintásra egy új ablak jön föl, amelyben egy bejegyzést választhat a DN alapján.'; //'Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'Nem lehet módosítani mikor a kiszolgáló csak olvasható üzemmódban van'; //'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = 'Hibás kiszolgáló-azonosító'; //'Bad server id';
$lang['not_enough_login_info'] = 'Kevés az adat a belépéshez. Kérem ellenőrizze a konfigurációt.'; //'Not enough information to login to server. Please check your configuration.';
$lang['could_not_connect'] = 'Nem tudok csatlakozni az LDAP-kiszolgálóhoz.'; //'Could not connect to LDAP server.';
$lang['could_not_connect_to_host_on_port'] = 'Nem tudok csatlakozni a(z) %s géphez a(z) %s porton.'; //'Could not connect to "%s" on port "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Nem tudom végrehajtani az ldap_mod_add műveletet.'; //'Could not perform ldap_mod_add operation.';
$lang['bad_server_id_underline'] = 'Hibás kiszolgáló-azonosító: '; //'Bad server_id: ';
$lang['success'] = 'Siker'; //'Success';
$lang['server_colon_pare'] = 'Kiszolgáló: '; //'Server: ';
$lang['look_in'] = 'Keresés itt: '; //'Looking in: ';
$lang['missing_server_id_in_query_string'] = 'Nincs megadva a kiszolgáló-azonosító a keresési füzérben!'; //'No server ID specified in query string!';
$lang['missing_dn_in_query_string'] = 'Nincs megadva a DN a keresési füzérben!'; //'No DN specified in query string!';
$lang['back_up_p'] = 'Vissza...'; //'Back Up...';
$lang['no_entries'] = 'nics bejegyzés'; //'no entries';
$lang['not_logged_in'] = 'Nincs belépve'; //'Not logged in';
$lang['could_not_det_base_dn'] = 'Nem tudom az alap-DN-t meghatározni'; //'Could not determine base DN';
$lang['please_report_this_as_a_bug'] = 'Kérjük jelentse ezt a hibát.'; //'Please report this as a bug.';
$lang['reasons_for_error'] = 'Ez több dolog miatt történhet, például:'; //'This could happen for several reasons, the most probable of which are:';
$lang['yes'] = 'Igen'; //'Yes';
$lang['no'] = 'Nem'; //'No';
$lang['go'] = 'Mehet'; //'Go';
$lang['delete'] = 'Törlés'; //'Delete';
$lang['back'] = 'Vissza'; //'Back';
$lang['object'] = 'objektum'; //'object';
$lang['delete_all'] = 'Minden törlése'; //'Delete all';
$lang['url_bug_report'] = 'https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546'; //'https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'tipp'; //'hint';
$lang['bug'] = 'hiba'; //'bug';
$lang['warning'] = 'figyelmeztetés'; //'warning';
$lang['light'] = 'lámpa'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Tovább &gt;&gt;'; //'Proceed &gt;&gt;';


// Add value form
$lang['add_new'] = 'Új'; //'Add new';
$lang['value_to'] = 'érték, RDN:'; //'value to';
$lang['distinguished_name'] = 'Megkülönböztető név (DN)'; //'Distinguished Name';
$lang['current_list_of'] = 'Az aktuális lista'; //'Current list of';
$lang['values_for_attribute'] = 'értéket tartalmaz. Attribútum:'; //'values for attribute';
$lang['inappropriate_matching_note'] = 'Megj.: Ha nincs beállítva EGYENLŐSÉG-szabály ehhez az attribútumhoz a kiszolgáló az ún. inappropriate matching hibát adja vissza.'; //'Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Adja meg a kívánt értéket:'; //'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Megj.: Lehet hogy új kötelező attribútumokat kell bevinnie ehhez az objektumosztályhoz'; //'Note: you may be required to enter new attributes that this objectClass requires';
$lang['syntax'] = 'Szintaxis'; //'Syntax';

//copy.php
$lang['copy_server_read_only'] = 'Nem lehet módosítást eszközölni amíg a kiszolgáló csak olvasható üzemmódban van'; //'You cannot perform updates while server is in read-only mode';
$lang['copy_dest_dn_blank'] = 'Üresen hagyta a cél-DN mezejét'; //'You left the destination DN blank.';
$lang['copy_dest_already_exists'] = 'A célbejegyzés (%s) már létezik.'; //'The destination entry (%s) already exists.';
$lang['copy_dest_container_does_not_exist'] = 'A cél-tároló (%s) nem létezik.'; //'The destination container (%s) does not exist.';
$lang['copy_source_dest_dn_same'] = 'A forrás- és cél-DN ugyanaz.'; //'The source and destination DN are the same.';
$lang['copy_copying'] = 'Másolás: '; //'Copying ';
$lang['copy_recursive_copy_progress'] = 'Rekurzív másolás folyamatban'; //'Recursive copy progress';
$lang['copy_building_snapshot'] = 'A fáról készítek egy pillanatfelvételt a másoláshoz... '; //'Building snapshot of tree to copy... ';
$lang['copy_successful_like_to'] = 'A másolás sikerült! Szeretné-e '; //'Copy successful! Would you like to ';
$lang['copy_view_new_entry'] = 'megtekinteni az új bejegyzést'; //'view the new entry';
$lang['copy_failed'] = 'A DN másolása nem sikerült: '; //'Failed to copy DN: ';

//edit.php
$lang['missing_template_file'] = 'Figyelem: hiányzik a mintaállomány.'; //'Warning: missing template file, ';
$lang['using_default'] = 'Az alapértelmezés használata.'; //'Using default.';
$lang['template'] = 'Minta'; //'Template';
$lang['must_choose_template'] = 'Választania kell egy mintát.'; //'You must choose a template';
$lang['invalid_template'] = 'A(z) %s hibás minta.'; //'%s is an invalid template';
$lang['using_template'] = 'használt minta:'; //'using template';
$lang['go_to_dn'] = 'Menj a(z) %s DN-hez'; //'Go to %s';

//copy_form.php
$lang['copyf_title_copy'] = 'Másolás: '; //'Copy ';
$lang['copyf_to_new_object'] = '; az új objektum'; //'to a new object';
$lang['copyf_dest_dn'] = 'Cél-DN'; //'Destination DN';
$lang['copyf_dest_dn_tooltip'] = 'A másolással létrejövő új bejegyzés teljes DN-je'; //'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'Célkiszolgáló'; //'Destination Server';
$lang['copyf_note'] = 'Tipp: A kiszolgálók közötti másolás csak akkor működik, ha nincs séma-sértés'; //'Hint: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = 'Az objektum összes gyermekeinek rekurzív másolása.'; //'Recursively copy all children of this object as well.';
$lang['recursive_copy'] = 'Rekurzív másolás'; //'Recursive copy';
$lang['filter'] = 'Szűrő'; //'Filter';
$lang['filter_tooltip'] = 'Rekurzív másolásnál csak azokat másolja le, amelyekre illik ez a szűrő'; //'When performing a recursive copy, only copy those entries which match this filter';

//create.php
$lang['create_required_attribute'] = 'A(z) %s kötelező attribútum értékét üresen hagyta.'; //'You left the value blank for required attribute (%s).';
$lang['redirecting'] = 'Átirányítás...'; //'Redirecting...';
$lang['here'] = 'ide'; //'here';
$lang['create_could_not_add'] = 'Nem tudom az objektumot létrehozni a kiszolgálón.'; //'Could not add the object to the LDAP server.';

//create_form.php
$lang['createf_create_object'] = 'Objektum létrehozása'; //'Create Object';
$lang['createf_choose_temp'] = 'Válasszon mintát'; //'Choose a template';
$lang['createf_select_temp'] = 'Válasszon mintát a létrehozási folyamathoz'; //'Select a template for the creation process';
$lang['createf_proceed'] = 'Tovább'; //'Proceed';
$lang['rdn_field_blank'] = 'Az RDN-mezőt üresen hagyta'; //'You left the RDN field blank.';
$lang['container_does_not_exist'] = 'A(z) %s tároló nem létezik. Kérem próbálja újra.'; //'The container you specified (%s) does not exist. Please try again.';
$lang['no_objectclasses_selected'] = 'Nem választott objektumosztályt ehhez az objektumhoz. Kérem lépjen vissza és válasszon egyet.'; //'You did not select any ObjectClasses for this object. Please go back and do so.';
$lang['hint_structural_oclass'] = 'Tipp: Legalább egy strukturális objektumosztályt kell választania'; //'Hint: You must choose at least one structural objectClass';

//creation_template.php
$lang['ctemplate_on_server'] = 'Kiszolgáló:'; //'On server';
$lang['ctemplate_no_template'] = 'Nincs megadva a minta a POST-változóban.'; //'No template specified in POST variables.';
$lang['ctemplate_config_handler'] = 'A konfiguráció egy kezelőt ad meg:'; //'Your config specifies a handler of';
$lang['ctemplate_handler_does_not_exist'] = 'ehhez a mintához. De ez a kezelő nincs a mintakönyvtárban.'; //'for this template. But, this handler does not exist in the templates/creation directory.';
$lang['create_step1'] = 'Első lépés: Név és objektumosztály(ok)'; //'Step 1 of 2: Name and ObjectClass(es)';
$lang['create_step2'] = 'Második lépés: Adja meg az attribútumokat és értékeiket'; //'Step 2 of 2: Specify attributes and values';
$lang['relative_distinguished_name'] = 'Viszonylagos megkülönböztető név (RDN)'; //'Relative Distinguished Name';
$lang['rdn'] = 'RDN'; //'RDN';
$lang['rdn_example'] = '(példa: cn=ÚjEmber)'; //'(example: cn=MyNewPerson)';
$lang['container'] = 'Tároló'; //'Container';
$lang['alias_for'] = 'A(z) %s álneve a(z) %s attribútumnévnek'; //'Alias for %s';

// search.php
$lang['you_have_not_logged_into_server'] = 'Nem lépett be a kiválasztott kiszolgálóba, s így nem tudok keresni.'; //'You have not logged into the selected server yet, so you cannot perform searches on it.';
$lang['click_to_go_to_login_form'] = 'Kattintson ide a belépési űrlap eléréséhez'; //'Click here to go to the login form';
$lang['unrecognized_criteria_option'] = 'Ismeretlen kritérium-opció: '; //'Unrecognized criteria option: ';
$lang['if_you_want_to_add_criteria'] = 'Ha kritériumot kíván adni a listához szerkessze meg a search.php-t, hogy kezelje le azokat. Most kilépek.'; //'If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.';
$lang['entries_found'] = 'A megtalált bejegyzések: '; //'Entries found: ';
$lang['filter_performed'] = 'Használt szűrő: '; //'Filter performed: ';
$lang['search_duration'] = 'A phpLDAPadmin'; //'Search performed by phpLDAPadmin in';
$lang['seconds'] = 's alatt hajtotta végre a keresést'; //'seconds';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'A keresés hatásköre'; //'The scope in which to search';
$lang['scope_sub'] = 'Az egész részfa'; //'Sub (entire subtree)';
$lang['scope_one'] = 'Egy szint az alap-DN alatt'; //'One (one level beneath base)';
$lang['scope_base'] = 'Csak az alap-DN'; //'Base (base dn only)';
$lang['standard_ldap_search_filter'] = 'Szabványos LDAP-szűrő, pl. (&(sn=Kovács)(givenname=István))'; //'Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Keresőszűrő'; //'Search Filter';
$lang['list_of_attrs_to_display_in_results'] = 'A megjelenítendő találatok attribútumainak vesszővel elválasztott listája'; //'A list of attributes to display in the results (comma-separated)';
$lang['show_attributes'] = 'Attribútumok megjelenítése'; //'Show Attributes';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Bejegyzések keresése, ahol:'; //'Search for entries whose:';
$lang['equals'] = 'egyenlő'; //'equals';
$lang['starts with'] = 'kezdet'; //'starts with';
$lang['contains'] = 'tartalmaz'; //'contains';
$lang['ends with'] = 'végződés'; //'ends with';
$lang['sounds like'] = 'hangzás'; //'sounds like';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Nem tudom az információt letölteni a kiszolgálóról'; //'Could not retrieve LDAP information from the server';
$lang['server_info_for'] = 'Kiszolgáló-infó: '; //'Server info for: ';
$lang['server_reports_following'] = 'A kiszolgáló ezeket az információkat közölte önmagáról'; //'Server reports the following information about itself';
$lang['nothing_to_report'] = 'A kiszolgálónak nincs mit elküldenie'; //'This server has nothing to report.';

//update.php
$lang['update_array_malformed'] = 'Az update_array hibés. Ez a phpLDAPadmin hibája lehet, kérem jelentse.'; //'update_array is malformed. This might be a phpLDAPadmin bug. Please report it.';
$lang['could_not_perform_ldap_modify'] = 'Nem tudom elvégezni az ldap_modify műveletet.'; //'Could not perform ldap_modify operation.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Érvényesíteni kívánja a változásokat?'; //'Do you want to make these changes?';
$lang['attribute'] = 'Attribútumok'; //'Attribute';
$lang['old_value'] = 'Régi érték'; //'Old Value';
$lang['new_value'] = 'Új érték'; //'New Value';
$lang['attr_deleted'] = '[attribútum törölve]'; //'[attribute deleted]';
$lang['commit'] = 'Érvényesítés'; //'Commit';
$lang['cancel'] = 'Mégsem'; //'Cancel';
$lang['you_made_no_changes'] = 'Nem történt változtatás'; //'You made no changes';
$lang['go_back'] = 'Vissza'; //'Go back';

// welcome.php
$lang['welcome_note'] = 'A bal oldali menüvel tájékozódhat a programban'; //'Use the menu to the left to navigate';
$lang['credits'] = 'Köszönetnyilvánítás'; //'Credits';
$lang['changelog'] = 'Változtatások naplója'; //'ChangeLog';
$lang['donate'] = 'Adományozzon'; //'Donate';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nem biztonságos állománynév: '; //'Unsafe file name: ';
$lang['no_such_file'] = 'Nincs ilyen állomány: '; //'No such file: ';

//function.php
$lang['auto_update_not_setup'] = 'Az auto_uid_numbers engedélyezett a konfigurációban  a(z) <b>%s</b> kiszolgálóhoz, de az auto_uid_number_mechanism nincs megadva. Kérem írja be ezt az értéket.'; 
//'You have enabled auto_uid_numbers for <b>%s</b> in your configuration,
//                                  but you have not specified the auto_uid_number_mechanism. Please correct
//                                  this problem.';
$lang['uidpool_not_set'] = 'A konfigurációban az auto_uid_number_mechanism értéke uidpool a(z) <b>%s</b> kiszolgálóhoz, de nem adta meg az auto_uid_number_uid_pool_dn értékét. Kérem írja be ezt az értéket.'; 
//'You specified the "auto_uid_number_mechanism" as "uidpool"
//                            in your configuration for server <b>%s</b>, but you did not specify the
//                            audo_uid_number_uid_pool_dn. Please specify it before proceeding.';
$lang['uidpool_not_exist'] = 'A konfigurációban megadott uidPool nem létezik (%s).'; 
//'It appears that the uidPool you specified in your configuration ("%s")
//                              does not exist.';
$lang['specified_uidpool'] = 'A konfigurációban az auto_uid_number_mechanism értéke search a(z) <b>%s</b> kiszolgálóhoz, de nem adta meg az auto_uid_number_search_base értékét. Kérem írja be ezt az értéket.';
//'You specified the "auto_uid_number_mechanism" as "search" in your
//                              configuration for server <b>%s</b>, but you did not specify the
//                              "auto_uid_number_search_base". Please specify it before proceeding.';
$lang['auto_uid_invalid_credential'] = 'A bind művelet sikertelen a(z) <b>%s</b> kiszolgálóhoz az auto_uid használatával. Kérem ellenőrizze a konfigurációt. '; //'Unable to bind to <b>%s</b> with your with auto_uid credentials. Please check your configuration file.'; 
$lang['bad_auto_uid_search_base'] = 'A konfigurációban érvénytelen auto_uid_search_base van megadva a(z) <b>%s</b> kiszolgálóhoz.'; //'Your phpLDAPadmin configuration specifies an invalid auto_uid_search_base for server %s';
$lang['auto_uid_invalid_value'] = 'Érvénytelen értéket adott meg a auto_uid_number_mechanism-nak (%s). Csak uidpool és search a megengedett érték.'; 
//'You specified an invalid value for auto_uid_number_mechanism ("%s")
//                                   in your configration. Only "uidpool" and "search" are valid.
//                                   Please correct this problem.';
$lang['error_auth_type_config'] = 'Csak a session, cookie és config a megengedett értékek az auth_type-nak a konfigurációban. A megadott helytelen érték: %s.'; 
//'Error: You have an error in your config file. The only three allowed values
//                                    for auth_type in the $servers section are \'session\', \'cookie\', and \'config\'. You entered \'%s\',
//                                    which is not allowed. ';
$lang['php_install_not_supports_tls'] = 'Ez a PHP nem támogatja a TLS-t.'; //'Your PHP install does not support TLS.';
$lang['could_not_start_tls'] = 'Nem tudom a TLS-t elindítani. Kérem ellenőrizze az LDAP-kiszolgálót és a konfigurációt.'; //'Could not start TLS. Please check your LDAP server configuration.';
$lang['could_not_bind_anon'] = 'Az anonymous bind művelet nem sikerült.'; //'Could not bind anonymously to server.';
$lang['could_not_bind'] = 'A bind művelet sikertelen az LDAP-kiszolgálóhoz.'; //'Could not bind to the LDAP server.';
$lang['anonymous_bind'] = 'Anonymous bind'; //'Anonymous Bind';
$lang['bad_user_name_or_password'] = 'Helytelen felhasználónév vagy jelszó. Kérem próbálja újra.'; //'Bad username or password. Please try again.';
$lang['redirecting_click_if_nothing_happens'] = 'Átirányítás... kérem kattintson ide, ha semmi sem történik.'; //'Redirecting... Click here if nothing happens.';
$lang['successfully_logged_in_to_server'] = 'A(z) %s kiszolgálóhoz sikeresen bejelentkezett.'; //'Successfully logged into server <b>%s</b>';
$lang['could_not_set_cookie'] = 'Nem tudok sütit beállítani.'; //'Could not set cookie.';
$lang['ldap_said'] = 'Az LDAP ezt mondta: %s'; //'LDAP said: %s';
$lang['ferror_error'] = 'Hiba'; //'Error';
$lang['fbrowse'] = 'böngészés'; //'browse';
$lang['delete_photo'] = 'Fotó törlése'; //'Delete Photo';
$lang['install_not_support_blowfish'] = 'Ez a PHP nem támogatja a blowfish titkosítást.'; //'Your PHP install does not support blowfish encryption.';
$lang['install_not_support_md5crypt'] = 'Ez a PHP nem támogatja az md5crypt titkosítást.'; //'Your PHP install does not support md5crypt encryption.';
$lang['install_no_mash'] = 'Ez a PHP nem tartalmazza az mhash() függvényt. Nem tudok SHA hash-eket kezelni.'; //'Your PHP install does not have the mhash() function. Cannot do SHA hashes.';
$lang['jpeg_contains_errors'] = 'A jpegPhoto hibás<br />'; //'jpegPhoto contains errors<br />';
$lang['ferror_number'] = 'Hibaszám: %s (%s)'; //'Error number: %s (%s)';
$lang['ferror_discription'] = 'Leírás: %s <br /><br />'; //'Description: %s <br /><br />';
$lang['ferror_number_short'] = 'Hibaszám: %s<br /><br />'; //'Error number: %s<br /><br />';
$lang['ferror_discription_short'] = 'Leírás: (nincs leírás)<br />'; //'Description: (no description available)<br />';
$lang['ferror_submit_bug'] = 'Ez a phpLDAPadmin hibája? Ha igen, kérem <a href=\'%s\'>jelentse</a>.'; //'Is this a phpLDAPadmin bug? If so, please <a href=\'%s\'>report it</a>.';
$lang['ferror_unrecognized_num'] = 'Ismeretlen hibaszám: '; //'Unrecognized error number: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Egy kisebb hibát talált a phpLDAPadmin-ban!</b></td></tr><tr><td>Hiba:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Állomány:</td>
             <td><b>%s.</b> sor <b>%s</b>, hívó <b>%s</b></td></tr><tr><td>Verziók:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web-kiszolgáló:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Kérem jelentse a hibát, kattintson ide!</a>.</center></td></tr></table></center><br />';
//'<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
//             <b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td>
//             <td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
//             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
//             Please report this bug by clicking here</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Gratulálunk! Hibát talált a phpLDAPadmin-ban.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Hiba:</td><td><b>%s</b></td></tr>
	     <tr><td>Szint:</td><td><b>%s</b></td></tr>
	     <tr><td>Állomány:</td><td><b>%s</b></td></tr>
	     <tr><td>Sor:</td><td><b>%s</b></td></tr>
		 <tr><td>Hívó:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA Verzió:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP Verzió:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web-kiszolgáló:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Kérjük jelentse a hibát, kattintson alulra!';
//'Congratulations! You found a bug in phpLDAPadmin.<br /><br />
//	     <table class=\'bug\'>
//	     <tr><td>Error:</td><td><b>%s</b></td></tr>
//	     <tr><td>Level:</td><td><b>%s</b></td></tr>
//	     <tr><td>File:</td><td><b>%s</b></td></tr>
//	     <tr><td>Line:</td><td><b>%s</b></td></tr>
//		 <tr><td>Caller:</td><td><b>%s</b></td></tr>
//	     <tr><td>PLA Version:</td><td><b>%s</b></td></tr>
//	     <tr><td>PHP Version:</td><td><b>%s</b></td></tr>
//	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
//	     <tr><td>Web server:</td><td><b>%s</b></td></tr>
//	     </table>
//	     <br />
//	     Please report this bug by clicking below!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'LDIF-állomány importálása'; //'Import LDIF File';
$lang['select_ldif_file'] = 'LDIF-állomány kiválasztása'; //'Select an LDIF file:';
$lang['select_ldif_file_proceed'] = 'Tovább &gt;&gt;'; //'Proceed &gt;&gt;';
$lang['dont_stop_on_errors'] = 'Ne állj meg hiba esetén'; //'Don\'t stop on errors';

//ldif_import
$lang['add_action'] = 'Hozzáadás...'; //'Adding...';
$lang['delete_action'] = 'Törlés...'; //'Deleting...';
$lang['rename_action'] = 'Átnevezés'; //'Renaming...';
$lang['modify_action'] = 'Módosítás...'; //'Modifying...';
$lang['warning_no_ldif_version_found'] = 'Nem találtam verziószámot, feltételeztem, hogy 1-es.'; //'No version found. Assuming 1.';
$lang['valid_dn_line_required'] = 'Érvényes DN-sor kell.'; //'A valid dn line is required.';
$lang['missing_uploaded_file'] = 'A feltöltött állomány hiányzik.'; //'Missing uploaded file.';
$lang['no_ldif_file_specified.'] = 'Nem adott meg LDIF-állományt. Kérem próbálja újra.'; //'No LDIF file specified. Please try again.';
$lang['ldif_file_empty'] = 'A feltöltött LDIF-állomány üres.'; //'Uploaded LDIF file is empty.';
$lang['empty'] = 'üres'; //'empty';
$lang['file'] = 'Állomány'; //'File';
$lang['number_bytes'] = '%s byte'; //'%s bytes';

$lang['failed'] = 'Sikertelen'; //'Failed';
$lang['ldif_parse_error'] = 'LDIF-pásztázási hiba'; //'LDIF Parse Error';
$lang['ldif_could_not_add_object'] = 'Nem tudom ezt az objektumot hozzáadni:'; //'Could not add object:';
$lang['ldif_could_not_rename_object'] = 'Nem tudom ezt az objektumot átnevezni:'; //'Could not rename object:';
$lang['ldif_could_not_delete_object'] = 'Nem tudom ezt az objektumot törölni:'; //'Could not delete object:';
$lang['ldif_could_not_modify_object'] = 'Nem tudom ezt az objektumot módosítani:'; //'Could not modify object:';
$lang['ldif_line_number'] = 'Sorszám:'; //'Line Number:';
$lang['ldif_line'] = 'Sor:'; //'Line:';

// Exports
$lang['export_format'] = 'Export-formátum'; //'Export format';
$lang['line_ends'] = 'Sorvégek'; //'Line ends';
$lang['must_choose_export_format'] = 'Az export formátumát ki kell választani.'; //'You must choose an export format.';
$lang['invalid_export_format'] = 'Az export formátuma érvénytelen'; //'Invalid export format';
$lang['no_exporter_found'] = 'Nincs használható exportáló.'; //'No available exporter found.';
$lang['error_performing_search'] = 'Keresés közben hibába akadtam.'; //'Encountered an error while performing search.';
$lang['showing_results_x_through_y'] = 'A(z) %s és %s közé eső találatok megjelenítése.'; //'Showing results %s through %s.';
$lang['searching'] = 'Folyik a keresés...'; //'Searching...';
$lang['size_limit_exceeded'] = 'Figyelem: a keresési méret korlátja túllépve.'; //'Notice, search size limit exceeded.';
$lang['entry'] = 'Bejegyzés'; //'Entry';
$lang['ldif_export_for_dn'] = 'A(z) %s LDIF exportja'; //'LDIF Export for: %s';
$lang['generated_on_date'] = 'A phpLDAPadmin generálta, dátum: %s'; //'Generated by phpLDAPadmin on %s';
$lang['total_entries'] = 'Bejegyzések száma összesen'; //'Total Entries';
$lang['dsml_export_for_dn'] = 'A(z) %s DSLM exportja'; //'DSLM Export for: %s';

// logins
$lang['could_not_find_user'] = 'Nem találtam meg a(z) %s felhasználót'; //'Could not find a user "%s"';
$lang['password_blank'] = 'A jelszó üresen maradt.'; //'You left the password blank.';
$lang['login_cancelled'] = 'A bejelentkezést megszakították.'; //'Login cancelled.';
$lang['no_one_logged_in'] = 'Arra a kiszolgálóra nincs bejelentkezve senki.'; //'No one is logged in to that server.';
$lang['could_not_logout'] = 'Nem sikerült kilépni.'; //'Could not logout.';
$lang['unknown_auth_type'] = 'Ismeretlen auth_type: %s'; //'Unknown auth_type: %s';
$lang['logged_out_successfully'] = 'A kilépés sikerült a(z) <b>%s</b> kiszolgálóból'; //'Logged out successfully from server <b>%s</b>';
$lang['authenticate_to_server'] = 'Azonosítsa magát a(z) %s kiszolgálón'; //'Authenticate to server %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Figyelem: A kapcsolat a bögészővel titkosítatlan.'; //'Warning: This web connection is unencrypted.';
$lang['not_using_https'] = 'Nem https kapcsolatot használ. A böngésző a bejelentkezés adatait sima szöveg formátumban fogja továbbítani'; //'You are not using \'https\'. Web browser will transmit login information in clear text.';
$lang['login_dn'] = 'Bejelentkezési DN'; //'Login DN';
$lang['user_name'] = 'A felhasználó neve'; //'User name';
$lang['password'] = 'Jelszó'; //'Password';
$lang['authenticate'] = 'Azonosítás'; //'Authenticate';

// Entry browser
$lang['entry_chooser_title'] = 'Bejegyzés kiválasztása'; //'Entry Chooser';

// Index page
$lang['need_to_configure'] = 'Konfigurálnia kell a phpLDAPadmint a config.php állomány szerkesztésével, amihez mintául a config.php.example szolgálhat.'; //'You need to configure phpLDAPadmin. Edit the file \'config.php\' to do so. An example config file is provided in \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Csak olvasható üzemmódban a törlés nem működik.'; //'Deletes not allowed in read only mode.';
$lang['error_calling_mass_delete'] = 'Hiba a mass_delete.php hívásakor. A POST változók közül hiányzik a mass_delete.'; //'Error calling mass_delete.php. Missing mass_delete in POST vars.';
$lang['mass_delete_not_array'] = 'A mass_delete POST változó nem tömb.'; //'mass_delete POST var is not an array.';
$lang['mass_delete_not_enabled'] = 'A tömeges törlés nincs engedélyezve. Mielőtt folytatná, állítsa át az enable_mass_delete értékét config.php állományban.'; //'Mass deletion is not enabled. Please enable it in config.php before proceeding.';
$lang['mass_deleting'] = 'Tömeges törlés'; //'Mass Deleting';
$lang['mass_delete_progress'] = 'A törlési művelet a(z) %s kiszolgálón'; //'Deletion progress on server "%s"';
$lang['malformed_mass_delete_array'] = 'A tömeges törléshez megadott tömb formátuma helytelen'; //'Malformed mass_delete array.';
$lang['no_entries_to_delete'] = 'Nem választotta ki a törlendő bejegyzés(eke)t. '; //'You did not select any entries to delete.';
$lang['deleting_dn'] = '%s törölve'; //'Deleting %s';
$lang['total_entries_failed'] = '%s darab bejegyzés törlése nem sikerült a(z) %s darabból.'; //'%s of %s entries failed to be deleted.';
$lang['all_entries_successful'] = 'Az összes bejegyzés törlése sikerült.'; //'All entries deleted successfully.';
$lang['confirm_mass_delete'] = 'Hagyja jóvá a(z) %s bejegyzés tömeges törlését a(z) %s kiszolgálóról'; //'Confirm mass delete of %s entries on server %s';
$lang['yes_delete'] = 'Igen, törlődjön'; //'Yes, delete!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Nem nevezhet át olyan bejegyzést aminek gyermekei vannak (azaz az átnevezés művelete csak levél-bejegyzéseken megengedett)'; //'You cannot rename an entry which has children entries (eg, the rename operation is not allowed on non-leaf entries)';
$lang['no_rdn_change'] = 'Nem változtatta meg az RDN-t'; //'You did not change the RDN';
$lang['invalid_rdn'] = 'Érvénytelen RDN érték'; //'Invalid RDN value';
$lang['could_not_rename'] = 'A bejegyzést nem sikerült átnevezni'; //'Could not rename the entry';

?>
