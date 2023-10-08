<?php
/**
 * 创建处方单
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-11-21
 */

namespace nisiya\mallsdk\order;

use nisiya\mallsdk\CommonSdk;
class CreaterecipelorderSdk extends CommonSdk
{

    /**
     * 第三方生成处方单
	 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
	 * @version 2.0
	 * @date 2019-11-21
     *
     */
    public function partercreate($params)
    {
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 第三方创建处方单失败
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @version 2.0
     * @date 2019-11-21
    **/
    public function partercreatefail($params)
    {
        return $this->send($params, __METHOD__,'post');
    }
}