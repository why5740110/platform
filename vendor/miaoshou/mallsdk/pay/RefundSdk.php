<?php

namespace nisiya\mallsdk\pay;

use nisiya\mallsdk\CommonSdk;

class RefundSdk extends CommonSdk
{

	/**
	 * 退款申请
	 * @param string $order_sn 订单号
	 * @param int $op_admin_id 后台操作人id
	 * @param string $refund_desc 退款描述
	**/
    public function applyrefund($order_sn,$op_admin_id='',$refund_desc = '')
    {
        $params = [
            'order_sn' => $order_sn,
            'op_admin_id'=>$op_admin_id,
            'refund_desc' => $refund_desc
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 确认退款
     * @param $orderSn 订单号
     * @param $payFee 退款金额
     * @param $refundDesc 退款描述
     * @param $adminId 管理员id
     * @return bool|mixed
     */
    public function confirmRefund($orderSn, $payFee, $refundDesc, $adminId){
        $params = [
            'order_sn'=>$orderSn,
            'pay_fee'=>$payFee,
            'refund_desc'=>$refundDesc,
            'op_admin_id'=>$adminId,
        ];
        return $this->send($params, __METHOD__);
    }
}
