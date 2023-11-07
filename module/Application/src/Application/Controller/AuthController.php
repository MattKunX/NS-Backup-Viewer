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
use Zend\Authentication\AuthenticationService;
// use Zend\Authentication\Adapter\Ldap as AuthAdapter;
use Zend\Authentication\Adapter\Digest as AuthAdapter; // for demo
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Config\Reader\Ini as ConfigReader;
use Zend\Config\Config;
use Zend\Debug\Debug;
use Zend\Log\Logger;
use Zend\Log\Writer;
use Zend\Session\Container;

class AuthController extends AbstractActionController
{
    public function indexAction()
    {
    	$username = $this->getRequest()->getPost('username');
		$password = $this->getRequest()->getPost('password');

		if(!empty($username) && !empty($password)){

			// if ($username == 'demo' && $password == 'demo') {
			// 	$acl = new Acl();
			// 	$acl->addRole(new Role('IT'));
			// 	$acl->addResource(new Resource('netsuite'));
			// 	$acl->allow('IT','netsuite');

			// 	$user = new Container('user');
			// 	$user->role = 'IT';
			// 	return $this->redirect()->toRoute(null,array('controller'=>'dashboard','action'=>'index'));
			// } else {
			// 	$this->flashMessenger()->addMessage("Incorrect username or password");
			// }

			/* ignore windows login verification for demo

	    	$configReader = new ConfigReader();
			$configData = $configReader->fromFile('/var/www/html/matrix/config/config.ini');
			$config = new Config($configData, true);
			$ldap_options = $config->production->ldap->toArray();
			*/

			// instantiate the authentication service
			$auth = new AuthenticationService();

			// Set up the authentication adapter
			//$adapter = new AuthAdapter($ldap_options,$username,$password);
			$adapter = new AuthAdapter('data/auth.txt','IT',$username,$password); // demo
			$result = $auth->authenticate($adapter);
			
			if($result->isValid()){
				if ($auth->hasIdentity()){

					/* for windows auth
					$search = $adapter->getLdap()->search('(samaccountname='.str_ireplace('DOMAIN\\', '',
															$auth->getIdentity()).')',
															null,
															\Zend\Ldap\Ldap::SEARCH_SCOPE_SUB,
															array('memberof')
														);

					$memberof = $search->getFirst()['memberof'];

				    // If member of AD group IT
				    if(stripos(implode($memberof),'CN=IT,')!==false){
				    	//TODO auth session hack for role storage
				    	$user = new Container('user');
				    	$user->role = 'IT';
				    	return $this->redirect()->toRoute(null,array('controller'=>'dashboard','action'=>'index'));
				    }
					*/

					// TODO Move
				    $acl = new Acl();
				    $acl->addRole(new Role('IT'));
					$acl->addResource(new Resource('netsuite'));
					$acl->allow('IT','netsuite');

				    return $this->redirect()->toRoute('netsuite',array('controller'=>'index','action'=>'index'));
				}
			}else{
				$msgs = $result->getMessages();
				$this->flashMessenger()->addMessage($msgs[0]);
			}

		}
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl());
    }

    public function logoutAction()
    {
    	$auth = new AuthenticationService();

		if($auth->hasIdentity())
			$auth->clearIdentity();

		return $this->redirect()->toUrl($this->getRequest()->getBaseUrl());
    }
}
