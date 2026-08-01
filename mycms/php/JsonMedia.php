<?php

/**
 * Description of JsonMedia
 *
 * @author Nminfo
 */
require_once "Database.php";
require_once "Media.php";
require_once "functions.php";
require_once "Config.php";

if (isset($_POST['function'])) {

    if ($_POST['function'] === "uploadMedia") {

        confirmLoggedIn();
        JsonMedia::upload();
    }

    if (($_POST['function'] === "deleteMedia") && (isset($_POST['id']))) {
        confirmLoggedIn();
        JsonMedia::delete($_POST['id'], true, true);
    }

    if (($_POST['function'] === "setMediaCover") && (isset($_POST['id'])) && (isset($_POST['object_id']))) {
        JsonMedia::setMediaCover($_POST['id'], $_POST['object_id'], true);
    }
    if (($_POST['function'] === "getMediaCoverOfObject") && (isset($_POST['object_id']))) {
        JsonMedia::getMediaCoverOfObject($_POST['object_id'], true);
    }
    if (($_POST['function'] === "getAllMediasOfObject") && (isset($_POST['object_id']))) {
        JsonMedia::getAllMediasOfObject($_POST['object_id'], true);
    }
}

class JsonMedia
{

   static function upload()
    {
        if (isset($_FILES['object-media'])) {
            $tmp_file = $_FILES['object-media']['tmp_name'];
            $fileExtension = pathinfo($_FILES['object-media']['name'], PATHINFO_EXTENSION);

            // Build dir: web-relative + filesystem dir
            $mediaDirRel = Config::$object_images_path . '/' . $_POST['object_id'] . "/";
            $mediaDirFs  = media_fs_path($mediaDirRel);
            if (!file_exists($mediaDirFs)) {
                mkdir($mediaDirFs, 0777, true);
            }
            $fileName = date('Y-m-d-H-i-s') . "." . strtolower($fileExtension);
            $mediaPathFs = $mediaDirFs . $fileName;

            if (!move_uploaded_file($tmp_file, $mediaPathFs)) {
                echo json_encode(array("state" => "f", "message" => Config::$fail_upload_file . " " . __FUNCTION__));
                exit;
            }

            $mediaRel = $mediaDirRel . $fileName; // what we store in DB

            $media = new Media();
            $media->setMedia($mediaRel);
            $media->setmediaDescription($_POST[Media::$col_mediaDescription]);
            $media->setMediaType($_POST[Media::$col_mediaType]);
            $media->setMediaPosition(sizeof(JsonMedia::getAllMediasOfObject($_POST['object_id'], false)) > 0 ? Config::$mediaPositionGallery : Config::$mediaPositionCover);
            $media->setObject_id($_POST['object_id']);
            $media->setUser_id($_SESSION["user_id"]);
            JsonMedia::create($media, True);
        } else {
            // Video (YouTube)
            $videoLink = Config::$youtube_embed_suffix . $_POST[Media::$col_media];
            $media = new Media();
            $media->setMedia($videoLink);
            $media->setMediaDescription($_POST[Media::$col_mediaDescription]);
            $media->setMediaType($_POST[Media::$col_mediaType]);
            $media->setMediaPosition(sizeof(JsonMedia::getAllMediasOfObject($_POST['object_id'], false)) > 0 ? Config::$mediaPositionGallery : Config::$mediaPositionCover);
            $media->setObject_id($_POST['object_id']);
            $media->setUser_id($_SESSION["user_id"]);
            JsonMedia::create($media, true);
        }
    }

    // Create un media
    static function create(Media $media, $extractData)
    {

        // if (JsonMedia::existMedia($media)) {
        //     echo json_encode(array("state" => "f", "message" => Config::$data_exist));
        //     exit;
        // }
        $conn = Database::getConnection();

        $query = "INSERT INTO " . Media::$table_name .
            "(" .
            Media::$col_media .
            ", " .
            Media::$col_mediaDescription .
            ", " .
            Media::$col_mediaType .
            ", " .
            Media::$col_mediaPosition .
            ", " .
            Media::$col_object_id .
            ", " .
            Media::$col_user_id .
            ", " .
            Media::$col_creationDate .
            ")" .
            " VALUES (:media, :mediaDescription, :mediaType, :mediaPosition, :object_id, :user_id, :creationDate)";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':media', $media->getMedia(), PDO::PARAM_STR);
        $stmt->bindValue(':mediaDescription', $media->getMediaDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':mediaType', $media->getMediaType(), PDO::PARAM_STR);
        $stmt->bindValue(':mediaPosition', $media->getMediaPosition(), PDO::PARAM_STR);
        $stmt->bindValue(':object_id', $media->getObject_id(), PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $media->getUser_id(), PDO::PARAM_INT);
        $stmt->bindValue(':creationDate', getCurrentDate(), PDO::PARAM_STR);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $query = "SELECT " .
            Media::$col_id .
            " FROM " .
            Media::$table_name .
            " ORDER BY " .
            Media::$col_id .
            " DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row["id"];
        if ($extractData) {
            JsonMedia::getMediaById($id, true);
        } else {
            return $id;
        }
    }

    static function setMediaCover($media_id, $object_id, $extractDaya)
    {

        $conn = Database::getConnection();

        $query = "UPDATE " . Media::$table_name .
            " SET " .
            Media::$col_mediaPosition . "= '" . Config::$mediaPositionCover . "'" .
            " WHERE " .
            Media::$col_id . "= :media_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':media_id', $media_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        //SET OTHER MEDIAS TO GALLERY
        $query = "UPDATE " . Media::$table_name .
            " SET " .
            Media::$col_mediaPosition . "= '" . Config::$mediaPositionGallery . "'" .
            " WHERE " .
            Media::$col_object_id . " = :object_id" .
            " AND " . Media::$col_id . " <> :media_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_INT);
        $stmt->bindValue(':media_id', $media_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($extractDaya) {
            echo json_encode(array("state" => "s"));
        }
    }

    // Get Media by id
    static function getMediaById($id, $extractData)
    {

        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Media::$table_name .
            " WHERE id = :id LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            echo json_encode(array("state" => "f", "message" => Config::$no_data_found));
            exit;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }

    static function getAllMediasOfObject($object_id, $extractData)
    {

        $conn = Database::getConnection();

        $query = "SELECT * FROM " . Media::$table_name .
            " WHERE " .
            Media::$col_object_id . "= :object_id" .
            " ORDER BY " . Media::$col_mediaPosition;

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
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

    // Check media existance in DB
    static function existMedia($media)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Media::$table_name .
            " WHERE " .
            Media::$col_media . " = :media" .
            " AND " . Media::$col_object_id . " = :object_id";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':media', $media->getMedia(), PDO::PARAM_STR);
        $stmt->bindValue(':object_id', $media->getObject_id(), PDO::PARAM_INT);

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

    static function delete($id, $setMediaAsCoverAfterDelete, $extractData)
    {

        //GET MEDIA BY ID BEFORE DELETED FROM DB
        $media = JsonMedia::getMediaById($id, false);
        //
        $conn = Database::getConnection();

        $query = "DELETE FROM " . Media::$table_name .
            " WHERE " .
            Media::$col_id . "= :id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }

        if ($setMediaAsCoverAfterDelete) {
            if (!JsonMedia::lastMedia($media[0][Media::$col_object_id])) {
                //IF MEDIA IS COVER, WE GET ONE OF THE MEDIAS OF OBJECT AND MAKE IT A COVER
                if ($media[0][Media::$col_mediaPosition] == Config::$mediaPositionCover) {

                    $mediaCover = JsonMedia::getAllMediasOfObject($media[0][Media::$col_object_id], false);

                    JsonMedia::setMediaCover($mediaCover[0][Media::$col_id], $mediaCover[0][Media::$col_object_id], false);
                }
            }
        }
        //IF MEDIA IS AN IMAGE ( A FILE ) WE DELETE IT FROM THE SERVER
        if (($media[0][Media::$col_mediaType] == Config::$mediaType_image)
            || ($media[0][Media::$col_mediaType] == Config::$mediaType_video)
        ) {
            JsonMedia::deleteMediaFromServer($media);
        }
        //
        if ($extractData) {
            echo json_encode(array("state" => "s"));
        }
    }

    static function lastMedia($object_id)
    {
        $conn = Database::getConnection();

        $query = "SELECT * FROM " . Media::$table_name .
            " WHERE " .
            Media::$col_object_id . "= :object_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            return true;
        } else {
            return false;
        }
    }
    static function lastMediaFile($object_id)
    {

        $conn = Database::getConnection();

        $query = "SELECT * FROM " . Media::$table_name .
            " WHERE " .
            Media::$col_object_id . "= :object_id" .
            " AND ( " .
            Media::$col_mediaType . " = '" . Config::$mediaType_image . "'" .
            " OR " .
            Media::$col_mediaType . " = '" . Config::$mediaType_video . "'" .
            " )";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':object_id', $object_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo json_encode(array("state" => "f", "message" => Config::$user_error . " " . __FUNCTION__));
            addTrace(getMsgPdoStmt($stmt) . " " . __FUNCTION__);
            exit;
        }
        if ($stmt->rowCount() == 0) {
            return true;
        } else {
            return false;
        }
    }

    static function deleteMediaFromServer($media)
    {

        if (!unlink(media_url_to_fs($media[0][Media::$col_media]))) {
            echo json_encode(array("state" => "f", "message" => Config::$fail_delete_file . " " . __FUNCTION__));
            exit;
        }
        //DELETE DIRECTORY OF OBJECT IF IT IS THE LAST MEDIA IN IT (EMPTY)
        if (JsonMedia::lastMediaFile($media[0][Media::$col_object_id])) {
            $mediaPathFs = media_url_to_fs($media[0][Media::$col_media]);
            $mediaFolder = dirname($mediaPathFs) . DIRECTORY_SEPARATOR;
            if (!rmdir($mediaFolder)) {
                echo json_encode(array("state" => "f", "message" => Config::$fail_remove_media_dir . " " . __FUNCTION__));
                exit;
            }
        }
    }

    static function deleteAllMediaOfObject($object_id)
    {
        $medias = JsonMedia::getAllMediasOfObject($object_id, false);

        foreach ($medias as $media) {
            JsonMedia::delete($media["id"], false, false);
        }
    }

    static function getMediaCoverOfObject($object_id, $extractData)
    {
        $conn = Database::getConnection();
        $query = "SELECT *" .
            " FROM " .
            Media::$table_name .
            " WHERE " .
            Media::$col_object_id . "= :object_id" .
            " AND " .
            Media::$col_mediaPosition . "= '" . Config::$mediaPositionCover . "'" .
            " LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':object_id', $object_id, PDO::PARAM_INT);

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
            
            return null;
        }
        $output[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($extractData) {
            echo json_encode($output);
        }
        return $output;
    }
}
