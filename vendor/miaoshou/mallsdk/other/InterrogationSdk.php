<?php
/**
 * 问诊对接sdk
 * @file InterrogationSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-20
 */

namespace nisiya\mallsdk\other;

use nisiya\mallsdk\CommonSdk;

class InterrogationSdk extends CommonSdk
{

    /**
    * 创建问诊用药列表 (推送给百度)
    * @param string $interrogationId 问诊id
    * @param string $recipelId 处方单号
   **/
   public function createproductlist($interrogationId, $recipelId)
   {
      $params['interrogation_id'] = $interrogationId;
      $params['recipel_id'] = $recipelId;
        return $this->send($params,__METHOD__);
   }

}