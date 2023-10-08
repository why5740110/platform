<?php
/**
 * 订单SDK
 * @file OrderSdk.php
 * @author zhibin <xiezhibin@yuanxin-inc.com>
 * @version 2.0
 * @date 2017-12-27
 */

namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class OrderSdk extends CommonSdk
{
    public function getorderstatuslist($userId,$orderSns,$partOrderSns){
        $params['user_id']=$userId;
        $params['order_sns']=$orderSns;
        $params['parter_order_sns']=$partOrderSns;
        return $this->send($params,__METHOD__);

    }
    /**
     * 获取订单列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-05-21
     * @param string $doctor_id              医生序号
     * @param string $start_last_modify_time 修改时间的开始时间
     * @param string $end_last_modify_time   修改时间的结束时间
     * @param int $require_doctor_id         是否医生序号不能为空
     * @param int $user_id                   用户序号
     * @return bool|mixed
     */
    public function orderpagelist($params=[]){
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取订单详情
     * @author xiezhibin <xiezhibin@yuanxin-inc.com>
     * @date 2018-05-21
     * @param $order_sn
     * @return bool|mixed
     */
    public function item($order_sn)
    {
        $params = ['order_sn' => $order_sn];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取订单详情
     * @author xiezhibin <xiezhibin@yuanxin-inc.com>
     * @date 2018-05-21
     * @param $order_sn
     * @return bool|mixed
     */
    public function itemtothird($order_sn)
    {
        $params = ['order_sn' => $order_sn];
        return $this->send($params, __METHOD__);
    }


    /**
     * 商品购买接口
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-05-26
     * @param $data
    province:省份序号
    city: 城市序号
    district:地区序号
    consignee:收货人地址
    mobile:收货人电话
    address:详细地址
    user_id:用户序号
    order_type:订单类型
    store_id:店铺序号
    pay_way:支付方式
     *      商品数组 products[商品序号]:购买数量
    products[111115]:9999
     * @return bool|mixed
     */
    public function buyproducts($data){
        return $this->send($data, __METHOD__,'post');
    }



    /**
     * 订单取消
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     * @param  string   $order_sn 订单号 （必填）
     * @param  int   $cancel_type 订单取消原因（选填）
     * @param  string   $cancel_note 订单取消备注（选填）
     * @param  string   $op_name 操作人（选填）
     * @param  string   $op_admin_id 操作人id（选填）
     * @param  int   $cancel_source 订单取消来源（选填）
     * @param  string   $op_content 订单跟踪内容（选填）
     * @param  string   $manage_name 店铺管理员姓名（选填 企业微信后台）
     * @param  string   $reason 店铺管理员取消原因（选填 企业微信后台）
     */
    public function cancel($params)
    {
        return $this->send($params, __METHOD__);
    }


    /**
     * 统计用户订单（待付款、待发货、待收货、待评价）数量
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     */
    public function myindexordertotal($user_id)
    {
        $params['user_id'] = $user_id;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取 代客下单 订单列表
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-03-28
     * @return bool|mixed
     */
    public function valetorderpagelist($data)
    {
        return $this->send($data, __METHOD__);
    }

    /**
     * 根据处方单号获取订单详情
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-04-12
     * @param $rx_sn
     * @return bool|mixed
     */
    public function getitembyrxsn($rx_sn)
    {
        $params = ['rx_sn' => $rx_sn];
        return $this->send($params, __METHOD__);
    }
    /**
     * 修改订单已签收、完成、状态
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @date 2019-04-25
     * @return bool|mixed
     */
    public function editorderok($data)
    {
        return $this->send($data, __METHOD__);
    }

    /**
     * 修改订单已签收、完成、已支付状态
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @date 2019-04-25
     * @return bool|mixed
     */
    public function editorderfinish($data)
    {
        return $this->send($data, __METHOD__);
    }

    /**
     * 获取几条最新的订单物流信息
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @param int $user_id 用户id
     * @param int $number 显示的条数
     * @date 2019-05-16
     **/
    public function getneworderstracelist($user_id, $number)
    {
        $params = [
            'user_id' => $user_id,
            'number' => $number
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 删除订单
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-05-20
     **/
    public function deleteorder($order_sn, $user_id)
    {
        $params = [
            'order_sn' => $order_sn,
            'user_id' => $user_id
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 根据医生id 商品id 获取商品销量
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-05-20
     * @param string $doctor_id 医生序号
     * @param string $product_id  商品id   1,2,3,4
     * @param string $start_time 开始时间
     * @param string $end_time   结束时间
     **/
    public function getprosalesvolume($doctorId, $productId,$startTime,$endTime)
    {
        $params = [
            'doctor_id'  => $doctorId,
            'product_id' => $productId,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 修改订单支付状态
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-08-12
     * @param string $orderSn 订单号
     * @param string $payStatus 支付状态
     **/
    public function updatepaystatus($orderSn, $payStatus)
    {
        $params = [
            'order_sn'  => $orderSn,
            'pay_status' => $payStatus,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取用户最新的几条订单信息
     **/
    public function actionGetuserneworderinfo()
    {
        $requestData = \Yii::$app->request->get();
        $userId = ArrayHelper::getValue($requestData, 'user_id', '');
        $number = ArrayHelper::getValue($requestData, 'number', 5);
        if ($userId<=0) {
            return $this->jsonError('用户信息不能为空');
        }
        $orderModel = new OrderModel();
        $orderInfo = $orderModel->getUserNewOrderSn($userId, $number);
        return $this->jsonSuccess($orderInfo);
    }
    /**
     * 根据订单号第三方开方
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @date 2019-11-22
     **/
    public function applyprescrip($orderSn)
    {
        $params = [
            'order_sn'     => $orderSn,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 通过 user_id  修改 订单表里面的用户数据
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-10-22
     **/
    public function updateorderuserid($oldUserId,$newUid,$newUseId,$newUserMobile,$newRealName)
    {
        $params = [
            'old_user_id'     => $oldUserId,
            'new_uid'         => $newUid,
            'new_user_id'     => $newUseId,
            'new_user_mobile' => $newUserMobile,
            'new_real_name'   => $newRealName,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 订单完成
     * @param  string   $order_sn 订单号 （必填）
     * @param  string   $op_name 操作人（选填）
     * @param  string   $op_admin_id 操作人id（选填）
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @date 2019-12-19
     **/
    public function finished($params)
    {
        return $this->send($params, __METHOD__);
    }
    /**
     * 获取是否存在活动商品的订单
     * @return bool|mixed
     */
    public function getactivityorder($params){
        return $this->send($params, __METHOD__);
    }

    /**
     * 重新申请开方
     * @param string $order_sn
     **/
    public function againapplyrecipel($orderSn)
    {
        $params['order_sn'] = $orderSn;
        return $this->send($params, __METHOD__);
    }

    /**
     * 订单取消(取消订单来源为电子处方、用药建议订单的接口;互联网医院调用)
     * @Author dongyaowei  <dongyaowei@yuanxin-inc.com>
     * @Date   2020-06-04
     * @param  string   $order_sn 订单号 （必填）
     * @param  int   $cancel_type 订单取消原因（选填）
     * @param  string   $cancel_note 订单取消备注（选填）
     * @param  string   $op_name 操作人（选填）
     * @param  string   $op_admin_id 操作人id（选填）
     * @param  int   $cancel_source 订单取消来源（选填）
     * @param  string   $op_content 订单跟踪内容（选填）
     * @param  string   $manage_name 店铺管理员姓名（选填 互联网医院
     * @param  string   $reason 店铺管理员取消原因（选填 互联网医院处方作废）
     */
    public function canceladviceandecrx($params)
    {
        return $this->send($params, __METHOD__);
    }

    
    /*
    * 支付回调
    *
    * */
    public function paybacksuccessaction($orderSn,$tradeType,$tradeNo,$tradeNote,$subPayWay,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'trade_type'=>$tradeType,
            'trade_no'=>$tradeNo,
            'trade_note'=>$tradeNote,
            'sub_pay_way'=>$subPayWay,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }
    /*
    * 退款回调（修改订单状态）
    *
    * */
    public function refundbackaction($orderSn,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }
}
