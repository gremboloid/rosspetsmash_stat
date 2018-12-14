<?php
namespace app\stat\report\reports;

use app\models\user\LoginUser;
use app\stat\model\Currency;
use app\stat\model\Classifier;
use app\stat\services\ClassifierService;
use app\stat\services\ContractorService;
use app\stat\db\SimpleSQLConstructor;
use app\stat\model\Contractor;
use app\stat\Tools;
/**
 * Description of EconomicRoot
 *
 * @author kotov
 */
abstract class EconomicRoot extends RootReport
{
    
    protected $dimensions = array();
    protected $dimensions_without_periods = array();
    protected $dimensions_without_periods_string = "";
    /** @var string Указывает какую колонку брать для отчета (общую или СХТ) */
    protected $prefix_all;

    public function __construct(LoginUser $user) {
        parent::__construct($user); 
        if (key_exists('units', $this->settings)) {
            $this->reportSettings['units'] = explode(',', $this->settings['units']);
        }
        else {
            $this->reportSettings['units'] = array('AverageSalary');
                    
        }
        $multiplierArray = l('MULTIPLIER_ARRAY', 'report');
        if (key_exists('multiplier', $this->settings)) {
           $this->reportSettings['multiplier'] = $multiplierArray[$this->settings['multiplier']];
        }
        else {
            $this->reportSettings['multiplier'] = $multiplierArray['none']; // по умолчанию
        }
        if (key_exists('russian', $this->settings)) {
            $this->reportSettings['russian'] = $this->settings['russian'];
        }
        else {
            $this->reportSettings['russian'] = 'on';
        }
        if (key_exists('assembly', $this->settings)) {
            $this->reportSettings['assembly'] = $this->settings['assembly'];
        }
        else {
            $this->reportSettings['assembly'] = 'off';
        }
        if (key_exists('foreign', $this->settings)) {
            $this->reportSettings['foreign'] = $this->settings['foreign'];
        }
        else {
            $this->reportSettings['foreign'] = 'off';
        }

    }
    protected function constructReportSettings() 
    {
        parent::constructReportSettings();
        
        $russian = true;
        $assembly = false;
        $foreign = false;
        $select_multiplier = 'none';
        $select_currency = '';
        
        
        if (key_exists('multiplier', $this->settings)) {
            $select_multiplier = $this->settings['multiplier'];
        }
        if (key_exists('currency', $this->settings)) {
            $select_currency = $this->settings['currency'];
        }
        $multiplierArray = l('MULTIPLIER_ARRAY', 'report');
        $currencyArray = Currency::getRowsArray(array(['Id'],['Name']));
        $currencyOptions = array();
        $multOptions = array();
        $optIdx = 0;
        foreach ($multiplierArray as $key=> $val)
        {
            $multOptions[$optIdx] = array('value' => $key, 'text' => $val['text']);
            if ($select_multiplier == $key) {
                $multOptions[$optIdx]['selected'] = true;
            }
            $optIdx++;
        }
        $optIdx=0;
        foreach ($currencyArray as $val)
        {
            $currencyOptions[$optIdx] = array('value' => $val['Id'], 'text' => $val['Name']);
            if ($select_currency == $val['Id']) {
                $currencyOptions[$optIdx]['selected'] = true;
            }            
            $optIdx++;
        }
        if (key_exists('russian', $this->settings) && $this->settings['russian'] == 'off') {
            $russian = false;
        }
        if (key_exists('assembly', $this->settings) && $this->settings['assembly'] == 'on') {
            $assembly = true;
        }
        if (key_exists('foreign', $this->settings) && $this->settings['foreign'] == 'on') {
            $foreign = true;
        }
        
        
        
        
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
                    'added_elements' => l('ADDED_CONTRACTORS'),
                    'all_selected' => l('ALL_CONTRACTORS'),
                 'elements' => true,
                    'list' => $list,
                    'buttons' => array ([
                        'id' => 'select_contractors', 'text' => l('SELECT_CONTRACTORS')])
                );
       $this->tpl_vars['dimension_elements']['Employees'] = 
                array('type' => 'checkbox', 'label' =>  l('DATAEMPLOYEES','words') );
     /*   $this->tpl_vars['dimension_elements']['AverageSalary'] = 
                array('type' => 'checkbox', 'label' =>  l('DATAAVERAGESALARY','words') );
*/
        $this->tpl_vars['dimension_elements']['AddBlock'] = 
                array ('type' => 'list','class'=>'dimensions','label_group' => l('CURRENCY_UNIT','words'),'elements' => 
                    array(
                        'multiplier' => ['type' => 'select','options' => $multOptions],
                        'currency' => ['type' => 'select','options' => $currencyOptions]),
                        'root_element' => array( 'type' => 'checkbox', 'name' => 'AverageSalary','class' => 'price_unit', 'label' => l('DATAAVERAGESALARY','words')
                ));
        $this->tpl_vars['dimension_elements']['Fond'] = 
                array('type' => 'checkbox', 'label' =>  l('DATAFONDALL','words'),'class' => 'price_unit' );
        
        
        if (in_array('Employees', $this->reportSettings['units'])) {
            $this->tpl_vars['dimension_elements']['Employees']['checked'] = true;
        }
        if (in_array('AverageSalary', $this->reportSettings['units'])) {
            $this->tpl_vars['dimension_elements']['AddBlock']['root_element']['checked'] = true;
        }
        if (in_array('Fond', $this->reportSettings['units'])) {
            $this->tpl_vars['dimension_elements']['Fond']['checked'] = true;
        }
        $this->tpl_vars['column_right']['russian'] = array (
            'type' => 'checkbox',
            'label' => l('RUSSIAN','report')
        );
        if ($russian == 'on') {
            $this->tpl_vars['column_right']['russian']['checked'] = true;
        }
        $this->tpl_vars['column_right']['foreign'] = array (
            'type' => 'checkbox',
            'label' => l('FOREIGN','report')
        );
        
        if (isset($foreign) && $foreign == 'on') {
            $this->tpl_vars['column_right']['foreign']['checked'] = true;
        }
        
        
        $this->tpl_vars['column_right']['assembly'] = array (
            'type' => 'checkbox',
            'label' => l('ASSEMBLY','report')
        );
        if ($assembly == 'on') {
            $this->tpl_vars['column_right']['assembly']['checked'] = true;
        }
        
        /*
        $contractor_categories_list = \Model\ContractorCategoryNames::getRowsArray();
        if (count ($contractor_categories_list) > 0) {
            foreach ($contractor_categories_list as $elem) {
                $this->tpl_vars['column_right']['id'.$elem['Id']] = array (
                  'type' => 'checkbox',
                  'name' => 'ContractorsCategories['.$elem['Id'].']',
                  'class' => 'contractors-categories-list',
                  'label' => $elem['Name']
                );
            }
        }
         */
        /*

         * 
         */
        
    }
    /**
     * Подготовить SQL-выражение для отчета
     */
    protected function prepareSQL() {
        parent::prepareSQL();
        if (is_array($this->reportParams->classifier)) {
            $classifierId = $this->reportParams->classifier[0]->getId();
        } else {
            $classifierId = $this->reportParams->classifier->getId();
        }
        $classifier_nfo = ClassifierService::getClassifierPath($classifierId);
        $classifier_path = explode('/', trim($classifier_nfo['root'],'/'));
        if ($classifier_nfo['Level'] > 2 && in_array(42,$classifier_path)) {
           $this->prefix_all = "";
        }
       else {
           $this->prefix_all = "All";
       }
        $sResult = "";

        $select = array(['ec','Employees'],['ec','EmployeesAll'],['ec','AverageSalary'],
            ['name' => 'Fond','textValue' => '"ec"."Employees" * "ec"."AverageSalary"'],
            ['name' => 'FondAll','textValue' => '"ec"."EmployeesAll" * "ec"."AverageSalaryAll"'],
            ['ec','CurrencyId'],
            ['ec','AverageSalaryAll'],['ec','ContractorId'], ["ec","Year"],["ec","Month"]);
        $tables = array (
            ["TBLINPUTFORM","frm"],
            ["TBLECONOMIC","ec"]
        );
        $where = array (
           ['param' => 'frm.Id', 'staticValue' => 'ec.InputFormId'],
           ['param' => 'frm.Actuality', 'operation' =>  '<>', 'staticNumber' => 0],                
        );

        // фильтр по периодам
        $periods = $this->reportParams->periods->getSQLString();
        $select[] = ["periods","PeriodHash"];
        $select[] = ["periods","PeriodOrder"];
        $tables[] = ['textValue' => $periods];
        $where[] = '"ec"."Year"*12+"ec"."Month" BETWEEN "periods"."PeriodStart" AND "periods"."PeriodEnd"';
         // Применить фильтр по не отчитавшимся за определенный период предприятиям
        $filter = $this->getFilterHashesArray();
        if (!empty($filter)) {
            $where[] = '"ec"."Year"*12+"ec"."Month" NOT IN('.implode(',',$filter).')';
        }        
        $sResult = SimpleSQLConstructor::generateClauseSelect($select).' '.
            SimpleSQLConstructor::generateClauseFrom($tables). ' '.
            SimpleSQLConstructor::generateClauseWhere($where);
        $this->sql.=$sResult; 
        $this->exchangePrice();
        $this->calculateDimensions();
        $this->appendDimensions();
        $this->addLastPeriod();
        $this->groupingValues();
        $this->percentCalc();
        $this->finalCalculating();
     //   $classifierId = $this->reportParams->classifier->getId();
    //    $classifier_nfo = \Model\Classifier::getClassifierPath($classifierId);
     //   $classifier_path = explode('/', trim($classifier_nfo['ROOT'],'/'));


    }

protected function calculateDimensions() {
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
        $elems = ContractorService::getContractorsListForEconomic( 
                $classifierElement,'',$present,$limit );
        $list = array_map( function($arr) {
                return $arr['Id'];
            }, $elems);            
        }

    $this->tmp_values['select'] = array (["textValue" => '"var"."PeriodHash"',"name" => "PeriodHash"],
        ['textValue' => '"var"."PeriodOrder"','name' => "PeriodOrder"],
        ['textValue' => '"var"."ContractorId"','name' => "ContractorId"],
        ['textValue' => 'NVL(avg(nullif("data"."Employees",0)),0)','name' => 'Employees'],
        ['textValue' => 'NVL(avg(nullif("data"."EmployeesAll",0)),0)','name' => 'EmployeesAll'],
        ['textValue' => 'NVL(avg(nullif("data"."AverageSalary",0)),0)','name' => 'AverageSalary'],
        ['textValue' => 'NVL(avg(nullif("data"."AverageSalaryAll",0)),0)','name' => 'AverageSalaryAll'],
        ['textValue' => 'NVL(sum("data"."Fond"),0)','name' => 'Fond'],
        ['textValue' => 'NVL(sum("data"."FondAll"),0)','name' => 'FondAll'],
        ['textValue' => 'NVL(RATIO_TO_REPORT(avg(nullif("data"."AverageSalary",0))) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'AverageSalaryShare'],
        ['textValue' => 'NVL(RATIO_TO_REPORT(avg(nullif("data"."AverageSalaryAll",0))) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'AverageSalaryAllShare'],
        ['textValue' => 'NVL(RATIO_TO_REPORT(avg(nullif("data"."Employees",0))) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'EmployeesShare'],
        ['textValue' => 'NVL(RATIO_TO_REPORT(avg(nullif("data"."EmployeesAll",0))) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'EmployeesAllShare'],
        ['textValue' => 'NVL(RATIO_TO_REPORT(sum("data"."Fond")) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'FondShare'],
        ['textValue' => 'NVL(RATIO_TO_REPORT(sum("data"."FondAll")) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'FondAllShare']);
    $this->tmp_values['from'] = array (['name'=> 'data','textValue' => '('. $this->sql . ')']);
    $this->tmp_values['where'] = array ('"data"."PeriodHash"(+) = "var"."PeriodHash"',
        '"data"."ContractorId" (+) = "var"."ContractorId"');

    $this->tmp_values['order'] = array(['name' => 'var.PeriodOrder','sort' => 'ASC']);
    $this->tmp_values['group'] = array('var.PeriodHash','var.PeriodOrder','var.ContractorId'); 

    $this->dimensions['select'] = array(["period","Hash","PeriodHash"],["period","Order","PeriodOrder"],
        ["contr","Id","ContractorId"]);
    $this->dimensions['from'] = array(['name' => 'period','textValue' => '(SELECT column_value AS "Hash", ROWNUM AS "Order" FROM TABLE(INT_ARRAY('.$this->reportParams->periods->getPeriodsString().')))'],
        ['name'=> 'contr','textValue' => '('. ContractorService::getContractorsForDimensions($list).')']);

     }
    protected function appendDimensions()
    {
        $dim_sql = $this->combineSql($this->dimensions);  
        $this->tmp_values['from'][] = array('name' => 'var', 'textValue' => '('.$dim_sql.')' );
        $this->sql = $this->combineSql($this->tmp_values);
        $this->tmp_values = Tools::getValuesFromArray($this->tmp_values['select'], 'name');
        $this->dimensions = array_diff($this->tmp_values, ['Employees','EmployeesShare','EmployeesAll','EmployeesAllShare',
            'AverageSalary','AverageSalaryShare','AverageSalaryAll','AverageSalaryAllShare','Fond','FondShare','FondAll','FondAllShare']);
        $this->dimensions_without_periods = array_diff($this->dimensions, ['PeriodHash','PeriodOrder']);
        if (count($this->dimensions_without_periods)) {
            $this->dimensions_without_periods_string =  '"' . implode('", "', $this->dimensions_without_periods) . '"';
        }
    }
    protected function addLastPeriod()
    {
        $sql =  'SELECT "' . implode('", "', $this->dimensions) . '",';
        foreach ($this->reportSettings['units'] as $value)
        {
            $sql.='"'.$value.$this->prefix_all.'" AS '.'"Data'.$value.$this->prefix_all. '",';
            $sql.=' FIRST_VALUE("' . $value .$this->prefix_all. '") OVER  (' . (($this->dimensions_without_periods_string) ? (' PARTITION BY ' . $this->dimensions_without_periods_string) : '') . ' ORDER BY "PeriodHash" DESC) AS "DataLast'.$value.$this->prefix_all.'" ,';
        }
        $sql = trim($sql,',');
        
         $this->sql = $sql.' FROM (' . $this->sql . ')';   
    }

    
    
    protected function exchangePrice () 
    {
        $multiplier = $this->reportSettings['multiplier']['val'];
        $currency = (int) $this->reportSettings['currency'];
        $tmp_sql = 'SELECT "PeriodHash","PeriodOrder","Employees","EmployeesAll","ContractorId",';
        $tmp_sql.= (($currency) ? ('EXCHANGE.EXCH_PRICE("AverageSalary", "Year", "Month", "CurrencyId",' . $currency . ')' . (($multiplier > 0) ? ('/' . $multiplier) : '')) : '0') . ' AS "AverageSalary",';
        $tmp_sql.= (($currency) ? ('EXCHANGE.EXCH_PRICE("AverageSalaryAll", "Year", "Month", "CurrencyId",' . $currency . ')' . (($multiplier > 0) ? ('/' . $multiplier) : '')) : '0') . ' AS "AverageSalaryAll",';
        $tmp_sql.= (($currency) ? ('EXCHANGE.EXCH_PRICE("Fond", "Year", "Month", "CurrencyId",' . $currency . ')' . (($multiplier > 0) ? ('/' . $multiplier) : '')) : '0') . ' AS "Fond",';
        $tmp_sql.= (($currency) ? ('EXCHANGE.EXCH_PRICE("FondAll", "Year", "Month", "CurrencyId",' . $currency . ')' . (($multiplier > 0) ? ('/' . $multiplier) : '')) : '0') . ' AS "FondAll" FROM ';
        $this->sql = $tmp_sql . '(' . $this->sql . ') "data"';

        
    }
    protected function groupingValues()
    {
        $operation = "";
        $round = 0;
        $sql = 'SELECT "' . implode('", "', $this->dimensions) . '",';
        foreach ($this->reportSettings['units'] as $value)
        {
          //  $operation = stristr($value, 'averagesalary') ? 'AVG' : 'SUM';
            $round = stristr($value, 'employees') ? 0 : 1;
            $sql.= 'ROUND( AVG(NULLIF("Data'.$value.$this->prefix_all.'",0)),'.$round.') AS "Data'.$value.$this->prefix_all.'Avg",';
//            $sql.= 'ROUND( AVG("Data'.$value.$this->prefix_all.'"),'.$round.') AS "Data'.$value.$this->prefix_all.'Avg",';
            $sql.= 'ROUND( SUM ("Data'.$value.$this->prefix_all.'"),'.$round.') AS "Data'.$value.$this->prefix_all.'",';
        }
        $sql = trim($sql,',');
        $this->sql = $sql.' FROM (' . $this->sql . ')';
        // зацепка для специальных отчетовотчетов
        $this->sql.= ' GROUP BY ROLLUP("' . implode('", "', $this->dimensions) . '")';
        
    }
    protected function percentCalc() 
    {
        $this->sql = 'WITH "data1" AS (' . $this->sql . ')
                                        SELECT * FROM "data1"
                                        WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL';
        if ($this->reportParams->periods->periodEqual()) {
           $hashes = $this->reportParams->periods->getPeriodsHashes();
           $this->sql.=' UNION SELECT -1 AS "PeriodHash", NULL as "PeriodOrder",'. 
                   ($this->dimensions_without_periods_string ? ($this->dimensions_without_periods_string . ', ') : '');
           foreach ($this->reportSettings['units'] as $value)
           {
               $this->sql.= 'P2L("Data'.$value.$this->prefix_all.'") AS "Data'.$value.$this->prefix_all.'",';
               $this->sql.= 'P2L("Data'.$value.$this->prefix_all.'Avg") AS "Data'.$value.$this->prefix_all.'Avg",';

           }
           $this->sql = trim($this->sql,',');
           $this->sql.= ' FROM (SELECT * FROM "data1" WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL ORDER BY "PeriodOrder" ASC)';
           if (count($this->dimensions_without_periods) > 0) {
                $this->sql.= ' GROUP BY ' . $this->dimensions_without_periods_string;
            }
           if (count($hashes) > 2) {
                $this->sql.=  ' UNION SELECT -2 AS "PeriodHash", NULL as "PeriodOrder", ' . ($this->dimensions_without_periods_string ? ($this->dimensions_without_periods_string . ', ') : '');
                foreach ($this->reportSettings['units'] as $value)
                {
                    $this->sql.= 'F2L("Data'.$value.$this->prefix_all.'") AS "Data'.$value.$this->prefix_all.'",';
                     $this->sql.= 'F2L("Data'.$value.$this->prefix_all.'Avg") AS "Data'.$value.$this->prefix_all.'Avg",';
                }
                $this->sql = trim($this->sql,',');            
                $this->sql.= ' FROM (SELECT * FROM "data1" WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL ORDER BY "PeriodOrder" ASC)';
                
                if ($this->dimensions_without_periods_string != "") {
                    $this->sql.= ' GROUP BY ' . $this->dimensions_without_periods_string;
                }
        
        
            }
        }
    }
     /**
     * Финальный вывод
     */
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
                 $this->tmp_values['select'][] = ['name' => 'Contractor', 'textValue' => 'DECODE("final_data"."ContractorId", -1, \''.l('OTHER','words').'\',Null,\'InAverage\', "TBLCONTRACTOR"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCONTRACTOR" ON "TBLCONTRACTOR"."Id" = "final_data"."ContractorId" ';
                 $this->tmp_values['order'][]= ['textValue' => 'CASE WHEN "final_data"."ContractorId" IS NULL THEN \'A\' WHEN "final_data"."ContractorId"=-1 THEN \'C\' ELSE \'B\' END'];
             
         }
    //     $this->tmp_values['select'][] = ['textValue' => '"final_data".'.\Rosagromash\Tools::addDoubleQuotes($value)];
     }        
        $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."PeriodHash">=0 THEN \'A\' WHEN "final_data"."PeriodHash"=-1 THEN \'B\' WHEN "final_data"."PeriodHash"=-2 THEN \'C\' END ASC NULLS LAST'];
        foreach ($this->reportSettings['units'] as $val)
         {
            array_push($this->columns, 'Data'.$val.$this->prefix_all);
            $this->tmp_values['select'][] = ['name' => 'Data'.$val.$this->prefix_all, 'textValue' => '"final_data"."Data'.$val.$this->prefix_all.'"'];
            $this->tmp_values['order'][] = ['textValue' =>  'FIRST_VALUE("final_data"."Data'.$val.$this->prefix_all.'") OVER ('.(count($this->dimensions_without_periods) ? ('PARTITION BY "final_data"."' . implode('", "final_data"."', $this->dimensions_without_periods) . '"') : '') .' ORDER BY "final_data"."PeriodHash" DESC) DESC'];
            $this->tmp_values['select'][] = ['name' => 'Data'.$val.$this->prefix_all.'Avg', 'textValue' => '"final_data"."Data'.$val.$this->prefix_all.'Avg"'];
            $this->tmp_values['order'][] = ['textValue' =>  'FIRST_VALUE("final_data"."Data'.$val.$this->prefix_all.'Avg") OVER ('.(count($this->dimensions_without_periods) ? ('PARTITION BY "final_data"."' . implode('", "final_data"."', $this->dimensions_without_periods) . '"') : '') .' ORDER BY "final_data"."PeriodHash" DESC) DESC'];
            $this->data_units[] = 'Data'.$val.$this->prefix_all;
         }

         $this->sql = $this->combineSql($this->tmp_values);
        
        
    
    }
            
        
}