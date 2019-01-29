<?php

namespace app\stat\admin;

use app\stat\model\Classifier;
use app\stat\model\OperatorsConfig;
/**
 * Description of AdminOperatorsSettings
 *
 * @author kotov
 */
class AdminOperatorsSettings extends AdminForm
{
    public function __construct()
    {
        $this->name = l('OPERATORS_SETTINGS','admin');
        $this->link = 'operators-settings';
        $this->parentElement = 'Global';
        $this->left_block = true;
        $this->_model_name = 'OperatorsConfig'; 
        $this->submit_button_method = 'saveModel';
        $this->form_name = 'form-for-model';
        parent::__construct();        
    }
    public function prepare()
    {
       parent::prepare();
       
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
    public function formConfigure()
    {
        $classifierList = Classifier::getRowsArray([
            ['TBLCLASSIFIER','Id'],
            ['TBLCLASSIFIER','Name']
            ],[['param' => 'ClassifierId','staticNumber' => Classifier::ROOT_ELEMENT]],[['textValue' => '"TBLCLASSIFIER"."OrderIndex" DESC NULLS LAST']]);
       $params = OperatorsConfig::getCurrentConfig();                      
       parent::formConfigure();
       
       if (!empty($params)) {
            $this->form_elements['form_class'] = 'edit_form';
            $this->form_elements['main_form']['element_id'] = $params['id'];
       }
        foreach ($classifierList as $classifierElement) {
            $this->form_elements['main_form']['elements_list']['element['.$classifierElement['Id'].']'] = [
                'label' => $classifierElement['Name'],
                'type' => 'mail',
                'size' => 250,
                'value' => $params['element'][$classifierElement['Id']] ?: '',
                'required' => true
            ];
        }
    }

    public function setTemplateVars()
    {
        parent::setTemplateVars();                
    }
}
