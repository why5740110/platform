<?php
/**
 * 错误页面
 * @file ErrorController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-08-24
 */

namespace api\controllers;

use common\libs\Log;

class ErrorController extends CommonController
{

    /**
     * 错误方法
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-18
     * @return array
     */
    public function actionError()
    {
        $exception = \Yii::$app->errorHandler->exception;
        //@todo 发送异常信息
        Log::sendExceptionMessage($exception);
        return $this->jsonError($exception->getMessage());
    }
    
}