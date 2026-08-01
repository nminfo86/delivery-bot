<?php

require_once "Database.php";
require_once "User.php";
require_once "Role.php";
require_once "JsonRole.php";
require_once "Licence.php";
require_once "JsonLicence.php";
require_once "functions.php";


if (isset($_POST['function'])) {

    if ($_POST['function'] === "create") {
        confirmLoggedIn();
        JsonUser::create(createUsertFromGetVariables());
    }
    if ($_POST['function'] === "update") {
        confirmLoggedIn();

        JsonUser::update(createUsertFromGetVariables(), JsonUser::$roleCheck);
    }
    if (($_POST['function'] === "delete") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonUser::delete($_POST['id']);
    }
    // if ($_POST['function'] === "getAllUsersExceptAdmin") {
    //     confirmLoggedIn();
    //     JsonUser::getAllUsersExceptAdmin();   
    // }
    if (($_POST['function'] === "getUserById") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonUser::getUserById($_POST['id'], TRUE);
    }
    if (($_POST['function'] === "getUsernameById") && (isset($_POST['id']))) {
        JsonUser::getUsernameById($_POST['id'],TRUE);
    }
    if (($_POST['function'] === "getUserByUsername") && (isset($_POST['username']))) {
        confirmLoggedIn();
        JsonUser::getUserByUsername($_POST['username']);
    }
    if (($_POST['function'] === "getAdminUserOfCompany") && (isset($_POST['company_id']))) {
        confirmLoggedIn();
        JsonUser::getAdminUserOfCompany($_POST['company_id']);
    }
    if (($_POST['function'] === "searchUser") && (isset($_POST['search']))) {
        confirmLoggedIn();
        JsonUser::searchUser($_SESSION["company_id"], $_POST['search']);
    }
}

function createUsertFromGetVariables()
{
    $user = new User();
    if (isset($_POST[User::$col_id])) {
        $data = JsonUser::getUserById($_POST[User::$col_id], FALSE);
        $user->setId($_POST[User::$col_id]);
        //
        // Check whether Role changed or not, if yes we check role number in Licence when update
        if ($data !== 0 && $data[0][User::$col_role_id] !== $_POST[User::$col_role_id]) {
            JsonUser::$roleCheck = True;
        }
    }

    $user->setUsername($_POST[User::$col_username]);

    if ($_POST[User::$col_password] !== "NULL") {
        $user->setPassword(hash("sha512", $_POST[User::$col_password]));
    } else {
        if ($_POST[User::$col_id] != 0) {
        $user->setPassword($data[0][User::$col_password]);
        //if it is a new user with password null, we trigger a user error
        } else {
              echo json_encode(array("state" => "f", "message" => Config::$user_define_password ));
              exit;
        }
    }

    $user->setName(trim($_POST[User::$col_name]));
    $user->setFamilyName(trim($_POST[User::$col_familyName]));
    $user->setEmail(trim($_POST[User::$col_email]));

    //IF create user from userManagement we use $_POST[User::$col_connected]
    //IF create user from companyManagement we use 0
    $user->setConnected(isset($_POST[User::$col_connected]) ? $_POST[User::$col_connected] : 0);
    $user->setRole_id($_POST[User::$col_role_id]);
    //IF create user from userManagement we use $_SESSION["company_id"]
    //IF create user from companyManagement we use $_POST[User::$col_company_id]
    $user->setCompany_id(isset($_POST[User::$col_company_id]) ? $_POST[User::$col_company_id] : $_SESSION["company_id"]);

    $user->setPrinter_id($_POST[User::$col_printer_id] == 'null' ? NULL : $_POST[User::$col_printer_id]);


    return $user;
}

class JsonUser
{

    static $roleCheck = False;

    // create user
    static function create(User $user)
    {
        // $licence = JsonLicence::getLicence($user->getCompany_id(), true);
        $role = JsonRole::getRoleById($user->getRole_id(), false)[0][Role::$col_role];
        // $licenceRole = $role . "Users"; //The column names in Licence table starts with "Role name" + "Users" see Licence Table in Database

        //Test whether Licence accept user role quantity
        // if ((int)$licence[0][$licenceRole] <= (int)JsonUser::countUserByRole($user->getRole_id(), $user->getCompany_id())) {
        //     echo json_encode(array("state" => "f", "message" => Config::$licence_limited));
        //     exit;
        // }
        //Test whether object already exists in DB
        if (JsonUser::existUser($user)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . User::$table_name .
            " ( " .
            User::$col_username .
            " , " .
            User::$col_password .
            " , " .
            User::$col_name .
            " , " .
            User::$col_familyName .
            " , " .
            User::$col_email .
            " , " .
            User::$col_connected .
            " , " .
            User::$col_nextAccess .
            " , " .
            User::$col_role_id .
            " , " .
            User::$col_company_id .
            " , " .
            User::$col_printer_id .
            " , " .
            User::$col_creationDate .
            " ) " .
            " VALUES (:username, :hashPsw, :name, :familyName, :email, :connected, :nextAccess, :role_id, :company_id, :printer_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':hashPsw', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':name', $user->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':familyName', $user->getFamilyName(), PDO::PARAM_STR);
        $stmt->bindValue(':connected', $user->getConnected(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':nextAccess', "1000-01-01 00:00:00", PDO::PARAM_STR);
        $stmt->bindValue(':role_id', $user->getRole_id(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $user->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':printer_id', $user->getPrinter_id(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            User::$col_id .
            " FROM " .
            User::$table_name .
            " ORDER BY " .
            User::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => getMsgPdoStmt($stmt) . " " . __FUNCTION__));
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        JsonUser::getUserById($id, TRUE);
    }

    // update user
    static function update(User $user, $roleCheck)
    {
        $licence = JsonLicence::getLicence($user->getCompany_id(), false);
        $role = JsonRole::getRoleById($user->getRole_id(), false)[0][Role::$col_role];
        $licenceRole = $role . "Users"; //The column names in Licence table starts with "Role name" + "Users" see Licence Table in Database

        //Test whether Licence accept user role quantity
        // if (JsonUser::$roleCheck) {
        //     if ((int)$licence[0][$licenceRole] <= (int)JsonUser::countUserByRole($user->getRole_id(), $user->getCompany_id())) {
        //         echo json_encode(array("state" => "f", "message" => Config::$licence_limited));
        //         exit;
        //     }
        // }
        //Test whether object already exists in DB
        if (JsonUser::existUser($user)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }

        $conn = Database::getConnection();

        $query = "UPDATE " . User::$table_name .
            " SET " .
            User::$col_username . " = :username " .
            " , " .
            User::$col_password . " = :password " .
            " , " .
            User::$col_name . " = :name " .
            " , " .
            User::$col_familyName . " = :familyName " .
            " , " .
            User::$col_email . " = :email" .
            " , " .
            User::$col_connected . " = :connected" .
            " , " .
            User::$col_role_id . " = :role_id" .
            " , " .
            User::$col_company_id . " = :company_id" .
            " , " .
            User::$col_printer_id . " = :printer_id" .
            " WHERE " .
            User::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':name', $user->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':familyName', $user->getFamilyName(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':connected', $user->getConnected(), PDO::PARAM_STR);
        $stmt->bindValue(':role_id', $user->getRole_id(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $user->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':printer_id', $user->getPrinter_id(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonUser::getUserById($user->getId(), TRUE);
    }

    // Delete user
    static function delete($id)
    {
        //
        $conn = Database::getConnection();

        $query = "DELETE FROM " . User::$table_name .
            " WHERE " .
            User::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        echo json_encode(array("state" => "s"));
    }
    //
    // THis function is used in ajaxUser. we search only users that does not have Role Admin
    static function searchUser($company_id, $search)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            User::$table_name . ".*" .
            " , " .
            Role::$table_name . "." . Role::$col_role .
            " , " .
            Company::$table_name . "." . Company::$col_companyName .
            " FROM " .
            User::$table_name .
            " INNER JOIN " .
            Role::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_role_id .
            " = " .
            Role::$table_name . "." . Role::$col_id .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            Role::$table_name . "." . Role::$col_role . " <> :roleAdmin" .
            " AND " .
            Role::$table_name . "." . Role::$col_role . " <> :roleSuperAdmin" .
            " AND " .
            User::$table_name . "." . USer::$col_company_id . " =:company_id" .
            " AND (" .
            User::$table_name . "." . User::$col_username . " LIKE :search " .
            " OR " .
            User::$table_name . "." . User::$col_name . " LIKE :search " .
            " OR " .
            User::$table_name . "." . User::$col_familyName . " LIKE :search " .
            " OR " .
            User::$table_name . "." . User::$col_email . " LIKE :search " .
            " OR " .
            Role::$table_name . "." . Role::$col_role . " LIKE :search " .
            " OR " .
            Company::$table_name . "." . Company::$col_companyName . " LIKE :search )";

        //        print_r($query);
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':roleAdmin', Config::$roleAdmin, PDO::PARAM_STR);
        $stmt->bindValue(':roleSuperAdmin', Config::$roleSuperAdmin, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);

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

    // Get User by id
    static function getUserById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$table_name . ".*" .
            " , " .
            Role::$table_name . "." . Role::$col_role .
            " , " .
            Company::$table_name . "." . Company::$col_companyName .
            " FROM " .
            User::$table_name .
            " INNER JOIN " .
            Role::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_role_id .
            " = " .
            Role::$table_name . "." . Role::$col_id .
            " INNER JOIN " .
            Company::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            User::$table_name . "." . User::$col_id . " = :id LIMIT 1";

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
            }
            return 0;
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    // Get Username by id
    static function getUsernameById($id,$extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$col_username .
            " FROM " .
            User::$table_name .
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
            }
            return 0;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;

    }

    // Get User by username
    static function getUserByUsername($username)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            User::$table_name .
            " WHERE " . User::$col_username . " = :username LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

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
        echo json_encode($output);
    }

    static function existUser(User $user)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            User::$table_name .
            " WHERE " .
            User::$col_username . " = :username" .
            " AND " .
            User::$col_id . "<> :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);

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

    static function countUserByRole($role_id, $company_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            " COUNT(id) as number " .
            " FROM " .
            User::$table_name .
            " WHERE " .
            User::$col_role_id . " = :role_id " .
            " AND " .
            User::$col_company_id . " = :company_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return $output[0]["number"];
    }

    // This function is used in superAdminPanel.php for company management
    static function getAdminUserOfCompany($company_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$table_name . " .*" .
            " FROM " .
            User::$table_name .
            " INNER JOIN " .
            Role::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_role_id .
            " = " .
            Role::$table_name . "." . Role::$col_id .
            " WHERE " .
            User::$col_company_id . " = :company_id " .
            " AND " .
            Role::$col_role . " = :role";


        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':role', Config::$roleAdmin, PDO::PARAM_STR);

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
        echo json_encode($output);
    }
}