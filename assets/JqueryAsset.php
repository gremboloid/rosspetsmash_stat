<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Содержит плагины JQuery
 *
 * @author kotov
 */
class JqueryAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        '/js/jquery/jquery.modal.min.js',
        '/js/jquery/jstree.min.js',
        '/js/jquery/jstree.checkbox.js',
        '/js/jquery/jstree.search.js',
        '/js/jquery/jquery.validate.js',
        '/js/jquery/messages_ru.min.js',
    ];
    public $css = [
         'css/jquery/jquery.modal.css',        
         'css/jquery/jquery-ui.min.css',        
         'css/jquery/jquery.treeview.css',        
    ];
    public $depends = [
          'yii\web\YiiAsset'
    ];
}
