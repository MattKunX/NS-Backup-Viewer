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

class InventoryitemRecord extends Record
{
    protected $related = null;

	public function pricesTableHtml(){
        
        $prices = array();
        // USD
        $prices[] = $this->getTable('INVENTORYITEM_price1',array('INVENTORYITEM_InternalId'=>$this->id),
            array('pricelevel','pricelevelname','price_1_','price_2_','price_3_','price_4_','price_5_'));
        /*
        // GBP
        $prices[] = $this->getTable('INVENTORYITEM_price2',array('INVENTORYITEM_InternalId'=>$this->id),
            array('pricelevel','pricelevelname','price_1_','price_2_','price_3_','price_4_','price_5_'));
        // CAD
        $prices[] = $this->getTable('INVENTORYITEM_price3',array('INVENTORYITEM_InternalId'=>$this->id),
            array('pricelevel','pricelevelname','price_1_','price_2_','price_3_','price_4_','price_5_'));
        // EUR
        $prices[] = $this->getTable('INVENTORYITEM_price4',array('INVENTORYITEM_InternalId'=>$this->id),
            array('pricelevel','pricelevelname','price_1_','price_2_','price_3_','price_4_','price_5_'));
        */
        $plevel = array();
        
        foreach($prices as $price){
            foreach($price as $p){
                $html = '';
                foreach($p->data as $k=>$v) {
                    if($k == 'pricelevel')
                        continue;

                    $html .= "<div class='field $k'>$v</div>";
                }
                $html .= '<div class="clear"></div>';
                
                if(isset($plevel[$p->data['pricelevel']]))
                    $plevel[$p->data['pricelevel']] .= $html;
                else
                    $plevel[$p->data['pricelevel']] = $html;
            }
        }

        $html = '';
        foreach($plevel as $pl){
            $html .= "<div>$pl</div>";
        }
        return $html;
    }

    public function inventoryTableHtml(){
        $inventory = $this->getTable('INVENTORYITEM_binnumber',array('INVENTORYITEM_InternalId'=>$this->id),
            array('binnumber_display','location_display'),null,0,array('order'=>'locationid'));

        $html = '';
        foreach($inventory as $i){
            foreach($i->data as $k=>$v) {
                $html .= "<div class='field $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }

    protected function relatedRecords($type){
        if($this->related && isset($this->related[$type]))
            return $this->related[$type];
        
        $this->related = array();
        
        switch ($type) {
            case 'purchase':
                $this->related['purchase'] = $purchase_orders = $this->getTable('PURCHASEORDER_item',array('item'=>$this->id),
                array('PURCHASEORDER_InternalId','item_display','location_display','quantity','quantitybilled','quantityreceived','rate','amount','vendorname','customer_display','ddistrib'));
                break;
        }
        
        return $this->related[$type];
    }

    public function purchaseTableHtml(){
        $records = $this->relatedRecords('purchase');

        $html = '';
        foreach($records as $r){
            foreach($r->data as $k=>$v) {
                
                $smallfield = 'small-field';
                switch ($k) {
                    case 'item_display':
                        continue 2;
                    case 'PURCHASEORDER_InternalId':
                        $v = "<a href='/netsuite/index/purchaseorder?id=$v'>{$r->data['item_display']}</a>";
                    case 'vendorname':
                        $smallfield = '';
                        break;
                }

                $html .= "<div class='field $smallfield $k'>$v</div>";
            }
            $html .= '<div class="clear"></div>';
        }
        return $html;
    }
}