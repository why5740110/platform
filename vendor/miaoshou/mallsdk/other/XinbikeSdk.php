<?php
/**
 *
 * @file XinbikeSdk.php
 * @author wangliwei <wangliwei@yuanxin-inc.com>
 * @version 2.0
 * @date 2020/06/28
 */

namespace nisiya\mallsdk\other;

use nisiya\mallsdk\CommonSdk;

class XinbikeSdk extends CommonSdk
{
    /**
     * 保存申请信息
     * @param $params
     * xinbike_send_name 姓名
     * xinbike_send_code 身份证
     * xinbike_send_mobile 手机号
     * xinbike_send_province 省 商城编码
     * xinbike_send_city 市 商城编码
     * xinbike_send_area 县 商城编码
     * xinbike_send_address 详细地址
     * xinbike_send_invoice 发票图片 数组
     * xinbike_send_supervision_code 药品监管码图片 数组
     * xinbike_send_region 患病区域照片 数组
     * xinbike_send_phone_code 手机验证码 6位
     * @return bool|mixed
     */
    public function saveinfo($params)
    {
        return $this->send($params,__METHOD__,'post');
    }
}