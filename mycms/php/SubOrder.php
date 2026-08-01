<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubOrder
 *
 * @author dell
 */
class SubOrder
{
    //put your code here
    public static $table_name = "suborder";
    //Object table columns
    public static $col_id = "id";
    public static $col_ordere_id = "ordere_id";
    public static $col_object_id = "object_id";
    public static $col_attributeValue_id = "attributeValue_id";
    public static $col_quantity = "quantity";
    public static $col_uPrice = "uPrice";
    public static $col_uCost = "uCost";
    public static $col_subTotal = "subTotal";
    public static $col_subCost = "subCost";
    public static $col_subCode = "subCode";
    public static $col_subProgression = "subProgression";
    public static $col_subComment = "subComment";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    //Table proprietes
    private $id;
    private $ordere_id;
    private $object_id;
    private $attributeValue_id;
    private $quantity;
    private $uPrice;
    private $uCost;
    private $subTotal;
    private $subCost;
    private $subCode;
    private $subProgression;
    private $subComment;
    private $creationDate;
    private $updateDate;

    function getId()
    {
        return $this->id;
    }

    function getOrdere_id()
    {
        return $this->ordere_id;
    }

    function getObject_id()
    {
        return $this->object_id;
    }

    function getAttributeValue_id()
    {
        return $this->attributeValue_id;
    }

    function getQuantity()
    {
        return $this->quantity;
    }

    function getUPrice()
    {
        return $this->uPrice;
    }

    function getUCost()
    {
        return $this->uCost;
    }

    function getSubTotal()
    {
        return $this->subTotal;
    }

    function getSubCost()
    {
        return $this->subCost;
    }

    function getSubCode()
    {
        return $this->subCode;
    }
    function getSubProgression()
    {
        return $this->subProgression;
    }
    function getSubComment()
    {
        return $this->subComment;
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

    function setOrdere_id($ordere_id)
    {
        $this->ordere_id = $ordere_id;
    }

    function setObject_id($object_id)
    {
        $this->object_id = $object_id;
    }

    function setAttributeValue_id($attributeValue_id)
    {
        $this->attributeValue_id = $attributeValue_id;
    }

    function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    function setUPrice($uPrice)
    {
        $this->uPrice = $uPrice;
    }

    function setUCost($uCost)
    {
        $this->uCost = $uCost;
    }

    function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
    }

    function setSubCost($subCost)
    {
        $this->subCost = $subCost;
    }
    function setSubCode($subCode)
    {
        $this->subCode = $subCode;
    }
    function setSubProgression($subProgression)
    {
        $this->subProgression = $subProgression;
    }
    function setSubComment($subComment)
    {
        $this->subComment = $subComment;
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