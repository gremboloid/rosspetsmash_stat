<?php


namespace app\stat\admin;


use app\stat\model\News;

/**
 * Description of AdminNews
 *
 * @author kotov
 */
class AdminNews extends AdminDirectory
{
    public function __construct() {
        $this->link = 'news';
        $this->name = l('GLOB_NEWS_SETTINGS','admin');
        $this->parentElement = 'Index';
        $this->directory_model = new News();
        $this->model_name = 'News';
        $this->left_block = false;
        $this->table_cols_headers = [ 'Number' => '№',
                                    'Name' => l('NEWS_HEAD','admin'),
                                    'StrDate' => l('NEWS_DATE_CREATE','admin'),
                                    ];
     /*   \Rosagromash\Application::addJS( array (
            _JS_DIR_.'tinyMCE/'.'tinymce.min.js',
            _JS_DIR_.'tinyMCE/'.'jquery.tinymce.min.js'));*/
        parent::__construct();
    }
}
