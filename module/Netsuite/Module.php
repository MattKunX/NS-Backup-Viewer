<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Netsuite;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Netsuite\Model\Customer;
use Netsuite\Model\CustomersTable;

class Module
{
    private $event;
    private $em;

    public function onBootstrap(MvcEvent $e)
    {
        $this->event = $e;
        $this->em = $e->getApplication()->getEventManager();
        
        // Register a render event
        //$app = $e->getParam('application');
        //$app->getEventManager()->attach('render', array($this, 'setLayoutTitle'));

        // didn't work
        //$this->em->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER_ERROR,array($this,'renderError'));

        //catch errors to rewrite E_Notices
        set_error_handler(array($this,'noticeHandler'),E_NOTICE);

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($this->em);
    }

    public function noticeHandler($no,$str,$file,$line,$context){
       
        if(error_reporting()){
            $actionName = $this->event->getRouteMatch()->getParam('action');
            if(strpos($file, $actionName) === false)
                $file = str_replace(array('/var/www/html/matrix/module/Netsuite/view/netsuite','/index/'), '', $file);
            else
                $file = '';
            $str = '<i>'.str_replace(':',':</i>',$str);
            echo "$str $file L:$line";
        }
        return;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Netsuite\Model\CustomersTable' =>  function($sm) {
                    $tableGateway = $sm->get('CustomersTableGateway');
                    $table = new CustomersTable($tableGateway);
                    return $table;
                },
                'CustomersTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('netsuite');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Customer());
                    return new TableGateway('CUSTOMER', $dbAdapter, null, $resultSetPrototype);
                },
                /*'Application\Model\LiveMproductTable' =>  function($sm) {
                    $tableGateway = $sm->get('LiveMproductTableGateway');
                    $table = new MproductTable($tableGateway);
                    return $table;
                },
                'LiveMproductTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('magentodb');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Mproduct(true));
                    return new TableGateway('catalog_product_flat_1', $dbAdapter, null, $resultSetPrototype);
                },*/
            ),
        );
    }

    /**
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function setLayoutTitle($e)
    {
        $matches    = $e->getRouteMatch();
        $action     = ucfirst($matches->getParam('action'));
        $controller = $matches->getParam('controller');
        $module     = __NAMESPACE__;
        $siteName   = 'Netsuite Backup Viewer';

        // Getting the view helper manager from the application service manager
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');

        // Getting the headTitle helper from the view helper manager
        $headTitleHelper   = $viewHelperManager->get('headTitle');

        // Setting a separator string for segments
        $headTitleHelper->setSeparator(' - ');

        // Setting the action, controller, module and site name as title segments
        $headTitleHelper->append($action);
        //$headTitleHelper->append($controller);
        $headTitleHelper->append($module);
        $headTitleHelper->append($siteName);
    }
}
