<?php

namespace app\stat\report;

use app\stat\Tools;
use app\stat\DatePeriod;
use app\stat\model\Classifier;
use app\stat\model\Datasource;
use app\stat\model\Currency;
use app\stat\db\QuerySelectBuilder;
use app\stat\model\Country;
use app\stat\model\Region;
use app\stat\Period;
use app\stat\Sessions;
use app\models\user\LoginUser;
use app\stat\report\reports\RootReport;

/**
 * Description of Params
 *
 * @author kotov
 */
class Params 
{
    /**
     * Объект классификатора
     * @var Classifier|Classifier[]
     */
    public $classifier;
    /**
     * Объект классификатора для полного отчета
     * @var Classifier
     */
    public $fullReportClassifier; 
    /**
     * @var Datasource Источник данных
     */
    public $datasource;
    /**
     * @var DatePeriod периоды
     */
    public $periods;            
    /** @var Currency валюта */
    public $currency;
    /** @var int размерность */
    public $multiplier;
    
    
    /**
     *
     * @var string параметры для отчета  сохраненные в формате JSON)
     */
    public $paramsJson;
    public $params;
    /**
     * Выбранный отчет
     * @var string
     */
    public $selectedReport;
    
    
    /**
     * Выбранные страны
     * @var array
     */
    public $countries;
    public $countriesStr;
    /**
     * Выбранные регионы
     * @var array
     */
    public $regions;
    public $regionsStr;
    /**
     * Значение флажка объединения регионов в один (on/off)
     * @var string
     */
    public $allRegionsTogether;
    
    
    /**
     *
     * @var array Доступные типы отчетов 
     */
    public $typeReportList = [];    
    /**
     * Значения параметров отчета по умолчанию
     * @var array
     */
    public $defaultValues = array ( 
        'classifier_id' => 42,
        'full_classifier_id' => 42,
        'datasource_id' => 1,
        'currency' => 1,
        'present' => true,
        'multiplier' => 1000,
        'all_regions_together' => 'off',
        'selected_report' => 'manufacturers'
        );
    
    /**
     * Флажок, указывает, что выбраны все страны
     * @var boolean
     */
    public $allCountries = false;
    /** @var array Индивидуальные параметры отчетов */
    public $reportList;
    /**
     *
     * @var RootReport
     */
    public $selectedReportObject;
    
    
    
    public function __construct(LoginUser $user) {
        
        // начальная инициализация
        $startMonth = 1;
        $monthParams = Period::getDefaultEndMonthAndYear();
        $firstYear = $monthParams['year'];
        $secondYear = $firstYear -1;
        $countries = array();
        $regions = array();
        $classifierId = 0;
        $fullReportClassifierId = 0;
        $datasourceId = 0;
        $periods = '';
        $multiplier = 0;
        $reportList = null;
        $selectedReport = '';
        
        $this->typeReportList = Container::getReportsList($user);
        $this->defaultValues['periods'] = '{"periods_list" : [{"start_period" : {"month" :'.$startMonth.',"year":'.$firstYear.'},"end_period" : {"month" :'.$monthParams['month'].',"year":'.$firstYear.'} },
            {"start_period" : {"month" :'.$startMonth.',"year":'.$secondYear.'},"end_period" : {"month" :'.$monthParams['month'].',"year":'.$secondYear.'} }  ] }';
        $this->paramsJson = Sessions::getReportParams();
        if ($this->paramsJson) {
            $this->params = json_decode($this->paramsJson,true);
            extract($this->params);
        } else {
            $this->paramsJson = '{}';
        }
        if (!$countries) {
            $this->allCountries = true;
            $countriesBuilder = new QuerySelectBuilder();
            $countriesBuilder->select = ['Id'];
            $countriesBuilder->from =  ['VW_COUNTRIESWITHDATA'];
            $countries = implode(',',Tools::getValuesFromArray(getDb()->getRows($countriesBuilder),'Id'));
        }
            $this->countries = Country::getFilteredRows($countries,array(['Id'],['Name']),'Id');
            $this->countriesStr = $countries ? $countries : '';
            $this->regions = $regions ? Region::getFilteredRows($regions,array(['Id'],['Name']),'Id') : array();
            $this->regionsStr = $regions ? $regions : '';
            $classifier = !empty($classifier_id) ? $classifier_id : $this->defaultValues['classifier_id'] ;
            $fullReportClassifierId = !empty($full_classifier_id) ? $full_classifier_id : $this->defaultValues['full_classifier_id'] ;
            if (is_array($classifier)) {
                if (count($classifier) > 1) {
                    foreach($classifier as $id) {
                        $this->classifier[] = new Classifier($id);
                    }
                } else {
                    $this->classifier = new Classifier($classifier[0]);
                }
            } else {
                $this->classifier = new Classifier($classifier);
            }
            $this->fullReportClassifier = new Classifier($fullReportClassifierId);
            $datasourceId = !empty($datasource_id) ? $datasource_id : (
                    (is_russian() || is_admin() || is_analytic()) ?
                        $this->defaultValues['datasource_id'] : 5 );
            $this->datasource = new Datasource($datasourceId);
            $periods = !empty($periods) ? $periods : $this->defaultValues['periods'] ;
            $this->periods = new DatePeriod($periods);
            $this->multiplier = !empty($multiplier) ? $multiplier : $this->defaultValues['multiplier'];
            $this->reportList = !empty($report_list) ? $report_list : null;
            $this->allRegionsTogether = isset($all_regions_together) ? $all_regions_together : $this->defaultValues['all_regions_together'];
            $this->selectedReport = !empty($selected_report) ? $selected_report : $this->defaultValues['selected_report'];                                    
            foreach ($this->typeReportList as $key => $value) {
                if ($this->typeReportList[$key]['alias'] == $this->selectedReport) {
                    $this->typeReportList[$key]['selected'] = true;
                    $this->selectedReportObject = $this->typeReportList[$key]['object'];
                }
            }
            // индивидуальные настройки
            if ($this->datasource->id === 5) {
                $this->defaultValues['individual_settings']['russian'] = false;
                $this->defaultValues['individual_settings']['foreign'] = true;
            }
    }
}
