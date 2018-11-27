<?php
use \Codeception\Test\Unit;
use \app\stat\db\SimpleSQLConstructor;

class SimpleSQLConstructorTest extends Unit
{    
    public function testEmpty() 
    {
        $stack = [];
        $this->assertEmpty($stack);
        return $stack;
    }
    
    public function testGenerateClauseSelect() {
        // 1. Пустые поля
        $test_string = 'SELECT *';
        $test_fields = '';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
       // $this->assertEquals('2','2');
        // один элемент
        $test_fields = 'Name';
        $test_string = 'SELECT "Name"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
        $test_fields = ['Name'];
        $test_string = 'SELECT "Name"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
        $test_fields = ['tbl','Name'];
        $test_string = 'SELECT "tbl"."Name"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
        // несколько элементов
        $test_fields = [['tbl','Name'],['tbl','Surname']];
        $test_string = 'SELECT "tbl"."Name","tbl"."Surname"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
                // с псевдонимом
        $test_fields = [['tbl','Name'],['tbl','Surname',"Fam"]];
        $test_string = 'SELECT "tbl"."Name","tbl"."Surname" AS "Fam"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
               // из ассоциативного массива
        $test_fields = [['name' => 'MyName', 'textValue' => '"tbl"."Name"']];
        $test_string = 'SELECT "tbl"."Name" AS "MyName"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseSelect($test_fields), $test_string);
    } 
    public function testGenerateClauseFrom() {
        // передается строка
        $test_field = 'TABLE1';
        $test_result = 'FROM "TABLE1"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseFrom($test_field),$test_result);
        $test_field = 'TABLE1,TABLE2';
        $test_result = 'FROM "TABLE1","TABLE2"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseFrom($test_field),$test_result);
        // передается массив
        $test_field = ['TABLE1','t1'];
        $test_result = 'FROM "TABLE1" "t1"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseFrom($test_field),$test_result);
        $test_field = [['TABLE1'],['TABLE2']];
        $test_result = 'FROM "TABLE1","TABLE2"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseFrom($test_field),$test_result);
        // передается псевдоним таблицы
        $test_field = [['TABLE1','t1'],['TABLE2','t2']];
        $test_result = 'FROM "TABLE1" "t1","TABLE2" "t2"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseFrom($test_field),$test_result);
        // из ассоциативного массива
        $test_field = [['textValue' => 'SELECT "Name" FROM TABLE2','name' => 't1']];
        $test_result = 'FROM (SELECT "Name" FROM TABLE2) "t1"';
        $this->assertEquals(SimpleSQLConstructor::generateClauseFrom($test_field),$test_result);
                
    }
     public function testGenerateClauseWhere() {
        // передается одиночный параметр
        $test_field = ['Id = 4'];
        $test_result = 'WHERE Id = 4';
        $this->assertEquals(SimpleSQLConstructor::generateClauseWhere($test_field),$test_result);
        // ассоциативный массив
        $test_field = [['param' => 'Id','staticNumber' => 4]];
        $test_result = 'WHERE "Id" = 4';
        $this->assertEquals(SimpleSQLConstructor::generateClauseWhere($test_field),$test_result);
        // ассоциативный массив, группа условий
         $test_field = [['param' => 'Id','staticNumber' => 4],
                        ['param' => 'Name','staticText' => 'Иванов']];
        $test_result = 'WHERE "Id" = 4 AND "Name" = \'Иванов\''; 
        $this->assertEquals(SimpleSQLConstructor::generateClauseWhere($test_field),$test_result);
        // значение как есть (pureValue)
        $test_field = [['param' => 'Id','pureValue' => 4],
                        ['param' => 'Name','pureValue' => "'Иванов'"]];
        $test_result = 'WHERE "Id" = 4 AND "Name" = \'Иванов\''; 
        $this->assertEquals(SimpleSQLConstructor::generateClauseWhere($test_field),$test_result);
        // тестирование подставляемого значения
        $test_field = [
            ['param' => 'Id','bindingValue' => 4],
                        ['param' => 'Name','pureValue' => "'Иванов'"]];
        $test_result = 'WHERE "Id"=:Id AND "Name" = \'Иванов\''; 
         $this->assertEquals(SimpleSQLConstructor::generateClauseWhere($test_field),$test_result);
        
     }
     public function testGenerateClauseOrderBy() {
        // передается одиночный параметр 
          $test_field = [['textValue' => 'tc."Id"']];
          $test_result = ' ORDER BY tc."Id"';
          $this->assertEquals(SimpleSQLConstructor::generateClauseOrderBy($test_field),$test_result);
     }                      	        
}

