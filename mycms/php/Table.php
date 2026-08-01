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
class Table
{
    //Object table name
    public static $table_name = "tabl";
    //Object table columns
    public static $col_id = "id";
    public static $col_tableName = "tableName";
    public static $col_tableCode = "tableCode";
    public static $col_tableFree = "tableFree";
    public static $col_creationDate = "creationDate";
    public static $col_updateDate = "updateDate";

    private $id;
    private $tableName;
    private $tableCode;
    private $tableFree;
    private $creationDate;
    private $updateDate;


    function getId()
    {
        return $this->id;
    }

    function getTableName()
    {
        return $this->tableName;
    }
    function getTableCode()
    {
        return $this->tableCode;
    }

    function getTableFree()
    {
        return $this->tableFree;
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

    function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
    function setTableCode($tableCode)
    {
        $this->tableCode = $tableCode;
    }

    function setTableFree($tableFree)
    {
        $this->tableFree = $tableFree;
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