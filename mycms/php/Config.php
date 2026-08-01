<?php
ini_set("upload_max_filesize", "100MB");
ini_set("post_max_size", "100MB");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author Nminfo
 */

// Application environment
//APP_ENV can get values: local or hosted

define('APP_ENV', 'hosted'); 
define('LICENSED_DOMAIN', 'bot.bouhezila.com');

date_default_timezone_set('Africa/Algiers');

class Config
{


    // ************************************  CLIENT PAAREMETERS ***************************

    public static $category_Pizza = "Pizza";
    public static $category_1_4_Pizza = "1/4_Pizza";
    public static $category_1_2_Pizza = "1/2_Pizza";
    public static $pizzaObjectsSuffix = "Pizza"; // Ex: Pizza 4 Fromages
    public static $pizzaSupplementSuffix = "-"; // Ex: supplements -1/2 Boisse
    public static $article_aux_choix = "aux choix"; // Ex: supplements -1/2 Boisse

    //************** ******************** DATABASE ******************************************//

    public static $bouhezilaCompany = "Bouhezila";

    public static $roleSuperAdmin = "superAdmin";
    public static $roleAdmin = "admin";
    public static $roleChef = "chef";
    public static $roleCheckout = "checkout";
    public static $roleWaiter = "waiter";

    public static $userChefAll= "chef-all";
    public static $printerAll = "printer-all";

    //this two variables must be the same  as radio input elements values in ArticleManagement.php and global.js 

    public static $mediaType_image = "IMG";
    public static $mediaType_video = "VID";
    public static $mediaPositionCover = "C";
    public static $mediaPositionGallery = "G";

    //
    public static $orderStateNew = "NEW";
    public static $orderStateStarted = "STARTED";
    public static $orderStateValid = "VALID";
    public static $orderStateReady = "READY";
    public static $orderStateNotified = "NOTIFIED"; //is used in Tv 
    public static $orderStateDelivred = "DELIVRED"; //is used win waiterHistory 
    public static $orderStatePayed = "PAYED";
    public static $orderStateCancel = "CANCEL";

    public static $orderPlaceOnTable = "onlocal";
    public static $orderPlaceCarryWith = "Emporter";


 public static $currencies = [
        "DA" => "DA - Algerian Dinar",
        "دج" => "الدينار الجزائري",
        "EUR" => "EUR - Euro",
        "USD" => "USD - US Dollar",
        "SAR" => "SAR - Saudi Riyal",
        "AED" => "AED - UAE Dirham",
        "TND" => "TND - Tunisian Dinar",
        "MAD" => "MAD - Moroccan Dirham",
        "EGP" => "EGP - Egyptian Pound",
        "GBP" => "GBP - British Pound",
        "INR" => "INR - Indian Rupee",
    ];

    //Static variables that are initialised from init.php
    public static $cmsCurrency = null;

    public static $cmsLanguage = null;
    public static $langStrings = null;

    public static $orderCapability = null;
    public static $printChef = null;
    public static $printClient = null;
    public static $printArabicRecipe = null;

     public static $vatEnabled = false;

    //********************* PRINTER LABELS ******************************************/
    public static $label_40_20 = "40-20";
    public static $label_50_30 = "50-30";
    public static $label_60_40 = "60-40";

    public static $USB_printer_name ="posprinter";

    // Delay (microseconds) between consecutive sends to the same network printer.
    // Increase to 1500000 (1.5s) or 2000000 (2s) for slow printers.
    public static $printerDelay = 700000; // 0.7s

    //************** ******************** MESSAGES ******************************************//
    // Messages to display to users
    // The messages in global.js file must be the same as these
    // Please see js/global.js file

    public static $user_error = "Ooops! there was a problem. ";

    public static $user_error_Cannot_delete = "Cannot delete: ";
     
    // this message must be the same as the variable noDataFound in Global.js
    public static $no_data_found = "no-data-found";
    public static $data_exist = "data-exist";
    public static $licence_limited = "licence-limited";
    public static $licence_not_created = "licence_not_created";
    public static $have_media = "have-media";
    public static $fail_upload_file = "Cannot-upload-file";
    public static $fail_delete_file = "Cannot-delete-file";
    public static $fail_remove_media_dir = "Cannot-remove-media-directoy";
    public static $last_price = "last-price";
    public static $last_subOrder = "last-subOrder";
    public static $user_not_found = "user-not-found";
    public static $user_still_blocked = "user-still-blocked";
    public static $user_still_connected = "user-still-connected";
    public static $user_define_password = "define-password";

    //********************************** CONFIG VARIABLES ************************************//

    // inactive session time,
    // this variable is used in functions.php/checkSessionAlive()
    public static $session_inactive = 18000;

    // acces errors permitted to the user befor blocked by the CMS,
    // this variable is used in Authentication.php/increaseAccessErrors()
    public static $access_errors = 5;

    // User waiting time (minutes) befor the CMS Gives acces an other time,
    // this variable is used in Authentication.php/increaseAccessErrors()
    public static $user_wait_time = 2;

    // The path of the Article images,
    // this variable is used in JsonMedia.php and in functions.php
    public static $media_path_suffix = '../..';
    public static $object_images_path = 'object-media';
    public static $category_images_path = 'category-media';
    public static $company_images_path = 'company-media';

    // Standardized media bases (set in init.php)
    public static $media_fs_base = null;  // absolute filesystem root of the app (trailing DIRECTORY_SEPARATOR)
    public static $media_url_base = '';   // URL base path of the app ('' or '/eatsmartly')

    // The base path for all backups, initialized from the licence settings.
    public static $backupBasePath = null;

    // Time cycle for the backup menu folders
    public static $backupMenuTimeCycle = (0 * 24 * 60 * 60); // 0 days * 24 hours * 60 minutes * 60 seconds

    // Number of MENU folders backups to keep
    public static $numberOfMenuFoldersBackupsToKeep = 3; 

    //Resize uploaded images parameters
    public static   $resizeWidth = 500;
    public static   $resizeHeight = 650;
    public static   $resizeImages = false;


    //This is the youtube embed suffix link, this is used to get the ID of youtube
    // video from the database
    //This variable is used in article.php page for external users
    public static $youtube_embed_suffix = 'https://www.youtube.com/embed/';
    public static $youtube_openGraph_suffix = 'https://www.youtube.com/v/';
    public static $youtube_video_thumbnail_suffix = 'http://img.youtube.com/vi/';
    public static $youtube_thumbnail_image_name = '/hqdefault.jpg';

    // The path of the log file ,
    // this variable is used in functions.php/addTrace()
    public static $log_file_path = "/logs/my-logs.log";
}

