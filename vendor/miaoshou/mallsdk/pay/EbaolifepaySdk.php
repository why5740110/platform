<?php

namespace nisiya\mallsdk\pay;

use nisiya\mallsdk\CommonSdk;

class EbaolifepaySdk extends CommonSdk
{
    public function pay($order_sn, $openid)
    {
        $params = [
            'order_sn' => $order_sn,
            'openid' => $openid
        ];
        return $this->send($params, __METHOD__);
    }


    public function notify($params)
    {
        return $this->send($params, __METHOD__);
    }


    /**
     * 退款
     * @param string $orderSn 订单号
     * @param string $remark 退款原因
    **/
    public function refund($orderSn, $ip, $remark = '')
    {   
        $params = [
            'order_sn' => $orderSn,
            'remark' => $remark,
            'ip' => $ip,
        ];
        return $this->send($params, __METHOD__);
    }
}
