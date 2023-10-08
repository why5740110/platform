<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : DataSdk.php
 * @author  : zhangyandong <xiezhibin@yuanxin-inc.com>
 * @date    : 2019-10-21
 */
namespace nisiya\ucentersdk\ucenter;

use nisiya\ucentersdk\CommonSdk;

class DataSdk extends CommonSdk
{
    /**
     * @param $login_key 登陆后返回的用户登陆标识
     * @param $mobile 手机号
     * @param int $is_merge 是否合并两个用户 （如果这个手机号已被注册）
     * @param $verify_code 手机验证码
     * @param $is_wechat_bind 是否是微信环境，1：微信环境，0：其他渠道
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/10/21 11:36
     * @return bool|mixed 返回用户对象
     */
    public function bindmobile($login_key,$mobile,$verify_code, $is_wechat_bind = 1, $is_merge=1) {
        $userData = [
            'login_key'        => $login_key,
            'mobile'        => $mobile,
            'is_merge'            => $is_merge,
            'code'          => $verify_code,
            'is_wechat_bind'     => $is_wechat_bind
        ];
        return $this->send($userData, __METHOD__,'post');
    }

}
