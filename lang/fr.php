<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/fr.php,v 1.29.2.2 2005/10/09 06:29:41 wurley Exp $


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
$lang['simple_search_form_str'] = 'Recherche simple';
$lang['advanced_search_form_str'] = 'Recherche avancée';
$lang['server'] = 'Serveur';
$lang['search_for_entries_whose'] = 'Rechercher les entrées dont';
$lang['base_dn'] = 'Base DN';
$lang['search_scope'] = 'Portée de la recherche';
$lang['show_attributes'] = 'Afficher les attributs';
$lang['Search'] = 'Rechercher';
$lang['predefined_search_str'] = 'Sélectionner une recherche prédéfinie';
$lang['predefined_searches'] = 'Recherches prédéfinies';
$lang['no_predefined_queries'] = 'Aucune requête n\'a été définie dans config.php.';
$lang['export_results'] = 'exporter le resultat';
$lang['unrecoginzed_search_result_format'] = 'Le format du résultat de la recherche n\'est pas reconnu : %s';
$lang['format'] = 'Format';
$lang['list'] = 'liste';
$lang['table'] = 'table';
$lang['bad_search_display'] = 'Votre config.php spécifie une valeur non valide pour $default_search_display : %s. Veuillez corriger cela';
$lang['page_n'] = 'Page %d';
$lang['next_page'] = 'Page suivante';
$lang['no_results'] = 'Aucun résultat pour cette recherche.';

// Tree browser
$lang['request_new_feature'] = 'Demander une fonctionnalité';
$lang['report_bug'] = 'Signaler une anomalie';
$lang['schema'] = 'schéma';
$lang['search'] = 'rechercher';
$lang['create'] = 'créer';
$lang['info'] = 'info';
$lang['import'] = 'importer';
$lang['refresh'] = 'rafraîchir';
$lang['logout'] = 'se déconnecter';
$lang['create_new'] = 'Créer une nouvelle entrée ici';
$lang['view_schema_for'] = 'Afficher le schéma pour';
$lang['refresh_expanded_containers'] = 'Rafraîchir tous les conteneurs étendus pour';
$lang['create_new_entry_on'] = 'Créer une nouvelle entrée sur';
$lang['new'] = 'nouveau';
$lang['view_server_info'] = 'Afficher les informations fournies par le serveur';
$lang['import_from_ldif'] = 'Importer les entrées d\'un fichier LDIF';
$lang['logout_of_this_server'] = 'Se déconnecter de ce serveur';
$lang['logged_in_as'] = 'Connecté en tant que : ';
$lang['this_base_dn_is_not_valid'] = 'Ce n\'est pas un DN valide.';
$lang['this_base_dn_does_not_exist'] = 'Cette entrée n\'existe pas.';
$lang['read_only'] = 'lecture seule';
$lang['read_only_tooltip'] = 'Cette attribut a été mis en lecture seule par l\'administrateur de phpLDAPadmin';
$lang['could_not_determine_root'] = 'Impossible de déterminer la racine de votre arborescence LDAP.';
$lang['ldap_refuses_to_give_root'] = 'Il semble que votre serveur LDAP a été configuré pour ne pas révéler sa racine.';
$lang['please_specify_in_config'] = 'Veuillez la spécifier dans config.php';
$lang['create_new_entry_in'] = 'Crée une nouvelle entrée dans';
$lang['login_link'] = 'Connexion...';
$lang['login'] = 'connexion';
$lang['base_entry_does_not_exist'] = 'L\'entrée racine n\'existe pas.';
$lang['create_it'] = 'La créer ?';

// Entry display
$lang['delete_this_entry'] = 'Supprimer cette entrée';
$lang['delete_this_entry_tooltip'] = 'Il vous sera demander de confirmer cette décision';
$lang['copy_this_entry'] = 'Copier ou déplacer cette entrée';
$lang['copy_this_entry_tooltip'] = 'Copier cet objet vers un autre emplacement, un nouveau DN, ou un autre serveur';
$lang['export'] = 'Exporter';
$lang['export_lcase'] = 'exporter';
$lang['export_tooltip'] = 'Enregistrer un dump de cet objet';
$lang['export_subtree_tooltip'] = 'Enregistrer un dump de cet objet et de tous ses sous-objets';
$lang['export_subtree'] = 'Exporter une sous-arborescence';
$lang['create_a_child_entry'] = 'Créer une sous-entrée';
$lang['rename_entry'] = 'Renommer l\'entrée';
$lang['rename'] = 'Renommer';
$lang['rename_lower'] = 'renommer';
$lang['add'] = 'Ajouter';
$lang['view'] = 'Afficher';
$lang['view_one_child'] = 'Afficher 1 sous-entrée';
$lang['view_children'] = 'Afficher %s sous-entrées';
$lang['add_new_attribute'] = 'Ajouter un nouvel attribut';
$lang['add_new_objectclass'] = 'Ajouter un nouvel ObjectClass';
$lang['hide_internal_attrs'] = 'Cacher les attributs internes';
$lang['show_internal_attrs'] = 'Afficher les attributs internes';
$lang['attr_name_tooltip'] = 'Cliquez pour afficher la définition de schéma pour le type d\'attribut « %s »';
$lang['none'] = 'aucun';
$lang['no_internal_attributes'] = 'Aucun attribut interne';
$lang['no_attributes'] = 'Cette entrée n\'a aucun attribut';
$lang['save_changes'] = 'Enregistrer les modifications';
$lang['add_value'] = 'ajouter une valeur';
$lang['add_value_tooltip'] = 'Ajouter une valeur additionnel à l\'attribut « %s »';
$lang['refresh_entry'] = 'Rafraîchir';
$lang['refresh_this_entry'] = 'Rafraîchir cette entrée';
$lang['delete_hint'] = 'Astuce : pour supprimer un attribut, videz le champ texte et enregistrez.';
$lang['attr_schema_hint'] = 'Astuce : pour afficher le schéma d\'un attribut, cliquez sur le nom de l\'attribut.';
$lang['attrs_modified'] = 'Quelques attributs (%s) ont été modifiés et sont surlignés ci-dessous.';
$lang['attr_modified'] = 'Un attribut (%s) a été modifié et est surligné ci-dessous.';
$lang['viewing_read_only'] = 'Afficher les entrées en lecture seule.';
$lang['no_new_attrs_available'] = 'aucun nouvel attribut disponible pour cette entrée';
$lang['no_new_binary_attrs_available'] = 'aucun nouvel attribut binaire pour cette entrée';
$lang['binary_value'] = 'Valeur binaire';
$lang['add_new_binary_attr'] = 'Ajouter un nouvel attribut binaire';
$lang['alias_for'] = 'Note : « %s » est un alias de « %s »';
$lang['required_for'] = 'Attribut requis pour l(es) objectClass %s';
$lang['required_by_entry'] = 'Cet attribut est requis pour le RDN.';
$lang['download_value'] = 'télécharger la valeur';
$lang['delete_attribute'] = 'supprimer l\'attribut';
$lang['true'] = 'vrai';
$lang['false'] = 'faux';
$lang['none_remove_value'] = 'aucun, supprimer la valeur';
$lang['really_delete_attribute'] = 'Voulez-vous vraiment supprimer l\'attribut';
$lang['add_new_value'] = 'Ajouter une nouvelle valeur';

// Schema browser
$lang['schema_retrieve_error_1']='Le serveur ne supporte pas complètement le protocole LDAP.';
$lang['schema_retrieve_error_2']='Votre version de PHP n\'effectue pas correctement la requête.';
$lang['schema_retrieve_error_3']='phpLDAPadmin ne sais pas comment récupérer le schéma de votre serveur.';
$lang['schema_retrieve_error_4']='Ou alors, votre serveur LDAP ne fournit pas cette information.';
$lang['jump_to_objectclass'] = 'Sauter vers un objectClass';
$lang['view_schema_for_oclass'] = 'Afficher la description de schéma de cet objectClass';
$lang['jump_to_attr'] = 'Sauter vers un type d\'attribut';
$lang['jump_to_matching_rule'] = 'Sauter vers une règle correspondante';
$lang['schema_for_server'] = 'Schema pour le serveur';
$lang['required_attrs'] = 'Attributs requis';
$lang['required'] = 'requis';
$lang['optional_attrs'] = 'Attributs optionnels';
$lang['optional_binary_attrs'] = 'Attributs binaires optionnels';
$lang['OID'] = 'OID';
$lang['aliases']='Alias';
$lang['desc'] = 'Description';
$lang['no_description']='aucune description';
$lang['name'] = 'Nom';
$lang['equality']='Égalité';
$lang['is_obsolete'] = 'Cet objectClass est obsolète.';
$lang['inherits'] = 'Hérite de';
$lang['inherited_from'] = 'Hérité de';
$lang['parent_to'] = 'Parent de';
$lang['jump_to_this_oclass'] = 'Sauter vers cette définition d\'objectClass';
$lang['matching_rule_oid'] = 'Règle OID correspondante';
$lang['syntax_oid'] = 'Syntaxe OID';
$lang['not_applicable'] = 'non applicable';
$lang['not_specified'] = 'non spécifié';
$lang['character']='caractère'; 
$lang['characters']='caractères';
$lang['used_by_objectclasses']='Utilisé par ces objectClass';
$lang['used_by_attributes']='Utilisé par ces attributs';
$lang['maximum_length']='Longueur maximum';
$lang['attribute_types']='Types d\'attributs';
$lang['syntaxes']='Syntaxes';
$lang['matchingrules']='Règles correspondantes';
$lang['oid']='OID';
$lang['obsolete']='Obsolète';
$lang['ordering']='Triage';
$lang['substring_rule']='Règle de sous-chaîne';
$lang['single_valued']='Valeur simple';
$lang['collective']='Collectif';
$lang['user_modification']='Modification utilisateur';
$lang['usage']='Usage';
$lang['could_not_retrieve_schema_from']='Impossible de récupérer le schéma de';
$lang['type']='Type';
$lang['no_such_schema_item'] = 'Aucun élément de schéma : « %s »';
$lang['random_password'] = 'Un mot de passe aléatoire a été généré pour vous';

// Deleting entries
$lang['entry_deleted_successfully'] = 'L\'entrée %s a été supprimé avec succès.';
$lang['you_must_specify_a_dn'] = 'Vous devez spécifier un DN';
$lang['could_not_delete_entry'] = 'Impossible de supprimer l\'entrée : %s';
$lang['no_such_entry'] = 'Aucune entrée: %s';
$lang['delete_dn'] = 'Supprimer %s';
$lang['permanently_delete_children'] = 'Supprimer aussi toutes les sous-entrées ?';
$lang['entry_is_root_sub_tree'] = 'Cette entrée est la racine d\'une sous-arborescence contenant %s entrées.';
$lang['view_entries'] = 'afficher les entrées';
$lang['confirm_recursive_delete'] = 'phpLDAPadmin peut supprimer récursivement cette entrée et toutes %s de ses sous-entrées. Voir ci-dessous pour une liste de toutes les entrées que cette action supprimera. Voulez-vous le faire ?';
$lang['confirm_recursive_delete_note'] = 'Note : ceci est potentiellement très dangereux et vous le faites à vos risques et périls. Cette opération ne peut être annulée. Prenez bien note que les alias, les référents, et d\'autres choses pourront vous causer des problèmes.';
$lang['delete_all_x_objects'] = 'Supprimer tous les %s objets';
$lang['recursive_delete_progress'] = 'Progression de la suppression récursive';
$lang['entry_and_sub_tree_deleted_successfully'] = 'L\'entrée %s et la sous-arborescence ont été supprimé avec succès.';
$lang['failed_to_delete_entry'] = 'La suppression de l\'entrée %s a échoué';
$lang['list_of_entries_to_be_deleted'] = 'Liste des entrées à supprimer :';
$lang['sure_permanent_delete_object']='Êtes-vous sûr de vouloir supprimer définitivement cet objet ?';
$lang['dn'] = 'DN';

// Deleting attributes
$lang['attr_is_read_only'] = 'L\'attribut « %s » est marqué en lecture seule dans la configuration de phpLDAPadmin.';
$lang['no_attr_specified'] = 'Aucun nom d\'attribut défini.';
$lang['no_dn_specified'] = 'Aucun DN spécifié';

// Adding attributes
$lang['left_attr_blank'] = 'Vous avez laissé la valeur de l\'attribut blanc. Veuillez revenir et essayer de nouveau.';
$lang['failed_to_add_attr'] = 'L\'ajout de l\'attribut a échoué.';
$lang['file_empty'] = 'Le fichier que vous avez choisi est soit vide, soit il n\'existe pas. Veuillez revenir et essayer de nouveau.';
$lang['invalid_file'] = 'Erreur de sécurité : le fichier téléchargé est peut être corrompu.';
$lang['warning_file_uploads_disabled'] = 'Votre configuration de PHP a désactivé le téléchargement de fichiers. Veuillez vérifier php.ini avant de procéder.';
$lang['uploaded_file_too_big'] = 'Le fichier que vous avez téléchargé est trop grand. Veuillez vérifier dans php.ini le réglage upload_max_size';
$lang['uploaded_file_partial'] = 'Le fichier que vous avez sélectionné est partiellement téléchargé, surement du à une erreur réseau.';
$lang['max_file_size'] = 'Taille de fichier maximum : %s';

// Updating values
$lang['modification_successful'] = 'Modification avec succès !';
$lang['change_password_new_login'] = 'Puisque vous avez changé de mot de passe, vous devez vous reconnecter avec votre nouveau mot de passe.';

// Adding objectClass form
$lang['new_required_attrs'] = 'Nouveaux attributs requis';
$lang['requires_to_add'] = 'Cette action nécessite que vous ajoutiez';
$lang['new_attributes'] = 'nouveaux attributs';
$lang['new_required_attrs_instructions'] = 'Instructions : afin d\'ajouter ces objectClass à cette entrée, vous devez spécifier';
$lang['that_this_oclass_requires'] = 'ce que cet objectClass nécessite. Vous pouvez le faire dans ce formulaire.';
$lang['add_oclass_and_attrs'] = 'Ajouter des objectClass et des attributs';
$lang['objectclasses'] = 'ObjectClass';

// General
$lang['chooser_link_tooltip'] = 'Cliquez afin de faire apparaitre une boîte de dialogue permettant de choisir une entrée graphiquement';
$lang['no_updates_in_read_only_mode'] = 'Vous ne pouvez effectuer de mises à jour tant que le serveur est en lecture seule';
$lang['bad_server_id'] = 'Mauvais id de serveur';
$lang['not_enough_login_info'] = 'Il n\'y a pas assez d\'informations pour se connecter au serveur. Veuillez vérifier votre configuration.';
$lang['could_not_connect'] = 'Impossible de se connecter au serveur LDAP.';
$lang['could_not_connect_to_host_on_port'] = 'Impossible de se connecter à « %s » sur le port « %s »';
$lang['could_not_perform_ldap_mod_add'] = 'Impossible d\'effectuer ldap_mod_add operation.';
$lang['home'] = 'Accueil';
$lang['help'] = 'Aide';
$lang['success'] = 'Succès';
$lang['server_colon_pare'] = 'Serveur : ';
$lang['look_in'] = 'Rechercher dans : ';
$lang['missing_dn_in_query_string'] = 'Aucun DN spécifié dans la chaine de requete !';
$lang['back_up_p'] = 'Sauvegarde...';
$lang['no_entries'] = 'aucune entrées';
$lang['could_not_det_base_dn'] = 'Impossible de déterminer le DN racine';
$lang['reasons_for_error']='Ceci peut arriver pour différentes raisons, le plus probable est sans doute :';
$lang['yes']='oui';
$lang['no']='Non';
$lang['go']='Aller à';
$lang['delete']='Supprimer';
$lang['back']='Revenir';
$lang['object']='objet';
$lang['delete_all']='Tout supprimer';
$lang['hint'] = 'astuce';
$lang['bug'] = 'anomalie';
$lang['warning'] = 'avertissement';
$lang['light'] = 'lumière'; // the word 'light' from 'light bulb'
$lang['proceed_gt'] = 'Procéder &gt;&gt;';
$lang['no_blowfish_secret'] = 'phpLDAPadmin ne peut pas chiffrer ou déchiffrer en toute sécurité vos informations sensibles, car $blowfish_secret n\'est pas défini dans config.php. Vous avez besoin d\'éditer config.php et de définir $blowfish_secret à une chaîne secrète maintenant.';
$lang['jpeg_dir_not_writable'] = 'Veuillez définir $jpeg_temp_dir à un répertoire accessible en écriture dans le config.php de phpLDAPadmin';
$lang['jpeg_dir_not_writable_error'] = 'Impossible d\'écrire dans le répertoire $jpeg_temp_dir %s. Veuillez vérifier que votre serveur Web peut écrire des fichiers dedans.';
$lang['jpeg_unable_toget'] = 'Impossible de récupérer les données JPEG depuis votre serveur LDAP pour l\'attribut %s.';
$lang['jpeg_delete'] = 'Supprimer la photo';

// Add value form
$lang['add_new'] = 'Ajouter une nouvelle valeur';
$lang['value_to'] = 'dans';
$lang['distinguished_name'] = 'Nom distingué';
$lang['current_list_of'] = 'Liste actuelle de';
$lang['values_for_attribute'] = 'valeur(s) pour l\'attribut';
$lang['inappropriate_matching_note'] = 'Note : vous obtiendrez une erreur « correspondance innapropriée » si vous n\'avez pas défini de règle d\'égalité sur votre serveur LDAP pour cette attribut.';
$lang['enter_value_to_add'] = 'Saisissez la valeur que vous voulez ajouter :';
$lang['new_required_attrs_note'] = 'Note : il peut vous être demandé de saisir les nouveaux attributs requis par ces objectClass';
$lang['syntax'] = 'Syntaxe';

//copy.php
$lang['copy_server_read_only'] = 'Vous ne pouvez faire de mises à jour tantq ue le serveur est en lecture seule';
$lang['copy_dest_dn_blank'] = 'Vopus avez laissé le DN de destination vide.';
$lang['copy_dest_already_exists'] = 'L\'entrée de destination (%s)existe déjà.';
$lang['copy_dest_container_does_not_exist'] = 'Le conteneur de destination (%s) n\'existe pas.';
$lang['copy_source_dest_dn_same'] = 'Les DN source et destination sont les mêmes.';
$lang['copy_copying'] = 'Copie ';
$lang['copy_recursive_copy_progress'] = 'progression de la copie récursive';
$lang['copy_building_snapshot'] = 'Construction d\'un instantané de l\'arborescence à copier... ';
$lang['copy_successful_like_to'] = 'Copie avec succès ! Voulez-vous ';
$lang['copy_view_new_entry'] = 'afficher la nouvelle entrée';
$lang['copy_failed'] = 'La copie du DN a échoué : ';

//edit.php
$lang['missing_template_file'] = 'Avertissement : fichier modèle manquant, ';
$lang['using_default'] = 'Utilisant les valeurs par défaut.';
$lang['template'] = 'Modèle';
$lang['must_choose_template'] = 'Vous devez choisir un modèle';
$lang['invalid_template'] = '%s n\'est pas un modèle valide';
$lang['using_template'] = 'en utilisant le modèle';
$lang['go_to_dn'] = 'Aller vers %s';
$lang['structural_object_class_cannot_remove'] = 'C\'est un ObjectClass structurel et il ne peut être supprimé.';
$lang['structural'] = 'structurel';

//copy_form.php
$lang['copyf_title_copy'] = 'Copie ';
$lang['copyf_to_new_object'] = 'vers un nouvel objet';
$lang['copyf_dest_dn'] = 'DN de destination';
$lang['copyf_dest_dn_tooltip'] = 'Le DN complet de la nouvelle entrée a créer lors de la copie de l\'entrée source';
$lang['copyf_dest_server'] = 'Serveur de destination';
$lang['copyf_note'] = 'Astuce : la copie entre différents serveurs ne fonctionne que si il n\'y a aucune violations de schéma';
$lang['copyf_recursive_copy'] = 'Copie récursive de toutes les sous-entrées de cet objet.';
$lang['recursive_copy'] = 'Copie récursive';
$lang['filter'] = 'Filtre';
$lang['filter_tooltip'] = 'Lors de la copie récursive, ne copier que les entrées qui correspondent à ce filtre';
$lang['delete_after_copy'] = 'Supprimer après la copie (déplacement) :';
$lang['delete_after_copy_warn'] = 'Veuillez vous assurer que vos filtres (ci-dessus) sélectionneront tous les enregistrements fils.';

//create.php
$lang['create_required_attribute'] = 'Vous avez laissez une valeur blanche pour l\'attribut requis (%s).';
$lang['redirecting'] = 'Redirection...';
$lang['here'] = 'ici';
$lang['create_could_not_add'] = 'Impossible d\'ajouter l\'objet au serveur LDAP.';

//create_form.php
$lang['createf_create_object'] = 'Créer un objet';
$lang['createf_choose_temp'] = 'Choisissez un modèle';
$lang['createf_select_temp'] = 'Sélectionner un modèle pour le processus de création';
$lang['save_as_file'] = 'Enregistrer dans un fichier';
$lang['rdn_field_blank'] = 'Vous avez laissé le champ RDN vide.';
$lang['container_does_not_exist'] = 'Le conteneur que vous avez spécifié (%s) n\'existe pas. Veuillez essayer de nouveau.';
$lang['no_objectclasses_selected'] = 'Vous n\'avez pas sélectionné d\'objectClass pour cet objet. Veuillez revenir et le faire.';
$lang['hint_structural_oclass'] = 'Astuce : vous devez choisir un seul objectClass structurel (affiché en gras ci-dessus)';
$lang['template_restricted'] = 'Ce modèle n\'est pas autorisé dans ce conteneur.';
$lang['template_invalid'] = 'Ce modèle a été désactivé, cela est sûrement du à un schéma manquant ou à des champs manquants dans le modèle XML.';

//creation_template.php
$lang['ctemplate_on_server'] = 'Sur le serveur';
$lang['ctemplate_no_template'] = 'Aucun modèle spécifié dans les variables POST.';
$lang['template_not_readable'] = 'Votre configuration spécifie un gestionnaire de « %s » pour ce modèle mais le fichier n\'est pas lisible car les permissions sont trop strictes..';
$lang['template_does_not_exist'] = 'Votre configuration spécifie un gestionnaire de « %s » pour ce modèle mais ce gestionnaire n\'existe pas dans le répertoire de modèles/création.';
$lang['create_step1'] = 'Étape 1 of 2 : nom et ObjectClass';
$lang['create_step2'] = 'Étape 2 of 2 : spécifie les attributs et valeur';
$lang['relative_distinguished_name'] = 'Nom distingué relatif';
$lang['rdn'] = 'RDN';
$lang['rdn_example'] = '(exemple : cn=MaNouvellePersonne)';
$lang['container'] = 'Conteneur';

// search.php
$lang['you_have_not_logged_into_server'] = 'Vous n\'êtes pas encore connecté au serveur sélectionné, vous ne pouvez pas effectuer de recherche dessus.';
$lang['click_to_go_to_login_form'] = 'Cliquez ici pour aller au formulaire de connexion';
$lang['unrecognized_criteria_option'] = 'Option de critère non reconnue : ';
$lang['if_you_want_to_add_criteria'] = 'Si vous voulez ajouter vos propres critères dans la liste. Veuillez vous assurer d\'éditer search.php pour les gérer. Quitte.';
$lang['entries_found'] = 'Entrées trouvées : ';
$lang['filter_performed'] = 'Filtrage effectué : ';
$lang['search_duration'] = 'Recherche effectuée par phpLDAPadmin dans';
$lang['seconds'] = 'secondes';

// search_form_advanced.php
$lang['scope_in_which_to_search'] = 'La portée dans laquelle effectuer la recherche';
$lang['scope_sub'] = 'Sub (sous-arborescence entière)';
$lang['scope_one'] = 'One (un niveau au-dessous de la base)';
$lang['scope_base'] = 'Base (dn de base seulement)';
$lang['standard_ldap_search_filter'] = 'Filtre de recherche LDAAP standard. Exemple : (&(sn=Smith)(givenname=David))';
$lang['search_filter'] = 'Filtre de recherche';
$lang['list_of_attrs_to_display_in_results'] = 'Une liste d\'attributs à afficher dans le résultat (séparé par des virgules)';

// search_form_simple.php
$lang['equals'] = 'est égal à';
$lang['starts with'] = 'commence par';
$lang['contains'] = 'contient';
$lang['ends with'] = 'se termine par';
$lang['sounds like'] = 'ressemble à';

// server_info.php
$lang['could_not_fetch_server_info'] = 'Impossible de récupérer les informations LDAP depuis le serveur. Ceci est sans doute du à une <a href="http://bugs.php.net/bug.php?id=29587">anomalie</a> dans votre version de PHP ou peut-être que votre serveur LDAP a un contrôle d\'accès défini qui empèche les clients LDAP d\'accéder au RootDSE.';
$lang['server_info_for'] = 'Info serveur pour : ';
$lang['server_reports_following'] = 'Le serveur rapporte les informations suivantes à propos de lui-même';
$lang['nothing_to_report'] = 'Ce serveur n\'a rien à rapporter.';

//update.php
$lang['update_array_malformed'] = 'update_array est malformé. C\'est peut-être une anomalie de phpLDAPadmin. Veuillez la rapporter.';
$lang['could_not_perform_ldap_modify'] = 'Impossible d\'effectuer une opération ldap_modify.';

// update_confirm.php
$lang['do_you_want_to_make_these_changes'] = 'Voulez-vous effectuer ces modifications ?';
$lang['attribute'] = 'Attribut';
$lang['old_value'] = 'Ancienne valeur';
$lang['new_value'] = 'Nouvelle valeur';
$lang['attr_deleted'] = '[attribut supprimé]';
$lang['commit'] = 'Valider';
$lang['cancel'] = 'Annuler';
$lang['you_made_no_changes'] = 'Vous n\'avez fait aucune modification';
$lang['go_back'] = 'Revenir';
$lang['unable_create_samba_pass'] = 'Impossible de créer le mot de passe Samba. Veuillez vérifier votre configuration dans template_config.php';

// welcome.php
$lang['welcome_note'] = 'Utiliser le menu de gauche pour naviguer';
$lang['credits'] = 'Crédits';
$lang['changelog'] = 'ChangeLog';
$lang['documentation'] = 'Documentation';
$lang['donate'] = 'Donation';
$lang['pla_logo'] = 'Logo phpLDAPadmim';

// Donate.php
$lang['donation_instructions'] = 'Pour contribuer financièrement au projet phpLDAPadmin, utilisez un des boutons PayPal ci-dessous.';
$lang['donate_amount'] = 'Donner %s';

$lang['purge_cache'] = 'Purger les caches';
$lang['no_cache_to_purge'] = 'Aucun cache à purger.';
$lang['done_purging_caches'] = '%s octets de cache purgés.';
$lang['purge_cache_tooltip'] = 'Purge toutes les données cachées dans phpLDAPadmin, incluant les schémas de serveur.';

// view_jpeg_photo.php
$lang['unsafe_file_name'] = 'Nom de fichier non sûr : ';
$lang['no_such_file'] = 'Pas de tel fichier : ';

//function.php
$lang['auto_update_not_setup'] = 'Vous avez activé auto_uid_numbers pour <b>%s</b> dans votre configuration,
                                  mais vous n\'avez pas spécifié de mécanisme auto_uid_number_mechanism. Veuillez corriger
                                  ce problème.';
$lang['uidpool_not_set'] = 'Vous avez défini « auto_uid_number_mechanism » comme « uidpool »
                            dans votre configuration pour le serveur <b>%s</b>, mais vous n\'avez pas spécifié le
                            auto_uid_number_uid_pool_dn. Veuillez le spécifier avant de procéder.';
$lang['uidpool_not_exist'] = 'Il semble que le uidPool que vous avez spécifié dans votre configuration (« %s »)
                              n\'existe pas.';
$lang['specified_uidpool'] = 'Vous avez spécifié le « auto_uid_number_mechanism » comme « search » dans votre
                              configuration du serveur <b>%s</b>, mais vous n\'avez pas spécifié le
                              « auto_uid_number_search_base ». Veuillez le spécifier avant de procéder.';
$lang['auto_uid_invalid_credential'] = 'Incapable de se connecter à <b>%s</b> avec votre crédentiel auto_uid . Veuillez vérifier votre fichier de configuration.'; 
$lang['bad_auto_uid_search_base'] = 'Votre configuration de phpLDAPadmin spécifie un auto_uid_search_base non valide pour le serveur %s';
$lang['auto_uid_invalid_value'] = 'Vous avez spécifié une valeur non valide pour pour auto_uid_number_mechanism ("%s")
                                   dans votre configuration. Seul « uidpool » et « search » sont valides.
                                   Veuillez corriger ce problème.';
$lang['error_auth_type_config'] = 'Erreur : Vous avez une erreur dans votre fichier de configuration. Les seuls trois valeurs autorisées
                                   pour auth_type dans la section $servers sont « session », « cookie », et « config ». Vous avez saisi « %s »,
                                   qui n\'est pas autorisée. ';
$lang['unique_attrs_invalid_credential'] = 'Impossible de se connecter à <b>%s</b> avec votre crédentiel unique_attrs. Veuillez vérifier votre fichier de configuration.'; 
$lang['unique_attr_failed'] = 'Votre tentative d\'ajouter <b>%s</b> (<i>%s</i>) à <br><b>%s</b><br> n\'est pas autorisée. Cet attribut/valeur appartient à une autre entrée.<p>Vous souhaitez <a href=\'%s\'>rechercher</a> cette entrée.';
$lang['php_install_not_supports_tls'] = 'Votre installation de PHP ne supporte pas TLS.';
$lang['could_not_start_tls'] = 'Impossible de démarrer TLS. Veuillez vérifier la configuration de votre serveur LDAP.';
$lang['could_not_bind_anon'] = 'Impossible de se connecter anonymement au serveur.';
$lang['could_not_bind'] = 'Impossible de se connecter au serveur LDAP.';
$lang['anonymous_bind'] = 'Connexion anonyme';
$lang['bad_user_name_or_password'] = 'Mauvais nom d\'utilisateur ou mot de passe. Veuillez réessayer.';
$lang['successfully_logged_in_to_server'] = 'Connexion au serveur <b>%s</b> avec succès';
$lang['could_not_set_cookie'] = 'Impossible de définir le cookie.';
$lang['ldap_said'] = 'LDAP dit : %s';
$lang['ferror_error'] = 'Erreur';
$lang['fbrowse'] = 'parcourir';
$lang['delete_photo'] = 'Supprimer la photo';
$lang['install_not_support_ext_des'] = 'Votre bibliothèque système crypt ne supporte pas le chiffrement DES étendu.';
$lang['install_not_support_blowfish'] = 'Votre bibliothèque système crypt ne supporte pas le chiffrement blowfish.';
$lang['install_not_support_md5crypt'] = 'Votre bibliothèque système crypt ne supporte pas le chiffrement md5crypt.';
$lang['install_no_mash'] = 'Votre installation PHP n\'a pas de fonction mhash(). Impossible de faire de signature SHA.';
$lang['jpeg_contains_errors'] = 'jpegPhoto contient des erreurs<br />';
$lang['ferror_number'] = 'Erreur numéro : %s (%s)';
$lang['ferror_discription'] = 'Description : %s <br /><br />';
$lang['ferror_number_short'] = 'Erreur numéro : %s<br /><br />';
$lang['ferror_discription_short'] = 'Description : (aucune description disponible)<br />';
$lang['ferror_submit_bug'] = 'Est-ce une anomalie phpLDAPadmin ? Si c\'est le cas, veuillez <a href=\'%s\'>la rapporter</a>.';
$lang['ferror_unrecognized_num'] = 'Numéro d\'erreur non reconnu: ';
$lang['ferror_nonfatil_bug'] = '<center><table class=\'notice\'><tr><td colspan=\'2\'><center><img src=\'images/warning.png\' height=\'12\' width=\'13\' />
             <b>Vous avez trouvé une anomalie phpLDAPadmin non fatale !</b></td></tr><tr><td>Erreur :</td><td><b>%s</b> (<b>%s</b>)</td></tr><tr><td>Fichier :</td>
             <td><b>%s</b> ligne <b>%s</b>, appelant <b>%s</b></td></tr><tr><td>Versions :</td><td>PLA : <b>%s</b>, PHP : <b>%s</b>, SAPI : <b>%s</b>
             </td></tr><tr><td>Serveur Web :</td><td><b>%s</b></td></tr>
	<tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Veuillez vérifier et voir si cette anomalie a déjà été rapportée ici</a>.</center></td></tr>
	<tr><td colspan=\'2\'><center><a target=\'new\' href=\'%s\'>Si elle n\'a pas été rapportée, vous pouver rapporter cette anomalie en cliquant ici</a>.</center></td></tr>
	</table></center><br />';
$lang['ferror_congrats_found_bug'] = 'Félicitations ! Vous avez trouvé une anomalie dans phpLDAPadmin.<br /><br />
	     <table class=\'bug\'>
	     <tr><td>Erreur :</td><td><b>%s</b></td></tr>
	     <tr><td>Niveau :</td><td><b>%s</b></td></tr>
	     <tr><td>Fichier :</td><td><b>%s</b></td></tr>
	     <tr><td>Ligne :</td><td><b>%s</b></td></tr>
		 <tr><td>Appelant :</td><td><b>%s</b></td></tr>
	     <tr><td>Version PLA :</td><td><b>%s</b></td></tr>
	     <tr><td>Version PHP :</td><td><b>%s</b></td></tr>
	     <tr><td>SAPI PHP :</td><td><b>%s</b></td></tr>
	     <tr><td>Serveur Web :</td><td><b>%s</b></td></tr>
	     </table>
	     <br />
	     Veuillez rapporter cette anomalie en cliquant ci-dessous !';

//ldif_import_form
$lang['import_ldif_file_title'] = 'Importer un fichier LDIF';
$lang['select_ldif_file'] = 'Sélectionner un fichier LDIF :';
$lang['dont_stop_on_errors'] = 'Ne pas vous arrêter sur les erreurs';

//ldif_import
$lang['add_action'] = 'Ajout...';
$lang['delete_action'] = 'Suppression...';
$lang['rename_action'] = 'Renommage...';
$lang['modify_action'] = 'Modification...';
$lang['warning_no_ldif_version_found'] = 'Aucune version trouvé. Assume 1.';
$lang['valid_dn_line_required'] = 'Une ligne dn valide est requise.';
$lang['missing_uploaded_file'] = 'Fichier téléchargé manquant.';
$lang['no_ldif_file_specified'] = 'Aucun fichier LDIF spécifié. Veuillez essayer de nouveau.';
$lang['ldif_file_empty'] = 'Le fichier LDIF téléchargé est manquant.';
$lang['empty'] = 'vide';
$lang['file'] = 'Fichier';
$lang['number_bytes'] = '%s octets';

$lang['failed'] = 'Échoue';
$lang['ldif_parse_error'] = 'Erreur d\'analyse LDIF';
$lang['ldif_could_not_add_object'] = 'Impossible d\'ajouter un objet :';
$lang['ldif_could_not_rename_object'] = 'Impossible de renommer l\'objet :';
$lang['ldif_could_not_delete_object'] = 'Impossible de supprimer l\'objet :';
$lang['ldif_could_not_modify_object'] = 'Impossible de modifier l\'objet :';
$lang['ldif_line_number'] = 'Numéro de ligne :';
$lang['ldif_line'] = 'Ligne :';

// Exports
$lang['export_format'] = 'Format d\'exportation';
$lang['line_ends'] = 'Les lignes se finissent par';
$lang['must_choose_export_format'] = 'Vous devez choisir un format d\'exportation.';
$lang['invalid_export_format'] = 'Format d\'exportation non valide';
$lang['no_exporter_found'] = 'Aucun filtre d\'exportation trouvé.';
$lang['error_performing_search'] = 'Une erreur est survenue lors de la recherche.';
$lang['showing_results_x_through_y'] = 'Affichage des résultats %s à %s.';
$lang['searching'] = 'Recherche en cours...';
$lang['size_limit_exceeded'] = 'Attention, la taille limite de recherche est dépassée.';
$lang['entry'] = 'Entrée';
$lang['ldif_export_for_dn'] = 'Exportation LDIF pour : %s';
$lang['generated_on_date'] = 'Généré par phpLDAPadmin ( http://phpldapadmin.sourceforge.net/ ) pour %s';
$lang['total_entries'] = 'Entrées totales';
$lang['dsml_export_for_dn'] = 'Exportation DSLM pour : %s';
$lang['include_system_attrs'] = 'Inclure les attributs système';
$lang['csv_spreadsheet'] = 'CSV (feuille de calcul)';

// logins
$lang['password_blank'] = 'Vous avez laissé le mot de passe vide.';
$lang['no_one_logged_in'] = 'Personne n\'est connecté sur ce serveur.';
$lang['could_not_logout'] = 'Impossible de se déconnecter.';
$lang['unknown_auth_type'] = 'auth_type inconnu : %s';
$lang['logged_out_successfully'] = 'Déconnexion du serveur avec succès <b>%s</b>';
$lang['authenticate_to_server'] = 'Authentification auprès du serveur %s';
$lang['warning_this_web_connection_is_unencrypted'] = 'Avertissement : la connexion Web n\'est pas chiffré.';
$lang['not_using_https'] = 'Vous n\'utilisez pas « https ». Le navigateur Web transmettra les informations de connexion en clair.';
$lang['login_dn'] = 'DN de connexion';
$lang['user_name'] = 'Nom d\'utilisateur';
$lang['password'] = 'Mot de passe';
$lang['authenticate'] = 'Authentification';
$lang['login_not_allowed'] = 'Désolé, vous n\'êtes pas autorisé à utiliser phpLDAPadmin avec ce serveur LDAP.';

// Entry browser
$lang['entry_chooser_title'] = 'Sélecteur d\'entrées';

// Index page
$lang['need_to_configure'] = 'Vous avez besoin de configurer phpLDAPadmin. Éditez le fichier « config.php » pour le faire. un exemple de fichier de configuration est fournit dans « config.php.example »';

// Mass deletes
$lang['no_deletes_in_read_only'] = 'Supprimer n\'est pas autorisé en lecture seule.';
$lang['error_calling_mass_delete'] = 'Erreur lors de l\'applel de mass_delete.php. Il manque mass_delete dans les variables POST.';
$lang['mass_delete_not_array'] = 'La variable POST mass_delete n\'est pas un tableau.';
$lang['mass_delete_not_enabled'] = 'La suppression de masse n\'est pas activé. Veuillez l\'activer dans config.php avant de procéder.';
$lang['mass_deleting'] = 'Suppression de masse';
$lang['mass_delete_progress'] = 'Progression de la suppression sur le serveur « %s »';
$lang['malformed_mass_delete_array'] = 'Tableau mass_delete malformé.';
$lang['no_entries_to_delete'] = 'Vous n\'avez sélectionné aucune entrées à supprimer.';
$lang['deleting_dn'] = 'Suppression de %s';
$lang['total_entries_failed'] = '%s sur %s entrées n\'ont pu être effacées.';
$lang['all_entries_successful'] = 'Toutes les entrées ont été supprimées avec succès.';
$lang['confirm_mass_delete'] = 'Confirmer la suppression de masse de %s entrées sur le serveur %s';
$lang['yes_delete'] = 'Oui, supprimer !';

// Renaming entries
$lang['non_leaf_nodes_cannot_be_renamed'] = 'Vous ne pouvez renommer une entrée qui a des sous-entrées (cad, l\'opération de renommage n\'est pas autorisé sur des entrées non terminales)';
$lang['no_rdn_change'] = 'Vous n\'avez pas modifié le RDN';
$lang['invalid_rdn'] = 'Valeur RDN non valide';
$lang['could_not_rename'] = 'Impossible de renommer l\'entrée';

// Password checker
$lang['passwords_match'] = 'Correspondance du mot de passe !';
$lang['passwords_do_not_match'] = 'Les mots de passe ne correspondent pas !';
$lang['password_checker_tool'] = 'Outil de vérification de mot de passe';
$lang['to'] = 'Vers';

// Templates
$lang['using'] = 'En utilisant le';
$lang['switch_to'] = 'Vous pouvez basculer vers ';
$lang['default_template'] = 'modèle par défaut';

// template_config
$lang['user_account'] = 'Compte utilisateur (posixAccount)';
$lang['address_book_inet'] = 'Entrée de carnet d\'adresses (inetOrgPerson)';
$lang['address_book_moz'] = 'Entrée de carnet d\'adresses (mozillaOrgPerson)';
$lang['kolab_user'] = 'Entrée d\'utilisateur Kolab';
$lang['organizational_unit'] = 'Unité organisationnelle';
$lang['new_organizational_unit'] = 'Nouvelle unité organisationnelle';
$lang['organizational_role'] = 'Rôle organisationnel';
$lang['posix_group'] = 'Groupe Posix';
$lang['samba_machine'] = 'Machine NT Samba';
$lang['samba3_machine'] = 'Machine NT Samba 3';
$lang['samba_user'] = 'Utilisateur Samba';
$lang['samba3_user'] = 'Utilisateur Samba 3';
$lang['samba3_group'] = 'Groupe de mappage Samba 3';
$lang['dns_entry'] = 'Entrée DNS';
$lang['simple_sec_object'] = 'Object de sécurité simple';
$lang['courier_mail_account'] = 'Compte de messagerie';
$lang['courier_mail_alias'] = 'Alias de compte de messagerie';
$lang['ldap_alias'] = 'Alias LDAP';
$lang['sendmail_cluster'] = 'Cluster Sendmail';
$lang['sendmail_domain'] = 'Domaine Sendmail';
$lang['sendmail_alias'] = 'Alias Sendmail';
$lang['sendmail_virt_dom'] = 'Domaine virtuel Sendmail';
$lang['sendmail_virt_users'] = 'Utilisateurs virtuels Sendmail';
$lang['sendmail_relays'] = 'Relais Sendmail';
$lang['custom'] = 'personnalisé';
$lang['samba_domain_name'] = 'Mon nom de domaine Samba';
$lang['administrators'] = 'Administrateurs';
$lang['users'] = 'Utilisateurs';
$lang['guests'] = 'Invités';
$lang['power_users'] = 'Utilisateurs avec pouvoir';
$lang['account_ops'] = 'Opérateurs de comptes';
$lang['server_ops'] = 'Opérateurs de serveurs';
$lang['print_ops'] = 'Opérateurs d\'impression';
$lang['backup_ops'] = 'Opérateurs de sauvegarde';
$lang['replicator'] = 'Duplicateurs';
$lang['unable_smb_passwords'] = ' Impossible de créer les mots de passe Samba. Veuillez vérifier la configuration dans template_config.php';
$lang['err_smb_conf'] = 'Erreur : vous avez une erreur dans votre confguration Samba.';
$lang['err_smb_no_name_sid'] = 'Erreur : un nom et un sid doivent être fournit pour votre domaine Samba.';
$lang['err_smb_no_name'] = 'Erreur : aucun nom fournit pour le domaine Samba.';
$lang['err_smb_no_sid'] = 'Erreur : aucun sid fournit pour le domaine Samba.';

// Samba Account Template
$lang['samba_account'] = 'Compte Samba';
$lang['samba_account_lcase'] = 'compte samba';

// New User (Posix) Account
$lang['t_new_user_account'] = 'Nouveau compte utilisateur';
$lang['t_hint_customize'] = 'astuce : pour personnaliser ce modèle, éditez le fichier templates/creation/new_user_template.php';
$lang['t_name'] = 'Nom';
$lang['t_first_name'] = 'Prénom';
$lang['t_last_name'] = 'Nom de famille';
$lang['t_first'] = 'premier';
$lang['t_last'] = 'dernier';
$lang['t_state'] = 'État';
$lang['t_common_name'] = 'Nom commun';
$lang['t_user_name'] = 'Nom d\'utilisateur';
$lang['t_password'] = 'Mot de passe';
$lang['t_encryption'] = 'Chiffrement';
$lang['t_login_shell'] = 'Shell de connexion';
$lang['t_home_dir'] = 'Dossier personnel';
$lang['t_uid_number'] = 'UID';
$lang['t_auto_det'] = '(déterminé automatiquement)';
$lang['t_group'] = 'Groupe';
$lang['t_gid_number'] = 'GID';
$lang['t_uid'] = 'ID utilisateur';
$lang['t_err_passwords'] = 'Vos mots de passe ne correspondent pas. Veuillez revenir et réessayer.';
$lang['t_err_field_blank'] = 'Vous ne pouvez laisser %s vide. Veuillez revenir et réessayer.';
$lang['t_err_field_num'] = 'Vous ne pouvez entrez que des valeurs numériques pour le champ %s. Veuillez revenir et réessayer.';
$lang['t_err_bad_container'] = 'Le conteneur que vous avez spécifié (%s) n\'existe pas. Veuillez revenir et réessayer.';
$lang['t_confirm_account_creation'] = 'Confirmer la création du compte';
$lang['t_secret'] = '[secret]';
$lang['t_create_account'] = 'Créer un compte';
$lang['t_verify'] = 'Vérifier';

// New Group (Posix)
$lang['t_new_posixgroup'] = 'Nouveau groupe Posix';

// New Address Template
$lang['t_new_address'] = 'Nouvelle entrée de carnet d\'adresses';
$lang['t_organization'] = 'Organisation';
$lang['t_address'] = 'Adresse';
$lang['t_city'] = 'Ville';
$lang['t_postal_code'] = 'Code postal';
$lang['t_street'] = 'Rue';
$lang['t_work_phone'] = 'Téléphone (bureau)';
$lang['t_fax'] = 'Fax';
$lang['t_mobile'] = 'Mobile';
$lang['t_email'] = 'Courriel';
$lang['t_container'] = 'Conteneur';
$lang['t_err_cn_blank'] = 'Vousne pouvez pas laissez le Nom commun vide. Veuiller revenir er réessayer.';
$lang['t_confim_creation'] = 'Confirmer la création de l\'entrée :';
$lang['t_create_address'] = 'Créer l\'adresse';

// default template
$lang['t_check_pass'] = 'Vérifier l\'adresse...';
$lang['t_auto_submit'] = '(Évaluation automatique lors de la soumission.)';

// compare form
$lang['compare'] = 'Comparer';
$lang['comparing'] = 'Compararaison des DNs suivants';
$lang['compare_dn'] = 'Comparer un autre DN avec';
$lang['with'] = 'avec ';
$lang['compf_source_dn'] = 'DN source';
$lang['compf_dn_tooltip'] = 'Comparer cn DN avec un autre';
$lang['switch_entry'] = 'Basculer l\'entrée';
$lang['no_value'] = 'Aucune valeur';
$lang['compare_with'] = 'Comparer avec une autre entrée';
$lang['need_oclass'] = 'Vous avez besoin d\'un autre ObjectClass(es) pour ajouter cet attribut %s.';

// Time out page
$lang['session_timed_out_1'] = 'Votre session s\'est terminé avec';
$lang['session_timed_out_2'] = 'min. d\'inactivité. Vous avez été automatiquement déconnecté.';
$lang['log_back_in'] = 'Pour vous reconnecter, veuillez cliquer sur le lien suivant :';
$lang['session_timed_out_tree'] = '(Session expirée. Déconnexion automatique.)';
$lang['timeout_at'] = 'L\'inactivité vous déconnectera à %s';
?>
