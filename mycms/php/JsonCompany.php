<?php

/**
 * Description of JsonObject
 *
 * @author Nminfo
 */
require_once "Database.php";
require_once "Company.php";
require_once "Licence.php";
require_once "JsonLicence.php";
require_once "functions.php";


if (isset($_POST['function'])) {

    if ($_POST['function'] === "createCompany") {
        confirmLoggedIn();
        JsonCompany::createCompany(createCompanyFromGetVariables());
    }

    if ($_POST['function'] === "updateCompany") {
        confirmLoggedIn();
        JsonCompany::updateCompany(createCompanyFromGetVariables());
    }
    if ($_POST['function'] === "deleteCompany") {
        confirmLoggedIn();
        JsonCompany::deleteCompany($_POST['id']);
    }

    if (($_POST['function'] === "getAllCompanies")) {
        JsonCompany::getAllCompanies(true);
    }

    if (($_POST['function'] === "searchCompany") && (isset($_POST['search']))) {
        JsonCompany::searchCompany($_POST['search']);
    }

    if (($_POST['function'] === "getCompanyById") && (isset($_POST['id']))) {
        JsonCompany::getCompanyById($_POST['id'], true);
    }

    if (($_POST['function'] === "existCarryCode") && (isset($_POST['carryCode']))) {
        JsonCompany::existCarryCode($_POST['carryCode'], true);
    }
    if (($_POST['function'] === "uploadMedia") && (isset($_POST['id'])) && (isset($_POST['dataType']))) {
        confirmLoggedIn();
        JsonCompany::upload($_POST['id'], $_POST['dataType']);
    }
    if (($_POST['function'] === "deleteCompanyMedia") && (isset($_POST['id'])) && (isset($_POST['dataType']))) {
        confirmLoggedIn();
        JsonCompany::deleteCompanyMedia($_POST['id'], $_POST['dataType'], True);
    }
}

function createCompanyFromGetVariables()
{
    $company = new Company();
    if (isset($_POST[Company::$col_id])) {
        $company->setId($_POST[Company::$col_id]);
    }
    $company->setCompanyName(trim($_POST[Company::$col_companyName]));
    $company->setCompanyDescription(trim($_POST[Company::$col_companyDescription]));
    $company->setAddress(trim($_POST[Company::$col_address]));
    $company->setPhone(trim($_POST[Company::$col_phone]));
    $company->setEmail(trim($_POST[Company::$col_email]));
    $company->setGps(trim($_POST[Company::$col_gps]));
    $company->setCarryCode($_POST[Company::$col_carryCode]);
    //

    return $company;
}

class JsonCompany
{

    // Create un object
    static function createCompany(Company $company)
    {

        //Test whether object already exists in DB
        if (JsonCompany::existCompany($company)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Company::$table_name .
            "(" .
            Company::$col_companyName .
            ", " .
            Company::$col_companyDescription .
            ", " .
            Company::$col_address .
            ", " .
            Company::$col_phone .
            ", " .
            Company::$col_email .
            ", " .
            Company::$col_gps .
            ", " .
            Company::$col_carryCode .
            ", " .
            Company::$col_creationDate .
            ")" .
            " VALUES (:companyName, :companyDescription, :address, :phone, :email, :gps, "
            . ":carryCode, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':companyName', $company->getCompanyName(), PDO::PARAM_STR);
        $stmt->bindValue(':companyDescription', $company->getCompanyDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':address', $company->getAddress(), PDO::PARAM_STR);
        $stmt->bindValue(':phone', $company->getPhone(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $company->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':gps', $company->getGps(), PDO::PARAM_STR);
        $stmt->bindValue(':carryCode', $company->getCarryCode(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        //Get the last inserted id to use it in the next query
        $id = $conn->lastInsertId();
        
        //Create a standard licence for the company
        $licence = new Licence();
        $licence->setLicenceName("Licence_" . $company->getCompanyName());
        $licence->setLicence("NULL");
        $licence->setAdminUsers(1);
        $licence->setChefUsers(6);
        $licence->setWaiterUsers(4);
        $licence->setCheckoutUsers(4);
        $licence->setOrderCapability(1);
        $licence->setCmsCurrency('DA');
        $licence->setCmsLanguage('en');
        $licence->setCompany_id($id);
        JsonLicence::createLicence($licence);
        //

        JsonCompany::getCompanyById($id, true);
    }

    //Update object
    static function updateCompany(Company $company)
    {

        //Test whether object already exists in DB
        if (JsonCompany::existCompany($company)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "UPDATE " . Company::$table_name .
            " SET " .
            Company::$col_companyName . "= :companyName" .
            " , " .
            Company::$col_companyDescription . "= :companyDescription" .
            " , " .
            Company::$col_address . "= :address" .
            " , " .
            Company::$col_phone . "= :phone" .
            " , " .
            Company::$col_email . "= :email" .
            " , " .
            Company::$col_gps . "= :gps" .
            " , " .
            Company::$col_carryCode . "= :carryCode" .
            " WHERE " .
            Company::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':companyName', $company->getCompanyName(), PDO::PARAM_STR);
        $stmt->bindValue(':companyDescription', $company->getCompanyDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':address', $company->getAddress(), PDO::PARAM_STR);
        $stmt->bindValue(':phone', $company->getPhone(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $company->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':gps', $company->getGps(), PDO::PARAM_STR);
        $stmt->bindValue(':carryCode', $company->getCarryCode(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $company->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonCompany::getCompanyById($company->getId(), true);
    }

    //Delete Company
    static function deleteCompany($id)
    {

        $conn = Database::getConnection();
        JsonCompany::deleteCompanyMedia($id, "logo", false);
        JsonCompany::deleteCompanyMedia($id, "cover", false);
        //Delete company from database
        $query = "DELETE FROM " . Company::$table_name .
            " WHERE " .
            Company::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

         if (!$stmt->execute()) {
                        $errorInfo = $stmt->errorInfo();
                        // MySQL error code 1451: Cannot delete or update a parent row: a foreign key constraint fails
                        if (isset($errorInfo[1]) && $errorInfo[1] == 1451) {
                                echo json_encode(array(
                                        "state" => "f",
                                        "message" => Config::$user_error_Cannot_delete . " Company has 'Categories' or 'Users'."
                                ));
                        } else {
                                echo json_encode(array(
                                        "state" => "f",
                                        "message" => Config::$user_error . " " . __FUNCTION__
                                ));
                        }
                        addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                        exit;
                }
        //

        echo json_encode(array("state" => "s"));
    }

    static function getCompanyById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            " id = :id LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    static function searchCompany($search)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_companyName . " <> :bouhezilaCompany" .
            " AND (" .
            Company::$col_companyName . " LIKE :search " .
            " OR " .
            Company::$col_companyDescription . " LIKE :search " .
            " OR " .
            Company::$col_email . " LIKE :search " .
            " OR " .
            Company::$col_phone . " LIKE :search " .
            ") " .
            " ORDER BY " .
            Company::$col_updateDate .
            " DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':bouhezilaCompany', Config::$bouhezilaCompany, PDO::PARAM_STR);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    //THis function get all companies without Bouhezila Company
    static function getAllCompanies($extractData)
    {
        //Verify Licence

        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_companyName . " <> :bouhezilaCompany" .
            " ORDER BY " .
            Company::$col_companyName;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':bouhezilaCompany', Config::$bouhezilaCompany, PDO::PARAM_STR);
        if (!$stmt->execute()) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            }
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
            exit;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    //This function is used in Cart.js to check if the carry Code set by the user exists in Database
    static function existCarryCode($carryCode, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Company::$table_name . "." . Company::$col_carryCode .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$table_name . "." . Company::$col_carryCode . " = :carryCode";

        //        print_r($query);

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':carryCode', $carryCode, PDO::PARAM_INT);

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
        echo json_encode(array("state" => "s"));
    }

    // Test whether the Company exists or not
    static function existCompany(Company $company)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_companyName . " = :companyName" .
            " AND " .
            Company::$col_id . "<> :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':companyName', $company->getCompanyName(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $company->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if (!$stmt->rowCount() == 0) {
            return true;
        } else {
            return false;
        }
    }

    // Upload Company Cover
   static function upload($id, $dataType)
    {
        // ...existing code...

        $mediaPath = "";

        $tmp_file = $_FILES['company-media']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['company-media']['name'], PATHINFO_EXTENSION));

        // Build dir paths
        $mediaDirRel = Config::$company_images_path.'/' . $id . "/";      // web-relative: '/company-media/{id}/'
        $mediaDirFs  = media_fs_path($mediaDirRel);                    // absolute FS path

        if (!file_exists($mediaDirFs)) {
            mkdir($mediaDirFs, 0777, true);
        }
        $fileName    = date('Y-m-d-H-i-s') . "." . $fileExtension;
        $mediaPathFs = $mediaDirFs . $fileName;

        if (!move_uploaded_file($tmp_file, $mediaPathFs)) {
            echo json_encode(array("state" => "f", "message" => Config::$fail_upload_file . " " . __FUNCTION__));
            exit;
        }

        // Store web-relative path in DB (no '../..')
        $mediaPath = $mediaDirRel . $fileName;

        $conn = Database::getConnection();

        $query1 = "UPDATE " . Company::$table_name .
            " SET " .
            Company::$col_logo . " = :logo" .
            " WHERE " .
            Company::$col_id . " = :id" .
            " OR " .
            Company::$col_id . " = 1" ;

        $query2 = "UPDATE " . Company::$table_name .
            " SET " .
            Company::$col_companyCover . " = :companyCover" .
            " WHERE " .
            Company::$col_id . " = :id".
            " OR " .
            Company::$col_id . " = 1";

        if ($dataType === "logo") {

            $stmt = $conn->prepare($query1);
            $stmt->bindValue(':logo', $mediaPath, PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare($query2);
            $stmt->bindValue(':companyCover', $mediaPath, PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        }

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonCompany::getCompanyById($id, true);
    }

    static function deleteCompanyMedia($id, $dataType, $extractData)
    {

        $conn = Database::getConnection();
        $query1 = "SELECT " .
            Company::$col_logo .
            " AS " .
            " media " .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_id . " = :id";

        $query2 = "SELECT " .
            Company::$col_companyCover .
            " AS " .
            " media " .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_id . " = :id";

        if ($dataType === "logo") {
            $stmt = $conn->prepare($query1);
        } else {
            $stmt = $conn->prepare($query2);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $media = $output[0]["media"];
        //
        if ($dataType === "logo") {
            JsonCompany::setLogoNull($id);
        } else {
            JsonCompany::setCoverNull($id);
        }
        //Delete Company logo or cover file
        $company = JsonCompany::getCompanyById($id, false);
        $logo = $company[0][Company::$col_logo];
        $cover = $company[0][Company::$col_companyCover];

        if (is_null($logo) && is_null($cover)) {
            JsonCompany::deleteMediaFromServer($id, $media, True);
        } else {
            JsonCompany::deleteMediaFromServer($id, $media, False);
        }

        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    static function setLogoNull($id)
    {
        $conn = Database::getConnection();
        $query = "UPDATE " . Company::$table_name .
            " SET " .
            Company::$col_logo . "= :logo" .
            " WHERE " .
            Company::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':logo', NULL, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    static function setCoverNull($id)
    {
        $conn = Database::getConnection();
        $query = "UPDATE " . Company::$table_name .
            " SET " .
            Company::$col_companyCover . "= :companyCover" .
            " WHERE " .
            Company::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':companyCover', NULL, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    static function deleteMediaFromServer($id, $media, $deleteMediaFolder)
    {
        if (!is_null($media)) {
            $fsPath = media_url_to_fs($media); // supports '/company-media/..' and '../../..'
            if (!@unlink($fsPath)) {
                echo json_encode(array("state" => "f", "message" => Config::$fail_delete_file . " " . __FUNCTION__));
                exit;
            }
            if ($deleteMediaFolder) {
                $mediaFolder = dirname($fsPath);
                if (!@rmdir($mediaFolder)) {
                    echo json_encode(array("state" => "f", "message" => Config::$fail_remove_media_dir . " " . __FUNCTION__));
                    exit;
                }
            }
        }
    }

    //this function is used in Company management when upload test whther Company have a cover or not
    static function haveCompanyCover($id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Company::$col_companyCover .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_null($output[0][Company::$col_companyCover])) {
            return false;
        } else {
            return true;
        }
    }

    //this function is used in Company management test whther Company have a logo or not
    static function haveCompanyLogo($id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Company::$col_logo .
            " FROM " .
            Company::$table_name .
            " WHERE " .
            Company::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_null($output[0][Company::$col_logo])) {
            return false;
        } else {
            return true;
        }
    }
}