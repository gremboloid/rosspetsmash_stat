<?php

namespace app\stat\model;

use app\stat\services\ClassifierService;
/**
 * Description of OperatorsConfig
 *
 * @author kotov
 */
class OperatorsConfig extends ObjectModel
{
    protected $name;
    protected $value;
    
    const PARAMS_NAME = 'OperationSettings';
    
    protected static $table = "TBLPARAMETERS";
      
    public function saveModelObject($data, $form_elements = null, $return_json = true)
    {   
        parse_str($data['frm_data'],$elements);
        $form_elements['name'] = self::PARAMS_NAME;
        $form_elements['value'] = json_encode($elements);        
    //    die($form_elements);
        return parent::saveModelObject($data, $form_elements, $return_json);
    }
    public static function getCurrentConfig() 
    {
        $params = self::getRows([
            [self::$table,'Id'],
            [self::$table,'Name'],
            [self::$table,'Value']
        ],null,[['param' => 'Name','staticText' => self::PARAMS_NAME]]);
        if (!empty($params)) {
            $elements = json_decode($params[0]['Value'],true);
            if (isset($elements['element'])) {
                return [
                    'id' => $params[0]['Id'],
                    'element' => $elements['element']
                        ];
            }
        } 
        return false;
        
    }
    public static function getConfigForClassifier(int $classifierId) 
    {
        $rootClassifier =  ClassifierService::getParentClassifierIdByLevel($classifierId);
        if (!$rootClassifier) {
            return false;
        }
        $config = self::getCurrentConfig();
        if (!$config) {
            return false;
        }
        foreach ($config['element'] as $key => $element) {
            if ((int) $key === $rootClassifier ) {
                return $element;
            }
        }
        return false;
    }
}
