<?php
/**
 * Created by PhpStorm.
 * User: mattkun
 * Date: 8/12/2016
 * Time: 1:07 PM
 */

namespace Netsuite\Form;

//use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class FilterForm extends Form
{
    public function __construct($action,$data=null)
    {
        parent::__construct('filterform');

        $this->setAttribute('action', '/netsuite/index/'.$action);
        $this->setAttribute('method', 'GET');

        $recordid = new Element('recordid');
        $recordid->setLabel('ID:');
        $recordid->setAttributes(array(
            'type' => 'text'
        ));

        $entity = new Element('entity');
        $entity->setLabel('Entity:');
        $entity->setAttributes(array(
            'type' => 'text'
        ));

        $email = new Element('email');
        $email->setLabel('Email:');
        $email->setAttributes(array(
            'type' => 'text'
        ));

        $phone = new Element('phone');
        $phone->setLabel('Phone:');
        $phone->setAttributes(array(
            'type' => 'text'
        ));

        $date = new Element('trandate');
        $date->setLabel('Date:');
        $date->setAttributes(array(
            'type' => 'text'
        ));

        $submit = new Element('filter');
        $submit->setValue('Filter');
        $submit->setAttributes(array(
            'type' => 'submit'
        ));

        $this->add($recordid);
        $this->add($entity);
        $this->add($email);
        $this->add($phone);
        $this->add($date);
        $this->add($submit);

        if($data)
            $this->setData($data);
    }
}