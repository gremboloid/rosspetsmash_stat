<?php

namespace app\stat\report;

use app\stat\report\reports\RootReport;
use app\stat\Sessions;
use app\stat\Tools;
use app\models\user\LoginUser;

/**
 * Description of Container
 *
 * @author kotov
 */
class Container 
{
    /**
     * @var array
     */
    protected $selectedReports;
    protected $defaultReport = 'manufacturers';
    
    
    public function __construct(LoginUser $user) 
    {        
        $defAlias = $this->defaultReport;
        $params = json_decode(Sessions::getReportParams(),true);
        $selectedReport = $params['selected_report'] ? 
        $params['selected_report'] : $defAlias;
        if ($selectedReport) {
            $this->selectedReports = array(self::getClass($user,$selectedReport));
        }              
    }
    
    
    /**
     * Фабричный метод, возвращает класс отчета, соответствующий его алиасу 
     * @param LoginUser $user текущий залогиненный пользователь
     * @param string $alias псевдоним (алиас) отчета
     * @return boolean|\Reports\className класс отчета
     */
    public static function getClass(LoginUser $user,string $alias) 
    {
        $className = 'app\\stat\\report\\reports\\'.ucfirst($alias).'Report'; 
        if (class_exists($className)) {
            return new $className($user);
        }
        return false;
    }
    /**
     * Возвращает список существующих отчетов
     * @return array
     */
    public static function getReportsList(LoginUser $user)
    {
        $listOfReports = array();
        $filesList = scandir(dirname(__FILE__).'/reports');
        foreach ($filesList as $currentFile)
        {
            if ($pos = strpos($currentFile, 'Report.php'))  {
                $className = substr ($currentFile,0,$pos);
                $classFullName = 'app\\stat\\report\\reports\\'. $className.'Report';
                if ($className !== 'Root' && class_exists($classFullName) && 
                        method_exists($classFullName, 'getReportName')) {
                    $obj = new $classFullName($user);
                    $order = $obj->order;
                    $listOfReports[$order] = array ('alias' => call_user_func(array ($obj,'getReportAlias')),
                            'object' => $obj, 'name' => call_user_func(array ($obj,'getReportName')), 'admin' => ($obj->is_admin || $obj->is_analitic ) );
                }
            }
        }
        ksort($listOfReports);
        return $listOfReports;                
    }
    /**
    * 
    * @param string $report
    */
    public static function getReportSettings(LoginUser $user, string $report)
    {       
        if ($obj = self::getClass($user, $report)) {
            if (method_exists($obj, 'getReportSettings')) {
                return call_user_func(array($obj,'getReportSettings'));
            }
        }
        return '<p>No settings available</p>';                    
    }
/**
 * Точка входа создания отчетов
 * @return type
 */
    public function createReport() {
        $result = array();
        if (count($this->selectedReports) === 0) {
           return Tools::getErrorMessage('Не выбрано ни одного отчета');
        }
        $index = 0;
        foreach ($this->selectedReports as $report)
        {                                    
            $result[$index] = $report->createReport();
            $index++;
        }
        return json_encode($result);        
    }
    public function createReportsExcel() {
        if  (count($this->selectedReports) != 1 ) {
            return;
        }
        $result = $this->selectedReports[0]->createReport( 1 );
        if ($result['errorCode'] == 1) {
            echo ('<h1>'.$result['message'].'</h1><a href="/reports">Вернуться на сайт</a>');
            die();
        }        
    }
    
    
}
