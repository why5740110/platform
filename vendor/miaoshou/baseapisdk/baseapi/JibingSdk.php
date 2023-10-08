<?php
/**
 * 疾病相关SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class JibingSdk extends CommonSdk
{


    /**
     * 根据名称匹配疾病、症状名称数据
     * @param string $name 名称
     * @return array|bool|mixed|string
     */
    public function getRelationsListByJnameOrZname($name)
    {
        $params = [
            'name' => $name
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}