<?php
namespace nisiya;

namespace nisiya\paysdk\open;

use nisiya\paysdk\CommonSdk;

class OrderSdk extends CommonSdk
{
    /**
     * 创建生成新订单
     * @param $params
     * pay_out_trade_no
     * @author xuyi
     * @date 2019/8/24
     * @return bool|mixed
     */
    public function create($params)
    {
        return $this->send($params, __METHOD__, 'POST');
    }

    /**
     * 获取微信小程序支付信息
     *
     * @param $params['pay_no']
     * @return bool|mixed
     */
    public function wechatminiprogrampaydata($params){
        return $this->send($params, __METHOD__);

    }


    /**
     * 获取支付宝小程序支付信息
     *
     * @param $params['pay_no']
     * @return bool|mixed
     */
    public function alipayminiprogrampaydata($params){
        return $this->send($params, __METHOD__);

    }


    public function wechatpayresult($params) {
        return $this->send($params, __METHOD__);
    }

    /**
     * 订单延时交易分账确认
     * @param $params
     * @return bool|mixed
     */
    public function delayconfirm($params){
        return $this->send($params, __METHOD__,'POST');
    }

    /**
     * 查询订单详情
     * @param $params
     * @return bool|mixed
     */
    public function detail($params){
        return $this->send($params, __METHOD__);
    }
    /**
     * 确认结算
     * @param $params
     * @return bool|mixed
     */
    public function confirmsettle($params){
        return $this->send($params, __METHOD__,'POST');
    }
    /**
     * 取消结算
     * @param $params
     * @return bool|mixed
     */
    public function cancelsettle($params){
        return $this->send($params, __METHOD__,'POST');
    }
}
