<?php
/**
 *
 * @file ProductpartnerSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-02-01
 */

namespace nisiya\mallsdk\product;
use nisiya\mallsdk\CommonSdk;

class ProductpartnerSdk extends CommonSdk
{
    /**
     * 获取商品列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-02-01
     * @param int $page 页数
     * @param int $pagesize 单页个数
     * @return bool|mixed
     */
    public function productlist($keyword, $pagesize)
    {
        $params['keyword'] = $keyword;
        $params['pagesize'] = $pagesize;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取商品列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-02-01
     * @param $params  参数集合
     * @param int $page 页数
     * @param int $pagesize 单页个数
     * @return bool|mixed
     */
    public function productpagelist($keyword, $page = 1, $pagesize = 20)
    {
        $params['keyword'] = $keyword;
        $params['page'] = $page;
        $params['pagesize'] = $pagesize;
        return $this->send($params, __METHOD__);
    }


}