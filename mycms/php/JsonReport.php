<?php

/**
 * Description of JsonReport
 * This class covers all database requests to handle reports data
 *
 * @author Nminfo
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

    if (($_POST['function'] === "getSalesByDate") && (isset($_POST['startDate'])) && (isset($_POST['endDate']))) {
        confirmLoggedIn();
        JsonReport::getSalesByDate($_POST['startDate'], $_POST['endDate'], true);
    }
    if (($_POST['function'] === "getSalesByAttributes")
        && (isset($_POST['startDate']))
        && (isset($_POST['endDate']))
        && (isset($_POST['category_id']))
    ) {
        confirmLoggedIn();

        //Get category name by ID, if category is Pizza, then the get data fro pizza category
        $categoryName = JsonCategory::getCategoryNameById($_POST['category_id'], false)[0][Category::$col_category];


        if (!empty($categoryName) && strpos((string)$categoryName, Config::$category_Pizza) === 0) {

            JsonReport::getSalesByAttributesOfPizza($_POST['startDate'], $_POST['endDate'], $_POST['category_id'], true);
        } else {

            JsonReport::getSalesByAttributes($_POST['startDate'], $_POST['endDate'], $_POST['category_id'], true);
        }
    }

    if (($_POST['function'] === "getSalesByAttributes2") && (isset($_POST['startDate'])) && (isset($_POST['endDate']))) {

        confirmLoggedIn();

        JsonReport::getSalesByAttributes($_POST['startDate'], $_POST['endDate'], 0, true);
    }

    if (($_POST['function'] === "getTotalVatByDate") && (isset($_POST['startDate'])) && (isset($_POST['endDate']))) {
    confirmLoggedIn();
    JsonReport::getTotalVatByDate($_POST['startDate'], $_POST['endDate'], true);
}
}

class JsonReport
{
    static function getSalesByDate($startDate, $endDate, $extractData)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $conn = Database::getConnection();

        $query = "SELECT " .
            "SUM(" . SubOrder::$table_name . "." . SubOrder::$col_quantity . ") AS sumQte" .
            " , " .
            "SUM(" . SubOrder::$table_name . "." . SubOrder::$col_subTotal . ") AS sumValue" .
            " , " .
            SubOrder::$table_name . ".*"  .
            " , " .
            Attribute_Value::$table_name . "." . Attribute_Value::$col_attributeValue .
            " , " .
            Objet::$table_name . "." . Objet::$col_title .
            " , " .
            Category::$table_name . "." . Category::$col_category .
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
            " DATE(" . Ordere::$table_name . "." . Ordere::$col_updateDate . ")" .
            " BETWEEN :startDate AND :endDate" .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_payed . " = 1 " .
            " AND " .
            Ordere::$table_name . "." . Ordere::$col_company_id . " =:company_id " .
            " GROUP BY " . SubOrder::$table_name . "." . SubOrder::$col_object_id .
            " ORDER BY sumValue";
        // Category::$table_name . "." . Category::$col_display;
        //        echo $query;
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION["company_id"], PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        // Get total VAT for the given date range
        $vatStmt = $conn->prepare(
            "SELECT COALESCE(SUM(" . Ordere::$table_name . "." . Ordere::$col_vatAmount . "),0) AS totalVat
         FROM " . Ordere::$table_name .
                " WHERE DATE(" . Ordere::$table_name . "." . Ordere::$col_updateDate . ") BETWEEN :startDate AND :endDate
        AND " . Ordere::$table_name . "." . Ordere::$col_payed . " = 1
        AND " . Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id"
        );
        $vatStmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $vatStmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $vatStmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);
        $vatStmt->execute();
        $vatRow = $vatStmt->fetch(PDO::FETCH_ASSOC);
        $totalVat = isset($vatRow['totalVat']) ? $vatRow['totalVat'] : 0;

        //
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
            exit;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        //this is custom extract data related to _report_io.js to send totalVat with sales data,
        //so we can use it in frontend to calculate total with VAT
        if ($extractData) {
            echo json_encode(array("state" => "s", "data" => $output, "totalVat" => $totalVat));
        }
        return $output;
        

    }

    static function getSalesByAttributesOfPizza($startDate, $endDate, $category_id, $extractData)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $conn = Database::getConnection();


        // get Id of 1/4_Pizza Category and 1/2_Pizza Category

        $category1_4Pizza = JsonCategory::getCategoryByName($_SESSION['company_id'], Config::$category_1_4_Pizza, false);
        $category1_2Pizza = JsonCategory::getCategoryByName($_SESSION['company_id'], Config::$category_1_2_Pizza, false);

        // Add test on 1/4 and 1/2 Ids, if there no categories, so we put them as 0 to prevent generater sql syntax error
        if ($category1_4Pizza == NULL) {
            $category1_4PizzaID = 0;
        } else {
            $category1_4PizzaID = $category1_4Pizza[0][Category::$col_id];
        }
        if ($category1_2Pizza == NULL) {
            $category1_2PizzaID = 0;
        } else {
            $category1_2PizzaID = $category1_2Pizza[0][Category::$col_id];
        }

        $pizzaCategory = JsonCategory::getCategoryByName($_SESSION['company_id'], Config::$category_Pizza, False);

        $sub_query = ' category_id=' . $category1_4PizzaID . " OR " . 'category_id=' . $category1_2PizzaID;
        foreach ((array)$pizzaCategory as $i => $category) {

            $sub_query = $sub_query . " OR " . 'category_id=' . $category[Category::$col_id];
        }

        $query = "
            SELECT
                SUM(suborder.quantity) AS total_qty,
                SUM(suborder.subTotal) AS total_price,
                object.category_id AS category_id,
                object.title AS title,
                category.category AS category,
                attribute_value.attributeValue AS attributeValue,
                CAST(suborder.updateDate AS DATE) AS updateDate
            FROM suborder
            JOIN object ON suborder.object_id = object.id
            JOIN ordere ON suborder.ordere_id = ordere.id
            LEFT JOIN attribute_value ON suborder.attributeValue_id = attribute_value.id
            JOIN category ON object.category_id = category.id
            WHERE ordere.payed = 1
            AND ordere.company_id = :company_id
            AND ( $sub_query )
            AND DATE(suborder.updateDate) BETWEEN :startDate AND :endDate
            GROUP BY object.title, suborder.attributeValue_id
            ORDER BY object.title
        ";


        $stmt = $conn->prepare($query);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);
        // $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        // $stmt->bindValue(':category_id_1_4', $category1_4PizzaID, PDO::PARAM_INT);
        // $stmt->bindValue(':category_id_1_2', $category1_2PizzaID, PDO::PARAM_INT);

        // End prepare query for Category Pizza

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

            //This line where added to make total_qty of 1/4 category and 1/2 category to 0 
            //so that they will not be added to Pizza total_qty in frontend dataTable
            if ($row['category_id'] == $category1_4PizzaID || $row['category_id'] == $category1_2PizzaID) {
                $row['total_qty'] = '';
            }
            //If the object is 'Pizza au Choix' we remove its total_price because it is 0.00
            if (strpos(strtolower($row['title']), strtolower(config::$article_aux_choix)) != false) {
                $row['total_price'] = '';
            }
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getSalesByAttributes($startDate, $endDate, $category_id, $extractData)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));
        $conn = Database::getConnection();

        $query = "
            SELECT
            SUM(suborder.quantity) AS total_qty,
            SUM(suborder.subTotal) AS total_price,
            object.category_id AS category_id,
            object.title AS title,
            category.category AS category,
            attribute_value.attributeValue AS attributeValue,
            CAST(suborder.updateDate AS DATE) AS updateDate,
            -- Cost per item (attribute or base)
            COALESCE(price.cost, object.baseCost) AS cost,
            -- Total cost
           SUM(suborder.subCost) AS total_cost,
            -- Total earning
            SUM(suborder.subTotal) - SUM(suborder.subCost) AS total_earning
        FROM suborder
        JOIN object ON suborder.object_id = object.id
        JOIN ordere ON suborder.ordere_id = ordere.id
        LEFT JOIN attribute_value ON suborder.attributeValue_id = attribute_value.id
        LEFT JOIN price ON price.object_id = object.id AND price.attributeValue_id = suborder.attributeValue_id
        JOIN category ON object.category_id = category.id
        WHERE ordere.payed = 1 
        AND ordere.company_id = :company_id
        AND DATE(suborder.updateDate) BETWEEN :startDate AND :endDate
        ";

        // Add category filter only if $category_id is not empty
        if ($category_id !== 0) {
            $query .= " AND object.category_id = :category_id";
        }

        $query .= " GROUP BY object.title, suborder.attributeValue_id ORDER BY category.display, object.title";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);

        if ($category_id !== 0) {
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        }

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

    static function getTotalVatByDate($startDate, $endDate, $extractData)
{
    $startDate = date("Y-m-d", strtotime($startDate));
    $endDate = date("Y-m-d", strtotime($endDate));

    $conn = Database::getConnection();

    $query = "SELECT COALESCE(SUM(" . Ordere::$table_name . "." . Ordere::$col_vatAmount . "), 0) AS totalVat
              FROM " . Ordere::$table_name .
             " WHERE DATE(" . Ordere::$table_name . "." . Ordere::$col_updateDate . ") BETWEEN :startDate AND :endDate
               AND " . Ordere::$table_name . "." . Ordere::$col_payed . " = 1
               AND " . Ordere::$table_name . "." . Ordere::$col_company_id . " = :company_id";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->bindValue(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);

    if (!$stmt->execute()) {
        echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
        addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
        exit;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalVat = isset($row['totalVat']) ? $row['totalVat'] : 0;

    if ($extractData) {
        echo json_encode(array("state" => "s", "vat" => $totalVat));
    }
    return $totalVat;
}
}
