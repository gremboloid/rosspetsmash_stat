<?php

namespace app\models\user;

use \yii\db\ActiveRecord;
use app\stat\model\Contractor;
use \app\models\forms\UserLoginForm;
use app\stat\model\ContractorGeografy;
use \Yii;

/**
* Содержит данные залогиненнного пользователя
* @property string $name
* @property string $patronymicName
* @property string $surName
* @property string $login
* @property string $password
* @property int $roleId
* @property int $contractorId
* @property int $Deleted
* @author kotov
 */
class LoginUser extends ActiveRecord
{   
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
    public function getRole() {
        return $this->roleId;
    }
    public static function findUserByLogin(string $name) {
        return static::findOne(['Login' => $name]);
    }
    public function validatePassword($password) {
        if ($this->Deleted == 1) 
            return false;
        return Yii::$app->security->validatePassword($password, $this->password);
    }
    public function getFIO() {
        return $this->surName. ' ' . $this->name . ' ' . $this->patronymicName;
    }
    public function isAdmin() {
        return ($this->roleId == 1);
    }
    public function isAnalytic() {
        return ($this->roleId == 3);
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