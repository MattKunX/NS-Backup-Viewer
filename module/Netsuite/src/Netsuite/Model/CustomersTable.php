<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 6/30/2016
 * Time: 4:21 PM
 */

namespace Netsuite\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select as Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class CustomersTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getCustomer($filter){
        $select = function(Select $select) use($filter){
            if(!empty($filter) && is_array($filter)){
                foreach($filter as $field => $value) {
                    $select->where("$field = '$value'");
                }
            }
            $select->limit(1);
            return $select;
        };
        return $this->tableGateway->select($select)->current();
    }

    public function getCustomers($filter=null){

        $select = new Select($this->tableGateway->getTable());
        $select->columns(array('InternalId',
            'Customer'=>'entityid',
            'Address'=>'defaultaddress',
            'Country'=>'shipcountry',
            'E-Mail'=>'email',
            'Phone',
            'Lead Source'=>'leadsource',
            'Subsidiary',
            'Date Created'=>'datecreated'));

        if(!empty($filter) && is_array($filter)){
            foreach($filter as $field => $value) {
                $select->where->or->like($field,"%$value%");
            }
        }

        $select->order(new \Zend\Db\Sql\Expression("STR_TO_DATE(datecreated,'%m/%d/%Y') DESC"));

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Customer());
        // create a new pagination adapter object
        $paginatorAdapter = new DbSelect(
            // our configured select object
            $select,
            // the adapter to run it against
            $this->tableGateway->getAdapter(),
            // the result set to hydrate
            $resultSetPrototype
        );
        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

}