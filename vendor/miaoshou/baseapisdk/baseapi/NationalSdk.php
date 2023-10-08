<?php
/**
 * 民族SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class NationalSdk extends CommonSdk
{
    /**
     * 民族管理
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function list()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}