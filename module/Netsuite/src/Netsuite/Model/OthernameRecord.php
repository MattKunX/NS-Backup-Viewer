<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 7/18/2016
 * Time: 3:00 PM
 */

namespace Netsuite\Model;

use Netsuite\Model\Record;

class OthernameRecord extends Record
{
    public function transactionTableHtml(){
        $transactions = array();

        // backup, search main sales order table
        $transactions['salesorder']['name'] = 'Sales Order';
        $transactions['salesorder']['data'] = $this->getSALESORDERTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','createdfrom','createddate','otherrefnum','total','custbodycsr','status','memo'),
                    null,0);

        $transactions['itemfulfillment']['name'] = 'Fulfillment';
        $transactions['itemfulfillment']['data'] = $this->getITEMFULFILLMENTTable(array('companyid'=>$this->id),
                        array('InternalId','tranid','createdfrom','createddate','custbody_ozlink_shipping_service','shippingcost','custbodycreatedby','status','memo'),
                        null,0);

        $transactions['cashsale']['name'] = 'Cash Sale';
        $transactions['cashsale']['data'] = $this->getCASHSALETable(array('companyid'=>$this->id),
                    array('InternalId','tranid','createdfrom','createddate','otherrefnum','total','custbodycsr','status','memo'),
                    null,0);

        $transactions['invoice']['name'] = 'Invoice';
        $transactions['invoice']['data'] = $this->getINVOICETable(array('companyid'=>$this->id),
                    array('InternalId','tranid','createdfrom','createddate','otherrefnum','total','custbodycsr','status','memo'),
                    null,0);

        $transactions['customerpayment']['name'] = 'Payment';
        $transactions['customerpayment']['data'] = $this->getCUSTOMERPAYMENTTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','nexttranid','createddate','pnrefnum','total','paymenteventupdatedby','status','memo','custcurrep'),
                    null,0);

        $transactions['customerdeposit']['name'] = 'Deposit';
        $transactions['customerdeposit']['data'] = $this->getCUSTOMERDEPOSITTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','pnrefnum','createddate','pnrefnum','payment','authcode','paymentmethod','memo'),
                    null,0);

        $transactions['customerrefund']['name'] = 'Refund';
        $transactions['customerrefund']['data'] = $this->getCUSTOMERREFUNDTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','pnrefnum','createddate','pnrefnum','total','balance','status','memo'),
                    null,0);

        $transactions['returnauthorization']['name'] = 'Return';
        $transactions['returnauthorization']['data'] = $this->getRETURNAUTHORIZATIONTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','createdfrom','createddate','otherrefnum','total','salesrep','status','memo'),
                    null,0);

        $transactions['itemreceipt']['name'] = 'Item Receipt';
        $transactions['itemreceipt']['data'] = $this->getITEMRECEIPTTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','createdfrom','createddate','landedcostamount1','postingperiod','currencysymbol','memo'),
                    null,0);

        $transactions['creditmemo']['name'] = 'Credit Memo';
        $transactions['creditmemo']['data'] = $this->getCREDITMEMOTable(array('companyid'=>$this->id),
                    array('InternalId','tranid','createdfrom','createddate','otherrefnum','total','custbodycsr','status','memo'),
                    null,0);

        // array for sorting etc
        $superarray = array();

        $html = '';
        foreach($transactions as $type => $t){
            foreach($t['data'] as $d) {
                $html = '<div class="field small-field">'.$t['name'].'</div>';
                foreach($d->data as $k=>$v) {
                    
                    if(strtolower($k)==$type.'_internalid' || $k == 'InternalId')
                        $v = "<a href='/netsuite/index/$type?id=$v'>$v</a>";

                    $smallfield = 'small-field';

                    switch($k){
                        case 'total':
                            switch($type){
                                case 'customerrefund':
                                case 'returnauthorization':
                                case 'itemreceipt':
                                case 'creditmemo':
                                    $v *= -1;
                                break;
                            }
                        break;
                        case 'createdfrom':
                            $v = str_replace(array('Authorization','Sales','Order','Quote','Cashsale','Return'),'',$v);
                        break;
                        case 'custbody_ozlink_shipping_service':
                            if(empty($v))
                                $v = 'Redstag';
                            else
                                $v = 'Kellyco '.$v;
                        case 'createddate':
                            $v = date('m/d/Y h:m A',strtotime($v));
                        case 'paymenteventupdatedby':
                        case 'custbodycsr':
                        case 'custbodycreatedby':
                            if(empty($v) && isset($d->data['custcurrep']))
                                $v = $d->data['custcurrep'];
                        case 'pnrefnum':
                        case 'otherrefnum':
                        case 'memo':
                            $smallfield = '';
                        break;
                    }
                    
                    if($k != 'custcurrep')
                        $html .= "<div class='field $smallfield $k'>$v</div>";
                }
                $html .= '<div class="clear"></div>';
                $superarray[strtotime($d->data['createddate'])] = $html;
            }
        }

        krsort($superarray);

        return implode('',$superarray);
    }

    public function addressTableHtml(){

        $addresses = $this->getTable('CUSTOMER_addressbook',array('CUSTOMER_InternalId'=>$this->id),
            array('label','defaultshipping','defaultbilling','isresidential','addressbookaddress_text'));

        $html = '';
        foreach($addresses as $a){
            foreach($a->data as $k=>$v) {
                if($v=='T')
                    $v = 'Yes';
                if($v=='F')
                    $v = 'No';

                $html .= "<div class='field'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function messagesTableHtml(){
        $messages = $this->getCUSTOMER_messagesTable(array('customer_InternalId'=>$this->id),
            array('messages_internalId_FK'),
            array('MESSAGE'=>array('condition'=>'InternalId=messages_internalId_FK',
                'columns'=>array('lastmodifieddate','author','subject','hasattachment'))
            ),
            0);

        $html = '';
        foreach($messages as $m){
            foreach($m->data as $k=>$v) {
                if(strtolower($k)=='messages_internalid_fk')
                    $v = "<a href='/netsuite/index/message?id=$v'>$v</a>";

                if($v=='T')
                    $v = 'Yes';
                if($v=='F')
                    $v = 'No';

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function notesTableHtml(){

        $notes = $this->getNOTE_customerTable(array('customer_InternalId_FK'=>$this->id),
            array('note_internalId'),
            array('NOTE'=>array('condition'=>'InternalId=note_internalId',
                'columns'=>array('notedate','author','title','note','direction'))
            ),
            0);

        $html = '';
        foreach($notes as $n){
            foreach($n->data as $k=>$v) {
                if(strtolower($k)=='note_internalid')
                    $v = "<a href='/netsuite/index/note?id=$v'>$v</a>";

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function filesTableHtml(){

        $files = $this->getCUSTOMER_fileTable(array('customer_InternalId'=>$this->id),
            array('name','folder','created','owner','documentsize','filetype'),null,0);

        $html = '';
        foreach($files as $f){
            $link = "<a href='/netsuite/{$f->data['folder']}/{$f->data['name']}'>{$f->data['name']}</a>";
            foreach($f->data as $k=>$v) {
                if($k=='name')
                    $v = $link;

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function casesTableHtml(){

        $cases = $this->getSUPPORTCASE_customerTable(array('customer_InternalId_FK'=>$this->id),
            array('supportcase_internalId'),
            array('SUPPORTCASE'=>array('condition'=>'InternalId=supportcase_internalId',
                'columns'=>array('issue','lastmodifieddate','status','assigned'))
            ),
            0);

        $html = '';
        foreach($cases as $c){
            foreach($c->data as $k=>$v) {
                if(strtolower($k)=='supportcase_internalid')
                    $v = "<a href='/netsuite/index/supportcase?id=$v'>$v</a>";

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }
}