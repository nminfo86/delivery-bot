<?php

/**
 * Description of JsonObject
 *
 * @author Nminfo
 */

require_once "Database.php";
require_once "Connection.php";
require_once "Licence.php";
require_once "functions.php";


if (isset($_POST['function'])) {
    if ($_POST['function'] === "updateLicenceByLicenceKey" && isset($_POST['licenceCode']) && isset($_POST['licenceKey'])) {
        JsonLicence::updateLicenceByLicenceKey($_POST['licenceCode'], $_POST['licenceKey']);
    }
    if ($_POST['function'] === "updateLicenceOptions" && isset($_POST['company_id'])) {
        $company_id = intval($_POST['company_id']);
        $printChef = isset($_POST['printChef']) ? intval($_POST['printChef']) : 0;
        $printClient = isset($_POST['printClient']) ? intval($_POST['printClient']) : 0;
        $printArabicRecipe = isset($_POST['printArabicRecipe']) ? intval($_POST['printArabicRecipe']) : 0;
        $orderCapability = isset($_POST['orderCapability']) ? intval($_POST['orderCapability']) : 0;
        $cmsCurrency = isset($_POST['cmsCurrency']) ? strval($_POST['cmsCurrency']) : 'DA';
        $cmsLanguage = isset($_POST['cmsLanguage']) ? strval($_POST['cmsLanguage']) : 'en'; // Added line
        $backupBasePath = isset($_POST['backupBasePath']) ? strval($_POST['backupBasePath']) : ''; // Added line

        JsonLicence::updateLicenceOptions($company_id, $printChef, $printClient, $printArabicRecipe, $orderCapability, $cmsCurrency, $cmsLanguage, $backupBasePath); // Added param
    }
    if ($_POST['function'] === "getLicence" && isset($_POST['company_id'])) {
        $company_id = intval($_POST['company_id']);
        $licence = JsonLicence::getLicence($company_id, true);
    }
}


class JsonLicence
{

    static function existDataBase()
    {
        $output = NULL;

        /**
         * Use mysqli connector instead of PDO because it is simple
         */

        // Create connection
        $conn = new mysqli(Connection::$host, Connection::$username, Connection::$password, "information_schema");
        // Check connection
        if ($conn->connect_error) {
            echo json_encode(array("state" => "f", "message" =>  $conn->connect_error . " " . __FUNCTION__));
        }

        $sql = "SELECT SCHEMA_NAME FROM SCHEMATA WHERE SCHEMA_NAME = '" . Connection::$db_name . "'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $output = 1;
        } else {
            $output = 0;
        }
        $conn->close();

        return $output;
    }

    // Create un licence
    static function createLicence(Licence $licence)
    {

        $conn = Database::getConnection();

        $query = "INSERT INTO " . Licence::$table_name .
            "(" .
            Licence::$col_licenceName .
            ", " .
            Licence::$col_licence .
            ", " .
            Licence::$col_adminUsers .
            ", " .
            Licence::$col_chefUsers .
            ", " .
            Licence::$col_waiterUsers .
            ", " .
            Licence::$col_checkoutUsers .
            ", " .
            Licence::$col_orderCapability .
            ", " .
            Licence::$col_printChef .
            ", " .
            Licence::$col_printClient .
            ", " .
            Licence::$col_printArabicRecipe .
            ", " .
            Licence::$col_cmsCurrency .
            ", " .
            Licence::$col_cmsLanguage . // Added line
            ", " .
            Licence::$col_backupBasePath .
            ", " .
            Licence::$col_company_id .
            ", " .
            Licence::$col_creationDate .
            ")" .
            " VALUES (:licenceName, :licence, :adminUsers, :chefUsers, :waiterUsers, :checkoutUsers, "
            . ":orderCapability, :printChef, :printClient,  :printArabicRecipe, :cmsCurrency, :cmsLanguage, :backupBasePath, :company_id, :creationDate)"; // Added param

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':licenceName', $licence->getLicenceName(), PDO::PARAM_STR);
        $stmt->bindValue(':licence', $licence->getLicence(), PDO::PARAM_STR);
        $stmt->bindValue(':adminUsers', $licence->getAdminUsers(), PDO::PARAM_INT);
        $stmt->bindValue(':chefUsers', $licence->getChefUsers(), PDO::PARAM_INT);
        $stmt->bindValue(':waiterUsers', $licence->getWaiterUsers(), PDO::PARAM_INT);
        $stmt->bindValue(':checkoutUsers', $licence->getCheckoutUsers(), PDO::PARAM_INT);
        $stmt->bindValue(':orderCapability', 0, PDO::PARAM_INT);
        $stmt->bindValue(':printChef', 1, PDO::PARAM_BOOL);
        $stmt->bindValue(':printClient', 1, PDO::PARAM_BOOL);
        $stmt->bindValue(':printArabicRecipe', 0, PDO::PARAM_BOOL); // Added line
        $stmt->bindValue(':cmsCurrency', $licence->getCmsCurrency(), PDO::PARAM_STR);
        $stmt->bindValue(':cmsLanguage', $licence->getCmsLanguage(), PDO::PARAM_STR); // Added line
        $stmt->bindValue(':backupBasePath', $licence->getBackupBasePath(), PDO::PARAM_STR); // Added line
        $stmt->bindValue(':company_id', $licence->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    // Update licencebyLicenceKey, this is used to activate the application 
    static function updateLicenceByLicenceKey($licenceCode, $licenceKey)
    {

        // Check if the licence key is valid
        $decodedMac = decodeMacAddress($licenceCode);

        $verifyLicenceKey = generateLicenceKey($decodedMac);

        if ($licenceKey != $verifyLicenceKey) {
            echo json_encode(array("state" => "f", "message" => T('licence_key_error')));
            exit;
        }

        $conn = Database::getConnection();

        $query = "UPDATE " . Licence::$table_name .
            " SET " .
            Licence::$col_licence . " = :licence" .
            ", " . Licence::$col_checked  . " = :dateTime" .
            //WHERE clause
            " WHERE " . Licence::$col_company_id . " = :company_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':licence', $licenceKey, PDO::PARAM_STR);
        $stmt->bindValue(':dateTime', getCurrentDate(), PDO::PARAM_STR);
        $stmt->bindValue(':company_id', 1, PDO::PARAM_INT); //Bouhezila Company

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        echo json_encode(array("state" => "s"));
    }

    static function updateLicenceOptions(
        $company_id,
        $printChef,
        $printClient,
        $printArabicRecipe, // Added param
        $orderCapability,
        $cmsCurrency,
        $cmsLanguage,
        $backupBasePath
    ) {
        $conn = Database::getConnection();
        $query = "UPDATE " . Licence::$table_name .
            " SET " .
            Licence::$col_printChef . " = :printChef, " .
            Licence::$col_printClient . " = :printClient, " .
            Licence::$col_printArabicRecipe . " = :printArabicRecipe, " . // Added line
            Licence::$col_orderCapability . " = :orderCapability, " .
            Licence::$col_cmsCurrency . " = :cmsCurrency, " .
            Licence::$col_cmsLanguage . " = :cmsLanguage, " .
            Licence::$col_backupBasePath . " = :backupBasePath " .
            " WHERE " . Licence::$col_company_id . " = :company_id OR " . Licence::$col_company_id . " = 1";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':printChef', $printChef, PDO::PARAM_INT);
        $stmt->bindValue(':printClient', $printClient, PDO::PARAM_INT);
        $stmt->bindValue(':printArabicRecipe', $printArabicRecipe, PDO::PARAM_INT);
        $stmt->bindValue(':orderCapability', $orderCapability, PDO::PARAM_INT); // Added line
        $stmt->bindValue(':cmsCurrency', $cmsCurrency, PDO::PARAM_STR);
        $stmt->bindValue(':cmsLanguage', $cmsLanguage, PDO::PARAM_STR); // Added line
        $stmt->bindValue(':backupBasePath', $backupBasePath, PDO::PARAM_STR); // Added line
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => "Erreur lors de la mise à jour des options licence"));
            exit;
        }
        echo json_encode(array("state" => "s"));
        exit;
    }

    static function updateCheckedLicence($dateTime)
    {

        $companies  = JsonCompany::getAllCompanies(FALSE);

        //If there is only one company we search for its checked field
        // if (count($companies) <= 1) {

        //     $company_id = $companies[0][Company::$col_id];

        //     //if there is more than one company we get the checked field key of bouhezilaCompany
        // } else {
        $company_id = 1; //Bouhezila Company
        // }

        $conn = Database::getConnection();

        $query = "UPDATE " . Licence::$table_name .
            " SET " .
            Licence::$col_checked . " = :dateTime" .
            " WHERE " .
            Licence::$col_company_id . " = :company_id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    static function getLicence($company_id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Licence::$table_name .
            " WHERE " .
            Licence::$col_company_id . " = :company_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
                exit;
            }
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getLicenceKey()
    {
        $licenceKey = '';

        $licenceKey = JsonLicence::getLicence(1, false)[0][Licence::$col_licence];
        // }
        return $licenceKey;
    }

    static function getLicenceChecked()
    {
        $licenceChecked = '';

        $licenceChecked = JsonLicence::getLicence(1, false)[0][Licence::$col_checked];

        return $licenceChecked;
    }
}
