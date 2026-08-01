<?php

require_once "Database.php";
require_once "Attribute_Value.php";
require_once "CmsAttribute.php";
require_once "functions.php";
require_once "Config.php";

if (isset($_POST['function'])) {
    if ($_POST['function'] === "create") {
        confirmLoggedIn();
        JsonAttribute_Value::create(createAttributeValueFromGetVariables());
    }
    if ($_POST['function'] === "update") {
        confirmLoggedIn();
        JsonAttribute_Value::update(createAttributeValueFromGetVariables());
    }
    if (($_POST['function'] === "delete") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonAttribute_Value::delete($_POST['id']);
    }
    if (($_POST['function'] === "getAttributeValueById") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonAttribute_Value::getAttributeValueById($_POST['id'], TRUE);
    }

    if (($_POST['function'] === "getAllAttributeValuesByAttributeID") && (isset($_POST['attribute_id']))) {
        confirmLoggedIn();
        JsonAttribute_Value::getAllAttributeValuesByAttributeID($_POST['attribute_id'], TRUE);
    }

    if ($_POST['function'] === "getAllAttributeValues") {
        confirmLoggedIn();
        JsonAttribute_Value::getAllAttributeValues(TRUE);
    }
}

function createAttributeValueFromGetVariables()
{
    $attributeValue = new Attribute_Value();
    if (isset($_POST[Attribute_Value::$col_id])) {
        $attributeValue->setId($_POST[Attribute_Value::$col_id]);
    }
    $attributeValue->setAttributeValue(trim($_POST[Attribute_Value::$col_attributeValue]));

    if (isset($_POST[Attribute_Value::$col_attribute_id])) {
        $attributeValue->setAttribute_id($_POST[Attribute_Value::$col_attribute_id]);
    }
    return $attributeValue;
}

class JsonAttribute_Value
{
    static function create(Attribute_Value $attributeValue)
    {
        $conn = Database::getConnection();
        $query = "INSERT INTO " . Attribute_Value::$table_name .
            " (" . Attribute_Value::$col_attributeValue . ", " . Attribute_Value::$col_attribute_id . ") " .
            "VALUES (:attributeValue, :attribute_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':attributeValue', $attributeValue->getAttributeValue(), PDO::PARAM_STR);
        $stmt->bindValue(':attribute_id', $attributeValue->getAttribute_id(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        // Get the last inserted ID
        $id = $conn->lastInsertId();

        // Fetch the newly created record
        JsonAttribute_Value::getAttributeValueById($id, TRUE);
    }

    static function update(Attribute_Value $attributeValue)
    {

        if (self::isAttributeValueUsedInSuborder($attributeValue->getId())) {
            echo json_encode(array("state" => "f", "message" => "Attribute value deja utilisé dans une commande. Merci de creer une nouvelle valeur."));
            exit;
        }

        $conn = Database::getConnection();
        $query = "UPDATE " . Attribute_Value::$table_name .
            " SET " . Attribute_Value::$col_attributeValue . " = :attributeValue " .
            "WHERE " . Attribute_Value::$col_id . " = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':attributeValue', $attributeValue->getAttributeValue(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $attributeValue->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        // Fetch the updated record
        JsonAttribute_Value::getAttributeValueById($attributeValue->getId(), TRUE);
    }

    static function delete($id)
    {
        $conn = Database::getConnection();

        if (self::isAttributeValueUsedInSuborder($id)) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error_Cannot_delete . "'Attribute value' used in some 'orders'"));
            exit;
        }
        $query = "DELETE FROM " . Attribute_Value::$table_name .
            " WHERE " . Attribute_Value::$col_id . " = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        echo json_encode(array("state" => "s"));
    }

    private static function isAttributeValueUsedInSuborder($id)
    {
        $conn = Database::getConnection();

        $checkQuery = "SELECT COUNT(*) FROM suborder WHERE attributeValue_id = :id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();
        return $count > 0;
    }

    static function getAttributeValueById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Attribute_Value::$table_name . " WHERE " . Attribute_Value::$col_id . " = :id LIMIT 1";
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

    static function getAllAttributeValuesByAttributeID($attribute_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT av.*, a." . CmsAttribute::$col_attribute . " " .
            "FROM " . Attribute_Value::$table_name . " av " .
            "JOIN " . CmsAttribute::$table_name . " a " .
            "ON av." . Attribute_Value::$col_attribute_id . " = a." . CmsAttribute::$col_id . " " .
            "WHERE av." . Attribute_Value::$col_attribute_id . " = :attribute_id " .
            "ORDER BY av." . Attribute_Value::$col_id . " DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':attribute_id', $attribute_id, PDO::PARAM_INT);

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

    static function getAllAttributeValues($extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT av.*, a.* " .
            "FROM " . Attribute_Value::$table_name . " av " .
            "JOIN " . CmsAttribute::$table_name . " a " .
            "ON av." . Attribute_Value::$col_attribute_id . " = a." . CmsAttribute::$col_id;
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
