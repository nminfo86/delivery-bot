<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonOrder
 *
 * @author dell
 */
require_once "Database.php";
require_once "Ordere.php";
require_once "Table.php";
require_once "JsonTable.php";
require_once "JsonSubOrder.php";
require_once "JsonCompany.php";
require_once "JsonPrinter.php";
require_once "functions.php";
require_once "Config.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "createOrder") {
        JsonOrdere::createOrder(true);
    }
    if (($_POST['function'] === "getOrderById") && (isset($_POST['id']))) {
        JsonOrdere::getOrderById($_POST['id'], true);
    }
    if (($_POST['function'] === "updateOrder") && (isset($_POST['id']))) {
        JsonOrdere::updateOrder(updateOrderFromGetVariables(), true);
    }
    if (($_POST['function'] === "updateOrderPayementAndPrint") && (isset($_POST['table_id'])) && (isset($_POST['ordere_id']))) {
        JsonOrdere::updateOrderPayementAndPrint($_POST['table_id'], $_POST['ordere_id']);
    }
    if (($_POST['function'] === "rePrintOrder") && (isset($_POST['ordere_id']))) {
        JsonOrdere::rePrintOrder($_POST['ordere_id']);
    }
    if (($_POST['function'] === "cancelOrder") && (isset($_POST['id']))) {
        JsonOrdere::cancelOrder($_POST['id'], true);
    }
    if (($_POST['function'] === "getOrderStatus") && (isset($_POST[Ordere::$col_code]))) {
        JsonOrdere::getOrderStatus($_POST[Ordere::$col_code], true);
    }
    if (($_POST['function'] === "getAllOrdersOfDayByProgression") && (isset($_POST[Ordere::$col_progression]))) {
        JsonOrdere::getAllOrdersOfDayByProgression($_POST["search"], $_POST[Ordere::$col_progression]);
    }
    if (($_POST['function'] === "getAllOrdersOfDayByValidation") && (isset($_POST[Ordere::$col_valid]))) {
        JsonOrdere::getAllOrdersOfDayByValidation($_POST["search"], $_POST[Ordere::$col_valid]);
    }
    if (($_POST['function'] === "getAllOrdersforHistory") && (isset($_POST["search"])) && (isset($_POST["date"]))) {
        JsonOrdere::getAllOrdersforHistory($_POST["search"], $_POST["date"]);
    }
    if (($_POST['function'] === "getAllOrdersOfDayByPayement") && (isset($_POST[Ordere::$col_payed])) && (isset($_POST["orderBy"]))) {
        JsonOrdere::getAllOrdersOfDayByPayement($_POST["search"], $_POST[Ordere::$col_payed], $_POST["orderBy"]);
    }
    if (($_POST['function'] === "updateOrderStatus") && (isset($_POST[Ordere::$col_id])) && (isset($_POST[Ordere::$col_progression]))) {
        JsonOrdere::updateOrderStatus($_POST[Ordere::$col_id], $_POST[Ordere::$col_progression], True);
    }

    if (($_POST['function'] === "updateOrderVatID") && 
    (isset($_POST[Ordere::$col_id])) &&  
    (isset($_POST[Ordere::$col_vat_id]))) {

        JsonOrdere::updateOrderVatID($_POST[Ordere::$col_id], $_POST[Ordere::$col_vat_id], True);
    }

    if (($_POST['function'] === "updateTableVatID") && 
    (isset($_POST[Ordere::$col_table_id])) &&  
    (isset($_POST[Ordere::$col_vat_id]))) {
        
        JsonOrdere::updateTableVatID($_POST[Ordere::$col_table_id], $_POST[Ordere::$col_vat_id], True);
    }

    if (($_POST['function'] === "updateOrdersCustomerLeftByTable") && (isset($_POST[Ordere::$col_table_id]))) {
        JsonOrdere::updateOrdersCustomerLeftByTable($_POST[Ordere::$col_table_id], True);
    }
    if (($_POST['function'] === "checkExistNotReadyOrdersOnTable") && (isset($_POST[Ordere::$col_table_id]))) {
        JsonOrdere::checkExistNotReadyOrdersOnTable($_POST[Ordere::$col_table_id], True);
    }
    if (($_POST['function'] === "getAllOrdersOfTable") && (isset($_POST[Table::$col_tableCode]))) {
        JsonOrdere::getAllOrdersOfTable($_POST[Table::$col_tableCode]);
    }
}

function updateOrderFromGetVariables()
{

    $order = new Ordere();

    if (isset($_POST[Ordere::$col_id])) {
        $order = JsonOrdere::getOrderById($_POST[Ordere::$col_id], FALSE);
    }
    if (isset($_POST[Ordere::$col_table_id])) {
        $order->setTable_id($_POST[Ordere::$col_table_id]);

        if ($_POST[Ordere::$col_table_id] == "NULL") {
            $order->setTable_id(NULL);
        }
    }
    if (isset($_POST[Ordere::$col_place])) {
        $order->setPlace($_POST[Ordere::$col_place]);

        $order->setCode("C" . (JsonOrdere::getCountOrdersOfDay() + 1));

        //Set a cookie to track user
        if (!isset($_COOKIE["cookieID"])) {
            $cookieID = '';
            if (isset($_SESSION["user_id"])) { //We use a trick to manage clients that have no Phone. we connect the PC to Waiter accounte
                $cookieID = $order->getCode();       //We set the cookie value = ordere code to prevent show other users Data in Progression
                setcookie('cookieID', $cookieID, time() + 120, '/', $_SERVER['SERVER_NAME'], false); // We set 3minutes cookie
            } else {
                $cookieID = session_id();
                setcookie('cookieID', $cookieID, time() + 10800, '/', $_SERVER['SERVER_NAME'], false); //We set 3hours cookie for standard users
            }

            $order->setCookieID($cookieID);
        } else {
            if (isset($_SESSION["user_id"])) {
                $cookieID = $order->getCode();
                setcookie('cookieID', $cookieID, time() + 120, '/', $_SERVER['SERVER_NAME'], false); // We set 3s cookie
            } else {
                $cookieID = $_COOKIE["cookieID"];
            }
            $order->setCookieID($cookieID);
        }
        //End Set a cookie to track user
        //Set a cookie "showProgress" to auto show progressionData to user after validate Order
        if (!isset($_COOKIE["showProgress"])) {
            if (!isset($_SESSION["user_id"])) {
                $cookieShow = "show";
                setcookie('showProgress', $cookieShow, 0, '/', $_SERVER['SERVER_NAME'], false);
            }
        }
    }

    if (isset($_POST[Ordere::$col_valid])) {
        $order->setValid($_POST[Ordere::$col_valid]);
        $order->setProgression(Config::$orderStateValid);
        JsonSubOrder::updateAllSubOrdersOfOrderByProgression($order->getId(), Config::$orderStateValid);
    }
    if (isset($_POST[Ordere::$col_payed])) {
        $order->setPayed($_POST[Ordere::$col_payed]);
    }
    if (isset($_POST[Ordere::$col_customerLeft])) {
        $order->setCustomerLeft($_POST[Ordere::$col_customerLeft]);
    }
    if (isset($_POST[Ordere::$col_vat_id])) {
        $order->setVat_id($_POST[Ordere::$col_vat_id]);
    }
    if (isset($_POST[Ordere::$col_discount_id])) {
        $order->setDiscount_id($_POST[Ordere::$col_discount_id]);

        if ($_POST[Ordere::$col_discount_id] == "NULL") {
            $order->setDiscount_id(NULL);
        }
    }
    if (isset($_POST[Ordere::$col_discountAmount])) {
        $order->setDiscountAmount($_POST[Ordere::$col_discountAmount]);
    }
    if (isset($_POST[Ordere::$col_company_id])) {
        $order->setCompany_id($_POST[Ordere::$col_company_id]);
    }
    if (isset($_POST[Ordere::$col_progression])) {
        $order->setProgression($_POST[Ordere::$col_progression]);
    }
    if (isset($_POST[Ordere::$col_comment])) {
        $order->setComment($_POST[Ordere::$col_comment]);
    }
    return $order;
}

class JsonOrdere
{

    //put your code here
    static function createOrder($extractData)
    {

        $conn = Database::getConnection();

        $query = "INSERT INTO " . Ordere::$table_name .
            " ( " .
            Ordere::$col_creationDate .
            " , " .
            Ordere::$col_progression .
            " )" .
            " VALUES (:creationDate, :progression)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);
        $stmt->bindValue(':progression', config::$orderStateNew, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            Ordere::$col_id .
            " FROM " .
            Ordere::$table_name .
            " ORDER BY " .
            Ordere::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        $_SESSION["ordere_id"] = $id;
        //   JsonOrdere::getOrderById($id,$extractData);

        //Delete unValidated User Orders
        $query2 = "DELETE " .
            " FROM " .
            Ordere::$table_name .
            " WHERE " .
            Ordere::$col_code . " IS NULL and updateDate < now() - interval 45 minute";

        $stmt2 = $conn->prepare($query2);


        if (!$stmt2->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        //

        return $id;
    }

    //This updateOrdere Function is used only for validate ordere after 
    //client or checkout user click on validate button
    static function updateOrder(Ordere $order, $extractData)
    {
        //        print_r($order);

        $conn = Database::getConnection();

        $query = "UPDATE " . Ordere::$table_name .
            " SET " .
            Ordere::$col_place . "= :place" .
            " , " .
            Ordere::$col_code . "= :code" .
            " , " .
            Ordere::$col_valid . "= :valid" .
            " , " .
            Ordere::$col_payed . "= :payed" .
            " , " .
            Ordere::$col_customerLeft . "= :customerLeft" .
            " , " .
            Ordere::$col_table_id . "= :table_id" .
            " , " .
            Ordere::$col_company_id . "= :company_id" .
            " , " .
            Ordere::$col_discount_id . "= :discount_id" .
            " , " .
            Ordere::$col_discountAmount . "= :discountAmount" .
            " , " .
            Ordere::$col_cookieID . "= :cookieID" .
            " , " .
            Ordere::$col_progression . "= :progression" .
            " , " .
            Ordere::$col_comment . "= :comment" .
            " , " .
            Ordere::$col_orderePrice . "= :orderePrice" .
            " WHERE " .
            Ordere::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':place', $order->getPlace(), PDO::PARAM_STR);
        $stmt->bindValue(':code', $order->getCode(), PDO::PARAM_STR);
        $stmt->bindValue(':valid', $order->getValid(), PDO::PARAM_BOOL);
        $stmt->bindValue(':payed', $order->getPayed(), PDO::PARAM_BOOL);
        $stmt->bindValue(':customerLeft', $order->getCustomerLeft(), PDO::PARAM_BOOL);
        $stmt->bindValue(':table_id', $order->getTable_id(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $order->getCompany_id(), PDO::PARAM_INT);
        if ($order->getDiscount_id() === NULL) {
            $stmt->bindValue(':discount_id', NULL, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':discount_id', $order->getDiscount_id(), PDO::PARAM_INT);
        }
        $stmt->bindValue(':discountAmount', $order->getDiscountAmount(), PDO::PARAM_STR);
        $stmt->bindValue(':cookieID', $order->getCookieID(), PDO::PARAM_STR);
        $stmt->bindValue(':progression', $order->getProgression(), PDO::PARAM_STR);
        $stmt->bindValue(':comment', $order->getComment(), PDO::PARAM_STR);
        $stmt->bindValue(':orderePrice', $order->getOrderePrice(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $order->getId(), PDO::PARAM_INT);
        //        print_r($order);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        if ($extractData) {
            JsonOrdere::getOrderById($order->getId(), TRUE);
        }
        unset($_SESSION["ordere_id"]);
    }

    //this function is used in Checkout panel when ordere payed
    static function updateOrderPayementAndPrint($table_id, $ordere_id)
    {
        $conn = Database::getConnection();
        $place = "";
        $subOrders = '';
        $onlyNotPrepareObject = False; //This variable is used to test whether the order 
        //contain only not prepare articles or not, if yes, we not print Client Ticket (For Printer optimisation)

        $company = JsonCompany::getCompanyById($_SESSION["company_id"], false);
        $printer = JsonPrinter::getPrinterById($_SESSION["printer_id"], false);
        // var_dump($printer);


        if ($table_id === "null") { //IF the payement concerns Emporter
            //            echo 'Enter Emporter '.$ordere_id;
            $ordere = JsonOrdere::getOrderById($ordere_id, FALSE);
            $vat = JsonVat::getVatById($ordere->getVat_id(), false);
            $vatRate = ($vat !== null) ? $vat->getRate() : null;

            // ************ Start Prepare to print Client Label *****************
            $subOrders = JsonSubOrder::getSubOrdersOfOrderLabel($ordere->getId(), false);

            //Test whether the Order contain only one object not preparable, 
            //if yes we dono not print Client Label
            if (((is_countable($subOrders) ? count($subOrders) : 0) == 1) && ($subOrders[0][Category::$col_prepare] == '0')) {
                $onlyNotPrepareObject = True;
            }
            //If the Checkout has not clicked on table choice and validate
            if (($ordere->getTable_id() == NULL) && ($ordere->getPlace() == Config::$orderPlaceOnTable)) {
                $place = " - " .T('order'). " : " . $ordere->getCode();
            }
            if (($ordere->getTable_id() == NULL) && ($ordere->getPlace() == Config::$orderPlaceCarryWith)) {
                $place = T('order')." : " .  T('take_away') . " " . $ordere->getCode();
            }
            $printer[0][Printer::$col_id] = $printer[0][Printer::$col_id] ?? null;

            if (($printer[0][Printer::$col_id] !== null) && (!$onlyNotPrepareObject)) { //If ther is no printer we do not print
                printClientLabel($ordere->getOrderePrice(), $ordere->getVatAmount(), $ordere->getTotalTtc(), $place, $subOrders, $company, $printer, $vatRate);
            }

            // ************ End Prepare to print Client Label *****************


            $ordere->setPayed('1');
            if ($ordere->getProgression() === Config::$orderStateNew) {
                $ordere->setProgression(Config::$orderStateValid);
                JsonSubOrder::updateAllSubOrdersOfOrderByProgression($ordere->getId(), Config::$orderStateValid);
            }
            $query = "UPDATE " . Ordere::$table_name .
                " SET " .
                Ordere::$col_payed . "= :payed" .
                " , " .
                Ordere::$col_progression . "= :progression" .
                " WHERE " .
                Ordere::$col_id . "= :id";

            $stmt = $conn->prepare($query);
            $stmt->bindValue(':payed', $ordere->getPayed(), PDO::PARAM_BOOL);
            $stmt->bindValue(':progression', $ordere->getProgression(), PDO::PARAM_STR);
            $stmt->bindValue(':id', $ordere->getId(), PDO::PARAM_INT);
            //        print_r($order);
            if (!$stmt->execute()) {
                echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                exit;
            }
            echo json_encode(array("state" => "s"));

        } else { //IF the payement concerns Tables
            //            echo 'Enter Table';
            $totalPrice = 0;
            $totalVat = 0;
            $totalTtc = 0;
            $vatRate = null;
            $place = '';
            $orderes = JsonOrdere::getAllOrdersOfTablePrint($table_id);
            $subOrders = JsonSubOrder::getSubOrdersOfTableLabel($table_id, false);
            // var_dump($subOrders);

            // ************ Start Prepare to print Client Label *****************
            $orderCodeArray = array();
            foreach ((array) $orderes as $i => $ord) {
                array_push($orderCodeArray, $ord[Ordere::$col_code]);
                $placeTable = $ord[Table::$col_tableName];
                //Place from any ordere table Name
                $totalPrice = $totalPrice + (float) $ord[Ordere::$col_orderePrice];
                $totalVat = $totalVat + (float) $ord[Ordere::$col_vatAmount];
                $totalTtc = $totalTtc + (float) $ord[Ordere::$col_totalTtc];
            }

            //Vat rate
            if (!empty($orderes)) {
                $firstVatId = $orderes[0][Ordere::$col_vat_id] ?? null;
                if ($firstVatId) {
                    $vatObj = JsonVat::getVatById($firstVatId, false);
                    $vatRate = ($vatObj !== null) ? $vatObj->getRate() : null;
                }
            }

            $place = $placeTable . " " . T('order') . " : " . implode(",", $orderCodeArray);

            //Test whether the Order contain only an object that is not preparable, 
            //if yes we do not print Client Label
            if (((is_countable($subOrders) ? count($subOrders) : 0) == 1) && ($subOrders[0][Category::$col_prepare] == '0')) {
                $onlyNotPrepareObject = True;
            }

            $printer[0][Printer::$col_id] = $printer[0][Printer::$col_id] ?? null;
            if (($printer[0][Printer::$col_id] !== null) && (!$onlyNotPrepareObject)) { //If ther is no printer we do not print
                printClientLabel($totalPrice, $totalVat, $totalTtc, $place, $subOrders, $company, $printer, $vatRate);
            }
            // ************ End Prepare to print Client Label ****************

            foreach ((array) $orderes as $i => $ord) {

                //Update suborders progression if the order progression is new of each order 
                $ordereProgression = $ord[Ordere::$col_progression];
                if ($ord[Ordere::$col_progression] === Config::$orderStateNew) {
                    $ordereProgression = Config::$orderStateValid;
                    JsonSubOrder::updateAllSubOrdersOfOrderByProgression($ord[Ordere::$col_id], Config::$orderStateValid);
                }

                //Update each order payement and progression
                $query = "UPDATE " . Ordere::$table_name .
                    " SET " .
                    Ordere::$col_payed . "= '1'" .
                    " , " .
                    Ordere::$col_progression . "= :progression" .
                    " WHERE " .
                    Ordere::$col_id . "= :id";

                $stmt = $conn->prepare($query);
                $stmt->bindValue(':progression', $ordereProgression, PDO::PARAM_STR);
                $stmt->bindValue(':id', $ord[Ordere::$col_id], PDO::PARAM_INT);
                //        print_r($order);
                if (!$stmt->execute()) {
                    echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                    addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
                    exit;
                }
            }
            echo json_encode(array("state" => "s"));
        }

        //
    }
    //this function is used in Checkout History panel for ordere rePrinting
    static function rePrintOrder($ordere_id)
    {
        $place = "";
        $subOrders = '';

        $company = JsonCompany::getCompanyById($_SESSION["company_id"], false);
        
        //because Admin can print all suborders, we need to get the printer by company_id 
        //of the user with role checkout
        $printer = JsonPrinter::getCheckoutPrinterByCompanyId($_SESSION["company_id"], false); 

        // echo json_encode($suborders);
        // var_dump($printer);
        $ordere = JsonOrdere::getOrderById($ordere_id, FALSE);

        //vat rate
        $vatObj = JsonVat::getVatById($ordere->getVat_id(), false);
        $vatRate = ($vatObj !== null) ? $vatObj->getRate() : null;

        $subOrders = JsonSubOrder::getSubOrdersOfOrderLabel($ordere->getId(), false);
        //            echo 'Enter Emporter '.$ordere_id;

        // ************ Start Prepare to print Client Label *****************

        //If place is not in table and is not carry with
        if (($ordere->getTable_id() == NULL) && ($ordere->getPlace() == Config::$orderPlaceOnTable)) {
            $place = " - " . T('order')." : " . $ordere->getCode();
        }
        //If place is emporter
        if (($ordere->getTable_id() == NULL) && ($ordere->getPlace() == Config::$orderPlaceCarryWith)) {
            $place = T('order')." : " .  T('take_away') . " " . $ordere->getCode();
        }
        //If place is on table
        if ($ordere->getTable_id() != NULL) {

            $place = JsonTable::getTableById($ordere->getTable_id(), false);
            $place = $place->getTableName() ." ". T('order'). " : " . $ordere->getCode();
        }

        $printer[0][Printer::$col_id] = $printer[0][Printer::$col_id] ?? null;
        if ($printer[0][Printer::$col_id] !== null) { //If ther is no printer we do not print
            printClientLabel($ordere->getOrderePrice(), $ordere->getVatAmount(), $ordere->getTotalTtc(), $place, $subOrders, $company, $printer, $vatRate);
        }
        echo json_encode(array("state" => "s"));


        //
    }

    static function getOrderById($id, $extractData)
    {

        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Ordere::$table_name .
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
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }

        $order = new Ordere();
        $order->setId($output[0][Ordere::$col_id]);
        $order->setPlace($output[0][Ordere::$col_place]);
        $order->setTable_id($output[0][Ordere::$col_table_id]);
        $order->setVat_id($output[0][Ordere::$col_vat_id]);
        $order->setDiscount_id($output[0][Ordere::$col_discount_id]);
        $order->setVatAmount($output[0][Ordere::$col_vatAmount]);
        $order->setDiscountAmount($output[0][Ordere::$col_discountAmount]);
        $order->setCompany_id($output[0][Ordere::$col_company_id]);
        $order->setCode($output[0][Ordere::$col_code]);
        $order->setProgression($output[0][Ordere::$col_progression]);
        $order->setComment($output[0][Ordere::$col_comment]);
        $order->setValid($output[0][Ordere::$col_valid]);
        $order->setPayed($output[0][Ordere::$col_payed]);
        $order->setOrderePrice($output[0][Ordere::$col_orderePrice]);
        $order->setTotalTtc($output[0][Ordere::$col_totalTtc]);
        $order->setCustomerLeft($output[0][Ordere::$col_customerLeft]);
        $order->setCookieID($output[0][Ordere::$col_cookieID]);
        return $order;
    }

    static function deleteOrder($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "DELETE " .
            " FROM " .
            Ordere::$table_name .
            " WHERE id = :id LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
        unset($_SESSION["ordere_id"]);
    }

    //This function is used by waiterPanel, it updates the order updateDate to old  
    //so that it will not be listed in orderes anymore
    static function cancelOrder($id, $extractData)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . Ordere::$table_name .
            " SET " .
            Ordere::$col_progression . "= :progression" .
            " , " .
            Ordere::$col_valid . "= '0' " .
            " , " .
            Ordere::$col_payed . "= '0' " .
            " WHERE " .
            Ordere::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':progression', Config::$orderStateCancel, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        } else {
            if ($extractData) {
                echo json_encode(array("state" => "s"));
            }
        }
    }

    static function getCountOrdersOfDay()
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();
        $query = "Select " .
            " count(id) as number" .
            " FROM " .
            Ordere::$table_name .
            " WHERE " .
            Ordere::$col_code . " IS NOT NULL " .
            " AND ".
            Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo";

        $stmt = $conn->prepare($query);
        // With date range:
$stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
$stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return $output[0]["number"];
    }

    static function getOrderStatus($code, $extractData)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();
        $query = "Select " .
            Ordere::$col_progression .
            " FROM " .
            Ordere::$table_name .
            " WHERE " .
            Ordere::$col_updateDate . " LIKE :search" .
            " AND " .
            Ordere::$col_code . " = :code";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search', '%' . $currDate . '%', PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);

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
        return $output[0][Ordere::$col_progression];
    }

    static function getOrderCode($id, $extractData)
    {

        $conn = Database::getConnection();
        $query = "Select " .
            Ordere::$col_code .
            " FROM " .
            Ordere::$table_name .
            " WHERE " .
            Ordere::$col_id . " = :id";

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
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output[0][Ordere::$col_code];
    }

    static function updateOrderVatID($ordere_id, $vat_id, $extractData)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . Ordere::$table_name .
            " SET " .
            Ordere::$col_vat_id . "= :vat_id" .
            " WHERE " .
            Ordere::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        if ($vat_id == "NULL") {
            $stmt->bindValue(':vat_id', NULL, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':vat_id', $vat_id, PDO::PARAM_INT);
        }
        $stmt->bindValue(':id', $ordere_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        } else {
            if ($extractData) {
            JsonOrdere::getOrderById($ordere_id, TRUE);
        }
        }
    }
    //This function is used Checkout panel to update vatID for all orderes of the table 
    //when the user change vat in checkout
    static function updateTableVatID($table_id, $vat_id, $extractData)
    {

        $conn = Database::getConnection();

        $query = "UPDATE " . Ordere::$table_name .
            " SET " . Ordere::$col_vat_id . " = :vat_id" .
            " WHERE " . Ordere::$col_table_id . " = :table_id";

        $stmt = $conn->prepare($query);

        if ($vat_id == "NULL") {
            $stmt->bindValue(':vat_id', NULL, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':vat_id', $vat_id, PDO::PARAM_INT);
        }
        $stmt->bindValue(':table_id', $table_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        } else {
            if ($extractData) {
                $output = JsonSubOrder::getSubOrdersOfTableLabel($table_id, false);
                echo json_encode($output);
            }
        }
    }

    static function updateOrderStatus($id, $progression, $extractData)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . Ordere::$table_name .
            " SET " .
            Ordere::$col_progression . "= :progression" .
            " WHERE " .
            Ordere::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':progression', $progression, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        } else {
            if ($extractData) {
                echo json_encode(array("state" => "s"));
            }
        }
    }

    static function updateOrdersCustomerLeftByTable($table_id, $extractData)
    {
        $conn = Database::getConnection();

        $query = "UPDATE " . Ordere::$table_name .
            " SET " .
            Ordere::$col_customerLeft . "= '1'" .
            " WHERE " .
            Ordere::$col_table_id . "= :table_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':table_id', $table_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        } else {
            if ($extractData) {
                echo json_encode(array("state" => "s"));
            }
        }
    }

    static function checkExistNotReadyOrdersOnTable($table_id, $extractData)
    {
        $currDate = date('Y-m-d');

        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Ordere::$table_name .
            " WHERE " .
            Ordere::$col_table_id . "= :table_id" .
            " AND " .
            " ( " .
            Ordere::$col_progression . "<> :progression1 " .
            " AND " .
            Ordere::$col_progression . "<> :progression2 " .
            " ) " .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_code . " IS NOT NULL" .
            " AND " .
            Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo";


        $stmt = $conn->prepare($query);

        $stmt->bindValue(':table_id', $table_id, PDO::PARAM_INT);
        $stmt->bindValue(':progression1', Config::$orderStateReady, PDO::PARAM_STR);
        $stmt->bindValue(':progression2', Config::$orderStateCancel, PDO::PARAM_STR);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);

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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getAllOrdersOfDayByProgression($search, $progression)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query = "SELECT " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_progression .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_valid .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_payed .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_comment .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discount_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discountAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Table::$table_name . "." . Table::$col_tableFree .
            " FROM " .
            Ordere::$table_name .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND (" .
            Ordere::$table_name . "." . Ordere::$col_valid . " = 0" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 0" .
            ") AND " .
            Ordere::$table_name . "." . Ordere::$col_code . " IS NOT NULL" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression" .
            " AND " .
            "( " .
            Ordere::$table_name . "." . Ordere::$col_code . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_comment . " LIKE :search" .
            " OR " .
            Table::$table_name . "." . Table::$col_tableName . " LIKE :search" .
            " ) " .
            " ORDER BY " .
            Ordere::$table_name . "." . Ordere::$col_updateDate;
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progression', $progression, PDO::PARAM_STR);
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

    // This function is used in waiter Panel to retriev all new orders for validation
    static function getAllOrdersOfDayByValidation($search, $valid)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query = "SELECT " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_progression .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_valid .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_payed .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_comment .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discount_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discountAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Table::$table_name . "." . Table::$col_tableFree .
            " FROM " .
            Ordere::$table_name .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_valid . " = :valid" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 0" .
            " AND " .
            // Ordere::$table_name . "." . Ordere::$col_company_id . " =:company_id" .
            // " AND " .
            Ordere::$table_name . "." . Ordere::$col_code . " IS NOT NULL" .
            " AND (" .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression1" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression2" .
            ") AND " .
            "( " .
            Ordere::$table_name . "." . Ordere::$col_code . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_table_id . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_comment . " LIKE :search" .
            " OR " .
            Table::$table_name . "." . Table::$col_tableName . " LIKE :search" .
            " ) " .
            " ORDER BY " .
            Ordere::$table_name . "." . Ordere::$col_updateDate;
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progression1', Config::$orderStateNew, PDO::PARAM_STR);
        $stmt->bindValue(':progression2', Config::$orderStateReady, PDO::PARAM_STR);
        $stmt->bindValue(':valid', $valid, PDO::PARAM_INT);
        // $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
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
    // This function is used in Checkout History to retriev all  orders that are already payed
    static function getAllOrdersforHistory($search, $date)
    {
        $currDate =  date("Y-m-d", strtotime($date));
        $conn = Database::getConnection();

        $query = "SELECT " .
            Ordere::$table_name . ".*"  .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Table::$table_name . "." . Table::$col_tableFree .
            " FROM " .
            Ordere::$table_name .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 1" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " =:company_id" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_code . " IS NOT NULL" .
            " AND " .
            "( " .
            Ordere::$table_name . "." . Ordere::$col_code . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_table_id . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_comment . " LIKE :search" .
            " OR " .
            Table::$table_name . "." . Table::$col_tableName . " LIKE :search" .
            " ) " .
            " ORDER BY " .
            Ordere::$table_name . "." . Ordere::$col_updateDate .
            " DESC";
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }
    // This function is used in checkout panel to retriev orders that are valide and not payed yet
    //this is for table orderes or without table orderes like carry with and without place
    static function getAllOrdersOfDayByPayement($search, $payed, $orderBy)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        // we use totalTtc because in case it is one ordere 
       // "carry with" or "without table" than we returne TotalTtc
        $query = "SELECT" .
            " sum(" .
            Ordere::$table_name . "." . Ordere::$col_totalTtc .   
            ") AS tablePrice" .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_code .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_place .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_progression .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_valid .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_payed .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_comment .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_vat_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discount_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_vatAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discountAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Table::$table_name . "." . Table::$col_tableFree .
            " FROM " .
            Ordere::$table_name .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = :payed" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_valid . " = 1" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_code . " IS NOT NULL" .
            " AND (" .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression1" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression2" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression3" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_progression . " = :progression4" .
            ") AND " .
            "( " .
            Ordere::$table_name . "." . Ordere::$col_code . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_place . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_table_id . " LIKE :search" .
            " OR " .
            Ordere::$table_name . "." . Ordere::$col_comment . " LIKE :search" .
            " OR " .
            Table::$table_name . "." . Table::$col_tableName . " LIKE :search" .
            " )" .
            " GROUP BY IFNULL (" .
            Table::$table_name . "." . Table::$col_tableName . "," . Ordere::$table_name . "." . Ordere::$col_id .
            ") ORDER BY " .
            Ordere::$table_name . "." . Ordere::$col_updateDate .
            " " . $orderBy;
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progression1', Config::$orderStateNew, PDO::PARAM_STR);
        $stmt->bindValue(':progression2', Config::$orderStateValid, PDO::PARAM_STR);
        $stmt->bindValue(':progression3', Config::$orderStateStarted, PDO::PARAM_STR);
        $stmt->bindValue(':progression4', Config::$orderStateReady, PDO::PARAM_STR);
        $stmt->bindValue(':payed', $payed, PDO::PARAM_INT);
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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        echo json_encode($output);
    }

    //THis function is used in cart.js to check whether table has allready NOT PAYED orders
    static function getAllOrdersOfTable($tableCode)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query = "SELECT" .
            " sum(" .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            ") AS tablePrice" .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_progression .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_payed .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discount_id .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_discountAmount .
            " , " .
            Ordere::$table_name . "." . Ordere::$col_orderePrice .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Table::$table_name . "." . Table::$col_tableCode .
            " , " .
            Table::$table_name . "." . Table::$col_tableFree .
            " FROM " .
            Ordere::$table_name .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_progression . "<> :progression" .
            " AND " .
            Table::$table_name . "." . Table::$col_tableCode . " = :tableCode" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 0" .
            " AND " .
            Table::$table_name . "." . Table::$col_tableFree . " = 0" .
            " GROUP BY " .
            Table::$table_name . "." . Table::$col_tableName;

        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progression', Config::$orderStateCancel, PDO::PARAM_STR);
        $stmt->bindValue(':tableCode', $tableCode, PDO::PARAM_INT);

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

    //This function is used in checkout when printing see updateOrderPayementAndPrint
    static function getAllOrdersOfTablePrint($table_id)
    {
        $currDate = date('Y-m-d');
        $conn = Database::getConnection();

        $query = "SELECT " .
            Ordere::$table_name . ".*" .
            " , " .
            Table::$table_name . "." . Table::$col_tableName .
            " , " .
            Table::$table_name . "." . Table::$col_tableCode .
            " , " .
            Table::$table_name . "." . Table::$col_tableFree .
            " FROM " .
            Ordere::$table_name .
            " LEFT JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " BETWEEN :dateFrom AND :dateTo" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_progression . "<> :progression" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_table_id . " = :table_id" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 0" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id";

        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':dateFrom', $currDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':dateTo',   $currDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':progression', Config::$orderStateCancel, PDO::PARAM_STR);
        $stmt->bindValue(':table_id', $table_id, PDO::PARAM_INT);
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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        return $output;
    }
}