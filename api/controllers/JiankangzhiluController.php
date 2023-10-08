<?php
/**
 * @file JiankangzhiluController.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/9/7
 */

namespace api\controllers;

use api\behaviors\RecordLoging;
use common\libs\GuahaoCallback;
use common\models\GuahaoOrderModel;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class JiankangzhiluController extends \yii\web\Controller
{
    const TP_PLATFORM = 9;

    /**
     * 请求参数
     * @var array
     */
    public $params = [];

    public $key = '';

    public $secret = '';

    public function init()
    {
        parent::init();

        $this->key = ArrayHelper::getValue(\Yii::$app->params, 'gh_jiankangzhilu.key');
        $this->secret = ArrayHelper::getValue(\Yii::$app->params, 'gh_jiankangzhilu.secret');

        //$this->validatorSign();
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
     * 回调入口
     * @return array
     * @author xiujianying
     * @date 2021/9/9
     */
    public function actionIndex()
    {
        //校验签名
        $res = $this->validatorSign();
        if(!$res){
            \Yii::$app->params['DataToHospitalRequest']['platform'] = '109';
            \Yii::$app->params['DataToHospitalRequest']['paramsData'] = $this->params;
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => '1',
                'statusInfo' => 'token验证失败',
                't' => time()
            ];
        }

        try {
            $res = [];
            $params = $this->params;
            $tp_orderId = ArrayHelper::getValue($params, 'orderId');  //第三方订单id

            \Yii::$app->params['DataToHospitalRequest']['platform'] = '109';
            \Yii::$app->params['DataToHospitalRequest']['paramsData'] = $params;
            \Yii::$app->params['DataToHospitalRequest']['index'] = $tp_orderId;

            if (!$tp_orderId) {
                throw new \Exception('订单id orderId不能为空');
            }
            $order = GuahaoOrderModel::find()->where(['tp_order_id' => $tp_orderId, 'tp_platform' => self::TP_PLATFORM])->asArray()->one();
            if ($order) {
                $data = ArrayHelper::getValue($params, 'data');
                if($data){
                    $data = json_decode($data, true);
                }

                $type = ArrayHelper::getValue($params, 'type');
                switch ($type) {
                    case 101:
                        //挂号结果推送
                        if (ArrayHelper::getValue($params, 'isSuccess') == 1) {
                            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderCancel';
                            //回调公共类
                            $res = GuahaoCallback::orderCancel($order['order_sn']);
                        }
                        break;
                    case 102:
                        //退号结果推送
                        //取消类型，当type=102退号时存在。1普通退号；2停诊退号
                        $cancelType = ArrayHelper::getValue($data, 'cancelType');
                        if ($cancelType == 1) {
                            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderCancel';
                            //回调公共类
                            $res = GuahaoCallback::orderCancel($order['order_sn']);
                        } elseif ($cancelType == 2) {
                            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderStop';
                            //回调公共类
                            $res = GuahaoCallback::orderStop($order['order_sn']);
                        }
                        break;
                    case -101:
                        //订单操作推送
                        //1：取消订单 2：撤销订单 3：订单退款 4：完成订单
                        $orderOper = ArrayHelper::getValue($data, 'orderOper');
                        if ($orderOper == 1 || $orderOper == 2) {
                            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderCancel';
                            //回调公共类
                            $res = GuahaoCallback::orderCancel($order['order_sn']);
                        } elseif ($orderOper == 4) {
                            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderResult';
                            //回调公共类
                            $res = GuahaoCallback::orderResult($order['order_sn'], 3);
                        }

                        break;
                    default:
                        throw new \Exception('type类型异常');
                }
                \Yii::$app->params['DataToHospitalRequest']['res'] = $res;
                //处理返回结果
                if ($res && ArrayHelper::getValue($res, 'code') == 0) {
                    throw new \Exception(ArrayHelper::getValue($res, 'msg'));
                }

            } else {
                throw new \Exception('订单不存在');
            }

            return $this->jsonSuccess();

        } catch (\Exception $e) {
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderError';
            return $this->jsonError($e->getMessage());
        }

    }

    /**
     * 验证sign
     * @return bool|void
     * @throws \Exception
     * @author xiujianying
     * @date 2021/9/7
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

        $signed = $this->makeSign($data);
        if ($data && ArrayHelper::getValue($data, 'sign') == $signed) {
            $this->params = $data;
        } else {
            return false;
            exit(json_encode([
                'status' => '1',
                'statusInfo' => 'token验证失败',
                't' => time()
            ]));
        }
        return true;
    }

    /**
     * 加密
     * @param $params
     * @return string
     * @author xiujianying
     * @date 2021/9/18
     */
    protected function makeSign($params)
    {
        unset($params['sign']);
        unset($params['appId']);
        //按照参数名排序
        ksort($params);

        //连接待加密的字符串
        $codes = $this->key;
        //请求的URL参数
        foreach ($params as $key => $val) {
            $codes .= ($key . $val);
        }
        //$postData = $params;

        $codes .= $this->secret;
        $sign = strtoupper(sha1($codes));
        //$postData['appId'] = $this->key;
        //$postData['sign'] = $sign;
        return $sign;
    }

    /**
     * 返回成功的json数据
     * @return array
     */
    protected function jsonSuccess()
    {
        $return['status'] = 0;
        $return['statusInfo'] = '';
        $return['t'] = time();
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @return array
     */
    protected function jsonError($msg = 'errors')
    {
        $return['status'] = 1;
        $return['statusInfo'] = $msg;
        $return['t'] = time();
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
        return $this->jsonOutputCore($return);
    }

    /**
     * json输出的核心
     * @param $data
     * @return array
     */
    protected function jsonOutputCore($data)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

}