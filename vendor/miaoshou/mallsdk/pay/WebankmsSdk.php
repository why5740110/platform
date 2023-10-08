<?php
namespace nisiya;
/**
 * 调用微众银行登录页面URL、交易页面URL、退款接口
 * 2018-11-7  dongyaowei
 * @file AskSDK.php
 * Ask project client side for api query
 */
namespace nisiya\mallsdk\pay;

use nisiya\mallsdk\CommonSdk;

class WebankmsSdk extends CommonSdk
{
    /**
     * 获取登录页URL
     * @author dongyaowei<dongyaowei@yuanxin-inc.com>
     * @date 2018-11-07
     * @param $params  参数集合
     * @param int $uid 用户id
     * @return bool|mixed
     */
    public function getloginurl($uid='')
    {
        $params['uid'] = $uid;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取交易页URL
     * @author dongyaowei<dongyaowei@yuanxin-inc.com>
     * @date 2018-11-07
     * @param $params  参数集合
     * @param int $uid 用户id
     * @param int $order_sn 订单号
     * @param str $pay_fee 支付金额
     * @return bool|mixed
     */
    public function getpayurl($params)
    {
        return $this->send($params, __METHOD__);
    }

    /**
     * 发起退款接口
     * @author dongyaowei<dongyaowei@yuanxin-inc.com>
     * @date 2018-11-07
     * @param $params  参数集合
     * @param int $refund_fee 退款金额
     * @param int $order_sn 订单号
     * @param str $pay_fee 支付金额
     * @return bool|mixed
     */
    public function webankrefund($params)
    {
        return $this->send($params, __METHOD__);
    }

    /**
     * 查询用户是否启用微众银行
     * @author dongyaowei<dongyaowei@yuanxin-inc.com>
     * @date 2018-11-07
     * @param $params  参数集合
     * @param int $uid 用户id
     * @return bool|mixed
     */
    public function webankcheckenabled($uid)
    { 
        if(!empty($uid)){
            $params['uid'] = $uid;
        }
        return $this->send($uid, __METHOD__);
    }
}
