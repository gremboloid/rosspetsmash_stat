<?php

namespace app\stat\model;

/**
 * Description of ValuesPerfAttr
 *
 * @author kotov
 */
class ValuesPerfAttr extends ObjectModel
{
    protected $performanceAttrId;
    protected $modelId;
    protected $value;

    protected static $table = 'TBLVALUESPERFATTR';
    
}
