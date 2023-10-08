<?php
/**
 *
 * @file ProductSdk.php
 * @author dongyaowei <dongyaowei@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-12-03
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class ProductActivitySdk extends CommonSdk
{

    /**
     * 获取单个商品活动信息
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @date 2018-12-03
     * @param $store_id  药店id
     * @param $product_id  商品id
     * @return bool|mixed
     */
    public function productactivityinfo($params)
    {
        return $this->send($params, __METHOD__);
    }

     /**
     * 获取多个活动信息
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-12-13
     * @param string activityids 活动id
     * @param int activity_type 活动类型
    **/
    public function productactivityinfobyidandtype($activityids = '', $activity_type = 0)
    {
        $params = [
            'activityids' => $activityids,
            'activity_type' => $activity_type
        ];
        return $this->send($params, __METHOD__);
    }
}