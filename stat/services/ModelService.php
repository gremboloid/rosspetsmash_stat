<?php

namespace app\stat\services;

use app\stat\model\Brand;
use app\stat\model\Contractor;
use app\stat\services\ClassifierService;
use app\stat\model\ModelRequest;
use app\stat\model\Models;
use app\stat\model\ModelType;
use app\stat\ViewHelper;
use app\stat\Mailer;
/**
 * Description of ModelService
 *
 * @author kotov
 */
class ModelService {
    
    /**
     * Возвращает список моделей
     * @param type $datasources
     * @param type $classifierid
     * @param type $filter
     * @param bool $present
     * @param string $limit
     * @return array 
     */
     public static function getModelsList(string $datasources,$classifier,$filter='',$presents=true,$limit='',$modelType=array())
    {
         $modelTypeFilter = '';
         if (!empty($modelType) && count($modelType) < 3) {
             $modelTypeFilter = ' AND "m"."ModelTypeId" IN ('. implode(',', $modelType).')';
         }
        $classifierFilter = '';
        if (is_array($classifier)) {
            foreach ($classifier as $id) {
                $classifierFilter .= 'SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$id;
                $classifierFilter .= ' UNION ';
            }
            $classifierFilter = trim($classifierFilter,' UNION ');
        } else {
            $classifierFilter = 'SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$classifier;
        }
        // if (is_array($c))
        $sql = 'SELECT "TBLMODEL"."Id" AS "Id","TBLMODEL"."BrandId","TBLMODEL"."ModelTypeId", "TBLMODEL"."Name" AS "Name" FROM (
            SELECT DISTINCT "ModelId" FROM "TBLPRODUCTION" WHERE "TypeData" IN ('.$datasources.')) "mdl_prod", 
            "TBLMODEL" WHERE "TBLMODEL"."Id" = "mdl_prod"."ModelId" AND "TBLMODEL"."ClassifierId" IN  
            ( '.$classifierFilter.') ORDER BY "TBLMODEL"."Name" ASC';
        $sql = 'SELECT "m".*,"c"."Id" AS "ContractorId","c"."Present" FROM ('.$sql.') "m","TBLCONTRACTOR" "c","TBLBRAND" "b"
            WHERE "m"."BrandId" ="b"."Id" AND "b"."ContractorId" = "c"."Id"'.$modelTypeFilter;        
        if ($filter) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Id" NOT IN ('.$filter.')';
        }
        if ($presents) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "Present" = 1';
        }
        if (!empty($limit)) {
            $sql = 'SELECT * FROM ('.$sql.') WHERE "ContractorId" IN ('.$limit.')';
        }
        $aResult = getDb()->querySelect($sql);
        return $aResult;
    }
    /**
     * Вернуть заполненную данными из запроса модель
     * @param ModelRequest $request
     * @return Models
     */
    public static function getModelFromRequest(ModelRequest $request) 
    {
        $model = new Models();
        $model->name = $request->name;
        $model->internationalName = $request->internationalName;
        $model->fullName = $request->fullName;
        $model->classifierId = $request->classifierId;
        $model->brandId = $request->brandId;
        $model->commemt = $request->comment;
        $model->year = $request->year;
        $model->isPrototype = $request->isPrototype;
        if ($request->modelTypeId === ModelRequest::MODEL_FROM_RUSSIAN) {
            $model->modelTypeId  ($request->localizationLevel >= 49) ? 
                    ModelType::RUSSIAN_PRODUCTION : 
                ModelType::ASSEMBLY_PRODUCTION;
        } else {
            $model->modelTypeId = ModelType::FOREIGN_PRODUCTION;
        }
        return $model;
    }
    /**
     * Отправка сообщения на электронную почту редактора при заведении новой модели
     * @param Models $model
     */
    public function sendEmailToEditor(Models $model) 
    {       
        $contractor = new Contractor( (new Brand($model->brandId))->contractorId);
        $editor = ContractorService::getEditor($contractor);
        $header = l('NEW_MODEL_ADDED','messages');
        $messageTemplate = $this->getMessageForEditor($model, $contractor);
        $mailer = new Mailer();
        $mailer->sendMessage($editor->email, $header, $messageTemplate);
        return;
        
    }
    protected function getMessageForEditor(Models $model,Contractor $contractor)
    {
        $tplVars = [
            'company' => $contractor->name,
            'model_name' => $model->name,
            'classifier' => ClassifierService::getParentClassifierString($model->classifierId)
        ];
        $viewHelper = new ViewHelper(_MAIL_TEMPLATES_DIR_,'ansver_for_request',$tplVars);
        return $viewHelper->getRenderedTemplate();        
    }
}
