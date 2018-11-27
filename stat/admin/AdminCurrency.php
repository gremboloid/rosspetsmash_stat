<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;


use app\stat\model\Currency;
/**
 * Description of AdminCorrency
 *
 * @author kotov
 */
class AdminCurrency extends AdminDirectory
{
    //put your code here
    public function __construct()
    {
        $this->link = 'currency';
        $this->name = l('GLOB_CURRENCY_SETTINGS','admin');
        $this->parentElement = 'Index';  
        $this->directory_model = new Currency();
        
        $this->table_cols_headers = [ 'Number' => '№',
                                       'FullName' => l('CURRENCY','admin'),
                                      'Name' => l('CURRENCY_SHORT_NAME','admin')];
        $this->model_name = 'Currency';
        parent::__construct();                
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр единиц измерения', 'link' => 'units'],
        ];
    }
}
