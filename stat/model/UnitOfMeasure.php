<?php

namespace app\stat\model;

/**
 * Description of UnitOfMeasure
 *
 * @author kotov
 */
class UnitOfMeasure extends ObjectModel
{
    protected $name;
    protected $shortName;
    protected $form_exist = true;
    protected $form_template_head = 'UNITOFMEASURE';
    protected $model_name = 'UnitOfMeasure';


    protected static $table = "TBLUNITOFMEASURE";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tu','Id'],
                ['tu','Name'],
                ['tu','ShortName'],
            ],
            'from' => [
                [ self::$table , 'tu']
            ],
            'where' => [                
            ]
        ];
    }
    protected function formConfigure() {
        parent::formConfigure();
         $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('UNITOFMEASURE_ELEMENT_NAME'),
            'type' => 'text',
            'required' => true,
            'size' => 250,
            'value' => $this->name ? $this->name : ''
        ];
        $this->form_elements['main_form']['elements_list']['shortName'] = [
            'label' => l('UNITOFMEASURE_ELEMENT_SHORTNAME'),
            'type' => 'text',
            'required' => true,
            'size' => 250,
            'value' => $this->shortName ? $this->shortName : ''
        ];
    }
    
}
