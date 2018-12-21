<?php

use app\stat\db\OracleDb;
// Функции "обертки"

/**
 *  Получить текст из языкового файла
 * @param string $string Строковый маркер (ключ ассоциативного массива) соответствующий тексту
 * @param string|null $module Подключаемый модуль с файлом перевода
 * @return string Строка с переводом
 */
function l($string,$module=null)
{
	return Yii::$app->languageModule->getTranslation($string,$module);
}
/**
 * Проверка на наличие административных прав у пользователя
 * @return bool
 */
function is_admin() {
    $user = Yii::$app->user;
    if ($user->isGuest) {
        return false;
    }
    return $user->getIdentity()->isAdmin();
}
/**
 * Проверка, является лм пользователь аналитиком
 * @return bool
 */
function is_analytic() {
    $user = Yii::$app->user;
    if ($user->isGuest) {
        return false;
    }
    return $user->getIdentity()->isAnalytic();
}
/**
 * Проверка, вошел ли пользователь под гостевой учетной пользователь 
 * @return bool
 */
function is_demo() {
    $user = Yii::$app->user;
    if ($user->isGuest) {
        return false;
    }
    return $user->getIdentity()->isDemo();
}
/**
 * Входит ли организация пользователя в ассоциацию
 * @return bool
 */
function is_rosspetsmash() {
    $user = Yii::$app->user;
    if ($user->isGuest) {
        return false;
    }
    return $user->getIdentity()->isRosspetsmash();
}
/**
 * Проверка, является лм пользователь аналитиком
 * @return bool
 */
function is_russian() {
    $user = Yii::$app->user;
    if ($user->isGuest) {
        return false;
    }
    return $user->getIdentity()->isRussian();
}
function get_role() {
    $user = Yii::$app->user;
    if ($user->isGuest) {
        return false;
    }
    return $user->getIdentity()->getRole();    
}

/**
 * 
 * @return DataBindable
 */
function getDb() {
    return OracleDb::getInstance();    
}
/**
 * Преобразование первого символа в верхний регистр в многобайтной кодировке
 * @param string $str входная строка
 * @return string результирующая строка
 */
function rs_mb_ucfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);    
}

/**
 * Преобразование первого символа в нижний регистр в многобайтной кодировке
 * @param string $str входная строка
 * @return string результирующая строка
 */
function rs_mb_lcfirst($str) {
    $fc = mb_strtolower(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);    
}

function is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}