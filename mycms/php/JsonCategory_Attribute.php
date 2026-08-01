<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonCategory_Attribute
 *
 * @author dell
 */
require_once "Database.php";
require_once "Category_Attribute.php";
require_once "CmsAttribute.php";
require_once "Price.php";
require_once "Attribute_Value.php";
require_once "functions.php";
if (isset($_POST['function'])) {

    if (($_POST['function'] === "getAllAttributesOfCategory") && (isset($_POST['category_id']))) {
        JsonCategory_Attribute::getAllAttributesOfCategory($_POST['category_id'], true);
    }
    if (($_POST['function'] === "getAttributesAndAttributeValuesOfCategory") && (isset($_POST['category_id']))) {
        JsonCategory_Attribute::getAttributesAndAttributeValuesOfCategory($_POST['category_id'], true);
    }
    if (($_POST['function'] === "getAttributesAndAttributeValuesAndPriceOfObject") && (isset($_POST['category_id']) && (isset($_POST['object_id'])))) {
        JsonCategory_Attribute::getAttributesAndAttributeValuesAndPriceOfObject($_POST['category_id'], $_POST['object_id'], true);
    }
    if (($_POST['function'] === "delete") && (isset($_POST['category_id'])) && (isset($_POST['attribute_id']))) {
        JsonCategory_Attribute::delete($_POST['category_id'], $_POST['attribute_id'], true);
    }
    if (($_POST['function'] === "create") && (isset($_POST['category_id'])) && (isset($_POST['attribute_id']))) {
        JsonCategory_Attribute::create($_POST['category_id'], $_POST['attribute_id'], true);
    }
}

class JsonCategory_Attribute
{

    static function getAllAttributesOfCategory($category_id, $extractData)
    {

        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Category_Attribute::$table_name .
            " WHERE " .
            Category_Attribute::$col_category_id .
            " = :category_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

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

    static function getAttributesAndAttributeValuesOfCategory($category_id, $extractData)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_category_id .
            " , " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_attribute_id .
            " , " .
            CmsAttribute::$table_name . "." . CmsAttribute::$col_attribute .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id . " AS attribute_value_id" .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            CmsAttribute::$table_name .
            " INNER JOIN " .
            Category_Attribute::$table_name .
            " ON " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_attribute_id .
            " = " .
            CmsAttribute::$table_name . "." . CmsAttribute::$col_id .
            " INNER JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_attribute_id .
            " = " .
            Attribute_Value::$table_name . "." .  Attribute_Value::$col_attribute_id .
            " WHERE " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_category_id .
            " =:category_id";

        //         echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

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

    static function getAttributesAndAttributeValuesAndPriceOfObject($category_id, $object_id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();

        $query = "SELECT " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_category_id .
            " , " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_attribute_id .
            " , " .
            CmsAttribute::$table_name . "." . CmsAttribute::$col_attribute .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Price::$table_name . "." .  Price::$col_object_id .
            " , " .
            Price::$table_name . "." .  Price::$col_price .
            " FROM " .
            CmsAttribute::$table_name .
            " INNER JOIN " .
            Category_Attribute::$table_name .
            " ON " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_attribute_id .
            " = " .
            CmsAttribute::$table_name . "." . CmsAttribute::$col_id .
            " INNER JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_attribute_id .
            " = " .
            Attribute_Value::$table_name . "." .  Attribute_Value::$col_attribute_id .
            " INNER JOIN " .
            Price::$table_name .
            " ON " .
            Price::$table_name . "." .  Price::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." .  Attribute_Value::$col_id .
            " WHERE " .
            Category_Attribute::$table_name . "." . Category_Attribute::$col_category_id . " =:category_id" .
            " AND " .
            Price::$table_name . "." . Price::$col_object_id . " =:object_id" .
            //  " AND ".
            //  Price::$table_name.".".Price::$col_price." <> 0".
            " ORDER BY " .
            Price::$table_name . "." . Price::$col_price;

        //         echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
            return $output;
        }
        //
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function delete($category_id, $attribute_id, $extractData)
    {
        $conn = Database::getConnection();

        $query = "DELETE FROM " . Category_Attribute::$table_name .
            " WHERE " .
            Category_Attribute::$col_category_id . "= :category_id" .
            " AND " .
            Category_Attribute::$col_attribute_id . "=:attribute_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':attribute_id', $attribute_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    static function create($category_id, $attribute_id, $extractData)
    {

        if (JsonCategory_Attribute::haveAttribute($category_id, $extractData)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Category_Attribute::$table_name .
            "(" .
            Category_Attribute::$col_category_id .
            ", " .
            Category_Attribute::$col_attribute_id .
            ")" .
            " VALUES (:category_id, :attribute_id)";


        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':attribute_id', $attribute_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    static function haveAttribute($category_id, $extractData)
    {

        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Category_Attribute::$table_name .
            " WHERE " .
            Category_Attribute::$col_category_id .
            " = :category_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

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