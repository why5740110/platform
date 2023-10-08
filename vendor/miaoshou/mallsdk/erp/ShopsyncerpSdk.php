<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : CartSdk.php
 * @author  : suxingwang <suxingwang@yuanxin-inc.com>
 * @date    : 2018-12-15
 */

namespace nisiya\mallsdk\erp;

use nisiya\mallsdk\CommonSdk;

class ShopsyncerpSdk extends CommonSdk
{
    /**
     * 同步订单信息 到erp
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * 07153604347981591 order_sn  58 store_id
     * 01154762342448814 order_sn  19 store_id
     **/
    public function syncordertoerp($order_sn)
    {
        $params = [
            'order_sn'  => $order_sn,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 取消订单信息 同步到erp
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * 07153604347981591 order_sn  58 store_id
     **/
    public function syncordercanceltoerp($order_sn)
    {
        $params = [
            'order_sn'  => $order_sn,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 修改支付方式 同步到erp
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * 07153604347981591 order_sn  58 store_id
     **/
    public function updatepayway($order_sn,$payway)
    {
        $params = [
            'order_sn'  => $order_sn,
            'pay_way'   => $payway,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 修改收货地址 同步到erp
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * 07153604347981591 order_sn  58 store_id
     **/
    public function updateaddress($order_sn,$address)
    {
        $params = [
            'order_sn'  => $order_sn,
            'address'   => $address,
        ];
        return $this->send($params, __METHOD__);
    }

}