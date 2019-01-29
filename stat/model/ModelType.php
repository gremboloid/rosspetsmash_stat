<?php

namespace app\stat\model;

/**
 * Description of ModelType
 *
 * @author kotov
 */
class ModelType extends ObjectModel
{
    protected $name;
    
    const RUSSIAN_PRODUCTION = 1;
    const ASSEMBLY_PRODUCTION = 2;
    const FOREIGN_PRODUCTION = 3;
    
    protected static $table = 'TBLMODELTYPE';
}
