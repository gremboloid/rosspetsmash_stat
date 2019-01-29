<?php

namespace app\stat\mock;

/**
 * Description of MockTableWidthEvents
 *
 * @author kotov
 */
class MockTableWidthEvents extends MockTable
{
    public function beforeInsert()
    {
        parent::beforeInsert();
        $this->textField = 'value!';
        $this->id = 666;
        $this->numberField = 666;                
    }
}
