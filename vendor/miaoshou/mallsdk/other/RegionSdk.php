<?php
/**
 *
 * @file RegionSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-05-19
 */

namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class RegionSdk extends CommonSdk
{
    /**
     * 行政区划列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-05-19
     * @return bool|mixed
     */
    public function list(){
        return $this->send([],__METHOD__);
    }

    /**
     * 行政区划树列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-05-19
     * @return bool|mixed
     */
    public function treelist(){
        return $this->send([],__METHOD__);
    }

    /**
     * 通过城市名称搜索
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-09-10
    **/
    public function searchcityname($keyword = '')
    {   
        $params = [
            'keyword' => trim($keyword),
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 通过行政区划序号获取行政区划信息
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018/12/26
     * @param $regionIds
     * @return bool|mixed
     */
    public function getlistbyregionids($regionIds){
        $params = [
            'region_ids' => trim($regionIds),
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 获取省份列表
     * @return bool|mixed
     */
    public function provincelist(){
        return $this->send([],__METHOD__);
    }
}