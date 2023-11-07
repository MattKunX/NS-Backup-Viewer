<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Netsuite\Model\Record;

class SearchController extends AbstractActionController
{
    protected $NScustomersTable;
    protected $NSproductTable;
    protected $MproductTable;

    private $record = null;
    private $results = null;
    private $type = null;

    public function __construct()
    {
        
    }

    public function indexAction()
    {
        return new ViewModel(array('flashMessages'=>$this->flashMessenger()->getMessages()));
    }

    public function globalAction()
    {
        $request = $this->getRequest();
        $searchterm = $request->getQuery('search-term');

        if(!empty($searchterm)) {

            $this->record = new Record($this->getServiceLocator());

            $searchterm = trim($searchterm);

            // Guess record type
            if(preg_match('/^[a-z\-]+:?/i',$searchterm,$match)){
                
                if(isset($match[0])){
                    $prefix = $match[0];
                    $prefix = str_replace(':','',$prefix);
                    $prefixFunc = $prefix.'Records';

                    $searchterm = str_replace(':','',$searchterm);

                    if(method_exists($this, $prefixFunc))
                        $this->results = $this->$prefixFunc($searchterm);
                }

            }

            // Customers
            if(!$this->type)
                $this->CRecords($searchterm,false);

            // Sales Orders
            if(!$this->type)
                $this->SORecords($searchterm);

            // Cash sales
            if(!$this->type)
                $this->CSRecords($searchterm);

            // Invoices
            if(!$this->type)
                $this->INVRecords($searchterm);

            // Products
            if(!$this->type)
                $this->ITEMRecords($searchterm);
            
            if(!$this->type)
                $this->INVITMRecords($searchterm);

            if(!$this->type)
                $this->KITRecords($searchterm);

            // Item Fulfillments
            if(!$this->type)
                $this->ITMFRecords($searchterm);

            // Journal Entrys
            if(!$this->type)
                $this->JERecords($searchterm);

            // Purchase Orders
            if(!$this->type)
                $this->PORecords($searchterm);

            // Credit Memos
            if(!$this->type)
                $this->CMRecords($searchterm);

            // Vendors
            if(!$this->type)
                $this->VRecords($searchterm);

            // Deposits
            if(!$this->type)
                $this->DRecords($searchterm);
        }

        if($this->results) {
            
            $results = array('rows'=>'','paginator'=>null,'count'=>0);
            if(is_array($this->results)){
                
                foreach ($this->results as $key => $value) {
                    $page = (int)$this->params()->fromQuery('page', 1);
                    $value->setCurrentPageNumber($page);
                    $value->setItemCountPerPage(10);

                    if($page <= count($value)){
                        $this->type = $key;
                        $r = $this->resultsTableHtml($value);
                        $results['rows'] .= $r['rows'];
                        $results['paginator'] = $r['paginator'];
                        $results['count'] += $r['count'];
                        $results['headers'] = $r['headers'];
                    }
                }
            }else{
                $this->results->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
                $this->results->setItemCountPerPage(10);
                $results = $this->resultsTableHtml($this->results);
            }
            
            if($results['count'])
                return new ViewModel( array_merge($results,array('searchterm' => $searchterm)) );
        }
        return new ViewModel(array('flashMessages' => "'$searchterm' No results found...",'searchterm'=>$searchterm));
    }

    protected function resultsTableHtml($results){
        $i = 0;
        $column_headers = $rows = '';

        foreach ($results as $result) {
            $i++;
            $column_data = '';
            foreach ($result->data as $col => $data) {
                if ($i == 1){
                    if($col == 'Phone')
                        $column_headers .= "<th style='width:106px;'>$col</th>";
                    else
                        $column_headers .= "<th>$col</th>";

                    if($col == 'SKU')
                        $column_headers .= '<th>Type</th>';
                }

                if($col == 'InternalId')
                    $column_data .= "<td><a href='/netsuite/index/{$this->type}?id=$data'>$data</a></td>";
                else
                    $column_data .= "<td>$data</td>";

                if($col == 'SKU')
                    $column_data .= '<td>'.ucfirst(str_replace('item', ' Item', $this->type)).'</td>';
            }
            $rows .= "<tr class='prow'>$column_data</tr>";
        }

        return array('headers' => $column_headers,
                     'rows' => $rows,
                     'count' => $i,
                     'paginator' => $results);
    }

    public function CRecords($searchterm,$idonly=true){
        
        $cparams = array('entityid'=>$searchterm);
        
        if(!$idonly){
            // fix phone # format
            $phone = $searchterm;
            if(is_numeric($phone) && strlen($phone) == 10)
                $phone = substr($phone,0,3).'-'.substr($phone,3,3).'-'.substr($phone,6,4);
            
            $cparams = array('entityid' => $searchterm, 'defaultaddress' => $searchterm, 'email' => $searchterm, 'phone' => $phone);
        }

        $this->results = $this->getNetsuiteCustomersTable()->getCustomers($cparams);

        if(count($this->results) > 0)
                $this->type = 'customer';
        return $this->results;
    }

    public function SORecords($searchterm){
        $this->results = $this->record->getPaginatedTable('SALESORDER',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entity','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'salesorder';
        return $this->results;
    }

    public function CSRecords($searchterm){
        $this->results = $this->record->getPaginatedTable('CASHSALE',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entityname','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'cashsale';
        return $this->results;        
    }

    public function INVRecords($searchterm){
        $this->results = $this->record->getPaginatedTable('INVOICE',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entityname','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'invoice';
        return $this->results;
    }

    public function SKURecords($searchterm){
        return $this->ITEMRecords($searchterm);
    }

    public function ITEMRecords($searchterm){
        $results = array();
        $results['inventoryitem'] = $this->INVITMRecords($searchterm);
        $results['kititem'] = $this->KITRecords($searchterm);
        $this->results = $results;
        return $this->results;
    }

    public function INVITMRecords($searchterm){
        $searchterm = str_ireplace(array('SKU','ITEM','INVITM','KIT'),'',$searchterm);

        $this->results = $this->record->getPaginatedTable('INVENTORYITEM',array("itemid like '%$searchterm%'","displayname like '%$searchterm%'"),array('InternalId','SKU'=>'itemid','Name'=>'displayname'));

        if(count($this->results) > 0)
            $this->type = 'inventoryitem';
        return $this->results;
    }

    public function KITRecords($searchterm){
        $searchterm = str_ireplace(array('SKU','ITEM','INVITM','KIT'),'',$searchterm);

        $this->results = $this->record->getPaginatedTable('KITITEM',array("itemid like '%$searchterm%'","displayname like '%$searchterm%'"),array('InternalId','SKU'=>'itemid','Name'=>'displayname'));

        if(count($this->results) > 0)
            $this->type = 'kititem';
        return $this->results;
    }

    public function JERecords($searchterm){
        $this->results = $this->record->getPaginatedTable('JOURNALENTRY',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Created From'=>'createdfrom','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'journalentry';
        return $this->results;
    }
    // JE alias
    public function JRecords($searchterm){
        return $this->JERecords(substr($searchterm,1));
    }

    public function ITMFRecords($searchterm){
        $this->results = $this->record->getPaginatedTable('ITEMFULFILLMENT',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entityname','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'itemfulfillment';
        return $this->results;
    }

    public function PORecords($searchterm){
        $this->results = $this->record->getPaginatedTable('PURCHASEORDER',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entityname','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'purchaseorder';
        return $this->results;
    }

    public function RMARecords($searchterm){
        $this->results = $this->record->getPaginatedTable('RETURNAUTHORIZATION',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entityname','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'returnauthorization';
        return $this->results;
    }

    public function CMRecords($searchterm){
        $this->results = $this->record->getPaginatedTable('CREDITMEMO',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Customer'=>'entityname','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'creditmemo';
        return $this->results;
    }

    public function VRecords($searchterm){
        $this->results = $this->record->getPaginatedTable('VENDOR',array("entityid like '%$searchterm%'"),array('InternalId','Entity Number'=>'entityid','Vendor Name'=>'entitytitle','Date Created'=>'datecreated'));

        if(count($this->results) > 0)
            $this->type = 'vendor';
        return $this->results;
    }

    public function DRecords($searchterm){
        $searchterm = substr($searchterm, 1); //remove 'D'
        $this->results = $this->record->getPaginatedTable('DEPOSIT',array("tranid like '%$searchterm%'"),array('InternalId','Transaction Number'=>'tranid','Account'=>'account','Transaction Date'=>'trandate'));

        if(count($this->results) > 0)
            $this->type = 'deposit';
        return $this->results;
    }    

    public function getNetsuiteCustomersTable()
    {
        if (!$this->NScustomersTable) {
            $sm = $this->getServiceLocator();
            $this->NScustomersTable = $sm->get('Netsuite\Model\CustomersTable');
        }
        return $this->NScustomersTable;
    }
}
