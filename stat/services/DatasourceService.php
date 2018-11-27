<?php

namespace app\stat\services;

use app\stat\model\Contractor;

use \Yii;
/**
 * Description of DatasourceService
 *
 * @author kotov
 */
class DatasourceService {
   /**
    * Производитель производителя
    * @var Contractor
    */
    protected $contractor;
    
    /**
     * 
     * @param Contractor $contractor объект модели производителя
     */
    public function __construct(Contractor $contractor) {
        $this->contractor = $contractor;
    }
    /**
     * Вернуть доступные для производителя источники данных
     * @return attsy
     */
    public function getAvailableDatasoure()
    {
        $defaultDatasource = [];
        if (!(is_admin() || is_analytic())) {
            if (is_russian()) {
                $defaultDatasource = [1,2,4,14];
                if ($this->contractor->$isImporter) {
                    $defaultDatasource[] = 5;
                }                
            } else {
                $defaultDatasource = [5,14];
            }
        } else {
            $defaultDatasource = [1,2,4,5,13,14];
        }
        $where = '"Id" IN ('.implode(',',$defaultDatasource).')';
        $sql= 'select "Id", "Name" ,"DataBaseTypeId" FROM
    TBLDATASOURCE WHERE '.$where.' order by "Name"';
        return getDb()->querySelect($sql);
    }        
}
