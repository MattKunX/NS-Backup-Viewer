<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 7/18/2016
 * Time: 11:50 AM
 */

namespace Netsuite\Model;

use Netsuite\Model\DataTable as DataTable;
use Netsuite\Model\SalesRep;

class Record
{
    protected $dynamicTable;
    protected $sm;
    protected $id;
    protected $results;

    public $data;

    public function __construct($sm=null)
    {
        $this->dynamicTable = null;
        $this->sm = $sm;
        $this->results = null;
    }

    public function exchangeArray($data)
    {
        $this->data = $data;
    }

    public function __call($name, $arguments)
    {
        $table = null;
        // getFunction()
        if(strpos($name,'get') === 0){

            // getField value
            if(stripos($name,'Record') === false && stripos($name,'Table') === false){
                
                if(!$this->results)
                    $this->results = $this->dynamicTable->getResults();
                
                $field = substr($name,3);
                if( isset($this->results[$field]) )
                    return $this->results[$field];
            }

            // get{tablename}Table|Record(where array,fields array,joins array,limit int)
            $tables = array('Table','Record');
            foreach($tables as $t) {
                $tablepos = stripos($name, $t) - 3;
                if ($tablepos > 0)
                    $table = substr($name, 3, $tablepos);
            }

            // replace _ with -
            $table = str_replace('_','-',$table);

            //$table = substr($name,3,strpos($name,'Record')-3);

            if($table){

                //if (!isset($this->dynamicTable))
                $this->dynamicTable = new DataTable($this->sm,$table);

                // record ID
                if(isset($arguments[0]['InternalId']))
                    $this->id = $arguments[0]['InternalId'];

                if(isset($arguments[3])) {
                    return $this->dynamicTable->getData($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                }
                if(isset($arguments[2])) {
                    return $this->dynamicTable->getData($arguments[0], $arguments[1], $arguments[2]);
                }
                if(isset($arguments[1])) {
                    return $this->dynamicTable->getData($arguments[0], $arguments[1]);
                }
                if(isset($arguments[0])) {
                    return $this->dynamicTable->getData($arguments[0]);
                }
            }
        }
        return null;
    }

    public function getRecord(...$arguments){
        if(get_parent_class($this)){
            $class = get_class($this);
            $classname = strtoupper(substr($class, strrpos($class, '\\') + 1));
            $callfunc = "get{$classname}";
            return $this->{$callfunc}(...$arguments);
        }
        return null;
    }

    public function getTable($name,$where,$fields=null,$joins=null,$limit=0,$misc=null){
        $this->dynamicTable = new DataTable($this->sm,$name);

        // record ID
        if(isset($where['InternalId']))
            $this->id = $where['InternalId'];

        return $this->dynamicTable->getData($where,$fields,$joins,$limit,$misc);
    }

    public function getPaginatedTable($name,$where,$fields=null){
        $this->dynamicTable = new DataTable($this->sm,$name);

        return $this->dynamicTable->getPaginatedRecords($where,$fields);
    }

    public function getData(){
        $this->results = $this->dynamicTable->getResults();
        
        if(isset($results['salesrep'])) {
            $salesrep = new SalesRep();
            $results['salesrep'] = $salesrep->getNameFromId($results['salesrep']);
        }

        return $this->results;
    }
    
    public function buildDynamicFields(){
        if(!empty($this->dynamicTable->getResults())) {

            $results = $this->dynamicTable->getResults();

            ksort($results);

            if(isset($results['salesrep'])) {
                $salesrep = new SalesRep();
                $results['salesrep'] = $salesrep->getNameFromId($results['salesrep']);
            }

            $dynamic_fields = '';
            $i = 0;
            foreach ($results as $field => $value) {

                // dynamic fields
                $class = 'field';

                if ($i % 6 == 0)
                    $class .= ' clear';

                switch ($value){
                    case 'T':
                        $value = 'Yes';
                        break;
                    case 'F':
                        $value = 'No';
                        break;
                }

                if(empty($value))
                    $class .= ' empty';

                $dynamic_fields .= "<div class='$class'><b>$field:</b> $value</div>";
                $i++;
            }
            return $dynamic_fields;
        }
        return null;
    }
}