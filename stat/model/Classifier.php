<?php

namespace app\stat\model;

use app\stat\db\QuerySelectBuilder;
use app\stat\Tools;
use app\stat\Convert;
use app\stat\Sessions;
use app\stat\services\ClassifierService;
/**
 * Description of Classifier
 *
 * @author kotov
 */
class Classifier extends ObjectModel implements IChangeClassifier
{
    protected $classifierId;
    protected $name;
    protected $internationalName;
    protected $classifierGroupId;
    protected $classifierGroupFull;
    protected $orderFull;
    protected $willBeMoved;
    protected $orderIndex;
    protected $form_exist = true;
    protected $form_template_head = 'CLASSIFIER';
    /** @var bool показывать действия в дополнительном блоке модального окнна  */
    protected $actions;
    protected $model_name = 'Classifier';


    protected static $table = "TBLCLASSIFIER";
    
    public function __construct($id = null) {            
        parent::__construct($id);
        $this->directory_rules = [
            'select' => [
                ['tc','Id'],
                ['tc','ClassifierId'],
                ['tc','Name'],
                ['tc','OrderIndex'],
            ],
            'from' => [
                [ self::$table , 'tc'],
            ],
            'where' => [
            ]
        ];
        $this->actions = true;
    }
    
    
    /* Вернуть массив подуровней классификатора 
     * @param null|string|array $filter возвращаемые столбцы
     * @param string|array $idList список идентификаторов, исключаемых из выборки
     * @return array
     */
    public function getChildClassifierList($filter=null,$idList='')
    {
        if (!$this->getId()) {
            return array();
        }     
       $filteredRows='';
       if (is_array($idList) && count($idList) > 0)
       {
           $filteredRows = implode(',', $idList);
       }
       else {
           $filteredRows = $idList;                   
       }        
       $queryBuilder = new QuerySelectBuilder();
       
        $queryBuilder->select = $filter ? $filter : '*';
        $queryBuilder->from = $this::$table;
        $queryBuilder->where = array(['param' => 'ClassifierId','staticText' => $this->id]);
        if ($filteredRows) {
           $queryBuilder->where[] = '"Id" NOT IN ('.$filteredRows.')';
       }
        return getDb()->getRows($queryBuilder);
    }
    public function getClassifierName()
    {
        return $this->name;
    }
    public function isLeaf() {
        $rows_count = (int) getDb()->getRowsCount(new QuerySelectBuilder ([
            'from' => self::$table, 
            'where' => [['param' => 'ClassifierId','staticNumber' => $this->getId()]]
        ]));
        return ($rows_count === 0);
    }
    /**
     * Выбор ближайших дочерних элементов
     * @param type $select
     */
    public function getChilds($select = '*'){
        return getDb()->getRows(new QuerySelectBuilder([
                'select' => $select,
                'from' => self::$table,
                'where' => [['param' => 'ClassifierId','staticNumber' => $this->getId() ]],
                'orderBy' => [['textValue' => '"OrderIndex" DESC NULLS LAST']]
            ]));
    }

    public static function changeClassifier($list, $parent_id) 
    {
        $classifier_id = Convert::getNumbers($parent_id);
        if (is_string($list)) {
           $list = explode(',', $list);
        }
        if (empty($parent_id)) {
            return Tools::getErrorMessage('request error');
        }
          foreach ($list as $element) {
              $cl_id = Convert::getNumbers($element);
              if (empty($cl_id)) {
                  continue ;
              }
              $cl = new Classifier($cl_id);
              if (empty($cl->getId())) {
                    continue;
              }
              $cl->set('classifierId',$parent_id);
              $cl->updateDb();
              unset($cl);              
          }
          return Tools::getMessage('Перенос разделов классификатора выполнен успешно');
        
    }
        protected function formConfigure() {
        parent::formConfigure();
        $id = $this->id;
        if (!$id) {
            $parent_classifier_id = Sessions::getParentClassifierId();
        } else {
            $parent_classifier_id = $this->classifierId;
        }
        $group_classifier_id = $this->classifierGroupId ?? 0;
        ////\Rosagromash\Tools::getValue('classifier', 'GET',41);
        //$parent_classifier = new Classifier()
        $this->form_elements['main_form']['elements_list']['name'] = [
            'label' => l('CLASSIFIER_ELEMENT_NAME'),
            'type' => 'text',
            'size' => 300,
            'required' => true,            
            'value' => $this->name ? $this->name : ''
        ];
        $this->form_elements['main_form']['elements_list']['internationalName'] = [
            'label' => l('CLASSIFIER_ELEMENT_INTERNATIONAL_NAME'),
            'type' => 'text',
            'size' => 300,
            'value' => $this->internationalName ? $this->internationalName : ''
        ];
        if ($this->id) {
            $root_classifier_id = $this->id;
        } else {
            $root_classifier_id = $parent_classifier_id;
        }
        $root_elements = ClassifierService::getClassifierParents($root_classifier_id);
        $root_elements_list = [];
        $root_elements_list[0] = ['text' => 'нет' , 'value' => ''];
        $index = 1;
        foreach ($root_elements as $elem) {
            $root_elements_list[$index++] =  ['text' => $elem['Name'] , 'value' => $elem['Id']];
        }
        $this->form_elements['main_form']['elements_list']['classifierGroupId'] = [
            'label' => l('CLASSIFIER_ELEMENT_GROUP'),
            'description' => l('CLASSIFIER_ELEMENT_GROUP_DESCRIPTION'),
            'type' => 'select',
            'size' => 300,
            'elements' => $root_elements_list ,
            'selected' => $group_classifier_id
        ];
        $this->form_elements['main_form']['elements_list']['orderIndex'] = [
            'label' => l('CLASSIFIER_ELEMENT_INDEX'),
            'description' => l('CLASSIFIER_ELEMENT_INDEX_DESCRIPTION'),
            'type' => 'number',
            'size' => 40,
            'value' => $this->orderIndex ?? 0
        ];
        $this->form_elements['main_form']['elements_list']['classifierId'] = [
            'type' => 'hidden',
            'value' => $parent_classifier_id
        ];
        $this->form_elements['main_form']['elements_list']['willBeMoved'] = [
            'type' => 'hidden',
            'value' => 0
        ];

        
        // Блок технических характеристики

        $this->form_elements['sub_block'][0] = array();
        $this->form_elements['sub_block'][0]['block_head_text'] = l('PERFOMANCE_HEAD');

        $table_headers = l('PERFOMANCE_TABLE_HEADERS');
        
        $td_list = TypeData::getRowsArray();
        $un_list = UnitOfMeasure::getRowsArray();
        $td_options = array();
        $un_options = array();
        
        foreach ($td_list as $el) {
            $td_options[] = ['text' => $el['Name'], 'value' => $el['Id']];
        }
        foreach ($un_list as $el) {
            $un_options[] = ['text' => $el['ShortName'], 'value' => $el['Id']];
        }
        $req_options = [
            ['text' => l('NO','words'), 'value' => 0],
            ['text' => l('YES','words'), 'value' => 1]
        ];
        
        $table_vals = array();
            $table_vals[0][0] = [
               'name' => 'NameChar',
                'type' => 'text',
                'size' => 150
                
            ];
            $table_vals[0][1] = [
               'name' => 'TypeData',
                'type' => 'select',
                'size' => 100,
                'elements' => $td_options
                
            ];
            $table_vals[0][2] = [
               'name' => 'Restriction',
                'type' => 'text',
                'size' => 150
                
            ];
            $table_vals[0][3] = [
               'name' => 'UnitOfMeasureId',
                'type' => 'select',
                'size' => 100,
                 'elements' => $un_options
                
            ];
            $table_vals[0][4] = [
               'name' => 'Necessarily',
                'type' => 'select',
                'size' => 50,
                'elements' => $req_options
                
            ];
        $action_buttons = array();
        
        $this->form_elements['sub_block'][0]['elements_list']['techCharacteristic'] = [
             'type' => 'table-form',
             'table_headers' => $table_headers,
             'table_vals' => $table_vals,
             'action_buttons' => true
         ];
        
        if ($this->actions) {
            $table_headers[] = l('ACTIONS');
            $this->form_elements['actions'] = true;
            $action_buttons = [
              [
                  'type' => 'glyth',
                  'icon' => 'remove-sign',
                  'css_class' => 'modal-remove',
                  'title' => l('DELETE_ROW'),
                  'action' => 'deleteAttr'
              ]
            ];
        } else {
            $this->form_elements['actions'] = false;
        }
        $this->form_elements['sub_block'][1] = array();
        $this->form_elements['sub_block'][1]['block_head_text'] = l('PERFOMANCE_AVAILABLE_HEAD');
        $perfomance_elements = array();
        if ($this->id) {        
            $perfomance_elements = PerformanceAttr::getPerformanceAttrForClassifier($this->id);
        }
        $table_elements = array();
        // сброс массиви технических характеристик
        $tech_chars = [];
        Sessions::unsetVarSession('TECH_CHARS');
        
        if (count($perfomance_elements) == 0) {
            $this->form_elements['sub_block'][1]['message'] = l('NO_PERFOMANCE_AVAILABLE');            
        } else {
            foreach ( $perfomance_elements as $p_el) {
                $action = ($p_el['ClassifierId'] == $id ) ? $this->actions : null;
                $required_text = ($p_el['Required']) ? l('YES','words') : l('NO','words');
                $table_elements[] = [
                    'id' => $p_el['Id'],
                    'elements' => [
                        $p_el['AttrName'],
                        $p_el['TypeDataName'],
                        $p_el['PossibleValue'],
                        $p_el['UnitName'],
                        $required_text
                    ],
                    'actions' => $action,
                    'actions_list' => $action_buttons
                ];
            }
        }
        Sessions::setValueToVar('TECH_CHARS', json_encode($tech_chars));
        
        
        //$available_headers =   
              /* $available_vals[0][0] = [              
                'type' => 'string',
                'size' => 150                
            ];*/
        
        $this->form_elements['sub_block'][1]['elements_list']['techCharacteristicAvailable'] = [
            'type' => 'table',
            'rows_class' => 'attr',
            'table_vals' => $table_elements,
            'table_headers' => $table_headers
        ];
    }
    public function saveModelObject($data, $form_elements = null, $return_json = true) 
    {
        $id = $this->getId();    
        parse_str($data['frm_data'],$form_elements);
        $attrs_list = json_decode(Sessions::getVarSession('TECH_CHARS','[]'));
        if (empty($attrs_list)) {
            Tools::cleanCache('classifier.dat');
            return parent::saveModelObject($data, $form_elements, true);
        }
        $res = parent::saveModelObject($data, $form_elements, false);
        if ($res['STATUS'] !== OBJECT_MODEL_SAVED) {        
            if ($return_json) {
                return json_encode($res);
            } else {
            return $res;
            }
        }
        Tools::cleanCache('classifier.dat');
        /*$res = [
            'STATUS' => OBJECT_MODEL_SAVE_ERROR,
            'MESSAGE' => 'Не удалось че-то там'
        ];*/
       if (count($attrs_list) > 0) {
            foreach ($attrs_list as $attr) {
                $attrs_arr = [
                    'name' => $attr->name,
                    'classifierId' => $attr->classifierId,
                    'typeDataId' => $attr->typeDataId,
                    'unitOfMeasureId' => $attr->unitOfMeasureId,
                    'possibleValue' => $attr->possibleValue,
                    'necessarily' => $attr->necessarily
                ];
                if ($id) {
                    $attrs_arr['classifierId'] = $id;
                } else {
                    $attrs_arr['classifierId'] = (int) $res['STATUS']['NEW_ID'];
                }
            }
            $attr_elem = PerformanceAttr::getInstance($attrs_arr);
            $attr_elem->addToDb();
        }
        if ($return_json) {
            return json_encode($res);
            } else {
            return $res;
        }
    }

}
