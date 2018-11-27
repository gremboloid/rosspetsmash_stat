<?php

namespace app\stat\html;

/**
 * Description of Hidden
 *
 * @author kotov
 */
class Hidden extends Input
{
    public function __construct($params =[]) 
    {
        parent::__construct($params);
        $this->type = 'hidden';
    }
}
