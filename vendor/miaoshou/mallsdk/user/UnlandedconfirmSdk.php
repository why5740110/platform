<?php
/**
 *
 * @file UnlandedSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-25
 */

namespace nisiya\mallsdk\user;


use nisiya\mallsdk\CommonSdk;

class UnlandedSdk extends CommonSdk
{

	/**
	 * 获取用户优惠券/红包
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date   2019-01-14
     * @param $amount 商品总金额
	**/
	public function add($params)
	{
		return $this->send($params, __METHOD__);
	}
}