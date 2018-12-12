<?php

namespace app\stat\model;

use \app\stat\db\QuerySelectBuilder;
use app\stat\exceptions\DefaultException;
use app\stat\db\SimpleSQLConstructor;
use \app\stat\Validate;
use app\stat\ViewHelper;
use app\stat\Tools;

/**
 * Description of ObjectModel
 *
 * @author kotov
 */
abstract class ObjectModel {
    
    public $id;       
    /** @var type  Указывает присутствие в таблице первичного ключа Id*/
    protected $id_flag = true;
    /** @var string Имя таблицы в базе данных SQL  */
    protected static $table;
    
    /** @var array массив с именами полей в таблице    */
    protected $table_fields;

    /** @var string SQL Table identifier */
    protected $identifier = 'Id';
    /** @var array поля, для которых запрещены нулевые значения */
    protected $not_nulls = array();
    /** @var boolean доступность объекта для записи */
    protected $writable = false;
    /** @var boolean доступность объекта для удаления */
    protected $deletable = false;
    /** @var array поля по которым идет фильтрация для удаления ( для объектов без id) */
    protected $delete_fields = array();
    /** @var array поля, подлежащие изменению */
    protected $update_fields = array();
    /** @var bool Флаг наличия информационного блока для объекта модели */
    protected $info_block_exist = false;
    /** @var array Переменные для шаблона информационного блока модели */
    protected $info_elements = array();
    protected $inform_block_data = null;  
    /** @var bool Связанная со строкой в таблице */
    protected $binding_table = false;
    /** @var bool тип формы (true - новый объект, false - существующий) */
    protected $is_new_form = false;
    /** @var bool Флаг наличия формы редактирования для объекта модели */
    protected $form_exist = false;
    protected $form_template_head = '';    
    protected $form_data = null;     
    /** @var array Переменные для подстановки в шаблон формы */ 
    protected $form_elements = array();
    /** @var array набор правил для отображения модели */
    protected $directory_rules = array();
    protected $search_fields = 'Name';    




    // protected static $table = '';






    /**
     * Создает новый объект модели по первичному ключу
     * @param int|string $id идентификатор
     * @param type $tblchk поле с первичным ключом, если ключ отличен от "Id"
     */
    public function __construct($id = null,$tblchk=null) {
        $dbInstance = getDb();
        $this->table_fields = $dbInstance->getFieldsFromTable(static::$table);
        $this->id_flag = $this->idExists(); 
        if (!$id) {
            if ($this->id_flag) {
                $this->writable = true;                
            }           
            return;
        }
        if (is_string($id)) {
            $id = intval($id);
        }
        if (empty($id)) {
            throw new DefaultException('CreateModelError','Неверные параметры модели');
        }
        // Имя первичного ключа
        $tblchk = $tblchk ? $tblchk : $this->identifier;
        if (!Validate::isTableOrIdentifier(static::$table)) {
            throw new DefaultException('CreateModelError','Неверные параметры модели');
        }
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->select = '*';
        $queryBuilder->from = static::$table;
        $queryBuilder->where = [ 
            [
                'param' => $tblchk,
                'bindingValue' => $id
            ]
        ];
        $result = $dbInstance->getRow($queryBuilder);
                if (!empty($result))
        {
            foreach ($result as $key => $value)
            {
                $key = lcfirst($key);
                   $this->{$key} = $value;
            }
            $this->binding_table = true;
        }
        array_push($this->not_nulls, $this->identifier);                                
    }
    
    protected function idExists() 
    {
        foreach ($this->table_fields as $field) {
            if (strtolower($field['column_name']) == 'id') {
                return true;
            }
        }
        return false;
    }
    public function __set ($name,$value)
    {
        if (property_exists ($this , $name ))
        {
            $this->writable = true;
            array_push($this->update_fields, ucfirst($name));
            $this->{$name} = $value;
        }
    }
    public function __get($name) {
        if (property_exists ( $this , $name )) {
            return $this->{$name};
        }        
    } 
    public function get($name) {
        return $this->__get($name);
    }
    /**
     * Принудительная функция сеттер
     * @param type $name
     * @param type $value
     */
    public function set($name,$value) {
        $this->__set($name,$value);
    }
    public static function getTableName()
    {
        return static::$table;
    }
 /**
 * Пдлучить массив строк объекта
 * @param type $filter возвращать только выбранные столбцы
 * @param array $where фильтрация данных (блок WHERE)
 * @param type $orderBy условие сортировки
 * @param type $distinct вернуть только уникальные строки
 * @param type $pageNumber номер страницы
 * @param type $rowCount число строк
 * @return array
 */
   public static function getRowsArray($filter=array(),$where=null,$orderBy=null,$distinct=false,$rownum = false,$pageNumber = null,$rowCount=null)
   {
       $builder = new QuerySelectBuilder();
       if (!$pageNumber) {
           $builder->select = $filter;
           $builder->from = static::$table;
           $builder->where = $where;
           $builder->orderBy = $orderBy;
           $builder->distinct = $distinct;
           $builder->rowNum = $rownum;
           return getDb()->getRows($builder);
       }
       if (Validate::isInt($pageNumber) &&
              Validate::isInt($rowCount)) {
           $sql = static::generateSelect($filter,$where,$orderBy,$distinct);
           $sql = SimpleSQLConstructor::generatePageFilter($sql, $rowCount, $pageNumber);           
           return getDb()->querySelect($sql);           
       }
       return array();
   }    
    
    /**
    * Сформировать Select выражение для списка строк объекта
    * @param array $filter возвращать только выбранные столбцы
    * @param array $where условие фильтрации
    * @param type $orderBy условие сортировки
    * @param bool $distinct вернуть только уникальные значения
    * @return string SQL выражение
    */
   public static function generateSelect($filter=array(),$where=null,$orderBy=null,$distinct=false)
   {
       $builder = new QuerySelectBuilder();
       if (!empty($filter)) {
            $builder->select = '*' ;
       } else {    
           $builder->select = implode(',', $filter);       
       }
       $builder->distinct = $distinct;
       $builder->from = static::$table;
       $builder->where = $where;
       $builder->orderBy = $orderBy;
       $sql = $builder->generateQuery();
       return $sql;
   }
    /**
     * Возвращает идентификатор
     * @return int
     */
    public function getId()
    {
        if (isset($this->id)) {
            return intval($this->id);
        } else {
            return false;
        }
    }
        /**
     * Вернуть имя
     * @return string
     */
    public function getName()
    {
        if (property_exists($this, 'name')) {
            return $this->name;
        }
        else {
            return '';
        }
    }
    /**
    * Получить отфильтрованные строки
    * @param type $idList массив или строка идентификаторов
    * @param type $filter возвращать только выбранные столбцы
    * @param type $distinct вернуть только уникальные строки
    * @return array
    */
    public static function getFilteredRows($idList,$filter=array(),$orderBy=null,$distinct=false) 
    {
        $filteredRows = '';
        if (is_array($idList) && count($idList) > 0) {
            $filteredRows = implode(',',$idList);
        } else {
            $filteredRows = $idList;
        }
        if (!$filteredRows) {
            return [];
        }
        $builder = new QuerySelectBuilder();
        $builder->where = array ('"Id" IN ('.$filteredRows.')');
        $builder->select = ($filter > 0) ? $filter : '*';
        $builder->from = static::$table;
        $builder->orderBy = $orderBy;       
        if ($distinct) {
            $builder->distinct = true;
        }
        return getDb()->getRows($builder);                               
    }
    /**
     * Вернуть значение $id соответствующее полю и значению. Подходит для полей 
     * с отношение один к одному или многие к одному,так как возвращается одно значение
     * @param string $field
     * @param int|string $value
     */
    public static function getFieldByValue($field,$value) {
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->select = 'Id';
        $queryBuilder->from = static::$table;
        if (intval($value)) {
            $queryBuilder->where =  [['param' => $field,'staticNumber' => (int)$value]];
            $aResult = getDb()->getRow($queryBuilder);
        } elseif (is_string($value)) {
            $queryBuilder->where = [['param' => $field,'staticText' => $value]];
            $aResult = getDb()->getRow($queryBuilder);
        } else {
            return;
        }
        return $aResult['Id'];
    }
    public static function getRowsCount($where=null)
    {
        $queryBuilder = new QuerySelectBuilder();
        $queryBuilder->from = static::$table;
        $queryBuilder->where = $where;
        return (int) getDb()->getRowsCount($queryBuilder);
    }
    /**
     * Показ формы соответствующей модели
     * @return $array
     */
    public function displayForm($getJSON  = true) {
       if (!$this->form_exist) {           
           $this->form_data = [ 
               'STATUS' => FORM_NOT_EXIST,
               'MESSAGE' => l('ERROR_FORM_NOT_EXIST','messages')] ;
       } else {                   // конфигурирование связанной с моделью формы
           $this->formConfigure();
           $this->getFormData();
       }
       if ($getJSON) {
           return json_encode($this->form_data);
       } else {
           return $this->form_data;
       }
    }
    protected function formConfigure() 
    {
        $btns = l('BTN_ACTIONS');
        $this->form_elements['submit_button'] = true;
        $this->form_elements['site_url'] = _SITE_ROOT_URL_;
        $this->form_elements['submit_button_method'] = 'saveModel';
        $this->form_elements['submit_button_text'] = $btns['save'];
        if (!empty($this->form_template_head)) {
            if (!$this->getId()) {
                $this->form_elements['form_class'] = 'new_form';
                $this->is_new_form = true;
            } else {
                $this->form_elements['form_class'] = 'edit_form';
                $this->form_elements['main_form']['element_id'] = $this->getId();
                
            }
            $this->form_elements['head_text'] = l($this->form_template_head.'_HEAD');
            $this->form_elements['model_name'] = $this->model_name;
            $this->form_elements['main_form']['block_head_text'] = $this->is_new_form ?  l($this->form_template_head.'_NEW') :  l($this->form_template_head.'_EDIT');
            $this->form_elements['form_type'] = 'modal';
            $this->form_elements['main_form']['form_id'] = 'form-for-model';			
        }
        
    }
    protected function getFormData() 
    {
        $form_data = [ 'STATUS' => FORM_OK];
        $helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'object_form',$this->form_elements);
        $form_data['HTML_DATA'] = $helper->getRenderedTemplate();        
        $this->form_data = $form_data;        
    }
    /**
    * 
    * @param type $assoc_array
    * @return ObjectModel
    */
   public static function getInstance($assoc_array) 
   {
       $className = static::class;
       $result = new $className();
       foreach ($assoc_array as $key=>$value) 
       {
           $prop = lcfirst($key);
           $result->$prop = $value;
           array_push($result->update_fields, ucfirst($key));
       }
       return $result;       
   }
    public function setWritable() 
    {
        $this->writable = true;
    }
    /**
     * Добавление объекта в базу данных
     * @return boolean
     */
    public function addToDb($addId = false) 
    {
        $data = array();
        if ($addId == false) {
            if (isset($this->id)) {
                unset($this->id);
            }
        }
        foreach ($this->table_fields as $value) {
            $insert_element = array();
            $property_name = lcfirst($value['column_name']);
            if ($this->$property_name !== null && $this->$property_name !== "") 
            {
                $insert_element['field'] = $value['column_name'];
                $insert_element['type'] = $value['type'];
                $insert_element['value'] = $this->$property_name;
                $data[] = $insert_element;
            }            
        }
        
        return getDb()->insert($this::$table, $data, $this->id_flag);
    }
    /**
    * Установить поля, подлежащие изменению в БД
    * @param array $fields
    */
    public function setFieldsToUpdate(array $fields)
    {
        $this->update_fields = $fields;
    }
    public function saveObject()
    {
        if (!$this->id_flag) {
            return $this->updateDb();
        }
        return (int) $this->id > 0 ? $this->updateDb() : $this->addToDb();      
    }    
     /**
     * Сохранение объекта в БД
     */
    public function updateDb($return_json=true) {
        $where = array();
        $update_fields = array();
        if (!$this->writable || (count($this->update_fields) == 0)) {
            if (!$return_json) {
                return false;
            } else {
                return Tools::getErrorMessage( l('REQUEST_PARAMS_ERROR','messages'),OBJECT_MODEL_SAVE_ERROR);
            }
        }
        if ($this->id) {
            $where = [['param' => 'Id', 'staticNumber' => $this->id]];
        } 
       // $update_fields = array();
         foreach ($this->table_fields as $field)
         {
            if (in_array($field['column_name'], $this->update_fields)) {
                $type = $field['type'];
                $prop = lcfirst($field['column_name']);
                if (!$this->id) {
                    array_push($where,['param' => $field['column_name'],'pureValue' => Tools::wrapDataByType($this->$prop, $type)]);
                }
                if ($this->$prop !== null) {
                    $update_fields[] = ['field' => $field['column_name'], 'type' => $type,'value' => $this->$prop];
                } else {
                    $update_fields[] = ['field' => $field['column_name'], 'value' => 'NULL'];
                }
            }
         }
         $db = getDb();
         $return_id = false; // возвращать ли ID добавленной записи 
         $queryBuilder = new QuerySelectBuilder();
         $queryBuilder->from = static::$table;
         $queryBuilder->where = $where;
         if ($db->getRowsCount($queryBuilder)) {
            $result = $db->update($this::$table, $update_fields,$where);
         }  
         else {
            $result = $db->insert($this::$table, $update_fields, $this->idExists());
            if ($this->idExists()) {
                $return_id = true;
            }
           
         }
         if ($result) {
             $jResult = [
                 'STATUS' => OBJECT_MODEL_SAVED
             ];
             if ($return_id) {
                 $jResult['NEW_ID'] = $result;
             }
         } else {
            if (!$return_json) {
                return false;
            } else {
                return Tools::getErrorMessage( l('REQUEST_PARAMS_ERROR','messages'),OBJECT_MODEL_SAVE_ERROR);
            }
        }
        if ($return_json) {
            return json_encode($jResult);
        } else {
            return $jResult;
        }
        //return Tools::getMessage('Объект успешно сохранен');
    }
        /**
     * 
     * @param array $arr
     */
    public static function deleteByProps(array $arr) {
        $obj = static::getInstance($arr);
        $where = array();
        foreach ($obj->table_fields as $field) {
            $prop = lcfirst($field['column_name']);
            if (key_exists($prop, $arr) ) {
                array_push($where,['param' => $field['column_name'],'pureValue' => Tools::wrapDataByType($obj->$prop, $field['type'])]);
                $obj->deletable = true;
            }            
        }
        return getDb()->delete($obj::$table, $where);

       // $obj->delete();
    }
    public function delete($arr=array()) {
        if (property_exists($this, 'deleted')) {
            $this->set('deleted',1);
            return (bool) $this->updateDb(false);
        }
        if ( $this->id_flag) {
            if ($this->id) {
                $where =  [[
                    'param' => 'Id',
                    'staticNumber' => $this->id
                ] ];                
                return getDb()->delete(static::$table, $where);
            }           
        }
        $where = array();
        foreach ($this->table_fields as $field) {
            $prop = lcfirst($field['column_name']);
            if (key_exists($prop, $arr) ) {
                array_push($where,['param' => $field['column_name'],'pureValue' => Tools::wrapDataByType($this->$prop, $field['type'])]);
                $this->deletable = true;
            }
         }
        if (!$this->deletable) {
            return false;
        }
         return getDb()->delete(static::$table, $where);
    }
    
   /**
     * Показ блока информации для соответствующей модели
     * @return $array|string
     */
    
    public function displayInfoBlock($getJSON  = true) {
        if (!$this->isInfoblockExist()) {
            $this->inform_block_data = [
                'STATUS' => INFO_BLOCK_NOT_EXIST,
                'MESSAGE' => l('ERROR_INFORM_BLOCK_NOT_EXIST','messages')
            ];
        } else {
            
            $this->informBlockConfigure();
            $this->getInformBlockData();
        }
        if ($getJSON) {
            return json_encode($this->inform_block_data);            
        } else {
            return $this->inform_block_data;
        }        
    }
    /**
     * Конфигурирование информационного блока модели
     */
    protected function informBlockConfigure() {        
    }
    protected function getInformBlockData() {       
        $inform_block_data = [ 'STATUS' => INFO_BLOCK_OK];
        $helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'model_inform',$this->info_elements);
        $inform_block_data['HTML_DATA'] = $helper->getRenderedTemplate();
        $this->inform_block_data = $inform_block_data;
    }
    
    
    /**
     * 
     * @return bool
     */
    public function isInfoblockExist() {
        return $this->info_block_exist;
    }
    /**
     * @param type $filter фильтрация данных
     * @param array $ex_filter не включать в фильтрацию
     * @param type $count вернуть число форм
     * @param type $pageNumber номер страницы
     * @param type $rows_сount общее число строк
     * @param int $rows_in_page число строк на странице (по умолчанию берется из настроек портала)
     * @param type $sortBy поля для сортировка
     * Получить список элементов справочника
     * @return array ассоциативный массив с элементами count- общее число строк, data - данные
     */
    public function getDirectory($filter = [],$ex_filter=[],$count=false,$pageNumber = null,$rows_count=null,$rows_in_page=null,$sortBy=null) {
        if (empty($this->directory_rules)) {
            return false;
        }
        $select = $this->directory_rules['select'];
        $from = $this->directory_rules['from'];
        if (key_exists('where', $this->directory_rules)) {
            $where = $this->directory_rules['where'];
        }
        if (!empty($filter) && is_array($filter) ) {
            $tmp_sql = SimpleSQLConstructor::generateSimpleSQLQuery($select, $from,$where,$sortBy);
            $select = '';
            $from = array(['name' => 'res','textValue' => '(' .$tmp_sql.')']);
            $where = array();            
            foreach ($filter as $el_key => $el_value)
            {
                if (!empty($el_value) && !in_array($el_key, $ex_filter)) {
                    switch ($el_key) 
                    {
                        case 'contractor':
                            array_push($where, ['param' => 'res.ContractorId','staticValue' => $el_value]);
                            break;
                        case 'role':
                            array_push($where, ['param' => 'res.RoleId','staticValue' => $el_value]);
                            break;
                        case 'country':
                            array_push($where, ['param' => 'res.CountryId','staticValue' => $el_value]);
                            break;
                        case 'parent':
                            array_push($where, ['param' => 'res.ClassifierId','staticValue' => $el_value]);
                            break;
                        case 'present':
                            array_push($where, ['param' => 'res.Present','staticValue' => $el_value]);
                            break;
                        case 'model_type':
                            array_push($where, ['param' => 'res.ModelTypeId','staticValue' => $el_value]);
                            break;
                        case 'classifier':
                            array_push($where, ['param' => 'res.ClassifierId',
                                'operation' => 'IN',
                                'pureValue' => ' (SELECT c."Id" FROM "TBLCLASSIFIER" c
                    START WITH c."Id" = '.$el_value.' CONNECT BY PRIOR  c."Id" = c."ClassifierId") '
                                ]);
                            break;
                        case 'search':
                            if (is_string($this->search_fields)) {
                                array_push($where, 'LOWER("res"."'.$this->search_fields.'") LIKE \'%'.mb_convert_case(trim($el_value),MB_CASE_LOWER).'%\'');
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
             
            // применить доп. фильтры, если присутствуют
            $this->applyAdditionalFilters($filter,$select,$from,$where);
        }
        if (!$pageNumber)
        {
            if (!$count) {
                return getDb()->getRows($select,$from,$where,$sortBy);
            }
            else {
                $sql = SimpleSQLConstructor::generateSimpleSQLQuery($select, $from, $where, $sortBy);
                $select = array(['textValue' => 'COUNT ("tbl"."Id") AS "Count"']);
                $from = array(['name' => 'tbl','textValue' => '(' .$sql.')']);
                
                return getDb()->getRows(new QuerySelectBuilder([
                    'select' => $select,
                    'from' => $from]));
            }
        }
        if (Validate::isInt($pageNumber) &&
            Validate::isInt($rows_count)) {
           $sql = SimpleSQLConstructor::generateSimpleSQLQuery($select, $from, $where, $sortBy);
           $sql = SimpleSQLConstructor::generatePageFilter($sql, $rows_count, $pageNumber,$rows_in_page);
        return getDb()->querySelect($sql);           
       }
       return array();
    }
    
    /**
     * Получить количество элементов справочника
     */
    public function getDirectoryСount($filter = [],$ex_filter = []) {
        return (int) $this->getDirectory($filter,$ex_filter,true)[0]['Count'];
        
    }
    /**
     * Доп. фильтры, при необходимости определяются в классе модели
     */
    protected function applyAdditionalFilters($filter,&$select,&$from,&$where) {}
    
     /**
     * Запись в БД объекта модели
     * @param string $data входные параметры из POST запрорса
     * @param array $data массив входных параметров 
     * @param boolean $return_json вернуть статус в виде массива или строки json
     * @return array|string статус записи
     */
    public function saveModelObject($data,$form_elements=null,$return_json=true) {
        if (!$form_elements) {
            parse_str($data['frm_data'],$form_elements);
        }
        if (!$this->getId()) {
            $new_object = $this->getInstance($form_elements);
            $new_object->setWritable();            
            $this->id = $new_object->addToDb();
            
            if ($this->id) {
                $res = [
                    'STATUS' => OBJECT_MODEL_SAVED,
                    'NEW_ID' => $this->id
                ];
            } else {
                $res = [ 
                    'STATUS' => OBJECT_MODEL_SAVE_ERROR, 
                    'MESSAGE' => l('ERROR_WRITE_TO_DB','messages')];
            }
            if ($return_json) {
                return json_encode($res);
            } else {
                return $res;
            }
        } else {
            foreach ($form_elements as $key => $value) {
                if (property_exists($this,$key)) {
                    if ($this->$key != $value) {
                        $this->set($key,$value);
                    }
                }
            }
            if ($this->writable) {
                $res = $this->updateDb(false);
            } else {
                $res = [
                            'STATUS' => OBJECT_MODEL_SAVED,
                        ];
            }
            if ($return_json) {
                return json_encode($res);
            } else {
                return $res;
            }
        }
    }
 /**
 * Получить список строк модели 
 */
    public static function getRows($filter='*',$rowsCount=null,$where=null,$orderBy=null) {
        if (!$rowsCount) {
            return getDb()->getRows(new QuerySelectBuilder ([ 
                'select' => $filter, 
                'from' => static::$table, 
                'where' => $where, 
                'orderBy' => $orderBy]));
        } else {
           $sql = static::generateSelect($filter,$where,$orderBy); 
           $sql = SimpleSQLConstructor::getLimitedRowsQuery($sql, $rowsCount);
           return getDb()->querySelect($sql);
        }
    }
    public function isBinding() {
        return $this->binding_table;
    }
    public function getNextAndPrevIds($where = null,$orderBy = null) {
        $sql = static::generateSelect(['"Id"'],$where,$orderBy);
        return getDb()->getNextAndPrevId($this->getId(), $sql);
    }
    public function setElementsFromArray ($assoc_array) 
    {
       foreach ($assoc_array as $key=>$value) 
       {    
           if (property_exists ( $this , $key )) {
               if ($this->$key != $value) {
                    $this->$key = $value;
                    array_push($this->update_fields, ucfirst($key));
               }
           }
       }        
    }
}
