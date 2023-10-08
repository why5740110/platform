<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : UserSdk.php
 * @author  : zhibin <xiezhibin@yuanxin-inc.com>
 * @date    : 2017-12-28
 */

namespace nisiya\mallsdk\user;

use nisiya\mallsdk\CommonSdk;

class UserSdk extends CommonSdk
{
    public function userinfo($user_id)
    {

        $params = ['user_id' => $user_id];
        return $this->send($params, __METHOD__);
    }

    public function userappeal($product_id,$mobile,$product_name='',$store_id=17,$user_name='')
    {
	    $params = [
	        'product_id'=>$product_id,
	        'mobile'=>$mobile,
	        'product_name'=>$product_name,
	        'store_id'=>$store_id,
	        'user_name' => $user_name,
	    ];

	    return $this->send($params, __METHOD__);
    }

       public function userinfobyuid($uid)
    {
        $params = ['uid' => $uid];
        //print_R($params);die;
        return $this->send($params, __METHOD__);
    }

    public function createUser($uid,$reg_source='mall',$wx_nickname='',$reg_store_id=0)
    {

        $params = ['uid' => $uid,'reg_source'=>$reg_source,'wx_nickname'=>$wx_nickname,'reg_store_id',$reg_store_id];
        return $this->send($params, __METHOD__);
    }

    /**
    微信用户 绑定手机号 以后转移用户信息
     */
    public function updatewxuser($oldUserId,$userId)
    {
        $params = ['oldUserId' => $oldUserId,'userId'=>$userId];
        return $this->send($params, __METHOD__);
    }
    
}