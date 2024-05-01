<?php

namespace app\models;

use \yii\db\ActiveRecord;

class User extends ActiveRecord
{

    public function rules()
    {
        return [
            
            [['login', 'password_hash', 'name', 'surname', 'email', 'role', 'auth_key'], 'required']
        ];
    }

    /**
     * Finds user by login
     *
     * @param string $login
     * @return static|null
     */
    public static function findByUsername($login)
    {
        return static::findOne(['login' => $login]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @param string $hash hash to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password, $hash)
    {
        $hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        return $this->password_hash === $hash;
    }

    public static function validateAuthKey($login, $auth_key)
    {
        $model_key = static::findByUsername($login)->auth_key;
        return $model_key === $auth_key;
    }
}
