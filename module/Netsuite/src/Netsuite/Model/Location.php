<?php

namespace Netsuite\Model;

class Location
{
    public $data;

    protected $locations = array('',
                                'Winter Springs',
                                'Returns',
                                'Warehouse',
                                'Showroom',
                                'Demo',
                                'Exparts',
                                'Tradeshow',
                                'Rental',
                                'Drop Shipment',
                                'Consignment',
                                'Service/Repair Center',
                                'Layaway',
                                'EVENTS',
                                'LATM',
                                'MetalDetectors.com',
                                'MDSShowroom',
                                'Ebay',
                                'MDSReturns',
                                'RedStag',
                                'RedStag MDS',
                                'MDSreturns',
                                'MDSreturns');

    public function exchangeArray($data)
    {
        $this->data = $data;
    }

    public function getNameFromId($id){
        return isset($this->locations[$id])?$this->locations[$id]:$id;
    }
}