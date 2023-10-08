<?php


namespace nisiya\paysdk\open;


use nisiya\paysdk\CommonSdk;

class WithdrawSdk extends CommonSdk
{
    public function wechat($withdrawNo,$openId,$money,$desc='')
    {
        $requestArr = array(
            'withdraw_no'  => $withdrawNo,
            'openid'            => $openId,
            'money'       => $money,
            'desc'           => $desc,
        );
        return $this->send($requestArr, __METHOD__, 'GET');
    }
}
