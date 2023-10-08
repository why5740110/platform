<?php
/**
 * 地区SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

/**
 * 地区管理SDK
 * Author: guowenzheng
 * Email: guowenzheng@yuanxinjituan.com
 * Date: 2022/3/29.
 * NameSpace: nisiya\baseapi
 * Name: DistrictSdk
 */
class DistrictSdk extends CommonSdk
{

    /**
     * 获取省份列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function province()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据省ID获取市列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $province_id
     * @return bool|mixed|string
     */
    public function city($province_id)
    {
        $params = [
            'province_id' => $province_id
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据城市ID获取地区列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $city_id
     * @return bool|mixed|string
     */
    public function district($city_id)
    {
        $params = [
            'city_id' => $city_id
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }


    /**
     * 获取所有地区数据，返回一维数组
     * @param int $is_update_cache 是否更新缓存，1更新，0不更新
     * @return bool|mixed|string
     */
    public function getAllData($is_update_cache = 0)
    {
        $params = [
            'is_update_cache' => $is_update_cache
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 获取所有地区数据，返回树状结构
     * @param int $is_update_cache 是否更新缓存，1更新，0不更新
     * @return bool|mixed|string
     */
    public function getAllDataTree($is_update_cache = 0)
    {
        $params = [
            'is_update_cache' => $is_update_cache
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 获取地区映射数据
     * @param int $is_update_cache 是否更新缓存，1是，0否
     * @return array|bool|mixed|string
     */
    public function getMapData($is_update_cache = 0)
    {
        $params = [
            'is_update_cache' => $is_update_cache
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}