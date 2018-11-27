<?php

namespace app\modules;

use app\stat\Module;
use app\stat\Application;
use app\stat\db\QuerySelectBuilder;

defined('START_FLAG') or die('ERROR');


class ReportsCounter extends Module {
    
    public static $name = 'reports_counter';          
    public static $author = 'Konstantin Kotov';    
    public static $def_lang = 'ru';   
            
    public function __construct() {
        parent::__construct();  
        $this->is_widget = true;
        $this->widget_position = ['page' => 'index', 'column' => 2];
    }
    public function init() {
        parent::init();        
    }
    protected function initStylesAndScripts() {
        parent::initStylesAndScripts();
        Application::addCSS(_MODULES_URI_.static::$name .'/templates/css/styles.css');
    }
    protected function configure() {
        parent::configure();
        $months = l('MONTHS2','words');
        $pYear = date('Y');
      /*  if (date('m')!= 1)
            $prevMonth = date('m')-1;
        else
        {
            $prevMonth = 12;
            $pYear--;
        }*/
        $pYear = date('Y');
        $prevMonth = (int) date('m');
        $firstday = mktime(0, 0, 0, $prevMonth,1,$pYear );
        $lastday = mktime(23, 59, 59, date('m'),date('t'),date('Y') );
        $step = ($lastday - $firstday) / 10;
        $curday = $firstday;
        $colInLastMonth = getDb()->getRow( new QuerySelectBuilder([
            'select' => [["textValue" => 'COUNT(*)','name' => 'Count']],
            'from' => ['TBLREPORTSLOG','t'],
            'where' => ['TRUNC("t"."Date",\'MONTH\') = TO_DATE(\''.$prevMonth.'.'.$pYear.'\',\'mm.YYYY\')' ]            
            ]),true);
        static::$template_vars['last_month_count'] = $colInLastMonth = $colInLastMonth['Count'];
        
        static::$template_vars['month'] = mb_strtolower($months[$prevMonth]);
        static::$template_vars['year'] = mb_strtolower($pYear);
        static::$template_vars['chpu'] = _USE_CHPU_;
        static::$template_vars['date1'] = '01.'.date('m').'.'.$pYear;
        static::$template_vars['date2'] = date('d').'.'.date('m').'.'.$pYear;
        
        
        if (date('w') > 0)
                $colDate = date('w') + 6;
            else $colDate = 6;
                $rDate = time() - $colDate*24*60*60;
            $prdArr = array();
            for ($i=0;$i<10;$i++)
            {
                $prd = getDb()->getRow( new QuerySelectBuilder([ 
                   'select' => [["textValue" => 'COUNT(*)','name' => 'Count']],
                   'from' => ['TBLREPORTSLOG','t'],
                   'where' => ['"t"."Date" BETWEEN TO_DATE(\''.date('H:i:s/d.m.Y',$curday).'\',\'HH24:Mi:SS/DD.MM.YYYY\') AND 
                      TO_DATE(\''.date('H:i:s/d.m.Y',$curday+=$step).'\',\'HH24:Mi:SS/DD.MM.YYYY\')']
                    ]),true);
                array_push($prdArr,$prd['Count']);                                
            }            
            $ratio = max($prdArr)/10;
            $ratio = ($ratio != 0) ? $ratio : 1;
            $height_arr = array();
            for ($i=0;$i<count($prdArr);$i++) {
                $height = $prdArr[$i]/$ratio*10;				
                $height = $height > 5 ? $height : 4;
                array_push($height_arr, $height);                                
            }
            static::$template_vars['heigth'] = $height_arr;
            
            
        
        
        
    }


    //  $this->displayName = $this->l('Watermark');
}

