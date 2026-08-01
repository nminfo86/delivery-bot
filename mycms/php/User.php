<?php

class User
{

    //User table name
    public static $table_name = "user";
    //User table columns
    public static $col_id = "id";
    public static $col_username = "username";
    public static $col_password = "password";
    public static $col_name = "name";
    public static $col_familyName = "familyName";
    public static $col_email = "email";
    public static $col_connected = "connected";
    public static $col_accessErrors = "accessErrors";
    public static $col_nextAccess = "nextAccess";
    public static $col_role_id = "role_id";
    public static $col_company_id = "company_id";
    public static $col_printer_id = "printer_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    // Table properties
    private $id;
    private $username;
    private $password;
    private $name;
    private $familyName;
    private $email;
    private $connected;
    private $accessErrors;
    private $role_id;
    private $company_id;
    private $printer_id;
    private $nextAccess;
    private $creationDate;
    private $updateDate;

    //    public function getJSONEncode() {
    //        return json_encode([get_object_vars($this)]);
    //    }
    function getId()
    {
        return $this->id;
    }

    function getUsername()
    {
        return $this->username;
    }

    function getPassword()
    {
        return $this->password;
    }

    function getName()
    {
        return $this->name;
    }

    function getFamilyName()
    {
        return $this->familyName;
    }

    function getEmail()
    {
        return $this->email;
    }
    function getConnected()
    {
        return $this->connected;
    }

    function getAccessErrors()
    {
        return $this->accessErrors;
    }

    function getNextAccess()
    {
        return $this->nextAccess;
    }
    function getRole_id()
    {
        return $this->role_id;
    }
    function getCompany_id()
    {
        return $this->company_id;
    }

    /**
     * Get the value of printer_id
     */
    public function getPrinter_id()
    {
        return $this->printer_id;
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

    function setUsername($username)
    {
        $this->username = $username;
    }

    function setPassword($password)
    {
        $this->password = $password;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setFamilyName($familyName)
    {
        $this->familyName = $familyName;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }
    function setConnected($connected)
    {
        $this->connected = $connected;
    }

    function setAccessErrors($accessErrors)
    {
        $this->accessErrors = $accessErrors;
    }

    function setNextAccess($nextAccess)
    {
        $this->nextAccess = $nextAccess;
    }

    function setRole_id($role_id)
    {
        $this->role_id = $role_id;
    }
    function setCompany_id($company_id)
    {
        $this->company_id = $company_id;
    }
    /**
     * Set the value of printer_id
     *
     * @return  self
     */
    public function setPrinter_id($printer_id)
    {
        $this->printer_id = $printer_id;

        return $this;
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