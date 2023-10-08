<?php
/**
 * 医院SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class HospitalSdk extends CommonSdk
{
    /**
     * 获取医院列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function list()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据医院ID获取详情
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $id
     * @return bool|mixed|string
     */
    public function detail($id)
    {
        $params = [
            'id' => $id
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 医院es搜索接口
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/8/27
     * @param $params
     * @return array|bool|mixed|string
     */
    public function search($params)
    {
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据疾病名称，获取医院列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/8/27
     * @param $params
     * @return array|bool|mixed|string
     */
    public function getListByDisease($params)
    {
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据医院ID批量获取医院详情数据-ES数据
     * @param array $ids 医院id数组
     * @return array|bool|mixed|string
     */
    public function getDetailByIds($ids)
    {
        $params = [
            'ids' => json_encode(array_filter($ids))
        ];
        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }
}