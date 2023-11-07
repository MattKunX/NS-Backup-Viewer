<?php
/**
 * User: mattkun
 * Date: 3/22/2017
 * XSD: https://webservices.netsuite.com/xsd/platform/v2016_2_0/core.xsd
 * XSD: https://webservices.netsuite.com/xsd/setup/v2016_2_0/customization.xsd
 * XSD: https://webservices.netsuite.com/xsd/platform/v2016_2_0/coreTypes.xsd
 */

namespace Netsuite\Model\Soap;
use Netsuite\Model\Debug;

define('NS_ENDPOINT', '2016_2');
define('NS_HOST', 'https://webservices.netsuite.com');
define('NS_EMAIL', '');
define('NS_PASSWORD', '');
define('NS_ROLE', '');
define('NS_ACCOUNT', '');
define('NS_APPID', '');

include_once('external/NetSuiteService.php');

class Api
{
	public function getSearch(){
		$service = new \NetSuiteService();

		$service->setSearchPreferences(false, 10);

		$SearchField = new \SearchStringField();
		$SearchField->operator = "is";
		$SearchField->searchValue = "1649";
		
		

		$sv = new \RecordRef();
		//$sv->type = 'creditcardcharge';
		$sv->internalId = 184431;
		//$sv->tranid = 'SO1100172';
		$sv->type = 'salesOrder';

		$sf2 = new \SearchMultiSelectField();
		$sf2->operator = "anyOf";
		$sf2->searchValue = $sv;

		$search = new \TransactionSearchBasic();
		//$search->transactionNumber = $SearchField;
		$search->internalId = $sf2;
		//$search->type = $sf2;

		$request = new \SearchRequest();
		$request->searchRecord = $search;
		//echo '<pre>';
		//print_r($request);
		//echo '</pre>'; 
		$searchResponse = $service->search($request);

		/*
		$request = new \asyncSearchRequest();
		$request->searchRecord = $search;
		$searchResponse = $service->asyncSearch($request);
		*/

		if ($searchResponse->searchResult->status->isSuccess) {
		    //echo "SEARCH SUCCESS, records found: " . $searchResponse->searchResult->totalRecords;
		    foreach($searchResponse->searchResult as $sr){
		    	echo '<pre>';
		    	print_r($sr);
		    	echo '</pre>';
		    }
		} else {
		    echo "SEARCH ERROR";
		    Debug::dump($searchResponse);
		}
	}
}