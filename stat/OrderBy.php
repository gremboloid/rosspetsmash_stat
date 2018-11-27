<?php

namespace app\stat;

/**
 * Класс для формирования поля для сортировки
 *
 * @author kotov
 */
class OrderBy {
    protected $sortOrder;
    protected $sortField;
    protected $realFieldName;
    
    public function __construct($field = 'Name',$orderBy = 'ASC') {
        $this->sortField = $field;
        $this->sortOrder = $orderBy;
        switch ($field)
        {
            case 'Period':
                $this->realFieldName = 'PeriodHash';
             //   $this->sort_order= $this->reverse_sort($order_by);
                break;
            case 'StrDate':
                $this->realFieldName = 'Date';
                break;
            default :
                $this->realFieldName = $this->sortField;                
                break;
        }
        
    }
    protected function reverseSort($orderBy='ASC')
    {
        return $orderBy == 'ASC' ? 'DESC' : 'ASC';
    }
    public function getAssociativeArray()
    {
        $aResult = array();
        $aResult[0]['name'] = $this->sortField;
        $aResult[0]['sort_col'] = $this->realFieldName;
        $aResult[0]['sort'] = $this->sortOrder;
        return $aResult;
    }
    public function getOrderBy()
    {
        return $this->sortOrder;
    }
    public function getSortTable()
    {
        return $this->sortField;
    }
    public function getRealSort()
    {
        return $this->realFieldName;
    }            
}
