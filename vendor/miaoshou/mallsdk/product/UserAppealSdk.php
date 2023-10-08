<?php
/**
 *
 * @file SearchSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-05-17
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class UserAppealSdk extends CommonSdk
{
    /**获取当天预约信息
     * @param $params
     *         user_id
     *         product_id
     * @return bool|mixed
     * @date 2019-11-22
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function getuserappeal($params){
        return $this->send($params,__METHOD__,'post');
    }

    /**保存预约信息
     * @param $params
     * @return bool|mixed
     * @date 2019-11-22
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function saveuserappeal($params){
        return $this->send($params,__METHOD__,'post');
    }

}