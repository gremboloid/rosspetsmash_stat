<?php

namespace app\stat\conf;

/**
 * Description of ViewFormsConfig
 *
 * @author kotov
 */
class ViewFormsConfig {
     // занчения по умолчанию для фильтров   
    
    /**
     * Настройки фильтра
     * @var array
     */
    protected $filterArray;
    /**
     * Параметры новой формы
     * @var array
     */
    protected $newFormParams;
    /**
     * @var boolean использовать фильтры
     */
    protected $filters = false;
    /**
     * 
     * @var boolean
     */
    protected $admin;
    
    protected $formType = 0;
    protected $startMonth = 1;
    protected $endMonth;
    protected $startYear = 2009;
    protected $endYear;
    
    /**
     * 
     * @param boolean $isAdmin
     * @param int $contractorId 
     */
    public function __construct($isAdmin = true,$contractorId = 0) {
        $this->endYear = date('Y');
        $this->endMonth = date('n');  
        $this->configurateFilter($isAdmin,$contractorId);
        $this->configurateNewFormParams();
    }
    /**
     * Настроить фильтр в соответствии с переданными параметрами
     * @param array $params
     */
    public function setupFilter(array $params) {
        if (empty($params)) {
            return;
        }
        $this->filters = true;
        if (isset($params['contractor'])) {
            $this->filterArray['contractor'] = $params['contractor'];
        }
        if (isset($params['formType'])) {
            $this->filterArray['formType'] = $params['formType'];
        }
        if (isset($params['startMonth']) && isset($params['startYear'])) {
            $this->filterArray['dateFilter']['start'] = array('month' => $params['startMonth'],$params['startYear']);
        }
        if (isset($params['endMonth']) && isset($params['endYear'])) {
            $this->filterArray['dateFilter']['end'] = array('month' => $params['endMonth'],'year' => $params['endYear']);
        }
        
        
    }
    public function getDefaultStartYear() {
        return $this->startYear;
    }
    public function getDefaultEndYear() {
        return $this->endYear;
    }
    public function getDefaultStartMonth() {
        return $this->startMonth;
    }
    public function getDefaultEndMonth() {
        return $this->endMonth;
    } 
    protected function configurateFilter ($isAdmin = true,$contractorId = 0) {
                // начальная инициальзация фильтра
        $this->filterArray = [
            'contractor' => ( $isAdmin ) ? 0 : $contractorId,
            'formType' => $this->formType,
            'dateFilter' => [ 
                'start' => [
                    'month' => 1,
                    'year' => $this->startYear
                ],
                'end' => [
                    'month' => $this->endMonth,
                    'year' => $this->endYear
                ]
            ]
        ];         
    }
    protected function configurateNewFormParams () {
        $this->newFormParams = [
            'date' => [
                'month' => $this->endMonth,
                'year' => $this->endYear,
            ]
        ];
    }
    public function getFilters() {
        if ($this->filterArray) {
            return $this->filterArray;
        }
        return array();
    }
    
}
