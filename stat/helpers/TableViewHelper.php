<?php

namespace app\stat\helpers;


use app\stat\OrderBy;
use app\stat\providers\TableDataProvideInterface;
use app\stat\exceptions\DefaultException;

/**
 * Description of TableViewHelper
 *
 * @author kotov
 */
class TableViewHelper 
{
    /**
     * @var array Массив с заголовками таблиц
     */
    public $tableColumnHeaders = array();
    /**
     * @var array табличные данные
     */
    protected $tableData = array();
    /**     
     * @var array Массив с видимыми полями таблицы
     */
    protected $tableFilter = array();
    /**
     * @var int номер страницы
     */    
    protected $rowNumber;
    /**     
     * @var int общее количество строк, возвращаемых запросом
     */
    protected $rowsCount;
    /**
     *
     * @var int число строк на странице
     */
    protected $pageRows;


    /**     
     * @var OrderBy
     */
    protected $order;
    /**
     * @var array колонки для которых будет работать сортировка
     */   
    protected $sortedColumns;
    /**
     *
     * @var TableDataProvideInterface
     */
    protected $provider;
    protected $defaultSortField = 'Name';
    protected $defaultSortType = 'ASC';
    
    
    public function __construct($setup = array()) {
        if (!empty($setup)) {
            foreach ($setup as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->$key = $val;
                }
            }
        }
    }
    public function setPageRows( int $count) {
        $this->pageRows = $count;
    }
    
    public function setSort(OrderBy $orderBy) {
        $this->order = $orderBy;
    }

    public function setRowsCount() {
        if (!isset($this->provider)) {
            throw new DefaultException('DataProviderNotConfig','Не сконфигурирован провайдер табличных данных');
        }
        $this->rowsCount = $this->provider->getCount();
    }
    public function setRowNumber(int $number) {
        $this->rowNumber = $number;
    }

    public function getRowsInPage() {
        return $this->pageRows;
    }
    public function getSortFields() {
        if ($this->order) {
            return [
                    [                
                        'name' => $this->order->getRealSort(),
                        'sort' => $this->order->getOrderBy()
                    ]
            ];
        }
        return [];        
    }
    public function getSortTable() {
        if ($this->order) {
            return $this->order->getSortTable();
        }
    }
    public function getOrderBy() {
        if ($this->order) {
            return $this->order->getOrderBy();
        }
    }
    public function orderExists() {
        if ($this->order) {
            return true;
        } else {
            return false;
        }
    }
    public function getOrderPapams () {
        if ($this->orderExists()) {
            return $this->order->getAssociativeArray();
        }
        return false;
    }

    public function setTableProvider(TableDataProvideInterface $tableProvider) {
        $this->provider = $tableProvider;
    }
    public function getRowsCount() {
        if (!isset($this->provider)) {
            throw new DefaultException('DataProviderNotConfig','Не сконфигурирован провайдер табличных данных');
        }
        if (empty($this->rowsCount)) {
            $this->setRowsCount();
        }
        return $this->rowsCount;
    }
    public function getTableData() {
        $months = l('MONTHS','words');
        if (!isset($this->provider) || (!isset($this->rowNumber)) || (!isset($this->rowsCount))) {
            throw new DefaultException('DataProviderNotConfig', 'Не сконфигурирован провайдер табличных данных');
        }
        $tableData = $this->provider->getTableData($this->rowNumber,$this->rowsCount );
        foreach ($tableData as $key => $val) {
            if (key_exists('Month', $tableData[$key])) {
                $monthNumber = (int) $tableData[$key]['Month'];
                $tableData[$key]['Period'] = $months[$monthNumber] . ' ' . $tableData[$key]['Year'];
            }
            
        }
        return $tableData;
    }
}
