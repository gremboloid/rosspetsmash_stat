<?php
namespace app\stat\report\reports;


use app\models\user\LoginUser;
/**
 * Description of DefaultReport
 *
 * @author kotov
 */
class DefaultReport extends ProductionRoot  
{
    protected $alias = 'default';
    protected $creatorClassName = 'DefaultOut';
    public $order = 0;


    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('DEFAULT_REPORT','report'); 
      //  $this->constructReportSettings();
    }
    protected function prepareSQL() 
    {
        parent::prepareSQL();        
        // фильтр по классификатору                
                
    }
    protected function finalCalculating() {
        parent::finalCalculating();     
        
    }
    public function constructReportSettings() {
        parent::constructReportSettings();
    }
    
}
