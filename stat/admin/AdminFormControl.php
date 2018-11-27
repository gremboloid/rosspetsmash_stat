<?php

namespace app\stat\admin;
use app\stat\model\ContractorEmail;
use app\stat\model\ContractorCategoryNames;
use app\stat\Tools;
use app\stat\db\QuerySelectBuilder;

/**
 * Отображает сведения о заполнении форм для производителей
 *
 * @author kotov
 */
class AdminFormControl extends AdminRoot
{
    protected $month;
    protected $year;
    protected $contractors_category;
    protected $show_email = false;
    protected $in_portal = false;
    protected $content = array();
    protected $cols_count = 7;
    protected $columns_head = array();
    protected $contractor_types = array();


    public function __construct() {
        $this->name = l('CONTRACTORS_FORM_CONTROL_HEAD','admin');
        $this->link = 'fcontrol';
        $this->parentElement = 'Index';
        $this->template_file = 'admin_fcontrol';
        $this->left_block = false;   
        $this->columns_head = [ 
                l('CONTRACTORS_FORM_CONTROL_HEAD_CONTRACTOR','admin'),
                l('CONTRACTORS_FORM_CONTROL_HEAD_FORM1','admin'),
                l('CONTRACTORS_FORM_CONTROL_HEAD_FORM2','admin'),
                l('CONTRACTORS_FORM_CONTROL_HEAD_FORM4','admin'),
                l('CONTRACTORS_FORM_CONTROL_HEAD_FORM5','admin'),
                l('CONTRACTORS_FORM_CONTROL_HEAD_FORM12','admin'),
                l('CONTRACTORS_FORM_CONTROL_HEAD_CONTRACTOR_PHONE','admin'),
            ];
        parent::__construct();
    }
    protected function prepare() {        
        parent::prepare();
        $email_for_dispatch = [];
        $email_list = ContractorEmail::getRows();
        $def_month = date('m') != 1 ? date('m') - 1 : 12;
        $def_year = $def_month != 12 ? date("Y") : date("Y") - 1;
        $this->month = Tools::getValue('month', 'POST',$def_month);
        $this->year = Tools::getValue('year', 'POST',$def_year);
        $this->contractors_category = (int) Tools::getValue('contractor-types', 'POST',0);
        $show_email = Tools::getValue('showEmail', 'POST',$def_year);
        if ($show_email === 'on') {
            $this->show_email = true;
            $this->cols_count = 8;  
            $this->columns_head[] = l('CONTRACTORS_FORM_CONTROL_HEAD_CONTRACTOR_EMAIL','admin');
        }
        foreach ($email_list as $val) {
            $email_for_dispatch[$val['ContractorId']][] = $val['Email'];            
        }
        $in_portal = Tools::getValue('inPortal','POST','');
        if ($in_portal == 'on') {
            $this->in_portal = true;
        }
        $filter = '';
        
        $select = [
                    ['tc','Id'],
                    ['tc','Name'],
                    ['tc','Phone'],
                    ['tc','Email']
                  ];
        $from = [[ 'TBLCONTRACTOR','tc' ]];
        $where = [
            '"tc"."Id" NOT IN (435,467)'
        ];
        if ($this->in_portal) {
            $where[] = ['param' => 'tc.Present', 'staticNumber' => 1];
        }
        if ($this->contractors_category != 0) {
            //$where[] = ['param' => 'tc.', 'staticNumber' => 1];
            $from[] = [ 'TBLCONTRACTORCATEGORY','tcc' ];
            $where[] = ['param' => 'tcc.ContractorId', 'staticValue' => 'tc.Id'];
            $where[] = ['param' => 'tcc.CategoryId', 'staticNumber' => $this->contractors_category];
        }
        
        $order = [['textValue' => '"tc"."Name"' ]];
        $result = getDb()->getRows( new QuerySelectBuilder([
                    'select' => $select, 
                    'from' => $from,
                    'where' => $where,
                    'orderBy' => $order,
                    'distinct' => true
            ]));
      //  $this->tpl_vars['test'] = print_ar($result,true);
        $prev_id = $result[0]['Id'];
        $types = array (1,2,4,5,12);
        $flag =false;
        for ($idx = 0;$idx < count($result);$idx++) {
            $select = [
                ['ti','DataBaseTypeId'],
                ['ti','Actuality'],
                ['name' => 'Date' ,'textValue' => 'TO_CHAR("ti"."Date",\'DD.MM.YYYY\')']
            ];
            $from = [
                [ 'TBLCONTRACTOR','tc' ],
                [ 'TBLINPUTFORM','ti' ],
            ];
            $where = [
                ['param' => 'tc.Id', 'staticNumber' => $result[$idx]['Id']],
                ['param' => 'ti.ContractorId','staticValue' => 'tc.Id'],
                ['param' => 'ti.Month', 'staticValue' => $this->month],
                ['param' => 'ti.Year', 'staticValue' => $this->year],
            ];            
            $ctr_result = getDb()->getRows(new QuerySelectBuilder([
                'select' => $select,
                'from' => $from,
                'where' => $where] 
            ));
            $rows_count = count($ctr_result);
            if ($rows_count == 0) {
                $this->content[$idx] = [
                    1 => ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')],
                    2 => ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')],
                    4 => ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')],
                    5 => ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')],
                    12 => ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')],
                ];
                           
            }
            else {
                $db_types = array();
                $i = 0;
                $types_count = count($types);
                for ($j = 0;$j<$types_count;$j++){
                    if ($i < $rows_count) {
                        if ($ctr_result[$i]['DataBaseTypeId'] == $types[$j]) {
                            $clr = $ctr_result[$i]['Actuality'] == 1 ? 'green':'red';
                            $this->content[$idx][$types[$j]] = [
                                'color' => $clr,
                                'text' => $ctr_result[$i]['Date']
                            ];
                            $i++;
                        } else {
                            $this->content[$idx][$types[$j]] = ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')];
                        }
                    } else {
                        $this->content[$idx][$types[$j]] = ['text' => l('CONTRACTORS_FORM_CONTROL_NODATA','admin')];                    
                    }                
                }
            }
            $this->content[$idx]['contractorName'] = $result[$idx]['Name'];
            $this->content[$idx]['contractorId'] = $result[$idx]['Id'];
            $this->content[$idx]['contractorPhone'] = $result[$idx]['Phone'];
            $this->content[$idx]['contractorEmail'] = $result[$idx]['Email'];
            
        }
       //  $this->tpl_vars['test'] = print_ar($this->content,true);
        $this->contractor_types = ContractorCategoryNames::getRowsArray([
            ['TBLCONTRACTORCATEGORYNAMES','Id','value'],
            ['TBLCONTRACTORCATEGORYNAMES','Name','text']]); 
        array_unshift($this->contractor_types, ['value' => 0,'text' => 'Все производители']);
    } 
    
    protected function setTemplateVars() {
        parent::setTemplateVars();
        $this->tpl_vars['filters_array']['dateFilter'] = [
            'month' => $this->month,
            'year' => $this->year
        ];
        $this->tpl_vars['month'] = l('MONTHS','words');
        $this->tpl_vars['years'] = ['start' => 2009,'end' => date('Y')];
        $this->tpl_vars['report_period'] = l('CONTRACTORS_FORM_CONTROL_REPORT_PERIOD', 'admin');
        $this->tpl_vars['period'] = l('CONTRACTORS_FORM_CONTROL_PERIOD', 'admin').': '
                .$this->tpl_vars['month'][$this->month].' '.$this->year;
        $this->tpl_vars['columns_head'] = $this->columns_head;
        $this->tpl_vars['content'] = $this->content;
        $this->tpl_vars['colspan'] = $this->cols_count;
        $this->tpl_vars['show_email'] = $this->show_email;
        $this->tpl_vars['in_portal'] = $this->in_portal;
        $this->tpl_vars['contractor_types'] = $this->contractor_types;
        $this->tpl_vars['contractors_category'] = $this->contractors_category;
        
    }
}
