<?php
/**
 * 疾病SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class DiseaseSdk extends CommonSdk
{
    /**
     * 获取所有疾病列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function list($page = 10, $perpage = 20)
    {
        $params = [];
        $params['page'] = $page;
        $params['perpage'] = $perpage;
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据ids批量获取疾病详情
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

    /**
     * 疾病搜索
     * @param string $disease_name 疾病关键词
     * @param int    $page 页数
     * @param int    $pageSize 每页数据量
     * @param int    $search_type 搜索类型，默认1， 1精确搜索，2模糊搜索
     * @return array|bool|mixed|string
     */
    public function search($disease_name, $page = 1, $pageSize = 10, $search_type = 1)
    {
        $params = [
            'disease_name' => trim($disease_name),
            'page' => intval($page),
            'pageSize' => intval($pageSize),
            'search_type' => intval($search_type)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据关键词匹配疾病数据
     * @param array $namesArr 关键词数组
     * @return array|bool|mixed|string
     */
    public function getDiseaseByNames($namesArr)
    {
        $params = [
            'disease_names' => json_encode($namesArr)
        ];

        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }
}