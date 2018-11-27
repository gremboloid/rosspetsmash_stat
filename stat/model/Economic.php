<?php

namespace app\stat\model;

use app\stat\Convert;
/**
 * Description of Economic
 *
 * @author kotov
 */
class Economic extends AllFormTypes
{
   protected $employees;
   protected $employeesAll;
   protected $employeesConstructor;
   protected $averageSalary;
   protected $averageSalaryAll;
   
   protected static $table = "TBLECONOMIC";
   
    public function __construct($id = null, $tblchk = null) {
       parent::__construct($id, $tblchk);
       $this->template_file = 'economic';
   }
    protected function prepareHtmlForm() {
        parent::prepareHtmlForm();
        if ($this->inputForm->getId()) {            
            $sql = 'SELECT "te"."Id","te"."Employees","te"."EmployeesConstructor","te"."EmployeesAll","te"."AverageSalary","te"."AverageSalaryAll" FROM TBLECONOMIC "te"
                WHERE "te"."InputFormId" = '.$this->inputForm->getId();
        }
        else {
            $sql = 'SELECT 0 AS "Id", 0 AS "Employees", 0 AS "EmployeesConstructor", 0 AS "EmployeesAll",0 AS "AverageSalary",0 AS "AverageSalaryAll" FROM DUAL';
        }
        $economic_list = getDb()->querySelect($sql);
        if (count($economic_list) == 1) {
            $this->tpl_vars['economic_list'] = $economic_list[0];
        } else {
            $this->tpl_vars['economic_list'] = '';
        }
        $this->tpl_vars['id_economic'] = $economic_list[0]['Id'] ?? '_new';
        $this->tpl_vars['if_index'] = l('INPUT_FORM_INDEX');
        $this->tpl_vars['if_value'] = l('INPUT_FORM_VALUE');
        $this->tpl_vars['if_economic_count'] = l('INPUT_FORM_ECONOMIC_COUNT');
        $this->tpl_vars['if_economic_constructors'] = l('INPUT_FORM_ECONOMIC_CONSTRUCTORS');
        $this->tpl_vars['if_economic_salary'] = l('INPUT_FORM_ECONOMIC_SALARY');                        
    }
        /**
     * Создание новой формы ввода
     * @param array $params
     * @return array Ствтус выполнения операции
     */
    public function saveNewInputForm($params) {
        parent::saveNewInputForm($params);
        if ($this->form_valid_status['STATUS'] !== FORM_VALID_OK) {
            return $this->form_valid_status;
        }
      //  return $this->form_valid_status;
        $assoc_array = array(); 
        foreach ($params['rows_list'] as $row)
        {
            switch ($row['prod_id']) {
                case 'count':
                    $assoc_array['employeesAll'] = Convert::getNumbers($row['values_list'][0]['value']);
                    break;
                case 'salary':
                    $assoc_array['averageSalaryAll'] = Convert::getNumbers($row['values_list'][0]['value']);
                    break;
                case 'countConstructors':
                    $assoc_array['employeesConstructor'] = Convert::getNumbers($row['values_list'][0]['value']);
                    break;
            }
        }        
        $this->inputForm->setWritable();
        $new_form_id = $this->inputForm->addToDb();
        if (!$new_form_id) {
            $this->form_valid_status = [
                'STATUS' => FORM_SAVE_ERROR,
                'MESSAGE' => 'Ошибка сохранения формы'
            ];
            
            return $this->form_valid_status;
        }
        $assoc_array['inputFormId'] = $new_form_id;
        $assoc_array['currencyId'] = 1;
        $assoc_array['month'] = $this->inputForm->month;
        $assoc_array['year'] = $this->inputForm->year;
        $assoc_array['contractorId'] = $this->inputForm->contractorId;
        $assoc_array['typeData'] = $this->inputForm->dataBaseTypeId;
        $form_row = $this::getInstance($assoc_array);
        $form_row->setWritable();
        $result = $form_row->addToDb();
        if (!$result) {
            $this->form_valid_status = [
                'STATUS' => FORM_SAVE_ERROR,
                'MESSAGE' => 'Ошибка сохранения данных формы'
            ];            
           
        } 
         return $this->form_valid_status;
    }
    
    /**
     * Обновление формы
     * @param InputForm $form форма для обновления
     * @param array $params данные для обновления
     * @return int Флаг выполнения операции (0-ок,1-без изменения,2 ошибка записи)
     */
    public function updateInputForm($form,$params) 
    {
        $flag = 1;
        parent::updateInputForm($form, $params);
        if ($this->inputForm->writable) {            
            if (!$this->inputForm->updateDb()) {
                $flag = 2;
                return $flag;
            }
            $flag = 0;
        }
        $eId = Convert::getNumbers($params['eId']);
        foreach ($params['rows_list'] as $row)
        {
            switch ($row['prod_id']) {
                case 'count':
                    $employeesAll = Convert::getNumbers($row['values_list'][0]['value']);
                    break;
                case 'countConstructors':
                    $employeesConstructor = Convert::getNumbers($row['values_list'][0]['value']);
                    break;
                case 'salary':
                    $averageSalaryAll = Convert::getNumbers($row['values_list'][0]['value']);
                    break;
            }            
        }
        $e_row = new Economic($eId);
        $change_date = false;
        foreach ($params['date'] as $p_date) {            
            switch ($p_date['type']) {
                case 'month':
                    if ($e_row->month != $p_date['value']) {
                        $e_row->set('month',$p_date['value']);
                        $change_date = true;
                    }
                break;
                case 'year':
                    if ($e_row->year != $p_date['value']) {
                        $e_row->set('year',$p_date['value']);
                        $change_date = true;
                    }
                break;
            }
        }         
        if ($e_row->averageSalaryAll != $averageSalaryAll || 
                $e_row->employeesAll != $employeesAll || 
                $e_row->employeesConstructor != $employeesConstructor || $change_date ) {
            $flag = 0;
            $e_row->set('averageSalaryAll',$averageSalaryAll);
            $e_row->set('employeesAll',$employeesAll);            
            $e_row->set('employeesConstructor',$employeesConstructor);  
            if (!$e_row->updateDb()) {                 
                return [
                    'STATUS' => FORM_SAVE_ERROR,
                    'MESSAGE' => 'Ошибка сохранения данных формы'
                ];
            }                                   
        }
        return ['STATUS' => FORM_VALID_OK];                               
    }
        protected function validateForm($params) {
        //parent::validateForm($params);
        $this->form_valid_status['STATUS'] = FORM_VALID_UNDEFINED;
        if (key_exists('rows_list', $params)) {
            $elements = $params['rows_list'];
            foreach ($elements as $element) {
                $type = $element['prod_id'];
                switch ($type) {
                    case 'salary': 
                        $val = Convert::getNumbers($element['values_list'][0]['value']);
                    if ($val < 5000 && $val != 0) {
                        $this->form_valid_status['STATUS'] = FORM_VALID_ERROR;
                        $this->form_valid_status['ELEMENTS'][] = ['NAME' => $type,'MESSAGE' => l('CHECK_SALARY','messages') ];
                    }
                    break;
                }
            }
            if ($this->form_valid_status['STATUS'] !== FORM_VALID_ERROR) {
                $this->form_valid_status = ['STATUS' => FORM_VALID_OK];
                
            }
        }        
    }
}
