<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : CartSdk.php
 * @author  : suxingwang <suxingwang@yuanxin-inc.com>
 * @date    : 2018-12-15
 */

namespace nisiya\mallsdk\user;

use nisiya\mallsdk\CommonSdk;

class CartSdk extends CommonSdk
{
    /** 获取购物车列表
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2018/12/15
     * @param $uid   int  格式  用户id
     * @param $store_id int 格式 药店id
     * @param $cart_name int 格式  购物车标识
     * @return bool|mixed
     */
    public function getlist($store_id,$cart_name,$cart_type_province = 0)
    {
        $params = [
            // 'uid' => $uid,
            'store_id'  => $store_id,
            'cart_name' => $cart_name,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }

    /** 获取购物车内商品数量
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2018/12/15
     * @param $uid   int  格式  用户id
     * @param $store_id int 格式 药店id
     * @param $cart_name int 格式  购物车标识
     * @return bool|mixed
     */
    public function getcartnum($store_id,$cart_name,$cart_type_province = 0)
    {
        $params = [
            // 'uid' => $uid,
            'store_id'  => $store_id,
            'cart_name' => $cart_name,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }

    /** 更改购物车内商品选中状态
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2018/12/15
     * @param $uid   int  格式  用户id
     * @param $store_id int 格式 购物车id
     * @param $cart_name int 格式  购物车标识
     * @return bool|mixed
     */
    public function cartstatusupdate($product_id,$cart_name,$store_id,$status,$activity_id,$sub_activity_id,$cart_type_province = 0)
    {
        $params = [
            // 'uid' => $uid,
            'product_id'  => $product_id,
            'activity_id'  => $activity_id,
            'sub_activity_id'  => $sub_activity_id,
            'cart_name' => $cart_name,
            'store_id'  => $store_id,
            'status'  => $status,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }


     /**
     * 加减购物车
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-12-17
    **/
    public function add($params = [])
    {
        $params = [
            // 'uid' => $params['uid'] ?? 0, //用户id
            'store_id' => $params['store_id'] ?: 0, //药店id
            'product_id' => $params['product_id'] ?: 0, //商品id
            'activity_id' => isset($params['activity_id']) && $params['activity_id'] ? $params['activity_id']: 0, //商品类型
            'sub_activity_id' => isset($params['sub_activity_id']) && $params['sub_activity_id']? $params['sub_activity_id']: 0, //活动id
            'user_cart_product_num' => $params['user_cart_product_num'] ?: 0, //追加购物车数量 为0时删除此条记录
            'cart_name' => $params['cart_name'], //购物车名称
            'allowance_num' => $params['allowance_num'] ? : 0, // 数量直接加或减
            'cart_sign' => isset($params['cart_sign']) && $params['cart_sign'] ? $params['cart_sign'] : '', //购物车商品标识
            'cart_type_province' => isset($params['cart_type_province'])&&$params['cart_type_province']?$params['cart_type_province']:0,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 添加商品到购物车校验
     * @author wangchenxu <wanchenxu@yuanxin-inc.com>
     * @date 2018-12-21
     * @param int product_id 商品id
     * @param int store_id 药店id
     * @param string cart_sign 购物车数据标识 活动序号_活动类型
     * @param user_cart_product_num 添加的数量
    **/
    public function addproductvalidate($product_id = 0, $store_id = 0, $cart_sign='0_0', $user_cart_product_num = 1,$cart_type_province = 0)
    {
        $params = [
            'product_id' => $product_id, 
            'store_id' => $store_id,
            'cart_sign' => $cart_sign,
            'user_cart_product_num' => $user_cart_product_num,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取临时用户购物车商品列表数据
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-12-21
     * $data[
        'products[1_12_1_0]' => 1, //key 商品id_活动id_活动类型_是否选中 value 添加的数量
        'products[1_13_2_1]' => 2, //key 商品id_活动id_活动类型_是否选中 value 添加的数量
        'store_id' => 17, //药店id
     ]
    **/
    public function tempcartproductlist($data)
    {
        return $this->send($data, __METHOD__,'post');
    }

    /**
     * 获取提交购物车选中的商品id
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-01-20
    **/
    public function getcartconfirmproductid($store_id, $cart_name,$cart_type_province=0)
    {
        $params = [
            'store_id' => $store_id,
            'cart_name' => $cart_name,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 检查再次购买商品是否符合购买条件
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @param string $order_sn 订单号
     * @param int $cart_name 购物车名称
     * @date 2019-05-21
    **/
    public function checkbuyagain($order_sn, $cart_name,$cart_type_province=0)
    {
        $params = [
            'order_sn' => $order_sn,
            'cart_name' => $cart_name,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 再次购买
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @param string $order_sn 订单号
     * @param int $cart_name 购物车名称
     * @date 2019-05-21
    **/
    public function buyagain($order_sn, $cart_name,$cart_type_province=0)
    {
        $params = [
            'order_sn' => $order_sn,
            'cart_name' => $cart_name,
            'cart_type_province' => $cart_type_province
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 修改购物车选中状态(新)
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @param string $cart_ids 主键id
     * @param int $cart_name 购物车名称
     * @param int $status 状态
     * @date 2019-05-21
    **/
    public function cartupdatestatus($cart_ids, $cart_name, $status, $store_id)
    {
        $params = [
            'cart_ids' => $cart_ids,
            'cart_name' => $cart_name,
            'status' => $status,
            'store_id' => $store_id,
        ];
        return $this->send($params, __METHOD__);
    }
}