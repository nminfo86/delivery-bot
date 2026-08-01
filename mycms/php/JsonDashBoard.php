<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonDashBoard
 *
 * @author dell
 */

require_once "Database.php";

require_once "JsonOrdere.php";
require_once "JsonPrice.php";
require_once "JsonCategory.php";
require_once "Attribute_Value.php";
require_once "Ordere.php";
require_once "SubOrder.php";
require_once "Category.php";
require_once "Table.php";
require_once "Company.php";
require_once "functions.php";
require_once "Config.php";



if (isset($_POST['function'])) {

    if ($_POST['function'] === "getDailySales") {
        confirmLoggedIn();
        $daysback = isset($_POST['daysBack']) ? (int)$_POST['daysBack'] : 7;
        JsonDashBoard::getDailySales(TRUE, $daysback);
    }

    if ($_POST['function'] === "salesByCategoryByPeriod") {
        confirmLoggedIn();
        JsonDashBoard::salesByCategoryByPeriod(TRUE, $_POST['startDate'], $_POST['endDate']);
    }

    if ($_POST['function'] === "getTopSoldObjects") {
        confirmLoggedIn();
        // Get parameters from POST or set defaults
        $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01');
        $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-d');
        $sortBy = (isset($_POST['sortBy']) && $_POST['sortBy'] === 'amount') ? 'amount' : 'quantity';
        JsonDashBoard::getTopSoldObjects($startDate, $endDate, $sortBy);
    }

    if ($_POST['function'] === "getTopSoldCategories") {
        confirmLoggedIn();
        $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01');
        $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-d');
        $sortBy = (isset($_POST['sortBy']) && $_POST['sortBy'] === 'amount') ? 'amount' : 'quantity';
        JsonDashBoard::getTopSoldCategories($startDate, $endDate, $sortBy);
    }

    if ($_POST['function'] === "getTopEarningArticles") {
        confirmLoggedIn();
        JsonDashBoard::getTopEarningArticles($_POST['startDate'], $_POST['endDate']);
    }

    if ($_POST['function'] === "getTopEarningCategories") {
        confirmLoggedIn();
        JsonDashBoard::getTopEarningCategories($_POST['startDate'], $_POST['endDate']);
    }
}

class JsonDashBoard
{
    //put your code here

    static function getTotalSales($startDate, $endDate)
    {
        $conn = Database::getConnection();
        $query = "SELECT" .
            " SUM" .
            "(" .
            SubOrder::$table_name . "." . SubOrder::$col_subTotal .
            ")" .
            " AS " .
            " CA " .
            " FROM " .
            Ordere::$table_name .
            " INNER JOIN " .
            SubOrder::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " = " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " INNER JOIN " .
            Objet::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " = " .
            Objet::$table_name . "." . Objet::$col_id .
            " INNER JOIN " .
            Company::$table_name  .
            " ON " .
            Objet::$table_name . "." . Objet::$col_company_id .
            " = " .
            Company::$table_name . "." . Company::$col_id .
            " WHERE " .
            " DATE(" . Ordere::$table_name . "." . Ordere::$col_updateDate . ")" .
            " BETWEEN" .
            " :startDate " . "AND" . " :endDate" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " =1 " .
            " AND " .
            Company::$table_name . "." . Company::$col_id . " = :company_id";

            // echo $query;

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($output[0]["CA"]);
    }

    static function getTotalCharges($startDate, $endDate)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        // Get company_id from session
        $company_id = $_SESSION["company_id"];

        $conn = Database::getConnection();

        $query = "SELECT " .
            " SUM" .
            "(" .
            Charge::$table_name . "." . Charge::$col_amount .
            ")" .
            " AS " .
            " totalCharges " .
            " FROM " .
            Charge::$table_name .
            " WHERE " .
            Charge::$table_name . "." . Charge::$col_company_id . " =:company_id" .
            " AND " .
            " DATE(" . Charge::$table_name . "." . Charge::$col_dateTime . ")" .
            " BETWEEN :startDate AND :endDate";
        // echo $query;

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($output[0]["totalCharges"]);
    }

    public static function getTotalEarnings($startDate, $endDate)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));
        $conn = Database::getConnection();
        $company_id = $_SESSION["company_id"];

        $query = "
        SELECT 
            SUM(so.subTotal) - SUM(so.subCost) AS total_earning
        FROM suborder so
        JOIN object o ON so.object_id = o.id
        JOIN ordere ord ON so.ordere_id = ord.id
        LEFT JOIN price p ON p.object_id = o.id AND p.attributeValue_id = so.attributeValue_id
        WHERE o.company_id = :company_id
          AND ord.payed = 1
          AND DATE(so.updateDate) BETWEEN :startDate AND :endDate
        ";


        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return only the earning value (as float, default 0)
        return $result && $result['total_earning'] !== null ? (float)$result['total_earning'] : 0.0;
    }

    //This function is used in Checkout panel to calculate caisse
    static function getTotalDecaisseCharges($startDate, $endDate)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        // Get company_id from session
        $company_id = $_SESSION["company_id"];
        $conn = Database::getConnection();

        $query = "SELECT " .
            " SUM" .
            "(" .
            Charge::$table_name . "." . Charge::$col_amount .
            ")" .
            " AS " .
            " totalCharges " .
            " FROM " .
            Charge::$table_name .
            " WHERE " .
            Charge::$table_name . "." . Charge::$col_company_id . " =:company_id" .
            " AND " .
            Charge::$table_name . "." . Charge::$col_decaise . " =1" .
            " AND " .
            " DATE(" . Charge::$table_name . "." . Charge::$col_dateTime . ")" .
            " BETWEEN :startDate AND :endDate";
        // echo $query;

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($output[0]["totalCharges"]);
    }

    static function getChefRequets()
    {

        $company_id = $_SESSION["company_id"];
        $conn = Database::getConnection();
        $query =
            "SELECT " .
            SubOrder::$table_name . "." . SubOrder::$col_id .
            "," .
            " count(" . SubOrder::$table_name . "." . SubOrder::$col_id . ") as number" .
            " FROM " .
            SubOrder::$table_name .
            " INNER JOIN " .
            Ordere::$table_name .
            " ON " .
            SubOrder::$table_name . "." . SubOrder::$col_ordere_id .
            " = " .
            Ordere::$table_name . "." . Ordere::$col_id .
            " WHERE " .
            SubOrder::$table_name . "." . SubOrder::$col_subProgression  . " = :progression" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':progression', Config::$orderStateStarted, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return $output[0]["number"];
    }

    static function getDailySales($extractData, $daysBack = 7)
    {
        $company_id = $_SESSION["company_id"];
        $conn = Database::getConnection();

        // Step 1: Get the last N dates with sales
        $query = "
            SELECT DISTINCT DATE(updateDate) as date
            FROM ordere
            WHERE payed = 1
              AND company_id = :company_id
            ORDER BY date DESC
            LIMIT :days_back
        ";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':days_back', $daysBack, PDO::PARAM_INT);
        $stmt->execute();

        $dates = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dates[] = $row['date'];
        }
        if (empty($dates)) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
            return [];
        }

        // Step 2: Get earnings for those dates
        $inClause = implode(',', array_fill(0, (is_countable($dates) ? count($dates) : 0), '?'));
        $query = "
            SELECT 
                DAYNAME(updateDate) AS day,
                SUM(orderePrice) AS earning,
                company_id,
                CAST(updateDate AS DATE) AS date
            FROM ordere
            WHERE payed = 1
              AND company_id = ?
              AND DATE(updateDate) IN ($inClause)
            GROUP BY date, company_id
            ORDER BY date ASC
        ";
        $stmt = $conn->prepare($query);
        $bindIndex = 1;
        $stmt->bindValue($bindIndex++, $company_id, PDO::PARAM_INT);
        foreach ($dates as $date) {
            $stmt->bindValue($bindIndex++, $date, PDO::PARAM_STR);
        }
        $stmt->execute();

        $output = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function salesByCategoryByPeriod($extractData, $startDate, $endDate)
    {

        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $company_id = $_SESSION["company_id"];
        $conn = Database::getConnection();

        $query = "
             SELECT
                cat.category AS category,
                obj.company_id AS company_id,
                SUM(so.subTotal) AS value,
                ROUND(
                    SUM(so.subTotal) * 100 /
                    (
                        SELECT SUM(so2.subTotal)
                        FROM suborder so2
                        JOIN object obj2 ON so2.object_id = obj2.id
                        JOIN ordere ord2 ON so2.ordere_id = ord2.id
                        WHERE obj2.company_id = :company_id
                          AND ord2.payed = 1
                          AND DATE(so2.updateDate) BETWEEN :startDate AND :endDate
                    )
                ) AS percentage
            FROM suborder so
            JOIN object obj ON so.object_id = obj.id
            JOIN category cat ON cat.id = obj.category_id
            JOIN ordere ord ON so.ordere_id = ord.id
            WHERE obj.company_id = :company_id
              AND ord.payed = 1
              AND DATE(so.updateDate) BETWEEN :startDate AND :endDate
            GROUP BY cat.category, obj.company_id
            ORDER BY percentage DESC
            LIMIT 5
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);

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

    static function getPaiementRequests()
    {
        $currDate = date('Y-m-d');

        // Get company_id from session
        $company_id = $_SESSION["company_id"];

        $conn = Database::getConnection();
        $query =
            "SELECT *" .
            "," .
            " COUNT(*) as number" .
            " FROM " .
            Ordere::$table_name .
            " INNER JOIN " .
            Table::$table_name .
            " ON " .
            Ordere::$table_name . "." . Ordere::$col_table_id .
            " = " .
            Table::$table_name . "." . Table::$col_id .
            " WHERE " .
            Ordere::$table_name . "." . Ordere::$col_payed  . " = 0" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_updateDate . " LIKE :today" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id" .
            " GROUP BY IFNULL (" .
            Table::$table_name . "." . Table::$col_tableName . "," . Ordere::$table_name . "." . Ordere::$col_id .
            ")";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':today', '%' . $currDate . '%', PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        return $output[0]["number"];
    }

    public static function getTopSoldObjects($startDate, $endDate, $sortBy = 'quantity')
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $conn = Database::getConnection();
        $company_id = $_SESSION["company_id"];

        // SQL query with date filter and dynamic ORDER BY
        $query = "SELECT o.title, SUM(so.quantity) as quantity, SUM(so.subTotal) as amount 
                  FROM suborder so
                  JOIN object o ON so.object_id = o.id
                  JOIN ordere ord ON so.ordere_id = ord.id
                  WHERE o.company_id = :company_id 
                    AND ord.payed = 1
                    AND DATE(so.updateDate) BETWEEN :startDate AND :endDate
                  GROUP BY so.object_id
                  ORDER BY $sortBy DESC
                  LIMIT 10";

        try {
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ((is_countable($result) ? count($result) : 0) > 0) {
                echo json_encode($result);
            } else {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
        } catch (PDOException $e) {
            echo json_encode(array("state" => "f", "message" => $e->getMessage()));
        }
    }

    public static function getTopSoldCategories($startDate, $endDate, $sortBy = 'quantity')
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $conn = Database::getConnection();
        $company_id = $_SESSION["company_id"];

        // SQL query with date filter and dynamic ORDER BY
        $query = "SELECT c.category, SUM(so.quantity) as quantity, SUM(so.subTotal) as amount 
                  FROM suborder so
                  JOIN object o ON so.object_id = o.id
                  JOIN category c ON o.category_id = c.id
                  JOIN ordere ord ON so.ordere_id = ord.id
                  WHERE o.company_id = :company_id 
                    AND ord.payed = 1
                    AND DATE(so.updateDate) BETWEEN :startDate AND :endDate
                  GROUP BY o.category_id
                  ORDER BY $sortBy DESC
                  LIMIT 10";

        try {
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ((is_countable($result) ? count($result) : 0) > 0) {
                echo json_encode($result);
            } else {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
        } catch (PDOException $e) {
            echo json_encode(array("state" => "f", "message" => $e->getMessage()));
        }
    }

    public static function getTopEarningArticles($startDate, $endDate)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));
        $conn = Database::getConnection();
        $company_id = $_SESSION["company_id"];

        $query = "
        SELECT 
            o.title,
            GROUP_CONCAT(DISTINCT av.attributeValue SEPARATOR ', ') AS attributeValues,
            SUM(so.quantity) AS total_qty,
            SUM(so.subTotal) AS total_sales,
            SUM(so.subCost) AS total_cost,
           SUM(so.subTotal) - SUM(so.subCost) AS earning
        FROM suborder so
        JOIN object o ON so.object_id = o.id
        JOIN ordere ord ON so.ordere_id = ord.id
        LEFT JOIN price p ON p.object_id = o.id AND p.attributeValue_id = so.attributeValue_id
        LEFT JOIN attribute_value av ON so.attributeValue_id = av.id
        WHERE o.company_id = :company_id
          AND ord.payed = 1
          AND DATE(so.updateDate) BETWEEN :startDate AND :endDate
        GROUP BY o.id
        ORDER BY earning DESC
        ";

            try {
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':company_id', $company_id);
                $stmt->bindParam(':startDate', $startDate);
                $stmt->bindParam(':endDate', $endDate);
                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ((is_countable($result) ? count($result) : 0) > 0) {
                    echo json_encode($result);
                } else {
                    echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
                }
            } catch (PDOException $e) {
                echo json_encode(array("state" => "f", "message" => $e->getMessage()));
            }
    }

    public static function getTopEarningCategories($startDate, $endDate)
    {
            $startDate = date("Y-m-d", strtotime($startDate));
            $endDate = date("Y-m-d", strtotime($endDate));
            $conn = Database::getConnection();
            $company_id = $_SESSION["company_id"];

            $query = "
            SELECT 
                c.category,
                SUM(so.quantity) AS total_qty,
                SUM(so.subTotal) AS total_sales,
                SUM(so.subCost) AS total_cost,
               SUM(so.subTotal) - SUM(so.subCost) AS earning
            FROM suborder so
            JOIN object o ON so.object_id = o.id
            JOIN category c ON o.category_id = c.id
            JOIN ordere ord ON so.ordere_id = ord.id
            LEFT JOIN price p ON p.object_id = o.id AND p.attributeValue_id = so.attributeValue_id
            WHERE o.company_id = :company_id
            AND ord.payed = 1
            AND DATE(so.updateDate) BETWEEN :startDate AND :endDate
            GROUP BY c.id, c.category
            ORDER BY earning DESC
        ";

        try {
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ((is_countable($result) ? count($result) : 0) > 0) {
                echo json_encode($result);
            } else {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
        } catch (PDOException $e) {
            echo json_encode(array("state" => "f", "message" => $e->getMessage()));
        }
    }
    
}
