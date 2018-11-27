<?php


namespace app\stat\admin;

use app\stat\model\ContractorCategoryNames;

/**
 * Реестр категорий производителей
 *
 * @author kotov
 */
class AdminContractorsCategory extends AdminDirectory 
{
     public function __construct()
    {
        $this->link = 'contractors_category';
        $this->name = l('GLOB_CONTRACTORS_CATEGORY_SETTINGS','admin');
        $this->parentElement = 'Contractors';  
        $this->directory_model = new ContractorCategoryNames();  
        $this->model_name = 'ContractorCategoryNames';
        
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('CONTRACTORS_CATEGORY_NAME','admin')];
        parent::__construct();                
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр брендов', 'link' => 'brands'],
            ['name' => 'Реестр производителей', 'link' => 'contractors']
        ];
    }
}
