<?php

namespace app\stat\helpers;


use app\stat\services\ClassifierService;
use app\stat\model\PerformanceAttr;
use app\stat\model\PerfAttrRequest;
use app\stat\model\ValuesPerfAttr;
use app\stat\model\UnitOfMeasure;
use app\stat\ViewHelper;
use app\stat\Tools;
/**
 * Description of TechCharacteristicFormHelper
 *
 * @author kotov
 */
class TechCharacteristicFormHelper
{
    /**
     * Id модели
     * @var type 
     */
    protected $modelId;
    /**
     * Id модели из запроса
     * @var type 
     */
    protected $requestId;
    /**
     * Кэш с массивом характеристик
     * @var array
     */
    protected $cachCharacteristic = array();
    /**
     * Текст заголовка
     * @var string
     */
    protected $headText; 
    /**
     * Список доступных технических характеристик
     * @var array
     */
    protected $perfirmanceAttrList = array();


    public function __construct($classifierId,$modelId = null,$requestId = null)
    {
        $this->headText = l('PERFOMANCE_HEAD');
        $this->modelId = $modelId;
        $classifierList = Tools::getValuesFromArray(ClassifierService::getClassifierParents($classifierId,'Id'),'Id');
        $this->perfirmanceAttrList = PerformanceAttr::getRowsArray(array(),['"ClassifierId" IN ('. implode(',', $classifierList). ') ']);
        $this->requestId = $requestId;
    }
/**
 * 
 * @return array
 * @throws \DomainException
 */
    public function getCharacteristic()
    {
        if (!empty($this->cachCharacteristic)) {
            return $this->cachCharacteristic;
        }
        $tableValues = [];
        if (count($this->perfirmanceAttrList) === 0) {
            throw new \DomainException('Characteristic not found');
        }
        foreach ($this->perfirmanceAttrList as $performanceAttr) {
            $measure = new UnitOfMeasure($performanceAttr['UnitOfMeasureId']);
            $measureText = $measure->shortName;
            $value = '';
            if ($this->modelId) {
                $value = $this->getCharacteristicValue($performanceAttr['Id']);
                }
            if ($this->requestId) {
                $value = $this->getCharacteristicValueByRequest($performanceAttr['Id']);
            }        
            $row = [ 
                0 => [   
                    'type' => 'string',
                     'size' => 150,
                    'text' => $performanceAttr['Name']
                    ],
                1 => [
                    'name' => 'Char'.$performanceAttr['Id'],
                    'value' => $value,
                    'type' => 'text'                    
                ],
                2 => [
                    'text' => $measureText,
                    'type' => 'string',
                    'size' => 150  
                    ],
            ];
            $tableValues[] = [
                    'list' => $row,
                    'required' => (bool) $performanceAttr['Necessarily']
            ];                                
        }        
        $this->cachCharacteristic = [
            'block_head_text' => l('PERFOMANCE_HEAD'),
            'elements_list' => [
                'techCharacteristic' => [
                    'type' => 'table-form',
                    'table_headers' => $this->headText,
                    'table_vals' => $tableValues
                ]
            ]
        ];
        return $this->cachCharacteristic;
    }
       
    protected function getCharacteristicValue(int $performanceAttr) 
    {
        $modelAttr = ValuesPerfAttr::getRowsArray(['Value'],
                    [
                        ['param' => 'ModelId', 'staticNumber' => $this->modelId ],
                        ['param' => 'PerformanceAttrId', 'staticNumber' => $performanceAttr ]
                    ]  
                );
         if (count($modelAttr) > 0) {
             return $modelAttr[0]['Value'];
         }
         return '';
    }
    protected function getCharacteristicValueByRequest(int $performanceAttr)
    {
        $modelAttr = PerfAttrRequest::getRowsArray(['Value'],
               [
                   ['param' => 'ModelId', 'staticNumber' => $this->requestId ],
                   ['param' => 'PerformanceAttrId', 'staticNumber' => $performanceAttr ]
               ]  
           ); 
        if (count($modelAttr) > 0) {
             return $modelAttr[0]['Value'];
        }
        return '';
    }
    public function getHtmlForm() 
    {
        $tplVars = $this->getCharacteristic();
        $helper = new ViewHelper(_BLOCKS_TEMPLATES_DIR_.'forms/','dopblock', $tplVars);
        return $helper->getRenderedTemplate();
    }
}
