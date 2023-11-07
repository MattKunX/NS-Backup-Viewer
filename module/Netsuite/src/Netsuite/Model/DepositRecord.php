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

class DepositRecord extends Record
{
    public function paymentsTableHtml(){
        $items = $this->getTable('DEPOSIT_payment',array('DEPOSIT_InternalId'=>$this->id,'deposit'=>'T'),
            array('docdate','type','docnumber','memo','paymentmethod','refnum','pmturl','paymentamount'));

        $location = new Location();

        $html = '';
        foreach($items as $i){
            foreach($i->data as $k=>$v) {

                $class = 'small-field';
                if($k=='memo')
                    $class = 'note';

                $html .= "<div class='field $k $class'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function otherdepositsTableHtml(){
        $items = $this->getTable('DEPOSIT_other',array('DEPOSIT_InternalId'=>$this->id),
            array('memo','paymentmethod','refnum','amount'));

        $location = new Location();

        $html = '';
        foreach($items as $i){
            foreach($i->data as $k=>$v) {

                $class = 'small-field';
                if($k=='memo')
                    $class = 'note';

                $html .= "<div class='field $k $class'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function cashbackTableHtml(){
        $items = $this->getTable('DEPOSIT_cashback',array('DEPOSIT_InternalId'=>$this->id),
            array('account','memo','amount'));

        $location = new Location();

        $html = '';
        foreach($items as $i){
            foreach($i->data as $k=>$v) {

                $class = 'small-field';
                if($k=='memo')
                    $class = 'note';

                $html .= "<div class='field $k $class'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }
    public function notesTableHtml($tranid){

        $notes = $this->getNOTETable(array('transaction'=>'Deposit #'.$tranid),
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

}