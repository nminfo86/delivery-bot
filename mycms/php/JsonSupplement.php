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
require_once "Supplement.php";
require_once "JsonSubOrder.php";
require_once "functions.php";

if (isset($_POST['function'])) {


    if (($_POST['function'] === "createSupplement") && (isset($_POST['ordere_id'])) && (isset($_POST['suborder_id'])) && (isset($_POST['supplementObject_id'])) && (isset($_POST['supplementSuborderID']))) {
        JsonSupplement::createSupplement($_POST['ordere_id'], $_POST['suborder_id'], $_POST['supplementObject_id'], $_POST['supplementSuborderID'], true);
    }
    if (($_POST['function'] === "deleteSupplement") && (isset($_POST['ordere_id'])) && (isset($_POST['suborder_id'])) && (isset($_POST['supplementObject_id']))) {
        JsonSupplement::deleteSupplement($_POST['ordere_id'], $_POST['suborder_id'], $_POST['supplementObject_id']);
    }
    if (($_POST['function'] === "getSupplements") && (isset($_POST['ordere_id']))) {
        JsonSupplement::getSupplements($_POST['ordere_id']);
    }
    if (($_POST['function'] === "getSupplementsOfSuborder") && (isset($_POST['suborder_id']))) {
        JsonSupplement::getSupplementsOfSuborder($_POST['suborder_id'], true);
    }
}

class JsonSupplement
{

    //put your code here
    // Create un object
    static function createSupplement($ordere_id, $suborder_id, $supplementObject_id, $supplementSuborderID, $extractData)
    {

        $conn = Database::getConnection();

        $query = "INSERT INTO " . Supplement::$table_name .
            "(" .
            Supplement::$col_ordere_id .
            ", " .
            Supplement::$col_suborder_id .
            ", " .
            Supplement::$col_supplementObject_id .
            ", " .
            Supplement::$col_supplementSuborderID .
            ")" .
            " VALUES (:ordere_id, :suborder_id, :supplementObject_id, :supplementSuborderID)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);
        $stmt->bindValue(':suborder_id', $suborder_id, PDO::PARAM_INT);
        $stmt->bindValue(':supplementObject_id', $supplementObject_id, PDO::PARAM_INT);
        $stmt->bindValue(':supplementSuborderID', $supplementSuborderID, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        echo json_encode(array("state" => "s"));
    }

    static function getSupplement($ordere_id, $suborder_id, $supplementObject_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Supplement::$table_name .
            " WHERE " .
            Supplement::$table_name . "." . Supplement::$col_ordere_id . " = :ordere_id " .
            " AND " .
            Supplement::$table_name . "." . Supplement::$col_suborder_id . " = :suborder_id " .
            " AND " .
            Supplement::$table_name . "." . Supplement::$col_supplementObject_id . " = :supplementObject_id " .
            " LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':ordere_id', $ordere_id, PDO::PARAM_INT);
        $stmt->bindParam(':suborder_id', $suborder_id, PDO::PARAM_INT);
        $stmt->bindParam(':supplementObject_id', $supplementObject_id, PDO::PARAM_INT);

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

    // This function is used in product-details.js and shop.js
    static function deleteSupplement($ordere_id, $suborder_id, $supplementObject_id)
    {

        $supplement = JsonSupplement::getSupplement($ordere_id, $suborder_id, $supplementObject_id, false);
        $conn = Database::getConnection();

        $query = "DELETE FROM " . Supplement::$table_name .
            " WHERE " .
            Supplement::$col_ordere_id . "= :ordere_id" .
            " AND " .
            Supplement::$col_suborder_id . "= :suborder_id" .
            " AND " .
            Supplement::$col_supplementObject_id . "= :supplementObject_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);
        $stmt->bindValue(':suborder_id', $suborder_id, PDO::PARAM_INT);
        $stmt->bindValue(':supplementObject_id', $supplementObject_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonSubOrder::deleteSubOrder($supplement[0][Supplement::$col_supplementSuborderID], 0);
    }

    // This function is used in cart.js and waiterPanel.js
    static function deleteSupplements($ordere_id, $supplementObject_id, $extractData)
    {
        //
        //        $conn = Database::getConnection();
        //
        //        $query = "DELETE FROM " . Supplement::$table_name .
        //                " WHERE " .
        //                Supplement::$col_ordere_id . "= :ordere_id".
        //                " AND ".
        //                Supplement::$col_supplementObject_id . "= :supplementObject_id";
        //
        //        $stmt = $conn->prepare($query);
        //
        //        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);
        //        $stmt->bindValue(':supplementObject_id', $supplementObject_id, PDO::PARAM_INT);
        //
        //        if (!$stmt->execute()) {
        //            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
        //            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
        //            exit;
        //        }
    }

    static function getSupplements($ordere_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " from " .
            Supplement::$table_name .
            " WHERE " .
            Supplement::$table_name . "." . Supplement::$col_ordere_id . " = :ordere_id";
        //        print_r($query); 

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':ordere_id', $ordere_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }

        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
        return $output;
    }

    //This function retrieve all supplements of a suborder (exemple supplements thar are added to "crepes")
    //This function was used in waiterPanel.js
    static function getSupplementsOfSuborder($suborder_id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();
        $query = "SELECT " .
            Supplement::$table_name . ".* " .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity .
            " from " .
            Supplement::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            Supplement::$table_name . "." . Supplement::$col_supplementObject_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            SubOrder::$table_name .
            " ON " .
            Supplement::$table_name . "." . Supplement::$col_supplementSuborderID .
            " = " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " WHERE " .
            Supplement::$table_name . "." . Supplement::$col_suborder_id . " = :suborder_id";
        //        print_r($query);

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':suborder_id', $suborder_id, PDO::PARAM_INT);

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
}
