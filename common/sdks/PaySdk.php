<?php
/**
 * 商城支付sdk
 * @file PaySdk.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/11/30
 */


namespace common\sdks;


use nisiya\paysdk\Config;
use nisiya\paysdk\open\OrderSdk;
use nisiya\paysdk\open\RefundSdk;
use yii\helpers\ArrayHelper;

class PaySdk
{
    protected static $_instance = null;

    public $config;

    private function __construct()
    {
        $this->config = ArrayHelper::getValue(\Yii::$app->params,'paysdk');
        Config::setConfig($this->config);
    }

    /**
     * 单例
     * @return static object
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    /**
     * 商城支付 生成订单url
     * @param $params
     *      pay_out_trade_no string 是 外部订单号
     *      pay_subject      string 是 订单标题描述
     *      pay_fee          float   是 支付金额
     *      pay_goods        string 是 商品信息
     *      pay_expire_time  int    是 支付过期时间戳
     *      user_sign        int    否 用户信息
     *      pay_return_url   string 是 支付同步通知地址
     *      pay_notify_url   string 是 支付异步回调地址
     *      pay_back_step    int    否 用户点击后退是返回的页面数
     *
     * @return bool|mixed
     *      data objcet
     *         data.pay_no  int 支付系统订单号
     *         data.pay_url int 支付地址
     * @throws \Exception
     * @author xiujianying
     * @date 2020/11/30
     */
    public static function pay($params)
    {

        $sdk = new OrderSdk();
        $res = $sdk->create($params);
        if ($res) {
            return ['code' => 1, 'data' => $res];
        } else {
            $msg = $sdk->getError();
            return ['code' => 0, 'msg' => $msg];
        }
    }

    /**
     * 退款
     * @param $params
     *  pay_no	            支付中心支付单号	是	[string]	20191216205645000001
     *	pay_out_trade_no	商户支付单号	是	[string]
     *	refund_fee	        退款金额	    是	[string]
     *	refund_notify_url	退款通知地址	是	[string]
     *	refund_out_trade_no	商户退款单号	是	[string]
     *	refund_desc	        退款描述	    是	[string]
     * @return bool|mixed
     * @author xiujianying
     * @date 2020/11/30
     */
    public static function refund($params)
    {
        $sdk = new RefundSdk();
        $res = $sdk->order($params);
        if ($res) {
            return ['code' => 1, 'data' => $res];
        } else {
            $msg = $sdk->getError();
            return ['code' => 0, 'msg' => $msg];
        }
    }

}