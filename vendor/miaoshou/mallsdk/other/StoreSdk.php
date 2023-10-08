<?php
/**
 *
 * @file StoreSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-24
 */

namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class StoreSdk extends CommonSdk
{
    /**
     *
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-07-24
     * @param null $store_type_ids    药店的类型
     * @param null $has_store_cover   是否有封面图
     * @param null $is_super          是否是超级药店
     * @param null $erp_code          erp_code
     * @param null $status            药店状态
     * @param null $store_ids          药店id
     * @return bool|mixed
     */
    public function getlist($store_type_ids =null, $has_store_cover = null, $is_super = null, $erp_code = null, $status=null, $city_id=null, $store_ids=null){
        $params['store_type_ids']=$store_type_ids;
        $params['has_store_cover']=$has_store_cover;
        $params['is_super']=$is_super;
        $params['erp_code']=$erp_code;
        $params['status']=$status;
        $params['city_id']=$city_id;
        $params['store_ids']=$store_ids;
        return $this->send($params,__METHOD__,'get');
    }

    /**
     *
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-07-24
     * @param $page
     * @param $pagesize
     * @param null $store_type_ids    药店的类型
     * @param null $has_store_cover   是否有封面图
     * @param null $is_super          是否是超级药店
     * @param null $erp_code          erp_code
     * @param null $status            药店状态
     * @return bool|mixed
     */
    public function getpagelist($page, $pagesize, $store_type_ids =null, $has_store_cover = null, $is_super = null, $erp_code = null, $status=null){
        $params['store_type_ids']=$store_type_ids;
        $params['page']=$page;
        $params['pagesize']=$pagesize;
        $params['has_store_cover']=$has_store_cover;
        $params['is_super']=$is_super;
        $params['erp_code']=$erp_code;
        $params['status']=$status;
        return $this->send($params,__METHOD__,'get');
    }

    /**
     * 获取当前城市默认发货店铺信息
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-09-07
     */
    public function selectdefaultstoreinfo($city_id)
    {
        $params = [
            'city_id' => $city_id,
        ];
        return $this->send($params, __METHOD__);

    }

    /**
     * 获取一个药店详情
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-09-07
    **/ 
    public function item($store_id)
    {
        $params = [
            'store_id' => $store_id
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取商城默认发货药店信息
    **/
    public function getsuperstoreinfo()
    {
        return $this->send([], __METHOD__);
    }

    /**
     * 获取当前推广城市所有药店
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-01-14
    **/
    public function getcitystorelist($city_id)
    {
        $params['city_id'] = $city_id;
        return $this->send($params, __METHOD__);
    }



    /**
     * 获取可服务药房列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2019/3/28
     * @param $cityId   城市序号
     * @param $products 商品信息  products[商品序号]=商品数量
     * @return bool|mixed
     */
    public function getserviceablestore($cityId, $products){
        $params = [
            'city_id' => $cityId,
            'products' =>$products
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 通过药店名获取药店列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2019/6/11
     * @param $storeName
     * @return bool|mixed
     */
    public function storelistbyname($storeName){
        $params = [
            'store_name' => $storeName,
        ];
        return $this->send($params, __METHOD__);

    }

    /**
     * 药店登陆
    **/
    public function login($mobile, $password)
    {
        $params = [
            'mobile' => $mobile,
            'password' => $password,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取登录用户的定位省份所绑定药店或中心仓
     * @param $provinceId
     * @param $regStoreId
     */
    public function getuserregstoreandstorecorewarebyprovinceid ($provinceId, $regStoreId)
    {
        $params = [
            'province_id' => $provinceId,
            'reg_store_id' => $regStoreId,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取当前药店省份中心店店和中心仓信息
    **/
    public function getwarestoreandshippingstore($storeId)
    {
        return $this->send(['store_id' => $storeId], __METHOD__);
    }
}