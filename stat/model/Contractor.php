<?php

namespace app\stat\model;
use app\stat\Tools;
use app\stat\db\QuerySelectBuilder;

/**
 * Description of Contractor
 *
 * @author kotov
 */
class Contractor extends ObjectModel
{
    protected $name;
    protected $fullName;
    protected $internationalName;
    protected $typeId;
    protected $cityId;
    protected $phone;
    protected $email;
    protected $web;
    protected $isRosagromash;
    protected $approved;
    protected $simple;
    protected $service;
    protected $comment;
    protected $isFirst;
    protected $email2;
    protected $present;
    protected $isImporter;
    protected $form_exist = true;
    protected $form_template_head = 'CONTRACTOR';
    protected $model_name = 'Contractor';
    protected $info_block_exist = true;
    
    protected static $table = "TBLCONTRACTOR";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tc','Id'],
                ['tc','Name'],
                ['tc','Present'],
                ['r','Name','Region']
            ],
            'from' => [
                [ self::$table , 'tc'],
                [ 'TBLREGION' , 'r'],
                [ 'TBLCITY' , 'c'],
            ],
            'where' => [ 
                ['param' => 'tc.CityId','staticValue' => 'c.Id'],
                ['param' => 'c.RegionId','staticValue' => 'r.Id'],
            ]
        ];
    }
    protected function applyAdditionalFilters($filter,&$select, &$from, &$where) {
        parent::applyAdditionalFilters($filter,$select, $from, $where);
        if (key_exists('category', $filter))
        {
            if ($filter['category'] != 0) {                
                $from[] = ['TBLCONTRACTORCATEGORY','cc'];
                $where[] = ['param' => 'res.Id','staticValue' => 'cc.ContractorId'];
                $where[] = ['param' => 'cc.CategoryId','staticNumber' => $filter['category']];
            }
        }
    }
    
    public function getEditorId() {
        if (!$this->getId()) {
            return '';
        }
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->select = 'Id';
        $queryBuilder->from = 'TBLUSER';
        $queryBuilder->where = ['"ContractorId" = '.$this->id. ' AND "ImportantPoll" = 1'];
        $editor = getDb()->getRow($queryBuilder);
        if (empty($editor)) {
            return '';
        }
        return (int)$editor['Id'];                
    }
    protected function informBlockConfigure() {
        parent::informBlockConfigure();
        $id= $this->getId();        
        $this->info_elements['elements_list']['company'] = [
            'name' => l('INFORM_COMPANY'),
            'value' => $this->name
        ];
        $this->info_elements['elements_list']['company_phone'] = [
            'name' => l('INFORM_COMPANY_PHONE'),
            'value' => $this->phone
        ];
        $this->info_elements['elements_list']['company_email'] = [
            'name' => l('INFORM_COMPANY_EMAIL'),
            'value' => $this->email
        ];

    }
    
    
    
    protected function formConfigure() {
        parent::formConfigure();
        $actions = l('BTN_ACTIONS');
        $id = $this->getId();
        if ($id) {
            $default_city_id = $this->cityId;
        } else {
            $default_city_id = 24;
        }
        
        $city = new City($default_city_id);
        $default_region_id = $city->regionId;
        $region = new Region($default_region_id);
        $default_country_id = $region->countryId;  
        
        //$default_country_id = $id ? $this->c
        $countries_list = Country::getRowsArray([
            ['TBLCOUNTRY','Id','value'],
            ['TBLCOUNTRY','Name','text'],            
        ],null,[['name' => 'Name','sort' => 'ASC']]);
        $cities_list = City::getRowsArray([
            ['TBLCITY','Id','value'],
            ['TBLCITY','Name','text']
        ],[['param' => 'RegionId', 'staticNumber' => $default_region_id]],
                [['name' => 'Name','sort' => 'ASC']]);
        $regions_list = Region::getRowsArray([
            ['TBLREGION','Id','value'],
            ['TBLREGION','Name','text']
        ],[['param' => 'CountryId', 'staticNumber' => $default_country_id]],
                [['name' => 'Name','sort' => 'ASC']]);
        
        if ($id) {
           $defaul_email2 = ContractorEmail::getRowsArray(
                   [['TBLCONTRACTOREMAIL','Email','text']],
                   [['param' => 'ContractorId', 'staticNumber' => $id]]);
        } else {
            $defaul_email2 = array();
        }
        $types_list = ContractorType::getRowsArray([
                ['TBLCONTRACTORTYPE','Id','value'],
                ['TBLCONTRACTORTYPE','Name','text']
            ]);
        $categories_list = ContractorCategoryNames::getRowsArray([
                ['TBLCONTRACTORCATEGORYNAMES','Id','value'],
                ['TBLCONTRACTORCATEGORYNAMES','Name','text']
            ]);
        if ($id) {
            $categories_contractor = Tools::getValuesFromArray(ContractorCategory::getRowsArray([
                ['TBLCONTRACTORCATEGORY','CategoryId'],
                ],[['param' => 'ContractorId', 'staticNumber' => $id]]), "CategoryId");
        } else {
            $categories_contractor = array();
        }
        if (count($categories_contractor) > 0) {
            foreach ($categories_list as $key => $category) {
                if (in_array($category['value'],$categories_contractor )) {
                    $categories_list[$key]['checked'] = true;
                }
            }
        }
        
        $default_contractor_present = $id ? $this->present : 0;
        $default_contractor_type = $id ? $this->typeId : 2;
        $default_rosagromash = $id ? $this->isRosagromash : 0;
        $default_importer = $id ? $this->isImporter : 0;
        $this->form_elements['main_form']['elements_list']['fullName'] = [
            'label' => l('CONTRACTOR_ELEMENT_NAME'),
            'type' => 'text',
             'description' => l('CONTRACTOR_ELEMENT_NAME_ADDITIONAL_TEXT'),
            'size' => 300,
            'value' => $this->fullName ? htmlspecialchars($this->fullName) : '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('CONTRACTOR_ELEMENT_FULLNAME'),
            'type' => 'text',            
            'size' => 300,
            'value' => $this->name ? htmlspecialchars($this->name) : '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['internationalName'] = [
            'label' => l('CONTRACTOR_ELEMENT_INTERNATIONALNAME'),
            'type' => 'text',
            'size' => 300,
            'value' => $this->internationalName ? htmlspecialchars($this->internationalName) : ''
        ];
        $this->form_elements['main_form']['elements_list']['geography'] = [
            'label' => l('CONTRACTOR_GEOGRAPHY'),
            'additional_text' => l('CONTRACTOR_GEOGRAPHY_ADDITIONAL_TEXT'),
            'show_additional_text' => false,
            'type' => 'options_group',
            'elements' => [
                'countryId' => [
                    'type' => 'select',
                    'label' => l('CONTRACTOR_COUNTRY'),
                    'elements' => $countries_list,
                    'selected' => $default_country_id,
                    'size' => 300,
                    'change' => 'changeGeographyElement'
                    ],
                'regionId' => [
                    'type' => 'select',
                    'label' => l('CONTRACTOR_REGION'),
                    'elements' => $regions_list,
                    'selected' => $default_region_id,
                    'size' => 300,  
                    'change' => 'changeGeographyElement'
                    ],
                'cityId' => [
                    'type' => 'select',
                    'label' => l('CONTRACTOR_CITY'),
                    'elements' => $cities_list ,
                    'selected' => $default_city_id,
                    'size' => 300,
                    ]                
            ]            
        ];
        $this->form_elements['main_form']['elements_list']['phone'] = [
            'label' => l('CONTRACTOR_PHONE'),
            'type' => 'text',
            'size' => 300,
            'required' => true,
            'value' => $this->phone ? $this->phone : ''
        ];
        $this->form_elements['main_form']['elements_list']['email'] = [
            'label' => l('CONTRACTOR_EMAIL'),
            'type' => 'text',
            'size' => 300,
            'required' => true,
            'value' => $this->email ? $this->email : ''
        ];
        $this->form_elements['main_form']['elements_list']['email2'] = [
            'label' => l('CONTRACTOR_EMAILS_FOR_REPORTS'),
            'type' => 'edit_list',
            'id' => 'email_list',
            'size' => 300,
            'js_add_id'=> 'add_email',
            'js_add_text'=> $actions['add'],
            'elements' => $defaul_email2,
            'css_wrapper' => 'border_block'
        ];
        $this->form_elements['main_form']['elements_list']['web'] = [
            'label' => l('CONTRACTOR_WEB'),
            'type' => 'text',
            'size' => 300,
            'value' => $this->web ? $this->web : ''
        ];
        $this->form_elements['main_form']['elements_list']['isRosagromash'] = [
            'label' => l('CONTRACTOR_ROSAGROMASH'),
            'type' => 'radio',            
            'value' => $default_rosagromash,
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['isImporter'] = [
            'label' => l('CONTRACTOR_IMPORTER'),
            'description' => l('CONTRACTOR_IMPORTER_DESC'),
            'type' => 'radio',            
            'value' => $default_importer,
            'hide' => !is_admin()
        ];
        
        $this->form_elements['main_form']['elements_list']['typeId'] = [
            'label' => l('CONTRACTOR_TYPE'),
            'type' => 'select',
            'size' => 300,
            'elements' => $types_list ,
            'selected' => $default_contractor_type,
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['comment'] = [
            'label' => l('CONTRACTOR_COMMENT'),
            'type' => 'textarea',
            'size' => 300,            
            'value' => $this->comment ? $this->comment : '',
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['category'] = [
            'label' => l('CONTRACTOR_CATEGORY'),
            'type' => 'multi_check', 
            'css_wrapper' => 'multi_check',
            'elements' => $categories_list,
            'name' => 'category',
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['present'] = [
            'label' => l('CONTRACTOR_PRESENT'),
            'type' => 'radio',
            'size' => 300,
            'value' => $default_contractor_present,
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['approved'] = [     
            'type' => 'hidden',            
            'value' => 1      
        ];
        $this->form_elements['main_form']['elements_list']['simple'] = [     
            'type' => 'hidden',            
            'value' => 0     
        ];
    }
    public function saveModelObject($data,$form_elements=null,$return_json=true) {
        $id = $this->getId();
        parse_str($data['frm_data'],$form_elements);       
        //if ($this->getId())
        $res = parent::saveModelObject($data,$form_elements,false);
        if ($res['STATUS'] !== OBJECT_MODEL_SAVED) {
            if ($return_json) {
                return json_encode($res);
            } else {
            return $res;
            }
        }
         if ($id) {
            ContractorCategory::deleteByProps(['contractorId' => $id]); 
            ContractorEmail::deleteByProps(['contractorId' => $id]);
             
         } else {
             $id = (int) $res['NEW_ID'];
         }
         if (isset($form_elements['category'])) {
            foreach ($form_elements['category'] as $cat_id) {
                $cat = ContractorCategory::getInstance([
                    'contractorId' => $id,
                    'categoryId' => $cat_id
                ]);
                $cat->addToDb();
            }
         }
        if (isset($form_elements['email_list'])) {
            foreach ($form_elements['email_list'] as $email) {
                $c_email = ContractorEmail::getInstance([
                   'contractorId' => $id,
                    'email' => $email
                ]);
                $c_email->addToDb();
            }
         }
         if ($return_json) {
            return json_encode($res);
         } else {
             return $res;
         }
         
        //if (key_exists('NEW_ID',$res) && !empty($res['NEW_ID'])) {
            
     //   } else {
            
     //   }
    }
}
