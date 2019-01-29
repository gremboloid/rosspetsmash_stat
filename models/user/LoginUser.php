<?php

namespace app\models\user;

use \yii\db\ActiveRecord;
use app\stat\model\Contractor;
use \app\models\forms\UserLoginForm;
use app\stat\model\ContractorGeografy;
use app\stat\Sessions;
use \Yii;

/**
* Содержит данные залогиненнного пользователя
* @property string $name
* @property string $patronymicName
* @property string $surName
* @property string $login
* @property string $password
* @property string $oldPassword
* @property int $roleId
* @property int $contractorId
* @property int $Deleted
* @author kotov
 */
class LoginUser extends ActiveRecord
{
    # роли
    /** администратор */
    const ADMIN = 1;
    /** редактор */
    const EDITOR = 2;
    /** аналитик */
    const ANALITIC = 3;
    /** читатель  */
    const READER = 4;
    /** гость */
    const GUEST = 5; 
    /** Супер админ */
    const ROOT = [236,80];
    
    public static function tableName() {
        return 'TBLUSER';
    }
    public function setPassword( $value ) {
        $this->Pasword = $value;
    }
    public function getId() {
        return $this->Id;
    }
    public function getRoleId() {
        return $this->RoleId;
    }
    public function getContractorId() {
        return $this->ContractorId;
    }
    public function getSurName() {
        return $this->SurName;
    }
    public function getName() {
        return $this->Name;
    }
    public function getPatronymicName() {
        return $this->PatronymicName;
    }
    
    public function getPassword() {
        return $this->Pasword;
    }
    public function getOldPassword() {
        return $this->OldPassword;
    }
    public function getRole() {
        return $this->roleId;
    }
    public static function findUserByLogin(string $name) {
        return static::findOne(['Login' => $name]);
    }
    public function validatePassword($password) {
        if ($this->Deleted == 1) 
            return false;
        $passwordValidate =  Yii::$app->security->validatePassword($password, $this->password);
        if ($passwordValidate) {
            return true;
        }
        if (!empty($this->oldPassword)) {
            if (Yii::$app->security->validatePassword($password, $this->oldPassword)) { 
                Sessions::setValueToVar('OLD_PASSWORD_LOGIN', $this->getId());
                Yii::$app->response->redirect('/authorization');
            }
        }
        return false;        
    }
    public function getFIO() {
        return $this->surName. ' ' . $this->name . ' ' . $this->patronymicName;
    }
    public function isAdmin() {
        return ($this->roleId == self::ADMIN);
    }
    public function isRoot() {
        return in_array($this->getId(), self::ROOT);
    }

    public function isAnalytic() {
        return ($this->roleId == self::ANALITIC);
    }
    public function isReader() {
        return ($this->roleId == self::READER);
    }
    public function isDemo() {
        return ($this->roleId == self::GUEST);
    }
    public function isRosspetsmash() {
        $contractor = new Contractor($this->contractorId);        
        return ($contractor->isRosagromash == 1);
    }
    /**
     * Возвращает, является ли предприятие российским
     * @return bool
     */
    public function isRussian() {        
        $contractorGeografy = new ContractorGeografy($this->contractorId); 
        return ($contractorGeografy->countryId == 1);
    }
}