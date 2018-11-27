<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;

use app\stat\model\Classifier;
use app\stat\Tools;
use app\stat\services\ClassifierService;
use app\stat\Sessions;
use Yii;

/**
 * Description of AdminClassifier
 *
 * @author kotov
 */
class AdminClassifier extends AdminDirectory
{    
    //put your code here
     public function __construct()
    {
        $this->link = 'classifier';
        $this->name = l('CLASSIFIER_SETTINGS','admin');
        $this->parentElement = 'Index';
        $this->directory_model = new Classifier();
        $this->model_name = 'Classifier';
        $this->table_cols_headers = [ 'Number' => '№',
                'Name' => l('MODELS_NAME','admin'),
                'OrderIndex' => l('CLASSIFIER_INDEX','admin'),
            ]; 
                // начальная инициализация фильтров
        $this->filter_array = [
            'parent' => 41,
        ];
        $this->cols_to_sort = ['Name','OrderIndex'];
        $this->group_operations = true;
        $this->group_operations_conf = [
            'change_classifier' => true ,  // наличие элемента "сменить раздел классификатора"
            'change_classifier_conf' => [  // конфигурация элемента "сменить раздел классификатора"
                'block_id' => 'replace_classifier'
            ]
        ];
        $this->group_operations_list = [
            [
                'value' => 'replace_classifier',
                'text' => l('REPLACE_CLASSIFIER','admin')
            ]
        ];
        
        parent::__construct();   
        
    }
    protected function prepare() {
        parent::prepare();      
       // $this->tpl_vars['classifier_json'] = \Model\Classifier::getClassifierListJSON();
    }
    protected function loadFilters() {
        parent::loadFilters();
        if ($val = (int) Tools::getValue('classifier','GET')) {
            $this->filter_array['parent'] = $val;
        }
        $this->hidden_inputs[] = [
            'id' => 'classifier',
            'name' => 'classifier',
            'value' => $this->filter_array['parent']
        ];        
    }
    protected function setLeftBlockVars() {
        
        $parent = new Classifier($this->filter_array['parent']);
        $parent_name = $parent->getName();
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр производителей', 'link' => 'contractors'],
            ['name' => 'Реестр моделей', 'link' => 'models'],
        ];
        $this->left_block_vars['blocks_list'][1]['hide'] = false;
        $this->left_block_vars['blocks_list'][1]['elements'] = [
            //        [ 'type' => 'test','header_text' => 'Тест'],
                    [ 'type' => 'change_button',
                      'header_text' => l('FILTERS_CLASSIFIER','admin'),
                      'text_element' => [
                        'id' => 'current_classifier',
                        'text' => $parent_name                 
                      ],
                        'button_element' => [
                            'id' => 'get_classifier',
                            'text' => l('BTN_ACTIONS')['change'],
                            'class_name' => 'constructor_action'
                            
                        ]
                    ]
            ];
        $this->left_block_vars['blocks_list'] = array_reverse($this->left_block_vars['blocks_list']);
        
    }
    function setTemplateVars() { 
        $classifierService = new ClassifierService(Yii::$app->user->getIdentity()->getContractorId());
        $actions = l('BTN_ACTIONS');
        $this->button_add_active = true;
        $current_classifier = new Classifier($this->filter_array['parent']);  
        $classifier_list = ClassifierService::getClassifierParents($this->filter_array['parent']);
        $classifier_list[0]['last_element'] = true;
        Sessions::setValueToVar('PARENT_CLASSIFIER_ID', $current_classifier->getId());
        if ($current_classifier->isLeaf()) {
            $classifier_list[0]['leaf'] = true;            
        } else {
            $classifier_list[0]['leaf'] = false;            
            $this->tpl_vars['classifier_childs'] = $current_classifier->getChilds([['Id'],['Name']]);                       
        }
        $this->tpl_vars['classifier_breadcrumbs'] = array();
        $classifier_list = array_reverse($classifier_list);
        foreach ($classifier_list as $key => $elem) {
            $this->tpl_vars['classifier_breadcrumbs'][$key] = [
                'id' => $elem['Id'],
                'name' => $elem['Name']
            ];
            if (key_exists('last_element', $elem)) {
                $last_element_idx = $key;
                $this->tpl_vars['classifier_breadcrumbs'][$key]['leaf'] = $elem['leaf'];
            }
        }
        $m_idx = key($this->modals_list);
        $m_idx = $m_idx !== null ? $m_idx + 1 : 0;
        $this->modals_list[$m_idx] = [
            'id' => 'classifiermodal',
            'type' => 'ajax-modal',
            'head_message' => 'Выберите раздел классификатора',
            'search_elem' => 'classifier-search',
            'class' => 'classifier_tree',
            'btnok_id' => 'select-classifier',
            'btnok_text' => $actions['select']
        ];
        parent::setTemplateVars();
        $this->tpl_vars['classifier_json'] = $classifierService->getClassifierListJSON();
    }

    
    
    
}