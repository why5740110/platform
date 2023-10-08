<?php
/**
 * Created by PhpStorm.
 * User: lixin
 * Date: 2018/12/21
 * Time: 16:00
 */

namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class CreateorderSdk extends CommonSdk
{

    /**
     *
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018/12/21
     * @param $params
     * $params
     *
     */
    public function usercreate($params){
//        $params['store_id']="药店序号";
//        $params['user_id']="用户序号";
//        $params['uid']="用户中心序号";
//        $params['address_id']="用户地址库序号";
//        $params['province']="省份序号";
//        $params['city']="城市序号";
//        $params['district']="区县序号";
//        $params['address']="门牌地址";
//        $params['mobile']="手机号";
//        $params['consignee']="收件人";
//        $params['products']['1_0']="商品数据";
//        $params['pay_way']="支付方式";
//        $params['sub_pay_way']="二级支付方式";
//        $params['delivery_type']="配送方式";
//        $params['order_source']="订单来源";
//        $params['inv_code']="纳税人识别号	";
//        $params['inv_payee']="发票抬头";
//        $params['inv_content']="发票内容";
//        $params['referer']="页面来源";
//        $params['remarks']="买家留言";
//        $params['skey']="推广关键词	";
//        $params['srefer']="推广来源";
//        $params['use_shopping_gold']="是否使用购物金	0不使用 1使用";
//        $params['red_pack_id']="红包序号	";
//        $params['coupon_id']="优惠卷序号	";
//        $params['is_change_price_order']="是否是改价订单   ";
//        $params['change_price']="改价后的商品价格   ";
//        $params['change_price_cause_status']="改价原因选择   ";
//        $params['change_price_cause_text']="改价备注   ";
//        $params['change_price_order_status']="改价订单审核状态   ";
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 省级药店库用户下单
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2019/1/11
     * @param $params
     * @return bool|mixed
     */
    public function doctorprovincecreate($params){

//        $params['store_id']="药店序号";
//        $params['user_id']="用户序号";
//        $params['uid']="用户中心序号";
//        $params['address_id']="用户地址库序号";
//        $params['province']="省份序号";
//        $params['city']="城市序号";
//        $params['district']="区县序号";
//        $params['address']="门牌地址";
//        $params['mobile']="手机号";
//        $params['consignee']="收件人";
//        $params['products']['1_0']="商品数据";
//        $params['pay_way']="支付方式";
//        $params['sub_pay_way']="二级支付方式";
//        $params['prescription_image'][]="处方图片数组";
//        $params['rx_sn']="处方号";
//        $params['rx_create_time']="处方时间";
//        $params['doctor_id']="医生序号";
//        $params['doctor_name']="医生名称";
//        $params['nisiya_id']="王氏id";
//        $params['outpatient_fee']="门诊费";
//        $params['doctor_advice']="用药建议";
//        $params['doctor_agent_id']="医务代表序号	";
//        $params['doctor_agent_name']="医务代表名称	";
//        $params['order_type']="订单类型  14 电子处方 15 用药建议";

        return $this->send($params, __METHOD__,'post');

    }

    /**
     * 合作方下单
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2019/1/15
     * @param $params
     * @return bool|mixed
     */
    public function partercreate($params){
//        $params['user_id']="用户序号";
//        $params['store_id']="药店序号";
//        $params['pay_way']="支付方式";
//        $params['pay_status']="支付状态";
//        $params['order_status']="订单状态";
//        $params['delivery_type']="配送方式";
//        $params['shipping_status']="物流状态";
//        $params['order_source']="订单来源";
//        $params['has_shipping_fee']="是否计算运费";
//        $params['prescription_image']="处方图片";
//        $params['parter_order_sn']['1_0']="合作方订单号";
//        $params['rx_sn']="处方单号";
//        $params['products'][1]="购买产品的数组";

        return $this->send($params, __METHOD__,'post');

    }

    /**
     * 医生下单
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018/12/21
     * @param $params
     * $params
     *
     */
    public function doctorcreate($params){
//        $params['store_id']="药店序号";
//        $params['user_id']="用户序号";
//        $params['uid']="用户中心序号";
//        $params['address_id']="用户地址库序号";
//        $params['province']="省份序号";
//        $params['city']="城市序号";
//        $params['district']="区县序号";
//        $params['address']="门牌地址";
//        $params['mobile']="手机号";
//        $params['consignee']="收件人";
//        $params['products']['1_0']="商品数据";
//        $params['pay_way']="支付方式";
//        $params['sub_pay_way']="二级支付方式";
//        $params['delivery_type']="配送方式";
//        $params['order_source']="订单来源";
//        $params['inv_code']="纳税人识别号   ";
//        $params['inv_payee']="发票抬头";
//        $params['inv_content']="发票内容";
//        $params['referer']="页面来源";
//        $params['remarks']="买家留言";
//        $params['skey']="推广关键词    ";
//        $params['srefer']="推广来源";
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 医生下单(中心仓)
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018/12/21
     * @param $params
     * $params
     *
     */
    public function doctorcreateprovince($params){
        return $this->send($params, __METHOD__,'post');
    }



    /**
     * 用户省级药店下单
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-10-15
     **/
    public function userprovincecreate($params)
    {
        return $this->send($params, __METHOD__,'post');
    }

    /**批量创建订单，传入订单数据列表json
     * @param $params
     * @return bool|mixed
     * @date 2020-04-17
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function batchCreate($params){
        return $this->send($params, __METHOD__,'post');
    }
}
