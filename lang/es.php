<?php
/*
 * Spanish (es_ES) translation file for phpLDAPadmin
 *
 * Date: 02/05/2005
 * Source: CVS snapshot of en.php as of 02/05/2005
 * Translator: Miguelanxo Otero Salgueiro <miguelanxo@telefonica.net>
 */

// Search form
$lang['simple_search_form_str'] = 'Formulario de búsqueda sencilla';
$lang['advanced_search_form_str'] = 'Formulario de búsqueda avanzada';
$lang['server'] = 'Servidor';
$lang['search_for_entries_whose'] = 'Buscar objetos que';
$lang['base_dn'] = 'DN base';
$lang['search_scope'] = 'Ámbito de búsqueda';
$lang['show_attributes'] = 'Mostrar atributos';
$lang['attributes'] = 'Atributos';
$lang['Search'] = 'Buscar';
$lang['predefined_search_str'] = 'Seleccionar una búsqueda predefinida';
$lang['predefined_searches'] = 'Búsquedas predefinidas';
$lang['no_predefined_queries'] = 'No hay ninguna búsqueda predefinida en config.php.';
$lang['export_results'] = 'exportar resultados';
$lang['unrecoginzed_search_result_format'] = 'Formato de resultado de búsqueda no reconocido: %s';
$lang['format'] = 'Formato';
$lang['list'] = 'lista';
$lang['table'] = 'tabla';
$lang['bad_search_display'] = 'Su config.php especifica un valor no válido para $default_search_display: %s. Por favor, arréglelo';
$lang['page_n'] = 'Página %d';
$lang['no_results'] = 'La búsqueda no ha encontrado resultados.';

// Tree browser
$lang['request_new_feature'] = 'Solicitar una nueva funcionalidad';
$lang['report_bug'] = 'Informar de un error';
$lang['schema'] = 'esquema';
$lang['search'] = 'buscar';
$lang['create'] = 'crear';
$lang['info'] = 'info';
$lang['import'] = 'importar';
$lang['refresh'] = 'refrescar';
$lang['logout'] = 'salir';
$lang['create_new'] = 'Crear nuevo objeto';
$lang['view_schema_for'] = 'Ver el esquema del';
$lang['refresh_expanded_containers'] = 'Refrescar todos los contenedores extendidos del';
$lang['create_new_entry_on'] = 'Crear un nuevo objeto en el';
$lang['new'] = 'nuevo';
$lang['view_server_info'] = 'Ver la información enviada por el servidor';
$lang['import_from_ldif'] = 'Importar desde un fichero LDIF';
$lang['logout_of_this_server'] = 'Desconectar del servidor';
$lang['logged_in_as'] = 'Conectado como: ';
$lang['this_base_dn_is_not_valid'] = 'Esta DN base no es válida.';
$lang['this_base_dn_does_not_exist'] = 'Este objeto no existe.';
$lang['read_only'] = 'sólo lectura';
$lang['read_only_tooltip'] = 'Este atributo ha sido marcado como de sólo lectura por el administrador de phpLDAPadmin';
$lang['could_not_determine_root'] = 'No se ha podido determinar la raíz de su árbol LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Parece que el servidor LDAP ha sido configurado para no revelar su raíz.';
$lang['please_specify_in_config'] = 'Por favór, especifíquela en config.php';
$lang['create_new_entry_in'] = 'Crear nuevo objeto en';
$lang['login_link'] = 'Autentificación';
$lang['login'] = 'conectar';
$lang['base_entry_does_not_exist'] = 'Este objeto base no existe.';
$lang['create_it'] = '¿Crearlo?';

// Entry display
$lang['delete_this_entry'] = 'Borrar este objeto';
$lang['delete_this_entry_tooltip'] = 'Se le pedirá confirmación';
$lang['copy_this_entry'] = 'Copiar este objeto';
$lang['copy_this_entry_tooltip'] = 'Copiar este objeto en otro sitio: un nuevo DN u otro servidor';
$lang['export'] = 'Exportar';
$lang['export_lcase'] = 'exportar';
$lang['export_tooltip'] = 'Exportar este objeto';
$lang['export_subtree_tooltip'] = 'Exportar este objeto y todos sus hijos';
$lang['export_subtree'] = 'Exportar subárbol';
$lang['create_a_child_entry'] = 'Crear un objeto hijo';
$lang['rename_entry'] = 'Renombrar objeto';
$lang['rename'] = 'Renombrar';
$lang['add'] = 'Añadir';
$lang['view'] = 'Ver';
$lang['view_one_child'] = 'Ver 1 hijo';
$lang['view_children'] = 'Ver %s hijos';
$lang['add_new_attribute'] = 'Añadir atributo';
$lang['add_new_objectclass'] = 'Añadir ObjectClass';
$lang['hide_internal_attrs'] = 'Ocultar atributos internos';
$lang['show_internal_attrs'] = 'Mostrar atributos internos';
$lang['attr_name_tooltip'] = 'Haga click para ver el esquema del atributo de tipo \'%s\'';
$lang['none'] = 'ninguno';
$lang['no_internal_attributes'] = 'No hay atributos internos';
$lang['no_attributes'] = 'Este objeto no tiene atributos';
$lang['save_changes'] = 'Guardar cambios';
$lang['add_value'] = 'añadir valor';
$lang['add_value_tooltip'] = 'Añadir un valor al atributo \'%s\'';
$lang['refresh_entry'] = 'Refrescar';
$lang['refresh_this_entry'] = 'Refrescar este objeto';
$lang['delete_hint'] = 'Nota: para borrar un atributo, borre su atributo y haga click en guardar.';
$lang['attr_schema_hint'] = 'Nota: Para ver el esquema de un atributo, haga click en su nombre.';
$lang['attrs_modified'] = 'Se han modificado algunos atributos (%s) que se encuentran destacados mas abajo.';
$lang['attr_modified'] = 'Se ha modificado algún atributo (%s) que se encuentra destacado más abajo.';
$lang['viewing_read_only'] = 'Nota: Modo de sólo lectura.';
$lang['no_new_attrs_available'] = 'no hay nuevos atributos en este objeto';
$lang['no_new_binary_attrs_available'] = 'no hay nuevos atributos binarios en este objeto';
$lang['binary_value'] = 'Valor binario';
$lang['add_new_binary_attr'] = 'Añadir atributo binario';
$lang['alias_for'] = 'Nota: \'%s\' es un alias de \'%s\'';
$lang['required_for'] = 'Atributo requerido para la(s) clase(s) de objeto %s';
$lang['download_value'] = 'descargar valor';
$lang['delete_attribute'] = 'borrar atributo';
$lang['true'] = 'verdadero';
$lang['false'] = 'falso';
$lang['none_remove_value'] = 'ninguno, borrar valor';
$lang['really_delete_attribute'] = '¿Borrar realmente el atributo';
$lang['add_new_value'] = 'Añadir valor';

// Schema browser
$lang['the_following_objectclasses'] = 'El servidor LDAP soporta las siguientes clases de objeto:';
$lang['the_following_attributes'] = 'El servidor LDAP soporta los siguientes tipos de atributo:';
$lang['the_following_matching'] = 'El servidor LDAP soporta las siguientes reglas de coincidencia:';
$lang['the_following_syntaxes'] = 'El servidor LDAP soporta las siguientes sintaxis:';
$lang['schema_retrieve_error_1']='El servidor LDAP no soporta el protocolo LDAP en su totalidad.';
$lang['schema_retrieve_error_2']='Su versión de PHP no realiza la petición correctamente.';
$lang['schema_retrieve_error_3']='O el phpLDAPadmin no sabe como obtener el esquema del servidor.';
$lang['jump_to_objectclass'] = 'Ir a la clase de objeto';
$lang['view_schema_for_oclass'] = 'Ver la descripción del esquema para esta clase de objeto.';
$lang['jump_to_attr'] = 'Ir al tipo de atributo';
$lang['jump_to_matching_rule'] = 'Ir a la regla de coincidencia';
$lang['schema_for_server'] = 'Esquema del servidor';
$lang['required_attrs'] = 'Atributos requeridos';
$lang['required'] = 'requerido';
$lang['optional_attrs'] = 'Atributos opcionales';
$lang['optional_binary_attrs'] = 'Atributos binarios opcionales';
$lang['OID'] = 'OID';
$lang['aliases']='Alias';
$lang['desc'] = 'Descripción';
$lang['no_description']='sin descripción';
$lang['name'] = 'Nombre';
$lang['equality']='Igualdad';
$lang['is_obsolete'] = 'Esta clase de objeto es obsoleta.';
$lang['inherits'] = 'Hereda de';
$lang['inherited_from'] = 'Heredado de';
$lang['parent_to'] = 'Padre de';
$lang['jump_to_this_oclass'] = 'Ir a la definición de esta clase de objeto';
$lang['matching_rule_oid'] = 'OID de la regla de coincidencia';
$lang['syntax_oid'] = 'OID de sintaxis';
$lang['not_applicable'] = 'no aplicable';
$lang['not_specified'] = 'no especificado';
$lang['character'] = 'carácter'; 
$lang['characters'] = 'caracteres';
$lang['used_by_objectclasses'] = 'Usado por la clase de objeto';
$lang['used_by_attributes'] = 'Usado por los atributos';
$lang['maximum_length'] = 'Longitud máxima';
$lang['attribute_types']='Tipos de atributo';
$lang['syntaxes'] = 'Sintaxis';
$lang['matchingrules'] = 'Reglas de coincidencia';
$lang['oid'] = 'OID';
$lang['obsolete'] = 'Obsoleto';
$lang['ordering'] = 'Ordenación';
$lang['substring_rule'] = 'Regla de subcadena';
$lang['single_valued'] = 'Univaludado';
$lang['collective'] = 'Colectivo';
$lang['user_modification'] = 'Modificado por el usuario';
$lang['usage'] = 'Uso';
$lang['could_not_retrieve_schema_from'] = 'No se ha podido obtener el esquema de';
$lang['type'] = 'Tipo';
$lang['no_such_schema_item'] = 'No hay tal ítem en el esquema: "%s"';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Objeto %s borrado correctamente.';
$lang['you_must_specify_a_dn'] = 'Debe especificar un DN';
$lang['could_not_delete_entry'] = 'No se ha podido borrar el objeto %s';
$lang['no_such_entry'] = 'No hay tal objeto %s';
$lang['delete_dn'] = 'Borrar %s';
$lang['permanently_delete_children'] = '¿Borrar también todos los hijos?';
$lang['entry_is_root_sub_tree'] = 'Este objeto es la raíz de un subárbol que contiene %s objetos.';
$lang['view_entries'] = 'ver objetos';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin puede borrar recursivamente este objeto y sus %s hijos. Vea más abajo la lista de objetos que se borrarán ahora. ¿Todavía quiere hacerlo?';
$lang['confirm_recursive_delete_note'] = 'Nota: es potencialmente peligroso y debe hacerlo a su cuenta y riesgo. Esta operación NO puede deshacerse. Tome en consideración alias, referencias y otras cosas que puedan causar problemas.';
$lang['delete_all_x_objects'] = 'Borrar los %s objetos';
$lang['recursive_delete_progress'] = 'Progreso de la borración recursiva';
$lang['entry_and_sub_tree_deleted_successfully'] = 'Objeto %s y su subárbol borrado correctamente.';
$lang['failed_to_delete_entry'] = 'Error al borrar el objeto %s';
$lang['list_of_entries_to_be_deleted'] = 'Lista de objetos a borrar:';
$lang['sure_permanent_delete_object']='¿Está seguro de querer borrar este objeto?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'El atributo "%s" está marcado como de sólo lectura en la configuración de phpLDAPadmin.';
$lang['no_attr_specified'] = 'No se ha especificado ningún nombre de atributo.';
$lang['no_dn_specified'] = 'No se ha especificado ningún DN';

// Adding attributes
$lang['left_attr_blank'] = 'Ha dejado en blanco el valor del atributo. Por favor, vuelva atrás e inténtelo de nuevo.';
$lang['failed_to_add_attr'] = 'Error al añadir atributo.';
$lang['file_empty'] = 'El fichero que ha escogido no existe o está vacío. Por favor vuelva atrás e inténtelo de nuevo.';
$lang['invalid_file'] = 'Error de seguridad: El fichero que está enviando puede ser malicioso.';
$lang['warning_file_uploads_disabled'] = 'Su configuración del PHP ha desactivado la recepción de ficheros. Por favor revise php.ini antes de continuar.';
$lang['uploaded_file_too_big'] = 'El fichero que está enviando es demasiado grande. Por favor revise el ajuste "upload_max_size" en php.ini.';
$lang['uploaded_file_partial'] = 'El fichero que ha seleccionado sólo se ha recibido parcialmente debido a un error de red.';
$lang['max_file_size'] = 'Tamaño máximo de fichero: %s';

// Updating values
$lang['modification_successful'] = '¡Modificación realizada correctamente!';
$lang['change_password_new_login'] = 'Como ha cambiado su contraseña, debe conectarse de nuevo empleando la nueva.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nuevos atributos requeridos';
$lang['requires_to_add'] = 'Esta acción requiere que añada';
$lang['new_attributes'] = 'nuevos atributos';
$lang['new_required_attrs_instructions'] = 'Instrucciones: Para poder añadir esta clase a este objeto, debe especificar';
$lang['that_this_oclass_requires'] = 'que esta clase requiere. Puede hacerlo en este formulario.';
$lang['add_oclass_and_attrs'] = 'Añadir clase de objeto y atributos';
$lang['objectclasses'] = 'Clases de objeto';

// General
$lang['chooser_link_tooltip'] = 'Haga click en el diálogo emergente para seleccionar un DN de forma gráfica';
$lang['no_updates_in_read_only_mode'] = 'No puede realizar cambios cuando el servidor está funcionando en modo de sólo lectura';
$lang['bad_server_id'] = 'Identificador de servidor no válido';
$lang['not_enough_login_info'] = 'No hay información suficiente para conectar con el servidor. Por favor, revise su configuración.';
$lang['could_not_connect'] = 'No se ha podido conectar con el servidor LDAP.';
$lang['could_not_connect_to_host_on_port'] = 'No se ha podido conectar con "%s" en el puerto "%s"';
$lang['could_not_perform_ldap_mod_add'] = 'No se ha podido realizar la operación "ldap_mod_add".';
$lang['bad_server_id_underline'] = 'Identificador de servidor no válido: ';
$lang['success'] = 'Éxito';
$lang['home'] = 'Inicio';
$lang['help'] = 'Ayuda';
$lang['success'] = 'Éxito';
$lang['server_colon_pare'] = 'Servidor: ';
$lang['look_in'] = 'Buscando en: ';
$lang['missing_server_id_in_query_string'] = '¡No se ha especificado ningún servidor en la petición!';
$lang['missing_dn_in_query_string'] = '¡No se ha especificado ningún DN en la petición!';
$lang['back_up_p'] = 'Volver';
$lang['no_entries'] = 'no hay objetos';
$lang['not_logged_in'] = 'No está conectado';
$lang['could_not_det_base_dn'] = 'No se puede determinar el DN base';
$lang['please_report_this_as_a_bug']='Por favor informe de este error.';
$lang['reasons_for_error']='Esto puede suceder por varias razones, las más probables son:';
$lang['yes']='Sí';
$lang['no']='No';
$lang['go']='Ir';
$lang['delete']='Borrar';
$lang['back']='Atrás';
$lang['object']='objeto';
$lang['delete_all']='Borrar todo';
$lang['url_bug_report']='https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546';
$lang['hint'] = 'nota';
$lang['bug'] = 'error';
$lang['warning'] = 'aviso';
$lang['light'] = 'bombilla'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Proceder &gt;&gt;';

// Add value form
$lang['add_new'] = 'Añadir';
$lang['value_to'] = 'valor de';
$lang['distinguished_name'] = 'Nombre distinguido';
$lang['current_list_of'] = 'Lista actual de';
$lang['values_for_attribute'] = 'valores del atributo';
$lang['inappropriate_matching_note'] = 'Nota: Si no ha creado una regla de igualdad en el servidor LDAP para este atributo, obtendrá un error de coincidencia inapropiada ("inappropriate matching").';
$lang['enter_value_to_add'] = 'Introduzca el valor a añadir:';
$lang['new_required_attrs_note'] = 'Nota: puede que tenga que introducir nuevos atributos que esta clase de objeto requiera';
$lang['syntax'] = 'Sintaxis';

//copy.php
$lang['copy_server_read_only'] = 'No puede realizar modificaciones cuando el servidor está en modo de sólo lectura';
$lang['copy_dest_dn_blank'] = 'Ha dejado el campo DN en blanco.';
$lang['copy_dest_already_exists'] = 'El objeto destino (%s) ya existe.';
$lang['copy_dest_container_does_not_exist'] = 'El contenedor destino (%s) no existe.';
$lang['copy_source_dest_dn_same'] = 'El DN origen y destino son iguales.';
$lang['copy_copying'] = 'Copiando ';
$lang['copy_recursive_copy_progress'] = 'Progreso de la copia recursiva';
$lang['copy_building_snapshot'] = 'Creando imagen del árbol a copiar... ';
$lang['copy_successful_like_to'] = '¡Copia correcta! ¿Le gustaría ';
$lang['copy_view_new_entry'] = 'ver el nuevo objeto';
$lang['copy_failed'] = 'Error al copiar el DN: ';

//edit.php
$lang['missing_template_file'] = 'Aviso: falta el fichero de plantilla, ';
$lang['using_default'] = 'usando la plantilla por defecto.';
$lang['template'] = 'Plantilla';
$lang['must_choose_template'] = 'Debes escoger una plantilla';
$lang['invalid_template'] = '%s no es una plantilla válida';
$lang['using_template'] = 'usando plantilla';
$lang['go_to_dn'] = 'Ir a %s';
$lang['structural_object_class_cannot_remove'] = 'Esta es una clase de objeto estructural y no se puede eliminar.';
$lang['structural'] = 'estructural';

//copy_form.php
$lang['copyf_title_copy'] = 'Copiar ';
$lang['copyf_to_new_object'] = 'a un objeto nuevo';
$lang['copyf_dest_dn'] = 'DN destino';
$lang['copyf_dest_dn_tooltip'] = 'El DN completo del objeto a crear';
$lang['copyf_dest_server'] = 'Servidor destino';
$lang['copyf_note'] = 'Nota: Sólo se puede copiar entre servidores diferentes cuando no hay violaciones de esquema';
$lang['copyf_recursive_copy'] = 'También copiar recursivamente todos los hijos de este objeto.';
$lang['recursive_copy'] = 'Copia recursiva';
$lang['filter'] = 'Filtro';
$lang['search_filter'] = 'Filtro de búsqueda';
$lang['filter_tooltip'] = 'Cuando se realice una copia recursiva, copiar sólo los objetos que superen el filtrado';

//create.php
$lang['create_required_attribute'] = 'Ha dejado el valor del atributo requerido (%s) en blanco.';
$lang['redirecting'] = 'Redirigiendo...';
$lang['here'] = 'aquí';
$lang['create_could_not_add'] = 'No se ha podido añadir el objeto al servidor LDAP.';

//create_form.php
$lang['createf_create_object'] = 'Crear objeto';
$lang['createf_choose_temp'] = 'Escoja una plantilla';
$lang['createf_select_temp'] = 'Seleccione una plantilla para el proceso de creación';
$lang['save_as_file'] = 'Guardar como fichero';
$lang['rdn_field_blank'] = 'Ha dejado el campo RDN en blanco.';
$lang['container_does_not_exist'] = 'El contenedor que ha especificado (%s) no existe. Por favor inténtelo de nuevo.';
$lang['no_objectclasses_selected'] = 'No ha seleccionado ninguna clase para este objeto. Por favor, vuelva atrás y hágalo ahora.';
$lang['hint_structural_oclass'] = 'Nota: Debe escoger al menos una clase de objeto estructural';

//creation_template.php
$lang['ctemplate_on_server'] = 'En el servidor';
$lang['ctemplate_no_template'] = 'No se ha especificado ninguna plantilla en las variables POST.';
$lang['template_not_readable'] = 'Su configuración especifica un fichero "%s" para esta plantilla pero dicho fichero no se puede leer debido a que sus permisos son demasiado restrictivos.';
$lang['template_does_not_exist'] = 'Su configuración especifica un fichero "%s" para esta plantilla pero dicho fichero no existe en el directorio templates/creation.';
$lang['create_step1'] = 'Paso 1 de 2: Nombre y clase(s) de objeto';
$lang['create_step2'] = 'Paso 2 de 2: Especifique attributos y valores';
$lang['relative_distinguished_name'] = 'Nombre Distinguido Relativo (RDN)';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(ejemplo: cn=nuevaPersona)';
$lang['container'] = 'Contenedor';

// search.php
$lang['you_have_not_logged_into_server'] = 'Todavía no ha conectado con el servidor, así que no puede realizar búsquedas.';
$lang['click_to_go_to_login_form'] = 'Pulse aquí para ir al formulario de conexión';
$lang['unrecognized_criteria_option'] = 'Criterio no reconocido: ';
$lang['if_you_want_to_add_criteria'] = 'Si quiere puede añadir su propios criterios a la lista. Asegúrese de editar search.php para manejarlos. Saliendo...';
$lang['entries_found'] = 'Objetos encontrados: ';
$lang['filter_performed'] = 'Filtrado realizado: ';
$lang['search_duration'] = 'Búsqueda realizada por phpLDAPadmin en';
$lang['seconds'] = 'segundos';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Ámbito de búsqueda';
$lang['scope_sub'] = 'Sub (subárbol completo)';
$lang['scope_one'] = 'One (un nivel bajo la base)';
$lang['scope_base'] = 'Base (sólo la base)';
$lang['standard_ldap_search_filter'] = 'Filtro de búsqueda LDAP estándar. Ejemplo: (&(sn=Picapiedra)(givenname=Pedro))';
$lang['search_filter'] = 'Filtro de búsqueda';
$lang['list_of_attrs_to_display_in_results'] = 'Lista de atributos para mostrar en los resultados (separados por comas)';
$lang['show_attributes'] = 'Mostrar atributos';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Buscar entradas en las que';
$lang['equals'] = 'sea igual a';
$lang['starts with'] = 'comience por';
$lang['contains'] = 'contenga';
$lang['ends with'] = 'acabe en';
$lang['sounds like'] = 'suene como';

// server_info.php
$lang['could_not_fetch_server_info'] = 'No se ha podido obtener ninguna información del servidor LDAP. Esto puede deberse a este <a href="http://bugs.php.net/bug.php?id=29587">error</a> es su versión de PHP o quizás su servidor LDAP posee controles de acceso que privan a los clientes de acceso al RootDSE.';
$lang['server_info_for'] = 'Información sobre el servidor: ';
$lang['server_reports_following'] = 'El servidor LDAP envía la siguiente información:';
$lang['nothing_to_report'] = 'Este servidor no tiene nada sobre lo que informar.';

//update.php
$lang['update_array_malformed'] = 'El array update_array no está bien formado. Esto puede indicar un error de phpLDAPadmin. Por favor, informe de ello.';
$lang['could_not_perform_ldap_modify'] = 'No se ha podido realizar la operación "ldap_modify".';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = '¿Quiere realizar estos cambios?';
$lang['attribute'] = 'Atributo';
$lang['old_value'] = 'Valor anterior';
$lang['new_value'] = 'Nuevo valor';
$lang['attr_deleted'] = '[atributo borrado]';
$lang['commit'] = 'Cometer';
$lang['cancel'] = 'Cancelar';
$lang['you_made_no_changes'] = 'No ha realizado cambios';
$lang['go_back'] = 'Volver atrás';

// welcome.php
$lang['welcome_note'] = 'Use el menú de la izquierda para navegar';
$lang['credits'] = 'Creditos';
$lang['changelog'] = 'Lista de cambios';
$lang['donate'] = 'Donar';
$lang['pla_logo'] = 'logotipo de phpLDAPadmin';

// Donate.php
$lang['donation_instructions'] = 'Para donar fondos al proyecto phpLDAPadmin, use uno de los botones de abajo.';
$lang['donate_amount'] = 'Donar %s';

$lang['purge_cache'] = 'Borrar cachés';
$lang['no_cache_to_purge'] = 'No hay que borrar ningún caché.';
$lang['done_purging_caches'] = 'Se han borrado %s bytes de caché.';
$lang['purge_cache_tooltip'] = 'Se han borrado todos los datos en el caché de phpLDAPadmin, incluyendo los esquemas del servidor.';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nombre de fichero no seguro: ';
$lang['no_such_file'] = 'No hay tal fichero: ';

//function.php
$lang['auto_update_not_setup'] = 'Ha activado "auto_uid_numbers" para <b>%s</b> en su configuración,
                                  pero no ha especificado el mecanismo "auto_uid_number_mechanism". Por favor, corrija este problema';
$lang['uidpool_not_set'] = 'Ha especificado el mecanismo "auto_uid_number_mechanism" como "uidpool"
                            en su configuración para el servidor <b>%s</b>, pero no ha especificado
                            "audo_uid_number_uid_pool_dn". Por favor, verifiquelo antes de continuar.';
$lang['uidpool_not_exist'] = 'Parece ser que el "uidPool" que ha especificado en su configuración ("%s")
                              no existe.';
$lang['specified_uidpool'] = 'Ha especificado "auto_uid_number_mechanism" como "search" en la
                              configuración del servidor <b>%s</b>, pero no ha especificado
                              "auto_uid_number_search_base". Por favor, especifíquelo antes de continuar.';
$lang['auto_uid_invalid_credential'] = 'Imposible conectar con <b>%s</b> con sus credenciales "auto_uid". Por favor, verifique su fichero de configuración.'; 
$lang['bad_auto_uid_search_base'] = 'Su configuración de phpLDAPadmin especifica "auto_uid_search_base" como no válida para el servidor %s';
$lang['auto_uid_invalid_value'] = 'Ha especificado un valor no válido para el mecanismo "auto_uid_number_mechanism" ("%s")
                                   en su configuración. Sólo son válidos "uidpool" y "search". Por favor, corrija este problema.';
$lang['error_auth_type_config'] = 'Error: Tiene un error en su fichero de configurción. Los tres únicos valores para "auth_type"
                                    en la sección $servers son \'session\', \'cookie\', y \'config\'. Usted ha introducido \'%s\',
                                    que no está permitido. ';
$lang['unique_attrs_invalid_credential'] = 'Imposible conectarse a <b>%s</b> con sus credenciales unique_attr. Por favor, revise su fichero de configuración.';
$lang['unique_attr_failed'] = 'Su intento de añadir <b>%s</b> (<i>%s</i>) a <br><b>%s</b><br> NO se ha permitido. Tal atributo/valor pertenece a otro objeto.<p>Probablemente desee <a href=\'%s\'>buscar</a> tal objeto.';
$lang['php_install_not_supports_tls'] = 'Su instalación de PHP no soporta TLS.';
$lang['could_not_start_tls'] = 'No se ha podido iniciar TLS. Por favor, revise su configuración LDAP.';
$lang['could_not_bind_anon'] = 'No se ha podido conectar con el servidor de forma anónima.';
$lang['could_not_bind'] = 'No se ha podido conectar con el servidor LDAP.';
$lang['anonymous_bind'] = 'Conexión anónima';
$lang['bad_user_name_or_password'] = 'Nombre de usuario o contraseña incorrectos. Por favor, inténtelo de nuevo.';
$lang['redirecting_click_if_nothing_happens'] = 'Redirigiendo... Pulse aquí si no sucede nada.';
$lang['successfully_logged_in_to_server'] = 'Ha conectado con el servidor <b>%s</b>.';
$lang['could_not_set_cookie'] = 'No se ha podido guardar la cookie.';
$lang['ldap_said'] = 'LDAP ha dicho: %s';
$lang['ferror_error'] = 'Error';
$lang['fbrowse'] = 'seleccionar';
$lang['delete_photo'] = 'Borrar foto';
$lang['install_not_support_blowfish'] = 'Su instalación de PHP no soporta encriptación blowfish.';
$lang['install_not_support_md5crypt'] = 'Su instalación de PHP no soporta encriptación md5crypt.';
$lang['install_no_mash'] = 'Su instalación de PHP no posee la función mhash(). No se pueden realizar hashes SHA.';
$lang['jpeg_contains_errors'] = 'La foto jpegPhoto contiene errores<br />';
$lang['ferror_number'] = 'Error número: %s (%s)';
$lang['ferror_discription'] = 'Descripción: %s <br /><br />';
$lang['ferror_number_short'] = 'Error número: %s<br /><br />';
$lang['ferror_discription_short'] = 'Descripción: (no existe descripción disponible)<br />';
$lang['ferror_submit_bug'] = '¿Es éste un error de phpLDAPadmin? Si es así, por favor <a href=\'%s\'>informe sobre ello</a>.';
$lang['ferror_unrecognized_num'] = 'Número de error no reconocido: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>¡Ha encontrado un error no fatal en phpLDAPadmin!</b></td></tr><tr><td>Error:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Fichero:</td>
             <td><b>%s</b> línea <b>%s</b>, caller <b>%s</b></td></tr><tr><td>Versiones:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Servidor web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             Por favor, informe de este error pulsando aquí</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = '¡Felicidades! Ha encontrado un error en phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Error:</td><td><b>%s</b></td></tr>
	     <tr><td>Nivel:</td><td><b>%s</b></td></tr>
	     <tr><td>Fichero:</td><td><b>%s</b></td></tr>
	     <tr><td>Línea:</td><td><b>%s</b></td></tr>
		 <tr><td>Caller:</td><td><b>%s</b></td></tr>
	     <tr><td>Verisón PLA:</td><td><b>%s</b></td></tr>
	     <tr><td>Versión PHP:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Servidor web:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Por favor, informe sobre este error haciendo click más abajo!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importar fichero LDIF';
$lang['select_ldif_file'] = 'Seleccione un fichero LDIF:';
$lang['dont_stop_on_errors'] = 'Ignorar errores';

//ldif_import
$lang['add_action'] = 'Añadiendo...';
$lang['delete_action'] = 'Borrando...';
$lang['rename_action'] = 'Renombrando...';
$lang['modify_action'] = 'Modificando...';
$lang['warning_no_ldif_version_found'] = 'No se ha encontrado versión. Asumiendo 1.';
$lang['valid_dn_line_required'] = 'Se requiere una línea válida.';
$lang['missing_uploaded_file'] = 'Falta el fichero enviado.';
$lang['no_ldif_file_specified.'] = 'No se ha especificado un fichero LDIF. Por favor, inténtelo de nuevo.';
$lang['ldif_file_empty'] = 'El fichero LDIF enviado está vacío.';
$lang['empty'] = 'vacío';
$lang['file'] = 'Fichero';
$lang['number_bytes'] = '%s bytes';

$lang['failed'] = 'Error';
$lang['ldif_parse_error'] = 'Error al parsear LDIF';
$lang['ldif_could_not_add_object'] = 'No se ha podido añadir objeto:';
$lang['ldif_could_not_rename_object'] = 'No se ha podido renombrar el objeto:';
$lang['ldif_could_not_delete_object'] = 'No se ha podido borrar el objeto:';
$lang['ldif_could_not_modify_object'] = 'No se ha podido modificar el objeto:';
$lang['ldif_line_number'] = 'Número de línea:';
$lang['ldif_line'] = 'Línea:';

// Exports
$lang['export_format'] = 'Formato de exportación';
$lang['line_ends'] = 'Fin de línea';
$lang['must_choose_export_format'] = 'Debe escoger un formato de exportación.';
$lang['invalid_export_format'] = 'Formato de exportación no válido';
$lang['no_exporter_found'] = 'No se ha encontrado ningún exportador válido.';
$lang['error_performing_search'] = 'Se ha encontrado un error al realizar la búsqueda.';
$lang['showing_results_x_through_y'] = 'Mostrando los resultados de %s a %s.';
$lang['searching'] = 'Buscando...';
$lang['size_limit_exceeded'] = 'Nota: se ha excedido el tiempo de búsqueda.';
$lang['entry'] = 'Objeto';
$lang['ldif_export_for_dn'] = 'Exportación LDIF de: %s';
$lang['generated_on_date'] = 'Generado por phpLDAPadmin el %s';
$lang['total_entries'] = 'Entradas totales';
$lang['dsml_export_for_dn'] = 'Exportación DSLM de: %s';
$lang['include_system_attrs'] = 'Incluir atributos de sistema';
$lang['csv_spreadsheet'] = 'CSV (Hoja de cálculo)';

// logins
$lang['could_not_find_user'] = 'No se ha podido encontrar el usuario "%s"';
$lang['password_blank'] = 'Ha dejado la contraseña en blanco.';
$lang['login_cancelled'] = 'Conexión cancelada.';
$lang['no_one_logged_in'] = 'No hay nadie conectado con el servidor.';
$lang['could_not_logout'] = 'No se ha podido desconectar.';
$lang['unknown_auth_type'] = 'Tipo de autentificación "auth_type" desconocido: %s';
$lang['logged_out_successfully'] = 'Se ha desconectado del servidor <b>%s</b>';
$lang['authenticate_to_server'] = 'Autentificación del servidor %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Aviso: Esta conexión no está encriptada.';
$lang['not_using_https'] = 'No está usando \'https\'. El navegador web enviará su información sin encriptar.';
$lang['login_dn'] = 'Login';
$lang['user_name'] = 'Nombre de usuario';
$lang['password'] = 'Contraseña';
$lang['authenticate'] = 'Entrar';

// Entry browser
$lang['entry_chooser_title'] = 'Selector de objetos';

// Index page
$lang['need_to_configure'] = 'Debe configurar phpLDAPadmin, editando el fichero \'config.php\'. Se proporciona un fichero de configuración de ejemplo en \'config.php.example\'';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'No se permiten borrados en modo de sólo lectura.';
$lang['error_calling_mass_delete'] = 'Error al llamar a "mass_delete.php". Falta la variable POST "mass_delete".';
$lang['mass_delete_not_array'] = 'La variable POST "mass_delete" no es un array.';
$lang['mass_delete_not_enabled'] = 'No está activado el borrado masivo. Por favor, actívelo en config.php antes de continuar.';
$lang['search_attrs_wrong_count'] = 'Su config.php tiene un error. El número de atributos en $search_attributes y $search_attributes_display es diferente';
$lang['mass_deleting'] = 'Efectuando borrado masivo';
$lang['mass_delete_progress'] = 'Borrado en progreso en el servidor "%s"';
$lang['malformed_mass_delete_array'] = 'Array de borrado masivo mal formado.';
$lang['no_entries_to_delete'] = 'No ha seleccionado objetos para borrar.';
$lang['deleting_dn'] = 'Borrando %s';
$lang['total_entries_failed'] = 'No han podido borrarse %s de %s objetos.';
$lang['all_entries_successful'] = 'Todos los objetos han sido borrados.';
$lang['confirm_mass_delete'] = 'Confirmar borrado masivo de %s objetos en el servidor %s';
$lang['yes_delete'] = '¡Sí, borrar!';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'No puede renombrar un objeto que tenga hijos';
$lang['no_rdn_change'] = 'No ha cambiado el RDN';
$lang['invalid_rdn'] = 'Valor RDN no válido';
$lang['could_not_rename'] = 'No se ha podido renombrar el objeto';

// General errors
$lang['php5_unsupported'] = 'phpLDAPadmin no soporta todavía PHP 5. Si continúa encontrará probablemente extraños errores.';
$lang['mismatched_search_attr_config'] = 'Su configuración tiene un error. $search_attributes debe tener el mismo número de atributos que $search_attributes_display.';

// Password checker
$lang['passwords_match'] = '¡Las contraseñas coinciden!';
$lang['passwords_do_not_match'] = '¡Las contraseñas no coinciden!';
$lang['password_checker_tool'] = 'Herramienta de comprobación de contraseñas';
$lang['compare'] = 'Comparar';
$lang['to'] = 'con';

// Templates
$lang['using'] = 'Usando la';
$lang['template'] = 'plantilla';
$lang['switch_to'] = 'Puede cambair a la ';
$lang['default_template'] = 'plantilla por defecto';

// template_config
$lang['user_account'] = 'Cuenta de Usuario (posixAccount)';
$lang['address_book_inet'] = 'Entrada del libro de direcciones (inetOrgPerson)';
$lang['address_book_moz'] = 'Entrada del libro de direcciones (mozillaOrgPerson)';
$lang['kolab_user'] = 'Entrada de Usuario Kolab';
$lang['organizational_unit'] = 'Unidad Organizativa';
$lang['organizational_role'] = 'Rol Organizativo';
$lang['posix_group'] = 'Grupo Posix';
$lang['samba_machine'] = 'Ordenador con Samba NT';
$lang['samba3_machine'] = 'Ordenador con Samba 3 NT';
$lang['samba_user'] = 'Usuario de Samba';
$lang['samba3_user'] = 'Usuario de Samba 3';
$lang['samba3_group'] = 'Mapeo de Grupo de Samba 3';
$lang['dns_entry'] = 'Entrada DNS';
$lang['simple_sec_object'] = 'Objeto de Seguridad Simple';
$lang['courier_mail_account'] = 'Cuenta de Correo Courier';
$lang['courier_mail_alias'] = 'Alias de Correo Courier';
$lang['ldap_alias'] = 'Alias de LDAP';
$lang['sendmail_cluster'] = 'Cluster de Sendmail';
$lang['sendmail_domain'] = 'Dominio de Sendmail';
$lang['sendmail_alias'] = 'Alias de Sendmail';
$lang['sendmail_virt_dom'] = 'Dominio Virtual de Sendmail';
$lang['sendmail_virt_users'] = 'Usuarios Virtuales de Sendmail';
$lang['sendmail_relays'] = 'Relés de Sendmail';
$lang['custom'] = 'A medida';
$lang['samba_domain_name'] = 'Mi Nombre de Dominio de Samba';
$lang['administrators'] = 'Administradores';
$lang['users'] = 'Usuarios';
$lang['guests'] = 'Invitados';
$lang['power_users'] = 'Usuarios Privilegiados';
$lang['account_ops'] = 'Administradores de Cuentas';
$lang['server_ops'] = 'Administradores de Servidor';
$lang['print_ops'] = 'Administradores de Impresión';
$lang['backup_ops'] = 'Administradores de Copias de Seguridad';
$lang['replicator'] = 'Replicador';
$lang['unable_smb_passwords'] = ' Imposible crear las contraseñas de Samba. Por favor, revise la configuración en template_config.php';
$lang['err_smb_conf'] = 'Error: Tiene un error en su configuración de Samba.';
$lang['err_smb_no_name_sid'] = 'Error: Necesita indicar el nombre y el sid de su dominio Samba.';
$lang['err_smb_no_name'] = 'Error: No se ha indicado el nombre del dominio Samba.';
$lang['err_smb_no_sid'] = 'Error: No se ha indicado el sid del dominio Samba.';

// Samba Account Template
$lang['samba_account'] = 'Cuenta Samba';
$lang['samba_account_lcase'] = 'cuenta samba';

// New User (Posix) Account
$lang['t_new_user_account'] = 'Nueva cuenta de usuario';
$lang['t_hint_customize'] = 'Nota: Para modificar esta plantilla, edite el fichero templates/creation/new_user_template.php';
$lang['t_name'] = 'Nombre';
$lang['t_first_name'] = 'Nombre propio';
$lang['t_last_name'] = 'Apellido';
$lang['t_first'] = 'nombre propio';
$lang['t_last'] = 'apellido';
$lang['t_common_name'] = 'Nombre común';
$lang['t_user_name'] = 'Nombre de Usuario';
$lang['t_password'] = 'Contraseña';
$lang['t_encryption'] = 'Encriptación';
$lang['t_login_shell'] = 'Shell de entrada';
$lang['t_home_dir'] = 'Directorio de usuario';
$lang['t_uid_number'] = 'Número UID';
$lang['t_auto_det'] = '(determinado automáticamente)';
$lang['t_group'] = 'Grupo';
$lang['t_gid_number'] = 'Número GID';
$lang['t_err_passwords'] = 'Sus contraseñas no coinciden. Por favor, vuelva atrás e inténtelo de nuevon.';
$lang['t_err_field_blank'] = 'No puede dejar en blanco el %s. Por favor, vuelva atrás e inténtelo de nuevo.';
$lang['t_err_field_num'] = 'Sólo puede introducir valores numéricos en el campo %s. Por favor, vuelva atrás e inténtelo de nuevo.';
$lang['t_err_bad_container'] = 'El contenedor que ha especificado (%s) no existe. Por favor, vuelva atrás e inténtelo de nuevo.';
$lang['t_confirm_account_creation'] = 'Confirmar creación de cuenta';
$lang['t_secret'] = '[secreta]';
$lang['t_create_account'] = 'Crear cuenta';

// New Address Template
$lang['t_new_address'] = 'Nueva entrada en el libro de direcciones';
$lang['t_organization'] = 'Organización';
$lang['t_address'] = 'Dirección';
$lang['t_city'] = 'Ciudad';
$lang['t_postal_code'] = 'Código postal';
$lang['t_street'] = 'Calle';
$lang['t_work_phone'] = 'Teléfono de trabajo';
$lang['t_fax'] = 'Fax';
$lang['t_mobile'] = 'Móvil';
$lang['t_email'] = 'Email';
$lang['t_container'] = 'Contenedor';
$lang['t_err_cn_blank'] = 'No puede dejar el campo "Nombre Común" en blanco. Por favor, vuelva atrás e inténtelo de nuevo.';
$lang['t_confim_creation'] = 'Confirmar creación de entrada:';
$lang['t_create_address'] = 'Crear dirección';

// default template
$lang['t_check_pass'] = 'Compruebe la contraseña...';

?>

