<?php
require_once "functions.php";
//require_once $_SERVER['DOCUMENT_ROOT']."/Connection.php";
require_once "Connection.php";
class Database
{

        public static $conn = null;
        // get the database connection
        public static function getConnection()
        {
                try {
                        Database::$conn = new PDO("mysql:host=" . Connection::$host . ";dbname=" . Connection::$db_name . ";charset=utf8;", Connection::$username, Connection::$password);
                } catch (PDOException $exception) {
                        echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
                        addTrace($exception->getMessage());
                        exit;
                }
                return Database::$conn;
        }
}