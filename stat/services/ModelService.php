<?php

namespace app\stat\services;

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
     public static function getModelsList(string $datasources,$classifierid,$filter='',$presents=true,$limit='')
    {
        $sql = 'SELECT "TBLMODEL"."Id" AS "Id","TBLMODEL"."BrandId", "TBLMODEL"."Name" AS "Name" FROM (
            SELECT DISTINCT "ModelId" FROM "TBLPRODUCTION" WHERE "TypeData" IN ('.$datasources.')) "mdl_prod", 
            "TBLMODEL" WHERE "TBLMODEL"."Id" = "mdl_prod"."ModelId" AND "TBLMODEL"."ClassifierId" IN  
            ( SELECT "Id" FROM "TBLCLASSIFIER" CONNECT BY PRIOR "Id" = "ClassifierId" START WITH "Id"='.$classifierid.') ORDER BY "TBLMODEL"."Name" ASC';
        $sql = 'SELECT "m".*,"c"."Id" AS "ContractorId","c"."Present" FROM ('.$sql.') "m","TBLCONTRACTOR" "c","TBLBRAND" "b"
            WHERE "m"."BrandId" ="b"."Id" AND "b"."ContractorId" = "c"."Id"';        
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
}