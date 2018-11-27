<?php

namespace app\stat\report\out;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
/**
 *
 * @author kotov
 */
abstract class ReportCreator implements IReport
{   
    protected $required = array ('Name','Columns','OtherColumns','HeadColumn','DataUnits','Classifier','ClassifierFull','Source','Multiplier','Currency','HeadArray','SourceId','ClassifierId','ClassifierFullId','RealPeriodsCount','ReportName','NoNulls','UserFilterEnable','UserFilter');
    /** @var string Название отчета */
    protected $name;
    /** @var array Массив всех столбцов */
    protected $columns;
    /** @var array Массив всех колонок без измерений и заголовка */ 
    protected $othercolumns;
     /** @var string Столбец, данные которого будут взяты для создания заголовка */
    protected $headcolumn;
    /** @var type Массив с данными для заголовка таблицы*/ 
    protected $headarray = array();
    /** @var array Массив колонок с измерениями */
    protected $dataunits;
    /** @var string Имя классификатора */
    protected $classifier;
    /** @var string Имя классификатора для поного отчета */
    protected $classifierfull;
    /** @var int Id классификатора */    
    protected $classifierid;
    /** @var int Id классификатора для полного отчета */    
    protected $classifierfullid;
    /** @var string Имя источника данных */
    protected $source;
    /** @var string ID источника данных */
    protected $sourceid;
    /** @var string Единицы измерения для валюты */
    protected $multiplier;
    /** @var string Валюта */
    protected $currency;
    /** @var array Данные отчета */
    protected $data;
    /** @var type Число периодов */
    protected $realperiodscount;
    /**
     * @var string alias отчета
     */
    protected $reportname;
    protected $nonulls;
    protected $rowscount;
    /** @var boolean флаг проверки заполненности данными */   
    protected $ready;
    protected $userfilterenable;
    protected $userfilter;
    /**
     * Переменные для HTML-шаблона
     * @var type 
     */
    protected $tpl_vars = array();
    
    public function __construct(array $data,$columnName="Period")
    {  
        $this->ready = true;
        foreach ($this->required as $field)
        {
            if (!key_exists($field, $data)) {
                $this->ready = false;
                return;
            }
            else {
                $key = strtolower($field);
                $this->$key = $data[$field];
            }
        }
        $this->data = $data['Data'];
        $this->rowscount = count($this->data[0]);
    }
    
    
   
    
    public function getWebTableData(){}
    public function getExcelData(Spreadsheet $xls ){}
    
    /**
     * преобразовать процентные значения согласно правилам
     * @param type $value
     * @return string преобразованное значение
     */
    protected  function percentConvert ($value,$xls=false,$zero=false) {
      //  return $value;
        if ($zero) {
            return '–';
        }
         if ($value == 0 || $value == 100) {
             return 0;
         }
         if (!intval($value)) {
             return '–';
         }
         $prc = $value / 100;
         if ($prc > 10) {
             return '> 10 р.';
         }
         if ($prc > 3 && $prc <= 10) {
             return 'в '. number_format($prc,1,',','') .' р.';
         }
         
         $val = $value - 100;
         if ($val > 0) {
             $result = $xls ? $val : '+'.number_format($val,1,',','');
             return $result;
         } else {
             return $xls ? $val : number_format($val,1,',','');
         }
         
        return $xls ? $result : number_format($result,1,',','');
    }
    
}
