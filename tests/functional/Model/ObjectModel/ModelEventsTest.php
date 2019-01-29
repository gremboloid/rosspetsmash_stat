<?php

use \Codeception\Test\Unit;
use app\stat\mock\MockTable;
use app\stat\mock\MockTableWidthEvents;
use app\stat\model\ObjectModel;

/**
 * Description of ModelEventsTest
 *
 * @author kotov
 */
class ModelEventsTest extends Unit
{
    public function setUp()
    {
        parent::setUp();
        // очистка тестовой таблицы
        $query = 'TRUNCATE TABLE "MOCKTABLE"';
        getDb()->dbQuery($query);
    }
    public function testAttachEvent() 
    {
        $mockObject = MockTable::getInstance([
            'textField' => 'value',
            'numberField' => 1,
            'id' => 1
        ]);
        $mockObject->attachEvent(ObjectModel::BEFORE_INSERT,function($mockObject) {
            $mockObject->textField = 'value1';
            $mockObject->numberField = 123;
        });
        $this->assertTrue($mockObject->eventsExist(ObjectModel::BEFORE_INSERT));
        $mockObject->saveObject();
        $this->assertEquals($mockObject->textField, 'value1');
        $this->assertEquals($mockObject->id, 1);
        $mockObjectWithEvent = new MockTableWidthEvents();
        $mockObjectWithEvent->id = 5;
        $mockObjectWithEvent->saveObject();
        $this->assertEquals($mockObjectWithEvent->textField, 'value!');
        $this->assertEquals($mockObjectWithEvent->numberField, 666);
        $this->assertEquals($mockObjectWithEvent->id, 666);
        
    }
}
