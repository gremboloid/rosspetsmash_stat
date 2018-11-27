<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;


use app\stat\model\Country;
/**
 * Description of AdminCounties
 *
 * @author kotov
 */
class AdminCountries extends AdminDirectory
{
//put your code here
    public function __construct()
    {
        $this->link = 'countries';
        $this->name = l('GLOB_COUNTRIES_SETTINGS','admin');
        $this->parentElement = 'Index';  
        $this->directory_model = new Country();  
        $this->model_name = 'Country';
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('COUNTRY','admin')];
		$this->cols_to_sort = ['Name'];
        parent::__construct();                
    }
    protected function prepare() {
        parent::prepare();                
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр городов', 'link' => 'cities'],
            ['name' => 'Реестр регионов', 'link' => 'regions']
        ];
    }
}
