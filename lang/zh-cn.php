<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/zh-cn.php,v 1.2 2005/03/25 01:21:30 wurley Exp $
// Translate to Simplified Chinese, by 张雪平(heromyth), from zxpmyth@yahoo.com.cn
// Based on en.php,v 1.119 2005/03/16 20:57:45

/*
 使用说明：将其转换不utf-8字符集，再放到lang/recoded目录下，接着修改文件lang/auto.php，执行如下步骤：

 1、修改第7行为：
	$useLang="zh-cn";

 2、在31行后面加下下面一行：
 		   ," zh-cn"=>"zh-cn" //Simplified Chinese

*/

/*        ---   翻译指导   ---
 *
 * 如果你想建立一个新的语言文件，
 * 请把它提交到SourceForge：
 *
 *   https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498548
 *
 * 请使用底部的选项"Check to Upload and Attach a File（上传附件）" 
 *
 * 阅读doc/README-translation.txt 可以得到翻译指导。
 *
 * 谢谢！
 *
 */

/*
 * 数组$lang 包含了所有phpLDAPadmin 所使用的字体串。
 * 每一个语言文件都只需要在这个数组中定义一个该语言的所对应的
 * 字符串。
 */

// Search form
$lang['simple_search_form_str'] = '简单搜索表格';
$lang['advanced_search_form_str '] = '高级搜索表格';
$lang['server'] = '服务器';
$lang['search_for_entries_whose'] = '搜索条目的所属';
$lang['base_dn'] = '基本DN';
$lang['search_scope'] = '搜索范围';
$lang['show_attributes'] = '显示属性';
//$lang['attributes'] = '属性';
$lang['Search'] = '搜索';
$lang['predefined_search_str'] = '选择一个预定义的搜索';
$lang['predefined_searches'] = '预定义的搜索';
$lang['no_predefined_queries'] = '在config.php里没有条目定义。';
$lang['export_results'] = '导出结果';
$lang['unrecoginzed_search_result_format'] = '不能识别的搜索结果格式： %s';
$lang['format'] = '格式';
$lang['list'] = '列表';
$lang['table'] = '表格';
$lang['bad_search_display'] = '你的config.php 给$default_search_display指定了一个非法的值： %s，请更正';
$lang['page_n'] = '第%d页';
$lang['no_results'] = '这次搜索没找到结果。';

// Tree browser
$lang['request_new_feature'] = '功能需求';
$lang['report_bug'] = '报告错漏';
$lang['schema'] = '格式';
$lang['search'] = '搜索';
$lang['create'] = '创建';
$lang['info'] = '信息';
$lang['import'] = '导入';
$lang['refresh'] = '刷新';
$lang['logout'] = '退出';
$lang['create_new'] = '创建新条目';
$lang['view_schema_for'] = '查看格式';
$lang['refresh_expanded_containers'] = '刷新所有展开的容器，来自';
$lang['create_new_entry_on'] = '创建新条目到';
$lang['new'] = '新建';
$lang['view_server_info'] = '查看附加的服务器信息';
$lang['import_from_ldif'] = '从LDIF文件导入条目';
$lang['logout_of_this_server'] = '退出这个服务器';
$lang['logged_in_as'] = '登录为： ';
$lang['this_base_dn_is_not_valid'] = '该DN是无效。';
$lang['this_base_dn_does_not_exist'] = '该条目不存在。';
$lang['read_only'] = '只读';
$lang['read_only_tooltip'] = '该属性已经被phpLDAPadmin 管理员标识为只读';
$lang['could_not_determine_root'] = '检测不到你的LDAP树的根。';
$lang['ldap_refuses_to_give_root'] = '好像你的LDAP服务器配置来限制了不要显露它的根。';
$lang['please_specify_in_config'] = '请在config.php里指定它';
$lang['create_new_entry_in'] = '创建新条目于';
$lang['login_link'] = '登录…';
$lang['login'] = '登录';
$lang['base_entry_does_not_exist'] = '该基准条目不存在。';
$lang['create_it'] = '要创建它吗？';

// Entry display
$lang['delete_this_entry'] = '删除该条目';
$lang['delete_this_entry_tooltip'] = '你会得到提示要求确认该选择';
$lang['copy_this_entry'] = '复制和移动该条目';
$lang['copy_this_entry_tooltip'] = '把这个对象复制到另一个位置，一个新DN或另一个服务器。';
$lang['export'] = '导出';
$lang['export_lcase'] = '导出';
$lang['export_tooltip'] = '保存一个该对象的导出';
$lang['export_subtree_tooltip'] = '保存一个该对象及其所有孩子的导出';
$lang['export_subtree'] = '导出子树';
$lang['create_a_child_entry'] = '创建一个子条目';
$lang['rename_entry'] = '条目更名';
$lang['rename'] = '更名';
$lang['add'] = '增加';
$lang['view'] = '查看';
$lang['view_one_child'] = '查看1个子条目';
$lang['view_children'] = '查看%s个子条目';
$lang['add_new_attribute'] = '增加新的属性';
$lang['add_new_objectclass'] = '增加新的ObjectClass';
$lang['hide_internal_attrs'] = '隐藏内部属性';
$lang['show_internal_attrs'] = '显示内部属性';
$lang['attr_name_tooltip'] = '点击查看属性类型\'%s\'的格式定义';
$lang['none'] = '什么都没有';
$lang['no_internal_attributes'] = '没有内部属性';
$lang['no_attributes'] = '这个条目没有属性';
$lang['save_changes'] = '保存更改';
$lang['add_value'] = '赋值';
$lang['add_value_tooltip'] = '给属性\'%s\'赋一个附加的值';
$lang['refresh_entry'] = '刷新';
$lang['refresh_this_entry'] = '刷新这个条目';
$lang['delete_hint'] = '提示： 想要删除一个属性，请将文本字段清空，然后点击保存。';
$lang['attr_schema_hint'] = '提示： 要查看一个属性的格式，请点击属性的名称。';
$lang['attrs_modified'] = '下面有一些属性(%s) 被修改了，而且被标识为高亮。';
$lang['attr_modified'] = '下面一个属性(%s) 被修改了，而且被标识为高亮';
$lang['viewing_read_only'] = '以只读模式查看条目。';
$lang['no_new_attrs_available'] = '这个条目没有提供新的属性';
$lang['no_new_binary_attrs_available'] = '这个条目没有提供新的二进制属性';
$lang['binary_value'] = '二进制值';
$lang['add_new_binary_attr'] = '增加新的二进制值';
$lang['alias_for'] = '注意： \'%s\'是\'%s\'的一个别名';
$lang['required_for'] = 'objectClass(es) %s所必需的属性';
$lang['download_value'] = '下载值';
$lang['delete_attribute'] = '删除属性';
$lang['true'] = '真的';
$lang['false'] = '假的';
$lang['none_remove_value'] = '什么都没有，将值移除';
$lang['really_delete_attribute'] = '真的删除属性';
$lang['add_new_value'] = '增加新的值';

// Schema browser
//$lang['the_following_objectclasses'] = '这个LDAP服务器支持下列objectClasses。';
//$lang['the_following_attributes'] = '这个LDAP服务器支持下列属性类型。';
//$lang['the_following_matching'] = '这个LDAP服务器支持下列匹配规则。';
//$lang['the_following_syntaxes'] = '这个LDAP服务器支持下列语法规则。';
$lang['schema_retrieve_error_1']='这个服务器并不完全支持LDAP 协议。';
$lang['schema_retrieve_error_2']='你的PHP版本不能正确地完成查询。';
$lang['schema_retrieve_error_3']='phpLDAPadmin不知道如果获取你的服务器的格式。';
$lang['schema_retrieve_error_4']='或者最后，你的LDAP服务器没有提供该信息。';
$lang['jump_to_objectclass'] = '跳转到一个objectClass';
$lang['view_schema_for_oclass'] = '查看该objectClass的格式描述';
$lang['jump_to_attr'] = '跳转到一个属性类型';
$lang['jump_to_matching_rule'] = '跳转到一个匹配的规则';
$lang['schema_for_server'] = '服务器的格式';
$lang['required_attrs'] = '必需的属性';
$lang['required'] = '必需的';
$lang['optional_attrs'] = '可选的属性';
$lang['optional_binary_attrs'] = '可选的二进制属性';
$lang['OID'] = 'OID';
$lang['aliases']='别名';
$lang['desc'] = '描述';
$lang['no_description']='没有描述';
$lang['name'] = '名称';
$lang['equality']='相等';
$lang['is_obsolete'] = '该objectClass过久了。';
$lang['inherits'] = '继承于';
$lang['inherited_from'] = '被继承于';
$lang['parent_to'] = '双亲指向';
$lang['jump_to_this_oclass'] = '跳转到该objectClass的定义处';
$lang['matching_rule_oid'] = '匹配规则 OID';
$lang['syntax_oid'] = '语法规则OID';
$lang['not_applicable'] = '不可应用';
$lang['not_specified'] = '未指定的';
$lang['character']='单个字符'; 
$lang['characters']='多个字符';
$lang['used_by_objectclasses']='被objectClasses所使用的';
$lang['used_by_attributes']='被属性所使用的';
$lang['maximum_length']='最大长度';
$lang['attribute_types']='属性类型';
$lang['syntaxes']='语法规则';
$lang['matchingrules']='匹配规则';
$lang['oid']='OID';
$lang['obsolete']='作废的';
$lang['ordering']='排序';
$lang['substring_rule']='子串规则';
$lang['single_valued']='单个赋值的';
$lang['collective']='集体的';
$lang['user_modification']='用户修改';
$lang['usage']='使用格式';
$lang['could_not_retrieve_schema_from']='不能找回格式的地方';
$lang['type']='类型';
$lang['no_such_schema_item'] = '没有这样的格式项目： "%s"';

// Deleting entries
$lang['entry_deleted_successfully'] = '成功删除条目：%s。';
$lang['you_must_specify_a_dn'] = '你必须指定一个DN';
$lang['could_not_delete_entry'] = '不能删除该条目： %s';
$lang['no_such_entry'] = '没有这样的条目： %s';
$lang['delete_dn'] = '删除：%s';
$lang['permanently_delete_children'] = '也要永久删除所的子条目吗？';
$lang['entry_is_root_sub_tree'] = '该条目是根，其子树中包含有条目：%s。';
$lang['view_entries'] = '查看条目';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin可以递归地删除该条目及其所有子条目中的：%s。 关于这个动作可能会删除的所有条目列表，请查看下面。 你真的想这样做吗？';
$lang['confirm_recursive_delete_note'] = '注释： 这个可能潜在危险，后果自负。 这个操作不可恢复。 一定要考虑到aliases（别名）、 referrals（提名）以及其它可能引起问题的事情。';
$lang['delete_all_x_objects'] = '删除所有的对象：%s ';
$lang['recursive_delete_progress'] = '递归式删除过程';
$lang['entry_and_sub_tree_deleted_successfully'] = '成功删除条目%s 和子树。';
$lang['failed_to_delete_entry'] = '删除条目：%s 失败';
$lang['list_of_entries_to_be_deleted'] = '将被删除的条目列表：';
$lang['sure_permanent_delete_object']='确认你想永久删除这个对象吗？';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = ' 在phpLDAPadmin的配置中，属性"%s"被标识为只读。';
$lang['no_attr_specified'] = '没有指定属性名称。';
$lang['no_dn_specified'] = '没有指定DN ';

// Adding attributes
$lang['left_attr_blank'] = '属性值为空白。 请返回再试。';
$lang['failed_to_add_attr'] = '增加属性失败。';
$lang['file_empty'] = '你选择的文件为空或不存在。 请返回再试。';
$lang['invalid_file'] = '安全错误： 上传的这个文件可能存在安全危险。';
$lang['warning_file_uploads_disabled'] = '你的PHP配置不允许上传文件。 请在进行下步之前检查一下php.ini。';
$lang['uploaded_file_too_big'] = '你上传的文件太大。 请检查php.ini中的upload_max_size 设置';
$lang['uploaded_file_partial'] = '你选择的文件上传不完整，可能是因为网络的缘故。';
$lang['max_file_size'] = '最大文件尺寸： %s';

// Updating values
$lang['modification_successful'] = '成功修改！';
$lang['change_password_new_login'] = '因为你更改了密码，你现在必须使用新的密码重新登录。';

// Adding objectClass form
$lang['new_required_attrs'] = '新增必需的属性';
$lang['requires_to_add'] = '这个动作要求你增加';
$lang['new_attributes'] = '新增属性';
$lang['new_required_attrs_instructions'] = '指导： 为了增加这个objectClass 到该条目，你必须指定';
$lang['that_this_oclass_requires'] = '这该objectClass所必需的。 你可以在这个表里完成。';
$lang['add_oclass_and_attrs'] = '增加ObjectClass 和属性';
$lang['objectclasses'] = 'ObjectClasses';

// General
$lang['chooser_link_tooltip'] = '点击弹出一个对话框来以图形方式选择一个条目(DN) ';
$lang['no_updates_in_read_only_mode'] = '服务器是以只读方式运行，你不能完成更新';
$lang['bad_server_id'] = '错误的服务器id';
$lang['not_enough_login_info'] = '没有足够的信息来登录服务器。 请检查你的配置。';
$lang['could_not_connect'] = '不能连接到LDAP服务器。';
$lang['could_not_connect_to_host_on_port'] = '不能连接到"%s" 的"%s"端口';
$lang['could_not_perform_ldap_mod_add'] = '不能完成ldap_mod_add 操作。';
//$lang['bad_server_id_underline'] = '错误的server_id：';
$lang['home'] = '主页';
$lang['help'] = '帮助';
$lang['success'] = '成功';
$lang['server_colon_pare'] = '服务器：';
$lang['look_in'] = '正在查看：';
//$lang['missing_server_id_in_query_string'] = '在查询串中没有指定服务器ID！';
$lang['missing_dn_in_query_string'] = '在查询串中没有指定DN！';
$lang['back_up_p'] = '后退...';
$lang['no_entries'] = '没有条目';
//$lang['not_logged_in'] = '没有登录';
$lang['could_not_det_base_dn'] = '不能确定base DN（基DN）';
//$lang['please_report_this_as_a_bug']='请报告这个错漏。';
$lang['reasons_for_error']='出现这种情况可能有几个原因，其中最有可能的是：';
$lang['yes']='是';
$lang['no']='不';
$lang['go']='开始';
$lang['delete']='删除';
$lang['back']='后退';
$lang['object']='对象';
$lang['delete_all']='删除所有的';
//$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = '提示';
$lang['bug'] = '错漏';
$lang['warning'] = '警告';
$lang['light'] = 'light'; // 单词'light' 来自 'light bulb（电灯泡）'
$lang['proceed_gt'] = '继续 &gt;&gt;';
$lang['no_blowfish_secret'] = 'phpLDAPadmin不能安全地加密和解密你的敏感信息，因为在config.php是没有设置$blowfish_secret。现在，你需要编辑config.php，并设置$blowfish_secret为某种隐秘的字符串。';
$lang['jpeg_dir_not_writable'] = '请在phpLDAPadmin的config.php里，将$jpeg_temp_dir设置到一个可写的目录';
$lang['jpeg_dir_not_writable_error'] = '不能将%s写入$jpeg_temp_dir目录。请确定你的web服务器能够在那里写文件。';
$lang['jpeg_unable_toget'] = '不能从LDAP服务器为属性%s获得jpeg数据。';
$lang['jpeg_delete'] = '删除图片';



// Add value form
$lang['add_new'] = '新增';
$lang['value_to'] = '赋值给';
$lang['distinguished_name'] = '识别名（DN）';
$lang['current_list_of'] = '当前列表';
$lang['values_for_attribute'] = '属性值';
$lang['inappropriate_matching_note'] = '注释： 如果在你的LDAP服务器上没有为这个属性设置EQUALITY 规则的话，你可能会碰到"inappropriate matching（不正确的匹配）"这样的错误。';
$lang['enter_value_to_add'] = '输入你想增加的值：';
$lang['new_required_attrs_note'] = '注释： 可能会要求你输入该objectClass所必需的新属性。';
$lang['syntax'] = '规则';

//copy.php
$lang['copy_server_read_only'] = '服务器处于只读模式，你不能完成更新';
$lang['copy_dest_dn_blank'] = ' 目标DN项为空。';
$lang['copy_dest_already_exists'] = '目标条目 (%s) 已经存在。';
$lang['copy_dest_container_does_not_exist'] = '目标容器 (%s) 不存在。';
$lang['copy_source_dest_dn_same'] = '源和目标DN 相同。';
$lang['copy_copying'] = '正在复制 ';
$lang['copy_recursive_copy_progress'] = '递归手复制过程';
$lang['copy_building_snapshot'] = '正在建立用于复制的树的快照… ';
$lang['copy_successful_like_to'] = '复制成功！ 你还想要 ';
$lang['copy_view_new_entry'] = '查看新条目';
$lang['copy_failed'] = '复制DN不成功： ';

//edit.php
$lang['missing_template_file'] = '警告： 样板文件不存在，';
$lang['using_default'] = '使用默认的。';
$lang['template'] = '样板';
$lang['must_choose_template'] = '你必须选择一个样板';
$lang['invalid_template'] = '%s 是一个非法的样板';
$lang['using_template'] = '使用样板';
$lang['go_to_dn'] = '转到 %s';
$lang['structural_object_class_cannot_remove'] = '这是个结构化的ObjectClass，因此不能移除。';
$lang['structural'] = '结构化';

//copy_form.php
$lang['copyf_title_copy'] = '复制';
$lang['copyf_to_new_object'] = '成为一个新的对象';
$lang['copyf_dest_dn'] = '目标DN';
$lang['copyf_dest_dn_tooltip'] = '在复制该源条目时，将被建立的新条目的完整DN ';
$lang['copyf_dest_server'] = '目标服务器';
$lang['copyf_note'] = '提示： 在两个不同的服务器之间复制时，要求它们没有"schema（格式）冲突"';
$lang['copyf_recursive_copy'] = '另外还要递归复制此对象的所有子内容。';
$lang['recursive_copy'] = '递归复制';
$lang['filter'] = '过滤器';
//$lang['search_filter'] = '搜索过滤器';
$lang['filter_tooltip'] = '在完成一个递归复制时，只会复制哪些匹配这个过滤器的条目。';
$lang['delete_after_copy'] = '复制后删除（即移动）：';
$lang['delete_after_copy_warn'] = '确认你的过滤器（见上面）会选择所有的子记录。';

//create.php
$lang['create_required_attribute'] = '必需属性(%s)的值为空白。';
$lang['redirecting'] = '重定向...';
$lang['here'] = '这里';
$lang['create_could_not_add'] = '不能增加该对象到LDAP服务器。';

//create_form.php
$lang['createf_create_object'] = '创建对象';
$lang['createf_choose_temp'] = '选择样板';
$lang['createf_select_temp'] = '选择用于创建过程的样板';
$lang['save_as_file'] = '另存为文件';
$lang['rdn_field_blank'] = 'RDN段为空白。';
$lang['container_does_not_exist'] = '你指定的(%s)容器不存在。 请再试试。';
$lang['no_objectclasses_selected'] = '你没有为该对象选择任何ObjectClasses。 请返回照做。';
$lang['hint_structural_oclass'] = '提示： 你必有选择一个结构化的objectClass (如上面粗体所显示的)';

//creation_template.php
$lang['ctemplate_on_server'] = '在服务器上';
$lang['ctemplate_no_template'] = '在POST变量中没有指定样板。';
$lang['template_not_readable'] = '你在配置中指定了用于该样板的"%s"的手柄，但是这个文件因为禁止权限太严格而不可读。';
$lang['template_does_not_exist'] = '你在配置文件中指定了用于该 样板的"%s"的手柄，但是该手柄在templates/creation 目录中不存在。';
$lang['create_step1'] = '第一步： Name 和 ObjectClass(es)';
$lang['create_step2'] = '第二步： 指定属性和值';
$lang['relative_distinguished_name'] = 'Relative Distinguished Name（相对标识名）';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(例如： cn=MyNewPerson)';
$lang['container'] = 'Container（容器）';

// search.php
$lang['you_have_not_logged_into_server'] = '你还没有登入所选择的服务器，因此你不能在它上面完成搜索。';
$lang['click_to_go_to_login_form'] = '点击这里转到登录表格';
$lang['unrecognized_criteria_option'] = '不认识的criteria（标准）选项： ';
$lang['if_you_want_to_add_criteria'] = '如果你想增加自己的criteria 到列表里。 记得编辑search.php 来处理它们。 退出。';
$lang['entries_found'] = '找到的条目： ';
$lang['filter_performed'] = '应用了的过滤器： ';
$lang['search_duration'] = 'phpLDAPadmin完成搜索，用时';
$lang['seconds'] = '秒';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = '搜索的范围';
$lang['scope_sub'] = 'Sub (整个子树)';
$lang['scope_one'] = 'One (base之下的一级)';
$lang['scope_base'] = 'Base (仅限于base dn)';
$lang['standard_ldap_search_filter'] = '标准的LDAP搜索过滤器。 例如： (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = '搜索过滤器';
$lang['list_of_attrs_to_display_in_results'] = '用于显示在结果中的属性列表(以逗号隔开)';
//$lang['show_attributes'] = '显示属性';

// search_form_simple.php
//$lang['search_for_entries_whose'] = '查找是谁的条目：';
$lang['equals'] = '等于';
$lang['starts with'] = '开始于';
$lang['contains'] = '包含';
$lang['ends with'] = '结束于';
$lang['sounds like'] = '看起来象';

// server_info.php
$lang['could_not_fetch_server_info'] = '不能从服务器上取得LDAP信息。 可能是因为你的PHP存在这个<a href="http://bugs.php.net/bug.php?id=29587">错漏</a>，或者是你的LDAP服务器里指定的"访问控制"禁止LDAP客户端访问RootDSE。';
$lang['server_info_for'] = '服务器信息： ';
$lang['server_reports_following'] = '下列信息是服务器报告的关于它自己的信息';
$lang['nothing_to_report'] = '该服务器没有报告任何信息。';

//update.php
$lang['update_array_malformed'] = '看不懂update_array。 这可能是phpLDAPadmin的错漏。 请报告。';
$lang['could_not_perform_ldap_modify'] = '不能完成ldap_modify 操作。';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = '你想应用这些变化吗？';
$lang['attribute'] = '属性';
$lang['old_value'] = '旧值';
$lang['new_value'] = '新值';
$lang['attr_deleted'] = '[删除的属性]';
$lang['commit'] = '提交';
$lang['cancel'] = '取消';
$lang['you_made_no_changes'] = '你没有进行更改';
$lang['go_back'] = '返回';
$lang['unable_create_samba_pass'] = '不能建立samba密码。请在template_config.php里检查你的配置';

// welcome.php
$lang['welcome_note'] = '使用左边菜单来导航';
$lang['credits'] = '荣誉';
$lang['changelog'] = '变更记录';
$lang['donate'] = '捐赠';
$lang['pla_logo'] = 'phpLDAPadmin 标识';

// Donate.php
$lang['donation_instructions'] = '想捐款给phpLDAPadmin项目，请点击下面PayPal按钮中的一个。';
$lang['donate_amount'] = '捐赠 %s';
//$lang['wish_list_option'] = '或许你可以买份礼物给phpLDAPadmin开发人员。';
//$lang['wish_list'] = '查看Dave的phpLDAPadmin 愿望列表';

$lang['purge_cache'] = '清空缓存';
$lang['no_cache_to_purge'] = '没有缓存可清空。';
$lang['done_purging_caches'] = '清空缓存%s 个字节。';
$lang['purge_cache_tooltip'] = '清空所有在phpLDAPadmin中缓存的数据，包括服务器schema（格式）。';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = '不安全的文件名： ';
$lang['no_such_file'] = '没有这样的文件： ';

//function.php
$lang['auto_update_not_setup'] = '在你的配置中为 <b>%s</b> 启用了auto_uid_numbers，
                                  但是你没有指定auto_uid_number_mechanism。 请更正
                                  这个问题';
$lang['uidpool_not_set'] = '你在服务<b>%s</b>的配置中指定"auto_uid_number_mechanism" 为 "uidpool"，
                            但是你没有指定
                            audo_uid_number_uid_pool_dn。 请在进行下步前指定它。';
$lang['uidpool_not_exist'] = '好像你在配置("%s")中指定的uidPool
                              不存在。';
$lang['specified_uidpool'] = '你在服务器<b>%s<b>的配置文件中指定了"auto_uid_number_mechanism" 为 "search"，
                              但是你没有指定
                              "auto_uid_number_search_base"。 请在进行下步前指定它。';
$lang['auto_uid_invalid_credential'] = '使用auto_uid credentials不能绑定到<b>%s</b>。 请检查你的配置文件。'; 
$lang['bad_auto_uid_search_base'] = '你的phpLDAPadmin配置为服务器%s指定了一个非法的auto_uid_search_base ';
$lang['auto_uid_invalid_value'] = '你在配置文件中为auto_uid_number_mechanism ("%s")指定了一个非法的值
                                   。 只有"uidpool" 和 "search" 是合法的。
                                   请更正这个问题。';
$lang['error_auth_type_config'] = '错误： 在你的配置文件中有个错误。 仅允许用于
                                    $servers部分的auth_type的值为 \'session\', \'cookie\', and \'config\'。 输入\'%s\'，
                                    这是不允许的。 ';
$lang['unique_attrs_invalid_credential'] = '不能使用unique_attrs 绑定到<b>%s</b>。 请检查你的配置文件。'; 
$lang['unique_attr_failed'] = '你企图增加<b>%s</b> (<i>%s</i>) 到 <br><b>%s</b><br>，这是不允许的。 该属性/值属于任何条目。<p>你可能想<a href=\'%s\'>搜索</a> 该条条目。';
$lang['php_install_not_supports_tls'] = '你安装的PHP 不支持TLS。';
$lang['could_not_start_tls'] = '不能启用TLS。 请检查你的LDAP 服务器配置。';
$lang['could_not_bind_anon'] = '不能匿名绑定到服务器。';
$lang['could_not_bind'] = '不能绑定到该LDAP服务器。';
$lang['anonymous_bind'] = '匿名绑定';
$lang['bad_user_name_or_password'] = '错误的用户名或密码。 请再试试。';
//$lang['redirecting_click_if_nothing_happens'] = '重定向... 如果没事发生请点击这里。';
$lang['successfully_logged_in_to_server'] = '成功登录到服务器<b>%s</b>';
$lang['could_not_set_cookie'] = '不能设置cookie。';
$lang['ldap_said'] = 'LDAP说： %s';
$lang['ferror_error'] = '出错';
$lang['fbrowse'] = '浏览';
$lang['delete_photo'] = '删除图片';
//$lang['install_not_support_blowfish'] = '你安装的PHP不支持blowfish 加密。';
//$lang['install_not_support_md5crypt'] = '你安装的PHP不支持 md5crypt 加密。';
//$lang['install_no_mash'] = '你安装的PHP没有mhash() 函数。 不能进行SHA哈希。';
$lang['install_not_support_ext_des'] = '你的系统加密库不支持扩展的DES加密。';
$lang['install_not_support_blowfish'] = '你的系统加密库不支持blowfish加密。';
$lang['install_not_support_md5crypt'] = '你的系统加密库不支持md5crypt加密。';
$lang['jpeg_contains_errors'] = 'jpegPhoto 包含有错<br />';
$lang['ferror_number'] = '错误代号 %s';
$lang['ferror_discription'] = '描述： %s <br /><br />';
$lang['ferror_number_short'] = '错误代号： %s <br /><br />';
$lang['ferror_discription_short'] = '描述： (没有描述可提供)<br />';
$lang['ferror_submit_bug'] = '这是个phpLDAPadmin 错漏吗？ 如果是，就请<a href=\'%s\'>报告一个</a>。';
$lang['ferror_unrecognized_num'] = '不认识的错误代号： ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>你发现了一个非致使的phpLDAPadmin 错漏！</b></td></tr><tr><td>错误：</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>文件：</td>
             <td><b>%s</b> 行 <b>%s</b>，调用函数 <b>%s</b></td></tr><tr><td>版本：</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Web 服务器：</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             请点击这里报告该错漏</a>。</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = '恭喜你！ 你发现了phpLDAPadmin的一个错漏。<br /><br />
	     <table class=\'bug\'>
	     <tr><td>错误：</td><td><b>%s</b></td></tr>
	     <tr><td>级别:</td><td><b>%s</b></td></tr>
	     <tr><td>文件：</td><td><b>%s</b></td></tr>
	     <tr><td>行：</td><td><b>%s</b></td></tr>
		 <tr><td>调用者：</td><td><b>%s</b></td></tr>
	     <tr><td>PLA 版本：</td><td><b>%s</b></td></tr>
	     <tr><td>PHP 版本：</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP 服务器：</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     请通过点击下面来报告这个错漏！';

//ldif_import_form
$lang['import_ldif_file_title'] = '导入LDIF文件';
$lang['select_ldif_file'] = '选择一个LDIF文件：';
$lang['dont_stop_on_errors'] = '发生错误时不停止';

//ldif_import
$lang['add_action'] = '增加...';
$lang['delete_action'] = '删除...';
$lang['rename_action'] = '更名...';
$lang['modify_action'] = '修改...';
$lang['warning_no_ldif_version_found'] = '没有找到版本号。 假设 1。';
$lang['valid_dn_line_required'] = '要求一个合法的dn行。';
$lang['missing_uploaded_file'] = '丢失上传的文件。';
$lang['no_ldif_file_specified'] = '没有指定LDIF文件。 请再试试。';
$lang['ldif_file_empty'] = '上传的LDIF 文件为空。';
$lang['empty'] = '空的';
$lang['file'] = '文件';
$lang['number_bytes'] = '%s 字节';

$lang['failed'] = '失败';
$lang['ldif_parse_error'] = 'LDIF解析错误';
$lang['ldif_could_not_add_object'] = '不能增加对象：';
$lang['ldif_could_not_rename_object'] = '不能更名对象：';
$lang['ldif_could_not_delete_object'] = '不能删除对象：';
$lang['ldif_could_not_modify_object'] = '不能修改对象：';
$lang['ldif_line_number'] = '行号：';
$lang['ldif_line'] = '行数:';

// Exports
$lang['export_format'] = '导出格式';
$lang['line_ends'] = '行结束';
$lang['must_choose_export_format'] = '不必须选择一个导出格式。';
$lang['invalid_export_format'] = '非法的导出格式';
$lang['no_exporter_found'] = '没有找到可用导出器。';
$lang['error_performing_search'] = '在执行搜索时碰到一个错误。';
$lang['showing_results_x_through_y'] = '显示结果%s，它是通过%s来完成的。';
$lang['searching'] = '搜索...';
$lang['size_limit_exceeded'] = '注意，超出搜索大小限制。';
$lang['entry'] = '条目';
$lang['ldif_export_for_dn'] = '为： %s，导出LDIF';
$lang['generated_on_date'] = '由phpLDAPadmin ( http://phpldapadmin.sourceforge.net/ ) 在 %s上生成的';
$lang['total_entries'] = '条目总数';
$lang['dsml_export_for_dn'] = '为： %s，导出DSLM';
$lang['include_system_attrs'] = '包含系统属性';
$lang['csv_spreadsheet'] = 'CSV (Spreadsheet)';

// logins
//$lang['could_not_find_user'] = '不能找到用户"%s"';
$lang['password_blank'] = '你的密码为空。';
//$lang['login_cancelled'] = '登录取消了。';
$lang['no_one_logged_in'] = '没人登录到该服务器。';
$lang['could_not_logout'] = '不能退出。';
$lang['unknown_auth_type'] = '不能识别的auth_type： %s';
$lang['logged_out_successfully'] = '成功从<b>%s</b>服务器退出';
$lang['authenticate_to_server'] = '认证到服务器%s';
$lang['warning_this_web_connection_is_unencrypted'] = '警告： 此web连接没有加密。';
$lang['not_using_https'] = '你没有使用\'https\'。 Web浏览器将会以明文传输登录信息。';
$lang['login_dn'] = '登录DN';
$lang['user_name'] = '用户名';
$lang['password'] = '密码';
$lang['authenticate'] = '认证';
$lang['login_not_allowed'] = '对不起，这台LDAP服务器不允许你使用phpLDAPadmin。';

// Entry browser
$lang['entry_chooser_title'] = '条目选择器';

// Index page
$lang['need_to_configure'] = '你需要配置phpLDAPadmin。 编辑文件\'config.php\'就可以。 有个样例配置文件提供在 \'config.php.example\'里';

// Mass deletes
$lang['no_deletes_in_read_only'] = '在只读模式下不允许删除。';
$lang['error_calling_mass_delete'] = '错误调用mass_delete.php。在POST变量中丢失 mass_delete。';
$lang['mass_delete_not_array'] = 'mass_delete POST 变量不是个数组。';
$lang['mass_delete_not_enabled'] = 'Mass deletion(大量删除)没有启用。 请在进行下一步前在config.php中启用它。';
$lang['search_attrs_wrong_count'] = '你的config.php有个错误。 在$search_attributes 和 $search_attributes_display里的属性编号不一样';
$lang['mass_deleting'] = '正在大量删除';
$lang['mass_delete_progress'] = '服务器"%s"上的删除过程';
$lang['malformed_mass_delete_array'] = '不规则的mass_delete 数组。';
$lang['no_entries_to_delete'] = '你没有选择任何要删除的条目。';
$lang['deleting_dn'] = '正在删除 %s';
$lang['total_entries_failed'] = '删除条目%s（属于%s）失败。';
$lang['all_entries_successful'] = '所有条目删除成功。';
$lang['confirm_mass_delete'] = '确认大量删除条目%s（在服务器%s上）';
$lang['yes_delete'] = '是的，删除！';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = '你不能更名一个带有子条目的条目，举例，更名操作允许在非叶子条目上完成';
$lang['no_rdn_change'] = '你不能更改RDN';
$lang['invalid_rdn'] = '无效的RDN值';
$lang['could_not_rename'] = '不能更名该条目';

// General errors
//$lang['php5_unsupported'] = 'phpLDAPadmin 还不支持PHP 5。如果你继续可能会遇到许多意想不到的问题。';
$lang['mismatched_search_attr_config'] = '你的配置有个错误。 $search_attributes 与 $search_attributes_display 必须要有相同数目的属性。';

// Password checker
$lang['passwords_match'] = '密码匹配！';
$lang['passwords_do_not_match'] = '密码不匹配！';
$lang['password_checker_tool'] = '密码检查工具';
$lang['compare'] = '比较';
$lang['to'] = '与';

// Templates
$lang['using'] = '使用该';
//$lang['template'] = '模板';
$lang['switch_to'] = '你可以转换到';
$lang['default_template'] = '默认模板';

// template_config
$lang['user_account'] = '用户账号(posixAccount)';
$lang['address_book_inet'] = '地址簿条目(inetOrgPerson)';
$lang['address_book_moz'] = '地址簿条目(mozillaOrgPerson)';
$lang['kolab_user'] = 'Kolab用户条目';
$lang['organizational_unit'] = '组织化单元(Organizational Unit)';
$lang['organizational_role'] = '组织化角色';
$lang['posix_group'] = 'Posix组';
$lang['samba_machine'] = 'Samba NT 主机';
$lang['samba3_machine'] = 'Samba 3 NT 主机';
$lang['samba_user'] = 'Samba 用户';
$lang['samba3_user'] = 'Samba 3 用户';
$lang['samba3_group'] = 'Samba 3 组映像(Mapping)';
$lang['dns_entry'] = 'DNS 条目';
$lang['simple_sec_object'] = '简单安全对象(Simple Security Object)';
$lang['courier_mail_account'] = 'Courier 邮件账号';
$lang['courier_mail_alias'] = 'Courier 邮件别名';
$lang['ldap_alias'] = 'LDAP 别名';
$lang['sendmail_cluster'] = 'Sendmail 集群';
$lang['sendmail_domain'] = 'Sendmail 域';
$lang['sendmail_alias'] = 'Sendmail 别名';
$lang['sendmail_virt_dom'] = 'Sendmail 虚拟域';
$lang['sendmail_virt_users'] = 'Sendmail 虚拟用户';
$lang['sendmail_relays'] = 'Sendmail 回复';
$lang['custom'] = '自定义';
$lang['samba_domain_name'] = '我的Samba域名';
$lang['administrators'] = '管理员';
$lang['users'] = '用户';
$lang['guests'] = '一般用户(Guests)';
$lang['power_users'] = '增强用户(Power Users)';
$lang['account_ops'] = '账号管理员';
$lang['server_ops'] = '服务器管理员';
$lang['print_ops'] = '打印管理员';
$lang['backup_ops'] = '备份管理员';
$lang['replicator'] = '复制器(Replicator)';
$lang['unable_smb_passwords'] = '不能建立Samba密码。请检查文件template_config.php里的配置。';
$lang['err_smb_conf'] = '错误：在你的samba配置里有个错误。';
$lang['err_smb_no_name_sid'] = '错误：需要为你的samba域提供一个名字(name)和一个sid。';
$lang['err_smb_no_name'] = '错误：没有为samba域提供名字。';
$lang['err_smb_no_sid'] = '错误：没有为samba域提供sid。';

// Samba Account Template
$lang['samba_account'] = 'Samba 账号';
$lang['samba_account_lcase'] = 'samba 账号';

// New User (Posix) Account
$lang['t_new_user_account'] = '新建用户账号';
$lang['t_hint_customize'] = '提示：想要定制模板的话，你可以编辑文件templates/creation/new_user_template.php';
$lang['t_name'] = '名字';
$lang['t_first_name'] = '第一个名字';
$lang['t_last_name'] = '最后一个名字';
$lang['t_first'] = '第一个';
$lang['t_last'] = '最后一个';
$lang['t_common_name'] = '公有名字(Common name)';
$lang['t_user_name'] = '用户名';
$lang['t_password'] = '密码';
$lang['t_encryption'] = '加密方法';
$lang['t_login_shell'] = '登录Shell';
$lang['t_home_dir'] = '主目录';
$lang['t_uid_number'] = 'UID号';
$lang['t_auto_det'] = '（自动检测到的）';
$lang['t_group'] = '组';
$lang['t_gid_number'] = 'GID号';
$lang['t_err_passwords'] = '你的密码不匹配。请返回再试试。';
$lang['t_err_field_blank'] = '%s处不能为空。请返回再试试。';
$lang['t_err_field_num'] = '%s字段不能只输入数字。请返回再试试。';
$lang['t_err_bad_container'] = '你指定的容器(%s)不存在。请返回再试试。';
$lang['t_confirm_account_creation'] = '确认创建账号';
$lang['t_secret'] = '[隐秘]';
$lang['t_create_account'] = '创建账号';

// New Address Template
$lang['t_new_address'] = '新建地址簿条目';
$lang['t_organization'] = '组织';
$lang['t_address'] = '地址';
$lang['t_city'] = '城市';
$lang['t_postal_code'] = '邮政编码';
$lang['t_street'] = '街道';
$lang['t_work_phone'] = '工作电话';
$lang['t_fax'] = '传真(Fax)';
$lang['t_mobile'] = '移动电话(Mobile)';
$lang['t_email'] = '电子邮件';
$lang['t_container'] = '容器';
$lang['t_err_cn_blank'] = '公有名字(Common Name)不能为空。请返回再试试。';
$lang['t_confim_creation'] = '确认创建条目：';
$lang['t_create_address'] = '创建地址簿';

// default template
$lang['t_check_pass'] = '正在检查密码……';

// compare form
$lang['compare'] = '比较';
$lang['comparing'] = '比较紧跟着的DN';
$lang['compare_dn'] = '比较另一个DN跟';
$lang['with'] = '同 ';
$lang['compf_source_dn'] = '源DN';
$lang['compf_dn_tooltip'] = '将该DN与另一个比较';
$lang['switch_entry'] = '切换条目';
$lang['no_value'] = '没有值';
$lang['compare_with'] = '同另一个条目进行比较';
$lang['need_oclass'] = '要新增属性%s，你需要下面的ObjectClass(es)——对象类。';

// Time out page
$lang['session_timed_out_1'] = '你的会话期将在无活动的';
$lang['session_timed_out_2'] = '分钟后过期。你将自动退出。';
$lang['log_back_in'] = '想登录回来，请点击下面的链接：';
$lang['session_timed_out_tree'] = '(会话超时，自动退出。)';
$lang['timeout_at'] = '在%s如果活动，你将被登出。';


?>
