<?php
/**
 * 症状SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class SymptomSdk extends CommonSdk
{
    /**
     * 获取所有症状信息
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $perpage
     * @return bool|mixed|string
     */
    public function list($perpage = '')
    {
        $params = [];
        if (!empty($perpage)) {
            $params['perpage'] = $perpage;
        }
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据ids批量获取症状详情
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $ids
     * @return bool|mixed|string
     */
    public function details($ids)
    {
        $params = [
            'ids' => $ids
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}