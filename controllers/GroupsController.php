<?php

namespace app\controllers;

use Override;
use Yii;
use app\models\Groups;
use app\models\User;
use app\models\GroupToStudent;
use yii\rest\Controller;

class GroupsController extends Controller
{
    public $modelClass = 'app\models\Groups';

    public function actionGet()
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

        if ($request->get('id')) {
            return Groups::findAll(['owner_id' => $user->getID(), 'group_id' => $request->get('id')]);
        }

        return Groups::findAll(['owner_id' => $user->getID()]);
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

    /*
        1) Получение студентов по группе!
        2) Проверить все ответы!
    */
}