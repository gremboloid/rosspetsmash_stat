<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;

use app\stat\model\User;
use app\stat\model\Role;
use app\stat\Tools;
use app\stat\services\ContractorService;
/**
 * Description of AdminUsers
 *
 * @author kotov
 */
class AdminUsers extends AdminDirectory
{
    //put your code here
    public function __construct()
    {
        $this->link = 'users';
        $this->name = l('GLOB_USERS_SETTINGS','admin');
        $this->parentElement = 'Index';  
        $this->directory_model = new User(); 
        $this->model_name = 'User';
        
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Fio' => l('USER_FIO','admin'),
                                      'ContractorName' => l('CONTRACTOR_HEAD')
                                    ];
        $this->cols_to_sort = ['Fio','ContractorName'];
        $this->defaultSortField = 'Fio';
        $this->filter_array = [
            'contractor' => 0,
            'role' => 0
        ];        
        parent::__construct();                
    }
    protected function prepare() {
        parent::prepare();                
    }
    protected function loadFilters() {
        parent::loadFilters();
        if ($val = (int) Tools::getValue('contractor','GET')) {
            $this->filter_array['contractor'] = $val;
        }
        if ($val = (int) Tools::getValue('role','GET')) {
            $this->filter_array['role'] = $val;
        }
        $val = Tools::getValue('present','GET');
        if ($val !== false) {
            $this->filter_array['present'] = (int)$val;              
        }
    }

    protected function setLeftBlockVars() {
        $contractor_selected = $this->filter_array['contractor'] ? $this->filter_array['contractor'] : 0;
        $contractorsPresent = [
          ['value' => 0, 'text' => 'Все производители'],
          ['value' => 1, 'text' => 'Присутствующие на портале']
        ];
        $contractorsPresentSelected = ($this->filter_array['present'] !== false) ? $this->filter_array['present'] : 1;
        $role_selected = $this->filter_array['role'] ? $this->filter_array['role'] : 0;
        $contractors_list = ContractorService::getActualContractors(
                [['TBLCONTRACTOR','Id','value'],['TBLCONTRACTOR','Name','text']],
                true,
                (bool) $contractorsPresentSelected);
        $roles_list = Role::getRows([['TBLROLE','Id','value'],['TBLROLE','Name','text']]);
        array_unshift($contractors_list, ['value' => 0, 'text' => 'Выбраны все']);         
        array_unshift($roles_list, ['value' => 0, 'text' => 'Все роли']);         
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр стран', 'link' => 'countries'],
            ['name' => 'Реестр производителей', 'link' => 'contractors']
        ];
        $this->left_block_vars['blocks_list'][1]['hide'] = false;
        $this->left_block_vars['blocks_list'][1]['elements'] = [
            [   'type' => 'list',
                'header_text' => l('FILTERS_CONTRACTORS','admin'),
                'elements' => [
                    [
                        'type' => 'select',
                //'header_text' => l('FILTERS_CONTRACTORS_PRESENT','admin'),
                        'class_name' => 'rs-form-control',
                        'name' => 'present',
                        'options' => $contractorsPresent,
                        'selected' => $contractorsPresentSelected                        
                    ],                    
                    [
                        'type' => 'select',
                        'class_name' => 'rs-form-control',
                        'id' => 'select-contractor',
                        'name' => 'contractor',
                        'options' => $contractors_list,
                        'selected' => $contractor_selected
                    ]
                ]                        
            ],
            [   'type' => 'select',
                        'header_text' => l('FILTERS_ROLES','admin'),
                        'class_name' => 'rs-form-control',
                        'id' => 'select-role',
                        'name' => 'role',
                        'options' => $roles_list,
                        'selected' => $role_selected
                        
            ],
        ];
        $this->left_block_vars['blocks_list'] = array_reverse($this->left_block_vars['blocks_list']);
    }
    
}
