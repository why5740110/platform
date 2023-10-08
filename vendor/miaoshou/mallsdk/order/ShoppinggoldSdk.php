<?php
/**
 * 购物金SDK
 * @file ShoppinggoldSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-03-27
 */

namespace nisiya\mallsdk\order;

use nisiya\mallsdk\CommonSdk;

class ShoppinggoldSdk extends CommonSdk
{
    /**
     * 用户购物金日志列表
     * @Author wangchenxu  <wangchenxu@yuanxin-inc.com>
     * @Date   2019-03-27
     * @param  int   $user_id 用户id （必填）
     * @param  int   $shopping_gold_type 购物金类型（选填）-1：默认全部 0:收入 1：支出
     * @param  int   $shopping_gold_status 购物金状态（选填）1：已入账（默认） 0：未入账
     * @param  int   $page 页码（选填）
     * @param  int   $pagesize 每页显示的条数（选填）默认10条
     */
    public function pagelist($user_id, $shopping_gold_type, $shopping_gold_status, $page = 1, $pagesize = 10)
    {
        $params = [
            'user_id' => $user_id,
            'shopping_gold_type' => $shopping_gold_type,
            'shopping_gold_status' => $shopping_gold_status,
            'page' => $page,
            'pagesize' => $pagesize
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 微信用户 绑定手机号 迁移 购物金
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-10-22
     **/
    public function wxusershoppingupdate($oldUserId,$newUid,$newUseId,$newUserMobile,$newRealName,$wxNickName)
    {
        $params = [
            'old_user_id'     => $oldUserId,
            'new_uid'         => $newUid,
            'new_user_id'     => $newUseId,
            'new_user_mobile' => $newUserMobile,
            'new_real_name'   => $newRealName,
            'new_wx_nickname' => $wxNickName
        ];
        return $this->send($params, __METHOD__);
    }
}