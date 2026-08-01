<?php

/**
 * Description of JsonObject
 *
 * @author Nminfo
 */
require_once 'JsonMedia.php';
require_once "Database.php";
require_once "Objet.php";
require_once "User.php";
require_once "Category.php";
require_once "JsonCategory.php";
require_once "Media.php";
require_once "JsonMedia.php";
require_once "functions.php";
require_once "init.php";
require_once "JsonPrice.php"; 
require_once "JsonPrinter.php"; 

if (isset($_GET['term'])) {
    JsonObject::searchObjectFromOutSide($_GET['term']);
}

if (isset($_POST['function'])) {

    if ($_POST['function'] === "createObject") {
        confirmLoggedIn();
        JsonObject::createObject(createObjectFromGetVariables(), true);
    }
    if ($_POST['function'] === "updateObject") {
        confirmLoggedIn();
        JsonObject::updateObject(createObjectFromGetVariables());
    }
    if (($_POST['function'] === "deleteObject") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonObject::deleteObject($_POST['id'], True);
    }
    if (($_POST['function'] === "getObjectById") && (isset($_POST['id']))) {
        JsonObject::getObjectById($_POST['id'], true);
    }
    if (($_POST['function'] === "getAllObjects") && (isset($_POST['id']))) {
        JsonObject::getAllObjects($_POST['id']);
    }
    if (($_POST['function'] === "getAllObjectsByCategoryId") && isset($_POST['company_id']) && isset($_POST['category_id'])) {
        JsonObject::getAllObjectsByCategoryId($_POST['company_id'], $_POST['category_id'], true);
    }
    if (($_POST['function'] === "getAllSupplementsObjects") && (isset($_POST['id']))) {
        JsonObject::getAllSupplementsObjects($_POST['id'], true);
    }
    if (($_POST['function'] === "searchObject") && (isset($_POST['search']))) {
        JsonObject::searchObject($_POST['search']);
    }
    if (($_POST['function'] === "getFirstObjectsByNumber") && (isset($_POST['number']))) {

        JsonObject::getFirstObjectsByNumber($_POST['number']);
    }

    if ($_POST['function'] === "printAllObjectsPrices") {
        confirmLoggedIn();
        JsonObject::printAllObjectsPrices();
    }

    if (($_POST['function'] === "generateVariants") && (isset($_POST['company_id'])) && (isset($_POST['category_id']))) {
        JsonObject::generateVariants($_POST['company_id'], $_POST['category_id'], True);
    }
    if (($_POST['function'] === "deleteVariants") && (isset($_POST['company_id'])) && (isset($_POST['category_id']))) {
        JsonObject::deleteVariants($_POST['company_id'], $_POST['category_id'], True);
    }
}

function createObjectFromGetVariables()
{
    $object = new Objet();
    if (isset($_POST[Objet::$col_id])) {
        $object->setId($_POST[Objet::$col_id]);
    }

    $basePrice = trim((string)$_POST[Objet::$col_basePrice]);
    $baseCost = isset($_POST[Objet::$col_baseCost]) ? trim((string)$_POST[Objet::$col_baseCost]) : '';

    if ($baseCost === '' || (float)$baseCost <= 0) {
        $baseCost = $basePrice;
    }
    $object->setTitle(trim($_POST[Objet::$col_title]));
    $object->setDescription($_POST[Objet::$col_description]);
    $object->setBasePrice($basePrice);
    $object->setBaseCost($baseCost);
    $object->setObservation(isset($_POST[Objet::$col_observation]) ? trim($_POST[Objet::$col_observation]) : '');
    $object->setCategory_id($_POST[Objet::$col_category_id]);
    $object->setObjAvailable($_POST[Objet::$col_objAvailable]);

    //Get the user company_id from the session already created in Authentication.php
    $object->setCompany_id($_SESSION["company_id"]);
    //
    return $object;
}

class JsonObject
{

    // Create un object
    static function createObject(Objet $object, $extractData)
    {

        //Test whether object already exists in DB
        if (JsonObject::existObject($object)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Objet::$table_name .
            "(" .
            Objet::$col_title .
            ", " .
            Objet::$col_description .
            ", " .
            Objet::$col_basePrice .
            ", " .
            Objet::$col_baseCost .
            ", " .
            Objet::$col_observation .
            ", " .
            Objet::$col_category_id .
            ", " .
            Objet::$col_objAvailable .
            ", " .
            Objet::$col_company_id .
            ", " .
            Objet::$col_creationDate .
            ")" .
            " VALUES (:title, :description, :basePrice, :baseCost, :observation, :category_id, :objAvailable, :company_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':title', $object->getTitle(), PDO::PARAM_STR);
        $stmt->bindValue(':description', $object->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':basePrice', $object->getBasePrice(), PDO::PARAM_STR);
        $stmt->bindValue(':baseCost', $object->getBaseCost(), PDO::PARAM_STR);
        $stmt->bindValue(':observation', $object->getObservation(), PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $object->getCategory_id(), PDO::PARAM_INT);
        $stmt->bindValue(':objAvailable', $object->getObjAvailable(), PDO::PARAM_BOOL);
        $stmt->bindValue(':company_id', $object->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            Objet::$col_id .
            " FROM " .
            Objet::$table_name .
            " ORDER BY " .
            Objet::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        if ($extractData) {
            JsonObject::getObjectById($id, true);
        } else {
            return $id;
        }
    }

    //Update object
    static function updateObject(Objet $object)
    {

        //Test whether object already exists in DB
        if (JsonObject::existObject($object)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "UPDATE " . Objet::$table_name .
            " SET " .
            Objet::$col_title . "= :title" .
            " , " .
            Objet::$col_description . "= :description" .
            " , " .
            Objet::$col_basePrice . "= :basePrice" .
            " , " .
            Objet::$col_baseCost . "= :baseCost" .
            " , " .
            Objet::$col_observation . "= :observation" .
            " , " .
            Objet::$col_category_id . "= :category_id" .
            " , " .
            Objet::$col_objAvailable . "= :objAvailable" .
            " , " .
            Objet::$col_company_id . "= :company_id" .
            " WHERE " .
            Objet::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':title', $object->getTitle(), PDO::PARAM_STR);
        $stmt->bindValue(':description', $object->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':basePrice', $object->getBasePrice(), PDO::PARAM_STR);
        $stmt->bindValue(':baseCost', $object->getBaseCost(), PDO::PARAM_STR);
        $stmt->bindValue(':observation', $object->getObservation(), PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $object->getCategory_id(), PDO::PARAM_INT);
        $stmt->bindValue(':objAvailable', $object->getObjAvailable(), PDO::PARAM_BOOL);
        $stmt->bindValue(':company_id', $object->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $object->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        JsonObject::getObjectById($object->getId(), true);
    }

    //Delete Object
    static function deleteObject($id, $extractData)
    {
        //

        $conn = Database::getConnection();

        // Prevent deletion if this object is used in any already paid order
        $checkQuery = "SELECT COUNT(*) AS cnt FROM suborder s
                       INNER JOIN ordere o ON s.ordere_id = o.id
                       WHERE s.object_id = :object_id AND o.payed = 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':object_id', $id, PDO::PARAM_INT);
        if (!$checkStmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($checkStmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row) && intval($row['cnt']) > 0) {
            echo json_encode(array("state" => "f", "message" => T('msg_object_cannot_delete')));
            exit;
        }
        // If not used in paid orders, proceed with deletion

        $query = "DELETE FROM " . Objet::$table_name .
            " WHERE " .
            Objet::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        JsonMedia::deleteAllMediaOfObject($id);
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    // Get Object by id
    static function getObjectById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Objet::$table_name . ".*" .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Category::$table_name . "." . Category::$col_available .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_color .
            " , " .
            Company::$table_name . "." . Company::$col_companyName .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            Objet::$table_name . "." . Objet::$col_id . " = :id";

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

    static function getObjectBasePrice($id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Objet::$col_basePrice .
            " FROM " .
            Objet::$table_name .
            " WHERE " .
            Objet::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $output[0][Objet::$col_basePrice];
    }

    static function getObjectBaseCost($id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Objet::$col_baseCost .
            " FROM " .
            Objet::$table_name .
            " WHERE " .
            Objet::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $output[0][Objet::$col_baseCost];
    }
    //This function is used  page and CheckoutMenu.php page
    static function getAllObjectsByCategoryId($company_id, $category_id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();

        $query = "SELECT " .
            Objet::$table_name . ".*" .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Category::$table_name . "." . Category::$col_available .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_acceptSupplement .
            " , " .
            Category::$table_name . "." . Category::$col_color .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
           " LEFT JOIN " .
            Media::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " AND " .
            Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .
            " WHERE " .
            Objet::$table_name . "." . Objet::$col_company_id . " =:company_id" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_category_id . " =:category_id" .
            " GROUP BY " .
            Objet::$table_name . "." . Objet::$col_id .
            " ORDER BY " .
            Objet::$table_name . "." . Objet::$col_title;

        // echo $query;

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);


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
                exit;
            }
        }
        while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
            $output[] = $row;
        }


        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    //This function is used  page and shop.php to get all object of category without "aux choix" objects
    static function getAllObjectsByCategoryId_Without_AuxChoix($company_id, $category_id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();

        $query = "SELECT " .
        Objet::$table_name . ".*" .
        " , " .
        Category::$table_name . "." . Category::$col_category .
        " , " .
        Category::$table_name . "." . Category::$col_available .
        " , " .
        Category::$table_name . "." . Category::$col_supplement .
        " , " .
        Category::$table_name . "." . Category::$col_acceptSupplement .
        " , " .
        Category::$table_name . "." . Category::$col_color .
        " , " .
        Media::$table_name . "." . Media::$col_media .
        " FROM " .
        Objet::$table_name .
        " INNER JOIN " .
        Category::$table_name .
        " ON " .
        Objet::$table_name . "." . Objet::$col_category_id .
        " = " .
        Category::$table_name . "." . Category::$col_id .
        " LEFT JOIN " .
        Media::$table_name .
        " ON " .
        Objet::$table_name . "." . Objet::$col_id .
        " = " .
        Media::$table_name . "." . Media::$col_object_id .
        " AND " .
        Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .
        " WHERE " .
        Objet::$table_name . "." . Objet::$col_company_id . " =:company_id" .
        " AND " .
        Objet::$table_name . "." . Objet::$col_category_id . " =:category_id" .
        " AND " .
        Objet::$table_name . "." . Objet::$col_title . "  NOT like '%" . Config::$article_aux_choix . "%'" .
        " GROUP BY " .
        Objet::$table_name . "." . Objet::$col_id .
        " ORDER BY " .
        Objet::$table_name . "." . Objet::$col_title;

        // echo $query;

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);


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
                exit;
            }
        }
        while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
            $output[] = $row;
        }


        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getAllSupplementsObjects($company_id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();

        $query = "SELECT " .
            Objet::$table_name . ".*" .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Category::$table_name . "." . Category::$col_available .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_color .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Media::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " AND " .
            Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .

            " WHERE " .
            Media::$col_mediaPosition . "= '" . Config::$mediaPositionCover . "'" .
            " AND " .
            Category::$col_supplement . " = '1'" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_company_id . " = :company_id" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_title . " NOT like '%" . Config::$article_aux_choix . "%'" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_title . " NOT LIKE '%-1/4%'" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_title . " NOT LIKE '%-1/2%'" .
            " ORDER BY " .
            Category::$table_name . "." . Category::$col_display;

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            }
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
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

    // Test whether the object exists or not
    static function existObject($object)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Objet::$table_name .
            " WHERE " .
            Objet::$col_title . " = :title" .
            " AND " .
            Objet::$col_id . "<> :id" .
            " AND " .
            Objet::$col_company_id . " = :company_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':title', $object->getTitle(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $object->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $object->getCompany_id(), PDO::PARAM_INT);

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

    static function searchObject($search)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            Objet::$table_name . ".*" .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Company::$table_name . "." . Company::$col_companyName .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            Objet::$table_name . "." . Objet::$col_company_id . " = :company_id " .
            " AND (" .
            Objet::$table_name . "." . Objet::$col_id . " LIKE :search" .
            " OR " .
            Objet::$table_name . "." . Objet::$col_title . " LIKE :search" .
            " OR " .
            Objet::$table_name . "." . Objet::$col_description . " LIKE :search" .
            " OR " .
            "CAST(" . Objet::$table_name . "." . Objet::$col_updateDate . " AS char) LIKE :search" .
            " OR " .
            Company::$table_name . "." . Company::$col_companyName . " LIKE :search" .
            " OR " .
            Category::$table_name . "." . Category::$col_category . " LIKE :search" .
            ") ORDER BY " .
            Category::$table_name . "." . Category::$col_display .
            "";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    // This function is used in Home search bar ,
    // the autocomplete JQueru Ui widget must have an array with label, value params
    static function searchObjectFromOutSide($search)
    {
        $output[] = '';
        $conn = Database::getConnection();

        $query = "SELECT " .
            " CONCAT ( " .
            Company::$table_name . "." . Company::$col_companyName . " , " . " ' - ' " . " , " . Objet::$table_name . "." . Objet::$col_title .
            " )" .
            " AS " . " label " .
            " , " .
            Objet::$table_name . "." . Objet::$col_id .
            " AS " . " value " .
            " , " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE (" .
            Objet::$table_name . "." . Objet::$col_title . " LIKE :search" .
            " OR " .
            Company::$table_name . "." . Company::$col_companyName . " LIKE :search" .
            ") AND (" .
            Objet::$col_title . " NOT like '%1/4%'" .
            " AND " .
            Objet::$col_title . " NOT like '%1/2%'" .
            ")";
        //         echo $query;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    //used in Checkout Menu.php
    static function getAllObjects($company_id)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            Objet::$table_name . ".*" .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Category::$table_name . "." . Category::$col_available .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Media::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " WHERE " .
            Media::$col_mediaPosition . "= '" . Config::$mediaPositionCover . "'" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_company_id . " = :company_id" .
            " ORDER BY " .
            Category::$table_name . "." . Category::$col_display;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        return $output;
    }
    //This function is used in shop.php to get all objects without "aux choix" objects
    static function getAllObjects_Without_AuxChoix($company_id)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            Objet::$table_name . ".*" .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Category::$table_name . "." . Category::$col_available .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_color .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " FROM " .
            Objet::$table_name .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " LEFT JOIN " .
            Media::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " AND " .
            Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .
            " WHERE " .
            Objet::$table_name . "." . Objet::$col_company_id . " = :company_id" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_title . " NOT like '%" . Config::$article_aux_choix . "%'" .
            " GROUP BY " .
            Objet::$table_name . "." . Objet::$col_id .
            " ORDER BY " .
            Category::$table_name . "." . Category::$col_display;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        return $output;
    }

// this function is used in the object page to get all objects prices for printing
static function getAllObjectsPrices($extractData = true) {
    $conn = Database::getConnection();
    
    // Query to get base prices ONLY for objects that don't have variants
    // plus all variant prices for all objects
    $query = "SELECT 
        " . Objet::$table_name . "." . Objet::$col_id . " AS object_id,
        " . Objet::$table_name . "." . Objet::$col_title . " AS title,
        " . Objet::$table_name . "." . Objet::$col_basePrice . " AS price,
        " . Category::$table_name . "." . Category::$col_category . " AS category,
        'Base.P' AS attributeValue,
        " . Company::$table_name . "." . Company::$col_companyName . " AS company,
        " . Category::$table_name . "." . Category::$col_display . " AS category_display
    FROM 
        " . Objet::$table_name . "
    INNER JOIN 
        " . Category::$table_name . " ON " . Objet::$table_name . "." . Objet::$col_category_id . " = " . Category::$table_name . "." . Category::$col_id . "
    INNER JOIN
        " . Company::$table_name . " ON " . Objet::$table_name . "." . Objet::$col_company_id . " = " . Company::$table_name . "." . Company::$col_id . "
    WHERE
        " . Objet::$table_name . "." . Objet::$col_company_id . " = :company_id
        AND NOT EXISTS (
            SELECT 1 FROM " . Price::$table_name . " WHERE " . Price::$table_name . "." . Price::$col_object_id . " = " . Objet::$table_name . "." . Objet::$col_id . "
        )
    
    UNION ALL
    
    SELECT 
        " . Objet::$table_name . "." . Objet::$col_id . " AS object_id,
        " . Objet::$table_name . "." . Objet::$col_title . " AS title,
        " . Price::$table_name . "." . Price::$col_price . " AS price,
        " . Category::$table_name . "." . Category::$col_category . " AS category,
        attribute_value.attributeValue AS attributeValue,
        " . Company::$table_name . "." . Company::$col_companyName . " AS company,
        " . Category::$table_name . "." . Category::$col_display . " AS category_display
    FROM 
        " . Objet::$table_name . "
    INNER JOIN 
        " . Category::$table_name . " ON " . Objet::$table_name . "." . Objet::$col_category_id . " = " . Category::$table_name . "." . Category::$col_id . "
    INNER JOIN
        " . Company::$table_name . " ON " . Objet::$table_name . "." . Objet::$col_company_id . " = " . Company::$table_name . "." . Company::$col_id . "
    INNER JOIN 
        " . Price::$table_name . " ON " . Objet::$table_name . "." . Objet::$col_id . " = " . Price::$table_name . "." . Price::$col_object_id . "
    INNER JOIN 
        attribute_value ON " . Price::$table_name . "." . Price::$col_attributeValue_id . " = attribute_value.id
    WHERE
        " . Objet::$table_name . "." . Objet::$col_company_id . " = :company_id
    ORDER BY 
        category_display,
        title,
        price ASC";
    
        // print_r($query);
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);

    if (!$stmt->execute()) {
        if ($extractData) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        return null;
    }
    
    if ($stmt->rowCount() == 0) {
        if ($extractData) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        return null;
    }
    
    $output = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output[] = $row;
    }
    
    if ($extractData) {
        echo json_encode($output);
        exit;
    }
 
    // print_r($output);
    return $output;
}

    static function getFirstObjectsByNumber($number)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Objet::$table_name .
            " ORDER BY " .
            Objet::$col_updateDate .
            " DESC LIMIT :number";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':number', (int) $number, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    //This function genearte PIZZA Category variantes automatically from pizzaVariants.php
    static function generateVariants($company_id, $category_id, $extractData)
    {

        //Get the name of 1/4 or 1/2 supplement category it must be 1/4_Pizza ou 1/2_Pizza
        $supplCategory = JsonCategory::getCategoryNameById($category_id, False)[0][Category::$col_category];
        //Get the id of the category Pizza to use it to fetch objects
        // $pizzaCategory_id = JsonCategory::getCategoryByName($company_id, Config::$category_Pizza, False)[0][Category::$col_id];
        $pizzaCategory = JsonCategory::getCategoryByName($company_id, Config::$category_Pizza, False);

        //check if 1/4_Pizza Objects and 1/2_Pizza Objects had been already generated
        $objectsSupplement = JsonObject::getAllObjectsByCategoryId($company_id, $category_id, False);
        if ($objectsSupplement !== NULL) {
            echo json_encode(array("state" => "f", "message" => T('variants_already_generated') . " " . $supplCategory . " " . __FUNCTION__));
            exit;
        }     

        foreach ((array)$pizzaCategory as $i => $category) {
            $pizzaCategory_id = $category[Category::$col_id];


            //Fetch Objects of the category Pizza and start script
            $objects = JsonObject::getAllObjectsByCategoryId($company_id, $pizzaCategory_id, False);

            //If there is no Category named "Pizza" we throw an error
            if ($pizzaCategory_id == '') {
                echo json_encode(array("state" => "f", "message" => "Category : " . Config::$category_Pizza . " non trouvée " . __FUNCTION__));
                exit;
            }
            $variantQte = '';
            $variantRatio = 1.0;
            if ($supplCategory == Config::$category_1_4_Pizza) {
                $variantQte = "1/4";
                $variantRatio = 0.25;
            }
            if ($supplCategory == Config::$category_1_2_Pizza) {
                $variantQte = "1/2";
                $variantRatio = 0.5;
            }

            foreach ((array) $objects as $i => $object) {

                $media = JsonMedia::getMediaCoverOfObject($object[Objet::$col_id], False);

                $newObject = new Objet();
                $newMedia = new Media();

                //******* END FOLDER CREATION *************//

                //if the object starts with the suffix "Pizza" we create a new object "

                if (strpos((string)$object[Objet::$col_title], Config::$pizzaObjectsSuffix) === 0) {

                    $newObject->setTitle(Config::$pizzaSupplementSuffix . $variantQte . str_replace(Config::$pizzaObjectsSuffix, "", $object[Objet::$col_title]));
                    $newObject->setDescription($object[Objet::$col_description]);

                     $basePrice = floatval($object[Objet::$col_basePrice]);
                    $baseCost = floatval($object[Objet::$col_baseCost]);

                    //set the new object price and cost by applying the variant ratio 
                    //to the base price and cost of the original object
                    $newObject->setBasePrice($basePrice * $variantRatio);
                    $newObject->setBaseCost($baseCost * $variantRatio);

                    $newObject->setObservation('');
                    $newObject->setCategory_id($category_id);
                    $newObject->setObjAvailable($object[Objet::$col_objAvailable]);
                    $newObject->setCompany_id($company_id); 
                    $id = JsonObject::createObject($newObject, false);

                    // Auto-calculate and insert prices for the variant
                    JsonObject::autoCalculateVariantPrices($object[Objet::$col_id], $id, $variantRatio);

                    //if object has media cover we create a new media for the new 1/4 or 1/2 supplement
                    if ( $media != NULL) {

                         //  Create media
                        // Create new folder for the new Object
                       $mediaDirRel = Config::$object_images_path . '/' . $id . "/";
                        $mediaFolderFs = media_fs_path($mediaDirRel);

                        if (!file_exists($mediaFolderFs)) {
                            mkdir($mediaFolderFs, 0777, true);
                        }
                        $newImage = pathinfo($media[0][Media::$col_media], PATHINFO_BASENAME);
                        $srcFs    = media_url_to_fs($media[0][Media::$col_media]);
                        $destFs   = $mediaFolderFs . $newImage;

                        if (!copy($srcFs, $destFs)) {
                            echo json_encode(array("state" => "f", "message" => "Problem in image copy of: -" . $object[Objet::$col_title] . "- " . __FUNCTION__));
                            exit;
                        }

                        $newMedia->setMedia($mediaDirRel . $newImage);
                        $newMedia->setmediaDescription($media[0][Media::$col_mediaDescription]);
                        $newMedia->setMediaType($media[0][Media::$col_mediaType]);
                        $newMedia->setMediaPosition($media[0][Media::$col_mediaPosition]);
                        $newMedia->setUser_id($media[0][Media::$col_user_id]);
                        $newMedia->setObject_id($id);
                        $id = JsonMedia::create($newMedia, false);
                    }
                }
            }
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    // Helper function to auto-calculate and insert prices for variants
    static function autoCalculateVariantPrices($baseObjectId, $variantObjectId, $ratio)
    {
        // Get all prices for the base object
        $conn = Database::getConnection();
        $query = "SELECT " . Price::$col_attributeValue_id . ", " . Price::$col_price .
                 ", " . Price::$col_cost .
                 " FROM " . Price::$table_name .
                 " WHERE " . Price::$col_object_id . " = :object_id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $baseObjectId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $attributeValueId = $row[Price::$col_attributeValue_id];
            $basePrice = floatval($row[Price::$col_price]);
            $baseCost = floatval($row[Price::$col_cost]);


            // Round generated variant sale prices up to the next 5 dinars.
            $variantPrice = self::roundVariantPrice($basePrice * $ratio);
            $variantCost = self::roundVariantPrice($baseCost * $ratio);  
            // Insert new price for the variant object
            JsonPrice::addPrice($variantObjectId, $attributeValueId, $variantPrice, $variantCost, false);
        }
    }

    // Variant sale prices must never end with  decimal fractions.
    // Always round up to the next 5 dinars: 112.5 -> 115, 375 -> 375, 137.5 -> 140.
    private static function roundVariantPrice($amount)
    {
        return ceil(((float)$amount) / 50) * 50;
    }

    static function deleteVariants($company_id, $category_id, $extractData)
    {
        $supplCategory = JsonCategory::getCategoryNameById($category_id, False)[0][Category::$col_category];
        $objects = JsonObject::getAllObjectsByCategoryId($company_id, $category_id, False);

        // var_dump($objects);
        if ($objects === NULL) {
            echo json_encode(array("state" => "f", "message" => T('object_no_variants') . " " . $supplCategory . " " . __FUNCTION__));
            exit;
        }
        foreach ($objects as $i => $object) {

            JsonObject::deleteObject($object[Objet::$col_id], False);
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    //Function thar print all objects prices
    static function printAllObjectsPrices() {

        $ObjectsPrices = JsonObject::getAllObjectsPrices(false);

        //because Admin can print all suborders, we need to get the printer by company_id 
        //of the user with role checkout
        $printer = JsonPrinter::getCheckoutPrinterByCompanyId($_SESSION["company_id"], false); 

        printAllPrices($ObjectsPrices, $printer);
    
        // echo json_encode($ObjectsPrices);
    
        echo json_encode(array("state" => "s"));
}

}