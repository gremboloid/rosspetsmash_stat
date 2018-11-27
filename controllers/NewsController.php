<?php

namespace app\controllers;


use Yii;
use app\stat\Tools;
use app\stat\model\News;

/**
 * Description of NewsController
 *
 * @author kotov
 */
class NewsController extends FrontController
{
    public $controller_name = 'news';
    /** @var bool Показывать раздел новостей списком заголовков */
    protected $showNewsList = true;
    /** @var array ассоциативный массив с текущей новостью */
    protected $currentNews = array();
    /** @var array ассоциативный массив со списком новостей */
    protected $newsList = array();
    /**
     *
     * @var array предыдущая или следующая новость
     */
    protected $prevOrNext = array();


    public function setParams() {
        parent::setParams();
        $newsId = (int) Tools::getValFromURI(1);
        if ($newsId) {            
            //$this->_show_news_list = false;
            $currentNews = new News($newsId);
            if (is_resource($currentNews->content)) {
                $content = stream_get_contents($currentNews->content);
            } else {
                $content = $currentNews->content;
            }
            if ($currentNews->isBinding() ) {
                $this->showNewsList = false;
                $this->currentNews = [
                    'head' => $currentNews->name,
                    'content' => $content
                ];
               $this->prevOrNext = $currentNews->getNextAndPrevIds([['param' => 'Publicate' ,'staticNumber' => 1]],[['name'=> 'Date','sort' => 'DESC']]);
            }            
            
        }
    }
    
    public function initVars() {
        parent::initVars();
        $this->tpl_vars['title'] = $this->tpl_vars['title'] . ' - ' . l('TITLE_NEWS_PAGE');
        $this->tpl_vars['news_header'] = l('NEWS_HEADER');
        $this->tpl_vars['show_news_list'] = $this->showNewsList;
        $this->tpl_vars['current_news'] = $this->currentNews;
        $this->tpl_vars['news_list'] = $this->newsList;
        $this->tpl_vars['prev_next'] = $this->prevOrNext;
        $this->tpl_vars['button_next'] = l('NEXT_NEWS');
        $this->tpl_vars['button_prev'] = l('PREVIOUS_NEWS');
        
    }
    public function actionIndex() {
        if (Yii::$app->user->isGuest) {           
            return $this->redirect('/authorization');
        }
        return $this->render('news.twig', $this->tpl_vars);
    }
        
}
