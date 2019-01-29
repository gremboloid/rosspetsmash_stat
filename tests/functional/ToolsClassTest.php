<?php

use \Codeception\Test\Unit;
use app\stat\Tools;

class ToolsClassTest extends Unit
{
    public function testIsDublicateInArray() 
    {
        $testArray = [1,2,3,4,5,6,7];            
        $this->assertFalse(Tools::isDublicateInArray($testArray));
        $testArray = [1,2,3,4,5,6,7,8,5];
        $this->assertTrue(Tools::isDublicateInArray($testArray));
    }
    public function testGetQuotedString() 
    {
        $string = 'a1,a2';
        $this->assertEquals(Tools::getQuotedString($string),'"a1","a2"');
        $string1 = 'a1.a2';
        $this->assertEquals(Tools::getQuotedString($string1,'.'),'"a1"."a2"');
    }
    public function testAddDoubleQuotes() 
    {
        $string = 'Тестовая строка';
        $this->assertEquals(Tools::addDoubleQuotes($string), '"Тестовая строка"');
        $this->assertEquals(Tools::addDoubleQuotes($string,'@'), '@Тестовая строка@');
    }
    public function testAddQuotes()
    {
        $testData = 1345;
        $this->assertEquals(Tools::addQuotes($testData), 1345);
        $this->assertEquals(Tools::addQuotes($testData,false), "'1345'");
    } 
    public function testSurroundText() 
    {
        $string = 'Тестовая строка';
        $this->assertEquals(Tools::SurroundText($string,5), '5Тестовая строка5');
        $this->assertEquals(Tools::SurroundText($string,'@'), '@Тестовая строка@');
    }
    public function testGetValuesFromArray()
    {
        $testArray = [
            [
                'Name' => 'Вася',
                'Nickname' => 'Pupkin'
            ],
            [
                'Name' => 'Петя',
                'Nickname' => 'KillerZ'
            ],
            [
                'Name' => 'Иван',
                'Nickname' => 'BatMan'
            ],
        ];
        $this->assertEquals(Tools::getValuesFromArray($testArray, 'Name'), ['Вася','Петя','Иван']);
    }
    public function testGetValFromURI()
    {
        $uri = '/admin/requests/edit/58';
        $this->assertEquals(Tools::getValFromURI(0, $uri),'admin');
        $this->assertEquals(Tools::getValFromURI(1, $uri),'requests');
        $this->assertEquals(Tools::getValFromURI(2, $uri),'edit');
        $this->assertEquals(Tools::getValFromURI(3, $uri),58);
        $this->assertEquals(Tools::getValFromURI(5, $uri),'');
    }
    
}
