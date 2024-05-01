<?php

namespace app\models;

use \yii\db\ActiveRecord;

class Groups extends ActiveRecord
{

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['invite_token'], 'default', 'value' => ''],
            [['owner_id'], 'default', 'value' => -1],
        ];
    }

    public function getId()
    {
        return $this->group_id;
    }

    public function getInvite()
    {
        return $this->invite_token;
    }

    public function getOwner()
    {
        return $this->owner_id;
    }

    public static function getGroupsByOwner($owner_id)
    {
        return static::findAll(['owner_id' => $owner_id]);
    }
}
