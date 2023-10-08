<?php
/**
 * 短信提醒SKD
 * @file UsersmsmsgSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-08-22
 */

namespace nisiya\mallsdk\message;

use nisiya\mallsdk\CommonSdk;

class UsersmsmsgSdk extends CommonSdk
{
    /**
     * 用户订单短信通知入队列
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-08-22
     * @param $order_sn 订单号
     * @param $user_id 用户id
     * @param $transfer_type  设置发送类型备注
     * @param $is_transfer_url 是否生产短链 1：获取短链接（默认） 0：不获取短链接
     * @param $sms_id 短信模板id 1:超时未支付 2：创建订单
    **/
    public function sendordermsg($order_sn, $user_id = 0, $transfer_type = '', $is_transfer_url = 1, $sms_id = 0)
    {

        $params = [
            'order_sn' => $order_sn,
            'user_id' => $user_id,
            'transfer_type' => $transfer_type,
            'is_transfer_url' => $is_transfer_url,
            'sms_id' => $sms_id,
        ];

        return $this->send($params, __METHOD__);
    }

}