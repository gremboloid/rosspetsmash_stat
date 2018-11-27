<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\model;

/**
 * Description of ContractorCategoryNames
 *
 * @author kotov
 */
class ContractorCategoryNames extends ObjectModel
{
    protected $name;
    protected $summaryName;
    protected $form_exist = true;
    protected $form_template_head = 'CONTRACTORS_CATEGORY_NAMES';
    protected $model_name = 'ContractorCategoryNames';

    protected static $table = "TBLCONTRACTORCATEGORYNAMES";
    //put your code here
    public function __construct($id = null) {
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['t','Id'],
                ['t','Name'],
                ['t','SummaryName']
            ],
            'from' => [
                [ self::$table , 't']
            ],
            'where' => [                
            ]
        ];
    }
    protected function formConfigure() {
        parent::formConfigure();
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('CONTRACTORS_CATEGORY_ELEMENT_NAME'),
            'type' => 'text',
            'size' => 250,
            'value' => $this->name ? $this->name : '',
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['summaryName'] = [
            'label' => l('CONTRACTORS_CATEGORY_ELEMENT_SUMMARY_NAME'),
            'description' => l('CONTRACTORS_CATEGORY_ELEMENT_SUMMARY_NAME_DESC'),
            'type' => 'text',
            'size' => 250,
            'value' => $this->summaryName ? $this->summaryName : '',
        ];
    }
}
