<?php

use \Codeception\Test\Unit;
use app\stat\mock\MockTable;
/**
 * Description of CreateModelTest
 *
 * @author kotov
 */
class CreateModelTest extends Unit
{

    public function setUp()
    {
        parent::setUp();
        // очистка тестовой таблицы
        $query = 'TRUNCATE TABLE "MOCKTABLE"';
        getDb()->dbQuery($query);
    }

    public function testCreateModelFomArray() 
    {
        $textValue = 'value';
        $numberValue = 123;
        $mockObject = MockTable::getInstance([
            'textField' => $textValue = 'value',
            'numberField' => $numberValue = 123
        ]);
        $this->assertEquals($mockObject->textField,$textValue);
        $this->assertEquals($mockObject->numberField,$numberValue);
    }
    public function testCRUD() 
    {
        $mockObject = MockTable::getInstance([
            'id' => 1,
            'textField' => $textValue = 'value',
            'numberField' => $numberValue = 123
        ]);        
        $mockObject->saveObject();
                
        $mockObject1 = new MockTable(1);
        $this->assertEquals($mockObject1->textField,$textValue);
        $this->assertEquals($mockObject1->numberField,$numberValue);
        $this->assertTrue($mockObject1->isExistInDb());
        
        $mockObject1->textField = 'value1';
        $mockObject1->saveObject();
        
        $mockObject2 = new MockTable(1);
        $this->assertNotEquals($mockObject2->textField,$textValue);
        $this->assertEquals($mockObject2->numberField,$numberValue);
        
        $this->assertEquals(MockTable::getRowsCount(),1);
        $mt = MockTable::getFieldByValue('TextField', 'value1');
        $this->assertEquals($mt, 1);
        
        $mockObject2->delete();        
        
        $mockObject3 = new MockTable(1);
        $this->assertFalse($mockObject3->isExistInDb());                                                
    }
    
}
