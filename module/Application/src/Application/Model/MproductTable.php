<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
//use Zend\Db\Sql;
//use Zend\Db\Sql\Predicate as Predicate;
//use Zend\Db\Sql\Where as Where;
use Zend\Db\Sql\Select as Select;
use Zend\Text\Table\Table;

class MproductTable
{
	protected $tableGateway;
	protected $categories;
	protected $stockTable;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
		$this->stockTable = new TableGateway('cataloginventory_stock_item',$this->tableGateway->getAdapter());
	}

	public function fetchAll()
	{
		/*$select = function (Select $s){
			$s->limit(100);
		};*/
		return $this->tableGateway->select();
	}

	public function getProduct($params)
	{
		
		$result = $this->tableGateway->select(function(Select $select) use($params){

			$select->join(array('inv'=>'cataloginventory_stock_item'),'entity_id=product_id','qty');

			if(isset($params['sku']) && !empty($params['sku']))
				$select->where->like('sku',"%{$params['sku']}%");
			
			if(isset($params['name']) && !empty($params['name']))
				$select->where->like('name',"%{$params['name']}%");

			if(isset($params['kit']) && !empty($params['kit']))
				$select->where->like('freeaccessorykit_value',"%{$params['kit']}%");
			
			if( (isset($params['price-low']) && !empty($params['price-low'])) || 
				(isset($params['price-high']) && !empty($params['price-high'])) ){

				if(empty($params['price-high']) &&  $params['price-low'] > 0)
					$params['price-high'] = 100000;

				$select->where->between('price',$params['price-low'],$params['price-high']);
			}
			
			if(isset($params['id']) && !empty($params['id']))
				$select->where->equalTo('entity_id',$params['id']);
			
			if(isset($params['manufacturer']) && !empty($params['manufacturer']))
				$select->where->equalTo('manufacturer',$params['manufacturer']);

			if(isset($params['type']) && !empty($params['type']))
				$select->where->equalTo('attribute_set_id',$params['type']);

			if(isset($params['hasbogo']) && is_numeric($params['hasbogo']))
				$select->where->equalTo('bogo_eligible',$params['hasbogo']);
			
			if(isset($params['order']) && !empty($params['order'])){
				
				switch ($params['order']) {
					case 'id':
						$params['order'] = 'entity_id';
						break;
					case 'kit':
						$params['order'] = 'freeaccessorykit_value';
						break;
					case 'type':
						$params['order'] = 'attribute_set_id';
						break;
					case 'has bogo':
						$params['order'] = 'bogo_eligible';
						break;
					case 'product page':
						$params['order'] = 'url_key';
						break;
					case 'stock qty':
						$params['order'] = 'qty';
						break;
				}

				if(empty($params['dir']))
					$params['dir'] = 'ASC';
				
				$select->order($params['order'].' '.$params['dir']);
			}
		});
		
		return $result;
	}

	public function getCategories(){
		$catTable = new TableGateway(array('ccp'=>'catalog_category_product'),$this->tableGateway->getAdapter());
		$select = function(Select $select) {
			$select->join(array('ccf' => 'catalog_category_flat_store_1'), 'ccf.entity_id=ccp.category_id', array('entity_id','name','level'),'left');
		};
		$result = $catTable->select($select);

		foreach($result as $r)
			$this->categories[$r->product_id][] = $r->name;

		return $this->categories;
	}

	public function getStockTable(){
		return $this->stockTable->select();
	}

	public function addStock($data){
		if($this->stockTable){
			$this->stockTable->insert($data);
		}
		return false;
	}

	public function clearStockTable(){
		$this->clearTable($this->stockTable->getTable());
	}

	public function addProduct($data){
		return $this->tableGateway->insert($data);
	}

	public function clearTable($table = null){
		if(!$table)
			$table = $this->tableGateway->getTable();
		$query = $this->tableGateway->getAdapter()->query('TRUNCATE TABLE '.$table);
		$query->execute();
	}
}