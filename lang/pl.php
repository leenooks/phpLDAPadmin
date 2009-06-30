<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/pl.php,v 1.13.2.1 2005/10/09 06:29:41 wurley Exp $

/*        ---   INSTRUCTIONS FOR TRANSLATORS   ---
 *
 * If you want to write a new language file for your language,
 * please submit the file on SourceForge:
 *
 * https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498548
 *
 * Use the option "Check to Upload and Attach a File" at the bottom
 *
 * Read the doc/README-translation.txt for translation instructions.
 *
 * Thank you!
 *
 */

/* $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/pl.php,v 1.13.2.1 2005/10/09 06:29:41 wurley Exp $
 * initial translation from Piotr (DrFugazi) Tarnowski on Version 0.9.3
 */
// Based on en.php version 1.133

// Search form
$lang['simple_search_form_str'] = 'Wyszukiwanie proste';
$lang['advanced_search_form_str'] = 'Wyszukiwanie zaawansowane';
$lang['server'] = 'Serwer';
$lang['search_for_entries_whose'] = 'Szukaj wpisów w których';
$lang['base_dn'] = 'Bazowa DN';
$lang['search_scope'] = 'Zakres przeszukiwania';
$lang['show_attributes'] = 'Poka¿ atrybuty';
$lang['Search'] = 'Szukaj';
$lang['equals'] = 'równa siê';
$lang['contains'] = 'zawiera';
$lang['predefined_search_str'] = 'Wybierz predefiniowane wyszukiwanie';
$lang['predefined_searches'] = 'Predefiniowane wyszukiwania';
$lang['no_predefined_queries'] = 'Brak zdefiniowanych zapytañ w config.php.';
$lang['export_results'] = 'wyeksportuj wyniki';
$lang['unrecoginzed_search_result_format'] = 'Nierozpoznany format wyniku wyszukiwania: %s';
$lang['format'] = 'Format';
$lang['list'] = 'lista';
$lang['table'] = 'tabela';
$lang['bad_search_display'] = 'W Twojej konfiguracji (config.php) okre¶lona jest nieprawid³owa warto¶æ dla $default_search_display: %s. Proszê to poprawiæ';
$lang['page_n'] = 'Strona %d';
$lang['next_page'] = 'Nastêpna strona'; // 'Next Page';
$lang['no_results'] = 'Wyszukiwanie nie przynios³o ¿adnych rezultatów.';
		      
// Tree browser
$lang['request_new_feature'] = 'Zg³o¶ zapotrzebowanie';
$lang['report_bug'] = 'Zg³o¶ b³±d';
$lang['schema'] = 'schemat';
$lang['search'] = 'szukaj';
$lang['create'] = 'utwórz';
$lang['info'] = 'info';
$lang['import'] = 'import';
$lang['refresh'] = 'od¶wie¿';
$lang['logout'] = 'wyloguj';
$lang['create_new'] = 'Utwórz nowy wpis';
$lang['view_schema_for'] = 'Poka¿ schemat dla';
$lang['refresh_expanded_containers'] = 'Od¶wie¿ wszystkie otwarte kontenery dla';
$lang['create_new_entry_on'] = 'Utwórz nowy wpis na';
$lang['new'] = 'nowy';
$lang['view_server_info'] = 'Poka¿ informacje o serwerze';
$lang['import_from_ldif'] = 'Importuj wpisy z pliku LDIF';
$lang['logout_of_this_server'] = 'Wyloguj z tego serwera';
$lang['logged_in_as'] = 'Zalogowany/a jako: ';
$lang['this_base_dn_is_not_valid'] = 'To nie jest prawid³owa DN.';
$lang['this_base_dn_does_not_exist'] = 'Ten wpis nie istnieje';
$lang['read_only'] = 'tylko-do-odczytu';
$lang['read_only_tooltip'] = 'Ten atrybut zosta³ oznaczony przez administratora phpLDAPadmin jako tylko-do-odczytu';
$lang['could_not_determine_root'] = 'Nie mo¿na ustaliæ korzenia Twojego drzewa LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Wygl±da, ¿e serwer LDAP jest skonfigurowany tak, aby nie ujawniaæ swojego korzenia.';
$lang['please_specify_in_config'] = 'Proszê okre¶liæ to w pliku config.php';
$lang['create_new_entry_in'] = 'Utwórz nowy wpis w';
$lang['login_link'] = 'Logowanie...';
$lang['login'] = 'login';
$lang['base_entry_does_not_exist'] = 'Ten wpis bazowy nie istnieje';
$lang['create_it'] = 'Utworzyæ ?';
     
// Entry display
$lang['delete_this_entry'] = 'Usuñ ten wpis';
$lang['delete_this_entry_tooltip'] = 'Bêdziesz poproszony/a o potwierdzenie tej decyzji';
$lang['copy_this_entry'] = 'Skopiuj lub przenie¶ ten wpis';
$lang['copy_this_entry_tooltip'] = 'Skopiuj ten obiekt do innej lokalizacji, nowej DN, lub do innego serwera';
$lang['export'] = 'Eksportuj';
$lang['export_lcase'] = 'eksportuj';
$lang['export_tooltip'] = 'Zapisz zrzut tego obiektu';
$lang['export_subtree_tooltip'] = 'Zapisz zrzut tego obiektu i wszystkich potomnych';
$lang['export_subtree'] = 'Eksportuj ca³e poddrzewo';
$lang['create_a_child_entry'] = 'Utwórz wpis potomny';
$lang['rename_entry'] = 'Zmieñ nazwê wpisu';
$lang['rename'] = 'Zmieñ nazwê';
$lang['rename_lower'] = 'zmieñ nazwê'; // 'rename';
$lang['add'] = 'Dodaj';
$lang['view'] = 'Poka¿';
$lang['view_one_child'] = 'Poka¿ 1 wpis potomny';
$lang['view_children'] = 'Poka¿ %s wpisy/ów potomne/ych';
$lang['add_new_attribute'] = 'Dodaj nowy atrybut';
$lang['add_new_objectclass'] = 'Dodaj now± klasê obiektu';
$lang['hide_internal_attrs'] = 'Ukryj wewnêtrzne atrybuty';
$lang['show_internal_attrs'] = 'Poka¿ wewnêtrzne atrybuty';
$lang['attr_name_tooltip'] = 'Kliknij aby obejrzeæ definicje schematu dla atrybutu typu \'%s\'';
$lang['none'] = 'brak';
$lang['no_internal_attributes'] = 'Brak atrybutów wewnêtrznych';
$lang['no_attributes'] = 'Ten wpis nie posiada atrybutów';
$lang['save_changes'] = 'Zapisz zmiany';
$lang['add_value'] = 'dodaj warto¶æ';
$lang['add_value_tooltip'] = 'Dodaj dodatkow± warto¶æ do atrybutu \'%s\'';
$lang['refresh_entry'] = 'Od¶wie¿';
$lang['refresh_this_entry'] = 'Od¶wie¿ ten wpis';
$lang['delete_hint'] = 'Wskazówka: Aby skasowaæ atrybut, wyczy¶æ pole tekstowe i kliknij zapisz.';
$lang['attr_schema_hint'] = 'Wskazówka: Aby zobaczyæ schemat dla atrybutu, kliknij na nazwie atrybutu.';
$lang['attrs_modified'] = 'Niektóre atrybuty (%s) zosta³y zmodyfikowane i s± wyró¿nione poni¿ej.';
$lang['attr_modified'] = 'Atrybut (%s) zosta³ zmodyfikowany i jest wyró¿niony poni¿ej.';
$lang['viewing_read_only'] = 'Ogl±danie wpisu w trybie tylko-do-odczytu.';
$lang['no_new_attrs_available'] = 'brak nowych atrybutów dostêpnych dla tego wpisu';
$lang['no_new_binary_attrs_available'] = 'brak nowych atrybutów binarnych dla tego wpisu';
$lang['binary_value'] = 'Warto¶æ binarna';
$lang['add_new_binary_attr'] = 'Dodaj nowy atrybut binarny';
$lang['alias_for'] = 'Uwaga: \'%s\' jest aliasem dla \'%s\'';
$lang['required_for'] = 'Atrybut wymagany dla klas(y) obiektu %s';
$lang['required_by_entry'] = 'Ten atrybut jest wymagany dla RDN'; 
$lang['download_value'] = 'pobierz (download) warto¶æ';
$lang['delete_attribute'] = 'usuñ atrybut';
$lang['true'] = 'prawda';
$lang['false'] = 'fa³sz';
$lang['none_remove_value'] = 'brak, usuñ warto¶æ';
$lang['really_delete_attribute'] = 'Definitywnie usuñ atrybut';
$lang['add_new_value'] = 'Dodaj now± warto¶æ';

// Schema browser
$lang['schema_retrieve_error_1']='Serwer nie wspiera w pe³ni protoko³u LDAP.';
$lang['schema_retrieve_error_2']='Twoja wersja PHP niepoprawnie wykonuje zapytanie.';
$lang['schema_retrieve_error_3']='phpLDAPadmin nie wie jak uzyskaæ schemat z Twojego serwera.';
$lang['schema_retrieve_error_4']='Lub w ostateczno¶ci, Twój serwer nie dostarcza tej informacji.';
$lang['jump_to_objectclass'] = 'Skocz do klasy obiektu';
$lang['view_schema_for_oclass'] = 'Poka¿ opis schematu dla tej klasy obiektu';
$lang['jump_to_attr'] = 'Skocz do typu atrybutu';
$lang['jump_to_matching_rule'] = 'Skocz do regu³y dopasowania';
$lang['schema_for_server'] = 'Schemat dla serwera';
$lang['required_attrs'] = 'Wymagane atrybuty';
$lang['required'] = 'wymagane';
$lang['optional_attrs'] = 'Opcjonalne atrybuty';
$lang['optional_binary_attrs'] = 'Opcjonalne atrybuty binarne';
$lang['OID'] = 'OID';
$lang['aliases']='Aliasy';
$lang['desc'] = 'Opis';
$lang['no_description']='brak opisu';
$lang['name'] = 'Nazwa';
$lang['equality']='Równo¶æ';
$lang['is_obsolete'] = 'Ta klasa obiektu jest przestarza³a';
$lang['inherits'] = 'Dziedziczy z';
$lang['inherited_from']='dziedziczone z';
$lang['parent_to'] = 'Nadrzêdny dla';
$lang['jump_to_this_oclass'] = 'Skocz do definicji klasy obiektu';
$lang['matching_rule_oid'] = 'OID regu³y dopasowania';
$lang['syntax_oid'] = 'OID sk³adni';
$lang['not_applicable'] = 'nie dotyczy';
$lang['not_specified'] = 'nie okre¶lone';
$lang['character']='znak'; 
$lang['characters']='znaki/ów';
$lang['used_by_objectclasses']='U¿ywane przez klasy obiektu';
$lang['used_by_attributes']='U¿ywane przez atrybuty';
$lang['maximum_length']='Maksymalna d³ugo¶æ';
$lang['attribute_types']='Typy atrybutów';
$lang['syntaxes']='Sk³adnie';
$lang['objectclasses']='Klasy Obiektu';
$lang['matchingrules']='Regu³y Dopasowania';
$lang['oid']='OID';
$lang['obsolete']='Przestarza³e ';
$lang['ordering']='Uporz±dkowanie';
$lang['substring_rule']='Regu³a podci±gu (Substring Rule)';
$lang['single_valued']='Pojedynczo ceniona (Single Valued)';
$lang['collective']='Zbiorcza ';
$lang['user_modification']='Modyfikacja u¿ytkownika';
$lang['usage']='U¿ycie';
$lang['could_not_retrieve_schema_from']='Nie mo¿na uzyskaæ schematu z';
$lang['type']='Typ';
$lang['no_such_schema_item'] = 'Nie ma takiej pozycji w schemacie: "%s"';
$lang['random_password'] = 'Zosta³o wygenerowane losowe has³o'; // 'A random password was generated for you';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Wpis %s zosta³ pomy¶lnie usuniêty.';
$lang['you_must_specify_a_dn'] = 'Musisz okre¶liæ DN';
$lang['could_not_delete_entry'] = 'Nie mo¿na usun±æ wpisu: %s';
$lang['no_such_entry'] = 'Nie ma takiego wpisu: %s';
$lang['delete_dn'] = 'Usuñ %s';
$lang['permanently_delete_children'] = 'Czy trwale usun±æ tak¿e wpisy potomne ?';
$lang['entry_is_root_sub_tree'] = 'Ten wpis jest korzeniem poddrzewa zawieraj±cego %s wpisów.';
$lang['view_entries'] = 'poka¿ wpisy';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin mo¿e rekursywnie usun±æ ten wpis i wszystkie jego %s wpisy/ów potomne/ych. Sprawd¼ poni¿sz± listê wpisów przeznaczonych do usuniêcia.<br /> Czy na pewno chcesz to zrobiæ ?';
$lang['confirm_recursive_delete_note'] = 'Uwaga: ta operacja jest potencjalnie bardzo niebezpieczna i wykonujesz j± na w³asne ryzyko. Ta akcja nie mo¿e zostaæ cofniêta. We¼ pod uwagê aliasy, owo³ania i inne rzeczy, które mog± spowodowaæ problemy.';
$lang['delete_all_x_objects'] = 'Usuñ wszystkie %s obiekty/ów';
$lang['recursive_delete_progress'] = 'Postêp rekursywnego usuwania';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Wpis %s oraz poddrzewo zosta³y pomy¶lnie usuniête.';
$lang['failed_to_delete_entry'] = 'B³±d podczas usuwania wpisu %s';

// Deleting attributes
$lang['attr_is_read_only'] = 'Atrybut "%s" jest oznaczony jako tylko-do-odczytu w konfiguracji phpLDAPadmin.';
$lang['no_attr_specified'] = 'Nie okre¶lono nazwy atrybutu.';
$lang['no_dn_specified'] = 'Nie okre¶lono DN';

// Adding attributes
$lang['left_attr_blank'] = 'Pozostawi³e¶/a¶ pust± warto¶æ atrybutu. Proszê wróciæ i spróbowaæ ponownie.';
$lang['failed_to_add_attr'] = 'B³±d podczas dodawania atrybutu.';
$lang['file_empty'] = 'Wybrany plik jest pusty lub nie istnieje. Wróæ i spróbuj ponownie.';
$lang['invalid_file'] = 'B³±d bezpieczeñstwa: Za³adowany plik mo¿e stanowiæ zagro¿enie.';
$lang['warning_file_uploads_disabled'] = 'Twoja konfiguracja PHP uniemo¿liwia za³adowanie plików. Proszê sprawdziæ php.ini przed kontynuacj±.';
$lang['uploaded_file_too_big'] = 'Za³adowany plik jest zbyt du¿y. Proszê sprawdziæ ustawienie upload_max_size w php.ini';
$lang['uploaded_file_partial'] = 'Wybrany plik zosta³ tylko czê¶ciowo za³adowany, prawdopodobnie wyst±pi³ b³±d w sieci.';
$lang['max_file_size'] = 'Maksymalny rozmiar pliku: %s';

// Updating values
$lang['modification_successful'] = 'Modyfikacja zakoñczona pomy¶lnie.';
$lang['change_password_new_login'] = 'Je¶li zmieni³e¶/a¶ has³o, musisz siê zalogowaæ ponownie z nowym has³em.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nowe atrybuty wymagane';
$lang['requires_to_add'] = 'Ta akcja wymaga, aby¶ doda³/a';
$lang['new_attributes'] = 'nowe atrybuty';
$lang['new_required_attrs_instructions'] = 'Instrukcja: Aby dodaæ tê/e klasê/y obiektu do tego wpisu, musisz okre¶liæ';
$lang['that_this_oclass_requires'] = 'co ta klasa obiektu wymaga. Mo¿esz zrobiæ to w tym formularzu.';
$lang['add_oclass_and_attrs'] = 'Dodaj klasê obiektu i atrybuty';

// General
$lang['chooser_link_tooltip'] = 'Kliknij aby wywo³aæ okno i wybraæ wpis (DN) graficznie';
$lang['no_updates_in_read_only_mode'] = 'Nie mo¿esz wykonaæ modyfikacji dopóki serwer jest w trybie tylko-do-odczytu';
$lang['bad_server_id'] = 'Z³y identyfikator (id) serwera';
$lang['not_enough_login_info'] = 'Brak wystarczaj±cych informacji aby zalogowaæ siê do serwera. Proszê sprawdziæ konfiguracjê.';
$lang['could_not_connect'] = 'Nie mo¿na pod³±czyæ siê do serwera LDAP.';
$lang['could_not_connect_to_host_on_port'] = 'Nie mo¿na pod³±czyæ siê do "%s" na port "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Nie mo¿na dokonaæ operacji ldap_mod_add.';
$lang['home'] = 'Strona g³ówna';
$lang['help'] = 'Pomoc';
$lang['success'] = 'Sukces';
$lang['server_colon_pare'] = 'Serwer: ';
$lang['look_in'] = 'Szukam w: ';
$lang['missing_dn_in_query_string'] = 'Nie okre¶lono DN w zapytaniu !';
$lang['back_up_p'] = 'Do góry...';
$lang['no_entries'] = 'brak wpisów';
$lang['could_not_det_base_dn'] = 'Nie mo¿na okre¶liæ bazowego DN';
$lang['reasons_for_error']='To mog³o zdarzyæ siê z kilku powodów, z których najbardziej prawdopodobne to:';
$lang['yes']='Tak';
$lang['no']='Nie';
$lang['go']='Id¼';
$lang['delete']='Usuñ';
$lang['back']='Powrót';
$lang['object']='obiekt';
$lang['delete_all']='Usuñ wszystko';
$lang['hint'] = 'wskazówka';
$lang['bug'] = 'b³±d (bug)';
$lang['warning'] = 'ostrze¿enie';
$lang['light'] = '¿arówka'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Dalej &gt;&gt;';
$lang['no_blowfish_secret'] = 'phpLDAPadmin nie mo¿e bezpiecznie szyfrowaæ danych, poniewa¿ zmienna $blowfish_secret nie jest ustawiona w config.php. Nale¿y wyedytowaæ config.php i wpisaæ jaki¶ ³añcuch znaków do zmiennej $blowfish_secret';
$lang['jpeg_dir_not_writable'] = 'Proszê ustawiæ zmienn± $jpeg_temp_dir w config.php na katalog z mo¿liwo¶ci± zapisu plików';
$lang['jpeg_dir_not_writable_error'] = 'Nie mo¿na zapisaæ do katalogu $jpeg_temp_dir %s. Sprawd¼ proszê czy Twój serwer mo¿e zapisywaæ pliki w tym katalogu.';
$lang['jpeg_unable_toget'] = 'Nie mo¿na pobraæ danych jpeg z serwera LDAP dla atrybutu %s.'; 
$lang['jpeg_delete'] = 'Usuñ zdjêcie';

// Add value form
$lang['add_new'] = 'Dodaj';
$lang['value_to'] = 'warto¶æ do';
$lang['distinguished_name'] = 'Wyró¿niona Nazwa (DN)';
$lang['current_list_of'] = 'Aktualna lista';
$lang['values_for_attribute'] = 'warto¶ci dla atrybutu';
$lang['inappropriate_matching_note'] = 'Uwaga: Je¶li nie ustawisz regu³y EQUALITY dla tego atrybutu na Twoim serwerze LDAP otrzymasz b³±d "niew³a¶ciwe dopasowanie (inappropriate matching)"';
$lang['enter_value_to_add'] = 'Wprowad¼ warto¶æ, któr± chcesz dodaæ:';
$lang['new_required_attrs_note'] = 'Uwaga: mo¿e byæ wymagane wprowadzenie nowych atrybutów wymaganych przez tê/e klasê/y obiektu';
$lang['syntax'] = 'Sk³adnia';

//copy.php
$lang['copy_server_read_only'] = 'Nie mo¿esz dokonaæ modyfikacji dopóki serwer jest w trybie tylko-do-odczytu';
$lang['copy_dest_dn_blank'] = 'Nie wype³niono docelowej DN.';
$lang['copy_dest_already_exists'] = 'Docelowy wpis (%s) ju¿ istnieje.';
$lang['copy_dest_container_does_not_exist'] = 'Docelowy kontener (%s) nie istnieje.';
$lang['copy_source_dest_dn_same'] = '¬ród³owa i docelowa DN s± takie same.';
$lang['copy_copying'] = 'Kopiowanie ';
$lang['copy_recursive_copy_progress'] = 'Postêp kopiowania rekursywnego';
$lang['copy_building_snapshot'] = 'Budowanie migawki (snapshot) drzewa do skopiowania... ';
$lang['copy_successful_like_to'] = 'Kopiowanie zakoñczone pomy¶lnie. Czy chcesz ';
$lang['copy_view_new_entry'] = 'zobaczyæ nowy wpis ';
$lang['copy_failed'] = 'B³±d podczas kopiowania DN: ';

//edit.php
$lang['missing_template_file'] = 'Uwaga: brak pliku szablonu, ';
$lang['using_default'] = 'U¿ywam domy¶lnego.';
$lang['template'] = 'Szablon';
$lang['must_choose_template'] = 'Musisz wybraæ szablon';
$lang['invalid_template'] = '%s nie jest prawid³owym szablonem';
$lang['using_template'] = 'wykorzystuj±c szablon';
$lang['go_to_dn'] = 'Id¼ do %s';
$lang['structural_object_class_cannot_remove'] = 'To jest strukturalna klasa obiektu i nie mo¿e zostaæ usuniêta.';
$lang['structural'] = 'strukturalna';

//copy_form.php
$lang['copyf_title_copy'] = 'Kopiuj ';
$lang['copyf_to_new_object'] = 'do nowego obiektu';
$lang['copyf_dest_dn'] = 'Docelowa DN';
$lang['copyf_dest_dn_tooltip'] = 'Pe³na DN nowego wpisu do utworzenia poprzez skopiowanie wpisu ¼ród³owego';
$lang['copyf_dest_server'] = 'Docelowy serwer';
$lang['copyf_note'] = 'Wskazówka: Kopiowanie pomiêdzy ró¿nymi serwerami dzia³a wtedy, gdy nie wystêpuje naruszenie schematów';
$lang['copyf_recursive_copy'] = 'Rekursywne kopiowanie wszystkich potomnych obiektów';
$lang['recursive_copy'] = 'Kopia rekursywna';
$lang['filter'] = 'Filtr';
$lang['filter_tooltip'] = 'Podczas rekursywnego kopiowania, kopiowane s± tylko wpisy pasuj±ce do filtra';
$lang['delete_after_copy'] = 'Usuñ po skopiowaniu (przenie¶):';
$lang['delete_after_copy_warn'] = 'Upewnij siê, ¿e ustawienia filtra (powy¿ej) pozwalaj± na wybranie wszystkich rekordów podrzêdnych.'; 
     
//create.php
$lang['create_required_attribute'] = 'Brak warto¶ci dla wymaganego atrybutu (%s).';
$lang['redirecting'] = 'Przekierowujê';
$lang['here'] = 'tutaj';
$lang['create_could_not_add'] = 'Nie mo¿na dodaæ obiektu do serwera LDAP.';

//create_form.php
$lang['createf_create_object'] = 'Utwórz obiekt';
$lang['createf_choose_temp'] = 'Wybierz szablon';
$lang['createf_select_temp'] = 'Wybierz szablon dla procesu tworzenia';
$lang['save_as_file'] = 'Zapisz jako';
$lang['rdn_field_blank'] = 'Pozostawi³e¶/a¶ puste pole RDN.';
$lang['container_does_not_exist'] = 'Kontener który okre¶li³e¶/a¶ (%s) nie istnieje. Spróbuj ponownie.';
$lang['no_objectclasses_selected'] = 'Nie wybra³e¶/a¶ ¿adnych Klas Obiektu dla tego obiektu. Wróæ proszê i zrób to.';
$lang['hint_structural_oclass'] = 'Wskazówka: Musisz wybraæ dok³adnie jedn± strukturaln± klasê obiektu (wyró¿nion± pogrubieniem)';
$lang['template_restricted'] = 'Ten szablon nie jest dostêpny w tym kontenerze'; // 'This template is not allowed in this container.';
$lang['template_invalid'] = 'Ten szablon zosta³ zablokowany, prawdopodobnie z powodu brakuj±cego schamatu lub brakuj±cych pól szablonu XML.'; // 'This template has been disabled, possibly due to missing schema or missing template XML fields.';
	      
//creation_template.php
$lang['ctemplate_on_server'] = 'Na serwerze';
$lang['ctemplate_no_template'] = 'Brak okre¶lenia szablonu w zmiennych POST.';
$lang['template_not_readable'] = 'Twoja konfiguracja okre¶la obs³ugê "%s" dla tego szablonu, ale tego pliku nie da siê odczytaæ, poniewa¿ uprawnienia s± zbyt restrykcyjne.';
$lang['template_does_not_exist'] = 'Twoja konfiguracja okre¶la obs³ugê "%s" dla tego szablonu, ale pliku obs³ugi nie ma w katalogu templates/creation.';
$lang['create_step1'] = 'Krok 1 z 2: Nazwa i klasa/y obiektu';
$lang['create_step2'] = 'Krok 2 z 2: Okre¶lenie atrybutów i warto¶ci';
$lang['relative_distinguished_name'] = 'Relatywna Wyró¿niona Nazwa (RDN)';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(przyk³ad: cn=MyNewPerson)';
$lang['container'] = 'Kontener';

// search.php
$lang['you_have_not_logged_into_server'] = 'Nie zalogowa³e¶/a¶ siê jeszcze do wybranego serwera, wiêc nie mo¿esz go przeszukiwaæ.';
$lang['click_to_go_to_login_form'] = 'Kliknij tutaj aby przej¶æ do formularza logowania';
$lang['unrecognized_criteria_option'] = 'Nierozpoznane kryterium opcji: ';
$lang['if_you_want_to_add_criteria'] = 'Je¶li chcesz dodaæ w³asne kryteria do listy, zmodyfikuj plik search.php aby to obs³u¿yæ.';
$lang['entries_found'] = 'Znaleziono wpisów: ';
$lang['filter_performed'] = 'Zastosowano filtr: ';
$lang['search_duration'] = 'Wyszukiwanie wykonane przez phpLDAPadmin w';
$lang['seconds'] = 'sekund(y)';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Przeszukiwany zakres';
$lang['scope_sub'] = 'Sub (ca³e poddrzewo)';
$lang['scope_one'] = 'One (jeden poziom poni¿ej bazowej)';
$lang['scope_base'] = 'Base (tylko bazowa dn)';
$lang['standard_ldap_search_filter'] = 'Standardowy filtr dla LDAP. Na przyk³ad: (&(sn=Kowalski)(givenname=Jan))';
$lang['search_filter'] = 'Filtr wyszukiwania';
$lang['list_of_attrs_to_display_in_results'] = 'Lista atrybutów do wy¶wietlenia rezultatów (rozdzielona przecinkami)';

// search_form_simple.php
$lang['starts with'] = 'zaczyna siê od';
$lang['ends with'] = 'koñczy siê na';
$lang['sounds like'] = 'brzmi jak';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Nie mo¿na uzyskaæ informacji od serwera LDAP. Mo¿e to byæ spowodowane <a href="http://bugs.php.net/bug.php?id=29587">b³êdem</a> w Twojej wersji PHP albo na przyk³ad tym, ¿e Twój serwer LDAP posiada listê kontroli dostêpu nie zezwalaj±c± na pobranie RootDSE klientom LDAP';
$lang['server_info_for'] = 'Informacja o serwerze: ';
$lang['server_reports_following'] = 'Serwer zwróci³ nastêpuj±ce informacje o sobie';
$lang['nothing_to_report'] = 'Ten serwer nie chce nic powiedzieæ o sobie :).';

//update.php
$lang['update_array_malformed'] = 'tablica modyfikacji (update_array) jest zniekszta³cona. To mo¿e byæ b³±d (bug) w phpLDAPadmin. Proszê to zg³osiæ.';
$lang['could_not_perform_ldap_modify'] = 'Nie mo¿na wykonaæ operacji modyfikacji (ldap_modify).';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Czy chcesz dokonaæ tych zmian ?';
$lang['attribute'] = 'Atrybuty';
$lang['old_value'] = 'Stara warto¶æ';
$lang['new_value'] = 'Nowa warto¶æ';
$lang['attr_deleted'] = '[atrybut usuniêty]';
$lang['commit'] = 'Zatwierd¼';
$lang['cancel'] = 'Anuluj';
$lang['you_made_no_changes'] = 'Nie dokonano ¿adnych zmian';
$lang['go_back'] = 'Powrót';
$lang['unable_create_samba_pass'] = 'Nie mo¿na utworzyæ has³a dla samby. Sprawd¼ proszê swoj± konfiguracjê w template_config.php'; 

// welcome.php
$lang['welcome_note'] = 'U¿yj menu z lewej strony do nawigacji';
$lang['credits'] = 'Lista p³ac';
$lang['changelog'] = 'Historia zmian';
$lang['documentation'] = 'Dokumentacja'; // 'Documentation';
$lang['donate'] = 'Wesprzyj projekt';
$lang['pla_logo'] = 'phpLDAPadmin logo';
     
// Donate.php
$lang['donation_instructions'] = 'Aby wesprzeæ projekt phpLDAPadmin skorzystaj z jednego z przycisków PayPal umieszczonych poni¿ej'; 
$lang['donate_amount'] = 'Wesprzyj kwot± %s'; 

$lang['purge_cache'] = 'Wyczy¶æ cache';
$lang['no_cache_to_purge'] = 'Nie ma czego czy¶ciæ.';
$lang['done_purging_caches'] = 'Wyczyszczono %s bajtów pamiêci podrêcznej (cache).';
$lang['purge_cache_tooltip'] = 'Czy¶ci wszystkie dane podrêczne (cache) w phpLDAPadmin, ³±cznie ze schematami serwera';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Niebezpieczna nazwa pliku: ';
$lang['no_such_file'] = 'Nie znaleziono pliku: ';

//function.php
$lang['auto_update_not_setup'] = 'Zezwoli³e¶/a¶ na automatyczne nadawanie uid (auto_uid_numbers) dla <b>%s</b> w konfiguracji, ale nie okre¶li³e¶/a¶ mechanizmu (auto_uid_number_mechanism). Proszê skorygowaæ ten problem.'; 
$lang['uidpool_not_set'] = 'Okre¶li³e¶/a¶ mechanizm autonumerowania uid "auto_uid_number_mechanism" jako "uidpool" w konfiguracji Twojego serwera <b>%s</b>, lecz nie okre¶li³e¶/a¶ audo_uid_number_uid_pool_dn. Proszê okre¶l to zanim przejdziesz dalej.';
$lang['uidpool_not_exist'] = 'Wygl±da na to, ¿e uidPool, któr± okre¶li³e¶/a¶ w Twojej konfiguracji ("%s") nie istnieje.';
$lang['specified_uidpool'] = 'Okre¶li³e¶/a¶ "auto_uid_number_mechanism" jako "search" w konfiguracji Twojego serwera <b>%s</b>, ale nie okre¶li³e¶/a¶ bazy "auto_uid_number_search_base". Zrób to zanim przejdziesz dalej.';
$lang['auto_uid_invalid_credential'] = 'Nie mo¿na pod³±czyæ do <b>%s</b> z podan± to¿samo¶ci± auto_uid. Proszê sprawdziæ swój plik konfiguracyjny.';
$lang['bad_auto_uid_search_base'] = 'W Twojej konfiguracji phpLDAPadmin okre¶lona jest nieprawid³owa warto¶æ auto_uid_search_base dla serwera %s';
$lang['auto_uid_invalid_value'] = 'Okre¶li³e¶/a¶ b³êdn± warto¶æ dla auto_uid_number_mechanism ("%s") w konfiguracji. Tylko "uidpool" i "search" s± poprawne. Proszê skorygowaæ ten problem.';
$lang['error_auth_type_config'] = 'B³±d: Masz b³±d w pliku konfiguracji. Trzy mo¿liwe warto¶ci dla auth_type w sekcji $servers to \'session\', \'cookie\' oraz \'config\'.  Ty wpisa³e¶/a¶ \'%s\', co jest niedozwolone. ';
$lang['unique_attrs_invalid_credential'] = 'Nie mo¿na pod³±czyæ do <b>%s</b> z podan± to¿samo¶ci± unique_attrs. Proszê sprawdziæ swój plik konfiguracyjny.';
$lang['unique_attr_failed'] = 'Próba dodania <b>%s</b> (<i>%s</i>) do <br><b>%s</b><br> jest NIEDOZWOLONA. Ten atrybut/warto¶æ nale¿y do innego wpisu.<p>Je¶li chcesz, mo¿esz <a href=\'%s\'>poszukaæ</a> tego wpisu.';
$lang['php_install_not_supports_tls'] = 'Twoja instalacja PHP nie wspiera TLS.';
$lang['could_not_start_tls'] = 'Nie mo¿na uruchomiæ TLS. Proszê sprawdziæ konfiguracjê serwera LDAP.';
$lang['could_not_bind_anon'] = 'Nie mo¿na anonimowo pod³±czyæ do serwera.';
$lang['could_not_bind'] = 'Nie mo¿na pod³±czyæ siê do serwera LDAP.';
$lang['anonymous_bind'] = 'Pod³±czenie anonimowe';
$lang['bad_user_name_or_password'] = 'Z³a nazwa u¿ytkownika lub has³o. Spróbuj ponownie.';
$lang['successfully_logged_in_to_server'] = 'Pomy¶lnie zalogowano do serwera <b>%s</b>';
$lang['could_not_set_cookie'] = 'Nie mo¿na ustawiæ ciasteczka (cookie).';
$lang['ldap_said'] = 'LDAP odpowiedzia³: %s';
$lang['ferror_error'] = 'B³±d';
$lang['fbrowse'] = 'przegl±daj';
$lang['delete_photo'] = 'Usuñ fotografiê';
$lang['install_not_support_ext_des'] = 'Twoja systemowa biblioteka crypt nie wspiera rozszerzonego szyfrowania DES'; 
$lang['install_not_support_blowfish'] = 'Twoja systemowa biblioteka crypt nie wspiera szyfrowania blowfish.';
$lang['install_not_support_md5crypt'] = 'Twoja systemowa biblioteka crypt nie wspiera szyfrowania md5crypt.';
$lang['install_no_mash'] = 'Twoja instalacja PHP nie posiada funkcji mhash(). Nie mogê tworzyæ haszy SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto zawiera b³êdy<br />';
$lang['ferror_number'] = 'B³±d numer: %s (%s)';
$lang['ferror_discription'] = 'Opis: %s<br /><br />';
$lang['ferror_number_short'] = 'B³±d numer: %s<br /><br />';
$lang['ferror_discription_short'] = 'Opis: (brak dostêpnego opisu)<br />';
$lang['ferror_submit_bug'] = 'Czy jest to b³±d w phpLDAPadmin ? Je¶li tak, proszê go <a href=\'%s\'>zg³osiæ</a>.';
$lang['ferror_unrecognized_num'] = 'Nierozpoznany numer b³êdu: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Znalaz³e¶ b³±d w phpLDAPadmin (nie krytyczny) !</b></td></tr><tr><td>B³±d:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Plik:</td>
             <td><b>%s</b> linia <b>%s</b>, wywo³ane z <b>%s</b></td></tr><tr><td>Wersje:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Serwer Web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Sprawd¼ proszê czy ten b³±d nie zosta³ ju¿ zg³oszony tutaj</a>.</center></td></tr>
	     <tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Je¶li nie zosta³ jeszcze zg³oszony, to mo¿esz go zg³osiæ tutaj</a>.</center></td></tr>
	     </table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Gratulacje ! Znalaz³e¶ b³±d w phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>B³±d:</td><td><b>%s</b></td></tr>
	     <tr><td>Poziom:</td><td><b>%s</b></td></tr>
	     <tr><td>Plik:</td><td><b>%s</b></td></tr>
	     <tr><td>Linia:</td><td><b>%s</b></td></tr>
	     <tr><td>Wywo³ane z:</td><td><b>%s</b></td></tr>
	     <tr><td>Wersja PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Wersja PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Serwer Web:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
         Proszê zg³o¶ ten b³±d klikaj±c poni¿ej !';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importuj plik LDIF';
$lang['select_ldif_file'] = 'Wybierz plik LDIF:';
$lang['dont_stop_on_errors'] = 'Nie zatrzymuj siê po napotkaniu b³êdów';

//ldif_import
$lang['add_action'] = 'Dodawanie...';
$lang['delete_action'] = 'Usuwanie...';
$lang['rename_action'] = 'Zmiana nazwy...';
$lang['modify_action'] = 'Modyfikowanie...';
$lang['warning_no_ldif_version_found'] = 'Nie znaleziono numeru wersji. Przyjmujê 1.';
$lang['valid_dn_line_required'] = 'Wymagana jest poprawna linia DN.';
$lang['missing_uploaded_file'] = 'Brak wgrywanego pliku.';
$lang['no_ldif_file_specified'] = 'Nie okre¶lono pliku LDIF. Spróbuj ponownie.';
$lang['ldif_file_empty'] = 'Wgrany plik LDIF jest pusty.';
$lang['empty'] = 'pusty';
$lang['file'] = 'Plik';
$lang['number_bytes'] = '%s bajtów';
			  
$lang['failed'] = 'Nieudane';
$lang['ldif_parse_error'] = 'B³±d przetwarzania LDIF (Parse Error)';
$lang['ldif_could_not_add_object'] = 'Nie mo¿na dodaæ obiektu:';
$lang['ldif_could_not_rename_object'] = 'Nie mo¿na zmieniæ nazwy obiektu:';
$lang['ldif_could_not_delete_object'] = 'Nie mo¿na usun±æ obiektu:';
$lang['ldif_could_not_modify_object'] = 'Nie mo¿na zmodyfikowaæ obiektu:';
$lang['ldif_line_number'] = 'Linia numer:';
$lang['ldif_line'] = 'Linia:';

//delete_form
$lang['sure_permanent_delete_object']='Czy na pewno trwale usun±æ ten obiekt ?';
$lang['list_of_entries_to_be_deleted'] = 'Lista wpisów do usuniêcia:';
$lang['dn'] = 'DN';

// Exports
$lang['export_format'] = 'Format eksportu';
$lang['line_ends'] = 'Zakoñczenie linii';
$lang['must_choose_export_format'] = 'Musisz wybraæ format eksportu.';
$lang['invalid_export_format'] = 'B³êdny format eksportu';
$lang['no_exporter_found'] = 'Nie znaleziono dostêpnego eksportera.';
$lang['error_performing_search'] = 'Napotkano b³±d podczas szukania.';
$lang['showing_results_x_through_y'] = 'Pokazywanie rezultatów %s przez %s.';
$lang['searching'] = 'Szukam...';
$lang['size_limit_exceeded'] = 'Uwaga, przekroczono limit rozmiaru wyszukiwania.';
$lang['entry'] = 'Wpis';
$lang['ldif_export_for_dn'] = 'Eksport LDIF dla: %s';
$lang['generated_on_date'] = 'Wygenerowane przez phpLDAPadmin ( http://phpldapadmin.sourceforge.net/ ) na %s';
$lang['total_entries'] = '£±cznie wpisów';
$lang['dsml_export_for_dn'] = 'Eksport DSLM dla: %s';
$lang['include_system_attrs'] = 'Zawiera atrybuty systemowe';
$lang['csv_spreadsheet'] = 'CVS (arkusz)';

// logins
$lang['password_blank'] = 'Pozostawi³e¶/a¶ puste has³o.';
$lang['no_one_logged_in'] = 'Nikt nie jest zalogowany do tego serwera.';
$lang['could_not_logout'] = 'Nie mo¿na wylogowaæ.';
$lang['unknown_auth_type'] = 'Nieznany auth_type: %s';
$lang['logged_out_successfully'] = 'Pomy¶lnie wylogowano z serwera <b>%s</b>';
$lang['authenticate_to_server'] = 'Uwierzytelnienie dla serwera %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Uwaga: To po³±czenie nie jest szyfrowane.';
$lang['not_using_https'] = 'Nie u¿ywasz \'https\'. Przegl±darka bêdzie transmitowaæ informacjê logowania czystym tekstem (clear text).';
$lang['login_dn'] = 'Login DN';
$lang['user_name'] = 'Nazwa u¿ytkownika';
$lang['password'] = 'Has³o';
$lang['authenticate'] = 'Zaloguj';
$lang['login_not_allowed'] = 'Przykro mi, ale nie masz uprawnieñ aby korzystaæ z phpLDAPadmin na tym serwerze LDAP.';

// Entry browser
$lang['entry_chooser_title'] = 'Wybór wpisu';

// Index page
$lang['need_to_configure'] = 'Musisz skonfigurowaæ phpLDAPadmin. Wyedytuj plik \'config.php\' aby to zrobiæ. Przyk³ad pliku konfiguracji znajduje siê w \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Usuwanie jest niedozwolone w trybie tylko-do-odczytu.';
$lang['error_calling_mass_delete'] = 'B³±d podczas wywo³ania mass_delete.php. Brakuj±ca mass_delete w zmiennych POST.';
$lang['mass_delete_not_array'] = 'zmienna POST mass_delete nie jest w tablic±.';
$lang['mass_delete_not_enabled'] = 'Masowe usuwanie nie jest dozwolone. Odblokuj to proszê w config.php przed kontynuacj±.';
$lang['mass_deleting'] = 'Masowe usuwanie';
$lang['mass_delete_progress'] = 'Postêp usuwania na serwerze "%s"';
$lang['malformed_mass_delete_array'] = 'Zniekszta³cona tablica mass_delete.';
$lang['no_entries_to_delete'] = 'Nie wybrano ¿adnegych wpisów do usuniêcia.';
$lang['deleting_dn'] = 'Usuwanie %s';
$lang['total_entries_failed'] = '%s z %s wpisów nie zosta³o usuniêtych.';
$lang['all_entries_successful'] = 'Wszystkie wpisy pomy¶lnie usunieto.';
$lang['confirm_mass_delete'] = 'Potwierd¼ masowe usuniêcie %s wpisów na serwerze %s';
$lang['yes_delete'] = 'Tak, usuñ !';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Nie mo¿esz zmieniæ nazwy wpisu, posiadaj±cego wpisy potomne (np. operacja zmiany nazwy nie jest dozwolona na wpisach nie bêd±cych li¶cmi).';
$lang['no_rdn_change'] = 'Nie zmieni³e¶/a¶ RDN';
$lang['invalid_rdn'] = 'B³êdna warto¶æ RDN';
$lang['could_not_rename'] = 'Nie mo¿na zmieniæ nazwy wpisu';

// Password checker
$lang['passwords_match'] = 'Has³a zgodne !';
$lang['passwords_do_not_match'] = 'Has³a nie zgadzaj± siê !'; 
$lang['password_checker_tool'] = 'Narzêdzie do sprawdzania hase³';
$lang['to'] = 'Do';
				     
// Templates
$lang['using'] = 'U¿ywaj±c';
$lang['switch_to'] = 'Mo¿esz prze³±czyæ siê do ';
$lang['default_template'] = 'domy¶lnego szablonu';

// template_config
$lang['user_account'] = 'Konto U¿ytkownika (posixAccount)';
$lang['address_book_inet'] = 'Wpis Ksi±¿ki Adresowej (inetOrgPerson)';
$lang['address_book_moz'] = 'Wpis Ksi±¿ki Adresowej (mozillaOrgPerson)';
$lang['kolab_user'] = 'Wpis U¿ytkownika Kolab';
$lang['organizational_unit'] = 'Jednostka Organizacyjna';
$lang['new_organizational_unit'] = 'Nowa Jednostka Organizacyjna';
$lang['organizational_role'] = 'Rola w Organizacji';
$lang['posix_group'] = 'Grupa Posix';
$lang['samba_machine'] = 'Maszyna Samba NT';
$lang['samba3_machine'] = 'Maszyna Samba 3 NT';
$lang['samba_user'] = 'U¿ytkownik Samba';
$lang['samba3_user'] = 'U¿ytkownik Samba 3';
$lang['samba3_group'] = 'Przypisanie grupy Samba 3';
$lang['dns_entry'] = 'Wpis DNS';
$lang['simple_sec_object'] = 'Prosty obiekt bezpieczeñstwa (SSO)';
$lang['courier_mail_account'] = 'Konto Pocztowe w Courier';
$lang['courier_mail_alias'] = 'Alias Pocztowy w Courier';
$lang['ldap_alias'] = 'Alias w LDAP';
$lang['sendmail_cluster'] = 'Klaster Sendmail';
$lang['sendmail_domain'] = 'Domena Sendmail';
$lang['sendmail_alias'] = 'Alias Sendmail';
$lang['sendmail_virt_dom'] = 'Wirtualna Domena Sendmail';
$lang['sendmail_virt_users'] = 'Wirtualni U¿ytkownicy Sendmail';
$lang['sendmail_relays'] = 'Sendmail Relays';
$lang['custom'] = 'Ogólne';
$lang['samba_domain_name'] = 'Moja nazwa domeny w Samba';
$lang['administrators'] = 'Administratorzy';
$lang['users'] = 'U¿ytkownicy';
$lang['guests'] = 'Go¶cie';
$lang['power_users'] = 'U¿ytkownicy zaawansowani';
$lang['account_ops'] = 'Operatorzy kont';
$lang['server_ops'] = 'Operatorzy serwera';
$lang['print_ops'] = 'Operatorzy drukowania';
$lang['backup_ops'] = 'Operatorzy archiwizacji danych';
$lang['replicator'] = 'Replikator';
$lang['unable_smb_passwords'] = ' Nie mo¿na utworzyæ hase³ Samba. Proszê sprawdziæ konfiguracjê w template_config.php';
$lang['err_smb_conf'] = 'B³±d: masz b³±d w konfiguracji samby';
$lang['err_smb_no_name_sid'] = 'B³±d: musisz wprowadziæ nazwê oraz sid dla Twojej domeny samby.';
$lang['err_smb_no_name'] = 'B³±d: brak nazwy dla domeny samby.';
$lang['err_smb_no_sid'] = 'B³±d: brak sid dla domeny samby';

// Samba Account Template
$lang['samba_account'] = 'Konto Samba';
$lang['samba_account_lcase'] = 'konto samba';

// New User (Posix) Account
$lang['t_new_user_account'] = 'Nowe konto u¿ytkownika';
$lang['t_hint_customize'] = 'Wskazówka: Aby dostosowaæ ten szablon, wyedytuj plik templates/creation/new_user_template.php';
$lang['t_name'] = 'Nazwa/Nazwisko';
$lang['t_first_name'] = 'Imiê';
$lang['t_last_name'] = 'Nazwisko';
$lang['t_first'] = 'imiê';
$lang['t_last'] = 'nazwisko';
$lang['t_state'] = 'Stan';
$lang['t_common_name'] = 'Nazwa';
$lang['t_user_name'] = 'Nazwa u¿ytkownika';
$lang['t_password'] = 'Has³o';
$lang['t_encryption'] = 'Szyfrowanie';
$lang['t_login_shell'] = 'Pow³oka (shell)';
$lang['t_home_dir'] = 'Katalog domowy';
$lang['t_uid_number'] = 'Numer UID';
$lang['t_auto_det'] = '(automatycznie okre¶lony)';
$lang['t_group'] = 'Grupa';
$lang['t_gid_number'] = 'Numer GID';
$lang['t_uid'] = 'ID U¿ytkownika';
$lang['t_err_passwords'] = 'Has³a nie zgadzaj± siê. Wróc i spróbuj ponownie.';
$lang['t_err_field_blank'] = 'Nie mo¿esz pozostawiæ pustego pola %s. Wróæ i spróbuj ponownie.';
$lang['t_err_field_num'] = 'Pole %s mo¿e zawieraæ tylko warto¶ci numeryczne. Wróæ i spróbuj ponownie.';
$lang['t_err_bad_container'] = 'Kontener który wybra³e¶/a¶ (%s) nie istnieje. Wróæ i spróbuj ponownie.';
$lang['t_confirm_account_creation'] = 'Potwierd¼ utworzenie konta';
$lang['t_secret'] = '[tajne]';
$lang['t_create_account'] = 'Utwórz konto';
$lang['t_verify'] = 'Weryfikuj';

// New Group (Posix)
$lang['t_new_posixgroup'] = 'Nowa Grupa Posix';

// New Address Template
$lang['t_new_address'] = 'Nowy wpis w Ksi±¿ce Adresowej';
$lang['t_organization'] = 'Organizacja';
$lang['t_address'] = 'Adres';
$lang['t_city'] = 'Miasto';
$lang['t_postal_code'] = 'Kod pocztowy';
$lang['t_street'] = 'Ulica';
$lang['t_work_phone'] = 'Telefon s³u¿bowy';
$lang['t_fax'] = 'Fax';
$lang['t_mobile'] = 'Telefon komórkowy';
$lang['t_email'] = 'E-mail';
$lang['t_container'] = 'Kontener';
$lang['t_err_cn_blank'] = 'Nie mo¿esz pozostawiæ pustego pola Nazwa. Wróæ i spróbuj ponownie.';
$lang['t_confim_creation'] = 'Potwierd¼ utworzenie wpisu:';
$lang['t_create_address'] = 'Utwórz adres';

// default template
$lang['t_check_pass'] = 'Sprawd¼ has³o';
$lang['t_auto_submit'] = '(Automatycznie wyliczane przy wys³aniu)'; // '(Auto evalutated on submission.)';

// compare form
$lang['compare'] = 'Porównaj';
$lang['comparing'] = 'Porównujê nastêpuj±ce DN';
$lang['compare_dn'] = 'Porównaj inny DN z';
$lang['with'] = 'z';
$lang['compf_source_dn'] = '¬ród³owa DN';
$lang['compf_dn_tooltip'] = 'Prównaj t± DN z inn±';
$lang['switch_entry'] = 'Zamieñ wpisy';
$lang['no_value'] = 'Brak warto¶ci';
$lang['compare_with'] = 'Porównaj z innym wpisem';
$lang['need_oclass'] = 'Musisz posiadaæ jedn± z nastêpuj±cych klas obiektów, aby dodaæ ten atrybut %s ';

// Time out page
$lang['session_timed_out_1'] = 'Twoja sesja wyga¶nie po'; 
$lang['session_timed_out_2'] = 'min. nieaktywno¶ci. Zostaniesz automatycznie wylogowany/a.';
$lang['log_back_in'] = 'Aby siê zalogowaæ ponownie kliknij w nastêpuj±cy link:';
$lang['session_timed_out_tree'] = '(Sesja wygas³a. Automatycznie wylogowano)';
$lang['timeout_at'] = 'Brak aktywno¶ci wyloguje Ciê o %s';
?>
