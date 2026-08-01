<?php

/**
 * Description of Object
 *
 * @author Nminfo
 */
class Company
{

    //Object table name
    public static $table_name = "company";
    //Object table columns
    public static $col_id = "id";
    public static $col_companyName = "companyName";
    public static $col_companyDescription = "companyDescription";
    public static $col_address = "address";
    public static $col_phone = "phone";
    public static $col_email = "email";
    public static $col_gps = "gps";
    public static $col_companyCover = "companyCover";
    public static $col_logo = "logo";
    public static $col_carryCode = "carryCode";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";
    // Table properties
    private $id;
    private $companyName;
    private $companyDescription;
    private $address;
    private $phone;
    private $email;
    private $gps;
    private $companyCover;
    private $logo;
    private $carryCode;
    private $creationDate;
    private $updateDate;

    function getId()
    {
        return $this->id;
    }

    function getCompanyName()
    {
        return $this->companyName;
    }

    function getCompanyDescription()
    {
        return $this->companyDescription;
    }

    function getAddress()
    {
        return $this->address;
    }

    function getPhone()
    {
        return $this->phone;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getCarryCode()
    {
        return $this->carryCode;
    }

    function getGps()
    {
        return $this->gps;
    }

    function getCompanyCover()
    {
        return $this->companyCover;
    }

    function getLogo()
    {
        return $this->logo;
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

    function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    function setCompanyDescription($companyDescription)
    {
        $this->companyDescription = $companyDescription;
    }

    function setAddress($address)
    {
        $this->address = $address;
    }

    function setPhone($phone)
    {
        $this->phone = $phone;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setCarryCode($carryCode)
    {
        $this->carryCode = $carryCode;
    }

    function setGps($gps)
    {
        $this->gps = $gps;
    }

    function setCompanyCover($companyCover)
    {
        $this->companyCover = $companyCover;
    }

    function setLogo($logo)
    {
        $this->logo = $logo;
    }


    function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }



    //    public function getJSONEncode() {
    //        return json_encode([get_object_vars($this)]);
    //    }


}