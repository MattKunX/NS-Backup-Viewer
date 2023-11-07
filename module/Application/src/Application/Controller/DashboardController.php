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
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;
use Application\Model\Mproduct;
use Application\Form\MproductFilterForm;
use Application\Form\MproductExportForm;

class DashboardController extends AbstractActionController
{
	private $user;

	protected $MproductTable;
	protected $LiveMproductTable;
	protected $products;
	protected $productParams;
	protected $categories;

	public function __construct(){
		$this->productParams = get_object_vars(new Mproduct);
	}

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
            return $this->redirect()->toRoute(null,array('controller'=>'auth','action'=>'index'));
        
        $user_session = new \Zend\Session\Container('user');
        if($user_session->role != 'IT')
        	return $this->redirect()->toRoute('netsuite/default',array('controller'=>'index','action'=>'denied'));
        	
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        return new ViewModel(array(
        							'user'=>ucfirst(str_ireplace('kellyco\\','',$this->user))
        					));
    }

    public function cartInventoryAction()
    {
    	$products = null;
    	$filterForm = new MproductFilterForm();
    	
    	$request = $this->getRequest();
	    if($request->isGet()){
			$filterForm->setData($request->getQuery());
			$this->getProducts($request);
		}else{
			$this->getProducts();
		}

    	$user = str_ireplace('kellyco\\','',$this->user);
        return new ViewModel(array(
        							'user'=>$user,
        							'products'=>$this->products,
									'categories'=>$this->categories,
        							'filterform' => $filterForm,
        							'exportform' => new MproductExportForm($this->productParams)
        					));
    }

    public function exportAction(){
    	$request = $this->getRequest();
	    if($request->isGet() || $request->isPost()){
			$this->getProducts($request);
			
			// for converting id functions =/
			$filterForm = new MproductFilterForm();

			ob_start();
			$out = fopen('php://output', 'w');
			foreach($this->products as $product){
				$product = (array) $product;
				foreach($request->getPost() as $field => $export){
					if(!$export){
						unset($product[$field]);
					}else{
						switch($field){
							case 'manufacturer':
							case 'type':
								$product[$field] = $filterForm->getIdName($product[$field],$field);
								break;
							case 'price':
							case 'msrp':
								$product[$field] = number_format($product[$field],2);
								break;
						}
					}
				}

				fputcsv($out, $product);
			}
			fclose($out);
			$csv = ob_get_clean();
		}
		
		$viewModel = new ViewModel(array('csv'=>$csv));
    	$viewModel->setTerminal(true);

    	header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=products-'.time().'.csv');

    	return $viewModel;
    }

    public function importAction(){
    	$liveProducts = $this->getLiveMproductTable()->fetchAll();
    	$this->getMproductTable()->clearTable();
    	foreach($liveProducts as $p) {
			$this->getMproductTable()->addProduct($p->getData());
		}
    	return new JsonModel(array(1));
    }

	public function updateStockAction(){
		$stockdata = $this->getLiveMproductTable()->getStockTable();
		$this->getMproductTable()->clearStockTable();
		foreach($stockdata as $sd) {
			$this->getMproductTable()->addStock((array)$sd);
		}
		return new JsonModel(array(1));
	}

	public function getMproductTable()
	{
		if (!$this->MproductTable) {
             $sm = $this->getServiceLocator();
             $this->MproductTable = $sm->get('Application\Model\MproductTable');
        }
        return $this->MproductTable;
	}

	public function getLiveMproductTable()
	{
		if (!$this->LiveMproductTable) {
             $sm = $this->getServiceLocator();
             $this->LiveMproductTable = $sm->get('Application\Model\LiveMproductTable');
        }
        return $this->LiveMproductTable;
	}

	protected function getProducts($request=null){

		if($request){		
			$this->products = $this->getMproductTable()->getProduct($request->getQuery());  		
    	}else{
    		$this->products = $this->getMproductTable()->fetchAll();
    	}

		$this->categories = $this->getMproductTable()->getCategories();
	    return $this->products;
	}

	// breaks curl body for unknown reason
	function handleHeader($curl,$header){
		echo $header.'<br><br>';
		print_r($curl);
	}

	public function curlAction(){

		$curl = $postfields = $body = null;
		$request = $this->getRequest();
		if($request->isPost()){
			$post = $request->getPost();
			$curl = $post['curl'];
			unset($post['curl']);
			unset($post['submit']);
			foreach($post as $field => $value){
				if($postfields)
					$postfields .= '&';
				$postfields .= "$field=$value";
			}
		}

		if(!empty($curl)) {
			/* Run Curl Request */
			$getheaders = 1;

			//https://communityfinancellc.com/api/?approval
			//$postfields = 'apikey=df09441affecc511591a434850a7ef31&code=300666';
			$ch = curl_init($curl);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			curl_setopt($ch, CURLOPT_HEADER, $getheaders);
			//curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,"handleHeader"));

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);

			if ($getheaders) {
				list($header, $body) = explode("\r\n\r\n", $output, 2);
				if (strpos($header, 'application/json') !== false)
					$body = print_r(json_decode($body), true);
			}
		}
		return new ViewModel(array('body'=>$body));
	}
}
