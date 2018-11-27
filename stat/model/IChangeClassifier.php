<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\model;

/**
 *
 * @author kotov
 */
interface IChangeClassifier {
    /**
     * Изменить классификатор для заданного списка моделей
     * @param string|array $list
     * @param string|int $classifier_id раздел классификатора для переноса
     */
    public static function changeClassifier($list,$classifier_id) ;
}
