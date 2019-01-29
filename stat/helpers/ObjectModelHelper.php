<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\helpers;


use app\stat\model\ObjectModel;
use app\stat\Tools;
/**
 * Description of ModelsHelper
 *
 * @author User
 */
class ObjectModelHelper 
{
    const ALL_MODELS = 0;
    const ONLY_RUSSIAN = 1;
    const ONLY_IMPORT = 2;
    const MODELS_WITHOUT_IMPORT = 3;
    
    /**
     * Модель использууемая в хелпере
     * @var ObjectModel
     */
    protected $model;
    
    public static function getInstance(ObjectModel $model) {
        $result = new self();
        $result->setModel($model);
        return $result;               
    }
    
    public function setModel(ObjectModel $model) {
        $this->model = $model;
    }
    public function getModelsListByContractor($contractorId,$classifierId = 41,$filter = '',$flag = self::ALL_MODELS) {
     //   $classifierList = \Rosagromash\Tools::getValuesFromArray(Classifier::getClassifierHierarchy($classifierId,true),'Id');
      /*  if (count ($classifierList) < 1) {
            return array();
        }*/
        $where = '';
        if ($flag == self::ONLY_IMPORT) {
            $where = ' AND "m"."ModelTypeId" = 3';
        }
        if ($flag == self::MODELS_WITHOUT_IMPORT) {
            $where = ' AND "m"."ModelTypeId" != 3';
        }
        
        $sql = 'SELECT "m"."Id","m"."Name" FROM "TBLMODEL" "m","TBLBRAND" "b" 
  WHERE "b"."Id" = "m"."BrandId"
  AND "b"."ContractorId" = '. $contractorId.$where;

        
  //// .
 // ' AND "m"."ClassifierId" IN ('.implode(',',$classifierList).')';
        if ($filter) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Id" NOT IN ('.$filter.')';
        }
        $aResult = getDb()->querySelect($sql);
        return $aResult;
        
    }
    public function getInfoBlock() {
        if (!$this->model) {
            return Tools::getErrorMessage('Объект не сконфигурирован');
        }
        if(!$this->model->isInfoblockExist()) {
            return Tools::getErrorMessage('Объект не содержит информационный блок');
        }
        return $this->model->displayInfoBlock();
    }
}
