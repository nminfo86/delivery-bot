<?php

require_once "Database.php";
require_once "User.php";
require_once "Role.php";
require_once "functions.php";
require_once "Config.php";

if (isset($_POST['function'])) {

    if (($_POST['function'] == "getUserByUsernamePassword") && (isset($_POST['username'])) && (isset($_POST['password']))) {
        Authentication::getUserByUsernamePassword($_POST['username'], (hash("sha512", $_POST['password'])));
    }
}


class Authentication
{

    // get User by username
    static function getUserByUsername($username)
    {

        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$col_username .
            " FROM " .
            User::$table_name .
            " WHERE " .
            User::$col_username . " = :username LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        verify();
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$user_not_found));
            exit;
        }
        //        echo json_encode(array("state" => "s"));
    }

    // get User by username and password
    static function getUserByUsernamePassword($username, $password)
    {
        //Check at first if the username is valide or not
        Authentication::getUserByUsername($username);
        //
        //Check if this user is blocked for some time
        Authentication::checkIfBlockedUser($username);
        //
        $conn = Database::getConnection();
        $query = " SELECT " .
            User::$table_name .  ".*" .
            " , " .
            Role::$table_name . "." . Role::$col_role .
            " FROM " .
            User::$table_name .
            " INNER JOIN " .
            Role::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_role_id .
            " = " .
            Role::$table_name . "." . Role::$col_id .
            " WHERE " .
            User::$col_username . " = :username" .
            " AND " .
            User::$col_password . " = :password" .
            " LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        // $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        //Check if this user is connected from some where else, 
        //This is appliquable only from non "admin" role users
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Authentication::checkIfConnectedUser($username);
        //
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            //If the user fails to log-in we increase accesErrors by 1
            Authentication::increaseAccessErrors($username);
            //
            exit;
        }

        $user_id = $row["id"];
        $role = $row["role"];
        $username =  $row["username"];
        $company_id =  $row["company_id"];
        $printer_id =  $row["printer_id"];

        $_SESSION["user_id"] = $user_id;
        $_SESSION["role"] = $username == Config::$roleSuperAdmin ? $username : $role;
        $_SESSION["username"] = $username;
        $_SESSION["company_id"] = $company_id;
        $_SESSION["printer_id"] = $printer_id;

        echo json_encode(array("state" => "s"));

        //If the users has been blocked or not, in both cases we reset it's accesErros to 0
        //We put this function here in case of: if old user has already accessErrors,
        // we reset them after a success log-in
        Authentication::resetAccessErrors($username);

        //Set user as connected to prevent him connecting from some where else,
        //This is appliquable only from non "admin" role users
        if (($row[Role::$col_role] !== Config::$roleAdmin) && ($row[User::$col_username] !== Config::$roleSuperAdmin)) {

            Authentication::setUserConnection($username, 1);
        }
        //
    }

    // Increase access errors to user every wrong loggin to CMS
    static function increaseAccessErrors($username)
    {

        $conn = Database::getConnection();

        $query = "UPDATE " . User::$table_name .
            " SET " .
            User::$col_accessErrors . "=" . User::$col_accessErrors . "+1" .
            " WHERE " .
            User::$col_username . " = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        //If the user has 03 wrong access here we set the nextaccess time after 05 minutes
        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$col_accessErrors .
            " FROM " .
            User::$table_name .
            " WHERE " .
            User::$col_username . " = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row["accessErrors"] == Config::$access_errors) {
            //creat current date + 5 minutes
            $currentDate = strtotime(date('Y-m-d H:i:s'));
            $futureDate = $currentDate + (60 * Config::$user_wait_time);
            $nextAccess = date("Y-m-d H:i:s", $futureDate);
            //
            //If the user has fails for 03 times we set a timeout to re log-in again
            Authentication::updateNextAccess($username, $nextAccess);
            //
        }
    }

    // Reset access errors of a user
    static function resetAccessErrors($username)
    {

        $conn = Database::getConnection();

        $query = "UPDATE " . User::$table_name .
            " SET " .
            User::$col_accessErrors . "=0" .
            ", " .
            User::$col_nextAccess . " = :nextAccessTime" .
            " WHERE " .
            User::$col_username . " = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':nextAccessTime', "1000-01-01 00:00:00", PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    // set user as connected
    static function setUserConnection($username, $connection)
    {

        $conn = Database::getConnection();

        $query = "UPDATE " . User::$table_name .
            " SET " .
            User::$col_connected . " = :connection" .
            " WHERE " .
            User::$col_username . " = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':connection', $connection, PDO::PARAM_INT);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    //Set 05 minutes timeout to the blocked user
    static function updateNextAccess($username, $nextAccessTime)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . User::$table_name .
            " SET " .
            User::$col_nextAccess . "= :nextAccessTime" .
            " WHERE " .
            User::$col_username . " = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nextAccessTime', $nextAccessTime, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    //Chech whether the user is blocked or not
    static function checkIfBlockedUser($username)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$col_accessErrors .
            ", " .
            User::$col_nextAccess .
            " FROM " .
            User::$table_name .
            " WHERE " .
            User::$col_username . " = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentDate = date('Y-m-d H:i:s');
        if ($row["accessErrors"] == 3) {
            if ($row["nextAccess"] > $currentDate) {
                echo json_encode(array("state" => "f", "message" => Config::$user_still_blocked . " " . __FUNCTION__));
                exit;
            } else {
                Authentication::resetAccessErrors($username);
            }
        }
    }

    //Chech whether the user is connected or not
    static function checkIfConnectedUser($username)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            User::$col_connected .
            " FROM " .
            User::$table_name .
            " WHERE " .
            User::$col_username . " = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row[User::$col_connected] == 1) {
            echo json_encode(array("state" => "f", "message" => Config::$user_still_connected . " " . __FUNCTION__));
            exit;
        }
    }
}