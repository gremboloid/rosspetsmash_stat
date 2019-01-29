<?php

namespace app\controllers;

use app\stat\admin\AdminRoot;
use app\stat\admin\AdminIndex;
use app\stat\Tools;
use app\stat\model\ModelRequest;
use app\stat\Validate;
use app\stat\services\ClassifierService;
use Yii;
/**
 * Админка
 *
 * @author kotov
 */
class AdminController extends FrontController
{
    const DEFAULT_NAMESPACE_FOR_ADMIN = 'app\\stat\\admin\\';
    
    public $controllerName = 'admin';
        
    /**
     * Элемент админской панели
     * @var AdminRoot 
     */
    public $element;
    /** @var array специальные объекты админки для определенных действий */
    protected $specialObjects = [
        'info',
    ];




    public function setParams() {
        parent::setParams();
        $elementName = Tools::getValFromURI(1);
        $elementName = $elementName ? 
            self::DEFAULT_NAMESPACE_FOR_ADMIN . 'Admin' . str_replace('-','',ucwords($elementName,'-')) : 
            self::DEFAULT_NAMESPACE_FOR_ADMIN .'AdminIndex';
        
        if (in_array($postFix = Tools::getValFromURI(2), $this->specialObjects)) {
            $elementName = $elementName . ucfirst($postFix);
            $param = Tools::getValFromURI(3);
        }
            
        if (class_exists($elementName)) {
            if ($param) {
                $this->element = new $elementName($param);
            } else {
                $this->element = new $elementName();
            }
        } else {
            $this->element = new AdminIndex();
        }
    }


    protected function initVars() {
        parent::initVars();
        $classifierService = new ClassifierService(Yii::$app->user->getIdentity()->getContractorId());        
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('MAIN_HEADER','admin');
        $this->tpl_vars['content_header'] = l('CONTENT_HEADER','admin');
                $this->tpl_vars['admin_menu'] = array(
            0 => ['name'=>'directories', 'text' => l('DIRECTORIES','admin')] ,
            1 => ['name'=>'portal_administrating', 'text' => l('PORTAL_ADMINISTRATING','admin')] ,
            2 => ['name'=>'statistic', 'text' => l('STATISTIC','admin')] ,
        );
        $this->tpl_vars['admin_menu'][1]['submenu'] = array(
            ['element'=>'global', 'text' => l('PORTAL_SETTINGS','admin')],
        //    ['element'=>'modules', 'text' => l('MODULES_SETTINGS','admin')],
            ['element'=>'classifier', 'text' => l('GLOB_CLASSIFIER_SETTINGS','admin')],
            ['element'=>'news', 'text' => l('NEWS','admin')],
          //  ['element'=>'requests', 'text' => l('MODEL_REQUESTS','admin')],
         //   ['element'=>'indicators', 'text' => l('INDICATORS','admin')],
       //     ['element'=>'polls', 'text' => l('POLLS','admin')],
         //   ['element'=>'polls_group', 'text' => l('POLLS_GROUP','admin')],
        );
        if (is_root()) {
            $this->tpl_vars['admin_menu'][1]['submenu'][] = ['element'=>'requests', 'text' => l('MODEL_REQUESTS','admin')];
        }
        $this->tpl_vars['admin_menu'][0]['submenu'] = array(            
            ['element'=>'contractors', 'text' => l('GLOB_CONTRACTORS_SETTINGS','admin')],
            ['element'=>'brands', 'text' => l('GLOB_BRANDS_SETTINGS','admin')],
            ['element'=>'users', 'text' => l('GLOB_USERS_SETTINGS','admin')],
            ['element'=>'models', 'text' => l('GLOB_MODELS_SETTINGS','admin')],
            ['element'=>'countries', 'text' => l('GLOB_COUNTRIES_SETTINGS','admin')],
            ['element'=>'currency', 'text' => l('GLOB_CURRENCY_SETTINGS','admin')],
            ['element'=>'units', 'text' => l('GLOB_UNITS_SETTINGS','admin')],
        );
        $this->tpl_vars['admin_menu'][2]['submenu'] = array(
            ['element' => 'index', 'text' => l('SUMMARY_INFORMATION','admin')],
            ['element' => 'form-control', 'text' => l('CONTRACTORS_FORM_CONTROL','admin')],
        );
        $this->tpl_vars['block_name'] = $this->element->getName();        
        $this->tpl_vars['test'] = $this->element->getTestData(); 
        $this->tpl_vars['admin_template'] = $this->element->display();
        $this->tpl_vars['left_block_enable'] = $this->element->isLeftBlockExist();
        $this->tpl_vars['left_block'] = $this->element->displayLeftBlock();
        if (is_subclass_of($this->element, self::DEFAULT_NAMESPACE_FOR_ADMIN . 'AdminDirectory')) {
            $this->tpl_vars['is_directory'] = true;
        } else {
            $this->tpl_vars['is_directory'] = false;
        }
        if (get_class($this->element) == self::DEFAULT_NAMESPACE_FOR_ADMIN . 'AdminNews') {
            $this->tpl_vars['is_news'] = true;
        } else {
            $this->tpl_vars['is_news'] = false;
        }
        $this->tpl_vars['modals_list'] = $this->element->modals_list;
        $this->tpl_vars['classifier_json'] = $classifierService->getClassifierListJSON();
       // $this->tpl_vars['breadcrumbs'] = $this->element->getBreadcrumbs();
    }
    public function actionIndex() {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('/authorization');
        }
        if (!is_admin()) {
            return $this->redirect('/index');
        }
        return $this->render('adminka.twig', $this->tpl_vars);
    }

    public function actionTest() {
        return 'test';
    }
    
}
