<?php
/**
 * 活动页
 * @file ActivityspecialSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-03-07
 */

namespace nisiya\mallsdk\other;

use nisiya\mallsdk\CommonSdk;

class ActivityspecialSdk extends CommonSdk
{

	/**
	 * 获取广告页内容
	 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
	 * @date 2019-03-08 
	 * @param string $act_en 活动页拼音名称
	 * @param int $store_id 药店id
	 * @param int $city_id 城市id
	**/
    public function getactivityinfo($act_en, $store_id, $city_id){
    	$params = [
    		'act_en' => $act_en,
    		'store_id' => $store_id,
    		'city_id' => $city_id,
    	];
        return $this->send($params,__METHOD__);
    }

}