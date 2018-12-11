<?php

namespace app\stat\services;


use app\stat\db\QuerySelectBuilder;
use app\stat\model\Contractor;
use app\stat\model\InputForm;
use app\stat\model\DatabaseType;
use app\stat\model\User;
use app\stat\Validate;
use app\stat\db\SimpleSQLConstructor;
use app\stat\DatePeriod as RsDatePeriod;
/**
 * Description of InputFormService
 *
 * @author kotov
 */
class InputFormService
{
    
    /**
     * фильтрация данных
     * @var array
     */
    protected $filter = array();
    /**
     * элементы, не включаемые в фильтрацию
     * @var array
     */
    protected $exFilter = array();
    /**
     * поля для сортировки
     * @var array
     */
    protected $sortBy = array();
      
    /**
     * 
     * @param array $filter фильтрация данных
     * @param array $sortBy поля для сортировки
     * @param array $exFilter элементы, не включаемые в фильтрацию
     */    
    public function __construct($filter=array(),$sortBy = array(),$exFilter = array()) {
        $this->filter = $filter;
        $this->sortBy = $sortBy;
        $this->exFilter = $exFilter;
    }
    public static function getFormTypes(Contractor $contractor) {
        if (!is_admin()) {
            $arr = &DatabaseType::$availableTypes;
            if (!$contractor->isImporter && is_russian())  {                
                array_splice($arr, array_search(5, $arr), 1);
            }
            if (!is_russian()) {
                array_splice($arr, array_search(1, $arr), 1);
                array_splice($arr, array_search(2, $arr), 1);
                array_splice($arr, array_search(4, $arr), 1);
            }
        }
        $ids = '('. implode(',', DatabaseType::$availableTypes).')';
        $filter = array(['param'=> 'Id','operation' => 'IN','staticNumber'=> $ids]);
        return DatabaseType::getRowsArray(array(), $filter);
    }

    /**
     * 
     * @param int $pageNumber номер страницы
     * @param type $rowCount число строк
     * @param bool $count вывод числа форм
     * @return array
     */
    public function getFormsList($pageNumber = null,$rowCount=null,$count = false) {
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->select = [
            ['ti','Id'],
            ['ti','Month'], 
            ['ti','Year'],
            ['ti','DataBaseTypeId'],
            ['name' => 'Fio','textValue' => '("tu"."SurName" || \' \' || "tu"."Name" || \' \' || "tu"."PatronymicName")'],
            ['name' => 'StrDate', 'textValue' => 'TO_CHAR ("ti"."Date",\'DD.MM.YYYY\')'],
            ['ti','Date'],
            ['ti','ContractorId'],
            ['textValue' =>'"ti"."Year"*12+"ti"."Month" AS "PeriodHash"'],
            ['td','Name','DatabaseTypeName'],
            ['tc','Name','ContractorName'],
            ['ti','Actuality']
        ];
        $queryBuilder->from = [
            [InputForm::getTableName() ,'ti'],
            [DatabaseType::getTableName(), 'td'],
            [User::getTableName(),'tu'],
            [Contractor::getTableName(),'tc']
        ];
        $queryBuilder->where = [
            ['param' => 'ti.ContractorId','staticValue'=>'tc.Id'],
            ['param' => 'ti.DataBaseTypeId','staticValue' => 'td.Id'],
            ['param' => 'ti.UserId','staticValue' => 'tu.Id']  
        ];        
        if (!empty($this->filter)) {
            $tempSql = $queryBuilder->generateQuery();
            $queryBuilder->select = [
                ['res','Id'],
                ['res','Month'], 
                ['res','Year'],
                ['res','StrDate'],
                ['res','Date'],
                ['res','Fio'],
                ['res','PeriodHash'],
                ['res','DatabaseTypeName'],
                ['res','ContractorName'],
                ['res','Actuality']
            ];
            $queryBuilder->from = [
                ['name' => 'res','textValue' => '(' .$tempSql.')']
            ];
            $queryBuilder->where = $this->setFilters();            
        }        
        if (!$pageNumber) {
            if (!$count) {                
                return getDb()->getRows($queryBuilder);
            }
            $queryBuilder->orderBy = $this->sortBy;
            $formsCount = getDb()->getRowsCount($queryBuilder);
            return $formsCount;
        }
        $queryBuilder->orderBy = $this->sortBy;
        if (Validate::isInt($pageNumber) && Validate::isInt($rowCount)) {
            $sql = SimpleSQLConstructor::generatePageFilter($queryBuilder->generateQuery(),$rowCount,$pageNumber);
            return getDb()->querySelect($sql);
        }
        return array();        
    }
    /**
     * Вернуть количество форм
     * @return int
     */
    public function getFormsListCount() {
        return (int) $this->getFormsList(null,null,true);
    }

    /**
     * 
     * @param string|array $sortParams
     */
    public function setSortParams($sortParams) {
        $this->sortBy = $sortParams;
    }
    /**
     * 
     * @param array $filter 
     * @param string $tableAlias псевдоним таблицы, к которой применяется фильтр
     * @return array кляузв where
     */
    protected function setFilters() {
        $where = array();
        foreach ($this->filter as $filterKey => $filterValue ) {
            if (!empty ($filterValue) && !in_array($filterKey, $this->exFilter)) {
                switch ($filterKey) {
                    case 'contractor':                        
                        array_push($where, ['param' => 'res.ContractorId','staticValue' => $filterValue]);
                    break;
                    case 'formType':
                        array_push($where, ['param' => 'res.DataBaseTypeId','staticValue' => $filterValue]);
                    break;
                    case 'dateFilter':
                        $startDate = new \DateTime();
                        $startDate->setDate( $this->filter['dateFilter']['start']['year'],
                                                $this->filter['dateFilter']['start']['month'], 
                                                1 );
                        $startHash = RsDatePeriod::generateUniqueValue($startDate);
                        if (!key_exists('end', $this->filter[$filterKey])) {
                            array_push($where, ['param' => 'res.PeriodHash','staticValue' => $startHash]);
                        }
                        else {
                            $endDate = new \DateTime();
                            $endDate->setDate( $this->filter['dateFilter']['end']['year'], 
                                                $this->filter['dateFilter']['end']['month'], 
                                                1);
                            $endHash = RsDatePeriod::generateUniqueValue($endDate);
                            array_push($where, '"res"."PeriodHash" BETWEEN '.$startHash.' AND '.$endHash);
                        }
                    break;        
                }
            }
        }        
        return $where;
    }
    
}
