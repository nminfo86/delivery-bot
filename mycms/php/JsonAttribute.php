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
require_once "CmsAttribute.php";
require_once "functions.php";

if (isset($_POST['function'])) {
        if ($_POST['function'] === "create") {
                confirmLoggedIn();
                JsonAttribute::create(createAttributeFromPostVariables());
        }
        if ($_POST['function'] === "update") {
                confirmLoggedIn();
                JsonAttribute::update(createAttributeFromPostVariables());
        }
        if (($_POST['function'] === "delete") && (isset($_POST['id']))) {
                confirmLoggedIn();
                JsonAttribute::delete($_POST['id']);
        }
        if (($_POST['function'] === "getAttributeById") && (isset($_POST['id']))) {
                confirmLoggedIn();
                JsonAttribute::getAttributeById($_POST['id'], TRUE);
        }
        if ($_POST['function'] === "getAllAttributes") {
                JsonAttribute::getAllAttributes(TRUE);
        }
}

function createAttributeFromPostVariables()
{
        $attribute = new CmsAttribute();
        if (isset($_POST[CmsAttribute::$col_id])) {
                $attribute->setId($_POST[CmsAttribute::$col_id]);
        }
        $attribute->setAttribute(trim($_POST[CmsAttribute::$col_attribute]));
        return $attribute;
}

class JsonAttribute
{
        static function create(CmsAttribute $attribute)
        {
                $conn = Database::getConnection();
                $query = "INSERT INTO " . CmsAttribute::$table_name .
                        " (" . CmsAttribute::$col_attribute . ") " .
                        "VALUES (:attribute)";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':attribute', $attribute->getAttribute(), PDO::PARAM_STR);

                if (!$stmt->execute()) {
                        echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                        addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                        exit;
                }

                // Get the last inserted ID
                $id = $conn->lastInsertId();

                // Fetch the newly created record
                JsonAttribute::getAttributeById($id, TRUE);
        }

        static function update(CmsAttribute $attribute)
        {
                $conn = Database::getConnection();
                $query = "UPDATE " . CmsAttribute::$table_name .
                        " SET " . CmsAttribute::$col_attribute . " = :attribute " .
                        "WHERE " . CmsAttribute::$col_id . " = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':attribute', $attribute->getAttribute(), PDO::PARAM_STR);
                $stmt->bindValue(':id', $attribute->getId(), PDO::PARAM_INT);

                if (!$stmt->execute()) {
                        echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                        addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                        exit;
                }

                // Fetch the updated record
                JsonAttribute::getAttributeById($attribute->getId(), TRUE);
        }

        static function delete($id)
        {
                $conn = Database::getConnection();
                $query = "DELETE FROM " . CmsAttribute::$table_name .
                        " WHERE " . CmsAttribute::$col_id . " = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);

                if (!$stmt->execute()) {
                        $errorInfo = $stmt->errorInfo();
                        // MySQL error code 1451: Cannot delete or update a parent row: a foreign key constraint fails
                        if (isset($errorInfo[1]) && $errorInfo[1] == 1451) {
                                echo json_encode(array(
                                        "state" => "f",
                                        "message" => Config::$user_error_Cannot_delete . "CmsAttribute has 'CmsAttribute Values'."
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
                echo json_encode(array("state" => "s"));
        }

        static function getAttributeById($id, $extractData)
        {
                $conn = Database::getConnection();
                $query = "SELECT * FROM " . CmsAttribute::$table_name . " WHERE " . CmsAttribute::$col_id . " = :id LIMIT 1";
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

                $output = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($extractData) {
                        echo json_encode($output);
                }
                return $output;
        }

        static function getAllAttributes($extractData)
        {
                $conn = Database::getConnection();
                $query = "SELECT * FROM " . CmsAttribute::$table_name . " ORDER BY " . CmsAttribute::$col_id . " DESC"; // Ensure ordering by ID
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
                        exit;
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
}
