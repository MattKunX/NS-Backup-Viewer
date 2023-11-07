<?php
/**
 * Netsuite Backup Viewer
 *
 * @copyright Copyright (c) 2005-2018
 * @author    MattKun
 */

namespace Netsuite\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use Netsuite\Model\DataTable;
use Netsuite\Model\CustomerRecord;
use Netsuite\Model\SalesorderRecord;
use Netsuite\Model\InvoiceRecord;
use Netsuite\Model\CustomerpaymentRecord as CustomerPaymentRecord;
use Netsuite\Model\CustomerrefundRecord as CustomerRefundRecord;
use Netsuite\Model\CreditmemoRecord as CreditMemoRecord;
use Netsuite\Model\CashsaleRecord;
use Netsuite\Model\CheckRecord;
use Netsuite\Model\DepositRecord;
use Netsuite\Model\EmployeeRecord;
use Netsuite\Model\OthernameRecord as OtherNameRecord;
use Netsuite\Model\PurchaseorderRecord as PurchaseOrderRecord;
use Netsuite\Model\InventoryitemRecord as InventoryItemRecord;
use Netsuite\Model\KititemRecord as KitItemRecord;
use Netsuite\Model\ItemfulfillmentRecord as ItemFulfillmentRecord;
use Netsuite\Model\JournalentryRecord as JournalEntryRecord;
use Netsuite\Model\VendorRecord;
use Netsuite\Model\VendorpaymentRecord as VendorPaymentRecord;
use Netsuite\Model\SalesRep;
use Netsuite\Form\FilterForm;

class IndexController extends AbstractActionController
{
    protected $customersTable;
    protected $dynamicTables = array();
    protected $recordId;
    protected $tabdata = array();

    protected function getId()
    {
        $this->recordId = $this->getRequest()->getQuery('id') | $this->params()->fromRoute('id');
        return $this->recordId;
    }

    /**
     * Override, check for login etc
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $auth = new AuthenticationService();

        if($auth->hasIdentity())
            $this->user = $auth->getIdentity();
        else
            return $this->redirect()->toRoute('application',array('controller'=>'index','action'=>'index'));

        return parent::onDispatch($e);
    }

    public function deniedAction()
    {
        echo "You do not have permission to this area.";
        return false;
    }

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('netsuite/index/index.phtml');
        return $view;
    }

    /*** Lists ***/

    public function accountsAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'ACCOUNT');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','acctnumber','acctname','accttype','balance','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Accounts',
                                    'action'=>$action,
                                    'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function customersAction()
    {
        $request = $this->getRequest();
        $customers = $this->getCustomersTable()->getCustomers($request->getQuery());
        $customers->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $customers->setItemCountPerPage(10);

        //$action = $this->params('action');
        //$link_action = substr($action,0,strlen($action)-1);
        $i = 0;
        $column_headers = $rows = '';
        foreach($customers as $customer){
            $i++;
            $column_data = '';
            foreach($customer->data as $col => $data){
                if($i==1)
                    $column_headers .= "<th>$col</th>";

                if($col == 'InternalId') {
                    // $column_data .= "<td><a href='/netsuite/index/customer?id=$data'>$data</a></td>";
                    $url = $this->url()->fromRoute('index', array('action' => 'customer', 'id' => $data));
                    $column_data .= "<td><a href='$url'>$data</a></td>";
                } else {
                    $column_data .= "<td>$data</td>";
                }
            }
            $rows .= "<tr class='prow'>$column_data</tr>";
        }

        $table = "<table class='table'><tr>$column_headers</tr><tbody>$rows</tbody></table>";

        return new ViewModel(array('table'=>$table,'paginator'=> $customers));
    }

    public function salesordersAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'SALESORDER');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','trandate','salesrep','CSR'=>'custbodycsr','orderstatus','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Sales Orders',
                                    'action'=>$action,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'subrecord'=>$recordaction,
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function cashsalesAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'CASHSALE');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','trandate','salesrep','CSR'=>'custbodycsr','status','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Cash Sales',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function invoicesAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'INVOICE');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','trandate','salesrep','CSR'=>'custbodycsr','status','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Invoices',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function fulfillmentsAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'ITEMFULFILLMENT');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','phone','trandate','createdfrom','status','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = 'itemfulfillment';

        $view = new ViewModel(array('title'=>'Netsuite Item Fulfillments',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function creditmemosAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'CREDITMEMO');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','trandate','salesrep','CSR'=>'custbodycsr','status','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Credit Memos',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function checksAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'CHECK');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','total','trandate','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Checks',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function journalentriesAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'JOURNALENTRY');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','custbody17','trandate','status','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = 'journalentry';

        $view = new ViewModel(array('title'=>'Netsuite Journal Entries',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function depositsAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'DEPOSIT');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','account','trandate','total','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Deposits',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function othernamesAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'OTHERNAME');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','entityid','entitytitle','datecreated','email','phone','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Other Names',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function vendorsAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'VENDOR');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','entityid','entitytitle','datecreated','phone','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Vendors',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function vendorbillsAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'VENDORBILL');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','memo','trandate','total','status','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Vendor Bills',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    public function vendorpaymentsAction(){
        $request = $this->getRequest();
        $table = new DataTable($this->getServiceLocator(),'VENDORPAYMENT');
        $paginator = $table->getPaginatedRecords($request->getQuery(),array('InternalId','tranid','entity','account','trandate','total','subsidiary'));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(15);

        $action = $this->params('action');
        $recordaction = substr($action,0,strlen($action)-1);

        $view = new ViewModel(array('title'=>'Netsuite Vendor Payments',
                                    'action'=>$action,
                                    'subrecord'=>$recordaction,
                                    // 'table'=>$table->getTableHtml($recordaction),
                                    'paginator'=>$paginator,
                                    'filterform'=>new FilterForm($this->params('action'),$request->getQuery()),
                                    'query'=>get_object_vars($request->getQuery())
                                    )
                             );
        $view->setTemplate('netsuite/index/recordlist.phtml');
        return $view;
    }

    /*** Records ***/

    public function customerAction(){
        $record = new CustomerRecord($this->getServiceLocator());
        $record->getCUSTOMERRecord(array('InternalId' => $this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
                                    'fields'=>$record->getData(),
                                    'tran_table_html'=>$record->transactionTableHtml(),
                                    'address_table_html'=>$record->addressTableHtml(),
                                    'messages_table_html'=>$record->messagesTableHtml(),
                                    'notes_table_html'=>$record->notesTableHtml(),
                                    'files_table_html'=>$record->filesTableHtml(),
                                    'cases_table_html'=>$record->casesTableHtml()
            ));
    }

    public function customerrefundAction(){
        $record = new CustomerRefundRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
        ));
    }

    public function creditmemoAction(){
        $record = new CreditMemoRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
            'salesteam_table_html'=>$record->salesteamTableHtml(),
            'giftcert_table_html'=>$record->giftcertTableHtml()
        ));
    }

    public function checkAction(){
        $record = new CheckRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'lines_table_html'=>$record->linesTableHtml(),
            'links_table_html'=>$record->linksTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid'])
        ));
    }

    public function salesorderAction(){
        $record = new SalesorderRecord($this->getServiceLocator());
        $record->getSALESORDERRecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
            'salesteam_table_html'=>$record->salesteamTableHtml(),
            'giftcert_table_html'=>$record->giftcertTableHtml()
        ));
    }

    public function cashsaleAction(){
        $record = new CashsaleRecord($this->getServiceLocator());
        $record->getCASHSALERecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
            'salesteam_table_html'=>$record->salesteamTableHtml(),
            'giftcert_table_html'=>$record->giftcertTableHtml()
        ));
    }

    public function depositAction(){
        $record = new DepositRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'payments_table_html'=>$record->paymentsTableHtml(),
            'otherdeposits_table_html'=>$record->otherdepositsTableHtml(),
            'cashback_table_html'=>$record->cashbackTableHtml(),
            'links_table_html'=>$record->linksTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid'])
        ));
    }

    public function employeeAction(){
        $record = new EmployeeRecord($this->getServiceLocator());
        $record->getEMPLOYEERecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>'',//$record->buildDynamicFields(),
                                    'fields'=>$record->getData(),
                                    'tran_table_html'=>'',//$record->transactionTableHtml(),
                                    'address_table_html'=>$record->addressTableHtml(),
                                    'messages_table_html'=>$record->messagesTableHtml(),
                                    'notes_table_html'=>$record->notesTableHtml(),
                                    'files_table_html'=>$record->filesTableHtml(),
                                    'cases_table_html'=>$record->casesTableHtml()
            ));
    }

    public function inventoryitemAction(){
        $record = new InventoryItemRecord($this->getServiceLocator());
        $record->getINVENTORYITEMRecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'prices_table_html'=>$record->pricesTableHtml(),
            'inventory_table_html'=>$record->inventoryTableHtml(),
            'purchase_table_html'=>$record->purchaseTableHtml()
        ));
    }

    public function kititemAction(){
        $record = new KitItemRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'prices_table_html'=>$record->pricesTableHtml(),
            'inventory_table_html'=>$record->inventoryTableHtml(),
            'purchase_table_html'=>$record->purchaseTableHtml()
        ));
    }

    public function itemfulfillmentAction(){
        $record = new ItemFulfillmentRecord($this->getServiceLocator());
        $record->getITEMFULFILLMENTRecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'packages_table_html'=>$record->packagesTableHtml(),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
        ));
    }

    public function invoiceAction(){
        $record = new InvoiceRecord($this->getServiceLocator());
        $record->getINVOICERecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
            'salesteam_table_html'=>$record->salesteamTableHtml(),
            'giftcert_table_html'=>$record->giftcertTableHtml()
        ));
    }

    public function customerpaymentAction(){
        $record = new CustomerPaymentRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));
        /*,
                    null,
                    array('CUSTOMERPAYMENT-account'=>
                        array(
                            'condition'=>'InternalId=customerpayment_internalId',
                            'columns'=>array('account_internalId_FK')
                             )
                         )
                    );
        */
        //TODO Join
        //$r = new DataTable($this->getServiceLocator(),'CUSTOMERPAYMENT_account');
        //$accountfields = $r->getData(array('customerpayment_internalId'=>$this->getId()),array('account_internalId_FK'));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid']),
            'salesteam_table_html'=>$record->salesteamTableHtml(),
            'giftcert_table_html'=>$record->giftcertTableHtml()
        ));
    }

    public function journalentryAction(){
        $record = new JournalEntryRecord($this->getServiceLocator());
        $record->getJOURNALENTRYRecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'lines_table_html'=>$record->linesTableHtml(),
            'links_table_html'=>$record->linksTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid'])
        ));
    }

    public function othernameAction()
    {
        $record = new OtherNameRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
                                    'fields'=>$record->getData(),
                                    'tran_table_html'=>$record->transactionTableHtml(),
                                    'address_table_html'=>$record->addressTableHtml(),
                                    'messages_table_html'=>$record->messagesTableHtml(),
                                    'notes_table_html'=>$record->notesTableHtml(),
                                    'files_table_html'=>$record->filesTableHtml(),
                                    'cases_table_html'=>$record->casesTableHtml()
            ));
    }

    public function purchaseorderAction(){
        $record = new PurchaseOrderRecord($this->getServiceLocator());
        $record->getPURCHASEORDERRecord(array('InternalId'=>$this->getId()));
        $fields = $record->getData();

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'items_table_html'=>$record->itemsTableHtml(),
            'links_table_html'=>$record->linksTableHtml(),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid'])
        ));
    }

    public function vendorAction()
    {
        $record = new VendorRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId()));

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
                                    'fields'=>$record->getData(),
                                    'tran_table_html'=>$record->transactionTableHtml(),
                                    'address_table_html'=>$record->addressTableHtml(),
                                    'messages_table_html'=>$record->messagesTableHtml(),
                                    'notes_table_html'=>$record->notesTableHtml(),
                                    'files_table_html'=>$record->filesTableHtml(),
                                    'cases_table_html'=>$record->casesTableHtml()
            ));
    }

    public function vendorpaymentAction(){
        $record = new VendorPaymentRecord($this->getServiceLocator());
        $fields = $record->getRecord(array('InternalId'=>$this->getId())); /*,
                    null,
                    array('VENDORPAYMENT-account'=>
                        array(
                            'condition'=>'InternalId=vendorpayment_internalId',
                            'columns'=>array('account_internalId_FK')
                             )
                         )
                    ); */

        return new ViewModel(array('dynamic_fields'=>$record->buildDynamicFields(),
            'fields'=>$fields,
            'record'=>$record,
            'related_table_html'=>$record->relatedTableHtml($fields['tranid']),
            'messages_table_html'=>$record->messagesTableHtml(),
            'notes_table_html'=>$record->notesTableHtml($fields['tranid'])
        ));
    }

    // TODO:
    public function vendorbillAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'vendorbill';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function itemreceiptAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'itemreceipt';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function discountitemAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'discountitem';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function markupitemAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'markupitem';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function serviceitemAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'serviceitem';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function itemgroupAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'itemgroup';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function assemblyitemAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'assemblyitem';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function giftcertificateitemAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'giftcertificateitem';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }


 
    public function returnauthorizationAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'returnauthorization';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function messageAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'message';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function noteAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'note';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    public function accountAction(){
        $q = $this->getRequest()->getQuery();
        $q['type'] = 'account';
        $this->getRequest()->setQuery($q);
        return $this->recordAction();
    }

    /*** Dynamic Record Loading ***/

    public function __call($name, $arguments)
    {
        $table = null;
        // getFunction()
        if(strpos($name,'get') === 0){

            // get{tablename}Table|Record(where array,fields array,joins array)
            $tables = array('Table','Record');
            foreach($tables as $t) {
                $tablepos = strpos($name, $t) - 3;
                if ($tablepos > 0)
                    $table = substr($name, 3, $tablepos);
            }

            // replace _ with -
            $table = str_replace('_','-',$table);

            //$table = substr($name,3,strpos($name,'Record')-3);

            if($table){

                if (!isset($this->dynamicTables[$table])) {
                    $sm = $this->getServiceLocator();
                    $this->dynamicTables[$table] = new DataTable($sm,$table);
                }

                if(isset($arguments[3]))
                    return $this->dynamicTables[$table]->getData($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
                if(isset($arguments[2]))
                    return $this->dynamicTables[$table]->getData($arguments[0],$arguments[1],$arguments[2]);
                if(isset($arguments[1]))
                    return $this->dynamicTables[$table]->getData($arguments[0],$arguments[1]);
                if(isset($arguments[0]))
                    return $this->dynamicTables[$table]->getData($arguments[0]);
            }
        }
        return parent::__call($name,$arguments);
    }

    public function getCustomersTable()
    {
        if (!$this->customersTable) {
            $sm = $this->getServiceLocator();
            $this->customersTable = $sm->get('Netsuite\Model\CustomersTable');
        }
        return $this->customersTable;
    }

    public function getCustomerRecord($internalId)
    {
        $customer = $this->getCustomersTable()->getCustomer($internalId);
        if($customer)
            return $customer->data;
        return null;
    }

    public function recordAction()
    {
        $request = $this->getRequest();
        $type = ucfirst($request->getQuery('type'));
        $this->recordId = $request->getQuery('id');

        $results = call_user_func(array(get_class($this),'get'.strtoupper($type).'Record'),array('InternalId'=>$this->recordId));

        $dynamic_fields = $title = '';

        if(!empty($results)) {

            if(isset($results['salesrep'])){
                $salesrep = new SalesRep();
                $results['salesrep'] = $salesrep->getNameFromId($results['salesrep']);
            }

            $i = 0;
            foreach ($results as $field => $value) {

                // Title
                switch ($field) {
                    case 'entityid':
                    case 'tranid':
                    case 'acctname':
                    case 'itemid':
                        $title = $value;
                    break;
                }

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

                $dynamic_fields .= "<div class='$class'><b>$field:</b> $value</div>";
                $i++;
            }
            $this->buildTabsHtml($type);

        }

        $viewModel =  new ViewModel(array('dynamic_fields'=>$dynamic_fields,'type'=>$type,'title'=>$title,'fields'=>$results,'tabdata'=>$this->tabdata));
        $viewModel->setTemplate('netsuite/index/record.phtml');
        return $viewModel;
    }

    protected function buildTabsHtml($type){
        switch ($type){
            case 'Customer':
                $this->tabdata['transactions'] = $this->transactionTableHtml();
                break;
        }
    }

    protected function transactionTableHtml(){
        $salesorders = $this->getSALESORDER_customerTable(array('customer_InternalId_FK'=>$this->recordId),
            array('salesorder_internalId'),
            array('SALESORDER'=>array('condition'=>'InternalId=salesorder_internalId',
                'columns'=>array('tranid','createddate','otherrefnum','total','custbodycsr','status','memo'))
            ),
            0);

        $cashsales = $this->getCASHSALE_customerTable(array('customer_InternalId_FK'=>$this->recordId),
            array('cashsale_internalId'),
            array('CASHSALE'=>array('condition'=>'InternalId=cashsale_internalId',
                'columns'=>array('tranid','createddate','otherrefnum','total','custbodycsr','status','memo'))
            ),
            0);

        $so_html = '<div class="field field-header">Type</div>
                                <div class="field field-header">Internal ID</div>
                                <div class="field field-header">Document Number</div>
                                <div class="field field-header">Date Created</div>
                                <div class="field field-header">Ref. No#</div>
                                <div class="field field-header">Total</div>
                                <div class="field field-header">CSR</div>
                                <div class="field field-header">Status</div>
                                <div class="field field-header">Memo</div>
                                <div class="clear"></div> ';

        foreach($salesorders as $so) {
            $so_html .= '<div class="field">Sales Order</div>';
            foreach($so->data as $k=>$v) {
                if($k=='salesorder_internalId')
                    $v = "<a href='/netsuite/index/record?id=$v&type=salesorder'>$v</a>";
                $so_html .= "<div class='field'>$v</div>";
            }
            $so_html .= '<div class="clear"></div>';
        }

        foreach($cashsales as $so) {
            $so_html .= '<div class="field">Cash Sale</div>';
            foreach($so->data as $k=>$v) {
                if($k=='cashsale_internalId')
                    $v = "<a href='/netsuite/index/record?id=$v&type=cashsale'>$v</a>";
                $so_html .= "<div class='field'>$v</div>";
            }
            $so_html .= '<div class="clear"></div>';
        }

        return $so_html;
    }
}
