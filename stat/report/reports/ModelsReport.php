<?php

namespace app\stat\report\reports;

use app\models\user\LoginUser;
use app\stat\model\Models;
use app\stat\model\Classifier;
use app\stat\services\ModelService;
use app\stat\Tools;
use app\stat\db\SimpleSQLConstructor;
use app\stat\exceptions\ReportException;
/**
 * Класс отчета "Модели"
 *
 * @author kotov
 */
class ModelsReport extends ProductionRoot
{
    protected $alias = 'models';
    protected $creatorClassName = 'DefaultOut';
    public $order = 3;

    /**
     * Конструктор отчета "Модели"
     */
    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('MODELS_REPORT','report');        
        if (key_exists('models_list', $this->settings)) {
            $this->reportSettings['models_list'] = explode(',', $this->settings['models_list']);
        }
        else {
            $this->reportSettings['models_list'] = array();
        }
        
    //    $this->constructReportSettings();
        
    }
    protected function prepareSQL() 
    {
        parent::prepareSQL();                               
    }
    protected function constructReportSettings() {
        parent::constructReportSettings();
         $m_list = $this->reportSettings['models_list'];
        if ($m_list && is_array($m_list)) {
            $list = Models::getFilteredRows($m_list,array(['Id'],['Name']),'Name');
        }
        else {
            $list = array();
        }
        
        // селектор выбора производителя
        $this->tpl_vars['footer_elements']['select_models'] = 
                array(
                    'type' => 'select_modal',
                 //   'label' => l('SELECT_MODELS'),
                    'added_elements' => l('ADDED_MODELS'),
                    'all_selected' => l('ALL_MODELS'),
                    'elements' => true,
                    'list' => $list,
                    'buttons' => array ([
                        'id' => 'select_models', 'text' => l('SELECT_MODELS')])
                );
    }
    protected function calculateDimensions() 
    {
        parent::calculateDimensions();
        $all_models = false;
        if ($this->reportSettings['models_list']) { 
            if ( is_array($this->reportSettings['models_list']) && 
                !empty($this->reportSettings['models_list'][0])) {
                $all_models = true;
            }
        }
        if ($all_models) {
            $list = $this->reportSettings['models_list'];
        } else {
            $present = false;
            $type_production = array();
            if ($this->reportSettings['presents'] == 'on') {
                $present = true;
            }
            $rus = $this->reportSettings['russian'] == 'on' ? true : false;
            $foreign = $this->reportSettings['foreign'] == 'on' ? true : false;
            $assembly = $this->reportSettings['assembly'] == 'on' ? true : false;
            if ($rus) {
                array_push($type_production,1 );
            }
            if ($assembly) {
                array_push($type_production,2);
            }
            if ($foreign) {
                array_push($type_production, 3);
            }
            $limit = '';
            if (!empty($type_production) && count($type_production) < 3) {
                $sql = 'SELECT DISTINCT "c"."Id" FROM TBLMODEL "m","TBLCONTRACTOR" "c",TBLBRAND "b"
                    WHERE "b"."ContractorId" = "c"."Id" AND
                    "m"."BrandId" = "b"."Id" AND "m"."ModelTypeId" IN ('. implode(',', $type_production).')';
                $lists = getDb()->querySelect($sql);
                if (count($lists) > 0) {
                    $limit = implode(',', Tools::getValuesFromArray($lists, 'Id'));
                }                
            }
            if (is_array($this->reportParams->classifier)) {
                $classifierElement = array_map( function(Classifier $val) { return $val->getId(); },$this->reportParams->classifier);
            } else {
                $classifierElement = $this->reportParams->classifier->getId();
            }
            $elems = ModelService::getModelsList(
                    $this->reportParams->datasource->getId(), 
                    $classifierElement,
                    '',
                    $present,
                    $limit
                    );
            $list = array_map( function($arr) {
                return $arr['Id'];
            }, $elems);
            
        }
        if (empty($list)) {
           throw new ReportException('Для данного отчета не найдено ни одной модели');
        }
        
        $this->dimensions['select'][] = array('textValue' => '"model"."Id"','name' => "ModelId");
        
        
        $this->dimensions['from'][] = ['name'=> 'model','textValue' => '('. SimpleSQLConstructor::getStringForDimensions($list).')'];
        $this->tmp_values['group'][] ='var.ModelId';
        $this->tmp_values['select'][] = array('textValue' => '"var"."ModelId"','name' => "ModelId");
        $this->tmp_values['where'][] = '"data"."ModelId" (+) = "var"."ModelId"';                                
    }
    
}

