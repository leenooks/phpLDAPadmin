<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/ja.php,v 1.5 2005/06/23 14:38:41 wurley Exp $


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
$lang['Search'] = '検索';
$lang['predefined_search_str'] = '事前定義された検索を選択';
$lang['predefined_searches'] = '事前定義検索';
$lang['no_predefined_queries'] = 'config.php で定義された照会はありません。';
$lang['export_results'] = '結果エクスポート';
$lang['unrecoginzed_search_result_format'] = '認識できない書式の検索結果です: %s';
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
$lang['view_schema_for'] = 'スキーマを閲覧 対象:';
$lang['refresh_expanded_containers'] = '展開済みの内容を再描画 対象:';
$lang['create_new_entry_on'] = 'エントリを新規作成 対象:';
$lang['new'] = '新規';
$lang['view_server_info'] = 'サーバーが提供する情報を閲覧';
$lang['import_from_ldif'] = 'LDIF ファイルからのインポートエントリ';
$lang['logout_of_this_server'] = 'このサーバーのログアウト';
$lang['logged_in_as'] = '次のコンテナでログイン: ';
$lang['this_base_dn_is_not_valid'] = 'これは有効な DN ではありません。';
$lang['this_base_dn_does_not_exist'] = 'このエントリは存在しません。';
$lang['read_only'] = '読み込み専用';
$lang['read_only_tooltip'] = 'この属性は phpLDAP 管理者により、読み込み専用で設定されています。';
$lang['could_not_determine_root'] = 'LDAP ツリーのルートが決定できません。';
$lang['ldap_refuses_to_give_root'] = 'LDAP サーバーが root を見えないように設定しているように見えます。';
$lang['please_specify_in_config'] = 'config.php でそれを指定してください。';
$lang['create_new_entry_in'] = 'Create a new entry in';
$lang['login_link'] = 'ログイン...';
$lang['login'] = 'ログイン';
$lang['base_entry_does_not_exist'] = 'ベースエントリが存在しません。';
$lang['create_it'] = '作成しますか?';

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
$lang['add_new_attribute'] = '新規属性を追加';
$lang['add_new_objectclass'] = '新規 ObjectClass を追加する';
$lang['hide_internal_attrs'] = '内部属性を隠す';
$lang['show_internal_attrs'] = '内部属性を表示';
$lang['attr_name_tooltip'] = '属性タイプ \'%s\' のためのスキーマ定義を見るためにクリックしてください。';
$lang['none'] = 'なし';
$lang['no_internal_attributes'] = '内部属性がありません';
$lang['no_attributes'] = 'このエントリは属性を持っていません';
$lang['save_changes'] = '変更を保存';
$lang['add_value'] = '値追加';
$lang['add_value_tooltip'] = '追加値を属性「%s」に追加する';
$lang['refresh_entry'] = '再描画';
$lang['refresh_this_entry'] = 'このエントリを再描画する';
$lang['delete_hint'] = 'ヒント: 属性を削除するにはテキストフィールドを空にして保存をクリックします。';
$lang['attr_schema_hint'] = 'ヒント: 属性のスキーマを閲覧するには、属性名をクリックします。';
$lang['attrs_modified'] = 'いくつかの属性 (%s) は修正され、下記でハイライトされました。';
$lang['attr_modified'] = 'ひとつの属性 (%s) は修正され、下記でハイライトされました。';
$lang['viewing_read_only'] = '読み込みモードでエントリを閲覧中。';
$lang['no_new_attrs_available'] = 'このエントリに利用可能な新規属性はありません。';
$lang['no_new_binary_attrs_available'] = 'このエントリに利用可能な新規バイナリ属性はありません。';
$lang['binary_value'] = 'バイナリ値';
$lang['add_new_binary_attr'] = '新規バイナリ属性を追加';
$lang['alias_for'] = '注: \'%s\' は \'%s\' のエイリアスです。';
$lang['required_for'] = 'objectClass %s の必須属性';
$lang['required_by_entry'] = 'この属性は RDN に必要です。';
$lang['download_value'] = 'ダウンロード値';
$lang['delete_attribute'] = '属性削除';
$lang['true'] = 'true';
$lang['false'] = 'false';
$lang['none_remove_value'] = 'いいえ、値を削除します';
$lang['really_delete_attribute'] = '本当に属性を削除';
$lang['add_new_value'] = '新規値追加';

// Schema browser
$lang['schema_retrieve_error_1']='このサーバーはすべての LDAP プロトコルをサポートしていません。';
$lang['schema_retrieve_error_2']='この PHP のバージョンは正確に照会を行えません。';
$lang['schema_retrieve_error_3']='あるいは、phpLDAPadmin は、あなたのサーバーからスキーマを取得する方法を知りません。';
$lang['schema_retrieve_error_4']='Or lastly, LDAP サーバーはこの情報を提供していません。';
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
$lang['syntax_oid'] = '文法 OID';
$lang['not_applicable'] = '適用可能ではありません';
$lang['not_specified'] = '指定されていません';
$lang['character']='character'; 
$lang['characters']='characters';
$lang['used_by_objectclasses']='objectClass を使する';
$lang['used_by_attributes']='属性で使する';
$lang['maximum_length']='最大長';
$lang['attribute_types']='属性タイプ';
$lang['syntaxes']='文法一覧';
$lang['matchingrules']='一致ルール';
$lang['oid']='OID';
$lang['obsolete']='旧式';
$lang['ordering']='Ordering';
$lang['substring_rule']='副文字列ルール';
$lang['single_valued']='単一の値';
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
$lang['permanently_delete_children'] = 'さらに永久にすべての子を削除しますか?';
$lang['entry_is_root_sub_tree'] = 'このエントリは %s エントリを含むサブツリーのルートです。';
$lang['view_entries'] = 'エントリ閲覧';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin は再帰的に、このエントリとその子のすべての %s を削除できます。このアクションが削除するすべてのエントリの一覧は、下記を参照してください。本当に本当に削除しますか?';
$lang['confirm_recursive_delete_note'] = '注: これは潜在的に非常に危険です。また、自己責任でこれをします。このオペレーションは取り消せません。エイリアス・referralとその他の問題を考察を持ってください。';
$lang['delete_all_x_objects'] = '%s オブジェクトをすべて削除';
$lang['recursive_delete_progress'] = '再帰削除進行';
$lang['entry_and_sub_tree_deleted_successfully'] = 'エントリ %s とサブツリーの削除に成功しました。';
$lang['failed_to_delete_entry'] = 'エントリ %s の削除に失敗しました';
$lang['list_of_entries_to_be_deleted'] = 'エントリの一覧を削除しました:';
$lang['sure_permanent_delete_object']='本当にこのオブジェクトを永続的に削除しますか?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'phpLDAPadmin の設定で属性 "%s" は読み込み専用に設定されています。';
$lang['no_attr_specified'] = '属性名が指定されていません。';
$lang['no_dn_specified'] = 'DN が指定されていません';

// Adding attributes
$lang['left_attr_blank'] = '属性値が空です。戻ってからもう一度試してください。';
$lang['failed_to_add_attr'] = '属性の追加に失敗しました。';
$lang['file_empty'] = 'あなたの選んだファイルは空か存在しないかのいずれかです。戻ってからもう一度試してください。';
$lang['invalid_file'] = 'セキュリティエラー: アップロードされたファイルは悪意のあるものかもしれません。';
$lang['warning_file_uploads_disabled'] = 'PHP の設定でファイルのアップロードが無効です。続行する前に、php.ini を確認してください。';
$lang['uploaded_file_too_big'] = 'アップロードされたファイルが大きすぎます。php.ini の upload_max_size 設定を確認してください。';
$lang['uploaded_file_partial'] = '選択したファイルは、部分的なアップロードでした。likley due to a network error.';
$lang['max_file_size'] = '最大ファイルサイズ: %s';

// Updating values
$lang['modification_successful'] = '修正に成功しました!';
$lang['change_password_new_login'] = 'パスワードを変更したので、今から新しいパスワードで再ログインしなければなりません。';

// Adding objectClass form
$lang['new_required_attrs'] = '新規必須属性';
$lang['requires_to_add'] = 'This action requires you to add';
$lang['new_attributes'] = '新規属性';
$lang['new_required_attrs_instructions'] = 'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'それは objectClass に必要です。このフォームでそうすることができます。';
$lang['add_oclass_and_attrs'] = 'ObjectClass と属性を追加';
$lang['objectclasses'] = 'ObjectClass 一覧';

// General
$lang['chooser_link_tooltip'] = 'Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'サーバーは読み込みモードなので､更新を実行できません。';
$lang['bad_server_id'] = '間違ったサーバー ID';
$lang['not_enough_login_info'] = 'サーバーにログインする情報が不足しています。設定を確認してください。';
$lang['could_not_connect'] = 'LDAP サーバーに接続できませんでした。';
$lang['could_not_connect_to_host_on_port'] = '"%s" のポート "%s" に接続できませんでした';
$lang['could_not_perform_ldap_mod_add'] = 'ldap_mod_add 操作を実行できませんでした。';
$lang['home'] = 'ホーム';
$lang['help'] = 'ヘルプ';
$lang['success'] = '成功';
$lang['server_colon_pare'] = 'サーバー: ';
$lang['look_in'] = 'Looking in: ';
$lang['missing_dn_in_query_string'] = '照会文字列に DN が指定されていません!';
$lang['back_up_p'] = 'バックアップ...';
$lang['no_entries'] = 'エントリがありません';
$lang['could_not_det_base_dn'] = 'ベース DN を決定することが出来ませんでした';
$lang['reasons_for_error']='This could happen for several reasons, the most probable of which are:';
$lang['yes']='はい';
$lang['no']='いいえ';
$lang['go']='Go';
$lang['delete']='削除';
$lang['back']='戻る';
$lang['object']='オブジェクト';
$lang['delete_all']='すべて削除';
$lang['hint'] = 'ヒント';
$lang['bug'] = '不都合';
$lang['warning'] = '警告';
$lang['light'] = 'light'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = '進む &gt;&gt;';
$lang['no_blowfish_secret'] = '$blowfish_secret が config.php で設定されていないので、phpLDAPadmin は安全に機密情報を暗号化と解読をすることができません。config.php を編集し、秘密文字列を $blowfish_secret に設定するする必要があります。';
$lang['jpeg_dir_not_writable'] = 'phpLDAPadmin の設定ファイル config.php で、書き込み可能なディレクトリを $jpeg_temp_dir に設定してください。';
$lang['jpeg_dir_not_writable_error'] = '$jpeg_temp_dir で指定したディレクトリ %s に書き込みできません。ウェブサーバーがそこにファイルを書き込みできることを確認してください。';
$lang['jpeg_unable_toget'] = '属性 %s 用に LDAP サーバーから jpeg データを取得できませんでした。';
$lang['jpeg_delete'] = '写真を削除';

// Add value form
$lang['add_new'] = '新規追加';
$lang['value_to'] = 'value to';
$lang['distinguished_name'] = '関連名';
$lang['current_list_of'] = 'Current list of';
$lang['values_for_attribute'] = '属性の値';
$lang['inappropriate_matching_note'] = '注: LDAP サーバーでこの属性の EQUALITY ルールをセットアップしていなかった場合、"inappropriate matching" エラーを取得するでしょう。';
$lang['enter_value_to_add'] = '追加したい値を入力する:';
$lang['new_required_attrs_note'] = '注: この objectClass が要求する新しい属性を入力することが要求かもしれません';
$lang['syntax'] = '文法';

//copy.php
$lang['copy_server_read_only'] = 'サーバーが読み込みモードなので、更新を行うことができません。';
$lang['copy_dest_dn_blank'] = '対象 DN がブランクで残されました。';
$lang['copy_dest_already_exists'] = '対象エントリ (%s) は既に存在します。';
$lang['copy_dest_container_does_not_exist'] = '対象先コンテナー (%s) は存在しません。';
$lang['copy_source_dest_dn_same'] = '対象元と対象先 DN が同じです。';
$lang['copy_copying'] = 'Copying ';
$lang['copy_recursive_copy_progress'] = '再帰コピー進行';
$lang['copy_building_snapshot'] = 'コピーするツリーのスナップショットを構築中... ';
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
$lang['structural_object_class_cannot_remove'] = 'これは構造 ObjectClass なので削除できませんでした。';
$lang['structural'] = '構造';

//copy_form.php
$lang['copyf_title_copy'] = 'コピー ';
$lang['copyf_to_new_object'] = 'to a new object';
$lang['copyf_dest_dn'] = '対象 DN';
$lang['copyf_dest_dn_tooltip'] = 'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = '対象サーバー';
$lang['copyf_note'] = 'Hint: スキーマ違反がなければ、異なるサーバー間のコピー処理のみ行います。';
$lang['copyf_recursive_copy'] = '同様にこのオブジェクトのすべての子を再帰コピーします。';
$lang['recursive_copy'] = '再帰コピー';
$lang['filter'] = 'フィルター';
$lang['filter_tooltip'] = '再帰的なコピーを行なう場合、このフィルタと一致するエントリのみコピーしてください。';
$lang['delete_after_copy'] = 'コピーの後に削除 (移動):';
$lang['delete_after_copy_warn'] = 'Make sure your filter (above) will select all child records.';

//create.php
$lang['create_required_attribute'] = 'You left the value blank for required attribute (%s).';
$lang['redirecting'] = 'リダイレクト中...';
$lang['here'] = 'ここ';
$lang['create_could_not_add'] = 'LDAP サーバーにオブジェクトを追加できませんでした。';

//create_form.php
$lang['createf_create_object'] = 'オブジェクト作成';
$lang['createf_choose_temp'] = 'テンプレート選択';
$lang['createf_select_temp'] = '作成処理のテンプレートを選択';
$lang['save_as_file'] = 'ファイルに保存';
$lang['rdn_field_blank'] = 'RDN フィールドが空です。';
$lang['container_does_not_exist'] = '指定したコンテナー(%s)が存在しません。もう一度行ってください。';
$lang['no_objectclasses_selected'] = 'このオブジェクトのためのいくつかの ObjectClass を選択しませんでした。戻ってそのように実行してください。';
$lang['hint_structural_oclass'] = 'ヒント: ひとつの構造 objectClass を選択しなければなりません (上で強調表示されています)';

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

// search_form_simple.php
$lang['equals'] = 'に一致';
$lang['starts with'] = 'で始まる';
$lang['contains'] = 'を含む';
$lang['ends with'] = 'で終わる';
$lang['sounds like'] = 'に近い';

// server_info.php
$lang['could_not_fetch_server_info'] = 'サーバーから LDAP 情報を取得できませんでした。これはあなたの PHP バージョンの<a href="http://bugs.php.net/bug.php?id=29587">不都合</a>によるかもしれません。あるいは、あなたの LDAP サーバーは、LDAP クライアントが RootDSE にアクセスするのを防ぐアクセス制御指定をしています。';
$lang['server_info_for'] = 'サーバー情報: ';
$lang['server_reports_following'] = 'サーバーは自分自身で次の情報を報告しました。';
$lang['nothing_to_report'] = 'このサーバーはなにも報告するものはありません。';

//update.php
$lang['update_array_malformed'] = 'update_array がおかしいです。これは phpLDAPadmin のバグかもしれませんので、報告してください。';
$lang['could_not_perform_ldap_modify'] = 'ldap_modify 操作が実行できませんでした。';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = '変更をしたいですか?';
$lang['attribute'] = '属性';
$lang['old_value'] = '古い値';
$lang['new_value'] = '新しい値';
$lang['attr_deleted'] = '[属性を削除しました]';
$lang['commit'] = 'コミット';
$lang['cancel'] = '取り消し';
$lang['you_made_no_changes'] = '変更はありません';
$lang['go_back'] = '戻る';
$lang['unable_create_samba_pass'] = 'samba パスワードを作成できませんでした。template_conf.php の設定を確認してください。';

// welcome.php
$lang['welcome_note'] = '左へのメニューを使用して操作します';
$lang['credits'] = 'クレジット';
$lang['changelog'] = '変更履歴';
$lang['donate'] = '寄附';
$lang['pla_logo'] = 'phpLDAPadmin ロゴ';

// Donate.php
$lang['donation_instructions'] = 'phpLDAPadmin プロジェクトに資金を寄贈するためには、PayPal ボタンのうちの 1 つを下に使用してください。';
$lang['donate_amount'] = '%s を寄贈';

$lang['purge_cache'] = 'キャッシュ破棄';
$lang['no_cache_to_purge'] = '破棄するキャッシュはありませんでした。';
$lang['done_purging_caches'] = '%s バイトのキャッシュを破棄しました。';
$lang['purge_cache_tooltip'] = 'サーバースキーマを含む、phpLDAPadmin のすべてのキャッシュを破棄しました。';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = '安全でないファイル名: ';
$lang['no_such_file'] = 'ファイルがありません: ';

//function.php
$lang['auto_update_not_setup'] = '設定ファイルの <b>%s</b>You の auto_uid_numbers が有効ですが、
                                  auto_uid_number_mechanism が指定されていません。
                                  この問題を修正してください。.';
$lang['uidpool_not_set'] = 'サーバー <b>%s</b> の設定で、"auto_uid_number_mechanism" を "uidpool" に指定していますが、
                            audo_uid_number_uid_pool_dn を指定していません。
                            続行する前にそれを指定してください';
$lang['uidpool_not_exist'] = 'It appears that the uidPool you specified in your configuration ("%s")
                              does not exist.';
$lang['specified_uidpool'] = 'サーバー <b>%s</b> の設定で「search」に「auto_uid_number_mechanism」を指定しました。
                              しかし「auto_uid_number_search_base」が指定されていません。
                              これを実行前に指定してください。';
$lang['auto_uid_invalid_credential'] = 'Unable to bind to <b>%s</b> with your with auto_uid credentials. 設定ファイルを確認してください。'; 
$lang['bad_auto_uid_search_base'] = 'phpLDAPadmin の設定で、サーバー %s に無効な auto_uid_search_base が指定されました';
$lang['auto_uid_invalid_value'] = '設定ファイルの auto_uid_number_mechanism の指定が間違った値("%s")です。
                                   "uidpool" と "search" のみ有効です。
                                   この問題を修正してください。';
$lang['error_auth_type_config'] = 'エラー: 設定ファイルのにエラーがあります。$servers セクションの auth_type は
                                    「session」「cookie」「config」のみっつの値のみ許可されていますが、
                                    許可されない「%s」が入力されました。';
$lang['unique_attrs_invalid_credential'] = 'Unable to bind to <b>%s</b> with your with unique_attrs credentials. 設定ファイルを確認してください。'; 
$lang['unique_attr_failed'] = '<b>%s</b> (<i>%s</i>) の <b>%s</b> への追加の試みは許可<b>されていません</b>。<br />その属性/値は別のエントリが所有しています。<p>そのエントリを<a href=\'%s\'>検索</a>などするしょう。';
$lang['php_install_not_supports_tls'] = 'インストールされている PHP は TLS をサポートしていません。';
$lang['could_not_start_tls'] = 'TLS を開始できません。LDAP サーバーの設定を確認してください。';
$lang['could_not_bind_anon'] = 'サーバーに匿名接続できませんでした。';
$lang['could_not_bind'] = 'LDAP サーバーに接続できませんでした。';
$lang['anonymous_bind'] = '匿名接続';
$lang['bad_user_name_or_password'] = 'ユーザー名かパスワードがおかしいです。もう一度行ってください。';
$lang['redirecting_click_if_nothing_happens'] = 'リダイレクト中... もし何も起こらなかったらここをクリックしてください。';
$lang['successfully_logged_in_to_server'] = 'サーバー <b>%s</b> へのログインに成功しました';
$lang['could_not_set_cookie'] = 'cookie を設定できませんでした。';
$lang['ldap_said'] = 'LDAP の回答: %s';
$lang['ferror_error'] = 'エラー';
$lang['fbrowse'] = '閲覧';
$lang['delete_photo'] = '写真削除';
$lang['install_not_support_blowfish'] = 'インストールされた PHP は blowfish 暗号化をサポートしていません。';
$lang['install_not_support_md5crypt'] = 'インストールされた PHP は md5crypt 暗号化をサポートしていません。';
$lang['install_no_mash'] = 'インストールされた PHP は mhash() をサポートしていません。SHA ハッシュをすることができません。';
$lang['jpeg_contains_errors'] = 'エラーを含む jpeg 写真<br />';
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
	<tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Please check and see if this bug has been reported here</a>.</center></td></tr>
	<tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>If it hasnt been reported, you may report this bug by clicking here</a>.</center></td></tr>
	</table></center><br />';
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
$lang['generated_on_date'] = '%s に phpLDAPadmin ( http://www.phpldapadmin.com/ ) で生成しました。';
$lang['total_entries'] = '総エントリ';
$lang['dsml_export_for_dn'] = 'DSLM エクスポート: %s';
$lang['include_system_attrs'] = 'システム属性を含む';
$lang['csv_spreadsheet'] = 'CSV (スプレッドシート)';

// logins
$lang['password_blank'] = 'パスワードが空です。';
$lang['no_one_logged_in'] = 'No one is logged in to that server.';
$lang['could_not_logout'] = 'ログアウトできませんでした。';
$lang['unknown_auth_type'] = '未知の auth_type: %s';
$lang['logged_out_successfully'] = 'サーバー <b>%s</b> からログアウトに成功しました';
$lang['authenticate_to_server'] = 'サーバー %s へ認証';
$lang['warning_this_web_connection_is_unencrypted'] = '警告: このウェブ接続は暗号化されていません。';
$lang['not_using_https'] = 'あなたは \'https\' を使っていません。ログイン情報はブラウザからクリアテキストで送信されます、';
$lang['login_dn'] = 'ログイン DN';
$lang['user_name'] = 'ユーザー名';
$lang['password'] = 'パスワード';
$lang['authenticate'] = 'Authenticate';
$lang['login_not_allowed'] = 'すみません、この LDAP サーバーと phpLDAPadmin を使用することを許可していません。';

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
$lang['mismatched_search_attr_config'] = '設定にエラーがあります。$search_attributes は $search_attributes_display の属性と同じ数を持たなければいけません。';

// Password checker
$lang['passwords_match'] = 'パスワードが一致しました!';
$lang['passwords_do_not_match'] = 'パスワードが一致しません!';
$lang['password_checker_tool'] = 'パスワードチェックツール';
$lang['compare'] = '比較';
$lang['to'] = 'To';

// Templates
$lang['using'] = 'Using the';
$lang['switch_to'] = 'You may switch to the ';
$lang['default_template'] = 'デフォルトテンプレート';

// template_config
$lang['user_account'] = 'ユーザーアカウント (posixAccount)';
$lang['address_book_inet'] = 'アドレス帳エントリ (inetOrgPerson)';
$lang['address_book_moz'] = 'アドレス帳エントリ (mozillaOrgPerson)';
$lang['kolab_user'] = 'Kolab ユーザーエントリ';
$lang['organizational_unit'] = '所属組織';
$lang['organizational_role'] = '所属職務';
$lang['posix_group'] = 'Posix グループ';
$lang['samba_machine'] = 'Samba NT マシン';
$lang['samba3_machine'] = 'Samba 3 NT マシン';
$lang['samba_user'] = 'Samba ユーザー';
$lang['samba3_user'] = 'Samba 3 ユーザー';
$lang['samba3_group'] = 'Samba 3 グループマップ';
$lang['dns_entry'] = 'DNS エントリ';
$lang['simple_sec_object'] = '簡易セキュリティオブジェクト';
$lang['courier_mail_account'] = 'Courier メールアカウント';
$lang['courier_mail_alias'] = 'Courier メール別名';
$lang['ldap_alias'] = 'LDAP 別名';
$lang['sendmail_cluster'] = 'Sendmail クラスター';
$lang['sendmail_domain'] = 'Sendmail ドメイン';
$lang['sendmail_alias'] = 'Sendmail エイリアス';
$lang['sendmail_virt_dom'] = 'Sendmail 仮想ドメイン';
$lang['sendmail_virt_users'] = 'Sendmail 仮想ユーザー';
$lang['sendmail_relays'] = 'Sendmail リレー';
$lang['custom'] = 'カスタム';
$lang['samba_domain_name'] = '自分の Samba ドメイン名';
$lang['administrators'] = 'Administrators';
$lang['users'] = 'Users';
$lang['guests'] = 'Guests';
$lang['power_users'] = 'Power Users';
$lang['account_ops'] = 'Account Operators';
$lang['server_ops'] = 'Server Operators';
$lang['print_ops'] = 'Print Operators';
$lang['backup_ops'] = 'backup Operators';
$lang['replicator'] = 'Replicator';
$lang['unable_smb_passwords'] = ' Samba のパスワードを作成できませんでした。template_config.php の設定を確認してください。';
$lang['err_smb_conf'] = 'エラー: samba の設定にエラーがあります。';
$lang['err_smb_no_name_sid'] = 'エラー: samba ドメインの名前および sid を提供する必要があります。';
$lang['err_smb_no_name'] = 'エラー: 名前は samba ドメインに提供されませんでした。';
$lang['err_smb_no_sid'] = 'エラー: sid は samba ドメインに提供されませんでした。';

// Samba Account Template
$lang['samba_account'] = 'Samba アカウント';
$lang['samba_account_lcase'] = 'samba アカウント';

// New User (Posix) Account
$lang['t_new_user_account'] = '新規ユーザーアカウント';
$lang['t_hint_customize'] = 'ヒント: このテンプレートをカスタマイズする場合、ファイル templates/creation/new_user_template.php を編集します。';
$lang['t_name'] = '名前';
$lang['t_first_name'] = '名前';
$lang['t_last_name'] = '苗字';
$lang['t_first'] = 'first';
$lang['t_last'] = 'last';
$lang['t_common_name'] = '共通名';
$lang['t_user_name'] = 'ユーザー名';
$lang['t_password'] = 'パスワード';
$lang['t_encryption'] = '暗号化';
$lang['t_login_shell'] = 'ログインシェル';
$lang['t_home_dir'] = 'ホームディレクトリ';
$lang['t_uid_number'] = 'UID 番号';
$lang['t_auto_det'] = '(自動採決)';
$lang['t_group'] = 'グループ';
$lang['t_gid_number'] = 'GID 番号';
$lang['t_err_passwords'] = 'パスワードが一致しません。戻ってからもう一度試してください。';
$lang['t_err_field_blank'] = '%s ブランクを残すことはできません。戻ってからもう一度試してください。';
$lang['t_err_field_num'] = 'フィールド %s は数値のみ入力で出来ます。戻ってからもう一度試してください。';
$lang['t_err_bad_container'] = '指定した内容(%s)は存在しません。戻ってからもう一度試してください。';
$lang['t_confirm_account_creation'] = 'アカウント作成確認';
$lang['t_secret'] = '[secret]';
$lang['t_create_account'] = 'アカウント作成';

// New Address Template
$lang['t_new_address'] = '新規アドレス帳エントリ';
$lang['t_organization'] = '組織';
$lang['t_address'] = '住所';
$lang['t_city'] = '都市';
$lang['t_postal_code'] = '郵便番号';
$lang['t_street'] = '築町村';
$lang['t_work_phone'] = '業務電話';
$lang['t_fax'] = 'Fax';
$lang['t_mobile'] = '携帯電話';
$lang['t_email'] = '電子メール';
$lang['t_container'] = 'コンテナー';
$lang['t_err_cn_blank'] = '一般名を空にすることは出来ません。戻ってからもう一度試してください。';
$lang['t_confim_creation'] = 'エントリ作成の確認:';
$lang['t_create_address'] = 'アドレス作成';

// default template
$lang['t_check_pass'] = 'パスワード検査...';

// compare form
$lang['compare'] = '比較';
$lang['comparing'] = '次の DN と比較中';
$lang['compare_dn'] = 'Compare another DN with';
$lang['with'] = 'with ';
$lang['compf_source_dn'] = '元の DN';
$lang['compf_dn_tooltip'] = 'この DN と別のものを比較';
$lang['switch_entry'] = 'エントリの切り替え';
$lang['no_value'] = '値がありません';
$lang['compare_with'] = '別のエントリと比較';
$lang['need_oclass'] = 'この属性 %s を追加するには次の ObjectClass のうちひとつを必要とします。';

// Time out page
$lang['session_timed_out_1'] = 'セッションは';
$lang['session_timed_out_2'] = '分活動しなかったのでタイムアウトです。自動でログアウトしました。';
$lang['log_back_in'] = '再ログインするには次のリンクをクリックしてください:';
$lang['session_timed_out_tree'] = '(セッションはタイムアウトです。自動でログアウトしました)';
$lang['timeout_at'] = '%s は活動的でなかったのでログアウト';
?>
