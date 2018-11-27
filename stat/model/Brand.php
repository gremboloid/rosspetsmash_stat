<?php

namespace app\stat\model;


use app\stat\services\ContractorService;
/**
 * Description of Brand
 *
 * @author kotov
 */
class Brand extends ObjectModel
{
    protected $name;
    protected $contractorId;
    protected $internationalName;
    protected $isImport;
    protected $countryId; 
    protected $form_exist = true;
    protected $form_template_head = 'BRAND';
    protected $model_name = 'Brand';

    
    protected static $table = "TBLBRAND";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tb','Id'],
                ['tb','Name']
            ],
            'from' => [
                [ self::$table , 'tb']
            ],
            'where' => [                
            ]
        ];
    }    
    /**
     * Подучить имя производителя
     */
    public function getContractorName() {
        $sResult = '';
        if ($this->getId()) {
            $ctr = new Contractor($this->contractorId);
            $sResult = $ctr->name;
        }
        return $sResult;
    }
    protected function formConfigure() {
        parent::formConfigure();
        $default_brand_type_id = '';
        $default_contractor_id = '';
        $contractors = ContractorService::getActualContractors([
            ['TBLCONTRACTOR','Id','value'],
            ['TBLCONTRACTOR','Name','text']
        ]);
        $default_country_id = 1;
        if ($this->getId()) {
            $default_country_id = $this->countryId ? $this->countryId : $default_country_id;
            $default_brand_type_id = $this->isImport;
            $default_contractor_id = $this->contractorId;
        }
        
        $countries = Country::getRowsArray([
            ['TBLCOUNTRY','Id','value'],
            ['TBLCOUNTRY','Name','text']
        ],null,[['name' => 'Name']]);
        $brand_type = [
          ['text' => 'Производство РФ', 'value' => 1],
          ['text' => 'Импорт', 'value' => 2],
          ['text' => 'Промышленная сборка', 'value' => 3]
        ];
        
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('BRAND_ELEMENT_NAME'),
            'type' => 'text',
            'size' => 250,
            'value' => $this->name ? $this->name : '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['internationalName'] = [
            'label' => l('BRAND_ELEMENT_INTERNATIONALNAME'),
            'type' => 'text',
            'size' => 250,
            'value' => $this->internationalName ? $this->internationalName : ''
        ];
        $this->form_elements['main_form']['elements_list']['contractorId'] = [
            'label' => l('BRAND_CONTRACTOR'),
            'type' => 'select',
			'size' => 250,
            'elements' => $contractors ,
            'selected' => $default_contractor_id
        ];
        $this->form_elements['main_form']['elements_list']['isImport'] = [
            'label' => l('BRAND_TYPE'),
            'type' => 'select',
			'size' => 250,
            'elements' => $brand_type,
            'selected' => $default_brand_type_id
        ];
        
        
        $this->form_elements['main_form']['elements_list']['countryId'] = [
            'label' => l('BRAND_COUNTRY'),
            'type' => 'select',
			'size' => 250,
            'elements' => $countries,
            'selected' => $default_country_id
        ];
    }
}
