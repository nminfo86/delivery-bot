<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 *
 * @author dell
 */
class Ordere
{
    //Object table name
    public static $table_name = "ordere";
    //Object table columns
    public static $col_id = "id";
    public static $col_place = "place";
    public static $col_code = "code";
    public static $col_valid = "valid";
    public static $col_payed = "payed";
    public static $col_customerLeft = "customerLeft";
    public static $col_table_id = "table_id";
    public static $col_vat_id = "vat_id"; 
    public static $col_discount_id = "discount_id";
    public static $col_discountAmount = "discountAmount";
    public static $col_company_id = "company_id";
    public static $col_cookieID = "cookieID";
    public static $col_progression = "progression";
    public static $col_comment = "comment";
    public static $col_vatAmount = "vatAmount";
    public static $col_orderePrice = "orderePrice";
    public static $col_totalTtc = "totalTtc";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    private $id;
    private $place;
    private $code;
    private $valid;
    private $payed;
    private $customerLeft;
    private $table_id;
    private $vat_id;
    private $discount_id;
    private $company_id;
    private $cookieID;
    private $progression;
    private $comment;
    private $vatAmount;
    private $discountAmount;
    private $orderePrice;
    private $totalTtc;
    private $creationDate;
    private $updateDate;

    function getId()
    {
        return $this->id;
    }

    function getTable_id()
    {
        return $this->table_id;
    }

    function getVat_id()
    {
        return $this->vat_id;
    }

    function getDiscount_id()
    {
        return $this->discount_id;
    }
 

    function getCompany_id()
    {
        return $this->company_id;
    }

    function getPlace()
    {
        return $this->place;
    }

    function getCode()
    {
        return $this->code;
    }

    function getValid()
    {
        return $this->valid;
    }
    function getPayed()
    {
        return $this->payed;
    }
    function getCustomerLeft()
    {
        return $this->customerLeft;
    }
    function getCookieID()
    {
        return $this->cookieID;
    }

    function getProgression()
    {
        return $this->progression;
    }

    function getComment()
    {
        return $this->comment;
    }

    function getVatAmount()
    {
        return $this->vatAmount;
    }

    function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    function getOrderePrice()
    {
        return $this->orderePrice;
    }
    function getTotalTtc()
    {
        return $this->totalTtc;
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

    function setTable_id($table_id)
    {
        $this->table_id = $table_id;
    }

  
    function setVat_id($vat_id)
    {
        $this->vat_id = $vat_id;
    }

    function setDiscount_id($discount_id)
    {
        $this->discount_id = $discount_id;
    }

   

    function setCompany_id($company_id)
    {
        $this->company_id = $company_id;
    }

    function setPlace($place)
    {
        $this->place = $place;
    }

    function setCode($code)
    {
        $this->code = $code;
    }

    function setValid($valid)
    {
        $this->valid = $valid;
    }
    function setPayed($payed)
    {
        $this->payed = $payed;
    }
    function setCustomerLeft($customerLeft)
    {
        $this->customerLeft = $customerLeft;
    }
    function setCookieID($cookieID)
    {
        $this->cookieID = $cookieID;
    }

    function setProgression($progression)
    {
        $this->progression = $progression;
    }

    function setComment($comment)
    {
        $this->comment = $comment;
    }

    function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;
    }

     function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
    }

    function setOrderePrice($orderePrice)
    {
        $this->orderePrice = $orderePrice;
    }
    function setTotalTtc($totalTtc)
    {
        $this->totalTtc = $totalTtc;
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