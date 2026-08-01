<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of category
 *
 * @author dell
 */
class Category
{
    //put your code here
    //
    //Category table name
    public static $table_name = "category";
    //Object table columns
    public static $col_id = "id";
    public static $col_category = "category";
    public static $col_prepare = "prepare";
    public static $col_display = "display";
    public static $col_available = "available";
    public static $col_supplement = "supplement";
    public static $col_acceptSupplement = "acceptSupplement";
    public static $col_color = "color";
    public static $col_categoryCover = "categoryCover";
    public static $col_minPrice = "minPrice";
    public static $col_company_id = "company_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";
    // Table properties
    private $id;
    private $category;
    private $prepare;
    private $display;
    private $available;
    private $supplement;
    private $acceptSupplement;
    private $color;
    private $categoryCover;
    private $minPrice;
    private $company_id;
    private $creationDate;
    private $updateDate;

    function getId()
    {
        return $this->id;
    }

    function getCategory()
    {
        return $this->category;
    }
    function getPrepare()
    {
        return $this->prepare;
    }
    function getDisplay()
    {
        return $this->display;
    }
    function getAvailable()
    {
        return $this->available;
    }
    function getSupplement()
    {
        return $this->supplement;
    }
    function getAcceptSupplement()
    {
        return $this->acceptSupplement;
    }
    function getColor()
    {
        return $this->color;
    }

    function getCategoryCover()
    {
        return $this->categoryCover;
    }

    function getMinPrice()
    {
        return $this->minPrice;
    }

    function getCompany_id()
    {
        return $this->company_id;
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

    function setCategory($category)
    {
        $this->category = $category;
    }
    function setPrepare($prepare)
    {
        $this->prepare = $prepare;
    }
    function setDisplay($display)
    {
        $this->display = $display;
    }
    function setAvailable($available)
    {
        $this->available = $available;
    }
    function setSupplement($supplement)
    {
        $this->supplement = $supplement;
    }
    function setAcceptSupplement($acceptSupplement)
    {
        $this->acceptSupplement = $acceptSupplement;
    }
    function setColor($color)
    {
        $this->color = $color;
    }

    function setCategoryCover($categoryCover)
    {
        $this->categoryCover = $categoryCover;
    }

    function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    function setCompany_id($company_id)
    {
        $this->company_id = $company_id;
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