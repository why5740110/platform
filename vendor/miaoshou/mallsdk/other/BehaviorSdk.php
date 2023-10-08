<?php
/**
 * Behavior sdk
 * @file BehaviorSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-03-07
 */

namespace nisiya\mallsdk\other;

use nisiya\mallsdk\CommonSdk;

class BehaviorSdk extends CommonSdk
{
    /**
     * add behavior sdk func
     * @param $userBehaviorType
     * @param $userBehaviorUniqIden
     * @param $appId
     * @param $productUnqieIden
     * @param $userBehaviorTypeId
     * @param $url
     * @param $refrer
     * @author xuyi
     * @date 2019/7/16
     * @return bool|mixed
     */
    public function add($userBehaviorType, $userBehaviorUniqIden, $appId, $productUnqieIden, $userBehaviorTypeId, $url, $refrer)
    {
        $params = array(
            'user_behavior_unique_identification_type'  =>$userBehaviorType,
            'user_behavior_unique_identification' => $userBehaviorUniqIden,
            'application_id' => $appId,
            'product_unique_identification' => $productUnqieIden,
            'user_behavior_type_id' => $userBehaviorTypeId,
            'user_behavior_url' => $url,
            'user_behavior_refrer' => $refrer,
        );

        return $this->send($params,__METHOD__);
    }
}