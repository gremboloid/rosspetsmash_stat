<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Html;

/**
 * Класс элемента "кнопка для отправки данных формы на сервер"
 *
 * @author kotov
 */
class Submit extends Input
{
        /**
     * Конструктор элемента "кнопка для отправки данных формы на сервер"
     * @param string $type тип элемента
     * @param array $params массив аттрибутов
     */
    public function __construct($params =[]) 
    {
        parent::__construct($params);
        $this->type = 'submit';
    }
}
