?php
use app\stat\model\User;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__  . '/../config/functions.php';  
//require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$users = User::getRowsArray('Id');
var_dump($users);

