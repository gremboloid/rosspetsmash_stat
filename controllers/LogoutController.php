<?php

namespace app\controllers;

use Yii;
/**
 * Description of LogoutController
 *
 * @author kotov
 */
class LogoutController extends FrontController
{
    public function actionIndex() 
    {
        Yii::$app->user->logout();
        return $this->redirect("/");
        
    }
}
