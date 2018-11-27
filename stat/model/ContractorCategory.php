<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\stat\model;

/**
 * Description of ContractorCategory
 *
 * @author kotov
 */
class ContractorCategory extends ObjectModel
{
    protected $contractorId;
    protected $categoryId;
    protected $id_flag = false;
    
    protected static $table = 'TBLCONTRACTORCATEGORY';
    
}
