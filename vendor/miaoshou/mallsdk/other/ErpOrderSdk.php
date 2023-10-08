<?php
/**
 * 订单SDK
 * @file OrderSdk.php
 * @author zhibin <xiezhibin@yuanxin-inc.com>
 * @version 2.0
 * @date 2017-12-27
 */

namespace nisiya\mallsdk\order;

use nisiya\mallsdk\CommonSdk;

class ErpOrderSdk extends CommonSdk
{

    public function create($orders){
        $params['orders']=$orders;
        return $this->send($params,__METHOD__,'post');
    }

}