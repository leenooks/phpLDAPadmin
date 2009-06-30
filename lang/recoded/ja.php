<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/ja.php,v 1.1 2004/12/09 14:19:28 uugdave Exp $


/*        ---   INSTRUCTIONS FOR TRANSLATORS   ---
 *
 * If you want to write a new language file for your language,
 * please submit the file on SourceForge:
 *
 *   https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498548
 *
 * Use the option "Check to Upload and Attach a File" at the bottom
 *
 * Read the doc/README-translation.txt for translation instructions.
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
$lang['simple_search_form_str'] = '簡易検索フォーム';
$lang['advanced_search_form_str'] = '高度な検索フォーム';
$lang['server'] = 'サーバー';
$lang['search_for_entries_whose'] = 'どのエントリを検索';
$lang['base_dn'] = 'ベース DN';
$lang['search_scope'] = '検索スコープ';
$lang['show_attributes'] = '属性表示';
$lang['attributes'] = '属性';
$lang['Search'] = '検索';
$lang['predefined_search_str'] = '事前定義された検索を選択';
$lang['predefined_searches'] = '事前定義検索';
$lang['no_predefined_queries'] = 'config.php で定義された照会はありません。';
$lang['export_results'] = '結果エクスポート';
$lang['unrecoginzed_search_result_format'] = 'Unrecognized search result format: %s';
$lang['format'] = '書式';
$lang['list'] = '一覧';
$lang['table'] = 'テーブル';
$lang['bad_search_display'] = 'config.php にて $default_search_display: %s の無効な値が指定されています。それを修正してください。';
$lang['page_n'] = 'ページ %d';
$lang['no_results'] = '検索結果がありませんでした。';

// Tree browser
$lang['request_new_feature'] = '機能リクエスト';
$lang['report_bug'] = '不都合報告';
$lang['schema'] = 'スキーマ';
$lang['search'] = '検索';
$lang['create'] = '作成';
$lang['info'] = '情報';
$lang['import'] = 'インポート';
$lang['refresh'] = '再描画';
$lang['logout'] = 'ログアウト';
$lang['create_new'] = 'ここに新規エントリを追加';
$lang['view_schema_for'] = 'View schema for';
$lang['refresh_expanded_containers'] = 'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'Create a new entry on';
$lang['new'] = '新規';
$lang['view_server_info'] = 'サーバーが提供する情報を閲覧';
$lang['import_from_ldif'] = 'LDIF ファイルからのインポートエントリ';
$lang['logout_of_this_server'] = 'このサーバーのログアウト';
$lang['logged_in_as'] = 'Logged in as: ';
$lang['read_only'] = '読み込み専用';
$lang['read_only_tooltip'] = 'この属性は phpLDAP 管理者により、読み込み専用で設定されています。';
$lang['could_not_determine_root'] = 'LDAP ツリーのルートが決定できません。';
$lang['ldap_refuses_to_give_root'] = 'LDAP サーバーが root を見えないように設定しているように見えます。';
$lang['please_specify_in_config'] = 'config.php でそれを指定してください。';
$lang['create_new_entry_in'] = 'Create a new entry in';
$lang['login_link'] = 'ログイン...';
$lang['login'] = 'ログイン';
$lang['base_entry_does_not_exist'] = 'ベースエントリが存在しません。';
$lang['create_it'] = 'Create it?';

// Entry display
$lang['delete_this_entry'] = 'このエントリを削除';
$lang['delete_this_entry_tooltip'] = 'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'このエントリをコピー';
$lang['copy_this_entry_tooltip'] = 'このオブジェクトを異なるロケーション、新しい DNもしくは別のサーバーにコピーする';
$lang['export'] = 'エクスポート';
$lang['export_lcase'] = 'エクスポート';
$lang['export_tooltip'] = 'このオブジェクトのダンプ保存';
$lang['export_subtree_tooltip'] = 'このオブジェクトと子の全てをダンプ保存する';
$lang['export_subtree'] = 'サブツリーをエクスポート';
$lang['create_a_child_entry'] = '子エントリ作成';
$lang['rename_entry'] = 'エントリ名称変更';
$lang['rename'] = '名称変更';
$lang['add'] = '追加';
$lang['view'] = 'View';
$lang['view_one_child'] = 'ひとつの子を閲覧';
$lang['view_children'] = '%s 個の子を閲覧';
$lang['add_new_attribute'] = '新規属性追加';
$lang['add_new_objectclass'] = '新規 ObjectClass を追加';
$lang['hide_internal_attrs'] = '内部属性を隠す';
$lang['show_internal_attrs'] = '内部属性を表示';
$lang['attr_name_tooltip'] = 'Click to view the schema defintion for attribute type \'%s\'';
$lang['none'] = 'なし';
$lang['no_internal_attributes'] = '内部属性がありません';
$lang['no_attributes'] = 'このエントリは属性を持っていません';
$lang['save_changes'] = '変更を保存';
$lang['add_value'] = '値追加';
$lang['add_value_tooltip'] = 'Add an additional value to attribute \'%s\'';
$lang['refresh_entry'] = '再描画';
$lang['refresh_this_entry'] = 'このエントリを再描画';
$lang['delete_hint'] = 'ヒント: 属性を削除するにはテキストフィールドを空にして保存をクリックします。';
$lang['attr_schema_hint'] = 'ヒント: 属性のスキーマを閲覧するには、属性名をクリックします。';
$lang['attrs_modified'] = 'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'Viewing entry in read-only mode.';
$lang['no_new_attrs_available'] = 'no new attributes available for this entry';
$lang['no_new_binary_attrs_available'] = 'no new binary attributes available for this entry';
$lang['binary_value'] = 'バイナリ値';
$lang['add_new_binary_attr'] = '新規バイナリ属性を追加';
$lang['alias_for'] = 'Note: \'%s\' is an alias for \'%s\'';
$lang['required_for'] = 'objectClass %s の必須属性';
$lang['download_value'] = 'ダウンロード値';
$lang['delete_attribute'] = '属性削除';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = 'none, remove value';
$lang['really_delete_attribute'] = '本当に属性を削除';
$lang['add_new_value'] = '新規値追加';

// Schema browser
$lang['the_following_objectclasses'] = '次の objectClasses はこの LDAP サーバーでサポートされています。';
$lang['the_following_attributes'] = '次の属性タイプはこの LDAP サーバーでサポートされています。';
$lang['the_following_matching'] = '次の一致ルールはこの LDAP サーバーでサポートされています。';
$lang['the_following_syntaxes'] = '次の文法はこの LDAP サーバーでサポートされています。';
$lang['schema_retrieve_error_1']='このサーバーはすべての LDAP プロトコルをサポートしていません。';
$lang['schema_retrieve_error_2']='この PHP のバージョンは正確に照会を行えません。';
$lang['schema_retrieve_error_3']='あるいは、phpLDAPadmin は、あなたのサーバーからスキーマを取得する方法を知りません。';
$lang['jump_to_objectclass'] = 'objectClass に移動';
$lang['view_schema_for_oclass'] = 'この objectClass のスキーマ説明を閲覧';
$lang['jump_to_attr'] = '属性タイプに移動';
$lang['jump_to_matching_rule'] = '一致ルールにジャンプ';
$lang['schema_for_server'] = 'サーバーのスキーマ';
$lang['required_attrs'] = '必須属性';
$lang['required'] = '必須';
$lang['optional_attrs'] = 'オプション属性';
$lang['optional_binary_attrs'] = 'オプションバイナリ属性';
$lang['OID'] = 'OID';
$lang['aliases']='別名';
$lang['desc'] = '説明';
$lang['no_description']='説明がありません';
$lang['name'] = '名前';
$lang['equality']='Equality';
$lang['is_obsolete'] = 'この objectClass は旧式です。';
$lang['inherits'] = '継承元';
$lang['inherited_from'] = '派生元';
$lang['parent_to'] = '派生先';
$lang['jump_to_this_oclass'] = 'この objectClass 定義に移動';
$lang['matching_rule_oid'] = '適用ルール OID';
$lang['syntax_oid'] = 'Syntax OID';
$lang['not_applicable'] = '適用可能ではありません';
$lang['not_specified'] = '指定されていません';
$lang['character']='character'; 
$lang['characters']='characters';
$lang['used_by_objectclasses']='Used by objectClasses';
$lang['used_by_attributes']='Used by Attributes';
$lang['maximum_length']='最大長';
$lang['attribute_types']='属性タイプ';
$lang['syntaxes']='Syntaxes';
$lang['matchingrules']='一致ルール';
$lang['oid']='OID';
$lang['obsolete']='旧式';
$lang['ordering']='Ordering';
$lang['substring_rule']='副文字列ルール';
$lang['single_valued']='Single Valued';
$lang['collective']='集合';
$lang['user_modification']='ユーザー修正';
$lang['usage']='使用法';
$lang['could_not_retrieve_schema_from']='次のスキーマを取得できません:';
$lang['type']='種類';
$lang['no_such_schema_item'] = 'スキーマ項目がありません: "%s"';

// Deleting entries
$lang['entry_deleted_successfully'] = '%s エントリを削除しました。';
$lang['you_must_specify_a_dn'] = 'DN を指定しなければなりません';
$lang['could_not_delete_entry'] = 'エントリを削除できませんでした: %s';
$lang['no_such_entry'] = 'エントリがありません: %s';
$lang['delete_dn'] = '%s 削除';
$lang['permanently_delete_children'] = 'Permanently delete all children also?';
$lang['entry_is_root_sub_tree'] = 'このエントリは %s エントリを含むサブツリーのルートです。';
$lang['view_entries'] = 'エントリ閲覧';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?';
$lang['confirm_recursive_delete_note'] = 'Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.';
$lang['delete_all_x_objects'] = '%s オブジェクトをすべて削除';
$lang['recursive_delete_progress'] = '再帰削除進行';
$lang['entry_and_sub_tree_deleted_successfully'] = 'エントリ %s とサブツリーの削除に成功しました。';
$lang['failed_to_delete_entry'] = 'エントリ %s のふぁ駆除に失敗しました';
$lang['list_of_entries_to_be_deleted'] = 'List of entries to be deleted:';
$lang['sure_permanent_delete_object']='本当にこのオブジェクトを永続的に削除しますか?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'phpLDAPadmin の設定で属性 "%s" は読み込み専用に設定されています。';
$lang['no_attr_specified'] = '属性名が指定されていません。';
$lang['no_dn_specified'] = 'DN が指定されていません';

// Adding attributes
$lang['left_attr_blank'] = 'You left the attribute value blank. Please go back and try again.';
$lang['failed_to_add_attr'] = '属性の追加に失敗しました。';
$lang['file_empty'] = 'The file you chose is either empty or does not exist. Please go back and try again.';
$lang['invalid_file'] = 'Security error: The file being uploaded may be malicious.';
$lang['warning_file_uploads_disabled'] = 'Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.';
$lang['uploaded_file_too_big'] = 'アップロードされたファイルが大きすぎます。php.ini の upload_max_size 設定を調べてください。';
$lang['uploaded_file_partial'] = '選択したファイルは、部分的なアップロードさでした。The file you selected was only partially uploaded, likley due to a network error.';
$lang['max_file_size'] = '最大ファイルサイズ: %s';

// Updating values
$lang['modification_successful'] = '修正に成功しました!';
$lang['change_password_new_login'] = 'パスワードを変更したので、今から新しいパスワードで再ログインしなければなりません。';

// Adding objectClass form
$lang['new_required_attrs'] = '新規必須属性';
$lang['requires_to_add'] = 'This action requires you to add';
$lang['new_attributes'] = '新貴族性';
$lang['new_required_attrs_instructions'] = 'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'ObjectClass と属性を追加';
$lang['objectclasses'] = 'ObjectClasses';

// General
$lang['chooser_link_tooltip'] = 'Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = '間違ったサーバー ID';
$lang['not_enough_login_info'] = 'サーバーにログインする情報が不足しています。設定を確認してください。';
$lang['could_not_connect'] = 'LDAP サーバーに接続できませんでした。';
$lang['could_not_connect_to_host_on_port'] = '"%s" のポート "%s" に接続できませんでした';
$lang['could_not_perform_ldap_mod_add'] = 'Could not perform ldap_mod_add operation.';
$lang['bad_server_id_underline'] = 'Bad server_id: ';
$lang['home'] = 'ホーム';
$lang['success'] = '成功';
$lang['server_colon_pare'] = 'サーバー: ';
$lang['look_in'] = 'Looking in: ';
$lang['missing_server_id_in_query_string'] = '照会文字列にサーバー ID が指定されていません!';
$lang['missing_dn_in_query_string'] = '照会文字列に DN が指定されていません!';
$lang['back_up_p'] = 'バックアップ...';
$lang['no_entries'] = 'エントリがありません';
$lang['not_logged_in'] = 'Not logged in';
$lang['could_not_det_base_dn'] = 'Could not determine base DN';
$lang['please_report_this_as_a_bug']='この不都合を報告してください。';
$lang['reasons_for_error']='This could happen for several reasons, the most probable of which are:';
$lang['yes']='はい';
$lang['no']='いいえ';
$lang['go']='Go';
$lang['delete']='削除';
$lang['back']='戻る';
$lang['object']='オブジェクト';
$lang['delete_all']='すべて削除';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'ヒント';
$lang['bug'] = '不都合';
$lang['warning'] = '警告';
$lang['light'] = 'light'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = '進む &gt;&gt;';


// Add value form
$lang['add_new'] = '新規追加';
$lang['value_to'] = 'value to';
$lang['distinguished_name'] = '関連名';
$lang['current_list_of'] = 'Current list of';
$lang['values_for_attribute'] = '属性の値';
$lang['inappropriate_matching_note'] = 'Note: You will get an "inappropriate matching" error if you have not setup an EQUALITY rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Note: you may be required to enter new attributes that this objectClass requires';
$lang['syntax'] = 'Syntax';

//copy.php
$lang['copy_server_read_only'] = 'You cannot perform updates while server is in read-only mode';
$lang['copy_dest_dn_blank'] = 'You left the destination DN blank.';
$lang['copy_dest_already_exists'] = '対象エントリ (%s) は既に存在します。';
$lang['copy_dest_container_does_not_exist'] = '対象先コンテナー (%s) は存在しません。';
$lang['copy_source_dest_dn_same'] = '対象元と対象先 DN が同じです。';
$lang['copy_copying'] = 'Copying ';
$lang['copy_recursive_copy_progress'] = '再帰コピー進行';
$lang['copy_building_snapshot'] = 'Building snapshot of tree to copy... ';
$lang['copy_successful_like_to'] = 'コピー成功! Would you like to ';
$lang['copy_view_new_entry'] = '新規エントリ閲覧';
$lang['copy_failed'] = 'DN のコピーに失敗しました: ';

//edit.php
$lang['missing_template_file'] = '警告: テンプレートファイルがありません。 ';
$lang['using_default'] = 'デフォルトを使います。';
$lang['template'] = 'テンプレート';
$lang['must_choose_template'] = 'テンプレートを選ばなければなりません';
$lang['invalid_template'] = '%s は無効なテンプレート';
$lang['using_template'] = '使用中のテンプレート';
$lang['go_to_dn'] = '%s に移動';
+$lang['structural_object_class_cannot_remove'] = 'これは構造 ObjectClass なので削除できませんでした。';
+$lang['structural'] = '構造';

//copy_form.php
$lang['copyf_title_copy'] = 'コピー ';
$lang['copyf_to_new_object'] = 'to a new object';
$lang['copyf_dest_dn'] = '対象 DN';
$lang['copyf_dest_dn_tooltip'] = 'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = '対象サーバー';
$lang['copyf_note'] = 'Hint: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = '同様にこのオブジェクトのすべての子を再帰コピーします。';
$lang['recursive_copy'] = '再帰コピー';
$lang['filter'] = 'Filter';
$lang['search_filter'] = '検索フィルター';
$lang['filter_tooltip'] = 'When performing a recursive copy, only copy those entries which match this filter';

//create.php
$lang['create_required_attribute'] = 'You left the value blank for required attribute (%s).';
$lang['redirecting'] = 'Redirecting...';
$lang['here'] = 'ここ';
$lang['create_could_not_add'] = 'LDAP サーバーにオブジェクトを追加できませんでした。';

//create_form.php
$lang['createf_create_object'] = 'オブジェクト作成';
$lang['createf_choose_temp'] = 'テンプレート選択';
$lang['createf_select_temp'] = '作成処理のテンプレートを選択';
$lang['save_as_file'] = 'ファイルに保存';
$lang['rdn_field_blank'] = 'You left the RDN field blank.';
$lang['container_does_not_exist'] = '指定したコンテナー(%s)が存在しません。もう一度行ってください。';
$lang['no_objectclasses_selected'] = 'You did not select any ObjectClasses for this object. Please go back and do so.';
$lang['hint_structural_oclass'] = 'ヒント: You must choose exactly one structural objectClass (shown in bold above)';

//creation_template.php
$lang['ctemplate_on_server'] = 'サーバー';
$lang['ctemplate_no_template'] = 'POST 値でテンプレートが指定されていません。';
$lang['template_not_readable'] = 'Your config specifies a handler of "%s" for this template but this file is not readable because the permissions are too strict.';
$lang['template_does_not_exist'] = 'Your config specifies a handler of "%s" for this template but this handler does not exist in the templates/creation directory.';
$lang['create_step1'] = 'ステップ 1/2: 名前と ObjectClass';
$lang['create_step2'] = 'Step 2 of 2: 属性と値を指定';
$lang['relative_distinguished_name'] = '関連識別名';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(例: cn=MyNewPerson)';
$lang['container'] = 'コンテナー';

// search.php
$lang['you_have_not_logged_into_server'] = 'You have not logged into the selected server yet, so you cannot perform searches on it.';
$lang['click_to_go_to_login_form'] = 'ここをクリックするとログインフォームに移動します';
$lang['unrecognized_criteria_option'] = '未承認の基準オプション: ';
$lang['if_you_want_to_add_criteria'] = 'If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.';
$lang['entries_found'] = 'エントリ発見: ';
$lang['filter_performed'] = '検索実行: ';
$lang['search_duration'] = 'phpLDadmin で検索を実行';
$lang['seconds'] = '秒';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'The scope in which to search';
$lang['scope_sub'] = 'Sub (entire subtree)';
$lang['scope_one'] = 'One (one level beneath base)';
$lang['scope_base'] = 'ベース (ベース dn のみ)';
$lang['standard_ldap_search_filter'] = '標準 LDAP 検索フィルター。例: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = '検索フィルター';
$lang['list_of_attrs_to_display_in_results'] = '結果から属性の一覧を表示 (カンマ区切り)';
$lang['show_attributes'] = '属性表示';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'どのエントリ検索するか:';
$lang['equals'] = 'に一致';
$lang['starts with'] = 'で始まる';
$lang['contains'] = 'を含む';
$lang['ends with'] = 'で終わる';
$lang['sounds like'] = 'に近い';

// server_info.php
$lang['could_not_fetch_server_info'] = 'サーバーから LDAP 情報を取得できませんでした。これはあなたの PHP バージョンの<a href="http://bugs.php.net/bug.php?id=29587">不都合</a>によるかもしれません。あるいは、あなたの LDAP サーバーは、LDAP クライアントが RootDSE にアクセスするのを防ぐアクセス制御指定をしています。';
$lang['server_info_for'] = 'Server info for: ';
$lang['server_reports_following'] = 'Server reports the following information about itself';
$lang['nothing_to_report'] = 'このサーバーはなにも報告するものはありません。';

//update.php
$lang['update_array_malformed'] = 'update_array がオカシイです。This might be a phpLDAPadmin bug. Please report it.';
$lang['could_not_perform_ldap_modify'] = 'ldap_modify 操作が実行できませんでした。';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Do you want to make these changes?';
$lang['attribute'] = '属性';
$lang['old_value'] = '古い値';
$lang['new_value'] = '新しい値';
$lang['attr_deleted'] = '[属性を削除しました]';
$lang['commit'] = 'コミット';
$lang['cancel'] = '取り消し';
$lang['you_made_no_changes'] = '変更はありません';
$lang['go_back'] = 'Go back';

// welcome.php
$lang['welcome_note'] = '左へのメニューを使用して捜査します';
$lang['credits'] = 'クレジット';
$lang['changelog'] = '変更履歴';
$lang['donate'] = '寄贈';
$lang['pla_logo'] = 'phpLDAPadmin ロゴ';

// Donate.php
$lang['donation_instructions'] = 'phpLDAPadmin プロジェクトに資金を寄贈するためには、PayPal ボタンのうちの 1 つを下に使用してください。';
$lang['donate_amount'] = '%s を寄贈';
$lang['wish_list_option'] = 'Or perhaps you would prefer to purchase an gift for a phpLDAPadmin developer.';
$lang['wish_list'] = 'Dave\'s phpLDAPadmin のゐっ主リストを閲覧';

$lang['purge_cache'] = 'キャッシュ破棄';
$lang['no_cache_to_purge'] = '破棄するキャッシュはありませんでした。';
$lang['done_purging_caches'] = '%s バイトのキャッシュを破棄しました。';
$lang['purge_cache_tooltip'] = 'サーバースキーマを含む、phpLDAPadmin のすべてのキャッシュを破棄しました。';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = '安全でないファイル名: ';
$lang['no_such_file'] = 'ファイルがありません: ';

//function.php
$lang['auto_update_not_setup'] = 'You have enabled auto_uid_numbers for <b>%s</b> in your configuration,
                                  but you have not specified the auto_uid_number_mechanism. Please correct
                                  this problem.';
$lang['uidpool_not_set'] = 'You specified the "auto_uid_number_mechanism" as "uidpool"
                            in your configuration for server <b>%s</b>, but you did not specify the
                            audo_uid_number_uid_pool_dn. Please specify it before proceeding.';
$lang['uidpool_not_exist'] = 'It appears that the uidPool you specified in your configuration ("%s")
                              does not exist.';
$lang['specified_uidpool'] = 'You specified the "auto_uid_number_mechanism" as "search" in your
                              configuration for server <b>%s</b>, but you did not specify the
                              "auto_uid_number_search_base". Please specify it before proceeding.';
$lang['auto_uid_invalid_credential'] = 'Unable to bind to <b>%s</b> with your with auto_uid credentials. Please check your configuration file.'; 
$lang['bad_auto_uid_search_base'] = 'Your phpLDAPadmin configuration specifies an invalid auto_uid_search_base for server %s';
$lang['auto_uid_invalid_value'] = 'You specified an invalid value for auto_uid_number_mechanism ("%s")
                                   in your configration. Only "uidpool" and "search" are valid.
                                   Please correct this problem.';
$lang['error_auth_type_config'] = 'Error: You have an error in your config file. The only three allowed values
                                    for auth_type in the $servers section are \'session\', \'cookie\', and \'config\'. You entered \'%s\',
                                    which is not allowed. ';
$lang['unique_attrs_invalid_credential'] = 'Unable to bind to <b>%s</b> with your with unique_attrs credentials. Please check your configuration file.'; 
$lang['unique_attr_failed'] = 'Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.';
$lang['php_install_not_supports_tls'] = 'インストールされている PHP は TLS をサポートしていません。';
$lang['could_not_start_tls'] = 'TLS を開始できません。LDAP サーバーの設定を確認してください。';
$lang['could_not_bind_anon'] = 'サーバーに匿名接続できませんでした。';
$lang['could_not_bind'] = 'LDAP サーバーに接続できませんでした。';
$lang['anonymous_bind'] = '匿名接続';
$lang['bad_user_name_or_password'] = 'ユーザー名かパスワードがおかしいです。もう一度行ってください。';
$lang['redirecting_click_if_nothing_happens'] = 'Redirecting... Click here if nothing happens.';
$lang['successfully_logged_in_to_server'] = 'サーバー <b>%s</b>へのログインに成功しました';
$lang['could_not_set_cookie'] = 'cookie を設定できませんでした。';
$lang['ldap_said'] = 'LDAP 回答: %s';
$lang['ferror_error'] = 'エラー';
$lang['fbrowse'] = '閲覧';
$lang['delete_photo'] = '写真削除';
$lang['install_not_support_blowfish'] = 'インストールされた PHP は blowfish 暗号化をサポートしていません。';
$lang['install_not_support_md5crypt'] = 'インストールされた PHP は md5crypt 暗号化をサポートしていません。';
$lang['install_no_mash'] = 'インストールされた PHP は mhash() をサポートしていません。SHA ハッシュをすることができません。';
$lang['jpeg_contains_errors'] = 'jpegPhoto contains errors<br />';
$lang['ferror_number'] = 'エラー番号: %s (%s)';
$lang['ferror_discription'] = '説明: %s <br /><br />';
$lang['ferror_number_short'] = 'エラー番号: %s<br /><br />';
$lang['ferror_discription_short'] = '説明: (利用可能な説明がありませんでした)<br />';
$lang['ferror_submit_bug'] = 'これは phpLDAPadmin の不都合ですか? もしそうなら <a href=\'%s\'>報告</a> してください。';
$lang['ferror_unrecognized_num'] = '未承認のエラー番号: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>致命的でない phpLDAPadmin の不都合を発見しました!</b></td></tr><tr><td>エラー:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>ファイル:</td>
             <td><b>%s</b> 行 <b>%s</b>, caller <b>%s</b></td></tr><tr><td>バージョン:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>ウェブサーバー:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             ここをクリックして個の不都合を報告してください。</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'おめでとうございます! phpLDAPadmin で不都合を発見しました。<br /><br />
	     <table class=\'bug\'>
	     <tr><td>エラー:</td><td><b>%s</b></td></tr>
	     <tr><td>レベル:</td><td><b>%s</b></td></tr>
	     <tr><td>ファイル:</td><td><b>%s</b></td></tr>
	     <tr><td>行:</td><td><b>%s</b></td></tr>
		 <tr><td>Caller:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA バージョン:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP バージョン:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web サーバー:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     下記をクリックしてこの不都合を報告してください!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'LDIF ファイルインポート';
$lang['select_ldif_file'] = 'LDIF ファイルを選択:';
$lang['dont_stop_on_errors'] = 'Don\'t stop on errors';

//ldif_import
$lang['add_action'] = '追加中...';
$lang['delete_action'] = '削除中...';
$lang['rename_action'] = '名称変更中...';
$lang['modify_action'] = '修正中...';
$lang['warning_no_ldif_version_found'] = 'バージョンが見つかりません。1 と仮定します。';
$lang['valid_dn_line_required'] = '有効な dn 行が必要です。';
$lang['missing_uploaded_file'] = 'アップロードファイルが見当たりません。';
$lang['no_ldif_file_specified'] = 'LDIF ファイルが指定されていません。もう一度行ってください。';
$lang['ldif_file_empty'] = 'アップロードされた LDIF ファイルが空です。';
$lang['empty'] = '空';
$lang['file'] = 'ファイル';
$lang['number_bytes'] = '%s バイト';

$lang['failed'] = '失敗しました';
$lang['ldif_parse_error'] = 'LDIF 解析エラー';
$lang['ldif_could_not_add_object'] = 'オブジェクトを追加できませんでした:';
$lang['ldif_could_not_rename_object'] = 'オブジェクトを名称変更できませんでした:';
$lang['ldif_could_not_delete_object'] = 'オブジェクトを削除できませんでした:';
$lang['ldif_could_not_modify_object'] = 'オブジェクトを修正できませんでした:';
$lang['ldif_line_number'] = '行番号:';
$lang['ldif_line'] = '行:';

// Exports
$lang['export_format'] = 'エクスポート書式';
$lang['line_ends'] = 'Line ends';
$lang['must_choose_export_format'] = 'エクスポート書式を選ばなければなりません。';
$lang['invalid_export_format'] = '無効なエクスポート書式';
$lang['no_exporter_found'] = 'No available exporter found.';
$lang['error_performing_search'] = '検索実行中にエラーに遭遇しました。';
$lang['showing_results_x_through_y'] = 'Showing results %s through %s.';
$lang['searching'] = '検索中...';
$lang['size_limit_exceeded'] = '通知です。検索サイズが制限を越えました。';
$lang['entry'] = 'エントリ';
$lang['ldif_export_for_dn'] = 'LDIF エクスポート: %s';
$lang['generated_on_date'] = 'Generated by phpLDAPadmin ( http://phpldapadmin.sourceforge.net/ ) on %s';
$lang['total_entries'] = '総エントリ';
$lang['dsml_export_for_dn'] = 'DSLM エクスポート: %s';
$lang['include_system_attrs'] = 'システム属性を含む';
$lang['csv_spreadsheet'] = 'CSV (スプレッドシート)';

// logins
$lang['could_not_find_user'] = 'ユーザー "%s" が見つかりません';
$lang['password_blank'] = 'You left the password blank.';
$lang['login_cancelled'] = 'ログインが取り消されました。';
$lang['no_one_logged_in'] = 'No one is logged in to that server.';
$lang['could_not_logout'] = 'ログアウトできませんでした。';
$lang['unknown_auth_type'] = '未知の auth_type: %s';
$lang['logged_out_successfully'] = 'サーバー <b>%s</b> からログアウトに成功しました';
$lang['authenticate_to_server'] = 'Authenticate to server %s';
$lang['warning_this_web_connection_is_unencrypted'] = '警告: このウェブ接続は暗号化されていません。';
$lang['not_using_https'] = 'You are not using \'https\'. Web browser will transmit login information in clear text.';
$lang['login_dn'] = 'ログイン DN';
$lang['user_name'] = 'ユーザー名';
$lang['password'] = 'パスワード';
$lang['authenticate'] = 'Authenticate';

// Entry browser
$lang['entry_chooser_title'] = 'エントリ選択';

// Index page
$lang['need_to_configure'] = 'phpLDAPadmin の設定を必要があります。ファイル \'config.php\' の変更をしてください。サンプル設定ファイルとして \'config.php.example\' を提供しています。';

// Mass deletes
$lang['no_deletes_in_read_only'] = '読み込み専用モードでは削除は許可されていません。';
$lang['error_calling_mass_delete'] = 'mass_delete.php 呼び出し中のエラーです。POST 値から mass_delete が見かりません。';
$lang['mass_delete_not_array'] = 'mass_delete POST 辺りが配列ではありません。';
$lang['mass_delete_not_enabled'] = '一括削除が有効ではありません。次に進む前に config.php でそれを有効にしてください。';
$lang['search_attrs_wrong_count'] = 'config.php にエラーがあります。The number of attributes in $search_attributes and $search_attributes_display is different';
$lang['mass_deleting'] = '一括削除中';
$lang['mass_delete_progress'] = 'サーバー "%s" から削除中';
$lang['malformed_mass_delete_array'] = 'おかしい mass_delete 配列です。';
$lang['no_entries_to_delete'] = '削除するエントリが選択されていません。';
$lang['deleting_dn'] = '%s 削除中';
$lang['total_entries_failed'] = '%s 個のエントリが %s 個のエントリ中で削除できませんでした。';
$lang['all_entries_successful'] = 'すべてのエントリの削除に成功しました。';
$lang['confirm_mass_delete'] = '%s エントリをサーバー %s から一括削除の確認';
$lang['yes_delete'] = 'はい, 削除します!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = '子エントリを持つエントリは名称変更できません (eg, the rename operation is not allowed on non-leaf entries)';
$lang['no_rdn_change'] = 'RDN を変更しませんでした';
$lang['invalid_rdn'] = '無効な RDN 値';
$lang['could_not_rename'] = 'エントリの名称変更が出来ませんでした';

// General errors
$lang['php5_unsupported'] = 'phpLDAPadmin は PHP 5 をサポートしていません。You will likely encounter many weird problems if you continue.';
$lang['mismatched_search_attr_config'] = '設定にエラーがあります。$search_attributes must have the same number of attributes as $search_attributes_display.';

// Password checker
$lang['passwords_match'] = 'パスワードが一致しました!';
$lang['passwords_do_not_match'] = 'パスワードが一致しません!';
$lang['password_checker_tool'] = 'パスワードチェックツール';
$lang['compare'] = '比較';
$lang['to'] = 'To';

?>
