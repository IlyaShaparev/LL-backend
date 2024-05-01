<?php

namespace app\models;

use \yii\db\ActiveRecord;

class GroupToStudent extends ActiveRecord
{

    public function rules()
    {
        return [

            [['group_id', 'student_id'], 'required']
        ];
    }

    public function getStudentsById($group_id)
    {
        return $this->static::findAll(['group_id' => $group_id]);
    }

    public function getGroupsByStudentId($student_id)
    {
        return $this->static::findAll(['student_id' => $student_id]);
    }
}
