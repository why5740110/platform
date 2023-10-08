<?php
namespace common\sdks\ucenter;
use common\sdks\IhsBaseSdk;
use yii\helpers\ArrayHelper;

/**
 * Class PihsSDK
 * @package common\sdks\ucenter
 */
class PihsSDK extends IhsBaseSdk
{

    //路由配置
    private $maps = [
        'registration'=>'v1/miaohealth/registration',
        'plus_list' => 'v1/visit/registerdoctor',
        'patient_list' => 'v1/interrogation/list',//获取问诊人列表
        'register_save' => 'v1/interrogation/edit',//修改添加问诊人
        'register_info' => 'v1/interrogation/new-detail',//获取问诊人详情
        'recent_patient' => 'v1/interrogation/recent',//获取最近添加的问诊人
        'doctor_scheduleplace' => 'v1/visit/doctor-hospital',//获取医生以及医生的出诊机构
        'patient_detail_and_add' => 'v1/interrogation/detail-and-add',//问诊人信息
    ];

    protected function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['pihsapi'];

        $this->appid = '1000000201';
        $this->appkey = 'B9D2A3AB8864445A5AD73BBDF8E10FB7';
        $this->baseParams = [
            'appid' => $this->appid,
            'os' => 'hospital',
            'time' => time(),
            'version' => $this->version,
            'noncestr' => $this->getRandChar(6),
        ];

    }

    /**
     * 挂号服务
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/8/19
     */
    public function getRegist($params)
    {
        $result = $this->curl($this->maps['registration']. '?' . http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }



    /**
     * 获取加号列表
     * @param $params
     * @return array|mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/9/7
     */
    public function plus_list($params)
    {
        $result = $this->curl($this->maps['plus_list']. '?' . http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     *Notes:获取问诊人列表
     *User:lixiaolong
     *Date:2020/10/10
     *Time:17:41
     * @param $params
     * user_id 用户id
     * @return array|mixed
     * @throws \Exception
     */
    public function actionPatientList($params)
    {
        $result = $this->curl($this->maps['patient_list']. '?' . http_build_query($params),array_merge($this->baseParams,$params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     *Notes:获取最近添加的问诊人
     *User:lixiaolong
     *Date:2020/10/13
     *Time:10:22
     */
    public function actionRecentPatient($params)
    {
        $result = $this->curl($this->maps['recent_patient']. '?' . http_build_query($params),array_merge($this->baseParams,$params));

        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     *Notes:获取问诊人详细信息
     *User:lixiaolong
     *Date:2020/10/12
     *Time:14:18
     */
    public function actionGetPatienInfo($params)
    {
        $result = $this->curl($this->maps['register_info']. '?' . http_build_query($params),array_merge($this->baseParams,$params));

        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     *Notes:就诊人信息添加/修改
     *User:lixiaolong
     *Date:2020/10/12
     *Time:11:11
     * @param $params
     * @return array|mixed
     * @throws \Exception
     */
    public function actionRegisterSave($params)
    {
        $result = $this->curl($this->maps['register_save']. '?' . http_build_query($params),array_merge($this->baseParams,$params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            $data = ['code'=>'success','msg'=>$result['msg'],'data'=>$result['data']];
            return $data;
        }else{
            $data = ['code'=>'faild','msg'=>$result['msg']];
            return $data;
        }
    }

    /**
     * 获取医生以及医生所在的出诊机构
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-23
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getDoctorScheduleplace($params = [])
    {
        $result = $this->curl($this->maps['doctor_scheduleplace']. '?' . http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取患者信息，有则返回没有创建
     * @param $params
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/25
     */
    public function interrogationDetailAndAdd($params)
    {
        $result = $this->curl($this->maps['patient_detail_and_add'] . '?' . http_build_query($params), array_merge($this->baseParams, $params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            $data = ['code' => 'success', 'msg' => $result['msg'], 'data' => $result['data']];
            return $data;
        } else {
            $data = ['code' => 'faild', 'msg' => $result['msg']];
            return $data;
        }
    }

}

