<?php
/**
 * 合作企业&&合作来源
 * @file EnterpriseSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\brand;

use nisiya\branddoctorsdk\CommonSdk;

class EnterpriseSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'bapi';
        parent::__construct();
    }

    /**
     * 获取合作企业、企业来源接口
     * @param int $type 类型 1:合作企业；2:合作来源；默认是1
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function getEnterpriseRides($type = 1)
    {
        $params = [
            'type' => intval($type),
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

    /**
     * 获取合作企业初始虚拟浏览数
     * @param int $enterprise_id 企业id
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @date 2020-07-02
     * @return array|bool|mixed
     */
    public function getEnterViews($enterprise_id)
    {
        $params = [
            'enterprise_id' => intval($enterprise_id),
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

}