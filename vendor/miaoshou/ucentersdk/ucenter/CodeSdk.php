<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : CodeSdk.php
 * @author  : zhangyandong <xiezhibin@yuanxin-inc.com>
 * @date    : 2019-07-29
 */

namespace nisiya\ucentersdk\ucenter;

use nisiya\ucentersdk\CommonSdk;

class CodeSdk extends CommonSdk
{
    /**
     * 图片验证码
     * @param $user_id
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 11:43
     * @return bool|mixed
     */
    public function captcha($user_id)
    {
        $params = array(
            'uid' => $user_id,
        );
        return $this->send($params, __METHOD__);
    }

//    /**
//     * @param $user_id 用户id,
//     * @param $refresh 是否刷新图片验证码
//     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
//     * @date 2019/7/29 11:46
//     * @return bool|mixed
//     */
//    public function captchaimg($user_id,$refresh)
//    {
//        $params = [
//            'uid' => $uid,
//            'refresh' => $refresh,
//        ];
//        return $this->send($params, __METHOD__);
//    }

    /**
     * @param $mobile 手机号   （必选）
     * @param string $verifycode 图片验证码   （非必选）
     * @param int $noverify 是否需要图片验证码 （默认0：需要 1：不需要）注意：当noverify为1时需要验证token
     * @param int $type 用户类型 为3时 不验证是否是医生
     * @param int $uid
     * @param int $accountSign  模板签名：默认为0:【王氏医生】;1:【曜影医疗】;2:【仙桃云医】;3:【妙优皮肤】;4:【王氏Doctor】
     * @param int $codetype  短信验证码类型  0 默认（未知）1 修改密码 2 找回密码 3 注册 4 登陆 5 绑定手机号
     * @param string $login_key 用户登陆标识 （修改密码必传）
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/9/21 10:40
     * @return bool|mixed
     */
    public function mobilecode ($mobile,$verifycode='',$noverify=0,$type=0,$uid=0,$accountSign=0,$codetype=0,$login_key=''){
        $params = [
            'uid'        => $uid,
            'mobile'        => $mobile,
            'verifycode'        => $verifycode,
            'noverify'        => 1,
            'type'        => $type,
            'accountSign'        => $accountSign,
            'codetype'        => $codetype,
            'login_key'        => $login_key,
        ];
        return $this->send($params, __METHOD__);
    }

}