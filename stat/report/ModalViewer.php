<?php

namespace app\stat\report;

use app\stat\Tools;
use app\stat\services\ContractorService;
use app\stat\services\ModelService;
use app\stat\model\Contractor;
use app\stat\model\Classifier;
use app\stat\model\Country;
use app\stat\ViewHelper;
use app\stat\Sessions;
use app\stat\report\Container;
use app\stat\report\reports\RootReport;
/**
 * Description of ModalViewer
 *
 * @author kotov
 */
class ModalViewer 
{
    /**
     *
     * @var Params параметры отчеты
     */
    protected $params;
    /**
     *
     * @var RootReport
     */
    protected $currentReport;


    public function __construct(Params $params) {
        $this->params = $params;
        //$reportAlias = $params->selectedReport;
        $this->currentReport = $params->selectedReportObject;
    }
    /**
    *  Вывод модального окна выбора подраздела классификатора
    * @param string $reportType - тип отчета
    */
    public function getSubClasiifier(string $reportType) 
    {
        $sub_classifier_list = '';      
        $params = json_decode(Sessions::getReportParams(),true); 
        if (key_exists($reportType, $params['report_list']))
        {
            if (key_exists('sub_classifier', $params['report_list'][$reportType])) {
                $sub_classifier_list =  $params['report_list'][$reportType]['sub_classifier'];
            }            
        }
        $tpl_vars['head'] = l('SELECT_SUB_CLASSIFIER','report');
        $tpl_vars['left_column_head'] = l('ALL_ELEMENTS');
        $tpl_vars['right_column_head'] = l('ADDED_ELEMENTS');
        $tpl_vars['btns'] = l('BTN_ACTIONS');
        $tpl_vars['alias'] = 'sub_classifier';
        $tpl_vars['select_all'] = l('SELECT_ALL');
        $tpl_vars['left_list'] = $this->params->classifier->getChildClassifierList(array(['Id'],['Name']),$sub_classifier_list);
        $tpl_vars['right_list'] = $sub_classifier_list ? Classifier::getFilteredRows($sub_classifier_list,array(['Id'],['Name']),'Name') : array();                
        $view_helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_select',$tpl_vars);
        return $view_helper->getRenderedTemplate();       
    }        
    
    /**
    *  Вывод модального окна выбора моделей
    * @param string $reportType - тип отчета
    */
    public function getModels(string $reportType) {
        //$models_list = '';
        $classifierId = $this->params->classifier->getId();
        $present = $this->params->defaultValues['present'];
        $typeProduction = array();
        $limit = '';
        $models_list = [];
        $params = json_decode(Sessions::getReportParams(),true);
        if (!empty($params['report_list'])) {
            if (key_exists($reportType, $params['report_list']))
            {
                $currentReportParams = $params['report_list'][$reportType];                  
                if (isset($currentReportParams['models_list'])) {               
                    $models_list =  explode (',',$currentReportParams['models_list']);                
                } else {
                    $models_list = [];
                }
                if (key_exists('presents', $currentReportParams)) {
                    $present = $currentReportParams['presents'] == 'on' ? true : false;
                }
                
                if (key_exists('russian', $currentReportParams)) {
                    $russian = $currentReportParams['russian'] == 'on' ? true : false;
                } else {
                    $russian = $this->currentReport->individualSettings['russian'];
                }
                
                if (key_exists('foreign', $currentReportParams)) {
                    $foreign = $currentReportParams['foreign'] == 'on' ? true : false;
                } else {
                     $foreign = $this->currentReport->individualSettings['foreign'];
                }
                if (key_exists('assembly', $currentReportParams)) {
                    $assembly = $currentReportParams['assembly'] == 'on' ? true : false;
                } else {
                    $assembly = $this->currentReport->individualSettings['assembly'];
                }             
            }
        } else {
            $russian = $this->currentReport->individualSettings['russian'];
            $assembly = $this->currentReport->individualSettings['assembly'];
            $foreign = $this->currentReport->individualSettings['foreign'];             
        }
        if (!empty($russian)) {
            array_push($typeProduction,1);
        }
        if (!empty($assembly)) {
            array_push($typeProduction,2);
        }
        if (!empty($foreign)) {
            array_push($typeProduction, 3);
        }
        if (!empty($typeProduction) && count($typeProduction) < 3) {
            $sql = 'SELECT DISTINCT "c"."Id" FROM TBLMODEL "m","TBLCONTRACTOR" "c",TBLBRAND "b"
                    WHERE "b"."ContractorId" = "c"."Id" AND
                        "m"."BrandId" = "b"."Id" AND "m"."ModelTypeId" IN ('. implode(',', $typeProduction).')';
            $lists = getDb()->querySelect($sql);
            if (count($lists) > 0) {
                $limit = implode(',',Tools::getValuesFromArray($lists, 'Id'));
            }
        }                
        $datasources = $this->params->datasource->getDataBaseTypes();
        $tpl_vars = array();
        $tpl_vars['head'] = l('SELECT_MODELS');
        $tpl_vars['left_column_head'] = l('MODELS_LIST');
        $tpl_vars['right_column_head'] = l('ADDED_MODELS');
        $tpl_vars['btns'] = l('BTN_ACTIONS');
        $tpl_vars['alias'] = 'models_list';
        $tpl_vars['select_all'] = l('ALL_MODELS');     
        //$tpl_vars['left_list']
        $models = ModelService::getModelsList($datasources, $classifierId,'',$present,$limit);
        $contractors_list = [];
        $models_count = count($models_list);
       /* if (count($models_list) == 0) {
            $tpl_vars['right_list'] = [];
            $tpl_vars['left_list'] = $models; 
        } else {*/
        $idx = 0;
        $tpl_vars['left_list'] = [];
        $tpl_vars['right_list'] = [];
        foreach ($models as $model) {
            if (!in_array($model['ContractorId'], $contractors_list)) {
                array_push($contractors_list, $model['ContractorId']);
            }
            if ($models_count == 0) {
                $tpl_vars['left_list'][] = $model;
                continue;                
            } else {
                $model['position'] = $idx;
                if (in_array($model['Id'], $models_list)) {
                    $tpl_vars['right_list'][] = $model;
                } else {
                    $tpl_vars['left_list'][] = $model;
                }        
                $idx++;
            }
        }
        $tpl_vars['contractors'] = Contractor::getRowsArray([
            ['TBLCONTRACTOR','Id','value'],
            ['TBLCONTRACTOR','Name','text']
        ],array(['param' =>'Id','operation' => 'IN','staticNumber' => '('. implode(',', $contractors_list).')' ]),[['name' => 'Name']]);
        array_unshift($tpl_vars['contractors'],['value' => 0,'text' => 'Все производители']);
        
            
        //}
        
       // $tpl_vars['right_list'] = $models_list ? \Model\Models::getFilteredRows($models_list,array(['Id'],['Name']),'Name') : array();
        
        $view_helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_select',$tpl_vars);
        return $view_helper->getRenderedTemplate();      
    }
    

    /**
     * Вернуть модальное окно с выбором производителей
     * @param string $reportType - тип отчета
     * @param bool $present - фильтр по присутствующим на портале
     */
    public function getManufacturers(string $reportType,bool $present = false)
    {
        //return Tools::getMessage('ок');
        $manuf_list = [];
        $classifierId = $this->params->classifier->getId();
        $params = json_decode(Sessions::getReportParams(),true);
        $typeProduction = array();
        if (!empty($params['report_list'])) {
            if (key_exists($reportType, $params['report_list']))
            {            
                $currentReportParams = $params['report_list'][$reportType];
                if (key_exists('presents', $currentReportParams)) {
                    $present = $currentReportParams['presents'] == 'on' ? true : false;
                } else {
                    $present = $this->params->defaultValues['present'];
                }
                if (key_exists('russian', $currentReportParams)) {
                    $russian = $currentReportParams['russian'] == 'on' ? true : false;
                } else {
                    $russian = $this->currentReport->individualSettings['russian'];
                }
                if (key_exists('foreign', $currentReportParams)) {
                    $foreign = $currentReportParams['foreign'] == 'on' ? true : false;
                } else {
                     $foreign = $this->currentReport->individualSettings['foreign'];
                }
                if (key_exists('assembly', $currentReportParams)) {
                    $assembly = $currentReportParams['assembly'] == 'on' ? true : false;
                } else {
                    $assembly = $this->currentReport->individualSettings['assembly'];
                }
                if (key_exists('manufacturers_list',$currentReportParams )) {
                   // $manuf_list = explode(',',$params['report_list'][$reportType]['manufacturers_list']);
                    $manuf_list =  explode(',',$currentReportParams['manufacturers_list']);
                }
            }
        } else {
            $russian = $this->currentReport->individualSettings['russian'];
            $assembly = $this->currentReport->individualSettings['assembly'];
            $foreign = $this->currentReport->individualSettings['foreign'];
        }
        if (!empty($russian)) {
            array_push($typeProduction,1 );
        }
        if (!empty($foreign)) {
            array_push($typeProduction, 3);
        }
        if (!empty($assembly)) {
            array_push($typeProduction,2);
        }               
        $limit = '';
        if (!empty($typeProduction) && count($typeProduction) < 3) {
            $sql = 'SELECT DISTINCT "c"."Id" FROM TBLMODEL "m","TBLCONTRACTOR" "c",TBLBRAND "b"
                    WHERE "b"."ContractorId" = "c"."Id" AND
                        "m"."BrandId" = "b"."Id" AND "m"."ModelTypeId" IN ('. implode(',', $typeProduction).')';
            $lists = getDb()->querySelect($sql);
            if (count($lists) > 0) {
                $limit = implode(',',Tools::getValuesFromArray($lists, 'Id'));
            }
        }                    
        $datasources = $this->params->datasource->getDataBaseTypes();
        $typedata = 2;
        $tpl_vars = array();
        $tpl_vars['head'] = l('SELECT_CONTRACTORS');
        $tpl_vars['left_column_head'] = l('CONTRACTORS_LIST');
        $tpl_vars['right_column_head'] = l('ADDED_CONTRACTORS');
        $tpl_vars['btns'] = l('BTN_ACTIONS');
        $tpl_vars['alias'] = 'manufacturers_list';
        $tpl_vars['select_all'] = l('ALL_CONTRACTORS');
        if ($datasources != '(12)') {
            $manufacturers = ContractorService::getContractorList($datasources, $classifierId, $typedata,'',$present,$limit);
        }
        else {
             $manufacturers = ContractorService::getContractorsListForEconomic($classifierId,'',$present,$limit);
        }
        if (count($manuf_list) == 0) {
            $tpl_vars['right_list'] = [];
            $tpl_vars['left_list'] = $manufacturers; 
        } else {
            $idx = 0;
            $tpl_vars['left_list'] = [];
            $tpl_vars['right_list'] = [];
            foreach ($manufacturers as $manuf) {
                $manuf['position'] = $idx;
                if (in_array($manuf['Id'], $manuf_list)) {
                    $tpl_vars['right_list'][] = $manuf;
                } else {
                    $tpl_vars['left_list'][] = $manuf;
                }        
                $idx++;
            }
            
        }
        $view_helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_select',$tpl_vars);
        return $view_helper->getRenderedTemplate();
    
    }
    /**
     * Вывод модального окна со списком стран
     */
    public function getCountries() {
        
        $params = json_decode(Sessions::getReportParams(),true); 
        $tpl_vars['head'] = l('SELECT_COUNTRIES_HEAD');
        $tpl_vars['left_column_head'] = l('COUNTRIES_LIST');
        $tpl_vars['right_column_head'] = l('ADDED_COUNTRIES');
        $tpl_vars['btns'] = l('BTN_ACTIONS');
        $tpl_vars['alias'] = 'countries';
        $tpl_vars['select_all'] = l('ALL_COUNTRIES');
        /*
        if ((count ($this->countries) > 0) && $params['all_countries_flag'] != 1) {
            $filter = [ '"Id" NOT IN ('. $this->countries_str.',1)'];
        } else {
            $filter = [ '"Id" != 1'];
        }*/       
        $selected_countries = explode(',', $this->params->countriesStr);
        $filter = [ '"Id" != 1'];
       // $tpl_vars['left_list'] 
       $countries_list = Country::getRowsArray(array(['Id'],['Name']),$filter,array(['name' => 'Name']));
       if ($params['all_countries_flag'] == 1) {
           $tpl_vars['right_list'] = [];
           $tpl_vars['left_list'] = $countries_list;           
       } else {
            $idx = 0;
            $tpl_vars['left_list'] = [];
            $tpl_vars['right_list'] = [];
            foreach ($countries_list as $country) {
                $country['position'] = $idx;
                if (in_array($country['Id'], $selected_countries)) {
                    $tpl_vars['right_list'][] = $country;
                } else {
                    $tpl_vars['left_list'][] = $country;
                }        
                $idx++;
            }
       } /*
        if ($params['all_countries_flag'] == 1) {
            $tpl_vars['right_list'] = []; 
        } else {
            $tpl_vars['right_list'] = $this->countries;
        }*/
        
       // echo '<div class="modal">тест</div>';
        $view_helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_select',$tpl_vars);
        return $view_helper->getRenderedTemplate();        
    }
    
}
