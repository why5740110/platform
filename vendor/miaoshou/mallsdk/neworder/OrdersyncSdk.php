<?php
/**
 * Created by PhpStorm.
 * User: suxingwang
 * Date: 2020-06-17
 * Time: 10:45
 */

namespace nisiya\mallsdk\neworder;

use nisiya\mallsdk\CommonSdk;

class OrdersyncSdk extends CommonSdk
{

    /**
     * 处方状态同步接口
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018/12/7
     * @param $orderSn   订单号
     * @param $rxSnStatus 处方审批状态 1 审核通过 -1审核未通过
     * @param $rxSnComment 处方审核备注
     * @return bool|mixed
     */
    public function rxstatus($orderSn, $rxSnStatus, $rxSnComment){
        $params['order_sn']=$orderSn;
        $params['rx_sn_status']=$rxSnStatus;
        $params['rx_sn_comment']=$rxSnComment;
        return $this->send($params,__METHOD__,'post');

    }
}