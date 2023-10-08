<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : Publicdk.php
 * @author  : zhangyandong <xiezhibin@yuanxin-inc.com>
 * @date    : 2019-07-29
 */

namespace nisiya\ucentersdk\ucenter;

use nisiya\ucentersdk\CommonSdk;

class PublicSdk extends CommonSdk
{
    /**
     * 修改密码/找回密码
     * @param $user_id 用户id
     * @param $new_password 原密码 string （非必填 与  短信验证码  必选其一） 原密码
     * @param $old_password 新密码
     * @param $code string   (非必填 与 原密码 必选其一) 短信验证码
     * @param $type int (必填) 1默认账号密码修改  2 找回密码 （无需登陆） 3 手机号验证码修改密码（需登陆）
     * @param $login_key login_key   非必填   用户登陆标识 （手机号验证码修改密码必传）
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 14:30
     * @return bool|mixed
     */
    public function findpwd($user_id,$new_password,$old_password='',$type,$code='',$login_key='')
    {
        $params = [
            'doctor_id'=>$user_id,
            'new_password'=>$new_password,
            'old_password'=>$old_password,
            'code'=>$code,
            'login_key'=>$login_key,
            'type' => $type,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 验证手机号和手机验证码是否正确匹配
     * @param $mobile 手机号
     * @param $code 手机验证码
     * @param $newSource 来源
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 14:32
     * @return bool|mixed
     */
    public function verifycode($mobile, $code, $newSource='')
    {
	    $params = [
	        'mobile'=>$mobile,
	        'code'=>$code,
	        'newSource'=>$newSource,
	    ];
	    return $this->send($params, __METHOD__);
    }
    
}