<?php

namespace app\stat\admin;


use app\stat\model\Contractor;
use app\stat\Tools;
use app\stat\model\ContractorCategoryNames;
/**
 * Description of AdminContractors
 *
 * @author kotov
 */
class AdminContractors extends AdminDirectory 
{
    //put your code here
     public function __construct()
    {
        $this->link = 'contractors';
        $this->name = l('GLOB_CONTRACTORS_SETTINGS','admin');
        $this->parentElement = 'Index';          
        $this->directory_model = new Contractor();    
        $this->model_name = 'Contractor';
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('CONTRACTOR_NAME','admin'),
                                      'Region' => l('REGION_HEAD')];
        $this->cols_to_sort = ['Name','Region'];
        $this->filter_array = [
            'present' => 1,
            'category' => 0,
        ];
        parent::__construct();                
    }
    protected function prepare() {
        parent::prepare();                
    }
    protected function loadFilters() {
        parent::loadFilters();
        $val = Tools::getValue('present','GET');
        if ($val !== false) {
            $this->filter_array['present'] = (int)$val;              
        }
        $this->filter_array['category'] = (int) Tools::getValue('category','GET',0);
    }

    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр брендов', 'link' => 'brands'],
            ['name' => 'Реестр категорий производителей', 'link' => 'contractors-category']
        ];
        $contractors_present = [
          ['value' => 0, 'text' => 'Все производители'],
          ['value' => 1, 'text' => 'Присутствующие на портале']
        ];
        $contractor_categories = ContractorCategoryNames::getRowsArray(
            [['TBLCONTRACTORCATEGORYNAMES','Id','value'],['TBLCONTRACTORCATEGORYNAMES','Name','text']]);
        array_unshift($contractor_categories,['value' => 0 ,'text' => l('FILTERS_CONTRACTORS_ALL_CATEGORIES','admin')]);
        
        $contractors_selected = ($this->filter_array['present'] !== false) ? $this->filter_array['present'] : 1;
        $categories_selected = ($this->filter_array['category']) ? $this->filter_array['category'] : 0;
        $this->left_block_vars['blocks_list'][1]['hide'] = false;
        $this->left_block_vars['blocks_list'][1]['elements'] = [
                    [   'type' => 'select',
                        'header_text' => l('FILTERS_CONTRACTORS_PRESENT','admin'),
                        'class_name' => 'rs-form-control',
                        'name' => 'present',
                        'options' => $contractors_present,
                        'selected' => $contractors_selected
                        
                    ],
                    [   'type' => 'select',
                        'header_text' => l('FILTERS_CONTRACTORS_CATEGORY','admin'),
                        'class_name' => 'rs-form-control',
                        'name' => 'category',
                        'options' => $contractor_categories,
                        'selected' => $categories_selected
                        
                    ]
                ];
        $this->left_block_vars['blocks_list'] = array_reverse($this->left_block_vars['blocks_list']);
    }
}
