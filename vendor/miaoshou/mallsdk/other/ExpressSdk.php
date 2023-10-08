<?php
/**
 *
 * @file ExpressSdk.php
 * @author suxingwang <suxingwang@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-02-15
 */

namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class ExpressSdk extends CommonSdk
{
    /**
     * 订阅快递单号
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-02-15
     * $order_sn    商城订单号     必须
     * $express_sn  填写的快递单号  必须
     * $express_company  快递公司  必须
     * $from_address  发货地址（省市区）  非必须
     * $to_address    收货地址（省市区）  非必须
     * $seller        发货商家           非必须 (默认王氏商城)
     * @return bool|mixed
     */
    public function subscribe($order_sn, $express_sn, $express_company, $from_address = '', $to_address='', $seller=''){
        $params['order_sn']       = $order_sn;
        $params['express_sn']     = $express_sn;
        $params['express_company']= $express_company;
        $params['from_address']   = $from_address;
        $params['to_address']     = $to_address;
        $params['seller']         = $seller;
        return $this->send($params,__METHOD__);
    }

    /**
     * 根据快递单号/订单号  查询快递信息
     * 如果是根据 订单号查询快递信息  则该订单对应的快递单号 必须订阅过 （mall_express_subscribe 表里面存储的订阅信息）
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-02-15
     * $param['order_sn']    商城订单号      order_sn 或者  express_sn 必须有一个
     * $param['express_sn']  填写的快递单号   order_sn 或者  express_sn 必须有一个  467388032016 (线上顺丰的快递单号)
     * $param['express_company']  快递公司  必须
     * $param['from']  发货地址（省市区）  非必须
     * $param['to']    收货地址（省市区）  非必须
     * @return bool|mixed
     */
    public function expressselect($order_sn='', $express_sn='', $express_company='', $from ='', $to = ''){
        $params['order_sn']   = $order_sn;
        $params['express_sn'] = $express_sn;
        $params['express_company'] = $express_company;
        $params['from']       = $from;
        $params['to']         = $to;
        return $this->send($params,__METHOD__);
    }

}