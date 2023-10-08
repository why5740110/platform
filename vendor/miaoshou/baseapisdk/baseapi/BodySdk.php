<?php
/**
 * 身体部位SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class BodySdk extends CommonSdk
{

    /**
     * 获取一级身体部位
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function first()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 获取二级身体部位
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $id
     * @return bool|mixed|string
     */
    public function second($bid)
    {
        $params = [
            'bid' => $bid
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}