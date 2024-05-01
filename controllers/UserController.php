<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function actionRegistration()
    {

        // Этот метод может кинуть PDO Exeption по дефолтному значению!!!

        $request = Yii::$app->request;
        $params = $request->bodyParams;
        $response = [
            "status" => '400',
            "data" => 'Что-то пошло не так!',
        ];

        // Проверяем не занят ли логин
        if (User::findByUsername($params['login'])) {
            $response['data'] = "Пользователь с таким логином уже существует!";
            return $response;
        }

        // инициализируем модель User
        $model = new User();
        $model->auth_key = Yii::$app->getSecurity()->generateRandomString();
        foreach ($params as $key => $value) {

            // Если попался пароль -> хэшируем его
            if ($key == "password") {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($value);
            } else {
                $model->$key = $value;
            }
        }

        // Меняем ответ на успешный, если все сохранилось
        if ($model->save()) {
            $response = [
                "status" => '200',
                "data" => 'Создан пользователь!',
            ];
            return $response;
        }

        return $response;
    }

    public function actionAuth()
    {

        // Сделать проверку на наличие параметров (пароль)

        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $response = [
            "status" => '400',
            "data" => 'Отказано в доступе!',
        ];

        if (!$params['password']) {
            return $response;
        }

        $model = User::findByUsername($params['login']);

        if ($model && Yii::$app->getSecurity()->validatePassword($params['password'], $model->password_hash)) {
            $response = [
                "status" => '200',
                "data" => 'Добро пожаловать!',
                "login" => $model->login,
                "auth_key" => $model->auth_key,
            ];
            return $response;
        }
        return $response;
    }
}
