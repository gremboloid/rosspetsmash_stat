<?php

namespace app\stat\report\reports;

use app\models\user\LoginUser;
use app\stat\services\ContractorService;
use app\stat\db\SimpleSQLConstructor;
use app\stat\model\Contractor;
/**
 * Класс отчета "Модели по производителям"
 *
 * @author kotov
 */
class ModelsContractorReport extends ProductionRoot {
    protected $alias = 'modelsContractor';
    protected $creatorClassName = 'ModelsContractorOut';
    public $order = 4;
    public $is_admin = true;
    
    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('MODELS_CONTRACTOR_REPORT','report');        
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
                   // 'label' => l('SELECT_CONTRACTORS'),
                    'elements' => true,
                    'added_elements' => l('ADDED_CONTRACTORS'),
                    'all_selected' => l('ALL_CONTRACTORS'),
                    'list' => $list,
                    'buttons' => array ([
                        'id' => 'select_contractors', 'text' => l('SELECT_CONTRACTORS')])
                );
    }
    protected function calculateDimensions() 
    {
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
            $elems = ContractorService::getContractorList(
                    1, 
                    $this->reportParams->classifier->getId(), 
                    2);
            $list = array_map( function($arr) {
                return $arr['Id'];
            }, $elems);
            
        }
        $this->dimensions['select'][] = array('textValue' => '"contr"."Id"','name' => "ContractorId");        
        $this->dimensions['from'][] = ['name'=> 'contr','textValue' => '('. SimpleSQLConstructor::getStringForDimensions($list).')'];        
        $this->tmp_values['select'][] = array('textValue' => '"var"."ContractorId"','name' => "ContractorId");
        $this->tmp_values['where'][] = '"data"."ContractorId" (+) = "var"."ContractorId"';        
        $this->tmp_values['group'][] ='var.ContractorId';
        
        $this->dimensions['select'][] = array('textValue' => '"type"."Id"','name' => "TypeId");
        $this->dimensions['from'][] = ['name'=> 'type','textValue' => '(SELECT 0 AS "Id" FROM DUAL UNION SELECT 1 AS "Id" FROM DUAL)'];
        $this->tmp_values['select'][] = array('textValue' => '"var"."TypeId"','name' => "TypeId");
        $this->tmp_values['where'][] = '"data"."Type"(+) = "var"."TypeId"';
        $this->tmp_values['group'][] ='var.TypeId';
        
        $this->dimensions['from'][] = ['name'=> 'class','textValue' => '(SELECT "Id", "ClassifierGroupId" AS "RootId" FROM TBLCLASSIFIER START WITH "Id" = '.$this->reportParams->classifier->getId().'  CONNECT BY PRIOR "Id" = "ClassifierId")'];
        $this->dimensions['select'][] = array('textValue' => '"class"."Id"','name' => "ClassifierId");
        $this->dimensions['select'][] = array('textValue' => '"class"."RootId"','name' => "ClassifierRootId");
        $this->dimensions['where'][] = '"mdl"."ClassifierId" = "class"."Id"';
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierRootId"','name' => "ClassifierId");
        $this->tmp_values['group'][] ='var.ClassifierRootId';
                
        $this->dimensions['select'][] = array('textValue' => '"mdl"."Id"','name' => "ModelId");
        $this->dimensions['from'][] = ['TBLBRAND','brn'];
        $this->dimensions['from'][] = ['TBLMODEL','mdl'];
        $this->dimensions['where'][] = '"contr"."Id" = "brn"."ContractorId"';
        $this->dimensions['where'][] = '"mdl"."BrandId" = "brn"."Id"';
        $this->tmp_values['select'][] = array('textValue' => '"var"."ModelId"','name' => "ModelId");
        $this->tmp_values['where'][] = '"data"."ModelId"(+)="var"."ModelId"';
        $this->tmp_values['group'][] = 'var.ModelId';                                               
    }
    
     protected function finalCalculating() {
        $this->tmp_values = $this->initArrayForSql();

        $this->tmp_values['from'][0] = ['textValue' => '('.$this->sql.') "final_data"'];
        $this->sql = '';
         foreach ($this->dimensions as $value)
     {
         switch ($value)
         {
             case 'PeriodHash':
                 array_push($this->columns, 'Period');
                $this->tmp_values['select'] = array(
                    ['name' => 'Period', 
                    'textValue' => 'CASE       
                                        WHEN "final_data"."PeriodHash" >= 0 THEN REPORTS.GET_PERIOD_NAME("final_data"."PeriodHash")
                                        WHEN "final_data"."PeriodHash" = -1 THEN \'proportion1\'
                                        WHEN "final_data"."PeriodHash" = -2 THEN \'proportion2\'
                                        WHEN "final_data"."PeriodHash" = -3 THEN \'proportion3\'
                                    END']); 
                  $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."PeriodHash" < 0 THEN "final_data"."PeriodHash" ELSE NULL END ASC NULLS FIRST'];
                 break;
             case 'PeriodOrder':
                  $this->tmp_values['order'][] = ['textValue' => '"final_data"."PeriodOrder" ASC NULLS LAST'];
                  break;
             case 'ContractorId':
                 $this->other_columns[] = 'Contractor';
                 $this->tmp_values['select'][] = ['name' => 'Contractor', 'textValue' => 'DECODE("final_data"."ContractorId", -1, \''.l('OTHER','words').'\', "TBLCONTRACTOR"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCONTRACTOR" ON "TBLCONTRACTOR"."Id" = "final_data"."ContractorId" ';
                 $this->tmp_values['order'][]= ['textValue' => 'CASE WHEN "final_data"."ContractorId" IS NULL THEN \'A\' WHEN "final_data"."ContractorId"=-1 THEN \'C\' ELSE \'B\' END'];
                 $this->tmp_values['order'][] = ['textValue' => '"Contractor"'];
                 break;
             case 'ClassifierId':
                 $this->other_columns[] = 'Classifier';
                 $this->tmp_values['select'][] = ['name' => 'Classifier','textValue' => '"TBLCLASSIFIER"."Name")','textValue' => 'DECODE("final_data"."ClassifierId", -1, \''.l('OTHER','words').'\',"TBLCLASSIFIER"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCLASSIFIER" ON "TBLCLASSIFIER"."Id" = "final_data"."ClassifierId"';
                 $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."ClassifierId" IS NULL THEN \'A\' WHEN "final_data"."ClassifierId"=0 THEN \'B\' WHEN "final_data"."ClassifierId"=-1 THEN \'D\' ELSE \'C\' END'];
                 $this->tmp_values['order'][] = ['textValue' => '"Classifier"'];
                 break;
             case 'ModelId':
                 $this->other_columns[] = 'Model';
                 $this->tmp_values['select'][] = ['name' => 'Model','textValue' => 'DECODE("final_data"."ModelId", -1, \''.l('OTHER','words').'\', "TBLMODEL"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLMODEL" ON "TBLMODEL"."Id" = "final_data"."ModelId"';
                 $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."ModelId" IS NULL THEN \'A\' WHEN "final_data"."ModelId"=-1 THEN \'C\' ELSE \'B\' END'];
                 $this->tmp_values['order'][] = ['textValue' => '"Model"'];
                 break;
             case 'TypeId':
                 $this->tmp_values['select'][] = ['name' => 'Type', 'textValue' => 'DECODE("final_data"."TypeId", 1, \'Производство\', 0, \'Отгрузка\')'];
                  $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."TypeId" IS NULL THEN \'A\' WHEN "final_data"."TypeId"=1 THEN \'B\' ELSE \'C\' END'];
                     break;
             
         }
    //     $this->tmp_values['select'][] = ['textValue' => '"final_data".'.\Rosagromash\Tools::addDoubleQuotes($value)];
     }        
        $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."PeriodHash">=0 THEN \'A\' WHEN "final_data"."PeriodHash"=-1 THEN \'B\' WHEN "final_data"."PeriodHash"=-2 THEN \'C\' END ASC NULLS LAST'];
        foreach ($this->reportSettings['units'] as $val)
         {
            array_push($this->columns, 'Data'.$val);
            $this->tmp_values['select'][] = ['name' => 'Data'.$val, 'textValue' => '"final_data"."Data'.$val.'"'];
            $this->tmp_values['order'][] = ['textValue' =>  'FIRST_VALUE("final_data"."Data'.$val.'") OVER ('.(count($this->dimensions_without_periods) ? ('PARTITION BY "final_data"."' . implode('", "final_data"."', $this->dimensions_without_periods) . '"') : '') .' ORDER BY "final_data"."PeriodHash" DESC) DESC'];
            $this->data_units[] = 'Data'.$val;
         }

         $this->sql = $this->combineSql($this->tmp_values);
        
        
    
    }
    protected function arraySync(array $data) 
    {
         // $aResult = $data;
         // 1. Разбивка по производителям и по типу (0-производство, 1-отгрузка)
        $split = 'Contractor';
        $contractor_list = array();
        $main_idx = 0;
        $first = true;
        $aResult = array();
        
        foreach ($data as $row) { 
            $type = $row['Type'];
           if (!key_exists($split, $row)|| !$row[$split]) {
               continue;
           }
           if (!$type) {
               continue;
           }
           $contractor = $row[$split];
           $c_idx = array_search($contractor,$contractor_list);
           if ($c_idx === false) {               
               array_push($contractor_list, $contractor);
               if ($first) {
                   $first = false;
               }
               else {
                   $main_idx++;
               }
               $aResult[$main_idx][$type][] = $row;
               
           }
           else {
              $aResult[$c_idx][$type][] = $row; 
           }
           
                   
        }
        unset ($data);
        
        // 2. Разбивка по периодам
        $pResult = array(); // промежуточный результат

        $first_circle = true;
        foreach ($aResult as $idx => $cntr) {

            foreach ($cntr as $type => $list) {
                $columnIndex = 0;
                $rowIndex = 0;
                $oldVal = 0;                
                 foreach ($list as $value) {
                    $newVal = $value[$this->head_column];
                    if (!$oldVal) {                        
                        $oldVal = $newVal; 
                        if ($first_circle) {
                            $this->head_array[] = $oldVal;
                        }
                    }
                    if ($newVal == $oldVal) {
                        $pResult[$idx][$type][$columnIndex][$rowIndex++] = $value;
                    }
                    else {
                        $rowIndex = 0;
                        $pResult[$idx][$type][++$columnIndex][$rowIndex++] = $value;
                        $oldVal = $newVal;
                        if ($first_circle) {
                            $this->head_array[] = $oldVal;
                       }
                    }
                }
                $first_circle = false;
            }
        }
        unset($aResult);
        // Финальное приведение данных
        $newArray = array();
        $cur_column = 'Model';
        foreach ($pResult as $idx => $cntr) {
            foreach ($cntr as $type => $list) {
                 $a_length = count($list);
                 $newArray[$idx][$type][0] =  $pResult[$idx][$type][0];
                 unset ($pResult[$idx][$type][0]);
                for ($i =1;$i<$a_length;$i++)
                {
                    foreach ($newArray[$idx][$type][0] as $j => $elem) 
                    {
                        $val = $elem[$cur_column];
                        foreach ($pResult[$idx][$type][$i] as $key => $elem1) {
                            if ($elem1[$cur_column] == $val) {
                                $newArray[$idx][$type][$i][$j] = $elem1;
                                unset ($pResult[$idx][$type][$i][$key]);
                                break;
                            }
                        }
                    }
                }
            }
        }                                        
        return $newArray;
    }

}
