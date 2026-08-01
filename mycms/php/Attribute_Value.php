<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Attribute_Value
 *
 * @author dell
 */
class Attribute_Value
{

    public static $table_name = "attribute_value";

    public static $col_id = "id";
    public static $col_attributeValue = "attributeValue";
    public static $col_attribute_id = "attribute_id";

    // Table properties
    private $id;
    private $attributeValue;
    private $attribute_id;

    // Getter and Setter for ID
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    // Getter and Setter for Attribute Value
    public function setAttributeValue($attributeValue)
    {
        $this->attributeValue = $attributeValue;
    }

    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    // Getter and Setter for Attribute ID
    public function setAttribute_id($attribute_id)
    {
        $this->attribute_id = $attribute_id;
    }

    public function getAttribute_id()
    {
        return $this->attribute_id;
    }
}