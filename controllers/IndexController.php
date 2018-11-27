<?php

namespace app\controllers;

use app\stat\helpers\NewsBlockHelper;
use app\stat\Module;
use \Yii;


/**
 * Главная страница
 *
 * @author kotov
 */
class IndexController extends FrontController
{
    public $controller_name = 'index';   
    /** @var bool показ блоков с новостями */
    public $show_news;
    /** @var string новости на главной */
    public $news_list = array();
 
    public function init() {
        parent::init();
        $this->show_news = true;
    }

    public function setParams() {
        parent::setParams();
        if ($this->show_news) {
            $newsHelper = new NewsBlockHelper();
            $this->news_list = $newsHelper->getNewsAnonsForMainPage();            
        }
    }

    protected function initVars() {
        parent::initVars();
        $modulesVars = Module::$template_vars;
        if (key_exists('index', $modulesVars)) {
            if (is_array($modulesVars['index'])) {
                $this->tpl_vars['main_page_modules'] = array();
                foreach ($modulesVars['index'] as $pos => $val) {
                    $this->tpl_vars['main_page_modules']['column'.$pos] = $val;
                }
            }
        }
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('TITLE_MAIN_PAGE');
        $this->tpl_vars['news_section'] = l('NEWS_SECTION');
        $this->tpl_vars['indicators_section'] = l('INDICATORS_SECTION');
        $this->tpl_vars['counters_section'] = l('COUNTERS_SECTION');
        $this->tpl_vars['show_news'] = $this->show_news;
        $this->tpl_vars['news_list'] = $this->news_list;
        
        
        
    }

    public function actionIndex() {
        if (Yii::$app->user->isGuest) {           
            return $this->redirect('/authorization');
        }
        return $this->render('index.twig', $this->tpl_vars);
    }
    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->redirect("/");
    }
}
