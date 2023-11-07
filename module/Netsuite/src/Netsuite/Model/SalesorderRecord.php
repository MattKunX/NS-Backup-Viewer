<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 7/18/2016
 * Time: 3:00 PM
 */

namespace Netsuite\Model;

use Netsuite\Model\Record;
use Netsuite\Model\Location;

class SalesorderRecord extends Record
{
    public function itemsTableHtml(){
        $items = $this->getTable('SALESORDER_item',array('SALESORDER_InternalId'=>$this->id),
            array('item','itemtype','item_display','quantity','rate','location','quantityfulfilled','quantitybackordered'));

        $location = new Location();

        $html = '';
        foreach($items as $i){
            foreach($i->data as $k=>$v) {

                if($k=='item'){
                    $record = 'inventoryitem';
                    switch ($i->data['itemtype']) {
                        case 'Assembly':
                            $record = 'assemblyitem';
                            break;
                        case 'Discount':
                            $record = 'discountitem';
                            break;
                        case 'Group':
                            $record = 'itemgroup';
                            break;
                        case 'Kit':
                            $record = 'kititem';
                            break;
                        case 'Markup':
                            $record = 'markupitem';
                            break;
                        case 'Service':
                            $record = 'serviceitem';
                            break;
                        case 'Giftcertificate':
                            $record = 'giftcertificateitem';
                            break;
                    }

                    $v = "<a href='/netsuite/index/$record?id=$v'>$v</a>";
                }

                $class = 'small-field';
                if($k=='item_display')
                    $class = '';

                if($k=='location')
                    $v = $location->getNameFromId($v);

                $html .= "<div class='field $k $class'>$v</div>";

                if($k=='rate'){
                    if(is_numeric($i->data['quantity']) && is_numeric($i->data['rate']))
                        $rate = $i->data['quantity']*$i->data['rate'];
                    else
                        $rate = $i->data['quantity'].' x '.$i->data['rate'];

                    $html .= '<div class="field small-field">'.$rate.'</div>';
                }
            }

            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function relatedTableHtml($sonum){
        $transactions = array();

        $transactions['itemfulfillment']['name'] = 'Fulfillment';
        $transactions['itemfulfillment']['data'] = $this->getITEMFULFILLMENTTable(array('orderid'=>$this->id),
            array('InternalId','tranid','createddate','createdfrom','shippingcost','custbodycreatedby','status','memo'),
            null,
            0);

        $transactions['cashsale']['name'] = 'Cash Sale';
        $transactions['cashsale']['data'] = $this->getCASHSALETable(array('createdfrom'=>'Sales Order #'.$sonum),
            array('InternalId','tranid','createddate','otherrefnum','total','custbodycsr','status','memo'),
            null,
            0);

        $transactions['invoice']['name'] = 'Invoice';
        $transactions['invoice']['data'] = $this->getINVOICETable(array('createdfrom'=>'Sales Order #'.$sonum),
            array('InternalId','tranid','createddate','otherrefnum','total','custbodycsr','status','memo'),
            null,
            0);

        $transactions['returnauthorization']['name'] = 'Return';
        $transactions['returnauthorization']['data'] = $this->getRETURNAUTHORIZATIONTable(array('createdfrom'=>'Sales Order #'.$sonum),
            array('InternalId','tranid','createddate','otherrefnum','total','salesrep','status','memo'),
            null,
            0);


        $html = '';
        foreach($transactions as $type => $t){
            foreach($t['data'] as $d) {
                $html .= '<div class="field">'.$t['name'].'</div>';
                foreach($d->data as $k=>$v) {
                    if($k=='InternalId')
                        $v = "<a href='/netsuite/index/$type?id=$v'>$v</a>";

                    if($k == 'total' && $type=='returnauthorization')
                        $v *= -1;

                    if($k=='createdfrom')
                        $v = str_replace('Authorization','',$v);

                    $html .= "<div class='field $k'>$v</div>";
                }
                $html .= '<div class="clear"></div>';
            }
        }
        return $html;
    }

    public function messagesTableHtml(){
        $messages = $this->getSALESORDER_messagesTable(array('salesorder_InternalId'=>$this->id),
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

    public function notesTableHtml($sonum){

        $notes = $this->getNOTETable(array('transaction'=>'Sales Order #'.$sonum),
            array('InternalId','notedate','author','title','note','direction'),
            null,
            0);

        $html = '';
        foreach($notes as $n){
            foreach($n->data as $k=>$v) {
                if($k == 'InternalId')
                    $v = "<a href='/netsuite/index/note?id=$v'>$v</a>";

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function salesteamTableHtml(){

        $salesreps = $this->getTable('SALESORDER_salesteam',array('salesorder_InternalId'=>$this->id),
            array('employee','contribution'),
            array('EMPLOYEE'=>array('condition'=>'InternalId=employee',
                'columns'=>array('entityid'))
            ),
            0);

        $html = '';
        foreach($salesreps as $sr){
            foreach($sr->data as $k=>$v) {
                if($k == 'employee')
                    $v = "<a href='/netsuite/index/employee?id=$v'>$v</a>";
                if($v=='USA')
                    $v = 'Website (USA)';

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function giftcertTableHtml(){

        $giftcerts = $this->getTable('SALESORDER_giftcertredemption',array('salesorder_InternalId'=>$this->id),
            array('authcode','authcodeapplied'),
            array('GIFTCERTIFICATE'=>array('condition'=>'InternalId=authcode',
                'columns'=>array('giftcertcode'))
            ),
            0);

        $html = '';
        foreach($giftcerts as $gc){
            foreach($gc->data as $k=>$v) {
                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

}