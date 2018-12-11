<?php

namespace app\controllers;

use app\stat\services\ClassifierService;
use app\stat\Tools;
use app\stat\Validate;
use app\stat\Application;
/**
 * Description of InformationController
 *
 * @author kotov
 */
class InformationController extends FrontController
{
     public $controller_name = 'information';  
     public $content;
     protected $leftBlockVars = array();
     
     public function setParams() {
         parent::setParams();         
     }

    protected function initVars() {
        Application::addJS('js/jquery/jquery-ui.min.js');
        parent::initVars();
       // $this->getLeftBlock();
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('TITLE_INFORMATION_PAGE');
        $this->tpl_vars['information_header'] = l('INFORMATION_HEAD');                
        $this->tpl_vars['content'] = $this->content;  
        
        
     }
     public function actionIndex() {
         $this->getIndexParams();
         $this->tpl_vars['left_block'] = $this->getLeftBlock();
        return $this->render('information.twig', $this->tpl_vars);        
     }
     public function actionClassifier() {  
         $this->getClassifierParams();
         $this->tpl_vars['left_block'] = $this->getLeftBlock();
         return $this->render('information.twig', $this->tpl_vars);
     }
     public function actionStat() {  
         $this->getStatParams();
         $this->tpl_vars['left_block'] = $this->getLeftBlock();
         return $this->render('information.twig', $this->tpl_vars);
     }
     

     protected function getLeftBlock() {
         $this->leftBlockVars['controller_name'] = $this->controller_name;
         $this->leftBlockVars['blocks_list'][0] = [
            'block_alias' => 'fast_link',
            'block_name' => l('INFORMATION_SUBSECTION'),
            'type' => 'ref_list',
            'refs' => [
                ['name' => 'Информационные материалы', 'link' => 'index'],
                ['name' => 'Классификатор', 'link' => 'classifier'],
                ['name' => 'Статистика использования конструктора отчетов', 'link' => 'stat'],
            ]
        ];         
        return $this->render('blocks/left_block.twig', $this->leftBlockVars);
     }

    protected function getIndexParams() 
    {
        $tplVars = [];
        $tplVars['files_uri'] = _FILES_URL_;              
        $this->tpl_vars['content'] = $this->render('blocks/information_index.twig',$tplVars);
    }
    protected function getClassifierParams() 
    {
        $tplVars = [];
        $classifierService = new ClassifierService($this->contractor->getId());
        $tplVars['classifier_json'] = $classifierService->getClassifierListJSON(null,true);
        $this->tpl_vars['content'] = $this->render('blocks/information_classifier.twig',$tplVars);
    }
     protected function getStatParams() {
        $tplVars = [];
        $period1 = Tools::getValue('date1', 'POST', date('d.m.Y'));
        $period2 = Tools::getValue('date2', 'POST', date('d.m.Y'));
        if (!(Validate::isDateFormat($period1) && Validate::isDateFormat($period2))) {
            $tplVars['error'] = true;
            $tplVars['error_message'] = [
                    'title' => l('ERROR','messages'),
                    'text' => l('ERROR_WRONG_FORMAT_DATE','messages')
                ];
        } else {
            $tplVars['period1'] = $period1;
            $tplVars['period2'] = $period2;
            $tplVars['head_text'] = l('STAT_HEAD_TEXT');
            $query = 'SELECT tc."Id", tc."Name", COUNT(*) "Count"
                    FROM TBLREPORTSLOG tl, TBLCONTRACTOR tc, TBLUSER tu
                    WHERE tu."ContractorId" = tc."Id" AND tl."UserId" = tu."Id" AND
                    tl."Date" BETWEEN TO_DATE(\'00:00:00/'.$period1.'\',\'HH24:Mi:SS/DD.MM.YYYY\') AND TO_DATE(\'23:59:59/'.$period2.'\',\'HH24:Mi:SS/DD.MM.YYYY\')
                    GROUP BY tc."Name", tc."Id" ORDER BY "Count" DESC';
            $sum =0;
            $res = getDb()->querySelect($query);
            $count = count($res);
            $elements = [];
            for ($i = 0; $i < $count; $i++) {
                $sum += $res[$i]['Count'];
                $elements [$i] = [
                    'id' => $res[$i]['Id'],
                    'name' => $res[$i]['Name'],
                    'count' => $res[$i]['Count']
                ];
                if (is_admin() || is_analytic()) {
                    $elements[$i]['class'] = 'replog';
                }
            }
            if (is_admin() || is_analytic()) {
                $tplVars['admin'] = true;
                $tplVars['total_class'] = 'replog';
            }
            $tplVars['elements'] = $elements;
            $tplVars['total_count'] = $sum;                        
        }        
        $this->leftBlockVars['blocks_list'][1] = [
            'form_id' => 'frmcontrol',
            'method' => 'POST',
            'block_name' => l('CHANGE_PERIOD_LEGEND'),
            'type' => 'form',
            'submit_button' => [
                'enable' => true,
                'text' => l('SHOW_IN_SCREEN')
            ],
            'elements' => [
                [
                    'label' => [
                        'text' => l('FROM','words').':',
                        'class' => 'first'],
                    'type' => 'date',
                    'id' => 'calendar-inputField1',
                    'value' => $period1,
                    'name' => 'date1'
                ],
                [
                    'label' => [
                        'text' => l('TO','words').':',
                        'class' => 'first'],
                    'type' => 'date',
                    'id' => 'calendar-inputField2',
                    'value' => $period2,
                    'name' => 'date2'
                ],
                [
                    'type' => 'hidden',
                    'id' => 'current-contractor',
                    'value' => '',
                    'name' => 'contractor'
                ],
            ],
        ];
        $this->tpl_vars['content'] = $this->render('blocks/information_statistic.twig',$tplVars);
     }
    
             
}
