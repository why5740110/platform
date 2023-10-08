<?php
/**
 * 错误页面
 * @file ErrorController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-08-24
 */

namespace minyingapi\controllers;

use common\helpers\ApiResponseTrait;
use common\libs\Log;
use yii\web\Controller;

class ErrorController extends Controller
{
    use ApiResponseTrait;

    /**
     * 错误方法
     * @return array
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-18
     */
    public function actionError()
    {
        $exception = \Yii::$app->errorHandler->exception;
        // 发送异常信息
        Log::sendExceptionMessage($exception);
        return $this->jsonError($exception->getMessage());
    }

}