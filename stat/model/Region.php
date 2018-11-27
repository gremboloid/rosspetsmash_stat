<?php

namespace app\stat\model;


use app\stat\db\QuerySelectBuilder;
/**
 * Description of Regions
 *
 * @author kotov
 */
class Region extends ObjectModel
{
    protected $name;
    protected $countryId;
    protected $federalDestict;
    protected $isDeleted;
    protected $form_exist = true;
    protected $form_template_head = 'REGION';
    protected $model_name = 'Region';
    
    //put your code here
    protected static $table = "TBLREGION";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tr','Id'],
                ['tr','Name'],                
                ['c','Name','CountryName'],
                ['c','Id','CountryId']
            ],
            'from' => [
                [ self::$table , 'tr'],
                [ 'TBLCOUNTRY' , 'c'],
            ],
            'where' => [  
                ['param' => 'tr.CountryId','staticValue' => 'c.Id']
            ]
        ];
    }
    protected function formConfigure() {
        parent::formConfigure();
        $countries_list = Country::getRowsArray([
            ['TBLCOUNTRY','Id','value'],
            ['TBLCOUNTRY','Name','text']
        ],null,[['name' =>'Name']]);
        $default_country_id = $this->countryId ? $this->countryId : 1;
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('REGION_ELEMENT_NAME'),
            'type' => 'text',
            'size' => 250,
            'value' => $this->name ? $this->name : '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['countryId'] = [
            'label' => l('REGION_COUNTRY'),
            'type' => 'select',
            'size' => 250,
            'elements' => $countries_list ,
            'selected' => $default_country_id
        ];
        $this->form_elements['main_form']['elements_list']['federalDestict'] = [
            'label' => l('REGION_COUNTRY'),
            'type' => 'hidden',
            'value' => 2147483647
        ];
    }
    /**
     * Получает список городов для выбранного региона для AJAX запроса
     * @return string
     */
    public function getCitiesList() {
        if ($this->getId()) {
            return json_encode(getDb()->getRows(new QuerySelectBuilder([
                    'select' => [['Id'],['Name']],
                    'from' => 'TBLCITY', 
                    'where' => [['param' => 'RegionId','staticNumber' => $this->id ]],
                    'orderBy' => [['name' => 'Name','sort' => 'ASC']] 
                ])));
        } else {
        return json_encode(array());
        }
    }
    
    
}
