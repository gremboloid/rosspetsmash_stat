<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;

use app\stat\model\UnitOfMeasure;

/**
 * Description of AdminUnits
 *
 * @author kotov
 */
class AdminUnits extends AdminDirectory
{
        //put your code here
    public function __construct()
    {
        $this->link = 'units';
        $this->name = l('GLOB_UNITS_SETTINGS','admin');
        $this->parentElement = 'Index';  
        $this->directory_model = new UnitOfMeasure();
        $this->model_name = 'UnitOfMeasure';
        
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('UNIT_NAME','admin'),
                                      'ShortName' => l('UNIT_SHORT_NAME','admin')];
        parent::__construct();                
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр валют', 'link' => 'currency'],
        ];
    }
}
