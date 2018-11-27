<?php

namespace app\controllers;

use app\stat\Pagination;
use app\stat\helpers\TableViewHelper;
use app\stat\Configuration;
use \yii\web\Request;
use \Yii;
use \app\stat\providers\TableDataProvideInterface;
use app\stat\OrderBy;
use app\stat\Validate;
use app\stat\html\Hidden;


/**
 * Производный класс для контроллеров выводящих табличные данные
 *
 * @author kotov
 */
abstract class TableViewController extends FrontController
{
    /**
     * Применяется в случае, если отсутствует настройка в конфиге
     */
    const DEFAULT_PAGE_ROWS = 10;        
    /**     
     * @var Pagination;
     */
    protected $pagination; 
    /**
     * @var TableViewHelper
     */
    protected $helper;
    /**
     * @var int число строк в таблицк
     */
    protected $rowsCount;
    /**
     * @var array Иассив с видимыми полями таблицы
     */
    protected $tableFilter = array();
    /**
     * @var array Иассив с элементами действия для форм
     */
    protected $actionList = array();
    /**
     * @var array колонки для которых будет работать сортировка
     */
    protected $sortedColumns = array(); 
    protected $defaultSortField = 'Name';
    protected $defaultSortType = 'ASC';
    protected $sortElements = array();
    

    /**
     * Установка параметров, пришедших из запроса
     */
    abstract protected function setParamsFromRequest(Request $request);
    /**
     * Настройка постраничной навигации
     */
    abstract protected function paginatorInit() ;
    /**
     * Настройка хелпера
     */
    abstract protected function tableConfigure();

    
    public function setParams() {
        parent::setParams();
        $this->helper = new TableViewHelper();
        $this->pagination = new Pagination();
        $this->config();
        
    }
    public function config() {        
        $request = Yii::$app->request;
        if (!$request->isGet) {
            throw new \yii\web\BadRequestHttpException();
        }
        $this->setParamsFromRequest($request);
        $this->tableConfigure();
        $this->paginatorInit();
    }
    public function initVars() {        
        parent::initVars();
        $this->tpl_vars['sort_elements'] = $this->sortElements;        
        $this->tpl_vars['table_cols_headers'] = $this->helper->tableColumnHeaders;
        $this->tpl_vars['table_filter'] = $this->tableFilter;
        $this->tpl_vars['action_list'] = $this->actionList;
        $this->tpl_vars['table_array'] = $this->helper->getTableData($this->rowsCount);
        $this->tpl_vars['pagination'] = $this->pagination->getPagination();
        $this->tpl_vars['cols_to_sort'] = $this->sortedColumns;
        if ($this->helper->orderExists()) {
            $this->tpl_vars['order_params'] = current($this->helper->getOrderPapams());
        }
        
    }
    protected function setTableDataProvider(TableDataProvideInterface $provider) {
        $this->helper->setTableProvider($provider);
    }
    protected function configSortFields(string $fieldToSort='',string $orderBy='') {
        if(!Validate::isTableOrIdentifier($fieldToSort)) {
            $fieldToSort = $this->defaultSortField;
        }
        if (!in_array($orderBy, array('ASC','DESC'))) {
            $orderBy = $this->defaultSortType;
        }
        $this->helper->setSort(new OrderBy($fieldToSort,$orderBy));
        $this->configForm();
    }
    protected function configForm() {
        $sortCol = new Hidden(['id' => 'sortColumn','name' => 'sortColumn','value'=> $this->helper->getSortTable()]);
        $sortType = new Hidden(['id' => 'sortType','name' => 'sortType','value'=> $this->helper->getOrderBy()]);
        $this->sortElements = ['column' => $sortCol->getHtml(), 'type' => $sortType->getHtml()];
    }
    

}
