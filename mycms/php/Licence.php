<?php

/**
 * Description of Object
 *
 * @author Nminfo
 */
class Licence
{

    //Object table name
    public static $table_name = "licence";
    //Object table columns
    public static $col_id = "id";
    public static $col_licenceName = "licenceName";
    public static $col_licence = "licence";
    public static $col_checked = "checked";
    public static $col_adminUsers = "adminUsers";
    public static $col_chefUsers = "chefUsers";
    public static $col_waiterUsers = "waiterUsers";
    public static $col_checkoutUsers = "checkoutUsers";
    public static $col_orderCapability = "orderCapability";
    public static $col_printChef = "printChef";
    public static $col_printClient = "printClient";
    public static $col_printArabicRecipe = "printArabicRecipe";

    public static $col_cmsCurrency = "cmsCurrency";
    public static $col_cmsLanguage = "cmsLanguage"; // Added line
    public static $col_backupBasePath = "backupBasePath";
    public static $col_company_id = "company_id";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    // Table properties
    private $id;
    private $licenceName;
    private $licence;
    private $checked;
    private $adminUsers;
    private $chefUsers;
    private $waiterUsers;
    private $checkoutUsers;
    private $orderCapability;
    private $printChef;
    private $printClient;
    private $printArabicRecipe;
    private $cmsCurrency;
    private $cmsLanguage; // Added line
    private $backupBasePath;
    private $company_id;
    private $creationDate;
    private $updateDate;

    function getId()
    {
        return $this->id;
    }

    function getLicenceName()
    {
        return $this->licenceName;
    }

    function getLicence()
    {
        return $this->licence;
    }

    /**
     * Get the value of checked
     */
    public function getChecked()
    {
        return $this->checked;
    }

    function getAdminUsers()
    {
        return $this->adminUsers;
    }

    function getChefUsers()
    {
        return $this->chefUsers;
    }

    function getWaiterUsers()
    {
        return $this->waiterUsers;
    }

    function getCheckoutUsers()
    {
        return $this->checkoutUsers;
    }
    function getOrderCapability()
    {
        return $this->orderCapability;
    }
    function getPrintChef()
    {
        return $this->printChef;
    }
    function getPrintClient()
    {
        return $this->printClient;
    }
    function getPrintArabicRecipe()
    {
        return $this->printArabicRecipe;
    }

    function getCmsCurrency()
    {
        return $this->cmsCurrency;
    }

    function getCmsLanguage() // Added getter
    {
        return $this->cmsLanguage;
    }

    public function getBackupBasePath()
    {
        return $this->backupBasePath;
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

    function setLicenceName($licenceName)
    {
        $this->licenceName = $licenceName;
    }

    function setLicence($licence)
    {
        $this->licence = $licence;
    }

    /**
     * Set the value of checked
     *
     * @return  self
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
        return $this;
    }

    function setAdminUsers($adminUsers)
    {
        $this->adminUsers = $adminUsers;
    }

    function setChefUsers($chefUsers)
    {
        $this->chefUsers = $chefUsers;
    }

    function setWaiterUsers($waiterUsers)
    {
        $this->waiterUsers = $waiterUsers;
    }

    function setCheckoutUsers($checkoutUsers)
    {
        $this->checkoutUsers = $checkoutUsers;
    }

    function setOrderCapability($orderCapability)
    {
        $this->orderCapability = $orderCapability;
    }

    function setPrintChef($printChef)
    {
        $this->printChef = $printChef;
    }

    function setPrintClient($printClient)
    {
        $this->printClient = $printClient;
    }
    function setPrintArabicRecipe($printArabicRecipe)
    {
        $this->printArabicRecipe = $printArabicRecipe;
    }

    function setCmsCurrency($cmsCurrency)
    {
        $this->cmsCurrency = $cmsCurrency;
    }

    function setCmsLanguage($cmsLanguage) // Added setter
    {
        $this->cmsLanguage = $cmsLanguage;
    }

    public function setBackupBasePath($path)
    {
        $this->backupBasePath = $path;
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
