<?php
/**
 * Created by PhpStorm.
 * User: suxingwang
 * Date: 2020-06-04
 * Time: 17:38
 */
namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class AdminorderSdk extends CommonSdk
{
    /*
      * 订单审核
      *
      * */
    public function verifyorder($orderSn, $orderStatus, $adminId, $adminName)
    {
        $params = [
            'order_status' => $orderStatus,
            'order_sn' => $orderSn,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }


    /*
      * 订单推送到 新erp
      *
      * */
    public function sendnewerp($orderSn)
    {

        $params = [
            'order_sn' => $orderSn,
        ];
        return $this->send($params, __METHOD__);
    }


    /*
      * 订单备注
      *
      * */
    public function remark($orderSn, $content, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'content' => $content,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
     * 选择售前 药师
     *
     * */
    public function orderbelonger($orderSn, $belongerId, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'belonger_id' => $belongerId,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
     * 订单 加星
     *
     * */
    public function orderstar($orderSn, $isStar, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'is_star' => $isStar,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
     * 订单 修改物流信息 单纯修改物流信息
     *
     * */

    public function updatebill($orderSn, $shippingId, $bill, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'shipping_id' => $shippingId,
            'shipping_bill' => $bill,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
   * 修改为自营配送
   * */
    public function updatebillself($orderSn, $storeId, $courierId, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'store_id' => $storeId,
            'courier_id' => $courierId,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
    * 更新物流信息
    *
    * */
    public function updatebillinfo($orderSn)
    {
        $params = [
            'order_sn' => $orderSn,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
       *
       *  修改收货人
       *
       * */
    public function updateaddress($orderSn, $consignee, $mobile, $province, $city, $district, $address, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'consignee' => $consignee,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'address' => $address,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
      *  转店铺
      *
      * */
    public function updatestore($orderSn, $storeId, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'store_id' => $storeId,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
      *
      * 修改转账方式 确认支付
      *
      * */
    public function offlinepay($orderSn, $adminId, $adminName)
    {
        $params = [
            'order_sn' => $orderSn,
            'admin_id' => $adminId,
            'admin_name' => $adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
      *
      * 上传线下转账的 流水号 与 截图
      *
      * */
    public function offlinetransfer($orderSn,$subPayWay,$adminId,$adminName,$payImgs,$tradeNo)
    {
        $params = [
            'order_sn'=>$orderSn,
            'sub_pay_way'=>$subPayWay,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
            'pay_images'=>$payImgs,
            'trade_no'=>$tradeNo,
        ];

        return $this->send($params, __METHOD__);
    }

    /*
      * erp / oms 同步发货
      *
      *
      * */
    public function ordersend($orderSn,$lastModifyTime,$logisticInfos,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
            'lastModifyTime'=>$lastModifyTime,
            'logisticInfos'=>$logisticInfos,
        ];

        return $this->send($params, __METHOD__);
    }

    /*
     *
     * 订单 拆单
     *
     * */
    public function ordersplit($orderSn,$producIds,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
            'product_id'=>$producIds,
        ];

        return $this->send($params, __METHOD__,'post');
    }

    /*
    * 订单详情
    *
    * */
    public function item($orderSn)
    {
        $params = [
            'order_sn'=>$orderSn,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
    * 订单确认收货
    *
    * */
    public function orderconfirmreceipt($orderSn,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
    * 订单完成
    *
    * */
    public function ordercomplete($orderSn,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
    * 修改改价订单状态
    *
    * */
    public function checkchangepriceorder($orderSn,$changePriceOrderStatus,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'change_price_order_status'=>$changePriceOrderStatus,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }
    /*
    * 取消订单
    *
    * */
    public function cancelorder($orderSn,$cancelType,$cancelSource,$cancelNote,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'cancel_type'=>$cancelType,
            'cancel_source'=>$cancelSource,
            'cancel_note'=>$cancelNote,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }

    /*
    * 审核处方订单
    *
    * */
    public function checkrecipelorder($orderSn,$recipelId,$orderAuditStatus,$orderAuditRefuse,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'recipel_id'=>$recipelId,
            'order_audit_status'=>$orderAuditStatus,
            'order_audit_refuse'=>$orderAuditRefuse,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }
    /*
    * 订单确认退款
    *
    * */
    public function confirmrefund($orderSn,$adminId,$adminName)
    {
        $params = [
            'order_sn'=>$orderSn,
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
        ];
        return $this->send($params, __METHOD__);
    }


}