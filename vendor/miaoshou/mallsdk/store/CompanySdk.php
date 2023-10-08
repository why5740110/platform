<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/6
 * Time: 11:21
 */


namespace nisiya\mallsdk\store;

use nisiya\mallsdk\CommonSdk;

class CompanySdk extends CommonSdk
{
    /**
     * 根据公司名称查询公司信息
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-8-6
     **/
    public function getcompaybyname($name)
    {
        $params['name'] = $name;
        return $this->send($params, __METHOD__,'post');
    }
}