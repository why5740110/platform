<?php
/**
 *
 * @file CouponSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-25
 */

namespace nisiya\mallsdk\user;


use nisiya\mallsdk\CommonSdk;

class CouponSdk extends CommonSdk
{

    /**
     * 获取用户优惠券/红包
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date   2019-01-14
     * @param $amount 商品总金额
     * @param string $product_ids 商品id
     * @param string $cat_ids 三级分类id
     * @param int $storeId 药店id
     * @param array $products商品信息 例如：$products[$productId] = $productNum;
     **/
    public function getmycoupon($amount, $product_ids = '', $cat_ids = '', $storeId = '', $products = [])
    {
        $params['amount'] = $amount;
        $params['product_ids'] = $product_ids;
        $params['cat_ids'] = $cat_ids;
        $params['store_id'] = $storeId;
        $params['products'] = $products;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取优惠券/红包（1待使用，2已过期，3已使用）列表
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date   2019-03-27
     * @param int $coupon_use_status 券类使用状态 1：待使用 2：已过期 3：已使用
     * @param int $page 页码
     * @param int $pagesize 每页显示的条数 默认20
     * @param int $bonusType 券类型 1优惠券 3运费
     **/
    public function myindexcouponlist($coupon_use_status, $page, $pagesize = 20, $bonusType = 0)
    {
        $params['coupon_use_status'] = $coupon_use_status;
        $params['page'] = $page;
        $params['pagesize'] = $pagesize;
        $params['bonus_type'] = $bonusType;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取优惠券/红包（1待使用，2已过期，3已使用）数量
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date   2019-03-27
     * @param int $user_id 用户id
     * @param int $coupon_use_status 券类使用状态 1：待使用 2：已过期 3：已使用
     **/
    public function myindexcoupontotal($user_id, $coupon_use_status)
    {
        $params['user_id'] = $user_id;
        $params['coupon_use_status'] = $coupon_use_status;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取券对应状态的数量
     * @param $bonusType 券类型 1优惠券 3运费券
     * @param int $coupon_use_status 券类使用状态 1：待使用 2：已过期 3：已使用
     */
    public function coupontotal($bonusType, $couponUseStatus)
    {
        $params['coupon_use_status'] = $couponUseStatus;
        $params['bonus_type'] = $bonusType;
        return $this->send($params, __METHOD__);
    }

    /**
     * 发送优惠券/红包
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date   2019-03-29
     * @param $rules_id  红包优惠券id
     * @param $send_user_name 发券人
     * @param $send_sms 接收短信的手机号
     **/
    public function sendcoupon($rules_id, $send_user_name,$send_sms)
    {
        $params['rules_id'] = $rules_id;
        $params['send_user_name'] = $send_user_name;
        $params['send_sms'] = $send_sms;
        return $this->send($params, __METHOD__);
    }
    /**
     * 获取优惠券 详情
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date   2019-06-4
     * @param $rules_id  红包优惠券id
     **/
    public function getcouponinfo($rules_id)
    {
        $params['rules_id'] = $rules_id;
        return $this->send($params, __METHOD__);
    }
    /**
     * 用户是否领取过 该 优惠券
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date   2019-06-4
     * @param $rules_id  红包优惠券id
     **/
    public function getcouponbyuserid($rules_id)
    {
        $params['rules_id'] = $rules_id;
        return $this->send($params, __METHOD__);
    }

    /**开卡前发放单张优惠券
     * @param $send_sms
     * @return bool|mixed
     * @date 2020-05-29
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function presendfreightcoupon(){  //Presendfreightcoupon
        $params=[];
        return $this->send($params, __METHOD__);
    }

    /**绑定运费券和vip福利发放的关联。
     * @param $orderSn
     * @param $uid
     * @param $bonusId
     * @return bool|mixed
     * @date 2020-05-29
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function bindfreightcoupon($orderSn,$bonusId){
        $params['order_sn']=$orderSn;
        $params['bonus_id']=$bonusId;
        return $this->send($params, __METHOD__);
    }

    /**发放运费券
     * @param $uid
     * @param $startTime
     * @param $endTime
     * @param $couponNumber
     * @param $uniqueIdentification
     * @param $ruleId
     * @param $orderSn
     * @param $sendSms
     * @return bool|mixed
     * @date 2020-05-29
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function sendfreightcoupon($startTime,$endTime,$couponNumber,$uniqueIdentification,$ruleId,$orderSn,$sendSms){
        $params['send_sms']=$sendSms;
        $params['start_time']=$startTime;
        $params['end_time']=$endTime;
        $params['coupon_number']=$couponNumber;
        $params['unique_identification']=$uniqueIdentification;
        $params['rule_id']=$ruleId;
        $params['order_sn']=$orderSn;
        return $this->send($params, __METHOD__);
    }

    /**
     * 验证卡券是否可用
     * @param $bonusId
     * @param $payFee
     * @param $productIds
     * @return bool|mixed
     */
    public function checkcouponuse($bonusId, $payFee, $productIds)
    {
        $params = [
            'bonus_id' => $bonusId,
            'pay_fee' => $payFee,
            'product_ids' => $productIds
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取券的详细信息
     * @param $bonusId
     * @return bool|mixed
     */
    public function bonusinfo($bonusId)
    {
        $params = [
            'bonus_id' => $bonusId
        ];
        return $this->send($params, __METHOD__);
    }
}
