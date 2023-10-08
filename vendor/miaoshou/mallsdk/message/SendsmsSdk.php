<?php
/**
 *
 * @file SendmsmsSdk.php
 * @author wangliwei <wangliwei@yuanxin-inc.com>
 * @version 2.0
 * @date 2020/06/28
 */

namespace nisiya\mallsdk\message;

use admin\models\CommonModel;
use nisiya\mallsdk\CommonSdk;

class SendsmsSdk extends CommonSdk
{
    /**
     * 发送验证码
     * @param $mobile  手机号
     * @param $captchaCode 验证码
     * @param $captchaType  验证码使用类型 例（1.欣比克赠药）
     * @return bool|mixed
     */
    public function sendcode($mobile,$captchaCode,$captchaType)
    {
        $params = [
            'user_mobile' => $mobile,
            'captcha_code' => $captchaCode,
            'captcha_type' => $captchaType,
        ];
        return $this->send($params,__METHOD__);
    }
}