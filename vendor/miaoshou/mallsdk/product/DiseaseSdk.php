<?php
/**
 * 疾病库接口
 * @file DiseaseSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-08-22
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class DiseaseSdk extends CommonSdk
{

    /**
     * 疾病库搜索建议
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-12-03
     * @param string $keyword 关键词
     */
    public function suggest($keyword)
    {
        $params = [
            'keyword' => $keyword,
        ];
        return $this->send($params, __METHOD__);
    }
}