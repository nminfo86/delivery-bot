<?php 

//Load cms Currency
//This will init the static $variable Config::$cmsCurrency to prevent accessing too much to DB
initCmsCurrency();

// language managment
// upload language config 
initCmsLanguageArray(); //this will init Config::$langStrings  and Config::$cmsLanguage


// check order capability licence
initOrderCapability();

//get Company config about the language of the Ticket, Arabic or other
initClientTicketsSetup();

// backup path
initBackupPath();

// check if VAT is enabled (at least one VAT record exists)
initVatStatus();



// set the body and html tags config based on the current language
// this will be used to set the direction and class of the body rtl ot ltr ..
  $cmsBodyConfig = getBodyConfig(Config::$cmsLanguage);
  $cmsHtmlConfig = getHtmlConfig(Config::$cmsLanguage);

// Compute filesystem base: two levels up from mycms/php -> app root folder
require_once __DIR__ . '/Config.php';

// Filesystem base (APP ROOT): parent of /mycms
// __DIR__ => .../eatsmartly/mycms/php
// dirname(__DIR__, 2) => .../eatsmartly
$appRoot = realpath(dirname(__DIR__, 2));
if ($appRoot === false) { $appRoot = dirname(__DIR__, 2); }
Config::$media_fs_base = rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $appRoot), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

// URL base: strip /mycms/php or /mycms
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$lower     = strtolower($scriptDir);
if (($p = strpos($lower, '/mycms/php')) !== false) {
    $scriptDir = substr($scriptDir, 0, $p);
} elseif (($p = strpos($lower, '/mycms')) !== false) {
    $scriptDir = substr($scriptDir, 0, $p);
}
if ($scriptDir === '/' || $scriptDir === '\\') { $scriptDir = ''; }
Config::$media_url_base = $scriptDir;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


