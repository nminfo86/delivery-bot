<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonTable
 *
 * @author dell
 */

require_once "Database.php";
require_once "Table.php";
require_once "functions.php";

if (isset($_POST['function'])) {

    if (($_POST['function'] === "getAllTables")) {
        JsonTable::getAllTables(true);
    }
    if (($_POST['function'] === "createTable")) {
        confirmLoggedIn();
        JsonTable::createTable(updateTableFromPostVariables());
    }
    if (($_POST['function'] === "updateTable")) {
        //No need for Confirm Logged in function here because we update Table free status from Outside
        JsonTable::updateTable(updateTableFromPostVariables());
    }

    if (($_POST['function'] === "deleteTable") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonTable::deleteTable($_POST['id']);
    }

    if (($_POST['function'] === "searchTable") && (isset($_POST['search']))) {
        JsonTable::searchTable($_POST['search']);
    }

    if (($_POST['function'] === "getTableById") && (isset($_POST['id']))) {
        JsonTable::getTableById($_POST['id'], true);
    }
    if (($_POST['function'] === "getTableByTableName") && (isset($_POST['tableName']))) {
        JsonTable::getTableByTableName($_POST['tableName'], true);
    }
    if (($_POST['function'] === "getTableByTableCode") && (isset($_POST['tableCode']))) {
        JsonTable::getTableByTableCode($_POST['tableCode'], true);
    }
}

function updateTableFromPostVariables()
{

    $table = new Table();

    if (isset($_POST[Table::$col_id])) {
        $existingTable = JsonTable::getTableById($_POST[Table::$col_id], False);
        if ($existingTable !== null) {
            $table = $existingTable;
        }
        if ($_POST[Table::$col_id] == 0) {
            $table->setId(0);
        }
    }

    if (isset($_POST[Table::$col_tableName])) {
        $table->setTableName(trim($_POST[Table::$col_tableName]));
    }
    if (isset($_POST[Table::$col_tableCode])) {
        $table->getTableCode() == 0 ? $table->setTableCode(mt_rand(1000, 9999)) : $table->setTableCode(trim($_POST[Table::$col_tableCode]));
    }
    if (isset($_POST[Table::$col_tableFree])) {
        $table->setTableFree($_POST[Table::$col_tableFree]);
    }
    return $table;
}

class JsonTable
{
    //put your code here

    // Create un object
    static function createTable(Table $table)
    {

        //Test whether object already exists in DB
        if (JsonTable::existTable($table)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Table::$table_name .
            "(" .
            Table::$col_tableName .
            ", " .
            Table::$col_tableCode .
            ", " .
            Table::$col_creationDate .
            ")" .
            " VALUES (:tableName, :tableCode, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':tableName', $table->getTableName(), PDO::PARAM_STR);
        $stmt->bindValue(':tableCode', $table->getTableCode(), PDO::PARAM_STR);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            Table::$col_id .
            " FROM " .
            Table::$table_name .
            " ORDER BY " .
            Table::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        JsonTable::getTableById($id, true);
    }

    static function getAllTables($extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Table::$table_name .
            " ORDER BY " .
            Table::$table_name . "." . Table::$col_tableName;

        $stmt = $conn->prepare($query);
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

    static function searchTable($search)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Table::$table_name .
            " WHERE " .
            Table::$col_tableName . " LIKE :search"  .
            " ORDER BY " .
            Table::$col_tableName;

        $stmt = $conn->prepare($query);
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

    static function getAllFreeTables($extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Table::$table_name .
            " WHERE " .
            Table::$col_tableFree . " = 1";

        $stmt = $conn->prepare($query);
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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function updateTable(Table $table)
    {

        //Test whether object already exists in DB
        if (JsonTable::existTable($table)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();
        $query = "UPDATE " . Table::$table_name .
            " SET " .
            Table::$col_tableName . "= :tableName" .
            " , " .
            Table::$col_tableFree . "= :tableFree" .
            " WHERE " .
            Table::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':tableName', $table->getTableName(), PDO::PARAM_STR);
        $stmt->bindValue(':tableFree', $table->getTableFree(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $table->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        JsonTable::getTableById($table->getId(), TRUE);
    }

    //Delete Table
    static function deleteTable($id)
    {
        //
        $conn = Database::getConnection();
        $query = "DELETE FROM " . Table::$table_name .
            " WHERE " .
            Table::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        echo json_encode(array("state" => "s"));
    }

    static function getTableById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Table::$table_name .
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
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $output[] = $row;
        if ($extractData) {
            echo json_encode($output);
        }
        $table = new Table();
        $table->setId($output[0][Table::$col_id]);
        $table->setTableName($output[0][Table::$col_tableName]);
        $table->setTableFree($output[0][Table::$col_tableFree]);
        $table->setCreationDate($output[0][Table::$col_creationDate]);
        $table->getUpdateDate($output[0][Table::$col_updateDate]);
        return $table;
    }

    static function getTableByTableName($tableName, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Table::$table_name .
            " WHERE " .
            Table::$table_name . "." . Table::$col_tableName . " = :tableName";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);

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
        $table = new Table();
        $table->setId($output[0][Table::$col_id]);
        $table->setTableName($output[0][Table::$col_tableName]);
        $table->setTableFree($output[0][Table::$col_tableFree]);
        $table->setCreationDate($output[0][Table::$col_creationDate]);
        $table->getUpdateDate($output[0][Table::$col_updateDate]);
        return $table;
    }

    static function getTableByTableCode($tableCode, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Table::$table_name .
            " WHERE " .
            Table::$table_name . "." . Table::$col_tableCode . " = :tableCode";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':tableCode', $tableCode, PDO::PARAM_INT);

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
        $table = new Table();
        $table->setId($output[0][Table::$col_id]);
        $table->setTableName($output[0][Table::$col_tableName]);
        $table->setTableCode($output[0][Table::$col_tableCode]);
        $table->setTableFree($output[0][Table::$col_tableFree]);
        $table->setCreationDate($output[0][Table::$col_creationDate]);
        $table->getUpdateDate($output[0][Table::$col_updateDate]);
        return $table;
    }

    // Test whether the Table  exists or not
    static function existTable(Table $table)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Table::$table_name .
            " WHERE " .
            Table::$col_tableName . " = :tableName" .
            " AND " .
            Table::$col_id . "<> :id";

        //        print_r($query);
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':tableName', $table->getTableName(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $table->getId(), PDO::PARAM_INT);

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
}