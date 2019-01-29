<?php

namespace app\stat\admin;

use app\stat\Configuration;

class AdminGlobal extends AdminForm
{        


    public function __construct() {
        $this->name = l('GLOBAL_SETTINGS','admin');
        $this->link = 'global';
        $this->parentElement = 'Index';
        $this->left_block = true;
        parent::__construct();
        
    }
    
    protected function formConfigure() {
        parent::formConfigure(); 
        $rows_count_list = [
            ['text' => 10,'value' => 10],
            ['text' => 20,'value' => 20],
            ['text' => 50,'value' => 50],
            ['text' => 100,'value' => 100],
        ];
        $news_count = Configuration::get('MainPageNewsCount');
        $rows_count_selected = Configuration::get('RowCount',20);
      /*  $this->form_elements['main_form']['elements_list']['PortalName'] = [
            'label' => l('GLOBAL_SETTINGS_PORTAL_NAME','admin'),
            'type' => 'text',
            'size' => 300,
            'value' => \Rosagromash\Configuration::get('PortalName'),
            'required' => true
        ];*/

        $this->form_elements['main_form']['elements_list']['RowCount'] = [
            'label' => l('GLOBAL_SETTINGS_ROWS_COUNT','admin'),
            'type' => 'select',
            'size' => 50,
            'selected' => $rows_count_selected,
            'elements' => $rows_count_list,
            'description' => l('GLOBAL_SETTINGS_ROWS_COUNT_DESCRIPTION','admin'),
        ];
        $this->form_elements['main_form']['elements_list']['MainPageNewsCount'] = [
            'label' => l('GLOBAL_SETTINGS_MAIN_PAGE_NEWS_COUNT','admin'),
            'type' => 'number',
            'size' => 50,
            'value' => $news_count ? $news_count : 1,
        ];
        
    }
    protected function setLeftBlockVars() { 
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][1] = [
            'block_alias' => 'fast_link',
            'block_name' => l('SETTINGS_CATEGORY', 'admin'),
            'type' => 'ref_list',
            'refs' => [
                ['name' => l('GLOBAL_SETTINGS','admin'), 'link' => '/admin/global'],
                ['name' => l('MAIL_SETTINGS','admin'), 'link' => '/admin/mail-settings'],
                ['name' => l('OPERATORS_SETTINGS','admin'), 'link' => '/admin/operators-settings'],
            ]
        ];
    }
    public function getBreadcrumbs() {
        return false;
    }
}
