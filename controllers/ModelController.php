<?php

namespace app\controllers;

use app\stat\Tools;
use Yii;
use app\stat\helpers\ModelsHelper;
use app\stat\services\ReportsLogService;
use app\stat\model\Models;
/**
 * Description of ModelController
 *
 * @author kotov
 */
class ModelController extends FrontController
{
    public $namespace = 'app\\stat\\model\\';
    
    public function init() {
        parent::init();
        $this->enableCsrfValidation = false;
    }
    public function actionDisplayInfoBlock() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['object']) || empty($postData['id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $className = $this->namespace . $postData['object'];
        if (!class_exists($className)) {
             return Tools::getErrorMessage('Класс не существует',1); 
        }
        $modelsHelper = ModelsHelper::getInstance(new $className((int) $postData['id']));
        return $modelsHelper->getInfoBlock();
    }
   public function actionDisplayForm() {
        if (!Yii::$app->request->isAjax || 
                !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['model'])) {
            return Tools::getErrorMessage('request error',1); 
        }        
        $modelClassName = DEFAULT_NAMESPACE_FOR_MODELS . $postData['model'];
        $modelParent = DEFAULT_NAMESPACE_FOR_MODELS . 'ObjectModel';
        if (!(class_exists($modelClassName) && is_subclass_of($modelClassName, $modelParent))) {
            return Tools::getErrorMessage('request error',1); 
        }
        $model = new $modelClassName($postData['id']);
        return $model->displayForm();
    }
    public function actionSaveModel() {
        /*if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }*/
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['form'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $modelClassName = DEFAULT_NAMESPACE_FOR_MODELS . $postData['model'];
        $modelParent = DEFAULT_NAMESPACE_FOR_MODELS . 'ObjectModel';
        if (!(class_exists($modelClassName) && is_subclass_of($modelClassName, $modelParent))) {
            return Tools::getErrorMessage('request error',1); 
        }
         $model = new $modelClassName($postData['id']);
        $res = $model->saveModelObject($postData['form']);
        return $res;
    }
    public function actionDeleteModel () 
    {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $modelClassName = DEFAULT_NAMESPACE_FOR_MODELS . $postData['model'];
        $modelParent = DEFAULT_NAMESPACE_FOR_MODELS . 'ObjectModel';
        if (!(class_exists($modelClassName) && is_subclass_of($modelClassName, $modelParent))) {
            return Tools::getErrorMessage('request error',1); 
        }
        $model = new $modelClassName($postData['id']);
        $result = $model->delete();
        if ($result) {
            return Tools::getMessage('Запись успешно удалена');
        }
      //  $res = $model->saveModelObject($postData['form']);
    //    return $res;
        return Tools::getErrorMessage('Ошибка удаления. Обратитесь к администратору.');
        
    }
    public function actionGetCities() {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $modelClassName = DEFAULT_NAMESPACE_FOR_MODELS . $postData['model'];
        $modelParent = DEFAULT_NAMESPACE_FOR_MODELS . 'ObjectModel';
        if (!(class_exists($modelClassName))) {
            return Tools::getErrorMessage('request error',1); 
        }
        $model = new $modelClassName($postData['id']);
        $res = $model->getCitiesList();
        return $res;                
    }
    
    public function actionGetRegions() {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $modelClassName = DEFAULT_NAMESPACE_FOR_MODELS . $postData['model'];
        $modelParent = DEFAULT_NAMESPACE_FOR_MODELS . 'ObjectModel';
        if (!(class_exists($modelClassName))) {
            return Tools::getErrorMessage('request error',1); 
        }
        $model = new $modelClassName($postData['id']);
        $res = $model->getRegionsList();
        return $res;  
        
    }
    public function actionGetClassifierId() {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['model_id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $model = new Models($postData['model_id']);
        return Tools::getMessage($model->classifierId);
    }
    public function actionDetailLogInfo() {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['frm_data'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $message = ReportsLogService::getDetailLogInfo($postData['frm_data']);
        return Tools::getMessage($message);
        
    }
    
}
