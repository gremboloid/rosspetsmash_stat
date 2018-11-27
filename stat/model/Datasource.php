<?php

namespace app\stat\model;

/**
 * Description of Datasource
 *
 * @author kotov
 */
class Datasource extends ObjectModel
{
    protected $name;
    protected $allowLimited;
    protected $dataBaseTypeId;  
    
    protected static $table = "TBLDATASOURCE";
    
    public function getDatasourceName() 
    {
        return $this->name;
    }
    public function getDataBaseTypes()
    {
        return $this->dataBaseTypeId;
    }
    
}
