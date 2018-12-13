<?php

namespace app\controllers;

use app\stat\report\Params;
use app\stat\Sessions;
use app\stat\Tools;
use app\stat\report\Container;
use app\stat\report\ModalViewer;
use app\stat\services\DatasourceService;
use app\stat\services\ClassifierService;
use app\stat\ViewHelper;
use app\stat\model\Contractor;
use Yii;
use app\stat\DatePeriod;
/**
 * Description of ReportsController
 *
 * @author kotov
 */
class ReportsController extends FrontController
{
    public $controllerName = 'reports';
    /** @var Params Параметры отчета */
    protected $reportParams;
    /** @var array Выбранные отчеты */
    protected $reports = array();


    public function init() {
        parent::init();       
        $this->enableCsrfValidation = false;
    }
    

    public function actionIndex() {
         return $this->render('constructor.twig', $this->tpl_vars);
    }
    
    public function initVars() {
        parent::initVars();
        $datasourceService = new DatasourceService($this->contractor);
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('REPORT_CONSTRUCTOR','report');
        $this->tpl_vars['content_header'] = l('REPORT_CONSTRUCTOR','report');
        $this->tpl_vars['report_params_header'] = l('REPORT_PARAMS','report');
        $this->tpl_vars['classifier_header'] = l('CLASSIFIER_SECTION','report');
        if (is_array($this->reportParams->classifier)) {
            $classifierId = [];
            foreach ($this->reportParams->classifier as $classifier) {
                $this->tpl_vars['clasifier_default'][] = $classifier->getName();
                $classifierId[] = (int) $classifier->id;
            }
            $this->tpl_vars['classifier_id'] = json_encode($classifierId);
        } else {
            $this->tpl_vars['clasifier_default'] = $this->reportParams->classifier->getName();
            $this->tpl_vars['classifier_id'] = $this->reportParams->classifier->id;
        }
        $this->tpl_vars['clasifier_full_default'] = $this->reportParams->fullReportClassifier->getName();
        // Список для полного отчета
       $this->tpl_vars['full_report_classifier'] = ClassifierService::getFullReportClassifier();
        
        $this->tpl_vars['report_type'] = l('REPORT_TYPE','report');
        //Массив доступных типов отчетов
       $this->tpl_vars['type_reports_list'] = $this->reportParams->typeReportList;
       // меню источника данных
       $this->tpl_vars['data_source_header'] = l('DATA_SOURCE','report');
       $this->tpl_vars['data_source_default'] = $this->reportParams->datasource->getName();
        // Список доступных источников данных
       $this->tpl_vars['data_source_list'] = $datasourceService->getAvailableDatasoure();
       
        // меню выбора периодов
       $this->tpl_vars['time_periods'] = l('TIME_PERIODS','report');
       // для элементов управления "Периоды"
       $this->tpl_vars['periods_array'] = $this->reportParams->periods->getPeriodsList();
    // элементы управления "Периоды"
       $this->tpl_vars['period_step'] = l('PERIOD_STEP','report'); 
        // для первого периода формируется щаг
       $monthAmount = $this->reportParams->periods->getPeriod(0)->getCountOfMonth();
       $this->tpl_vars['period_step_content'] = $this->reportParams->periods->getStepPeriodOptions($monthAmount, $this->reportParams->periods->getPeriodStep());
       $this->tpl_vars['step_select'] = $this->reportParams->periods->getPeriodStep();
       
    // элементы управления "Создание отчета"
       $this->tpl_vars['report_creation'] = l('REPORT_CREATION', 'report');
       $this->tpl_vars['create_report'] = l('CREATE_REPORT', 'report');
       $this->tpl_vars['save_to_excel'] = l('SAVE_TO_EXCEL', 'report');
       $this->tpl_vars['load_indicators'] = l('LOAD_FOR_INDICATORS', 'report');
    // параметры отчета
       
       $this->tpl_vars['full_classifier_id'] = $this->reportParams->fullReportClassifier->id;
       $this->tpl_vars['datasource_id'] = $this->reportParams->datasource->id;
       $this->tpl_vars['periods'] = $this->reportParams->periods->getJSONString();
       $this->tpl_vars['periods_count'] = $this->reportParams->periods->getPeriodsCount();
    // индивидуальные параметры отчетов
       $this->tpl_vars['report_list'] = json_encode($this->reportParams->reportList);
        
        // выбранный отчет
        $this->tpl_vars['selected_report'] = $this->reportParams->selectedReport;                      
        
        //  заголовки окон изменения параметров отчетов
       $this->tpl_vars['change_classifier_section'] = l('CHANGE_CLASSIFIER_SECTION','report');
       $this->tpl_vars['change_data_source'] = l('CHANGE_DATA_SOURCE','report');
       $this->tpl_vars['change_periods'] = l('CHANGE_PERIODS','report');
       $this->tpl_vars['available_reports'] = l('AVAILABLE_REPORTS','report');
       $this->tpl_vars['reports_out'] = l('REPORTS_OUTPUT','report');
        // для элементов управления "Периоды"
       $this->tpl_vars['periods_array'] = $this->reportParams->periods->getPeriodsList();
       $this->tpl_vars['years_list'] = $this->reportParams->periods->getYearsList();
       $this->tpl_vars['add_time_period'] =  l('ADD_TIME_PERIOD','report');
       
        // выбранные страны
       $this->tpl_vars['countries'] = $this->reportParams->allCountries ? [[ 'Name' => 'Все страны' ]]  : $this->reportParams->countries;       
       $this->tpl_vars['obj_countries'] =  $this->reportParams->allCountries ? '' : $this->reportParams->countriesStr;
       // выбранные регионы
       $this->tpl_vars['regions'] = $this->reportParams->regions;
       $this->tpl_vars['obj_regions'] = $this->reportParams->regionsStr;
       $this->tpl_vars['all_regions_together'] = l('ALL_REGIONS_TOGETHER','report');
       $this->tpl_vars['all_regions_together_val'] = $this->reportParams->allRegionsTogether;
       
       
    }
    public function setParams() {
        parent::setParams();
        if ($this->user->getId() == 1) {
            Sessions::unsetVarSession('report_params');
        }
        $this->reportParams = new Params($this->user);
        
    }
    public function actionGetManufacturers() 
    {        
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['report_type'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
        $present = false;        
        $reportParams = new Params($this->user);
        $modalViewer = new ModalViewer($reportParams);
        if (!empty($postData['present'])) {
            $present = true;
        }
        $message = $modalViewer->getManufacturers($postData['report_type'],$present);
        return Tools::getMessage($message);
        //$message = Container::getReportSettings($postData['rType']);
    }
    public function actionGetSubClassifier() 
    {        
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['report_type'])) {
            return  Tools::getErrorMessage('request error',1); 
        }       
        $reportParams = new Params($this->user);
        $modalViewer = new ModalViewer($reportParams);
        $message = $modalViewer->getSubClasiifier($postData['report_type']);
        return Tools::getMessage($message);
        //$message = Container::getReportSettings($postData['rType']);
    }
        public function actionGetModels() 
    {        
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['report_type'])) {
            return  Tools::getErrorMessage('request error',1); 
        }       
        $reportParams = new Params($this->user);
        $modalViewer = new ModalViewer($reportParams);
        $message = $modalViewer->getModels($postData['report_type']);
        return Tools::getMessage($message);
        //$message = Container::getReportSettings($postData['rType']);
    }
    
   
    public function actionGetSettings() 
    {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['rType'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
        $message = Container::getReportSettings($this->user, $postData['rType']);
        return Tools::getMessage($message);
    }
    public function actionCreateReport() {
        $reportsContainer = new Container($this->user);
       // return Tools::getErrorMessage('OK');  
        $hash = Yii::$app->request->get('hash');
        $serverHash = Sessions::getVarSession('REPORT_HASH');
        if ($hash != $serverHash) {
            $vars = [                  
                        'body' => 'Параметры отчета были изменены. Перезагрузите страницу, проверьте параметры и повторите попытку.',
                        'close' => false
                    ];
            $viewHelper = new ViewHelper(_INFORM_TEMPLATES_DIR_, 'message_inform',$vars);
            $resultHtml = $viewHelper->getRenderedTemplate();
            return json_encode([Tools::getErrorMessage($resultHtml,1,false,true)]);
        }
        return $reportsContainer->createReport();
       // return Tools::getMessage('OK');
    }
    public function actionCreateExcelReport() {
        $reportsContainer = new Container($this->user);
        $reportsContainer->createReportsExcel();                
    }
    public function actionGetStepPeriod() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['cols'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
        $message = DatePeriod::getStepPeriodOptions($postData['cols']);
        return Tools::getMessage($message);
        
    }
    public function actionGetCountries() 
    {        
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        $reportParams = new Params($this->user);
        $modalViewer = new ModalViewer($reportParams);
        $msg = $modalViewer->getCountries();
        return Tools::getMessage($msg);
    }
    public function actionGetRegions() 
    {        
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        $reportParams = new Params($this->user);
        $modalViewer = new ModalViewer($reportParams);
        return Tools::getMessage('ok');
    }
}

