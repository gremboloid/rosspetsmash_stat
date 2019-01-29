<?php

namespace app\controllers;

use Yii;
use app\stat\model\ModelRequest;
use app\stat\services\ClassifierService;
use app\stat\helpers\TechCharacteristicFormHelper;
use app\stat\Tools;
/**
 * Description of ModelRequestController
 *
 * @author kotov
 */
class ModelRequestController extends FormManagementController
{
    public $controllerName = 'model-request';
    
    protected $formId = 'form-for-config';
    
    public function actionIndex() 
    {
        return $this->render('profiles.twig', $this->tpl_vars);
    }
    public function actionGetFormCharacteristic() 
    {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = (int) Yii::$app->request->post('classifierId'); 
        if (empty($postData)) {
            return  Tools::getErrorMessage('request error',1);
        }
        $techCharacteristicHelper = new TechCharacteristicFormHelper($postData);
        try {
            $htmlData = $techCharacteristicHelper->getHtmlForm();
        } catch (\DomainException $e) {
            Tools::getErrorMessage($e->getMessage(),1);
        }
        return Tools::getMessage($htmlData);
    }

    public function initVars()
    {
        $actions = l('BTN_ACTIONS');
        $classifierService = new ClassifierService(Yii::$app->user->getIdentity()->getContractorId());
        parent::initVars();
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('MODEL_REQUEST');        
        $this->tpl_vars['head_text'] = l('MODEL_REQUEST');   
        $this->tpl_vars['classifier_json'] = $classifierService->getClassifierListJSON(null,true);
        $this->tpl_vars['modals_list'][0] = [
            'id' => 'classifiermodal',
            'type' => 'ajax-modal',
            'head_message' => 'Выберите раздел классификатора',
            'search_elem' => 'classifier-search',
            'class' => 'classifier_tree',
            'btnok_id' => 'select-classifier',
            'btnok_text' => $actions['select']
        ];
     //   $this->tpl_vars['template'] = 'model-request';
        
    }
    public function setParams()
    {
        parent::setParams();
        $this->model = new ModelRequest();
        
    }
    
}
