<?php

namespace app\stat\report\reports;

use app\models\user\LoginUser;
use app\stat\exceptions\ReportException;
/**
 * Класс отчета "Модели"
 *
 * @author kotov
 */
class ModelsClassifierReport extends ProductionRoot
{
    protected $alias = 'modelsClassifier';
    protected $creatorClassName = 'ModelsClassifierOut';
    public $order = 5;
    public $is_admin = true;
    public $is_analitic = true;

    /**
     * Конструктор отчета "Модели"
     */
    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('MODELS_CLASSIFIER_REPORT','report');   
        if (key_exists('simple', $this->settings)) {
            $this->reportSettings['simple'] = $this->settings['simple'];
        }
        else {
            $this->reportSettings['simple'] = 'off';
        }
        
        
    //    $this->constructReportSettings();
        
    }
    protected function prepareSQL() 
    {
        parent::prepareSQL();                               
    }
    protected function constructReportSettings() {
        parent::constructReportSettings();
        $simple = false;
        if (key_exists('simple', $this->settings) && $this->settings['simple'] == 'on') {
            $simple = true;
        }
        
        $this->tpl_vars['column_left']['simple'] = array (
            'type' => 'checkbox',
            'label' => l('ONLY_CLASSIFIER','report')
        );
        
        if ($simple) {
            $this->tpl_vars['column_left']['simple']['checked'] = true;
        }
    }
    protected function exchangePrice() {
        parent::exchangePrice();
    /*    $this->sql.= ' UNION SELECT 0 AS "Count",0 AS "Price", "period"."Hash" AS "PeriodHash","tm"."Id" AS "ModelId","ctor"."Id" AS "ContractorId", "ctor"."Id" AS "ManufacturerId","tm"."ClassifierId" AS "ClassifierId",1 AS "TypeData",0 AS "Type" 
  FROM "TBLBRAND" "brn", "TBLCONTRACTOR" "ctor","TBLMODEL" "tm", (SELECT column_value AS "Hash", ROWNUM AS "Order" FROM TABLE(INT_ARRAY(2419324198,2418124186))) "period" 
  WHERE "tm"."ClassifierId" IN ( SELECT "tc"."Id" FROM "TBLCLASSIFIER" "tc" START WITH "Id" = 92 CONNECT BY PRIOR "tc"."Id" = "tc"."ClassifierId") AND
 "ctor"."Id" = "brn"."ContractorId" AND "tm"."BrandId" = "brn"."Id"';*/
    }
    
    protected function calculateDimensions() 
    {
        parent::calculateDimensions();
        
        
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierId"','name' => "ClassifierId");
        $this->tmp_values['select'][] = array('textValue' => '"var"."ModelId"','name' => "ModelId");
        $this->tmp_values['select'][] = array('textValue' => '"var"."TypeId"','name' => "TypeId");
        
        $this->tmp_values['where'][] = '"data"."ModelId" (+) = "var"."ModelId"';
        $this->tmp_values['where'][] = '"data"."Type" (+)="var"."TypeId"';
        $this->tmp_values['group'][] = 'var.ModelId';
        $this->tmp_values['group'][] = 'var.ClassifierId';
        $this->tmp_values['group'][] = 'var.TypeId';
        
        
        
        $this->dimensions['select'][] = array('textValue' => '"model"."ClassifierId"','name' => "ClassifierId");
        $this->dimensions['select'][] = array('textValue' => '"model"."ModelId"','name' => "ModelId");
        $this->dimensions['select'][] = array('textValue' => '"type"."Id"','name' => "TypeId");
        $this->dimensions['from'][] = ['name'=> 'model','textValue' => '( SELECT "ctbl"."ClassifierId" AS "ClassifierId",
                                                     "ctbl"."ModelId" AS "ModelId" FROM "CACHETABLE" "ctbl" WHERE 
                                                     "ctbl"."ClassifierId" IN ( SELECT "c"."Id" FROM "TBLCLASSIFIER" "c"
                                        START WITH "c"."Id" = '.$this->reportParams->classifier->getId().' CONNECT BY PRIOR "c"."Id" = "c"."ClassifierId"))'];
        $this->dimensions['from'][] = ['name'=> 'type','textValue' => '(SELECT 0 AS "Id" FROM DUAL)'];
        
        
        
        /*
        $this->dimensions['select'][] = array('textValue' => '"model"."Id"','name' => "ModelId");
        
        $this->dimensions['from'][] = ['name'=> 'model','textValue' => '('. \Rosagromash\SimpleSQLConstructor::getStringForDimensions($this->reportSettings['models_list']).')'];
        $this->tmp_values['group'][] ='var.ModelId';
        $this->tmp_values['select'][] = array('textValue' => '"var"."ModelId"','name' => "ModelId");
        $this->tmp_values['where'][] = '"data"."ModelId" (+) = "var"."ModelId"';
          
         */
    }
    
    protected function addLastPeriod() 
    {
        parent::addLastPeriod();                
    }
   protected function groupingValues() {
       
       $models_filter = '';
        if ($this->reportSettings['simple'] == 'on') {
            $models_filter = ' AND "ContractorId" IS NULL ';
        }
        $sql = 'SELECT "' . implode('", "', $this->dimensions) . '",';
        $type = array_pop($this->dimensions);
        foreach ($this->reportSettings['units'] as $value)
        {
            $sql.= 'SUM("Data'.$value.'") AS "Data'.$value.'",';
        }
        $sql = trim($sql,',');
        $this->sql = $sql.' FROM (' . $this->sql . ')';
        // зацепка для специальных отчетовотчетов
        $this->groupingValuesFiltersHook();
        $this->sql.= ' GROUP BY "'.$type.'",ROLLUP("' . implode('", "', $this->dimensions) . '")';
        
       // дополнительная фильтрация
       array_push($this->dimensions_without_periods,'ContractorId');
       array_push($this->dimensions_without_periods,'Order');
       array_push($this->dimensions_without_periods,'Level');
       $this->dimensions_without_periods_string =  '"' . implode('", "', $this->dimensions_without_periods) . '"';
       $dataVal = '';
       foreach ($this->reportSettings['units'] as $value)
        {
            $dataVal.= '"Data'.$value.'",';
        }
        $dataVal = trim($dataVal,',');
       $this->sql = '  SELECT 
                        "PeriodHash", 
			"PeriodOrder", 
			"ClassifierId","ModelId", 
                        "TypeId",
                        "ContractorId",
                        "tree"."Number" AS "Order",
                        "tree"."Level",
			'.$dataVal.' FROM
    ( '.$this->sql.' )
LEFT OUTER JOIN (SELECT "c"."Id" AS "ContractorId", 
       "m"."Id" AS "ContractorModel"
                 FROM TBLMODEL "m",TBLBRAND "b",TBLCONTRACTOR "c"
  WHERE "m"."BrandId" = "b"."Id" AND
        "b"."ContractorId" = "c"."Id" ) "con"
        ON "con"."ContractorModel" = "ModelId"
RIGHT OUTER JOIN (  SELECT 
    "tc"."Id" AS "Order",
    ROWNUM AS "Number",
    LEVEL AS "Level"
  FROM 
    TBLCLASSIFIER "tc"
      START WITH "Id" = '.$this->reportParams->classifier->getId().' 
      CONNECT BY PRIOR "tc"."Id" = "tc"."ClassifierId") "tree"
    ON "tree"."Order" = "ClassifierId"
  
    
   WHERE ("ModelId" IS NULL OR
    ("ClassifierId" IN (SELECT "Id" FROM TBLCLASSIFIER "tc"
                              WHERE NOT EXISTS
                              ( SELECT "tc"."Id" FROM TBLCLASSIFIER "tc1"
                               WHERE "tc"."Id" = "tc1"."ClassifierId")))
        AND "tree"."Number" IS NOT NULL )'.$models_filter.'
  ORDER BY 
           "PeriodHash",
           "tree"."Number",
           "ModelId" NULLS FIRST,
           "PeriodOrder" ';               
   }
   protected function percentCalc() {
       parent::percentCalc();
   }
    protected function finalCalculating() {
        $this->tmp_values = $this->initArrayForSql();

        $this->tmp_values['from'][0] = ['textValue' => '('.$this->sql.') "final_data"'];
        $this->sql = '';
        $this->tmp_values['select'] = array(
            ['name' => 'Level', 'textValue' => '"final_data"."Level"'],
            ['name' => 'Period', 
            'textValue' => 'CASE       
                                WHEN "final_data"."PeriodHash" >= 0 THEN REPORTS.GET_PERIOD_NAME("final_data"."PeriodHash")
                                WHEN "final_data"."PeriodHash" = -1 THEN \'proportion1\'
                                WHEN "final_data"."PeriodHash" = -2 THEN \'proportion2\'
                                WHEN "final_data"."PeriodHash" = -3 THEN \'proportion3\'
                            END']); 
    $this->other_columns[] = 'Contractor';
    $this->tmp_values['select'][] = ['name' => 'Contractor', 'textValue' => 'DECODE("final_data"."ContractorId", -1, \''.l('OTHER','words').'\', "TBLCONTRACTOR"."Name")'];
    $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCONTRACTOR" ON "TBLCONTRACTOR"."Id" = "final_data"."ContractorId" ';
    $this->other_columns[] = 'Classifier';
    $this->tmp_values['select'][] = ['name' => 'Classifier','textValue' => '"TBLCLASSIFIER"."Name")','textValue' => 'DECODE("final_data"."ClassifierId", -1, \''.l('OTHER','words').'\',"TBLCLASSIFIER"."Name")'];                
    $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCLASSIFIER" ON "TBLCLASSIFIER"."Id" = "final_data"."ClassifierId"';
    $this->other_columns[] = 'Model';
    $this->tmp_values['select'][] = ['name' => 'Model','textValue' => '"TBLMODEL"."Name")','textValue' => 'DECODE("final_data"."ModelId", -1, \''.l('OTHER','words').'\',(CASE WHEN "CONTR"."Name" IS NULL THEN \'\' ELSE "CONTR"."Name" || \' - \' END) ||  "TBLMODEL"."Name")'];
    $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLMODEL" ON "TBLMODEL"."Id" = "final_data"."ModelId" LEFT OUTER JOIN "TBLBRAND" ON "TBLMODEL"."BrandId" = "TBLBRAND"."Id" LEFT OUTER JOIN "TBLCONTRACTOR" "CONTR" ON "TBLBRAND"."ContractorId" = "CONTR"."Id"';    
    
    
    
    
    $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."PeriodHash" < 0 THEN "final_data"."PeriodHash" ELSE NULL END ASC NULLS FIRST'];
    $this->tmp_values['order'][] = ['textValue' => '"final_data"."PeriodOrder" ASC NULLS LAST'];
    $this->tmp_values['order'][] = ['textValue' => '"final_data"."Order"'];
    $this->tmp_values['order'][]= ['textValue' => 'CASE WHEN "final_data"."ContractorId" IS NULL THEN \'A\' WHEN "final_data"."ContractorId"=-1 THEN \'C\' ELSE \'B\' END'];
    $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."ModelId" IS NULL THEN \'A\' WHEN "final_data"."ModelId"=-1 THEN \'C\' ELSE \'B\' END'];
    
    foreach ($this->reportSettings['units'] as $val)
    {
        array_push($this->columns, 'Data'.$val);
        $this->tmp_values['select'][] = ['name' => 'Data'.$val, 'textValue' => '"final_data"."Data'.$val.'"'];
        $this->tmp_values['order'][] = ['textValue' =>  'FIRST_VALUE("final_data"."Data'.$val.'") OVER ('.(count($this->dimensions_without_periods) ? ('PARTITION BY "final_data"."' . implode('", "final_data"."', $this->dimensions_without_periods) . '"') : '') .' ORDER BY "final_data"."PeriodHash" DESC) DESC'];
        $this->tmp_values['order'][] = ['textValue' => '"Model"'];
        $this->data_units[] = 'Data'.$val;
    }
    
    $this->sql = $this->combineSql($this->tmp_values);
    

    
    }
        protected function arraySync(array $data) 
    {
        $idx =0;
        $col_count = count ($this->other_columns);
        $aResult = array();
        $columnIndex = 0;
        $rowIndex = 0;
        $oldVal = 0;
        foreach ($data as $value) {            
            $newVal = $value[$this->head_column];
            if (!$oldVal) {
                $this->head_array[] = $oldVal = $newVal;                
            }
            if ($newVal == $oldVal) {
                $aResult[$columnIndex][$rowIndex++] = $value;
            }
            else {
                $rowIndex = 0;
                $aResult[++$columnIndex][$rowIndex++] = $value;
                $this->head_array[] = $oldVal = $newVal;
            }  
        } 
        unset($data);                
        $diff_cols = array('Classifier','Contractor','Model');
        $a_length = count($aResult);
        if ($a_length == 0) {
            throw new ReportException('Для данного отчета не найдено ни одной модели');
        }
        $newArray[] = $aResult[0];
        unset ($aResult[0]);
        for ($i =1;$i<$a_length;$i++)
        {
            foreach ($newArray[0] as $j => $elem) 
            {   
                $brk_flag = false;
                foreach ($aResult[$i] as $key => $elem1) {
                    if ($brk_flag) break;
                        $compare = true;                        
                        $idx=0;
                        foreach ($diff_cols as $column) {
                            $val = $elem[$column];
                            $idx++;
                            if ($elem1[$column] == $val) {
                                if ($idx==count($diff_cols)) {
                                    if ($compare) {
                                        $newArray[$i][$j] = $elem1;
                                        unset ($aResult[$i][$key]);
                                        $brk_flag =true;
                                        continue;
                                    }                                    
                                }
                                continue;                               
                            }
                            else {
                               $compare = false;
                               break;
                            }
                        }
                    }
                }
            }                                      
        return $newArray;
            /*
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
                    foreach ($newArray[$idx][$type][0] as $elem) 
                    {
                        $val = $elem[$cur_column];
                        foreach ($pResult[$idx][$type][$i] as $key => $elem1) {
                            if ($elem1[$cur_column] == $val) {
                                $newArray[$idx][$type][$i][$key] = $elem1;
                                unset ($pResult[$idx][$type][$i][$key]);
                                break;
                            }
                        }
                    }
            }
            }
        }
        
        
        
        
        
        return $newArray;*/
    }
    
}

