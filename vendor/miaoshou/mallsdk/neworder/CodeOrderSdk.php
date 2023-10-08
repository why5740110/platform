<?php
/**
 * Created by PhpStorm.
 * User: suxingwang
 * Date: 2020-06-16
 * Time: 15:53
 */

/**
 * 提货码订单SDK
 * @file CodeOrderSdk.php
 * @author zhibin <xiezhibin@yuanxin-inc.com>
 * @version 2.0
 * @date 2017-12-27
 */

namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class CodeOrderSdk extends CommonSdk
{
    public function item($order_sn)
    {

        $params = ['order_sn' => $order_sn];
        return $this->send($params, __METHOD__);
    }

}