<?php
/**
 * 处理挂号回调
 * @file GuahaoCallback.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/9/7
 */

namespace common\libs;

use common\models\GuahaoOrderModel;
use common\sdks\snisiya\SnisiyaSdk;

class GuahaoCallback
{

    /**
     * 医院方发起 订单停诊
     * @param $order_sn
     * @param string $msg
     * @return array|int[]
     * @author xiujianying
     * @date 2021/9/7
     */
    public static function orderStop($order_sn, $msg = '')
    {
        $warningMsg = '';
        try {
            $model = GuahaoOrderModel::find()->where(['order_sn' => $order_sn])->one();
            if ($model) {
                if ($model->state == 0) {
                    $model->state = 2;
                    //发短信
                    $stop_type = '停诊';
                    $stop_desc = $msg ? $msg : '您的预约被取消，给您带来的不便敬请理解。';
                    $model->state_desc = $model->state_desc . "||$stop_type:$stop_desc";
                    $model->state_desc = ltrim($model->state_desc, '||');
                    $model->update_time = time();
                    $res = $model->save();
                    if ($res) {
                        CommonFunc::guahaoSendSms('guahao_stop', $order_sn, $stop_type, $stop_desc);
                        //结果推送给合作方 （停诊订单推送合作方为取消状态）
                        CommonFunc::guahaoPushQueue($model->id, 2, 2, $model->tp_platform);
                        //更新排班缓存
                        $snisiyaSdk = new SnisiyaSdk();
                        $snisiyaSdk->updateScheduleCache(['doctor_id' => $model->doctor_id, 'tp_platform' => $model->tp_platform]);
                    }else{
                        throw new \Exception('服务器异常，请重试');
                    }

                } elseif ($model->state == 2) {
                    //throw new \Exception('订单已是停诊状态，不要重复操作');
                    $warningMsg = '订单已是停诊状态，不要重复操作';
                } else {
                    //throw new \Exception('订单状态非已预约状态|status:' . $model->state);
                    $warningMsg = '订单状态非已预约状态|status:' . $model->state;
                }
            } else {
                throw new \Exception('订单不存在');
            }
            return ['code' => 1,'warningMsg'=>$warningMsg];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }


    /**
     * 医院方发起 订单取消
     * @param $order_sn
     * @param string $msg
     * @return array|int[]
     * @author xiujianying
     * @date 2021/9/7
     */
    public static function orderCancel($order_sn, $msg = '')
    {
        $warningMsg = '';
        try {
            $model = GuahaoOrderModel::find()->where(['order_sn' => $order_sn])->one();
            if ($model) {
                if ($model->state == 0) {
                    $model->state = 1;
                    //发短信
                    $stop_type = '取消';
                    $stop_desc = $msg ? $msg : '您的预约被取消，给您带来的不便敬请理解。';
                    $model->state_desc = $model->state_desc . "||$stop_type:$stop_desc";
                    $model->state_desc = ltrim($model->state_desc, '||');
                    $model->update_time = time();
                    $res = $model->save();
                    if ($res) {
                        //短信通知
                        CommonFunc::guahaoSendSms('guahao_stop', $order_sn, $stop_type, $stop_desc);

                        //结果推送给合作方
                        CommonFunc::guahaoPushQueue($model->id, 2, 2, $model->tp_platform);

                        //更新排班缓存
                        $snisiyaSdk = new SnisiyaSdk();
                        $snisiyaSdk->updateScheduleCache(['doctor_id' => $model->doctor_id, 'tp_platform' => $model->tp_platform]);
                    }else{
                        throw new \Exception('服务器异常，请重试');
                    }

                } elseif ($model->state == 1) {
                    //throw new \Exception('订单已是取消状态，不要重复操作');
                    $warningMsg = '订单已是取消状态，不要重复操作';
                } else {
                    //throw new \Exception('订单状态非已预约状态|status:' . $model->state);
                    $warningMsg = '订单状态非已预约状态|status:' . $model->state;
                }
            } else {
                throw new \Exception('订单不存在');
            }
            return ['code' => 1,'warningMsg'=>$warningMsg];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 订单结果 只更新状态无其他操作
     * @param $order_sn
     * @param $state int   订单状态 3 完成  4 爽约
     * @return array|int[]
     * @author xiujianying
     * @date 2021/9/9
     */
    public static function orderResult($order_sn, $state)
    {
        $warningMsg = '';
        try {

            if (!in_array($state, [3, 4])) {
                throw new \Exception('状态不在约定中，state:' . $state);
            }

            $model = GuahaoOrderModel::find()->where(['order_sn' => $order_sn])->one();
            if ($model) {
                if ($model->state == 0) {
                    $model->state = $state;
                    $model->update_time = time();
                    $res = $model->save();
                    if(!$res){
                        throw new \Exception('服务器异常，请重试');
                    }
                } else {
                    //throw new \Exception('订单状态非已预约状态|status:' . $model->state);
                    $warningMsg = '订单状态非已预约状态|status:' . $model->state;
                }
            } else {
                throw new \Exception('订单不存在');
            }
            return ['code' => 1,'warningMsg'=>$warningMsg];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function orderTizhen($order_sn,$tp_doctor_old,$tp_doctor_name_old,$tp_doctor_new,$tp_doctor_name_new){

    }

    /**
     * 下单成功待就诊 只更新状态无其他操作
     * @param $order_sn
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2022/1/6
     */
    public static function orderConsult($order_sn)
    {
        $warningMsg = '';
        try {
            $model = GuahaoOrderModel::find()->where(['order_sn' => $order_sn])->one();
            if ($model) {
                if ($model->state == 8) {
                    $model->state = 0;
                    $model->update_time = time();
                    $res = $model->save();
                    if (!$res) {
                        throw new \Exception('服务器异常，请重试');
                    }
                } else {
                    $warningMsg = '订单状态非待审核状态|status:' . $model->state;
                }
            } else {
                throw new \Exception('订单不存在');
            }
            return ['code' => 1, 'warningMsg' => $warningMsg];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

}