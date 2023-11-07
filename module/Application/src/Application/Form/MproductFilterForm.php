<?php
	
namespace Application\Form;

use Zend\Form\Form;

class MproductFilterForm extends Form
{

	protected $type = array (
					  '' => '',
					  4 => 'Default',
					  9 => 'Apparel',
					  10 => 'Metal Detector',
					  11 => 'Coil',
					  12 => 'Headphone',
					  13 => 'Coil Cover',
					  14 => 'Apparel Hat',
					  15 => 'Apparel Glove',
					  16 => 'Apparel Patch',
					  17 => 'Apparel Shirt',
					  20 => 'Pinpointer',
					  21 => 'Loop Support',
					  40 => 'Apparel Waders',
					  41 => 'Apparel Jackets',
					  42 => 'Apparel Pants',
					  44 => 'Book',
					  46 => 'Demo Metal Detector',
					  47 => 'Handle',
					  49 => 'Generic with Colors',
					);

	protected $manufacturer = array(''=>'',
					4=>'none',
					3=>'1st-texas',
					5=>'adams',
					6=>'anderson-rods',
					7=>'aquascan',
					8=>'b-f-system-inc',
					9=>'beachmaster',
					10=>'blisstool',
					11=>'bounty-hunter',
					12=>'cache',
					13=>'cobra',
					14=>'coiltek',
					470=>'compass',
					15=>'cricket',
					16=>'denitsa',
					17=>'detech',
					18=>'detector-pro',
					19=>'duck-commander',
					20=>'estwing-prospecting',
					21=>'fisher',
					22=>'fiskars',
					23=>'garmin',
					24=>'garrett',
					25=>'gemoro',
					26=>'gold-cube',
					211=>'gold-rush',
					27=>'gopro',
					28=>'gpl',
					29=>'ground-efx',
					30=>'jw-fishers',
					31=>'keene',
					32=>'kellyco',
					33=>'kelty',
					34=>'koss',
					35=>'laser-scan',
					36=>'lejermon-ent',
					37=>'lesche',
					38=>'lorenz',
					39=>'minelab',
					40=>'mp-series',
					41=>'nautilus',
					42=>'night-owl',
					43=>'nokta',
					44=>'okm',
					45=>'petzl',
					46=>'pro-series-ii-locators',
					47=>'pulse-star',
					48=>'quantro',
					49=>'rnb-innovations',
					50=>'rothco',
					51=>'sampson',
					52=>'scanmaster',
					53=>'sierra-designs',
					54=>'simmons',
					55=>'svl',
					56=>'teknetics',
					57=>'tesoro',
					58=>'thermacell',
					59=>'titan',
					60=>'treasure-commander',
					61=>'treasure-products',
					62=>'turbo-pan',
					63=>'vibra',
					64=>'viper',
					65=>'wenzel',
					66=>'western-safety',
					67=>'whites',
					364=>'outsol',
					380=>'trentco',
					498=>'gold-digger',
					499=>'nel',
					503=>'onxmaps',
					513=>'jobe',
					521=>'gear-keeper',
                    527=>'makro'
					);

	public function __construct($name=null)
	{
		parent::__construct('mproductfilter');

		asort($this->type);
		asort($this->manufacturer);

		$this->setAttribute('method', 'GET');

		$this->add(array(
             'name' => 'id',
             'type' => 'Text',
             'attributes' => array(
             	'id' => 'id'
             )
         ));
         $this->add(array(
             'name' => 'sku',
             'type' => 'Text'
         ));
         $this->add(array(
             'name' => 'name',
             'type' => 'Text'
         ));
         $this->add(array(
             'name' => 'price-low',
             'type' => 'Text',
             'attributes' => array(
                 'class' => 'price'
             )
         ));
         $this->add(array(
             'name' => 'price-high',
             'type' => 'Text',
             'attributes' => array(
                 'class' => 'price'
             )
         ));
         $this->add(array(
             'name' => 'kit',
             'type' => 'Text'
         ));
         $this->add(array(
             'name' => 'manufacturer',
             'type' => 'Select',
             'options'=> array(
             	'empty_option'=>'Select',
             	'value_options'=> $this->manufacturer
             )
         ));
         $this->add(array(
             'name' => 'type',
             'type' => 'Select',
             'options'=> array(
             	'empty_option'=>'Select',
             	'value_options'=> $this->type
             )
         ));
         $this->add(array(
             'name' => 'hasbogo',
             'type' => 'Select',
             'options'=> array(
             	'empty_option'=>'Select',
             	'value_options'=> array(
             		0 => 'No',
             		1 => 'Yes'
             	)
             )
         ));
         $this->add(array(
             'name' => 'order',
             'type' => 'Hidden',
             'attributes' => array(
                 'id' => 'order'
             )
         ));
         $this->add(array(
             'name' => 'dir',
             'type' => 'Hidden',
             'attributes' => array(
             	'value' => 'asc',
             	'id' => 'dir'
             )
         ));
         $this->add(array(
             'name' => 'search',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Search',
                 'id' => 'submitbutton'
             )
         ));
	}

	public function getIdName($id,$field){
		return $this->$field[$id];
	}

	public function getFieldArray($field){
		return $this->$field;
	}
}