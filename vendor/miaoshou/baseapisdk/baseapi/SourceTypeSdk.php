<?php
/**
 * 来源类型SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class SourceTypeSdk extends CommonSdk
{
    /**
     * 获取全部来源类型数据
     * @param int $is_update_cache 是否刷新缓存，1是，0否
     * @return array|bool|mixed|string
     */
    public function getAllData($is_update_cache = 0)
    {
        $params = ['is_update_cache' => $is_update_cache];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}