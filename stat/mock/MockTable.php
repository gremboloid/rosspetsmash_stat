<?php
namespace app\stat\mock;

use app\stat\model\ObjectModel;

/**
 * Description of MockTable
 * @property integer $numberField
 * @property string $textField
 * @author kotov
 */
class MockTable extends ObjectModel
{
    
    protected $numberField;
    protected $textField;

    protected static $table = 'MOCKTABLE';
}
