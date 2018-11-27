<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;

use app\stat\model\Region;
use app\stat\model\Country;
use app\stat\Tools;
/**
 * Description of AdminRegions
 *
 * @author kotov
 */
class AdminRegions extends AdminDirectory
{
     public function __construct()
    {
        $this->link = 'regions';
        $this->name = l('GLOB_REGIONS_SETTINGS','admin');
        $this->parentElement = 'Countries';  
        $this->directory_model = new Region();
        $this->model_name = 'Region';
        
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('REGION','admin'),
                                      'CountryName' => l('COUNTRY','admin')];
        $this->cols_to_sort = ['Name','CountryName'];
        $this->filter_array = [
            'country' => 0,
        ];
        parent::__construct();                
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $countries_list = Country::getRowsArray(
                [['TBLCOUNTRY','Id','value'],['TBLCOUNTRY','Name','text']],null,[['name' => 'Name']]);
        $country_selected = $this->filter_array['country'] ? $this->filter_array['country'] : 0;
        array_unshift($countries_list, ['value' => 0, 'text' => 'Все страны']);
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр городов', 'link' => 'cities'],
            ['name' => 'Реестр стран', 'link' => 'countries']
        ];
        $this->left_block_vars['blocks_list'][1]['hide'] = false;
        $this->left_block_vars['blocks_list'][1]['elements'] = [
            //        [ 'type' => 'test','header_text' => 'Тест'],
                    [   'type' => 'select',
                        'header_text' => l('FILTERS_COUNTRY','admin'),
                        'class_name' => 'rs-form-control',
                        'id' => 'select-country',
                        'name' => 'country',
                        'options' => $countries_list,
                        'selected' => $country_selected
                        
                    ]
                ];
        $this->left_block_vars['blocks_list'] = array_reverse($this->left_block_vars['blocks_list']);
        
    }
    protected function loadFilters() {
        parent::loadFilters();
        if ($val = (int) Tools::getValue('country','GET')) {
            $this->filter_array['country'] = $val;
        }
    }
}
