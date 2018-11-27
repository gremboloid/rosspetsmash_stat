<?php
namespace app\stat\db;

use app\stat\Tools;
use app\stat\Configuration;
/**
 * Содержит методы для генерации строки запроса SQL из входящих данных
 *
 * @author kotov
 */
class SimpleSQLConstructor 
{
    /**
     * Создать блок для выражения SELECT
     * @param string|attay $fields поля
     * @param bool $distinct возвращать уникальные строки
     * @return type
     */
    public static function generateClauseSelect($fields,$distinct=false)
    {
        
        $sResult = 'SELECT ';
        if ($distinct) {
            $sResult.= 'DISTINCT ';
        }
        if (empty($fields)) {
            return $sResult .'*';
        }
        if (is_string($fields)) {
            return $sResult . Tools::getQuotedString($fields);
        }
        if (is_string($fields[0])) {
		return $sResult. self::getStringFromSelectArray($fields);
	}
	foreach ($fields as $value) {
		$sResult.= self::getStringFromSelectArray($value).',';
	}
	return rtrim($sResult,',');        
    }
    /**
     * Создать блок для выражения FROM
     * @param type $tables поля (строка или массив)
     * @return type
     */
    public static function generateClauseFrom($tables)
    {      
       $sResult = 'FROM ';
       if (is_string($tables)) {
                 return $sResult . Tools::getQuotedString($tables);
        }
        if (is_string($tables[0])) {
            return $sResult. self::getStringFromClauseFromArray($tables);
	}
	foreach ($tables as $value) {
            $sResult.= self::getStringFromClauseFromArray($value).',';
	}
        return rtrim ($sResult,',');
    }
    /**
     * Создать блок для выражения WHERE
     * @param array $where поля, содержащие условие
     * @return string
     */
     public static function generateClauseWhere($where)
    {
         $sResult = '';
        if (!is_array($where)) {
            return '';
        }
        $index = 0;
        $countArray = count($where);
        if ($countArray == 0) {
            return '';
        }
        foreach ($where as $value) {
            $index++;
            $con = $index > 1 ? ' AND ' : 'WHERE ';
            if (is_string($value)) {
                $sResult .= $con . $value;
                continue;
            }
            $op = isset($value['operation']) ? $value['operation'] : '=';
            if (key_exists('bindingValue', $value)) {
                $sResult .= $con. Tools::getQuotedString($value['param'],'.') . $op .':'. $value['param'];
            }
            elseif (key_exists('staticValue', $value)) { 
                $sResult .= $con . Tools::getQuotedString($value['param'],'.') .' '. $op.' ' . Tools::getQuotedString($value['staticValue'],'.');
            }
            elseif (key_exists('staticText', $value)) { 
                $sResult .= $con . Tools::getQuotedString($value['param'],'.') .' ' . $op .' ' . Tools::addQuotes($value['staticText'],'.');
            }
            elseif (key_exists('staticNumber', $value) ) {
                $sResult .= $con . Tools::getQuotedString($value['param'],'.').' ' . $op .' '. $value['staticNumber'];
            }
            elseif (key_exists('pureValue', $value) ) { 
                $sResult .= $con . Tools::getQuotedString($value['param'],'.').' ' . $op .' '. $value['pureValue'];
            }
            else {
                continue;
            }
        }
        return $sResult;
    }
    /**
     * Создать блок для выражения ORDER BY
     * @param string|array $orderBy
     * @return string
     */
    public static function generateClauseOrderBy($orderBy='')
    {        
        if (!$orderBy) {
            return '';
        }
        $sql = ' ORDER BY ';
        if (!is_array($orderBy)) {
            return $sql . Tools::addDoubleQuotes($orderBy);
        }

        foreach ($orderBy as $value)
        {
            if (key_exists('name', $value)) {
                $sql.= Tools::getQuotedString($value['name'],'.');
                if (key_exists('sort', $value)) {
                    $sql.= ' ' . $value['sort'];
                }
                $sql.=',';
            }
            if (key_exists('textValue', $value)) {
                $sql.= $value['textValue'].',';
            }
            
        }
        return rtrim($sql,',');
    }
        /**
     * Создать блок для выражения GROUP BY
     * @param type $groupBy
     * @return string
     */
    public static function generateClauseGroupBy($groupBy)
    {    
        if (!$groupBy) {
            return '';
        }
        $sql = ' GROUP BY ';
        if (!is_array($groupBy)) {
            return $sql . $groupBy;
        }

        foreach ($groupBy as $value)
        {
            $sql.= Tools::getQuotedString($value,'.').',';
        }
        return rtrim($sql,',');
    }
    /**
     * TODO
     * @param type $having
     * @return string
     */
     public static function generateClauseHaving($having) 
     {
         return '';
     }
/**
 * вспомогательная функция для конструирования строки из массива SELECT
 * @param type $array
 * @return string
 */
    protected static function getStringFromSelectArray($array)
    {
        if (is_array($array)) {
            switch (count($array))
            {
                case 1:
                    if (key_exists('textValue', $array)) {
                        return $array['textValue'];
                    }
                    return Tools::addDoubleQuotes($array[0]);
                case 2:
                    if (key_exists('textValue', $array)) {
                        $sResult = $array['textValue'];
                        if (key_exists('name', $array)) {
                            $sResult .= ' AS ' . Tools::addDoubleQuotes($array['name']);
                        }
                        return $sResult;
                    }
                    return Tools::addDoubleQuotes($array[0]).'.'.
                            Tools::addDoubleQuotes($array[1]);
                case 3:
                    return Tools::addDoubleQuotes($array[0]).'.'.
                        Tools::addDoubleQuotes($array[1]).' AS '.
                        Tools::addDoubleQuotes($array[2]);
                default:
                    return '';
            }
        }
        return '';
    }
    /**
 * вспомогательная функция для конструирования строки из массива FROM
 * @param type $array
 * @return string
 */
    protected static function getStringFromClauseFromArray($array)
    {
	switch (count($array))
	{
            case 1:
                if (key_exists('textValue', $array)) {
                    return $array['textValue'];
                }
                return Tools::addDoubleQuotes($array[0]);
            case 2:
                if (key_exists('textValue', $array)) {
                    $sResult = '('.$array['textValue'].')';
                    if (key_exists('name', $array)) {
                        $sResult .= ' ' . Tools::addDoubleQuotes($array['name']);
                    }
                return $sResult;
                }
                return Tools::addDoubleQuotes($array[0]).' '.
                       Tools::addDoubleQuotes($array[1]);
            default:
		return '';
	}
    }
    /**
     * 
     * @param type $fields
     * @param type $from
     * @param type $where выражение для фильтра
     * @param type $orderBy выражение для сортировки
     * @param type $diatinct выбрать уникальные записи
     * @return type SQL выражение
     */
    public static function generateSimpleSQLQuery($fields,$from,$where=array(),$orderBy=array(),$groupBy=array(),$having=array(),$diatinct=false)
    {
       $sql = self::generateClauseSelect($fields,$diatinct) . ' ' . self::generateClauseFrom($from) ;
               if (is_array($where) && count($where) != 0) {
                $sql.= ' ' . self::generateClauseWhere($where);
               }

               if (is_array($groupBy) && count($groupBy) != 0) {
                 $sql.= ' '. self::generateClauseGroupBy($groupBy);
               }
               if (is_array($having) && count($having) != 0) {
                 $sql.= ' '. self::generateClauseHaving($having);
               }
               if (is_array($orderBy) && count($orderBy) != 0) {
                    $sql.= ' '. self::generateClauseOrderBy($orderBy);
               }
        return $sql;
    }
    /**
     * Формирование подставляемых значений для массива WHERE
     * @param type $where
     * @return type
     */
    public static function generateBindingArray($where)
    {
        $aResult = array();
        if (!is_array($where)) {
            return array();
        }
        foreach ($where as $value)
        {
            if (!is_array($value)) {
                continue;
            }
           if (key_exists('bindingValue', $value) && key_exists('param', $value)) {
               $aResult[$value['param']] = $value['bindingValue'];
           }   
        }
        return $aResult;    
    }
    public static function getLimitedRowsQuery($sql,$rowsCount) {
        $sql = 'SELECT * FROM
                (SELECT ROWNUM, s1.* FROM ( '.$sql. ' ) s1  ) s2
                 WHERE s2."ROWNUM" <=' .$rowsCount;
        return $sql;
    }

    /**
     * Создание SQL запроса для постраничного вывода результатов
     * @param string $sql входящий запрос
     * @param int $rowCount число строк
     * @param int $pageNumber номер страницы
     * @param int $rows_in_page @param int $rows_in_page число строк на странице (по умолчанию берется из настроек портала)
     * @return string sql выражение    
     */
    public static function generatePageFilter ($sql,$rowCount,$pageNumber,$rows_in_page=null)
    {
        $rows = $rows_in_page ? $rows_in_page : Configuration::get('RowCount');
        $pageCount = ceil ($rowCount/$rows);
        if ($pageCount == 1) {
             $sql = 'SELECT * FROM
                (SELECT ROWNUM as "Number", s1.* FROM ( '.$sql. ' ) s1  ) s2';
            return $sql;
        }
        if ($pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }
        $startVal = ($pageNumber-1)*$rows + 1;
        $endVal = ($pageCount==$pageNumber) ? $rowCount : $startVal + $rows - 1;
        $sql = 'SELECT * FROM
                (SELECT ROWNUM AS "Number", s1.* FROM ( '.$sql. ' ) s1  ) s2
                 WHERE s2."Number" BETWEEN '.$startVal.' AND '.$endVal;
        return $sql;
    }
    /**
     * Формирование SQL-аыражения для списка измерений
     * @param type $list список 
     * @return string SQL выражение
     */
    public static function getStringForDimensions ($list)
    {
        $sResult = '';
        if (is_string($list)) {
            $list = explode(',', $list);           
        }
        if (count($list) > 0) {
            $idx = 0;
            foreach ($list as $elem)
            {
                if ($idx++ != 0) {
                    $sResult .= ' UNION ';
                }
                $sResult.= ' SELECT '.$elem.' AS "Id" FROM DUAL ';
            }
        }
        return $sResult;
    }
    
}