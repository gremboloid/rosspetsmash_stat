<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;


use app\stat\model\Brand;
/**
 * Description of AdminBrands
 *
 * @author kotov
 */
class AdminBrands extends AdminDirectory
{
    //put your code here
    public function __construct()
    {
        $this->link = 'brands';
        $this->name = l('GLOB_BRANDS_SETTINGS','admin');
        $this->parentElement = 'Contractors';  
        $this->directory_model = new Brand(); 
        $this->model_name = 'Brand';
        
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('BRAND_NAME','admin')];
        $this->cols_to_sort = ['Name'];
        parent::__construct();                
    }
    protected function prepare() {
        parent::prepare();                
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр производителей', 'link' => 'contractors'],
            ['name' => 'Реестр категорий производителей', 'link' => 'contractors-category']
        ];
    }
    
  
}
