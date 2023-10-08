<?php
/**
 *
 * @file AccountSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-25
 */

namespace nisiya\mallsdk\user;


use nisiya\mallsdk\CommonSdk;

class AccountSdk extends CommonSdk
{
    /**
     * 通过用户中心uid创建商城用户
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $uid  用户中心
     * @param $reg_source  1 PC端 ，2 wap 端 ，3 药店 ，4 android ，5 ios ，7 微信注册 ，8 小程序注册 ，9 通过扫店铺二维码 ，7 通过扫点小程序吗
     * @param $nickname  昵称
     * @param $reg_store_id 药店序号
     * @return bool|mixed
     */
    public function create($uid, $reg_source, $nickname, $reg_store_id){

        $params = [
            'uid' => $uid,
            'reg_source' => $reg_source,
            'nickname' => $nickname,
            'reg_store_id' => $reg_store_id
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 通过用户中心uid获取商城用户id
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-07-26
     * @param $uid
     * @return bool|mixed
     */
    public function getuseridbyuid($uid){
        $params = [
            'uid' => $uid,
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 通过用户中心uid获取商城用户id
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-07-26
     * @param $uid
     * @return bool|mixed
     */
    public function getuseridbyuids($uid){
        $params = [
            'uid' => $uid,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 通过用户user_id获取用户中心uid
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-07-26
     * @param $uid
     * @return bool|mixed
     */
    public function getuidbyuserid($uid){
        $params = [
            'user_id' => $uid,
        ];
        return $this->send($params, __METHOD__);
    }
}