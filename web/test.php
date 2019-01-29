<?php 
use app\stat\Tools;
use app\stat\mock\MockTableWidthEvents;
use yii\web\Application;
//phpinfo(); 
require __DIR__ . '/../vendor/autoload.php';
require __DIR__  . '/../config/functions.php'; 
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__  . '/../config/web-debug.php';
new Application($config);

//echo Tools::getUri();
$mockObjectWithEvent = new MockTableWidthEvents();
$this->id = 5;
$mockObjectWithEvent->saveObject();
?>