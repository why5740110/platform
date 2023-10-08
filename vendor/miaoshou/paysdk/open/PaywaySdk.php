<?php
namespace nisiya;

namespace nisiya\paysdk\open;

use nisiya\paysdk\CommonSdk;

class PaywaySdk extends CommonSdk
{
    /**
     * 支付方式列表
     * @param $params
     * @return bool|mixed
     */
    public function paywaylist($params)
    {
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取支付地址
     * @param $params
     * @return bool|mixed
     */
    public function getpayurl($params){
        return $this->send($params, __METHOD__);

    }


}
