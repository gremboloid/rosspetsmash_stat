<?php

namespace app\stat;

/**
 * Description of DatePeriod
 *
 * @author kotov
 */
class DatePeriod {
    
    protected $minYear=2009;
    protected $maxYear;
    protected $periodStep = 0;
    
    protected $defaultStartMonth;
    protected $defaultEndMontrh;
    protected $defaultStartYear;
    protected $defaultEndYear;
    /**
     * @var array
     */
    protected $periods=array();
    
/**
 * Конструктор инстанцирует объект, загружая в него периоду из JSON строки
 * @param string $JSONString строка в формате JSON содержащая список периодов и параметры
 */
    public function __construct($JSONString=null) {
        if (!$JSONString) {
            return;
        }
        if (is_array($JSONString)) {
            $JSONString = json_encode($JSONString);
        }
        $obj = is_object($JSONString) ? $JSONString : json_decode($JSONString);
        if (!is_object($obj)) {
            return;
        }
        $this->maxYear = date('Y') + 1;

        foreach ($obj->periods_list as $value) {
            $startDate = new \DateTime();
            $endDate = new \DateTime();
            $startDate->setDate($value->start_period->year, $value->start_period->month, 1);
            $endDate->setDate($value->end_period->year,$value->end_period->month,1); 
            $period = new Period($startDate, $endDate);
            $this->addPeriod($period);            
        }
        if (property_exists($obj,'period_step')) {
            $this->periodStep = $obj->period_step;
        }
        // инициализация значений "по умолчанию"
        $defParams = Period::getDefaultEndMonthAndYear();
        $this->defaultStartMonth = 1;
        $this->defaultEndMontrh = $defParams['month'];
    }
    
    /**
     * Добавление нового периода
     * @param \Rosagromash\Period $period Добавляемый период
     */
    public function addPeriod(Period $period)
    {
        $this->periods[]=$period;
    }
    
    
    /**
     * Создание хэша периода
     * @param DateTime $date фильтрация данных   
     * @return int
     */
    public static function generateUniqueValue(\DateTime $date)
    {
        return $date->format('Y') * 12 + $date->format('n'); 
    }
        /**
     * Получить список периодов для HTML шаблона
     * @return array
     */
    public function getPeriodsList() {
        $resArray = array();
        $resArray['periods_list'] = array();
        $count = $this->getPeriodsCount();
        for ($i=0;$i<$count;$i++)
        {            
            $resArray['periods_list'][$i]['start_period'] = array ('month' => $this->periods[$i]->getStartMonth(),
                'year' => $this->periods[$i]->getStartYear());
            $resArray['periods_list'][$i]['end_period'] = array ('month' => $this->periods[$i]->getEndMonth(),
                'year' => $this->periods[$i]->getEndYear());
        }
        return $resArray;
    }
    
    /**
     * Вернуть количество периодов (без учета шага периода)
     * @return int
     */
    public function getPeriodsCount()
    {
        return count($this->periods);
    }
    /**
     * Вернуть количество периодов
     * @return int
     */
    public function getRealPeriodsCount()
    {
        return count($this->getPeriodsHashes());
    }
    /**
    * Получить параметры периодов в виде JSON строки
    * @return string
    */
    public function getJSONString()
    {
        $periodParams = Period::getDefaultEndMonthAndYear();
        $count = $this->getPeriodsCount();
        $jString = '{"default_params": {"start_period": {"month":1,"year":'.$periodParams['year'].
                    '}, "end_period": {"month":'.$periodParams['month'].
                                        ',"year":'.$periodParams['year'].'} }';
        $jString.=',';
        $jString.= '"periods_list":[';
        for ($i=0;$i<$count;$i++)
        {
            $jString.= '{"start_period": {"month":'.intval($this->periods[$i]->getStartMonth()).
                                        ',"year":'.$this->periods[$i]->getStartYear().
                    '}, "end_period": {"month":'.intval($this->periods[$i]->getEndMonth()).
                                        ',"year":'.$this->periods[$i]->getEndYear().'} }';
            if ($i != ($count-1)) {
                $jString.=',';
            }
                
        }
        $jString .= ']}';
        return $jString;
    }
    /**
     * Получить шаг периода
     * @return int
     */
    public function getPeriodStep() {
        return $this->periodStep;
    }
    /**
    * Вернуть доступные опции для выбора шага периода. 
    * @param type $monthAmount количество месяцев в периоде
    * @param type $periodStep выбранное значение
    * @return string
    */
    public static function getStepPeriodOptions($monthAmount,$periodStep = 0)
    {
        $options = '';
        $filter = array();
        if ($monthAmount % 12 != 0 || $monthAmount == 12) {
               $filter[] = 12;
            }
        if ($monthAmount % 6 != 0 || $monthAmount == 6) {
           $filter[] = 6;
        }
        if ($monthAmount % 3 != 0 || $monthAmount == 3) {
           $filter[] = 3;
        }
        if ($monthAmount == 1) {
           $filter[] = 1; 
        }
        $steps_list = l('STEP_ARRAY','report');
        
        foreach ($steps_list as $key => $step) 
        {
            if (!in_array($key, $filter)) {
                $options.= '<option value="'.$key.'"'.($key == $periodStep ? ' selected=""' :'').'>'.$step.'</option>';
            }
        }
        return $options;                
    }
    /**
     * Получить период по его индексу
     * @param int $idx
     * @return Period
     */
    public function getPeriod($idx) {
        if (key_exists($idx, $this->periods)) {
            return $this->periods[$idx];
        }
    }
    /**
     * Вернуть массив доступных для выбора годов
     * @return array
     */
    public function getYearsList() {
        $aResult = array();
        for ($p = $this->minYear;$p<=$this->maxYear;$p++)
        {
           $aResult[] = $p;
        }
        return $aResult;
        
    }
    /**
     * Вернуть параметры периода для использования их в SQL-выражениее
     * @param \Rosagromash\Period $period объект периода
     * @return string
     */
    public static function getPeriodParamsForSQL(Period $period)
    {
        $aResult = array();
        $aResult['periodStart'] = self::generateUniqueValue($period->getStartDate());
        $aResult['periodEnd'] = self::generateUniqueValue($period->getEndDate());
        $aResult['periodHash'] = $aResult['periodStart'] . $aResult['periodEnd'];
        return $aResult;
    }
        /**
     * Вернуть параметры всех периодов для использования их в SQL-выражениее
     * @return type
     */
    public function getAllPeriodsForSQL()
    {
        $aResult = array();
        if ($this->getPeriodsCount() > 1) 
        {
            foreach ($this->periods as $period) 
            {
                $aResult[] = $this->getPeriodParamsForSQL($period);
            }
        }
        else {
            if (in_array($this->periodStep,[1,3,6,12])) {
                $from = self::generateUniqueValue($this->periods[0]->getStartDate());
                $to = self::generateUniqueValue($this->periods[0]->getEndDate());
                for ($i = $from; $i<=$to;$i+=$this->periodStep)
                {
                    $start = $i;
                    $end = $i + $this->periodStep -1;
                    $hash = $start.$end;
                    $tmpArray = [ 'periodStart' => $start ,
                                   'periodEnd' => $end,
                                   'periodHash' => $hash ];
                    $aResult[] = $tmpArray;
                }
            }
            else {
                $aResult[0] = $this->getPeriodParamsForSQL($this->periods[0]);
            }
            return $aResult;
        }
        return $aResult;
    }
    /**
     * Вернуть SQL-выражение для выбора периодов
     * @return string SQL выражение
     */
    public function getSQLString()
    {
        $periods = $this->getAllPeriodsForSQL();
        if (count($periods) > 0) {
            $sResult = ' ( ';
            $length = count($periods);
            $idx = 0;
            foreach ($periods as $period)
            {
                $sResult .= 'SELECT ' . $period['periodStart'] . ' AS "PeriodStart", '.
                        $period['periodEnd'] . ' AS "PeriodEnd", '.
                        $period['periodHash'] . ' AS "PeriodHash", '.
                        $idx . ' AS "PeriodOrder" FROM DUAL';
                $idx++;
                if ($idx < $length) {
                    $sResult.= ' UNION ';
                }
            }
            $sResult .= ' ) "periods"';
            return $sResult;
        }
    }
    /**
     * Получить хэш значений периодов в виде строки
     * @param type $type значение которое вернется ('start' - начало периода, 'end' - конец,'hash' - интервал)
     * @param type $separator разделитель значений
     * @return string
     */
    public function getPeriodsString($type='hash',$separator=',')
    {
        if (count($this->periods) > 0 && in_array($type, ['start','end','hash'])) {
            $periodsArray = $this->getPeriodsArray($type);
            return implode($separator, $periodsArray);
        }
        return '';
       
    }
     /**
     * Получить массив значений периодов
     * @param string $type значение которое вернется ('start' - хэш начало периода, 'end' - хэш конеца,'hash' - хэш интервала,
     * 'startyear' - начальный год','endyear' - конечный год')
     * 'startmonth' - начальный месяц','endmonth' - конечный месяц'
     * 'intervals' - массив интервалов вида [ 'start'=> startHash,'end' => 'endHash' ])
     * @return array
     */
    public function getPeriodsArray($type='hash')
    {
        $aResult = array();
        if (!in_array($type, ['start','end','hash','startyear','endyear','startmonth','endmonth','intervals'])) {
            return $aResult;
        }
        if ($this->getPeriodsCount() > 1 || ($this->periodStep == 0 && $this->getPeriodsCount() == 1)) {
            foreach ($this->periods as $period)
            {
                switch ($type)
                {
                    case 'start':
                        $aResult[] = self::generateUniqueValue($period->getStartDate());
                        break;
                    case 'end':
                        $aResult[] = self::generateUniqueValue($period->getEndDate());
                        break;
                    case 'hash':
                        $aResult[] = self::generateUniqueValue($period->getStartDate()).
                            self::generateUniqueValue($period->getEndDate());
                        break;
                    case 'startmonth':
                        $aResult[] = $period->getStartMonth();
                        break;
                    case 'endmonth':
                        $aResult[] = $period->getEndMonth();
                        break;
                    case 'startyear':
                        $aResult[] = $period->getStartYear();
                        break;
                    case 'endyear':
                        $aResult[] = $period->getEndYear();
                        break;
                    case 'intervals':
                        $aResult[] = [
                            'start' => DatePeriod::generateUniqueValue($period->getStartDate()),
                            'end' => DatePeriod::generateUniqueValue($period->getEndDate())
                                    ];
                            break;
                }
            }            
        }
        elseif (in_array($this->periodStep,[1,3,6,12]) && $this->getPeriodsCount() == 1) {
                $from = DatePeriod::generateUniqueValue($this->periods[0]->getStartDate());
                $to = DatePeriod::generateUniqueValue($this->periods[0]->getEndDate());
                if ($type == 'intervals') {
                    return [['start' => $from,'end' => $to]];
                }
                for ($i = $from; $i<=$to;$i+=$this->periodStep)
                {
                    $start = $i;
                    $end = $i + $this->periodStep -1;
                    //$startmonth = 
                    $startyear = floor( ($start - 1) / 12);
                    $endyear = floor( ($end - 1) / 12);
                    $startmonth = $start - ($startyear*12);
                    $endmonth = $end - ($endyear*12);
                    $hash = $start.$end;                    
                    $aResult[] = $$type;                    
                }
            }
        return $aResult;
        
    }
        /**
     * Получить массив значений хэша периодов
     * @return array
     */
    public function getPeriodsHashes()
    {
        $periodsCount = $this->getPeriodsCount();
        $aResult = array();
        if ($periodsCount > 1 || ($this->periodStep == 0 && $periodsCount == 1)) {
            
            foreach ($this->periods as $period) 
            {
                $periodParams = $this->getPeriodParamsForSQL($period);
                array_push($aResult, (float) $periodParams['periodHash']);
            }
        }
        if (in_array($this->periodStep,[1,3,6,12]) && $periodsCount == 1) {
            $from = DatePeriod::generateUniqueValue($this->periods[0]->getStartDate());
            $to = DatePeriod::generateUniqueValue($this->periods[0]->getEndDate());
            for ($i = $from; $i<=$to;$i+=$this->periodStep)
            {
                $start = $i;
                $end = $i + $this->periodStep -1;
                $hash = $start.$end;                    
                array_push($aResult, (float) $hash);                  
            }
            
        }
        return $aResult;
    }
    /**
     * Возвращает true если периоды с одинаковым временным интервалом
     * @return boolean
     */
    public function periodEqual()
    {
        $hashes = $this->getPeriodsHashes();
        if (count($hashes) <= 1) {
            return false;
        }
        foreach ($hashes as $value)
        {
            $check = (int) ($value / 100000) - $value % 100000;
            if (!isset($length)) {
                 $length = $check;
            }
            elseif ($length != $check) {
                return false;
            }
        }
        return true;
    }
    /**
     * Проверяет на уникальность выбранных периодов
     * @return bool
     */
    public function isPeriodsUnique() 
    {
        return !Tools::isDublicateInArray($this->getPeriodsHashes());
    }

    /**
     * Возвращает true если периоды попарно с одинаковым временным интервалом
     * @return boolean
     */  
    public function pairPeriodEqual() {
        $hashes = $this->getPeriodsHashes();
        $periods_count = count($hashes);
        if (($periods_count % 2) != 0 ) {
            return false;
        }
        $idx = 0;
        foreach ($hashes as $value)
        {
            if ($idx == 2) {
                unset($length);
                $idx = 0;
            }
            $check = (int) ($value / 100000) - $value % 100000;
            if (!isset($length)) {
                 $length = $check;
            }
            elseif ($length != $check) {
                return false;
            }
            $idx++;
        }
        return true;
    }
        /**
     * Возвращает true если все значения года для эквивалентных периодов различны
     * @return boolean
     */
    public function isDifferentYear() 
    {
        
        if (!$this->pairPeriodEqual()) {
            return false;
        }
        $start = $this->getPeriodsArray('startyear');
        $end = $this->getPeriodsArray('endyear');
        $start_month = $this->getPeriodsArray('startmonth');
        $count = count ($start);
        for ($idx = 0;$idx < $count;$idx+=2) {
            if ($start[$idx] != $end[$idx]) {
                return false;
            }
            else {
                if ($start[$idx] == $start[$idx+1] || $start_month[$idx] !=  $start_month[$idx + 1]) {
                    return false;
                }          
            }
        }
        return true;
    }       
}
