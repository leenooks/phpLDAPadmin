<?php
// encoding: ISO-8859-1,fr.php,éèîàùçî
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
 */

//  Search form
$lang['simple_search_form_str'] = 'Recherche Simple';
$lang['advanced_search_form_str'] = 'Recherche avancée';
$lang['server'] = 'Serveur';
$lang['search_for_entries_whose'] = 'Chercher les entrées dont';
$lang['base_dn'] = 'Base DN';
$lang['search_scope'] = 'Portée de la recherche';
$lang['search_ filter'] = 'Filtre de la recherche';
$lang['show_attributes'] = 'Montrer les attributs';
$lang['Search'] = 'Chercher';
$lang['equals'] = 'est égal à';
$lang['starts_with'] = 'commence par';
$lang['contains'] = 'contient';
$lang['ends_with'] = 'finit par';
$lang['sounds_like'] = 'ressemble à;';

// tree.php
$lang['request_new_feature'] = 'Demander une nouvelle fonctionnalité';
$lang['see_open_requests'] = 'voir les demandes en cours';
$lang['report_bug'] = 'Signaler un bogue';
$lang['see_open_bugs'] = 'voir les bogues en cours';
$lang['schema'] = 'schema';
$lang['search'] = 'chercher';
$lang['refresh'] = 'rafraîchir';
$lang['create'] = 'créer';
$lang['info'] = 'info';
$lang['import'] = 'importer';
$lang['logout'] = 'se déconnecter';
$lang['create_new'] = 'Créer';
$lang['view_schema_for'] = 'Voir les schemas pour';
$lang['refresh_expanded_containers'] = 'Rafraîchir tous les containeurs étendus';
$lang['create_new_entry_on'] = 'Créer une nouvelle entrée sur';
$lang['view_server_info'] = 'Voir les informations sur le serveur';
$lang['import_from_ldif'] = 'Importer des entrées à partir d\'un fichier LDIF';
$lang['logout_of_this_server'] = 'Se déconnecter de ce serveur';
$lang['logged_in_as'] = 'Connecté en tant que: ';
$lang['read_only'] = 'Lecture seule';
$lang['could_not_determine_root'] = 'La racine de l\'arborescence Ldap n\'a pu être déterminée.';
$lang['ldap_refuses_to_give_root'] = 'Il semble que le serveur LDAP a été configuré de telle sorte que la racine ne soit pas révelée.';
$lang['please_specify_in_config'] = 'Veuillez le spécifier dans le fichier config.php';
$lang['create_new_entry_in'] = 'Créer une nouvelle entrée dans';
$lang['login_link'] = 'Login...';

// entry display
$lang['delete_this_entry'] = 'Supprimer cette entrée';
$lang['delete_this_entry_tooltip'] = 'Il vous sera demandé confirmation';
$lang['copy_this_entry'] = 'Copier cette entrée';
$lang['copy_this_entry_tooltip'] = 'Copier cet objet vers un autre endroit, un nouveau DN ou un autre serveur';
$lang['export_to_ldif'] = 'Exporter au format LDIF';
$lang['export_to_ldif_tooltip'] = 'Sauvegarder cet objet au format LDIF';
$lang['export_subtree_to_ldif_tooltip'] = 'Sauvegarder cet objet ainsi que tous les sous-objets au format LDIF';
$lang['export_subtree_to_ldif'] = 'Exporter l\'arborescence au format LDIF';
$lang['export_to_ldif_mac'] = 'Fins de ligne Macintosh';
$lang['export_to_ldif_win'] = 'Fins de lignes Windows';
$lang['export_to_ldif_unix'] = 'Fins de ligne Unix ';
$lang['create_a_child_entry'] = 'Créer une sous-entrée'; 
$lang['add_a_jpeg_photo'] = 'Ajouter un attribut jpegPhoto';
$lang['rename_entry'] = 'Renommer l\'entrée';
$lang['rename'] = 'Renommer';
$lang['add'] = 'Ajouter';
$lang['view'] = 'Voir';
$lang['add_new_attribute'] = 'Ajouter un nouvel attribut';
$lang['add_new_attribute_tooltip'] = 'Ajouter un nouvel attribut/une nouvelle valeur à cette entrée';
$lang['internal_attributes'] = 'Attributs Internes';
$lang['hide_internal_attrs'] = 'Cacher les attributs internes';
$lang['show_internal_attrs'] = 'Montrer les attributs internes';
$lang['internal_attrs_tooltip'] = 'Attributs établis automatiquement par le système';
$lang['entry_attributes'] = 'Attributs de l\'entrée'; 
$lang['attr_name_tooltip'] = 'Cliquer pour voir la définition de schéma pour l\'attribut de type \'%s\'';
$lang['click_to_display'] = 'Cliquer pour afficher'; 
$lang['hidden'] = 'caché'; 
$lang['none'] = 'aucun'; 
$lang['save_changes'] = 'Sauver les modifications';
$lang['add_value'] = 'ajouter une valeur';
$lang['add_value_tooltip'] = 'Ajouter une valeur supplémentaire à  cet attribut';
$lang['refresh_entry'] = 'Rafraichir';
$lang['refresh'] = 'rafraîchir';
$lang['refresh_this_entry'] = 'Rafraîchir cette entrée';
$lang['delete_hint'] = 'Note: <b>Pour effacer un attribut</b>, laissez le champs vide et cliquez pour sauvegarder.';
$lang['attr_schema_hint'] = 'Note: <b>Pour voir le schéma pour un attribut</b>, cliquer sur le nom de l\'attribut.';
$lang['attrs_modified'] = 'Certains attributs (%s) ont été mdoifiés et sont mis en évidence ci-dessous.';
$lang['attr_modified'] = 'Un attribut (%s) a été modifié et est mis en évidence ci-dessous.';
$lang['viewing_read_only'] = 'Voir une entrée en lecture seule.';
$lang['change_entry_rdn'] = 'Changer le RDN de cette entrée';
$lang['no_new_attrs_available'] = 'plus d\'attributs disponibles pour cette entrée';
$lang['binary_value'] = 'Valeur de type binaire';
$lang['add_new_binary_attr'] = 'Ajouter un nouvel attribut de type binaire';
$lang['add_new_binary_attr_tooltip'] = 'Ajouter un nouvel attribut à partir d\'un fichier';
$lang['alias_for'] = 'Alias pour';
$lang['download_value'] = 'Télécharger le contenu';
$lang['delete_attribute'] = 'Supprimer l\'attribut';
$lang['true'] = 'vrai';
$lang['false'] = 'faux';
$lang['none_remove_value'] = 'aucun, suppression de la valeur';
$lang['really_delete_attribute'] = 'Voulez-vous vraiment supprimer l\'attribut';

// Schema browser
$lang['the_following_objectclasses'] = 'Les <b>classes d\'objets (objectClasses)</b> suivantes sont supportés par ce serveur LDAP.';
$lang['the_following_attributes'] = 'Les <b>types d\'attributs (attributesTypes)</b> suivants sont supportés par ce serveur LDAP.';
$lang['the_following_matching'] = 'Les <b>opérateurs (matching rules)</b> suivants sont supportés par ce serveur LDAP.';
$lang['the_following_syntaxes'] = 'Les <b>syntaxes</b> suivantes sont supportés par ce serveur LDAP.';
$lang['jump_to_objectclass'] = 'Aller à une classe d\'objet';
$lang['jump_to_attr'] = 'Aller à un attribut';
$lang['schema_for_server'] = 'Schema pour le serveur';
$lang['required_attrs'] = 'Attributs obligatoires';
$lang['optional_attrs'] = 'Attributs facultatifs';
$lang['OID'] = 'OID';
$lang['desc'] = 'Description';
$lang['name'] = 'Nom';
$lang['is_obsolete'] = 'Cette classe d\'objet est <b>obsolete</b>';
$lang['inherits'] = 'hérite';
$lang['jump_to_this_oclass'] = 'Aller à la définition de cette classe d\'objet';
$lang['matching_rule_oid'] = 'OID de l\'opérateur';
$lang['syntax_oid'] = 'OID de la syntaxe';
$lang['not_applicable'] = 'not applicable';
$lang['not_specified'] = 'non spécifié';

// Deleting entries
$lang['entry_deleted_successfully'] = 'Suppression de l\'entrée \'%s\' réussie.';
$lang['you_must_specify_a_dn'] = 'Un DN doit être spécifié';
$lang['could_not_delete_entry'] = 'Impossible de supprimer l\'entrée: %s';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nouveaux Attributs Obligatoires';
$lang['requires_to_add'] = 'Cette action nécessite d\'ajouter';
$lang['new_attributes'] = 'nouveaux attributs';
$lang['new_required_attrs_instructions'] = 'Instructions: Afin d\'ajouter cette classe d\'objet, vous devez spécifier';
$lang['that_this_oclass_requires'] = 'dont cette classe d\'objet a besoin. Vous pouvez le faire avec ce formulaire.';
$lang['add_oclass_and_attrs'] = 'Ajout d\' ObjectClass et d\'attributs';

// General
$lang['chooser_link_tooltip'] = 'Cliquer pour choisir un entré(DN)';
$lang['no_updates_in_read_only_mode'] = 'Vous ne pouvez effectuer des mises à jour si le serveur est en lecture seule';
$lang['bad_server_id'] = 'Id de serveur invalide';
$lang['not_enough_login_info'] = 'Informations insuffisantes pour se logguer au serveur. Veuillez, s\'il vous plaî, vérifier votre configuration.';
$lang['could_not_connect'] = 'Impossible de se connecter au serveur LDAP.';
$lang['could_not_perform_ldap_mod_add'] = 'Echec lors de l\'opération ldap_mod_add.';
$lang['bad_server_id_underline'] = 'serveur_id invalide: ';
$lang['success'] = 'Succès';
$lang['server_colon_pare'] = 'Serveur: ';
$lang['look_in'] = 'Recherche dans: ';
$lang['missing_server_id_in_query_string'] = 'Aucun serveur ID spécifié dans la ligne de requête !';
$lang['missing_dn_in_query_string'] = 'Aucun DN spécifié dans la ligne de requête !';
$lang['back_up_p'] = 'Retour...';
$lang['no_entries'] = 'aucune entrée';
$lang['not_logged_in'] = 'Vous n\'êtes pas loggué';
$lang['could_not_det_base_dn'] = 'Impossible de déterminer le DN de base';

// Add value form
$lang['add_new'] = 'Ajout d\'une nouvelle valeur ';
$lang['value_to'] = 'pour';
$lang['distinguished_name'] = 'Distinguished Name';
$lang['current_list_of'] = 'Liste actuelle de';
$lang['values_for_attribute'] = 'valeur(s) pour l\' attribut';
$lang['inappropriate_matching_note'] = 'Note: Vous obtiendrez une erreur de type "inappropriate matching" si vous n\'avez pas<br />' .
			'défini une règle <tt>EQUALITY</tt> pour cet attribut auprès du serveur LDAP.';
$lang['enter_value_to_add'] = 'Entrez la valeur que vous voulez ajouter:';
$lang['new_required_attrs_note'] = 'Note: vous aurez peut-êre besoin d\'introduire de nouveaux attributs requis pour cette classe d\'objet';
$lang['syntax'] = 'Syntaxe';

//Copy.php
$lang['copy_server_read_only'] = 'Des mises à jours ne peuvent pas  être effectuées si le serveur est en lecture seule';
$lang['copy_dest_dn_blank'] = 'Vous avez laissé le DN de destination vide.';
$lang['copy_dest_already_exists'] = 'L\'entrée de destination (%s) existe déjà.';
$lang['copy_dest_container_does_not_exist'] = 'Le conteneur de destination (%s) n\'existe pas.';
$lang['copy_source_dest_dn_same'] = 'Le DN d\'origine et le DN de destination sont identiques.';
$lang['copy_copying'] = 'Copie ';
$lang['copy_recursive_copy_progress'] = 'Progression de la copie récursive';
$lang['copy_building_snapshot'] = 'Construction de l\'image de l\'arborscence à copier... ';
$lang['copy_successful_like_to'] = 'Copie réussite!  Voulez-vous ';
$lang['copy_view_new_entry'] = 'éditer cette nouvelle entrée';
$lang['copy_failed'] = 'Echec lors de la copie de: ';

//edit.php
$lang['missing_template_file'] = 'Avertissement: le fichier modèle est manquant, ';
$lang['using_default'] = 'Utilisation du modèle par défaut.';

//copy_form.php
$lang['copyf_title_copy'] = 'Copie de ';
$lang['copyf_to_new_object'] = 'vers un nouvel objet';
$lang['copyf_dest_dn'] = 'DN de destination';
$lang['copyf_dest_dn_tooltip'] = 'Le DN de la nouvelle entrée à créer lors de la copie de l\'entrée source';
$lang['copyf_dest_server'] = 'Destination Serveur';
$lang['copyf_note'] = 'Note: La copie entre différents serveurs fonctionne seulement si il n\'y a pas de violation de schéma';
$lang['copyf_recursive_copy'] = 'Copier récursivement les sous-entrées de cet object.';

//create.php
$lang['create_required_attribute'] = 'Une valeur n\'a pas été spécifiée pour l\'attribut requis <b>%s</b>.';
$lang['create_redirecting'] = 'Redirection';
$lang['create_here'] = 'ici';
$lang['create_could_not_add'] = 'L\'ajout de l\'objet au serveur LDAP n\'a pu être effectuée.';

//create_form.php
$lang['createf_create_object'] = 'Creation d\'un objet';
$lang['createf_choose_temp'] = 'Choix d\'un modèle';
$lang['createf_select_temp'] = 'Selectionner un modèle pour la procédure de création';
$lang['createf_proceed'] = 'Continuer';

//creation_template.php
$lang['ctemplate_on_server'] = 'Sur le serveur';
$lang['ctemplate_no_template'] = 'Aucun modèle spécifié dans les variables POST.';
$lang['ctemplate_config_handler'] = 'Votre configuration scécifie un gestionnaire de';
$lang['ctemplate_handler_does_not_exist'] = 'pour ce modèle. Cependant, ce gestionnaire n\'existe pas dans le répertoire \'templates/creation\'.';

//search.php
$lang['you_have_not_logged_into_server'] = 'Vous ne vous êtes pas encore loggé auprès du serveur sélectionné. Vous ne pouvez y effectuer des recherches.';
$lang['click_to_go_to_login_form'] = 'Cliquer ici pour vous rendre au formulaire de login';
$lang['unrecognized_criteria_option'] = 'Critère non reconnu: ';
$lang['if_you_want_to_add_criteria'] = 'Si vous voulez ajouter vos propres critère à la liste, soyez cetain d\'éditer search.php afin de pouvoir les gérer.';
$lang['entries_found'] = 'Entrées trouvée: ';
$lang['filter_performed'] = 'Filtre utilisé: ';
$lang['search_duration'] = 'Recherche effectuée par phpLDAPadmin en';
$lang['seconds'] = 'secondes';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'Portée de la recherche';
$lang['scope_sub'] = 'Sub (le sous-arbre)';
$lang['scope_one'] = 'One (un niveau sous la base)';
$lang['scope_base'] = 'Base (le dn de base)';
$lang['standard_ldap_search_filter'] = 'Un filtre standard de recherche LDAP. Exemple: (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Filtre pour la recherche';
$lang['list_of_attrs_to_display_in_results'] = 'Une liste des attributs à afficher dans les résultats(séparés par des virgules)';
$lang['show_attributes'] = 'Attributs à afficher';

// search_form_simple.php
$lang['search_for_entries_whose'] = 'Chercher les entrées dont:';
$lang['equals'] = 'est egal à;';
$lang['starts with'] = 'commence par';
$lang['contains'] = 'contient';
$lang['ends with'] = 'se termine par';
$lang['sounds like'] = 'ressemble à';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Impossible de récupérer les informations concernant le serveur Ldap';
$lang['server_info_for'] = 'Informations pour le serveur: ';
$lang['server_reports_following'] = 'Le serveur a rapporté les informations suivantes';
$lang['nothing_to_report'] = 'Ce serveur n\'a aucunes informations a rapporter.';

//update.php
$lang['update_array_malformed'] = 'update_array n\'est pas bien formé. Ceci est peut-être un bogue de phpLDAPadmin. Pourriez-vous effectuer un rapport de bogue, s\'il vous plaît.';
$lang['could_not_perform_ldap_modify'] = 'L\'opération ldap_modify n\'a pu être effectuée.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Voulez-vous effectuer ces changements?';
$lang['attribute'] = 'Attribut';
$lang['old_value'] = 'Ancienne Valeur';
$lang['new_value'] = 'Nouvelle Valeur';
$lang['attr_deleted'] = '[attribut supprimé]';
$lang['commit'] = 'Valider';
$lang['cancel'] = 'Annuler';
$lang['you_made_no_changes'] = 'Aucun changement n\'a été effectué';
$lang['go_back'] = 'Retour';

// welcome.php
$lang['welcome_note'] = 'Utilisez le menu de gauche pour la navigation';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nom de fichier non sûr: ';
$lang['no_such_file'] = 'Aucun fichier trouvé: ';

//function.php
$lang['auto_update_not_setup'] = 'auto_uid_numbers a été activé pour <b>%s</b> dans votre configuration,
                                  mais vous n\'avez pas spécifié l\' auto_uid_number_mechanism. Veuiller corriger
                                  ce problème.';
$lang['uidpool_not_set'] = 'Vous avez spécifié l<tt>auto_uid_number_mechanism</tt> comme <tt>uidpool</tt>
                            dans la configuration du serveur <b>%s</b>, mais vous n\'avez pas spécifié de valeur pour
                            auto_uid_number_uid_pool_dn. Veuillez le spécifier avant de continuer.';
$lang['uidpool_not_exist'] = 'Le uidPool que vous avez spécifié dans votre configuration (<tt>%s</tt>)
                              n\'existe pas.';
$lang['specified_uidpool'] = 'L\'<tt>auto_uid_number_mechanism</tt> a été défini à <tt>search</tt> dans votre
                              configuration pour le serveur <b>%s</b>, mais vous n\'avez pas défini
                              <tt>auto_uid_number_search_base</tt>. Veuillez le spécifier avant de continuer.';
$lang['auto_uid_invalid_value'] = 'Une valeur non valide a été spécifiée pour auto_uid_number_mechanism (<tt>%s</tt>)
                                   dans votre configuration. Seul <tt>uidpool</tt> et <tt>search</tt> sont valides.
                                   Veuillez corriger ce problème.';
$lang['error_auth_type_config'] = 'Erreur: Vous avez une erreur dans votre fichier de configuration.Les valeurs 
                                   supportées pour \'auth_type\' sont \'config\' et \'form\' dans la section $servers.
                                   Vous avez mis \'%s\', ce qui n\'est pas autorisé.';
$lang['php_install_not_supports_tls'] = 'Votre installation PHP ne supporte pas TLS.';
$lang['could_not_start_tls'] = 'Impossible de démarrer TLS.<br />Veuillez,s\'il vous plaît, vérifier la configuration de votre serveur LDAP.';                                
$lang['auth_type_not_valid'] = 'Vous avez une erreur dans votre fichier de configuration. auth_type  %s n\'est pas valide.';
$lang['ldap_said'] = '<b>LDAP said</b>: %s<br /><br />';
$lang['ferror_error'] = 'Erreur';
$lang['fbrowse'] = 'naviguer';
$lang['delete_photo'] = 'Supprimer la photo';
$lang['install_not_support_blowfish'] = 'Votre installation PHP ne support pas l\'encryption blowfish.';
$lang['install_no_mash'] = 'Votre installation PHP ne supporte pas la fonction mhash(). Impossible de créer un hash SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto contient des erreurs<br />';
$lang['ferror_number'] = '<b>Numéro de l\'erreur</b>: %s <small>(%s)</small><br /><br />';
$lang['ferror_discription'] = '<b>Description</b>: %s <br /><br />';
$lang['ferror_number_short'] = '<b>Numé de l\'erreur</b>: %s<br /><br />';
$lang['ferror_discription_short'] = '<b>Description</b>: (pas de description disponible)<br />';
$lang['ferror_submit_bug'] = 'Est-ce un bogue de phpLDAPadmin? Si c\'est le cas,veuillez s\'il vous plaît <a href=\'%s\'>le rapporter</a>.';
$lang['ferror_unrecognized_num'] = 'Numéro de l\'erreur non reconnu: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Vous avez trouvé un bogue non fatal dans phpLDAPAdmin!</b></td></tr><tr><td>Erreur:</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Fichier:</td>
             <td><b>%s</b> ligne <b>%s</b>, origine de l\'appel <b>%s</b></td></tr><tr><td>Versions:</td><td>PLA: <b>%s</b>, PHP: <b>%s</b>, SAPI: <b>%s</b>
             </td></tr><tr><td>Serveur Web:</td><td><b>%s</b></td></tr><tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>
             S\'il vous plaît, veuillez rapporter ce bogue en cliquant ici</a>.</center></td></tr></table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Félicitations! Vous avez trouvé un bogue dans phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Erreur:</td><td><b>%s</b></td></tr>
	     <tr><td>Niveau:</td><td><b>%s</b></td></tr>
	     <tr><td>Fichier:</td><td><b>%s</b></td></tr>
	     <tr><td>Ligne:</td><td><b>%s</b></td></tr>
        	 <tr><td>Origine de l\'appel:</td><td><b>%s</b></td></tr>
	     <tr><td>PLA Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP Version:</td><td><b>%s</b></td></tr>
	     <tr><td>PHP SAPI:</td><td><b>%s</b></td></tr>
	     <tr><td>Serveur Webr:</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
             S\'il vous plaît, veuillez rapporter ce bogue en cliquant ici!';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Import de fichier LDIF';
$lang['select_ldif_file'] = 'Sélectionner un fichier LDIF:';
$lang['select_ldif_file_proceed'] = 'Continuer &gt;&gt;';

//lldif_import
$lang['add_action'] = 'Ajout de...';
$lang['delete_action'] = 'Supression de...';
$lang['rename_action'] = 'Renommage de...';
$lang['modify_action'] = 'Modification de...';
$lang['failed'] = 'échec';
$lang['ldif_parse_error'] = 'Erreur lors de l\'analyse du fichier LDIF';
$lang['ldif_could_not_add_object'] = 'Impossible d\'ajouter l\'objet:';
$lang['ldif_could_not_rename_object'] = 'Impossible de renommer l\'objet:';
$lang['ldif_could_not_delete_object'] = 'Impossible de supprimer l\'objet:';
$lang['ldif_could_not_modify_object'] = 'Impossible de modifier l\'objet:';
$lang['ldif_line_number'] = 'Numéro de ligne';
$lang['ldif_line'] = 'Ligne';

?>
