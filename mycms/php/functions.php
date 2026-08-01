<?php
session_start();
require_once "Config.php";
require_once "Company.php";
require_once "JsonCompany.php";
require_once "Attribute_Value.php";
require_once "Ordere.php";
require_once "SubOrder.php";
require_once "Authentication.php";
require_once "Licence.php";
require_once "JsonLicence.php";
require_once "JsonVat.php";
require_once "Vat.php";
require_once "Connection.php";
require_once "init.php";
require_once "vendor/autoload.php"; // Instead of direct include
use ArPHP\I18N\Arabic;  // The correct namespace for newer versions




//
//
// *************** GLOBAL FUNCTIONS ************** ************//

// function that load the Currency and return set it to Config::$cmsCurrency
function initCmsCurrency()
{
        if (Config::$cmsCurrency === null) {
                $licenceEatSmartly = JsonLicence::getLicence(1, false);
                $cmsCurrency = (is_array($licenceEatSmartly) && isset($licenceEatSmartly[0][Licence::$col_cmsCurrency]))
                        ? $licenceEatSmartly[0][Licence::$col_cmsCurrency]
                        : 'DA';
                Config::$cmsCurrency = $cmsCurrency;
        }
}

// function that load the language file and return the array of $key => $value
//In Food court approach, every company can setup it's UI language
//that's why we used getLicence with $_SESSION['company_id']
function initCmsLanguageArray()
{
        if (Config::$langStrings === null) {

                $company_id = $_SESSION["company_id"] ?? 1; // Default to company 1 if not set
                $licenceEatSmartly = JsonLicence::getLicence($company_id, false);

                $cmsLanguage = (is_array($licenceEatSmartly) && isset($licenceEatSmartly[0][Licence::$col_cmsLanguage]))
                        ? $licenceEatSmartly[0][Licence::$col_cmsLanguage]
                        : 'en';

                Config::$cmsLanguage = $cmsLanguage; //en, fr, or ar

                $langFile = __DIR__ . "/_lang.$cmsLanguage.php";

                if (file_exists($langFile)) {
                        Config::$langStrings = include $langFile;
                } else {
                        Config::$langStrings = include __DIR__ . "/_lang.en.php";
                }
        }
}

// function that initializes order capability authorisation
function initOrderCapability()
{

        if (Config::$orderCapability === null) {
                $licenceEatSmartly = JsonLicence::getLicence(1, false);
                // check order capability licence
                Config::$orderCapability = (is_array($licenceEatSmartly) && isset($licenceEatSmartly[0][Licence::$col_orderCapability]))
                        ? $licenceEatSmartly[0][Licence::$col_orderCapability]
                        : false;
        }
}
// function that initializes Client Ticket Language
//In Food court approach, every company can setup it's Client reciept Languages
//that's why we used getLicence with $_SESSION['company_id']
function initClientTicketsSetup()
{
        $company_id = $_SESSION["company_id"] ?? 1; // Default to company 1 if not set
        if (Config::$printArabicRecipe === null) {
                $licenceCompany = JsonLicence::getLicence($company_id, false);

                // check Client reciept Language
                Config::$printArabicRecipe = (is_array($licenceCompany) && isset($licenceCompany[0][Licence::$col_printArabicRecipe]))
                        ? $licenceCompany[0][Licence::$col_printArabicRecipe]
                        : false;
                // check Print Client reciept posiibility
                Config::$printClient = (is_array($licenceCompany) && isset($licenceCompany[0][Licence::$col_printClient]))
                        ? $licenceCompany[0][Licence::$col_printClient]
                        : false;
                // check Print Chef reciept posiibility
                Config::$printChef = (is_array($licenceCompany) && isset($licenceCompany[0][Licence::$col_printChef]))
                        ? $licenceCompany[0][Licence::$col_printChef]
                        : false;
        }
}

/**
 * Initializes the backup base path from the current company's licence settings.
 */
function initBackupPath()
{
        if (Config::$backupBasePath === null) {
                $company_id = $_SESSION["company_id"] ?? 1; // Default to company 1 if not set
                $licence = JsonLicence::getLicence($company_id, false);

                Config::$backupBasePath = (is_array($licence) && !empty($licence) && !empty($licence[0][Licence::$col_backupBasePath]))
                        ? $licence[0][Licence::$col_backupBasePath]
                        : ''; // Set to empty string if not found, to prevent re-querying.
        }
}

function initVatStatus()
{
        $vats = JsonVat::getAllVats(FALSE);
        Config::$vatEnabled = ($vats && count($vats) > 0);
}


// Function that return a value of key from language array file
function T($key)
{
        return Config::$langStrings[$key] ?? $key;
}

/**
 * Returns an array with 'dir' and 'class' for the body tag based on the language.
 * @param string $lang
 * @return array
 */
function getBodyConfig($lang)
{
        $rtlLanguages = ['ar', 'he', 'fa', 'ur']; // Add more RTL languages if needed
        if (is_string($lang) && is_array($rtlLanguages) && in_array($lang, $rtlLanguages, true)) {
                return ['dir' => 'rtl', 'class' => 'rtl'];
        }
        return ['dir' => 'ltr', 'class' => 'ltr'];
}
/**
 * Returns an array with 'dir' and 'class' for the html tag based on the language.
 * @param string $lang
 * @return array
 */
function getHtmlConfig($lang)
{
        $rtlLanguages = ['ar', 'he', 'fa', 'ur']; // Add more RTL languages if needed

        if (is_string($lang) && is_array($rtlLanguages) && in_array($lang, $rtlLanguages, true)) {

                return ['lang' => $lang, 'dir' => 'rtl'];
        }
        return ['lang' => $lang, 'dir' => 'ltr'];
}


function getApplicationPath()
{
        return dirname($_SERVER['DOCUMENT_ROOT']) . '/eatsmartly/';
}



function getCurrentDate()
{
        return date('Y-m-d H:i:s');
}

function getMediaServerpath($mediaPath)
{
        return str_replace(Config::$media_path_suffix, "http://" . $_SERVER['SERVER_NAME'], $mediaPath);
}

function getOpenGraphVideoLink($embedVideoLink)
{
        return str_replace(Config::$youtube_embed_suffix, Config::$youtube_openGraph_suffix, $embedVideoLink);
}

function getVideoThumbnailImage($embedVideoLink)
{
        $videoThumbnailLink = str_replace(Config::$youtube_embed_suffix, Config::$youtube_video_thumbnail_suffix, $embedVideoLink);
        return $videoThumbnailLink . Config::$youtube_thumbnail_image_name;
}

function getMsgPdoStmt(PDOStatement $stmt)
{
        $arr = $stmt->errorInfo();
        return $arr[2];
}

function shrinkCategoryNames($category)
{
        $words = explode(' ', (string)$category);

        $newCaregory = '';

        if (((is_countable($words) ? count($words) : 0) < 2)) {
                $newCaregory = mb_substr($words[0], 0, 4);
        } else {
                foreach ($words as $word) {
                        $newCaregory .= mb_substr($word, 0, 3) . ' ';
                }
        }
        return $newCaregory;
}

function loggedIn()
{
        return isset($_SESSION["role"]);
}

function checkSessionAlive()
{

        $inactive = Config::$session_inactive;
        if (!isset($_SESSION['timeout'])) {
                $_SESSION['timeout'] = time() + $inactive;
        }

        $session_life = time() - $_SESSION['timeout'];

        if ($session_life >= $inactive) {
                if (isset($_SESSION["username"])) {
                        Authentication::setUserConnection($_SESSION["username"], 0);
                }
                session_unset();
                session_destroy();
                redirectTo("../mycms/odkhol.php");
        }

        $_SESSION['timeout'] = time();
}

function confirmLoggedIn()
{
        //regenerate session id to prevent SESSION FIXATION ATTACK
        session_regenerate_id();
        //
        if (!loggedIn()) {
                redirectTo("../mycms/odkhol.php");
        }
        checkSessionAlive();
}

function autoRedirect($role)
{
        redirectTo($role . "Panel.php");
}

function accessControl($roles)
{
        $role = $_SESSION['role'] ?? null;
        $accessArray = explode(",", $roles);
        $accessArray = is_array($accessArray) ? $accessArray : [];

        if ($role === null || !in_array($role, $accessArray, true)) {
                logout();
        }
}


function logout()
{
        if (isset($_SESSION["username"])) {
                Authentication::setUserConnection($_SESSION["username"], 0);
        }
        session_unset();
        session_destroy();
        redirectTo("odkhol.php");
}

function forceHTTPS()
{
        $is_https = false;
        if (isset($_SERVER['HTTPS']))
                $is_https = $_SERVER['HTTPS'];
        if ($is_https !== "on") {
                //    header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                redirectTo("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit(1);
        }
}
function appUrl(string $path = ''): string
{
        // Prefer configured base URL (e.g., '/eatsmartly' or 'http://eatsmart.local')
        $base = rtrim((string)(Config::$media_url_base ?? ''), '/');

        // Fallback: directory of the executing script (not the filename)
        // - '/index.php'      -> ''
        // - '/eatsmartly/index.php' -> '/eatsmartly'
        if ($base === '') {
                $script = $_SERVER['SCRIPT_NAME'] ?? '';
                $dir = str_replace('\\', '/', dirname($script)); // '/eatsmartly' or '/'
                $base = rtrim($dir, '/'); // '' when at document root
        }

        $rel = '/' . ltrim($path, '/');
        return ($base === '' ? '' : $base) . $rel;
}
function redirectTo($page)
{
        // If not an absolute URL and starts with '/', treat as app-root (subfolder-safe)
        if (strpos($page, 'http://') !== 0 && strpos($page, 'https://') !== 0 && substr($page, 0, 1) === '/') {
                $page = appUrl($page);
        }
        header("Location: {$page}");
        exit;
}

function redirectToHome()
{
        redirectTo("/");
        exit;
}

function verify()
{
        /**
         * Hybrid licence verification:
         *
         * - If APP_ENV === 'hosted'  → validate by comparing HTTP_HOST against LICENSED_DOMAIN (Config.php).
         * - If APP_ENV === 'local'   → use the existing getmac hardware check (Windows MAC address).
         * - If APP_ENV is not defined (old versions without the constant) → fall back to the getmac check.
         *
         * NEW (hosted path): added 2026-07-06 to support shared web-hosting deployments.
         * The hosted check does NOT use the database 'checked' timestamp – domain validation
         * is stateless and fast, so no caching is needed.
         */

        // ── Determine environment ──────────────────────────────────────────────────
        $env = defined('APP_ENV') ? APP_ENV : 'local'; // fall back to local for old versions

        if ($env === 'hosted') {

                // ── Hosted path: domain-based validation ──────────────────────────
                // Strip leading 'www.' so both 'www.domain.com' and 'domain.com' match.
                $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
                if (strncasecmp($host, 'www.', 4) === 0) {
                        $host = substr($host, 4);
                }

                $licensedDomain = defined('LICENSED_DOMAIN') ? strtolower(LICENSED_DOMAIN) : '';

                if ($host !== $licensedDomain || $licensedDomain === '') {
                        redirectTo("/licenceContact.php");
                }

                // Domain matches – licence is valid, nothing more to do.
                return;
        }

        // ── Local path: existing getmac hardware check (unchanged) ────────────────
        /**
         * This function handles the checking licence process of the application.
         * It is based on Ethernet Adapter MAC address.
         * We check the Database hashed MAC Address with exec('getmac') every hour
         * based on the 'checked' field value in the Licence Database Table.
         *
         * To keep the application fast, exec('getmac') is only called once per hour
         * and the 'checked' field in the Licence Table is updated afterwards.
         */

        // The hashed mac address must be generated from generateLicenceKey()
        $checked = JsonLicence::getLicenceChecked();

        $futerCheckeTime = strtotime($checked) + (60 * 60); // + 1 hour in seconds
        $currentDateTime = strtotime(date('Y-m-d H:i:s'));

        if ($currentDateTime > $futerCheckeTime) {

                // Run Check licence script
                $licenceKey = JsonLicence::getLicenceKey();

                //    print_r($licenceKey);
                //    exit;
                $hashes = getHashedWindowsMAC();
                $hashes = is_array($hashes) ? $hashes : [];
                $licenceKey = isset($licenceKey) ? $licenceKey : null;

                // Check if the ethernet mac in the licence key is found in the PC all mac array
                if ($licenceKey === null || $licenceKey === '' || !in_array($licenceKey, $hashes, true)) {

                        redirectTo("/licenceContact.php");
                } else {

                        JsonLicence::updateCheckedLicence(date('Y-m-d H:i:s'));
                        // Update the 'checked' field in the licence table to the current date time
                }
        } else {

                // Do not run Check licence script (within the 1-hour cache window)
        }
}

/**

 * The licence key is one of the machine mac address hashed and encoded in base64
 * Generates a shortened hash for a given MAC address. this is used to make Licence Key user friendly
 * This function takes a MAC address as input, hashes it using the MD5 algorithm,
 * converts the resulting hash to a base64-encoded string, and returns the first
 * 12 characters of the encoded string.
 *
 * @param string $mac The MAC address to be hashed.
 * @return string A 12-character shortened hash of the MAC address.
 */
function generateLicenceKey($mac)
{
        $hash = md5($mac);

        // Convert the hash to base64 and take the first 12 characters
        return substr(base64_encode($hash), 0, 12);
}
//This function return all ethernet mac@ and put them hashed in an array, this is used in verify()
//to check if the hashed mac@ in the licence key is found in the PC all mac@ array
function getHashedWindowsMAC(): array
{
        $output = null;
        $mac_array = [];

        exec('getmac', $output);  // Don't gate on return value

        if (!empty($output)) {
                for ($i = 0; $i < count($output); $i++) {
                        $line = trim((string)($output[$i] ?? ''));
                        $mac = explode(' ', $line);
                        // Only hash valid MAC addresses (same filter as getEncodedEthernetMAC)
                        if (isset($mac[0]) && $mac[0] !== 'N/A' && strlen($mac[0]) == 17) {
                                $mac_array[] = generateLicenceKey($mac[0]);
                        }
                }
        }

        //     print_r($mac_array); // Debug output to verify the hashed MAC addresses
        //     exit;
        return $mac_array;
}

/**
 * Encodes a MAC address using a simple format
 * @param string $mac The MAC address to encode
 * @return string The encoded string
 */
function encodeMacAddress($mac)
{
        if (empty($mac)) return '';

        // Remove dashes and convert to uppercase
        $mac = strtoupper(str_replace('-', '', $mac));

        // Simple key for XOR
        $key = 'ES25';

        // XOR the MAC with the key
        $encoded = '';
        $mac = (string)($mac ?? '');
        $key = (string)($key ?? '');
        for ($i = 0; $i < strlen($mac); $i++) {
                $encoded .= chr(ord($mac[$i]) ^ ord($key[$i % strlen($key)]));
        }

        // Convert to base64 to ensure safe encoding
        return base64_encode($encoded);
}

/**
 * Decodes an encoded MAC address back to its original format
 * @param string $encoded The encoded string
 * @return string The original MAC address
 */
function decodeMacAddress($encoded)
{
        if (empty($encoded)) return '';

        // Decode from base64
        $decoded = base64_decode($encoded);

        // Use the same key as encoding
        $key = 'ES25';

        // XOR decrypt
        $mac = '';
        $decoded = (string)($decoded ?? '');
        $key = (string)($key ?? '');
        for ($i = 0; $i < strlen($decoded); $i++) {
                $mac .= chr(ord($decoded[$i]) ^ ord($key[$i % strlen($key)]));
        }

        // Format back to MAC address format (XX-XX-XX-XX-XX-XX)
        return implode('-', str_split($mac, 2));
}

/**
 * Gets the first available MAC address of a connected network adapter, or first disconnected if none connected
 * @return array|null Array containing encoded mac and interface name, or null if no adapters found
 * This function is used in licenceContact.php to obsfucate the MAC address from the user
 */
function getEncodedEthernetMAC(): ?array
{
        $output = null;
        $connected_adapters = array();
        $disconnected_adapters = array();

        if (exec('getmac', $output)) {
                // Skip the header line that contains "Physical Address    Transport Name"
                for ($i = 1, $n = (is_countable($output) ? count($output) : 0); $i < $n; $i++) {
                        $line = trim((string)($output[$i] ?? ''));

                        // Parse using same approach as getWindowsMAC()
                        $mac = explode(' ', (string)$line);
                        if (isset($mac[0]) && $mac[0] !== 'N/A' && strlen((string)$mac[0]) == 17) {
                                // Get the rest of the line after the MAC as the interface
                                $interface = trim(substr($line, strlen((string)$mac[0])));

                                $adapter = array(
                                        'mac' => encodeMacAddress($mac[0]), // Encode the MAC address
                                        'interface' => $interface
                                );

                                // Sort into connected and disconnected arrays
                                if (stripos((string)$interface, 'Media disconnected') === false) {
                                        $connected_adapters[] = $adapter;
                                } else {
                                        $disconnected_adapters[] = $adapter;
                                }
                        }
                }
        }

        // Return first connected adapter if available, otherwise first disconnected adapter
        if ((is_countable($connected_adapters) ? count($connected_adapters) : 0) > 0) {
                return $connected_adapters[0];
        } else if ((is_countable($disconnected_adapters) ? count($disconnected_adapters) : 0) > 0) {
                return $disconnected_adapters[0];
        }

        return null; // No adapters found
}

function printChefLabel($place, $qte, $article, $attributes, $supplements, $obs, $printer, $rePrint)
{

        if (Config::$printArabicRecipe) {
                $isArabic = true;
        } else {
                $isArabic = false;
        }

        if (Config::$printChef) {


                $label = getChefLabelByLanguage($place, $qte, $article, $attributes, $supplements, $obs, $printer, $isArabic);

                if ($rePrint) {
                        $RePrintPlace = "R-P " . $place;
                        $label = getChefLabelByLanguage($RePrintPlace, "1", $article, $attributes, $supplements, $obs, $printer, $isArabic);
                }

                // Check if printerIP is 'USB' to print via local printer
                if (strtoupper($printer[0][Printer::$col_printerIP]) === 'USB') {

                        $printerName = Config::$USB_printer_name; // Replace with your printer name

                        $tmpFile = tempnam(sys_get_temp_dir(), 'print');
                        file_put_contents($tmpFile, $label);

                        $cmd = 'copy /b "' . $tmpFile . '" "\\\\localhost\\' . $printerName . '"';
                        exec($cmd, $output, $result);
                        unlink($tmpFile);
                        if ($result !== 0) {
                                echo json_encode(array("state" => "f", "message" => "exec() print USB failed" . " " . __FUNCTION__));
                        }
                        //if printer is configured to print via IP
                } else {
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));

                        if ($socket === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "socket_create() failed" . " " . __FUNCTION__));
                                addTrace("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                        }

                        $result = socket_connect($socket, $printer[0][Printer::$col_printerIP], $printer[0][Printer::$col_printerPort]);
                        if ($result === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "Printer_connect() failed" . " " . __FUNCTION__));
                                addTrace("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
                        }
                        $sWrite = socket_write($socket, $label, strlen((string)$label));
                        if ($sWrite === FALSE) {
                                addTrace("socket_write() failed. Reason: " . socket_strerror(socket_last_error($socket)));
                                exit;
                        } else {
                                socket_close($socket);
                        }
                        usleep(Config::$printerDelay); // Wait for the printer to process the label (0.5 seconds)
                }
        }
}


//This function print all suborders that have same printer ESC in one label
function printChefOneLabel($prepareArray, $place)
{

        if (Config::$printArabicRecipe) {
                $isArabic = true;
        } else {
                $isArabic = false;
        }

        if ($isArabic) {

                $search = ['Take Away', 'Emporter'];
                $replace = ['محمول', 'محمول',];
                $place = str_replace($search, $replace, $place);

                $dateTime = date(' H:i Y-m-d');
                $textDirection = 'right';
        } else {
                $dateTime = date('d-m-Y H:i');
                $textDirection = 'left';
        }
        $arabic = new Arabic('Glyphs');

        if (Config::$printChef) {

                //TO print all chef subOrders in one ticket we use a trick of looping throw the prepared array 
                //from JsonSuborder.php twice to gather suborders that have same printer together


                //This is added to show subCode to Chef in case the order has other suborders that must be 
                //prepared , this is requested by Lahcen to manage suborders coocking time and avoid confusion in the kitchen
                // Determine if there are multiple distinct printers
                $uniquePrinterIds = array_unique(array_filter(array_column((array)$prepareArray, 'printer_id')));
                $hasMultiplePrinters = count($uniquePrinterIds) > 1;

                //this array is set to gather printers_id that are already printed to prevet redundency 
                $printer_id_array = array();

                foreach ((array) $prepareArray as $i => $subordersPrinter) {

                        $printer_id = $subordersPrinter["printer_id"];
                        $label =
                                "\x1B\x40" . // Initializes the printer (ESC @)
                                EscPosImage($arabic->utf8Glyphs($place, 50, false), 40, 'center') . "";


                        if ($printer_id !== null && !in_array($printer_id, $printer_id_array, true)) {

                                $printerIP = '';
                                $printerPort = '';
                                $sublabel = '';

                                foreach ((array) $prepareArray as $j => $subordPrint) {

                                        if ($subordPrint["printer_id"] == $printer_id) {
                                                // ************************ Start prepare Ticket ********************

                                                $fontPath = dirname(__DIR__) . '/fonts/arial.ttf';
                                                $textMaxWidth = 550;

                                                if ($subordPrint["supplements"] != '') {
                                                        $supplementsESC = '';
                                                        $suppLines = array_filter(explode("\n", $subordPrint["supplements"]), fn($l) => trim($l) !== '');
                                                        foreach ($suppLines as $suppLine) {
                                                                $suppText = wrapEscPosText(
                                                                        $arabic->utf8Glyphs(trim($suppLine), 50, false),
                                                                        $fontPath,
                                                                        28,
                                                                        $textMaxWidth
                                                                );
                                                                $supplementsESC .= EscPosImage($suppText, 28, $textDirection) . "";
                                                        }
                                                } else {
                                                        $supplementsESC = '';
                                                }

                                                if ($subordPrint["obs"] != '') {
                                                        $obsText = wrapEscPosText(
                                                                $arabic->utf8Glyphs($subordPrint["obs"], 50, false),
                                                                $fontPath,
                                                                28,
                                                                $textMaxWidth
                                                        );
                                                        $obsESC = EscPosImage($obsText, 28, $textDirection) . "\xA";
                                                } else {
                                                        $obsESC = '';
                                                }

                                                // $subcodeText = wrapEscPosText(
                                                //                 $arabic->utf8Glyphs($subordPrint["subCode"], 50, false),
                                                //                 $fontPath,
                                                //                 28,
                                                //                 $textMaxWidth
                                                // );
                                                // $subCodeESC = EscPosImage($subcodeText, 20, $textDirection);


                                                $title = " \"" . $subordPrint["qte"] . "\" " . $subordPrint["article"] . " " . $subordPrint["attributes"];
                                                $titleText = wrapEscPosText(
                                                        $arabic->utf8Glyphs($title, 50, false),
                                                        $fontPath,
                                                        36,
                                                        $textMaxWidth
                                                );

                                                $subCode = $hasMultiplePrinters ? $subordPrint["subCode"] : '';
                                                $footer = $subCode . " --------- " . $dateTime . " ---------";
                                                $footerText = wrapEscPosText(
                                                        $arabic->utf8Glyphs($footer, 50, false),
                                                        $fontPath,
                                                        20,
                                                        $textMaxWidth
                                                );
                                                $footerESC = EscPosImage($footerText, 20, 'center');

                                                $sublabel = $sublabel .
                                                        "\xA" . EscPosImage($titleText, 36, $textDirection) . "\xA" .
                                                        $supplementsESC .
                                                        $obsESC .

                                                        "\x1B\x61\x1" . // Specifies a centered printing position (ESC a)
                                                        "\x1B\x21\x2" . // Specifies font A (ESC !)
                                                        "\xA" .
                                                        $footerESC;

                                                // ************************ End prepare Ticket ********************

                                                $printerIP = $subordPrint["printerIP"];
                                                $printerPort = $subordPrint["printerPort"];
                                        }
                                }
                                array_push($printer_id_array, $printer_id);

                                $label = $label . $sublabel .
                                        "\xA\x6" . //Add 6 line feed 
                                        "\xA\x6" . //Add 6 line feed 
                                        "\xA\x6" . //Add 6 line feed 
                                        "\xA\x6" . //Add 6 line feed 
                                        "\xA\x6" . //Add 6 line feed 
                                        "\x1B\x69"; //cut ticket 

                                // var_dump($label);

                                // Check if printerIP is 'USB' to print via local printer
                                if (strtoupper($printerIP) === 'USB') {

                                        $printerName = Config::$USB_printer_name; // Replace with your printer name

                                        $tmpFile = tempnam(sys_get_temp_dir(), 'print');
                                        file_put_contents($tmpFile, $label);

                                        $cmd = 'copy /b "' . $tmpFile . '" "\\\\localhost\\' . $printerName . '"';
                                        exec($cmd, $output, $result);
                                        unlink($tmpFile);
                                        if ($result !== 0) {
                                                echo json_encode(array("state" => "f", "message" => "exec() print USB failed" . " " . __FUNCTION__));
                                        }
                                        //if printer is configured to print via IP         
                                } else {

                                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

                                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
                                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));

                                        if ($socket === FALSE) {
                                                echo json_encode(array("state" => "f", "message" => "socket_create() failed" . " " . __FUNCTION__));
                                                addTrace("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                                        }

                                        $result = socket_connect($socket, $printerIP, $printerPort);
                                        if ($result === FALSE) {
                                                echo json_encode(array("state" => "f", "message" => "Printer_connect() failed" . " " . __FUNCTION__));
                                                addTrace("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
                                        }
                                        $sWrite = socket_write($socket, $label, strlen((string)$label));
                                        if ($sWrite === FALSE) {
                                                addTrace("socket_write() failed. Reason: " . socket_strerror(socket_last_error($socket)));
                                                exit;
                                        } else {
                                                socket_close($socket);
                                        }
                                }
                                usleep(Config::$printerDelay); // Wait for the printer to process the label (0.7 seconds)
                        }
                }
        }
}


function printClientLabel($totalPrice, $totalVat, $totalTtc, $place, $subOrders, $company, $printer, $vatRate = null,)
{
        // $company_id = $_SESSION['company_id'];
        // $printClient = JsonLicence::getLicence($company_id, false)[0][Licence::$col_printClient];

        if (Config::$printClient) {

                $langaugeCode = $printer[0][Printer::$col_printerProtocole];

                if (Config::$printArabicRecipe) {
                        $isArabic = true;
                } else {
                        $isArabic = false;
                }
                $label = getClientLabelByLanguage($totalPrice, $totalVat, $totalTtc, $place, $subOrders, $company, $langaugeCode, $isArabic, $vatRate);

                // Check if printerIP is 'USB' or 'LPT' to print via local printer
                $printerIP = $printer[0][Printer::$col_printerIP];

                // Check if printerIP is 'USB' to print via local printer
                if (strtoupper($printerIP) === 'USB') {

                        // print_r("USB or LPT printer detected");
                        // Use Windows printer name (e.g., "Receipt Printer")
                        $printerName = Config::$USB_printer_name; // Replace with your printer name

                        $tmpFile = tempnam(sys_get_temp_dir(), 'print');
                        file_put_contents($tmpFile, $label);

                        $cmd = 'copy /b "' . $tmpFile . '" "\\\\localhost\\' . $printerName . '"';
                        exec($cmd, $output, $result);
                        unlink($tmpFile);
                        if ($result !== 0) {
                                echo json_encode(array("state" => "f", "message" => "exec() print USB failed" . " " . __FUNCTION__));
                        }
                        //if printer is configured to print via IP
                } else {
                        // Existing network printing logic
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
                        if ($socket === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "socket_create() failed" . " " . __FUNCTION__));
                                addTrace("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                        }
                        $result = socket_connect($socket, $printer[0][Printer::$col_printerIP], $printer[0][Printer::$col_printerPort]);
                        if ($result === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "Printer_connect() failed" . " " . __FUNCTION__));
                                addTrace("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
                        }
                        $sWrite = socket_write($socket, $label, strlen((string)$label));
                        if ($sWrite === FALSE) {
                                addTrace("socket_write() failed. Reason: " . socket_strerror(socket_last_error($socket)));
                                exit;
                        } else {
                                socket_close($socket);
                        }

                        usleep(Config::$printerDelay); // Wait for the printer to process the label (0.7 seconds)
                }
        }
}

//this function print sales label, used in _report_io.php
// Update the printSalesLabel function to use the pagination correctly
function printSalesLabel($subOrders, $printer, $startDate, $endDate)
{
        $company = JsonCompany::getCompanyById($_SESSION["company_id"], false);
        $langaugeCode = $printer[0][Printer::$col_printerProtocole];
        $isArabic = Config::$printArabicRecipe ?? false;

        // Calculate pagination
        $itemsPerPage = 25; // Number of items per page
        $totalItems = count((array) $subOrders);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Process and print each page
        for ($page = 1; $page <= $totalPages; $page++) {

                // Get subset of items for this page
                $startIndex = ($page - 1) * $itemsPerPage;
                $pageItems = array_slice((array) $subOrders, $startIndex, $itemsPerPage);

                // Generate label for this page
                $label = getSalesLabelByLanguage($pageItems, $startDate, $endDate, $company, $langaugeCode, $isArabic, $page, $totalPages);

                // Print logic (USB or network printer)
                if (strtoupper($printer[0][Printer::$col_printerIP]) === 'USB') {
                        $printerName = Config::$USB_printer_name;
                        $tmpFile = tempnam(sys_get_temp_dir(), 'print');
                        file_put_contents($tmpFile, $label);
                        $cmd = 'copy /b "' . $tmpFile . '" "\\\\localhost\\' . $printerName . '"';
                        exec($cmd, $output, $result);
                        unlink($tmpFile);
                        if ($result !== 0) {
                                echo json_encode(array("state" => "f", "message" => "exec() print USB failed" . " " . __FUNCTION__));
                        }
                } else {
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));

                        if ($socket === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "socket_create() failed" . " " . __FUNCTION__));
                                addTrace("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                        }

                        $result = socket_connect($socket, $printer[0][Printer::$col_printerIP], $printer[0][Printer::$col_printerPort]);
                        if ($result === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "Printer_connect() failed" . " " . __FUNCTION__));
                                addTrace("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
                        }
                        $sWrite = socket_write($socket, $label, strlen((string)$label));
                        if ($sWrite === FALSE) {
                                addTrace("socket_write() failed. Reason: " . socket_strerror(socket_last_error($socket)));
                                break;
                        } else {
                                socket_close($socket);
                        }
                }

                // Wait briefly between prints to allow the printer to process
                if ($page < $totalPages) {
                        usleep(Config::$printerDelay); // Wait for the printer to process the label (0.5 seconds)
                }
        }
}
//this function print Expenses label, used in _report_io.php
// Update the printChargesLabel function to use the pagination correctly
function printChargesLabel($charges, $printer, $startDate, $endDate, $cashOnly = false)
{
        $company = JsonCompany::getCompanyById($_SESSION["company_id"], false);
        $langaugeCode = $printer[0][Printer::$col_printerProtocole];
        $isArabic = Config::$printArabicRecipe ?? false;

        $itemsPerPage = 25;
        $totalItems = count((array) $charges);
        $totalPages = ceil($totalItems / $itemsPerPage);

        for ($page = 1; $page <= $totalPages; $page++) {
                $startIndex = ($page - 1) * $itemsPerPage;
                $pageItems = array_slice((array) $charges, $startIndex, $itemsPerPage);

                $label = getChargesLabelByLanguage($pageItems, $charges, $startDate, $endDate, $company, $langaugeCode, $isArabic, $cashOnly, $page, $totalPages);

                if (strtoupper($printer[0][Printer::$col_printerIP]) === 'USB') {
                        $printerName = Config::$USB_printer_name;
                        $tmpFile = tempnam(sys_get_temp_dir(), 'print');
                        file_put_contents($tmpFile, $label);
                        $cmd = 'copy /b "' . $tmpFile . '" "\\\\localhost\\' . $printerName . '"';
                        exec($cmd, $output, $result);
                        unlink($tmpFile);
                        if ($result !== 0) {
                                echo json_encode(array("state" => "f", "message" => "exec() print USB failed" . " " . __FUNCTION__));
                        }
                } else {
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));

                        if ($socket === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "socket_create() failed" . " " . __FUNCTION__));
                                addTrace("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                        }

                        $result = socket_connect($socket, $printer[0][Printer::$col_printerIP], $printer[0][Printer::$col_printerPort]);
                        if ($result === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "Printer_connect() failed" . " " . __FUNCTION__));
                                addTrace("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
                        }

                        $sWrite = socket_write($socket, $label, strlen((string)$label));
                        if ($sWrite === FALSE) {
                                addTrace("socket_write() failed. Reason: " . socket_strerror(socket_last_error($socket)));
                                break;
                        } else {
                                socket_close($socket);
                        }
                }

                if ($page < $totalPages) {
                        usleep(Config::$printerDelay);
                }
        }
}
function printAllPrices($ObjectsPrices, $printer)
{
        $company = JsonCompany::getCompanyById($_SESSION["company_id"], false);
        $langaugeCode = $printer[0][Printer::$col_printerProtocole]; //Printer language code
        $isArabic = Config::$printArabicRecipe ?? false;

        // Calculate pagination
        $itemsPerPage = 25;
        $totalItems = count((array) $ObjectsPrices);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Process and print each page
        for ($page = 1; $page <= $totalPages; $page++) {
                // Get subset of items for this page
                $startIndex = ($page - 1) * $itemsPerPage;
                $pageItems = array_slice((array) $ObjectsPrices, $startIndex, $itemsPerPage);

                // Generate label for this page
                $label = getAllPricesLabelByLanguage($pageItems, $company, $langaugeCode, $isArabic, $page, $totalPages);

                // Print logic (USB or network printer)
                if (strtoupper($printer[0][Printer::$col_printerIP]) === 'USB') {
                        $printerName = Config::$USB_printer_name;
                        $tmpFile = tempnam(sys_get_temp_dir(), 'print');
                        file_put_contents($tmpFile, $label);
                        $cmd = 'copy /b "' . $tmpFile . '" "\\\\localhost\\' . $printerName . '"';
                        exec($cmd, $output, $result);
                        unlink($tmpFile);
                        if ($result !== 0) {
                                echo json_encode(array("state" => "f", "message" => "exec() print USB failed" . " " . __FUNCTION__));
                        }
                } else {
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));

                        if ($socket === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "socket_create() failed" . " " . __FUNCTION__));
                                addTrace("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                                break;
                        }

                        $result = socket_connect($socket, $printer[0][Printer::$col_printerIP], $printer[0][Printer::$col_printerPort]);
                        if ($result === FALSE) {
                                echo json_encode(array("state" => "f", "message" => "Printer_connect() failed" . " " . __FUNCTION__));
                                addTrace("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
                                break;
                        }

                        $sWrite = socket_write($socket, $label, strlen((string)$label));
                        if ($sWrite === FALSE) {
                                addTrace("socket_write() failed. Reason: " . socket_strerror(socket_last_error($socket)));
                                break;
                        } else {
                                socket_close($socket);
                        }
                }

                // Wait briefly between prints to allow the printer to process
                if ($page < $totalPages) {
                        usleep(Config::$printerDelay); // Wait for the printer to process the label (0.7 seconds)
                }
        }
}

//This function return the label format depending on printer programming code
function getChefLabelByLanguage($place, $copies, $article, $attributes, $supplements, $obs, $printer, $isArabic = false)
{
        $langaugeCode = $printer[0][Printer::$col_printerProtocole]; //Printer language code
        $labelSize = $printer[0][Printer::$col_labelSize]; //Printer language code
        $label = '';

        $arabic = new Arabic('Glyphs');
        // $article = "1 " . $article;
        switch ($langaugeCode) {

                //If label printer support TSPL (Taiwan Semiconductor Printing/Programming Language)
                //like (Gprinters, IT POS,..)
                case "TSPL":
                        switch ($labelSize) {
                                case Config::$label_40_20:
                                        // Remove "R-P " prefix from place
                                        $place40 = preg_replace('/^R-P\s*/i', '', $place);

                                        // Shrink each word of article to first 4 chars
                                        $article40 = implode(' ', array_map(fn($w) => mb_substr($w, 0, 4), explode(' ', $article)));

                                        $label =
                                                "SIZE 40 mm, 20 mm\n" . //Specify label size width hight
                                                "GAP 2 mm, 0 mm\n" .     // Specify space between two labels
                                                "DIRECTION 1\n" .       //Specify the printing direction (up to down or down to up)
                                                "REFERENCE 0,0\n" .      // specify the initial label starting coordinates  
                                                "CLS\n" .                 // clears the image buffer
                                                "TEXT 10,5,\"1\",0,2,4," . "\"$place40\"" . "\n" . //TEXT X(dots), Y(dots), "font", rotation, x-multiplication, y-multiplication, "content"
                                                "TEXT 2,60,\"4\",0,1,1," . "\"$article40\"" . "\n" . //(1 mm = 8 dots) print 1 article
                                                "TEXT 5,100,\"3\",0,1,1," . "\"$attributes\"" . "\n" .
                                                "TEXT 5,120,\"2\",0,1,1," . "\"$supplements\"" . "\n" .
                                                "PRINT 1," . $copies . "\n";      // Print x,y with one copies  (quantity of suborder)
                                        break;
                                case Config::$label_50_30:
                                        $label =
                                                "SIZE 50 mm, 30 mm\n" . //Specify label size width hight
                                                "GAP 2 mm, 0 mm\n" .     // Specify space between two labels
                                                "DIRECTION 1\n" .       //Specify the printing direction (up to down or down to up)
                                                "REFERENCE 0,0\n" .      // specify the initial label starting coordinates  
                                                "CLS\n" .                 // clears the image buffer
                                                "TEXT 5,28,\"3\",0,1,1," . "\"$place\"" . "\n" . //TEXT X(dots), Y(dots), "font", rotation, x-multiplication, y-multiplication, "content"
                                                "TEXT 1,80,\"4\",0,1,1," . "\"$article\"" . "\n" . //(1 mm = 8 dots) print 1 article
                                                "TEXT 5,125,\"4\",0,1,1," . "\"$attributes\"" . "\n" .
                                                "TEXT 5,170,\"3\",0,1,1," . "\"$supplements\"" . "\n" .
                                                "TEXT 5,200,\"3\",0,1,1," . "\"$obs\"" . "\n" .
                                                "PRINT 1," . $copies . "\n";      // Print x,y with one copies  (quantity of suborder)
                                        break;
                                case Config::$label_60_40:
                                        $label =
                                                "SIZE 60 mm, 30 mm\n" . //Specify label size width hight
                                                "GAP 2 mm, 0 mm\n" .     // Specify space between two labels
                                                "DIRECTION 1\n" .       //Specify the printing direction (up to down or down to up)
                                                "REFERENCE 0,0\n" .      // specify the initial label starting coordinates  
                                                "CLS\n" .                 // clears the image buffer
                                                "TEXT 1,5,\"4\",0,1,1," . "\"$place\"" . "\n" . //TEXT X(dots), Y(dots), "font", rotation, x-multiplication, y-multiplication, "content"
                                                "TEXT 1,70,\"3\",0,2,2," . "\"$article\"" . "\n" . //(1 mm = 8 dots) print 1 article
                                                "TEXT 5,150,\"3\",0,2,2," . "\"$attributes\"" . "\n" .
                                                "TEXT 120,175,\"3\",0,1,1," . "\"$supplements\"" . "\n" .
                                                "TEXT 5,215,\"3\",0,1,1," . "\"$obs\"" . "\n" .
                                                "PRINT 1," . $copies . "\n";      // Print x,y with one copies  (quantity of suborder)
                                        break;
                        }
                        break;

                //If label printer support EZPL (E Zebra Programming languag)
                //like (Godex,..)
                case "EZPL":
                        switch ($labelSize) {
                                case Config::$label_40_20:

                                        $place40 = preg_replace('/^R-P\s*/i', '', $place);

                                        // Shrink each word of article to first 4 chars
                                        $article40 = implode(' ', array_map(fn($w) => mb_substr($w, 0, 4), explode(' ', $article)));

                                        $label =
                                                "^C" . $copies . "\n" . //$copies  is the quantity of suborder
                                                "^Q20\n" .
                                                "^W40\n" .
                                                "^L\n" .
                                                "AT,30,5,50,40,0,0,0,0," . $place40 . "\n" .
                                                "AC,5,38,1,2,0,0," . $article40 . "\n" .
                                                "AC,100,95,1,1,1,0," . $attributes . "\n" .
                                                "AT,14,130,25,25,0,0,0,0," . $supplements . "\n" .
                                                "E\n";
                                        break;
                        }
                        break;
                case "ESC":
                        $article = "\"$copies\"" . "  " . $article;
                        if ($isArabic) {

                                $search = ['Take Away', 'Emporter'];
                                $replace = ['محمول', 'محمول',];
                                $place = str_replace($search, $replace, $place);

                                $dateTime = date(' H:i Y-m-d');
                                $textDirection = 'right';
                        } else {
                                $dateTime = date('d-m-Y H:i');
                                $textDirection = 'left';
                        }

                        $fontPath = dirname(__DIR__) . '/fonts/arial.ttf';
                        $textMaxWidth = 550;

                        $titleText = wrapEscPosText(
                                $arabic->utf8Glyphs($article . " " . $attributes, 50, false),
                                $fontPath,
                                36,
                                $textMaxWidth
                        );

                        if ($supplements != '') {
                                $supplementsESC = '';
                                $suppLines = array_filter(explode("\n", $supplements), fn($l) => trim($l) !== '');
                                foreach ($suppLines as $suppLine) {
                                        $suppText = wrapEscPosText(
                                                $arabic->utf8Glyphs(trim($suppLine), 50, false),
                                                $fontPath,
                                                26,
                                                $textMaxWidth
                                        );
                                        $supplementsESC .= EscPosImage($suppText, 26, $textDirection) . "";
                                }
                        } else {
                                $supplementsESC = '';
                        }

                        if ($obs != '') {
                                $obsText = wrapEscPosText(
                                        $arabic->utf8Glyphs($obs, 50, false),
                                        $fontPath,
                                        26,
                                        $textMaxWidth
                                );
                                $obsESC = EscPosImage($obsText, 26, $textDirection) . "\xA";
                        } else {
                                $obsESC = '';
                        }

                        $label =
                                "\x1B\x40" . // Initializes the printer (ESC @)
                                EscPosImage($arabic->utf8Glyphs($place, 50, false), 40, 'center') . "\xA" .
                                EscPosImage($titleText, 36, $textDirection) . "\xA" .
                                $supplementsESC .
                                $obsESC  .
                                "\x1B\x61\x1" . // Specifies a centered printing position (ESC a)
                                "\x1B\x21\x2" . // Specifies font A (ESC !)
                                "\xA" .
                                "------------ " . $dateTime . " ----------" . "\xA" .
                                "\xA\x6" . //Add 6 line feed 
                                "\xA\x6" . //Add 6 line feed 
                                "\xA\x6" . //Add 6 line feed 
                                "\xA\x6" . //Add 6 line feed 
                                "\xA\x6" . //Add 6 line feed 
                                "\x1B\x69"; //cut ticket 
                        break;
        }

        return $label;
}

function getClientLabelByLanguage($totalPrice, $totalVat, $totalTtc, $place, $subOrders, $company, $langaugeCode, $isArabic, $vatRate = null)
{

        $label = '';


        // *****************Prepare Suborderes printing **************************

        $arabic = new Arabic('Glyphs');

        $subordersForImage = []; // This will be an array of arrays
        foreach ((array) $subOrders as $i => $suborder) {

                //if subOrder is supplement
                if ($suborder[Category::$col_supplement] == '1') {

                        //if supplement subOrder price is 0 we skip foreach to prevent print this suborder
                        //because it is not important
                        if ($suborder[SubOrder::$col_subTotal] == '0') {
                                continue;
                        }

                        //If SUPPLEMENT attribute value starts with non numéric char then we print it in ticket
                        //like (25cl, 1/P, 2P,.. )
                        //Otherwise we don't print it because it may concern Pizza Attributes (L,XL,XXL)
                        $subOrderAttribute = $suborder[Attribute_Value::$col_attributeValue];
                        if ($subOrderAttribute !== null) {
                                if (!is_numeric($subOrderAttribute[0]))
                                        $suborder[Attribute_Value::$col_attributeValue] = '';
                        }
                }

                $itemTitle = $arabic->utf8Glyphs($suborder[Objet::$col_title] . " " . $suborder[Attribute_Value::$col_attributeValue], 50, false);

                $resultArray =
                        [
                                $itemTitle,
                                $suborder[SubOrder::$col_quantity], //Qty
                                $suborder[SubOrder::$col_subTotal] == '0' ? '' : $suborder[SubOrder::$col_subTotal] // Sub Total
                        ];
                if ($isArabic) {
                        $subordersForImage[] = array_reverse($resultArray);
                } else {
                        $subordersForImage[] = ($resultArray);
                }
        }
        // print_r($subordersPrintESC);
        // ***************** ***************************************************

        //******************************* */ Label Data  **************************

        $rawTotalPrice = floatval($totalPrice);
        $rawTotalVat = floatval($totalVat);
        $rawTotalTtc = floatval($totalTtc);
        $rawVatRate = ($vatRate !== null && $vatRate !== '') ? floatval($vatRate) : null;

        if ($isArabic) {
                $dateTime = date('H:i Y-m-d');

                //we will trick the printer to print the total in Arabic format 
                //by reversing the order of the segments before and after the dot

                $formattedTotal = number_format($rawTotalPrice, 2, '.', '');
                // Split by the dot and manually reverse the order of the segments
                $parts = explode('.', $formattedTotal);
                $formattedTotal = $parts[1] . '.' . $parts[0]; // Becomes "00.250"

                $formattedVat = number_format($rawTotalVat, 2, '.', '');
                // Split by the dot and manually reverse the order of the segments
                $parts = explode('.', $formattedVat);
                $formattedVat = $parts[1] . '.' . $parts[0]; // Becomes "00.250"

                $formattedTtc = number_format($rawTotalTtc, 2, '.', '');
                // Split by the dot and manually reverse the order of the segments
                $parts = explode('.', $formattedTtc);
                $formattedTtc = $parts[1] . '.' . $parts[0]; // Becomes "00.250"

        } else {
                $dateTime = date('d-m-Y H:i');

                $formattedTotal = number_format($rawTotalPrice, 2, '.', ' ');
                $formattedVat = number_format($rawTotalVat, 2, '.', ' ');
                $formattedTtc = number_format($rawTotalTtc, 2, '.', ' ');
        }
        $companyName = $company[0][Company::$col_companyName];
        $address = $company[0][Company::$col_address];
        $mobile = $company[0][Company::$col_phone];

        //******************************* ***************************
        // Prepare header row and other text for the image
        //If user decide to print reciept to Arabic
        if ($isArabic) {
                $headerRow = [
                        $arabic->utf8Glyphs("السعر"),
                        $arabic->utf8Glyphs("الكمية"),
                        $arabic->utf8Glyphs("الطبق")
                ];
                $search = ['Order', 'Take Away', 'Emporter', 'Commande'];
                $replace = ['الطلبية', 'محمول', 'محمول', 'الطلبية'];
                $place = str_replace($search, $replace, $place);

                $totalText = 'المجموع بدون ض';
                $vatText = 'ر.ق.م';

                if ($rawTotalVat == 0.0) {
                        $totalTtcText = 'المجموع';
                } else {
                        $totalTtcText = 'المجموع مع ض';
                }
        } else {
                //If user decide to print reciept to Not Arabic but the UI language is Arabic
                //we set header and Total to default English
                if (Config::$cmsLanguage == 'ar') {
                        $headerRow = [
                                'Article',
                                'Qty',
                                'Price'
                        ];
                        $search = ['الطلبية', 'محمول',];
                        $replace = ['Order', 'Take Away'];
                        $place = str_replace($search, $replace, $place);

                        $totalText = 'Total';
                        $vatText = 'VAT';

                        if ($rawTotalVat == 0.0) {
                                $totalTtcText = 'Total';
                        } else {
                                $totalTtcText = 'Total + VAT';
                        }
                } else {
                        $headerRow = [
                                T('ch_menu_article'),
                                T('ch_menu_qty'),
                                T('ch_menu_price')
                        ];

                        $totalText = T('total_ht_label');
                        $vatText = T('total_tva_label');
                        if ($rawTotalVat == 0.0) {
                                $totalTtcText = T('total_ht_label');
                        } else {
                                $totalTtcText = T('total_ttc_label');
                        }
                }
        }

        // *********** Start prepare Total Block for the image ****************

        //if there is no VAT we print only one total line with total price, 
        if ($rawTotalVat == 0.0) {
                // existing single total line behavior
                $totalsBlock = EscPosImage(
                        $arabic->utf8Glyphs($totalTtcText . " : "  . $formattedTtc . " " . Config::$cmsCurrency, '50', false),
                        30,
                        'center'
                ) . "\xA";
                //if there is VAT we print 3 lines, one for total without VAT, one for VAT and one for total with VAT        
        } else {

                // include rate in VAT label if available
                $vatLabelText = $vatText;
                if ($rawVatRate !== null) {
                        $rateStr = rtrim(rtrim(number_format($rawVatRate, 2, '.', ''), '0'), '.'); // "20" or "5.5"
                        $vatLabelText .= " (" . $rateStr . "%)";
                }

                $totalsBlock =
                        EscPosImage($arabic->utf8Glyphs($totalText . " : " . $formattedTotal . " " . Config::$cmsCurrency, '50', false), 26, 'center') . "\xA" .
                        EscPosImage($arabic->utf8Glyphs($vatLabelText . " : " . $formattedVat . " " . Config::$cmsCurrency, '50', false), 26, 'center') . "\xA" .
                        EscPosImage($arabic->utf8Glyphs($totalTtcText . " : " . $formattedTtc . " " . Config::$cmsCurrency, '50', false), 30, 'center') . "\xA";
        }

        // **********End prepare Total Block for the image ****************

        switch ($langaugeCode) {
                //If Ticket printer support ESC (Epson Standard Code for printers)
                //like (Xprinter,...)
                case "ESC":

                        $label = "\x1B\x40" . // Initializes the printer (ESC @)
                                "\x1B\x61\x1" . // Specifies a centered printing position (ESC a)
                                "\x1C\x70\x01\x00" . //Print the logo
                                EscPosImage($arabic->utf8Glyphs($companyName, 50, false), 35, 'center') . "\xA" .       // Company name
                                EscPosImage($arabic->utf8Glyphs($address, 50, false), 20, 'center') . "\xA" .       // Addresse
                                $mobile . "\xA" . // Mobile and LF
                                "_________________________" . "\xA\xA" .
                                ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") . // Specifies a left or right printing position (ESC a)
                                "\x1B\x21\x1" . // Specifies font (ESC !)
                                $dateTime . "\xA" .
                                EscPosImage($arabic->utf8Glyphs($place, 50, false), 25, 'center')  .  //Print date and place (table or importer + cmd number)
                                "\x1B\x21\x0" . //fix font style for all text after this section
                                "________________________________________________" . "\xA" . //print line
                                EscPosImageColumns([$headerRow], 21, $isArabic)  .   //Print (item Qte Montant)
                                "________________________________________________" . "\xA" . //print line with LF LF
                                EscPosImageColumns($subordersForImage, 21, $isArabic) .  //Print suborders 
                                "\x1B\x61\x1" .      // Specifies a centered printing position (ESC a)
                                "_________________________" . "\xA\xA" . //print line
                                $totalsBlock .
                                "\x1Bd\x6\x1B\x69" . //Add 6 line feed and cut label
                                "\x1B\x70" . //open cash drawer
                                "\x1B\x40"; // init printer
                        break;
        }
        return $label;
        // }
}
// this function is used to prepare sales label .
// used to print report about daily sales in _report_io.php
function getSalesLabelByLanguage($subOrders, $startDate, $endDate, $company, $langaugeCode, $isArabic, $page = 1, $totalPages = 1)
{
        $label = '';
        $arabic = new Arabic('Glyphs');
        $subordersForImage = []; // This will be an array of arrays

        // Process current page items
        foreach ((array) $subOrders as $i => $suborder) {
                //if suborder attribute value is null then we set it to empty string
                $subOrderAttribute = $suborder[Attribute_Value::$col_attributeValue];
                if ($subOrderAttribute == null) {
                        $suborder[Attribute_Value::$col_attributeValue] = '';
                }

                $itemTitle = $arabic->utf8Glyphs($suborder[Objet::$col_title] . " " . $suborder[Attribute_Value::$col_attributeValue], 50, false);

                // Convert numeric values to strings before processing
                $quantity = $arabic->utf8Glyphs((string)$suborder['total_quantity'], 50, false);
                $subtotal = $arabic->utf8Glyphs((string)$suborder['total_subtotal'], 50, false);

                $resultArray = [
                        $itemTitle,
                        $quantity,
                        $subtotal
                ];

                if ($isArabic) {
                        $subordersForImage[] = array_reverse($resultArray);
                } else {
                        $subordersForImage[] = $resultArray;
                }
        }

        // Prepare header row for the image
        if ($isArabic) {
                $headerRow = [
                        $arabic->utf8Glyphs("الثمن"),
                        $arabic->utf8Glyphs("الكمية"),
                        $arabic->utf8Glyphs("العنصر")
                ];
                $labelTitle = 'تقرير المبيعات';
                $from = "من";
                $pageText = "الصفحة";
        } else {
                if (Config::$cmsLanguage == 'ar') {
                        $headerRow = [
                                'Item',
                                'Qty',
                                'Price'
                        ];
                        $labelTitle = 'Sales Report';
                        $from = "From";
                        $pageText = "Page";
                } else {
                        $headerRow = [
                                T('ch_menu_article'),
                                T('ch_menu_qty'),
                                T('ch_menu_price')
                        ];
                        $labelTitle = T('report_sales_admin_print');
                        $from = T('from');
                        $pageText = T('page');
                }
        }

        // Format date information and totals
        $rawTotal = floatval($subOrders[0]['total_of_day'] ?? 0);
        $rawTotalVat = floatval($subOrders[0]['totalVat_of_day'] ?? 0);
        $rawTotalTtc = floatval($subOrders[0]['totalTTC_of_day'] ?? 0);

        if ($isArabic) {
                $startDate = date('Y-m-d', strtotime($startDate));
                $endDate = date('Y-m-d', strtotime($endDate));
                $dateTime = date('H:i Y-m-d');

                $formattedTotal = number_format($rawTotal, 2, '.', '');
                $parts = explode('.', $formattedTotal);
                $formattedTotal = $parts[1] . '.' . $parts[0];

                $formattedVat = number_format($rawTotalVat, 2, '.', '');
                $parts = explode('.', $formattedVat);
                $formattedVat = $parts[1] . '.' . $parts[0];

                $formattedTtc = number_format($rawTotalTtc, 2, '.', '');
                $parts = explode('.', $formattedTtc);
                $formattedTtc = $parts[1] . '.' . $parts[0];

                $totalText = 'المجموع بدون ض';
                $vatText = 'ر.ق.م';
                if ($rawTotalVat == 0.0) {
                        $totalTtcText = 'المجموع';
                } else {
                        $totalTtcText = 'المجموع مع ض';
                }
        } else {
                $startDate = date('d-m-Y', strtotime($startDate));
                $endDate = date('d-m-Y', strtotime($endDate));
                $dateTime = date('d-m-Y H:i');

                $formattedTotal = number_format($rawTotal, 2, '.', ' ');
                $formattedVat = number_format($rawTotalVat, 2, '.', ' ');
                $formattedTtc = number_format($rawTotalTtc, 2, '.', ' ');

                if (Config::$cmsLanguage == 'ar') {
                        $totalText = 'Total';
                        $vatText = 'VAT';
                        if ($rawTotalVat == 0.0) {
                                $totalTtcText = 'Total';
                        } else {
                                $totalTtcText = 'Total + VAT';
                        }
                } else {
                        $totalText = T('total_ht_label');
                        $vatText = T('total_tva_label');
                        if ($rawTotalVat == 0.0) {
                                $totalTtcText = T('total_ht_label');
                        } else {
                                $totalTtcText = T('total_ttc_label');
                        }
                }
        }

        $report_dates = $startDate == $endDate ? $startDate : $startDate . " - " . $endDate;
        $companyName = $company[0][Company::$col_companyName];

        // Add pagination info
        $pageInfo = $arabic->utf8Glyphs(sprintf($pageText . " %d " . $from . " %d", $page, $totalPages), 50, false);

        // Prepare totals block like client ticket
        if ($rawTotalVat == 0.0) {
                $totalsBlock = EscPosImage(
                        $arabic->utf8Glyphs($totalTtcText . " : " . $formattedTtc . " " . Config::$cmsCurrency, '50', false),
                        30,
                        'center'
                ) . "\xA";
        } else {
                $totalsBlock =
                        EscPosImage($arabic->utf8Glyphs($totalText . " : " . $formattedTotal . " " . Config::$cmsCurrency, '50', false), 26, 'center') . "\xA" .
                        EscPosImage($arabic->utf8Glyphs($vatText . " : " . $formattedVat . " " . Config::$cmsCurrency, '50', false), 26, 'center') . "\xA" .
                        EscPosImage($arabic->utf8Glyphs($totalTtcText . " : " . $formattedTtc . " " . Config::$cmsCurrency, '50', false), 30, 'center') . "\xA";
        }

        // Only include header and logo on first page
        if ($page == 1) {
                $label .= "\x1B\x40" .                   // Initialize printer
                        "\x1B\x61\x1" .                 // Center align
                        "\x1C\x70\x01\x00" .            // Print logo
                        EscPosImage($arabic->utf8Glyphs($companyName, 50, false), 40, 'center') . "\xA" .       // Company name
                        EscPosImage($arabic->utf8Glyphs(T('report_sales_admin_print')) . "\n" . $report_dates, 23, 'center') .
                        "\x1B\x21\x0" .
                        "_________________________" . "\xA\xA" .
                        ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") .                 // Align right
                        "\x1B\x21\x1" .                 // Font style
                        $dateTime . "\xA";       // Company name

        } else {
                // For continuation pages, just initialize the printer and add a page header
                $label .=  "\x1B\x40" .                   // Initialize printer 
                        EscPosImage($arabic->utf8Glyphs($labelTitle) . "\n" . $report_dates, 23, 'center') .
                        "\x1B\x21\x0" .
                        "\x1B\x61\x1" .                 // Center align
                        "_________________________" . "\xA\xA" .
                        ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") .                 // Align right
                        "\x1B\x21\x1" .                 // Font style
                        $dateTime . "\xA";
        }

        // Always include column headers and data
        $label .= "\x1B\x21\x0" .
                "________________________________________________" . "\xA" .
                EscPosImageColumns([$headerRow], 21, $isArabic) .
                "________________________________________________" . "\xA" .
                EscPosImageColumns($subordersForImage, 21, $isArabic);

        // Only include totals on the last page
        if ($page == $totalPages) {
                $label .= "\x1B\x61\x1" .                 // Center align
                        "_________________________" . "\xA" .
                        $totalsBlock;
        } else {
                // For non-last pages, just add a "Continued..." message
                $label .= "\x1B\x61\x1" .                 // Center align
                        "_________________________" . "\xA" .
                        EscPosImage($pageInfo, 20, 'center') . "\xA";
        }

        // Always include the page cut command
        $label .= "\x1B\x64\x06" .                // 6 line feeds (correct command)
                "\x1B\x69" .                    // Cut paper
                "\x1B\x40";                     // Reset printer

        return $label;
}
// this function is used to prepare charges label to use it in printChargesLabel()
// used to print report about daily charges in _report_io.php
function getChargesLabelByLanguage($pageCharges, $allCharges, $startDate, $endDate, $company, $langaugeCode, $isArabic, $cashOnly = false, $page = 1, $totalPages = 1)
{
        $label = '';
        $arabic = new Arabic('Glyphs');
        $fontPath = dirname(__DIR__) . '/fonts/arial.ttf';
        $textMaxWidth = 540;
        $textDirection = $isArabic ? 'right' : 'left';
        $chargesBlock = '';

        $rawTotal = 0.0;
        foreach ((array) $allCharges as $charge) {
                $rawTotal += (float) $charge[Charge::$col_amount];
        }

        foreach ((array) $pageCharges as $charge) {
                $typeCharge = trim((string) ($charge[Type_Charge::$col_typeCharge] ?? ''));
                $dateValue = date($isArabic ? 'Y-m-d' : 'd-m-Y', strtotime($charge[Charge::$col_dateTime]));
                $amountValue = number_format((float) $charge[Charge::$col_amount], 2, '.', ' ') . ' ' . Config::$cmsCurrency;
                $observationValue = trim((string) ($charge[Charge::$col_observation] ?? ''));

                $lineText = $typeCharge . ' | ' . $dateValue . ' | ' . $amountValue;

                if ($observationValue !== '') {
                        $lineText .= ' | ' . T('observation_label') . ': ' . $observationValue;
                }

                $wrappedLine = wrapEscPosText(
                        $arabic->utf8Glyphs($lineText, 50, false),
                        $fontPath,
                        22,
                        $textMaxWidth
                );

                $chargesBlock .= EscPosImage($wrappedLine, 22, $textDirection) . "";
                $chargesBlock .= "\x1B\x61\x1" . "________________________" . "\xA";
        }

        if ($isArabic) {
                $labelTitle = $cashOnly ? 'تقرير مصاريف الصندوق' : 'تقرير المصاريف';
                $from = "من";
                $pageText = "الصفحة";

                $startDate = date('Y-m-d', strtotime($startDate));
                $endDate = date('Y-m-d', strtotime($endDate));
                $dateTime = date('H:i Y-m-d');

                $formattedTotal = number_format($rawTotal, 2, '.', '');
                $parts = explode('.', $formattedTotal);
                $formattedTotal = $parts[1] . '.' . $parts[0];

                $totalText = 'المجموع';
        } else {
                $labelTitle = T('report_expenses_admin_print');
                if ($cashOnly) {
                        $labelTitle .= ' - ' . T('report_io_cash');
                }

                $from = T('from');
                $pageText = T('page');

                $startDate = date('d-m-Y', strtotime($startDate));
                $endDate = date('d-m-Y', strtotime($endDate));
                $dateTime = date('d-m-Y H:i');

                $formattedTotal = number_format($rawTotal, 2, '.', ' ');
                $totalText = T('report_io_total');
        }

        $report_dates = $startDate == $endDate ? $startDate : $startDate . " - " . $endDate;
        $companyName = $company[0][Company::$col_companyName];
        $pageInfo = $arabic->utf8Glyphs(sprintf($pageText . " %d " . $from . " %d", $page, $totalPages), 50, false);

        $totalsBlock = EscPosImage(
                $arabic->utf8Glyphs($totalText . " : " . $formattedTotal . " " . Config::$cmsCurrency, 50, false),
                30,
                'center'
        ) . "\xA";

        if ($page == 1) {
                $label .= "\x1B\x40" .
                        "\x1B\x61\x1" .
                        "\x1C\x70\x01\x00" .
                        EscPosImage($arabic->utf8Glyphs($companyName, 50, false), 40, 'center') . "\xA" .
                        EscPosImage($arabic->utf8Glyphs($labelTitle, 50, false) . "\n" . $report_dates, 23, 'center') .
                        "\x1B\x21\x0" .
                        "_________________________" . "\xA\xA" .
                        ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") .
                        "\x1B\x21\x1" .
                        $dateTime . "\xA";
        } else {
                $label .= "\x1B\x40" .
                        EscPosImage($arabic->utf8Glyphs($labelTitle, 50, false) . "\n" . $report_dates, 23, 'center') .
                        "\x1B\x21\x0" .
                        "\x1B\x61\x1" .
                        "_________________________" . "\xA\xA" .
                        ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") .
                        "\x1B\x21\x1" .
                        $dateTime . "\xA";
        }

        $label .= "\x1B\x21\x0" .
                "________________________________________________" . "\xA" .
                $chargesBlock;

        if ($page == $totalPages) {
                $label .= "\x1B\x61\x1" .
                        "_________________________" . "\xA" .
                        $totalsBlock;
        } else {
                $label .= "\x1B\x61\x1" .
                        "_________________________" . "\xA" .
                        EscPosImage($pageInfo, 20, 'center') . "\xA";
        }

        $label .= "\x1B\x64\x06" .
                "\x1B\x69" .
                "\x1B\x40";

        return $label;
}

function getAllPricesLabelByLanguage($ObjectsPrices, $company, $langaugeCode, $isArabic, $page = 1, $totalPages = 1)
{
        $label = '';
        $arabic = new Arabic('Glyphs');
        $subordersForImage = []; // This will be an array of arrays

        foreach ((array) $ObjectsPrices as $i => $ObjectPrice) {
                $itemTitle = $arabic->utf8Glyphs($ObjectPrice[Objet::$col_title], 50, false);
                $itemAttribute = $arabic->utf8Glyphs($ObjectPrice[Attribute_Value::$col_attributeValue], 50, false);
                $price = $arabic->utf8Glyphs($ObjectPrice['price'], 50, false);

                $resultArray = [
                        $itemTitle, //Item
                        $itemAttribute, //attribute name
                        $price  // price
                ];

                if ($isArabic) {
                        $subordersForImage[] = array_reverse($resultArray);
                } else {
                        $subordersForImage[] = ($resultArray);
                }
        }

        // Prepare header row for the image
        if ($isArabic) {
                $headerRow = [
                        $arabic->utf8Glyphs("السعر"),
                        $arabic->utf8Glyphs("الخاصية"),
                        $arabic->utf8Glyphs("العنصر")
                ];
                $labelTitle = 'تقرير الأسعار';
                $pageText = "الصفحة";
                $from = "من";
        } else {
                if (Config::$cmsLanguage == 'ar') {
                        $headerRow = [
                                'Item',
                                'Attribute',
                                'Price'
                        ];
                        $labelTitle = 'Print Sale Prices';
                        $pageText = "Page";
                        $from = "from";
                } else {
                        $headerRow = [
                                T('ch_menu_article'),
                                T('sales_category_attr'),
                                T('ch_menu_price')
                        ];
                        $labelTitle = T('sale_price_print');
                        $pageText = T('page');
                        $from = T('from');
                }
        }

        // Format date information
        if ($isArabic) {
                $dateTime = date('H:i Y-m-d');
        } else {
                $dateTime = date('d-m-Y H:i');
        }

        $companyName = $company[0][Company::$col_companyName];

        // Add pagination info
        $pageInfo = $arabic->utf8Glyphs(sprintf($pageText . " %d " . $from . " %d", $page, $totalPages), 50, false);

        // Only include header and logo on first page
        if ($page == 1) {
                $label .= "\x1B\x40" .                   // Initialize printer
                        "\x1B\x61\x1" .                 // Center align
                        "\x1C\x70\x01\x00" .            // Print logo
                        EscPosImage($arabic->utf8Glyphs($companyName, 50, false), 40, 'center') . "\xA" .       // Company name
                        EscPosImage($arabic->utf8Glyphs($labelTitle, 50, false), 23, 'center')  .
                        "\x1B\x21\x0" .
                        "_________________________" . "\xA\xA" .
                        ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") .   // Align right
                        "\x1B\x21\x1" . // Specifies font (ESC !)
                        $dateTime . "\xA";
        } else {
                // For continuation pages, just initialize the printer and add a page header
                $label .= "\x1B\x40" .                   // Initialize printer
                        EscPosImage($arabic->utf8Glyphs($labelTitle), 23, 'center')  .
                        "\x1B\x21\x0" .
                        "\x1B\x61\x1" .                 // Center align
                        "_________________________" . "\xA\xA" .
                        ($isArabic ? "\x1B\x61\x2" : "\x1B\x61\x0") .   // Align right
                        "\x1B\x21\x1" . // Specifies font (ESC !)
                        $dateTime . "\xA";
        }

        // Always include column headers and data
        $label .= "\x1B\x21\x0" . //fix font style for all text after this section
                "________________________________________________" . "\xA" . //print line
                EscPosImageColumns([$headerRow], 21, $isArabic)  .
                "________________________________________________" . "\xA" . //print line with LF LF
                EscPosImageColumns($subordersForImage, 21, $isArabic);

        // Add pagination footer
        if ($page < $totalPages) {
                // For non-last pages, add a "Continued..." message
                $label .= "\x1B\x61\x1" .                 // Center align
                        "_________________________" . "\xA" .
                        EscPosImage($pageInfo, 20, 'center') . "\xA";
        }

        // Always include the page cut command
        $label .= "\x1B\x61\x1" .      // Specifies a centered printing position (ESC a)
                "_________________________" . "\xA" . //print line
                "\x1B\x64\x06" .                // 6 line feeds (correct command)
                "\x1B\x69" .                    // Cut paper
                "\x1B\x40";                     // Reset printer

        return $label;
}

// this function is used to convert suborders lines to arabic text to esc pos image
function EscPosImageColumns($lines, $fontSize = 28, $isArabic = false)
{
        $fontPath = dirname(__DIR__) . '/fonts/arial.ttf';

        if (!file_exists($fontPath)) {
                return "Font file not found";
        }

        $padding = 10;
        $lineHeight = $fontSize + 12;
        $width = 570;

        if ($isArabic) {
                $columnWidths = [20, 20, 60];
        } else {
                $columnWidths = [60, 20, 20];
        }

        $col1Width = (int) round($width * ($columnWidths[0] / 100));
        $col2Width = (int) round($width * ($columnWidths[1] / 100));
        $col3Width = $width - $col1Width - $col2Width;

        $preparedRows = [];
        $contentHeight = 0;

        foreach ((array)$lines as $columns) {
                $itemText = (string)($columns[0] ?? '');
                $qtyText = (string)($columns[1] ?? '');
                $priceText = (string)($columns[2] ?? '');

                $itemMaxWidth = max(10, $col1Width - (2 * $padding));
                $itemLines = wrapTextToColumnWidth($itemText, $fontPath, $fontSize, $itemMaxWidth);
                $rowHeight = max(count($itemLines), 1) * $lineHeight;

                $preparedRows[] = [
                        'itemLines' => $itemLines,
                        'qtyText' => $qtyText,
                        'priceText' => $priceText,
                        'rowHeight' => $rowHeight,
                ];

                $contentHeight += $rowHeight;
        }

        $height = max($contentHeight + (2 * $padding), $lineHeight + (2 * $padding));

        $im = imagecreatetruecolor($width, $height);
        if (!($im instanceof GdImage)) {
                throw new RuntimeException('GD failed to create image (imagecreatetruecolor)');
        }

        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);

        $currentY = $padding;

        foreach ($preparedRows as $row) {
                $rowTop = $currentY;
                $rowBaseline = $rowTop + $lineHeight;

                foreach ($row['itemLines'] as $lineIndex => $itemLine) {
                        $itemY = $rowTop + (($lineIndex + 1) * $lineHeight);
                        imagettftext($im, $fontSize, 0, $padding, $itemY, $black, $fontPath, $itemLine);
                }

                $qtyText = $row['qtyText'];
                $qtyColX = $col1Width;
                $bboxQty = imagettfbbox($fontSize, 0, $fontPath, $qtyText);
                $qtyTextWidth = abs($bboxQty[2] - $bboxQty[0]);
                $qtyX = (int) round($qtyColX + (($col2Width - $qtyTextWidth) / 2));
                imagettftext($im, $fontSize, 0, $qtyX, $rowBaseline, $black, $fontPath, $qtyText);

                $priceText = $row['priceText'];
                $priceColX = $col1Width + $col2Width;
                $bboxPrice = imagettfbbox($fontSize, 0, $fontPath, $priceText);
                $priceTextWidth = abs($bboxPrice[2] - $bboxPrice[0]);
                $priceX = (int) round($priceColX + $col3Width - $priceTextWidth - $padding);
                imagettftext($im, $fontSize, 0, $priceX, $rowBaseline, $black, $fontPath, $priceText);

                $currentY += $row['rowHeight'];
        }

        imagefilter($im, IMG_FILTER_GRAYSCALE);
        imagefilter($im, IMG_FILTER_CONTRAST, -100);

        $bytes = "\x1D\x76\x30\x00";
        $widthPx = imagesx($im);
        $heightPx = imagesy($im);
        $widthBytes = intval(($widthPx + 7) / 8);

        $bytes .= pack('v', $widthBytes);
        $bytes .= pack('v', $heightPx);

        for ($y = 0; $y < $heightPx; $y++) {
                for ($x = 0; $x < $widthBytes * 8; $x += 8) {
                        $byte = 0;
                        for ($b = 0; $b < 8; $b++) {
                                $px = $x + $b;
                                if ($px < $widthPx) {
                                        $gray = imagecolorat($im, $px, $y) & 0xFF;
                                        if ($gray < 128) {
                                                $byte |= (1 << (7 - $b));
                                        }
                                }
                        }
                        $bytes .= chr($byte);
                }
        }

        // imagedestroy($im); deprecated in PHP 8.0 and later, resources are automatically freed at the end of the script
        return $bytes;
}

// This function is used to wrap text to fit within a specified column width when 
//generating ESC/POS images.
function wrapTextToColumnWidth($text, $fontPath, $fontSize, $maxWidth)
{
        $text = trim((string)$text);
        if ($text === '') {
                return [''];
        }

        $words = preg_split('/\s+/u', $text) ?: [];
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
                $candidate = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $candidate);
                $candidateWidth = abs($bbox[2] - $bbox[0]);

                if ($candidateWidth <= $maxWidth) {
                        $currentLine = $candidate;
                        continue;
                }

                if ($currentLine !== '') {
                        $lines[] = $currentLine;
                }

                $currentLine = $word;

                $bbox = imagettfbbox($fontSize, 0, $fontPath, $currentLine);
                $wordWidth = abs($bbox[2] - $bbox[0]);

                if ($wordWidth <= $maxWidth) {
                        continue;
                }

                $characters = preg_split('//u', $currentLine, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $chunk = '';

                foreach ($characters as $char) {
                        $candidateChunk = $chunk . $char;
                        $bbox = imagettfbbox($fontSize, 0, $fontPath, $candidateChunk);
                        $chunkWidth = abs($bbox[2] - $bbox[0]);

                        if ($chunkWidth <= $maxWidth) {
                                $chunk = $candidateChunk;
                        } else {
                                if ($chunk !== '') {
                                        $lines[] = $chunk;
                                }
                                $chunk = $char;
                        }
                }

                $currentLine = $chunk;
        }

        if ($currentLine !== '') {
                $lines[] = $currentLine;
        }

        return $lines === [] ? [''] : $lines;
}
// This function is a helper that wraps text and then joins the wrapped lines with newlines,
function wrapEscPosText($text, $fontPath, $fontSize, $maxWidth)
{
        $wrappedLines = wrapTextToColumnWidth($text, $fontPath, $fontSize, $maxWidth);
        return implode("\n", $wrappedLines);
}

// This function converts Any text to an ESC/POS image format.
function EscPosImage($text, $fontSize = 28, $align = 'center')
{
        $fontPath = dirname(__DIR__) . '/fonts/arial.ttf'; // Make sure this path is valid

        if (!file_exists($fontPath)) {
                die("Font file not found at $fontPath");
        }

        // // ---- Basic Arabic shaping and RTL simulation ----
        // $arabic = new Arabic('Glyphs');

        // $text = $arabic->utf8Glyphs($text,50,false);


        $lines = explode("\n", (string)$text);
        $padding = 10;
        $lineHeight = $fontSize + 12;
        $height = ((is_countable($lines) ? count($lines) : 0) * $lineHeight) + 2 * $padding;
        $width = 570; // Approx. 72mm paper width for 80mm printer

        $im = imagecreatetruecolor($width, $height);
        // PHP 8+: ensure GdImage object
        if (!($im instanceof GdImage)) {
                throw new RuntimeException('GD failed to create image (imagecreatetruecolor)');
        }
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);

        foreach ($lines as $i => $line) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $textWidth = abs($bbox[2] - $bbox[0]);

                if ($align === 'center') {
                        $x = (int) round(($width - $textWidth) / 2);
                } elseif ($align === 'right') {
                        $x = (int) round($width - $textWidth - $padding);
                } else {
                        $x = (int) $padding;
                }

                $y = (int) round($padding + ($i + 1) * $lineHeight);
                imagettftext($im, $fontSize, 0, $x, $y, $black, $fontPath, $line);
        }

        // Convert to monochrome for ESC/POS
        imagefilter($im, IMG_FILTER_GRAYSCALE);
        imagefilter($im, IMG_FILTER_CONTRAST, -100);

        $bytes = "\x1D\x76\x30\x00"; // GS v 0 - raster bit image
        $widthPx = imagesx($im);
        $heightPx = imagesy($im);
        $widthBytes = intval(($widthPx + 7) / 8);

        $bytes .= pack('v', $widthBytes);  // width in bytes
        $bytes .= pack('v', $heightPx);    // height in pixels

        for ($y = 0; $y < $heightPx; $y++) {
                for ($x = 0; $x < $widthBytes * 8; $x += 8) {
                        $byte = 0;
                        for ($b = 0; $b < 8; $b++) {
                                $px = $x + $b;
                                if ($px < $widthPx) {
                                        $gray = imagecolorat($im, $px, $y) & 0xFF;
                                        if ($gray < 128) {
                                                $byte |= (1 << (7 - $b));
                                        }
                                }
                        }
                        $bytes .= chr($byte);
                }
        }

        // imagedestroy($im); deprecated in PHP 8.0 and later, resources are automatically freed at the end of the script
        return $bytes;
}

/**
 * Detects if a string contains Arabic characters.
 * @param string $string The string to check.
 * @return bool True if the string contains Arabic characters, false otherwise.
 */
function isArabic($string)
{
        // This regex checks for characters in the main Arabic Unicode block.
        // The 'u' modifier is essential for this to work with UTF-8 strings.
        if (is_string($string) && $string !== '' && preg_match('/[\x{0600}-\x{06FF}]/u', $string) === 1) {
                return true;
        }
        return false;
}

function addTrace($message)
{

        $user = "[Unknown user]";
        if (loggedIn()) {
                $user = '[' . $_SESSION["username"] . ']';
        }
        $time = @date('[d/M/Y:H:i:s]');
        $newMessage = "\n" . $time . " " . $user . " " . $message . "\n";
        // error_log($newMessage, 3, $_SERVER['DOCUMENT_ROOT'] . Config::$log_file_path);
        error_log($newMessage, 0);
}

// This function is used in CheckoutMenu, ChefPanel, WaiterPanel
function fillTablesCms($tables)
{

        echo "<select validation='SELECETD' placeholder='" . T('place') . "' class='form-control searcheTable border-radion-0'>";
        echo "<option value = 'NULL' selected = 'selected' class='text-center'>" . T('place') . "</option>";
        echo "<option value = '" . Config::$orderPlaceCarryWith  . "' class='text-center'>" . T('take_away') . "</option>";
        // $tableNumber = 0;
        foreach ((array) $tables as $i => $table) {
                echo "<option value =" . $table[Table::$col_id] . " tablecode=" . $table[Table::$col_tableCode] . " class='text-center'>" . $table[Table::$col_tableName] . "</option>";
                // $tableNumber++;
        }

        echo "</select>";
}

function fillCompanies($companies)
{

        echo "<select validation='' placeholder='Categorie' class='form-control searcheCompany'>";
        echo "<option value = '0' selected = 'selected'>Companies</option>";

        foreach ((array) $companies as $i => $company) {
                echo "<option value ='" . $company[Company::$col_id] . "'>" . $company[Company::$col_companyName] . "</option>";
        }
        echo "</select>";
}
function fillCategories($categories)
{

        echo "<select validation='' placeholder='" . T('category_label') . "' class='form-control searcheCategory'>";
        echo "<option value = '0' selected = 'selected'>" . T('category_label') . "</option>";
        $categoryNumber = 0;
        foreach ((array) $categories as $i => $category) {
                echo "<option value ='" . $category[Category::$col_id] . "'>" . $category[Category::$col_category] . "</option>";
                $categoryNumber++;
        }
        echo "</select>";
}

// **************************** Old functions  ****************************************

function getClientLabelByLanguage_OLD($totalPrice, $place, $subOrders, $company, $langaugeCode)
{
        $company_id = $_SESSION['company_id'];

        $companyLicence = JsonLicence::getLicence($company_id, false);

        $cmsCurrency = $companyLicence[0][Licence::$col_cmsCurrency];
        // check if client whant to print client label or not
        $printClient = $companyLicence[0][Licence::$col_printClient];
        if ($printClient) {

                $label = '';
                $Lf = ''; //init Lf variable

                //Set the LF code
                switch ($langaugeCode) {
                        //If Ticket printer support ESC (Epson Standard Code for printers)
                        //like (Xprinter,...)
                        case "ESC":
                                $Lf = "\xA";
                                break;
                }

                // *****************Prepare Suborderes printing *************************

                $subordersPrintESC = '';
                foreach ((array) $subOrders as $i => $suborder) {

                        //if subOrder is supplement
                        if ($suborder[Category::$col_supplement] == '1') {

                                //if supplement subOrder price is 0 we skip foreach to prevent print this suborder
                                //because it is not important
                                if ($suborder[SubOrder::$col_subTotal] == '0') {
                                        continue;
                                }

                                //If SUPPLEMENT attribute value starts with non numéric char then we print it in ticket
                                //like (25cl, 1/P, 2P,.. )
                                //Otherwise we don't print it because it may concern Pizza Attributes (L,XL,XXL)
                                $subOrderAttribute = $suborder[Attribute_Value::$col_attributeValue];
                                if ($subOrderAttribute !== null) {
                                        if (!is_numeric($subOrderAttribute[0]))
                                                $suborder[Attribute_Value::$col_attributeValue] = '';
                                }
                        }
                        $result = getSubOrderPrintFormat(
                                iconv("UTF-8", "CP437//IGNORE", $suborder[Objet::$col_title]) . " " . $suborder[Attribute_Value::$col_attributeValue], //Item
                                $suborder[SubOrder::$col_quantity], //Qty
                                $suborder[SubOrder::$col_subTotal] == '0' ? '' : $suborder[SubOrder::$col_subTotal] // Sub Total
                        );
                        $subordersPrintESC = $subordersPrintESC . $result . $Lf; // Line Feed is set when code inter switch case
                }
                // print_r($subordersPrintESC);
                // ***************** ***************************************************

                //******************************* */ Label Data  **************************

                $companyName = $company[0][Company::$col_companyName];
                $address = $company[0][Company::$col_address];
                $mobile = $company[0][Company::$col_phone];
                $dateTime = date('d-m-Y H:i');
                $total = number_format($totalPrice, 0, '', ' ');

                //******************************* ***************************

                switch ($langaugeCode) {
                        //If Ticket printer support ESC (Epson Standard Code for printers)
                        //like (Xprinter,...)
                        case "ESC":

                                $label = "\x1B\x40" . // Initializes the printer (ESC @)
                                        "\x1B\x74\x00" .  // ESC t 0 — sets code page 437
                                        "\x1B\x61\x1" . // Specifies a centered printing position (ESC a)
                                        "\x1C\x70\x01\x00" . //Print the logo
                                        "\x1B\x21\x30" . // Specifies font (ESC !)
                                        iconv("UTF-8", "CP437//IGNORE", $companyName) . "\xA\xA" . // Company and 2 LF
                                        "\x1B\x21\x0" . // Specifies font A (ESC !)
                                        iconv("UTF-8", "CP437//IGNORE", $address) . "\xA" . // Addresse and LF
                                        $mobile . "\xA" . // Mobile and LF
                                        "_________________________" . "\xA\xA" .
                                        "\x1B\x61\x0" . //'Selects the left print position (ESC a)
                                        "\x1B\x21\x1" . // Specifies font (ESC !)
                                        $dateTime . "       " . "\x1B\x21\x18" . $place . "\xA" .  //Print date and place (table or importer + cmd number)
                                        "\x1B\x21\x0" . //fix font style for all text after this section
                                        "________________________________________________" . "\xA" . //print line
                                        getSubOrderPrintFormat("Item", "Qte", "Montant") . "\xA" .   //Print (item Qte Montant)
                                        "________________________________________________" . "\xA\xA" . //print line with LF LF
                                        $subordersPrintESC .   //Print suborders 
                                        "\x1B\x61\x1" .      // Specifies a centered printing position (ESC a)
                                        "_________________________" . "\xA\xA" . //print line
                                        "\x1B\x21\x30" . //Specifies font (ESC !)
                                        "TOTAL: " . $total . " " . $cmsCurrency . "\xA" . //print Total
                                        "\x1Bd\x6\x1B\x69" . //Add 6 line feed and cut label
                                        "\x1B\x70" . //open cash drawer
                                        "\x1B\x40"; // init printer
                                break;
                }
                return $label;
        }
}

function getSalesLabelByLanguage_OLD($subOrders, $startDate, $endDate, $company, $langaugeCode)
{
        //get cms currency from licence
        $company_id = $_SESSION['company_id'];
        $companyLicence = JsonLicence::getLicence($company_id, false);
        $cmsCurrency = $companyLicence[0][Licence::$col_cmsCurrency];

        $label = '';
        $Lf = ''; //init Lf variable

        //Set the LF code
        switch ($langaugeCode) {
                //If Ticket printer support ESC (Epson Standard Code for printers)
                //like (Xprinter,...)
                case "ESC":
                        $Lf = "\xA";
                        break;
        }

        // *****************Prepare Suborderes printing **************************
        $subordersPrintESC = '';

        foreach ((array) $subOrders as $i => $suborder) {
                //if suborder attribute value is null then we set it to empty string
                $subOrderAttribute = $suborder[Attribute_Value::$col_attributeValue];
                if ($subOrderAttribute == null) {
                        $suborder[Attribute_Value::$col_attributeValue] = '';
                }

                // Use appropriate encoding based on language
                $itemTitle = iconv("UTF-8", "CP437//IGNORE", $suborder[Objet::$col_title]);

                $result = getSubOrderPrintFormat(
                        $itemTitle . " " . $suborder[Attribute_Value::$col_attributeValue], //Item
                        $suborder['total_quantity'], //Total Qty
                        $suborder['total_subtotal']  // Total Sub Total
                );
                $subordersPrintESC = $subordersPrintESC . $result . $Lf;
        }

        //******************************* */ Label Data  **************************
        $report_dates = $startDate == $endDate ? $startDate : $startDate . " - " . $endDate;
        $companyName = $company[0][Company::$col_companyName];
        $dateTime = date('d-m-Y H:i');
        $total = number_format($subOrders[0]['total_of_day'], 0, '', ' ');
        //******************************* ***************************

        $label = "\x1B\x40" .                   // Initialize printer
                "\x1B\x74\x00" .                // Set code page to 437 (US English)
                "\x1B\x61\x1" .                 // Center align
                "\x1C\x70\x01\x00" .            // Print logo
                "\x1B\x21\x30" .                // Big font
                $companyName . "\xA\xA" .       // Company name
                "\x1B\x21\x0" .                 // Normal font
                iconv("UTF-8", "CP437//IGNORE", T('report_sales_admin_print')) . $report_dates . "\xA" .
                "_________________________" . "\xA\xA" .
                "\x1B\x61\x0" .                 // Align left
                "\x1B\x21\x1" .                 // Font style
                $dateTime . "\xA" .
                "\x1B\x21\x0" .
                "________________________________________________" . "\xA" .
                getSubOrderPrintFormat("Item", "Qte", "Montant") . "\xA" .
                "________________________________________________" . "\xA\xA" .
                $subordersPrintESC .
                "\x1B\x61\x1" .                 // Center align
                "_________________________" . "\xA\xA" .
                "\x1B\x21\x30" .                // Big font
                "TOTAL: " . $total . " " . $cmsCurrency . "\xA" .
                "\x1B\x64\x06" .                // 6 line feeds (correct command)
                "\x1B\x69" .                    // Cut paper
                "\x1B\x40";                     // Reset printer

        return $label;
}

//Function that returne an ESC POS Code that will be implimanted in Label, used in printClientLabel
function getSubOrderPrintFormat($item, $qte, $montant)
{
        $labelWidth = 50;
        $itemPlacePercentage = 60; //60%
        $qtePlacePercentage = 20; //20%
        $totalPlacePercentage = 20; //20%

        $qteStartPlace = $labelWidth * $itemPlacePercentage / 100;
        $totalStartPlace = $qteStartPlace + ($labelWidth * $qtePlacePercentage / 100);

        return $item . (addSpaceQteArabic($item, $qteStartPlace)) . $qte . (addSpaceMontantArabic($qte, $qteStartPlace, $totalStartPlace)) . $montant;
}

function getSubOrderPrintFormatArabic($item, $qte, $montant)
{
        // Return an array of the parts. The layout will be handled by the image generation function.
        return [$item, $qte, $montant];
}


//Function that calculate Space between Item and Qty of subordere, used in printClientLabel
// ...existing code...
//function that calculate space between Item and Qty for arabic language
function addSpaceQteArabic($qte, $qteWidth)
{
        // Pad the quantity string to the right with spaces to align it.
        $qteLength = mb_strlen($qte, 'UTF-8');
        $spacesNeeded = $qteWidth > $qteLength ? $qteWidth - $qteLength : 0;
        return $qte . str_repeat(' ', $spacesNeeded);
}
//function that calculate space between Qte and price for arabic language
function addSpaceMontantArabic($montant, $montantWidth)
{
        // Pad the price string to the right with spaces to align it.
        $montantLength = mb_strlen($montant, 'UTF-8');
        $spacesNeeded = $montantWidth > $montantLength ? $montantWidth - $montantLength : 0;
        return $montant . str_repeat(' ', $spacesNeeded);
}
//Function that returne an ESC POS Code for ARABIC language that will be implimanted in Label, used in printClientLabel
function getSubOrderPrintFormatArabic2($item, $qte, $montant)
{
        $labelWidth = 50;
        $itemPlacePercentage = 60; //60%
        $qtePlacePercentage = 20; //20%
        $totalPlacePercentage = 20; //20%

        $qteStartPlace = $labelWidth * $itemPlacePercentage / 100;
        $totalStartPlace = $qteStartPlace + ($labelWidth * $qtePlacePercentage / 100);

        return $item . (addSpaceQteArabic($item, $qteStartPlace)) . $qte . (addSpaceMontantArabic($qte, $qteStartPlace, $totalStartPlace)) . $montant;
}

/**
 * Build absolute filesystem path from a web-relative media path.
 * Example: '/object-media/444/x.jpg' -> 'D:\...\eatsmartly_envato\object-media\444\x.jpg'
 */
function media_fs_path($webRelativePath)
{
        $rel = ltrim((string)$webRelativePath, '/\\');
        $fs = rtrim(Config::$media_fs_base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
        return $fs;
}

/**
 * Build public URL from a web-relative media path.
 * Example: '/object-media/444/x.jpg' under '/eatsmartly' -> '/eatsmartly/object-media/444/x.jpg'
 */
function media_url($webRelativePath)
{
        $base = rtrim((string)Config::$media_url_base, '/');
        $rel = '/' . ltrim((string)$webRelativePath, '/');
        return $base . $rel;
}

/**
 * Backward compatible resolver for stored media values.
 * - If it starts with http/https, return as-is.
 * - If it starts with '../..', strip it and prefix current base path.
 * - Otherwise, treat as web-relative and prefix base path.
 */
function resolveMediaUrl($stored)
{
        if (!$stored) return '';
        $s = (string)$stored;

        if (stripos($s, 'http://') === 0 || stripos($s, 'https://') === 0) {
                return $s;
        }
        // Old records: '../../object-media/...'
        if (strpos($s, Config::$media_path_suffix) === 0) {
                $s = substr($s, strlen(Config::$media_path_suffix)); // now '/object-media/...'
        }
        return media_url($s);
}

/**
 * Convert a stored URL/path back to filesystem path.
 */
function media_url_to_fs($stored)
{
        if (!$stored) return '';
        $s = (string)$stored;
        // Normalize to path only
        $path = parse_url($s, PHP_URL_PATH);
        if (!$path) $path = $s;

        // Strip base path if present
        $base = rtrim((string)Config::$media_url_base, '/');
        if ($base && strpos($path, $base . '/') === 0) {
                $path = substr($path, strlen($base));
        }
        // Strip legacy prefix '../..'
        if (strpos($path, Config::$media_path_suffix) === 0) {
                $path = substr($path, strlen(Config::$media_path_suffix));
        }
        return media_fs_path($path);
}
