<?php

namespace app\stat\model;

use app\stat\db\QuerySelectBuilder;
use app\stat\ViewHelper;
use app\stat\Sessions;
use app\stat\Tools;
use app\stat\Convert;
use Yii;
/**
 * Description of AllFormTypes
 *
 * @author kotov
 */
class AllFormTypes extends ObjectModel
{
    protected $month;
    protected $year;
    protected $currencyId;
    protected $inputFormId;
    protected $contractorId;    
    protected $typeData;
    /** @var boolean флаг корректности типа формы  */
    protected $is_available;
    /** @var boolean новая форма или открытая для редактирования  */
    protected $is_new_form;
    /** @var \DateTime период формы  */
    protected $period;
    /** @var array универсальный шаблон формы */        
    public $form_template = array();
    /** @var Users пользователь, редактор формы */
    protected $user;
    /** @var Contractor производитель, которому форма принадлежит */
    protected $contractor;
    /* Переменные шаблона */
    protected $tpl_vars = array();
    protected $template_file;
    /** @var array Статус валидации формы */
    protected $form_valid_status = array();
    /** @var InputForm
     */
    protected $inputForm;
    
    public $formName;
    

    public function __construct($id = null, $tblchk = null) {
        parent::__construct($id, $tblchk);
        $this->not_nulls = array_merge($this->not_nulls,['currencyId','inputFormId', 'year','month','typeData']);
    }
    
    /**
     * Установить значение периода
     * @param type \DateTime $period
     */
    public function setPeriod(\DateTime $period) {
        $this->period = $period;
    }
    public function setContractorId($id) {
        $this->contractorId = $id;
    }
    public function setTypeData ($formType)
    {
        if (is_numeric($formType)) 
        {
            $this->typeData = $formType;
        }
    }
    /**
     * 
     * @param \InputForm $if
     */
    public function getHtmlForm(InputForm $if)
    {
        
        if (in_array($this->typeData, DatabaseType::$availableTypes)) {            
            $this->is_available = true;
            $this->inputForm = $if;
            $this->contractor = new Contractor ($if->contractorId);
            if ($if->getId()) {
                Sessions::setValueToVar('FORM_STATUS', 'open');
                Sessions::setValueToVar('DEFAULT_FORM_MONTH', $this->period->format('n'));
                Sessions::setValueToVar('DEFAULT_FORM_YEAR', $this->period->format('Y'));                
                $this->is_new_form = false;
                $this->user = new User($if->userId);
                $this->contractor = new Contractor ($if->contractorId);
                
            }
            else {
                Sessions::setValueToVar('FORM_STATUS', 'new');
                $this->is_new_form = true;
                $this->user = new User(Yii::$app->user->getId());
                $this->contractor = new Contractor ($this->contractorId);
                
            }                        
        }
        $this->prepareHtmlForm();
        $view_helper = new ViewHelper(_FORMS_TEMPLATES_DIR_, $this->template_file,$this->tpl_vars);
        return $view_helper->getRenderedTemplate();
    }
    protected function prepareHtmlForm() {
        $monthsArray = l('MONTHS','words'); 
        $comment = '';
        if (!$this->is_new_form) {
             //   $form_input = '<input class="if_id" type="hidden" name="if_id" value="'.$this->inputForm->getId().'"/>';
             $this->tpl_vars['table_id'] = 'opened_form';
             $comment = $this->inputForm->comment; 
             
        } else {            
            $form_input = '';
            $this->tpl_vars['table_id'] = 'new_form';
        }
        // для выбора периода при редактировании формы администратором
        $start_year = 2009;
        $end_year = date('Y');
        $this->tpl_vars['years'] = ['start' => $start_year,'end' => $end_year];
        $this->tpl_vars['month'] = $monthsArray;        
        $editorId = $this->contractor->getEditorId();
        $editor = new User( $editorId );
        $editot_fio = $editor->getFIO();
        $type_form = new DatabaseType($this->inputForm->dataBaseTypeId);
        $this->tpl_vars['form_name'] = $type_form->getName(); 
        $this->tpl_vars['form_type'] = $type_form->id;
        $this->tpl_vars['month'] = $this->inputForm->month; 
        $this->tpl_vars['year'] = $this->inputForm->year;
        $this->tpl_vars['contractor_id'] = $this->inputForm->contractorId;
        $this->tpl_vars['form_period'] = l('REPORT_PERIOD').':<br><span id="period-month">'.$monthsArray[$this->period->format('n')].'</span> <span id="period-year">'.$this->period->format('Y').'</span>';
        $this->tpl_vars['comment'] = l('INPUT_FORM_COMMENT');
        $this->tpl_vars['comment_text'] = $comment;
        $this->tpl_vars['user_role'] = get_role();
        $this->tpl_vars['demo'] = is_demo();
        $this->tpl_vars['check_admin'] = l('CHECK_ADMINISTRATOR');
        $this->tpl_vars['actuality'] = $this->inputForm->actuality;
        $this->tpl_vars['btn_send'] = l('INPUT_FORM_BUTTON_SEND');
        $this->tpl_vars['btn_cancel'] = l('INPUT_FORM_BUTTON_CANCEL');  
        $this->tpl_vars['btn_close'] = l('INPUT_FORM_BUTTON_CLOSE');  
        $this->tpl_vars['company'] = l('COMPANY');
        $this->tpl_vars['editor'] = l('EDITOR');
        $this->tpl_vars['admin'] = is_admin();
        $this->tpl_vars['company_name'] = $this->contractor->getName();
        $this->tpl_vars['editor_name'] = $editor->surName . ' ' .$editor->name;
        $this->tpl_vars['editor_id'] = $editor->getId();                
    }
    /**
     * Создание новой формы ввода
     * @param array $params
     * @return int Флаг выполнения операции (0-ок,2 ошибка)
     */
    public function saveNewInputForm($params) {
        // Проверка формы на валидность
        
        $user = Yii::$app->user->getIdentity();
        $this->inputForm = new InputForm();
        $this->inputForm->comment = $params['comment'];
        if (!empty(trim($params['comment']))) {
            if (key_exists('actuality', $params)) {
                $this->inputForm->actuality = $params['actuality'];
            }
        } else {
            $this->inputForm->actuality = 1;
        }
        $this->inputForm->dataBaseTypeId = $params['formType'];
        $this->inputForm->userId = $user->getId();
        $this->inputForm->contractorId = $params['contractorId'];
        $this->inputForm->date = date("d.m.Y");
        if (!$params['confirm']) {
            $this->validateForm($params);           
        } else {
            $this->form_valid_status = [
                'STATUS' => FORM_VALID_OK
            ];
        }
                        

        if (!is_array($params['date'])) {
            return [
                'STATUS' => FORM_SAVE_ERROR,
                'MESSAGE' => l('REQUEST_PARAMS_ERROR','messages')
            ];
        }
        foreach ($params['date'] as $value) {
            if (key_exists('type', $value)) {
                switch ($value['type'])
                {
                    case 'month':
                        $this->inputForm->month = $value['value'];
                        break;
                    case 'year': 
                        $this->inputForm->year = $value['value'];
                        break;                        
                }
            }            
        }
        $this->inputForm->day = -1;                
    }
    /**
     * Обновление формы
     * @param InputForm $form форма для обновления
     * @param array $params данные для обновления
     * @return int Флаг выполнения операции (0-ок,1-без изменения,2 ошибка записи)
     */
    public function updateInputForm($form,$params) {
        $this->inputForm = $form;
        if ($this->inputForm->comment != $params['comment']) {
          //  array_push($this->inputForm->update_fields,'Comment');
            $this->inputForm->set('comment',$params['comment']);
        }
        if (key_exists('actuality', $params)) {
             if ($this->inputForm->actuality != $params['actuality']) {
               //  array_push($this->inputForm->update_fields,'Comment');
                 $this->inputForm->set('actuality', $params['actuality']);
             }                   
        }
        foreach ($params['date'] as $p_date) {
            
            switch ($p_date['type']) {
                case 'month':
                    if ($this->inputForm->month != $p_date['value']) {
                        $this->inputForm->set('month', $p_date['value']);                        
                    }
                    break;
                case 'year':
                    if ($this->inputForm->year != $p_date['value']) {
                        $this->inputForm->set('year', $p_date['value']);
                        $writable = true;
                    }
                    break;
            }
        }
    }
    
    protected function validateForm($params) {
        $this->form_valid_status['STATUS'] = FORM_VALID_UNDEFINED;
        $percent = 50;
        $max_price = (100 + $percent) / 100;
        $min_price = (100 - $percent) / 100;
        $old_date_count = 7;
        foreach ($params['date'] as $val) {
            if ($val['type'] == 'month') {
                $month = $val['value'];
            }
            if ($val['type'] == 'year') {
                $year = $val['value'];
            }
        }
        //$year = date("Y");
        //$month = date("m");
        $mydate = $year . '/' . $month;
        $old_dates = Tools::getOldDate($mydate, $old_date_count);
        $old_date_count--;
        array_shift($old_dates);
        $selection = array();
        $j = 0;
        for ($i=0; $i<$old_date_count; $i++) {    
            $date_arr = explode('/',$old_dates[$i]);
            $y = $date_arr[0];
            $m = $date_arr[1];
            if ($i == 0) {
                $selection[0][1] = $m;
                $selection[0][0] = $y;
                continue;
            }
            if ($selection[$j][0] == $date_arr[0]) {
                $selection[$j][1] .= ',' . $m;
            }
            else {
                $j++;
                $y = $date_arr[0];
                $m = $date_arr[1];
                $selection[$j][1] = $m;
                $selection[$j][0] = $y;    
            }         
        }
        if (count ($selection) == 1) {
            $where_date = '"t"."Year" = ' . $selection[0][0] . ' AND "t"."Month" IN (' . $selection[0][1].')';
        }
        else { 
           $where_date = '(';
           for ($i = 0; $i < count($selection);$i++) {

                $where_date .= '("t"."Year" = ' . $selection[$i][0] . ' AND "t"."Month" IN (' . $selection[$i][1].'))';
                if ($i != count($selection)  - 1) {
                    $where_date .= ' OR ';
                }
            }
            $where_date .= ')';
        }
        
        
        if (key_exists('rows_list', $params)) {
            $elements = $params['rows_list'];
            foreach ($elements as $element) {
                foreach ($element['values_list'] as $vals) {
                    $element_id = $element['row'];
                    preg_match_all('/\d+/', $element_id, $model_nfo);
                    if (count ($model_nfo[0]) == 0) continue;                                        
                    $model_id = $model_nfo[0][0];                    
                    if ($vals['type'] == 'price') {
                        $value_price = Convert::getNumbers($vals['value']);
                    }
                    if ($vals['type'] == 'count') {
                        $value_count = Convert::getNumbers($vals['value']);
                    }                                            
                    
                }
                if (empty($value_count) && empty($value_price)) {
                    continue;                    
                }
                if (empty($value_count)) {
                    if (!key_exists('STATUS', $this->form_valid_status)) {
                        $this->form_valid_status['STATUS'] = FORM_VALID_ERROR;
                    }
                    $this->form_valid_status['ELEMENTS'][] = [
                        'NAME' => $element_id, 
                        'MESSAGE' => l('CHECK_COUNT_VALUE','messages')];
                    continue;
                }
                if (empty($value_price)) {
                    if ($this->form_valid_status['STATUS'] == FORM_VALID_UNDEFINED) {
                        $this->form_valid_status['STATUS'] = FORM_VALID_ERROR;
                    }
                    $this->form_valid_status['ELEMENTS'][] = [
                        'NAME' => $element_id, 
                        'MESSAGE' => l('CHECK_PRICE_VALUE','messages')];
                    continue;
                }
                $select = [
                    ['name' => 'Price', 'textValue' => 'AVG("t"."Price"/"t"."Count")']                        
                ];
                $from = ['TBLPRODUCTION','t'];
                $where = [
                    $where_date,
                    ['param' => 't.ModelId','staticNumber' => $model_id ],
                    ['param' => 't.Count', 'operation' => '!=','staticNumber' => 0],
                    ['param' => 't.TypeData','staticNumber' => $this->inputForm->dataBaseTypeId]
                ];
              //  $sql = \Rosagromash\SimpleSQLConstructor::generateSimpleSQLQuery($select, $from, $where);
                $builder = new QuerySelectBuilder();
                $builder->select = $select;
                $builder->from = $from;
                $builder->where = $where;
                $result = getDb()->getRows($builder);                
                $average_price = (int)$result[0]['Price'];
                $real_price = round($value_price / $value_count);
                if (empty($average_price)) {
                    continue;
                }
                if ($real_price > $average_price * $max_price || $real_price < $average_price * $min_price) {
                    if ($this->form_valid_status['STATUS'] == FORM_VALID_UNDEFINED) {
                        $this->form_valid_status['STATUS'] = FORM_VALID_ERROR;
                    }
                    $this->form_valid_status['ELEMENTS'][] = [
                        'NAME' => $element_id, 
                        'MESSAGE' => 'Возможна ошибка:<br>'.
                            'Средняя стоимость модели за предыдущие 6 мес - ' . Tools::addSpaces($average_price) .
                        ' руб.<br> Стоимость модели в форме - '.$real_price.' руб.' ];
                }                                    
                
            }
        }
        if ($this->form_valid_status['STATUS'] !== FORM_VALID_ERROR) {
            $this->form_valid_status = ['STATUS' => FORM_VALID_OK];
                
        }
        
    }
    
    
    
    
}
