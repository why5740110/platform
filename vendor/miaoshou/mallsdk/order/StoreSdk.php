<?php
/**
 * 药店相关SDK
 * @file StoreSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-09-07
 */

namespace nisiya\mallsdk\order;

use nisiya\mallsdk\CommonSdk;

class StoreSdk extends CommonSdk
{
    public function item($order_sn)
    {

        $params = ['order_sn' => $order_sn];
        return $this->send($params, __METHOD__);
    }



}