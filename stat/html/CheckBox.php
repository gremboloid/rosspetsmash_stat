<?php

namespace Html;

/**
 * Класс Input элементоа CheckBox
 *
 * @author kotov
 */
class CheckBox extends Input
{
    public function __construct($params =[]) 
    {
        parent::__construct($params);
        $this->type = 'checkbox';
    }
}
