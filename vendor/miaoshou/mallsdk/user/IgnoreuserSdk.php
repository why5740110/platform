<?php
/**
 *
 * @file IgnoreuserSdk.php
 * @author suxingwang <suxingwang@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-25
 */

namespace nisiya\mallsdk\user;


use nisiya\mallsdk\CommonSdk;

class IgnoreuserSdk extends CommonSdk
{

    /**
     * 判断该用户是否在 购物金和名单
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date   2019-01-14
     * @param $amount 商品总金额
     **/
    public function getignoreuser()
    {
        $params = [];
        return $this->send($params, __METHOD__);
    }
}