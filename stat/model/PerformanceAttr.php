<?php

namespace app\stat\model;

use app\stat\services\ClassifierService;
use app\stat\Convert;
use app\stat\Tools;
use app\stat\db\QuerySelectBuilder;

/**
 * Description of PerformanceAttr
 *
 * @author kotov
 */
class PerformanceAttr extends ObjectModel
{
    protected $name;
    protected $classifierId;
    protected $unitOfMeasureId;
    protected $typeDataId;
    protected $possibleValue;
    protected $necessarily;
    
    const TYPE_STRING = 1;
    const TYPE_NUMBER = 2;
    const TYPE_DATE = 3;
    const TYPE_BOOLEAN = 4;
    const TYPE_ENUM = 5;
    
    protected static $table = "TBLPERFORMANCEATTR";
    
    public static function getPerformanceAttrForClassifier ($classifier_id) 
    {           
        if (!$cl_id = Convert::getNumbers($classifier_id)) {
            return array();
        } else {
            $classifier_list = Tools::getValuesFromArray(ClassifierService::getClassifierParents($cl_id),'Id');
            $select = [
                ['p','Id'],
                ['p','Name','AttrName'],
                ['p','ClassifierId'],
                ['p','PossibleValue'],
                ['p','Necessarily','Required'],
                ['u','Name','UnitName'],
                ['u','ShortName','UnitShortName'],
                ['t','Name','TypeDataName']
            ];
            $from = [
                [ self::$table , 'p'],
                [ 'TBLUNITOFMEASURE' , 'u'],
                [ 'TBLTYPEDATA' , 't']
            ];            
            $where = [  
                '"p"."ClassifierId" IN ('. implode(',', $classifier_list). ') ',
                ['param' => 'p.UnitOfMeasureId','staticValue' => 'u.Id'],
                ['param' => 'p.TypeDataId','staticValue' => 't.Id']
            ];
            return getDb()->getRows(new QuerySelectBuilder([
                'select' => $select, 
                'from' => $from, 
                'where' => $where 
                    ]));
            
        }
    }
}
