<?php

namespace backend\controllers;

use yii\web\Controller;

class TestController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {

        return $this->render('index');
    }
}