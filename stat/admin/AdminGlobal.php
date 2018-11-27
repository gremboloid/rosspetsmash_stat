<?php

namespace app\stat\admin;

use app\stat\Configuration;

class AdminGlobal extends AdminForm
{        


    public function __construct() {
        $this->name = l('GLOBAL_SETTINGS','admin');
        $this->link = 'global';
        $this->parentElement = 'Index';
        $this->left_block = false;
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
        $this->form_elements['main_form']['elements_list']['AdminEmail'] = [
            'label' => l('MAIL_SETTINGS_ADMIN_EMAIL','admin'),
            'type' => 'text',
            'size' => 300,
            'value' => Configuration::get('AdminEmail'),
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['SMTPServer'] = [
            'label' => l('MAIL_SETTINGS_SMTP_SERVER','admin'),
            'type' => 'text',
            'size' => 300,
            'value' => Configuration::get('SMTPServer'),
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['SMTPPort'] = [
            'label' => l('MAIL_SETTINGS_SMTP_PORT','admin'),
            'type' => 'text',
            'size' => 30,
            'value' => Configuration::get('SMTPPort'),
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['SenderName'] = [
            'label' => l('MAIL_SETTINGS_SENDER_NAME','admin'),
            'type' => 'text',
            'size' => 300,
            'value' => Configuration::get('SenderName'),
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['MailUserName'] = [
            'label' => l('MAIL_SETTINGS_USERNAME','admin'),
            'type' => 'text',
            'size' => 300,
            'value' => Configuration::get('MailUserName'),
            'required' => true
        ];
        $this->form_elements['main_form']['elements_list']['MailPassword'] = [
            'label' => l('MAIL_SETTINGS_PASSWORD','admin'),
            'type' => 'password',
            'size' => 300,
            'value' => Configuration::get('MailPassword'),
            'required' => true
        ];
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
    public function getBreadcrumbs() {
        return false;
    }
}
