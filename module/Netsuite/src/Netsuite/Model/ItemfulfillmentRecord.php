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

class ItemfulfillmentRecord extends Record
{
    public function itemsTableHtml(){
        $items = $this->getTable('ITEMFULFILLMENT_item',array('ITEMFULFILLMENT_InternalId'=>$this->id),
            array('item','itemname','itemdescription','location','binnumbers','onhand','itemquantity','custcol_ozlink_serial_number','itemtype'));

        $location = new Location();

        $html = '';
        foreach($items as $i){
            foreach($i->data as $k=>$v) {

                if($k=='item')
                    $v = "<a href='/netsuite/index/inventoryitem?id=$v'>$v</a>";

                $class = 'small-field';
                if($k=='itemdescription')
                    $class = 'item_display';

                if($k=='location')
                    $v = $location->getNameFromId($v);

                $html .= "<div class='field $k $class'>$v</div>";
            }

            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function packagesTableHtml(){
        $packages = $this->getTable('ITEMFULFILLMENT_packagefedex',array('ITEMFULFILLMENT_InternalId'=>$this->id),
            array('packageweightfedex','packagetrackingnumberfedex'));
        
        $html = '';
        foreach($packages as $p){
            foreach($p->data as $k=>$v) {
                if($k == 'packagetrackingnumberfedex')
                    $v = "<a href='https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=$v' target='_blank'>$v</a>";

                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    public function messagesTableHtml(){
        $messages = $this->getITEMFULFILLMENT_messagesTable(array('ITEMFULFILLMENT_InternalId'=>$this->id),
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

    public function notesTableHtml($itmfnum){

        $notes = $this->getNOTETable(array('transaction'=>'Item Fulfillment #'.$itmfnum),
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