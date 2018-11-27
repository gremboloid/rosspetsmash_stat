<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\providers;

/**
 *
 * @author kotov
 */
interface TableDataProvideInterface {
    
    /**
     * Вернуть количество строк
     */
    public function getCount();
    /**
     * Вернуть табличные данные
     */
    public function getTableData(int $pageNumber,int $rowsCount);
}
