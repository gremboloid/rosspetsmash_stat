<?php

namespace app\stat;


use app\stat\db\QuerySelectBuilder;
use app\stat\model\Parameters;
/**
 * Конфигурация приложения
 *
 * @author kotov
 */
class Configuration 
{
    
    protected static $_CONF;
    
/**
 * Загрузить конфиг в массив $_CONF
 */
    public static function loadConfiguration() {
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->select = 'Name, Value';
        $queryBuilder->from = 'TBLPARAMETERS';
        $result = getDb()->getRows($queryBuilder);
        if ($result) {
            foreach ($result as $row ) {
                self::$_CONF[$row['Name']] = $row['Value'];
            }
        }
    }
    public static function get($key,$defaultValue=false)
    {
        if (isset(self::$_CONF[$key])) {
            return self::$_CONF[$key];
        }
        return $defaultValue;        
    }
    public static function getConfigururation() {
        return self::$_CONF;
    }
    /** 
     * Сохранение параметров портала
     * @param string $data сериализованная строка get запроса с настройками
     * для сохранения
     */
    public static function saveConfigurationData($data) {
        parse_str($data,$form_elements);
        if (count($form_elements) > 0 ) {
            foreach ($form_elements as $key => $value) {
                if (Validate::isTableOrIdentifier($key)) {
                    $confId = Parameters::getFieldByValue('Name', $key);
                    if ($confId) {
                        $parameters = new Parameters($confId);
                        //$val = $parameters->get('value');
                        if ($parameters->value !== $value) {
                            $parameters->set('value',$value);
                            $parameters->saveObject();
                        }
                    } else {
                        $obj = ['name' => $key,
                              'value' => $value
                        ];
                        $parameters = Parameters::getInstance($obj);
                        $parameters->setFieldsToUpdate(['Value']);
                        $parameters->saveObject();
                    }
                    unset ($parameters);
                }
            }
        }      
    }
}
