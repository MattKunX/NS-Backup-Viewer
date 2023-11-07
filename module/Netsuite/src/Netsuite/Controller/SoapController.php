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
use Netsuite\Model\Soap\Api;

ini_set('max_execution_time',200);

class SoapController extends AbstractActionController
{
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

	public function getSearchAction(){

		$soap = new Api();
		$soap->getSearch();

		echo '<pre>';
		//print_r($data);
		echo '</pre>';

		return false;
	}

	public function getRecordAction(){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		return false;
	}
}

?>