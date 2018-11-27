<?php

namespace app\models\forms;

use app\models\user\LoginUser;
use \app\models\user\UserIdentity;
use yii\base\Model;
use \Yii;
/**
 * Модель формы авторизации
 *
 * @author kotov
 */
class UserLoginForm extends Model
{    
    public $login;
    public $password;
    /**    
     * @var LoginUser
     */
    protected $userRecord;


    public function rules() {
        return [
            [['login','password'],'required','message' => 'Заполните поле'],
            ['login','errorIfUsernameNotFound'],
            ['password','errorIfPasswordWrong'],  
        ];
        
    }
    public function errorIfUsernameNotFound() {
        $this->userRecord = LoginUser::findUserByLogin($this->login);    
        if ($this->userRecord == null ) {
           $this->addError('login','Пользователь не существует');
       }
    }
    public function errorIfPasswordWrong() {
        if ($this->hasErrors()) {
            return ;
        } 
        if (!$this->userRecord->validatePassword($this->password)) {
            $this->addError('password','Неверный пароль');
        }
    }
    public function login() {
        if ($this->hasErrors()) {
            return ;
        }        
       $userIdentity = UserIdentity::findIdentity($this->userRecord->Id);
       Yii::$app->user->login($userIdentity);
    }
}
