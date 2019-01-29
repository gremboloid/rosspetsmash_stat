<?php

namespace app\stat\helpers;

use app\stat\model\TypeData;
use app\stat\model\UnitOfMeasure;
use app\stat\ViewHelper;
/**
 * Description of ClassifierCharacteristicHelper
 *
 * @author kotov
 */
class ClassifierCharacteristicHelper
{
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
    
    protected $typeDataOptions = array();
    
    protected $unitsOfMeasureOptions = array();
    
    protected $reqOptions = array();


    public function __construct()
    {
        $this->headText = l('PERFOMANCE_TABLE_HEADERS');
    //    $this->headText[] = l('ACTIONS');
        $td_list = TypeData::getRowsArray();
        $un_list = UnitOfMeasure::getRowsArray();
        
        foreach ($td_list as $el) {
            $this->typeDataOptions[] = ['text' => $el['Name'], 'value' => $el['Id']];
        }
        foreach ($un_list as $el) {
            $this->unitsOfMeasureOptions[] = ['text' => $el['ShortName'], 'value' => $el['Id']];
        }
        $this->reqOptions = [
            ['text' => l('NO','words'), 'value' => 0],
            ['text' => l('YES','words'), 'value' => 1]
        ];
    }
    public function getCharacteristic()
    {
        if (!empty($this->cachCharacteristic)) {
            return $this->cachCharacteristic;
        }
        $row = [
            
                [
                    'name' => 'NameChar',
                    'type' => 'simple',
                    'size' => 150
                ],
                [
                    'name' => 'TypeData',
                    'type' => 'select',
                    'size' => 100,
                    'elements' => $this->typeDataOptions
                ],
                [
                    'name' => 'Restriction',
                    'type' => 'simple',
                    'size' => 150
                ],
                [
                    'name' => 'UnitOfMeasureId',
                    'type' => 'select',
                    'size' => 100,
                    'elements' => $this->unitsOfMeasureOptions
                ],
                [
                    'name' => 'Necessarily',
                    'type' => 'select',
                    'size' => 50,
                    'elements' => $this->reqOptions
                ]
            ];
            $tableValues[] = [
                    'list' => $row,
            ];
        $this->cachCharacteristic = [
                'block_head_text' => l('PERFOMANCE_HEAD'),
                'elements_list' => [
                    'techCharacteristic' => [
                        'type' => 'table-form',
                        'table_headers' => $this->headText,
                        'table_vals' => $tableValues,
                        'action_buttons' => true
                    ]
                ]
            ];
        return $this->cachCharacteristic;
    }
    public function getHtmlForm() 
    {
        $tplVars = $this->getCharacteristic();
        $helper = new ViewHelper(_BLOCKS_TEMPLATES_DIR_.'forms/','dopblock', $tplVars);
        return $helper->getRenderedTemplate();
    }
    
}
