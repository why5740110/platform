<?php

namespace nisiya\mallsdk\pay;

use nisiya\mallsdk\CommonSdk;

class MallpaySdk extends CommonSdk
{
    public function pay($order_sn,$user_sign='',$pay_app_id='', $payCancelUrl = '')
    {
        $params = [
            'order_sn' => $order_sn,
            'user_sign'=>$user_sign,
            'pay_app_id'=>$pay_app_id,
            'pay_cancel_url' => $payCancelUrl
        ];
        return $this->send($params, __METHOD__);
    }
}
