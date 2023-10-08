<?php
/**
 * 错误处理
 * @file SiteController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-10
 */

namespace mobile\controllers;

use yii\web\Controller;

class SiteController extends CommonController
{

    public function actionError()
    {
        $this->layout = false;
        \Yii::$app->getResponse()->statusCode = 404;
        return $this->render('404');
    }

    public function action404()
    {
        $this->layout = false;
        \Yii::$app->getResponse()->statusCode = 404;
        return $this->render('404');
    }
}