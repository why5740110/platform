<?php
/**
 * 检查
 * @file JianchaSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\jiancha;

use nisiya\branddoctorsdk\CommonSdk;

class JianchaSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'jiancha';
        parent::__construct();
    }

    /**
     * 根据id获取检查详情
     * @param $jcid
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function detail($jcid)
    {
        $params = [
            'jcid' => $jcid,
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

}