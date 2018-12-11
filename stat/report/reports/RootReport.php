<?php
namespace app\stat\report\reports;

use app\stat\Sessions;
use app\stat\model\Currency;
use app\stat\ViewHelper;
use app\stat\report\Params;
use app\models\user\LoginUser;
use app\stat\Convert;
use app\stat\db\SimpleSQLConstructor;
use app\stat\model\Classifier;
use app\stat\Tools;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\stat\exceptions\ReportException;
/**
 * Общий класс для всех типов отчета
 *
 * @author kotov
 */
abstract class RootReport {
    
    const DEFAULT_NAMESPACE_FOR_OUT = 'app\\stat\\report\\out\\';
    /** @var string название отчета  */
    protected $name;
    /** @var string псевдоним (alias) отчета  */
    protected $alias;

    /**
     * Массив переменных для шаблонизатора
     */
    protected $tpl_vars = array();
    /** @var int Порядок отчетов в конструкторе */
    public $order = 0;

    /** @var \Html\BlockElement настройки отчета */
    protected $reportSettingHtml;
    
    /** @var array Элементы для формирования формы с параметрами отчета */
    protected $reportSettingsElements = array();
    
    /** @var array настройки данного отчеты */
    protected $reportSettings=array();
    
    
    /** @var string sql запрос для формирования отчета */
    protected $sql;
    /** @var array  временные данные для запроса */
    protected $tmp_values = array();
    /**@var array специальный ассоциативный массив для отчетов */
    protected $hook = array();
    /** @var IReport  формирует вывод */
    protected $reportObject; //
    /** @var array выводимые столбцы */
    protected $columns = array();
    protected $creatorClassName;
    /** @var array массив параметров отчета */
    protected $settings;
    /** @var array Столбцы с измерениями */
    protected $data_units =array();
    /** @var string Колонка, данные из которой используются для заголовка таблицы */
    protected $head_column;
    /** @var array Массив с данными для заголовка таблицы*/ 
    protected $head_array = array();
    /**@var array Остальные колонки, для вывода в таблице */
    protected $other_columns = array();
    /** @var Params */
    protected $reportParams;
    /** @var array статус формирования отчета */
    protected $status = array();
    /** @var int тип запроса (1 - выгрузка в EXCEL,
     *                        2 - выгрузка для индикаторов
     *                        по умолчанию - табличный отчет) */
    protected $type; 
    
    protected $datasource_id;
    /** @var array Фильтр по незаполненным формам за период */ 
    protected $user_filter = array();
    /**  @var bool  Применить пользовательский фильтр */
    protected $user_filter_enable = true;
    /**
     * Индивидуальные параметры отчетов по умолчанию
     * @var array
     */
    public $individualSettings = [];
    /**
     * Использовать фильтрацию по регионам
     * @var string
     */
    protected $regionType;
    /**
     * Текущий залогиненный пользователь
     * @var LoginUser
     */
    protected $currentUser;
    
    public $is_admin = false;
    public $is_analitic = false;


    public function __construct(LoginUser $user) {                 
        $this->settings = array();
        $this->currentUser = $user;
        $this->status = ['ERROR' => false, 'ERROR_MESSAGE' => []]; // гачальная установка статуса
        $this->head_column = 'Period';
        $report_params = [];
        $datasource_id = 0;
        if (is_array($params = json_decode(Sessions::getReportParams(),true))) {
            $report_params = $params;           
            if (key_exists('report_list', $report_params) && is_array($report_params['report_list'])) {
                if (key_exists($this->alias, $report_params['report_list'])) {
                   $this->settings = $report_params['report_list'][$this->alias];
                }        
            }
        }
        if (key_exists('datasource_id', $report_params)) {
            $datasource_id = $report_params['datasource_id'];
        }
        if (key_exists('regions', $report_params) && !empty($report_params['regions'])) {
            $this->reportSettings['regions'] = $report_params['regions'];
        }
        
        if (key_exists('presents', $this->settings)) {
            $this->reportSettings['presents'] = $this->settings['presents'];
        }
        else {
            $this->reportSettings['presents'] = 'on'; // по умолчанию включено
        }
        
        if (key_exists('no_nulls', $this->settings)) {
            $this->reportSettings['no_nulls'] = $this->settings['no_nulls'];
        }
        else {
            $this->reportSettings['no_nulls'] = 'on';
        }

        if (key_exists('currency', $this->settings)) {
            $currency = $this->settings['currency'];
        }
        else {
            $currency = 1; // по умолчанию
        }
        $this->individualSettings = [
            'russian' => true,
            'foreign' => false,
            'assembly' => false
        ];
        $this->reportSettings['currency'] = $currency;  
        switch ($datasource_id) {
            case 4:
                $this->regionType = 'country';
                break;
            case 5:
                $this->individualSettings['russian'] = false;
                $this->individualSettings['foreign'] = true;
                $this->regionType = 'country';
                break;
            case 6:
                $this->regionType = 'country';                
                break;
            case 13:
                $this->regionType = 'region';
                break;
            default :
                $this->regionType = '';
                break;
        }
        $this->datasource_id = $datasource_id;
    }
    /**
     * Создание HTML-формы с настройками
     */
    protected function constructReportSettings()
    {
        $no_nulls = true;
        if (key_exists('no_nulls', $this->settings) && $this->settings['no_nulls'] == 'off') {
            $no_nulls = false;
        }
        $presents = true;
        if (key_exists('presents', $this->settings) && $this->settings['presents'] == 'off') {
            $presents = false;
        }

        // инициализация переменных
        
        $this->tpl_vars['params'] = l('PARAMS','words');
        // для настроек в шапке окна
        $this->tpl_vars['header_elements'] = array();
        // для настроек в блоке измерений
        $this->tpl_vars['dimension_elements'] = array();
        // для настроек в двухколоночном блоке
        $this->tpl_vars['column_left'] = array();
        $this->tpl_vars['column_right'] = array();
        
        // для настроек в нижней части (подвале)
        $this->tpl_vars['footer_elements'] = array();
        /*
        $this->tpl_vars['header_elements']['report_form'] = array('type' => 'select',
                                                                'class' => 'hide',
                                                                'label' =>  l('REPORT_FORM','report'),
                                                                'options' => array(['text' => l('TABLE','words'),'value' => 'table']));
        */
        
        $this->tpl_vars['column_left']['presents'] = array (
            'type' => 'checkbox',
            'label' => l('PRESENTS','report'),
            'hide' => (!is_admin() && !is_analytic())
        );
        if ($presents == 'on') {
            $this->tpl_vars['column_left']['presents']['checked'] = true;
        }
        $this->tpl_vars['units'] = l('UNITS','report');
        $this->tpl_vars['column_left']['no_nulls'] = array (
            'type' => 'checkbox',
            'label' => l('NO_NULLS','report')
        );       
        if ($no_nulls == 'on') {
            $this->tpl_vars['column_left']['no_nulls']['checked'] = true;
        }      
        


    }
    /**
     * Отобразить настройки отчета из шаблона
     */
    protected function displaySettings() 
    {
      //return Yii::$app->view->render(_REPORTS_TEMPLATES_DIR_.$this->alias .'.twig', $params) ;
        $viewHelper = new ViewHelper(_REPORTS_TEMPLATES_DIR_, $this->alias,$this->tpl_vars);
        return $viewHelper->getRenderedTemplate();


    }

    protected function getCurrentReportParam($reportsList)
    {
        if (!is_array($reportsList)) {
            return array();
        }
        foreach ($reportsList as $report) 
        {
            if ($report['alias'] == $this->alias) {
                return $report;
            }
        }
        return false;
    }

    public function getReportName()
    {
        return $this->name;
    }
    public function getReportAlias() 
    {
        return $this->alias;
    }
    public function getReportSettings()
    {   
        $this->constructReportSettings();
        return $this->displaySettings();
       // return $this->reportSettingHtml->getHtml();
    }
    /**
     * 
     * @param int $type - вид отчета (0 -таблица, 1-excel)
     * @return type
     */
    public function createReport($type = 0)
    {    
     //$SQLDebug = true;
     //  $Debug = true;
       // $CustomDebug = true;

        $this->type = $type;
        $currency = new Currency($this->reportSettings['currency']);  
        try {
            $this->prepareSQL();
        } catch ( ReportException $e ) {
            $this->tpl_vars = [   
                    'head' => l('ERROR','messages'),                
                    'body' => $e->getMessage(),
                    'close' => false
                ];
                $viewHelper = new ViewHelper(_INFORM_TEMPLATES_DIR_, 'message_inform',$this->tpl_vars);
                $resultHtml = $viewHelper->getRenderedTemplate();
            return [
                'errorCode' => 1,
                'message' => $resultHtml                
            ];
        }
        if (isset($CustomDebug)) {
            return [
                 'errorCode' => 0,
                 'message' => print_ar($this->user_filter,true)
            ];
        }
        if (isset($SQLDebug)) {
            return [
                    'errorCode' => 0,
                    'message' => $this->sql
                        ];
        }
 //       return print_ar($this->arraySync($this->constructReport()));
        $paramsArray['Columns'] = $this->columns;
        $paramsArray['Name'] = $this->getReportName();
        $paramsArray['Classifier'] = $this->reportParams->classifier->getClassifierName();
        $paramsArray['ClassifierId'] = $this->reportParams->classifier->getId();
        $paramsArray['ClassifierFull'] = $this->reportParams->fullReportClassifier->getClassifierName();
        $paramsArray['ClassifierFullId'] = $this->reportParams->fullReportClassifier->getId();
        $paramsArray['Source'] = $this->reportParams->datasource->getDatasourceName();
        $paramsArray['SourceId'] = $this->reportParams->datasource->getId();
        $paramsArray['Multiplier'] = $this->reportSettings['multiplier']['val'] != 1 ? $this->reportSettings['multiplier']['text'] : '';
        $paramsArray['Currency'] = $currency->getCurrencyName();
        $paramsArray['DataUnits'] = $this->data_units;
        $paramsArray['HeadColumn']= $this->head_column;        
        $paramsArray['RealPeriodsCount'] = $this->reportParams->periods->getRealPeriodsCount();
        $paramsArray['ReportName'] = $this->alias;
        $paramsArray['UserFilterEnable'] = $this->user_filter_enable;
        $paramsArray['UserFilter'] = $this->user_filter;
        
        if (key_exists('no_nulls', $this->settings)) {
            $paramsArray['NoNulls'] = $this->settings['no_nulls'] == 'off' ? false : true;
        } else {
            $paramsArray['NoNulls'] = true; 
        }        
        
        if (!$this->status['ERROR']) {
        //    $paramsArray['Data'] = array();
           $report_data = $this->constructReport();
           try {
                $paramsArray['Data'] = $this->arraySync($report_data); 
           }
           catch ( ReportException $e ) {
                $this->tpl_vars = [   
                    'head' => l('ERROR','messages'),                
                    'body' => $e->getMessage(),
                    'close' => false
                ];
                $viewHelper = new ViewHelper(_INFORM_TEMPLATES_DIR_, 'message_inform',$this->tpl_vars);
                $resultHtml = $viewHelper->getRenderedTemplate();
            return [
                'errorCode' => 1,
                'message' => $resultHtml                
            ];        
           } 
          //  return $this->sql;
         if (isset($Debug)) {
            return [
                'message' => print_ar($paramsArray['Data'],true)
                    ];
         }
           // return print_ar($paramsArray['Data'],true);
            $paramsArray['HeadArray'] = $this->head_array;
            $paramsArray['OtherColumns'] = $this->other_columns;
            $className = self::DEFAULT_NAMESPACE_FOR_OUT . $this->creatorClassName;
            $this->reportObject = new $className($paramsArray);
            if (!$type) {
                $resultHtml = $this->reportObject->getWebTableData();
                return [
                'message' => $resultHtml
                    ];
            }
            if ($type == 2) {
                $dt = new \DateTime();
                $current_dir_name = $dt->format('dmY');
                //$this->tpl_vars['test'] = $dt->format('dmY');
                if (chdir(_INDICATORS_DIR_)) {
                    $template_file_name = 'save_data_for_indicators';                    
                    if (!is_dir($current_dir_name)) {
                        mkdir($current_dir_name);
                    }
                    
                    $elements = scandir($current_dir_name);
                    $fn_pattern = '/^\d\d\d\d*[.]dat$/';
                    $files_list = array();
                    foreach ($elements as $key => $content) {
                        if (preg_match($fn_pattern, $content)) {
                            array_push($files_list, $content);
                        }
                    }
                    if (count($files_list) > 0) {
                        $last_file = array_pop($files_list);
                        $file_name = Convert::getNumbers($last_file);
                        $file_name++;
                        $file_name = str_pad($file_name, 4,'0',STR_PAD_LEFT);
                    } else {
                        $file_name = '0001';
                    }
                    chdir(_INDICATORS_DIR_.$current_dir_name);
                    $result_data = [
                        'classifier' => $this->reportParams->classifier_full->getName(),
                        'first_period' => [
                            'start_month' => $this->reportParams->periods->getPeriod(0)->getStartMonth(),
                            'end_month' => $this->reportParams->periods->getPeriod(0)->getEndMonth(),
                            'start_year' =>  $this->reportParams->periods->getPeriod(0)->getStartYear(),
                            'end_year' =>  $this->reportParams->periods->getPeriod(0)->getEndYear()
                            
                            ],
                        'second_period' => [
                            'start_month' => $this->reportParams->periods->getPeriod(1)->getStartMonth(),
                            'end_month' => $this->reportParams->periods->getPeriod(1)->getEndMonth(),
                            'start_year' =>  $this->reportParams->periods->getPeriod(1)->getStartYear(),
                            'end_year' =>  $this->reportParams->periods->getPeriod(1)->getEndYear() 
                            ],
                        'report_data' => $paramsArray['Data'],                    
                    ];
                    $new_file = new FileHelper($file_name.'.dat');                    
                    $new_file->putOrReplaceData(serialize($result_data));
                    
                 //   $new_file->                
                    
                    
                    
                }

                $viewHelper = new ViewHelper(_INFORM_TEMPLATES_DIR_, 'save_data_for_indicators',$this->tpl_vars);
                return [
                    'message' => $viewHelper->getRenderedTemplate()
                    ];                
            }
             if ($type == 1 ) {
                $xls = new Spreadsheet(); 
                if (count(ob_get_status()) > 0) {
                    ob_end_clean();
                }
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=ANSI');                                
                header('Content-Disposition: attachment;filename="'.$this->alias.date('d.m.Y').'.xlsx"');
                header('Cache-Control: max-age=0');
               // $this->reportObject->              
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle(Tools::catString($paramsArray['Name'],31));
                $this->reportObject->getExcelData($xls);
                $objWriter = new Xlsx($xls);                                
                $objWriter->save('php://output'); 

                die();
            }                        
        }
        else {
            
                $this->tpl_vars = [
                   // 'head_text' => $this->status['ERROR_MESSAGE']['head'],
                    'body' => $this->status['ERROR_MESSAGE']['body'],
                    'close' => false
                ];
                $viewHelper = new ViewHelper(_INFORM_TEMPLATES_DIR_, 'message_inform',$this->tpl_vars);
                $resultHtml = $viewHelper->getRenderedTemplate();
                return [
                    'errorCode' => 1,
                    'message' => $resultHtml
                        ];
            }
                   
        //return $this->sql;
       // return $paramsArray;
        
    }
    /**
     * Подготовить SQL запрос для формирования отчета
     */
    protected function prepareSQL() {
        $this->sql = ''; 
        $ctr = $this->currentUser->getContractorId();
        if (!is_a($this->reportParams,'app\stat\report\Params')) {
            $this->reportParams = new Params($this->currentUser);        
        } 
        if (!$this->reportParams->periods->isPeriodsUnique()) {
            $this->status['ERROR'] = true;
            $this->status['ERROR_MESSAGE'] = [
                'head' => l('ERROR','messages'),
                'body' => l('ERROR_PERIODS_NOT_UNIQUE','messages')
                ];
            return;
        }
        $dc = Convert::getNumbers($this->reportParams->datasource->dataBaseTypeId);
        $this->saveReportLog();
        
        // не применять фильтры для следующик производителей и типов отчета
        $ctrs = [ 508,514 ] ;
        $dataTypes = [ 5 ];
        if (is_admin() || is_analytic()) {
            $this->user_filter_enable = false;
        } else if(in_array($ctr, $ctrs)) {
            if (in_array($dc, $dataTypes)) {
                $this->user_filter_enable = false;
            }
        }
        if ($this->user_filter_enable) {
            $sql = 'SELECT z2.*,(z2."Year" * 12 + z2."Month") AS "Hash" FROM ( SELECT 
            y."Month", y."Year" FROM (
            SELECT '.$dc.' AS "DataType" FROM DUAL) dt,
           (SELECT DISTINCT p."Year",p."Month" FROM TBLPRODUCTION p
            WHERE p."Year" >= 2009 ) y
            MINUS SELECT i."Month", i."Year"
            FROM TBLINPUTFORM i, TBLCONTRACTOR c WHERE
            c."Id" = '. $ctr .' AND i."Year" >= 2009 AND c."Id" = i."ContractorId" AND i."DataBaseTypeId" = '.$dc.') z2
            ORDER BY z2."Year",z2."Month"';
            $filter = getDb()->querySelect($sql);
            $list_of_intervals = $this->reportParams->periods->getPeriodsArray('intervals');
            $this->user_filter = array_filter($filter, function ($element) use ($list_of_intervals) {
                foreach ($list_of_intervals as $interval) {
                    if ($element['Hash'] >= $interval['start'] && $element['Hash'] <= $interval['end']) {
                        return true;
                    }
                }
               return false; 
            });
        }
        
        
    }
    protected function constructReport() {
       // return $this->sql;
        
        return getDb()->querySelect($this->sql);
    }

    protected function combineSql(array $array)
    {
        $select = key_exists('select', $array) ? $array['select'] : [];
        $from = key_exists('from', $array) ? $array['from'] : [];
        $where = key_exists('where', $array) ? $array['where'] : [];
        $order = key_exists('order', $array) ? $array['order'] : [];
        $group = key_exists('group', $array) ? $array['group'] : [];
        $having = key_exists('having', $array) ? $array['having'] : [];
        return  SimpleSQLConstructor::generateSimpleSQLQuery($select,
                  $from, $where,$order,
                  $group,$having);
    }
    /**
     * Преобразует массив для вывода колонки для вывода
     * @param array $data
     * @return array
     */
    protected function arraySync(array $data)
    {
        $regions = false;
        $reg_idx = array_search('Region', $this->other_columns);
        if ( $reg_idx !== false) {
            array_splice($this->other_columns, $reg_idx,1);
            $regions = true;
        }
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
        unset ($data);
        
        if ($col_count > 0) {
            $cur_column = $this->other_columns[$idx];
            $a_length = count($aResult);
            $newArray[] = $aResult[0];
            unset ($aResult[0]);
            for ($i =1;$i<$a_length;$i++)
            {
                foreach ($newArray[0] as $j => $elem) 
                {
                    $val = $elem[$cur_column];
                    foreach ($aResult[$i] as $key => $elem1) {
                        if ($elem1[$cur_column] == $val) {
                            $newArray[$i][$j] = $elem1;
                            unset ($aResult[$i][$key]);
                            break;
                        }
                    }
                }
            }
            if ($regions) {
                // группировка по регионам
            //    array_multisort($newArray[0],SORT_ASC, 'Region');
                $regions_list = array();
                foreach ($newArray[0] as $el) {                    
                    if (!in_array($el['Region'],$regions_list)) {                        
                        array_push($regions_list, $el['Region']);
                    }
                }
                $resArray = array();
                $ar_idx = 0;
                foreach ($regions_list as $reg) {
                    foreach ($newArray[0] as $j1 => $elem1) {
                        if ($elem1['Region'] == $reg) {
                            for ($i = 0;$i<$a_length;$i++)
                            {
                                $resArray[$i][$ar_idx] = $newArray[$i][$j1];
                            }
                            unset($newArray[0][$j1]);
                            unset($newArray[1][$j1]);
                            unset($newArray[2][$j1]);
                            $ar_idx++;
                        }
                        
                    }
                //    reset ($newArray[0]);
                }
                
            //    foreach ($aResult[0] as $) 
                
                return $resArray;
            } else {
                return $newArray;
            }
        }        
                
        
        return $aResult; 
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
    /**
     * Возвращает значения hash месяцев не включаемых в отчет в виде массива
     * @return array 
     */
    protected function getFilterHashesArray() {
        $aResult = [];
        if (empty($this->user_filter)) {
            return $aResult;
        }
        foreach ($this->user_filter as $user_filter) {
            array_push($aResult, ( $user_filter['Hash']));
        }        
        return $aResult;
    }
    protected function saveReportLog () {
        $userId = (int) $this->currentUser->getId();
        $datasource = $this->reportParams->datasource->id;
        $reportName = $this->name;
        $sql = 'INSERT INTO "TBLREPORTSLOG" ("UserId","DataSourceTypeId","ReportType","Date","Id") VALUES ('.
               $userId.','.$datasource.',\''.$reportName.'\',SYSDATE,"SEQ_TBLREPORTSLOG".NEXTVAL)';
        getDb()->dbQuery($sql);
    }

    
}