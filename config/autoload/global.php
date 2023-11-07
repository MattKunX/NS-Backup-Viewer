<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
   'db' => array(
   		'adapters' => array(
   			'netsuite' => array(
				'driver'         => 'Pdo',
				'dsn'            => 'mysql:dbname=netsuite;host=localhost',
				'driver_options' => array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
				)
			),
		    'magentodb' => array(
		         'driver'         => 'Pdo',
		         'dsn'            => 'mysql:dbname=catalog;host=172.16.0.157',
		         'driver_options' => array(
		             PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
		        ),
		    ),
		)
	),
    'service_manager' => array(
         // Primary Adapter
         'factories' => array(
             'Zend\Db\Adapter\Adapter'
                     => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
        // allow adapters to be called by $sm->get('name')
        'abstract_factories' => array(
        	'Zend\Db\Adapter\AdapterAbstractServiceFactory'
        )
	),
	'view_manager' => [
		'base_path' => '/ns-backup-viewer'
	],

);
