<?php

namespace app\controllers;

use \Yii;
use app\stat\model\User;
use app\stat\model\NewPasswords;
use app\stat\Mailer;
use app\stat\Tools;

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
      //  $this->tpl_vars['testdata'] = $test_data;
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
    public function actionChangePassword() 
    {
        $sql = 'SELECT "u"."Id" FROM TBLUSER "u" WHERE "u"."ContractorId" IN (
                SELECT DISTINCT "c"."Id" FROM TBLCONTRACTOR "c", TBLCONTRACTORCATEGORY "cc" WHERE "cc"."CategoryId" = 1 AND "c"."Id" = "cc"."ContractorId")
                AND "u"."Email" != \' \' AND "u"."Pasword" != \' \'';// AND "u"."Id" = 338';
        $users = getDb()->querySelect($sql);
        foreach ($users as $val) {
            $user = new User((int) $val['Id']);
            $head = 'Внимание! Сменился пароль для доступа на портвл РОССПЕЦМАШ_СТАТ';
            $user->oldPassword = $user->pasword;
      /*      $newPasswd = Tools::generatePassword(8);
            $npUser = NewPasswords::getInstance([
                'id' => (int) $val['Id'],
                'password' => $newPasswd
            ]);
            
            $npUser->saveObject();*/
        //    $user->setNewPassword($newPasswd);
      //      $message = $this->getMessageForEmail($newPasswd);            
            $user->updateDb();
       //     $mailer = new Mailer();
       //     $mailer->sendMessage($user->email, $head, $message,true);
         //   echo $user->getName();
          //  unset ($user);
            unset ($user);
        }
    //    var_dump($users);
        die();
        return '1';
    }
    public function actionGeneratePassword() {
        $passwordsCount = 100;
        for ($i= 1 ;$i< $passwordsCount;$i++){
            echo 'Пароль #'.$i .': '. Tools::generatePassword(8) .'<br>' ;
        }
        die();
    }

        protected function getMessageForEmail($password) {
        return '<h2>Уважаемые коллеги!</h2>
<p>В связи с необходимостью выполнения п.2.2.5 и п.3.1 Соглашения об участии на Интернет-портале "Росспецмаш-стат"
о конфиденциальности параметров доступа, а также данных, содержащихся в системе,
инициирована <strong>автоматическая смена пароля участника.</strong></p>
 
<p><strong>Ваш новый пароль:</strong> <span>'.$password.'</span></p>
<p>С уважением, администрация портала РОССПЕЦМАШ-СТАТ</p>';                               
    }
}
