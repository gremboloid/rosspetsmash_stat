<?php

namespace app\stat\helpers;

use app\stat\ViewHelper;
use app\stat\Convert;
use app\stat\model\InputForm;
use app\stat\model\Country;
use app\stat\model\CountryContractorForm;
use app\stat\model\ModelContractorForm;
use app\stat\model\Models;
use app\stat\model\Production;
use app\stat\model\ContractorGeografy;
use app\stat\Tools;
use app\stat\Sessions;
/**
 * Description of ExportFormHelper
 *
 * @author kotov
 */
class ExportFormHelper 
{
    /**
     * Форма ввода
     * @var InputForm
     */
    protected  $inputForm;


    public function getCountriesForAddToForm ($assocArray) {
        extract($assocArray);
        if (isset($form_id)) {
            $form_id = Convert::getNumbers($form_id);
            $if = new InputForm($form_id);
        } else {
            $if = new InputForm();
            $if->dataBaseTypeId = $form_type;
            $if->contractorId = $contractor_id;
        }
        
        $tpl_vars = [
            'head' => "Выберите страны",
            'check_head' => "Список стран для выбора"
        ];
        
        
        $filtered_countries = array_keys($this->getCountriesList($if));
        if (count ($filtered_countries) > 0) {
            $filter = [ '"Id" NOT IN ('. implode(',', $filtered_countries) .',1)'];
        } else {        
            $filter = [ '"Id" != 1'];
        }
        
        $result_array = Country::getRowsArray(array(['Id'],['Name']),$filter,array(['name' => 'Name']));
        $tpl_vars['elements_list'] = $result_array;
        $tpl_vars['btns'] = l('BTN_ACTIONS');
        $tpl_vars['submit_id'] = 'add-country-submit';
        $view_helper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_check',$tpl_vars);
        return $view_helper->getRenderedTemplate();
    }
    
    
    protected function getCountriesList(InputForm $inputForm) {
        if ($inputForm->getId()) {
         $sql = 'SELECT DISTINCT "tc"."Id","tc"."Name","tc"."Iso" FROM TBLCOUNTRY "tc" 
                WHERE "tc"."Id" IN ( SELECT DISTINCT tp."CountryId" FROM 
                      TBLPRODUCTION tp WHERE tp."InputFormId" = '.$inputForm->getId().')  ORDER BY "tc"."Name"';
        } else {
        $sql = 'SELECT DISTINCT "tc"."Id","tc"."Name","tc"."Iso" FROM TBLCOUNTRYCONTRACTORFORM "tcc",TBLCOUNTRY "tc" 
                WHERE "tcc"."CountryId"= "tc"."Id" AND "tcc"."ContractorId" = '.$inputForm->contractorId.' 
                    AND "tc"."Id" != 1 AND "tcc"."FormTypeId" = '.$inputForm->dataBaseTypeId.' ORDER BY "tc"."Name"';
        }
        
        return Tools::SplittingArray (getDb()->querySelect($sql),'Id',false);
    }
    /**
     * Добавить новые стрвны в форму
     * @param type $assocArray
     */
    public function addCountriesToForm($assocArray)
    { 
        extract($assocArray); // #countries_list,$form_type,$contractor_id,$form_id (?)
        if (count ($countries_list) < 1) {
            return;
        }
        $countries_str = implode(',', $countries_list);
        $form_type = (int) $form_type;
        $contractor_id = (int) $contractor_id;
        if (isset($form_id)) {
            $tpl_vars['if_id'] = $form_id;
            $form_id = Convert::getNumbers($form_id);         
            $this->inputForm = new InputForm($form_id);
            if ($this->inputForm->getModelsCount() == 0) {
                $countries_from_session = Sessions::getVarSession('TMP_COUNTRIES_LIST',[]);
                foreach ($countries_list as $country) {
                    if (!in_array($country, $countries_from_session)) {
                       array_push($countries_from_session,$country);
                    }
                }
                Sessions::setValueToVar('TMP_COUNTRIES_LIST', $countries_from_session);
            }
            $form_type = $this->inputForm->dataBaseTypeId;
            foreach ($countries_list as $country) {
            // добавление моделей для страны в выбранную форму           
                $sql = 'INSERT INTO TBLPRODUCTION (
                    "ModelId", "Month", "Year", "Count", "CountryId", "Price", "RegionId", "TypeData", "CurrencyId","ContractorId","InputFormId")
                    SELECT DISTINCT "tp"."ModelId","tp"."Month","tp"."Year", 0 AS "Count", '.$country.' AS "CountryId", 0 AS "Price", "tp"."RegionId", "tp"."TypeData",
                    "tp"."CurrencyId","tp"."ContractorId","tp"."InputFormId" FROM TBLPRODUCTION "tp" WHERE
                    "tp"."ContractorId" = '. $contractor_id .' AND "tp"."InputFormId" = '.$form_id;
                getDb()->dbQuery($sql);
                $formcountry = CountryContractorForm::getInstance([
                    'contractorId' => $contractor_id,
                    'countryId' => $country, 
                    'formTypeId' => $form_type
                ]);        
                $formcountry->addToDb();
            }
            
        } else {
            $this->inputForm = new InputForm();
            foreach ($countries_list as $country) {
                $formcountry = CountryContractorForm::getInstance([
                    'contractorId' => $contractor_id,
                    'countryId' => $country, 
                    'formTypeId' => $form_type
                ]);        
                $formcountry->addToDb();
            }
        }
        
        
        
        if (isset($form_id)) {
        $sql = 'SELECT "final"."Rank","final"."CountryId","final"."ProductionId","final"."Classifier","final"."ClassifierId","final"."ModelId","final"."Model",
                Sum("final"."Count") AS "Count",Sum("final"."Price") AS "Price" FROM  ( SELECT "grp"."Rank",DECODE (GROUPING ("grp"."CountryId"),1,0,"grp"."CountryId") AS "CountryId",DECODE (GROUPING ("grp"."CountryId"),1,Null,"grp"."ProductionId") AS "ProductionId","grp"."Classifier","grp"."ClassifierId","grp"."ModelId","grp"."Model", SUM ("grp"."Count") AS "Count", SUM ("grp"."Price") AS "Price" FROM (
                SELECT  DENSE_RANK() OVER (PARTITION BY "tb2"."CountryId" ORDER BY "tb1"."ClassifierId") AS "Rank","tb2"."ProductionId","tb2"."CountryId","tb1"."ClassifierId","tb1"."Name" AS "Classifier","tb1"."ModelId","tb1"."Model","tb2"."Count","tb2"."Price" FROM 
                ( SELECT "cl"."Id" AS "ClassifierId","cl"."Name","res"."Id" AS "ModelId","res"."Name" AS "Model","res"."Approved" FROM (
                WITH "TREE" AS ( SELECT "TBLCLASSIFIER".*, LEVEL AS "Level" FROM "TBLCLASSIFIER"
                CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "ClassifierId" IS NULL )
                SELECT "ctree"."ClassId" AS "ClassifierId","TBLMODEL"."Id" AS "Id","TBLMODEL"."Name" AS "Name",
                "TBLMODEL"."Approved" AS "Approved" FROM  (
                SELECT "TREE"."Id" as "Id", CONNECT_BY_ROOT("TREE"."Id") as "ClassId" FROM TREE CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Level" = 3
                ) "ctree", "TBLMODEL" WHERE  "ctree"."Id" = "TBLMODEL"."ClassifierId"  AND
                "TBLMODEL"."Id" IN ( SELECT DISTINCT "ModelId" FROM "TBLPRODUCTION" "tp" WHERE "tp"."InputFormId" = '.$form_id.') ORDER BY "ClassifierId","Name" DESC ) "res",TBLCLASSIFIER "cl"
                WHERE "res"."ClassifierId" = "cl"."Id" AND "res"."Approved" = 1 ) "tb1",
                ( SELECT "tp"."Id" AS "ProductionId","tp"."CountryId","tp"."ModelId","tp"."Count","tp"."Price" FROM TBLPRODUCTION "tp" WHERE "tp"."InputFormId" = '.$form_id.' AND "tp"."CountryId" IN ('.$countries_str.')) "tb2"
                WHERE "tb1"."ModelId" = "tb2"."ModelId" ) "grp"
                GROUP BY "grp"."Rank","grp"."ProductionId","grp"."Classifier","grp"."ClassifierId","grp"."Model","grp"."ModelId", ROLLUP ("grp"."CountryId") ) "final"
                WHERE "final"."CountryId" != 0
                GROUP BY "final"."Rank","final"."CountryId","final"."ProductionId","final"."Classifier","final"."ClassifierId","final"."ModelId","final"."Model"
                ORDER BY "final"."CountryId"  NULLS FIRST,"final"."Rank","final"."Model"';
        } else {
            if ($form_type == 5) {
                $filter = ' AND "TBLMODEL"."ModelTypeId" = 3 ';
            } else {
                $filter = '';
            }
            $sql = 'SELECT "q1"."Rank","tcf"."CountryId","q1"."Classifier","q1"."ClassifierId","q1"."ModelId","q1"."Model",0 AS "Count",0 AS "Price" FROM (
                SELECT DENSE_RANK() OVER (ORDER BY "cl"."Id") AS "Rank", "cl"."Id" AS "ClassifierId","cl"."Name" AS "Classifier",
                "res"."Id" AS "ModelId","res"."Name" AS "Model",0 AS "Count",0 AS "Price" FROM (
                WITH "TREE" AS ( SELECT "TBLCLASSIFIER".*, LEVEL AS "Level" FROM
                "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "ClassifierId" IS NULL )
                SELECT "ctree"."ClassId" AS "ClassifierId","TBLMODEL"."Id" AS "Id","TBLMODEL"."Name" AS "Name","TBLMODEL"."Approved" AS "Approved"
                FROM ( SELECT "TREE"."Id" as "Id", CONNECT_BY_ROOT("TREE"."Id") as "ClassId" FROM TREE CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Level" = 3  ) "ctree",
                "TBLMODEL" WHERE "ctree"."Id" = "TBLMODEL"."ClassifierId"'.$filter.' AND "TBLMODEL"."Id" IN 
                ( SELECT "ModelId" FROM "TBLMODELCONTRACTORFORM" WHERE "ContractorId" = '.$contractor_id.' ) 
                    ORDER BY "ClassifierId","Name" DESC ) "res",TBLCLASSIFIER "cl"
                WHERE "res"."ClassifierId" = "cl"."Id" AND "res"."Approved" = 1 ) "q1", (SELECT * FROM (
                    SELECT * FROM TBLCOUNTRYCONTRACTORFORM UNION SELECT '.$contractor_id.',0,'.$form_type.' FROM DUAL)) "tcf"
                WHERE "tcf"."CountryId" IN ('.$countries_str.') AND "tcf"."ContractorId" = '.$contractor_id.'  AND "tcf"."FormTypeId" = '.$form_type.' ORDER BY "tcf"."CountryId","q1"."Rank","q1"."Model"';
            
            
        }
        $list_models = Tools::SplittingArray (getDb()->querySelect($sql),'CountryId');
        
        $where = 'WHERE "tc"."Id" IN ('. $countries_str . ')';
        $sql = 'SELECT DISTINCT "tc"."Id","tc"."Name","tc"."Iso" FROM TBLCOUNTRY "tc" '.$where;
        $list_of_countries = Tools::SplittingArray (getDb()->querySelect($sql),'Id',false);
        $tpl_vars['list_of_countries'] = $list_of_countries;
        $tpl_vars['list_models']= $list_models;
        $tpl_vars['summary_header'] = l('INPUT_FORM_EXPORT_ALL');
        $view_helper = new ViewHelper(_FORMS_TEMPLATES_DIR_,'country_block',$tpl_vars);        
        
        return $view_helper->getRenderedTemplate();
    }
    public function removeCountry($assortArray) {
        extract($assortArray);
        if (empty($input_form_id)) { 
            $input_form_id = null;
        }
        $input_form = new InputForm($input_form_id);
        $form_type = !empty($form_type) ? (int) $form_type : $input_form->dataBaseTypeId ;       
        $contractor_id = (int) $contractor_id;
        $country_id = (int) $country_id;
        $input_form_id = (int) $input_form_id;

        $deleted = CountryContractorForm::deleteByProps(['contractorId' => $contractor_id,
            'countryId' => $country_id, 'formTypeId' => $form_type]);
       // $formcountry->delete();
        if ($input_form_id) {
            $sql = 'DELETE FROM "TBLPRODUCTION" "tp" WHERE "tp"."InputFormId" = '.$input_form_id.' AND "tp"."CountryId" = '.$country_id;            
            $deleted = getDb()->dbQuery($sql);
        } 
        if ($deleted) {
            return Tools::getMessage('ok');
        }
        return Tools::getErrorMessage('Ошибка записи в БД');
    }
        /**
    * Добавить новые модели в форму
    * @param array $assocArray
    * @return type
    */
    public function addModelToForm($assocArray) {
        extract($assocArray);
        $assocArray = array();
        //$aResult = array( 'models_list' => array());
        if (isset($formId)) {
            $formId =  Convert::getNumbers($formId);
        }
        $contractorId = Convert::getNumbers($contractorId);
        
        if (count($models_list) < 1) {
            echo json_encode( $aResult);
            return;
        }
        if (!$contractorId) {
            echo json_encode( $aResult);
            return;            
        }  
        
        foreach ($models_list as $idx => $modelId) {
            $mcf = ModelContractorForm::getInstance( array(
                'contractorId' => $contractorId,
                'modelId' => Convert::getNumbers($modelId)
            ));
            $mcf->setFieldsToUpdate( ['ContractorId','ModelId']);
            $mcf->writable = true;
            $mcf->updateDb();
            $model = new Models($modelId);
            $model_name = $model->getName();                     
            $aResult['models_list'][$idx] = [
                'model_id' => $modelId,
                'model_name' => $model_name,
                'rows' => array()
            ];
            $aResult['type'] = 2;                                    
            if ($formId) {
             //  $aResult['models-list'][$idx]['rows'] = array();
              //  $production = new Production();
                $inputForm = new InputForm($formId);
                $contractorGeografy = new ContractorGeografy($inputForm->contractorId);                
                $listOfCountries = array_keys($this->getCountriesList($inputForm));
                if (count($listOfCountries) == 0) {
                    $listOfCountries = Sessions::getVarSession('TMP_COUNTRIES_LIST', []);
                }
                foreach ($listOfCountries as $countryId) {
                    $assoc_array = array(); 
                    $assoc_array['inputFormId'] = $formId;
                    $assoc_array['currencyId'] = 1;
                    $assoc_array['month'] = $inputForm->month;
                    $assoc_array['year'] = $inputForm->year;
                    $assoc_array['contractorId'] = $inputForm->contractorId;
                    $assoc_array['regionId'] = $contractorGeografy->regionId;
                    $assoc_array['typeData'] = $inputForm->dataBaseTypeId;
                    $assoc_array['countryId'] = $countryId;
                    $assoc_array['count'] = 0;
                    $assoc_array['price'] = 0;
                    $assoc_array['modelId'] = $modelId;
                    $production = Production::getInstance($assoc_array);
                    $production->setWritable();
                    $result = $production->addToDb();
                  // $result = 1111;
                    unset ($production);
                    $aResult['models_list'][$idx]['rows'][] = ['country' => $countryId,'production' => $result];
                   // $aResult[]
                    
                    
                }
                $aResult['form_id'] = $formId;
               // $production = Production::getInstance('')
            } else {
                $sql =  CountryContractorForm::generateSelect(array('"CountryId"'),[['param' => 'ContractorId', 'staticNumber' => $contractorId ],
                                                                                          ['param' => 'FormTypeId', 'staticNumber' => $type ] ]);
                $country_list = getDb()->querySelect($sql);
                foreach ($country_list as $country) {
                    $aResult['models_list'][$idx]['rows'][] = ['country' => $country['CountryId']];
                }
            }
                
            
        }
        return json_encode($aResult);
    }
    
}
