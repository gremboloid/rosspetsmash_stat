<?php

namespace app\stat\model;

/**
 * Description of CountryContractorForm
 *
 * @author kotov
 */
class CountryContractorForm extends ObjectModel
{
    protected $contractorId;
    protected $countryId;
    protected $formTypeId;
    protected $id_flag = false;
        
    protected static $table = "TBLCOUNTRYCONTRACTORFORM";
    
    
}
