<?php
/**
 * 药品推荐
 * @file RecommendSdk.php
 * @author zhibin<xiezhibin@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-01-09
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class RecommendSdk extends CommonSdk
{

    /**
     * 获取浏览推荐
     * @author zhibin <xiezhibin@yuanxin-inc.com>
     * @date   2018-01-17
     * @param  array $params 请求参数
     * @return array|false
     */
    public function relate($params)
    {
        return $this->send($params, __METHOD__);
    }


    /**
     * 标签推荐
     * @author zhibin <xiezhibin@yuanxin-inc.com>
     * @date   2018-01-17
     * @param  array $params 请求参数
     * @return array|false
     */
    public function interest($params)
    {
    	return $this->send($params, __METHOD__);
    }
}