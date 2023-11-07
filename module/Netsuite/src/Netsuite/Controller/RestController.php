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
use Netsuite\Model\Rest\Restlet;

class RestController extends AbstractActionController
{
	private $ajax = false;

	private $ns_account = '';
	private $ns_login = '';
	private $ns_pass = '';
	private $resturl = 'https://rest.netsuite.com/app/site/hosting/restlet.nl';

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
			return $this->redirect()->toUrl($this->getRequest()->getBaseUrl());

        return parent::onDispatch($e);
    }

	/*** RESTlet Functions ***/
	public function getSearchAction(){
		$rest = new Restlet();
		$data = $rest->getSavedSearch(0,null);
		echo '<pre>';
		print_r($data);
		echo '</pre>';

		return false;
	}

	public function getRestSearchAction(){
		$this->_helper->viewRenderer->setNoRender(); 

		// giftcerts 921 // so imported 864-transaction  // item (inventory) 923
		$decoded = $this->getSavedSearch(921,'giftcertificate','&gc=918XSPHYA');
		
		echo '<pre>';
		print_r($decoded);
		echo '</pre>';
	}

	public function getRecordAction(){
		// inventoryitem 7388 // gift 2052
		//$url = 'https://rest.netsuite.com/app/site/hosting/restlet.nl?script=280&deploy=1&type=giftcertificate&id=2052';
		$url = 'https://rest.netsuite.com/app/site/hosting/restlet.nl?script=280&deploy=1&type=inventoryitem&id=7388&json=1';

		$data = json_decode(Restlet::nsCurl($url));
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		return false;
	}

	public function salesTodayAction(){
		$this->_helper->viewRenderer->setNoRender(); 

		// Web Sales Orders today
		$sales = $this->getSavedSearch(959,'transaction','&date1=today');
		
		$revenue = 0;
		$count = 0;
		if($sales){
			foreach($sales as $s){
				$revenue += $s->columns->amount;
				$count++;
			}
		}
		echo '<h3>Today</h3><div class="seperator5px"></div><div style="font-size:38px;"">Orders: ';
		echo "<span style='color:darkred;'>$count</span>";
		echo ' Revenue: <span style="color:darkred;">$'.number_format($revenue,2).'</span></div>';

		// Yesterday
		$sales = $this->getSavedSearch(959,'transaction','&date1=yesterday');
		
		$revenue = 0;
		$count = 0;
		if($sales){
			foreach($sales as $s){
				$revenue += $s->columns->amount;
				$count++;
			}
		}

		$interval = isset($_GET['interval'])?$_GET['interval']:0;

		echo '<br><br><h3>Yesterday</h3><div class="seperator5px"></div><div style="font-size:38px;"">Orders: ';
		echo "<span style='color:darkred;'>$count</span>";
		echo ' Revenue: <span style="color:darkred;">$'.number_format($revenue,2).'</span></div>';

		echo '<style>#loadbar{background-color:#0F0;height:100%; width:0%;}</style>';
		echo '<div style="width:100%;text-align:center;">
					<div id="reftxt"></div>
					<div style="display:inline-block;height:10px;width:300px;border:1px solid black;">
						<div id="loadbar"></div>
					</div>
					<div>
						<form action="" method="GET">
							<select id="interval" name="interval">
								<option value="15000">15 sec</option>
								<option value="30000">30 sec</option>
								<option value="60000">1 min</option>
								<option value="300000">5 min</option>
								<option value="900000">15 min</option>
								<option value="1800000">30 min</option>
								<option value="3600000">1 hour</option>
								<option value="14400000">4 hours</option>
								<option value="86400000">1 day</option>
							</select>
							<input id="autobtn" type="submit" value="Enable Auto Refresh"/>
							<a href=\'/COMPANY/netsuite-rest/sales-today\'>Disable</a>
						</form>
						<button style="font-size:18px;margin:5px;padding:5px;" onclick="javascript:document.location.reload()">Refresh Now</button>
					</div>
			</div>';

		if($interval){
			echo "<script>
					
					// time in milliseconds
					var interval = $interval; //30000;
					var cur_time = 0;
					var update_speed = 1000;

					$('#interval').val($interval);
					$('#autobtn').val('Change Interval');
					$('#reftxt').append('Refreshing in '+$('#interval option:selected').text());

					var timer = setInterval(function(){
									cur_time += update_speed; 
									var pct = (cur_time / interval) * 100;
									$('#loadbar').css('width',pct+'%');

									if(pct >= 100){
										clearInterval(timer);
										document.location.reload();
									}

								}, update_speed);
				</script>";
		}

	}

	public function shippingTodayAction(){

		$pending_orders = $this->getSavedSearch(990,'transaction'); // Sales Orders Pending Fulfillment
		$picked = $this->getSavedSearch(989,'transaction'); // Current Orders Picked Today
		$shipped = $this->getSavedSearch(988,'transaction'); // All Packages Shipped Today
		$shipped_yesterday = $this->getSavedSearch(992,'transaction'); // All Packages Shipped Yesterday

		$chartarray = '';
		$totalshipped = 0;
		foreach($shipped as $k => $a){
			if(isset($a->columns)){
				$totalshipped += $a->columns->packagecount;
				
				if(!empty($chartarray))
					$chartarray .= ',';
				$chartarray .= "['{$a->columns->name->name}', {$a->columns->packagecount}]";
			}
		}

		$totalshipped_y = 0;
		if($shipped_yesterday){
			foreach($shipped_yesterday as $k => $a){
				if(isset($a->columns)){
					$totalshipped_y += $a->columns->packagecount;
				}
			}
			
		}
		$this->view->shipped_yesterday = $totalshipped_y;
		
		$this->view->pending = count($pending_orders);
		$this->view->picked = count($picked);
		$this->view->shipped = $totalshipped;
		$this->view->chartdata = $chartarray;

	}

	public function NStoCSVarray($NSarray,&$csv,$subsidairy = 'Parent Company'){
		foreach($NSarray as $l){
			
			if(!empty($l->columns->email) && $l->columns->subsidiarynohierarchy->name == $subsidairy){
				$csv[] = array( $l->columns->email,
								$l->columns->firstname,
								@$l->columns->lastname,
								$l->columns->subsidiarynohierarchy->name,
								@$l->columns->lastorderdate,
								@$l->columns->billaddress1,
								@$l->columns->billaddress2,
								@$l->columns->billaddress3,
								@$l->columns->billcity,
								@$l->columns->billstate->name,
								@$l->columns->billzipcode,
								@$l->columns->entityid,
								@$l->columns->datecreated,
								@$l->columns->globalsubscriptionstatus->name,
								@$l->columns->leadsource->name,
								@$l->columns->phone,
								@$l->columns->salesrep->name,
								@$l->columns->entitystatus->name);
			}
		}
	}

	public function silverpopAction(){
		$this->_helper->viewRenderer->setNoRender();

		// CSV headers
		$csv = array(array('email',
							'first_name',
							'last_name',
							'subsidiary',
							'last_sale_date',
							'billing_address_1',
							'billing_address_2',
							'billing_address_3',
							'billing_city',
							'billing_state',
							'billing_zip',
							'customer_id',
							'date_created',
							'global_subscription_status',
							'lead_source',
							'phone',
							'sales_rep',
							'status'));
		
		// Leads Today From Netsuite
		$leads = $this->getSavedSearch(972,'customer');
		$this->NStoCSVarray($leads,$csv);
		
		// Customers Last Ordered Today
		$leads = $this->getSavedSearch(975,'customer');
		$this->NStoCSVarray($leads,$csv);


		$fname = 'netsuite.csv';
		$csvfile = $_SERVER['DOCUMENT_ROOT'].'/uploads/silverpop/'.$fname;

		$h = fopen($csvfile,'w');
		foreach($csv as $fields)
			fputcsv($h,$fields);
		fclose($h);

		// Send CSV to Silverpop
		$sp = new Silverpop();
		if($sp->getJSessionId()){
			$ssh = new SSH('transfer1.silverpop.com',$sp->getAPIlogin(),$sp->getAPIpassword());
			$mapfile = 'mappings.xml';
			$ssh->uploadFile($csvfile,'/upload/'.$fname);
			$ssh->uploadFile($_SERVER['DOCUMENT_ROOT'].'/uploads/silverpop/'.$mapfile,'/upload/'.$mapfile);

			$xml = '<Envelope>
						<Body>
							<ImportList>
								<MAP_FILE>'.$mapfile.'</MAP_FILE>
								<SOURCE_FILE>'.$fname.'</SOURCE_FILE>
							</ImportList>
						</Body>
					</Envelope>';
			$sp->sendRequest($xml);
		}
	}

	private function NStoCMarray($NSarray,&$CMarray,$subsidairy = 'Parent Company'){

		foreach($NSarray as $l){
			
			if(!empty($l->columns->email) && $l->columns->subsidiarynohierarchy->name == $subsidairy){
				
				$upload = 'Intranet';
				if($subsidairy != 'Parent Company')
					$upload = 'Intranet MDS';

				$lastorderdate = '';
				if(isset($l->columns->lastorderdate))
					$lastorderdate = date('Y-m-d',strtotime($l->columns->lastorderdate));

				$datecreated = '';
				if(isset($l->columns->datecreated))
					$datecreated = date('Y-m-d',strtotime($l->columns->datecreated));

				$CMarray[] = array(
							    'EmailAddress' => $l->columns->email,
							    'Name' => $l->columns->firstname.' '.@$l->columns->lastname,
							    'CustomFields' => array(
								        array(
								            'Key' => 'City',
								            'Value' => @$l->columns->billcity
								        ),
								        array(
								            'Key' => 'State',
								            'Value' => @$l->columns->billstate->name
								        ),
								        array(
								            'Key' => 'PostalCode',
								            'Value' => @$l->columns->billzipcode,
								        ),
								        array(
								            'Key' => 'optin_source',
								            'Value' => @$l->columns->leadsource->name
								        ),
								        array(
								            'Key' => 'billing_address_1',
								            'Value' => @$l->columns->billaddress1
								        ),
								        array(
								            'Key' => 'billing_address_2',
								            'Value' => @$l->columns->billaddress2
								        ),
								        array(
								            'Key' => 'billing_address_3',
								            'Value' => @$l->columns->billaddress3
								        ),
								        array(
								            'Key' => 'sales_rep',
								            'Value' => @$l->columns->salesrep->name
								        ),
								        array(
								            'Key' => 'last_order_date',
								            'Value' => $lastorderdate
								        ),
								        array(
								            'Key' => 'date_created',
								            'Value' => $datecreated
								        ),
								        array(
								            'Key' => 'Phone',
								            'Value' => @$l->columns->phone
								        ),
								        array(
								        	'Key' => 'api_source',
								        	'Value' => $upload
								        )
						            )
							);
			}
		}

	}

	public function campaignMonitorAction(){
		$this->_helper->viewRenderer->setNoRender(); 
		
		$Ksubscribers = array();
		$MDsubscribers = array();

		// Leads Today From Netsuite
		$leads = $this->getSavedSearch(972,'customer');
		$this->NStoCMarray($leads,$Ksubscribers);
		$this->NStoCMarray($leads,$MDsubscribers,'COMPANY2.com');

		// Customers Last Ordered Today
		$leads = $this->getSavedSearch(975,'customer');
		$this->NStoCMarray($leads,$Ksubscribers);
		$this->NStoCMarray($leads,$MDsubscribers,'COMPANY2.com');

		
		echo '<pre>'; 
		print_r($Ksubscribers);
		print_r($MDsubscribers); 
		echo '</pre>';
		

		$cm = new CampaignMonitor();
		$cm->import($Ksubscribers);
		echo '<h2>COMPANY1 Import</h2>'.$cm->get_response();

		$cm = new CampaignMonitor('1abed8cb41aad10cb41c78f773deaf19');
		$cm->import($MDsubscribers);
		echo '<h2>COMPANY2 Import</h2>'.$cm->get_response();
	}

	public function updateOrdersImportedAction(){
		$this->_helper->viewRenderer->setNoRender(); 

		$searchData = $this->getSavedSearch(793,'transaction'); // last hour 793, last 24hr: 864

		// Update Live Database
		if(is_array($searchData)){
			$orders_db = new DbTable_Orders();
			foreach($searchData as $r){
					$order_id = substr($r->columns->custbody_intranet_link,45);
					echo "{$r->id} : {$r->columns->tranid} - $order_id : ";
					echo $orders_db->updateOrderStatus($order_id,12,array('tranId'=>$r->columns->tranid,'internalId'=>$r->id),true);
					echo '<br/>';
			}
		}

	}

	/* Script 271 - get saved search restlet*/
	private function getSavedSearch($search_id,$type,$filter=NULL){
		//$url = 'https://rest.netsuite.com/app/site/hosting/restlet.nl?script=271&deploy=1&type=giftcertificate&id=921&gc=918XSPHYA';
		$url = $this->resturl.'?script=271&deploy=1&type='.$type.'&id='.$search_id.$filter;
		$data = $this->nsCurl($url);
		return json_decode($data);
	}

	private function nsCurl($url){
		$ch = curl_init();
	 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_HTTPHEADER,array(
			'Authorization:NLAuth nlauth_account='.$this->ns_account.', nlauth_email='.$this->ns_login.', nlauth_signature='.$this->ns_pass)); 
		curl_setopt($ch, CURLOPT_URL, $url);
	 
		$data = curl_exec($ch);
		curl_close($ch);

		if(substr($data,0,10) == 'error code')
			mail('mattkun@DOMAIN.com','NS Rest Error',$data); 

		return $data;
	}
}

?>