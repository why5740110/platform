<?php
/**
 * @file Jiankang160Controller.php
 * @author zhangfan
 * @version 1.0
 * @date 2021/2/5
 */

namespace api\controllers;

use api\behaviors\RecordLoging;
use common\libs\CommonFunc;
use common\models\GuahaoOrderModel;
use common\models\HdfMessageModel;
use common\models\TmpDoctorThirdPartyModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use common\libs\GuahaoCallback;

class Jiankang160Controller extends \yii\web\Controller
{

    protected $cid;
    protected $token;
    public $postData;

    public function init()
    {
        parent::init();

        $gh_jiankang160 = ArrayHelper::getValue(\Yii::$app->params, 'gh_jiankang160');
        $this->cid = ArrayHelper::getValue($gh_jiankang160, 'cid');
        $this->token = ArrayHelper::getValue($gh_jiankang160, 'token');

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
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/2/6
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

        $this->postData = $data;
        if (ArrayHelper::getValue($data, 'cid') != $this->cid || ArrayHelper::getValue($data, 'token') != $this->token) {
            exit(json_encode([
                'errorCode' => -1,
                'msg' => 'token验证失败',
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
        $return['state'] = 1;
        $return['msg'] = $msg;
        $return['content'] = $data;
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
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
        $return['state'] = $code;
        $return['msg'] = $msg;
        $return['content'] = [];
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

    /**
     * 回调接口
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/2/6
     */
    public function actionIndex()
    {
        $requestTypeArr = ['YUYUE_SUCCESS' => 'orderSuccess', 'PAY_SUCCESS' => 'orderPaySuccess', 'YUYUE_CANCEL' => 'cancelOrder', 'YUYUE_CHANGE' => 'orderChange', 'YUYUE_REPLACE' => 'orderChange', 'YUYUE_STOP' => 'orderStop'];
        $code = ArrayHelper::getValue($this->postData, 'eventId', '');
        $info = json_decode(ArrayHelper::getValue($this->postData, 'msgBody', ''), true);
        \Yii::$app->params['DataToHospitalRequest']['platform'] = '105';

        $tp_order_id = ArrayHelper::getValue($info, 'yuyueId', '');
        \Yii::$app->params['DataToHospitalRequest']['index'] = ArrayHelper::getValue($info, 'yuyueId', '');
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = (isset($requestTypeArr[$code])) ? $requestTypeArr[$code] : $code;

        if (!$tp_order_id) {
            return $this->jsonError('订单ID不能为空');
        }

        $model = new GuahaoOrderModel();
        $order = $model->find()->where(['tp_platform' => 5])->where(['tp_order_id' => $tp_order_id])->one();

        if (!$order) {
            return $this->jsonError('订单不存在');
        }

        switch ($code) {
            case 'YUYUE_SUCCESS':
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderSuccess';
                if ($order->state == 0) {
                    return $this->jsonSuccess();
                }
                $order->state = 0;
                $order->update_time = time();
                $order->save();
                break;
            case 'PAY_SUCCESS':
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderPaySuccess';
                if ($order->state == 0) {
                    return $this->jsonSuccess();
                }
                $order->update_time = time();
                $order->save();
                break;
            case 'YUYUE_CANCEL':
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = 'cancelOrder';
                if ($order->state == 1) {
                    return $this->jsonSuccess();
                }
                /*$order->state = 1;
                //发短信
                CommonFunc::guahaoSendSms('guahao_cancel', $order['order_sn']);*/
                //回调公共类
                GuahaoCallback::orderCancel($order['order_sn']);
                break;
            case 'YUYUE_CHANGE'://改诊
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderChange';
                //发短信
                $stop_type = '改诊';
                $stop_desc = '时间调整为：' . $info['changeToDate'] . $this->formatNoonCode($info['changeTimeType']) . $info['changeBeginTime'] . ' ，请您及时就诊。';
                $order->state_desc = $order->state_desc . "||$stop_type:$stop_desc";
                $order->state_desc = ltrim($order->state_desc, '||');
                $order->update_time = time();
                $order->save();
                CommonFunc::guahaoSendSms('guahao_stop', $order['order_sn'], $stop_type, $stop_desc);
                //更新排班缓存
                $snisiyaSdk = new SnisiyaSdk();
                $snisiyaSdk->updateScheduleCache(['doctor_id' => $order->doctor_id, 'tp_platform' => 5]);
                break;
            case 'YUYUE_REPLACE'://替诊
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderChange';
                //发短信
                $stop_type = '替诊';
                $stop_desc = '我们为您安排同级别医生' . $info['replaceDocName'] . '替诊，请您及时就诊。';
                $order->state_desc = $order->state_desc . "||$stop_type:$stop_desc";
                $order->state_desc = ltrim($order->state_desc, '||');
                $order->update_time = time();
                $order->save();
                CommonFunc::guahaoSendSms('guahao_stop', $order['order_sn'], $stop_type, $stop_desc);
                //更新排班缓存
                $snisiyaSdk = new SnisiyaSdk();
                $snisiyaSdk->updateScheduleCache(['doctor_id' => $order->doctor_id, 'tp_platform' => 5]);
                break;
            case 'YUYUE_STOP'://停诊
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderStop';
                if ($order->state == 2) {
                    return $this->jsonSuccess();
                }
                //回调公共类
                GuahaoCallback::orderStop($order['order_sn']);

                /*$order->state = 2;
                //发短信
                $stop_type = '停诊';
                $stop_desc = '您的预约被取消，给您带来的不便敬请理解。';
                $order->state_desc = $order->state_desc . "||$stop_type:$stop_desc";
                $order->state_desc = ltrim($order->state_desc, '||');
                CommonFunc::guahaoSendSms('guahao_stop', $order['order_sn'], $stop_type, $stop_desc);
                //更新排班缓存
                $snisiyaSdk = new SnisiyaSdk();
                $snisiyaSdk->updateScheduleCache(['doctor_id' => $order->doctor_id, 'tp_platform' => 5]);*/
                break;
            default:
                //记录日志
                //\Yii::$app->params['DataToHospitalRequest']['request_type'] = $code;
                return $this->jsonError('error');
        }

        /*$order->update_time = time();
        $order->save();*/
        return $this->jsonSuccess();
    }

    /**
     * 格式化午别
     * @param $str
     * @return int|string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/3/4
     */
    public function formatNoonCode($str)
    {
        $Arr = [
            'am' => '上午',//上午
            'pm' => '下午',//下午
            'em' => '晚上',//晚上
        ];
        return $Arr[$str] ?? '';
    }

}