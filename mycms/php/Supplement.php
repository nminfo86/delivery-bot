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
class Supplement
{
    //Object table name
    public static $table_name = "supplement";
    //Object table columns
    public static $col_id = "id";
    public static $col_ordere_id = "ordere_id";
    public static $col_suborder_id = "suborder_id";
    public static $col_supplementObject_id = "supplementObject_id";
    public static $col_supplementSuborderID = "supplementSuborderID";

    private $id;
    private $ordere_id;
    private $suborder_id;
    private $supplementObject_id;
    private $supplementSuborderID;

    function getId()
    {
        return $this->id;
    }

    function getOrdere_id()
    {
        return $this->ordere_id;
    }
    function getSuborder_id()
    {
        return $this->suborder_id;
    }

    function getsupplementObject_id()
    {
        return $this->supplementObject_id;
    }
    function getSupplementSuborderID()
    {
        return $this->supplementSuborderID;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setSuborder_id($suborder_id)
    {
        $this->suborder_id = $suborder_id;
    }
    function setOrdere_id($ordere_id)
    {
        $this->ordere_id = $ordere_id;
    }

    function setSupplementObject_id($supplementObject_id)
    {
        $this->supplementObject_id = $supplementObject_id;
    }
    function setSupplementSuborderID($supplementSuborderID)
    {
        $this->supplementSuborderID = $supplementSuborderID;
    }
}