<?php
/**
 * User: mattkun
 * Date: 3/16/2017
 */

namespace Netsuite\Model\Rest;

class Restlet
{
	const ns_account = '';
	const ns_login = '';
	const ns_pass = '';
	const resturl = 'https://rest.netsuite.com/app/site/hosting/restlet.nl?script=271&deploy=1';  // suitescript 1.0
	const resturl2 = 'https://rest.netsuite.com/app/site/hosting/restlet.nl?script=347&deploy=1'; // suitescript 2.0

	public function transactionSearchBasic(){
		$soapxml = '<soap:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:platformCommon="urn:common_2016_2.platform.webservices.netsuite.com" xmlns:platformCore="urn:core_2016_2.platform.webservices.netsuite.com" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:platformMsgs="urn:messages_2016_2.platform.webservices.netsuite.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<soap:Header>
				<platformMsgs:applicationInfo>
				    <platformMsgs:applicationId>0X0X0X0X-XXXX-0X0X-0X0X-000000000000</platformMsgs:applicationId>
				</platformMsgs:applicationInfo>
				<platformMsgs:passport>
				    <platformCore:email>'.self::ns_login.'</platformCore:email>
				    <platformCore:password>'.self::ns_pass.'</platformCore:password>
				    <platformCore:account>'.self::ns_account.'</platformCore:account>
				</platformMsgs:passport>
				<platformMsgs:searchPreferences>
				    <platformMsgs:pageSize>100</platformMsgs:pageSize>
				    <platformMsgs:disableSystemNotesForCustomFields>true</platformMsgs:disableSystemNotesForCustomFields>
				</platformMsgs:searchPreferences>
				<platformMsgs:preferences>
				    <platformMsgs:warningAsError>false</platformMsgs:warningAsError>
				    <platformMsgs:disableMandatoryCustomFieldValidation>false</platformMsgs:disableMandatoryCustomFieldValidation>
				    <platformMsgs:ignoreReadOnlyFields>true</platformMsgs:ignoreReadOnlyFields>
				</platformMsgs:preferences>
			</soap:Header>
			<soap:Body>
			<search xmlns="urn:messages_2016_2.platform.webservices.netsuite.com">
			    <searchRecord xsi:type="platformSearch:TransactionSearchAdvanced" xmlns:platformSearch="urn:sales_2016_2.transactions.webservices.netsuite.com">
			        <platformSearch:criteria>
			            <platformSearch:basic>
			                <platformCommon:memorized>
			                    <platformCore:searchValue>false</platformCore:searchValue>
			                </platformCommon:memorized>
			            </platformSearch:basic>
			        </platformSearch:criteria>
			    </searchRecord>
			</search>
			</soap:Body>
		</soap:Envelope>';
	}
	/* Script 271 - get saved search restlet*/
	public function getSavedSearch($search_id,$type,$filter=NULL){
		//$url = 'https://rest.netsuite.com/app/site/hosting/restlet.nl?script=271&deploy=1&type=giftcertificate&id=921&gc=918XSPHYA';
		$url = self::resturl2.'&type='.$type.'&id='.$search_id.$filter;
		$data = $this->nsCurl($url);
		return json_decode($data);
	}

	public function nsCurl($url){
		$ch = curl_init();
	 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_HTTPHEADER,array(
			'Authorization:NLAuth nlauth_account='.self::ns_account.', nlauth_email='.self::ns_login.', nlauth_signature='.self::ns_pass)); 
		curl_setopt($ch, CURLOPT_URL, $url);
	 
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
}

?>