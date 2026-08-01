<?php

/**
 * Description of Charge
 *
 * @author Nminfo
 */
class Type_Charge
{

    //Object table name
    public static $table_name = "type_charge";
    //Object table columns
    public static $col_id = "id";
    public static $col_typeCharge = "typeCharge";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";
    public static $col_company_id = "company_id"; // <-- Add this line

    // Table properties
    private $id;
    private $typeCharge;
    private $creationDate;
    private $updateDate;
    private $company_id; // <-- Add this line



    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of typeCharge
     */
    public function getTypeCharge()
    {
        return $this->typeCharge;
    }

    /**
     * Set the value of typeCharge
     *
     * @return  self
     */
    public function setTypeCharge($typeCharge)
    {
        $this->typeCharge = $typeCharge;

        return $this;
    }

    /**
     * Get the value of creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set the value of creationDate
     *
     * @return  self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get the value of updateDate
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set the value of updateDate
     *
     * @return  self
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get the value of company_id
     */
    public function getCompany_id()
    {
        return $this->company_id;
    }

    /**
     * Set the value of company_id
     *
     * @return  self
     */
    public function setCompany_id($company_id)
    {
        $this->company_id = $company_id;
        return $this;
    }
}