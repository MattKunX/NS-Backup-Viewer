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

class PurchaseorderRecord extends Record
{
    public function itemsTableHtml(){
        $items = $this->getTable('PURCHASEORDER_item',array('PURCHASEORDER_InternalId'=>$this->id),
            array('item','itemtype','vendorname','quantityreceived','quantitybilled','quantity','item_display','rate','location'));

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

                if($k=='rate')
                    $html .= '<div class="field small-field">'.$i->data['quantity']*$i->data['rate'].'</div>';
            }

            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    // Related Records
    public function linksTableHtml(){
        $links = $this->getTable('PURCHASEORDER_links',array('PURCHASEORDER_InternalId'=>$this->id),
            array('id','trandate','type','tranid','status','total'));

        $html = '';
        foreach($links as $i){
            foreach($i->data as $k=>$v) {

                if($k=='id') continue;
                if($k=='trandate'){
                    $type = array('Bill'=>'vendorbill','Item Receipt'=>'itemreceipt');
                    $v = "<a href='/netsuite/index/{$type[$i->data['type']]}?id={$i->data['id']}'>$v</a>";
                }


                $html .= "<div class='field $k small-field'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function messagesTableHtml(){
        $messages = $this->getPURCHASEORDER_messagesTable(array('purchaseorder_InternalId'=>$this->id),
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

    public function notesTableHtml($tranid){

        $notes = $this->getNOTETable(array('transaction'=>'Purchase Order #'.$tranid),
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