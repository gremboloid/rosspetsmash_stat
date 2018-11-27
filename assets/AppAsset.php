<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Description of AppAsset
 *
 * @author kotov
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/normalize.css',
         'css/fontawesome-all.css',
         'css/global.css',        
    ];
    public $js = [
        'js/functions.js',
        'js/main.js',
    ];
    public $depends = [
          'app\assets\JqueryAsset'
    ];
            
    
}
