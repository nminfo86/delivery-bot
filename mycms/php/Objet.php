<?php

/**
 * Description of Object
 *
 * @author Nminfo
 */
class Objet
{

    //Object table name
    public static $table_name = "object";
    //Object table columns
    public static $col_id = "id";
    public static $col_title = "title";
    public static $col_description = "description";
    public static $col_basePrice = "basePrice";
    public static $col_baseCost = "baseCost";
    public static $col_observation = "observation";
    public static $col_category_id = "category_id";
    public static $col_objAvailable = "objAvailable";
    public static $col_company_id = "company_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";
    // Table properties
    private $id;
    private $title;
    private $description;
    private $basePrice;
    private $baseCost;
    private $observation;
    private $category_id;
    private $objAvailable;
    private $company_id;
    private $creationDate;
    private $updateDate;

    //    public function getJSONEncode() {
    //        return json_encode([get_object_vars($this)]);
    //    }

    function getId()
    {
        return $this->id;
    }

    function getTitle()
    {
        return $this->title;
    }

    function getDescription()
    {
        return $this->description;
    }

    function getBasePrice()
    {
        return $this->basePrice;
    }

    function getBaseCost()
    {
        return $this->baseCost;
    }

    function getObservation()
    {
        return $this->observation;
    }

    function getCategory_id()
    {
        return $this->category_id;
    }
    function getObjAvailable()
    {
        return $this->objAvailable;
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

    function setTitle($title)
    {
        $this->title = $title;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function setBaseCost($baseCost)
    {
        $this->baseCost = $baseCost;
    }


    function setBasePrice($price)
    {
        $this->basePrice = $price;
    }

    function setObservation($observation)
    {
        $this->observation = $observation;
    }

    function setCategory_id($category_id)
    {
        $this->category_id = $category_id;
    }
    function setObjAvailable($objAvailable)
    {
        $this->objAvailable = $objAvailable;
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