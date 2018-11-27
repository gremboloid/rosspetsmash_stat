<?php

namespace app\stat;


/**
 * Description of Period
 *
 * @author kotov
 */
class Period {
    
    /**
     * @var \DateTime
     */
    protected $startDate;
    /**
     * @var \DateTime
     */
    protected $endDate;
    /**
     * 
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function __construct(\DateTime $startDate, \DateTime $endDate) 
    {
        $this->startDate=$startDate;
        $this->endDate=$endDate;
    }
    public function getStartMonth()
    {
        return $this->startDate->format('m');
    }
    public function getEndMonth()
    {
        return $this->endDate->format('m');
    }
    public function getStartYear()
    {
        return $this->startDate->format('Y');
    }
    public function getEndYear()
    {
        return $this->endDate->format('Y');
    }
    /**
     * Количесто месяцев в периоде
     * @return int
     */
    public function getCountOfMonth()
    {
        $startMonth = $this->startDate->format('n');
        $endMonth = $this->endDate->format('n');
        $startYear = $this->startDate->format('Y');
        $endYear = $this->endDate->format('Y');
        if ($startYear == $endYear) {
            return ($endMonth - $startMonth) + 1;
        }
        if ($endYear > $startYear) {
            return 0;
        }
        $fullYear = ($endYear-$startYear-1) * 12;
        return $endMonth + $fullYear + 13 - $startMonth ;

    }
    /**
     * 
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    /**
     * 
     * @return DateTime
     */    
    public function getEndDate()
    {
        return $this->endDate;
    }
    /**
     * Получить числовое значение конечного месяца  по умолчанию
     * @return int
     */
        public static function getDefaultEndMonth() 
    {
        date("m") == 1 ? $endMonth = date("m") : $endMonth = date("m") - 1;
        if (date('d')<10) {
            $endMonth ==1 ? $endMonth = 12 : $endMonth--;
        }
        return intval($endMonth);
    }
    
}
