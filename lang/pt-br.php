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

/*
 * The $lang array contains all the strings that phpLDAPadmin uses.
 * Each language file simply defines this aray with strings in its
 * language.
 * $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/pt-br.php,v 1.2 2004/01/07 20:28:24 i18phpldapadmin Exp $
 */
/*
Initial translation from Alexandre Maciel (digitalman (a) bol (dot) com (dot) br) for phpldapadmin-0.9.3

*/

// Search form
$lang['simple_search_form_str'] = 'Busca Simples';
$lang['advanced_search_form_str'] = 'Busca Avan&ccedil;ada';
$lang['server'] = 'Servidor';
//$lang['search_for_entries_whose'] = 'Procurar por objetos cujo';
$lang['base_dn'] = 'Base <acronym title="Nome Distinto">DN</acronym>';
$lang['search_scope'] = 'Abrang&ecirc;ncia da Busca';
$lang['search_ filter'] = 'Filtro de Busca';
$lang['show_attributes'] = 'Exibir Atributos';
$lang['Search'] = 'Buscar';
// $lang['equals'] = 'equals';
// $lang['starts_with'] = 'starts with';
// $lang['contains'] = 'contains';
// $lang['ends_with'] = 'ends with';
// $lang['sounds_like'] = 'sounds like';

// Tree browser
$lang['request_new_feature'] = 'Requisitar um novo recurso';
$lang['see_open_requests'] = 'Ver as requisi&ccedil;&otilde;es em aberto';
$lang['report_bug'] = 'Comunicar um problema/defeito';
$lang['see_open_bugs'] = 'Ver os problemas/defeitos em aberto';
$lang['schema'] = 'esquemas';
$lang['search'] = 'buscar';
$lang['create'] = 'novo';
$lang['info'] = 'info';
$lang['import'] = 'importar';
$lang['refresh'] = 'atualizar';
$lang['logout'] = 'desconectar';
$lang['create_new'] = 'Criar Novo';
$lang['view_schema_for'] = 'ver esquemas de';
$lang['refresh_expanded_containers'] = 'atualizar todos os containers abertos em';
$lang['create_new_entry_on'] = 'criar um novo objeto em';
$lang['view_server_info'] = 'visualizar as informa&ccedil;&otilde;es fornecidas pelo servidor';
$lang['import_from_ldif'] = 'importar objetos de um arquivo LDIF';
$lang['logout_of_this_server'] = 'desconectar deste servidor';
$lang['logged_in_as'] = 'Conectado como: ';
$lang['read_only'] = 'somente leitura';
$lang['could_not_determine_root'] = 'Incapaz de determinar a raiz do sua &aacute;rvore LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Aparentemente o servidor LDAP foi configurado para ocultar sua raiz.';
$lang['please_specify_in_config'] = 'Por favor, especifique no arquivo config.php';
$lang['create_new_entry_in'] = 'Criar um novo objeto em';
$lang['login_link'] = 'Conectar...';

// Entry display
$lang['delete_this_entry'] = 'Apagar este objeto';
$lang['delete_this_entry_tooltip'] = 'Ser&aacute; solicitado que voc&ecirc; confirme sua decis&atilde;o';
$lang['copy_this_entry'] = 'Copiar este objeto';
$lang['copy_this_entry_tooltip'] = 'Copia este objeto para outro contexto, para um novo DN ou para outro servidor';
$lang['export_to_ldif'] = 'Exportar para LDIF';
$lang['export_to_ldif_tooltip'] = 'Salva um arquivo LDIF com os dados deste objeto';
$lang['export_subtree_to_ldif_tooltip'] = 'Salva um arquivo LDIF com os dados deste objeto e todos os seus filhos';
$lang['export_subtree_to_ldif'] = 'Exportar ramos para LDIF';
$lang['export_mac'] = 'Arquivo texto do tipo Macintosh';
$lang['export_win'] = 'Arquivo texto do tipo DOS/Windows';
$lang['export_unix'] = 'Arquivo texto do tipo Unix';
$lang['create_a_child_entry'] = 'Criar um objeto-filho';
$lang['add_a_jpeg_photo'] = 'Adicionar uma imagem JPEG';
$lang['rename_entry'] = 'Renomear Objeto';
$lang['rename'] = 'Renomear';
$lang['add'] = 'Adicionar';
$lang['view'] = 'Ver';
$lang['add_new_attribute'] = 'Adicionar Novo Atributo';
$lang['add_new_attribute_tooltip'] = 'Adiciona um novo atributo/valor para este objeto';
$lang['internal_attributes'] = 'Atributos Internos';
$lang['hide_internal_attrs'] = 'Ocultar os atributos internos';
$lang['show_internal_attrs'] = 'Exibir os atributos internos';
$lang['internal_attrs_tooltip'] = 'Atributos configurados automaticamente pelo sistema';
$lang['entry_attributes'] = 'Atributos do Objeto';
$lang['attr_name_tooltip'] = 'Clique para visualizar a defini&ccedil;&atilde;o do esquema para atributos do tipo \'%s\'';
$lang['click_to_display'] = 'Clique em \'+\' para exibir';
$lang['hidden'] = 'ocultos';
$lang['none'] = 'nenhum';
$lang['save_changes'] = 'Salvar Altera&ccedil;&otilde;es';
$lang['add_value'] = 'adicionar novo valor';
$lang['add_value_tooltip'] = 'Adiciona um novo valor para o atributo \'%s\'';
$lang['refresh_entry'] = 'Atualizar';
$lang['refresh_this_entry'] = 'Atualiza este objeto';
$lang['delete_hint'] = "Dica: <b>Para apagar um atributo</b>, deixe o campo correspondente vazio e clique em 'Salvar Altera&ccedil;&otilde;es'.";
$lang['attr_schema_hint'] = 'Dica: <b>Para visualizar o esquema de um atributo</b>, clique no nome do atributo desejado.';
$lang['attrs_modified'] = 'Alguns atributos (%s) foram modificados e est&atilde;o destacados abaixo.';
$lang['attr_modified'] = 'Um atributo (%s) foi modificado e est&aacute; destacado abaixo.';
$lang['viewing_read_only'] = 'Visualizando o objeto em modo somente-leitura.';
$lang['change_entry_rdn'] = 'Alterar o RDN deste objeto';
$lang['no_new_attrs_available'] = 'n&atilde;o h&aacute; mais atributos dispon&iacute;veis para este objeto';
$lang['binary_value'] = 'Valor bin&aacute;rio';
$lang['add_new_binary_attr'] = 'Adicionar Novo Atributo Bin&aacute;rio';
$lang['add_new_binary_attr_tooltip'] = 'Adiciona um novo atributo/valor bin&aacute;rio de um arquivo para este objeto';
$lang['alias_for'] = 'Nota: \'%s\' &eacute; um nome amig&aacute;vel para \'%s\'';
$lang['download_value'] = 'fazer download';
$lang['delete_attribute'] = 'apagar atributo';
$lang['true'] = 'verdadeiro';
$lang['false'] = 'falso';
$lang['none_remove_value'] = 'nenhum, remover o valor';
$lang['really_delete_attribute'] = 'Deseja realmente apagar atributo';

// Schema browser
$lang['the_following_objectclasses'] = 'As seguintes <b>classes de objetos</b> s&atilde;o suportadas por este servidor LDAP.';
$lang['the_following_attributes'] = 'Os seguintes <b>tipos de atributos</b> s&atilde;o suportados por este servidor LDAP.';
$lang['the_following_matching'] = 'As seguintes <b>regras de consist&ecirc;ncia/b> s&atilde;o suportadas por este servidor LDAP.';
$lang['the_following_syntaxes'] = 'As seguintes <b> regras de sintaxe</b> s&atilde;o suportadas por este servidor LDAP.';
$lang['jump_to_objectclass'] = 'Ir para uma classe de objetos';
$lang['jump_to_attr'] = 'Ir para um atributo';
$lang['schema_for_server'] = 'Esquemas do servidor';
$lang['required_attrs'] = 'Atributos Necess&aacute;rios';
$lang['optional_attrs'] = 'Atributos Opcionais';
$lang['OID'] = 'OID';
$lang['desc'] = 'Descri&ccedil;&atilde;o';
$lang['name'] = 'Nome';
$lang['is_obsolete'] = 'Essa classe de objetos &eacute; <b>obsoleta</b>';
$lang['inherits'] = 'Herda de';
$lang['jump_to_this_oclass'] = 'Ir para a defini&ccedil;&atilde;o desta classe de objetos';
$lang['matching_rule_oid'] = 'OID da Regra de Consist&ecirc;ncia';
$lang['syntax_oid'] = 'OID da Regra de Sintaxe';
$lang['not_applicable'] = 'n&atilde;o aplic&aacute;vel';
$lang['not_specified'] = 'n&atilde;o especificado';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Objeto \'%s\' apagado com sucesso.';
$lang['you_must_specify_a_dn'] = 'Voc&ecirc; precisa especificar o DN';
$lang['could_not_delete_entry'] = 'Imposs&iacute;vel apagar o objeto: %s';

// Adding objectClass form
$lang['new_required_attrs'] = 'Novo(s) Atributo(s) Necess&aacute;rio(s)';
$lang['requires_to_add'] = 'Essa a&ccedil;&atilde;o requer que voc&ecirc; adicione';
$lang['new_attributes'] = 'novo(s) atributos(s)';
$lang['new_required_attrs_instructions'] = 'Instru&ccedil;&otilde;es: Para poder acrescentar esta Classe de Objetos a este objeto, voc&ecirc; precisa especificar';
$lang['that_this_oclass_requires'] = 'que esta Classe de Objetos necessita. Voc&ecirc; pode faz&ecirc;-lo no formul&aacute;rio abaixo:';
$lang['add_oclass_and_attrs'] = 'Acrescentar Classe de Objetos e Atributos';

// General
$lang['chooser_link_tooltip'] = 'Clique aqui para abrir uma janela e selecionar o valor do atributo (DN) graficamente';
$lang['no_updates_in_read_only_mode'] = 'Voc&ecirc; n&atilde;o pode realizar altera&ccedil;&otilde;es enquanto o servidor estiver em modo somente-leitura';
$lang['bad_server_id'] = 'ID do servidor inv&aacute;lido';
$lang['not_enough_login_info'] = 'Informa&ccedil;&atilde;o insuficiente para efetuar conex&atilde;o ao servidor. Por favor verifique a sua configura&ccedil;&atilde;o.';
$lang['could_not_connect'] = 'Imposs&iacute;vel conectar ao servidor LDAP.';
$lang['could_not_perform_ldap_mod_add'] = 'Imposs&iacute;vel realizar opera&ccedil;&atilde;o ldap_mod_add.';
$lang['bad_server_id_underline'] = 'ID do servidor inv&aacute;lido: ';
$lang['success'] = ' Sucesso';
$lang['server_colon_pare'] = 'Servidor: ';
$lang['look_in'] = 'Procurando em: ';
$lang['missing_server_id_in_query_string'] = 'Nenhum ID de servidor especificado na busca!';
$lang['missing_dn_in_query_string'] = 'Nenhum DN especificado na busca!';
$lang['back_up_p'] = 'Backup...';
$lang['no_entries'] = 'nenhum objeto';
$lang['not_logged_in'] = 'N&atilde;o conectado';
$lang['could_not_det_base_dn'] = 'Imposs&iacute;vel determinar a base DN';

// Add value form
$lang['add_new'] = 'Adicionar novo valor';
$lang['value_to'] = 'para';
$lang['distinguished_name'] = 'Nome Distinto';
$lang['current_list_of'] = 'Lista atual de';
$lang['values_for_attribute'] = 'valor(es) para o atributo';
$lang['inappropriate_matching_note'] = 'Nota: Voc&ecirc; vai receber um erro de "inappropriate matching" se voc&ecirc; n&atilde;o<br />' .
			'configurar uma regra de <tt>IGUALDADE</tt> no seu servidor LDAP para este atributo.';
$lang['enter_value_to_add'] = 'Entre com o valor que voc&ecirc; gostaria de adicionar:';
$lang['new_required_attrs_note'] = 'Nota: talvez seja solicitado que voc&ecirc; entre com os atributos necess&aacute;rios para esta classe de objetos';
$lang['syntax'] = 'Sintaxe';

//copy.php
$lang['copy_server_read_only'] = 'Voc&ecirc; n&atilde;o pode realizar altera&ccedil;&otilde;es enquanto o servidor estiver em modo somente-leitura';
$lang['copy_dest_dn_blank'] = 'Voc&ecirc; n&atilde;o especificou o DN de destino.';
$lang['copy_dest_already_exists'] = 'O objeto de destino (%s) j&aacute; existe.';
$lang['copy_dest_container_does_not_exist'] = 'O container de destino (%s) n&atilde;o existe.';
$lang['copy_source_dest_dn_same'] = 'O DN de origem e o DN de destino s&atilde;o iguais.';
$lang['copy_copying'] = 'Copiando ';
$lang['copy_recursive_copy_progress'] = 'Progresso da c&oacute;pia recursiva';
$lang['copy_building_snapshot'] = 'Construindo a imagem da &aacute;rvore a ser copiada...';
$lang['copy_successful_like_to'] = 'C&oacute;pia bem-sucedida! Voc&ecirc; gostaria de ';
$lang['copy_view_new_entry'] = 'visualizar o novo objeto';
$lang['copy_failed'] = 'Falha ao copiar o DN: ';

//edit.php
$lang['missing_template_file'] = 'Alerta, o arquivo-modelo n&atilde;o encontrado: ';
$lang['using_default'] = 'Usando padr&atilde;o.';

//copy_form.php
$lang['copyf_title_copy'] = 'Copiar ';
$lang['copyf_to_new_object'] = 'para um novo objeto';
$lang['copyf_dest_dn'] = 'DN de Destino';
$lang['copyf_dest_dn_tooltip'] = 'O DN completo do novo objeto que ser&aacute; criado a partir da c&oacute;pia da origem';
$lang['copyf_dest_server'] = 'Servidor de Destino';
$lang['copyf_note'] = 'Dica: Copiar objetos entre servidores diferentes s&oacute; funcionar&aacute; se n&atilde;o houverem viola&ccedil;&otilde;es de esquema.';
$lang['copyf_recursive_copy'] = 'Copiar recursivamente todos os objetos-filhos deste objeto tamb&eacutem';

//create.php
$lang['create_required_attribute'] = 'Voc&ecirc; especificou um valor em branco para <b>%s</b>, que &eacute; um atributo necess&aacute;rio.';
$lang['create_redirecting'] = 'Redirecionando';
$lang['create_here'] = 'aqui';
$lang['create_could_not_add'] = 'Imposs&iacute;vel adicionar o objeto ao servidor LDAP.';

//create_form.php
$lang['createf_create_object'] = 'Criar um Objeto';
$lang['createf_choose_temp'] = 'Escolha um modelo';
$lang['createf_select_temp'] = 'Escolha o modelo correspondente ao objeto que deseja criar';
$lang['createf_proceed'] = 'Prosseguir';

//creation_template.php
$lang['ctemplate_on_server'] = 'No servidor';
$lang['ctemplate_no_template'] = 'Nenhum Modelo foi especificado.';
$lang['ctemplate_config_handler'] = 'Seu arquivo de configura&ccedil;&atilde;o determina que o modelo';
$lang['ctemplate_handler_does_not_exist'] = "&eacute; v&aacute;lido. Por&eacute;m este modelo n&atilde;o existe no diret&oacute;rio 'templates/creation'.";

// search.php
$lang['you_have_not_logged_into_server'] = 'Voc&ecirc; ainda n&atilde;o se conectou ao servidor LDAP selecionado, portanto voc&ecirc; n&atilde;o pode efetuar buscas nele.';
$lang['click_to_go_to_login_form'] = 'Clique aqui para conectar-se ao servidor';
$lang['unrecognized_criteria_option'] = 'Crit&eacute;rio de busca desconhecido: ';
$lang['if_you_want_to_add_criteria'] = 'Se voc&ecirc; quer adicionar o seu pr&oacute;prio crit&eacute;rio de busca &agrave; lista, certifique-se de que voc&ecirc; editou \'search.php\' para contemplar o novo crit&eacute;rio. Saindo.';
$lang['entries_found'] = 'Objetos encontrados: ';
$lang['filter_performed'] = 'Filtro utilizado: ';
$lang['search_duration'] = 'A busca foi realizada pelo phpLDAPadmin em';
$lang['seconds'] = 'segundos';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'A abrang&ecirc;ncia de onde &eacute; feita a busca pelos objetos';
$lang['scope_sub'] = 'Sub (toda a sub-arvore)';
$lang['scope_one'] = 'One (um nivel abaixo do base DN)';
$lang['scope_base'] = 'Base (apenas no base DN)';
$lang['standard_ldap_search_filter'] = 'Filtro de busca LDAP padr&atilde;o. Exemplo: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Filtro de Busca';
$lang['list_of_attrs_to_display_in_results'] = 'A lista de atributos que devem ser mostrados no resultado (separados por v&iacute;rgula)';
$lang['show_attributes'] = 'Exibir Atributos';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Procurar por objetos cujo:';
$lang['equals'] = 'e igual a';
$lang['starts with'] = 'comeca com';
$lang['contains'] = 'contem';
$lang['ends with'] = 'termina com';
$lang['sounds like'] = 'e semelhante a';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Incapaz de obter informa&ccedil;&otilde;es LDAP &agrave; partir do servidor';
$lang['server_info_for'] = 'Informa&ccedil;&otilde;es do servidor: ';
$lang['server_reports_following'] = 'O servidor forneceu as seguintes informa&ccedil;&otilde;es sobre si mesmo';
$lang['nothing_to_report'] = 'O servidor n&atilde;o tem nenhuma informa&ccedil;&atilde;o sobre si mesmo para fornecer.';

//update.php
$lang['update_array_malformed'] = 'update_array est&aaculte; mal-formado. Isto pode ser um problema/defeito do phpLDAPadmin. Por favor comunique isto.';
$lang['could_not_perform_ldap_modify'] = 'Imposs&iacute;vel realizar opera&ccedil;&atilde;o ldap_modify.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Voc&ecirc; confirma estas altera&ccedil;&otilde;es?';
$lang['attribute'] = 'Atributo';
$lang['old_value'] = 'Valor Antigo';
$lang['new_value'] = 'Valor Novo';
$lang['attr_deleted'] = '[atributo apagado]';
$lang['commit'] = 'Confirma';
$lang['cancel'] = 'Cancela';
$lang['you_made_no_changes'] = 'Voc&ecirc; n&atilde;o fez nenhuma altera&ccedil;&atilde;o.';
$lang['go_back'] = 'Voltar';

// welcome.php
$lang['welcome_note'] = 'Use o menu a esquerda para navegar';
$lang['credits'] = 'Cr&eacute;ditos';
$lang['changelog'] = '&Uacute;ltimas Atualiza&ccedil;&otilde;es';
$lang['documentation'] = 'Documenta&ccedil;&atilde;o';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nome de arquivo inseguro: ';
$lang['no_such_file'] = 'Nome de arquivo n&atilde;o encontrado: ';

//function.php
$lang['auto_update_not_setup'] = 'Voc&ecirc; ativou o recurso auto_uid_numbers para <b>%s</b> na sua configura&ccedil;&atilde;o,
                                 mas voc&ecirc; n&atilde;o definiu auto_uid_number_mechanism. Por favor, corrija este problema.';
$lang['uidpool_not_set'] = 'Voc&ecirc; especificou o recurso <tt>auto_uid_number_mechanism</tt> como <tt>uidpool</tt>
                            na sua configura&ccedil;&atilde;o para o servidor <b>%s</b>, mas voc&ecirc; n&atilde;o definiu 
			    audo_uid_number_uid_pool_dn. Por favor, corrija este problema.';
$lang['uidpool_not_exist'] = 'Aparentemente, o objeto uidPool que voc&ecirc; especificou (<tt>%s</tt>)
                              n&atilde;o existe.';
$lang['specified_uidpool'] = 'Voc&ecirc; especificou o recurso <tt>auto_uid_number_mechanism</tt> como <tt>search</tt> na sua 
			      configura&ccedil;&atilde;o para o servidor <b>%s</b>, mas voc&ecirc; n&atilde;o definiu 
                              <tt>auto_uid_number_search_base</tt>. Por favor, corrija este problema.';
$lang['auto_uid_invalid_value'] = 'Voc&ecirc; especificou um valor inv&aacute;lido para o recurso auto_uid_number_mechanism (<tt>%s</tt>)
                                   na sua configura&ccedil;&atilde;o. Apenas <tt>uidpool</tt> e <tt>search</tt> s&atilde;o v&aacute;lidos.
				   Por favor, corrija este problema.';
$lang['error_auth_type_config'] = 'Erro: Seu arquivo de configura&ccedil;&atilde;o possui um erro. Os &uacute;nicos valores permitidos para
                                   auth_type na se&ccedil;&atilde;o $servers s&atilde;o \'config\' e \'form\'. Voc&ecirc; usou \'%s\',
                                   o que n&atilde;o &eacute; permitido. ';
$lang['php_install_not_supports_tls'] = 'Sua instala&ccedil;&atilde;o do PHP n&atilde;o suporta TLS';
$lang['could_not_start_tls'] = 'Imposs&iacute;vel inicializar TLS.<br />Por favor, verifique a configura&ccedil;&atilde;o do seu servidor LDAP.';
$lang['auth_type_not_valid'] = 'Seu arquivo de configura&ccedil;&atilde;o possui um erro. O valor %s para auth_type n&atilde;o &eacute; permitido.';
$lang['ldap_said'] = '<b>O servidor LDAP respondeu</b>: %s<br /><br />';
$lang['ferror_error'] = 'Erro';
$lang['fbrowse'] = 'Procurar';
$lang['delete_photo'] = 'Apagar Imagem';
$lang['install_not_support_blowfish'] = 'Sua instala&ccedil;&atilde;o do PHP n&atilde;o suporta encripta&ccedil;&atilde;o blowfish.';
$lang['install_no_mash'] = 'Sua instala&ccedil;&atilde;o do PHP n&atilde;o possui a fun&ccedil;&atilde;o mhash(). Imposs&iacute;vel realizar encripta&ccedil;&otilde;es SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto possui erros<br />';
$lang['ferror_number'] = '<b>N&uacute;mero do erro</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] = '<b>Descri&ccedil;&atilde;o</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>N&uacute;mero do erro</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Descri&ccedil;&atilde;o</b>: (nenhuma descri&ccedil;&atilde;o dispon&iacute;vel)<br />';
$lang['ferror_submit_bug'] = 'Isto &eacute; um problema/defeito do phpLDAPadmin? Se sim, por favor <a href=\'%s\'>comunique isto</a>.';
$lang['ferror_unrecognized_num'] = 'N&uacute;mero do erro desconhecido: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Voc&ecirc; encontrou um erro n&atilde;o-fatal do phpLDAPadmin!</b></td></tr><tr><td>Erro:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Arquivo:</td>
             <td><b>%s</b> Linha <b>%s</b>, Requisitante <b>%s</b></td></tr><tr><td>Vers&otilde;es:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Servidor Web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Por favor, comunique este problema clicando aqui</a>.</center></td></tr></table></center><br />';

$lang['ferror_congrats_found_bug'] = 'Parab&eacute;ns! Voc&ecirc; encontrou um problema/defeito no phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Erro:</td><td><b>%s</b></td></tr>
	     <tr><td>N&iacute;vel:</td><td><b>%s</b></td></tr>
	     <tr><td>Arquivo:</td><td><b>%s</b></td></tr>
	     <tr><td>Linha:</td><td><b>%s</b></td></tr>
		 <tr><td>Requisitante:</td><td><b>%s</b></td></tr>
	     <tr><td>Vers&atilde;o do PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Vers&atilde;o do PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Servidor Web:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Por favor comunique esse problema/defeito clicando abaixo!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importar Arquivo LDIF';
$lang['select_ldif_file'] = 'Selecione um arquivo LDIF:';
$lang['select_ldif_file_proceed'] = 'Prosseguir &gt;&gt;';

//ldif_import
$lang['add_action'] = 'Adicionando...';
$lang['delete_action'] = 'Apagando...';
$lang['rename_action'] = 'Renomeando...';
$lang['modify_action'] = 'Modificando...';

$lang['failed'] = 'falhou';
$lang['ldif_parse_error'] = 'Erro Analisando Arquivo LDIF';
$lang['ldif_could_not_add_object'] = 'Imposs&iacute;vel adicionar objeto:';
$lang['ldif_could_not_rename_object'] = 'Imposs&iacute;vel renomear objeto:';
$lang['ldif_could_not_delete_object'] = 'Imposs&iacute;vel apagar objeto:';
$lang['ldif_could_not_modify_object'] = 'Imposs&iacute;vel modificar objeto:';
$lang['ldif_line_number'] = 'N&uacute;mero da Linha:';
$lang['ldif_line'] = 'Linha:';
?>
