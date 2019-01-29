<?php

namespace app\stat\model;


use app\stat\Sessions;
use Yii;
/**
 * Description of User
 *
 * @author kotov
 */
class User extends ObjectModel
{
    protected $name;
    protected $patronymicName;
    protected $surName;
    protected $contractorId;
    protected $roleId;
    protected $phone;
    protected $phone2;
    protected $email;
    protected $login;
    protected $cityId;
    protected $approved;
    protected $pasword;
    protected $importantPoll;
    protected $contactPerson;
    protected $contactPersonPhone;
    protected $contactPersonEmail;
    protected $oldPassword;
    protected $form_exist = true;
    protected $form_template_head = 'USER';
    protected $model_name = 'User';
    protected $info_block_exist = true;
    protected $search_fields = 'Fio';
    protected $deleted;




    protected static $table = "TBLUSER";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tu','Id'],
                ['name' => 'Fio','textValue' => '("tu"."SurName" || \' \' || "tu"."Name" || \' \' || "tu"."PatronymicName")'],
                ['tu','ContractorId'],
                ['tu','RoleId'],
                ['tc','Name','ContractorName'],
                ['tc','Present'],
            ],
            'from' => [
                [ self::$table , 'tu'],
                [ 'TBLCONTRACTOR' , 'tc'],
            ],
            'where' => [  
                ['param' => 'tu.ContractorId','staticValue' => 'tc.Id'],
                ['param' => 'tu.Deleted','staticValue' => 0],
            ]
        ];
    }
    
    protected function informBlockConfigure() {
        parent::informBlockConfigure();
        $id= $this->getId();        
        $this->info_elements['elements_list']['fio'] = [
            'name' => l('INFORM_FIO'),
            'value' => $this->getFIO()
        ];
        $this->info_elements['elements_list']['phone'] = [
            'name' => l('INFORM_USER_PHONE_MOBILE'),
            'value' => $this->phone
        ];
        $this->info_elements['elements_list']['phone2'] = [
            'name' => l('INFORM_USER_PHONE_WORK'),
            'value' => $this->phone2
        ];
        $this->info_elements['elements_list']['email'] = [
            'name' => l('INFORM_USER_EMAIL'),
            'value' => $this->email
        ];
    }
    
     protected function formConfigure($specialParams = []) {
        parent::formConfigure($specialParams);
        $is_admin = is_admin();        
        $id = $this->getId();
        $contractors = Contractor::getRowsArray([
            ['TBLCONTRACTOR','Id','value'],
            ['TBLCONTRACTOR','Name','text']
        ],null,[['name' => 'Name','sort' => 'ASC']]);
        $roles = Role::getRowsArray([
            ['TBLROLE','Id','value'],
            ['TBLROLE','Name','text']
        ]);
        
        $fio = $id ? $this->getFIO() : '';
        $default_contractor_id = $id ? $this->contractorId : '';
        $default_role = $id ? $this->roleId : 2;
        $default_phone = $id ? $this->phone : '';
        $default_phone2 = $id ? $this->phone2 : '';
        $default_email = $id ? $this->email : '';
        $default_login = $id ? $this->login : '';
        $default_important_poll = $id ? $this->importantPoll : 1;
        $default_contact_person = $id ? $this->contactPerson : '';
        $default_contact_person_phone = $id ? $this->contactPersonPhone : '';
        $default_contact_person_email = $id ? $this->contactPersonEmail : '';
        if (!$id) {
            $default_city_id = 24;
        } else {
            $default_city_id = $this->cityId;
        }        
        $city = new City($default_city_id);
        $default_region_id = $city->regionId;
        $region = new Region($default_region_id);
        $default_country_id = $region->countryId;       
        //$default_country_id = $id ? $this->c
        $countries_list = Country::getRowsArray([
            ['TBLCOUNTRY','Id','value'],
            ['TBLCOUNTRY','Name','text']
        ],null,[['name' => 'Name','sort','ASC']]);
        $cities_list = City::getRowsArray([
            ['TBLCITY','Id','value'],
            ['TBLCITY','Name','text']
        ],[['param' => 'RegionId', 'staticNumber' => $default_region_id]],
                [['name' => 'Name','sort','ASC']]);
        $regions_list = Region::getRowsArray([
            ['TBLREGION','Id','value'],
            ['TBLREGION','Name','text']
        ],[['param' => 'CountryId', 'staticNumber' => $default_country_id]]
                ,[['name' => 'Name','sort','ASC']]);
        
        
        $this->form_elements['main_form']['elements_list']['fio'] = [
            'label' => l('USER_FIO'),
            'type' => 'text',
            'size' => 300,
            'value' => $fio,
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['contractorId'] = [
            'label' => l('USER_CONTRACTOR'),
            'type' => 'select',
            'size' => 300,
            'elements' => $contractors ,
            'selected' => $default_contractor_id,
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['roleId'] = [
            'label' => l('USER_ROLE'),
            'type' => 'select',
            'size' => 300,
            'elements' => $roles ,
            'selected' => $default_role,
            'hide' => !is_admin()
        ];
        $this->form_elements['main_form']['elements_list']['phone'] = [
            'label' => l('USER_PHONE'),
            'type' => 'text',
            'size' => 300,
            'value' => $default_phone,
        ];
        $this->form_elements['main_form']['elements_list']['phone2'] = [
            'label' => l('USER_PHONE2'),
            'type' => 'text',
            'size' => 300,
            'value' => $default_phone2,
        ];
        $this->form_elements['main_form']['elements_list']['email'] = [
            'label' => l('USER_EMAIL'),
            'type' => 'mail',
            'size' => 300,
            'value' => $default_email,
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['importantPoll'] = [
            'label' => l('USER_IMPORTANT_POLL'),
            'type' => 'radio',            
            'value' => $default_important_poll,
        ];
        $this->form_elements['main_form']['elements_list']['geography'] = [
            'label' => l('USER_GEOGRAPHY'),
            'additional_text' => l('USER_GEOGRAPHY_ADDITIONAL_TEXT'),
            'show_additional_text' => false,
            'type' => 'options_group',
            'elements' => [
                'countryId' => [
                    'type' => 'select',
                    'label' => l('USER_COUNTRY'),
                    'elements' => $countries_list,
                    'selected' => $default_country_id,
                    'size' => 300,
                    'change' => 'changeGeographyElement'
                    ],
                'regionId' => [
                    'type' => 'select',
                    'label' => l('USER_REGION'),
                    'elements' => $regions_list,
                    'selected' => $default_region_id,
                    'size' => 300,  
                    'change' => 'changeGeographyElement'
                    ],
                'cityId' => [
                    'type' => 'select',
                    'label' => l('USER_CITY'),
                    'elements' => $cities_list ,
                    'selected' => $default_city_id,
                    'size' => 300,
                    ]                
            ]            
        ];
        $this->form_elements['main_form']['elements_list']['login'] = [
            'label' => l('USER_LOGIN'),
            'type' => 'text',
            'size' => 300,
            'value' => $default_login,
            'required' => true
        ];
        if ($id) {
            $this->form_elements['main_form']['elements_list']['editPassword'] = [
                'label' => l('USER_PASSWORD_CHANGE'),
                'type' => 'radio', 
                'onchange' => 'isPasswordEdit',
                'value' => 0
                ];
        } else {
            $this->form_elements['main_form']['elements_list']['editPassword'] = [     
                'type' => 'hidden',            
                'value' => 1      
            ];
        }
        $this->form_elements['main_form']['elements_list']['password'] = [
            'label' => l('USER_PASSWORD'),
            'type' => 'password',
            'size' => 150,
            'value' => '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['repeatPassword'] = [
            'label' => l('USER_PASSWORD_REPEAT'),
            'type' => 'password',
            'size' => 150,
            'value' => '',
        ];
        $this->form_elements['main_form']['elements_list']['contactPerson'] = [
            'label' => l('USER_CONTACT_PERSON'),
            'type' => 'text',
            'size' => 300,
            'value' => $default_contact_person,
        ];
        $this->form_elements['main_form']['elements_list']['contactPersonPhone'] = [
            'label' => l('USER_CONTACT_PERSON_PHONE'),
            'type' => 'text',
            'size' => 300,
            'value' => $default_contact_person_phone,
        ];
        $this->form_elements['main_form']['elements_list']['contactPersonEmail'] = [
            'label' => l('USER_CONTACT_PERSON_EMAIL'),
            'type' => 'text',
            'size' => 300,
            'value' => $default_contact_person_email,
        ];
        $this->form_elements['main_form']['elements_list']['approved'] = [     
            'type' => 'hidden',            
            'value' => 1      
        ];
        if ($id) {
            $this->form_elements['main_form']['elements_list']['password']['hide'] = true;
            $this->form_elements['main_form']['elements_list']['repeatPassword']['hide'] = true;
        }        
    }
    public function getFIO()
    {
        return $this->surName .' ' .
                $this->name . ' ' .
                $this->patronymicName;
    }
        public function saveModelObject($data, $form_elements = null, $return_json = true) {
        $id = $this->getId();
        parse_str($data['frm_data'],$form_elements);
        if (!empty($form_elements['editPassword'])) {
            if (count($form_elements['password']) == 0) {
                return json_encode([                   
                    'STATUS' => OBJECT_MODEL_SAVE_ERROR, 
                    'MESSAGE' => l('EMPTY_PASSWORD_ERROR','messages')
                ]);
            }
            if ($form_elements['password'] !== $form_elements['repeatPassword']) {
                return json_encode([                   
                    'STATUS' => OBJECT_MODEL_SAVE_ERROR, 
                    'MESSAGE' => l('PASSWORD_CONFIRM_ERROR','messages')
                ]);
            }
            $form_elements['pasword'] = password_hash($form_elements['password'], PASSWORD_DEFAULT);
            if (Yii::$app->user->getId() == $id) {
                Sessions::setValueToVar('password', $form_elements['password']);
            }
        }
        $fio_arr = explode(' ',$form_elements['fio'],3);
        if (is_array($fio_arr)) {
            if(count($fio_arr > 0)) {
                $fio_arr[1] = $fio_arr[1] ? $fio_arr[1] : ' ';
                $fio_arr[2] = $fio_arr[2] ? $fio_arr[2] : ' ';
                $form_elements['surName'] = $fio_arr[0];
                $form_elements['name'] = $fio_arr[1];
                $form_elements['patronymicName'] = $fio_arr[2];            
            }
        }        
        return parent::saveModelObject($data, $form_elements, $return_json);
    }
    public function setNewPassword($new_pwd) {
        $new_password = password_hash($new_pwd, PASSWORD_DEFAULT);
        $this->set('pasword', $new_password);
    }
    
    
    
    
}
