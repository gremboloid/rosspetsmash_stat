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
    
}
