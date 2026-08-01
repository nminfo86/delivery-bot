<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonAttribute
 *
 * @author dell
 */

require_once "Database.php";
require_once "Role.php";
require_once "Config.php";
require_once "functions.php";

if (isset($_POST['function'])) {
    if ($_POST['function'] === "getAllRolesExceptAdmin") {
        JsonRole::getAllRolesExceptAdmin(true);
    }
}

class JsonRole
{
    //put your code here
    //THis function is used in adminPanel in userManagement
    static function getAllRolesExceptAdmin($extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Role::$table_name .
            " WHERE " .
            Role::$table_name . "." . Role::$col_role . " <> :roleAdmin" .
            " AND " .
            Role::$table_name . "." . Role::$col_role . " <> :roleSuperAdmin";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':roleAdmin', Config::$roleAdmin, PDO::PARAM_STR);
        $stmt->bindValue(':roleSuperAdmin', Config::$roleSuperAdmin, PDO::PARAM_STR);

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
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getRoleById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Role::$table_name .
            " WHERE " .
            Role::$table_name . "." . Role::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

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

        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getRoleByRole($role, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Role::$table_name .
            " WHERE " .
            Role::$table_name . "." . Role::$col_role . " = :role";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

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

        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }
}