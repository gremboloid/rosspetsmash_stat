<?php
namespace app\stat\db;

interface DataBindable {
    
    public function getRow(QuerySelectBuilder $builder);    
    public function getRows(QuerySelectBuilder $builder);    
    public function getRowsCount(QuerySelectBuilder $builder);    
    public function dbQuery(string $sql);    
    
/**
 * Выполнить SELECT и вернуть полученные строки
 */  
    public function querySelect(string $sql,$bindingValues=array(),bool $indexById = false);
    
    /**
     * Вставка данных в таблицу
     */
    public function insert($table,$data);
    /**
     * Обновление строк в таблице 
     */
    public function update($table,$data,$where);
    /**
     * Удаление строк из таблицы
     */
    public function delete($table,$where);
    /**
    * Получить список полей таблицы
    * @param string $tableName
    * @return array
    */
    public function getFieldsFromTable(string $tableName);
    
    public function getNextAndPrevId($id,$sql,$bindingArray=null);
}
