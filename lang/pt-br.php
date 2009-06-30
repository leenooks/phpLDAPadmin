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
 * $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/pt-br.php,v 1.3 2004/05/06 20:00:37 i18phpldapadmin Exp $
 */
/*
Initial translation from Alexandre Maciel (digitalman (a) bol (dot) com (dot) br) for phpldapadmin-0.9.3
Next translation from  Elton (CLeGi - do at - terra.com.br) to cvs-release 1.65

*/

// Search form
$lang['simple_search_form_str'] = 'Formulário de busca Simples';//'Simple Search Form';
$lang['advanced_search_form_str'] = 'Formulário de busca Avançada';//'Advanced Search Form';
$lang['server'] = 'Servidor';//'Server';
$lang['search_for_entries_whose'] = 'Buscar objetos cujo...';//'Search for entries whose';
$lang['base_dn'] = 'Base DN';//'Base DN';
$lang['search_scope'] = 'Abrangência da Busca';//'Search Scope';
$lang['show_attributes'] = 'Exibir Atributos';//'Show Attributtes';
$lang['Search'] = 'Buscar';//'Search';
$lang['equals'] = 'igual';//'equals';
$lang['contains'] = 'contém';//'contains';
$lang['predefined_search_str'] = 'Selecione uma busca pré-definida';//'or select a predefined search';
$lang['predefined_searches'] = 'Buscas pré-definidas';//'Predefined Searches';
$lang['no_predefined_queries'] = 'Nenhum critério de busca foi definido no config.php';// 'No queries have been defined in config.php.';


// Tree browser
$lang['request_new_feature'] = 'Solicitar uma nova função';//'Request a new feature';
$lang['report_bug'] = 'Comunicar um bug';//'Report a bug';
$lang['schema'] = 'esquema';//'schema';
$lang['search'] = 'buscar';//'search';
$lang['refresh'] = 'atualizar';//'refresh';
$lang['create'] = 'criar';//'create';
$lang['info'] = 'info';//'info';
$lang['import'] = 'importar';//'import';
$lang['logout'] = 'desconectar';//'logout';
$lang['create_new'] = 'Criar Novo';//'Create New';
$lang['new'] = 'Novo';//'new';
$lang['view_schema_for'] = 'Ver esquemas de';//'View schema for';
$lang['refresh_expanded_containers'] = 'Atualizar todos containers abertos para';//'Refresh all expanded containers for';
$lang['create_new_entry_on'] = 'Criar um novo objeto em';//'Create a new entry on';
$lang['view_server_info'] = 'Ver informações fornecidas pelo servidor';//'View server-supplied information';
$lang['import_from_ldif'] = 'Importar objetos de um arquivo LDIF';//'Import entries from an LDIF file';
$lang['logout_of_this_server'] = 'Desconectar deste servidor';//'Logout of this server';
$lang['logged_in_as'] = 'Conectado como: ';//'Logged in as: ';
$lang['read_only'] = 'somente leitura';//'read only';
$lang['read_only_tooltip'] = 'Este atributo foi marcado como somente leitura pelo administrador do phpLDAPadmin';//This attribute has been flagged as read only by the phpLDAPadmin administrator';
$lang['could_not_determine_root'] = 'Não foi possível determinar a raiz da sua árvore LDAP.';//'Could not determin the root of your LDAP tree.';
$lang['ldap_refuses_to_give_root'] = 'Parece que o servidor LDAP foi configurado para não revelar seu root.';//'It appears that the LDAP server has been configured to not reveal its root.';
$lang['please_specify_in_config'] = 'Por favor especifique-o no config.php';//'Please specify it in config.php';
$lang['create_new_entry_in'] = 'Criar um novo objeto em';//'Create a new entry in';
$lang['login_link'] = 'Conectar...';//'Login...';
$lang['login'] = 'conectar';//'login';

// Entry display
$lang['delete_this_entry'] = 'Excluir este objeto';//'Delete this entry';
$lang['delete_this_entry_tooltip'] = 'Será solicitado que você confirme sua decisão';//'You will be prompted to confirm this decision';
$lang['copy_this_entry'] = 'Copiar este objeto';//'Copy this entry';
$lang['copy_this_entry_tooltip'] = 'Copiar este objeto para outro local, um novo DN, ou outro servidor';//'Copy this object to another location, a new DN, or another server';
$lang['export'] = 'Exportar';//'Export to LDIF';
$lang['export_tooltip'] = 'Salva um arquivo LDIF com os dados deste objeto';//'Save an LDIF dump of this object';
$lang['export_subtree_tooltip'] = 'Salva um arquivo LDIF com os dados deste objeto e todos os seus filhos';//'Save an LDIF dump of this object and all of its children';
$lang['export_subtree'] = 'Exportar sub-árvore para LDIF';//'Export subtree to LDIF';
$lang['create_a_child_entry'] = 'Criar objeto filho';//'Create a child entry';
$lang['rename_entry'] = 'Renomear objeto';//'Rename Entry';
$lang['rename'] = 'Renomear';//'Rename';
$lang['add'] = 'Inserir';//'Add';
$lang['view'] = 'Ver';//'View';
$lang['view_one_child'] = 'Ver 1 filho';//'View 1 child';
$lang['view_children'] = 'Ver %s filhos';//'View %s children';
$lang['add_new_attribute'] = 'Inserir Novo Atributo';//'Add New Attribute';
$lang['add_new_objectclass'] = 'Inserir nova ObjectClass';//'Add new ObjectClass';
$lang['hide_internal_attrs'] = 'Ocultar atributos internos';//'Hide internal attributes';
$lang['show_internal_attrs'] = 'Exibir atributos internos';//'Show internal attributes';
$lang['attr_name_tooltip'] = 'Clique para ver a definição do esquema para atributos do tipo \'%s\'';//'Click to view the schema defintion for attribute type \'%s\'';
$lang['none'] = 'nenhum';//'none';
$lang['no_internal_attributes'] = 'Nenhum atributo interno.';//'No internal attributes';
$lang['no_attributes'] = 'Este objeto não tem atributos.';//'This entry has no attributes';
$lang['save_changes'] = 'Salvar Alterações';//'Save Changes';
$lang['add_value'] = 'inserir valor';//'add value';
$lang['add_value_tooltip'] = 'Insere um novo valor para o atributo \'%s\'';//'Add an additional value to this attribute';
$lang['refresh_entry'] = 'Atualizar';// 'Refresh';
$lang['refresh_this_entry'] = 'Atualizar este objeto';//'Refresh this entry';
$lang['delete_hint'] = 'Dica: Para apagar um atributo, apague o conteúdo do campo de texto e clique salvar.';//'Hint: <b>To delete an attribute</b>, empty the text field and click save.';
$lang['attr_schema_hint'] = 'Dica: Para ver o esquema de um atributo clique no nome do atributo.';//'Hint: <b>To view the schema for an attribute</b>, click the attribute name.';
$lang['attrs_modified'] = 'Alguns atributos (%s) foram modificados e estão destacados abaixo.';//'Some attributes (%s) were modified and are highlighted below.';
$lang['attr_modified'] = 'Um atributo (%s) foi modificado e está destacado abaixo';//'An attribute (%s) was modified and is highlighted below.';
$lang['viewing_read_only'] = 'Vizualizando objeto em modo somente-leitura.';//'Viewing entry in read-only mode.';
$lang['no_new_attrs_available'] = 'novos atributos não disponíveis para este objeto.';//'no new attributes available for this entry';
$lang['no_new_binary_attrs_available'] = 'novos atributos binários não disponíveis para este objeto.';//'no new binary attributes available for this entry';
$lang['binary_value'] = 'Valor binário';//'Binary value';
$lang['add_new_binary_attr'] = 'Inserir novo atributo binário';//'Add New Binary Attribute';
$lang['alias_for'] = 'Nota: \'%s\' é um alias para \'%s\'';//'Note: \'%s\' is an alias for \'%s\'';
$lang['download_value'] = 'download do valor';//'download value';
$lang['delete_attribute'] = 'apagar atributo';//'delete attribute';
$lang['true'] = 'verdadeiro';//'true';
$lang['false'] = 'falso';//'false';
$lang['none_remove_value'] = 'nenhum, remover valor';//?? //'none, remove value';
$lang['really_delete_attribute'] = 'Deseja realmente apagar atributo';//'Really delete attribute';
$lang['add_new_value'] = 'Inserir novo valor';//'Add New Value';

// Schema browser
$lang['the_following_objectclasses'] = 'As seguintes Classes de objetos são suportadas por este servidor LDAP.';//'The following <b>objectClasses</b> are supported by this LDAP server.';
$lang['the_following_attributes'] = 'Os seguintes tipos de atributos são suportadas por este servidor LDAP.';//'The following <b>attributeTypes</b> are supported by this LDAP server.';
$lang['the_following_matching'] = 'As seguintes regras de consistência são suportadas por este servidor LDAP.';//'The following <b>matching rules</b> are supported by this LDAP server.';
$lang['the_following_syntaxes'] = 'As seguintes sintaxes são suportadas por este servidor LDAP.';//'The following <b>syntaxes</b> are supported by this LDAP server.';
$lang['schema_retrieve_error_1']='O servidor não suporta o protocolo LDAP completamente.';//'The server does not fully support the LDAP protocol.';
$lang['schema_retrieve_error_2']='Sua versão do PHP não executa a consulta corretamente.';//'Your version of PHP does not correctly perform the query.';
$lang['schema_retrieve_error_3']='Ou, por fim, o phpLDAPadmin não sabe como buscar o esquema para o seu servidor.';//'Or lastly, phpLDAPadmin doesn\'t know how to fetch the schema for your server.';
$lang['jump_to_objectclass'] = 'Ir para uma classe de objetos';//'Jump to an objectClass';
$lang['jump_to_attr'] = 'Ir para um tipo de atributo';//'Jump to an attribute';
$lang['jump_to_matching_rule'] = 'Ir para regras de consistência';//'Jump to a matching rule';
$lang['schema_for_server'] = 'Esquema para servidor';//'Schema for server';
$lang['required_attrs'] = 'Atributos Obrigatórios';//'Required Attributes';
$lang['optional_attrs'] = 'Atributos Opcionais';//'Optional Attributes';
$lang['optional_binary_attrs'] = 'Atributos Binários Opcionais';//'Optional Binary Attributes';
$lang['OID'] = 'OID';//'OID';
$lang['aliases']='Apelidos';//'Aliases';
$lang['desc'] = 'Descrição';//'Description';
$lang['no_description']='sem descrição';//'no description';
$lang['name'] = 'Nome';//'Name';
$lang['equality']='Igualdade';//'Equality';
$lang['is_obsolete'] = 'Esta classe de objeto está obsoleta.';//'This objectClass is <b>obsolete</b>';
$lang['inherits'] = 'Herda de';//'Inherits';
$lang['inherited_from']='Herdado de';//inherited from';
$lang['parent_to'] = 'Pai para';//'Parent to';
$lang['jump_to_this_oclass'] = 'Ir para definição desta classe de objeto';//'Jump to this objectClass definition';
$lang['matching_rule_oid'] = 'OID da Regra de consistência';//'Matching Rule OID';
$lang['syntax_oid'] = 'OID da Sintaxe';//'Syntax OID';
$lang['not_applicable'] = 'não aplicável';//'not applicable';
$lang['not_specified'] = 'não especificado';//not specified';
$lang['character']='caracter';//'character'; 
$lang['characters']='caracteres';//'characters';
$lang['used_by_objectclasses']='Usado por classes de objetos';//'Used by objectClasses';
$lang['used_by_attributes']='Usado por Atributos';//'Used by Attributes';
$lang['oid']='OID';
$lang['obsolete']='Obsoleto';//'Obsolete';
$lang['ordering']='Ordenando';//'Ordering';
$lang['substring_rule']='Regra de substring';//'Substring Rule';
$lang['single_valued']='Avaliado sozinho';//'Single Valued';
$lang['collective']='Coletivo';//'Collective';
$lang['user_modification']='Alteração do usuário';//'User Modification';
$lang['usage']='Uso';//'Usage';
$lang['maximum_length']='Tamanho Máximo';//'Maximum Length';
$lang['attributes']='Tipos de Atriburos';//'Attributes Types';
$lang['syntaxes']='Sintaxes';//'Syntaxes';
$lang['objectclasses']='Classe de Objetos';//'objectClasses';
$lang['matchingrules']='Regra de consistência';//'Matching Rules';
$lang['could_not_retrieve_schema_from']='Não foi possível encontrar esquema de';//'Could not retrieve schema from';
$lang['type']='Tipo';// 'Type';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Objeto \'%s\' excluído com sucesso.';//'Entry \'%s\' deleted successfully.';
$lang['you_must_specify_a_dn'] = 'Você deve especificar um DN';//'You must specify a DN';
$lang['could_not_delete_entry'] = 'Não foi possível excluir o objeto: %s';//'Could not delete the entry: %s';
$lang['no_such_entry'] = 'Objeto inexistente: %s';//'No such entry: %s';
$lang['delete_dn'] = 'Excluir %s';//'Delete %s';
$lang['entry_is_root_sub_tree'] = 'Este objeto é a raiz de uma sub-árvore e contém %s objetos.';//'This entry is the root of a sub-tree containing %s entries.';
$lang['view_entries'] = 'Ver objetos';//'view entries';
$lang['confirm_recursive_delete'] = 'o phpLDAPadmin pode excluir recursivamente este objeto e todos %s filhos. Veja abaixo uma lista de todos os objetos que esta ação vai excluir. Deseja fazer isso?';//'phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?';
$lang['confirm_recursive_delete_note'] = 'Nota: isto é potencialmente muito perigoso, faça isso por sua conta e risco. Esta operação não pode ser desfeita. Leve em consideração apelidos, referências e outras coisas que podem causar problemas.';//'Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.';
$lang['delete_all_x_objects'] = 'Excluir todos os %s objetos';//'Delete all %s objects';
$lang['recursive_delete_progress'] = 'Progresso de exclusão recursiva';//'Recursive delete progress';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Objeto %s e sub-árvore excluído com sucesso.';// 'Entry %s and sub-tree deleted successfully.';
$lang['failed_to_delete_entry'] = 'Falha ao excluir objeto %s';//'Failed to delete entry %s';

// Deleting attributes
$lang['attr_is_read_only'] = 'O atributo %s está marcado como somente leitura na configuração do phpLDAPadmin.';//'The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.';
$lang['no_attr_specified'] = 'Nome de atributo não especificado.';//'No attribute name specified.';
$lang['no_dn_specified'] = 'DN não especificado';//'No DN specified';

// Adding attributes
$lang['left_attr_blank'] = 'Você deixou o valor do atributo vazio. Por favor retorne e tente novamente.';//'You left the attribute value blank. Please go back and try again.';
$lang['failed_to_add_attr'] = 'Falha ao inserir o atributo.';//'Failed to add the attribute.';
$lang['file_empty'] = 'O arquivo que você escolheu está vazio ou não existe. Por favor retorne e tente novamente.';//'The file you chose is either empty or does not exist. Please go back and try again.';
$lang['invalid_file'] = 'Erro de segurança: O arquivo que está sendo carregado pode ser malicioso.';//'Security error: The file being uploaded may be malicious.';
$lang['warning_file_uploads_disabled'] = 'Sua configuração do PHP desabilitou o upload de arquivos. Por favor verifique o php.ini antes de continuar.';//'Your PHP configuration has disabled file uploads. Please check php.ini before proceeding.';
$lang['uploaded_file_too_big'] = 'O arquivo que você carregou é muito grande. Por favor verifique a configuração do upload_max_size no php.ini';//'The file you uploaded is too large. Please check php.ini, upload_max_size setting';
$lang['uploaded_file_partial'] = 'O arquivo que você selecionou foi carregado parcialmente, provavelmente por causa de um erro de rede.';//'The file you selected was only partially uploaded, likley due to a network error.';
$lang['max_file_size'] = 'Tamanho máximo de arquivo: %s';//'Maximum file size: %s';

// Updating values
$lang['modification_successful'] = 'Alteração bem sucedida!';//'Modification successful!';
$lang['change_password_new_login'] = 'Você alterou sua senha, você deve conectar novamente com a sua nova senha.'; //'Since you changed your password, you must now login again with your new password.';


// Adding objectClass form
$lang['new_required_attrs'] = 'Novos Atributos Obrigatórios';//'New Required Attributes';
$lang['requires_to_add'] = 'Esta ação requer que você insira';//'This action requires you to add';
$lang['new_attributes'] = 'novos atributos';//'new attributes';
$lang['new_required_attrs_instructions'] = 'Instruções: Para poder inserir esta Classe de Objetos a este objeto, você deve especificar';//'Instructions: In order to add this objectClass to this entry, you must specify';
$lang['that_this_oclass_requires'] = 'que esta Classe de Objetos requer. Você pode fazê-lo no formulário abaixo:';//'that this objectClass requires. You can do so in this form.';
$lang['add_oclass_and_attrs'] = 'Inserir Classe de Objetos e Atributos';//'Add ObjectClass and Attributes';

// General
$lang['chooser_link_tooltip'] = 'Clique para abrir uma janela e selecionar um objeto (DN) graficamente';//"Click to popup a dialog to select an entry (DN) graphically';
$lang['no_updates_in_read_only_mode'] = 'Você não pode realizar atualizações enquanto o servidor estiver em modo somente leitura';//'You cannot perform updates while server is in read-only mode';
$lang['bad_server_id'] = 'ID do servidor inválido';//'Bad server id';
$lang['not_enough_login_info'] = 'Informações insuficientes para a conexão com o servidor. Por favor verifique sua configuração.';//'Not enough information to login to server. Please check your configuration.';
$lang['could_not_connect'] = 'Não foi possível conectar com o servidor LDAP.';//'Could not connect to LDAP server.';
$lang['could_not_connect_to_host_on_port'] = 'Não foi possível conectar em "%s" na porta "%s"';//'Could not connect to "%s" on port "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'Não foi possível executar operação ldap_mod_add.';//'Could not perform ldap_mod_add operation.';
$lang['bad_server_id_underline'] = 'server_id inválido: ';//"Bad server_id: ';
$lang['success'] = 'Sucesso';//"Success';
$lang['server_colon_pare'] = 'Servidor: ';//"Server: ';
$lang['look_in'] = 'Procurando em: ';//"Looking in: ';
$lang['missing_server_id_in_query_string'] = 'ID do servidor não especificado na consulta!';//'No server ID specified in query string!';
$lang['missing_dn_in_query_string'] = 'DN não especificado na consulta!';//'No DN specified in query string!';
$lang['back_up_p'] = 'Backup...';//"Back Up...';
$lang['no_entries'] = 'nenhum objeto';//"no entries';
$lang['not_logged_in'] = 'Não conectado';//"Not logged in';
$lang['could_not_det_base_dn'] = 'Não foi possível determinar a base DN';//"Could not determine base DN';
$lang['reasons_for_error']='Isso pode ter acontecido por vários motivos, os mais provável são:';//'This could happen for several reasons, the most probable of which are:';
$lang['please_report_this_as_a_bug']='Por favor informe isso como bug.';//'Please report this as a bug.';
$lang['yes']='Sim';//'Yes'
$lang['no']='Não';//'No'
$lang['go']='Ir';//'go'
$lang['delete']='Excluir';//'Delete';
$lang['back']='Voltar';//'Back';
$lang['object']='objeto';//'object';
$lang['delete_all']='Excluir tudo';//'Delete all';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'dica';//'hint';
$lang['bug'] = 'bug';//'bug';
$lang['warning'] = 'aviso';//'warning';
$lang['light'] = 'a palavra "light" de "light bulb"'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Continuar &gt;&gt;';//'Proceed &gt;&gt;';


// Add value form
$lang['add_new'] = 'Inserir novo';//'Add new';
$lang['value_to'] = 'valor para';//'value to';
//also used in copy_form.php
$lang['distinguished_name'] = 'Nome Distinto';// 'Distinguished Name';
$lang['current_list_of'] = 'Lista atual de';//'Current list of';
$lang['values_for_attribute'] = 'valores para atributo';//'values for attribute';
$lang['inappropriate_matching_note'] = 'Nota: Você vai receber um erro de "inappropriate matching" se você não configurar uma regra de IGUALDADE no seu servidor LDAP para este atributo.'; //'Note: You will get an "inappropriate matching" error if you have not<br /> setup an <tt>EQUALITY</tt> rule on your LDAP server for this attribute.';
$lang['enter_value_to_add'] = 'Entre com o valor que você quer inserir: ';//'Enter the value you would like to add:';
$lang['new_required_attrs_note'] = 'Nota: talvez seja solicitado que você entre com os atributos necessários para esta classe de objetos';//'Note: you may be required to enter new attributes<br />that this objectClass requires.';
$lang['syntax'] = 'Sintaxe';//'Syntax';

//Copy.php
$lang['copy_server_read_only'] = 'Você não pode realizar atualizações enquanto o servidor estiver em modo somente leitura';//"You cannot perform updates while server is in read-only mode';
$lang['copy_dest_dn_blank'] = 'Você deixou o DN de destino vazio.';//"You left the destination DN blank.';
$lang['copy_dest_already_exists'] = 'O objeto de destino (%s) já existe.';//"The destination entry (%s) already exists.';
$lang['copy_dest_container_does_not_exist'] = 'O container de destino (%s) não existe.';//'The destination container (%s) does not exist.';
$lang['copy_source_dest_dn_same'] = 'O DN de origem e destino são o mesmo.';//"The source and destination DN are the same.';
$lang['copy_copying'] = 'Copiando ';//"Copying ';
$lang['copy_recursive_copy_progress'] = 'Progresso da cópia recursiva';//"Recursive copy progress';
$lang['copy_building_snapshot'] = 'Construindo a imagem da árvore a ser copiada...';//"Building snapshot of tree to copy... ';
$lang['copy_successful_like_to'] = 'Copiado com sucesso! Você gostaria de ';//"Copy successful! Would you like to ';
$lang['copy_view_new_entry'] = 'ver o novo objeto';//"view the new entry';
$lang['copy_failed'] = 'Falha ao copiar DN: ';//'Failed to copy DN: ';


//edit.php
$lang['missing_template_file'] = 'Aviso: arquivo modelo faltando, ';//'Warning: missing template file, ';
$lang['using_default'] = 'Usando o padrão.';//'Using default.';
$lang['template'] = 'Modelo';//'Template';
$lang['must_choose_template'] = 'Você deve escolher um modelo';//'You must choose a template';
$lang['invalid_template'] = '%s é um modelo inválido';// '%s is an invalid template';
$lang['using_template'] = 'usando o modelo';//'using template';
$lang['go_to_dn'] = 'Ir para %s';//'Go to %s';

//copy_form.php
$lang['copyf_title_copy'] = 'Copiar ';//"Copy ';
$lang['copyf_to_new_object'] = 'para um novo objeto';//"to a new object';
$lang['copyf_dest_dn'] = 'DN de destino';//"Destination DN';
$lang['copyf_dest_dn_tooltip'] = 'O DN completo do novo objeto que será criado a partir da cópia da origem';//'The full DN of the new entry to be created when copying the source entry';
$lang['copyf_dest_server'] = 'Servidor de destino';//"Destination Server';
$lang['copyf_note'] = 'Dica: Copiando entre diferentes servidores somente funciona se não houver violação de esquema';//"Note: Copying between different servers only works if there are no schema violations';
$lang['copyf_recursive_copy'] = 'Copia recursivamente todos filhos deste objeto também.';//"Recursively copy all children of this object as well.';
$lang['recursive_copy'] = 'Cópia Recursiva';//'Recursive copy';
$lang['filter'] = 'Filtro';//'Filter';
$lang['filter_tooltip'] = 'Quando executar uma cópia recursiva, copiar somente os objetos que atendem a este filtro';// 'When performing a recursive copy, only copy those entries which match this filter';


//create.php
$lang['create_required_attribute'] = 'Você deixou vazio o valor de um atributo obrigatório (%s).';//"Error, you left the value blank for required attribute ';
$lang['redirecting'] = 'Redirecionando...';//"Redirecting'; moved from create_redirection -> redirection
$lang['here'] = 'aqui';//"here'; renamed vom create_here -> here
$lang['create_could_not_add'] = 'Não foi possível inserir o objeto no servidor LDAP.';//"Could not add the object to the LDAP server.';

//create_form.php
$lang['createf_create_object'] = 'Criar Objeto';//"Create Object';
$lang['createf_choose_temp'] = 'Escolher um modelo';//"Choose a template';
$lang['createf_select_temp'] = 'Selecionar um modelo para o processo de criação';//"Select a template for the creation process';
$lang['createf_proceed'] = 'Prosseguir';//"Proceed &gt;&gt;';
$lang['rdn_field_blank'] = 'Você deixou o campo RDN vazio.';//'You left the RDN field blank.';
$lang['container_does_not_exist'] = 'O container que você especificou (%s) não existe. Por favor tente novamente.';// 'The container you specified (%s) does not exist. Please try again.';
$lang['no_objectclasses_selected'] = 'Você não selecionou nenhuma Classe de Objetos para este objeto. Por favor volte e faça isso.';//'You did not select any ObjectClasses for this object. Please go back and do so.';
$lang['hint_structural_oclass'] = 'Dica: Você deve escolher pelo menos uma Classe de Objetos estrutural';//'Hint: You must choose at least one structural objectClass';

//creation_template.php
$lang['ctemplate_on_server'] = 'No servidor';//"On server';
$lang['ctemplate_no_template'] = 'Nenhum modelo especificado.';//"No template specified in POST variables.';
$lang['ctemplate_config_handler'] = 'Seu arquivo de configuração determina que o modelo';//"Your config specifies a handler of';
$lang['ctemplate_handler_does_not_exist'] = 'é válido. Porém este modelo não existe no diretório "templates/creation".';//"for this template. But, this handler does not exist in the 'templates/creation' directory.';
$lang['create_step1'] = 'Passo 1 de 2: Nome e Classe(s) de Objetos';//'Step 1 of 2: Name and ObjectClass(es)';
$lang['create_step2'] = 'Passo 2 de 2: Especifica atributos e valores';//'Step 2 of 2: Specify attributes and values';
$lang['relative_distinguished_name'] = 'Nome Distinto Relativo';//'Relative Distinguished Name';
$lang['rdn'] = 'RDN';//'RDN';
$lang['rdn_example'] = 'exemplo: cn=MinhaNovaPessoa';//'(example: cn=MyNewPerson)';
$lang['container'] = 'Container';//'Container';


// search.php
$lang['you_have_not_logged_into_server'] = 'Você não conectou no servidor selecionado ainda, assim, você não pode realizar buscas nele.';//'You have not logged into the selected server yet, so you cannot perform searches on it.';
$lang['click_to_go_to_login_form'] = 'Clique aqui para conectar-se ao servidor';//'Click here to go to the login form';
$lang['unrecognized_criteria_option'] = 'Critério desconhecido: ';// 'Unrecognized criteria option: ';
$lang['if_you_want_to_add_criteria'] = 'Se você quer inserir seus próprios critérios à lista. Certifique-se de editar o search.php para tratá-los. Saindo.';//'If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.';
$lang['entries_found'] = 'Objetos encontrados: ';//'Entries found: ';
$lang['filter_performed'] = 'Filtro aplicado: ';//'Filter performed: ';
$lang['search_duration'] = 'Busca realizada pelo phpLDAPadmin em';//'Search performed by phpLDAPadmin in';
$lang['seconds'] = 'segundos';//'seconds';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'O escopo no qual procurar';//'The scope in which to search';
$lang['scope_sub'] = 'Sub (toda a sub-árvore)';//'Sub (entire subtree)';
$lang['scope_one'] = 'One (um nível de profundidade)';//'One (one level beneath base)';
$lang['scope_base'] = 'Base (apenas a base dn)';//'Base (base dn only)';
$lang['standard_ldap_search_filter'] = 'Filtro de busca LDAP padrão. Exemplo: (&(sn=Silva)(givenname=Pedro))';//'Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Filtro de Busca';//'Search Filter';
$lang['list_of_attrs_to_display_in_results'] = 'A lista de atributos que devem ser mostrados nos resultados (separados por vírgula)';//'A list of attributes to display in the results (comma-separated)';

// search_form_simple.php
$lang['starts with'] = 'inicia com';//'starts with';
$lang['ends with'] = 'termina com';//'ends with';
$lang['sounds like'] = 'é semelhante a';//'sounds like';


// server_info.php
$lang['could_not_fetch_server_info'] = 'Não foi possível obter informação LDAP do servidor';//'Could not retrieve LDAP information from the server';
$lang['server_info_for'] = 'Informações do servidor: ';//'Server info for: ';
$lang['server_reports_following'] = 'O servidor forneceu a seguinte informação sobre si mesmo';//'Server reports the following information about itself';
$lang['nothing_to_report'] = 'Este servidor não tem nada a informar';//'This server has nothing to report.';

//update.php
$lang['update_array_malformed'] = 'update_array danificado. Isto pode ser um bug do phpLDAPadmin. Por favor informe.';//'update_array is malformed. This might be a phpLDAPadmin bug. Please report it.';
$lang['could_not_perform_ldap_modify'] = 'Não foi possível realizar a operação ldap_modify.';//'Could not perform ldap_modify operation.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Você confirma estas alterações?';//'Do you want to make these changes?';
$lang['attribute'] = 'Atributo';//'Attribute';
$lang['old_value'] = 'Valor Antigo';//'Old Value';
$lang['new_value'] = 'Valor Novo';//'New Value';
$lang['attr_deleted'] = '[atributo excluído]';//'[attribute deleted]';
$lang['commit'] = 'Confirmar';//'Commit';
$lang['cancel'] = 'Cancelar';//'Cancel';
$lang['you_made_no_changes'] = 'Você não fez alterações';//'You made no changes';
$lang['go_back'] = 'Voltar';//'Go back';

// welcome.php
$lang['welcome_note'] = 'Use o menu à esquerda para navegar';//'Use the menu to the left to navigate';
$lang['credits'] = 'Créditos';//'Credits';
$lang['changelog'] = 'Log de Alterações';//'ChangeLog';
$lang['donate'] = 'Contribuir';//'Donate';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nome de arquivo inseguro: ';//'Unsafe file name: ';
$lang['no_such_file'] = 'Arquivo inexistente: ';//'No such file: ';

//function.php
$lang['auto_update_not_setup'] = 'Você habilitou auto_uid_numbers para <b>%s</b> na sua configuração, mas você não especificou auto_uid_number_mechanism. Por favor corrija este problema.';//"You have enabled auto_uid_numbers for <b>%s</b> in your configuration, but you have not specified the auto_uid_number_mechanism. Please correct this problem.';
$lang['uidpool_not_set'] = 'Você especificou o "auto_uid_number_mechanism" como "uidpool" na sua configuração para o servidor <b>%s</b>, mas você não especificou o audo_uid_number_uid_pool_dn. Por favor especifique-o antes de continuar.';//"You specified the <tt>auto_uid_number_mechanism</tt> as <tt>uidpool</tt> in your configuration for server <b>%s</b>, but you did not specify the audo_uid_number_uid_pool_dn. Please specify it before proceeding.';
$lang['uidpool_not_exist'] = 'Parece que o uidPool que você especificou na sua configuração (<tt>%s</tt>) não existe.';//"It appears that the uidPool you specified in your configuration (<tt>%s</tt>) does not exist.';
$lang['specified_uidpool'] = 'Você especificou o "auto_uid_number_mechanism" como "busca" na sua configuração para o servidor <b>%s</b>, mas você não especificou o "auto_uid_number_search_base". Por favor especifique-o antes de continuar.';//"You specified the <tt>auto_uid_number_mechanism</tt> as <tt>search</tt> in your configuration for server <b>%s</b>, but you did not specify the <tt>auto_uid_number_search_base</tt>. Please specify it before proceeding.';
$lang['auto_uid_invalid_credential'] = 'Problema ao conectar ao <b>%s</b> com as suas credenciais auto_uid. Por favor verifique seu arquivo de configuração.';// 'Unable to bind to <b>%s</b> with your with auto_uid credentials. Please check your configuration file.'; 
$lang['bad_auto_uid_search_base'] = 'Sua configuração do phpLDAPadmin especifica que o auto_uid_search_base é inválido para o servidor %s';//'Your phpLDAPadmin configuration specifies an invalid auto_uid_search_base for server %s'; 
$lang['auto_uid_invalid_value'] = 'Você especificou um valor inválido para auto_uid_number_mechanism ("%s") na sua configuração. Somente "uidpool" e "busca" são válidos. Por favor corrija este problema.';//"You specified an invalid value for auto_uid_number_mechanism (<tt>%s</tt>) in your configration. Only <tt>uidpool</tt> and <tt>search</tt> are valid. Please correct this problem.';

$lang['error_auth_type_config'] = 'Erro: Você tem um erro no seu arquivo de configuração. Os dois únicos valores permitidos para auth_type na seção $servers são \'config\' e \'form\'. Você entrou \'%s\', que não é permitido.';//"Error: You have an error in your config file. The only two allowed values for 'auth_type' in the $servers section are 'config' and 'form'. You entered '%s', which is not allowed. ';

$lang['php_install_not_supports_tls'] = 'Sua instalação do PHP não suporta TLS';//"Your PHP install does not support TLS';
$lang['could_not_start_tls'] = 'Impossível iniciar TLS. Por favor verifique a configuração do servidor LDAP.';//"Could not start TLS.<br />Please check your LDAP server configuration.';
$lang['could_not_bind_anon'] = 'Não foi possível conectar ao servidor anonimamente.';//'Could not bind anonymously to server.';
$lang['could_not_bind'] = 'Não foi possível conectar ao servidor LDAP.';//'Could not bind to the LDAP server.';
$lang['anonymous_bind'] = 'Conexão anônima';//'Anonymous Bind';
$lang['bad_user_name_or_password'] = 'Usuário ou senha inválido. Por favor tente novamente.';//'Bad username or password. Please try again.';
$lang['redirecting_click_if_nothing_happens'] = 'Redirecionando... Clique aqui se nada acontecer.';//'Redirecting... Click here if nothing happens.';
$lang['successfully_logged_in_to_server'] = 'Conexão estabelecida com sucesso no sevidor <b>%s</b>';//'Successfully logged into server <b>%s</b>';
$lang['could_not_set_cookie'] = 'Não foi possível criar o cookie.';//'Could not set cookie.';
$lang['ldap_said'] = 'O servidor LDAP respondeu: %s';//"<b>LDAP said</b>: %s<br /><br />';
$lang['ferror_error'] = 'Erro';//"Error';
$lang['fbrowse'] = 'procurar';//"browse';
$lang['delete_photo'] = 'Excluir imagem';//"Delete Photo';
$lang['install_not_support_blowfish'] = 'Sua instalação do PHP não suporta codificação blowfish.';//"Your PHP install does not support blowfish encryption.';
$lang['install_not_support_md5crypt'] = 'Sua instalação do PHP não suporta codificação md5crypt.';//'Your PHP install does not support md5crypt encryption.';
$lang['install_no_mash'] = 'Sua instalação do PHP não tem a função mhash(). Impossível fazer transformações SHA.';// "Your PHP install does not have the mhash() function. Cannot do SHA hashes.';
$lang['jpeg_contains_errors'] = 'Foto jpeg contém erros<br />';//"jpegPhoto contains errors<br />';
$lang['ferror_number'] = 'Erro número: %s (%s)';//"<b>Error number</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] ='Descrição: %s <br /><br />';// "<b>Description</b>: %s <br /><br />';
$lang['ferror_number_short'] = 'Erro número: %s<br /><br />';//"<b>Error number</b>: %s<br /><br />';
$lang['ferror_discription_short'] = 'Descrição: (descrição não disponível<br />';//"<b>Description</b>: (no description available)<br />';
$lang['ferror_submit_bug'] = 'Isto é um bug do phpLDAPadmin? Se for, por favor <a href=\'%s\'>informe</a>.';//"Is this a phpLDAPadmin bug? If so, please <a href=\'%s\'>report it</a>.';
$lang['ferror_unrecognized_num'] = 'Número do erro desconhecido: ';//"Unrecognized error number: ';

$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' /><b>Você encontrou um bug não-fatal no phpLDAPadmin!</b></td></tr><tr><td>Erro:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Arquivo:</td><td><b>%s</b> linha <b>%s</b>, solicitante <b>%s</b></td></tr><tr><td>Versão:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b></td></tr><tr><td>Servidor Web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Por favor informe este bug clicando aqui</a>.</center></td></tr></table></center><br />';//"<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' /><b>You found a non-fatal phpLDAPadmin bug!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>File:</td><td><b>%s</b> line <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b></td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Please report this bug by clicking here</a>.</center></td></tr></table></center><br />';

$lang['ferror_congrats_found_bug'] = 'Parabéns! Você encontrou um bug no phpLDAPadmin.<br /><br /><table class=\'bug\'><tr><td>Erro:</td><td><b>%s</b></td></tr><tr><td>Nível:</td><td><b>%s</b></td></tr><tr><td>Arquivo:</td><td><b>%s</b></td></tr><tr><td>Linha:</td><td><b>%s</b></td></tr><tr><td>Caller:</td><td><b>%s</b></td></tr><tr><td>PLA Vers&atile;o:</td><td><b>%s</b></td></tr><tr><td>PHP Vers&atile;o:</td><td><b>%s</b></td></tr><tr><td>PHP SAPI:</td><td><b>%s</b></td></tr><tr><td>Servidor Web:</td><td><b>%s</b></td></tr></table><br />Por favor informe o bug clicando abaixo!';//"Congratulations! You found a bug in phpLDAPadmin.<br /><br /><table class=\'bug\'><tr><td>Error:</td><td><b>%s</b></td></tr><tr><td>Level:</td><td><b>%s</b></td></tr><tr><td>File:</td><td><b>%s</b></td></tr><tr><td>Line:</td><td><b>%s</b></td></tr><tr><td>Caller:</td><td><b>%s</b></td></tr><tr><td>PLA Version:</td><td><b>%s</b></td></tr><tr><td>PHP Version:</td><td><b>%s</b></td></tr><tr><td>PHP SAPI:</td><td><b>%s</b></td></tr><tr><td>Web server:</td><td><b>%s</b></td></tr></table><br /> Please report this bug by clicking below!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importar arquivo LDIF';//'Import LDIF File';
$lang['select_ldif_file'] = 'Selecionar um arquivo LDIF';//'Select an LDIF file:';
$lang['select_ldif_file_proceed'] = 'Continuar &gt;&gt;';//'Proceed &gt;&gt;';
$lang['dont_stop_on_errors'] = 'Não parar quando der erro';//'Don\'t stop on errors';

//ldif_import
$lang['add_action'] = 'Inserindo...';//'Adding...';
$lang['delete_action'] = 'Deletando...';//'Deleting...';
$lang['rename_action'] = 'Renomeando...';//'Renaming...';
$lang['modify_action'] = 'Alterando...';//'Modifying...';
$lang['warning_no_ldif_version_found'] = 'Nenhuma versão encontrada. Assumindo 1.';//'No version found. Assuming 1.';
$lang['valid_dn_line_required'] = 'Uma linha dn válida é obrigatória.';//'A valid dn line is required.';
$lang['missing_uploaded_file'] = 'Arquivo carregado perdido.';//'Missing uploaded file.';
$lang['no_ldif_file_specified.'] = 'Nenhum arquivo LDIF especificado. Por favor tente novamente.';//'No LDIF file specified. Please try again.';
$lang['ldif_file_empty'] = 'Arquivo LDIF carregado está vazio.';// 'Uploaded LDIF file is empty.';
$lang['empty'] = 'vazio';//'empty';
$lang['file'] = 'Arquivo';//'File';
$lang['number_bytes'] = '%s bytes';//'%s bytes';

$lang['failed'] = 'Falhou';//'failed';
$lang['ldif_parse_error'] = 'Erro Analisando Arquivo LDIF';//'LDIF Parse Error';
$lang['ldif_could_not_add_object'] = 'Não foi possível inserir objeto:';//'Could not add object:';
$lang['ldif_could_not_rename_object'] = 'Não foi possível renomear objeto:';//'Could not rename object:';
$lang['ldif_could_not_delete_object'] = 'Não foi possível excluir objeto:';//'Could not delete object:';
$lang['ldif_could_not_modify_object'] = 'Não foi possível alterar objeto:';//'Could not modify object:';
$lang['ldif_line_number'] = 'Linha Número:';//'Line Number:';
$lang['ldif_line'] = 'Linha:';//'Line:';

//delete_form
$lang['sure_permanent_delete_object']='Você tem certeza que deseja excluir este objeto permanentemente?';//'Are you sure you want to permanently delete this object?';
$lang['permanently_delete_children']='Exluir permanentemente todos os objetos filho também?';//'Permanently delete all children also?';

$lang['list_of_entries_to_be_deleted'] = 'Lista de objetos a serem deletados: ';//'List of entries to be deleted:';
$lang['dn'] = 'DN'; //'DN';

// Exports
$lang['export_format'] = 'Formato para exportar';// 'Export format';
$lang['line_ends'] = 'Fins de linha'; //'Line ends';
$lang['must_choose_export_format'] = 'Você deve especificar um formato para exportar';//'You must choose an export format.';
$lang['invalid_export_format'] = 'Formato para exportação inválido';//'Invalid export format';
$lang['no_exporter_found'] = 'Nenhum exportador de arquivos encontrado.';//'No available exporter found.';
$lang['error_performing_search'] = 'Erro encontrado enquanto fazia a pesquisa.';//'Encountered an error while performing search.';
$lang['showing_results_x_through_y'] = 'Mostrando resultados %s através %s.';//'Showing results %s through %s.';
$lang['searching'] = 'Pesquisando...';//'Searching...';
$lang['size_limit_exceeded'] = 'Aviso, limite da pesquisa excedido.';//'Notice, search size limit exceeded.';
$lang['entry'] = 'Objeto';//'Entry';
$lang['ldif_export_for_dn'] = 'Exportação LDIF para: %s'; //'LDIF Export for: %s';
$lang['generated_on_date'] = 'Gerado pelo phpLDAPadmin no %s';//'Generated by phpLDAPadmin on %s';
$lang['total_entries'] = 'Total de objetos';//'Total Entries';
$lang['dsml_export_for_dn'] = 'Exportação DSLM para: %s';//'DSLM Export for: %s';

// logins
$lang['could_not_find_user'] = 'Não foi possível encontrar o usuário "%s"';//'Could not find a user "%s"';
$lang['password_blank'] = 'Você deixou a senha vazia.';//'You left the password blank.';
$lang['login_cancelled'] = 'Login cancelado.';//'Login cancelled.';
$lang['no_one_logged_in'] = 'Ninguém está conectado neste servidor.';//'No one is logged in to that server.';
$lang['could_not_logout'] = 'Não foi possível desconectar.';//'Could not logout.';
$lang['unknown_auth_type'] = 'auth_type desconhecido: %s';//'Unknown auth_type: %s';
$lang['logged_out_successfully'] = 'Desconectado com sucesso do servidor <b>%s</b>';//'Logged out successfully from server <b>%s</b>';
$lang['authenticate_to_server'] = 'Autenticar no servidor %s';//'Authenticate to server %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Aviso: Esta conexão NÃO é criptografada.';//'Warning: This web connection is unencrypted.';
$lang['not_using_https'] = 'Você não está usando \'https\'. O navegador internet vai transmitir as informações de login sem criptografar.';// 'You are not use \'https\'. Web browser will transmit login information in clear text.';
$lang['login_dn'] = 'Login DN';//'Login DN';
$lang['user_name'] = 'Nome de usuário';//'User name';
$lang['password'] = 'Senha';//'Password';
$lang['authenticate'] = 'Autenticar';//'Authenticate';

// Entry browser
$lang['entry_chooser_title'] = 'Selecionador de objeto';//'Entry Chooser';

// Index page
$lang['need_to_configure'] = 'Você deve configurar o phpLDAPadmin. Faça isso editando o arquivo \'config.php\'. Um arquivo de exemplo é fornecido em \'config.php.example\'';// ';//'You need to configure phpLDAPadmin. Edit the file \'config.php\' to do so. An example config file is provided in \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Exclusões não permitidas no modo somente leitura.';//'Deletes not allowed in read only mode.';
$lang['error_calling_mass_delete'] = 'Erro chamando mass_delete.php. Faltando mass_delete nas variáveis POST.';//'Error calling mass_delete.php. Missing mass_delete in POST vars.';
$lang['mass_delete_not_array'] = 'a variável POST mass_delete não é um conjunto';//'mass_delete POST var is not an array.';
$lang['mass_delete_not_enabled'] = 'Exclusão em massa não habilitada. Por favor habilite-a no arquivo config.php antes de continuar';//'Mass deletion is not enabled. Please enable it in config.php before proceeding.';
$lang['mass_deleting'] = 'Exclusão em massa';//'Mass Deleting';
$lang['mass_delete_progress'] = 'Progresso da exclusão no servidor "%s"';//'Deletion progress on server "%s"';
$lang['malformed_mass_delete_array'] = 'Conjunto mass_delete danificado.';//'Malformed mass_delete array.';
$lang['no_entries_to_delete'] = 'Você não selecionou nenhum objeto para excluir';//'You did not select any entries to delete.';
$lang['deleting_dn'] = 'Excluindo %s';//'Deleting %s';
$lang['total_entries_failed'] = '%s de %s objetos falharam na exclusão.';//'%s of %s entries failed to be deleted.';
$lang['all_entries_successful'] = 'Todos objetos foram excluídos com sucesso.';//'All entries deleted successfully.';
$lang['confirm_mass_delete'] = 'Confirme exclusão em massa de %s objetos no servidor %s';//'Confirm mass delete of %s entries on server %s';
$lang['yes_delete'] = 'Sim, excluir!';//'Yes, delete!';


// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Você não pode renomear um objeto que tem objetos filhos (isto é, a operação de renomear não é permitida em objetos não-folha)';// 'You cannot rename an entry which has children entries (eg, the rename operation is not allowed on non-leaf entries)';
$lang['no_rdn_change'] = 'Você não alterou o RDN';//'You did not change the RDN';
$lang['invalid_rdn'] = 'Valor RDN inválido';//'Invalid RDN value';
$lang['could_not_rename'] = 'Não foi possível renomear o objeto';//'Could not rename the entry';


?>
