<?php

namespace app\stat\report\reports;

use app\stat\model\Currency;
use app\models\user\LoginUser;
use app\stat\db\SimpleSQLConstructor;
use app\stat\Tools;
use app\stat\Validate;
use Yii;
/**
 * Класс содержащий отчеты по производимой и отгружаемой продукции
 *
 * @author kotov
 */
abstract class ProductionRoot extends RootReport 
{
    protected $dimensions = array();
    protected $dimensions_without_periods = array();
    protected $dimensions_without_periods_string = "";




/**
 * Конструктор класса-контейнера классов отчетов по производимой и отгружаемой продукции
 */
    public function __construct(LoginUser $user) {
        parent::__construct($user);
         if (key_exists('presents', $this->settings)) {
            $this->reportSettings['presents'] = $this->settings['presents'];
        }
        else {
            $this->reportSettings['presents'] = 'on';
        }
        
	if (key_exists('units', $this->settings)) {
            $this->reportSettings['units'] = explode(',', $this->settings['units']);
        }
        else {
            $this->reportSettings['units'] = array('Price');
        }
        if (key_exists('russian', $this->settings)) {
            $this->reportSettings['russian'] = $this->settings['russian'];
        }
        else {
                $this->reportSettings['russian'] = $this->individualSettings['russian'] ? 'on': 'off';
        }
        if (key_exists('assembly', $this->settings)) {
            $this->reportSettings['assembly'] = $this->settings['assembly'];
        }
        else {
            $this->reportSettings['assembly'] = $this->individualSettings['assembly'] ? 'on': 'off';
        }
        if (key_exists('foreign', $this->settings)) {
            $this->reportSettings['foreign'] = $this->settings['foreign'];
        }
        else {
            $this->reportSettings['foreign'] = $this->individualSettings['foreign'] ? 'on': 'off';
        }
        
        
        if (key_exists('no_nulls', $this->settings)) {
            $this->reportSettings['no_nulls'] = $this->settings['no_nulls'];
        }
        else {
            $this->reportSettings['no_nulls'] = 'on';
        }
        $multiplierArray = l('MULTIPLIER_ARRAY', 'report');
        if (key_exists('multiplier', $this->settings)) {
           $this->reportSettings['multiplier'] = $multiplierArray[$this->settings['multiplier']];
        }
        else {
            $this->reportSettings['multiplier'] = $multiplierArray['thousand']; // по умолчанию
        }
        
        $this->tmp_values = $this->initArrayForSql();
        $this->dimensions = $this->initArrayForSql();
        
    }
    /**
     * Индивидуальные параметра для данного типа отчетов
     */
    protected function constructReportSettings() 
    {
        parent::constructReportSettings();
        
        // единицы измерения

        
        $select_multiplier = '';
        $select_currency = '';
        $russian = true;
        $assembly = false;
        $foreign = false;
        if (key_exists('multiplier', $this->settings)) {
            $select_multiplier = $this->settings['multiplier'];
        }
        else {
            $select_multiplier = 'thousand'; // по умолчанию
        }
        if (key_exists('currency', $this->settings)) {
            $select_currency = $this->settings['currency'];
        }
        
        if (key_exists('russian', $this->settings) && $this->settings['russian'] == 'off') {
            $russian = false;
        } else {
            if (!key_exists('russian', $this->settings) && $this->datasource_id == 5) {
                $russian = false;
            }                           
        }
        if (key_exists('assembly', $this->settings) && $this->settings['assembly'] == 'on') {
            $assembly = true;
        }
        if (key_exists('foreign', $this->settings) && $this->settings['foreign'] == 'on') {
            $foreign = true;
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
        $this->tpl_vars['dimension_elements']['Amount'] = 
                array('type' => 'checkbox', 'label' =>  l('DATAAMOUNT','words') );
       /* $this->tpl_vars['dimension_elements']['Price'] = 
                array('type' => 'checkbox', 'label' =>  l('DATAPRICE','words') );*/
        if (in_array('Amount', $this->reportSettings['units'])) {
            $this->tpl_vars['dimension_elements']['Amount']['checked'] = true;
        }

        $this->tpl_vars['dimension_elements']['PriceBlock'] = 
                array ('type' => 'list','class'=>'price_dimensions','label_group' => l('DATAPRICE_UNIT','words'),'elements' => 
                    array(                        
                        'multiplier' => ['type' => 'select','options' => $multOptions],
                        'currency' => ['type' => 'select','options' => $currencyOptions]                
                    ), 'root_element' => array( 'type' => 'checkbox','class' => 'price_unit' ,'name' => 'Price', 'label' =>  l('DATAPRICE','words')));
        
        if (in_array('Price', $this->reportSettings['units'])) {
            $this->tpl_vars['dimension_elements']['PriceBlock']['root_element']['checked'] = true;
        }
        
        if (key_exists('checked', $this->tpl_vars['dimension_elements']['PriceBlock']['root_element'])) {
            if (!$this->tpl_vars['dimension_elements']['PriceBlock']['root_element']['checked']) {
                $this->tpl_vars['dimension_elements']['PriceBlock']['hide'] = true;
            }
        }


        if ($this->datasource_id != 5 || is_admin() || is_analytic()) {
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
        }
                
    }

    /**
     * Фильтр по классификатору
     */
    protected function classifierFilter () 
    {
        $manuf_type_filter = array(); // фильтр по типу техники
        $where_manuf_filter = '';
        $regions_manuf_filter = '';
        $presents_manuf_filter = '';
        if ($this->reportSettings['russian'] == 'on') {
            array_push($manuf_type_filter, 1);
        }
        if ($this->reportSettings['assembly'] == 'on') {
            array_push($manuf_type_filter,2);
        }
        if ($this->reportSettings['foreign'] == 'on') {
            array_push($manuf_type_filter,3);
        }
        if ($this->reportSettings['presents'] == 'on') {
            $presents_manuf_filter = ' AND "base"."Present" = 1 ';
        }
        if (count($manuf_type_filter)> 0)
        {
            $where_manuf_filter = ' AND "mdl"."ModelTypeId" IN ('.implode(',',$manuf_type_filter).') ';
        }
        if (!(is_admin() || is_analytic() || Yii::$app->user->getIdentity()->contractorId != 676)) {
            $where_manuf_filter.= ' AND "mdl"."ClassifierId" != 1507 ';
        }
        
        if (key_exists('regions', $this->reportSettings)) {
            $regions = $this->reportSettings['regions'];
            $regions_manuf_filter = ' AND "mdl"."BrandId" IN (SELECT "tb"."Id" FROM "TBLBRAND" "tb", "TBLCONTRACTOR" "tc" ,"TBLREGION" "tr","TBLCITY" "cty","TBLCOUNTRY" "cnt"
                                    WHERE
                                    "tc"."CityId" = "cty"."Id" AND
                                    "tr"."Id"  IN ('.$regions.') AND
                                    "cty"."RegionId" = "tr"."Id" AND
                                    "cnt"."Id" = "tr"."CountryId" AND
                                    "tb"."ContractorId" = "tc"."Id"
                                  )';
        }        
        
        $classifier = $this->reportParams->classifier->getId();
        $this->sql = 'SELECT "base".*,
                             "classifier"."Id" AS "ClassifierId"
                                FROM (' . $this->sql . ') "base",
                             TBLMODEL "mdl",
                            (SELECT "Id" FROM "TBLCLASSIFIER" START WITH "Id"=' . $classifier . ' CONNECT BY PRIOR "Id"="ClassifierId") "classifier"
					WHERE "base"."ModelId" = "mdl"."Id" AND
						"mdl"."ClassifierId" = "classifier"."Id"'.
                            $presents_manuf_filter.
                            $where_manuf_filter.
                            $regions_manuf_filter;
                
    }    
    /**
     * Пересчет цены
     */
    protected function exchangePrice()
    {        
        $multiplier = $this->reportSettings['multiplier']['val'];
        $currency = (int) $this->reportSettings['currency'];
        $regions = (empty($this->reportParams->countries) || $this->alias == 'full' || $this->alias == 'modelsContractor' ) ? false : true;
        $this->sql =  'SELECT "Count", ' . (($currency) ? ('EXCHANGE.EXCH_PRICE("Price", "Year", "Month", "CurrencyId",' . $currency . ')' . (($multiplier > 0) ? ('/' . $multiplier) : '')) : '0') . ' AS "Price", "PeriodHash", "ModelId", "ContractorId", "ManufacturerId", ' . (($regions) ? '"RegionId", ' : '') . '"ClassifierId", "TypeData", "Type" FROM (' . $this->sql . ')'; 
    }
    /**
     * Измерения
     */
    protected function calculateDimensions()
    {
        $this->tmp_values['select'] = array (["textValue" => '"var"."PeriodHash"',"name" => "PeriodHash"],
            ['textValue' => '"var"."PeriodOrder"','name' => "PeriodOrder"],
            ['textValue' => 'NVL(sum("data"."Count"),0)','name' => 'Amount'],
            ['textValue' => 'NVL(RATIO_TO_REPORT(sum("data"."Count")) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'AmountShare'],
            ['textValue' => 'NVL(sum("data"."Price"),0)','name' => 'Price'],
            ['textValue' => 'NVL(RATIO_TO_REPORT(sum("data"."Price")) OVER (PARTITION BY "var"."PeriodHash"),0)*100','name' => 'PriceShare']);
        $this->tmp_values['from'] = array (['name'=> 'data','textValue' => '('. $this->sql . ')']);
        $this->tmp_values['where'] = array ('"data"."PeriodHash"(+) = "var"."PeriodHash"');        
        
        $this->tmp_values['order'] = array(['name' => 'var.PeriodOrder','sort' => 'ASC']);
        $this->tmp_values['group'] = array('var.PeriodHash','var.PeriodOrder'); 
        
        $this->dimensions['select'] = array(["period","Hash","PeriodHash"],["period","Order","PeriodOrder"]);
        $this->dimensions['from'] = array(['name' => 'period','textValue' => '(SELECT column_value AS "Hash", ROWNUM AS "Order" FROM TABLE(INT_ARRAY('.$this->reportParams->periods->getPeriodsString().')))']);
        if ($this->alias != 'full' && $this->alias != 'modelsContractor') {
            if (!empty ($this->reportParams->countries)) {
                $country_elements_str = '';
                $is_first = true;
                foreach ($this->reportParams->countries as $country) {
                    if (Validate::isInt($country['Id'])) {
                        if (!$is_first) {
                            $country_elements_str .= ' UNION ';
                        }                        
                        $country_elements_str.= 'SELECT '.$country['Id'].' AS "Id" FROM DUAL';
                        $is_first = FALSE;                    
                    }
                }

                if (!empty($country_elements_str)) {
                    $this->dimensions['select'][] = ["region","Id","RegionId"];
                    $this->dimensions['from'][] = ['name' => 'region', 'textValue' => '(' .$country_elements_str . ')'];
                    $this->tmp_values['group'][] = 'var.RegionId';
                    $this->tmp_values['select'][] = ["name" => "RegionId", "textValue" => '"var"."RegionId"'];
                    $this->tmp_values['where'][] = '"data"."RegionId"(+) = "var"."RegionId"';
                }
                //$select_countries_str = 
            }
        }
        
     //   $this->dimensions['from'] = array('textValue' = )
    }
    /**
     * Объединение с измерениями
     */
    protected function appendDimensions()
    {
        $dim_sql = $this->combineSql($this->dimensions);        
        $this->tmp_values['from'][] = array('name' => 'var', 'textValue' => '('.$dim_sql.')' );
        $this->sql = $this->combineSql($this->tmp_values);
        $this->tmp_values = Tools::getValuesFromArray($this->tmp_values['select'], 'name');
        $this->dimensions = array_diff($this->tmp_values, ['Amount','AmountShare','Price','PriceShare']);
        
        $this->dimensions_without_periods = array_diff($this->dimensions, ['PeriodHash','PeriodOrder']);
        if (count($this->dimensions_without_periods)) {
            $this->dimensions_without_periods_string =  '"' . implode('", "', $this->dimensions_without_periods) . '"';
        }
        
        
    }
    /**
     * Значение последнего периода
     */
    protected function addLastPeriod()
    {
                
        $sql =  'SELECT "' . implode('", "', $this->dimensions) . '",';
        foreach ($this->reportSettings['units'] as $value)
        {            
            $sql.='"'.$value.'" AS '.'"Data'.$value. '",';
            $sql.=' FIRST_VALUE("' . $value . '") OVER  (' . (($this->dimensions_without_periods_string) ? (' PARTITION BY ' . $this->dimensions_without_periods_string) : '') . ' ORDER BY "PeriodHash" DESC) AS "DataLast'.$value.'" ,';            
        }
        $sql = trim($sql,',');
        
         $this->sql = $sql.' FROM (' . $this->sql . ')';   
    }
    /**
     * Группировка
     */
    protected function groupingValues()
    {
        $sql = 'SELECT "' . implode('", "', $this->dimensions) . '",';
        foreach ($this->reportSettings['units'] as $value)
        {
            $sql.= 'SUM("Data'.$value.'") AS "Data'.$value.'",';
        }
        $sql = trim($sql,',');
        $this->sql = $sql.' FROM (' . $this->sql . ')';
        // зацепка для специальных отчетовотчетов
        $this->groupingValuesFiltersHook();
        $this->sql.= ' GROUP BY ROLLUP("' . implode('", "', $this->dimensions) . '")';
        
        
        
        
    }

    protected function groupingValuesFiltersHook() {}
    
    /**
     * Вычисление процентов
     */
    protected function percentCalc() {
        
        $this->sql = 'WITH "data1" AS (' . $this->sql . ')
						SELECT * FROM "data1"
                                                WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL';
        if ($this->reportParams->periods->periodEqual()) {
           $hashes = $this->reportParams->periods->getPeriodsHashes();
           $this->sql.=' UNION SELECT -1 AS "PeriodHash", NULL as "PeriodOrder",'. 
                   ($this->dimensions_without_periods_string ? ($this->dimensions_without_periods_string . ', ') : '');
           foreach ($this->reportSettings['units'] as $value)
           {
               $this->sql.= 'P2L("Data'.$value.'") AS "Data'.$value.'",';

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
                    $this->sql.= 'F2L("Data'.$value.'") AS "Data'.$value.'",';
                }
                $this->sql = trim($this->sql,',');            
                $this->sql.= ' FROM (SELECT * FROM "data1" WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL ORDER BY "PeriodOrder" ASC)';
                
                if ($this->dimensions_without_periods_string != "") {
                    $this->sql.= ' GROUP BY ' . $this->dimensions_without_periods_string;
                }
           }           
        } 
     /*   else {
            $this->sql.= ' WHERE "PeriodHash" IS NOT NULL AND "PeriodOrder" IS NOT NULL';
        }*/
        
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
                 $this->tmp_values['select'][] = ['name' => 'Contractor', 'textValue' => 'DECODE("final_data"."ContractorId", -1, \''.l('OTHER','words').'\', "TBLCONTRACTOR"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCONTRACTOR" ON "TBLCONTRACTOR"."Id" = "final_data"."ContractorId" ';
                 $this->tmp_values['order'][]= ['textValue' => 'CASE WHEN "final_data"."ContractorId" IS NULL THEN \'A\' WHEN "final_data"."ContractorId"=-1 THEN \'C\' ELSE \'B\' END'];
                 break;
             case 'ClassifierId':
                 $this->other_columns[] = 'Classifier';
                 $this->tmp_values['select'][] = ['name' => 'Classifier','textValue' => '"TBLCLASSIFIER"."Name")','textValue' => 'DECODE("final_data"."ClassifierId", -1, \''.l('OTHER','words').'\', "TBLCLASSIFIER"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCLASSIFIER" ON "TBLCLASSIFIER"."Id" = "final_data"."ClassifierId"';
                 $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."ClassifierId" IS NULL THEN \'A\' WHEN "final_data"."ClassifierId"=0 THEN \'B\' WHEN "final_data"."ClassifierId"=-1 THEN \'D\' ELSE \'C\' END'];
                 break;
             case 'ModelId':
                 $this->other_columns[] = 'Model';
                 $this->tmp_values['select'][] = ['name' => 'Model','textValue' => '"TBLMODEL"."Name")','textValue' => 'DECODE("final_data"."ModelId", -1, \''.l('OTHER','words').'\', (CASE WHEN "CONTR"."Name" IS NULL THEN \'\' ELSE "CONTR"."Name" || \' - \' END) ||  "TBLMODEL"."Name")'];
                 $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLMODEL" ON "TBLMODEL"."Id" = "final_data"."ModelId" LEFT OUTER JOIN "TBLBRAND" ON "TBLMODEL"."BrandId" = "TBLBRAND"."Id" LEFT OUTER JOIN "TBLCONTRACTOR" "CONTR" ON "TBLBRAND"."ContractorId" = "CONTR"."Id"';
                 $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."ModelId" IS NULL THEN \'A\' WHEN "final_data"."ModelId"=-1 THEN \'C\' ELSE \'B\' END'];
                 break;
             case 'RegionId':
                 //$this->other_columns[] = 'Regions';
                 switch ($this->regionType) {
                    case 'country':
                        $this->other_columns[] = 'Region';
                        $this->tmp_values['select'][] = ['name' => 'Region', 'textValue' => 'DECODE("final_data"."RegionId", -1, \''.l('OTHER','words').'\', "TBLCOUNTRY"."Name")'];
                        $this->tmp_values['from'][0]['textValue'] .= ' LEFT OUTER JOIN "TBLCOUNTRY" ON "TBLCOUNTRY"."Id" = "final_data"."RegionId"';
                      //  $this->tmp_values['order'][]= ['textValue' => 'CASE WHEN "final_data"."RegionId" IS NULL THEN \'A\' WHEN "final_data"."RegionId"=-1 THEN \'C\' ELSE \'B\' END'];
                        $this->tmp_values['order'][] = ['textValue' => '"Region" NULLS FIRST'];
                        break;
                 }
                 break;
                 
             
         }
    //     $this->tmp_values['select'][] = ['textValue' => '"final_data".'.\Rosagromash\Tools::addDoubleQuotes($value)];
     }  
        $this->tmp_values['order'][] = ['textValue' => 'CASE WHEN "final_data"."PeriodHash">=0 THEN \'A\' WHEN "final_data"."PeriodHash"=-1 THEN \'B\' WHEN "final_data"."PeriodHash"=-2 THEN \'C\' END ASC NULLS LAST'];
       // $sort = $this->reportParams->periods->periodEqual() ? 'DESC' : 'ASC';
        $sort = 'DESC';
        foreach ($this->reportSettings['units'] as $val)
         {
            array_push($this->columns, 'Data'.$val);
            $this->tmp_values['select'][] = ['name' => 'Data'.$val, 'textValue' => '"final_data"."Data'.$val.'"'];
            $this->tmp_values['order'][] = ['textValue' =>  'LAST_VALUE("final_data"."Data'.$val.'") OVER ('.(count($this->dimensions_without_periods) ? ('PARTITION BY "final_data"."' . implode('", "final_data"."', $this->dimensions_without_periods) . '"') : '') .' ORDER BY "final_data"."PeriodHash" '.$sort.') DESC'];
            $this->data_units[] = 'Data'.$val;
         }

         $this->sql = $this->combineSql($this->tmp_values);
        
        
    
    }

    protected function prepareSQL()
    {
        $max_periods_count = 12;
        parent::prepareSQL();
        if ($this->reportParams->datasource->id == 4 || $this->reportParams->datasource->id == 5) {
            if ($this->reportParams->periods->getRealPeriodsCount() > $max_periods_count) {
                    $this->status['ERROR'] = true;
                    $this->status['ERROR_MESSAGE'] = [
                        'head' => l('ERROR','messages'),
                        'body' => 'Для построения отчета по экспорту или импорту выберите не более '.$max_periods_count.' периодов.'
                ];
            return;
            }
        }
        
        $sResult = '';
       $select = array( ["prod","CurrencyId"],
                        ["prod","Year"],
                        ["prod","Month"],
                        ["prod","ModelId"],
                        ["prod","ContractorId"],
                        ["prod","TypeData"],
                        ["manuf","Id","ManufacturerId"],
                        ["manuf","Present"]);
        $tables = array(
            [ 'TBLPRODUCTION','prod'],
            ['TBLINPUTFORM','frm'],
            ['TBLBRAND','manuf_brn'],
             ['TBLMODEL','manuf_mdl'],
             ['TBLCONTRACTOR','manuf']);
        $where = array(
            ['param' => 'frm.Id', 'staticValue' => 'prod.InputFormId'],
            ['param' => 'frm.Actuality', 'operation' =>  '<>', 'staticNumber' => 0],
            ['param' => 'manuf.Id', 'staticValue' => 'manuf_brn.ContractorId'],
            ['param' => 'manuf_mdl.BrandId','staticValue' => 'manuf_brn.Id'],
            ['param' => 'manuf_mdl.Id', 'staticValue' => 'prod.ModelId'],
            '"prod"."TypeData" IN '.$this->reportParams->datasource->getDataBaseTypes());
            
        switch ($this->reportParams->datasource->getId())
        {
            case 13:
                $select[] = ['textValue' => '(CASE WHEN "prod"."TypeData" = 1 THEN 1 ELSE 0 END)', 'name' => 'Type'];
                $select[] = ['textValue' => 'CASE
                            WHEN "prod"."TypeData" = 1 then "prod"."Count"
                            WHEN RANK() OVER
                            (
                              PARTITION BY
                                "prod"."RegionId",
                                "prod"."Year",
                                "prod"."Month",
                                "prod"."ModelId"
                              ORDER BY
                                CASE WHEN "prod"."TypeData"=2 THEN 1 ELSE 2 END ASC
                            ) = 1 THEN "prod"."Count" ELSE 0 END','name' => 'Count'];
                $select[] = ['textValue' => 'CASE
                            WHEN "prod"."TypeData" = 1 then "prod"."Price"
                            WHEN RANK() OVER
                            (
                              PARTITION BY
                                "prod"."RegionId",
                                "prod"."Year",
                                "prod"."Month",
                                "prod"."ModelId"
                              ORDER BY
                                CASE WHEN "prod"."TypeData"=2 THEN 1 ELSE 2 END ASC
                            ) = 1 THEN "prod"."Price" ELSE 0 END','name' => 'Price'];
                
                break;            
            default:
                $select[] = [0,'Type'];
                $select[] = ["prod","Count"];
                $select[] = ["prod","Price"];
                
                break;
        }
    
        // @todo фильтр по регионам
        switch ($this->regionType) {
            case 'region':
                if (count($this->reportParams->regions) > 0) {
                    $where[]= '"prod"."RegionId" IN (' . $this->reportParams->regions_str . ')' ; 
                    $select[] = ["prod","RegionId" ];
                    $this->reportParams->countries = [];
                }                
                break;                            
            default: 
                $regId = ["prod","RegionId" ];
                $tables[] = ['"TBLREGION"','"reg"'];
                $where[]= ['param' => '"reg"."Id"','staticValue' => '"prod"."RegionId"' ];
                if (!in_array($this->reportParams->datasource->getId(), [4,5]) 
                       || empty($this->reportParams->countries)
                        ) {
                    $where[]= ['param' => '"reg"."CountryId"','staticNumber' => 1 ]; 
                    $this->reportParams->countries = [];
                } else {
                    if ($this->reportParams->allRegionsTogether != 'on') {
                       $select[] = ["prod","CountryId","RegionId"];
                       $regId = [];
                       
                    }
                    else {
                        $this->reportParams->countries = [];
                    }
                    if (!empty($this->reportParams->countriesStr)) {
                        $where[]= '"prod"."CountryId" IN ('.$this->reportParams->countriesStr.')' ; 
                    }
                }
                if (!empty ($regId)) {
                    $select[] = $regId;
                }
                
            break;
        }
        // фильтр по периодам
        $periods = $this->reportParams->periods->getSQLString();
        $select[] = ["periods","PeriodHash"];
        $select[] = ["periods","PeriodOrder"];
        $tables[] = ['textValue' => $periods];
        $where[] = '"prod"."Year"*12+"prod"."Month" BETWEEN "periods"."PeriodStart" AND "periods"."PeriodEnd"';
        // Применить фильтр по не отчитавшимся за определенный период предприятиям
        $filter = $this->getFilterHashesArray();
        if (!empty($filter)) {
            $where[] = '"prod"."Year"*12+"prod"."Month" NOT IN('.implode(',',$filter).')';
        }
        // -----------------------------------   
       // $this->reportParams->datasource
        $sResult = SimpleSQLConstructor::generateClauseSelect($select).' '.
        SimpleSQLConstructor::generateClauseFrom($tables). ' '.
        SimpleSQLConstructor::generateClauseWhere($where);
        $this->sql.=$sResult; 
        $this->classifierFilter();
        $this->exchangePrice();
        $this->calculateDimensions();
        $this->appendDimensions();
        $this->addLastPeriod();
        $this->groupingValues();
        $this->percentCalc();
        $this->finalCalculating();
    }
    // Вспомогательные 
    protected function initArrayForSql() 
    {
        return array (
            'select' => array(),
            'from' => array(),
            'where' => array(),
            'group' => array(),
            'order' => array(),
            'having' => array()
        );
    }
    
}
