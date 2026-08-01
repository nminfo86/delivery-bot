<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonSubOrder
 *
 * @author dell
 */
require_once "Database.php";
require_once "SubOrder.php";
require_once "JsonOrdere.php";
require_once "JsonSupplement.php";
require_once "JsonPrice.php";
require_once "JsonObject.php";
require_once "JsonCategory.php";
require_once "JsonPrinter.php";
require_once "Attribute_Value.php";
require_once "Objet.php";
require_once "Category.php";
require_once "User_Category.php";
require_once "Table.php";
require_once "functions.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "createSubOrder") {
        JsonSubOrder::createSubOrder(createSubOrderFromGetVariables(), true);
    }
    if (($_POST['function'] === "updateSubOrders") && (isset($_POST['array']))) {
        // Update All suborders of order in the array
        $subOrders = json_decode(($_POST['array']), true);
        $subSequence = 0;
        $prepareSequence = 0;
        $companyArray = array();
        $deleteOriginalOrder = FALSE; //this variable is used in case the first SubOrder have order of different company. 
        //We delete the original order at the end of the opération

        foreach ((array) $subOrders as $i => $subOrder) {
            $newSubOrder = JsonSubOrder::getSubOrderById($subOrder["id"], False);

            // Create a new SubCode  for all subOrders
            // If the Object in Suborder have category prepare then create new subCode sequencly
            // Else , we create the subCode as the same as the code of it's Order
            $orderCode = strval(JsonOrdere::getOrderCode($newSubOrder[0][SubOrder::$col_ordere_id], False));
            $isPrepare = JsonCategory::isPrepare($newSubOrder[0][SubOrder::$col_object_id]);
            if ($isPrepare) {
                $prepareSequence++;
            }
            $newSubOrderCode = $isPrepare ? ($orderCode . '-' . strval($prepareSequence)) : $orderCode;
            JsonSubOrder::updateSubOrder($newSubOrder, $subOrder["quantity"], $newSubOrderCode, $subOrder["comment"]);
            $subSequence++;

            //*************************** //*************************************
            if ($i === 0) {
                //Get order of suborder to test whether the suborder Company is the same as the order company
                // If not we create a new order of the same company as suborder, 
                // then update this order
                // then update suborder "ordere_id" to the new created ordere
                $ordere   = JsonOrdere::getOrderById($newSubOrder[0][SubOrder::$col_ordere_id], false);
                if ($newSubOrder[0][Objet::$col_company_id] !== $ordere->getCompany_id()) {
                    // Create new Ordere and then update new Order and the suborder
                    $newOrder_id = JsonOrdere::createOrder(false);
                    $newOrdere = new Ordere();
                    //Fill New ordere
                    $newOrdere->setId($newOrder_id);
                    $newOrdere->setPlace($ordere->getPlace());
                    $newOrdere->setTable_id($ordere->getTable_id());
                    $newOrdere->setCode($ordere->getCode());
                    $newOrdere->setProgression($ordere->getProgression());
                    $newOrdere->setComment($ordere->getComment());
                    $newOrdere->setValid($ordere->getValid());
                    $newOrdere->setPayed($ordere->getPayed());
                    $newOrdere->setOrderePrice(0);
                    $newOrdere->setCustomerLeft($ordere->getCustomerLeft());
                    $newOrdere->setCookieID($ordere->getCookieID());
                    $newOrdere->setCompany_id($newSubOrder[0][Objet::$col_company_id]);

                    JsonOrdere::updateOrder($newOrdere, False);
                    JsonSubOrder::updateSubOrderOrdereID($newSubOrder[0][SubOrder::$col_id], $newOrder_id);
                    // Add company_id and ordere_id to the array to test existance after
                    $companyArray[] = array('company_id' => $newSubOrder[0][Objet::$col_company_id], 'ordere_id' => $newOrder_id);
                    //Set variable $deleteOriginalOrder to delete the first ordere because it not valid order
                    $deleteOriginalOrder = True;
                } else {
                    $companyArray[] = array('company_id' => $newSubOrder[0][Objet::$col_company_id], 'ordere_id' => $newSubOrder[0][SubOrder::$col_ordere_id]);
                }
            } else {
                $resultKey = array_search($newSubOrder[0][Objet::$col_company_id], array_column($companyArray, 'company_id'));
                if ($resultKey !== FALSE) {
                    $resultArray = $companyArray[$resultKey];
                    JsonSubOrder::updateSubOrderOrdereID($newSubOrder[0][SubOrder::$col_id], $resultArray['ordere_id']);
                } else {
                    $originalOrder   = JsonOrdere::getOrderById($newSubOrder[0][SubOrder::$col_ordere_id], false);
                    $newOrder_id = JsonOrdere::createOrder(false);
                    $newOrdere = new Ordere();
                    //Fill New ordere
                    $newOrdere->setId($newOrder_id);
                    $newOrdere->setPlace($originalOrder->getPlace());
                    $newOrdere->setTable_id($originalOrder->getTable_id());
                    $newOrdere->setCode($originalOrder->getCode());
                    $newOrdere->setProgression($originalOrder->getProgression());
                    $newOrdere->setComment($originalOrder->getComment());
                    $newOrdere->setValid($originalOrder->getValid());
                    $newOrdere->setPayed($originalOrder->getPayed());
                    $newOrdere->setOrderePrice(0);
                    $newOrdere->setCustomerLeft($originalOrder->getCustomerLeft());
                    $newOrdere->setCookieID($originalOrder->getCookieID());
                    $newOrdere->setCompany_id($newSubOrder[0][Objet::$col_company_id]);

                    JsonOrdere::updateOrder($newOrdere, False);
                    JsonSubOrder::updateSubOrderOrdereID($newSubOrder[0][SubOrder::$col_id], $newOrder_id);
                    $companyArray[] = array('company_id' => $newSubOrder[0][Objet::$col_company_id], 'ordere_id' => $newOrder_id);
                }
            }
            //            print_r($companyArray);
            //****************************** // ***************************************
        }
        if ($deleteOriginalOrder) {
            JsonOrdere::deleteOrder($ordere->getId(), false);
        }
        echo json_encode(array("state" => "s"));
    }

    if (($_POST['function'] === "updateSubOrderQte") && (isset($_POST['id'])) && (isset($_POST['quantity']))) {
        JsonSubOrder::updateSubOrderQte($_POST['id'], $_POST['quantity']);
    }

    if (($_POST['function'] === "updateSubOrderComment")
        && (isset($_POST['id']))
        && (isset($_POST['comment']))
    ) {
        JsonSubOrder::updateSubOrderComment($_POST['id'], $_POST['comment']);
    }
    if (($_POST['function'] === "updateAllSubOrderProgressionAndPrint") && (isset($_POST['array'])) && (isset($_POST['progression']))) {
        $subOrders = json_decode(($_POST['array']), true);
        JsonSubOrder::updateAllSubOrderProgressionAndPrint($subOrders, $_POST["progression"]);
    }

    if (($_POST['function'] === "updateSubOrderProgression") && (isset($_POST['id'])) && (isset($_POST['progression'])) && (isset($_POST['print']))) {
        JsonSubOrder::updateSubOrderProgression(
            $_POST['id'],
            $_POST['progression'],
            $_POST['print']
        );
    }

    if (($_POST['function'] === "deleteSubOrder") && (isset($_POST['id'])) && (isset($_POST['deleteOrder']))) {
        JsonSubOrder::deleteSubOrder($_POST['id'], $_POST['deleteOrder']);
    }

    if (($_POST['function'] === "getAllSubOrdersOfDay") && (isset($_POST['search'])) && (isset($_POST['status'])) && (isset($_POST['orderBy']))) {
        JsonSubOrder::getAllSubOrdersOfDay($_POST['search'], $_POST['status'], $_POST['orderBy']);
    }

    if ($_POST['function'] === "getSubOrdersOfTv") {
        JsonSubOrder::getSubOrdersOfTv();
    }

    if (($_POST['function'] === "getAllSubOrdersProgressionOfDay") && (isset($_POST['orderCode']))) {
        JsonSubOrder::getAllSubOrdersProgressionOfDay($_POST['orderCode']);
    }

    if (($_POST['function'] === "getSubOrdersProgressionOfDayByCookie") && (isset($_POST['cookieID']))) {
        JsonSubOrder::getSubOrdersProgressionOfDayByCookie($_POST['cookieID']);
    }

    if (($_POST['function'] === "getAllTableSubOrdersProgressionOfDay") && (isset($_POST['tableCode']))) {
        JsonSubOrder::getAllTableSubOrdersProgressionOfDay($_POST['tableCode']);
    }

    if (($_POST['function'] === "getSubOrdersOfOrder") && (isset($_POST['ordere_id']))) {
        JsonSubOrder::getSubOrdersOfOrder($_POST['ordere_id'], TRUE);
    }

    if (($_POST['function'] === "getSubOrdersOfOrderLabel") && (isset($_POST['ordere_id']))) {
        JsonSubOrder::getSubOrdersOfOrderLabel($_POST['ordere_id'], TRUE);
    }

    if (($_POST['function'] === "getSubOrdersOfTableLabel") && (isset($_POST['table_id']))) {
        JsonSubOrder::getSubOrdersOfTableLabel($_POST['table_id'], TRUE);
    }

    if (($_POST['function'] === "getSubOrdersOrderOfSupplements") && (isset($_POST['ordere_id'])) && (isset($_POST['company_id']))) {
        JsonSubOrder::getSubOrdersOrderOfSupplements($_POST['company_id'], $_POST['ordere_id'], TRUE);
    }

    if (($_POST['function'] === "getSubOrdersOfWaiterHistory") && (isset($_POST['search']))) {
        JsonSubOrder::getSubOrdersOfWaiterHistory($_POST['search']);
    }


    if (($_POST['function'] === "getSubOrdersOfTablecheckOut") && (isset($_POST['table_id']))) {
        JsonSubOrder::getSubOrdersOfTablecheckOut($_POST['table_id']);
    }

    if (($_POST['function'] === "rePrint") && (isset($_POST['id']))) {
        JsonSubOrder::rePrintSubOrder($_POST['id']);
    }
    if (($_POST['function'] === "getPaidSubOrdersSummaryOfDay") && (isset($_POST['date_from']))
        && (isset($_POST['date_to']))
    ) {
        JsonSubOrder::getPaidSubOrdersSummaryOfDay($_POST['date_from'], $_POST['date_to'], true);
    }
    if (($_POST['function'] === "printPaidSubOrdersSummaryOfDay") && (isset($_POST['startDate']))
        && (isset($_POST['endDate']))
    ) {
        JsonSubOrder::printPaidSubOrdersSummaryOfDay($_POST['startDate'], $_POST['endDate']);
    }
}

function createSubOrderFromGetVariables()
{

    $subOrder = new SubOrder();

    if (isset($_POST[SubOrder::$col_id])) {
        $subOrder->setId($_POST[SubOrder::$col_id]);
    }
    if (!isset($_SESSION['ordere_id'])) {
        $subOrder->setOrdere_id(JsonOrdere::createOrder(false));
    } else {
        $subOrder->setOrdere_id($_SESSION['ordere_id']);
    }
    $subOrder->setObject_id($_POST[SubOrder::$col_object_id]);
    $subOrder->setSubProgression(JsonCategory::isPrepare($subOrder->getObject_id()) ? Config::$orderStateNew : Config::$orderStateReady);
    
    // We check if the attributeValue_id is set and not null or empty string 
    //because in some cases we can have attributeValue_id with null value but in string format "null" and we don't want to set this value in this case
    $attributeValueId = NULL;
    if (isset($_POST[SubOrder::$col_attributeValue_id])) {
        $rawAttributeValueId = trim((string)$_POST[SubOrder::$col_attributeValue_id]);

        if ($rawAttributeValueId !== '' && strtolower($rawAttributeValueId) !== 'null') {
            $attributeValueId = $rawAttributeValueId;
        }
    }

    $subOrder->setAttributeValue_id($attributeValueId);
    $subOrder->setQuantity($_POST[SubOrder::$col_quantity]);

    $subOrder->setUPrice($attributeValueId !== NULL
        ? JsonPrice::getPrice($_POST[SubOrder::$col_object_id], $attributeValueId, false)
        : JsonObject::getObjectBasePrice($_POST[SubOrder::$col_object_id]));

    $subOrder->setUCost($attributeValueId !== NULL
        ? JsonPrice::getCost($_POST[SubOrder::$col_object_id], $attributeValueId, false)
        : JsonObject::getObjectBaseCost($_POST[SubOrder::$col_object_id]));

    // 
    $subOrder->setSubTotal($subOrder->getQuantity() * $subOrder->getUPrice());
    $subOrder->setSubCost($subOrder->getQuantity() * $subOrder->getUCost());
    return $subOrder;
}

class JsonSubOrder
{

    //put your code here

    static function createSubOrder(SubOrder $subOrder, $extractData)
    {

        // if (JsonSubOrder::existSubOrder($subOrder)) {
        //     echo json_encode(array("state" => "f", "message" => Config::$data_exist));
        //     exit;
        // }

        $conn = Database::getConnection();

        $query = "INSERT INTO " . SubOrder::$table_name .
            "(" .
            SubOrder::$col_attributeValue_id .
            ", " .
            SubOrder::$col_object_id .
            ", " .
            SubOrder::$col_ordere_id .
            ", " .
            SubOrder::$col_quantity .
            ", " .
            SubOrder::$col_uPrice .
            ", " .
            SubOrder::$col_uCost .
            ", " .

            SubOrder::$col_subTotal .
            ", " .
            SubOrder::$col_subCost .
            ", " .
            SubOrder::$col_subCode .
            ", " .
            SubOrder::$col_subProgression .
            ", " .
            SubOrder::$col_creationDate .
            ")" .
            " VALUES (:attributeValue_id, :object_id, :ordere_id, :quantity, :uPrice, :uCost, :subTotal, :subCost, :subCode, :subProgression, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':attributeValue_id', $subOrder->getAttributeValue_id(), PDO::PARAM_INT);
        $stmt->bindValue(':object_id', $subOrder->getObject_id(), PDO::PARAM_INT);
        $stmt->bindValue(':ordere_id', $subOrder->getOrdere_id(), PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $subOrder->getQuantity(), PDO::PARAM_INT);
        $stmt->bindValue(':uPrice', $subOrder->getUPrice(), PDO::PARAM_STR);
        $stmt->bindValue(':uCost', $subOrder->getUCost(), PDO::PARAM_STR);
        $stmt->bindValue(':subTotal', $subOrder->getSubTotal(), PDO::PARAM_STR);
        $stmt->bindValue(':subCost', $subOrder->getSubCost(), PDO::PARAM_STR);
        $stmt->bindValue(':subCode', null, PDO::PARAM_STR);
        $stmt->bindValue(':subProgression', $subOrder->getSubProgression(), PDO::PARAM_STR);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            }
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            SubOrder::$col_id .
            " FROM " .
            SubOrder::$table_name .
            " ORDER BY " .
            SubOrder::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        JsonSubOrder::getSubOrderById($id, $extractData);
    }

    static function updateSubOrder($subOrder, $newQte, $newSubCode, $newSubComment)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . SubOrder::$table_name .
            " SET " .
            SubOrder::$col_quantity . "= :quantity" .
            " , " .
            SubOrder::$col_subTotal . "= :subTotal" .
            " , " .
            SubOrder::$col_subCost . "= :subCost" .
            " , " .
            SubOrder::$col_subCode . "= :subCode" .
            " , " .
            SubOrder::$col_subComment . "= :subcomment" .
            " WHERE " .
            SubOrder::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':quantity', $newQte, PDO::PARAM_INT);
        $stmt->bindValue(':subTotal', $newQte * $subOrder[0][SubOrder::$col_uPrice], PDO::PARAM_STR);
        $stmt->bindValue(':subCost', $newQte * $subOrder[0][SubOrder::$col_uCost], PDO::PARAM_STR);
        $stmt->bindValue(':subCode', $newSubCode, PDO::PARAM_STR);
        $stmt->bindValue(':subcomment', $newSubComment, PDO::PARAM_STR);
        $stmt->bindValue(':id', $subOrder[0][SubOrder::$col_id], PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        JsonSubOrder::autoUpdateOrderProgression($subOrder[0][SubOrder::$col_id], $subOrder[0][SubOrder::$col_subProgression]);
    }
    //This function is used in Checkout.php Menu POS to update Qte 
    static function updateSubOrderQte($id, $newQte)
    {
        $subOrder = JsonSubOrder::getSubOrderById($id, false);
        $conn = Database::getConnection();

        $query = "UPDATE " . SubOrder::$table_name .
            " SET " .
            SubOrder::$col_quantity . "= :quantity" .
            " , " .
            SubOrder::$col_subTotal . "= :subTotal" .
            " , " .
            SubOrder::$col_subCost . "= :subCost" .
            " WHERE " .
            SubOrder::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':quantity', $newQte, PDO::PARAM_INT);
        $stmt->bindValue(':subTotal', $newQte * $subOrder[0][SubOrder::$col_uPrice], PDO::PARAM_STR);
        $stmt->bindValue(':subCost', $newQte * $subOrder[0][SubOrder::$col_uCost], PDO::PARAM_STR);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonSubOrder::getSubOrderById($id, true);
    }
    //This function is used in waiter.php and checkout.php to update Qte 
    static function updateSubOrderComment($id, $comment)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . SubOrder::$table_name .
            " SET " .
            SubOrder::$col_subComment . "= :comment" .
            " WHERE " .
            SubOrder::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonSubOrder::getSubOrderById($id, true);
    }

    //this function is used when there are many companies
    static function updateSubOrderOrdereID($id, $ordere_id)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . SubOrder::$table_name .
            " SET " .
            SubOrder::$col_ordere_id . "= :ordere_id" .
            " WHERE " .
            SubOrder::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    static function getSubOrderById($id, $extractData)
    {
        $output = NULL;
        $conn = Database::getConnection();
        $query = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " , " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " , " .
            Category::$table_name . "." . Category::$col_prepare .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_acceptSupplement .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            SubOrder::$table_name . "." . SubOrder::$col_id . " = :id " .
            " LIMIT 1";

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
        } else {
            $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($extractData) {
                echo json_encode($output);
            }
        }
        return $output;
    }

    // This function is used in Cart.Php
    static function getSubOrdersOfOrder($ordere_id, $extractData)
    {
        $conn = Database::getConnection();

        $query1 = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            " suplTable.* " .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " , " .
            Category::$table_name . "." . Category::$col_acceptSupplement .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " , " .
            Media::$table_name . "." . Media::$col_mediaDescription .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " LEFT JOIN " .
            " (SELECT " .
            Objet::$table_name . "." . Objet::$col_title . " AS suplTitle " .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity . " AS suplQuantity " .
            " , " .
            Supplement::$table_name . "." . Supplement::$col_suborder_id .
            " FROM " .
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
            SubOrder::$table_name . "." . SubOrder::$col_id . " ) " .
            "AS suplTable " .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " = " .
            "suplTable.suborder_id" .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Media::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id . " = :ordere_id" .
            " AND (" .
            Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .
            " OR " .
            Media::$table_name . "." . Media::$col_media . " is null" .
            ") " .
            " ORDER BY " .
            Category::$table_name . "." . Category::$col_display;
        // print_r($query1);

        $stmt = $conn->prepare($query1);
        $stmt->bindParam(':ordere_id', $ordere_id, PDO::PARAM_INT);

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
        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    // This function is used in JsonOrdere.php to retrieve suborders of ordere 
    //and put them in ClientTicket for printing
    //It used also in Menu Panel and CheckoutPanel and CheckoutHistory and WaiterPAnel and Waiter History
    static function getSubOrdersOfOrderLabel($ordere_id, $extractData)
    {
        $conn = Database::getConnection();

        $query1 = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_acceptSupplement .
            " , " .
            Category::$table_name . "." . Category::$col_prepare .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_vat_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_vatAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_totalTtc .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id . " = :ordere_id" .
            " ORDER BY " .
            SubOrder::$table_name . "." . SubOrder::$col_id;

        $stmt = $conn->prepare($query1);
        $stmt->bindParam(':ordere_id', $ordere_id, PDO::PARAM_INT);

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
        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    // This function is used in JsonOrdere.php to retrieve suborders of table and put them in ClientTicket for printing
    //also used in Checkout
    static function getSubOrdersOfTableLabel($table_id, $extractData)
    {
        $conn = Database::getConnection();
        $currDate = date('Y-m-d');

        $query1 = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_acceptSupplement .
            " , " .
            Category::$table_name . "." . Category::$col_prepare .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_vat_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_vatAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_totalTtc .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 0" .   //add by me because i found prb in Checkout panel 
            " AND " .                                                   //when click on table to pay it shows all historical order payed in that table
            Ordere::$table_name . "." . Ordere::$col_table_id . " = :table_id " .
            " ORDER BY " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " , " .
            Category::$table_name . "." . Category::$col_display;
        //        echo $query;
        $stmt = $conn->prepare($query1);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindParam(':table_id', $table_id, PDO::PARAM_STR);

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
        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    // This function is used in Supplement modal
    // We retriev all suborders that the user already submit but they are "accept supplements" and "preparable" and "Not supplement"
    static function getSubOrdersOrderOfSupplements($company_id, $ordere_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " , " .
            Media::$table_name . "." . Media::$col_mediaDescription .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " LEFT JOIN " .
            Media::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id . " = :ordere_id" .
            " AND " .
            Category::$table_name . "." . Category::$col_prepare . " <> '0'" .
            " AND " .
            Category::$table_name . "." . Category::$col_supplement . " <> '1'" .
            " AND " .
            Category::$table_name . "." . Category::$col_acceptSupplement . " <> '0'" .
            " AND " .
            Category::$table_name . "." . Category::$col_prepare . " <> '0'" .
            " AND " .
            Objet::$table_name . "." . Objet::$col_company_id . "= :company_id" .
            " AND (" .
            Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .
            " OR " .
            Media::$table_name . "." . Media::$col_media . " is null" .
            ") ";
        //                " GROUP BY " .
        //                SubOrder::$table_name . "." . SubOrder::$col_object_id .
        //                " ," .
        //                SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id;
        //        print_r($query); 

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':ordere_id', $ordere_id, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);

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
        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    // This function is used in waiterHistory.php only not prepared and not supplements
    static function getSubOrdersOfWaiterHistory($search)
    {
        $conn = Database::getConnection();
        $currDate = date('Y-m-d');
        $query = "SELECT " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_subTotal .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_subComment .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_display .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            Category::$table_name . "." . Category::$col_prepare . " = 0" .
            " AND " .
            Category::$table_name . "." . Category::$col_supplement . " = 0" .
            " AND " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression . " != :progression" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :place" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id" .
            " AND " .
            " ( " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :search" .
            " OR " .
            Category::$table_name . "." . Category::$col_category . " LIKE :search" .
            " OR " .
            Objet::$table_name . "." . Objet::$col_title . " LIKE :search" .
            " OR " .
            Table::$table_name . "." . Table::$col_tableName . " LIKE :search" .
            " ) " .
            " ORDER BY " .
            SubOrder::$table_name . "." . SubOrder::$col_updateDate;

        // print_r($query);

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':place', '%' . Config::$orderPlaceOnTable . '%', PDO::PARAM_STR);
        $stmt->bindValue(':progression', Config::$orderStateDelivred, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
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
        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    // This function is used in tv.php 
    static function getSubOrdersOfTv()
    {
        $conn = Database::getConnection();
        $currDate = date('Y-m-d');
        $query = "SELECT " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_updateDate .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            Category::$table_name . "." . Category::$col_prepare . " = 1" .
            " AND " .
            Category::$table_name . "." . Category::$col_supplement . " = 0" .
            " AND " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression . " =:progresReady" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " ORDER BY " .
            SubOrder::$table_name . "." . SubOrder::$col_updateDate . " DESC " .
            " LIMIT 6";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progresReady', Config::$orderStateReady, PDO::PARAM_STR);

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
    }

    // This function is used in CheckoutPanel.js to retrieve All Suborders of Table 
    // with suppelments if exists
    static function getSubOrdersOfTablecheckOut($table_id)
    {
        $conn = Database::getConnection();
        $currDate = date('Y-m-d');

        // print_r($query1);
        $query1 = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            " suplTable.* " .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Media::$table_name . "." . Media::$col_media .
            " , " .
            Media::$table_name . "." . Media::$col_mediaDescription .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " LEFT JOIN " .
            " (SELECT " .
            Objet::$table_name . "." . Objet::$col_title . " AS suplTitle " .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity . " AS suplQuantity " .
            " , " .
            Supplement::$table_name . "." . Supplement::$col_suborder_id .
            " FROM " .
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
            SubOrder::$table_name . "." . SubOrder::$col_id . " ) " .
            "AS suplTable " .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " = " .
            "suplTable.suborder_id" .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Media::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Media::$table_name . "." . Media::$col_object_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 0" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_table_id . " = :table_id" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id" .
            " AND (" .
            Media::$table_name . "." . Media::$col_mediaPosition . " = '" . Config::$mediaPositionCover . "'" .
            " OR " .
            Media::$table_name . "." . Media::$col_media . " is null" .
            ") " .
            " ORDER BY " .
            Ordere::$table_name . "." . ordere::$col_code .
            " , " .
            Category::$table_name . "." . Category::$col_display;
        //        print_r($query1); 
        $stmt = $conn->prepare($query1);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindParam(':table_id', $table_id, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);

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
    }

    //This function is used in create Suborder.
    //It contains query to check existance for all suborders except thos who are supplement or accept supplements
    static function existSubOrder(SubOrder $subOrder)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_prepare .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_acceptSupplement .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            SubOrder::$col_object_id . "= :object_id" .
            " AND (" .
            SubOrder::$col_attributeValue_id . "= :attributeValue_id" . " OR " . SubOrder::$col_attributeValue_id . " is null)" .
            " AND " .
            SubOrder::$col_ordere_id . "= :ordere_id" .
            " AND " .
            Category::$col_acceptSupplement . "<> '1'" . //Not accept supplement
            " AND " .
            Category::$col_supplement . "<> '1'";    //is not supplement
        //                 print_r($query);

        $query1 = "SELECT " .
            SubOrder::$col_id .
            " FROM " .
            SubOrder::$table_name .
            " WHERE " .
            SubOrder::$col_object_id . "= :object_id" .
            " AND (" .
            SubOrder::$col_attributeValue_id . "= :attributeValue_id" . " OR " . ":attributeValue_id is null)" .
            " AND " .
            SubOrder::$col_ordere_id . "= :ordere_id";


        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $subOrder->getObject_id(), PDO::PARAM_INT);
        $stmt->bindValue(':attributeValue_id', $subOrder->getAttributeValue_id(), PDO::PARAM_INT);
        $stmt->bindValue(':ordere_id', $subOrder->getOrdere_id(), PDO::PARAM_INT);

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

    //Delete SubOrder
    static function deleteSubOrder($id, $deleteOrder)
    {

        $supplements = JsonSupplement::getSupplementsOfSuborder($id, false);

        $subOrder = JsonSubOrder::getSubOrderById($id, False);

        $conn = Database::getConnection();

        // *********** Prevent deletion if the related order is not from today or is already paid
        if ($subOrder !== NULL) {
            $ordere_id = $subOrder[0][SubOrder::$col_ordere_id];

            $checkQ = "SELECT " . Ordere::$col_updateDate . ", " . Ordere::$col_payed .
                " FROM " . Ordere::$table_name .
                " WHERE " . Ordere::$col_id . " = :id LIMIT 1";
            $checkStmt = $conn->prepare($checkQ);
            $checkStmt->bindValue(':id', $ordere_id, PDO::PARAM_INT);

            if (!$checkStmt->execute()) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                addTrace(getMsgPdoStmt($checkStmt) . " " . __FUNCTION__);
                exit;
            }
            $orderRow = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$orderRow) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
                exit;
            }

            $currDate = date('Y-m-d');
            $orderDate = date('Y-m-d', strtotime($orderRow[Ordere::$col_updateDate] ?? ''));

            // combined condition: only block when OLD AND PAYED
            if (($orderDate !== $currDate) && ((int)($orderRow[Ordere::$col_payed] ?? 0) === 1)) {
                echo json_encode(array("state" => "f", "message" => "Cannot delete validated orders."));
                exit;
            }
        }

        // *********** End of prevention logic ***********************

        //Delete subOrder from database
        $query = "DELETE FROM " . SubOrder::$table_name .
            " WHERE " .
            SubOrder::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        // Delete Supplements suborder that have relation with the Object in this suborder 
        // (Here we manually delete supplement in case of the user delete suborder that contain a supplement)
        if ($supplements !== NULL) {
            JsonSubOrder::deleteSupplementsOfSubOrders($supplements);
        }

        if ($subOrder !== NULL) {
            //IF it is the last subOrder of order we delete Ordere
            if (JsonSubOrder::lastSubOrderOfOrder($subOrder[0][SubOrder::$col_ordere_id])) {
                if ($deleteOrder) { //If deleteOrder it is true, that mean that the request is get from Cart.js
                    JsonOrdere::deleteOrder($subOrder[0][SubOrder::$col_ordere_id], FALSE);
                } else { //If delete Order it is false, that mean that the request is get from waiterPanel
                    JsonOrdere::cancelOrder($subOrder[0][SubOrder::$col_ordere_id], FALSE);
                }

                echo json_encode(array("state" => "s", "message" => Config::$last_subOrder));
            } else {
                echo json_encode(array("state" => "s"));
            }
        } else {
            echo json_encode(array("state" => "s"));
        }
    }

    //This function delete supplements suborders automatically  
    //when article suborders deleted by the user manually
    static function deleteSupplementsOfSubOrders($supplements)
    {
        //        print_r($supplements);
        $conn = Database::getConnection();

        foreach ((array) $supplements as $i => $supplement) {
            //                echo $supplement[Supplement::$col_supplementSuborderID];
            //Delete suborder from database
            $query = "DELETE FROM " . SubOrder::$table_name .
                " WHERE " .
                SubOrder::$col_id . "= :id";

            $stmt = $conn->prepare($query);
            $stmt->bindValue(':id', $supplement[Supplement::$col_supplementSuborderID], PDO::PARAM_INT);

            if (!$stmt->execute()) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                exit;
            }
        }
    }

    static function lastSubOrderOfOrder($ordere_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            SubOrder::$col_id .
            " FROM " .
            SubOrder::$table_name .
            " WHERE " .
            SubOrder::$col_ordere_id . " = :ordere_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        if ($stmt->rowCount() > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    static function countSubOrdersOfOrder($ordere_id)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            SubOrder::$col_id .
            " FROM " .
            SubOrder::$table_name .
            " WHERE " .
            SubOrder::$col_ordere_id . "= :ordere_id";


        $stmt = $conn->prepare($query);
        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        return $stmt->rowCount();
    }
    //This function used in auto update ordere status
    static function countSubOrdersOfOrderByProgression($ordere_id, $progression)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            SubOrder::$col_id .
            " FROM " .
            SubOrder::$table_name .
            " WHERE " .
            SubOrder::$col_ordere_id . "= :ordere_id" .
            " AND (" .
            SubOrder::$col_subProgression . "= :subProgression1" .
            " OR " .
            SubOrder::$col_subProgression . "= :subProgression2)";


        $stmt = $conn->prepare($query);
        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);
        $stmt->bindValue(':subProgression1', $progression, PDO::PARAM_STR);
        $stmt->bindValue(':subProgression2', Config::$orderStateDelivred, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        return $stmt->rowCount();
    }

    //This function is used in chefPanel.js
    static function getAllSubOrdersOfDay($search, $status, $orderBy)
    {
        $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;

        $username = isset($_SESSION["username"]) ? $_SESSION["username"] : 0;

        //if username is chefAll we don't need to filter by user_id
        //and get all suborders of All categories of the day
        if ($username !== null && strpos($username, Config::$userChefAll) === 0) {
            $queryPartUserId = "";
        }
        //if user is standard chef we need to filter by user_id
        //and get only suborders of his categories of the day
        else {
            $queryPartUserId = " AND " .
                User_Category::$table_name . "." . User_Category::$col_user_id . " = :user_id";
        }
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query1 = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            " suplTable.* " .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_updateDate .
            " , " .
            Category::$table_name . "." . Category::$col_prepare .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_valid .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_payed .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " FROM " .
            SubOrder::$table_name .
            " LEFT JOIN " .
            " (SELECT " .
            Objet::$table_name . "." . Objet::$col_title . " AS suplTitle " .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity . " AS suplQuantity " .
            " , " .
            Supplement::$table_name . "." . Supplement::$col_suborder_id .
            " FROM " .
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
            SubOrder::$table_name . "." . SubOrder::$col_id . " ) " .
            "AS suplTable " .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " = " .
            "suplTable.suborder_id" .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            User_Category::$table_name .
            " ON " .
            Category::$table_name . "." . Category::$col_id .
            " = " .
            User_Category::$table_name . "." . User_Category::$col_category_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .

            " AND (" .
            Ordere::$table_name . "." . Ordere::$col_valid . " = 1" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 1" .
            ") AND " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression . " = :progression" .
            " AND " .
            SubOrder::$table_name . "." . SubOrder::$col_subCode . " IS NOT NULL " .
            " AND " .
            Category::$table_name . "." . Category::$col_prepare . " IS TRUE " .

            $queryPartUserId .

            " AND " .
            Objet::$table_name . "." . Objet::$col_company_id . " = :company_id" .
            " AND " .
            " ( " .
            SubOrder::$table_name . "." . SubOrder::$col_subCode . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_table_id . " LIKE :search" .
            " OR " .
            Category::$table_name . "." . Category::$col_category . " LIKE :search" .
            " OR " .
            Objet::$table_name . "." . Objet::$col_title . " LIKE :search" .
            " OR " .
            Table::$table_name . "." . Table::$col_tableName . " LIKE :search" .
            " ) " .
            " ORDER BY " .
            SubOrder::$table_name . "." . SubOrder::$col_updateDate .
            " " . $orderBy;

        //        echo $query;      
        $stmt = $conn->prepare($query1);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progression', $status, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
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

        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    //This function is used in Progression Modal in user HMI 
    static function getSubOrdersProgressionOfDayByCookie($cookieID)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query1 = "SELECT " .
            SubOrder::$table_name . ".*" .
            " , " .
            " suplTable.* " .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_updateDate .
            " , " .
            Category::$table_name . "." . Category::$col_prepare .
            " , " .
            Category::$table_name . "." . Category::$col_supplement .
            " , " .
            Category::$table_name . "." . Category::$col_category .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_valid .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_payed .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " FROM " .
            SubOrder::$table_name .
            " LEFT JOIN " .
            " (SELECT " .
            Objet::$table_name . "." . Objet::$col_title . " AS suplTitle " .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity . " AS suplQuantity " .
            " , " .
            Supplement::$table_name . "." . Supplement::$col_suborder_id .
            " FROM " .
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
            SubOrder::$table_name . "." . SubOrder::$col_id . " ) " .
            "AS suplTable " .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " = " .
            "suplTable.suborder_id" .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_cookieID . " = :cookieID " .
            " ORDER BY " .
            Ordere::$table_name . "." . Ordere::$col_place . " DESC " .
            " , " .
            Category::$table_name . "." . Category::$col_display;

        $stmt = $conn->prepare($query1);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':cookieID', $cookieID, PDO::PARAM_STR);

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
    }

    //This function may be  used in Progression Modal in user order Code
    static function getAllSubOrdersProgressionOfDay($orderCode)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query = "SELECT " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_creationDate .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_display .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_progression .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_updateDate .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_code . " = :orderCode " .
            " ORDER BY " .
            Category::$table_name . "." . Category::$col_display;
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':orderCode', $orderCode, PDO::PARAM_STR);

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
    }

    //This function may be  used in Progression Modal in user HMI for ontable orders
    static function getAllTableSubOrdersProgressionOfDay($tableCode)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query = "SELECT " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_quantity .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_subTotal .
            " , " .
            SubOrder::$table_name . "." . SubOrder::$col_creationDate .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_display .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_progression .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_updateDate .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Category::$table_name .
            " ON " .
            Objet::$table_name . "." . Objet::$col_category_id .
            " = " .
            Category::$table_name . "." . Category::$col_id .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " LEFT JOIN " .
            Attribute_Value::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_attributeValue_id .
            " = " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_id .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_customerLeft . " ='0'" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " ='0'" .
            " AND " .
            Table::$table_name . "." . Table::$col_tableCode . " = :tableCode " .
            " ORDER BY " .
            Category::$table_name . "." . Category::$col_display;
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo', $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':tableCode', $tableCode, PDO::PARAM_STR);

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
    }

    // This function is used in Chef Panel update suborder and print
    static function updateSubOrderProgression($id, $progression, $print)
    {
        $suborder = JsonSubOrder::getSubOrderById($id, false);
        if ($print) {
            //************** Start Prepare to print Chef label *********************** 

            $article = $suborder[0][Objet::$col_title];
            $attributes = $suborder[0][Attribute_Value::$col_attributeValue];
            $supplPrint = '';
            $qte = $suborder[0][SubOrder::$col_quantity];
            $obs = $suborder[0][SubOrder::$col_subComment];

            //if ($suborder[0][Ordere::$col_table_id] == null) {
            //  $place = $suborder[0][Ordere::$col_code];
            //} else {
            //  $place = JsonTable::getTableById($suborder[0][Ordere::$col_table_id], false)->getTableName() . " " . $suborder[0][Ordere::$col_code];
            //}
            if ($suborder[0][Ordere::$col_table_id] == null) {
                if ($suborder[0][Ordere::$col_place] == Config::$orderPlaceCarryWith) {
                    $place = $suborder[0][Ordere::$col_place] . " " . $suborder[0][Ordere::$col_code];
                } else { //If there was no place selected (not ontable nether emporter)
                    $place = $suborder[0][Ordere::$col_code];
                }
            } else {
                $place = JsonTable::getTableById($suborder[0][Ordere::$col_table_id], false)->getTableName() . " " . $suborder[0][Ordere::$col_code];
            }

            //*************** Start prepare supplements if exist **************************
            $supplements = JsonSupplement::getSupplementsOfSuborder($id, false);
            foreach ((array) $supplements as $i => $suppl) {
                $supplPrint = $supplPrint .  $suppl[Suborder::$col_quantity] . $suppl[Objet::$col_title] . "-";
            }
            //*************** End prepare supplements if exist ****************************

            $printer = JsonPrinter::getPrinterByCategoryId($suborder[0][Objet::$col_category_id], false);
            $printerRow = (is_array($printer) && isset($printer[0]) && is_array($printer[0])) ? $printer[0] : null;
            if (!empty($printerRow[Printer::$col_id] ?? null)) {
                // Re-wrap as array-of-row for downstream functions
                $printer = [$printerRow];
                printChefLabel($place, $qte, $article, $attributes, $supplPrint, $obs, $printer, false);
            }
        }

        //************** End Prepare print Chef label *********************** 

        $conn = Database::getConnection();
        $query = "UPDATE " . SubOrder::$table_name .
            " SET " .
            SubOrder::$col_subProgression . "= :progression" .
            " WHERE " .
            SubOrder::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':progression', $progression, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        } else {
            //
            JsonSubOrder::autoUpdateOrderProgression($id, $progression);
            echo json_encode(array("state" => "s"));
        }
    }
    //This function is used in MENU PANEL update suborders and print Chef Label
    static function updateAllSubOrderProgressionAndPrint($subOrdersArray, $progression)
    {
        $prepareArray = array();
        if ($_SESSION["printer_id"] != null) {
            foreach ((array) $subOrdersArray as $i => $subOrder) {

                $id = $subOrder["id"];
                $place = '';
                $suborder = JsonSubOrder::getSubOrderById($id, false);
                //************** Start Prepare to print Chef label *********************** 

                $article = $suborder[0][Objet::$col_title];
                $attributes = $suborder[0][Attribute_Value::$col_attributeValue];
                $supplPrint = '';
                $qte = $suborder[0][SubOrder::$col_quantity];
                $obs = $suborder[0][SubOrder::$col_subComment];
                $subCode = $suborder[0][SubOrder::$col_subCode];

                if ($suborder[0][Ordere::$col_table_id] == null) {
                    if ($suborder[0][Ordere::$col_place] == Config::$orderPlaceCarryWith) {
                        $place = $suborder[0][Ordere::$col_place] . " " . $suborder[0][Ordere::$col_code];
                    } else { //If there was no place selected (not ontable nether emporter)
                        $place = $suborder[0][Ordere::$col_code];
                    }
                } else {
                    $place = JsonTable::getTableById($suborder[0][Ordere::$col_table_id], false)->getTableName() . " " . $suborder[0][Ordere::$col_code];
                }

                //*************** Start prepare supplements text if exist **************************
                $supplements = JsonSupplement::getSupplementsOfSuborder($id, false);
                foreach ((array) $supplements as $i => $suppl) {

                    //remove qte of supplements if =1
                    if ($suppl[Suborder::$col_quantity] == '1') {
                        $suppl[Suborder::$col_quantity] = '';
                    }
                    $supplPrint = $supplPrint . "" . $suppl[Suborder::$col_quantity] . " " . $suppl[Objet::$col_title] . "\xA";
                }
                //*************** End prepare supplements if exist ****************************

                $printer = JsonPrinter::getPrinterByCategoryId($suborder[0][Objet::$col_category_id], false);
                $printerRow = (is_array($printer) && isset($printer[0]) && is_array($printer[0])) ? $printer[0] : null;
                if (!empty($printerRow[Printer::$col_id] ?? null)) {
                    $protocol = $printerRow[Printer::$col_printerProtocole] ?? '';
                    if ($protocol === "ESC") {
                        $prepareArray[] = array(
                            "place" => $place,
                            "printer_id" => $printerRow[Printer::$col_id],
                            "printerIP" => $printerRow[Printer::$col_printerIP] ?? null,
                            "printerPort" => $printerRow[Printer::$col_printerPort] ?? null,
                            "article" => $article,
                            "qte" => $qte,
                            "attributes" => $attributes,
                            "supplements" => $supplPrint,
                            "obs" => $obs,
                            "subCode" => $subCode
                        );
                    } else {
                        // Re-wrap as array-of-row for downstream functions
                        $printer = [$printerRow];
                        printChefLabel($place, $qte, $article, $attributes, $supplPrint, $obs, $printer, false);
                    }
                }
                //************** End Prepare print Chef label *********************** 

                //This is added to prevent script from update progression of not prepare articles
                //Because when printing from outside to Chef we are setting new progression to STARTED
                $progres = '';
                if (($suborder[0][Category::$col_prepare] != "0") && ($suborder[0][SubOrder::$col_subProgression] != Config::$orderStateReady)) {
                    $progres = $progression;
                } else {
                    $progres = Config::$orderStateReady;
                }

                $conn = Database::getConnection();
                $query = "UPDATE " . SubOrder::$table_name .
                    " SET " .
                    SubOrder::$col_subProgression . "= :progression" .
                    " WHERE " .
                    SubOrder::$col_id . "= :id";

                $stmt = $conn->prepare($query);

                $stmt->bindValue(':progression', $progres, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);

                if (!$stmt->execute()) {
                    echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                    addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                    exit;
                } else {
                    //
                    JsonSubOrder::autoUpdateOrderProgression($id, $progression);
                }
            }
            //If we have suborders that will be printed in ESC printers to print them 
            //in one ticket by Chef by Printer
            if (!empty($prepareArray)) {
                // var_dump($prepareArray);
                printChefOneLabel($prepareArray, $place);
            }
        }
        echo json_encode(array("state" => "s"));
    }

    static function rePrintSubOrder($id)
    {
        //************** Start Prepare to print Chef label *********************** 
        $suborder = JsonSubOrder::getSubOrderById($id, false);

        $article = $suborder[0][Objet::$col_title];
        $attributes = $suborder[0][Attribute_Value::$col_attributeValue];
        $supplPrint = '';
        $qte = $suborder[0][SubOrder::$col_quantity];
        $obs = $suborder[0][SubOrder::$col_subComment];

        // if ($suborder[0][Ordere::$col_table_id] == null) {
        //     $place = $suborder[0][Ordere::$col_code];
        // } else {
        //     $place = JsonTable::getTableById($suborder[0][Ordere::$col_table_id], false)->getTableName() . " " . $suborder[0][Ordere::$col_code];;
        // }

        if ($suborder[0][Ordere::$col_table_id] == null) {
            if ($suborder[0][Ordere::$col_place] == Config::$orderPlaceCarryWith) {
                $place = $suborder[0][Ordere::$col_place] . " " . $suborder[0][Ordere::$col_code];
            } else { //If there was no place selected (not ontable nether emporter)
                $place = $suborder[0][Ordere::$col_code];
            }
        } else {
            $place = JsonTable::getTableById($suborder[0][Ordere::$col_table_id], false)->getTableName() . " " . $suborder[0][Ordere::$col_code];
        }

        //*************** Start prepare supplements if exist **************************
        $supplements = JsonSupplement::getSupplementsOfSuborder($id, false);
        foreach ((array) $supplements as $i => $suppl) {
            $supplPrint = $supplPrint .  $suppl[Suborder::$col_quantity] . $suppl[Objet::$col_title] . "-";
        }
        //*************** End prepare supplements if exist ****************************

        // $printer = JsonPrinter::getPrinterById($_SESSION["printer_id"], false);
        // Try printer-all first, fallback to category printer
        $printerAll = JsonPrinter::getPrinterAllByCompanyId($_SESSION['company_id'], false);
        $printerAllRow = (is_array($printerAll) && isset($printerAll[0]) && is_array($printerAll[0])) ? $printerAll[0] : null;

        if (!empty($printerAllRow[Printer::$col_id] ?? null)) {
            $printer = [$printerAllRow];
        } else {
            $printer = JsonPrinter::getPrinterByCategoryId($suborder[0][Objet::$col_category_id], false);
            $printerRow = (is_array($printer) && isset($printer[0]) && is_array($printer[0])) ? $printer[0] : null;
            $printer = !empty($printerRow[Printer::$col_id] ?? null) ? [$printerRow] : null;
        }

        if (!empty($printer)) {
            printChefLabel($place, $qte, $article, $attributes, $supplPrint, $obs, $printer, true);
        }
        echo json_encode(array("state" => "s"));


        //************** End Prepare to print Chef label ***********************    
    }
    //This function is used from JsonOrder to auto update order suborders progression
    //In case the client paye before eat we use this function
    static function updateAllSubOrdersOfOrderByProgression($ordere_id, $progression)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . SubOrder::$table_name .
            " SET " .
            SubOrder::$col_subProgression . "= :progression" .
            " WHERE " .
            SubOrder::$col_ordere_id . "= :ordere_id" .
            " AND " .
            SubOrder::$col_subProgression . "<> :progresReady"; //This line is added to prevent update suborders that have object with category Not prepare

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':progression', $progression, PDO::PARAM_STR);
        $stmt->bindValue(':progresReady', Config::$orderStateReady, PDO::PARAM_STR);
        $stmt->bindValue(':ordere_id', $ordere_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
    }

    // This function updates order progression automatically
    static function autoUpdateOrderProgression($suborder_id, $progression)
    {
        //If one sub order is STARTED, then update Ordere status to STARTED
        if ($progression == Config::$orderStateStarted) {
            $subOrder = JsonSubOrder::getSubOrderById($suborder_id, False);
            JsonOrdere::updateOrderStatus($subOrder[0][SubOrder::$col_ordere_id], Config::$orderStateStarted, FALSE);
        }
        // If All sub-orders are READY, then update Ordere progression statu to READY
        if ($progression == Config::$orderStateReady) {
            $subOrder = JsonSubOrder::getSubOrderById($suborder_id, False);
            $ordere_id = $subOrder[0][SubOrder::$col_ordere_id];
            if (JsonSubOrder::countSubOrdersOfOrderByProgression($ordere_id, Config::$orderStateReady) == JsonSubOrder::countSubOrdersOfOrder($ordere_id)) {
                JsonOrdere::updateOrderStatus($ordere_id, Config::$orderStateReady, FALSE);
            }
        }
    }

    // This function is used in checkoutHistory to retrieve all paid suborders of a day, 
    //grouped by object and attribute value, ordered by category display.
    //with every line there is total_of_day (Total HT) and totalVat_of_day and totalTTC_of_day 
    //to be able to print them in the end of the report
    static function getPaidSubOrdersSummaryOfDay($date_from, $date_to,  $extractData)
    {

        $date_from = date("Y-m-d", strtotime($date_from));
        $date_to = date("Y-m-d", strtotime($date_to));
        $conn = Database::getConnection();

        // Get company_id from session or as a parameter (here using session)
        $company_id = isset($_SESSION["company_id"]) ? $_SESSION["company_id"] : null;


        $query = "SELECT
            o." . Objet::$col_title . ",
            av." . Attribute_Value::$col_attributeValue . ",
            SUM(s." . SubOrder::$col_quantity . ") AS total_quantity,
            SUM(s." . SubOrder::$col_subTotal . ") AS total_subtotal,
            (
                SELECT COALESCE(SUM(s2." . SubOrder::$col_subTotal . "), 0)
                FROM " . SubOrder::$table_name . " s2
                INNER JOIN " . Ordere::$table_name . " o2
                    ON s2." . SubOrder::$col_ordere_id . " = o2." . Ordere::$col_id . "
                INNER JOIN " . Objet::$table_name . " obj2
                    ON s2." . SubOrder::$col_object_id . " = obj2." . Objet::$col_id . "
                WHERE DATE(o2." . Ordere::$col_updateDate . ") BETWEEN :date_from AND :date_to
                AND o2." . Ordere::$col_payed . " = 1
                AND obj2." . Objet::$col_company_id . " = :company_id
            ) AS total_of_day,
            (
                SELECT COALESCE(SUM(ord_vat." . Ordere::$col_vatAmount . "), 0)
                FROM " . Ordere::$table_name . " ord_vat
                WHERE DATE(ord_vat." . Ordere::$col_updateDate . ") BETWEEN :date_from AND :date_to
                AND ord_vat." . Ordere::$col_payed . " = 1
                AND ord_vat." . Ordere::$col_company_id . " = :company_id
            ) AS totalVat_of_day,
            (
                SELECT COALESCE(SUM(ord_ttc." . Ordere::$col_totalTtc . "), 0)
                FROM " . Ordere::$table_name . " ord_ttc
                WHERE DATE(ord_ttc." . Ordere::$col_updateDate . ") BETWEEN :date_from AND :date_to
                AND ord_ttc." . Ordere::$col_payed . " = 1
                AND ord_ttc." . Ordere::$col_company_id . " = :company_id
            ) AS totalTTC_of_day
        FROM
            " . SubOrder::$table_name . " s
        INNER JOIN " . Objet::$table_name . " o
            ON s." . SubOrder::$col_object_id . " = o." . Objet::$col_id . "
        INNER JOIN " . Category::$table_name . " c
            ON o." . Objet::$col_category_id . " = c." . Category::$col_id . "
        INNER JOIN " . Ordere::$table_name . " ord
            ON s." . SubOrder::$col_ordere_id . " = ord." . Ordere::$col_id . "
        LEFT JOIN " . Attribute_Value::$table_name . " av
            ON s." . SubOrder::$col_attributeValue_id . " = av." . Attribute_Value::$col_id . "
        WHERE
            DATE(ord." . Ordere::$col_updateDate . ") BETWEEN :date_from AND :date_to
            AND ord." . Ordere::$col_payed . " = 1
            AND o." . Objet::$col_company_id . " = :company_id
        GROUP BY
            o." . Objet::$col_id . ", av." . Attribute_Value::$col_id . "
        ORDER BY
            c." . Category::$col_display . ", o." . Objet::$col_title . ", av." . Attribute_Value::$col_attributeValue . ";";
        // print_r($query);
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':date_from', $date_from, PDO::PARAM_STR);
        $stmt->bindValue(':date_to', $date_to, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);



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
        $output = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    //this function is used in _report_io.php to print sales label of paid suborders of a day  
    static function printPaidSubOrdersSummaryOfDay($startDate, $endDate)
    {
        $suborders = JsonSubOrder::getPaidSubOrdersSummaryOfDay($startDate, $endDate, false);

        if (empty($suborders)) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }

        //because Admin can print all suborders, we need to get the printer by company_id 
        //of the user with role checkout
        $printer = JsonPrinter::getCheckoutPrinterByCompanyId($_SESSION["company_id"], false);

        printSalesLabel($suborders, $printer, $startDate, $endDate);
        // echo json_encode($suborders);

        echo json_encode(array("state" => "s"));
    }
}
