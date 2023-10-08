<?php
/**
 * 百度订单状态更新推送
 * @file BaiduordernotifySdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-06-28
 */

namespace nisiya\mallsdk\message;

use nisiya\mallsdk\CommonSdk;

class BaiduordernotifySdk extends CommonSdk
{

    /**
     * @param string $order_sn 订单号
     * @param string $tp_status 状态
    PAYED 已支付
    CANCELED 已取消
    USERSIGNED 用户签收
    WORKERSIGNED 物流签收
    COMPLETED 已完成
     * @param 与订单状态变更相关的文案信息
     **/
    public function push($order_sn, $tp_status, $reason)
    {

        $params = [
            'order_sn' => $order_sn,
            'tp_status' => $tp_status,
            'reason' => $reason
        ];

        return $this->send($params, __METHOD__);
    }

    /**
     * 订单物流信息 推送给百度
     * @param string $orderSn 订单号
     * @param string $txtStatus  物流状态
     * */

    public function pushbaidushippinginfo($orderSn, $txtStatus)
    {
        $params = [
            'order_sn' => $orderSn,
            'txt_status' => $txtStatus
        ];

        return $this->send($params, __METHOD__);
    }

}