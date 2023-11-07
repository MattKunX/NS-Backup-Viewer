<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TasksController extends AbstractActionController
{
    public function indexAction()
    {
    	echo 'hello';
        return false;
    }

    public function mattAction()
    {
    	$uid = '3d0cf4fd-ab66-4092-a8e4-69fc7db364d0';
    	$passkey = 'c058cac1-2d03-4054-9fd0-029015dd63bb';
    	$url = 'https://oldgods.net/habitrpg/habitrpg_user_data_display.html?userId='.$uid.'&apiToken='.$passkey;
    	//https://github.com/Alys/tools-for-habitrpg
    	/*
    	$data = array('userId' => $uid, 'apiToken' => $passkey);
    	$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
    	echo file_get_contents($url,false,$context);
		*/
    	return new ViewModel(array('url'=>$url,'uid'=>$uid,'passkey'=>$passkey));
    }
}
