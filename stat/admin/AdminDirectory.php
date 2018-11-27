<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\admin;

use app\stat\Configuration;
use app\stat\Tools;
use app\stat\Convert;
use app\stat\Validate;
use app\stat\OrderBy;

/**
 * Базовый адстрактный класс всех справочников админки
 *
 * @author kotov
 */
abstract class AdminDirectory extends AdminRoot  
{
    /** @var array табличные данные */
    protected $table_data;
    /** @var array Массив с видимыми полями таблицы  */
    protected $table_filter = array();
    /** @var array Массив с заголовками таблиц */    
    protected $table_cols_headers = array();
    /** @var int количество записей в таблице */
    protected $rows_count;
    /** @var int текущая страница */
    protected $page_num; 
    /** @var int число страниц */
    protected $pages_count;
    /** @var int число строк на странице */    
    protected $page_rows;
    /** @var \Rosagromash\OrderBy объект сортировки  */
    protected $order_object;
    /** @var array колонки для которых будет работать сортировка  */
    protected $cols_to_sort = array();
    /** @var bool доступность кнопки добавления нового элементв */
    protected $button_add_active =true;
    /** @var string Название класса модели */
    protected $model_name = '';
    /**  @var \Html\Form Форма для фильтрации данных */    
    public $filter_window;
    /**  @var array Список полей для фильтрации */
    public $filter_array = array();
    public $action_list = array();
    public $hidden_inputs = array();
    /** @var \Model\ObjectModel объект справочника */
    public $directory_model;
    /** @var bool показывать панель групповых операций (TRUE) */
    public $group_operations = false;
    /** @var array конфигурация групповых операций */
    public $group_operations_conf;
    /** @var array список групповых операций  */
    public $group_operations_list = array();
    protected $defaultSortField = "Name";
    


    public function __construct() {
        $this->template_file = 'admin_directory';
        $def_rows = Configuration::get('RowCount');
        $cur_rows = Tools::getValue('rows', 'GET');
        $this->page_rows = (empty($cur_rows) || $cur_rows == $def_rows) ? $def_rows : $cur_rows;
        $page_num = Convert::getNumbers(Tools::getValue('page','GET'));
        $this->page_num = $page_num ? $page_num : 1;
        parent::__construct(); 
        
    }

    protected function setTemplateVars() {
        parent::setTemplateVars(); 
        $sortBy = array (['name' => $this->order_object->getRealSort(), 'sort' => $this->order_object->getOrderBy()]);
        $this->tpl_vars['table_data'] = $this->directory_model->getDirectory($this->filter_array,[],false, $this->page_num, $this->rows_count, $this->page_rows,$sortBy);
        $this->tpl_vars['pagination'] = $this->getPagination();
        $this->tpl_vars['table_cols_headers'] = $this->table_cols_headers;
        $this->tpl_vars['model_name'] = $this->model_name;
        $this->tpl_vars['table_filter'] = $this->table_filter;
        $this->tpl_vars['rows_count_options'] = [10,20,50,100];
        $this->tpl_vars['rows_count'] = (int)$this->page_rows; 
        $this->tpl_vars['button_add_active'] = $this->button_add_active;
        $this->tpl_vars['cols_to_sort'] = $this->cols_to_sort;
        $this->tpl_vars['order_params'] = current($this->order_object->getAssociativeArray());
        $this->tpl_vars['search'] = $this->left_block_vars['search']; 
        $this->tpl_vars['action_list'] = $this->action_list;
        $this->tpl_vars['group_operations'] = $this->group_operations;
        $this->tpl_vars['group_operations_conf'] = $this->group_operations_conf;
        $this->tpl_vars['group_operations_list'] = $this->group_operations_list;
        
        end($this->modals_list);
        $m_idx = key($this->modals_list);
        $m_idx = $m_idx !== null ? $m_idx + 1 : 0;
        $this->modals_list[$m_idx] = [
            'id' => 'confirm-delete',
            'type' => 'message',
            'noclose' => true,
            'message' => 'Вы действительно хотите удалть элемент?',
            'btnok_id' => 'delete-from-directory'
        ];
      //  $this->tpl_vars['modals_list']['']
        
    }
   

    protected function prepare() {
        parent::prepare();
        $this->loadFilters();        
   //     \Rosagromash\Application::addJS(array(_JS_DIR_.'table_view.js'));
    //    \Rosagromash\Application::addJS(array(_JS_DIR_.'admin_directory.js'));
        $this->table_cols_headers['Actions'] = l('Actions');
        $this->action_list[] = ['action' => 'editModel',
                        'name' => l('EDIT_ROW'),
                        'image' => 'documentinformation32.gif'] ;
        $this->action_list[] = ['action' => 'deleteModel',
                        'name' => l('DELETE_ROW'),
                        'image' => 'delete32.png'] ;
        $this->table_filter = array_keys($this->table_cols_headers);
        $this->configSortFields($this->defaultSortField);
        $this->rows_count = $this->directory_model->getDirectoryСount($this->filter_array, []);
        $this->pages_count = ceil($this->rows_count / $this->page_rows);
        if ($this->page_num <= 0 || $this->page_num > $this->pages_count) {
            $this->page_num = 1;
        }
    }
    /**
     * Установка фильтров для справочников
     */
     protected function loadFilters() {
         if (Tools::getValue('searchFlag')) {
            if ($val = Tools::getValue('search','GET')) {
                $this->filter_array['search'] = $val; 
         }
            unset($this->table_cols_headers['ContractorName']);
        }         
     }
    protected function configSortFields($fieldName = 'Name')
    {        
        $field_to_sort = Tools::getValue('sortColumn','GET' , $fieldName);
        $order_by = Tools::getValue('sortType','GET','ASC');
        if (!Validate::isTableOrIdentifier($field_to_sort)) {
            $field_to_sort = $fieldName;
        }        
        if (!in_array($order_by, array('ASC','DESC'))) {
            $order_by = 'ASC';
        }
        $this->order_object = new OrderBy($field_to_sort,$order_by);
        //$this->sort_array = $this->order_object->getAssociativeArray();
    }
    protected function setLeftBlockVars() {
        parent::setLeftBlockVars();
        $this->left_block_vars['hidden_inputs'] = $this->hidden_inputs;
        $this->left_block_vars['directory_type'] = $this->link;        
        $this->left_block_vars['blocks_list'][1] = [
            'form_id' => 'filterDataForm',            
            'model' => $this->link,
            'hide' => true,
            'block_name' => l('FILTERS', 'admin'),
            'type' => 'filters'];
        $this->left_block_vars['sort_column'] = $this->order_object->getSortTable();
        $this->left_block_vars['sort_type'] = $this->order_object->getOrderBy();
        if (key_exists('search', $this->filter_array)) {
            $this->left_block_vars['search'] = $this->filter_array['search'];
        } else {
            $this->left_block_vars['search'] = '';
        }
    }

    protected function initTableParams() {
        
    }
        /**
     * Вернуть массив для постраничной навигации
     * @return array
     */
    protected function getPagination()
    {       
        $aResult = array();
        if ($this->pages_count < 10) {
            $startPage = 1;
            $endPage = $this->pages_count;
            $startIdx=0;
            $rightFlag = false;
        }
        else {
            if ($this->page_num > 5) {
                $startPage = $this->page_num - 4;
                $aResult[0] = array ('page' => '1'.$getParams, 'val' => '«');
                $aResult[1] = array ('page' => ($this->page_num-1).$getParams, 'val' => '<');
                $startIdx = 2;
                $endPage = $this->page_num + 4;
                if ($this->page_num > ($this->pages_count - 4)) {
                    $rightFlag = false;
                    $startPage = $this->pages_count - 9;
                    $endPage = $this->pages_count;
                }
                else {
                    $startPage = $this->page_num - 4;
                    $endPage = $this->page_num + 4;
                    $rightFlag = true;
                }
            }
            else {
                $startIdx = 0;
                $rightFlag = true;
                $startPage = 1;
                $endPage = 9;
            }  
        }
        for ($i=$startPage;$i <=$endPage; $i++)
        {
            $aResult[$startIdx]= array ( 'page' => $i.$getParams , 'val' => $i);
            if ($this->page_num == $i) {
                $aResult[$startIdx]['active'] = 1;
            }
            $startIdx++;
        }
        if ($rightFlag) {
            $aResult[$startIdx++] = array ('page' => ($this->page_num+1).$getParams, 'val' => '>');
            $aResult[$startIdx++] = array ('page' => ($this->pages_count).$getParams, 'val' => '»');
        }   
        return $aResult;
    }
    


}
