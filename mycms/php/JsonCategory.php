<?php

/**
 * Description of JsonCategory
 *
 * @author Nminfo
 */
require_once "Database.php";
require_once "Category.php";
require_once "functions.php";
require_once "User_Category.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "createCategory") {
        confirmLoggedIn();
        JsonCategory::createCategory(createCategoryFromGetVariables());
    }
    if ($_POST['function'] === "updateCategory") {
        confirmLoggedIn();
        JsonCategory::updateCategory(createCategoryFromGetVariables());
    }
    if (($_POST['function'] === "deleteCategory") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonCategory::deleteCategory($_POST['id']);
    }
    if (($_POST['function'] === "getCategoryById") && (isset($_POST['id']))) {
        JsonCategory::getCategoryById($_POST['id'], true);
    }
    if (($_POST['function'] === "getCategoryNameById") && (isset($_POST['id']))) {
        JsonCategory::getCategoryNameById($_POST['id'], true);
    }
    if (($_POST['function'] === "getCategoryByName") && (isset($_POST['company_id'])) && (isset($_POST['category']))) {
        JsonCategory::getCategoryByName($_POST['company_id'], $_POST['category'], true);
    }

    if ($_POST['function'] === "getAllCategories") {
        JsonCategory::getAllCategories($_SESSION["company_id"], true);
    }
    if ($_POST['function'] === "getAllCategoriesByPreparation") {
        JsonCategory::getAllCategoriesByPreparation($_SESSION["company_id"], $_POST['preparation'], true);
    }
    if ($_POST['function'] === "getCountCategories") {
        JsonCategory::getCountCategories(true);
    }

    if (($_POST['function'] === "searchCategory") && (isset($_POST['search']))) {
        JsonCategory::searchCategory($_POST['search']);
    }

    if (($_POST['function'] === "uploadMedia") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonCategory::upload($_POST['id']);
    }

    if (($_POST['function'] === "deleteCategoryCover") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonCategory::deleteCategoryCover($_POST['id'], TRUE);
    }
}

function createCategoryFromGetVariables()
{
    $category = new Category();
    if (isset($_POST[Category::$col_id])) {
        $category->setId($_POST[Category::$col_id]);
    }
    $category->setCategory(trim($_POST[Category::$col_category]));
    $category->setPrepare($_POST[Category::$col_prepare]);
    $category->setAvailable($_POST[Category::$col_available]);
    $category->setSupplement($_POST[Category::$col_supplement]);
    $category->setAcceptSupplement($_POST[Category::$col_acceptSupplement]);
    $category->setColor($_POST[Category::$col_color]);
    $category->setDisplay($_POST[Category::$col_display]);

    //Get the user company_id from the session already created in Authentication.php
    $category->setCompany_id($_SESSION["company_id"]);
    //

    return $category;
}

class JsonCategory
{

    // Create un object
    static function createCategory(Category $category)
    {

        //Test whether object already exists in DB
        if (JsonCategory::existCategory($category)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Category::$table_name .
            "(" .
            Category::$col_category .
            ", " .
            Category::$col_prepare .
            ", " .
            Category::$col_available .
            ", " .
            Category::$col_supplement .
            ", " .
            Category::$col_acceptSupplement .
            ", " .
            Category::$col_color .
            ", " .
            Category::$col_display .
            ", " .
            Category::$col_company_id .
            ", " .
            Category::$col_creationDate .
            ")" .
            " VALUES (:category, :prepare, :available, :supplement, :acceptSupplement, :color, :display, :company_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':category', $category->getCategory(), PDO::PARAM_STR);
        $stmt->bindValue(':prepare', $category->getPrepare(), PDO::PARAM_BOOL);
        $stmt->bindValue(':available', $category->getAvailable(), PDO::PARAM_BOOL);
        $stmt->bindValue(':supplement', $category->getSupplement(), PDO::PARAM_BOOL);
        $stmt->bindValue(':acceptSupplement', $category->getAcceptSupplement(), PDO::PARAM_BOOL);
        $stmt->bindValue(':color', $category->getColor(), PDO::PARAM_STR);
        $stmt->bindValue(':display', $category->getDisplay(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $category->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            Category::$col_id .
            " FROM " .
            Category::$table_name .
            " ORDER BY " .
            Category::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        JsonCategory::getCategoryById($id, true);
    }

    //Update object
    static function updateCategory(Category $category)
    {

        //Test whether object already exists in DB
        if (JsonCategory::existCategory($category)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        //test whether category was preparable before update and whether it will be preparable after update to decide 
        //if we need to delete the user category associations or not
        $wasPreparable = JsonCategory::isCategoryPreparableById($category->getId());
        $willBePreparable = ((int)$category->getPrepare() === 1);

        $query = "UPDATE " . Category::$table_name .
            " SET " .
            Category::$col_category . "= :category" .
            ", " .
            Category::$col_prepare . "= :prepare" .
            ", " .
            Category::$col_available . "= :available" .
            ", " .
            Category::$col_supplement . "= :supplement" .
            ", " .
            Category::$col_acceptSupplement . "= :acceptSupplement" .
            ", " .
            Category::$col_color . "= :color" .
            ", " .
            Category::$col_display . "= :display" .
            ", " .
            Category::$col_company_id . "= :company_id" .
            " WHERE " .
            Category::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':category', $category->getCategory(), PDO::PARAM_STR);
        $stmt->bindValue(':prepare', $category->getPrepare(), PDO::PARAM_BOOL);
        $stmt->bindValue(':available', $category->getAvailable(), PDO::PARAM_BOOL);
        $stmt->bindValue(':supplement', $category->getSupplement(), PDO::PARAM_BOOL);
        $stmt->bindValue(':acceptSupplement', $category->getAcceptSupplement(), PDO::PARAM_BOOL);
        $stmt->bindValue(':color', $category->getColor(), PDO::PARAM_STR);
        $stmt->bindValue(':display', $category->getDisplay(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $category->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $category->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        if ($wasPreparable && !$willBePreparable) {
            JsonCategory::deleteUserCategoryAssociationsByCategoryId($category->getId());
        }
        JsonCategory::getCategoryById($category->getId(), true);
    }

    //Delete Category
    static function deleteCategory($id)
    {

        //get the category folder path and save it before delete category from database
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$col_categoryCover .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $cover = $output[0][Category::$col_categoryCover];
        //
        //
        
        //Delete category from database
        $query = "DELETE FROM " . Category::$table_name .
            " WHERE " .
            Category::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
                        $errorInfo = $stmt->errorInfo();
                        // MySQL error code 1451: Cannot delete or update a parent row: a foreign key constraint fails
                        if (isset($errorInfo[1]) && $errorInfo[1] == 1451) {
                                echo json_encode(array(
                                        "state" => "f",
                                        "message" => Config::$user_error_Cannot_delete . "Category has 'Articles'."
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

        if ($cover !== null && strpos($cover, 'http') === false) {
            JsonCategory::deleteCoverFromServer($id, $cover);
        }
        //

        echo json_encode(array("state" => "s"));
    }

    // Get Category by id
    static function getCategoryById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$table_name . ".*" .
            ", " .
            Company::$table_name . "." . Company::$col_companyName .
            " FROM " .
            Category::$table_name .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            Category::$table_name . "." . Category::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            Category::$table_name . "." . Category::$col_id . " = :id LIMIT 1";

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


    static function getCategoryNameById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$col_category .
            " FROM " .
            Category::$table_name .
            " WHERE id = :id LIMIT 1";

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

    //This function is used in superAdmin Bulk Action panel
    static function getCategoryByName($company_id, $category, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$table_name . "." . Category::$col_company_id . " = :company_id" .
            " AND " .
            " category LIKE :category";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindValue(':category',  $category . '%', PDO::PARAM_STR);

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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }

        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }
    //This funciton is used in shop.php to get all categories
    //Without the 1/4_Pizza and 1/2_Pizza
    static function getAllCategoriesWithout_1_4_1_2($company_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_company_id . " =:company_id" .
            " AND " .
            Category::$col_category . " NOT like '1/4%'" .
            " AND " .
            Category::$col_category . " NOT like '1/2%'" .
            " ORDER BY " .
            Category::$col_display;


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
    //This funciton is used for CheckoutMenu.php for POS Menu HMI to get all categories
    //included the 1/4_Pizza and 1/2_Pizza
    static function getAllCategories($company_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_company_id . " =:company_id" .
            " ORDER BY " .
            Category::$col_display;

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
    //This function is used in user.js and waiterHistory.php to display all  categories by preparation
    static function getAllCategoriesByPreparation($company_id, $preparation, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_prepare . " = :preparation" .
            " AND " .
            Category::$col_supplement . " = 0" .
            " AND " .
            Category::$col_company_id . " = :company_id" .
            " ORDER BY " .
            Category::$col_display;

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':preparation', $preparation, PDO::PARAM_BOOL);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);

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

    //This function is used in user.js to display all preparable categories
    static function getAllPrepareCategoriesByUserID($user_id, $extractData)
    {
        $username = isset($_SESSION["username"]) ? $_SESSION["username"] : 0;
        
        $conn = Database::getConnection();

        //Test whether the user is chef all or not
        //if it is chef all we get all categories without exception
        // if ($username === Config::$userChefAll){
        if ($username !== null && strpos($username, Config::$userChefAll) === 0) {
           

        $query = "SELECT * " .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_prepare . " ='1'" .
            " AND " .
            Category::$col_company_id . " = :company_id" .
            " ORDER BY " .
            Category::$col_display;

        $stmt = $conn->prepare($query);
         $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
        }
        //if it is not chef all we get only the categories that are linked to the user
        else{

       $query = "SELECT * " .
            " FROM " .
            Category::$table_name .
            " INNER JOIN " .
            User_Category::$table_name .
            " ON " .
            Category::$table_name . "." . Category::$col_id .
            " = " .
            User_Category::$table_name . "." . User_Category::$col_category_id .
            " WHERE " .
            Category::$col_prepare . " ='1'" .
            " AND " .
            User_Category::$col_user_id . " = :user_id" .
             " AND " .
            Category::$col_company_id . " = :company_id" .
            " ORDER BY " .
            Category::$col_display;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
        }

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
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
        return $output;
    }

    // Test whether the Category exists or not
    static function existCategory(Category $category)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_category . " = :category" .
            " AND " .
            Category::$col_id . "<> :id" .
            " AND " .
            Category::$col_company_id . "= :company_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category', $category->getCategory(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $category->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $category->getCompany_id(), PDO::PARAM_INT);

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
    // Return if category is prepare or not
    static function isPrepare($object_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$table_name . "." . Category::$col_id .
            " FROM " .
            Category::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " WHERE " .
            Objet::$table_name . "." . Objet::$col_id . " = :object_id" .
            " AND " .
            Category::$col_prepare . " IS TRUE";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_INT);

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

    static function searchCategory($search)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$table_name . ".*" .
            ", " .
            Company::$table_name . "." . Company::$col_companyName .
            " FROM " .
            Category::$table_name .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            Category::$table_name . "." . Category::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            Category::$col_company_id . " = :company_id"  .
            " AND " .
            Category::$col_category . " LIKE :search"  .
            " ORDER BY " .
            Category::$col_display;

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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }
    // Upload Category Cover
    static function upload($id)
    {
        if (JsonCategory::havecategoryCover($id)) {
            echo json_encode(array("state" => "f", "message" => Config::$have_media));
            exit;
        }
        $mediaPathToStore = "";

        if (isset($_FILES['category-media'])) {
            $tmp_file = $_FILES['category-media']['tmp_name'];
            $fileExtension = pathinfo($_FILES['category-media']['name'], PATHINFO_EXTENSION);

            $mediaDirRel = Config::$category_images_path . '/' . $id . "/";
            $mediaDirFs  = media_fs_path($mediaDirRel);
            if (!file_exists($mediaDirFs)) {
                mkdir($mediaDirFs, 0777, true);
            }
            $fileName = date('Y-m-d-H-i-s') . "." . strtolower($fileExtension);
            $mediaPathFs = $mediaDirFs . $fileName;

            if (!move_uploaded_file($tmp_file, $mediaPathFs)) {
                echo json_encode(array("state" => "f", "message" => Config::$fail_upload_file . " " . __FUNCTION__));
                exit;
            }
            $mediaPathToStore = $mediaDirRel . $fileName; // store web-relative path
        } else {
            // YouTube cover
            $mediaPathToStore = Config::$youtube_embed_suffix . $_POST[Category::$col_categoryCover];
        }

        $conn = Database::getConnection();
        $query = "UPDATE " . Category::$table_name .
            " SET " .
            Category::$col_categoryCover . "= :categoryCover" .
            " WHERE " .
            Category::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':categoryCover', $mediaPathToStore, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonCategory::getCategoryById($id, true);
    }
    // Test whether the Category have cover or not. this is needed when upload a cover
    static function havecategoryCover($id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$col_categoryCover .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_null($output[0][Category::$col_categoryCover])) {
            return false;
        } else {
            return true;
        }
    }

    static function deleteCategoryCover($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$col_categoryCover .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($output) 
    && isset($output[0][Category::$col_categoryCover]) 
    && strpos((string)$output[0][Category::$col_categoryCover], 'http') !== false) {
            //setcategorycover to null and returne json s message
            JsonCategory::setCategoryCoverNull($id);
        } else {

            //Delete Category cover file
            JsonCategory::deleteCoverFromServer($id, $output[0][Category::$col_categoryCover]);
            //
            JsonCategory::setCategoryCoverNull($id);
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    static function setCategoryCoverNull($id)
    {
        $conn = Database::getConnection();
        $query = "UPDATE " . Category::$table_name .
            " SET " .
            Category::$col_categoryCover . "= :categoryCover" .
            " WHERE " .
            Category::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':categoryCover', NULL, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    static function deleteCoverFromServer($id, $cover)
    {
        $fsPath = media_url_to_fs($cover);
        if (!unlink($fsPath)) {
            echo json_encode(array("state" => "f", "message" => Config::$fail_delete_file . " " . __FUNCTION__));
            exit;
        }
        // Delete directory if empty
        $mediaFolder = dirname($fsPath);
        @rmdir($mediaFolder);
    }

    static function getCountCategories($extractData)
    {
        $conn = Database::getConnection();
        $query = "Select " .
            " count(id) as number" .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_company_id . " =:company_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);

            return $output[0]["number"];
        }
    }

    // Test whether the Category have cover or not. this is needed when upload a cover
    static function existCategoryWithSameDisplay($display)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$col_id .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_display . " = :display";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':display', $display, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            return false;
        } else {
            return true;
        }
    }

    //This functions are used to help add feature of delete category from user_Category table
    //when user decided to make category not preparable, so we need to delete all associations of this category with users 
    static function isCategoryPreparableById($id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Category::$col_prepare .
            " FROM " .
            Category::$table_name .
            " WHERE " .
            Category::$col_id . " = :id LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)$row[Category::$col_prepare] === 1);
    }

    //helper
    static function deleteUserCategoryAssociationsByCategoryId($category_id)
    {
        $conn = Database::getConnection();

        $query = "DELETE FROM " . User_Category::$table_name .
            " WHERE " .
            User_Category::$col_category_id . " = :category_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }
}