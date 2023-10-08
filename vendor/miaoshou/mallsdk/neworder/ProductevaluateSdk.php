<?php
/**
 * Created by PhpStorm.
 * User: suxingwang
 * Date: 2020-06-17
 * Time: 10:46
 */


namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class ProductevaluateSdk extends CommonSdk
{
    /**
     * 评价列表
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     * @param  int   $user_id 用户id （必填）
     * @param  int   $page 页码（选填）
     * @param  int   $pagesize 每页显示的条数（选填）默认10条
     */
    public function pagelist($user_id, $page = 1, $pagesize = 10)
    {
        $params = [
            'user_id' => $user_id,
            'order_sn' => $page,
            'pagesize' => $pagesize
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 添加评价
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     * @param  int   $user_id 用户id （必填）
     * @param  int   $order_product_id 订单商品表主键id （必填）
     * @param  int   $service 总体评价数（必填）
     * @param  int   $distribution 配送评价数（必填）
     * @param  int   $attitude 态度评价数（必填）
     * @param  int   $content 评价内容（必填）
     */
    public function add($params)
    {
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取一个评价详情
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     * @param  int   $user_id 用户id （必填）
     * @param  int   $product_id 商品id（必填）
     * @param  int   $order_sn 订单号（选填）
     */
    public function detail($user_id, $product_id, $order_sn)
    {
        $params = [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'order_sn' => $order_sn
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 添加评价详情
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     * @param  int   $order_product_id 订单商品表主键id（必填）
     */
    public function createdetail($order_product_id)
    {
        $params = [
            'order_product_id' => $order_product_id,
        ];
        return $this->send($params, __METHOD__);
    }
}