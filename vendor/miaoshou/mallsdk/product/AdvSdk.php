<?php
/**
 * 通用广告位接口
 * @file AdvSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-08-22
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class AdvSdk extends CommonSdk
{

    /**
     * 通用获取广告位内容接口
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-08-22
     * @param int $store_id  药店id
     * @param int $city_id 城市id
     * @param string $position_ids 广告位id字符串
     * @param string $position_name 广告位名称
     * @param bool $get_products_status 是否获取广告对应的商品信息 true:获取(默认)  false:不获取
     * @return bool|mixed
     */
    public function index($store_id = 0, $city_id = 0, $position_ids = 0, $position_name = '', $get_products_status = true, $province_id = 0)
    {
        $params = [
            'store_id' => $store_id,
            'city_id' => $city_id,
            'position_ids' => $position_ids,
            'position_name' => $position_name,
            'get_products_status' => $get_products_status,
            'province_id' => $province_id,
        ];
        return $this->send($params, __METHOD__);
    }
}