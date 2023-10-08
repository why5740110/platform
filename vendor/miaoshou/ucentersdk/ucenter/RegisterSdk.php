<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : CodeSdk.php
 * @author  : zhangyandong <xiezhibin@yuanxin-inc.com>
 * @date    : 2019-07-29
 */
namespace nisiya\ucentersdk\ucenter;

use nisiya\ucentersdk\CommonSdk;

class RegisterSdk extends CommonSdk
{
    /**
     * 用户注册
     * @param  string  $username        用户名
     * @param  string  $password        用户密码
     * @param  string  $type            用户类型  user/doctor
     * @param  integer $mobile          (option) 手机号码
     * @param  string  $email           (option) 邮箱地址
     * @param  boolean $hash            (option) 是否hash密码修改
     * @param  string  $nickname        (option) 昵称
     * @param  string  $avatar          (option) 头像地址
     * @param  integer $mobile_verified (option) 手机号是否验证过 调用类常量 static::VERIFIED_****
     * @param  integer $email_verified  (option) 邮箱是否验证过 调用类常量 static::VERIFIED_****
     * @param  string  $verify_code      手机验证码
     * @return array   结果数组
     */
    public function register($username , $password ,$type, $mobile = 0, $email ='', $hash=true, $nickname='', $avatar='', $mobile_verified = 0, $email_verified = 0,$source='',$verify_code='',$newsource='',$weAvatar='',$ip='',$invite_by='',$invited_nisiya_id=0,
                             $gender=0,$age=0,$idcard='',$height=0,$weight=0,$waistline=0,$orgid='',$birthday=0,$healthcare=0,$realname='',$street='',$address='',$telephone='',$membercode='',$location=''
) {
        if($hash == true) {
            $passwordHash =  md5('yuanxin'.md5($password));
        } else {
            $passwordHash =  $password;
        }
        $userData = [
            'username'        => $username,
            'password'        => $passwordHash,
            'type'            => $type,
            'mobile'          => $mobile,
            'email'           => $email,
            'nickname'        => urlencode($nickname),
            'avatar'          => $avatar,
            'mobile_verified' => $mobile_verified,
            'email_verified'  => $email_verified,
            'verify_code'  => $verify_code,
            'new_source'  => $newsource,
            'we_avatar'  => $weAvatar,
            'ip'  => $ip,
            'invite_by'  => $invite_by,
            'invited_nisiya_id'  => $invited_nisiya_id,
        ];
        $userData['realname']  = $realname;//varchar
        $userData['membercode']  = $membercode;//会员卡号
        $userData['birthday']  = $birthday;//出生日期
        $userData['gender']  = $gender;//0未知 1 男 2 女
        $userData['age']  = $age;//年龄
        $userData['idcard']  = $idcard;//身份证
        $userData['telephone']  = $telephone;//联系电话
        $userData['street']  = $street;//街道
        $userData['address']  = $address;//联系地址
        $userData['orgid']  = $orgid;//新erp药店编码
        $userData['height']  = $height;//身高cm
        $userData['weight']  = $weight;//体重kg
        $userData['waistline']  = $waistline;//腰围cm
        $userData['healthcare']  = $healthcare;//医保情况 0:否；1：有
        $userData['location']  = $location;//用户地区
        $source?$userData['source']=$source:null;
        return $this->send($userData, __METHOD__);
    }
    
}