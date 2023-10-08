<?php
/**
 *
 * @author wangliangliang <wangliangliang@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-12-02
 */

namespace nisiya\mallsdk\order;

use nisiya\mallsdk\CommonSdk;

class RecipelorderSdk extends CommonSdk
{
    /**
     * 关闭处方单
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019-12-02
     *
     */
    public function close($params)
    {
        return $this->send($params, __METHOD__, 'post');
    }

    /**
     * 通知医生已接单
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019-12-02
     *
     */
    public function receive($params)
    {
        return $this->send($params, __METHOD__, 'post');
    }

    /**
     * 通知医生已接单
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019-12-02
     *
     */
    public function reply($params)
    {
        return $this->send($params, __METHOD__, 'post');
    }

    /**
     * 获取处方单信息
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019-12-02
     *
     */
    public function getrecipelorder($orderSn)
    {
        $params = [
            'order_sn' => $orderSn
        ];
        return $this->send($params, __METHOD__, 'get');
    }

    /**
     * 处理处方消息
     * @author wangliangliang <wangliangliang@yuanxin-inc.com>
     * @version 2.0
     * @date 2019-12-02
     *
     */
    public function processreply($orderSn)
    {
        $params = [
            'order_sn' => $orderSn
        ];
        return $this->send($params, __METHOD__, 'post');
    }
}