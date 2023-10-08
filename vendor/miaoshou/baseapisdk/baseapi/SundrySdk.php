<?php
/**
 * 杂项配置SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class SundrySdk extends CommonSdk
{
    /**
     * 杂项配置
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function list($type_id)
    {
        $params = [
            'type_id' => $type_id
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 杂项配置更新缓存（redis:set）
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/4/20
     * @return array|bool|mixed|string
     */
    public function updatecache()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据四种类型杂项配置（num:用药频率 dose:剂量 way:服用方法 time:服用时间）
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/4/21
     * @return array|bool|mixed|string
     */
    public function appointlist()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

}