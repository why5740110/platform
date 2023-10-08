<?php
/**
 * @project : api.ucenter.nisiya.top
 * @file    : DappdoctorSdk.php
 * @author  : zhangyandong <xiezhibin@yuanxin-inc.com>
 * @date    : 2019-07-29
 */


namespace nisiya\ucentersdk\doctor;

use nisiya\ucentersdk\CommonSdk;

class DappdoctorSdk extends CommonSdk
{
    /**
     * 医生详细信息
     * @param $doctor_id
     * @param int $type int 详情还是简介 1 简介 2详情 3 103后台使用 4 5 品牌医生使用
     * @param int $build 0 否 1新建缓存
     * @param int $cache 0 不读缓存 1 读缓存
     * @param int $es  0 否 1 更新es
     * @param int $esinit 0 否 1 初始化es 新建elasticsearch index 后充实数据
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/8/1 17:21
     * @return bool|mixed
     */
    public function info($doctor_id,$type = 0,$build=0,$cache = 0,$es=0,$esinit =0)
    {
        $params = [
           'doctor_id' => $doctor_id,
            'type'  => $type,
            'build' => $build,
            'cache' => $cache,
            'es' => $es,
            'esinit' => $esinit,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 批量获取医生的简介
     * @param array $doctorIds 医生ID数组
     * @param int $type 0 1 2 3 103后台使用
     * @param int $cache 是否读取缓存
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 15:03
     * @return array
     */
    public function infos( $doctorIds,$type=0, $cache=1){
        $params = [
            'doctor_ids' => json_encode($doctorIds),
            'type'  => $type,
            'cache' => $cache
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据医生的ID获取医生的 doctor_info 表的信息
     * @param $doctor_id
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 14:57
     * @return array
     */
    public function infotable($doctor_id)
    {
        $params = [
            'doctor_id' => $doctor_id,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据医生的ID获取医生的 doctor_user 表的信息
     * @param $doctor_id
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 14:57
     * @return array
     */
    public function usertable($doctor_id)
    {
        $params = [
            'doctor_id' => $doctor_id,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取医生风采照片
     * @param $doctorId int 医生id
     * @param int $page  int 页码
     * @param int $pagesize int 每页条数
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 15:57
     * @return array
     */
    public function doctordemeanor($doctorId,$page=1,$pagesize = 20){
        $params = [
            'doctor_id' => $doctorId,
            'page' => $page,
            'pagesize' => $pagesize,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取互联网医院医生信息
     * @param $doctorId
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 16:00
     * @return array
     */
    public function internetdoctor($doctorId){
        $params = [
            'doctor_id' => $doctorId,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据医生的姓名获取医生的id列表
     * @param $doctor_name 医生名称
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 16:04
     * @return array
     */
    public function getdoctorid($doctor_name){
        $params = [
            'doctor_name' => $doctor_name,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据邀请码获取医生总数
     * @param $invitecode
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 16:10
     * @return array
     */
    public function getdoctorbycode($invitecode){
        $params = [
            'invitecode' => $invitecode,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 根据王氏id获取医生信息
     * @param $nisiya_id int 邀请码
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 16:13
     * @return array
     */
    public function nisiyaid($nisiya_id){
        $params = [
            'nisiya_id' => $nisiya_id,
        ];
        return $this->send($params, __METHOD__);
    }
    /**
     * 更新医生信息
     * @param array $params 要修改的信息
     * @param string $key 条件
     * @param string $value 条件值
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 17:02
     * @return array
     */
    public function updateby($params, $key,$value)
    {
        $data = [
            'params'=>json_encode($params),
            'key'=>$key,
            'value'=>$value
        ];
        return $this->send($data, __METHOD__);
    }

    /**
     * 获取医生列表
     * @param int $page
     * @param int $limit
     * @param string $order
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 16:50
     * @return array
     */
    public function getalldoctor($page = 1, $limit = 10,$order='lastvisit'){
        $params = [
            'page'=>$page,
            'pagesize'=>$limit,
            'order'=>$order
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 更新互联网医院医生信息
     * @param $doctor_id
     * @param $params
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 16:54
     * @return array
     */
    public function updateinternetdoctor($doctor_id, $params){
        $param = [
            'params' => json_decode($params),
            'doctor_id' =>$doctor_id
        ];
        return $this->send($param, __METHOD__);
    }

    /**
     * 注册医生
     * @param array $user_arr 医生主要信息
     * @param array $info_arr 医生额外信息
     * @author zhangyandong <zhangyandong@yuanxin-inc.com>
     * @date 2019/7/29 17:02
     * @return array
     */
    public function insertdoctor($user_arr, $info_arr)
    {
        $params = [
            'params_user' => json_encode($user_arr),
            'params_info' => json_encode($info_arr)
        ];
        return $this->send($params, __METHOD__);
    }
}