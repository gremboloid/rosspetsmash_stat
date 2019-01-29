<?php

namespace app\stat\admin;
use app\stat\Configuration;

/**
 * Description of AdminMailSettings
 *
 * @author kotov
 */
class AdminMailSettings extends AdminForm
{
    public function __construct() {
        $this->name = l('MAIL_SETTINGS','admin');
        $this->link = 'mail-settings';
        $this->parentElement = 'Global';
        $this->left_block = true;
        parent::__construct();        
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
            ]
        ];
    }
    protected function formConfigure() {
        parent::formConfigure(); 
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
    }
    
}
