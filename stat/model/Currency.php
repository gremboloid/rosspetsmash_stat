<?php

namespace app\stat\model;

/**
 * Description of Currency
 *
 * @author kotov
 */
class Currency extends ObjectModel
{
    protected $name;
    protected $fullName;
    protected $form_exist = true;
    protected $form_template_head = 'CURRENCY';
    protected $model_name = 'Currency';

    protected static $table = "TBLCURRENCY";
    
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tc','Id'],
                ['tc','Name'],
                ['tc','FullName'],
            ],
            'from' => [
                [ self::$table , 'tc']
            ],
            'where' => [                
            ]
        ];
    }
    
    public function getCurrencyName()
    {
        return $this->name;
    }
    protected function formConfigure() {
        parent::formConfigure();
        $this->form_elements['main_form']['elements_list']['fullName'] = [
            'label' => l('CURRENCY_ELEMENT_FULLNAME'),
            'type' => 'text',
            'required' => true,
            'size' => 250,
            'value' => $this->fullName ? $this->fullName : ''
        ];
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('CURRENCY_ELEMENT_NAME'),
            'type' => 'text',
            'required' => true,
            'size' => 250,
            'value' => $this->name ? $this->name : ''
        ];
    }
}
