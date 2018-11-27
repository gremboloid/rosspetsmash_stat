<?php

namespace app\models\user;

use yii\web\IdentityInterface;

class UserIdentity extends LoginUser implements IdentityInterface
{
    
    public function getAuthKey() {
        return $this->autokey;
    }


    public function validateAuthKey($authKey) 
    {
        return $this->getAuthKey() === $authKey;
    }
    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null) {
        
    }

}
