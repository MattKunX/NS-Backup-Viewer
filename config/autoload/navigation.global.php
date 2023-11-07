<?php

return array(
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'controller' => 'Dashboard',
                'action'     => 'index',
                'resource'	  => 'Dashboard\Controller\Index',
                'privilege'  => 'index',
                'pages' => array(
                    array(
                        'label' => 'Curl', // 'Child #1',
                        'route' => 'dashboard',
                        'controller'=>'Dashboard',
                        'action' => 'curl',
                        'resource'	=> 'Dashboard\Controller\Curl',
                        'privilege'	=> 'curl',
                    ),
                    array(
                        'label' => 'Cart Inventory',
                        'route' => 'dashboard',
                        'controller'=>'Dashboard',
                        'action' => 'cart-inventory',
                        'resource' => 'Dashboard\Controller\cartInventory',
                        'privilege'    => 'magento',
                    ),
                ),
            ),
            array(
                'label' => 'Netsuite',
                'route' => 'netsuite',
                'controller' => 'index',
                'action' => 'index',
                'privilege' => 'netsuite',
                'pages' => array(
                    array(
                        'label' => 'Home', // 'Fake Child #1',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'index',
                        'privilege'    => 'netsuite',
                    ),
                    array(
                        'label' => 'Accounting',
                        'route' => 'netsuite/default',
                        'privilege' => 'netsuite',
                        'pages' => array(
                            array(
                                    'label' => 'Accounts',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'accounts',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Checks',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'checks',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Deposits',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'deposits',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Journals',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'journalentries',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Other Names',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'othernames',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Vendors',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'vendors',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Vendor Bills',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'vendorbills',
                                    'privilege'    => 'netsuite',
                            ),
                            array(
                                    'label' => 'Vendor Payments',
                                    'route' => 'netsuite/default',
                                    'controller'=>'index',
                                    'action' => 'vendorpayments',
                                    'privilege'    => 'netsuite',
                            ),
                        ),
                    ),
                    array(
                        'label' => 'Customers',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'customers',
                        'privilege'	=> 'netsuite',
                    ),
                    array(
                        'label' => 'Sales Orders',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'salesorders',
                        'privilege'	=> 'netsuite',
                    ),
                    array(
                        'label' => 'Cash Sales',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'cashsales',
                        'privilege'	=> 'netsuite',
                    ),
                    array(
                        'label' => 'Invoices',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'invoices',
                        'privilege'    => 'netsuite',
                    ),
                    array(
                        'label' => 'Credit Memos',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'creditmemos',
                        'privilege'    => 'netsuite',
                    ),
                    array(
                        'label' => 'Fulfillments',
                        'route' => 'netsuite/default',
                        'controller'=>'index',
                        'action' => 'fulfillments',
                        'privilege'    => 'netsuite',
                    ),
                ),
            ),
            array(
                'label' => 'Logout',
                'route' => 'application/default',
                'controller' => 'Auth',
                'action'     => 'logout',
                'resource'	  => 'CsnUser\Controller\Index',
            ),
            /*array(
                'label' => 'Login',
                'route' => 'login', 
                'controller' => 'Index',
                'action'     => 'login',
                'resource'	  => 'CsnUser\Controller\Index',
                'privilege'  => 'login',
            ),
            array(
                'label' => 'Registration',
                'route' => 'registration', 
                'controller' => 'Registration',
                'action'     => 'index',
                'resource'	  => 'CsnUser\Controller\Registration',
                'privilege'  => 'index',
                'title'	  => 'Registration Form'
            ),
            array(
                'label' => 'Forgotten Password',
                'route' => 'forgotten-password', 
                'controller' => 'Registration',
                'action'     => 'forgotten-password',
                'resource'	  => 'CsnUser\Controller\Registration',
                'privilege'  => 'forgotten-password'
            ),
            array(
                'label' => 'Change Password',
                'route' => 'changePassword', 
                'controller' => 'Registration',
                'action'     => 'change-password',
                'resource'	  => 'CsnUser\Controller\Registration',
                'privilege'  => 'changePassword'
            ),
            array(
                'label' => 'Logout',
                'route' => 'logout', 
                'controller' => 'Index',
                'action'     => 'logout',
                'resource'	  => 'CsnUser\Controller\Index',
                'privilege'  => 'logout'
            ),
            array(
                'label' => 'Zend',
                'uri'   => 'http://framework.zend.com/',
                'resource' => 'Zend',
                'privilege'	=>	'uri'
            ),*/
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),
);