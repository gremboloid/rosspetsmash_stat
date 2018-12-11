<?php

namespace app\controllers;

use app\stat\Sessions;
use app\stat\Tools;
use Yii;
use app\stat\services\ClassifierService;
use app\stat\services\ContractorService;
use app\stat\helpers\ModalHelper;
use app\stat\Configuration;
use app\stat\Mailer;
use app\stat\model\Brand;
use app\stat\model\PerformanceAttr;
/**
 * Description of CustomController
 *
 * @author kotov
 */
class CustomController extends FrontController
{    
    public function init() {
        parent::init();
        $this->enableCsrfValidation = false;
    }
    public function actionWriteSessionValue() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['name']) || empty($postData['param'])) {
            return  Tools::getErrorMessage('request error',1);
        }
        Sessions::setValueToVar($postData['name'], $postData['param']);
    }
    public function actionReadSessionValue() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['name'])) {
            return  Tools::getErrorMessage('request error',1);
        }
        //Sessions::gValueToVar($postData['name'], $postData['param']);
        return Tools::getMessage(Sessions::getVarSession($postData['name']));
    }
    
    public function actionGetClassifierJson() {
        $classifierService = new ClassifierService($this->contractor->getId());
        return $classifierService->getClassifierListJSON();
    }
    
    public function actionShowDateChange() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['params'])) {
            return  Tools::getErrorMessage('request error',1);
        }
        $modalHelper = new ModalHelper();
        $message = $modalHelper->showDateChangeModal($postData['params']);
        return Tools::getMessage($message);
    }
    public function actionGetParentClassifier() 
    {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['id'])) {
            return  Tools::getErrorMessage('request error',1);
        }
        return Tools::getMessage(ClassifierService::getParentClassifierString($postData['id']));
        
    }
    public function actionSaveConfiguration() {
        if (!is_admin() ||
            !Yii::$app->request->isAjax ||
            !Yii::$app->request->isPost ) {
            return Tools::getErrorMessage('request error',1);
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['frm_data'])) {
            return Tools::getErrorMessage('request error',1);
        }
       Configuration::saveConfigurationData($postData['frm_data']);
       return Tools::getMessage('Конфигурация успешно сохранена');
    }    
    public function actionParentClassifierChange() {
        if (!Yii::$app->request->isAjax || 
                !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['object'])) {
            return Tools::getErrorMessage('request error',1); 
        }        
        $modelClassName = DEFAULT_NAMESPACE_FOR_MODELS . $postData['object']['model'];
        $modelInterface = DEFAULT_NAMESPACE_FOR_MODELS . 'IChangeClassifier';
        if (!(class_exists($modelClassName) && is_subclass_of($modelClassName, $modelInterface))) {
            return Tools::getErrorMessage('request error',1); 
        }
        
        return $modelClassName::changeClassifier($postData['object']['elements'],$postData['object']['classifier']);
    }
    public function actionGetContractorName() {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isGet) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $getData = Yii::$app->request->get();
        if (empty($getData['id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        $brand = new Brand($getData['id']);
        return Tools::getMessage($brand->getContractorName());
    }
    public function actionDeleteCharacteristic() 
    {
        if (!Yii::$app->request->isAjax || 
            !is_admin()) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isGet) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $getData = Yii::$app->request->get();
        if (empty($getData['id'])) {
            return Tools::getErrorMessage('request error',1); 
        }
        
        $perfomanceAttr = new PerformanceAttr($getData['id']);
        $perfomanceAttr->delete();
        return Tools::getMessage('ok');        
    }
    public function actionIsEmailExist() 
    {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['email'])) {
            return  Tools::getErrorMessage('request error',1);
        }
       return Mailer::isEmailExist($postData['email']);
    }
    public function actionSendNewPassword() 
    {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['email'])) {
            return  Tools::getErrorMessage('request error',1);
        }
        $mailer = new Mailer();        
        return $mailer->sendNewPassword($postData['email']);
    }
    public function actionGetClassifierCsv() {
        if (Yii::$app->user->isGuest) {
            die('access denied');
        }
        $classifierService = new ClassifierService(Yii::$app->user->getIdentity()->contractorId);
        $classifierService->getClassifierCsv();
        die();
    }
    public function actionGetExcelContractors()
    {
        if (Yii::$app->user->isGuest) {
            die('access denied');
        }
        if (!Yii::$app->request->isGet) {
            die('bad request');
        }
        $params = [];
        $params['month'] = Yii::$app->request->get('month');
        $params['year'] = Yii::$app->request->get('year');
        $params['filter'] = Yii::$app->request->get('fltr');
        $params['category'] = Yii::$app->request->get('cat');
        ContractorService::getExcelContractors($params);
        die();        
    }
    
}
