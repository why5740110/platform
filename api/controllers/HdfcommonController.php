<?php
/**
 * 接收好大夫请求接口
 * @file HdfcommonController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/11/26
 */


namespace api\controllers;

use api\behaviors\RecordLoging;
use common\libs\CommonFunc;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class HdfcommonController extends \yii\web\Controller
{

    protected $partnerKey;
    protected $secret;
    public $postData;

    public function init()
    {
        parent::init();
        //好大夫功能下架
        exit(json_encode([
            'errorCode' => -1,
            'msg' => 'error',
            'content' => []
        ]));
        return false;

        $gh_haodaifu = ArrayHelper::getValue(\Yii::$app->params,'gh_haodaifu');
        $this->secret = ArrayHelper::getValue($gh_haodaifu,'secret');
        $this->partnerKey = ArrayHelper::getValue($gh_haodaifu,'partnerKey');

        $this->validatorSign();

    }

    public function behaviors()
    {
        return [
            // 记录请求日志
            [
                'class' => RecordLoging::className()
            ]
        ];
    }

    /**
     * 验证签名
     * @return bool
     * @throws \Exception
     * @author xiujianying
     * @date 2020/11/27
     */
    private function validatorSign()
    {
        $data = [];
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
        }
        if (\Yii::$app->request->isGet) {
            $data = \Yii::$app->request->get();
        }

        $res = CommonFunc::validatorApi($this->partnerKey, $this->secret, $data);
        //解析参数 content  是个json
        $body = ArrayHelper::getValue($data,'content');
        if($body){
            $body = json_decode($body,true);
        }
        $data['content'] = $body;
        $this->postData = $data;
        if (ArrayHelper::getValue($res, 'code') != 0) {
            //暂时关闭签名验证
//            //记录日志 关闭redis日志
//            $redis = \Yii::$app->redis_codis;
//            if (\Yii::$app->request->isPost) {
//                $isPost = 'post';
//            } else {
//                $isPost = 'get';
//            }
//            $redis->LPUSH('haodaifu:log', json_encode(['type' => 'sign error', 'isPost' => $isPost, 'param' => $this->postData, 'result' => [
//                'errorCode' => -1,
//                'msg' => ArrayHelper::getValue($res, 'msg'),
//                'content' => []
//            ]]));

            exit(json_encode([
                'errorCode' => -1,
                'msg' => ArrayHelper::getValue($res, 'msg'),
                'content' => []
            ]));
            return false;
        }
        return true;
    }


    /**
     * 返回成功的json数据
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function jsonSuccess($data = [], $msg = 'success')
    {
        $return['errorCode'] = 0;
        $return['msg'] = $msg;
        $return['content'] = $data;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @param int $code
     * @return array
     */
    protected function jsonError($msg = '', $code = -1)
    {
        $return['errorCode'] = $code;
        $return['msg'] = $msg;
        $return['content'] = [];
        return $this->jsonOutputCore($return);
    }

    /**
     * json输出的核心
     * @param $data
     * @return array
     */
    protected function jsonOutputCore($data)
    {
//        //记录日志 关闭redis日志
//        $redis = \Yii::$app->redis_codis;
//        if (\Yii::$app->request->isPost) {
//            $isPost = 'post';
//        } else {
//            $isPost = 'get';
//        }
//        $redis->LPUSH('haodaifu:log', json_encode(['isPost' => $isPost, 'param' => $this->postData, 'result' => $data]));

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

}