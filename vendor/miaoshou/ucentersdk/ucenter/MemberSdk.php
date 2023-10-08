<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : CodeSdk.php
 * @author  : zhangyandong <xiezhibin@yuanxin-inc.com>
 * @date    : 2019-07-29
 */

namespace nisiya\ucentersdk\ucenter;

use nisiya\ucentersdk\CommonSdk;

class MemberSdk extends CommonSdk
{
    /**
     * 判断账号是否存在
     * @param $data 账号值
     * @param $type 账号类型 mobile email username
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 11:03
     * @return json
     */
    public function verification($data,$type='mobile')
    {
        $params = [
            'data' => $data,
            'type' => $type,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据手机号批量获取用户id
     * @param $phones 手机号列表
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 11:01
     * @return json
     */
    public function getuidsbyphone($phones)
    {
        $params = ['phones' => json_encode($phones)];
        return $this->send($params, __METHOD__);
    }

    /**
     * 更新members字段
     * @param  int  $uid  用户id
     * @param  array  $param  要修改的内容
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 11:09
     * @return json
     */
    public function updateparam($uid,$param)
    {
        $params = [
            'uid' => $uid,
            'param' => json_encode($param),
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取用户数据
     * @param  $uids 批量用户UID,数组
     * @param  $is_avatar   是否需要头像 1需要
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 10:56
     * @return json
     */
    public function getusers($uids,$is_avatar=0)
    {
        $uids = json_encode($uids);
        $params = [
            'uids' => $uids,
            'is_avatar' => $is_avatar,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 重置密码
     * @param $uid  用户id
     * @param $newpwd 新密码
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 10:48
     * @return json
     */
    public function resetpwd($uid,$newpwd)
    {
        $params = array(
            'newpwd' =>md5('yuanxin'.md5($newpwd)) ,
            'uid' => $uid,
        );
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取用户数据
     * @param  $type $type用户类型 username，mobile，uid,nisiya_id
     * $param  $str 值
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 10:55
     * @return json
     */
    public function getuser($str , $type) {
        $params = [
            'str'=>$str,
            'type' => $type
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 更新用户信息
     */
    public function updateinfo($uid,$gender=0,$age=0,$idcard='',$height=0,$weight=0,$waistline=0,$orgid='',$birthday=0,$healthcare=0,$mem_type=0,$realname='',$street='',$address='',$telephone='',$nickname='',$membercod='',$location=''){
        $data = [];
        $data['uid']  = $uid;//varchar
        $data['realname']  = $realname;//varchar
        $data['membercode']  = $membercode;//会员卡号
        $data['birthday']  = $birthday;//出生日期
        $data['gender']  = $gender;//0未知 1 男 2 女
        $data['age']  = $age;//年龄
        $data['idcard']  = $idcard;//身份证
        $data['telephone']  = $telephone;//联系电话
        $data['street']  = $street;//街道
        $data['address']  = $address;//联系地址
        $data['orgid']  = $orgid;//新erp药店编码
        $data['height']  = $height;//身高cm
        $data['weight']  = $weight;//体重kg
        $data['waistline']  = $waistline;//腰围cm
        $data['healthcare']  = $healthcare;//医保情况 0:否；1：有
        $data['mem_type']  = $mem_type;//会员类型0:患者；1：普通
        $data['location']  = $location;//用户地区
        return $this->send($data, __METHOD__,'post');
    }
}