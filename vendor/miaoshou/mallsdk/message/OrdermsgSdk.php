<?php
/**
 * 消息模板SKD
 * @file OrdermsgSdk.php
 * @author zhibin <xiezhibin@yuanxin-inc.com>
 * @version 2.0
 * @date 2017-12-29
 */

namespace nisiya\mallsdk\message;

use nisiya\mallsdk\CommonSdk;

class OrdermsgSdk extends CommonSdk
{
    
     /**
     * 微信模板消息接口
     * @param int $order_action 模板消息类型
     * @param int $order_sn 订单号
     * @param string|array $send_to 发送模板消息用户类型 agent|user|doctor|all
     * @param int $msg_type 底部文案类型
     * @param string $keyword1 自定义消息1
     * @param string $keyword2 自定义消息2
     * @date 2018-08-22
    **/
    // public function dosend($params=[])
    // {

    //    $params = [
    //         'order_action' => isset($params['order_action']) ? intval($params['order_action']) : 0,
    //         'order_sn' => isset($params['order_sn']) ? trim($params['order_sn']) : '',
    //         'send_to' => isset($params['send_to']) && is_array($params['send_to']) ? implode(',', $params['send_to']) : $params['send_to'],
    //         'msg_type' => isset($params['msg_type']) ? intval($msg_type) : 0,
    //         'keyword1' => isset($params['keyword1']) && !empty($params['keyword1']) ? addslashes(htmlspecialchars($params['keyword1'])) : '',
    //         'keyword2' => isset($params['keyword1']) && !empty($params['keyword1']) ? addslashes(htmlspecialchars($params['keyword2'])) : '',
    //     ];

    //     return $this->send($params, __METHOD__);
    // }
    public function dosend($order_sn, $order_action, $send_to, $is_sms = 0)
    {

        $params = [
            'order_sn' => $order_sn,
            'order_action' => $order_action,
            'send_to' => $send_to,
            'is_sms' => $is_sms,
        ];

        return $this->send($params, __METHOD__);
    }

    public function _send($params)
    {
        $_params = [
            'order_action' => isset($params['order_action']) ? intval($params['order_action']) : 0,
            'order_sn' => isset($params['order_sn']) ? trim($params['order_sn']) : '',
            'send_to' => isset($params['send_to']) && is_array($params['send_to']) ? implode(',', $params['send_to']) : $params['send_to'],
            'msg_type' => isset($params['msg_type']) ? intval($msg_type) : 0,
            'keyword1' => isset($params['keyword1']) && !empty($params['keyword1']) ? addslashes(htmlspecialchars($params['keyword1'])) : '',
            'keyword2' => isset($params['keyword1']) && !empty($params['keyword1']) ? addslashes(htmlspecialchars($params['keyword2'])) : '',
        ];
        return $this->send($params, 'nisiya\mallsdk\message\OrdermsgSdk::send');
    }

}