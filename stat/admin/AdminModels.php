<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;


use app\stat\model\Models;
use app\stat\Tools;
use app\stat\model\Classifier;
use app\stat\services\ClassifierService;
use app\stat\model\Contractor;
use app\stat\model\ModelType;
use app\stat\services\ContractorService;
use app\stat\Sessions;
use Yii;
//use Model
/**
 * Description of AdminModels
 *
 * @author kotov
 */
class AdminModels extends AdminDirectory
{
        //put your code here
     public function __construct()
    {
        $this->link = 'models';
        $this->name = l('GLOB_MODELS_SETTINGS','admin');
        $this->parentElement = 'Index';  
        $this->directory_model = new Models(); 
        $this->model_name = 'Models';
        
        $this->table_cols_headers = [ 'Number' => '№',
                                      'Name' => l('MODELS_NAME','admin'),
                                       'ContractorName' =>'Производитель'];
        // начальная инициализация фильтров
        $this->filter_array = [
            'classifier' => 41,
            'contractor' => 0,
            'model_type' => 0
        ];
        $this->cols_to_sort = ['Name','ContractorName'];
        $this->group_operations = true;
        $this->group_operations_conf = [
            'change_classifier' => true ,  // наличие элемента "сменить раздел классификатора"
            'change_classifier_conf' => [  // конфигурация элемента "сменить раздел классификатора"
                'block_id' => 'replace_model'
            ]
        ];
        $this->group_operations_list = [
            [
                'value' => 'replace_model',
                'text' => l('REPLACE_CLASSIFIER','admin')
            ]
        ];
        parent::__construct();                
    }
    protected function prepare() {
        parent::prepare();
        array_unshift($this->action_list, ['action' => 'gotoClassifier',
                        'name' => l('GOTO_CLASSIFIER'),
                        'image' => 'classificator.gif']) ;
    }
    protected function loadFilters() {
        parent::loadFilters();
        if ($val = (int)Tools::getValue('contractor','GET')) {
            $this->filter_array['contractor'] = $val;
            //unset($this->table_cols_headers['ContractorName']);
        }
        if ($val = (int)Tools::getValue('classifier','GET')) {
            $this->filter_array['classifier'] = $val;
        }
        if ($val = (int)Tools::getValue('model_type','GET')) {
            $this->filter_array['model_type'] = $val;
        }
        $this->hidden_inputs[] = [
            'id' => 'classifier',
            'name' => 'classifier',
            'value' => $this->filter_array['classifier']
        ];
    }
    protected function setLeftBlockVars() {
        
        $classifier = new Classifier($this->filter_array['classifier']);
        $classifier_name = $classifier->getName();
        $contractor_selected = $this->filter_array['contractor'] ? $this->filter_array['contractor'] : 0;
        $model_type_selected = $this->filter_array['model_type'] ? $this->filter_array['model_type'] : 0;
        $contractors_list = ContractorService::getActualContractors([['TBLCONTRACTOR','Id','value'],['TBLCONTRACTOR','Name','text']]);
        $model_type_list = ModelType::getRowsArray([['TBLMODELTYPE','Id','value'],['TBLMODELTYPE','Name','text']]);
        array_unshift($contractors_list, ['value' => 0, 'text' => 'Все производители']);
        array_unshift($model_type_list, ['value' => 0, 'text' => 'Выбраны все']);
        parent::setLeftBlockVars();
        $this->left_block_vars['blocks_list'][0]['refs'] = [
            ['name' => 'Реестр производителей', 'link' => 'contractors'],
            ['name' => 'Реестр брендов', 'link' => 'brands'],
        ];
        
        $this->left_block_vars['blocks_list'][1]['hide'] = false;
        $this->left_block_vars['blocks_list'][1]['elements'] = [
            //        [ 'type' => 'test','header_text' => 'Тест'],
                    [ 'type' => 'change_button',
                      'header_text' => l('FILTERS_CLASSIFIER','admin'),
                      'text_element' => [
                        'id' => 'current_classifier',
                        'text' => $classifier_name                 
                      ],
                        'button_element' => [
                            'id' => 'get_classifier',
                            'text' => l('BTN_ACTIONS')['change'],
                            'class_name' => 'constructor_action'
                            
                        ]
                    ],
                    [   'type' => 'select',
                        'header_text' => l('FILTERS_CONTRACTORS','admin'),
                        'class_name' => 'rs-form-control',
                        'id' => 'select-contractor',
                        'name' => 'contractor',
                        'options' => $contractors_list,
                        'selected' => $contractor_selected
                        
                    ],
                    [
                        'type' => 'select',
                        'header_text' => l('FILTERS_MODEL_TYPE','admin'),
                        'class_name' => 'rs-form-control',
                        'id' => 'select-model-type',
                        'name' => 'model_type',
                        'options' => $model_type_list,
                        'selected' => $model_type_selected
                    ]
                    
                ];
        $this->left_block_vars['blocks_list'] = array_reverse($this->left_block_vars['blocks_list']);
    }
    function setTemplateVars() {
        $classifierService = new ClassifierService(Yii::$app->user->getIdentity()->getContractorId());
        $actions = l('BTN_ACTIONS');
        $current_classifier = new Classifier($this->filter_array['classifier']);        
        $classifier_list = $classifierService->getClassifierParents($this->filter_array['classifier']);
        $classifier_list[0]['last_element'] = true;
        if ($current_classifier->isLeaf()) {
            $classifier_list[0]['leaf'] = true;
            Sessions::setValueToVar('CLASSIFIER_ID', $current_classifier->getId());
        } else {
            $classifier_list[0]['leaf'] = false;
            $this->button_add_active = false;
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
        
        parent::setTemplateVars();
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
        $this->tpl_vars['classifier_json'] = $classifierService->getClassifierListJSON();
        
        
    }

    
}
