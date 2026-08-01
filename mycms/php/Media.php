<?php

/**
 * Description of Media
 *
 * @author Nminfo
 */
class Media
{

    //Object table name
    public static $table_name = "media";
    //Object table columns
    public static $col_id = "id";
    public static $col_media = "media";
    public static $col_mediaDescription = "mediaDescription";
    public static $col_mediaType = "mediaType";
    public static $col_mediaPosition = "mediaPosition";
    public static $col_object_id = "object_id";
    public static $col_user_id = "user_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";
    // Table properties
    private $id;
    private $media;
    private $mediaDescription;
    private $mediaType;
    private $mediaPosition;
    private $object_id;
    private $user_id;
    private $creationDate;
    private $updateDate;

    //    public function getJSONEncode() {
    //        return json_encode([get_object_vars($this)]);
    //    }

    function getId()
    {
        return $this->id;
    }

    function getMedia()
    {
        return $this->media;
    }

    function getMediaDescription()
    {
        return $this->mediaDescription;
    }

    function getMediaType()
    {
        return $this->mediaType;
    }

    function getMediaPosition()
    {
        return $this->mediaPosition;
    }

    function getObject_id()
    {
        return $this->object_id;
    }

    function getUser_id()
    {
        return $this->user_id;
    }

    function getCreationDate()
    {
        return $this->creationDate;
    }

    function getUpdateDate()
    {
        return $this->updateDate;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setMedia($media)
    {
        $this->media = $media;
    }

    function setMediaDescription($mediaDescription)
    {
        $this->mediaDescription = $mediaDescription;
    }

    function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
    }

    function setMediaPosition($mediaPosition)
    {
        $this->mediaPosition = $mediaPosition;
    }

    function setObject_id($object_id)
    {
        $this->object_id = $object_id;
    }

    function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }

    function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }
}