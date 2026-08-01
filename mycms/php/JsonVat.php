<?php

require_once "Database.php";
require_once "Vat.php";
require_once "functions.php";
require_once "Ordere.php";

if (isset($_POST['function'])) {

    if (($_POST['function'] === "getAllVats")) {
        JsonVat::getAllVats(true);
    }
    if (($_POST['function'] === "createVat")) {
        confirmLoggedIn();
        JsonVat::createVat(updateVatFromPostVariables());
    }
    if (($_POST['function'] === "updateVat")) {
        confirmLoggedIn();
        JsonVat::updateVat(updateVatFromPostVariables());
    }

    if (($_POST['function'] === "deleteVat") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonVat::deleteVat($_POST['id']);
    }

    if (($_POST['function'] === "getVatById") && (isset($_POST['id']))) {
        JsonVat::getVatById($_POST['id'], true);
    }
}

function updateVatFromPostVariables()
{
    $vat = new Vat();

    if (isset($_POST[Vat::$col_id])) {
        $existingVat = JsonVat::getVatById($_POST[Vat::$col_id], False);
        if ($existingVat !== null) {
            $vat = $existingVat;
        }
        if ($_POST[Vat::$col_id] == 0) {
            $vat->setId(0);
        }
    }

    if (isset($_POST[Vat::$col_vat])) {
        $vat->setVat(trim($_POST[Vat::$col_vat]));
    }
    if (isset($_POST[Vat::$col_rate])) {
        $vat->setRate($_POST[Vat::$col_rate]);
    }
    return $vat;
}

class JsonVat
{
    static function createVat(Vat $vat)
    {
        if (JsonVat::existVat($vat)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Vat::$table_name .
            "(" .
            Vat::$col_vat .
            ", " .
            Vat::$col_rate .
            ")" .
            " VALUES (:vat, :rate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':vat', $vat->getVat(), PDO::PARAM_STR);
        $stmt->bindValue(':rate', $vat->getRate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        $query = "SELECT " . Vat::$col_id . " FROM " . Vat::$table_name . " ORDER BY " . Vat::$col_id . " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        JsonVat::getVatById($row["id"], true);
    }

    static function getAllVats($extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Vat::$table_name . " ORDER BY " . Vat::$col_vat;

        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            if ($extractData) {
                echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            }
            return NULL;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function updateVat(Vat $vat)
    {
        if (JsonVat::existVat($vat)) {
            echo json_encode(array("state" => "f", "message" => Config::$data_exist));
            exit;
        }

         // Add this check to prevent changing the rate if used in an order:
        $existingVat = self::getVatById($vat->getId(), false);
        if ($existingVat !== null && $existingVat->getRate() != $vat->getRate()) {
            if (self::isVatUsedInOrdere($vat->getId())) {
                echo json_encode(array("state" => "f", "message" => "Cannot update VAT rate because it is already used in orders."));
                exit;
            }
        }

        $conn = Database::getConnection();
        $query = "UPDATE " . Vat::$table_name .
            " SET " .
            Vat::$col_vat . "= :vat, " .
            Vat::$col_rate . "= :rate" .
            " WHERE " .
            Vat::$col_id . "= :id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':vat', $vat->getVat(), PDO::PARAM_STR);
        $stmt->bindValue(':rate', $vat->getRate(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $vat->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        JsonVat::getVatById($vat->getId(), TRUE);
    }

    static function deleteVat($id)
    {

       //check if the VAT is used in any order before deleting
        if (self::isVatUsedInOrdere($id)) {
            echo json_encode(array("state" => "f", "message" => "Cannot delete VAT because it is already used in orders."));
            exit;
        }


        $conn = Database::getConnection();
        $query = "DELETE FROM " . Vat::$table_name . " WHERE " . Vat::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        echo json_encode(array("state" => "s"));
    }

    static function getVatById($id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Vat::$table_name . " WHERE id = :id LIMIT 1";

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
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $output[] = $row;
        if ($extractData) {
            echo json_encode($output);
        }

        $vat = new Vat();
        $vat->setId($row[Vat::$col_id]);
        $vat->setVat($row[Vat::$col_vat]);
        $vat->setRate($row[Vat::$col_rate]);
        return $vat;
    }

    static function existVat(Vat $vat)
    {
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . Vat::$table_name .
            " WHERE " . Vat::$col_vat . " = :vat" .
            " AND " . Vat::$col_id . "<> :id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':vat', $vat->getVat(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $vat->getId(), PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        return ($stmt->rowCount() > 0);
    }

     static function isVatUsedInOrdere($vatId)
    {
        $conn = Database::getConnection();
        $query = "SELECT " . Ordere::$col_id . " FROM " . Ordere::$table_name . 
                 " WHERE " . Ordere::$col_vat_id . " = :vat_id LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':vat_id', $vatId, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        
        return ($stmt->rowCount() > 0);
    }
}