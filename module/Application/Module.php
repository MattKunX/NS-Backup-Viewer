<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Application\Model\Mproduct;
use Application\Model\MproductTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Register a render event
        $app = $e->getParam('application');
        $app->getEventManager()->attach('render', array($this, 'setLayoutTitle'));
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
                'Application\Model\MproductTable' =>  function($sm) {
                    $tableGateway = $sm->get('MproductTableGateway');
                    $table = new MproductTable($tableGateway);
                    return $table;
                },
                'MproductTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('matrix');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Mproduct());
                    return new TableGateway('catalog_product_flat_1', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\LiveMproductTable' =>  function($sm) {
                    $tableGateway = $sm->get('LiveMproductTableGateway');
                    $table = new MproductTable($tableGateway);
                    return $table;
                },
                'LiveMproductTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('magentodb');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Mproduct(true));
                    return new TableGateway('catalog_product_flat_1', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }

    /**
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function setLayoutTitle($e)
    {
        $action = 'None';
        $controller = 'None';
        $matches    = $e->getRouteMatch();
        if($matches){
            $action     = ucfirst($matches->getParam('action'));
            $controller = $matches->getParam('controller');
        }
        $module     = __NAMESPACE__;
        $siteName   = 'Kellyco Matrix';

        // Getting the view helper manager from the application service manager
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');

        // Getting the headTitle helper from the view helper manager
        $headTitleHelper   = $viewHelperManager->get('headTitle');

        // Setting a separator string for segments
        $headTitleHelper->setSeparator(' - ');

        // Setting the action, controller, module and site name as title segments
        $headTitleHelper->append($action);
        //$headTitleHelper->append($controller);
        //$headTitleHelper->append($module);
        $headTitleHelper->append($siteName);
    }
}
