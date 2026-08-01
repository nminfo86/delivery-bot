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
require_once "User_Category.php";
require_once "Category.php";
require_once "User.php";
require_once "functions.php";
if (isset($_POST['function'])) {

    if (($_POST['function'] === "getAllCategoriesOfUser") && (isset($_POST['user_id']))) {
        JsonUser_Category::getAllCategoriesOfUser($_POST['user_id'], true);
    }
    if (($_POST['function'] === "delete") && (isset($_POST['user_id'])) && (isset($_POST['category_id']))) {
        JsonUser_Category::delete($_POST['user_id'], $_POST['category_id'], true);
    }
    if (($_POST['function'] === "create") && (isset($_POST['user_id'])) && (isset($_POST['category_id']))) {
        JsonUser_Category::create($_POST['user_id'], $_POST['category_id'], true);
    }
}

class JsonUser_Category
{

    static function getAllCategoriesOfUser($user_id, $extractData)
    {

        $conn = Database::getConnection();
        $query = "SELECT * " .
            " FROM " .
            User_Category::$table_name .
            " WHERE " .
            User_Category::$col_user_id .
            " = :user_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

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

    static function delete($user_id, $category_id, $extractData)
    {
        $conn = Database::getConnection();

        $query = "DELETE FROM " . User_Category::$table_name .
            " WHERE " .
            User_Category::$col_user_id . "= :user_id" .
            " AND " .
            User_Category::$col_category_id . "=:category_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    static function create($user_id, $category_id, $extractData)
    {

        $conn = Database::getConnection();

        $query = "INSERT INTO " . User_Category::$table_name .
            "(" .
            User_Category::$col_user_id .
            ", " .
            User_Category::$col_category_id .
            ")" .
            " VALUES (:user_id, :category_id)";


        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }


    
}