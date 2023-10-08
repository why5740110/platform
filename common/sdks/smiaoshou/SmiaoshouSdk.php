<?php
/**
 * s.接口相关
 * @file SnisiyaSdk.php.
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2020-07-30
 * @version 1.0
 */

namespace common\sdks\snisiya;

use common\sdks\BaseSdk;
use yii\helpers\ArrayHelper;

class SnisiyaSdk extends BaseSdk
{

    protected $domain = '';
    public function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['sapi'];
    }

    //路由配置
    private $maps = [
        'get_hospital_list'=>'/hospital/get-list',//医院查询
        'get_doctor_list'=>'/doctor/get-list',//、医生查询
        'get_doctor_info'=>'/doctor/get-hospital-doctor-detail',//医生详情
        'get_hospital_detail'=>'/hospital/get-detail',  //医院详情
        'hospital_department' => '/hospital/get-department', //医院下科室
        'department' => '/hospital/get-com-department', //常见科室
        'get_district' => '/hospital/get-district', //获取地区列表缓存
        'get_region_info' => '/hospital/get-region-info-by-pinyin', //获取地区详情根据拼音
        'get_keshi_info' => '/hospital/get-keshi-info', //获取科室详情根据id
        'search_list'       => '/hospital/search', //搜索结果列表
        'index'       => '/hospital/index', //首页
        'guahao_cancel' => '/guahao/cancel', //取消预约接口
        'guahao_confirm' => '/guahao/guahao',//提交确认预约
        'guahao_paiban' => '/guahao/get-paiban', //排班
        'guahao_detail' => '/guahao/get-order',//获取挂号订单详情
        'guahao_hospital' => '/guahao/get-hospital',//获取挂号平台相关医院
        'guahao_hospital_byid' => '/guahao/get-hospital-byid',//获取健康160附属信息
        'guahao_department' => '/guahao/get-department',//获取挂号相关科室
        'guahao_doctor' => '/guahao/get-doctor',//获取挂号相关医生
        'verify_person' => '/baidu-auth/verify-person',//百度实名认证

        'get_doctor_ids' => '/guahao/get-doctor-ids',//拉取医生ID接口
        'get_doctor_byid' => '/guahao/get-doctor-byid',//根据医生ID获取详细信息
        'getHospitalConfig' => '/guahao/getHospitalConfig',//根据医生ID获取详细信息

        'guahao_paiban_info' => '/guahao/get-paiban-info', //排班详情
        'pay_order' => '/guahao/pay-order', //订单支付接口 通知好大夫
        'order_refund' => '/guahao/order-refund',  //订单退款接口 通知好大夫
        'schedule_change' => '/guahao/schedule-change', //排班变更
        'get_department_paiban'=>'/guahao/get-department-paiban',//医院科室对应排班
        'update_schedule'=>'/guahao/update-schedule',//重新拉取排班
        'get_hoslist_by_department'=>'/hospital/get-list-by-department',//通过科室id获取医院列表
        'update_schedule_cache' => '/guahao/update-schedule-cache',//更新排班缓存
        'get_real_plus' => '/guahao/get-real-plus',//根据排班是否有号
        'get_paiban_status' => '/guahao/get-department-paiban-status',//查询科室30天排班状态
        'get_paiban_info' => '/guahao/get-department-paiban-info',//按日期、科室查询排班
        'get_time_config' => '/guahao/get-time-config',//获取时间配置
        'guahao_paiban_api' => '/guahao/get-paiban-api',//获取排班
        'get_tp_hospital_info' => '/hospital/get-tp-hospital-info',//获取第三方医院信息
        'get-hospital-close'    => '/guahao/get-hospital-close',//检测医院停诊

        'get_coo_list'    => '/platform/get-coo-list',//合作平台列表
        'get_platform_list'    => '/platform/get-platform-list',//第三方平台列表
        'guahao_paiban_agg_api' => '/guahao/get-paiban-agg-api',//获取聚合排班
        'get_doctor' => '/doctor/index',//通过科室id获取医生
        'config_department' => '/hospital/department', //获取权重配置科室
        'department_Doctor' => '/doctor/related', //同一家医院，获取推荐医生
    ];


    public function getDoctorIds($params = [])
    {
        $result = $this->curl($this->maps['get_doctor_ids'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    public function getDoctorByid($params = [])
    {
        $result = $this->curl($this->maps['get_doctor_byid'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 获取医院列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-30
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public  function getHospitalList($params = [])
    {
        $result = $this->curl($this->maps['get_hospital_list'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }


    public  function getDoctorList($params = [])
    {
        $result = $this->curl($this->maps['get_doctor_list'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    public  function getDoctorInfo($params = [])
    {
        $result = $this->curl($this->maps['get_doctor_info'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 医院详情
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/8/3
     */
    public  function getHospitalDetail($params = [])
    {
        $result = $this->curl($this->maps['get_hospital_detail'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 医院下科室
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/8/3
     */
    public  function hospital_department($params = [])
    {
        $result = $this->curl($this->maps['hospital_department'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 常见科室
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/8/3
     */
    public  function department($params = [])
    {
        $result = $this->curl($this->maps['department'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 获取地区列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-03
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getDistrict($params = [])
    {
        $result = $this->curl($this->maps['get_district'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 根据拼音获取地区信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-03
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getRegionInfo($params = [])
    {
        $result = $this->curl($this->maps['get_region_info'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取科室信息根据id
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-03
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getKeshiInfo($params = [])
    {
        $result = $this->curl($this->maps['get_keshi_info'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 搜索结果列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-10
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getSearchList($params = [])
    {
        $result = $this->curl($this->maps['search_list'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    public function getIndex($params = [])
    {
        $result = $this->curl($this->maps['index'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 排班
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/10/9
     */
    public function guahao_paiban($params = [])
    {
        $result = $this->curl($this->maps['guahao_paiban'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 取消预约接口
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/10/12
     */
    public function guahaoCancel($params = [])
    {
        $result = $this->curl($this->maps['guahao_cancel'] . '?' . http_build_query($params), [], 35);
        return $result;
    }

    /**
     *Notes:提交确认预约
     *User:lixiaolong
     *Date:2020/10/13
     *Time:14:46
     * @param array $params
     * @return mixed
     */
    public function guahaoConfirm($params = [])
    {
        $result = $this->curl($this->maps['guahao_confirm'] . '?' . http_build_query($params), [], 35);
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            $data = ['code'=>200,'data'=>ArrayHelper::getValue($result,'data')];
            return $data;
        }else{
            if (is_array($result) && ArrayHelper::getValue($result, 'code', '404') != 404) {
                $data = ['code' => 400, 'msg' => $result['msg'] ?? "预约失败,请稍后再试"];
                return $data;
            } else {
                return [];
            }

        }
    }

    /**
     *Notes:获取挂号订单详情
     *User:lixiaolong
     *Date:2020/10/23
     *Time:10:55
     * @param array $params
     */
    public function guahaoDetail($params = [])
    {
        $result = $this->curl($this->maps['guahao_detail'] . '?' . http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }   

    /**
     * 获取挂号医院根据平台
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-30
     * @version 1.0
     * @param   string     $value [description]
     */
    public function getGuahaoHospital($params = [])
    {
        $result = $this->curl($this->maps['guahao_hospital'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取健康160附属信息
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/1/25
     */
    public function getHospitalByid($params = []){
        $result = $this->curl($this->maps['guahao_hospital_byid'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取挂号平台科室
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-30
     * @version 1.0
     * @param   string     $value [description]
     */
    public function getGuahaoDepartment($params = [])
    {
        $result = $this->curl($this->maps['guahao_department'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取挂号平台医生
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-30
     * @version 1.0
     * @param   string     $value [description]
     */
    public function getGuahaoDoctor($params = [])
    {
        $result = $this->curl($this->maps['guahao_doctor'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }    


    /**
     * 调取实名认证接口
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-11-05
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function verifyPerson($params = [])
    {
        $result = $this->curl($this->maps['verify_person'].'?'.http_build_query($params));
        return $result;
    }


    /**
     *Notes:获取排班详情
     *User:lixiaolong
     *Date:2020/12/1
     *Time:10:25
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     */
    public function guahao_paiban_info($params = [])
    {
        $result = $this->curl($this->maps['guahao_paiban_info'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }



    /**
     * 订单支付接口 通知好大夫
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/2
     */
    public function pay_order($params = [])
    {
        $result = $this->curl($this->maps['pay_order'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 订单退款接口 通知好大夫
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/2
     */
    public function order_refund($params = [])
    {
        $result = $this->curl($this->maps['order_refund'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 排班变更
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/2
     */
    public function scheduleChange($params = [])
    {
        unset($params['time']);
        $result = $this->curl($this->maps['schedule_change'], array_filter($params));
        return $result;
    }

    /**
     * 重新拉取排班
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/24
     */
    public function updateSchedule($params = [])
    {
        $result = $this->curl($this->maps['update_schedule'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     *Notes:获取医院下科室对应排班
     *User:lixiaolong
     *Date:2020/12/7
     *Time:15:09
     * @param array $params
     */
    public function getDepartmentPaiban($params = [])
    {
        $result = $this->curl($this->maps['get_department_paiban'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }
    
    public function getListByDepartment($params = [])
    {
        $result = $this->curl($this->maps['get_hoslist_by_department'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取医院配置信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @param   string     $value [description]
     * @return  [type]            [description]
     */
    public function getHospitalConfig($params = [])
    {
        $result = $this->curl($this->maps['getHospitalConfig'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 更新排班缓存
     * @param array $params
     * @return false|mixed
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/2/9
     */
    public function updateScheduleCache($params = [])
    {
        sleep(1);##暂停一秒
        $result = $this->curl($this->maps['update_schedule_cache'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $params
     * @return int
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/4/20
     */
    public function getRealPlus($params = [])
    {
        $result = $this->curl($this->maps['get_real_plus'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data.real_plus', 0);
        }
        return 0;
    }

    /**
     * 查询医院、科室排班状态
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-27
     * @version v1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getPaibanStatus($params = [])
    {
        $result = $this->curl($this->maps['get_paiban_status'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 查询科室排班详情
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-27
     * @version v1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getPaibanInfo($params = [])
    {
        $result = $this->curl($this->maps['get_paiban_info'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 获取第三方时间配置
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/5/8
     */
    public function getTimeConfig($params = [])
    {
        $result = $this->curl($this->maps['get_time_config'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 获取es排班
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/23
     */
    public function getPaibanApi($params = [])
    {
        $result = $this->curl($this->maps['guahao_paiban_api'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 挂号
     * @param array $params
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/23
     */
    public function guahao($params = [])
    {
        $result = $this->curl($this->maps['guahao_confirm'] . '?' . http_build_query($params), [], 35);
        return $result;
    }

    /**
     * 获取第三方医院缓存
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/7/28
     */
    public function getTpHospitalInfo($params = [])
    {
        $result = $this->curl($this->maps['get_tp_hospital_info'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 检测第三方医院停诊信息
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author wanghongying <wanghongying@yuanxin-inc.com>
     * @date 2021/10/20
     */
    public function getTpHospitalClose($params = [])
    {
        $result = $this->curl($this->maps['get-hospital-close'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 合作平台列表
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/9
     */
    public function getCooList($params = [])
    {
        $result = $this->curl($this->maps['get_coo_list'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 第三方平台列表
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/11
     */
    public function getPlatformList($params = [])
    {
        $result = $this->curl($this->maps['get_platform_list'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     *  对外输出排班数据， 格式特殊
     * @param array $params
     * @return array|mixed|null
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-25
     */
    public function getPaibanAggApi($params = [])
    {
        $result = $this->curl($this->maps['guahao_paiban_agg_api'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 通过科室id获取医生
     * @param array $params
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-08-02
     */
    public function getDoctor($params = [])
    {
        $result = $this->curl($this->maps['get_doctor'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 获取权重配置科室
     * @return array|mixed|null
     * @throws \Exception
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-08-02
     */
    public function configDepartment()
    {
        $result = $this->curl($this->maps['config_department']);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 同一家医院，获取推荐医生
     * @return array|mixed|null
     * @throws \Exception
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-08-03
     */
    public function getDepartmentDoctor($params = [])
    {
        $result = $this->curl($this->maps['department_Doctor']. '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

}