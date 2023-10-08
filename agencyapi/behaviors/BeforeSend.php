<?php
/**
 * Created by PhpStorm.
 * User: hito
 * Date: 2019/10/24
 * Time: 上午10:18
 */

namespace agencyapi\behaviors;

use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class BeforeSend extends Behavior
{
    public function events()
    {
        return [
            'beforeSend' => 'beforeSend'
        ];
    }

    /**
     * 响应格式化返回
     * @param $event
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-09
     * @throws \Exception
     */
    public function beforeSend($event)
    {
        /** @var Response $response */
        $response = $event->sender;
        $response->statusText = 'success';
        // 记录日志
        // $errData = Yii::$app->services->errorLog->record($response, true);

        // 格式化报错输入格式：threw抛出的异常级别
        if ($response->statusCode >= 500) {
            $msg = YII_DEBUG ? Yii::$app->getErrorHandler()->exception->getMessage() : '服务器迷路了，请联系客服';
            $response->setStatusCode(500, $msg);
            $response->data = [];
        }

        if ($response->statusCode == 401) {
            $response->setStatusCode(400, '账号未登录');
            $response->data = [];
        }
        // 提取系统的报错信息
//        ArrayHelper::getValue($response->data, 'msg')
//        if ($response->statusCode >= 300 && isset($response->data['data']['message']) && isset($response->data['data']['status'])) {
//            $response->data['msg'] = $response->data['data']['message'];
//        }

        $response->data = [
            'request_id' => $this->getRequestID(),
            'code' => $response->statusCode,
            'msg' => $response->statusText,
            'data' => ArrayHelper::getValue($response, 'data') ?: (Object)[],
        ];
        $response->format = yii\web\Response::FORMAT_JSON;
    }

    /**
     * 设置唯一的请求ID
     * @return string
     */
    private function getRequestID()
    {
        return md5(uniqid() . microtime() . mt_rand(111111, 999999));
    }
}