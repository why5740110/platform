<?php
/**
 * 环信消息转发sdk
 */

namespace nisiya\mallsdk\message;

use nisiya\mallsdk\CommonSdk;

class MessageSdk extends CommonSdk
{
    /**转发消息到环信客服云
     * @param $message
     * @return bool|mixed
     * @date 2020-07-02
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function sendtohuanxin($message)
    {

        $params = [
            'message' => $message,
        ];
        return $this->send($params, __METHOD__,'post');
    }

    /**环信客服回复的消息发送至百度
     * @param $message
     * @return bool|mixed
     * @date 2020-07-02
     * @author renranran <renranran@yuanxin-inc.com>
     */
    public function huanxintobaidu($message)
    {
        $params = [
            'message' => $message,
        ];

        return $this->send($params, __METHOD__,'post');
    }
}
