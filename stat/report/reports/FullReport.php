<?php
namespace app\stat\report\reports;


use app\models\user\LoginUser;
use app\stat\report\Params;
use app\stat\db\SimpleSQLConstructor;
/**
 * Description of DefaultReport
 *
 * @author kotov
 */
class FullReport extends ProductionRoot  
{
    protected $alias = 'full';
    protected $creatorClassName = 'FullOut';
    public $order = 6;
    public $is_admin = true;

    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('FULL_REPORT','report'); 
                if (key_exists('manufacturers_list', $this->settings)) {
            $this->reportSettings['manufacturers_list'] = explode(',', $this->settings['manufacturers_list']);
        }
        else {
            $this->reportSettings['manufacturers_list'] = array();
        }
      //  $this->constructReportSettings();
    }
    protected function prepareSQL() 
    {
        $this->reportParams = new Params($this->currentUser); 
        if (!$this->reportParams->periods->pairPeriodEqual()) {
            $this->status['ERROR'] = true;
            $this->status['ERROR_MESSAGE'] = [
                'head' => l('ERROR','messages'),
                'body' => l('ERROR_PERIODS_NOT_EQUAL','messages')
                ];
            return;
        }
        if ($this->type == 2) {
            if ($this->reportParams->periods->getRealPeriodsCount() != 2) {
                $this->status['ERROR'] = true;
                $this->status['ERROR_MESSAGE'] = [
                    'head' => l('ERROR','messages'),
                    'body' => l('ERROR_TWO_PERIODS_NEED','messages')
                ];
                return;
                
            }
        }
        parent::prepareSQL();      
        // фильтр по классификатору                
                
    }
    protected function classifierFilter () {        
        $manuf_type_filter = array(); // фильтр по типу техники
        $where_manuf_filter = '';
        if ($this->reportSettings['russian'] == 'on') {
            array_push($manuf_type_filter, 1);
        }
        if ($this->reportSettings['assembly'] == 'on') {
            array_push($manuf_type_filter,2);
        }
        if (count($manuf_type_filter)> 0)
        {
            $where_manuf_filter = ' AND "mdl"."ModelTypeId" IN ('.implode(',',$manuf_type_filter).') ';
        }
        if ($this->reportSettings['presents'] == 'on') {
            $presents_manuf_filter = ' AND "base"."Present" = 1 ';
        }
        
        $this->sql = 'SELECT "base".*,"classifier"."Id" AS "ClassifierId" FROM
                    (' . $this->sql . ') "base",TBLMODEL "mdl",	"TBLCLASSIFIER" "classifier"
                    WHERE "base"."ModelId" = "mdl"."Id"	AND "mdl"."ClassifierId" = "classifier"."Id"'.$presents_manuf_filter.$where_manuf_filter;                
    }
    
    
    protected function finalCalculating() {
        $this->tmp_values = $this->initArrayForSql();
        $this->tmp_values['from'][0] = ['textValue' => '('.$this->sql.') "final_data"'];
        foreach ($this->dimensions as $value)
        {
            switch ($value)
            {
             case 'PeriodHash':               
                 $this->tmp_values['select'] = array(
                    ['name' => 'Period', 
                    'textValue' => 'CASE WHEN "final_data"."PeriodHash" >= 0 
                                        THEN REPORTS.GET_PERIOD_NAME("final_data"."PeriodHash")
                                        ELSE RPAD(\'Изм., %\', "final_data"."PeriodOrder"+7.5) END']);
                 break;
            case 'PeriodOrder':
                 $this->tmp_values['order'][] = ['textValue' => '"final_data"."PeriodOrder" ASC NULLS LAST'];
                 break;
            case 'ClassifierSubClassId':
            case 'ClassifierGroupId':
            case 'ClassifierSubGroupId':
            case 'ClassifierSubSubGroupId':
                    $this->tmp_values['select'][] =
                        ['name' => substr($value,0,-2),
                           'textValue' => 'DECODE("final_data"."'.$value.'", -1, \'Прочие\', 0, \'#\', "TBL'.$value.'"."Name")'];
                    $this->tmp_values['from'][0]['textValue'].= ' LEFT OUTER JOIN 
                                    "CLS" "TBL'.$value.'" ON "TBL'.$value.'"."Id" = "final_data"."'.$value.'" '; 
                    $this->tmp_values['order'][] = ['textValue' => 'CASE 
                        WHEN "final_data"."'.$value.'" IS NULL 
                            THEN \'A\' 
                        WHEN "final_data"."'.$value.'"=0 
                            THEN \'B\' 
                        WHEN "final_data"."'.$value.'"=-1 
                            THEN \'D\' 
                        ELSE \'C\' END'];
                    $this->tmp_values['order'][] = ['textValue' => '"TBL' . $value . '"."OrderFull"'];                    
                break;
             case 'ContractorId': 
                 $this->tmp_values['select'][] = ['name' => 'Contractor', 'textValue' => 'DECODE("final_data"."ContractorId", -1, \''.l('OTHER','words').'\', "TBLCONTRACTOR"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCONTRACTOR" ON "TBLCONTRACTOR"."Id" = "final_data"."ContractorId" ';
                 $this->tmp_values['order'][]= ['textValue' => 'CASE WHEN "final_data"."ContractorId" IS NULL THEN \'A\' WHEN "final_data"."ContractorId"=-1 THEN \'C\' ELSE \'B\' END'];
                 break;
             case 'TypeId':
                 $this->tmp_values['select'][] = ['name' => 'Type', 'textValue' => 'DECODE("final_data"."TypeId", 1, \'Производство\', 0, \'Отгрузка\')'];
                 $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."TypeId" IS NULL THEN \'A\' WHEN "final_data"."TypeId"=1 THEN \'C\' ELSE \'B\' END'];
                 break;
            }
        }
            $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."PeriodHash">=0 THEN \'A\' WHEN "final_data"."PeriodHash"=-1 THEN \'B\' WHEN "final_data"."PeriodHash"=-2 THEN \'C\' END ASC NULLS LAST'];
            foreach ($this->reportSettings['units'] as $val) {
                $this->tmp_values['select'][] = ['name' => 'Data'.$val, 'textValue' => '"final_data"."Data'.$val.'"'];
                $this->tmp_values['order'][] = ['textValue' => 'TO_NUMBER(FIRST_VALUE("final_data"."Data'.$val.'") OVER (' . (count($this->dimensions_without_periods) ? ('PARTITION BY "final_data"."' . implode('", "final_data"."', $this->dimensions_without_periods) . '"') : '') . ' ORDER BY "final_data"."PeriodOrder" ASC), \'999G999G999G999' . (($unit == 'Amount') ? '' : 'D99') . '\') DESC'];
                $this->data_units[] = 'Data'.$val;
            }
            
            $this->sql =  SimpleSQLConstructor::generateSimpleSQLQuery($this->tmp_values['select'],
                  $this->tmp_values['from']);            
            $this->sql = 'WITH CLS AS (
                SELECT "Id", CASE
                                WHEN "Name" IN (\'Машины для внесения минеральных удобрений\', \'Машины для внесения органических удобрений\') THEN (SELECT "ClassifierId" FROM TBLCLASSIFIER "C" WHERE "C"."Id" = "TBLCLASSIFIER"."ClassifierId")
                                WHEN "Name" IN ( \'Дробилки\', \'Смесители\', \'Смесители-кормораздатчики\') THEN -10
                                WHEN "Name" IN (\'Погрузчики сельскохозяйственные\', \'Транспортеры навозоуборочные\') THEN -20
                                WHEN "Name" IN (\'Доильные установки\') THEN (SELECT "Id" FROM TBLCLASSIFIER WHERE "Name" = \'Сельхозтехника\')
                            ELSE "ClassifierId"
                            END AS "ClassifierId",
                        CASE
                            WHEN "Name" = \'Доильные установки\' THEN \'Оборудование для производства молока\'
                            WHEN "Name" = \'Запасные части\' THEN \'Запасные части к сельхозтехнике и оборудованию\'
                            WHEN "Name" = \'Комбайны кормоуборочные прицепные\' THEN \'Прицепные\'
                            WHEN "Name" = \'Комбайны кормоуборочные самоходные\' THEN \'Самоходные\'
                            WHEN "Name" = \'Погрузчики сельскохозяйственные\' THEN \'Погрузчики\'
                            WHEN "Name" = \'Тракторы сельскохозяйственные\' THEN \'Тракторы\'
                            WHEN "Name" = \'Тракторы сельскохозяйственные колесные\' THEN \'Колесные\'
                            WHEN "Name" = \'Тракторы сельскохозяйственные полноприводные\' THEN \'Полноприводные\'
                            WHEN "Name" = \'Тракторы сельскохозяйственные гусеничные\' THEN \'Гусеничные\'
			ELSE "TBLCLASSIFIER"."Name"
                        END AS "Name",
                        "TBLCLASSIFIER"."ClassifierGroupIdFull",
                        CASE
                            WHEN "Name" = \'Доильные установки\' THEN 56.3
                            ELSE "TBLCLASSIFIER"."OrderFull"
                        END AS "OrderFull"
                        FROM
                        TBLCLASSIFIER WHERE
			"ClassifierGroupIdFull" IS NOT NULL
			AND "Name" <> \'Запасные части\' UNION
                        SELECT -10 AS "Id",
                        (SELECT "Id" FROM TBLCLASSIFIER WHERE "Name" = \'Сельхозтехника\') AS "ClassifierId",
                            \'Техника и оборудование для приготовления кормов для животных\' AS "Name",
                            -10 AS "ClassifierGroupIdFull",
                            56.1 AS "OrderFull"
                            FROM DUAL UNION SELECT -20 AS "Id",
                        (SELECT "Id" FROM TBLCLASSIFIER WHERE "Name" = \'Сельхозтехника\') AS "ClassifierId",
                        \'Техника и оборудование для животноводства\' AS "Name",
                        -20 AS "ClassifierGroupIdFull",
                        56.2 AS "OrderFull"
                        FROM DUAL ) ' . $this->sql;
            $this->sql = $this->sql . SimpleSQLConstructor::generateClauseOrderBy($this->tmp_values['order']);
            $this->tmp_values = array();    
    }
    protected function calculateDimensions() {
        parent::calculateDimensions();
        $this->tmp_values['select'][] = array('textValue' => '"var"."TypeId"','name' => "TypeId");
        $this->tmp_values['where'][] = '"data"."Type"(+)="var"."TypeId"';
        $this->tmp_values['group'][] = 'var.TypeId';
        
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierSubClassId"','name' => "ClassifierSubClassId");
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierGroupId"','name' => "ClassifierGroupId");
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierSubGroupId"','name' => "ClassifierSubGroupId");
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierSubSubGroupId"','name' => "ClassifierSubSubGroupId");
        
        $this->tmp_values['where'][] = '"data"."ClassifierId" (+) = "var"."ClassifierId"';
        
        $this->tmp_values['group'][] = '"var"."ClassifierSubClassId"';
        $this->tmp_values['group'][] = '"var"."ClassifierGroupId"';
        $this->tmp_values['group'][] = '"var"."ClassifierSubGroupId"';
        $this->tmp_values['group'][] = '"var"."ClassifierSubSubGroupId"';
        
        $this->tmp_values['select'][] = array('textValue' => '"var"."ContractorId"','name' => "ContractorId");
        
        $this->tmp_values['where'][] = '"data"."ManufacturerId"(+) = "var"."ContractorId"';
        $this->tmp_values['group'][] = '"var"."ContractorId"';
                
        $this->dimensions['select'][] = array('textValue' => '"type"."Id"','name' => "TypeId"); 
        $this->dimensions['from'][] = ['name'=> 'type','textValue' => '(SELECT 0 AS "Id" FROM DUAL UNION SELECT 1 AS "Id" FROM DUAL)'];
        
        $this->dimensions['select'][] = array('textValue' => '"class"."SubClassId"','name' => "ClassifierSubClassId"); 
        $this->dimensions['select'][] = array('textValue' => '"class"."GroupId"','name' => "ClassifierGroupId");
        $this->dimensions['select'][] = array('textValue' => '"class"."SubGroupId"','name' => "ClassifierSubGroupId");
        $this->dimensions['select'][] = array('textValue' => '"class"."SubSubGroupId"','name' => "ClassifierSubSubGroupId");
        $this->dimensions['select'][] = array('textValue' => '"class"."Id"','name' => "ClassifierId");
        $this->dimensions['select'][] = array('textValue' => '"class"."ContractorId"','name' => "ContractorId");
        $this->dimensions['from'][] = ['name'=> 'class','textValue' => ' ( SELECT DISTINCT
            NVL("GROUPS"."SubClassId", 0) AS "SubClassId",NVL("GROUPS"."GroupId", 0) AS "GroupId",
            NVL("GROUPS"."SubGroupId", 0) AS "SubGroupId",NVL("GROUPS"."SubSubGroupId", 0) AS "SubSubGroupId",
            "CLS"."Id","VW_CONTRACTORCLASSIFIER"."ContractorId" FROM ( 
                SELECT "SubClassId","GroupId","SubGroupId","SubSubGroupId" FROM (
                    SELECT "C0"."Id" AS "SubClassId","C1"."Id" AS "GroupId","C2"."Id" AS "SubGroupId",
                            "C3"."Id" AS "SubSubGroupId" 
                    FROM "CLS" "C0", "CLS" "C1","CLS" "C2", "CLS" "C3"
                    WHERE "C0"."ClassifierId" = ' .$this->reportParams->fullReportClassifier->getId(). '
                        AND "C1"."ClassifierId"(+) = "C0"."Id"
                        AND "C2"."ClassifierId"(+) = "C1"."Id"
                        AND "C3"."ClassifierId"(+) = "C2"."Id"
                    GROUP BY
                            ROLLUP("C0"."Id", "C1"."Id", "C2"."Id", "C3"."Id")
                    HAVING GROUPING_ID("C0"."Id") = 0 )
                GROUP BY "SubClassId","GroupId","SubGroupId","SubSubGroupId" ) "GROUPS",
                "CLS","VW_CONTRACTORCLASSIFIER"
            WHERE "CLS"."ClassifierGroupIdFull" = COALESCE("GROUPS"."SubSubGroupId", "GROUPS"."SubGroupId" , "GROUPS"."GroupId", "GROUPS"."SubClassId")
            AND "VW_CONTRACTORCLASSIFIER"."ClassifierId" (+) = "CLS"."Id"
            AND "VW_CONTRACTORCLASSIFIER"."ContractorId" NOT IN (SELECT "Id" FROM "TBLCONTRACTOR" WHERE "Name" = \'Загрузка из Excel\'))'];
        
        
        
    }
    protected function groupingValuesFiltersHook() 
    {
        parent::groupingValuesFiltersHook();
        // Фильтр по роизводителям
        $manuf_filter = '';
        if ($this->reportSettings['manufacturers_list']) { 
            if ( is_array($this->reportSettings['manufacturers_list']) && 
                !empty($this->reportSettings['manufacturers_list'][0])) {
                $list = $this->reportSettings['manufacturers_list'];
                $manuf_filter = ' AND "TBLCONTRACTOR"."Id" IN ('.implode(',',$list).') ';
            }
        }
        // не учитывать данные иностранных организаций
        $this->sql.='WHERE "ContractorId" IN 
                ( SELECT "TBLCONTRACTOR"."Id" FROM "TBLCONTRACTOR"  
                WHERE "TBLCONTRACTOR"."CityId" IN ( 
                SELECT TBLCITY."Id" FROM TBLCITY  WHERE  TBLCITY."RegionId" IN 
                    ( SELECT TBLREGION."Id" FROM TBLREGION WHERE TBLREGION."CountryId"=1)) '.$manuf_filter.')';
    }
    
    protected function percentCalc() 
    {    
            $hashes = $this->reportParams->periods->getPeriodsHashes();
            foreach ($this->reportSettings['units'] as $value)
            {
                $s_data .= ' CASE
                     WHEN "Data'.$value.'" = 0 THEN \'0' . ((($value == 'Amount') || ($value == 'Price')) ? '' : ',00') . '\'
                     WHEN "Data'.$value.'" < 1 THEN TO_CHAR("Data'.$value.'", \'0' . ((($value == 'Amount') || ($value == 'Price')) ? '' : 'D99') . '\')
                     ELSE TO_CHAR("Data'.$value.'", \'999G999G999G999' . ((($value == 'Amount') || ($value == 'Price')) ? '' : 'D99') . '\')
                      END AS "Data'.$value.'" ,';
                
            }
            $s_data=trim($s_data,',');         
            $this->sql = 'WITH "data1" AS (' . $this->sql . ')
            SELECT "PeriodHash", "PeriodOrder",'.$this->dimensions_without_periods_string.','.$s_data.
              'FROM "data1" WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL';                    
        $s_data = '';
        for ($i = 0; $i < count($hashes); $i+=2) {
            foreach ($this->reportSettings['units'] as $value) {
                $s_data .= ' CASE WHEN "Data'.$value.'" = 0 THEN \'–\'
                                WHEN "Data'.$value.'" = 1 THEN \'0\'
                                WHEN "Data'.$value.'" > 0.99 AND "Data'.$value.'" < 1.01 THEN TO_CHAR("Data'.$value.'"*100-100, \'0D0\')
                                WHEN "Data'.$value.'" < 3 THEN TO_CHAR("Data'.$value.'"*100-100, \'9999D0\')
                                WHEN "Data'.$value.'" <= 10 THEN \'в \' || TRIM(TO_CHAR("Data'.$value.'", \'99\' || (CASE WHEN "Data'.$value.'" <> TRUNC("Data'.$value.'") THEN \'D9\' END))) || \' р.\'
                                WHEN "Data'.$value.'" > 10 THEN \'> 10 р.\'
                            END AS "Data'.$value.'" ,';
                $f_data .= ' MAX ( CASE WHEN "PeriodHash" = ' . $hashes[$i] . ' THEN "Data'.$value.'"
                                    END	)/NULLIF(MAX( CASE WHEN "PeriodHash" = ' . $hashes[$i + 1] . '
                                                               THEN "Data'.$value.'" END),0) AS "Data'.$value.'" ,';

            }
            $s_data=trim($s_data,','); 
            $f_data=trim($f_data,','); 
            $this->sql.= ' UNION SELECT "PeriodHash", "PeriodOrder", ' . ($this->dimensions_without_periods_string ? ($this->dimensions_without_periods_string . ', ') : '').$s_data . 
                    ' FROM ( SELECT -' . ($i + 1) . ' AS "PeriodHash",'
                                      . ($i + 2.5) . ' as "PeriodOrder",'
                                      . ($this->dimensions_without_periods_string ? ($this->dimensions_without_periods_string . ', ') : '').$f_data.
                    'FROM (SELECT * FROM "data1" WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL ORDER BY "PeriodOrder" ASC)' . (($this->dimensions_without_periods_string) ? (' GROUP BY ' . $this->dimensions_without_periods_string) : '') . ')';
            $s_data='';
            $f_data='';
        }                     
    }
        /**
     * Преобразует массив для вывода колонки для вывода
     * @param array $data
     * @return array
     */
    protected function arraySync(array $data)
    {
         // $aResult = $data;
         // 1. Разбивка по прериодам и по типу (0-производство, 1-отгрузка)
        $split = 'Period';
        $periods_list = array();

        
                
        $main_idx = 0;
        $first = true;
        $aResult = array();
        
        foreach ($data as $row) { 
            $type = $row['Type'];
            $sub_class = $row['ClassifierSubClass'];
            
           if (!key_exists($split, $row)|| !$row[$split]) {
               continue;
           }
           if (!$type) {
               continue;
           }
           
           $period = $row[$split];
           $c_idx = array_search($period,$periods_list);
           if ($c_idx === false) {               
               array_push($periods_list, $period);
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
        // финальное приведение данных 
        
        $diff_cols = array('Type','ClassifierSubClass','ClassifierGroup','ClassifierSubGroup','ClassifierSubSubGroup','Contractor');
        $final_result= array(); 
        $a_length = count ($aResult);
        for ($i =0;$i<$a_length;$i++) {
            foreach ($aResult[0] as $type => $elem_list) {
                foreach ($elem_list as $elem) {
                    $brk_flag = false;
                    foreach ($aResult[$i][$type] as $key => $elem1) {
                        $real_cols = array();
                        if ($brk_flag) break;
                        $compare = true;
                        $idx=0;
                        foreach ($diff_cols as $column) {
                            $val = $elem[$column];
                            $idx++;
                            if ($elem1[$column] == $val) {
                                if ($val) {
                                    array_push($real_cols,$column);                                    
                                }
                                if ($idx==count($diff_cols)) {
                                    if ($compare) {
                                        $last_column = array_pop($real_cols);                                        
                                        if ($last_column == 'Type') {
                                            //$final_result[0][$elem1['Type']][$key] = $elem;
                                            $final_result[$i][$elem1['Type']]['Root'] = $elem1;
                                        }
                                        if ($last_column == 'ClassifierSubClass') {
                                            if ($elem1[$last_column] != '#') {
                                                 $final_result[$i][$elem1['Type']][$elem1['ClassifierSubClass']]['Root'] = $elem1;
                                            }
                                        }
                                        if ($last_column == 'ClassifierGroup') {
                                            if ($elem1[$last_column] != '#') {
                                                 $final_result[$i][$elem1['Type']][$elem1['ClassifierSubClass']][$elem1['ClassifierGroup']]['Root'] = $elem1;
                                            }
                                        }
                                        if ($last_column == 'ClassifierSubGroup') {
                                            if ($elem1[$last_column] != '#') {
                                                 $final_result[$i][$elem1['Type']][$elem1['ClassifierSubClass']][$elem1['ClassifierGroup']][$elem1['ClassifierSubGroup']]['Root'] = $elem1;
                                            }
                                        }
                                        if ($last_column == 'ClassifierSubSubGroup') {
                                            if ($elem1[$last_column] != '#') {
                                                 $final_result[$i][$elem1['Type']][$elem1['ClassifierSubClass']][$elem1['ClassifierGroup']][$elem1['ClassifierSubGroup']][$elem1['ClassifierSubSubGroup']]['Root'] = $elem1;
                                            }
                                        }
                                        
                                        if ($last_column == 'Contractor') {
                                            do {
                                                $last_column2 = array_pop($real_cols);
                                            }
                                             while ($elem1[$last_column2]== '#');
                                            $final_result['Contractors'][$i][$elem1['Type']][$elem1[$last_column2]][] = $elem1;
                                        }
                                        
                                     //   unset ($aResult[$i][$type][$key]);
                                     //   unset ($aResult[0][$type][$key]);
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
         }
         unset ($aResult);
        if ($this->reportParams->periods->isDifferentYear()) {
            $final_result['Periods'] = array();
            $month_array = l('MONTHS','words');
            $start_months = $this->reportParams->periods->getPeriodsArray('startmonth');
            $start_years = $this->reportParams->periods->getPeriodsArray('startyear');
            $end_months = $this->reportParams->periods->getPeriodsArray('endmonth');
            $end_years = $this->reportParams->periods->getPeriodsArray('endyear');
            $count = count ($start_months);
            for ($idx = 0;$idx < $count;$idx+=2) {
                if ($start_months[$idx] == $end_months[$idx]) {
                    $month = $month_array[(int) $start_months[$idx]];
                }
                else {
                    $month = $month_array[(int)$start_months[$idx]] . ' - ' . mb_convert_case($month_array[(int)$end_months[$idx]],MB_CASE_LOWER);
                }
                array_push($final_result['Periods'],array(
                    'Month' => $month,
                    'Years' => [$start_years[$idx],$start_years[$idx+1]]
                ));
            }
            
          //    $final_result['Period'] = ['Months' => $months,'Years' => $years ];
            
        }
            $final_result['PeriodsList'] = $periods_list;
         return $final_result;     
    }

    

    public function constructReportSettings() {
        parent::constructReportSettings();
        $m_list = $this->reportSettings['manufacturers_list'];
        if ($m_list && is_array($m_list)) {
            $list = \Model\Contractor::getFilteredRows($m_list,array(['Id'],['Name']),'Name');
        }
        else {
            $list = array();
        }
         // селектор выбора производителя
        $this->tpl_vars['footer_elements']['select_contractor'] = 
                array(
                    'type' => 'select_modal',
                    //'label' => l('SELECT_CONTRACTORS'),
                    'all_selected' => l('ALL_CONTRACTORS'),
                    'added_elements' => l('ADDED_CONTRACTORS'),
                    'elements' => true,
                    'list' => $list,
                    'buttons' => array ([
                        'id' => 'select_contractors', 'text' => l('SELECT_CONTRACTORS')])
                );
    }
    
}
