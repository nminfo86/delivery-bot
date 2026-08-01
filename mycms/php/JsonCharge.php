<?php

require_once "Database.php";
require_once "Charge.php";
require_once "Type_Charge.php";
require_once "functions.php";
require_once "JsonPrinter.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "create") {
        confirmLoggedIn();
        JsonCharge::create(createChargeFromGetVariables());
    }
    if ($_POST['function'] === "update") {
        confirmLoggedIn();

        JsonCharge::update(createChargeFromGetVariables());
    }
    if (($_POST['function'] === "delete") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonCharge::delete($_POST['id']);
    }

    if (($_POST['function'] === "getChargeById") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonCharge::getChargeById($_POST['id'], TRUE);
    }

    if (($_POST['function'] === "getAllCharges") && (isset($_POST['startDate'])) && (isset($_POST['endDate']))) {
        confirmLoggedIn();
        JsonCharge::getAllCharges($_POST['startDate'], $_POST['endDate'], true);
    }
    if (($_POST['function'] === "getAllChargesByDecaisse")
        && (isset($_POST['startDate']))
        && (isset($_POST['endDate']))
        && (isset($_POST['decaisse']))
    ) {
        confirmLoggedIn();
        JsonCharge::getAllChargesByDecaisse($_POST['startDate'], $_POST['endDate'], $_POST['decaisse'], true);
    }

     if (($_POST['function'] === "printChargesSummaryOfDay")
        && (isset($_POST['startDate']))
        && (isset($_POST['endDate']))
    ) {
        confirmLoggedIn();
        JsonCharge::printChargesSummaryOfDay($_POST['startDate'], $_POST['endDate'], isset($_POST['decaisse']) ? $_POST['decaisse'] : 0);
    }
}

function createChargeFromGetVariables()
{
    $charge = new Charge();
    if (isset($_POST[Charge::$col_id])) {
        $charge->setId($_POST[Charge::$col_id]);
        //
    }
    $charge->setAmount($_POST[Charge::$col_amount]);
    $charge->setDateTime(date("Y-m-d", strtotime($_POST[Charge::$col_dateTime])));
    $charge->setObservation(trim($_POST[Charge::$col_observation]));
    $charge->setDecaise($_SESSION["role"] == Config::$roleCheckout ? "1" : $_POST[Charge::$col_decaise]);
    $charge->setTypeCharge_id($_POST[Charge::$col_typeCharge_id]);
    $charge->setCompany_id(isset($_POST[Charge::$col_company_id]) ? $_POST[Charge::$col_company_id] : $_SESSION["company_id"]);

    return $charge;
}

class JsonCharge
{
    // create Charge
    static function create(Charge $Charge)
    {
        $conn = Database::getConnection();
        $query = "INSERT INTO " . Charge::$table_name .
            " ( " .
            Charge::$col_typeCharge_id .
            " , " .
            Charge::$col_dateTime .
            " , " .
            Charge::$col_decaise .
            " , " .
            Charge::$col_amount .
            " , " .
            Charge::$col_observation .
            " , " .
            Charge::$col_company_id .
            " , " .
            Charge::$col_creationDate .
            " ) " .
            " VALUES (:typeCharge_id, :dateTime, :decaise, :amount, :observation, :company_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':typeCharge_id', $Charge->getTypeCharge_id(), PDO::PARAM_INT);
        $stmt->bindValue(':dateTime', $Charge->getDateTime(), PDO::PARAM_STR);
        $stmt->bindValue(':amount', $Charge->getAmount(), PDO::PARAM_STR);
        $stmt->bindValue(':observation', $Charge->getObservation(), PDO::PARAM_STR);
        $stmt->bindValue(':decaise', $Charge->getDecaise(), PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $Charge->getCompany_id(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            Charge::$col_id .
            " FROM " .
            Charge::$table_name .
            " ORDER BY " .
            Charge::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => getMsgPdoStmt($stmt) . " " . __FUNCTION__));
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        JsonCharge::getChargeById($id, TRUE);
    }

    //update Charge
    static function update(Charge $Charge)
    {

        $conn = Database::getConnection();

        $query = "UPDATE " . Charge::$table_name .
            " SET " .
            Charge::$col_typeCharge_id . " = :typeCharge_id" .
            " , " .
            Charge::$col_dateTime . " = :dateTime" .
            " , " .
            Charge::$col_amount . " = :amount" .
            " , " .
            Charge::$col_observation . " = :observation" .
            " , " .
            Charge::$col_decaise . " = :decaise" .
            " WHERE " .
            Charge::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':typeCharge_id', $Charge->getTypeCharge_id(), PDO::PARAM_INT);
        $stmt->bindValue(':dateTime', $Charge->getDateTime(), PDO::PARAM_STR);
        $stmt->bindValue(':amount', $Charge->getAmount(), PDO::PARAM_STR);
        $stmt->bindValue(':observation', $Charge->getObservation(), PDO::PARAM_STR);
        $stmt->bindValue(':decaise', $Charge->getDecaise(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $Charge->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonCharge::getChargeById($Charge->getId(), TRUE);
    }

    //Delete Charge
    static function delete($id)
    {
        //
        $conn = Database::getConnection();

        $query = "DELETE FROM " . Charge::$table_name .
            " WHERE " .
            Charge::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        echo json_encode(array("state" => "s"));
    }

    // THis function is used in ajaxCharge. we search only Charges that does not have Role Admin
    //Also it is used in __report_io.ajax
    static function getAllCharges($startDate, $endDate, $extractData)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));
        $conn = Database::getConnection();

        //Add exception for checkout role users to display only Decais charges
        $checkoutExceptionQuery =  $_SESSION["role"] == Config::$roleCheckout ?
            " AND " . Charge::$table_name . "." . Charge::$col_decaise . " =1 " : "";

        $query = "SELECT " .
            Charge::$table_name . ".*" .
            " , " .
            Type_Charge::$table_name . "." . Type_Charge::$col_typeCharge .
            " FROM " .
            Charge::$table_name .
            " INNER JOIN " .
            Type_Charge::$table_name .
            " ON " .
            Charge::$table_name . "." . Charge::$col_typeCharge_id .
            " = " .
            Type_Charge::$table_name . "." . Role::$col_id .
            " WHERE " .
            Charge::$table_name . "." . Charge::$col_company_id . " =:company_id" .
            " AND " .
            " DATE(" . Charge::$table_name . "." . Charge::$col_dateTime . ")" .
            " BETWEEN :startDate AND :endDate" .
            $checkoutExceptionQuery .
            " order by " .
            Charge::$table_name . "." . Charge::$col_dateTime . " DESC";
        // echo $query;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);

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
            return array();
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
    // THis function is used in ajaxCharge. we search only Charges that does not have Role Admin
    //Also it is used in __report_io.ajax
    static function getAllChargesByDecaisse($startDate, $endDate, $decaisse, $extractData)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));
        $conn = Database::getConnection();

        $query = "SELECT " .
            Charge::$table_name . ".*" .
            " , " .
            Type_Charge::$table_name . "." . Type_Charge::$col_typeCharge .
            " FROM " .
            Charge::$table_name .
            " INNER JOIN " .
            Type_Charge::$table_name .
            " ON " .
            Charge::$table_name . "." . Charge::$col_typeCharge_id .
            " = " .
            Type_Charge::$table_name . "." . Role::$col_id .
            " WHERE " .
            Charge::$table_name . "." . Charge::$col_company_id . " =:company_id" .
            " AND " .
            " DATE(" . Charge::$table_name . "." . Charge::$col_dateTime . ")" .
            " BETWEEN :startDate AND :endDate" .
            " AND " .
            Charge::$table_name . "." . Charge::$col_decaise . " =:decaisse" .
            " order by " .
            Charge::$table_name . "." . Charge::$col_dateTime . " DESC";
        // echo $query;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindValue(':decaisse', $decaisse, PDO::PARAM_INT);

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
            return array();
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

    static function printChargesSummaryOfDay($startDate, $endDate, $decaisse = 0)
    {
        $charges = ((int)$decaisse === 1)
            ? JsonCharge::getAllChargesByDecaisse($startDate, $endDate, 1, false)
            : JsonCharge::getAllCharges($startDate, $endDate, false);

        if (empty($charges)) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }

        $printer = JsonPrinter::getCheckoutPrinterByCompanyId($_SESSION["company_id"], false);

        printChargesLabel($charges, $printer, $startDate, $endDate, ((int)$decaisse === 1));

        echo json_encode(array("state" => "s"));
    }

    // Get Charge by id
    static function getChargeById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Charge::$table_name . ".*" .
            " , " .
            Type_Charge::$table_name . "." . Type_Charge::$col_typeCharge .
            " FROM " .
            Charge::$table_name .
            " INNER JOIN " .
            Type_Charge::$table_name .
            " ON " .
            Charge::$table_name . "." . Charge::$col_typeCharge_id .
            " = " .
            Type_Charge::$table_name . "." . Role::$col_id .
            " WHERE " .
            Charge::$table_name . "." . Charge::$col_id . " =:id LIMIT 1";

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
            }
            return 0;
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }
}
