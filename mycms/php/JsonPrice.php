<?php

/*

 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonPrice
 *
 * @author dell
 */

require_once "Database.php";
require_once "Price.php";
require_once "functions.php";

if (isset($_POST['function'])) {

    if (($_POST['function'] === "addPrice") && (isset($_POST['object_id'])) && (isset($_POST['attributeValue_id']))) {
        JsonPrice::addPrice($_POST['object_id'], $_POST['attributeValue_id'], $_POST['price'], $_POST['cost'], true);
    }
    if (($_POST['function'] === "updatePrice") && (isset($_POST['object_id'])) && (isset($_POST['attributeValue_id']))) {
        JsonPrice::updatePrice($_POST['object_id'], $_POST['attributeValue_id'], $_POST['price'], $_POST['cost']);
    }
    if (($_POST['function'] === "deletePrice") && (isset($_POST['object_id'])) && (isset($_POST['attributeValue_id']))) {
        JsonPrice::deletePrice($_POST['object_id'], $_POST['attributeValue_id']);
    }
    if (($_POST['function'] === "getPrice") && (isset($_POST['object_id'])) && (isset($_POST['attributeValue_id']))) {
        JsonPrice::getPrice($_POST['object_id'], $_POST['attributeValue_id'], true);
    }
    if (($_POST['function'] === "getMinPriceOfObject") && (isset($_POST['object_id']))) {
        JsonPrice::getMinPriceOfObject($_POST['object_id'], true);
    }
}

class JsonPrice
{
    //put your code here

    static function addPrice($object_id, $attributeValue_id, $price, $cost, $extractData)
    {
        //Test whether object already exists in DB
        if (JsonPrice::existPrice($object_id, $attributeValue_id)) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$data_exist));
                exit;
            }
            return false;
        }

        $price = trim((string)$price);
        $cost = self::normalizeCost($price, $cost);

        $conn = Database::getConnection();

        $query = "INSERT INTO " . Price::$table_name .
            "(" .
            Price::$col_object_id .
            ", " .
            Price::$col_attributeValue_id .
            ", " .
            Price::$col_price .
            ", " .
            Price::$col_cost .
            ")" .
            " VALUES (:object_id, :attributeValue_id, :price, :cost)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindValue(':attributeValue_id', $attributeValue_id, PDO::PARAM_INT);
        $stmt->bindValue(':price', $price, PDO::PARAM_STR);
        $stmt->bindValue(':cost', $cost, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                exit;
            }
            return false;
        }

        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
        return true;
    }

    static function updatePrice($object_id, $attributeValue_id, $price, $cost)
    {

        $price = trim((string)$price);
        $cost = self::normalizeCost($price, $cost);

        $conn = Database::getConnection();

        $query = "UPDATE " . Price::$table_name .
            " SET " .
            Price::$col_price . " = :price" .
            ", " .
            Price::$col_cost . " = :cost" .
            " WHERE " .
            Price::$col_object_id . "= :object_id" .
            " AND " .
            Price::$col_attributeValue_id . "= :attributeValue_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindValue(':attributeValue_id', $attributeValue_id, PDO::PARAM_INT);
        $stmt->bindValue(':price', $price, PDO::PARAM_STR);
        $stmt->bindValue(':cost', $cost, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        echo json_encode(array("state" => "s"));
        exit;
    }

    static function deletePrice($object_id, $attributeValue_id)
    {

        $conn = Database::getConnection();
        $query = "DELETE FROM " .
            Price::$table_name .
            " WHERE " .
            Price::$col_object_id . "= :object_id" .
            " AND " .
            Price::$col_attributeValue_id . "= :attributeValue_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindParam(':attributeValue_id', $attributeValue_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if (JsonPrice::lastPrice($object_id)) {
            echo json_encode(array("state" => "s", "message" => Config::$last_price));
        } else {
            echo json_encode(array("state" => "s"));
        }
    }

    static function getPrice($object_id, $attributeValue_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Price::$col_price .
            ", " .
            Price::$col_cost .
            " FROM " .
            Price::$table_name .
            " WHERE " .
            Price::$col_object_id . "= :object_id" .
            " AND " .
            Price::$col_attributeValue_id . "= :attributeValue_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindParam(':attributeValue_id', $attributeValue_id, PDO::PARAM_INT);

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
        return $output[0][Price::$col_price];
    }

    static function getCost($object_id, $attributeValue_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Price::$col_cost .
            " FROM " .
            Price::$table_name .
            " WHERE " .
            Price::$col_object_id . "= :object_id" .
            " AND " .
            Price::$col_attributeValue_id . "= :attributeValue_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindParam(':attributeValue_id', $attributeValue_id, PDO::PARAM_INT);

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
        return $output[0][Price::$col_cost];
    }

    static function existPrice($object_id, $attributeValue_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Price::$col_price .
            ", " .
            Price::$col_cost .
            " FROM " .
            Price::$table_name .
            " WHERE " .
            Price::$col_object_id . "= :object_id" .
            " AND " .
            Price::$col_attributeValue_id . "= :attributeValue_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindParam(':attributeValue_id', $attributeValue_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    static function lastPrice($object_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Price::$table_name .
            " WHERE " .
            Price::$col_object_id . " = :object_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if (!$stmt->rowCount()  == 0) {
            return false;
        } else {
            return true;
        }
    }


    static function getMinPriceOfObject($object_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            "MIN" .
            "(" . Price::$col_price . ")" .
            " AS " . Price::$col_price .
            " FROM " .
            Price::$table_name .
            " WHERE " .
            Price::$col_object_id . " = :object_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_STR);

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
            return 0;
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return  $output[0][Price::$col_price];
    }

    private static function normalizeCost($price, $cost)
    {
        $price = trim((string)$price);
        $cost = trim((string)$cost);

        if ($cost === '' || (float)$cost <= 0) {
            return $price;
        }

        return $cost;
    }
}