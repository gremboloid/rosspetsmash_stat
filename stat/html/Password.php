<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Html;

/**
 * Класс элемента "Пароль"
 *
 * @author kotov
 */
class Password extends Input
{
    public function __construct($params =[]) 
    {
   /**
     * Конструктор элемента "пароль"
     * @param string $type тип элемента
     * @param array $params массив аттрибутов
     */
        parent::__construct($params);
        $this->type = 'password';
    }
}
