<?php
	
    use yii\web\Application;
    
    define('START_FLAG',true );
    
    define('YII_DEBUG',false );
    require __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
    
    $config = (YII_DEBUG) ? require __DIR__  . '/../config/web-debug.php' :
                            require __DIR__  . '/../config/web.php';  
    require __DIR__  . '/../config/functions.php';  
    require __DIR__  . '/../config/defines.inc.php';  
    (new Application($config))->run();
?>