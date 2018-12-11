<?php
namespace app\stat\db;

use yii\db\Connection;
use \app\stat\Validate;
use app\stat\Tools;
use \Yii;
use yii\base\Exception;

/**
 * Description of OracleDb
 *
 * @author kotov
 */
class OracleDb implements DataBindable
{
    /**
     *
     * @var Connection
     */
    public $_link;
    
    /**
     * Объект синглтон
     * @var DataBindable
     */
    protected static $_instance;
    
    public function __construct() {
        $this->_link = Yii::$app->db;
        $this->_link->open();
    }

    /**     
     * @return DataBindable
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    public function delete($table, $where) {
        //$this->result = false;
        $sql = 'DELETE FROM '.Tools::addDoubleQuotes($table). (($where) ? ' '.SimpleSQLConstructor::generateClauseWhere($where) :'');
        try {     
            $this->dbQuery($sql);
            return true;
        } catch (Exception $ex) {
                return false;
        }              
    }
    /**
     * Получить первой илил единственной строки
     * @param type $fields
     * @param type $from
     * @param type $where
     * @param bool $group - групповая фунция
     * @return array
     */
    public function getRow(QuerySelectBuilder $builder,$group = false) {
        $separator = SimpleSQLConstructor::generateClauseWhere($builder->where) ? ' AND ' : ' WHERE ';
        $rowNum = $group ? '' : $separator . 'ROWNUM = 1';
        $sql = $builder->generateQuery() . $rowNum;
        $bindingArray = SimpleSQLConstructor::generateBindingArray($builder->where);
        $aResult = $this->querySelect($sql,$bindingArray);
        if (count($aResult) >= 1) {
            return $aResult[0];
        } else {
            return array();
        }
        
    }

    public function getRows(QuerySelectBuilder $builder) {
        $sql = $builder->generateQuery();
        $bindingArray = SimpleSQLConstructor::generateBindingArray($builder->where);
        return $this->querySelect($sql, $bindingArray);
    }
    public function getRowsCount(QuerySelectBuilder $builder) {
        $sql = $builder->getnerateQueryForCount();
        $irResult = $this->querySelect($sql);
        return $irResult[0]['Count'];
    }
    /**
     * Вставка данных в таблицу
     * @param string $table имя таблицы
     * @param array $data ассоциативный массив с параметрами добавляемых данных
     * @param type $isId вернуть идентификатор вставленной записи
     * @return bool
     */
    public function insert($table, $data,$isId = true) 
    {
        $keys = array();
        $vals = array();
        foreach ($data as $row_data) {
            $keys[] = Tools::addDoubleQuotes($row_data['field']);
            $vals[] = Tools::wrapDataByType($row_data['value'],$row_data['type']);
        }
        $keys_stringified = implode(', ', $keys);
        $values_stringified = implode(', ', $vals);
        $sql = 'INSERT INTO '.Tools::addDoubleQuotes($table).' ('.$keys_stringified.') VALUES ('. $values_stringified.')';
        
        if ($isId) {
            $sql.= ' RETURN "Id" INTO :newID';           
            $state = $this->_link->createCommand($sql)->bindParam(':newID', $id, \PDO::PARAM_INT|\PDO::PARAM_INPUT_OUTPUT,10);
            try { 
                $state->execute();
                return $id;

            } catch (Exception $ex) {
             //   $this->_link->rollBack();
                return false;
            }            
        } else {
            $state = $this->_link->createCommand($sql);
            try {     
                $state->execute();
                return true;

            } catch (Exception $ex) {       
                return false;
            }
            
        }                
    }

    public function querySelect(string $sql,$bindingValues=array(),bool $indexById = false) {    
        $state = $this->_link->createCommand($sql)->bindValues($bindingValues);
        $queryResult = $state->query(); 
        if ($indexById) { 
            $queryResult->setFetchMode(\PDO::FETCH_ASSOC);
            while ($val = $queryResult->read()) {
                $arr[$val['Id']] = $val;
            }
           // $arr = $queryResult();
        }
        else {
            $arr = $queryResult->readAll();
        }
        return $arr;
        
    }

    public function update($table, $data, $where) {
        if (!$data) {
            return true;
        }
        $sql = 'UPDATE '. Tools::addDoubleQuotes($table). ' SET ';
        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }
            $sql.= Tools::addDoubleQuotes($value['field']).' = '. Tools::wrapDataByType($value['value'],$value['type']).',';            
        }
        $sql = rtrim($sql, ',');
        if ($where) {
            $sql .= ' '. SimpleSQLConstructor::generateClauseWhere($where);
        }
        return $this->dbQuery($sql);
        
        
    }
    /**
        * Получить список полей таблицы
        * @param string $tableName
        * @return array
    */
    public function getFieldsFromTable(string $tableName): array {
        $aResult = [];
        if (!Validate::isTableOrIdentifier($tableName)) {
            return $aResult;
        }
        $sql = 'select column_name, data_type from user_tab_columns where table_name=:name ';
        $state = $this->_link->createCommand($sql)->bindValue(':name', $tableName);
        $queryResult = $state->query();  
        $queryResult->next();
        while ($val = $queryResult->current()) {
            array_push($aResult, [ 'column_name' => $val['COLUMN_NAME'],
                                   'type' => $val['DATA_TYPE']
                                ]);
            $queryResult->next();
        }
        return $aResult;
        //return $state->queryAll();
    }

    public function dbQuery(string $sql) {
        $state = $this->_link->createCommand($sql);
        return $state->query();
        
    }
    /**
     * Получить id следующей или предыдузей строки за указанной
     * @param type $id
     * @param type $sql
     * @param type $bindingArray
     */

    public function getNextAndPrevId($id,$sql,$bindingArray=null) 
    {
        $state = $this->_link->createCommand($sql);
        $state->execute($bindingArray);
        $queryResult = $state->query();
        $queryResult->setFetchMode(\PDO::FETCH_ASSOC);
        while ($val = $queryResult->read()) {
            if ($val['Id'] != $id) {
                $curId = $val['Id'];
            } else {
                if (!empty($curId)) {
                    $aResult['prev'] = $curId;
                }
                $nextval = true;
                continue;
            }
            if ($nextval) {
                $aResult['next'] = $val['Id'];
                break;
            }
        }
        return $aResult;
    }
}
