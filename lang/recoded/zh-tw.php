<?php

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

// Based on en.php version 1.58

// Search form
$lang['simple_search_form_str']='簡易搜尋表單';
$lang['advanced_search_form_str']='進階搜尋表單';
$lang['server']='伺服器';

// search_form_simple.php
$lang['search_for_entries_whose']='選擇搜尋條件';
$lang['base_dn']='基礎識別名稱';
$lang['search_scope']='搜尋範圍';
$lang['show_attributes']='顯示屬性';
$lang['Search']='搜尋';
$lang['predefined_search_str']='以預先定義好的條件搜尋';
$lang['predefined_searches']='預先定義好的搜尋';
$lang['no_predefined_queries']='config.php中沒有預先定義的搜尋條件';

// Tree browser
$lang['request_new_feature']='建議新功能';
$lang['report_bug']='回報問題';
$lang['schema']='schema';
$lang['search']='搜尋';
$lang['create']='建立';
$lang['info']='系統資訊';
$lang['import']='匯入';
$lang['refresh']='重新整理';
$lang['logout']='登出';
$lang['create_new']='建立新紀錄';
$lang['view_schema_for']='查閱schema';
$lang['refresh_expanded_containers']='重新整理所有展開的集合';
$lang['create_new_entry_on']='建立新紀錄於';
$lang['new']='新';
$lang['view_server_info']='查閱伺服器提供的資訊';
$lang['import_from_ldif']='從LDIF檔案匯入紀錄';
$lang['logout_of_this_server']='登出此伺服器';
$lang['logged_in_as']='登入為';
$lang['read_only']='唯讀';
$lang['could_not_determine_root']='找不到LDAP根目錄';
$lang['ldap_refuses_to_give_root']='此LDAP伺服器設定為不顯示根節點';
$lang['please_specify_in_config']='請在config.php中指定';
$lang['create_new_entry_in']='建立新紀錄於';
$lang['login_link']='登入...';
$lang['login']='登入';

// Entry display
$lang['delete_this_entry']='刪除此紀錄';
$lang['delete_this_entry_tooltip']='在您確認之後刪除此記錄';
$lang['copy_this_entry']='複製此記錄';
$lang['copy_this_entry_tooltip']='將此記錄複製到您選擇的目的地';
$lang['export']='匯出';
$lang['export_tooltip']='將此物件匯出';
$lang['export_subtree_tooltip']='將此物件及所有子物件匯出';
$lang['export_subtree']='將此子樹匯出';
$lang['create_a_child_entry']='建立子記錄';
$lang['rename_entry']='重新命名';
$lang['rename']='更名';
$lang['add']='新增';
$lang['view']='查閱';
$lang['view_one_child']='查閱 1 子紀錄';
$lang['view_children']='查閱 %s 子紀錄';
$lang['add_new_attribute']='新增屬性';
$lang['add_new_objectclass']='新增ObjectClass';
$lang['hide_internal_attrs']='不顯示隱藏屬性';
$lang['show_internal_attrs']='顯示隱藏屬性';
$lang['attr_name_tooltip']='查閱屬性 ';
$lang['none']='none';
$lang['save_changes']='儲存';
$lang['add_value']='加入新數值';
$lang['add_value_tooltip']='增加一個附加值到屬性 \"%s\"';
$lang['refresh_entry']='重新整理';
$lang['refresh_this_entry']='重整此筆資';
$lang['delete_hint']='提示: 將欄位數值刪除並按存檔即可刪除該屬性';
$lang['attr_schema_hint']='提示: 在該屬性上點一下即可查閱所使用的schema';
$lang['attrs_modified']='修改後的屬性為 (%s) 並以不同的顏色區分';
$lang['attr_modified']='修改後的屬性為 (%s) 並以不同的顏色區分';
$lang['viewing_read_only']='以唯讀模式查閱資料';
$lang['no_new_attrs_available']='此筆資料沒有新屬性';
$lang['no_new_binary_attrs_available']='此筆資料沒有二進位屬性';
$lang['binary_value']='二進位數值';
$lang['add_new_binary_attr']='新增二進位屬性';
$lang['alias_for']='Alias for %s';
$lang['download_value']='下載數值';
$lang['delete_attribute']='刪除屬性';
$lang['true']='true';
$lang['false']='false';
$lang['none_remove_value']='空白, 移除該值';
$lang['really_delete_attribute']='確定要刪除屬性？';
$lang['add_new_value']='新增數值';

// Schema browser
$lang['the_following_objectclasses']='此LDAP伺服器支援下列objectClasses';
$lang['the_following_attributes']='此LDAP伺服器支援下列屬性';
$lang['the_following_matching']='此LDAP伺服器支援下列搜尋規則';
$lang['the_following_syntaxes']='此LDAP伺服器支援下列語法';
$lang['schema_retrieve_error_1']='此伺服器並未完全支援LDAP協定';
$lang['schema_retrieve_error_2']='您的php版本並未正確執行此查詢';
$lang['schema_retrieve_error_3']='或是phpLDAPadmin不知道如何從您的伺服器擷取schema';
$lang['jump_to_objectclass']='選擇objectClass';
$lang['jump_to_attr']='選擇屬性';
$lang['jump_to_matching_rule']='選擇配對規則';
$lang['schema_for_server']='伺服器的schema';
$lang['required_attrs']='必要屬性';
$lang['optional_attrs']='選擇性屬性';
$lang['optional_binary_attrs']='選擇性二進位屬性';
$lang['OID']='OID';
$lang['aliases']='別名';
$lang['desc']='說明';
$lang['no_description']='沒有說明';
$lang['name']='名稱';
$lang['equality']='相同';
$lang['is_obsolete']='這個 objectClass 是必須得.';
$lang['inherits']='繼承';
$lang['inherited_from']='已繼承於';
$lang['jump_to_this_oclass']='選擇此objectClass定義';
$lang['matching_rule_oid']='OID配對規則';
$lang['syntax_oid']='OID語法';
$lang['not_applicable']='未應用的';
$lang['not_specified']='未指定的';
$lang['character']='字元';
$lang['characters']='字元集';
$lang['used_by_objectclasses']='已被 objectClasses 使用';
$lang['used_by_attributes']='已被屬性使用';
$lang['maximum_length']='最大長度';
$lang['attributes']='屬性型態';
$lang['syntaxes']='語法';
$lang['matchingrules']='配對規則';
$lang['oid']='OID';
$lang['obsolete']='必要';
$lang['ordering']='排序中';
$lang['substring_rule']='子字串規則';
$lang['single_valued']='單一值';
$lang['collective']='集合';
$lang['user_modification']='使用者修改';
$lang['usage']='使用量';
$lang['could_not_retrieve_schema_from']='無法恢復 Schema 於';
$lang['type']='類型';

// Deleting entries
$lang['entry_deleted_successfully']='資料 %s 成功的刪除';
$lang['you_must_specify_a_dn']='您必須指定一個識別名稱';
$lang['could_not_delete_entry']='無法刪除記錄 %s';
$lang['no_such_entry']='無此記錄 %s';
$lang['delete_dn']='刪除 %s';
$lang['permanently_delete_children']='永久刪除所有子資料？';
$lang['entry_is_root_sub_tree']='此資料';
$lang['view_entries']='查閱資料';
$lang['confirm_recursive_delete']='phpLDAPadmin可以幫您刪除此資料與所有 %s 子資料.下列資料將會被刪除，您確定要刪除？';
$lang['confirm_recursive_delete_note']='請注意：這項功能無法還原，且可能是非常危險的，且可能因此而造成問題';
$lang['delete_all_x_objects']='刪除所有 %s 物件';
$lang['recursive_delete_progress']='遞迴刪除進度';
$lang['entry_and_sub_tree_deleted_successfully']='成功刪除資料 %s 與子資料';
$lang['failed_to_delete_entry']='無法刪除資料 %s';
$lang['list_of_entries_to_be_deleted']='下列資料將被刪除:';
$lang['sure_permanent_delete_object']='您確定要永久刪除此物件？';
$lang['dn']='識別名稱';

// Deleting attributes
$lang['attr_is_read_only']='屬性 \"%s\" 在phpLDAPadmin中被設定成唯讀';
$lang['no_attr_specified']='請指定屬性名稱';
$lang['no_dn_specified']='請指定識別名稱';

// Adding attributes
$lang['left_attr_blank']='您沒有輸入屬性數值，請重新輸入';
$lang['failed_to_add_attr']='無法新增此屬性';

// Updating values
$lang['modification_successful']='修改成功';
$lang['change_password_new_login']='您的密碼已更新，請登出並以新密碼重新登入';

// Adding objectClass form
$lang['new_required_attrs']='新必須屬性';
$lang['requires_to_add']='執行此項操作前您必須先加入';
$lang['new_attributes']='新屬性';
$lang['new_required_attrs_instructions']='作法：在新增objectClass之前，您必須指定';
$lang['that_this_oclass_requires']='此objectClass為必須，您可以在此表單中指定';
$lang['add_oclass_and_attrs']='新增ObjectClass與屬性';
$lang['objectclasses']='ObjectClasses';

// General
$lang['chooser_link_tooltip']='點選此處即可以圖形介面選擇資料(識別名稱)';
$lang['no_updates_in_read_only_mode']='伺服器為唯讀模式，無法更新資料';
$lang['bad_server_id']='錯誤的 server id';
$lang['not_enough_login_info']='沒有足夠的資訊以進行登入伺服器,請檢查您的設定值';
$lang['could_not_connect']='無法連接LDAP伺服器';
$lang['could_not_connect_to_host_on_port']='無法透過port \"%s\" 連接到 \"%s\" ';
$lang['could_not_perform_ldap_mod_add']='無法執行ldap_mod_add操作';
$lang['bad_server_id_underline']='錯誤的 server_id:';
$lang['success']='成功';
$lang['server_colon_pare']='伺服器:';
$lang['look_in']='登入:';
$lang['missing_server_id_in_query_string']='查詢字串中並未指定server ID';
$lang['missing_dn_in_query_string']='查詢字串中並未指定識別名稱';
$lang['back_up_p']='備份...';
$lang['no_entries']='沒有任何紀錄';
$lang['not_logged_in']='尚未登入';
$lang['could_not_det_base_dn']='無法確定 Base DN';
$lang['please_report_this_as_a_bug']='請回報這個問題至 Bug 系統';
$lang['reasons_for_error']='此問題可能有好幾個原因，最有可能的是：';
$lang['yes']='確定';
$lang['no']='否';
$lang['go']='衝!!';
$lang['delete']='刪除';
$lang['back']='返回';
$lang['object']='物件';
$lang['delete_all']='刪除全部';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint']='提示';
$lang['bug']='臭蟲';
$lang['warning']='警告';
$lang['light']='light';
$lang['proceed_gt']='下一步 >>';

// Add value form
$lang['add_new']='新增';
$lang['value_to']='數值至';
$lang['distinguished_name']='識別名稱';
$lang['current_list_of']='此屬性有';
$lang['values_for_attribute']='下列數值:';
$lang['inappropriate_matching_note']='請注意：若您的LDAP伺服器中並未設定等式規則，您將會遇到\"無適合的比對\"錯誤';
$lang['enter_value_to_add']='請輸入您要加入的數值';
$lang['new_required_attrs_note']='請注意：由於此objectClass定義的';
$lang['syntax']='語法，您必須輸入新屬性';

//copy.php
$lang['copy_server_read_only']='無法在伺服器為唯讀模式時更新資料';
$lang['copy_dest_dn_blank']='目的地識別名稱不能是空白';
$lang['copy_dest_already_exists']='目的地識別名稱 (%s) 已經存在';
$lang['copy_dest_container_does_not_exist']='目的地集合 (%s) 不存在';
$lang['copy_source_dest_dn_same']='來源識別名稱與目的地識別名稱重複';
$lang['copy_copying']='複製中';
$lang['copy_recursive_copy_progress']='遞迴複製作業';
$lang['copy_building_snapshot']='對資料樹建立副本來複製';
$lang['copy_successful_like_to']='複製成功!!您要';
$lang['copy_view_new_entry']='查閱此新紀錄?';
$lang['copy_failed']='以下 DN 複製失敗：';

//edit.php
$lang['missing_template_file']='警告:找不到樣版檔案';
$lang['using_default']='使用預設值';
$lang['template']='樣版';
$lang['must_choose_template']='你必須選擇一個樣版';
$lang['invalid_template']='%s 是錯誤的樣版';
$lang['using_template']='使用樣版';
$lang['go_to_dn']='到 %s';

//copy_form.php
$lang['copyf_title_copy']='複製';
$lang['copyf_to_new_object']='成新物件';
$lang['copyf_dest_dn']='目的地識別名稱';
$lang['copyf_dest_dn_tooltip']='來源資料複製後的完整識別名稱';
$lang['copyf_dest_server']='目的伺服器';
$lang['copyf_note']='提示：只有當不同的伺服器間的schema相容才能在不同的伺服器之間進行複製';
$lang['copyf_recursive_copy']='遞迴複製所有此物件的子資料';
$lang['recursive_copy']='遞迴複製';
$lang['filter']='過濾條件';
$lang['filter_tooltip']='只有符合過濾條件的資料會被遞迴複製';

//create.php
$lang['create_required_attribute']='必要的屬性 (%s) 必須有資料';
$lang['redirecting']='正在轉換至...';
$lang['here']='這裡';
$lang['create_could_not_add']='不能新增物件到此LDAP伺服器';

//create_form.php
$lang['createf_create_object']='創造物件';
$lang['createf_choose_temp']='選擇樣版';
$lang['createf_select_temp']='請選擇一個你要建立的紀錄模板';
$lang['createf_proceed']='下一步';
$lang['rdn_field_blank']='相對識別名稱欄位不能是空白';
$lang['container_does_not_exist']='您指定的集合 (%s) 不存在，請重新指定';
$lang['no_objectclasses_selected']='您必須為此物件指定ObjectClasses';
$lang['hint_structural_oclass']='提示：您必須在伺服器上至少';

//creation_template.php
$lang['ctemplate_on_server']='選擇一個objectClass';
$lang['ctemplate_no_template']='在POST變數中並未指定模版';
$lang['ctemplate_config_handler']='您的設定指定了由某個程式來執行此模版';
$lang['ctemplate_handler_does_not_exist']='但此程式在 templates/creation 目錄中找不到';
$lang['create_step1']='步驟 1 之 2:名稱與ObjectClass(es)';
$lang['create_step2']='步驟 2 之 2:指定屬性與數值';
$lang['relative_distinguished_name']='相對識別名稱';
$lang['rdn']='相對識別名稱';
$lang['rdn_example']='(範例: cn=MyNewPerson)(範例: cn=MyNewPerson)';
$lang['container']='集合';

// search.php
$lang['you_have_not_logged_into_server']='您必須先登入伺服器才能在伺服器執行搜尋';
$lang['click_to_go_to_login_form']='點選這邊回去登入表單';
$lang['unrecognized_criteria_option']='無法辨識的基準';
$lang['if_you_want_to_add_criteria']='如果您要加入自己的基準到項目列表中，請編輯search.php';
$lang['entries_found']='找到的紀錄:';
$lang['filter_performed']='執行過濾條件';
$lang['search_duration']='phpLDAPadmin將會';
$lang['seconds']='馬上執行查詢';

// search_form_advanced.php
$lang['scope_in_which_to_search']='搜尋範圍';
$lang['scope_sub']='Sub (整個子樹)';
$lang['scope_one']='One (單一階層下)';
$lang['scope_base']='Base (只有基礎識別名稱)';
$lang['standard_ldap_search_filter']='標準的LDAP搜尋條件. 如： (&(sn=Smith)(givenname=David))';
$lang['search_filter']='過濾搜尋';
$lang['list_of_attrs_to_display_in_results']='下列為搜尋結果(以 , 區隔)';
$lang['equals']='等於';
$lang['starts with']='開始於';
$lang['contains']='內含';
$lang['ends with']='結束於';
$lang['sounds like']='喜愛的聲音';

// server_info.php
$lang['could_not_fetch_server_info']='無法從伺服器取得 LDAP 資訊';
$lang['server_info_for']='伺服器資訊:';
$lang['server_reports_following']='伺服器回報下列資訊：';
$lang['nothing_to_report']='此伺服器沒有資訊可以回報';

//update.php
$lang['update_array_malformed']='無法更新陣列，可能是phpLDAPadmin的bug，請回報此問題';
$lang['could_not_perform_ldap_modify']='無法執行ldap_modify操作';

// update_confirm.php
$lang['do_you_want_to_make_these_changes']='您確定要做這些更動?';
$lang['attribute']='屬性';
$lang['old_value']='原設定值';
$lang['new_value']='新設定植';
$lang['attr_deleted']='[屬性已刪除]';
$lang['commit']='送出';
$lang['cancel']='取消';
$lang['you_made_no_changes']='您並沒有做任何更動';
$lang['go_back']='回上頁';

// welcome.php
$lang['welcome_note']='請用左邊的選單瀏覽';
$lang['credits']='成員列表';
$lang['changelog']='更新紀錄';
$lang['donate']='捐獻';

// view_jpeg_photo.php
$lang['unsafe_file_name']='不安全的檔案名稱:';
$lang['no_such_file']='沒有以下檔案:';

//function.php
$lang['auto_update_not_setup']='您在設定中開啟 <b>%s</b>的auto_uid_numbers功能，但並未指定auto_uid_number_mechanism，請修正此問題';
$lang['uidpool_not_set']='伺服器<b>%s</b>的auto_uid_number_mechanism指定為uidpool，但並未指定audo_uid_number_uid_pool_dn，請修正此問題再繼續';
$lang['uidpool_not_exist']='您再設定檔(\"%s\")中指定的uidPool機制並不存在';
$lang['specified_uidpool']='伺服器<b>%s</b>的auto_uid_number_mechanism指定為search，但您並未指定auto_uid_number_search_base，請修正此問題再繼續';
$lang['auto_uid_invalid_credential']='無法以您的auto_uid設定登入<b>%s</b>，請檢查您的設定檔';
$lang['bad_auto_uid_search_base']='您在phpLDAPadmin設定中對伺服器%s指定了無效的auto_uid_search_base';
$lang['auto_uid_invalid_value']='您的auto_uid_number_mechanism (\"%s\") 設定是無效的，只有uidpool與search為有效設定，請修正此問題';
$lang['error_auth_type_config']='錯誤：您的設定有錯誤，變數auth_type只允許session , cookie與config，您的設定值 ';
$lang['php_install_not_supports_tls']='您安裝的 php 並沒有支援 TLS.';
$lang['could_not_start_tls']='無法啟動 TLS 請檢查您的 LDAP 伺服器設定.';
$lang['could_not_bind_anon']='伺服器不接受匿名登入';
$lang['anonymous_bind']='匿名登入';
$lang['bad_user_name_or_password']='錯誤的 使用者名稱 或 密碼.請重新輸入一次.';
$lang['redirecting_click_if_nothing_happens']='正在重新導向...如果瀏覽器沒有動作,請點選這裡.';
$lang['successfully_logged_in_to_server']='成功登入伺服器 %s';
$lang['could_not_set_cookie']='不能設定 cookie';
$lang['ldap_said']='伺服器回應: %s';
$lang['ferror_error']='錯誤';
$lang['fbrowse']='瀏覽';
$lang['delete_photo']='刪除相片';
$lang['install_not_support_blowfish']='您所安裝的 PHP 並不支援 Blowfish 加密';
$lang['install_no_mash']='您所安裝的 PHP 並沒有 mhash() 函數,無法進行 SHA 加密';
$lang['jpeg_contains_errors']='jpegPhoto 內容發生錯誤<br />';
$lang['ferror_number']='錯誤碼: %s (%s)';
$lang['ferror_discription']='說明: %s <br /><br />';
$lang['ferror_number_short']='錯誤編號: %s';
$lang['ferror_discription_short']='說明: (無任何說明內容)<br />';
$lang['ferror_submit_bug']='這是 phpLDAPadmin 的 bug? 如果是,請<a href=\"%s\">回報這個Bug</a>.';
$lang['ferror_unrecognized_num']='無法辨識的錯誤代碼：';
$lang['ferror_nonfatil_bug']='<center><table class=\"notice\"><tr><td colspan=\"2\"><center><img src=\"images/warning.png\" height=\"12\" width=\"13\" />
             <b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td>
             <td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\"2\"><center><a target=\"new\" href=\"%s\">
             Please report this bug by clicking here</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug']='恭喜! 您發現了phpLDAPadmin的bug.<br /><br />  <table class=\"bug\">
	     <tr><td>Error:</td><td><b>%s</b></td></tr>
	     <tr><td>Level:</td><td><b>%s</b></td></tr>
	     <tr><td>File:</td><td><b>%s</b></td></tr>
	     <tr><td>Line:</td><td><b>%s</b></td></tr>
		 <tr><td>Caller:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Web server:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Please report this bug by clicking below!';

//ldif_import_form
$lang['import_ldif_file_title']='匯入 LDIF 檔案';
$lang['select_ldif_file']='選擇一個 LDIF 檔案:';
$lang['select_ldif_file_proceed']='下一步';

//ldif_import
$lang['add_action']='增加中...';
$lang['delete_action']='刪除中...';
$lang['rename_action']='更名中...';
$lang['modify_action']='改變中...';
$lang['warning_no_ldif_version_found']='找不到版本資訊，預設使用版本1';
$lang['valid_dn_line_required']='需要有效的識別名稱行';
$lang['missing_uploaded_file']='找不到指定的上傳檔案';
$lang['no_ldif_file_specified.']='沒有指定LDIF檔案請重試';
$lang['ldif_file_empty']='上傳的 LDIF 檔案是空白的';
$lang['file']='檔案';
$lang['number_bytes']='%s bytes';
$lang['failed']='失敗';
$lang['ldif_parse_error']='LDIF 解析錯誤';
$lang['ldif_could_not_add_object']='無法新增 object:';
$lang['ldif_could_not_rename_object']='無法重新命名 object:';
$lang['ldif_could_not_delete_object']='無法刪除 object:';
$lang['ldif_could_not_modify_object']='無法修改 object:';
$lang['ldif_line_number']='行數：';
$lang['ldif_line']='行：';

// Exports
$lang['export_format']='匯出格式';
$lang['line_ends']='斷行';
$lang['must_choose_export_format']='你必須選擇一種匯出格式';
$lang['invalid_export_format']='無效的匯出格式';
$lang['no_exporter_found']='沒有可用的匯出程式';
$lang['error_performing_search']='在執行搜尋時發生錯誤';
$lang['showing_results_x_through_y']='透過 %s 顯示結果 %s';
$lang['searching']='搜索中...';
$lang['size_limit_exceeded']='注意：到達搜尋大小上限';
$lang['entry']='紀錄';
$lang['ldif_export_for_dn']='匯出 LDIF :';
$lang['generated_on_date']='由';
$lang['total_entries']='全部的紀錄';
$lang['dsml_export_for_dn']='匯出 DSLM :';

// logins
$lang['could_not_find_user']='找不到使用者 \"%s\"';
$lang['password_blank']='您沒有輸入密碼欄位.';
$lang['login_cancelled']='已經取消登入.';
$lang['no_one_logged_in']='在此伺服器尚無人登入.';
$lang['could_not_logout']='無法登出.';
$lang['unknown_auth_type']='未知的認證模式: %s';
$lang['logged_out_successfully']='成功的從 %s 伺服器登出';
$lang['authenticate_to_server']='登入伺服器 %s';
$lang['warning_this_web_connection_is_unencrypted']='警告: 這個網頁連線是沒有加密的.';
$lang['not_using_https']='您並未使用https加密連線，您的瀏覽器將直接以明碼傳送您的帳號與密碼';
$lang['login_dn']='登入 DN';
$lang['user_name']='使用者名稱';
$lang['password']='密碼';
$lang['authenticate']='驗證';

// Entry browser
$lang['entry_chooser_title']='紀錄選擇器';

// Index page
$lang['need_to_configure']='您必須先設定phpLDAPadmin，請依照config.php.example編輯config.php';

// Mass deletes
$lang['no_deletes_in_read_only']='在唯讀模式時不允許刪除資料';
$lang['error_calling_mass_delete']='呼叫mass_delete.php時發生錯誤，mass_delete不在POST變數中';
$lang['mass_delete_not_array']='mass_delete POST變數不是陣列';
$lang['mass_delete_not_enabled']='大量刪除功能並未開啟，請在config.php中開啟此功能';
$lang['mass_deleting']='大量刪除';
$lang['mass_delete_progress']='正在 \"%s\" 伺服器上進行刪除程序';
$lang['malformed_mass_delete_array']='錯誤的大量刪除陣列';
$lang['no_entries_to_delete']='您沒有選擇任何要刪除的紀錄';
$lang['deleting_dn']='刪除 %s';
$lang['total_entries_failed']='無法刪除紀錄 %s %s';
$lang['all_entries_successful']='全部刪除完成';
$lang['confirm_mass_delete']='確認要刪除 %s 登入在伺服器 %s上';
$lang['yes_delete']='沒錯,刪除吧!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed']='當此記錄有子紀錄時無法重新命名';
$lang['no_rdn_change']='您並沒有改變相對識別名稱';
$lang['invalid_rdn']='無效的相對識別名稱';
$lang['could_not_rename']='無法重新命名';

?>