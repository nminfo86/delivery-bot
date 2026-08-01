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
require_once "Printer.php";
require_once "Config.php";
require_once "functions.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "createPrinter") {
        confirmLoggedIn();
        JsonPrinter::create(createPrintertFromGetVariables());
    }
    if ($_POST['function'] === "updatePrinter") {
        confirmLoggedIn();
        JsonPrinter::update(createPrintertFromGetVariables());
    }
    if (($_POST['function'] === "deletePrinter") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonPrinter::delete($_POST['id']);
    }

    if (($_POST['function'] === "getPrinterById") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonPrinter::getPrinterById($_POST['id'], TRUE);
    }
    if (($_POST['function'] === "searchPrinter") && (isset($_POST['search']))) {
        confirmLoggedIn();
        JsonPrinter::searchPrinter($_POST['search']);
    }
    if ($_POST['function'] === "checkPrintersConnection") {
        confirmLoggedIn();
        JsonPrinter::checkPrintersConnection();
    }
}

function createPrintertFromGetVariables()
{
    $printer = new Printer();
    if (isset($_POST[Printer::$col_id])) {
        $printer->setId($_POST[Printer::$col_id]);
    }

    $printer->setPrinterName(trim($_POST[Printer::$col_printerName]));
    $printer->setPrinterIP($_POST[Printer::$col_printerIP]);
    $printer->setPrinterPort($_POST[Printer::$col_printerPort]);
    $printer->setPrinterProtocole($_POST[Printer::$col_printerProtocole]);
    $printer->setLabelSize($_POST[Printer::$col_labelSize]);
    $printer->setCompanyId($_SESSION['company_id']);

    return $printer;
}

class JsonPrinter
{
    //put your code here

    static function searchPrinter($search)
    {
        $conn = Database::getConnection();

        $query = "SELECT " .
            Printer::$table_name . ".*" .
            " FROM " .
            Printer::$table_name .
            " WHERE " .
            Printer::$table_name . "." . Printer::$col_company_id . " = :company_id " .
            " AND (" .
            Printer::$table_name . "." . Printer::$col_printerName . " LIKE :search " .
            " OR " .
            Printer::$table_name . "." . Printer::$col_printerIP . " LIKE :search " .
            " OR " .
            Printer::$table_name . "." . Printer::$col_labelSize . " LIKE :search " .
            " OR " .
            Printer::$table_name . "." . Printer::$col_printerProtocole . " LIKE :search " .
            " OR " .
            Printer::$table_name . "." . Printer::$col_printerPort . " LIKE :search )";

        //        print_r($query);
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);

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

    // create printer
    static function create(Printer $printer)
    {

        if (JsonPrinter::existPrinter($printer)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Printer::$table_name .
            " ( " .
            Printer::$col_printerName .
            " , " .
            Printer::$col_printerIP .
            " , " .
            Printer::$col_printerPort .
            " , " .
            Printer::$col_printerProtocole .
            " , " .
            Printer::$col_labelSize .
            " , " .
            Printer::$col_company_id .
            " , " .
            Printer::$col_creationDate .
            " ) " .
            " VALUES (:printerName, :printerIP, :printerPort, :printerProtocole, :labelSize, :company_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':printerName', $printer->getPrinterName(), PDO::PARAM_STR);
        $stmt->bindValue(':printerIP', $printer->getPrinterIP(), PDO::PARAM_STR);
        $stmt->bindValue(':printerPort', $printer->getPrinterPort(), PDO::PARAM_STR);
        $stmt->bindValue(':printerProtocole', $printer->getPrinterProtocole(), PDO::PARAM_STR);
        $stmt->bindValue(':labelSize', $printer->getLabelSize(), PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $printer->getCompanyId(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        $id = $conn->lastInsertId();

        JsonPrinter::getPrinterById($id, TRUE);
    }

    // update printer
    static function update(Printer $printer)
    {

        //Test whether object already exists in DB
        if (JsonPrinter::existPrinter($printer)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }

        $conn = Database::getConnection();

        $query = "UPDATE " . Printer::$table_name .
            " SET " .
            Printer::$col_printerName . " = :printer_name " .
            " , " .
            Printer::$col_printerIP . " = :printer_ip " .
            " , " .
            Printer::$col_printerPort . " = :printer_port " .
            " , " .
            Printer::$col_printerProtocole . " = :printer_protocole " .
            " , " .
            Printer::$col_labelSize . " = :label_size" .
            " WHERE " .
            Printer::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':printer_name', $printer->getPrinterName(), PDO::PARAM_STR);
        $stmt->bindValue(':printer_ip', $printer->getPrinterIP(), PDO::PARAM_STR);
        $stmt->bindValue(':printer_port', $printer->getPrinterPort(), PDO::PARAM_STR);
        $stmt->bindValue(':printer_protocole', $printer->getPrinterProtocole(), PDO::PARAM_STR);
        $stmt->bindValue(':label_size', $printer->getLabelSize(), PDO::PARAM_STR);

        $stmt->bindValue(':id', $printer->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        JsonPrinter::getPrinterById($printer->getId(), TRUE);
    }

    // Delete Printer
    static function delete($id)
    {
        //
        $conn = Database::getConnection();

        $query = "DELETE FROM " . Printer::$table_name .
            " WHERE " .
            Printer::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        echo json_encode(array("state" => "s"));
    }
    // //THis function is used in PrinterManagement 
    static function getAllPrinters($extractData)
    {
        $output = array();
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Printer::$table_name .
            " WHERE " .
            Printer::$col_company_id . " = :company_id ";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
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
    static function getPrinterById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            Printer::$table_name .
            " WHERE " .
            Printer::$table_name . "." . Printer::$col_id . " = :id";

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
        }

        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    //This function is used in JsonSuborder printChefLabel to get the printer by Category of chef
    static function getPrinterByCategoryId($category_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Printer::$table_name . ".*" .
            " FROM " .
            Printer::$table_name .
            " INNER JOIN " .
            User::$table_name .
            " ON " .
            Printer::$table_name . "." . Printer::$col_id .
            " = " .
            User::$table_name . "." . User::$col_printer_id .
            " INNER JOIN " .
            User_Category::$table_name .
            " ON " .
            User_Category::$table_name . "." . User_Category::$col_user_id .
            " = " .
            User::$table_name . "." . User::$col_id .
            " WHERE " .
            User_Category::$table_name . "." . User_Category::$col_category_id . " = :category_id " .
            " LIMIT 1";

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
                exit;
            }
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    //this function is used in JsonSuborder rePrint checkout recip by admin 
    //to get the printer by company id and role checkout
    static function getCheckoutPrinterByCompanyId($company_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT " .
            Printer::$table_name . ".*" .
            " FROM " .
            Printer::$table_name .
            " INNER JOIN " .
            User::$table_name .
            " ON " .
            Printer::$table_name . "." . Printer::$col_id .
            " = " .
            User::$table_name . "." . User::$col_printer_id .
            " INNER JOIN " .
            Role::$table_name .
            " ON " .
            User::$table_name . "." . User::$col_role_id .
            " = " .
            Role::$table_name . "." . Role::$col_id .
            " WHERE " .
            User::$table_name . "." . User::$col_company_id . " = :company_id" .
            " AND " .
            Role::$table_name . "." . Role::$col_role . " = :role" .
            " LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindValue(':role', Config::$roleCheckout, PDO::PARAM_STR); // Assuming Config::$user_role is the intended value

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

    static function existPrinter(Printer $printer)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Printer::$table_name .
            " WHERE " .
            Printer::$col_printerName . " = :printer_name" .
            " AND " .
            Printer::$col_id . "<> :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':printer_name', $printer->getPrinterName(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $printer->getId(), PDO::PARAM_INT);

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


    // Get the "printer-all" printer for a company (used by chef reprint)
    static function getPrinterAllByCompanyId($company_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Printer::$table_name .
            " WHERE " . Printer::$col_company_id . " = :company_id" .
            " AND " . Printer::$col_printerName . " = :printerName" .
            " LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindValue(':printerName', Config::$printerAll, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            return [];
        }
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
            return [];
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function checkPrintersConnection()
    {
        $printers = self::getAllPrinters(false);
        $results = array();
        foreach ($printers as $printer) {
            $ip = $printer[Printer::$col_printerIP];
            $port = $printer[Printer::$col_printerPort];
            $id = $printer[Printer::$col_id];
            $status = 'disconnected';

            // Check if it's a USB printer (typically starts with USB or COM, or contains specific indicators)
            if (is_string($ip) && (preg_match('/^(USB|COM|LPT)/i', $ip) === 1 || stripos($ip, 'usb') !== false)) {
                // For USB printers on Windows the name is fixed in the config 
                // and we need to check if the printer is installed and online
                $printerName = Config::$USB_printer_name;

                // Get list of all installed printers on Windows
                $installedPrinters = array();
                exec('wmic printer get name', $printerList);

                // Remove the header line that says "Name"
                if ((is_countable($printerList) ? count($printerList) : 0) > 0) {
                    array_shift($printerList);
                }

                $printerList = is_array($printerList) ? $printerList : [];
                $installedPrinters = array_map(
                    static function ($v) {
                        return is_string($v) ? trim($v) : '';
                    },
                    $printerList
                );
                $installedPrinters = array_values(array_filter($installedPrinters, static fn($s) => $s !== ''));

                // Check if our USB printer is in the installed printers list
                if (is_string($printerName) && in_array($printerName, $installedPrinters, true)) {
                    // Further check the printer status
                    exec('wmic printer where name="' . $printerName . '" get WorkOffline', $statusOut);

                    // If count is greater than 1 and the second line is FALSE (not offline)
                    if ((is_countable($statusOut) ? count($statusOut) : 0) > 1 && trim(strtoupper($statusOut[1])) === 'FALSE') {
                        $status = 'connected';
                    }
                }
            }
            // For IP-based printers
            else if (filter_var($ip, FILTER_VALIDATE_IP) && is_numeric($port)) {
                $sock = @fsockopen($ip, $port, $errno, $errstr, 1.5); // 1.5s timeout
                if ($sock) {
                    $status = 'connected';
                    fclose($sock);
                }
            }

            $results[] = array('id' => $id, 'status' => $status);
        }
        echo json_encode(array('state' => 's', 'results' => $results));
    }
}
