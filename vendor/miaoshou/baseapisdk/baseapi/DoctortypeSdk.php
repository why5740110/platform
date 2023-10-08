<?php
/**
 * 医生职称SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class DoctortypeSdk extends CommonSdk
{
    /**
     * 获取医生职称列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function list($type = 0)
    {
        $params = [
            'type' => $type
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }


    /**
     * 更新医生职称缓存（redis:set）
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/4/20
     * @return array|bool|mixed|string
     */
    public function updatecache()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}