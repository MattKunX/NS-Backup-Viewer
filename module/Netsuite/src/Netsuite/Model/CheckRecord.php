<?php
/**
 * Created by SublimeText.
 * User: mattkun
 * Date: 12/14/2016
 * Time: 12:27 PM
 */

namespace Netsuite\Model;

use Netsuite\Model\Record;
use Netsuite\Model\Location;

class CheckRecord extends Record
{
    public function linesTableHtml(){
        $items = $this->getTable('JOURNALENTRY_line',array('JOURNALENTRY_InternalId'=>$this->id),
            array('account','account_display','entity_display','debit','credit','memo','department_display','class','location','custcol_mkt_exp','custcol12','custcol13','taxcode_display','taxrate1','tax1amt','tax1acct','grossamt'));

        $location = new Location();

        $html = '';
        foreach($items as $i){
            foreach($i->data as $k=>$v) {

                if($k=='account')
                    $v = "I don't know!";//continue;

                if($k=='account_display')
                    $v = "<a href='/netsuite/index/account?id={$i->data['account']}'>$v</a>";

                $class = 'small-field';
                if($k=='itemdescription')
                    $class = 'item_display';

                if($k=='location')
                    $v = $location->getNameFromId($v);

                $html .= "<div class='field $k $class'>$v</div>";
            }
            $html .= '<div class="field small-field">Link!</div>';
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function notesTableHtml($tranid){

        $notes = $this->getNOTETable(array('transaction'=>'Journal #'.$tranid),
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

    // Related Records
    public function linksTableHtml(){
        /* $transactions = array();

        $transactions['customerpayment']['name'] = 'Payment';
        $transactions['customerpayment']['data'] = $this->getCUSTOMERPAYMENTTable(array('tranid'=>$tranid),
            array('tranid','createddate','pnrefnum','total','postingperiod','status','memo'),
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
        */

        $links = $this->getTable('JOURNALENTRY_links',array('JOURNALENTRY_InternalId'=>$this->id),
            array('id','trandate','type','tranid','status','total'));

        $html = '';
        foreach($links as $i){
            foreach($i->data as $k=>$v) {

                if($k=='id') continue;
                if($k=='trandate')
                    $v = "<a href='/netsuite/index/{$i->data['type']}?id={$i->data['id']}'>$v</a>";


                $html .= "<div class='field $k small-field'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

}