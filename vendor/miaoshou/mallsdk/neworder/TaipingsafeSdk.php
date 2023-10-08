<?php
/**
 * 太平特药险合作sdk
 * @file TaipingsafeSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-01-08
 */

namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class TaipingsafeSdk extends CommonSdk
{
    public function pushcooperate($order_sn)
    {

        $params = ['order_sn' => $order_sn];
        return $this->send($params, __METHOD__);
    }



}