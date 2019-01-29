<?php

namespace app\stat\helpers;


use app\stat\model\InputForm;
use app\stat\Convert;
use app\stat\Tools;
use app\stat\Sessions;
use app\stat\model\Production;
use app\stat\model\Economic;
use app\stat\model\Export;
use app\stat\model\ModelContractorForm;
use app\stat\model\CountryContractorForm;
use app\stat\model\ContractorGeografy;
use app\stat\model\Models;
use app\stat\helpers\ObjectModelHelper;
use app\stat\ViewHelper;
/**
 * Description of FormsHelper
 *
 * @author kotov
 */
class FormsHelper {
    
    
    public function getNewFormHtml($formType,$period,$contractor=0)
    {
        $form = new InputForm();
        $form->dataBaseTypeId = Convert::getNumbers($formType);
        $form->contractorId = Convert::getNumbers($contractor);
        $form->month = Convert::getNumbers($period['month']);
        $form->year = Convert::getNumbers($period['year']);      
        if ($this->isFormExist($form)) {
            return Tools::getErrorMessage('Форма уже существует');
        }
        $prd = new \DateTime();
        $prd->setDate($form->year, $form->month, 1);
        return $form->getFormHtml($prd);  
    }
    /**
     * Проверка, существует ли указанная форма
     * @param InputForm $form
     * @return type
     */
    public function isFormExist(InputForm $form) {
        $form_exist = InputForm::getRowsCount([ ['param' => 'DataBaseTypeId', 'staticNumber' => $form->dataBaseTypeId ],
            ['param' => 'ContractorId', 'staticNumber' => $form->contractorId ],
            ['param' => 'Month', 'staticNumber' => $form->month ],
            ['param' => 'Year', 'staticNumber' => $form->year ] ]);
        return ($form_exist > 0) ? true : false;        
    }
    
    /**
     * Проверка существования формы с заданными данными
     * @param type $formData
     * @param type $return_json
     * @return type
     */
    public function checkFormExist($formData,$return_json=true) {
        $aResult = array();
        parse_str($formData,$form_elements);
        if (!key_exists('contractorId', $form_elements) ||
                !key_exists('startMonth', $form_elements) ||
                !key_exists('startYear', $form_elements) ||
                !key_exists('typeId', $form_elements)) {
            return Tools::getErrorMessage( l('REQUEST_PARAMS_ERROR','messages'),1,$return_json);
        }        
        $form_props = array(
            'contractorId' => $form_elements['contractorId'],
            'month' => $form_elements['startMonth'],
            'year' => $form_elements['startYear'],
            'dataBaseTypeId' => $form_elements['typeId']
        );
        $form_status = Sessions::getVarSession('FORM_STATUS');
        if (!$form_status) {
            return Tools::getErrorMessage( l('REQUEST_PARAMS_ERROR','messages'),1,$return_json);                      
        }
        if ($form_status == 'open') {
            $def_month = Sessions::getVarSession('DEFAULT_FORM_MONTH');
            $def_year = Sessions::getVarSession('DEFAULT_FORM_YEAR');
            if (!$def_month || !$def_year) {
                return Tools::getErrorMessage( l('REQUEST_PARAMS_ERROR','messages'),1,$return_json);
            }
            if ($form_props['month'] == $def_month && $form_props['year'] == $def_year) {
                return Tools::getMessage('ok', $return_json);
            }            
        }
        $if = InputForm::getInstance($form_props);
        if ($this->isFormExist($if)) {
            return Tools::getErrorMessage( l('ERROR_FORM_IS_EXIST','messages'),2,$return_json);
        } else {
            return Tools::getMessage('ok', $return_json);               
        }
    }
    
    
    public function getCountriesList(InputForm $inputForm) {
        if ($inputForm->getId()) {
         $sql = 'SELECT DISTINCT "tc"."Id","tc"."Name","tc"."Iso" FROM TBLCOUNTRY "tc" 
                WHERE "tc"."Id" IN ( SELECT DISTINCT tp."CountryId" FROM 
                      TBLPRODUCTION tp WHERE tp."InputFormId" = '.$inputForm->getId().')  ORDER BY "tc"."Name"';
        } else {
        $sql = 'SELECT DISTINCT "tc"."Id","tc"."Name","tc"."Iso" FROM TBLCOUNTRYCONTRACTORFORM "tcc",TBLCOUNTRY "tc" 
                WHERE "tcc"."CountryId"= "tc"."Id" AND "tcc"."ContractorId" = '.$inputForm->contractorId.' 
                    AND "tc"."Id" != 1 AND "tcc"."FormTypeId" = '.$inputForm->dataBaseTypeId.' ORDER BY "tc"."Name"';
        }
        
        return Tools::splittingArray (getDb()->querySelect($sql),'Id',false);
    }  
    public function getFormHtmlById($id=null)
    {
        $form = new InputForm($id);
        $prd = new \DateTime();
        $prd->setDate($form->year, $form->month, 1);        
        return $form->getFormHtml($prd);
    }
     /**
     * 
     * @param type $ifOblect объект JSON содержащий в себе данные формы для сохранения
     */
    public function saveInputForm($ifOblect) {
        $paramsArray = json_decode($ifOblect,true);
       // return serialize($paramsArray);
        $result=false;
        if ($paramsArray) {
            $paramsArray['formType'] = Convert::getNumbers($paramsArray['formType']);
            switch ($paramsArray['formType'])
            {
                case 1:
                case 2:
                    $formData = new Production();
                    break;
                case 4:
                case 5:
                    $formData = new Export();
                    break;
                case 12:
                    $formData = new Economic();
                    break;
                default:              
            }
            if ($formData) {
                switch ($paramsArray['action'])
                {
                    case 'opened_form':
                        $formId = (int) $paramsArray['formId'];
                        $form = new InputForm($formId);
                        $result = $formData->updateInputForm($form,$paramsArray);                        
                        break;
                    case 'new_form':
                        $result = $formData->saveNewInputForm($paramsArray);
                        break;
                    default:                                           
                }                
            }            
        }
        //$msg = InputForm::getErrorMessages($err_code);
        return json_encode ($result);
    }
    /**
     * Получить список моделей доступных для доьавления в форму
     * @param type $assocArray
     * @return type
     */
    public function getModelsForAddToForm ($assocArray)
    {
        extract($assocArray);
        //$classifier = \Rosagromash\Convert::getNumbers($classifier);
        $classifier = 41;
        $contractor = Convert::getNumbers($contractor);
        $form_type = Convert::getNumbers($type);
        if (isset($formId)) {
            $formId = Convert::getNumbers($formId);
        }
        if (empty($classifier) || empty($contractor)) {
            return;
        }
        switch ($form_type) {
            case 1:
            case 2:
                $mt_flag = ObjectModelHelper::MODELS_WITHOUT_IMPORT;
                break;
            case 5:
                $mt_flag = ObjectModelHelper::ONLY_IMPORT;
                break;
            default:
                $mt_flag = ObjectModelHelper::ALL_MODELS;
                break;
        }
        $modelsHelper = new ObjectModelHelper();
        //filtered_models = \Rosagromash\Tools::getValuesFromArray(ModelContractorForm::getRowsArray(null,null,null,[['param' => 'ContractorId','bindingValue' => $contractor]]),'ModelId');
        $filtered_models = Tools::getValuesFromArray($modelsHelper->getModelsListByContractor($contractor,$classifier,null,$mt_flag),'Id');

        if ($formId) {
            $sql = Production::generateSelect(array('"ModelId"'),[['param' => 'InputFormId', 'staticNumber' => $formId]]);
            $models_list = Tools::getValuesFromArray(getDb()->querySelect($sql), 'ModelId');
            //$models_list =  getDb()->querySelect($sql);
        } else {
            $models_list =  Tools::getValuesFromArray(ModelContractorForm::getRowsArray(array(),null,null,[['param' => 'ContractorId','bindingValue' => $contractor]]),'ModelId');
        }
        $filtered_models = array_filter($filtered_models,function($el) use ($models_list) {
           return !in_array($el, $models_list) ;
        });
        if (count($filtered_models) > 0) {
            $sql = ' SELECT DENSE_RANK() OVER (ORDER BY "tb"."ClassifierId") AS "Rank","tb".* FROM (
     WITH "TREE" AS (SELECT "TBLCLASSIFIER".*,
                            LEVEL AS "Level"
           FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "ClassifierId" IS NULL)
     SELECT "ctree"."ClassId" AS "ClassifierId",
            "ClassifierName",
            "TBLMODEL"."Id" AS "Id",
            "TBLMODEL"."Name" AS "Name",
            "TBLMODEL"."Approved" AS "Approved"
       FROM (SELECT "TREE"."Id" AS "Id",
                    CONNECT_BY_ROOT ("TREE"."Name") AS "ClassifierName",
                    CONNECT_BY_ROOT ("TREE"."Id") AS "ClassId"
                FROM "TREE" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Level" = 3) "ctree",
            "TBLMODEL"
       WHERE "ctree"."Id" = "TBLMODEL"."ClassifierId"
         AND "TBLMODEL"."Id" IN ('.implode(',', $filtered_models).')
      ) "tb"';
            $models = getDb()->querySelect($sql);
           /* $b = array_map(function ($el) {
                return $el['Rank'];
            }, $models);*/
            $result_array = array();
         foreach ($models as $el_models) {
             $result_array[$el_models['Rank']][] = $el_models;

         }
         unset($el_models);
        } else {
            $result_array = [];
        }
         
        //return $result_array;
        
  /*
        if (count($filtered_models) > 0) {
            $filter = implode(',', $filtered_models);
            $where = [ [ 'param' => 'Id',
                        'operation' => 'IN',
                        'pureValue' => '('. $filter. ' )'] ]; 
            $all_models = Models::getRowsArray(null,null,null,$where);
        } else {
            $all_models = [];
        }
        */
        
       // $all_models = Models::getModelsListByContractor($contractor,$classifier,$filter);
       
        $tpl_vars = [
            'head' => "Выберите модели",
            'check_head' => "Список доступных моделей"
        ];
        $tpl_vars['tree_elements_list'] = $result_array;
        $tpl_vars['btns'] = l('BTN_ACTIONS');
        $tpl_vars['submit_id'] = 'add-model-submit';        
        
        
        $viewHelper = new ViewHelper(_MODAL_TEMPLATES_DIR_,'modal_check',$tpl_vars);
        $message = $viewHelper->getRenderedTemplate();
        return Tools::getMessage($message);
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
            $aResult['type'] = 1;                        
            if ($formId) {
             //  $aResult['models-list'][$idx]['rows'] = array();
              //  $production = new Production();
                $inputForm = new InputForm($formId);
                $contractorGeografy = new ContractorGeografy($inputForm->contractorId);                
                $listOfCountries = array(1);
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
        
        //print_ar ($assocArray);
        
    }
    
    
}
