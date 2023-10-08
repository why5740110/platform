<?php
namespace nisiya;

namespace nisiya\paysdk\open;

use nisiya\paysdk\CommonSdk;

class RefundSdk extends CommonSdk
{

    /**
     * 订单退款
     * @param $params
     * pay_out_trade_no 外部系统订单号
     * pay_no           支付平台单号
     * refund_out_trade_no 外部系统退款单号
     * refund_fee  退款金额
     * refund_notify_url 退款通知地址
     * @return bool|mixed
     */
    public function order($params)
    {
        return $this->send($params, __METHOD__, 'POST');
    }

    /**
     * 订单延时交易退款确认
     * @param $params
     * @return bool|mixed
     */
    public function delayconfirm($params){
        return $this->send($params, __METHOD__, 'POST');
    }
}
