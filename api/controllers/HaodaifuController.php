<?php
/**
 * @file HaodaifuController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/11/27
 */


namespace api\controllers;


use common\libs\CommonFunc;
use common\models\GuahaoOrderModel;
use common\models\HdfMessageModel;
use common\models\DoctorModel;
use common\models\TmpDoctorThirdPartyModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class HaodaifuController extends HdfcommonController
{
    const HDF_ScheduleChange = 15;
    const HDF_DocInfoChange = 1;
    const HDF_OrderChange = 16;
    const HDF_Message = 22;

    /**
     * 好大夫回调接口
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/8
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isPost) {
            $code = \Yii::$app->request->post('code', '');
        } else {
            $code = \Yii::$app->request->get('code', '');
        }

        switch ($code) {
            case self::HDF_ScheduleChange:
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'scheduleChange';
                return $this->scheduleChange();
            case self::HDF_DocInfoChange:
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'docInfoChange';
                return $this->docInfoChange();
            case self::HDF_OrderChange:
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderChange';
                return $this->orderChange();
            case self::HDF_Message:
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'message';
                return $this->message();
            default:
                //记录日志
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = $code;
                return $this->jsonError('error');
        }
    }

    /**
     * 接收好大夫方回调的短信接口
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2020/11/27
     */
    public function message()
    {

        $post = $this->postData;
        $questionId = ArrayHelper::getValue($post, 'content.questionId');
        //记录日志
        \Yii::$app->params['DataToHospitalRequest']['index'] = $questionId ?: '';
        if (!$questionId) {
            return $this->jsonError('问题ID不能为空');
        }
        $message = ArrayHelper::getValue($post, 'content.message');
        $patientId = ArrayHelper::getValue($post, 'content.patientId');
        $mobile = ArrayHelper::getValue($post, 'content.mobile');
        $model = new HdfMessageModel();
        //判断是否请求过
        $exists = $model->find()->where(['tp_order_id' => $questionId,'message'=>$message])->exists();
        if (!$exists) {
            $model->tp_order_id = $questionId;
            $model->message = $message;
            $model->tp_patient_id = $patientId;
            $model->mobile = $mobile;
            $model->time = time();
            $model->save();
        }
        return $this->jsonSuccess();
    }

    /**
     * 接收好大夫 退款请求
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2020/11/30
     */
    public function orderChange()
    {
        $changeMsg = [
            'NeedPay' => '待支付',
            'Cancel' => '支付前取消',
            'CancelRefund' => '支付后取消',
            'Delete' => '超时自动取消',
            'Consult' => '待就诊',
            'AdminRefuseRefund' => '审核拒绝退款',
            'Evaluation' => '待评价已完成',
            'Complete' => '订单完成',
            'PatientMiss' => '患者爽约',
            'DoctorMissRefund' => '医生爽约退款',
            'TPCancelResult' => '合作方申请退款结果'
        ];

        $post = $this->postData;

        $questionId = ArrayHelper::getValue($post, 'content.questionId');
        //记录日志
        \Yii::$app->params['DataToHospitalRequest']['index'] = $questionId ?: '';
        if (!$questionId) {
            return $this->jsonError('问题ID不能为空');
        }
        $message = ArrayHelper::getValue($post, 'content.message');
        $status = ArrayHelper::getValue($post, 'content.status');
        //status 包含'refund' 即需要退款
        $word = 'refund';
        // $questionId 获取订单
        $model = new GuahaoOrderModel();
        $order = $model->find()->where(['tp_order_id' => $questionId])->one();
        if ($order) {
            $state_desc = ArrayHelper::getValue($changeMsg,$status);
            //退款 判断是否退款  取消
            if (stripos($status, $word) !== false || $status == 'Cancel' || $status == 'CancelRefund' || $status == 'Delete' ) {
                //取消订单
                $sdk = SnisiyaSdk::getInstance();
                //tp_refund：第三方需要退款
                $type = stripos($status, $word) !== false ? 'tp_refund' : 'tp_cancel';
                $sdk->guahaoCancel(['id'=>$order->order_sn,'state_desc'=>$state_desc,'type'=>$type]);

            }else{
                $order->state_desc = $order->state_desc.'||'.$state_desc;
                $order->state_desc = ltrim($order->state_desc,'||');
                //其他状态
                if($order->state==0) {
                    if ($status == 'Complete' || $status == 'Evaluation' ) {
                        //完成
                        $order->state = 3;
                        if(!$order->complete_time){
                            $order->complete_time = time();
                        }
                        $order->update_time = time();
                        $order->save();
                    } elseif ($status == 'PatientMiss') {
                        //患者爽约
                        $order->state = 4;
                        $order->update_time = time();
                        $order->save();
                    }
                }elseif($order->state==3){
                    //已完成变爽约
                    if ($status == 'PatientMiss') {
                        //患者爽约
                        $order->state = 4;
                        $order->update_time = time();
                        $order->save();
                    }
                }

            }
            return $this->jsonSuccess();
        } else {
            return $this->jsonError('订单不存在');
        }

    }

    /**
     * 排班变更通知
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/2
     */
    public function scheduleChange()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $post = $this->postData;
            $scheduleId = ArrayHelper::getValue($post, 'content.scheduleId');
            //记录日志
            \Yii::$app->params['DataToHospitalRequest']['index'] = $scheduleId ?: '';

            $data = ArrayHelper::getValue($post, 'content', []);
            $data['tp_platform'] = 3;
            $data['doctorId'] = ArrayHelper::getValue($post, 'content.doctorId');
            $data['visittime'] = ArrayHelper::getValue($post, 'content.time');
            $snisiyaSdk = new SnisiyaSdk();
            $result = $snisiyaSdk->scheduleChange($data);
            if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
                return $this->jsonSuccess(ArrayHelper::getValue($result, 'data'));
            } else {
                return $this->jsonError(ArrayHelper::getValue($result, 'msg', '请求失败！'));
            }
        }

        return $this->jsonError('error');
    }

    /**
     * 医生信息改变通知
     */
    public function docInfoChange(){
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $post = $this->postData;
            $data = ArrayHelper::getValue($post, 'content', []);
            $data['tp_platform'] = 3;
            $data['tp_doctor_id'] = ArrayHelper::getValue($post, 'content.doctorId');
            //记录日志
            \Yii::$app->params['DataToHospitalRequest']['index'] = $data['tp_doctor_id'] ?: '';
            $snisiyaSdk = new SnisiyaSdk();
            $result = $snisiyaSdk->getDoctorIds($data);
            //更新排班
            $snisiyaSdk->updateSchedule(['tp_platform' => 3, 'tp_doctor_id' => $data['tp_doctor_id']]);
            if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
                TmpDoctorThirdPartyModel::updateDoctor($result,$data['tp_doctor_id'],$data['tp_platform']);
            }
            return $this->jsonSuccess();
        }

        return $this->jsonError('error');
    }

}