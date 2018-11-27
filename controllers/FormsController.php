<?php

namespace app\controllers;

use \Yii;
use \app\stat\conf\ViewFormsConfig;
use \yii\web\Request;
use app\stat\providers\InputFormTableProvider;
use app\stat\services\InputFormService;
use app\stat\Configuration;
use app\stat\Pagination;
use app\stat\services\ContractorService;
use app\stat\Tools;
use app\stat\helpers\FormsHelper;
use app\stat\helpers\ExportFormHelper;
use app\stat\model\InputForm;



/**
 * Description of FormsController
 *
 * @author kotov
 */
class FormsController extends TableViewController
{
    /**
     * Конфигурация вывода форм ввода
     * @var ViewFormsConfig
     */
    protected $formConfig; 
    /**
     * Параметры постраничной навигации
     * @var type 
     */
    protected $paginationParams = array();
    protected $defaultSortField = 'Period';
    protected $defaultSortType = 'DESC';


    public $controller_name = 'forms';
    
    
    
    
    public function init() {              
        parent::init();
        $this->formConfig = new ViewFormsConfig(is_admin(), $this->contractor->getId());  
    }
    
    public function setParams() {
        parent::setParams();
        $this->sortedColumns = array('StrDate','Fio','DatabaseTypeName','ContractorName','Period','Actuality');
    //    $this->pagination->setParams($this->paginationParams);                
    }
    protected function tableConfigure() {
        $this->helper->setPageRows(Configuration::get('RowCount',self::DEFAULT_PAGE_ROWS));
        $this->helper->setRowNumber($this->paginationParams['pageNum']);
        $sortBy = $this->helper->getSortFields();
        $formService = new InputFormService($this->formConfig->getFilters(),$sortBy);
        $formProvider = new InputFormTableProvider($formService);
        $this->setTableDataProvider($formProvider);        
        
    }
    

    public function actionIndex() {
         return $this->render('input_forms.twig', $this->tpl_vars);        
    }
    public function initVars() {
        parent::initVars();        
        $contractorsList = ContractorService::getActualContractors([
            ['Id'],
            ['Name']
        ]);
        $allContractorsList = $contractorsList;        
        array_unshift($allContractorsList, ['Id' => 0, 'Name' => 'Все производители']);
        $formsList = InputFormService::getFormTypes($this->contractor);
        $allFormsList = $formsList;
        array_unshift($allFormsList, ['Id' => 0, 'Name' => 'Все формы']);        
        $this->tpl_vars['actual_contractors'] = $contractorsList;
        $this->tpl_vars['all_contractors'] = $allContractorsList;
        $this->tpl_vars['forms_list'] = $formsList;
        $this->tpl_vars['all_forms_list'] = $allFormsList;
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('TITLE_FORMS_PAGE');
        $this->tpl_vars['content_header'] = l('INPUT_FORMS','menu');
        $this->tpl_vars['months'] = l('MONTHS','words');
        $this->tpl_vars['years'] = ['start' => $this->formConfig->getDefaultStartYear(), 'end' => $this->formConfig->getDefaultEndYear()];
         // заголовки блоков
        $this->tpl_vars['filters'] = l('FILTERS','words');
        $this->tpl_vars['edit_forms'] = l('EDIT_FORMS');
        $this->tpl_vars['select_contractors'] = l('SELECT_CONTRACTOR');
        
        $this->tpl_vars['visible_forms'] =  l('VISIBLE_FORMS');
        $this->tpl_vars['date_interval'] =  l('DATE_INTERVAL');
        $this->tpl_vars['new_form'] =  l('ADD_NEW_FORM');
        $this->tpl_vars['report_period'] =  l('REPORT_PERIOD');
        $this->tpl_vars['current_contractor_id'] = $this->contractor->getId();
        $this->tpl_vars['current_contractor_name'] = $this->contractor->getName();
        // Выбранные фильтры        
        $this->tpl_vars['filters_array'] = $this->formConfig->getFilters();
        $this->tpl_vars['nf_parameters'] = [
            'date' => [
                'month' =>  $this->formConfig->getDefaultEndMonth(),
                'year' =>  $this->formConfig->getDefaultEndYear()
            ]
        ];
        
        
        
        
    }
    
    protected function setParamsFromRequest(Request $request) {
        $filterArray = array();
        if (!empty($request->get())) {            
            if ($val = $request->get('contractor')) {
                $filterArray['contractor'] = $val;
            }
            if ($val = $request->get('formType')) {
                $filterArray['formType'] = $val;
            }
            if (($valMonth = $request->get('startMonth')) &&
            ($valYear = $request->get('startYear'))) {
                $filterArray['startMonth'] = $valMonth;
                $filterArray['startYear'] = $valYear;
                if (($valMonth = $request->get('endMonth')) &&
                ($valYear = $request->get('endYear'))) {
                    $filterArray['endMonth'] = $valMonth;
                    $filterArray['endYear'] = $valYear;
                    
                }
            }
            $this->formConfig->setupFilter($filterArray);            
            $this->paginationParams['pageNum'] = (int) $request->get('page',1);
            $sortColumn = $request->get('sortColumn', $this->defaultSortField);
            $sortType = $request->get('sortType', $this->defaultSortType);                        
            
        } else {
            $this->paginationParams['pageNum'] = 1;
            $sortColumn = $this->defaultSortField;
            $sortType = $this->defaultSortType;
        }
        $this->configSortFields($sortColumn,$sortType);
        
     //   $formService = new InputFormService($this->formConfig->getFilters());
      //  $this->tableConfigure();
      //  $this->paginatorInit($currentPage);
        $this->configurateTableColumns();
        $this->configurateActions();
    } 


    /**
     * Инициализация пагинатора
     * @param int $currentPage
     */
    protected function paginatorInit() { 
        if (key_exists('pageNum', $this->paginationParams)) {
            $currentPage = $this->paginationParams['pageNum'];
        } else {
            $currentPage = 1;
        }
        $pageRows = $this->helper->getRowsInPage();
        $this->helper->setRowsCount();
        $pagesCount = ceil($this->helper->getRowsCount() / $pageRows);
        $this->pagination = new Pagination($pagesCount,$currentPage);
    }
    /**
     * Сконфигурировать вывод табличных данных
     */
    protected function configurateTableColumns() {
        $this->helper->tableColumnHeaders = [
            'Number' => '№',
            'StrDate' => 'Дата',
            'Fio' => 'ФИО',
            'DatabaseTypeName' => l('INPUT_FORM'),
            'ContractorName'=>l('COMPANY'),
            'Period' => l('REPORT_PERIOD'),
            'Actuality' => '<i class="fa fa-flag" title="Статус" aria-hidden="true"></i>',
            'Actions' => l('Actions')
        ]; 
        $this->tableFilter = array_keys($this->helper->tableColumnHeaders);        
    }
    protected function configurateActions() {
        $this->actionList[] = [
            'action' => 'viewForm',
            'name' => l('detailed_info'),
            'image' => 'documentinformation32.gif'
            ] ;
        if (is_admin()) {
            $this->actionList[] = ['action' => 'deleteForm',
                    'name' => l('form_delete'),
                    'image' => 'delete32.png'
            ] ;
        }
    }
    public function actionGetExistForm() 
    {
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
        $formHelper = new FormsHelper();
        $result = call_user_func_array([$formHelper,'getFormHtmlById'], $postData['params']);
        return $result; 
    }
    public function actionGetNewForm()
    {
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
        $formHelper = new FormsHelper();
        $result = call_user_func_array([$formHelper,'getNewFormHtml'], $postData['params']);
        return $result; 
    }
    public function actionCheckFormExist() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['frm_data'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
        $formsHelper = new FormsHelper();
        return $formsHelper->checkFormExist($postData['frm_data']);                
    }
    public function actionFormSave() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['val'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
        $formHelper = new FormsHelper();
        return $formHelper->saveInputForm($postData['val']);                
    }
    
    public function actionGetModels() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['object'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
       $formHelper = new FormsHelper();
       return $formHelper->getModelsForAddToForm($postData['object']);
    }
    public function actionAddModels() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['object'])) {
            return  Tools::getErrorMessage('request error',1); 
        }
        $params = $postData['object'];
        if (empty($params['models_list'])) {
            return Tools::getErrorMessage('Не выбрано ни оной модели');
        }
        if (!in_array($params['type'],[4,5])) {
            $formHelper = new FormsHelper();
        } else {
            $formHelper = new ExportFormHelper();
        }
        
        return $formHelper->addModelToForm($params);
    }
    public function actionDeleteModels() {
        if (!Yii::$app->request->isAjax) {
            return Tools::getErrorMessage('request error',1,false);
        }
        if (!Yii::$app->request->isPost) {
            return  Tools::getErrorMessage('request error',1);          
        }
        $postData = Yii::$app->request->post();
        if (empty($postData['object'])) {
            return  Tools::getErrorMessage('request error',1); 
        }                
        if (!empty($postData['object']['ifId'])) {
            $inputForm = new InputForm($postData['object']['ifId']);
        } else {
            $inputForm = new InputForm();
        }        
        return $inputForm->deleteModelFromForm($postData['object']['elements']);
    }
    
    public function actionGetCountries() {
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
        $formHelper = new ExportFormHelper();
        $message = $formHelper->getCountriesForAddToForm($postData['params']);
        return Tools::getMessage($message);
    }
    public function actionAddCountries() {
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
        $formHelper = new ExportFormHelper();
        $message = $formHelper->addCountriesToForm($postData['params']);
        return Tools::getMessage($message);
    }
    public function actionDeleteCountries() {
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
        $formHelper = new ExportFormHelper();
       // $message = $formHelper->addCountriesToForm($postData['params']);
        return $formHelper->removeCountry($postData['params']);
    //    return Tools::getMessage($message);
    }
    public function actionDeleteForm() {
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
        $inputForm = new InputForm($postData['id']);
        
        if (!$inputForm->deleteForm()) {
            return  Tools::getErrorMessage('Удалить не удалось',1); 
        }
        return Tools::getMessage('Форма успешно удалена');
    }
    
            
}
