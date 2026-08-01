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
require_once "Type_Charge.php";
require_once "Config.php";
require_once "functions.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "getAllTypeCharge") {
        JsonType_Charge::getAllTypeCharge($_SESSION['company_id'] ,true);
    }
    if ($_POST['function'] === "createTypeCharge") {
        confirmLoggedIn();
        JsonType_Charge::createTypeCharge(updateTypeChargeFromPostVariables());
    }
    if ($_POST['function'] === "updateTypeCharge") {
        JsonType_Charge::updateTypeCharge(updateTypeChargeFromPostVariables());
    }
    if ($_POST['function'] === "deleteTypeCharge" && isset($_POST['id'])) {
        confirmLoggedIn();
        JsonType_Charge::deleteTypeCharge($_POST['id']);
    }
    if ($_POST['function'] === "searchTypeCharge" && isset($_POST['search'])) {
        JsonType_Charge::searchTypeCharge($_SESSION['company_id'], $_POST['search']);
    }
    if ($_POST['function'] === "getTypeChargeById" && isset($_POST['id'])) {
        JsonType_Charge::getTypeChargeById($_POST['id'], true);
    }
}

function updateTypeChargeFromPostVariables()
{
    $typeCharge = new Type_Charge();

    if (isset($_POST[Type_Charge::$col_id])) {
        $existingTypeCharge = JsonType_Charge::getTypeChargeById($_POST[Type_Charge::$col_id], false);
        if ($existingTypeCharge !== null && is_array($existingTypeCharge) && count($existingTypeCharge) > 0) {
            $existingObj = $existingTypeCharge[0];
            if ($existingObj instanceof Type_Charge) {
                $typeCharge->setId($existingObj->getId());
                $typeCharge->setTypeCharge(trim($existingObj->getTypeCharge()));
                $typeCharge->setCreationDate($existingObj->getCreationDate());
                $typeCharge->setCompany_id($existingObj->getCompany_id());
            }
        }
        if ($_POST[Type_Charge::$col_id] == 0) {
            $typeCharge->setId(0);
            $typeCharge->setCompany_id($_SESSION['company_id']);
        }
    }

    if (isset($_POST[Type_Charge::$col_typeCharge])) {
        $typeCharge->setTypeCharge(trim($_POST[Type_Charge::$col_typeCharge]));
    }
    return $typeCharge;
}

class JsonType_Charge
{
    static function createTypeCharge(Type_Charge $typeCharge)
    {
        if (JsonType_Charge::existTypeCharge($typeCharge)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Type_Charge::$table_name .
            "(" .
            Type_Charge::$col_typeCharge . ", " .
            Type_Charge::$col_company_id . ", " .
            Type_Charge::$col_creationDate .
            ")" .
            " VALUES (:typeCharge, :company_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':typeCharge', $typeCharge->getTypeCharge(), PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " . Type_Charge::$col_id . " FROM " . Type_Charge::$table_name . " ORDER BY " . Type_Charge::$col_id . " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        JsonType_Charge::getTypeChargeById($id, true);
    }

    static function getAllTypeCharge($company_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Type_Charge::$table_name .
            " WHERE " . Type_Charge::$col_company_id . " = :company_id" .
            " ORDER BY " . Type_Charge::$col_typeCharge;

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
            }
            return NULL;
        }

        $output = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function searchTypeCharge($company_id, $search)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Type_Charge::$table_name .
            " WHERE " . Type_Charge::$col_typeCharge . " LIKE :search" .
            " AND " . Type_Charge::$col_company_id . " = :company_id" .
            " ORDER BY " . Type_Charge::$col_typeCharge;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    static function updateTypeCharge(Type_Charge $typeCharge)
    {
        if (JsonType_Charge::existTypeCharge($typeCharge)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();
        $query = "UPDATE " . Type_Charge::$table_name .
            " SET " .
            Type_Charge::$col_typeCharge . " = :typeCharge" .
            " , " . Type_Charge::$col_company_id . " = :company_id" .
            " WHERE " . Type_Charge::$col_id . " = :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':typeCharge', $typeCharge->getTypeCharge(), PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $typeCharge->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $typeCharge->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonType_Charge::getTypeChargeById($typeCharge->getId(), true);
    }

    static function deleteTypeCharge($id)
    {
        $conn = Database::getConnection();

        // Check if type_charge is used in charge table
        $checkQuery = "SELECT COUNT(*) as cnt FROM charge WHERE typeCharge_id = :id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$checkStmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($checkStmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($row['cnt'] > 0) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error_Cannot_delete . "'Type Charge' is used in some 'Charges'"));
            exit;
        }

        // Proceed with delete if not used
        $query = "DELETE FROM " . Type_Charge::$table_name .
            " WHERE " . Type_Charge::$col_id . " = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        echo json_encode(array("state" => "s"));
    }

    static function getTypeChargeById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Type_Charge::$table_name . " WHERE id = :id LIMIT 1";

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
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $output[] = $row;
        if ($extractData) {
            echo json_encode($output);
        }
        $typeChargeObj = new Type_Charge();
        $typeChargeObj->setId($output[0][Type_Charge::$col_id]);
        $typeChargeObj->setTypeCharge($output[0][Type_Charge::$col_typeCharge]);
        $typeChargeObj->setCreationDate($output[0][Type_Charge::$col_creationDate]);
        $typeChargeObj->setCompany_id($output[0][Type_Charge::$col_company_id]);
        return array($typeChargeObj);
    }

    static function existTypeCharge(Type_Charge $typeCharge)
    {

        // print_r($typeCharge); 
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Type_Charge::$table_name .
            " WHERE " . Type_Charge::$col_typeCharge . " = :typeCharge" .
            " AND " . Type_Charge::$col_id . " <> :id" .
            " AND " . Type_Charge::$col_company_id . " = :company_id";

            // print_r($query);
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':typeCharge', $typeCharge->getTypeCharge(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $typeCharge->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $typeCharge->getCompany_id(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        return $stmt->rowCount() > 0;
    }
}