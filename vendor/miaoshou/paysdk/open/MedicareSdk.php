<?php
/**
 * Created by PhpStorm.
 * @file MedicareSdk.php
 * @author zhouhaifeng <zhouhaifeng@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-03-07
 */


namespace nisiya\paysdk\open;

use nisiya\paysdk\CommonSdk;

class MedicareSdk extends CommonSdk
{
    /**
     * 获取微信医保卡信息
     * @param $params
     * @return bool|mixed
     * @author zhouhaifeng <zhouhaifeng@yuanxinjituan.com>
     * @date 2022-03-07
     */
    public function wechatmedicarecardinfo($params)
    {
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取绑卡链接
     * @param $params
     * @return bool|mixed
     * @author zhouhaifeng <zhouhaifeng@yuanxinjituan.com>
     * @date 2022-03-07
     */
    public function wechatmedicarebindurl($params)
    {
        return $this->send($params,__METHOD__);
    }

}