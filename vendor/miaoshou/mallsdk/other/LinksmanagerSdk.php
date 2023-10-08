<?php
/**
 *
 * @file LinksmanagerSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-01-09
 */

namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class LinksmanagerSdk extends CommonSdk
{
    /**
     * 商品分类
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-09
     */
    public function productmenu()
    {
        return $this->send([], __METHOD__);
    }

    /**
     * 首页推荐商品
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-09
     */
    public function recommendproduct()
    {
        return $this->send([], __METHOD__);
    }

    /**
     * 首页轮播图
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-15
     * @return bool|mixed
     */
    public function sliderpicture()
    {
        return $this->send([], __METHOD__);

    }
}