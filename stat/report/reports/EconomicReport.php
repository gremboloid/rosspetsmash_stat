<?php
namespace app\stat\report\reports;

use app\models\user\LoginUser;
/**
 * Description of EconomicReport
 *
 * @author kotov
 */
class EconomicReport extends EconomicRoot
{
    protected $alias = 'economic';
    protected $creatorClassName = 'DefaultOut';
    public $order = 8;
    
    public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('ECONOMIC_REPORT','report'); 

        if (key_exists('manufacturers_list', $this->settings)) {
            $this->reportSettings['manufacturers_list'] = explode(',', $this->settings['manufacturers_list']);
        }
        else {
            $this->reportSettings['manufacturers_list'] = array();
        }
        
        
    }
    protected function constructReportSettings() {
        parent::constructReportSettings();

        
    }
    
}
