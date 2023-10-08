<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : CartSdk.php
 * @author  : suxingwang <suxingwang@yuanxin-inc.com>
 * @date    : 2018-12-15
 */

namespace nisiya\mallsdk\erp;

use nisiya\mallsdk\CommonSdk;

class SyncSdk extends CommonSdk
{
    /**
     * erp  更新订单状态
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     *
     *
     **/
    public function erporderstatus($order_sn,$erp_order_status='',$deliver_type='',$shipping_name='',$shipping_bill='',$operation_time='',$remark)
    {
        $params = [
            'order_sn'          => $order_sn,
            'erp_order_status'  => $erp_order_status,
            'deliver_type'      => $deliver_type,
            'shipping_name'     => $shipping_name,
            'shipping_bill'     => $shipping_bill,
            'operation_time'    => $operation_time,
            'remark'            => $remark,
        ];
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * erp  同步毛利
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     *
     **/
    public function ordercost($order_sn,$products)
    {
        $params = [
            'order_sn'  => $order_sn,
            'products'  => $products,
        ];
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * erp  同步库存
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     *
     **/
    public function quantity($pf_unique_code,$erp_orgid,$store_quantity)
    {
        $params = [
            'pf_unique_cod'  => $pf_unique_code,
            'erp_orgid'      => $erp_orgid,
            'store_quantity' => $store_quantity,
        ];
        return $this->send($params, __METHOD__,'post');
    }

}