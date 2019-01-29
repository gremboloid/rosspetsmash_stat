<?php

namespace app\stat\model;


/**
 * Description of City
 *
 * @author kotov
 */
class City extends ObjectModel
{
    protected $name;
    protected $regionId;
    protected $form_exist = true;
    protected $form_template_head = 'CITY';
    protected $model_name = 'City';   
    
    protected static $table = 'TBLCITY';
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tc','Id'],
                ['tc','Name'],
                ['tr','Name','RegionName'],
                ['c','Name','CountryName'],
                ['c','Id','CountryId']
            ],
            'from' => [
                [ self::$table , 'tc'],
                [ 'TBLREGION' , 'tr'],
                [ 'TBLCOUNTRY' , 'c'],
            ],
            'where' => [  
                ['param' => 'tc.RegionId','staticValue' => 'tr.Id'],
                ['param' => 'tr.CountryId','staticValue' => 'c.Id']
            ]
        ];
    }
    protected function formConfigure($specialParams = []) {
        parent::formConfigure($specialParams);
        $id = $this->getId();
        $default_region_id = $id ? $this->regionId : 53;
        
        $region = new Region($default_region_id);
        $default_country_id = $region->countryId;       
        //$default_country_id = $id ? $this->c
        $countries_list = Country::getRowsArray([
            ['TBLCOUNTRY','Id','value'],
            ['TBLCOUNTRY','Name','text']
        ],null,[['name' =>'Name']]);
        $regions_list = Region::getRowsArray([
            ['TBLREGION','Id','value'],
            ['TBLREGION','Name','text']
        ],[['param' => 'CountryId', 'staticNumber' => $default_country_id]]);
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('CITY_ELEMENT_NAME'),
            'type' => 'text',
            'size' => 250,
            'value' => $this->name ? $this->name : '',
            'required' => true
        ];
         $this->form_elements['main_form']['elements_list']['geography'] = [
            'label' => l('CITY_GEOGRAPHY'),
            'additional_text' => l('CITY_GEOGRAPHY_ADDITIONAL_TEXT'),
            'show_additional_text' => false,
            'type' => 'options_group',
            'elements' => [
                'countryId' => [
                    'type' => 'select',
                    'label' => l('CITY_COUNTRY'),
                    'elements' => $countries_list,
                    'selected' => $default_country_id,
                    'size' => 250,
                    'change' => 'changeGeographyElement'
                    ],
                'regionId' => [
                    'type' => 'select',
                    'label' => l('CITY_REGION'),
                    'elements' => $regions_list,
                    'selected' => $default_region_id,
                    'size' => 250,  
                    'change' => 'changeGeographyElement'
                    ],               
            ]            
        ];
    }
    
}
