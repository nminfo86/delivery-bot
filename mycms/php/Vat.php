
<?php

class Vat

{

    public static $table_name = "vat";
    public static $col_id = "id";
    public static $col_vat = "vat";
    public static $col_rate = "rate";

    private $id;
    private $vat;
    private $rate;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function getRate()
    {
        return $this->rate;
    }
}

?>