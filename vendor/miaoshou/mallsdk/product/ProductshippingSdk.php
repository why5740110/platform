<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : ProductshippingSdk.php
 * @author  : suxingwang <suxingwang@yuanxin-inc.com>
 * @date    : 2018-12-15
 */

namespace nisiya\mallsdk\product;

use nisiya\mallsdk\CommonSdk;

class ProductshippingSdk extends CommonSdk
{
    /** 获取商品包邮信息
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/1/18
     * @param $pid string 格式 商品id  1,2,3
     * @param $status int 格式 是否启动 默认 1
     * @return bool|mixed
     */
    public function getproductrules($pid,$status=1)
    {
        $params = [
            'pid'    => $pid,
            'status' => $status
            ];
        return $this->send($params, __METHOD__);
    }

}