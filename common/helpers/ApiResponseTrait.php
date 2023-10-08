<?php
/**
 * api响应类
 * Created by wangwencai.
 * @file: SendJson.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-15
 */

namespace common\helpers;

use yii\web\Response;

Trait ApiResponseTrait
{
    public $requestID;

    /**
     * 返回成功的json数据
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function jsonSuccess($data = [], $msg = 'success')
    {
        $return['request_id'] = $this->requestID;
        $return['code'] = 200;
        $return['msg'] = $msg;
        $return['data'] = $data;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @param int $code
     * @return array
     */
    protected function jsonError($msg = '', $code = 400)
    {
        $return['request_id'] = $this->requestID;
        $return['code'] = $code;
        $return['msg'] = $msg;
        $return['data'] = (object)[];
        return $this->jsonOutputCore($return);
    }

    /**
     * 退出返回json
     * @param string $message
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-15
     */
    public function exitJson($message = 'error')
    {
        header('Content-Type: application/json; charset=UTF-8');
        $return = [
            'request_id' => $this->requestID,
            'code' => 401,
            'msg' => $message,
            'data' => (Object)[]
        ];
        exit(json_encode($return));
    }

    /**
     * json输出的核心
     * @param $data
     * @return array
     */
    protected function jsonOutputCore($data)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        //所有内容输出转换为字符型;
        array_walk_recursive($data['data'], function (&$value) {
            $value = strval($value);
        });
        return $data;
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