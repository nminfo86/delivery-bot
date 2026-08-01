<?php

class CmsAttribute
{
    public static $table_name = "attribute";
    public static $col_id = "id";
    public static $col_attribute = "attribute";

    private $id;
    private $attribute;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }
}