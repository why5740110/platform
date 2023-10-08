<?php
/**
 *第三方调用，不需传member_login_token
 * @file BonusSdk.php
 * @author renranran <renranran@yuanxin-inc.com>
 * @version 1.0
 * @date 2020-06-16
 */

namespace nisiya\mallsdk\user;


use nisiya\mallsdk\CommonSdk;

class BonusSdk extends CommonSdk
{
    /**
     * 获取券对应状态的数量
     * @param $bonusType //券类型 1优惠券 3运费券
     * @param int $coupon_use_status 券类使用状态 1：待使用 2：已过期 3：已使用
     * @param int $uid 用户uid
     * @param string rulesId 需要查询的优惠券id 多个英文逗号拼接
     * @param string filterRulesId 需要过滤的优惠券id 多个英文逗号拼接
     * @param int startTime 开始查询时间戳
     * @param int endTime 结束查询时间戳
     */
    public function coupontotal($uid,$bonusType, $couponUseStatus,$rulesId = '',$filterRulesId = '',$startTime = '',$endTime = '')
    {
        $params['coupon_use_status'] = $couponUseStatus;
        $params['bonus_type'] = $bonusType;
        $params['uid'] = $uid;
        $params['rules_id'] = $rulesId;
        $params['filter_rules_id'] = $filterRulesId;
        $params['start_time'] = $startTime;
        $params['end_time'] = $endTime;
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
    public function sendfreightcoupon($uid,$startTime,$endTime,$couponNumber,$uniqueIdentification,$ruleId,$orderSn,$sendSms){
        $params['send_sms']=$sendSms;
        $params['uid']=$uid;
        $params['start_time']=$startTime;
        $params['end_time']=$endTime;
        $params['coupon_number']=$couponNumber;
        $params['unique_identification']=$uniqueIdentification;
        $params['rule_id']=$ruleId;
        $params['order_sn']=$orderSn;
        return $this->send($params, __METHOD__);
    }
    /**开卡前发放单张优惠券
     * @param $send_sms
     * @return bool|mixed
     * @date 2020-05-29
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function presendfreightcoupon($uid){  //Presendfreightcoupon
        $params=[];
        $params['uid'] = $uid;
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
    public function bindfreightcoupon($uid,$orderSn,$bonusId){
        $params['order_sn']=$orderSn;
        $params['bonus_id']=$bonusId;
        $params['uid'] = $uid;
        return $this->send($params, __METHOD__);
    }
}
