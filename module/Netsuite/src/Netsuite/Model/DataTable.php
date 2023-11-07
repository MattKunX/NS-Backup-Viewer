<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 6/30/2016
 * Time: 4:21 PM
 */

namespace Netsuite\Model;

use Zend\Db\Sql\Predicate;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Sql\Where as Where;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class DataTable
{
    protected $tableGateway;
    protected $results;

    public function __construct($sm,$table)
    {
        $dbAdapter = $sm->get('netsuite');
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Data());
        $this->tableGateway = new TableGateway($table, $dbAdapter, null, $resultSetPrototype);
        $this->results = null;
        return $this->tableGateway;
    }

    public function getData(array $filter,array $columns = null,array $joins = null,$limit=1,$misc=null)
    {
        if(!is_array($filter))
            return false;

        $select = function (Select $select) use ($filter,$columns,$joins,$limit,$misc) {
            if (!empty($filter) && is_array($filter)) {
                foreach ($filter as $field => $value) {
                    $select->where("$field = '$value'");
                }
            }

            if ($columns && !empty($columns) && is_array($columns)) {
                $select->columns($columns);
            }

            if (!empty($joins) && is_array($joins)) {
                //\Zend\Debug\Debug::dump($joins);
                foreach ($joins as $table => $values) {
                    $select->join(array($table=>$table),$values['condition'],$values['columns']);
                }
            }

            if($limit)
                $select->limit($limit);

            if($misc)
                call_user_func(array($select,key($misc)),current($misc));

            return $select;
        };


        if($limit==1)
            $this->results = $this->tableGateway->select($select)->current();
        else
            $this->results = $this->tableGateway->select($select);

        if(isset($this->results->data))
            $this->results = $this->results->data;

        return $this->results;
    }

    /* where: array('price > ?'=>'100','price < ?'=>'300') */
    public function getPaginatedRecords($where,$columns = null)
    {
        $select = new Select($this->tableGateway->getTable());
        $w = new Where();

        if(isset($where) && !empty($where)){
            
            if(is_object($where)){
                $params = clone $where; //for &'page'

                if(isset($params->page))
                    unset($params->page);

                foreach ($params as $field => $value) {
                    if(!empty($value) && $field!='filter') {
                        switch ($field) {
                            case 'recordid':
                                $field = $columns[1];
                                break;
                        }
                        $w->and->like($field, "%$value%");
                    }
                }
            }

            if(is_array($where)) {
                $params = $where;
                if(isset($params['page']))
                    unset($params['page']);
               
                foreach ($params as $value) {
                    $w->or->literal($value);
                }
            }
            $select->where($w);
        }

        $select->order('InternalId DESC');
        //$select->order(new \Zend\Db\Sql\Expression("STR_TO_DATE(trandate,'%m/%d/%Y') DESC"));

        if(is_array($columns))
            $select->columns($columns);

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Record());
        // create a new pagination adapter object
        $paginatorAdapter = new DbSelect(
            // our configured select object
            $select,
            // the adapter to run it against
            $this->tableGateway->getAdapter(),
            // the result set to hydrate
            $resultSetPrototype
        );
        $this->results = new Paginator($paginatorAdapter);

        return $this->results;
    }

    public function getResults(){
        return $this->results;
    }

    public function getTableHtml($type){
        $i = 0;
        $column_headers = $rows = '';
        foreach($this->results as $row){
            $i++;
            $column_data = '';
            foreach($row->data as $col => $data){
                if($i==1)
                    $column_headers .= "<th>$col</th>";

                if($col == 'InternalId'){
                    $column_data .= "<td><a href='/netsuite/index/$type?id=$data'>$data</a></td>";
                }else{
                    if(!preg_match('/tranid|acctnumber/', $col) && is_numeric($data))
                        $data = number_format($data,2);

                    $column_data .= "<td>$data</td>";
                }
            }
            $rows .= "<tr class='prow'>$column_data</tr>";
        }

        $table = "<table class='table'><tr>$column_headers</tr><tbody>$rows</tbody></table>";

        return $table;
    }
}