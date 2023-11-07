<?php

namespace Application\Model;

class Mproduct
{
	public $id;
	public $sku;
	public $name;
	public $msrp;
	public $price;
	public $manufacturer;
	public $type;
	public $thumbnail;
	public $url;
	public $kit;
	public $hasbogo;
	public $qty;
	
	private $fulldata; // boolean: save full data array in each object?
	private $data;

	public function __construct($fulldata=false){
		$this->fulldata = $fulldata;
	}

	public function exchangeArray($data)
	{
		if($this->fulldata)
			$this->data     = (is_array($data)&&!empty($data)) ? $data : null;

		$this->id     = (!empty($data['entity_id'])) ? $data['entity_id'] : null;
		$this->sku = (!empty($data['sku'])) ? $data['sku'] : null;
		$this->name  = (!empty($data['name'])) ? $data['name'] : null;
		$this->price  = (!empty($data['price'])) ? $data['price'] : null;
		$this->msrp  = (!empty($data['msrp'])) ? $data['msrp'] : null;
		$this->manufacturer  = (!empty($data['manufacturer'])) ? $data['manufacturer'] : null;
		$this->type  = (!empty($data['attribute_set_id'])) ? $data['attribute_set_id'] : null;
		$this->thumbnail  = (!empty($data['thumbnail'])) ? $data['thumbnail'] : null;
		$this->url  = (!empty($data['url_key'])) ? 'http://www.kellycodetectors.com/catalog/'.$data['url_key'] : null;
		$this->kit  = (!empty($data['freeaccessorykit_value'])) ? $data['freeaccessorykit_value'] : null;
		$this->hasbogo  = (!empty($data['bogo_eligible'])) ? 'Yes' : 'No';
		$this->qty  = (!empty($data['qty'])) ? $data['qty'] : null;
	}

	public function getData(){
		return $this->data;
	}

}