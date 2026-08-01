<?php
return [

   //Validation messages

   'validation_no_file_selected' => 'Aucun fichier sélectionné.',
   'validation_image_type_invalid' => "Type d'image non valide.",
   'validation_image_too_large' => 'Image tros grand, c\'est plus de 10 Mb.',
   'validation_video_type_invalid' => 'Type de video non valide.',
   'validation_video_too_large' => 'Video tros grand, c\'est plus de 10 Mb.',
   'validation_ip_invalid' => 'Saisie une addresse IP ou un mot \"USB\".',
   'validation_not_empty' => 'ne doit pas être vide.',
   'validation_select_choice' => 'Selectionner un choix.',
   'validation_must_be_selected' => 'doit etre selectioné.',
   'validation_numbers_only' => 'doit être chiffres.',
   'validation_letters_or_numbers' => 'doit être lettres ou chiffres (pas de caractères spéciaux).',
   'validation_letters_numbers_special' => 'doit être lettres, chiffres ou (!?\'/@.) (pas de caractères spéciaux).',
   'validation_paragraph' => 'doit être lettres, chiffres ou (/@!?\'\';:.,) (pas de caractères spéciaux).',
   'validation_embed_link' => 'doit être lettres, chiffres ou (://.) (pas de caractères spéciaux).',
   'validation_no_spaces' => 'ne doit pas contenir d\'espaces.',
   'validation_invalid' => 'n\'est pas valide.',
   'validation_no_trim_spaces' => 'ne doit pas commencer ou se terminer par un l\'espace.',

   //Datatable Language entries
   'dT_sProcessing'    => 'Traitement en cours...',
   'dT_sLengthMenu'    => 'Afficher _MENU_ entrées',
   'dT_sZeroRecords'   => 'Aucun enregistrement trouvé',
   'dT_sInfo'          => 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
   'dT_sInfoEmpty'     => 'Affichage de 0 à 0 sur 0 entrée',
   'dT_sInfoFiltered'  => '(filtré à partir de _MAX_ entrées au total)',
   'dT_sSearch'        => 'Rechercher :',
   'dT_searchPlaceholder' => 'Rechercher',
   'dT_oPaginate_sFirst'    => 'Premier',
   'dT_oPaginate_sPrevious' => 'Précédent',
   'dT_oPaginate_sNext'     => 'Suivant',
   'dT_oPaginate_sLast'     => 'Dernier',

   //general UI text: functions.php, global.js,..

   'place' => 'Emplacement',
   'order' => 'Commande',
   'yes' => 'Oui',
   'no' => 'Non',
   'from' => 'De',
   'page' => 'Page',
   // ********** Messages des fichiers Js ******************
   'oops' => "Oups !",
   'nice' => 'Bien',
   'ok' => 'OK',
   'comment' => "Commentaire",

   // Titres et boutons des modales
   'modal_title_cancel' => 'Annuler',
   'modal_title_becarefull' => "Attention",
   'modal_title_success' => 'Succès',
   'modal_title_empty' => 'Vide',
   'modal_title_add_value' => 'Ajouter un nouvel enregistrement',
   'modal_title_update_value' => 'Mettre à jour l\'enregistrement',
   'modal_title_update_qty' => 'Mettre à jour la quantité',
   'modal_title_place' =>  "Commande passée",
   'modal_title_pay' =>  "Payer ?",
   'modal_title_reprint' =>  "Réimprimer ?",
   'modal_title_track_order' => 'Suivre votre commande',

   'modal_close_btn' => "Fermer",
   'modal_check_btn' => "Vérifier ...",
   'modal_reprint_btn' => "Réimprimer",
   'modal_ready_btn' => "Prêt",

   'add_chef_comment' => "Ajouter un commentaire pour le chef.",
   'reprint_ticket' => "Réimprimer le ticket..",

   // Messages globaux dans les fichiers javascript

   //Messages de Config.php

   'user_error' => "Oups ! Un problème est survenu.",
   'user_password_error' => "Veuillez définir un mot de passe pour cet utilisateur.",
   'user_error_cannot_delete' => "Impossible de supprimer : ",
   'variants_already_generated' => "Les variantes sont déjà générées.",
   'object_no_variants' => "L'objet n'a pas de variantes à supprimer.",
   'report_sales_admin_print' => "Rapport des ventes pour : ",
   'report_prices_print' => "Rapport des prix des articles",
   'licence_key_error' => "Clé de licence incorrecte.",


   'licenceActivated' => "Licence activée avec succès.",
   'msg_choice_added' => "Choix ajouté avec succès, suivant .. ?",
   'msgConfirmDelSubOrder' => "Voulez-vous annuler ce choix ?",
   'msgTableValidation' => "Veuillez saisir un code de table valide.",
   'msgCarryCodeValidation' => "Veuillez saisir un code à emporter valide.",
   'msgSuivieCommandeTable' => "Vous pouvez maintenant suivre l'avancement de votre commande dans le menu principal sous",
   'unpaid_orders_table_warning' => "Des commandes impayées existent sur le compte : {tableName}\nVoulez-vous vraiment valider cette commande ?",
   'msgErrorAccured' => "Une erreur s'est produite.",
   'msgChoixDejaAjouté' => "Ce choix existe déjà.",
   'msgCommandeTableMenu' => "Bien. La commande a été ajoutée avec succès.",
   'msgPlaceRequired' => "Veuillez choisir un emplacement pour la commande.",
   'msgNameEmpty' => "Le nom ne peut pas être vide.",

   'msgConfirmDeleteAttributeFromCategory' => "Êtes-vous sûr de vouloir supprimer cet attribut de la catégorie ?",
   'msgConfirmDelete' => "Êtes-vous sûr de vouloir supprimer l'enregistrement ?",
   'msgHaveAttribute' => "Cette catégorie a déjà un attribut.",
   'msgOperationReussie' => "Opération réussie.",
   'msgObjectExists' => "Cet objet existe déjà.",
   'msgObjectDelete' => "Objet supprimé avec succès.",
   'msghaveMedia' => "Cette catégorie a déjà un média.",
   'msg_object_added' => " Objet \"{object}\" ajouté avec succès.",
   'msg_object_updated' => " Objet \"{object}\" mis à jour avec succès.",
   'msg_object_cannot_delete' => " L'objet ne peut pas être supprimé car il est utilisé dans une ou plusieurs commandes payées.",
   'msgmediaExists' => "Ce média existe déjà.",
   'msgLastPrice' => 'Vous devez vérifier le prix de base de l\'objet',
   'start_after_end_warning' => "La \"date de début\" doit être antérieure à la \"date de fin\".",
   'msg_cost_price_error' => "Le coût de revient ne peut pas être supérieur au prix de vente.",
   'msgUserLicenceLimited' => "Votre licence ne permet pas plusieurs utilisateurs avec le rôle sélectionné.",
   'msgUserExist' => "Cet utilisateur existe déjà, veuillez choisir un autre nom d'utilisateur.",
   'msgUserLicenceNotCreated' => "La licence n'existe pas pour cette entreprise",

   // ********** Menu page UI PHP AND JS *******************
   'home' => "Accueil",
   'food_court' => "Food Court",
   'menu' => "menu",
   'cart' => "CART",
   'tracking' => "Suivie commande",
   'search' => "Recherche",
   'notifications' => "Notifications",
   'login' => "Connexion",
   'logout' => "Déconnexion",
   'dashboard' => "Tableau de bord",
   'add' => "Ajouter",

   //***** Texte Js **********
   'supplements' => 'Ajouter des suppléments',
   'last_choice' => 'Terminer la commande',
   'continue' => 'Continuer',
   'main_page' => 'Vers la page principale',
   'take_away_input_tooltip' => 'Code à emporter',
   'on_table_input_tooltip' => 'Votre code de table',

   //********** modal de progression *******/
   'order_ordered' => "Commandée",
   'order_validated' => "Validée",
   'order_to_chef' => "Transmise au chef",
   'order_ready' => "Prête",
   'order_delivered' => "Livrée",

   // restaurent page
   'start_from' => 'A partir de',

   //Product detail page
   'available' => "Disponible",
   'not_available' => "Non disponible",
   'your_opinion' => "Votre avis",

   //Cart page
   'order_validation' => "Valider la commande",
   'article'  => "Article",
   'price' => "Prix",
   'quantity' => "Qté",
   'action' => "Action",
   'total' => "Total",
   'take_away' => "Emporter",
   'on_table' => "Table",
   'confirm_order' => "Commander",

   //Login page UI
   'username' => "Nom d'utilisateur",
   'password' => "Mot de passe",
   'return_home' => "Retour à l'accueil",
   'msgUserRedirect' => "Vous serez redirigé vers une nouvelle page dans quelques secondes.",
   'msgUserNotFound' => "Je ne reconnais pas ce nom d'utilisateur.",
   'msgPswNotMatch' => "Le nom d'utilisateur et le mot de passe saisis ne correspondent pas.",
   'msgUserStillBlocked' => "Le nombre maximum de tentatives autorisées est atteint.\nRéessayer dans 05 minutes.",
   'msgUserStillConnected' => "Utilisateur déjà connecté",


   // Cms header
   'menu' => "MENU",
   'categories' => "Catégories",
   'articles' => "Articles",
   'attributes' => "Attributs",

   'reports' => "Rapports",
   'orders' => "Commandes",
   'i/o' => "Ventes/Charges",
   'sales_by_categories' => "Ventes par catégories",
   'earnings' => "Bénéfices",

   'configuration' => "Configuration",
   'expense_types' => "Types de dépenses",
   'tables' => "Tables",
   'vat' => "T.V.A",
   'pizza_variantes' => "Variantes de pizza",
   'printers' => "Imprimantes",

   'company' => "Société",
   'administration' => 'Administration',

   //Admin Panel page  & JS text
   'bouhezila_text' => 'fournisseur des solutions informatiques',

   'sales_day' => "Ventes (jour)",
   'sales_month' => "Ventes (mois)",
   'expenses_day' => "Charges (jour)",
   'expenses_month' => "Charges (mois)",
   'earnings_day' => "Bénéfices (jour)",
   'earnings_month' => "Bénéfices (mois)",
   'evolution_of_sales' => "Évolution des ventes",
   'configure_period' => "Configurer la période",
   'sources_of_gains' => "Sources de ventes",
   'top_10_articles_sold' => "Top 10  articles vendus",
   'top_10_categories_sold' => "Top 10  catégories vendues",
   'top_20_earnings_articles' => "Top articles par bénéfices",
   'top_20_earnings_categories' => "Top catégories par bénéfices",

   //JS messages text
   'choose_last_days_title' => "Choisir les derniers jours",
   'cancel_button' => "Annuler",
   'validate_button' => "Valider",
   'invalid_days_warning' => "Veuillez entrer un nombre de jours valide (> 0)",
   'choose_period_title' => "Choisir une période",
   'start_date_label' => "Date de début :",
   'end_date_label' => "Date de fin :",
   'error_title' => "Erreur",
   'missing_dates_error' => "Veuillez saisir les deux dates.",
   'invalid_date_format_error' => "Format de date invalide (utilisez JJ-MM-AAAA).",

   'top_objects_options_title' => "Options des meilleurs objets",
   'top_categories_options_title' => "Options des meilleures catégories",
   'criteria_label' => "Critère :",
   'quantity_option' => "Quantité",
   'amount_option' => "Valeur",
   'last_days_period' => "derniers {days} jours",
   'period_range' => "du {start} au {end}",
   'welcome_user' => "Bienvenue M. {familyName} {name}",

   //Category page
   'category_management_title' => 'Gestion des catégories',
   'view_categories' => 'Voir catégories',
   'category_label' => 'Catégorie',
   'prepare_label' => 'Préparable',
   'supplement_label' => 'Un supplément',
   'accept_supplement_label' => 'Accepter le supplément',
   'display_label' => 'Afficher',
   'display_order_option' => 'Ordre d\'affichage',
   'color_label' => 'Couleur',
   'available_label' => 'Disponible',
   'company_label' => 'Société',
   'date_label' => 'Date',
   'action_label' => 'Action',
   'add_button' => 'Ajouter',
   'edit_button' => 'Modifier',
   'attributes_title' => 'Attributs',
   'media_uploader_title' => 'Téléverseur de médias',
   'image_radio' => 'Image',
   'video_radio' => 'Vidéo',
   'upload_button' => 'Téléverser',
   'embed_button' => 'Intégrer',

   // Articles page
   'articles_heading' => 'Gestion des articles',
   'alert_close' => '×',
   'loading_alt' => 'Chargement...',
   'table_article' => 'Article',
   'table_category' => 'Catégorie',
   'table_available' => 'Disponible',
   'table_company' => 'Compagnie',
   'table_date' => 'Date',
   'table_action' => 'Action',
   'show_articles_btn' => 'Voir articles',
   'article_label' => 'Article',
   'article_placeholder' => 'Titre',
   'description_label' => 'Description',
   'description_placeholder' => 'Description de l\'objet',
   'base_price_label' => 'Prix de vente',
   'base_cost_label' => 'Coût de revient',
   'base_price_placeholder' => 'Prix de vente',
   'base_cost_placeholder' => 'Coût de revient',
   'observation_label' => 'Observation',
   'observation_placeholder' => 'Observation',
   'available_label' => 'Disponible',
   'add_btn' => 'Ajouter',
   'edit_btn' => 'Modifier',
   'price_heading' => 'Gestion des prix',
   'price_alert_close' => '×',
   'attribute_select_placeholder' => 'Valeur de l\'attribut',
   'attribute_option_choose' => 'Choisir la valeur de l\'attribut',
   'price_placeholder' => 'Prix de vente de l\'attribut',
   'cost_placeholder' => 'Coût de revient',
   'media_heading' => 'Téléverseur de médias',
   'media_alert_close' => '×',
   'image_label' => 'Image',
   'video_label' => 'Vidéo',
   'upload_alt' => 'Chargement...',
   'upload_btn' => 'Téléverser',
   'media_description_placeholder' => 'Description',
   'show_image_div' => 'Image',
   'show_video_div' => 'Vidéo',
   'sale_price_print' => "Imprimer prix de vente",

   //Attributes page
   'attributes_heading' => 'Gestion des attributs',
   'section_heading' => 'Attributs',
   'attributes_table_name' => 'Nom de l\'attribut',
   'attributes_table_action' => 'Action',
   'attributes_table_color' => 'Couleur',
   'attributes_table_size' => 'Taille',
   'values_section_heading' => 'Valeurs des attributs',
   'values_table_value' => 'Valeur',
   'values_table_action' => 'Action',

   //=================================================================
   //Checkout History page
   'checkout_title' => 'Paiement de la commande',
   'search_placeholder' => 'Lieu, Commande...',
   'orders_table_article' => 'Article',
   'orders_table_quantity' => 'Qté',
   'orders_table_amount' => 'Montant',
   'orders_table_action' => 'Action',
   'cancel_order_btn' => 'ANNULER',
   'print_chef_btn' => 'Chef',
   'print_client_btn' => 'Client',

   //Three most used buttons in Checkout HIstory, checkout panel,
   'cancel_order_btn' => 'ANNULER',
   'print_chef_btn' => 'Chef',
   'print_client_btn' => 'Client',
   'reprint_all_btn' => 'Ré-imprimer',

   //report IO page
   'report_io_title' => 'Rapport Entrées/Sorties',
   'report_io_qty' => 'Qty',
   'report_io_article' => 'Article',
   'report_io_amount' => 'Montant',
   'report_io_sales' => 'Ventes',
   'report_io_expense' => 'Dépense',
   'report_io_result' => 'Résultat',
   'report_io_date' => 'Date',
   'report_io_decaisse' => 'Depuis la caisse',
   'report_io_filter' => 'Filtrer',
   'report_io_print_sales' => 'Imprimer les ventes',
   'report_io_print_expenses' => 'Imprimer les dépenses',
   'report_expenses_admin_print' => 'Rapport des dépenses',
   'report_io_reset' => 'Réinitialiser',
   'report_io_cash' => 'Caisse',
   'report_io_total' => 'Total',

   // Texte JS
   'attribut_value_id_missed' => 'La valeur ou l\'ID de l\'attribut est manquant.',

   // report Sales by categories page
   'sales_category_title' => 'Ventes',
   'sales_category_cat' => 'Catégorie',
   'sales_category_art' => 'Article',
   'sales_category_attr' => 'Attribut',
   'sales_category_qty_total' => 'Qty totale',
   'sales_category_price_total' => 'Montant',
   'sales_category_cost_total' => 'Coût',
   'sales_category_earnings_total' => 'Bénéfice',

   //JS messages text
   'report_select_category' => 'Sélectionnez une catégorie.',
   //=================================================================

   //type charge managment page

   'type_Expense_mgmt' => 'Gestion des types de dépenses',
   'type_expense' => 'Type de dépense',
   'created' => 'Créé',
   'action' => 'Action',
   'view_type_expenses' => 'Voir les types de dépenses',
   'add_btn' => 'Ajouter',
   'edit_btn' => 'Modifier',

   //table page
   'table_mgmt' => 'Gestion des tables',
   'table' => 'Table',
   'code' => 'Code',
   'date' => 'Date',
   'action' => 'Action',
   'view_tables' => 'Voir les tables',
   'add_btn' => 'Ajouter',
   'edit_btn' => 'Modifier',

    //vat page
    //VAT page
      'vat_mgmt' => 'Gestion des Taxes', 
      'vat' => 'Taxe',
      'rate' => 'Taux',

      'action' => 'Action',
      'view_vats' => 'Voir les Taxes',
      'add_btn' => 'Ajouter',
      'edit_btn' => 'Modifier',
      'cancel_vat' => 'Annuler Taxe',

      //This are used in CheckoutMenu, CheckoutPanel and CheckoutHistory
      'total_ht_label' => "Total",
      'total_tva_label' => "Taxe",
      'total_ttc_label' => "Total TTC",

   //Pizza variants page
   'pizza_var_title' => 'Génération de variantes de pizza (1/2 et 1/4)',
   'pizza_var_desc' => 'Cette section a été développée pour répondre à une demande spécifique du "marché algérien", afin de générer automatiquement les variantes (Demi 1/2) et (Quart 1/4) des catégories commençant par "Pizza". Le système doit respecter les conditions suivantes pour que le script fonctionne correctement :',
   'pizza_var_cond1' => '1. Les catégories principales des pizzas doivent commencer par "Pizza" ou "PIZZA"',
   'pizza_var_ex1' => 'exemple : "Pizza Sauce Rouge", "PIZZA Sauce Boisée"',
   'pizza_var_cond2' => '2. Chaque article appartenant à la catégorie "Pizza" doit être nommé comme suit : "Pizza + Nom de l\'article"',
   'pizza_var_ex2' => 'exemple : "Pizza Margherita", "Pizza 4 Fromages"',
   'pizza_var_cond3' => '3. Un article intitulé "Pizza aux choix" doit être créé pour utiliser l\'option de commande personnalisée',
   'pizza_var_cond4' => '4. Les catégories (1/4_Pizza) et (1/2_Pizza) doivent être créées comme suppléments',
   'pizza_var_delete_info' => 'Le bouton "Supprimer" est utilisé pour retirer les articles commençant par (-1/4) ou (-1/2) de la base de données.',
   'pizza_var_generate_1_4' => 'Générer 1/4',
   'pizza_var_delete_1_4' => 'Supprimer 1/4',
   'pizza_var_generate_1_2' => 'Générer 1/2',
   'pizza_var_delete_1_2' => 'Supprimer 1/2',

   //Js text
   'pizza_var_generate_success' => 'Variantes de pizza générées avec succès.',
   'pizza_var_delete_success' => 'Variantes de pizza supprimées avec succès.',

   //printer page
   'printer_mgmt' => 'Gestion des imprimantes',
   'printer_name' => 'Nom',
   'printer_ip' => 'IP/USB',
   'printer_port' => 'Port',
   'printer_proto' => 'Protocole',
   'printer_labelsize' => 'Taille_étiquette',
   'printer_date' => 'Date',
   'printer_action' => 'Action',
   'printer_view' => 'Voir les imprimantes',
   'printer_add' => 'Ajouter',
   'printer_edit' => 'Modifier',
   'printer_categories' => 'Catégories',
   'printer_test' => 'Tester la connexion des imprimantes',

   // Texte JS
   'msgCheckPrinterStatus' =>  "Impossible de vérifier l'état de l'imprimante.",
   'msgErrorCheckPrinter' => "Erreur lors de la vérification de l'état de l'imprimante.",
   'modal_title_printer_status' => 'État de l\'imprimante',

   // Company page (superAdminPanel)

   'company_title' => 'Gestion des sociétés',
   'company_label' => 'Société',
   'desc_label' => 'Description',
   'address_label' => 'Adresse',
   'phone_label' => 'N° de mobile',
   'email_label' => 'E-mail',
   'gps_label' => 'GPS',
   'take_away_label' => 'Code à emporter',
   'generate_btn' => 'Générer',
   'add_btn' => 'Ajouter',
   'edit_btn' => 'Modifier',
   'view_companies_btn' => 'Voir les sociétés',
   'media_uploader_title' => 'Téléverseur de médias',
   'logo_radio' => 'Logo',
   'cover_radio' => 'Couverture',
   'upload_btn' => 'Téléverser',
   'options_title' => 'Options société',
   'print_chef_label' => 'Imprimer ticket chef',
   'print_client_label' => 'Imprimer reçu client',
   'print_arabicRecipe_label' => "Imprimer Ticket en Arabe",
   'order_capability_label' => 'Possibilité de commande',
   'currency_label' => 'Devise',
   'language_label' => 'Langue',
   'backup_base_path_label' => 'Dossier de base des sauvegardes',
   'backup_base_path_ph' => 'ex. D:\\backup',
   'browse' => 'Parcourir...',
   'select_folder_title' => 'Sélectionner un dossier de sauvegarde',
   'current_path' => 'Chemin actuel',
   'up_one_level' => 'Niveau sup.',
   'select_this_folder' => 'Sélectionner ce dossier',
   'admin_account_title' => 'Compte administrateur',
   'username_label' => 'Nom d\'utilisateur',
   'password_label' => 'Mot de passe',
   'name_label' => 'Prénom',
   'family_name_label' => 'Nom de famille',
   'admin_email_label' => 'E-mail',
   'delete_btn' => 'Supprimer',

   //Js Text
   'licence_options_updated' => "Options de licence mises à jour avec succès.",

   //=================================================================

   //Charge Management page
   'expense_mgmt_title' => 'Gestion des dépenses',
   'expenses_title' => 'Dépenses',
   'type_expense' => 'Type de dépense',
   'expense_label' => 'Dépense',
   'date_label' => 'Date',
   'amount_label' => 'Montant',
   'from_cashier_label' => 'Depuis la caisse',
   'obs_label' => 'Notes',
   'created_label' => 'Créé',
   'action_label' => 'Action',
   'view_expenses_btn' => 'Voir dépenses',
   'observation_label' => 'Observation',
   'date_placeholder' => 'Date',
   'amount_placeholder' => 'Montant',
   'add_expense_btn' => 'Ajouter dépense',
   'edit_expense_btn' => 'Modifier dépense',

   //Js text
   'msgExpenseAdded' => "Dépense ajoutée avec succès.",
   'msgExpenseUpdated' => "Dépense mise à jour avec succès.",

   //user management page
   'user_mgmt' => 'Gestion des utilisateurs',
   'view_users' => 'Voir utilisateurs',
   'connected' => 'Connecté',
   'username' => 'Nom d\'utilisateur',
   'password' => 'Mot de passe',
   'name' => 'Prénom',
   'family_name' => 'Nom de famille',
   'email' => 'E-mail',
   'role' => 'Rôle',
   'printer' => 'Imprimante',
   'add' => 'Ajouter',
   'edit' => 'Modifier',
   'users_table_user' => 'Utilisateur',
   'users_table_role' => 'Rôle',
   'users_table_name' => 'Prénom',
   'users_table_company' => 'Société',
   'users_table_date' => 'Date',
   'users_table_action' => 'Action',
   'categories' => 'Catégories',
   'alert_close' => '×',
   'loading_alt' => 'Chargement...',
   'select_printer' => 'Imprimantes',
   'select_role' => 'Rôle',


   //backup page
   'backup_title' => 'Sauvegarde',
   'db_backup_done' => '✅ Sauvegarde de la base de données terminée ...',
   'folder_backup_done' => '✅ Le dossier : {folder} a été sauvegardé ...',
   'folder_not_exist' => '❌ Le dossier : {folder} n\'existe pas.',
   'menu_backup_done' => '✅ Le MENU a été sauvegardé.',
   'menu_backup_not_needed' => 'ℹ️ La sauvegarde du MENU n\'est pas nécessaire.',
   'removed_old_backup' => 'Ancienne sauvegarde supprimée : {backup}',
   'backup_path_not_set_title' => 'Chemin de sauvegarde non configuré',
   'backup_path_not_set_msg' => 'Veuillez définir le dossier de base pour la sauvegarde dans le panneau des paramètres de l\'entreprise avant de lancer une sauvegarde.',

   // checkoutMenu page
   'ch_menu_title' => 'Checkout Menu',
   'ch_menu_article' => 'Article',
   'ch_menu_qty' => 'Qte',
   'ch_menu_price' => 'Prix',
   'ch_menu_msg' => 'Msg',
   'ch_menu_validate_client' => 'Chef + Client',
   'ch_menu_validate' => 'Chef',
   'ch_menu_cancel' => 'Annuler',
   'ch_menu_alert_close' => '×',
   'ch_menu_no_orders' => 'Aucune commande trouvée.',
   'ch_menu_confirm_cancel' => 'Êtes-vous sûr de vouloir annuler cette commande ?',
   'ch_menu_success' => 'Paiement réussi.',
   'ch_menu_error' => 'Une erreur est survenue. Veuillez réessayer.',

   // checkoutpanel page

   'pay_orders'         => 'Payer les commandes',
   'search_placeholder' => 'Emplacement, Commande..',
   'go'                 => 'Aller!',
   'date_prefix'        => 'Le ',
   'cash'               => 'Caisse = ',
   'sales'              => 'Ventes = ',
   'charges'            => 'Charges = ',
   'code'               => 'Code',
   'article'            => 'Article',
   'qty'                => 'Qté',
   'amount'             => 'Montant',
   'action'             => 'Action',
   'cancel'             => 'ANNULER',
   'e_ticket'           => 'Chef',
   'pay'                => 'Payer',
   'validate'           => 'Chef',

   //Texte JS
   'print_chef_text' => 'Imprimer un ticket pour le chef ?'

   // Others (add more as needed)


];
