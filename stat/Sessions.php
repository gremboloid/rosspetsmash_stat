<?php

namespace app\stat;

/**
 * Description of Sessions
 *
 * @author kotov
 */
final class Sessions {
    
    protected static $init = false; // Флаг
    /**
 * устанавливает переменную сессии пользователя
 * @param array $data
*/
    public static function setVarSession($data) 
    {
        if (!self::$init)  {
            return ;
        }
        while(true)
        {
            
            list( $key, $val ) = each($data);
            if (!isset($key)) {
                break;
            }
            $_SESSION[$key] = $val;
        }
    }
    /**
     * Установить значение для переменной сессии
     * @param type $var имя переменной
     * @param type $value значение
     */
    public static function setValueToVar($var,$value)
    {  
        self::sessionInit();
        $_SESSION[$var] = $value;
    }
    
    /**
 * получает данные из переменной сессии
 * @param type $name
 * @return string
 */
    public static function getVarSession($name,$def_value = false) 
    {
        if (isset($_SESSION[$name])) {            
            return $_SESSION[$name];
        }
        else {
            return $def_value;
        }
    }
    public static function unsetVarSession($name) {
        if (key_exists($name, $_SESSION)) {
            unset ($_SESSION[$name]);
        }
    }
    /**
     * Инициализация сессии
     */
    public static function sessionInit() 
    {
        if (self::$init)  {
            return null;
        }
        if (!is_session_started()) {
           session_start();
        }
        self::$init = true;
    }

/**
 * Вернуть имя пользователя из сессии
 * @return string
 */
    public static function getUsername()
    {
        if (!self::$init)  {
            return ;
        }
        return self::getVarSession('username');
    }
/**
 * Вернуть пароль из сессии
 * @return string
 */        
    public static function getPassword()
    {
        if (!self::$init)  {
            return ;
        }
        return self::getVarSession('password');
    }
    /**
     * Вернуть параметры отчета
     * @return array
     */
    public static function getReportParams()
    {
        if (!self::$init)  {
            return  ;
        }
        return self::getVarSession('report_params');
    }
    /**
     * Получить id выбранного производителя
     */
    public static function getContractorId() {
        if (!self::$init)  {
            return  ;
        }
        return self::getVarSession('CONTRACTOR_ID');
        
    }
    /**
     * Получить id выбранного раздела классификатор
     */
    public static function getClassifierId() {
        if (!self::$init)  {
            return  ;
        }
        return self::getVarSession('CLASSIFIER_ID');        
    }
    /**
     * Получить id родительского раздела классификатор (по умолчанию возвращается корневой раздел)
     */
    public static function getParentClassifierId() {
        if (!self::$init)  {
            return  41;
        }
        return self::getVarSession('PARENT_CLASSIFIER_ID',41);
    }
    
    public static function destroySession() 
    {
        self::sessionInit();
        session_unset();
        $_SESSION = array();
        self::$init = false;
    }
    public static function isSessionInit()
    {
        return self::$init;
    }
    
}
