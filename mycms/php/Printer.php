<?php

/**
 * Description of Printer
 *
 * @author Nminfo
 */
class Printer
{

    //Object table name
    public static $table_name = "printer";
    //Object table columns
    public static $col_id = "id";
    public static $col_printerName = "printerName";
    public static $col_printerIP = "printerIP";
    public static $col_printerPort = "printerPort";
    public static $col_printerProtocole = "printerProtocole";
    public static $col_labelSize = "labelSize";
    public static $col_company_id = "company_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    // Table properties
    private $id;
    private $printerName;
    private $printerIP;
    private $printerPort;
    private $printerProtocole;
    private $labelSize;
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
     * Get the value of printerName
     */
    public function getPrinterName()
    {
        return $this->printerName;
    }

    /**
     * Get the value of printerIP
     */
    public function getPrinterIP()
    {
        return $this->printerIP;
    }

    /**
     * Get the value of printerPort
     */
    public function getPrinterPort()
    {
        return $this->printerPort;
    }

    /**
     * Get the value of printerProtocole
     */
    public function getPrinterProtocole()
    {
        return $this->printerProtocole;
    }

    /**
     * Get the value of labelSize
     */
    public function getLabelSize()
    {
        return $this->labelSize;
    }
    
     /**
     * Get the value of company_id
     */
    public function getCompanyId()
    {
        return $this->company_id;
    }


    /**
     * Get the value of creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get the value of updateDate
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
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
     * Set the value of printerName
     *
     * @return  self
     */
    public function setPrinterName($printerName)
    {
        $this->printerName = $printerName;

        return $this;
    }

    /**
     * Set the value of printerIP
     *
     * @return  self
     */
    public function setPrinterIP($printerIP)
    {
        $this->printerIP = $printerIP;

        return $this;
    }

    /**
     * Set the value of printerPort
     *
     * @return  self
     */
    public function setPrinterPort($printerPort)
    {
        $this->printerPort = $printerPort;

        return $this;
    }

    /**
     * Set the value of printerProtocole
     *
     * @return  self
     */
    public function setPrinterProtocole($printerProtocole)
    {
        $this->printerProtocole = $printerProtocole;

        return $this;
    }

    /**
     * Set the value of labelSize
     *
     * @return  self
     */
    public function setLabelSize($labelSize)
    {
        $this->labelSize = $labelSize;

        return $this;
    }

   
     /**
     * Set the value of company_id
     *
     * @return  self
     */
    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;

        return $this;
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