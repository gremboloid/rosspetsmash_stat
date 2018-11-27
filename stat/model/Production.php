<?php

namespace app\stat\model;


use app\stat\Convert;

/**
 * Description of Production
 *
 * @author kotov
 */
class Production extends AllFormTypes
{
    protected $modelId;
    protected $count;
    protected $countryId;
    protected $regionId;
    protected $price;    
    protected $contractId;
    protected $subsidy;
    protected $discount;  

    protected static $table = 'TBLPRODUCTION';
    
    public function __construct($id = null, $tblchk = null) {
        parent::__construct($id, $tblchk);
        $this->template_file = 'production';
        $this->not_nulls = array_merge($this->not_nulls,['count','price', 'regionId','contactId']);
    } 
    protected function prepareHtmlForm() {
        parent::prepareHtmlForm();
        $form_id = $this->inputForm->getId();
        if ($form_id) {            
            $sql = 'SELECT "tb1"."Rank","tb2"."ProductionId","tb1"."ClassifierId", "tb1"."Name" AS "Classifier","tb1"."ModelId","tb1"."Model","tb2"."Count",Round("tb2"."Price",0) AS "Price" FROM (
                SELECT DENSE_RANK() OVER (ORDER BY "cl"."Id") AS "Rank", "cl"."Id" AS "ClassifierId","cl"."Name","res"."Id" AS "ModelId","res"."Name" AS "Model","res"."Approved" FROM (
                WITH "TREE" AS (
                SELECT "TBLCLASSIFIER".*,LEVEL AS "Level" FROM
                "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "ClassifierId" IS NULL )
                SELECT "ctree"."ClassId" AS "ClassifierId","TBLMODEL"."Id" AS "Id","TBLMODEL"."Name" AS "Name",
                "TBLMODEL"."Approved" AS "Approved" FROM 
                ( SELECT "TREE"."Id" as "Id", CONNECT_BY_ROOT("TREE"."Id") as "ClassId" FROM TREE CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Level" = 3  ) "ctree",
                "TBLMODEL" WHERE "ctree"."Id" = "TBLMODEL"."ClassifierId"  AND "TBLMODEL"."ModelTypeId" != 3 AND
                "TBLMODEL"."Id" IN ( SELECT "ModelId" FROM "TBLPRODUCTION" "tp" WHERE "tp"."InputFormId" = '.$this->inputForm->getId().' ) ORDER BY "ClassifierId","Name" DESC ) "res",TBLCLASSIFIER "cl"
                WHERE "res"."ClassifierId" = "cl"."Id" AND "res"."Approved" = 1) "tb1",
                (SELECT "tp"."Id" AS "ProductionId","tp"."ModelId","tp"."Count","tp"."Price" FROM TBLPRODUCTION "tp" WHERE "tp"."InputFormId" = '.$this->inputForm->getId().') "tb2"
                WHERE "tb1"."ModelId" = "tb2"."ModelId"';
            //$user = new User();                                        
            }
        else {
            $sql = 'SELECT DENSE_RANK() OVER (ORDER BY "cl"."Id") AS "Rank", "cl"."Id" AS "ClassifierId","cl"."Name" AS "Classifier","res"."Id" AS "ModelId","res"."Name" AS "Model",0 AS "Count",0 AS "Price" FROM (
                WITH "TREE" AS ( SELECT "TBLCLASSIFIER".*, LEVEL AS "Level" FROM "TBLCLASSIFIER"
                CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "ClassifierId" IS NULL )
                SELECT "ctree"."ClassId" AS "ClassifierId", "TBLMODEL"."Id" AS "Id", "TBLMODEL"."Name" AS "Name", "TBLMODEL"."Approved" AS "Approved"
                    FROM ( SELECT "TREE"."Id" as "Id", CONNECT_BY_ROOT("TREE"."Id") as "ClassId" FROM TREE CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Level" = 3  ) "ctree",
                "TBLMODEL" WHERE "ctree"."Id" = "TBLMODEL"."ClassifierId" AND
                "TBLMODEL"."ModelTypeId" != 3 AND "TBLMODEL"."Id" IN ( SELECT "ModelId" FROM "TBLMODELCONTRACTORFORM" "tmc" WHERE "tmc"."ContractorId" = '.$this->inputForm->contractorId.' ) ORDER BY "ClassifierId","Name" DESC ) "res",TBLCLASSIFIER "cl"
                WHERE "res"."ClassifierId" = "cl"."Id" AND "res"."Approved" = 1';
        }
        $this->tpl_vars['add_model'] = l('ADD_MODEL');
        $this->tpl_vars['if_model'] = l('INPUT_FORM_MODEL');
        $this->tpl_vars['if_count'] = l('INPUT_FORM_COUNT');
        $this->tpl_vars['if_price'] = l('INPUT_FORM_PRICE');
        $this->tpl_vars['if_id'] = $form_id;
        $this->tpl_vars['REMOVE_MODEL_FROM_FORM'] = l('REMOVE_MODEL_FROM_FORM');
        $this->tpl_vars['list_models'] = getDb()->querySelect($sql);
        $this->tpl_vars['currency'] = ' '. l('DEFAULT_CURRENCY'). ' ('.l('NDS').')';
        $this->tpl_vars['amount'] = ' '. l('DEFAULT_AMOUNT');
    }
    /**
     * Создание новой формы ввода
     * @param array $params
     * @return int Флаг выполнения операции (0-ок,2 ошибка)
     */
    public function saveNewInputForm($params) {
        parent::saveNewInputForm($params);
        if ($this->form_valid_status['STATUS'] !== FORM_VALID_OK) {
            return $this->form_valid_status;
        }
      //  return $this->form_valid_status;
        $this->inputForm->setWritable();
        $new_form_id =  $this->inputForm->addToDb();
        if (!$new_form_id) {
            return 2;
        }
        $list_of_productions = array();
        $query = 'SELECT "r"."Id" AS "Region","cnt"."Id" AS "Country" FROM TBLCONTRACTOR "tc",TBLCITY "cty",TBLREGION "r",TBLCOUNTRY "cnt"
            WHERE "tc"."Id" = '.$this->inputForm->contractorId.' AND "cty"."Id" = "tc"."CityId" AND "r"."Id" = "cty"."RegionId" AND "r"."CountryId"="cnt"."Id"';
        $res = getDb()->querySelect($query);
        if (count($res) == 0) {
            return 0;            
        }
        else {
            $region = $res[0];
        }
        
        if ($region['Country'] != 1) {
            $region['Country'] = 1;
            $region['Region'] = 1;
        }
        
        foreach ($params['rows_list'] as $row)
        {            
            $assoc_array = array(); 
            $assoc_array['inputFormId'] = $new_form_id;
            $assoc_array['currencyId'] = 1;
            $assoc_array['month'] = $this->inputForm->month;
            $assoc_array['year'] = $this->inputForm->year;
            $assoc_array['contractorId'] = $this->inputForm->contractorId;
            $assoc_array['regionId'] = $region['Region'];
            $assoc_array['typeData'] = $this->inputForm->dataBaseTypeId;
            
            preg_match_all('/\d+/', $row['row'], $model_nfo);
            if (count ($model_nfo[0]) == 0) return false;
             $assoc_array['modelId'] = $model_nfo[0][0];
            if (count($model_nfo[0]) > 1) {
                $assoc_array['countryId'] = $model_nfo[0][1];
            }
            else {
                $assoc_array['countryId'] = 1;
            }
         //   $assoc_array['modelId'] = \Rosagromash\Convert::getNumbers($row['row']);
            foreach ($row['values_list'] as $value) 
            {
                switch ($value['type'])
                {
                    case 'count':
                        $assoc_array['count'] = Convert::getNumbers($value['value']);
                        break;
                    case 'price':
                        $assoc_array['price'] = Convert::getNumbers($value['value']);
                        break;
                }
            }
            
            $form_row = $this::getInstance($assoc_array);
            $form_row->setWritable();
            $result = $form_row->addToDb();
            if (!$result) {
                getDb()->dbQuery('ROLLBACK');
                return [
                    'STATUS' => FORM_SAVE_ERROR,
                    'MESSAGE' => 'Ошибка сохранения данных формы'
                ];
                
            }            
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
        foreach ($params['rows_list'] as $row)
        {
            preg_match_all("/(?P<id>\d+)_?(?P<country>\d+)?/", $row['prod_id'],$matches);
            $prodId = Convert::getNumbers($matches['id'][0]);
             foreach ($row['values_list'] as $value) 
            {
                switch ($value['type'])
                {
                    case 'count':
                        $count = Convert::getNumbers($value['value']);
                        break;
                    case 'price':
                        $price = Convert::getNumbers($value['value']);
                        break;
                }
            }
            $prod_row = new Production($prodId);
            $change_date = false;
            foreach ($params['date'] as $p_date) {            
                switch ($p_date['type']) {
                    case 'month':
                    if ($prod_row->month != $p_date['value']) {
                        $prod_row->set('month',$p_date['value']);
                        $change_date = true;
                    }
                    break;
                case 'year':
                    if ($prod_row->year != $p_date['value']) {
                        $prod_row->set('year',$p_date['value']);
                        $change_date = true;
                    }
                    break;
                }
            }            
            if ($count != $prod_row->count || $price != $prod_row->price || $change_date) {
                $flag = 0;
                $prod_row->__set ('count', $count);
                $prod_row->__set('price', $price);
                if (!$prod_row->updateDb()) {                 
                    return [
                        'STATUS' => FORM_SAVE_ERROR,
                        'MESSAGE' => 'Ошибка сохранения данных формы'
                    ];
                }
            }                        
        }
        return ['STATUS' => FORM_VALID_OK];        
    }
}
