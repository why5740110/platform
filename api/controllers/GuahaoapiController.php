<?php
/**
 * 王氏医生加号相关接口
 * @file GuahaoapiController.php
 * @author zhangfan
 * @version 1.0
 * @date 2021/03/22
 */

namespace api\controllers;

use api\behaviors\ApiCheckerBehavior;
use api\behaviors\RecordLoging;
use common\libs\GuahaoCallback;
use common\models\GuahaoOrderModel;
use common\sdks\snisiya\SnisiyaSdk;
use common\libs\CommonFunc;
use yii\helpers\ArrayHelper;
use queues\ScheduleChangeJob;
use Yii;

class GuahaoapiController extends CommonController
{
    protected $postData;

    public function init()
    {
        parent::init();
        $this->verifyParam();
    }

    public function behaviors()
    {
        return [
            // 记录请求日志
            [
                'class' => ApiCheckerBehavior::class
            ],
            [
                'class' => RecordLoging::className()
            ]
        ];
    }

    /**
     * 验证参数
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/3/22
     */
    public function verifyParam()
    {
        $this->postData = ArrayHelper::merge(Yii::$app->request->get(), Yii::$app->request->post());
        $appid = ArrayHelper::getValue($this->postData, 'appid', 0);
        if (isset($this->postData['tp_first_department_id'])) {
            $this->postData['tp_frist_department_id'] = $this->postData['tp_first_department_id'];
        }
        if (isset($this->postData['first_department_name'])) {
            $this->postData['frist_department_name'] = $this->postData['first_department_name'];
        }
        switch ($appid) {
            case '2000000100'://通用测试来源
                $this->postData['tp_platform'] = 0;
                break;
            case '2000000060'://王氏医生加号
                $this->postData['tp_platform'] = 6;
                break;
            case '2000000112'://四川
                $this->postData['tp_platform'] = 12;
                break;
            default:
                $this->postData['tp_platform'] = -1;
                break;
        }

        if ($this->postData['tp_platform'] == 0) {
            exit(json_encode([
                'code' => 200,
                'msg' => '测试接口',
                'data' => []
            ]));
            return false;
        } elseif ($this->postData['tp_platform'] == -1) {
            exit(json_encode([
                'code' => 400,
                'msg' => '来源验证失败',
                'data' => []
            ]));
            return false;
        }

        unset($this->postData['appid']);
        unset($this->postData['os']);
        unset($this->postData['version']);
        unset($this->postData['time']);
        unset($this->postData['noncestr']);
        unset($this->postData['sign']);
    }

    /**
     * 排班变更通知
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/3/22
     */
    public function actionScheduleChange()
    {
        \Yii::$app->params['DataToHospitalRequest']['platform'] = $this->postData['tp_platform'] + 100;
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'scheduleChange';
        if (\Yii::$app->request->isPost) {
            $post = $this->postData;
            $scheduleId = ArrayHelper::getValue($post, 'tp_scheduling_id');
            //记录日志
            \Yii::$app->params['DataToHospitalRequest']['index'] = $scheduleId ?: '';

            // $snisiyaSdk = new SnisiyaSdk();
            // $result = $snisiyaSdk->scheduleChange($post);
            // if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            //     return $this->jsonSuccess(ArrayHelper::getValue($result, 'data'));
            // } else {
            //     return $this->jsonError(ArrayHelper::getValue($result, 'msg', '请求失败！'));
            // }
            ##异步处理
            \Yii::$app->queue->push(new ScheduleChangeJob(['postData' => $post]));
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
            return $this->jsonSuccess();
        }

        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
        return $this->jsonError('error');
    }

    /**
     * 订单变更通知
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/3/24
     */
    public function actionOrderChange()
    {
        \Yii::$app->params['DataToHospitalRequest']['platform'] = $this->postData['tp_platform'] + 100;
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderChange';
        if (\Yii::$app->request->isPost) {
            $changeMsg = [
                'needPay' => '待支付',
                'cancel' => '未支付取消',
                'cancelRefund' => '支付后取消并且退款',
                'cancelNotRefund' => '支付后取消并且不退款',
                'consult' => '下单成功待就诊',
                'complete' => '就诊完成',
                'patientMiss' => '患者爽约',
                'doctorMiss' => '医生停诊',
                'doctorMissRefund' => '医生停诊并退款',
            ];

            $post = $this->postData;

            $tp_order_id = ArrayHelper::getValue($post, 'tp_order_id');
            //记录日志
            \Yii::$app->params['DataToHospitalRequest']['index'] = $tp_order_id ?: '';
            if (!$tp_order_id) {
                \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
                return $this->jsonError('订单ID不能为空');
            }

            $state_desc = ArrayHelper::getValue($post, 'state_desc');
            $status = ArrayHelper::getValue($post, 'status', '');

            if (!array_key_exists($status, $changeMsg)) {
                \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
                return $this->jsonError('订单状态不合法');
            }

            // 获取订单
            $model = new GuahaoOrderModel();
            $order = $model->find()->where(['tp_platform' => $post['tp_platform'], 'tp_order_id' => $tp_order_id])->one();
            if ($order) {
                $state_desc = $state_desc ?: ArrayHelper::getValue($changeMsg, $status, '');
                switch ($status) {
                    case 'cancel':
                        $res = GuahaoCallback::orderCancel($order->order_sn);
                        break;
                    case 'complete':
                        $res = GuahaoCallback::orderResult($order->order_sn, 3);
                        break;
                    case 'patientMiss':
                        $res = GuahaoCallback::orderResult($order->order_sn, 4);
                        break;
                    case 'doctorMiss':
                        $res = GuahaoCallback::orderStop($order->order_sn);
                        break;
                    case 'consult':
                        $res = GuahaoCallback::orderConsult($order->order_sn);
                        break;
                    case 'needPay':
                    case 'cancelRefund':
                    case 'cancelNotRefund':
                    case 'doctorMissRefund':
                    default:
                        //退款 判断是否退款  取消
                        if (in_array($status, ['cancelRefund', 'doctorMissRefund', 'cancelNotRefund'])) {
                            //取消订单
                            $sdk = SnisiyaSdk::getInstance();
                            //tp_refund：第三方需要退款
                            $type = in_array($status, ['cancelRefund', 'doctorMissRefund']) ? 'tp_refund' : 'tp_cancel';
                            $result = $sdk->guahaoCancel(['id' => $order->order_sn, 'state_desc' => $state_desc, 'type' => $type]);
                            if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
                                //发短信
                                CommonFunc::guahaoSendSms('guahao_cancel', $order->order_sn);
                                $res = ['code' => 1, 'msg' => ''];
                                break;
                            } else {
                                $res = ['code' => 0, 'msg' => ArrayHelper::getValue($result, 'msg', '请求失败')];
                                break;
                            }
                        }
                        break;
                }
                if (ArrayHelper::getValue($res, 'code') != 1) {
                    \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
                    return $this->jsonError(ArrayHelper::getValue($res, 'msg', '请求失败'));
                } else {
                    \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
                    return $this->jsonSuccess();
                }
            } else {
                \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
                return $this->jsonError('订单不存在');
            }
        }
    }

    /**
     * 医生或者出诊机构信息变更通知
     * @return  [type]     [description]
     * @version 1.0
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     */
    public function actionDoctorChange()
    {
        \Yii::$app->params['DataToHospitalRequest']['platform'] = $this->postData['tp_platform'] + 100;
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'doctorChange';
        if (\Yii::$app->request->isPost) {
            $postData = $this->postData;
            $tp_platform = $this->postData['tp_platform'] ?? 6;
            $postData['tp_platform'] = $tp_platform;
            $tp_doctor_id = ArrayHelper::getValue($postData, 'tp_doctor_id', 0);
            //记录日志
            \Yii::$app->params['DataToHospitalRequest']['index'] = $tp_doctor_id ?: '';
            $tp_hospital_code = ArrayHelper::getValue($postData, 'tp_hospital_code', 0);
            $scheduleplace_hospital_id = ArrayHelper::getValue($postData, 'scheduleplace_hospital_id', 0);
            $scheduleplace_hospital_name = ArrayHelper::getValue($postData, 'scheduleplace_hospital_name', '');
            if (!$tp_doctor_id || !$scheduleplace_hospital_id) {
                \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
                return $this->jsonError('医生信息或者出诊机构信息不能为空！');
            }
            ##模拟请求
            CommonFunc::upDoctorVisitPlace($postData);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
            return $this->jsonSuccess();
        }
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
        return $this->jsonError('error');

    }

}