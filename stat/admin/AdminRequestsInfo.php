<?php

namespace app\stat\admin;

use app\stat\model\ModelRequest;
/**
 * Description of AdminRequestsInfo
 *
 * @author kotov
 */
class AdminRequestsInfo extends AdminRoot
{
    
    protected $modelRequest;
    protected $elementsList = array();


    public function __construct($id = null)
    {
        $this->name = l('GLOB_MODEL_REQUESTS_INFO','admin');
        $this->parentElement = 'ModelRequests';
        $this->left_block = false;
        $this->template_file = 'admin-info';
        $this->modelRequest = new ModelRequest($id); 
        parent::__construct();
    }
    protected function prepare()
    {
        parent::prepare();
        $actions = l('BTN_ACTIONS');
        $this->elementsList = $this->modelRequest->getInformBlockElements();
        $this->modals_list[] = [
            'id' => 'classifiermodal',
            'type' => 'ajax-modal',
            'head_message' => 'Выберите раздел классификатора',
            'search_elem' => 'classifier-search',
            'class' => 'classifier_tree',
            'btnok_id' => 'select-classifier',
            'btnok_text' => $actions['select']
        ];

    }
    public function setTemplateVars()
    {
        parent::setTemplateVars();
        $this->tpl_vars['infoblock_exist'] = $this->modelRequest->isExistInDb();
        $this->tpl_vars['not_found_error'] = l('MODEL_NOT_FOUND','messages');
        $this->tpl_vars['information_block_header'] = l('REQUESTS_INFO_HEAD','admin').' '.$this->modelRequest->id;
        $this->tpl_vars['inform_data'] = $this->elementsList;
        if ($this->modelRequest->publicated != 1) {
            $this->tpl_vars['buttons_list'] = [
                [
                    'id' => 'getModelForRequestForm',
                    'text' => l('BUTTON_SEND_FROM_REQUEST'),
                    'data' => ['id' => $this->modelRequest->getId()]
                ],
            ];
        }
    }
}
