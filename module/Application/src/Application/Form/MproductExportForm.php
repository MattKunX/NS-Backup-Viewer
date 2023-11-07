<?php
	
namespace Application\Form;

use Zend\Form\Form;

class MproductExportForm extends Form
{

	public function __construct($fields)
	{
		parent::__construct('mproductexport');

		$this->setAttribute('method', 'POST');

		foreach($fields as $k => $v){
			$this->add(array(
	             'name' => $k,
	             'type' => 'Checkbox',
	             'options' => array(
	             	'label' => ucfirst($k)
	             )
	         ));
		}
        
         $this->add(array(
             'name' => 'export',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Export',
                 'id' => 'exportbutton',
                 'class' => 'button-link'
             )
         ));
	}
}