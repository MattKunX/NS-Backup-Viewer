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

class CustomerrefundRecord extends Record
{
    public function relatedTableHtml($num){
        $transactions = array();

        $transactions['journalentry']['name'] = 'Journal Entry';
        $transactions['journalentry']['data'] = $this->getJOURNALENTRYTable(array('createdfrom'=>'Customer Refund #'.$num),
            array('InternalId','tranid','createddate','status','memo'),
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
        $messages = $this->getCASHSALE_messagesTable(array('cashsale_InternalId'=>$this->id),
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

    public function notesTableHtml($num){

        $notes = $this->getNOTETable(array('transaction'=>'Cash Sale #'.$num),
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

        $salesreps = $this->getTable('CASHSALE_salesteam',array('cashsale_InternalId'=>$this->id),
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

        $giftcerts = $this->getTable('CASHSALE_giftcertredemption',array('cashsale_InternalId'=>$this->id),
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