<?php
/**
 * 淘宝客sdk
 * @file TbkSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @date 2017-12-27
 */

namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class TbkSdk extends CommonSdk
{
    /**
     * 获取淘宝分类信息
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @return bool|mixed
     */
    final public function getcouponcategory()
    {

        return $this->send([], __METHOD__);
    }

    /**
     * 获取淘宝客优惠券列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @param int $category_id
     * @param int $page
     * @param int $pagesize
     * @return bool|mixed
     */
    final function getcouponlist($category_id, $page, $pagesize)
    {
        $params = [
            'category_id' => $category_id,
            'page' => $page,
            'pagesize' => $pagesize
        ];
        return $this->send($params, __METHOD__);

    }
}