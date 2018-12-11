<?php
namespace app\stat\report\reports;

use app\models\user\LoginUser;
/**
 * Класс отчета "Распределение по видам машин"
 *
 * @author kotov
 */
class MachinesReport extends ProductionRoot 
{
    protected $alias = 'machines';
    protected $creatorClassName = 'DefaultOut';
    public $order = 1;
    /**
     * Конструктор отчета "Распределение по видам машин"
     */
        public function __construct(LoginUser $user) 
    {        
        parent::__construct($user);
        $this->name = l('MMACHINES_REPORT','report');        
        if (key_exists('sub_classifier', $this->settings)) {
            $this->reportSettings['sub_classifier'] = explode(',', $this->settings['sub_classifier']);
        }
        else {
            $this->reportSettings['sub_classifier'] = array();
        }
        
    }
    protected function constructReportSettings() {
        parent::constructReportSettings();
        $sub_classifier_list = $this->reportSettings['sub_classifier'];
        if ($sub_classifier_list && is_array($sub_classifier_list)) {
            $list = \Model\Classifier::getFilteredRows($sub_classifier_list, array(['Id'],['Name']),'Name');            
        }
        else {
            $list = array();
        }
        $this->tpl_vars['footer_elements']['select_sub_classifier'] = 
                array(
                    'type' => 'select_modal',
                //    'label' => l('SELECT_SUB_CLASSIFIER','report'),
                    'elements' => true,
                    'list' => $list,
                    'added_elements' => l('SELECTED_SUB_CLASSIFIER','report'),
                    'all_selected' => l('ALL_SUB_CLASSIFIER','report'),
                    'buttons' => array ([
                        'id' => 'select_sub_classifier', 'text' => l('SELECT_SUB_CLASSIFIER','report')])
                );
        
    }
    protected function prepareSQL() 
    {
        parent::prepareSQL();                               
    }
    protected function calculateDimensions() {
        parent::calculateDimensions();
         $all_subs = false;
         $list = '';
        if ($this->reportSettings['sub_classifier']) { 
            if ( is_array($this->reportSettings['sub_classifier']) && 
                !empty($this->reportSettings['sub_classifier'][0])) {
                $all_subs = true;
            }
        }
        if ($all_subs) {
            $list = $this->settings['sub_classifier'];
        } else {
            $elems = $this->reportParams->classifier->getChildClassifierList(array(['Id']));
            foreach ($elems as $elem) {
                $list.= $elem['Id'].',';
            }
            $list = trim($list,',');
        }
        $this->dimensions['select'][] = array('textValue' => '"classifier_distr"."Root"','name' => "ClassifierId");
        $this->dimensions['select'][] = array('textValue' => '"classifier_distr"."Id"','name' => "ClassifierIdSub");
        $this->dimensions['from'][] = ['name'=> 'classifier_distr','textValue' => '(SELECT CONNECT_BY_ROOT("Id") AS "Root", "Id" FROM "TBLCLASSIFIER" START WITH "Id" IN (' . $list. ') CONNECT BY PRIOR "Id" = "ClassifierId")'];
        
        $this->tmp_values['select'][] = array('textValue' => '"var"."ClassifierId"','name' => "ClassifierId");
        $this->tmp_values['where'][] = '"data"."ClassifierId"(+) = "var"."ClassifierIdSub"';
        $this->tmp_values['group'][] ='"var"."ClassifierId"';
        
    }
    protected function finalCalculating() {
        parent::finalCalculating();
     }
    
    
    
}
