<?php

namespace app\stat\model;

/**
 * Description of PerfAttrRequest
 *
 * @author kotov
 */
class PerfAttrRequest extends ObjectModel
{
    protected $performanceAttrId;
    protected $modelId;
    protected $value;

    protected static $table = 'TBLPERFATTRREQUEST';
    
}
