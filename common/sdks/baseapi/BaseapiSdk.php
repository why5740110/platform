<?php
/**
 * 基础数据sdk
 * @file BaseapiSdk.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-22
 */

namespace common\sdks\baseapi;

use nisiya\baseapisdk\baseapi\DistrictSdk;
use nisiya\baseapisdk\Config;

class BaseapiSdk
{

    /**
     * 获取所有省市数据
     * @param int $is_update_cache 是否更新缓存，1更新，0不更新
     * @return bool|mixed|string
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-23
     */
    public function getAllData(int $is_update_cache = 0){
        Config::setConfig(\Yii::$app->params['baseapisdk']);
        $districtSdk = new DistrictSdk();
        return $districtSdk->getAllData($is_update_cache);
    }

    /**
     * 获取所有地区数据，返回树状结构
     * @param int $is_update_cache $is_update_cache 是否更新缓存，1更新，0不更新
     * @return bool|mixed|string
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-23
     */
    public function getAllDataTree(int $is_update_cache = 0){
        Config::setConfig(\Yii::$app->params['baseapisdk']);
        $districtSdk = new DistrictSdk();
        return $districtSdk->getAllDataTree($is_update_cache);
    }

    /**
     * 获取省列表
     * @return bool|mixed|string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function getProvince(){
        Config::setConfig(\Yii::$app->params['baseapisdk']);
        $districtSdk = new DistrictSdk();
        return $districtSdk->province();
    }

    /**
     * 根据省id获取城市列表
     * @param $province_id
     * @return bool|mixed|string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function getCity($province_id){
        Config::setConfig(\Yii::$app->params['baseapisdk']);
        $districtSdk = new DistrictSdk();
        return $districtSdk->city($province_id);
    }


    /**
     * 根据城市id获取县（区）列表
     * @param $city_id
     * @return bool|mixed|string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function getDistrict($city_id){
        Config::setConfig(\Yii::$app->params['baseapisdk']);
        $districtSdk = new DistrictSdk();
        return $districtSdk->district($city_id);
    }

}