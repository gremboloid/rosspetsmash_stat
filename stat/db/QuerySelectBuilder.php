<?php
namespace app\stat\db;

use \app\stat\exceptions\DefaultException;
use app\stat\db\SimpleSQLConstructor;

/**
 * Description of QueryBuilder
 *
 * @author kotov
 */
class QuerySelectBuilder {
    /** @var bool возвращать только уникальные строки */
    public $distinct;
    /** @var string|array данные для поля SELECT */
    public $select;
        /** @var string|array */
    public $from;
        /** @var string|array */
    public $where;
    
    public $groupBy;
    
    public $having;
    
    public $orderBy;
    /** @var boolean Добавить поле номер строки */
    public $rowNum = false;
    
    /**
     * Инициализация конструктора
     * @param array $assocArray
     */
    public function __construct(array $assocArray = array()) {        
        foreach ($assocArray as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

        public function generateQuery() {
        if (empty($this->from)) {
            throw new DefaultException('WrongQueryParams','Неверные параметры запроса');
        }
        $sql = SimpleSQLConstructor::generateClauseSelect($this->select, $this->distinct) . ' ' .
            SimpleSQLConstructor::generateClauseFrom($this->from);
        if (!empty($this->where)) {
            $sql.= ' ' . SimpleSQLConstructor::generateClauseWhere($this->where);
        }
        if (!empty($this->groupBy)) {
            $sql.= ' ' . SimpleSQLConstructor::generateClauseGroupBy($this->groupBy);
        }
        if (!empty($this->having)) {
            $sql.= ' ' . SimpleSQLConstructor::generateClauseHaving($this->having);
        }
        if (!empty($this->orderBy)) {
            $sql.= ' ' . SimpleSQLConstructor::generateClauseOrderBy($this->orderBy);
        }
        if ($this->rowNum) {
            $sql = 'SELECT ROWNUM AS "Number", "r".* FROM ('.$sql.') "r"';
        }
        return $sql;
    }
    
    public function getnerateQueryForCount() {
        $sql = 'SELECT COUNT(*) AS "Count" FROM ('.$this->generateQuery().') "tbl1"';
        return $sql;
    }
}
