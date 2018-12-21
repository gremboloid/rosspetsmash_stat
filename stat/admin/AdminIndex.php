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
        $this->summary_information['contractors_summary'][0]['head'] = l('CONTRACTOR_SUMMARY_HEAD','admin');
        $this->summary_information['contractors_summary'][1]['head'] = l('CONTRACTOR_SUMMARY_PRESENT_HEAD','admin');
        $this->summary_information['contractors_summary'][0]['subhead'] = l('CONTRACTOR_SUMMARY_SUBHEAD','admin');
        $this->summary_information['contractors_summary'][1]['subhead'] = l('CONTRACTOR_SUMMARY_SUBHEAD','admin');
        $def_link_all_contractors = '/admin/contractors?present=0';
        $def_link_contractors = '/admin/contractors?present=1';
        
        $contractors_count = Contractor::getRowsCount();
        $contractors_present_count = Contractor::getRowsCount([
           ['param' => 'Present','staticNumber' => 1]
        ]);
        $this->summary_information['contractors_summary'][0]['id'] = 'contractor_summary';
        $this->summary_information['contractors_summary'][1]['id'] = 'contractor_summary_present';
        $this->summary_information['contractors_summary'][0]['headlink'] = [
            'text' => $contractors_count,
            'href' => $def_link_all_contractors
        ];
        $this->summary_information['contractors_summary'][1]['headlink'] = [
            'text' => $contractors_present_count,
            'href' => $def_link_contractors
        ];
        $this->summary_information['contractors_summary'][0]['elements_list'] = array();
    // группировка производителей по категориям
        $sqlAll = 'SELECT "n"."Id","n"."Name","n"."SummaryName", Count("n"."Name") AS "Count" FROM "TBLCONTRACTOR" "tc","TBLCONTRACTORCATEGORY" "c","TBLCONTRACTORCATEGORYNAMES" "n"
  WHERE "c"."CategoryId" = "n"."Id" AND "c"."ContractorId" = "tc"."Id" GROUP BY "n"."Id","n"."Name","n"."SummaryName" ORDER BY "n"."Id"';
        $sql = 'SELECT "n"."Id","n"."Name","n"."SummaryName", Count("n"."Name") AS "Count" FROM "TBLCONTRACTOR" "tc","TBLCONTRACTORCATEGORY" "c","TBLCONTRACTORCATEGORYNAMES" "n"
  WHERE "c"."CategoryId" = "n"."Id" AND "c"."ContractorId" = "tc"."Id" AND "tc"."Present" = 1 GROUP BY "n"."Id","n"."Name","n"."SummaryName" ORDER BY "n"."Id"';
        $contractors_types_count = getDb()->querySelect($sqlAll);
        $contractors_present_types_count = getDb()->querySelect($sql);
        foreach ($contractors_types_count as $element) {
            $this->summary_information['contractors_summary'][0]['elements_list'][$element['Id']] = [
                'text' => $element['SummaryName'] ? $element['SummaryName'] : $element['Name'],
                'link' => [
                    'text' => $element['Count'],
                    'href' => $def_link_all_contractors . '&category='.$element['Id'] 
                    
                ]
            ];
            
        }
        foreach ($contractors_present_types_count as $element) {
            $this->summary_information['contractors_summary'][1]['elements_list'][$element['Id']] = [
                'text' => $element['SummaryName'] ? $element['SummaryName'] : $element['Name'],
                'link' => [
                    'text' => $element['Count'],
                    'href' => $def_link_contractors . '&category='.$element['Id'] 
                    
                ]
            ];
            
        }
       // подготовка второй таблицы
        $this->summary_information['models_summary'] = array();
        $this->summary_information['models_summary'][0]['id'] = 'models_summary';
        $this->summary_information['models_summary'][1]['id'] = 'models_summary_present';
        $this->summary_information['models_summary'][0]['head'] = l('MODELS_SUMMARY_HEAD','admin');
        $this->summary_information['models_summary'][1]['head'] = l('MODELS_SUMMARY_PRESENT_HEAD','admin');
        $this->summary_information['models_summary'][0]['subhead'] = l('MODELS_SUMMARY_SUBHEAD','admin');
        $this->summary_information['models_summary'][1]['subhead'] = l('MODELS_SUMMARY_SUBHEAD','admin');
        $sql = 'SELECT Count(*) AS "Count" FROM TBLMODEL "m", TBLCONTRACTOR "c","TBLBRAND" "b" WHERE
            "c"."Id" = "b"."ContractorId" AND "c"."Present" = 1 AND "m"."BrandId" = "b"."Id"';
        $sqlAll = 'SELECT Count(*) AS "Count" FROM TBLMODEL "m", TBLCONTRACTOR "c","TBLBRAND" "b" WHERE
            "c"."Id" = "b"."ContractorId" AND "m"."BrandId" = "b"."Id"';
        $def_link_all_models = '/admin/models';
        $def_link_models = '/admin/models?present=1';
        $models_count = getDb()->querySelect($sql)[0]['Count'];
        $all_models_count = getDb()->querySelect($sqlAll)[0]['Count'];
        $this->summary_information['models_summary'][0]['headlink'] = [
            'text' => $all_models_count,
            'href' => $def_link_all_models
        ];
        $this->summary_information['models_summary'][1]['headlink'] = [
            'text' => $models_count,
            'href' => $def_link_models
        ];
        $sql = 'SELECT "Id","Name","OrderIndex" FROM TBLCLASSIFIER "c" WHERE "c"."ClassifierId" = 41 ORDER BY "OrderIndex" DESC NULLS LAST';
        $classifierList = getDb()->querySelect($sql);
        foreach ($classifierList as $classifier) {
            $cl_list_sql = ' SELECT c."Id" FROM BIX.TBLCLASSIFIER c
                START WITH c."Id" = '.$classifier['Id'].' CONNECT BY PRIOR c."Id" = c."ClassifierId"';
            $sqlAll =  'SELECT Count(*) AS "Count" FROM TBLMODEL "m", TBLCONTRACTOR "c","TBLBRAND" "b" WHERE "ClassifierId" IN ('.$cl_list_sql.')'
                    . 'AND "c"."Id" = "b"."ContractorId" AND "m"."BrandId" = "b"."Id"';
            $sql =  'SELECT Count(*) AS "Count" FROM TBLMODEL "m", TBLCONTRACTOR "c","TBLBRAND" "b" WHERE "ClassifierId" IN ('.$cl_list_sql.')'
                    . 'AND "c"."Id" = "b"."ContractorId" AND "c"."Present" = 1 AND "m"."BrandId" = "b"."Id"';
            $cl_count = getDb()->querySelect($sql);            
            $all_cl_count = getDb()->querySelect($sqlAll);            
            $this->summary_information['models_summary'][0]['elements_list'][$classifier['Id']] = [
                'text' => $classifier['Name'],
                'link' => [
                    'text' => $all_cl_count[0]['Count'],
                    'href' => $def_link_all_models . '?classifier='.$classifier['Id']
                ]
            ];
            $this->summary_information['models_summary'][1]['elements_list'][$classifier['Id']] = [
                'text' => $classifier['Name'],
                'link' => [
                    'text' => $cl_count[0]['Count'],
                    'href' => $def_link_models . '&classifier='.$classifier['Id']
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