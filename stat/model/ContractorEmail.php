<?php

namespace app\stat\model;


class ContractorEmail extends ObjectModel
{
    protected $contractorId;
    protected $email;
    protected $id_flag = false;
    
    protected static $table = 'TBLCONTRACTOREMAIL';
}
