<?php
/**
 *
 * @file EproductapilogSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-08-01
 */

namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class EproductapilogSdk extends CommonSdk
{

    public function addlog($params){
        $status = $this->send($params, __METHOD__, 'post');
        return $status;
    }

}