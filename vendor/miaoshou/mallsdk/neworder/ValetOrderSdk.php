<?php
/**
 * 代客下单SDK
 * @file ValetOrderSdk.php
 * @author zhibin <xiezhibin@yuanxin-inc.com>
 * @version 2.0
 * @date 2017-12-27
 */

namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class ValetOrderSdk extends CommonSdk
{
    public function item($order_sn)
    {

        $params = ['order_sn' => $order_sn];
        return $this->send($params, __METHOD__);
    }

}