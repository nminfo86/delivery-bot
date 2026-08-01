<?php

/**
 * Description of Charge
 *
 * @author Nminfo
 */
class Charge
{

    //Object table name
    public static $table_name = "charge";
    //Object table columns
    public static $col_id = "id";
    public static $col_amount = "amount";
    public static $col_observation = "observation";
    public static $col_dateTime = "dateTime";
    public static $col_decaise = "decaise";
    public static $col_typeCharge_id = "typeCharge_id";
    public static $col_company_id = "company_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    // Table properties
    private $id;
    private $amount;
    private $observation;
    private $dateTime;
    private $decaise;
    private $typeCharge_id;
    private $company_id;
    private $creationDate;
    private $updateDate;



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
     * Get the value of amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }


    /**
     * Get the value of observation
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     *
     * @return  self
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get the value of dateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set the value of dateTime
     *
     * @return  self
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get the value of decaise
     */
    public function getDecaise()
    {
        return $this->decaise;
    }

    /**
     * Set the value of decaise
     *
     * @return  self
     */
    public function setDecaise($decaise)
    {
        $this->decaise = $decaise;

        return $this;
    }

    /**
     * Get the value of typeCharge_id
     */
    public function getTypeCharge_id()
    {
        return $this->typeCharge_id;
    }

    /**
     * Set the value of typeCharge_id
     *
     * @return  self
     */
    public function setTypeCharge_id($typeCharge_id)
    {
        $this->typeCharge_id = $typeCharge_id;

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
}