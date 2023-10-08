<?php
/**
 * 山西回调
 * @file ShanxiController.php
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

class ShanxiController extends \yii\web\Controller
{
    const TP_PLATFORM = 8;

    /**
     * 请求参数
     * @var array
     */
    public $params = [];

    public $key = '';

    public $secret = '';

    public $server = [];

    public function init()
    {
        parent::init();

        $this->key = ArrayHelper::getValue(\Yii::$app->params, 'gh_shanxi.key');
        $this->secret = ArrayHelper::getValue(\Yii::$app->params, 'gh_shanxi.secret');

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
        $res = $this->validatorSign();
        if(!$res){
            \Yii::$app->params['DataToHospitalRequest']['platform'] = '108';
            \Yii::$app->params['DataToHospitalRequest']['paramsData'] = $this->params;

            \Yii::$app->params['DataToHospitalRequest']['paramsHead'] = $this->server;

            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'code' => '1',
                'message' => 'token验证失败',
                'rsp' => []
            ];
        }

        try {
            $res = [];
            $params = $this->params;
            $tp_order_id = ArrayHelper::getValue($params, 'appointId');  //第三方订单id

            \Yii::$app->params['DataToHospitalRequest']['platform'] = '108';
            \Yii::$app->params['DataToHospitalRequest']['paramsData'] = $params;
            \Yii::$app->params['DataToHospitalRequest']['index'] = $tp_order_id;

            if (!$tp_order_id) {
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderError';
                throw new \Exception('订单id orderId不能为空');
            }
            sleep(2);
            $order = GuahaoOrderModel::find()->where(['tp_order_id' => $tp_order_id, 'tp_platform' => self::TP_PLATFORM])->asArray()->one();
            if ($order) {
                $TreatStatus = ArrayHelper::getValue($params, 'treatStatus'); //诊疗状态
                $OrderStatus = ArrayHelper::getValue($params, 'orderStatus'); //订单状态
                //取消 (停诊也走取消)
                if ($TreatStatus == -1 || $OrderStatus == 8) {
                    \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderCancel';
                    $res = GuahaoCallback::orderCancel($order['order_sn']);
                }
                //已就诊   已完成  //不支持通知
                /*if ($TreatStatus == 4) {
                    \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderResult';
                    $res = GuahaoCallback::orderResult($order['order_sn'], 3);
                }
                //已爽约
                if ($TreatStatus == 5) {
                    \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderResult';
                    $res = GuahaoCallback::orderResult($order['order_sn'], 4);
                }*/

                \Yii::$app->params['DataToHospitalRequest']['res'] = $res;
                //处理返回结果
                if ($res && ArrayHelper::getValue($res, 'code') == 0) {
                    throw new \Exception(ArrayHelper::getValue($res, 'msg'));
                }

            } else {
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderError';
                throw new \Exception('订单不存在');
            }

            return $this->jsonSuccess();

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     *
     * @return bool|void
     * @throws \Exception
     * @author xiujianying
     * @date 2021/9/9
     */
    private function validatorSign()
    {
        $data = [];
        if (empty($_POST) && false !== strpos( ArrayHelper::getValue($_SERVER,'HTTP_CONTENT_TYPE'), 'application/json')) {
            $content = file_get_contents('php://input');
            $data = json_decode($content, true);
        } else {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();
            }
        }

        $key = ArrayHelper::getValue($_SERVER, 'HTTP_UH_RDSP_APP_ID');
        $timestamp = ArrayHelper::getValue($_SERVER, 'HTTP_UH_RDSP_TIMESTAMP');
        $signature = ArrayHelper::getValue($_SERVER, 'HTTP_UH_RDSP_SIGNATURE');

        $sign = $this->makeSign($timestamp);
        $this->params = $data;
        $this->server = $_SERVER;
        if ($key == $this->key && $signature == $sign) {

            $this->params = $data;

        } else {
            return false;
            $return['code'] = 1;
            $return['message'] = 'token验证失败';
            $return['rsp'] = [];
            exit(json_encode($return));

        }
        return true;
    }

    protected function makeSign($microtime)
    {
        return hash_hmac('sha256', $this->key . $microtime, $this->secret);
    }

    /**
     * 返回成功的json数据
     * @return array
     */
    protected function jsonSuccess()
    {
        $return['code'] = 0;
        $return['message'] = 'success';
        $return['rsp'] = [];
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @return array
     */
    protected function jsonError($msg = '')
    {
        $return['code'] = 1;
        $return['message'] = $msg;
        $return['rsp'] = [];
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