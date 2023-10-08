<?php
/**
 *
 * @file ProductCategorySdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-01-13
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class ProductCategorySdk extends CommonSdk
{
    /**
     * 商品分类调用ID
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-13
     * @param $pid 分类父级ID
     */
    public function getlist($pid = 0, $filter_category_ids_3 = '')
    {
        $params = [
            'pid' => $pid,
            'filter_category_ids_3' => $filter_category_ids_3,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取某一分类的上级分类信息
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-02-27
    **/
    public function getparentinfo($cat_id = 0)
    {
        $params = [
            'cat_id' => $cat_id
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 商品分类调用ID(过滤没有商品得分类)
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-04-29
     * @param $pid 分类父级ID
     */
    public function getlistbyfilternotnull($pid = 0, $filter_category_ids_3 = '', $store_id=17)
    {
        $params = [
            'pid' => $pid,
            'filter_category_ids_3' => $filter_category_ids_3,
            'store_id' => $store_id,
        ];
        return $this->send($params, __METHOD__);
    }
    public function vipproductcategory(){
        $params = [];
        return $this->send($params, __METHOD__);
    }
}
