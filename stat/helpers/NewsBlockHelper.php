<?php


namespace app\stat\helpers;


use app\stat\Configuration;
use app\stat\model\News;

class NewsBlockHelper {
    /** @var int Количество отображаемых новостей на главной странице */
    protected $_main_page_news_count;
    
    
    public function __construct() {
        $this->_main_page_news_count = Configuration::get('MainPageNewsCount', 1);        
    }
    public function getNewsAnonsForMainPage()
    {
        $result = News::getRows(['"Id"','"Name"','"Anons","Date","PublishDate","Content"'],$this->_main_page_news_count,[['param' => 'Publicate','staticNumber' => 1]],[['name'=> 'Date','sort' => 'DESC']]);
        foreach ($result as $key=> $val) {
            if (is_resource($val['Content'])) {
                $result[$key]['Text'] = stream_get_contents($val['Content']);
            } else {
                $result[$key]['Text'] = $val['Content'];
            }            
        }
        return $result;
    }
    public function getNewsHeadList() {
        return getDb()->getRows([
                ['n','Id'],
                ['n','Name']
            ], 
                ['TBLNEWS','n'],
            ['param' => 'Publicate', 'staticNumber' => 1],
                [['name' => 'n.PublishData'],['name' => 'n.Id']]);
    }
    
}
