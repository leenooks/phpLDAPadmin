<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/ru.php,v 1.6 2004/06/21 20:12:18 i18phpldapadmin Exp $


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

/* Translated to Russian by Sergey Saukh - cn=lich,dc=khv,dc=mts,dc=ru
   to cvs-Version of en.php 1.65
   
   Initial translation to russian by Dmitry Gorpinenko dima at uz.energy.gov.ua

 */

/*
 * The $lang array contains all the strings that phpLDAPadmin uses.
 * Each language file simply defines this aray with strings in its
 * language.
 */

// Search form
$lang['simple_search_form_str'] = 'Простая форма поиска';
$lang['advanced_search_form_str'] = 'Расширенная форма поиска';
$lang['server'] = 'Сервер';
$lang['search_for_entries_whose'] = 'Искать записи чьи';
$lang['base_dn'] = 'Базовый DN';
$lang['search_scope'] = 'Область поиска';
$lang['show_attributes'] = 'Показать атрибуты';
$lang['Search'] = 'Поиск';
$lang['predefined_search_str'] = 'Выбрать предопределенный поиск';
$lang['predefined_searches'] = 'Предопределенные поиски';
$lang['no_predefined_queries'] = 'Не определено ни одного запроса в config.php.';

// Tree browser
$lang['request_new_feature'] = 'Запросить фичу';
$lang['report_bug'] = 'Сообщить об ошибке';
$lang['schema'] = 'схема';
$lang['search'] = 'поиск';
$lang['create'] = 'создать';
$lang['info'] = 'инфо';
$lang['import'] = 'импорт';
$lang['refresh'] = 'обновить';
$lang['logout'] = 'выйти';
$lang['create_new'] = 'Создать новый';
$lang['view_schema_for'] = 'Просмотреть схему для';
$lang['refresh_expanded_containers'] = 'Обновить все открытые контейнеры для';
$lang['create_new_entry_on'] = 'Создать новую запись на';
$lang['new'] = 'новая';
$lang['view_server_info'] = 'Просмотреть информацию с сервера';
$lang['import_from_ldif'] = 'Импортировать записи из LDIF-файла';
$lang['logout_of_this_server'] = 'Выйти из этого сервера';
$lang['logged_in_as'] = 'Вошли как: ';
$lang['read_only'] = 'только для чтения';
$lang['read_only_tooltip'] = 'Этот атрибут установлен администратором только для чтения';
$lang['could_not_determine_root'] = 'Не могу найти корень вашего LDAP дерева.';
$lang['ldap_refuses_to_give_root'] = 'Похоже что ваш LDAP сервер сконфигурирован не показывать свой корень.';
$lang['please_specify_in_config'] = 'Укажите это в config.php';
$lang['create_new_entry_in'] = 'Создать новую запись в';
$lang['login_link'] = 'Вход...';
$lang['login'] = 'войти';

// Entry display
$lang['delete_this_entry'] = 'Удалить эту запись';
$lang['delete_this_entry_tooltip'] = 'Вас попросят подтвердить Ваше решение';
$lang['copy_this_entry'] = 'Скопировать эту запись';
$lang['copy_this_entry_tooltip'] = 'Скопировать этот объект в другое место, в новый DN или на другой сервер';
$lang['export'] = 'Экспорт';
$lang['export_tooltip'] = 'Сохранить дамп этого объекта';
$lang['export_subtree_tooltip'] = 'Сохранить дамп этого объекта и всех его потомков';
$lang['export_subtree'] = 'Экспортировать ветвь';
$lang['create_a_child_entry'] = 'Создать потомка';
$lang['rename_entry'] = 'Переименовать запись';
$lang['rename'] = 'Переименовать';
$lang['add'] = 'Добавить';
$lang['view'] = 'Просмотреть';
$lang['view_one_child'] = 'Просмотреть 1 потомка';
$lang['view_children'] = 'Просмотреть %s потомков';
$lang['add_new_attribute'] = 'Добавить новый атрибут';
$lang['add_new_objectclass'] = 'Добавить новый объект';
$lang['hide_internal_attrs'] = 'Скрыть внутренние атрибуты';
$lang['show_internal_attrs'] = 'Показать внутренние атрибуты';
$lang['attr_name_tooltip'] = 'Нажмите чтобы просмотреть описание схемы для атрибута \'%s\'';
$lang['none'] = 'нет';
$lang['no_internal_attributes'] = 'Нет внутренних атрибутов';
$lang['no_attributes'] = 'Эта запись не имеет атрибутов';
$lang['save_changes'] = 'Сохранить изменения';
$lang['add_value'] = 'добавить значение';
$lang['add_value_tooltip'] = 'Добавить дополнительное значение для атрибута \'%s\'';
$lang['refresh_entry'] = 'Обновить';
$lang['refresh_this_entry'] = 'Обновить эту запись';
$lang['delete_hint'] = 'Подсказка: Чтобы удалить атрибут - очистите текстовое поле и нажмите "сохранить"';
$lang['attr_schema_hint'] = 'Подсказка: Чтобы просмотреть схему атрибута - нажмите на его названии.';
$lang['attrs_modified'] = 'Некоторые атрибуты (%s) были модифицированы и теперь подсвечены ниже.';
$lang['attr_modified'] = 'Атрибут (%s) был модифицирован и теперь подсвечен ниже.';
$lang['viewing_read_only'] = 'Просмотр записи в режиме "только для чтения".';
$lang['no_new_attrs_available'] = 'для этой записи нет доступных новых атрибутов';
$lang['no_new_binary_attrs_available'] = 'для этой записи нет доступных новых бинарных атрибутов';
$lang['binary_value'] = 'Бинарное значение';
$lang['add_new_binary_attr'] = 'Добавить новый бинарный атрибут';
$lang['alias_for'] = 'Внимание: \'%s\' - алиас для \'%s\'';
$lang['download_value'] = 'загрузить значение';
$lang['delete_attribute'] = 'удалить атрибут';
$lang['true'] = 'правда';
$lang['false'] = 'ложь';
$lang['none_remove_value'] = 'пусто, удалите значение';
$lang['really_delete_attribute'] = 'Действительно удалить значение';
$lang['add_new_value'] = 'Добавить новое значение';

// Schema browser
$lang['the_following_objectclasses'] = 'Этот LDAP сервер поддерживает следующие объекты.';
$lang['the_following_attributes'] = 'Этот LDAP сервер поддерживает следующие типы атрибутов.';
$lang['the_following_matching'] = 'Этот LDAP сервер поддерживает следующие правила соответствия.';
$lang['the_following_syntaxes'] = 'Этот LDAP сервер поддерживает следующие синтаксисы.';
$lang['schema_retrieve_error_1']='Этот сервер не полностью поддерживает LDAP протокол.';
$lang['schema_retrieve_error_2']='Ваша версия PHP неправильно обработала запрос.';
$lang['schema_retrieve_error_3']='Или phpLDAPadmin не знает как получить схему для вашего сервера.';
$lang['jump_to_objectclass'] = 'Перейти к объекту';
$lang['jump_to_attr'] = 'Перейти к типам атрибутов';
$lang['jump_to_matching_rule'] = 'Перейти к правилам соответствия';
$lang['schema_for_server'] = 'Схема для сервера';
$lang['required_attrs'] = 'Требуемые атрибуты';
$lang['optional_attrs'] = 'Необязательные атрибуты';
$lang['optional_binary_attrs'] = 'Необязательные бинарные атрибуты';
$lang['OID'] = 'OID';
$lang['aliases']='Алиасы';
$lang['desc'] = 'Описание';
$lang['no_description']='нет описания';
$lang['name'] = 'Имя';
$lang['equality']='Равенство';
$lang['is_obsolete'] = 'Этот объект устарел.';
$lang['inherits'] = 'Заимствует из';
$lang['inherited_from'] = 'Заимствован из';
$lang['parent_to'] = 'Родитель для';
$lang['jump_to_this_oclass'] = 'Перейти к определению этого объекта';
$lang['matching_rule_oid'] = 'OID правила соответствия';
$lang['syntax_oid'] = 'OID синтаксиса';
$lang['not_applicable'] = 'неприменимый';
$lang['not_specified'] = 'не указано';
$lang['character']='символ'; 
$lang['characters']='символы';
$lang['used_by_objectclasses']='Используется объектами';
$lang['used_by_attributes']='Использован атрибутами';
$lang['maximum_length']='Максимальная длина';
$lang['attributes']='Типы атрибутов';
$lang['syntaxes']='Синтаксисы';
$lang['matchingrules']='Правила соответствия';
$lang['oid']='OID';
$lang['obsolete']='Устаревший';
$lang['ordering']='Сортировка';
$lang['substring_rule']='Правило подстроки';
$lang['single_valued']='Однозначный';
$lang['collective']='Совместный';
$lang['user_modification']='Изменение пользователем';
$lang['usage']='Использование';
$lang['could_not_retrieve_schema_from']='Не могу получить схему из';
$lang['type']='Тип';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Запись %s успешно удалена.';
$lang['you_must_specify_a_dn'] = 'Вы должны указать DN';
$lang['could_not_delete_entry'] = 'Не удалось удалить запись: %s';
$lang['no_such_entry'] = 'Нет такой записи: %s';
$lang['delete_dn'] = 'Удалить %s';
$lang['permanently_delete_children'] = 'Также окончательно удалить всех потомков?';
$lang['entry_is_root_sub_tree'] = 'Эта запись является корнем дерева содержащего %s записей.';
$lang['view_entries'] = 'просмотреть записи';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin может рекурсивно удалить эту запись и %s его потомков. Ниже список всех записей, которые будут удалены. Вы действительно хотите это сделать?';
$lang['confirm_recursive_delete_note'] = 'Внимание: это потенциально очень опасно и вы делаете это на свой собственный риск. Эта операция не может быть отменена. Примите во внимание алиасы, ссылки и другие вещи, которые могум вызвать проблемы.';
$lang['delete_all_x_objects'] = 'Удалить все %s объектов';
$lang['recursive_delete_progress'] = 'Прогресс рекурсивного удаления';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Запись %s и ее потомки успешно удалены.';
$lang['failed_to_delete_entry'] = 'Не удалось удалить запись %s';
$lang['list_of_entries_to_be_deleted'] = 'Список записей на удаление:';
$lang['sure_permanent_delete_object']='Вы уверены, что хотите окончательно удалить этот объект?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'Этот атрибут "%s" установлен "только для чтения" в конфигурации phpLDAPadmin.';
$lang['no_attr_specified'] = 'Не указано имя атрибута.';
$lang['no_dn_specified'] = 'Не указан DN';

// Adding attributes
$lang['left_attr_blank'] = 'Вы оставили значение атрибута пустым. Вернитесь и попробуйте еще раз.';
$lang['failed_to_add_attr'] = 'Не удалось добавить атрибут.';
$lang['file_empty'] = 'Файл, который Вы выбрали или не существует или пуст. Пожалуйста вернитесь и попробуйте еще раз.';
$lang['invalid_file'] = 'Ошибка безопасности: Файл который Вы загрузили может иметь вредоносный код.';
$lang['warning_file_uploads_disabled'] = 'Ваша конфигурация PHP запрещает загрузку файлов. Проверьте php.ini перед тем как продолжить.';
$lang['uploaded_file_too_big'] = 'Файл, который Вы загрузили слишком велик. Проверьте php.ini, опцию upload_max_size';
$lang['uploaded_file_partial'] = 'Файл, который Вы выбрали был загружен только частично, вероятно из-за ошибки в сети.';
$lang['max_file_size'] = 'Максимальный размер файла: %s';

// Updating values
$lang['modification_successful'] = 'Изменения успешно произведены!';
$lang['change_password_new_login'] = 'Т.к. Вы изменили свой пароль, Вы должны войти заново, используя свой новый пароль.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Новые требуемые атрибуты';
$lang['requires_to_add'] = 'Это действие требует добавить';
$lang['new_attributes'] = 'новые атрибуты';
$lang['new_required_attrs_instructions'] = 'Инструкция: Чтобы добавить этот объект к этой записи, Вы должны указать';
$lang['that_this_oclass_requires'] = 'что требует этот объект. Вы можете это сделать в этой форме.';
$lang['add_oclass_and_attrs'] = 'Добавить объект и атрибуты';
$lang['objectclasses'] = 'Объекты';

// General
$lang['chooser_link_tooltip'] = 'Нажмите чтобы вызвать диалог для визуального выбора записи(DN)';
$lang['no_updates_in_read_only_mode'] = 'Вы не можете производить обновления до тех пор, пока сервер находится в режиме "только для чтения"';
$lang['bad_server_id'] = 'Неправильный id сервера';
$lang['not_enough_login_info'] = 'Недостаточно информации чтобы подключиться к серверу. Проверьте Вашу конфигурацию.';
$lang['could_not_connect'] = 'Не могу подключиться к LDAP-серверу.';
$lang['could_not_connect_to_host_on_port'] = 'Не могу подключиться к "%s" на порт "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Не удалось произвести операцию ldap_mod_add.';
$lang['bad_server_id_underline'] = 'Неправильный server_id: ';
$lang['success'] = 'Успешно';
$lang['server_colon_pare'] = 'Сервер: ';
$lang['look_in'] = 'Ищу в: ';
$lang['missing_server_id_in_query_string'] = 'Не указан ID сервера в строке запроса!';
$lang['missing_dn_in_query_string'] = 'Не указан DN в строке запроса!';
$lang['back_up_p'] = 'Back Up...';
$lang['no_entries'] = 'нет записей';
$lang['not_logged_in'] = 'Не авторизовались';
$lang['could_not_det_base_dn'] = 'Не могу выяснить основной DN';
$lang['please_report_this_as_a_bug']='Пожалуйста сообщите об этой ошибке.';
$lang['reasons_for_error']='Это могло случится по нескольким причинам, наиболее вероятные это:';
$lang['yes']='Да';
$lang['no']='Нет';
$lang['go']='Перейти';
$lang['delete']='Удалить';
$lang['back']='Назад';
$lang['object']='объект';
$lang['delete_all']='Удалить все';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'подсказка';
$lang['bug'] = 'ошибка';
$lang['warning'] = 'предупреждение';
$lang['light'] = 'свет'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Далее &gt;&gt;';


// Add value form
$lang['add_new'] = 'Добавить новый';
$lang['value_to'] = 'значение для';
$lang['distinguished_name'] = 'Отличительное имя';
$lang['current_list_of'] = 'Текущий список';
$lang['values_for_attribute'] = 'значений атрибутов';
$lang['inappropriate_matching_note'] = 'Внимание: Вы получите ошибку "неподходящее соответствие", если Вы выставили правило "равенство"(EQUALITY) на Вашем LDAP-сервере для этого атрибута.';
$lang['enter_value_to_add'] = 'Введите значение, которое Вы хотите добавить:';
$lang['new_required_attrs_note'] = 'Внимание: Вам может потребоваться ввести новые атрибуты, которые требует этот объект';
$lang['syntax'] = 'Синтакс';

//copy.php
$lang['copy_server_read_only'] = 'Вы не можете производить обновления до тех пор, пока сервер находится в режиме "только для чтения"';
$lang['copy_dest_dn_blank'] = 'Вы не заполнили конечный DN.';
$lang['copy_dest_already_exists'] = 'Конечная запись (%s) уже сужествует.';
$lang['copy_dest_container_does_not_exist'] = 'Конечный контейнер (%s) не существует.';
$lang['copy_source_dest_dn_same'] = 'Исходный и конечный DN одинаковы.';
$lang['copy_copying'] = 'Сопирую ';
$lang['copy_recursive_copy_progress'] = 'Прогресс рекурсивного копирования';
$lang['copy_building_snapshot'] = 'Создаю копию состояния дерева для копирования... ';
$lang['copy_successful_like_to'] = 'Копирование прошло успешно! Вы хотите ';
$lang['copy_view_new_entry'] = 'просмотреть новую запись';
$lang['copy_failed'] = 'Не удалось скопировать DN: ';

//edit.php
$lang['missing_template_file'] = 'Предупреджение: отсутствуе файл шаблона, ';
$lang['using_default'] = 'Использую основной.';
$lang['template'] = 'Шаблон';
$lang['must_choose_template'] = 'Вы должны выбрать шаблон';
$lang['invalid_template'] = '%s неверный шаблон';
$lang['using_template'] = 'использую шаблон';
$lang['go_to_dn'] = 'Перейти к %s';

//copy_form.php
$lang['copyf_title_copy'] = 'Копировать ';
$lang['copyf_to_new_object'] = 'в новый объект';
$lang['copyf_dest_dn'] = 'Конечный DN';
$lang['copyf_dest_dn_tooltip'] = 'Полный DN новой записи будет создан при копировании исходной';
$lang['copyf_dest_server'] = 'Конечный сервер';
$lang['copyf_note'] = 'Подсказка: Копирование между различными серверами сработает, если между ними нет противоречий в схемах';
$lang['copyf_recursive_copy'] = 'Рекурсивно скопировать и всех потомков этого объекта.';
$lang['recursive_copy'] = 'Рекурсивное копирование';
$lang['filter'] = 'Фильтр';
$lang['filter_tooltip'] = 'Если производите рекурсивное копирование - копируйте только те записи, которые соответствуют этому фильтру';

//create.php
$lang['create_required_attribute'] = 'Вы оставили пустым значение для требуемого атрибута (%s).';
$lang['redirecting'] = 'Переназначаю...';
$lang['here'] = 'сюда';
$lang['create_could_not_add'] = 'Не удалось добавить объект на LDAP-сервер.';

//create_form.php
$lang['createf_create_object'] = 'Создать объект';
$lang['createf_choose_temp'] = 'Выберите шаблон';
$lang['createf_select_temp'] = 'Выберите шаблон для создания объекта';
$lang['createf_proceed'] = 'Далее';
$lang['rdn_field_blank'] = 'Вы оставили RDN поле пустым.';
$lang['container_does_not_exist'] = 'Контейнер, который Вы указали (%s) не существует. Попробуйте еще раз.';
$lang['no_objectclasses_selected'] = 'Вы не выбрали ни одного класса для этого объекта. Вернитесь и сделайте это.';
$lang['hint_structural_oclass'] = 'Подсказка: Вы должны выбрать как минимум один структурный класс';

//creation_template.php
$lang['ctemplate_on_server'] = 'На сервере';
$lang['ctemplate_no_template'] = 'В POST переменных не указан шаблон.';
$lang['ctemplate_config_handler'] = 'Ваша конфигурация указывает обработчик';
$lang['ctemplate_handler_does_not_exist'] = 'для этого шаблона. Но этот обработчик не существует в каталоге templates/creation.';
$lang['create_step1'] = 'Шаг 1 из 2: Имя и класс(ы) объекта';
$lang['create_step2'] = 'Шаг 2 из 2: Определение атрибутов и значений';
$lang['relative_distinguished_name'] = 'Относительное оттличительное имя';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(пример: cn=MyNewPerson)';
$lang['container'] = 'Контейнер';
$lang['alias_for'] = 'Алиас для %s';

// search.php
$lang['you_have_not_logged_into_server'] = 'Вы еще на вошли на выбранный сервер, поэтому Вы не можете производить на нем поиск.';
$lang['click_to_go_to_login_form'] = 'Нажмите здесь чтобы перейти на форму входа на сервер';
$lang['unrecognized_criteria_option'] = 'Нераспознанный критерий поиска: ';
$lang['if_you_want_to_add_criteria'] = 'Если Вы хотите добавить свои критерии поиска в список, отредактируйте search.php, чтобы они поддерживались. Выхожу.';
$lang['entries_found'] = 'Найдено записей: ';
$lang['filter_performed'] = 'Применен фильтр: ';
$lang['search_duration'] = 'phpLDAPadmin произвел поиск за';
$lang['seconds'] = 'секунд';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Область поиска';
$lang['scope_sub'] = 'Все (все поддерево)';
$lang['scope_one'] = 'Один (один уровень под базовым)';
$lang['scope_base'] = 'База (только базовый dn)';
$lang['standard_ldap_search_filter'] = 'Стандартный фильтр поиска LDAP. Пример: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Фильтр поиска';
$lang['list_of_attrs_to_display_in_results'] = 'Список атрибутов, которые отображать в результатах (разделенные запятыми)';
$lang['show_attributes'] = 'Показать атрибуты';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Искать записи, которые:';
$lang['equals'] = 'равны';
$lang['starts with'] = 'начинаются с';
$lang['contains'] = 'содержат';
$lang['ends with'] = 'заканчиваются на';
$lang['sounds like'] = 'похожи на';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Не могу получить LDAP-информацию с сервера';
$lang['server_info_for'] = 'Информация о сервере: ';
$lang['server_reports_following'] = 'Сервер сообщил о себе следующую информацию';
$lang['nothing_to_report'] = 'Этому серверу нечего о себе сообщить.';

//update.php
$lang['update_array_malformed'] = 'неправильно сформирован update_array. Возможно это ошибка phpLDAPadmin. Сообщите о ней.';
$lang['could_not_perform_ldap_modify'] = 'Не удалось произвести операцию ldap_modify.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Вы действительно хотите произвести эти изменения?';
$lang['attribute'] = 'Атрибут';
$lang['old_value'] = 'Старое значение';
$lang['new_value'] = 'Новое значение';
$lang['attr_deleted'] = '[атрибут удален]';
$lang['commit'] = 'Подтвердить';
$lang['cancel'] = 'Отменить';
$lang['you_made_no_changes'] = 'Вы не сделали никаких изменений';
$lang['go_back'] = 'Вернуться';

// welcome.php
$lang['welcome_note'] = 'Используйте меню слева для навигации';
$lang['credits'] = 'Создатели';
$lang['changelog'] = 'Список изменений';
$lang['donate'] = 'Спонсировать';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Небезопастное имя файла: ';
$lang['no_such_file'] = 'Нет такого файла: ';

//function.php
$lang['auto_update_not_setup'] = 'Вы разрешили auto_uid_numbers для <b>%s</b> в Вашей конфигурации,
                                  но Вы не указали auto_uid_number_mechanism. Пожалуйста исправьте эту проблему.';
$lang['uidpool_not_set'] = 'Вы указали "auto_uid_number_mechanism" как "uidpool"
                            в Вашей конфигурации для сервера <b>%s</b>, но не указали
                            audo_uid_number_uid_pool_dn. Исправьте это перед тем как продолжить.';
$lang['uidpool_not_exist'] = 'Похоже что uidPool, который Вы указали в Вашей конфигурации ("%s")
                              не существует.';
$lang['specified_uidpool'] = 'Вы указали "auto_uid_number_mechanism" как "search" в Вашей кофигурации
                              для сервера <b>%s</b>, но не указали
                              "auto_uid_number_search_base". Исправьте это перед тем как продолжить.';
$lang['auto_uid_invalid_credential'] = 'Не могу привязаться к <b>%s</b> с Вашими auto_uid данными. Проверьте Ваш файл конфигурации.'; 
$lang['bad_auto_uid_search_base'] = 'В Вашей конфигурации phpLDAPadmin указано неверное значение для auto_uid_search_base для сервера %s';
$lang['auto_uid_invalid_value'] = 'Вы указали неверное значение для auto_uid_number_mechanism ("%s")
                                   в Вашей конфигурации. Возможны только "uidpool" и "search".
                                   Пожалуйста исправьте эту проблему.';
$lang['error_auth_type_config'] = 'Ошибка: В Вашем конфигурационном файле ошибка. Единственные три допустимых значения
                                    для auth_type в секции $servers - \'session\', \'cookie\', и \'config\'. Вы ввели \'%s\',
                                    что недопустимо. ';
$lang['php_install_not_supports_tls'] = 'Ваша установленная версия PHP не поддерживает TLS.';
$lang['could_not_start_tls'] = 'Не могу запустить TLS. Проверьте конфигурацию Вашего LDAP-сервера.';
$lang['could_not_bind_anon'] = 'Не могу анонимно привязаться к серверу.';
$lang['could_not_bind'] = 'Не удалось привязаться к LDAP-серверу.';
$lang['anonymous_bind'] = 'Анонимная привязка';
$lang['bad_user_name_or_password'] = 'Неверное имя или пароль. Попробуйте еще раз.';
$lang['redirecting_click_if_nothing_happens'] = 'Перенапрявляю... Нажмите здесь, если ничего не происходит.';
$lang['successfully_logged_in_to_server'] = 'Успешно вошли на сервер <b>%s</b>';
$lang['could_not_set_cookie'] = 'Не удалось установить cookie.';
$lang['ldap_said'] = 'LDAP ответил: %s';
$lang['ferror_error'] = 'Ошибка';
$lang['fbrowse'] = 'просмотр';
$lang['delete_photo'] = 'Удалить фотографию';
$lang['install_not_support_blowfish'] = 'Ваша установленная версия PHP не поддерживает шифрование blowfish.';
$lang['install_not_support_md5crypt'] = 'Ваша установленная версия PHP не поддерживает шифрование md5crypt.';
$lang['install_no_mash'] = 'Ваша установленная версия PHP не имеет функцию mhash(). Не могу создать хэши SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto содержит ошибки<br />';
$lang['ferror_number'] = 'Номер ошибки: %s (%s)';
$lang['ferror_discription'] = 'Описание: %s <br /><br />';
$lang['ferror_number_short'] = 'Номер ошибки: %s<br /><br />';
$lang['ferror_discription_short'] = 'Описание: (нет описания)<br />';
$lang['ferror_submit_bug'] = 'Если это ошибка phpLDAPadmin - пожалуйста, <a href=\'%s\'>сообщите о ней</a>.';
$lang['ferror_unrecognized_num'] = 'Нераспознанная ошибка: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Вы нашли нефатальную ошибку phpLDAPadmin!</b></td></tr><tr><td>Ошибка:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Файл:</td>
             <td><b>%s</b> строка <b>%s</b>, вызвано <b>%s</b></td></tr><tr><td>Версии:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web сервер:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Сообщите об этой ошибке нажав здесь</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Поздравляем! Вы нашли ошибку в phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Ошибка:</td><td><b>%s</b></td></tr>
	     <tr><td>Уровень:</td><td><b>%s</b></td></tr>
	     <tr><td>Файл:</td><td><b>%s</b></td></tr>
	     <tr><td>Строка:</td><td><b>%s</b></td></tr>
		 <tr><td>Вызвано:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA Версия:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP Версия:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web сервер:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Сообщите об этой ошибке нажав ниже!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Иморт LDIF файла';
$lang['select_ldif_file'] = 'Выберите LDIF файл:';
$lang['select_ldif_file_proceed'] = 'Далее &gt;&gt;';
$lang['dont_stop_on_errors'] = 'Не останавливаться на ошибках';

//ldif_import
$lang['add_action'] = 'Добавляю...';
$lang['delete_action'] = 'Удаляю...';
$lang['rename_action'] = 'Переименовываю...';
$lang['modify_action'] = 'Изменяю...';
$lang['warning_no_ldif_version_found'] = 'Не найдено номера версии. Предполагаю 1.';
$lang['valid_dn_line_required'] = 'Требуется правильная строка с dn.';
$lang['missing_uploaded_file'] = 'Отсутствует загруженный файл.';
$lang['no_ldif_file_specified.'] = 'не указан LDIF файл. Попробуйте еще раз.';
$lang['ldif_file_empty'] = 'Загруженный LDIF файл пуст.';
$lang['empty'] = 'пуст';
$lang['file'] = 'Файл';
$lang['number_bytes'] = '%s байт';

$lang['failed'] = 'Не удалось';
$lang['ldif_parse_error'] = 'Ошибка интерпретации LDIF файла';
$lang['ldif_could_not_add_object'] = 'Не удалось добавить объект:';
$lang['ldif_could_not_rename_object'] = 'Не удалось переименовать объект:';
$lang['ldif_could_not_delete_object'] = 'Не удалось удалить объект:';
$lang['ldif_could_not_modify_object'] = 'Не удалось изменить объект:';
$lang['ldif_line_number'] = 'Номер строки:';
$lang['ldif_line'] = 'Строка:';

// Exports
$lang['export_format'] = 'Формат экспорта';
$lang['line_ends'] = 'Конец строки';
$lang['must_choose_export_format'] = 'Вы должны выбрать формат экспорта.';
$lang['invalid_export_format'] = 'Неверный формат экспорта';
$lang['no_exporter_found'] = 'Нет доступных экспортеров.';
$lang['error_performing_search'] = 'Произошла ошибка во время поиска.';
$lang['showing_results_x_through_y'] = 'Показываю результаты с %s по %s.';
$lang['searching'] = 'Поиск...';
$lang['size_limit_exceeded'] = 'Внимание, превышен размер поиска.';
$lang['entry'] = 'Запись';
$lang['ldif_export_for_dn'] = 'LDIF экспорт для: %s';
$lang['generated_on_date'] = 'Сгенерировано phpLDAPadmin %s';
$lang['total_entries'] = 'Всего записей';
$lang['dsml_export_for_dn'] = 'DSLM экспорт для: %s';

// logins
$lang['could_not_find_user'] = 'Не могу найти пользователя "%s"';
$lang['password_blank'] = 'Вы не ввели пароль.';
$lang['login_cancelled'] = 'Вход отменен.';
$lang['no_one_logged_in'] = 'На этом сервере сейчас никого нет.';
$lang['could_not_logout'] = 'Не удалось выйти.';
$lang['unknown_auth_type'] = 'Неизвестный auth_type: %s';
$lang['logged_out_successfully'] = 'Успешно вышли с сервера <b>%s</b>';
$lang['authenticate_to_server'] = 'Авторизация на сервере %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Предупреждение: Это web-соединение нешифровано.';
$lang['not_using_https'] = 'Вы не используете \'https\'. Web-браузер передает авторизационные данные открытым текстом.';
$lang['login_dn'] = 'Login DN';
$lang['user_name'] = 'Имя пользователя';
$lang['password'] = 'Пароль';
$lang['authenticate'] = 'Авотризация';

// Entry browser
$lang['entry_chooser_title'] = 'Выбор записи';

// Index page
$lang['need_to_configure'] = 'Вам надо сконфигурировать phpLDAPadmin. Отредактируйте файл \'config.php\'. Примерный файл настроек - \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Удаление невозможно в режиме "только для чтения".';
$lang['error_calling_mass_delete'] = 'Ошибка вызова mass_delete.php. Отсутствует mass_delete в POST переменных.';
$lang['mass_delete_not_array'] = 'POST переменная mass_delete не в массиве.';
$lang['mass_delete_not_enabled'] = 'Массовое удаление не разрешено. Испровьте это в config.php перед тем как продолжить.';
$lang['mass_deleting'] = 'Массовое удаление';
$lang['mass_delete_progress'] = 'Прогресс удаления на сервере "%s"';
$lang['malformed_mass_delete_array'] = 'Неправильно сформированный массив mass_delete.';
$lang['no_entries_to_delete'] = 'Вы не выбрали ни одной записи для удаления.';
$lang['deleting_dn'] = 'Удаляю %s';
$lang['total_entries_failed'] = 'Не удалось удалить %s из %s записей.';
$lang['all_entries_successful'] = 'Все записи успешно удалены.';
$lang['confirm_mass_delete'] = 'Подтвердите массовое удаление %s записей на сервере %s';
$lang['yes_delete'] = 'Да, удалить!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Вы не можете переименовать запись, которая имеет потомков (т.е., операция переименования не допустима для дерева)';
$lang['no_rdn_change'] = 'Вы не изменили RDN';
$lang['invalid_rdn'] = 'Неверное значение RDN';
$lang['could_not_rename'] = 'Не удалось переименовать запись';

?>
