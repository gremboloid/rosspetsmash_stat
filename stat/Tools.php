<?php

namespace app\stat;
/**
 * Description of Tools
 *
 * @author kotov
 */
class Tools {
/**
 * Вернуть строку, с полями (разделенными запятыми) обрамленными в  двойные кавычки
 * @param type $aString строка содержащая поля для обрамления
 * @param type $separator разделитель полей, по умолчанию ","
 * @return string возвращаемая строка
 */
    public static function getQuotedString($aString,$separator=',')
    {
        $sResult = '';
        if (!Validate::isString($aString)) {
            if (Validate::isInt($aString)) {
                return $aString;
            }
         return '';
        }
        $fields = explode($separator, $aString);
        foreach ($fields as $field) {
            $sResult .= self::addDoubleQuotes(trim($field));
            $sResult .= $separator;
        }
        return rtrim($sResult,$separator);
    }
        /**
     * Обернуть строку в двойные кавычки
     * @param type $string
     * @return type
     */
    public static function addDoubleQuotes($string,$wrap='"')
    {
        if (Validate::isInt($string)) {
            return $string;
        }
        if (trim($string)=='*')
        {
            return $string;
        }
        return self::SurroundText($string,$wrap);

    }
    public static function addQuotes($string,$validateInt = true)
    {
        if ($validateInt) {
            if (Validate::isInt($string)) {
                return $string;
            }
        }
        return self::SurroundText($string,"'");
    }
    /**
     * Обернуть текст заданными символами
     * @param string $string входная строка
     * @param string $pad символы для оборачивания текста
     * @return string
     */            
    public static function SurroundText($string,$pad)
    {
        return $pad.trim($string,$pad).$pad;
    }
    /**
     * 
     * @param array $array
     * @param type $aKey
     * @return array
     */
    public static function getValuesFromArray(array $array,$aKey)
    {
        $aResult = array();
        foreach ($array as $val)
        {
            if (is_array($val)) {
                if (key_exists($aKey, $val)) {
                    array_push($aResult, $val[$aKey]);
                }
            }
        }
        return $aResult;
    }
    /**
     * Построение дерева из ассоциативного массива
     * @param array $dataset
     * @return array
     */
    public static function mapTree($dataset) 
    {
	$tree = array();
	foreach ($dataset as $id=>&$node) 
        {
            if (!$node['PId']) {
                $tree[$id] = &$node;
            } else { 
                $dataset[$node['PId']]['childs'][$id] = &$node;
            }
	}
        return $tree;
    }
        /**
     * возвращает представление иерархического дерева в формате JSON
     * @param array $list иерархический массив заданного формата возвращаемый функцией Tools::mapTree()
     * @return string строка в формате JSON
     */
    public static function getJSONtree(array $list)
    {
        $string='[';
        foreach ($list as $data => $item) {
            $string.='{"text": "'.$item['Name'].'", "id":'.$item['Id'];
            if (key_exists('childs', $item)) {
                
                $string.= ',"children": '. self::getJSONtree($item['childs']);
            }
            else {
                $string.= ', "icon": "glyphicon glyphicon-cog"';
            }
            $string.='},';
        }
        
        return trim($string,',') . ']';
    }
    /**
     * 
     * @param type $message Текст сообщения
     * @param type $code Код ошибки 
     * @param type $json формат вывода
     * @return array|string массив или объект Json
     */
    public static function getErrorMessage($message,$code=0,$json=true,$array=false) {
        $error = [
                    'errorCode' => $code,
                    'message' => $message
                ];
        if ($json) {
            return json_encode($error);
        }
        if ($array) {
            return $error;
        }
        return $error['message'];
    }
    /**
     * 
     * @param type $messageText Текст сообщения
     * @param type $json формат вывода
     * @return array|string массив или объект Json
     */
    public static function getMessage($messageText,$json=true) {
        $message = [
            'message' => $messageText
        ];
        if ($json) {
            return json_encode($message);
        }
        return $message;
        
    }
    
/**
     * Разбиение архива по значению столбца (значение становится ключом
     * @param array архив для разбиения
     * @param string $columnName имя столбца
     * @param boolean $multi может быть несколько элементов с заданным ключом (по умолчанию)
     * @return array
     */
public static function splittingArray($array,$columnName,$multi=true)
    {
        $newArray = array();
        foreach ($array as $value)
        {
            if (key_exists($columnName, $value)) {
                $key = $value[$columnName];
                unset($value[$columnName]);
                if ($multi) {
                    $newArray[$key][] = $value;
                }
                else {
                    $newArray[$key] = $value;
                }
            }
        }
        return $newArray;
    }
/** Добавляет пробелы при выводе больших чисед
 * @param string $str
 * @return string
 */
    public static function addSpaces($str)
    {
        if (strlen($str) > 3) {
            $len =  strrpos($str, ',');
            if (!$len) {
                $len = strlen($str);
            }
            for ($i = $len-3; $i > 0 ; $i-=3)
            {
                $str = substr_replace($str, " ", $i,0);
            }
        }
        return $str;        
    }   
/** Возвращает массив, содержащий N предыдущих записей месяц/год (вида YYYY/MM)
 * @param array $inpDate
 * @param int $n
 * @return boolean|array
 */

public static function getOldDate($inpDate, $n)
{
    $myDate = explode('/', $inpDate);
    if (count ($myDate) !=2)
        return false;
    $prevYear = $myDate[0];
    $prevMounth = $myDate[1] + 0;
    for ($i = 0;$i< $n; $i++)
    {
          if ($prevMounth == 1)
          {
              $prevMounth = 12;
              $prevYear -= 1;
          }
         else {
        $prevMounth -= 1;
          }
       // $oldMonth = $prevMounth >= 10 ? $prevMounth : '0'.$prevMounth;        
        $oldDates[$i] = implode('/', array ($prevYear,$prevMounth));
     }
     return $oldDates;            
    }
   public static function wrapDataByType($data,$type='number')
    {
        $type = strtolower($type);
        switch ($type)
           {
               case 'varchar2':
               case 'nvarchar2':
               case 'clob':
                   $sResult = Tools::addQuotes($data,false);
                   break;
               case 'date':
                   $sResult = 'to_date(\'' .  $data . '\',\'DD.MM.YYYY\')';
                   break;
               default: 
                   $sResult = !($data === '') ? $data : 'NULL';
                   break;
           }
           return $sResult;        
    } 
    
/**
 * Возвращает значение строки пути (относительно хоста)
 * @param int $num индекс вложенности 
 * @return string protocol
 */
    public static function getValFromURI($num=0)
    {
        $ret = self::getFilteredUrl();
        $path_array = explode("/", trim ($ret,"/"));
        if (count ($path_array)-1 < $num) {
            return '';
        } 
        return $path_array[$num] ;
        
    }
/**
 *  Вернуть очищенное от параметров значение URI
 * @return string
 */
    public static function getFilteredUrl()
    {
        $ret = self::getUri();
        $position = strpos($ret,'?');
        return  $position ? substr($ret,0,$position) : $ret;
    
    }
     /**
  * Вернуть полную строку URI
  * @return string
  */
    public static function getUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) 
        {
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        return $_SERVER['REQUEST_URI'];
        
    }
    /**
 * Получает значение из массива $_POST / $_GET
 * Если не найдено, возвращвет значение по умолчанию 
 * @param string $key ключ
 * @param string $type тип запроса GET/POST (по умолчанию поиск в обоих массивах false)
 * @param mixed $defaultValue Значение по умолчанию
 * @return mixed значение переменный или false если не найдено
 */
    public static function getValue($key, $type = false, $defaultValue = false)
    {
        if (!is_string($key))
            return false;
        switch ($type) {
            case 'GET':
            {
                $ret = isset($_GET[$key]) ? $_GET[$key] : $defaultValue;
                break;
            }
            case 'POST':
            {
                $ret = isset($_POST[$key]) ? $_POST[$key] : $defaultValue;
                break;
            }
            default:
            {
                $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));   
                break;   
            }
        }
        if (is_string($ret))
            $ret = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret))));
        elseif (is_array($ret))
            $ret = Tools::getArrayValue($ret);
        return $ret;
    }
    /**
     * Вернуть строку GET запроса
     * @return string
     */
    public static function getRequestParamsFromUri()
    {
        $ret = self::getUri();
        $position = strpos($ret,'?');
        return $position ? substr($ret,$position, strlen($ret)) : '';
    }
    public static function cleanCache ( $cacheFileName = null ) {
        if (!$cacheFileName) {
            array_map('unlink', glob(_CACHE_DIR_ . '*.dat'));
        } else {
            unlink(_CACHE_DIR_ . $cacheFileName);
        }
    }
    public static function catString( $string, $num ) {
        if ($num < 4) {
            return $string;
        }
        return mb_substr($string, 0, ($num - 3)).'...';                
    }
    public static function generatePassword(
            $length=8,
            $chars = '023456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $first_digit = false
            ) {
        if (!is_int($length) || $length < 0) {
           return false;
        }
        $characters_length = strlen($chars) - 1;
        $string = '';
        for ($i = $length; $i > 0;$i--) {
            if ($first_digit || $i != ($length - 1 )) {
                $string.= $chars[mt_rand(0,$characters_length)];
            } else {
                $no_digit_str = preg_replace('/[\d]/', '', $chars);
                $no_digit_str_length = strlen($no_digit_str) - 1;
                $string.= $no_digit_str[mt_rand(0, $no_digit_str_length)];
            }
        }
        return $string;
    }
    /**
     * Проверяет на наличие уникальных значений в массиве
     */
    public static function isDublicateInArray( array $data ) {
        $dublicateExist = false;
        while ($val = array_pop($data)) {
            if (in_array($val, $data)) {
                $dublicateExist = true;
                break;
            }
        }
        return $dublicateExist;
    }
    // Проверить является ли элемент массива последним
    public static function hasNext($array) {
        if (is_array($array)) {
            if (next($array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    
    
}
