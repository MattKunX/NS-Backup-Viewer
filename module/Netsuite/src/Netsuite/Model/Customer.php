<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 6/30/2016
 * Time: 4:25 PM
 */

namespace Netsuite\Model;

class Customer
{
    public $data;

    public function __construct($fulldata = false)
    {
        $this->fulldata = $fulldata;
    }

    public function exchangeArray($data)
    {
        $this->data = $data;
    }
}