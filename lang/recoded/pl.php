<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/pl.php,v 1.8 2004/04/26 19:21:40 i18phpldapadmin Exp $

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

/* $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/pl.php,v 1.8 2004/04/26 19:21:40 i18phpldapadmin Exp $
 * initial translation from Piotr (DrFugazi) Tarnowski on Version 0.9.3
 */
// Based on en.php version 1.64

// Search form
$lang['simple_search_form_str'] = 'Wyszukiwanie proste';
$lang['advanced_search_form_str'] = 'Wyszukiwanie zaawansowane';
$lang['server'] = 'Serwer';
$lang['search_for_entries_whose'] = 'Szukaj wpisów w których';
$lang['base_dn'] = 'Bazowa DN';
$lang['search_scope'] = 'Zakres przeszukiwania';
$lang['show_attributes'] = 'Pokaż atrybuty';
$lang['Search'] = 'Szukaj';
$lang['equals'] = 'równa się';
$lang['contains'] = 'zawiera';
$lang['predefined_search_str'] = 'Wybierz predefiniowane wyszukiwanie';
$lang['predefined_searches'] = 'Predefiniowane wyszukiwania';
$lang['no_predefined_queries'] = 'Brak zdefiniowanych zapytań w config.php.';

// Tree browser
$lang['request_new_feature'] = 'Zgłoś zapotrzebowanie na nową funkcjonalność';
$lang['report_bug'] = 'Zgłoś błąd (report a bug)';
$lang['schema'] = 'schemat';
$lang['search'] = 'szukaj';
$lang['create'] = 'utwórz';
$lang['info'] = 'info';
$lang['import'] = 'import';
$lang['refresh'] = 'odśwież';
$lang['logout'] = 'wyloguj';
$lang['create_new'] = 'Utwórz nowy';
$lang['view_schema_for'] = 'Pokaż schemat dla';
$lang['refresh_expanded_containers'] = 'Odśwież wszystkie otwarte kontenery dla';
$lang['create_new_entry_on'] = 'Utwórz nowy wpis na';
$lang['new'] = 'nowy';
$lang['view_server_info'] = 'Pokaż informacje o serwerze';
$lang['import_from_ldif'] = 'Importuj wpisy z pliku LDIF';
$lang['logout_of_this_server'] = 'Wyloguj z tego serwera';
$lang['logged_in_as'] = 'Zalogowany/a jako: ';
$lang['read_only'] = 'tylko-do-odczytu';
$lang['read_only_tooltip'] = 'Ten atrybut został oznaczony przez administratora phpLDAPadmin jako tylko-do-odczytu';
$lang['could_not_determine_root'] = 'Nie można ustalić korzenia Twojego drzewa LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Wygląda, że serwer LDAP jest skonfigurowany tak, aby nie ujawniać swojego korzenia.';
$lang['please_specify_in_config'] = 'Proszę określić to w pliku config.php';
$lang['create_new_entry_in'] = 'Utwórz nowy wpis w';
$lang['login_link'] = 'Logowanie...';
$lang['login'] = 'login';

// Entry display
$lang['delete_this_entry'] = 'Usuń ten wpis';
$lang['delete_this_entry_tooltip'] = 'Będziesz poproszony/a o potwierdzenie tej decyzji';
$lang['copy_this_entry'] = 'Skopiuj ten wpis';
$lang['copy_this_entry_tooltip'] = 'Skopiuj ten obiekt do innej lokalizacji, nowej DN, lub do innego serwera';
$lang['export'] = 'Eksportuj';
$lang['export_tooltip'] = 'Zapisz zrzut tego obiektu';
$lang['export_subtree_tooltip'] = 'Zapisz zrzut tego obiektu i wszystkich potomnych';
$lang['export_subtree'] = 'Eksportuj całe poddrzewo';
$lang['create_a_child_entry'] = 'Utwórz wpis potomny';
$lang['rename_entry'] = 'Zmień nazwę wpisu';
$lang['rename'] = 'Zmień nazwę';
$lang['add'] = 'Dodaj';
$lang['view'] = 'Pokaż';
$lang['view_one_child'] = 'Pokaż 1 wpis potomny';
$lang['view_children'] = 'Pokaż %s wpisy/ów potomne/ych';
$lang['add_new_attribute'] = 'Dodaj nowy atrybut';
$lang['add_new_objectclass'] = 'Dodaj nową klasę obiektu';
$lang['hide_internal_attrs'] = 'Ukryj wewnętrzne atrybuty';
$lang['show_internal_attrs'] = 'Pokaż wewnętrzne atrybuty';
$lang['attr_name_tooltip'] = 'Kliknij aby obejrzeć definicje schematu dla atrybutu typu \'%s\'';
$lang['none'] = 'brak';
$lang['no_internal_attributes'] = 'Brak atrybutów wewnętrznych';
$lang['no_attributes'] = 'Ten wpis nie posiada atrybutów';
$lang['save_changes'] = 'Zapisz zmiany';
$lang['add_value'] = 'dodaj wartość';
$lang['add_value_tooltip'] = 'Dodaj dodatkową wartość do atrybutu \'%s\'';
$lang['refresh_entry'] = 'Odśwież';
$lang['refresh_this_entry'] = 'Odśwież ten wpis';
$lang['delete_hint'] = 'Wskazówka: Aby skasować atrybut, wyczyść pole tekstowe i kliknij zapisz.';
$lang['attr_schema_hint'] = 'Wskazówka: Aby zobaczyć schemat dla atrybutu, kliknij na nazwie atrybutu.';
$lang['attrs_modified'] = 'Niektóre atrybuty (%s) zostały zmodyfikowane i są wyróżnione poniżej.';
$lang['attr_modified'] = 'Atrybut (%s) został zmodyfikowany i jest wyróżniony poniżej.';
$lang['viewing_read_only'] = 'Oglądanie wpisu w trybie tylko-do-odczytu.';
$lang['no_new_attrs_available'] = 'brak nowych atrybutów dostępnych dla tego wpisu';
$lang['no_new_binary_attrs_available'] = 'brak nowych atrybutów binarnych dla tego wpisu';
$lang['binary_value'] = 'Wartość binarna';
$lang['add_new_binary_attr'] = 'Dodaj nowy atrybut binarny';
$lang['alias_for'] = 'Uwaga: \'%s\' jest aliasem dla \'%s\'';
$lang['download_value'] = 'pobierz (download) wartość';
$lang['delete_attribute'] = 'usuń atrybut';
$lang['true'] = 'prawda';
$lang['false'] = 'fałsz';
$lang['none_remove_value'] = 'brak, usuń wartość';
$lang['really_delete_attribute'] = 'Definitywnie usuń atrybut';
$lang['add_new_value'] = 'Dodaj nową wartość';

// Schema browser
$lang['the_following_objectclasses'] = 'Następujące klasy obiektu są wspierane przez ten serwer LDAP.';
$lang['the_following_attributes'] = 'Następujące typy atrybutów są wspierane przez ten serwer LDAP.';
$lang['the_following_matching'] = 'Następujące reguły dopasowania są wspierane przez ten serwer LDAP.';
$lang['the_following_syntaxes'] = 'Następujące składnie są wspierane przez ten serwer LDAP.';
$lang['schema_retrieve_error_1']='Serwer nie wspiera w pełni protokołu LDAP.';
$lang['schema_retrieve_error_2']='Twoja wersja PHP niepoprawnie wykonuje zapytanie.';
$lang['schema_retrieve_error_3']='Lub w ostateczności, phpLDAPadmin nie wie jak uzyskać schemat dla Twojego serwera.';
$lang['jump_to_objectclass'] = 'Skocz do klasy obiektu';
$lang['jump_to_attr'] = 'Skocz do typu atrybutu';
$lang['jump_to_matching_rule'] = 'Skocz do reguły dopasowania';
$lang['schema_for_server'] = 'Schemat dla serwera';
$lang['required_attrs'] = 'Wymagane atrybuty';
$lang['optional_attrs'] = 'Opcjonalne atrybuty';
$lang['optional_binary_attrs'] = 'Opcjonalne atrybuty binarne';
$lang['OID'] = 'OID';
$lang['aliases']='Aliasy';
$lang['desc'] = 'Opis';
$lang['no_description']='brak opisu';
$lang['name'] = 'Nazwa';
$lang['equality']='Równość';
$lang['is_obsolete'] = 'Ta klasa obiektu jest przestarzała';
$lang['inherits'] = 'Dziedziczy z';
$lang['inherited_from']='dziedziczone z';
$lang['parent_to'] = 'Nadrzędny dla';
$lang['jump_to_this_oclass'] = 'Skocz do definicji klasy obiektu';
$lang['matching_rule_oid'] = 'OID reguły dopasowania';
$lang['syntax_oid'] = 'OID składni';
$lang['not_applicable'] = 'nie dotyczy';
$lang['not_specified'] = 'nie określone';
$lang['character']='znak'; 
$lang['characters']='znaki/ów';
$lang['used_by_objectclasses']='Używane przez klasy obiektu';
$lang['used_by_attributes']='Używane przez atrybuty';
$lang['maximum_length']='Maksymalna długość';
$lang['attributes']='Typy atrybutów';
$lang['syntaxes']='Składnie';
$lang['objectclasses']='Klasy Obiektu';
$lang['matchingrules']='Reguły Dopasowania';
$lang['oid']='OID';
$lang['obsolete']='Przestarzałe ';
$lang['ordering']='Uporządkowanie';
$lang['substring_rule']='Reguła podciągu (Substring Rule)';
$lang['single_valued']='Pojedynczo ceniona (Single Valued)';
$lang['collective']='Zbiorcza ';
$lang['user_modification']='Modyfikacja użytkownika';
$lang['usage']='Użycie';
$lang['could_not_retrieve_schema_from']='Nie można uzyskać schematu z';
$lang['type']='Typ';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Wpis %s został pomyślnie usunięty.';
$lang['you_must_specify_a_dn'] = 'Musisz określić DN';
$lang['could_not_delete_entry'] = 'Nie można usunąć wpisu: %s';
$lang['no_such_entry'] = 'Nie ma takiego wpisu: %s';
$lang['delete_dn'] = 'Usuń %s';
$lang['permanently_delete_children'] = 'Czy trwale usunąć także wpisy potomne ?';
$lang['entry_is_root_sub_tree'] = 'Ten wpis jest korzeniem poddrzewa zawierającego %s wpisów.';
$lang['view_entries'] = 'pokaż wpisy';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin może rekursywnie usunąć ten wpis i wszystkie jego %s wpisy/ów potomne/ych. Sprawdź poniższą listę wpisów przeznaczonych do usunięcia.<br /> Czy na pewno chcesz to zrobić ?';
$lang['confirm_recursive_delete_note'] = 'Uwaga: ta operacja jest potencjalnie bardzo niebezpieczna i wykonujesz ją na własne ryzyko. Ta akcja nie może zostać cofnięta. Weź pod uwagę aliasy, owołania i inne rzeczy, które mogą spowodować problemy.';
$lang['delete_all_x_objects'] = 'Usuń wszystkie %s obiekty/ów';
$lang['recursive_delete_progress'] = 'Postęp rekursywnego usuwania';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Wpis %s oraz poddrzewo zostały pomyślnie usunięte.';
$lang['failed_to_delete_entry'] = 'Błąd podczas usuwania wpisu %s';

// Deleting attributes
$lang['attr_is_read_only'] = 'Atrybut "%s" jest oznaczony jako tylko-do-odczytu w konfiguracji phpLDAPadmin.';
$lang['no_attr_specified'] = 'Nie określono nazwy atrybutu.';
$lang['no_dn_specified'] = 'Nie określono DN';

// Adding attributes
$lang['left_attr_blank'] = 'Pozostawiłeś/aś pustą wartość atrybutu. Proszę wrócić i spróbować ponownie.';
$lang['failed_to_add_attr'] = 'Błąd podczas dodawania atrybutu.';

// Updating values
$lang['modification_successful'] = 'Modyfikacja zakończona pomyślnie.';
$lang['change_password_new_login'] = 'Jeśli zmieniłeś/aś hasło, musisz się zalogować ponownie z nowym hasłem.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nowe atrybuty wymagane';
$lang['requires_to_add'] = 'Ta akcja wymaga, abyś dodał/a';
$lang['new_attributes'] = 'nowe atrybuty';
$lang['new_required_attrs_instructions'] = 'Instrukcja: Aby dodać tę klasę obiektu do tego wpisu, musisz określić';
$lang['that_this_oclass_requires'] = 'co ta klasa obiektu wymaga. Możesz zrobić to w tym formularzu.';
$lang['add_oclass_and_attrs'] = 'Dodaj klasę obiektu i atrybuty';

// General
$lang['chooser_link_tooltip'] = 'Kliknij aby wywołać okno i wybrać wpis (DN) graficznie';
$lang['no_updates_in_read_only_mode'] = 'Nie możesz wykonać modyfikacji dopóki serwer jest w trybie tylko-do-odczytu';
$lang['bad_server_id'] = 'Zły identyfikator (id) serwera';
$lang['not_enough_login_info'] = 'Brak wystarczających informacji aby zalogować się do serwera. Proszę sprawdzić konfigurację.';
$lang['could_not_connect'] = 'Nie można podłączyć się do serwera LDAP.';
$lang['could_not_connect_to_host_on_port'] = 'Nie można podłączyć się do "%s" na port "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Nie można dokonać operacji ldap_mod_add.';
$lang['bad_server_id_underline'] = 'Zły server_id: ';
$lang['success'] = 'Sukces';
$lang['server_colon_pare'] = 'Serwer: ';
$lang['look_in'] = 'Szukam w: ';
$lang['missing_server_id_in_query_string'] = 'Nie określono ID serwera w zapytaniu !';
$lang['missing_dn_in_query_string'] = 'Nie określono DN w zapytaniu !';
$lang['back_up_p'] = 'Do góry...';
$lang['no_entries'] = 'brak wpisów';
$lang['not_logged_in'] = 'Nie zalogowany/a';
$lang['could_not_det_base_dn'] = 'Nie można określić bazowego DN';
$lang['please_report_this_as_a_bug']='Proszę zgłosić to jako błąd.';
$lang['reasons_for_error']='To mogło zdarzyć się z kilku powodów, z których najbardziej prawdopodobne to:';
$lang['yes']='Tak';
$lang['no']='Nie';
$lang['go']='Idź';
$lang['delete']='Usuń';
$lang['back']='Powrót';
$lang['object']='obiekt';
$lang['delete_all']='Usuń wszystko';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'wskazówka';
$lang['bug'] = 'błąd (bug)';
$lang['warning'] = 'ostrzeżenie';
$lang['light'] = 'żarówka'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Dalej &gt;&gt;';

// Add value form
$lang['add_new'] = 'Dodaj';
$lang['value_to'] = 'wartość do';
$lang['distinguished_name'] = 'Wyróżniona Nazwa (DN)';
$lang['current_list_of'] = 'Aktualna lista';
$lang['values_for_attribute'] = 'wartości dla atrybutu';
$lang['inappropriate_matching_note'] = 'Uwaga: Jeśli nie ustawisz reguły EQUALITY dla tego atrybutu na Twoim serwerze LDAP otrzymasz błąd "niewłaściwe dopasowanie (inappropriate matching)"';
$lang['enter_value_to_add'] = 'Wprowadź wartość, którą chcesz dodać:';
$lang['new_required_attrs_note'] = 'Uwaga: może być wymagane wprowadzenie nowych atrybutów wymaganych przez tę klasę obiektu';
$lang['syntax'] = 'Składnia';

//copy.php
$lang['copy_server_read_only'] = 'Nie możesz dokonać modyfikacji dopóki serwer jest w trybie tylko-do-odczytu';
$lang['copy_dest_dn_blank'] = 'Nie wypełniono docelowej DN.';
$lang['copy_dest_already_exists'] = 'Docelowy wpis (%s) już istnieje.';
$lang['copy_dest_container_does_not_exist'] = 'Docelowy kontener (%s) nie istnieje.';
$lang['copy_source_dest_dn_same'] = 'Źródłowa i docelowa DN są takie same.';
$lang['copy_copying'] = 'Kopiowanie ';
$lang['copy_recursive_copy_progress'] = 'Postęp kopiowania rekursywnego';
$lang['copy_building_snapshot'] = 'Budowanie migawki (snapshot) drzewa do skopiowania... ';
$lang['copy_successful_like_to'] = 'Kopiowanie zakończone pomyślnie. Czy chcesz ';
$lang['copy_view_new_entry'] = 'zobaczyć nowy wpis ';
$lang['copy_failed'] = 'Błąd podczas kopiowania DN: ';

//edit.php
$lang['missing_template_file'] = 'Uwaga: brak pliku szablonu, ';
$lang['using_default'] = 'Używam domyślnego.';
$lang['template'] = 'Szablon';
$lang['must_choose_template'] = 'Musisz wybrać szablon';
$lang['invalid_template'] = '%s nie jest prawidłowym szablonem';
$lang['using_template'] = 'wykorzystując szablon';
$lang['go_to_dn'] = 'Idź do %s';

//copy_form.php
$lang['copyf_title_copy'] = 'Kopiuj ';
$lang['copyf_to_new_object'] = 'do nowego obiektu';
$lang['copyf_dest_dn'] = 'Docelowa DN';
$lang['copyf_dest_dn_tooltip'] = 'Pełna DN nowego wpisu do utworzenia poprzez skopiowanie wpisu źródłowego';
$lang['copyf_dest_server'] = 'Docelowy serwer';
$lang['copyf_note'] = 'Wskazówka: Kopiowanie pomiędzy różnymi serwerami działa wtedy, gdy nie występuje naruszenie schematów';
$lang['copyf_recursive_copy'] = 'Rekursywne kopiowanie wszystkich potomnych obiektów';
$lang['recursive_copy'] = 'Kopia rekursywna';
$lang['filter'] = 'Filtr';
$lang['filter_tooltip'] = 'Podczas rekursywnego kopiowania, kopiowane są tylko wpisy pasujące do filtra';

//create.php
$lang['create_required_attribute'] = 'Brak wartości dla wymaganego atrybutu (%s).';
$lang['redirecting'] = 'Przekierowuję';
$lang['here'] = 'tutaj';
$lang['create_could_not_add'] = 'Nie można dodać obiektu do serwera LDAP.';

//create_form.php
$lang['createf_create_object'] = 'Utwórz obiekt';
$lang['createf_choose_temp'] = 'Wybierz szablon';
$lang['createf_select_temp'] = 'Wybierz szablon dla procesu tworzenia';
$lang['createf_proceed'] = 'Dalej';
$lang['rdn_field_blank'] = 'Pozostawiłeś/aś puste pole RDN.';
$lang['container_does_not_exist'] = 'Kontener który określiłeś/aś (%s) nie istnieje. Spróbuj ponownie.';
$lang['no_objectclasses_selected'] = 'Nie wybrałeś/aś żadnych Klas Obiektu dla tego obiektu. Wróć proszę i zrób to.';
$lang['hint_structural_oclass'] = 'Wskazówka: Musisz wybrać co najmniej jedną strukturalną klasę obiektu';
	      
//creation_template.php
$lang['ctemplate_on_server'] = 'Na serwerze';
$lang['ctemplate_no_template'] = 'Brak określenia szablonu w zmiennych POST.';
$lang['ctemplate_config_handler'] = 'Twoja konfiguracja określa handler';
$lang['ctemplate_handler_does_not_exist'] = 'dla tego szablonu. Ale, ten handler nie istnieje w szablonach/tworzonym katalogu';
$lang['create_step1'] = 'Krok 1 z 2: Nazwa i klasa/y obiektu';
$lang['create_step2'] = 'Krok 2 z 2: Określenie atrybutów i wartości';
$lang['relative_distinguished_name'] = 'Relatywna Wyróżniona Nazwa (RDN)';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(przykład: cn=MyNewPerson)';
$lang['container'] = 'Kontener';

// search.php
$lang['you_have_not_logged_into_server'] = 'Nie zalogowałeś/aś się jeszcze do wybranego serwera, więc nie możesz go przeszukiwać.';
$lang['click_to_go_to_login_form'] = 'Kliknij tutaj aby przejść do formularza logowania';
$lang['unrecognized_criteria_option'] = 'Nierozpoznane kryterium opcji: ';
$lang['if_you_want_to_add_criteria'] = 'Jeśli chcesz dodać własne kryteria do listy, zmodyfikuj plik search.php aby to obsłużyć.';
$lang['entries_found'] = 'Znaleziono wpisów: ';
$lang['filter_performed'] = 'Zastosowano filtr: ';
$lang['search_duration'] = 'Wyszukiwanie wykonane przez phpLDAPadmin w';
$lang['seconds'] = 'sekund(y)';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Przeszukiwany zakres';
$lang['scope_sub'] = 'Sub (całe poddrzewo)';
$lang['scope_one'] = 'One (jeden poziom poniżej bazowej)';
$lang['scope_base'] = 'Base (tylko bazowa dn)';
$lang['standard_ldap_search_filter'] = 'Standardowy filtr dla LDAP. Na przykład: (&(sn=Kowalski)(givenname=Jan))';
$lang['search_filter'] = 'Filtr wyszukiwania';
$lang['list_of_attrs_to_display_in_results'] = 'Lista atrybutów do wyświetlenia rezultatów (rozdzielona przecinkami)';

// search_form_simple.php
$lang['starts with'] = 'zaczyna się od';
$lang['ends with'] = 'kończy się na';
$lang['sounds like'] = 'brzmi jak';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Nie można uzyskać informacji od serwera LDAP';
$lang['server_info_for'] = 'Informacja o serwerze: ';
$lang['server_reports_following'] = 'Serwer zwrócił następujące informacje o sobie';
$lang['nothing_to_report'] = 'Ten serwer nie chce nic powiedzieć o sobie :).';

//update.php
$lang['update_array_malformed'] = 'tablica modyfikacji (update_array) jest zniekształcona. To może być błąd (bug) w phpLDAPadmin. Proszę to zgłosić.';
$lang['could_not_perform_ldap_modify'] = 'Nie można wykonać operacji modyfikacji (ldap_modify).';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Czy chcesz dokonać tych zmian ?';
$lang['attribute'] = 'Atrybuty';
$lang['old_value'] = 'Stara wartość';
$lang['new_value'] = 'Nowa wartość';
$lang['attr_deleted'] = '[atrybut usunięty]';
$lang['commit'] = 'Zatwierdź';
$lang['cancel'] = 'Anuluj';
$lang['you_made_no_changes'] = 'Nie dokonano żadnych zmian';
$lang['go_back'] = 'Powrót';

// welcome.php
$lang['welcome_note'] = 'Użyj menu z lewej strony do nawigacji';
$lang['credits'] = 'Credits';
$lang['changelog'] = 'ChangeLog';
$lang['donate'] = 'Donate';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Niebezpieczna nazwa pliku: ';
$lang['no_such_file'] = 'Nie znaleziono pliku: ';

//function.php
$lang['auto_update_not_setup'] = 'Zezwoliłeś/aś na automatyczne nadawanie uid (auto_uid_numbers) 
                                  dla <b>%s</b> w konfiguracji, ale nie określiłeś/aś mechanizmu
                                  (auto_uid_number_mechanism). Proszę skorygować ten problem.';
$lang['uidpool_not_set'] = 'Określiłeś/aś mechanizm autonumerowania uid "auto_uid_number_mechanism" jako "uidpool" w konfiguracji Twojego serwera <b>%s</b>, lecz nie określiłeś/aś audo_uid_number_uid_pool_dn. Proszę określ to zanim przejdziesz dalej.';
$lang['uidpool_not_exist'] = 'Wygląda na to, że uidPool, którą określiłeś/aś w Twojej konfiguracji ("%s") nie istnieje.';
$lang['specified_uidpool'] = 'Określiłeś/aś "auto_uid_number_mechanism" jako "search" w konfiguracji Twojego serwera <b>%s</b>, ale nie określiłeś/aś bazy "auto_uid_number_search_base". Zrób to zanim przejdziesz dalej.';
$lang['auto_uid_invalid_credential'] = 'Nie można podłączyć do <b>%s</b> z podaną tożsamością auto_uid. Sprawdź proszę swój plik konfiguracyjny.';
$lang['bad_auto_uid_search_base'] = 'W Twojej konfiguracji phpLDAPadmin określona jest nieprawidłowa wartość auto_uid_search_base dla serwera %s';
$lang['auto_uid_invalid_value'] = 'Określiłeś/aś błędną wartość dla auto_uid_number_mechanism ("%s") w konfiguracji. Tylko "uidpool" i "search" są poprawne. Proszę skorygować ten problem.';
$lang['error_auth_type_config'] = 'Błąd: Masz błąd w pliku konfiguracji. Trzy możliwe wartości dla auth_type w sekcji $servers to \'session\', \'cookie\' oraz \'config\'.  Ty wpisałeś/aś \'%s\', co jest niedozwolone. ';
$lang['php_install_not_supports_tls'] = 'Twoja instalacja PHP nie wspiera TLS.';
$lang['could_not_start_tls'] = 'Nie można uruchomić TLS. Proszę sprawdzić konfigurację serwera LDAP.';
$lang['could_not_bind_anon'] = 'Nie można anonimowo podłączyć do serwera.';
$lang['could_not_bind'] = 'Nie można podłączyć się do serwera LDAP.';
$lang['anonymous_bind'] = 'Podłączenie anonimowe';
$lang['bad_user_name_or_password'] = 'Zła nazwa użytkownika lub hasło. Spróbuj ponownie.';
$lang['redirecting_click_if_nothing_happens'] = 'Przekierowuję... Kliknij tutaj jeśli nic się nie dzieje.';
$lang['successfully_logged_in_to_server'] = 'Pomyślnie zalogowano do serwera <b>%s</b>';
$lang['could_not_set_cookie'] = 'Nie można ustawić ciasteczka (cookie).';
$lang['ldap_said'] = 'LDAP odpowiedział: %s';
$lang['ferror_error'] = 'Błąd';
$lang['fbrowse'] = 'przeglądaj';
$lang['delete_photo'] = 'Usuń fotografię';
$lang['install_not_support_blowfish'] = 'Twoja instalacja PHP nie wspiera szyfrowania blowfish.';
$lang['install_not_support_md5crypt'] = 'Twoja instalacja PHP nie wspiera szyfrowania md5crypt.';
$lang['install_no_mash'] = 'Twoja instalacja PHP nie posiada funkcji mhash(). Nie mogę tworzyć haszy SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto zawiera błędy<br />';
$lang['ferror_number'] = 'Błąd numer: %s (%s)';
$lang['ferror_discription'] = 'Opis: %s<br /><br />';
$lang['ferror_number_short'] = 'Błąd numer: %s<br /><br />';
$lang['ferror_discription_short'] = 'Opis: (brak dostępnego opisu)<br />';
$lang['ferror_submit_bug'] = 'Czy jest to błąd w phpLDAPadmin ? Jeśli tak, proszę go <a href=\'%s\'>zgłosić</a>.';
$lang['ferror_unrecognized_num'] = 'Nierozpoznany numer błędu: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Znalazłeś błąd w phpLDAPadmin (nie krytyczny) !</b></td></tr><tr><td>Błąd:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Plik:</td>
             <td><b>%s</b> linia <b>%s</b>, wywołane z <b>%s</b></td></tr><tr><td>Wersje:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Serwer Web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Proszę zgłoś ten błąd klikając tutaj</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Gratulacje ! Znalazłeś błąd w phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Błąd:</td><td><b>%s</b></td></tr>
	     <tr><td>Poziom:</td><td><b>%s</b></td></tr>
	     <tr><td>Plik:</td><td><b>%s</b></td></tr>
	     <tr><td>Linia:</td><td><b>%s</b></td></tr>
	     <tr><td>Wywołane z:</td><td><b>%s</b></td></tr>
	     <tr><td>Wersja PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Wersja PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Serwer Web:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
         Proszę zgłoś ten błąd klikając poniżej !';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importuj plik LDIF';
$lang['select_ldif_file'] = 'Wybierz plik LDIF:';
$lang['select_ldif_file_proceed'] = 'Dalej &gt;&gt;';
$lang['dont_stop_on_errors'] = 'Nie zatrzymuj się po napotkaniu błędów';

//ldif_import
$lang['add_action'] = 'Dodawanie...';
$lang['delete_action'] = 'Usuwanie...';
$lang['rename_action'] = 'Zmiana nazwy...';
$lang['modify_action'] = 'Modyfikowanie...';
$lang['warning_no_ldif_version_found'] = 'Nie znaleziono numeru wersji. Przyjmuję 1.';
$lang['valid_dn_line_required'] = 'Wymagana jest poprawna linia DN.';
$lang['missing_uploaded_file'] = 'Brak wgrywanego pliku.';
$lang['no_ldif_file_specified.'] = 'Nie określono pliku LDIF. Spróbuj ponownie.';
$lang['ldif_file_empty'] = 'Wgrany plik LDIF jest pusty.';
$lang['empty'] = 'pusty';
$lang['file'] = 'Plik';
$lang['number_bytes'] = '%s bajtów';
			  
$lang['failed'] = 'Nieudane';
$lang['ldif_parse_error'] = 'Błąd przetwarzania LDIF (Parse Error)';
$lang['ldif_could_not_add_object'] = 'Nie można dodać obiektu:';
$lang['ldif_could_not_rename_object'] = 'Nie można zmienić nazwy obiektu:';
$lang['ldif_could_not_delete_object'] = 'Nie można usunąć obiektu:';
$lang['ldif_could_not_modify_object'] = 'Nie można zmodyfikować obiektu:';
$lang['ldif_line_number'] = 'Linia numer:';
$lang['ldif_line'] = 'Linia:';

//delete_form
$lang['sure_permanent_delete_object']='Czy na pewno trwale usunąć ten obiekt ?';
$lang['list_of_entries_to_be_deleted'] = 'Lista wpisów do usunięcia:';
$lang['dn'] = 'DN';

// Exports
$lang['export_format'] = 'Format eksportu';
$lang['line_ends'] = 'Zakończenie linii';
$lang['must_choose_export_format'] = 'Musisz wybrać format eksportu.';
$lang['invalid_export_format'] = 'Błędny format eksportu';
$lang['no_exporter_found'] = 'Nie znaleziono dostępnego eksportera.';
$lang['error_performing_search'] = 'Napotkano błąd podczas szukania.';
$lang['showing_results_x_through_y'] = 'Pokazywanie rezultatów %s przez %s.';
$lang['searching'] = 'Szukam...';
$lang['size_limit_exceeded'] = 'Uwaga, przekroczono limit rozmiaru wyszukiwania.';
$lang['entry'] = 'Wpis';
$lang['ldif_export_for_dn'] = 'Eksport LDIF dla: %s';
$lang['generated_on_date'] = 'Wygenerowane przez phpLDAPadmin na %s';
$lang['total_entries'] = 'Łącznie wpisów';
$lang['dsml_export_for_dn'] = 'Eksport DSLM dla: %s';

// logins
$lang['could_not_find_user'] = 'Nie można znaleźć użytkownika "%s"';
$lang['password_blank'] = 'Pozostawiłeś/aś puste hasło.';
$lang['login_cancelled'] = 'Logowanie anulowane.';
$lang['no_one_logged_in'] = 'Nikt nie jest zalogowany do tego serwera.';
$lang['could_not_logout'] = 'Nie można wylogować.';
$lang['unknown_auth_type'] = 'Nieznany auth_type: %s';
$lang['logged_out_successfully'] = 'Pomyślnie wylogowano z serwera <b>%s</b>';
$lang['authenticate_to_server'] = 'Uwierzytelnienie dla serwera %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Uwaga: To połączenie nie jest szyfrowane.';
$lang['not_using_https'] = 'Nie używasz \'https\'. Przeglądarka będzie transmitować informację logowania czystym tekstem (clear text).';
$lang['login_dn'] = 'Login DN';
$lang['user_name'] = 'Nazwa użytkownika';
$lang['password'] = 'Hasło';
$lang['authenticate'] = 'Zaloguj';

// Entry browser
$lang['entry_chooser_title'] = 'Wybór wpisu';

// Index page
$lang['need_to_configure'] = 'Musisz skonfigurować phpLDAPadmin. Wyedytuj plik \'config.php\' aby to zrobić. Przykład pliku konfiguracji znajduje się w \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Usuwanie jest niedozwolone w trybie tylko-do-odczytu.';
$lang['error_calling_mass_delete'] = 'Błąd podczas wywołania mass_delete.php. Brakująca mass_delete w zmiennych POST.';
$lang['mass_delete_not_array'] = 'zmienna POST mass_delete nie jest w tablicą.';
$lang['mass_delete_not_enabled'] = 'Masowe usuwanie nie jest dozwolone. Odblokuj to proszę w config.php przed kontynuacją.';
$lang['mass_deleting'] = 'Masowe usuwanie';
$lang['mass_delete_progress'] = 'Postęp usuwania na serwerze "%s"';
$lang['malformed_mass_delete_array'] = 'Zniekształcona tablica mass_delete.';
$lang['no_entries_to_delete'] = 'Nie wybrano żadnegych wpisów do usunięcia.';
$lang['deleting_dn'] = 'Usuwanie %s';
$lang['total_entries_failed'] = '%s z %s wpisów nie zostało usuniętych.';
$lang['all_entries_successful'] = 'Wszystkie wpisy pomyślnie usunieto.';
$lang['confirm_mass_delete'] = 'Potwierdź masowe usunięcie %s wpisów na serwerze %s';
$lang['yes_delete'] = 'Tak, usuń !';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Nie możesz zmienić nazwy wpisu, posiadającego wpisy potomne (np. operacja zmiany nazwy nie jest dozwolona na wpisach nie będących liścmi).';
$lang['no_rdn_change'] = 'Nie zmieniłeś/aś RDN';
$lang['invalid_rdn'] = 'Błędna wartość RDN';
$lang['could_not_rename'] = 'Nie można zmienić nazwy wpisu';

?>
