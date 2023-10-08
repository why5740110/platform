<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : LoginSdk.php
 * @author  : zhangyandong <zhangyandong@yuanxin-inc.com>
 * @date    : 2019-07-29
 */

namespace nisiya\ucentersdk\ucenter;

use nisiya\ucentersdk\CommonSdk;

class LoginSdk extends CommonSdk
{
    /**
     * 账号密码登陆
     * @param $username  $username $password $type$hash
     * @param $password
     * @param string $type 用户类型 user/doctor
     * @param bool $hash 是否hash密码
     * @param string $idType 帐号类型 email/mobile 如果为空,则按照 用户名/王氏id/手机 来判断 luoyunan 15.07.10 for 商城登录
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/26 17:35
     * @return json
     */
    public function login($username, $password, $type='user', $hash=true, $idType='') {
        if($hash) {
            $passwordHash = md5('yuanxin'.md5($password));
        } else {
            $passwordHash = $password;
        }
        $params = [
            "username" =>$username,
            "password" =>$passwordHash,
            "type"      => $type,
            "idType"    => $idType
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * @param $loginKey 用户中心登陆token
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/26 17:58
     * @return bool|mixed
     */
    public function verificationlogin($loginKey)
    {
        $params = [
            'loginkey' => $loginKey,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * @param $mobile         手机号   （必填）
     * @param $verify_code    验证码    （必填）
     * @param $appname        应用名称   （非必填） 默认mall
     * @param $type           用户类型   （非必填）默认user
     * @param $new_source     来源     （非必填）默认空
     * @param int $tid        三方用户id(目前用于百度小程序绑定) （非必填）默认空
     * @param int $close_verify_code 关闭验证
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/26 17:33
     * @return mixed
     */
    public  function mobilelogin($mobile , $verify_code='' ,$appname='mall', $type='user', $new_source='' ,$tid='',$close_verify_code=0)
    {
        $params = [
            'mobile'        => $mobile,
            'verify_code'        => $verify_code,
            'appname'            => $appname,
            'type'           => $type,
            'new_source'        => $new_source,
            'tid'        => $tid,
            'close_verify_code'        => $close_verify_code,
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 统一退出接口
     * @param $loginkey 登陆唯一标识
     * @param string $appname appname 非必填
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/1/18 10:23
     * @return json
     */
    public function logout($loginkey,$appname=''){
        $params = array(
            'loginkey'        => $loginkey,
        );
        return $this->send($params, __METHOD__);
    }

    /**
     * wc 项目快速登陆
     * @param $uid 用户id
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 18:32
     * @return $data  json
     */
    public function wechat($uid) {
        $params = [
            "uid" => $uid,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据渠道来源返回是否需要验证手机号
     * User: niewei
     * Datetime: 2020/8/14 10:10
     *
     * @param string $from 合作方渠道标识
     */
    public function isverifymobile($from)
    {
        $params = [
            "from" => trim($from),
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取第三方用户授权loginkey
     * User: niewei
     * Datetime: 2020/8/14 10:27
     *
     * @param string  $from  合作方渠道标识
     * @param string $mobile 用户手机号
     * @param string $unique_id 用户在合作方唯一标识
     */
    public function getloginkey($from, $mobile, $unique_id)
    {
        $params = [
            "from" => trim($from),
            "mobile" => trim($mobile),
            "unique_id" => trim($unique_id)
        ];
        return $this->send($params, __METHOD__, 'post');
    }
}