<?php
/**
 * 疾病编码sdk
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class DiseasecodeSdk extends CommonSdk
{
    /**
     * 获取疾病代码管理列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/8/27
     * @param $perpage
     * @return array|bool|mixed|string
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
     * es高亮搜索疾病名称（疾病代码管理）
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/8/27
     * @param $disease_name
     * @param $color
     * @return array|bool|mixed|string
     */
    public function search($params)
    {
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据别名搜索诊断数据
     * @param array $params
     * @param string $disease_name 诊断关键词
     * @param int $page 页数
     * @param int $pageSize 每页数据条数
     * @param string  $color 高亮颜色
     * @param string $tag 标签，默认为空，阿里蚂蚁(mayi)
     * @return array|bool|mixed|string
     */
    public function searchAlias($disease_name, $page = 1, $pageSize = 20, $color = 'blue', $tag = '')
    {
        $params = [
            'disease_name' => $disease_name,
            'page' => $page,
            'pageSize' => $pageSize,
            'color' => $color,
            'tag' => $tag
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 添加疾病诊断别名
     * @param int $id 疾病诊断ID
     * @param string $disease_name_alias 疾病诊断别名
     * @return array|bool|mixed|string
     */
    public function addAlias($id, $disease_name_alias)
    {
        $params = [
            'id' => $id,
            'disease_name_alias' => $disease_name_alias
        ];
        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 删除疾病诊断别名
     * @param int $id 疾病诊断ID
     * @param string $disease_name_alias 疾病诊断别名
     * @return array|bool|mixed|string
     */
    public function deleteAlias($id, $disease_name_alias)
    {
        $params = [
            'id' => $id,
            'disease_name_alias' => $disease_name_alias
        ];
        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据疾病诊断名称判断疾病诊断是否存在
     * @param string $disease_name 疾病诊断名称
     * @return array|bool|mixed|string
     */
    public function isExistsByDiseaseName($disease_name)
    {
        $params = [
            'disease_name' => trim($disease_name)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据诊断ID获取诊断详情
     * @param int $id 诊断ID
     * @return array|bool|mixed|string
     */
    public function getDetailById($id)
    {
        $params = [
            'id' => intval($id)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据诊断ID批量获取诊断详情
     * @param array $ids 诊断ID
     * @return array|bool|mixed|string
     */
    public function getDetailByIds($ids)
    {
        $params = [
            'ids' => json_encode($ids)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 更新疾病编码数据
     * @param int $id 主键
     * @param array $fields 更新数据,字段=>字段值
     * @return array|bool|mixed|string
     */
    public function update($id, $fields)
    {
        $params = [
            'id' => $id,
            'fields' => json_encode($fields)
        ];
        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }
}