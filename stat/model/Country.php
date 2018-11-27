<?php

namespace app\stat\model;


use app\stat\db\QuerySelectBuilder;
/**
 * Description of Country
 *
 * @author kotov
 */
class Country extends ObjectModel
{
    protected $name;
    protected $iso;
    protected $form_exist = true;
    protected $form_template_head = 'COUNTRY';
    protected $model_name = 'Country';    

    protected static $table = "TBLCOUNTRY";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tc','Id'],
                ['tc','Name']
            ],
            'from' => [
                [ self::$table , 'tc']
            ],
            'where' => [                
            ]
        ];
    }
    protected function formConfigure() {
        parent::formConfigure();
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('COUNTRY_ELEMENT_NAME'),
            'type' => 'text',
            'required' => true,
            'size' => 250,
            'value' => $this->name ? $this->name : ''
        ];
        $this->form_elements['main_form']['elements_list']['iso'] = [
            'label' => l('COUNTRY_ELEMENT_SYMBOL_CODE'),
            'type' => 'text',        
            'size' => 50,
            'value' => $this->iso ? $this->iso : ''
        ];
        
    }
            /**
     * Получает список городов для выбранной страны
     * @return array
     */
    public function getRegionsList() {
        if ($this->getId()) {
            return json_encode(getDb()->getRows(new QuerySelectBuilder( [
                    'select' => [['Id'],['Name']],
                    'from' => 'TBLREGION', 
                    'where' => [['param' => 'CountryId','staticNumber' => $this->id ]],
                    'orderBy' => [['name' => 'Name','sort' => 'ASC']] 
                ])));
        } else {
        return json_encode(array());
        }
    }
    
    
    
}
