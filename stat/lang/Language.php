<?php

namespace app\stat\lang;

use \Yii;
use yii\base\Component;

/**
 * Description of Language
 *
 * @author kotov
 */
class Language extends Component
{
    /**
     * Язык сайта
     * @var string
     */
    public $isoCode = 'ru';
    /**
     * Имя модуля по умолчанию
     * @var string
     */
    public $defaultModuleName = 'default';
    /**
     * Путь к каталогу с файлами текстов для сайта
     * @var string
     */
    public $folder = '@app/translations';
    
    /** @var array Кеш с языковыми данными */
    
    protected $cache = array();
    
    
    /**
     * 
     * @param string $str
     * @param string|null $module
     * @return mixed
     */
    public function getTranslation(string $str,string $module = null) {
        if (!$module) {
            $module = $this->defaultModuleName;
        }
        $nameOfArray = '_' . strtoupper($module);
        $keyOfArray = strtoupper($str);
        $$nameOfArray = $this->getTranslationCache($module); // название переменной является именем модуля
        if (empty($$nameOfArray)) {
            $file_core = $this->getPathToFile($module);
            if (!file_exists($file_core)) {
                return $keyOfArray;
            }
            include_once $file_core;
            if (!empty($$nameOfArray) && is_array($$nameOfArray)) {
                $this->setTranslationCache($module, $$nameOfArray);
            } else {
                $$nameOfArray = array();
            }            
        }
        if (key_exists($keyOfArray, $$nameOfArray)) {
            return $$nameOfArray[$keyOfArray];
        }
        return $keyOfArray;        
    }
    
    /**
     * Вернуть кэш с языковыми данными для соответствующего модуля
     * @param string $moduleName
     * @return array
     */
    protected function getTranslationCache(string $moduleName) {
        return $this->cache[$moduleName] ?? array();
    }
    /**
     * Добавить данные из модуля в кэш
     * @param string $moduleName
     * @param array $data
     * @return boolean
     */
    protected function setTranslationCache (string $moduleName , array $data) {
        if (count($data) > 0) {
            $this->cache[$moduleName] = $data;
            return true;
        }
        return false;
    }

    /**
     * Вернуть путь к файлу с языковыми данными
     * @param string $moduleName
     * @return string
     */
    protected function getPathToFile(string $moduleName) {
        return Yii::getAlias($this->folder) . '/' . $this->isoCode . '/' . $moduleName .'.php';
    }

}