<?php
/**
 * 科室SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class DepartmentSdk extends CommonSdk
{
    /**
     * 获取一级科室
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function first()
    {
        $params = [];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据一级科室获取二级科室
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $id
     * @return bool|mixed|string
     */
    public function second($id)
    {
        $params = [
            'id' => $id
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 获取科室结构树
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function all()
    {
        $params = [

        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 科室缓存更新(redis:set)
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
     * 获取所有数据，非树状结构
     * return array|bool|mixed|string
     * author niewei
     * @date 2022/9/15 15:09
     */
    public function allData()
    {
        $is_post = false;
        return $this->send($is_post, [], __METHOD__);
    }

    /**
     * 科室搜索
     * @param string $keyword 科室关键词
     * @param int $page 页数
     * @param int $type 默认0，搜索一二级科室，1搜索一级科室，2搜索二级科室
     * @param int $pageSize 每页数据量
     * @return array|bool|mixed|string
     */
    public function search($keyword, $type = 0, $page = 1, $pageSize = 10)
    {
        $params = [
            'keyword' => trim($keyword),
            'type' => intval($type),
            'page' => intval($page),
            'pageSize' => intval($pageSize)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }
}