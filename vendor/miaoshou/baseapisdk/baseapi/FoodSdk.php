<?php
/**
 * 食物SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class FoodSdk extends CommonSdk
{
    /**
     * 获取食物列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function list($perpage = '')
    {
        $params = [];
        if (!empty($perpage)) {
            $params['perpage'] = $perpage;
        }
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     *
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $id
     * @return bool|mixed|string
     */
    public function detail($id)
    {
        $params = [
            'id' => $id
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}