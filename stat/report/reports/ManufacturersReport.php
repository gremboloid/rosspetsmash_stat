<?php

namespace app\stat\report\reports;

use app\models\user\LoginUser;
use app\stat\model\Contractor;
use app\stat\model\Classifier;
use app\stat\services\ContractorService;
use app\stat\Tools;
use app\stat\db\SimpleSQLConstructor;
use app\stat\exceptions\ReportException;

class ManufacturersReport extends ProductionRoot  
{
    protected $alias = 'manufacturers';
    protected $creatorClassName = 'DefaultOut';
    public $order = 2;
    

    /**
     * Конструктор отчета "Производители"
     */
    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('MANUFACTURERS_REPORT','report');        
        if (key_exists('manufacturers_list', $this->settings)) {
            $this->reportSettings['manufacturers_list'] = explode(',', $this->settings['manufacturers_list']);
        }
        else {
            $this->reportSettings['manufacturers_list'] = array();
        }
    //    $this->constructReportSettings();
        
    }
    protected function prepareSQL() 
    {
        parent::prepareSQL();                               
    }
    protected function calculateDimensions() {
        parent::calculateDimensions();
        $all_manufacturers = false;
        if ($this->reportSettings['manufacturers_list']) { 
            if ( is_array($this->reportSettings['manufacturers_list']) && 
                !empty($this->reportSettings['manufacturers_list'][0])) {
                $all_manufacturers = true;
            }
        }
        if ($all_manufacturers) {
            $list = $this->reportSettings['manufacturers_list'];
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
            $elems = ContractorService::getContractorList(
                    $this->reportParams->datasource->getId(), 
                    $classifierElement, 
                    2,'',$present,$limit);
            $list = array_map( function($arr) {
                return $arr['Id'];
            }, $elems);
            
        }
        if (empty($list)) {
           throw new ReportException('Для данного отчета не найдено ни одного производителя');
        }
        
        $this->dimensions['select'][] = array('textValue' => '"contr"."Id"','name' => "ContractorId");
        $this->dimensions['from'][] = ['name'=> 'contr','textValue' => '('. SimpleSQLConstructor::getStringForDimensions($list).')'];
        $this->tmp_values['group'][] ='var.ContractorId';
        $this->tmp_values['select'][] = array('textValue' => '"var"."ContractorId"','name' => "ContractorId");
        $this->tmp_values['where'][] = '"data"."ContractorId" (+) = "var"."ContractorId"';
        
        
        
        
    }
    protected function constructReportSettings() {
        
        parent::constructReportSettings();
        $m_list = $this->reportSettings['manufacturers_list'];
        if ($m_list && is_array($m_list)) {
            $list = Contractor::getFilteredRows($m_list,array(['Id'],['Name']),'Name');
        }
        else {
            $list = array();
        }
        
        // селектор выбора производителя
        $this->tpl_vars['footer_elements']['select_contractor'] = 
                array(
                    'type' => 'select_modal',
                 //   'label' => l('SELECT_CONTRACTORS'),
                    'elements' => true,
                    'list' => $list,
                    'added_elements' => l('ADDED_CONTRACTORS'),
                    'all_selected' => l('ALL_CONTRACTORS'),
                    'buttons' => array ([
                        'id' => 'select_contractors', 'text' => l('SELECT_CONTRACTORS')])
                );
        
    }
    
     protected function finalCalculating() {
        parent::finalCalculating();
     }
    
}
