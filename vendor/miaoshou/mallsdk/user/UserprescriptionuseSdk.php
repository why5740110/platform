<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : UserprescriptionuseSdk.php
 * @author  : suxingwang <suxingwang@yuanxin-inc.com>
 * @date    : 2019-1-15
 */

namespace nisiya\mallsdk\user;

use nisiya\mallsdk\CommonSdk;

class UserprescriptionuseSdk extends CommonSdk
{
    /** 用药人列表
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/1/15
     * 根据 user_id 获取
     *
     * @return bool|mixed
     */
    public function getlist()
    {
        $params = [];
        return $this->send($params, __METHOD__);
    }

    /** 添加用药人
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/11/15
     * @return bool|mixed
     */
    public function adduser($params)
    {
        return $this->send($params, __METHOD__,'post');
    }

    /** 修改用药人信息
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/11/15
     * @return bool|mixed
     */
    public function edituser($params)
    {
        return $this->send($params, __METHOD__,'post');
    }
    /** 修改用药人选中状态
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/1/15
     * @return bool|mixed
     */
    public function editstatus($prescription_use_id,$status)
    {
        $params = [
            'prescription_use_id'  => $prescription_use_id,
            'status'               => $status,
        ];
        return $this->send($params, __METHOD__);
    }

    /** 删除用药人
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/1/15
     * @return bool|mixed
     */
    public function deleteuser($userPrescriptionUseId)
    {
        $params = [
            'user_prescription_use_id'  => $userPrescriptionUseId,
        ];
        return $this->send($params, __METHOD__);
    }

    /** 获取默认的用药人
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/1/15
     * 根据 user_id 获取
     *
     * @return bool|mixed
     */
    public function getdefaultuser()
    {
        $params = [];
        return $this->send($params, __METHOD__);
    }

}