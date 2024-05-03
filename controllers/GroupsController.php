<?php

namespace app\controllers;

use Override;
use Yii;
use app\models\Groups;
use app\models\User;
use app\models\GroupToStudent;
use yii\rest\Controller;
use yii\rest\ActiveController;

class GroupsController extends ActiveController
{
    public $modelClass = 'app\models\Groups';
    private $_response = [
        "status" => '400',
        "data" => 'Что-то пошло не так!',
    ];

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create'], $actions['delete'], $actions['update']);

        return $actions;
    }

    public function actionCreate()
    {
        $request = Yii::$app->request;
        $params = $request->bodyParams;
        $response = [
            "status" => '400',
            "data" => 'Что-то пошло не так!',
        ];

        // Ищем данные автора
        $user = User::findByUsername($params['login']);

        // Вычисляем уровень доступа
        $permission = $user->checkPermission($params['login'], $params['auth_key']);

        // Проверяем может ли пользователь выполнить задачу
        if ($permission['role'] !== 1 || !$permission['approval']) {
            return $response;
        }

        // Проверяем не дублируется ли группа
        if (Groups::findAll(['owner_id' => $user->getId(), 'name' => $params['name']])) {
            $response['data'] = "У вас уже существует группа с таким именем!";
            return $response;
        }

        $group = new Groups();
        $token = Yii::$app->getSecurity()->generateRandomString();

        $group->name = $params["name"];
        $group->invite_token = $token;
        $group->owner_id = $user->getId();

        if ($group->save()) {
            $response = [
                "status" => '200',
                "data" => 'Группа создана',
                "invite_token" => $group->invite_token,
            ];
            return $response;
        }

        return $response;
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request;
        $params = $request->bodyParams;
        $response = [
            "status" => '400',
            "data" => 'Что-то пошло не так!',
        ];

        // Ищем данные автора
        $user = User::findByUsername($params['login']);

        // Вычисляем уровень доступа
        $permission = $user->checkPermission($params['login'], $params['auth_key']);

        // Проверяем может ли пользователь выполнить задачу
        if ($permission['role'] !== 1 || !$permission['approval']) {
            return $response;
        }

        // Проверяем не дублируется ли группа
        if (Groups::findAll(['owner_id' => $user->getId(), 'name' => $params['new_name']])) {
            $response['data'] = "У вас уже существует группа с таким именем!";
            return $response;
        }

        $group = Groups::findOne(['name' => $params['name'], 'owner_id' => $user->getId()]);
        if (!$group) {
            return $response;
        }

        $group->name = $params['new_name'];

        if ($group->save()) {
            $response = [
                "status" => '200',
                "data" => 'Имя изменено',
                "name" => $group->name,
            ];
            return $response;
        }

        return $response;
    }

    public function actionDelete()
    {
        $request = Yii::$app->request;
        $params = $request->bodyParams;
        $response = [
            "status" => '400',
            "data" => 'Что-то пошло не так!',
        ];

        // Ищем данные автора
        $user = User::findByUsername($params['login']);

        // Вычисляем уровень доступа
        $permission = $user->checkPermission($params['login'], $params['auth_key']);

        // Проверяем может ли пользователь выполнить задачу
        if ($permission['role'] !== 1 || !$permission['approval']) {
            return $response;
        }

        if (Groups::findOne(['owner_id' => $user->getId(), 'group_id' => $request->get('id')])) {
            Groups::deleteAll(['group_id' => $request->get('id')]);
            $response = [
                "status" => '200',
                "data" => 'Запись успешно удалена!',
            ];
            return $response;
        }

        return $response;
    }

    public function actionJoin($invite_token)
    {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $response = $this->_response;

        $user = User::findByUsername($params['login']);

        $permission = $user->checkPermission($params['login'], $params['auth_key']);

        if ($permission['role'] !== 0 || !$permission['approval']) {
            $response['data'] = "Отказано в доступе";
            return $response;
        }

        if (!Groups::findByInvite($request->get('invite_token')))
        {
            $response['data'] = 'Приглашение недействительно!';
            return $response;
        }
        
        $group_id = Groups::findByInvite($request->get('invite_token'))->getId();
        if (GroupToStudent::findAll(['group_id' => $group_id, 'student_id' => $user->getId()])) {
            $response['data'] = 'Вы уже состоите в группе!';
            return $response;
        }

        $group2student = new GroupToStudent();
        $group2student->group_id = $group_id;
        $group2student->student_id = $user->getId();

        if ($group2student->save()) {
            $response = [
                'status' => 200,
                'data' => 'Вы успешно вступили в группу!',
            ];
            return $response;
        }
        
        return $response;
    }
}




// Vobrw3lR_7D1oneZertxQRozr6qkO7ZA     Max
// BbDgwyakYmmEeYJvu-Wuiqh4zzkfUjv_     Anton