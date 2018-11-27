<?php

namespace app\controllers;

use \Yii;

/**
 * Тестовый контороллер
 *
 * @author kotov
 */
class TestController extends FrontController
{
    public function initVars() {
        parent::initVars();
      // $test_data = var_export(getDb()->getFieldsFromTable('TBLROLE'),true);
      // $city = new \app\stat\model\City(95);
      // $test_data = $city->name;
      //  $pagination = new \app\stat\Pagination();
     //   $pagination->getPagination();
      //  $test_data = print_r($pagination->getPagination(), true);
    //  \app\stat\Configuration::loadConfiguration();
  //    $test_data = print_r(\app\stat\Configuration::getConfigururation(),true);
      //  $formService = new \app\stat\services\InputFormService(['contractor' => 644])    ;    
     //   $test_data = print_r(\app\stat\report\Container::getReportsList(),true);      
        $this->tpl_vars['testdata'] = $test_data;
    }

    public function actionIndex() {    
      //  throw new \yii\base\ErrorException('Эта проблема критичная');
       // throw new \app\stat\exceptions\DefaultException('error');
        return $this->render('test.twig', $this->tpl_vars);
    }
    public function actionTest() {
        throw new \app\stat\exceptions\DefaultException('error');
      //  return Yii::getAlias('@web/css');
    }
    public function actionModel() {
     //   $city = new \app\stat\model\City('g');
    }
}
