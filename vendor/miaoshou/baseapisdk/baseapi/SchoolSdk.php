<?php
/**
 * 学校、专业SDK
 */
namespace nisiya\baseapisdk\baseapi;

use nisiya\baseapisdk\CommonSdk;

class SchoolSdk extends CommonSdk
{
    /**
     * 根据学校关键词like查询学校
     * @param string $school_name 学校关键词
     * @return array|bool|mixed|string
     */
    public function getSchoolByName($school_name)
    {
        $params = [
            'school_name' => trim($school_name)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据专业名称查询专业关键词
     * @param string $professional_name 专业关键词
     * @return array|bool|mixed|string
     */
    public function getProfessionalByName($professional_name)
    {
        $params = [
            'professional_name' => trim($professional_name)
        ];
        $is_post = false;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据学校ID获取学校详情
     * @param int $id 学校ID
     * @return array|bool|mixed|string
     */
    public function getSchoolDetail($id)
    {
        $params = ['id' => $id];
        $is_post = false;

        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据学校ID批量获取学校详情
     * @param int $id 学校ID
     * @return array|bool|mixed|string
     */
    public function getSchoolDetailByIds($ids)
    {
        $params = ['ids' => json_encode($ids)];
        $is_post = false;

        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据专业ID获取专业详情
     * @param int $id 专业ID
     * @return array|bool|mixed|string
     */
    public function getProfessionalDetail($id)
    {
        $params = ['id' => $id];
        $is_post = false;

        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 根据专业ID批量获取专业详情
     * @param array $ids 专业ID
     * @return array|bool|mixed|string
     */
    public function getProfessionalDetailByIds($ids)
    {
        $params = ['ids' => json_encode($ids)];
        $is_post = false;

        return $this->send($is_post, $params, __METHOD__);
    }
}