<?php

namespace app\stat;

use app\stat\db\QuerySelectBuilder;
use app\stat\model\User;
/**
 * Description of Validate
 *
 * @author kotov
 */
class Validate {
       public static function isEmail($email)
    {
	return !empty($email) && preg_match('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+$/ui', $email);
    }
    /**
     * Проверяет является лм переменная строкой
     * @param type $string
     * @return bool
     */
    public static function isString($string)
    {
        return !empty($string) && is_string($string);
    }
    /**
     * Проверяет является лм переменная числом или строкой, содержащей число
     * @param type $string
     * @return type
     */
    public static function isInt($string)
    {
        return is_numeric($string);
    }
    /**
     * Проверяет является лм переменная корректным именем таблицы или идентификатора
     * @param type $table
     * @return boolean
     */
    public static function isTableOrIdentifier($table)
    {
		return preg_match('/^[a-zA-Z0-9_-]+$/', $table);
    }
    /**
     * Проверяет является лм переменная корректным именем модуля
     * @param type $moduleName
     * @return boolean
     */
    public static function isModuleName($moduleName)
    {
		return preg_match('/^[a-zA-Z0-9_-]+$/', $moduleName);
    }
    /** 
     * проверяет, существует ли email адрес в базе зарегистрированных пользователей
     */
    public static function emailExist($email) {
        if (!Validate::isEmail($email)) {
            return false;
        }
        $rows = User::getRows('Email',null,[['param' => 'Email','bindingValue' => $email]]);
        if (count($rows) > 0) {
            return true;
        } else {
            return false;
        }
    }
    public static function isDateFormat($var) {
        return preg_match('#^\d{1,2}\.\d{1,2}\.\d{4}$#', $var);
    }
    
}
