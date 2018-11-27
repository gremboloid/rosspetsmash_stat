<?php

namespace app\stat\admin;


use app\stat\model\Contractor;
use app\stat\model\Models;

class AdminIndex extends AdminRoot {
    
    /** @var array данные для построения таблиц со сводной или статистической информацией */
    protected $summary_information;
    
    public function __construct()
    {
        $this->link = 'index';
        $this->name = l('SUMMARY_INFORMATION','admin');
        $this->parentElement = null;
        $this->left_block = false;
        $this->template_file = 'admin_index';
        
        parent::__construct();
    }
    protected function prepare() {
        parent::prepare();
        // подготовка первой таблицы
        $this->summary_information['contractors_summary'] = array();
        $this->summary_information['contractors_summary']['head'] = l('CONTRACTOR_SUMMARY_HEAD','admin');
        $this->summary_information['contractors_summary']['subhead'] = l('CONTRACTOR_SUMMARY_SUBHEAD','admin');
        if (_USE_CHPU_) {
            $def_link_contractors = '/admin/contractors';
        } else {
            $def_link_contractors = '?controller=admin&element=contractors';
        }
        
        $contractors_count = Contractor::getRowsCount([
           ['param' => 'Present','staticNumber' => 1]
        ]);
        $this->summary_information['contractors_summary']['headlink'] = [
            'text' => $contractors_count,
            'href' => $def_link_contractors
        ];
        $this->summary_information['contractors_summary']['elements_list'] = array();
    // группировка производителей по категориям
        $sql = 'SELECT "n"."Id","n"."Name","n"."SummaryName", Count("n"."Name") AS "Count" FROM "TBLCONTRACTOR" "tc","TBLCONTRACTORCATEGORY" "c","TBLCONTRACTORCATEGORYNAMES" "n"
  WHERE "c"."CategoryId" = "n"."Id" AND "c"."ContractorId" = "tc"."Id" AND "tc"."Present" = 1 GROUP BY "n"."Id","n"."Name","n"."SummaryName" ORDER BY "n"."Id"';
        $contractors_types_count = getDb()->querySelect($sql);
        foreach ($contractors_types_count as $element) {
            $this->summary_information['contractors_summary']['elements_list'][$element['Id']] = [
                'text' => $element['SummaryName'] ? $element['SummaryName'] : $element['Name'],
                'link' => [
                    'text' => $element['Count'],
                    'href' => $def_link_contractors . '?category='.$element['Id'] 
                    
                ]
            ];
        }
       // подготовка второй таблицы
        $this->summary_information['models_summary'] = array();
        $this->summary_information['models_summary']['head'] = l('MODELS_SUMMARY_HEAD','admin');
        $this->summary_information['models_summary']['subhead'] = l('MODELS_SUMMARY_SUBHEAD','admin');
        if (_USE_CHPU_) {
            $def_link_models = '/admin/models';
        } else {
            $def_link_models = '?controller=admin&element=models';
        }
        $models_count = Models::getRowsCount();
        $this->summary_information['models_summary']['headlink'] = [
            'text' => $models_count,
            'href' => $def_link_models
        ];
        $sql = 'SELECT "Id","Name","OrderIndex" FROM TBLCLASSIFIER "c" WHERE "c"."ClassifierId" = 41 ORDER BY "OrderIndex" DESC NULLS LAST';
        $classifierList = getDb()->querySelect($sql);
        foreach ($classifierList as $classifier) {
            $cl_list_sql = ' SELECT c."Id" FROM BIX.TBLCLASSIFIER c
                START WITH c."Id" = '.$classifier['Id'].' CONNECT BY PRIOR c."Id" = c."ClassifierId"';
            $sql =  'SELECT Count(*) AS "Count" FROM TBLMODEL WHERE "ClassifierId" IN ('.$cl_list_sql.')';
            $cl_count = getDb()->querySelect($sql);            
            $this->summary_information['models_summary']['elements_list'][$classifier['Id']] = [
                'text' => $classifier['Name'],
                'link' => [
                    'text' => $cl_count[0]['Count'],
                    'href' => $def_link_models . '?classifier='.$classifier['Id']
                ]
            ];
        }                                
    }

    protected function setTemplateVars() {
        $this->tpl_vars['information_tables'] = $this->summary_information;
        
    }
    public function getBreadcrumbs() {
        return false;
    }
}