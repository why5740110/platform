<?php

namespace common\sdks;


use common\libs\AliVerification;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\libs\MiaoCrypt3Des;
use common\models\DoctorModel;
use common\models\GuahaoCooInterrogationModel;
use common\models\GuahaoOrderModel;
use common\sdks\snisiya\SnisiyaSdk;
use GuzzleHttp\Client;
use common\libs\Log;
use yii\helpers\ArrayHelper;

class AliGuahaoSdk
{
    protected static $_instance = null;

    public $domain = '';

    public $msg_id = '';

    public $log = [];
    /**
     *
     * @var string
     */
    public $key = '';


    /**
     * @var array 请求接口返回的结果
     */
    protected $result = [];

    public $baseParams = [];

    public function __construct()
    {

        $this->key = ArrayHelper::getValue(\Yii::$app->params, 'ali_healthy.key');
        $this->encryptKey = ArrayHelper::getValue(\Yii::$app->params, 'ali_healthy.encryptKey');
        $this->domain = ArrayHelper::getValue(\Yii::$app->params, 'ali_healthy.aliUrl');
    }

    /**
     * 单例
     * @return static
     * @author xiujianying
     * @date 2021/6/22
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    /**
     * 阿里预约订单回传数据接口
     * @param $order_sn
     * @return bool
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-14
     */
    public function pushOrderStatus($order_sn)
    {

        $log_start_time = microtime(true); // 记录进入方法开始时间
        if (empty($order_sn)) {
            return ["msg"=>"订单号不能为空:".strval($order_sn)];
        }
        $orderFind = GuahaoOrderModel::find()
            ->alias('ord')
            ->select([
                'tpr.tp_section_id','tpr.scheduling_id','tpr.tp_scheduling_id',
                'ord.order_sn','ord.hospital_name','ord.department_name','ord.doctor_id',
                'ord.doctor_name','ord.patient_name','ord.mobile','ord.card','ord.gender',
                'ord.visit_time','ord.create_time','ord.state','ord.tp_platform','ord.skey',
                'ord.patient_id','ord.uid'
            ])
            ->join('LEFT JOIN', ['tpr' => 'tb_guahao_order_info'], 'ord.id=tpr.order_id')
            ->where(['ord.order_sn'=>$order_sn])
            ->asArray()
            ->all();

        if (count($orderFind) ==1){
            $orderInfo = $orderFind[0];
        }else{
            return ["msg"=>"没有查询到相关订单信息,订单号:".strval($order_sn)];
        }

        $doctorIdNum = ArrayHelper::getValue($orderInfo, 'doctor_id');
        $paiBanParams = [
            "doctor_id"=>HashUrl::getIdEncode(strval($orderInfo['doctor_id'])),
            "tp_scheduling_id"=>strval($orderInfo['tp_scheduling_id']),
            "tp_platform"=>$orderInfo['tp_platform'],
        ];
        $scheduleInfo = SnisiyaSdk::getInstance()->guahao_paiban_info($paiBanParams);

        $infos = DoctorModel::getInfo($doctorIdNum);
        $doctorId = ArrayHelper::getValue($infos, 'doctor_id');

        $aliuserInfo = GuahaoCooInterrogationModel::find()->select('coo_user_id')
            ->where(['coo_platform'=>2,'uid'=>ArrayHelper::getValue($orderInfo, 'uid')])->one();

        if (empty($aliuserInfo)) {
            return ["msg"=>"没有查询到当前用户的第三方就诊人id:".strval($order_sn)];
        }
        $requestData['biz_content']['info'] = [];
        $params= [
            "appChannel"=> ArrayHelper::getValue($orderInfo, 'skey'),
            "registrationCode"=>ArrayHelper::getValue($orderInfo, 'order_sn'),
            "hosCode"=> HashUrl::getIdEncode(ArrayHelper::getValue($infos, 'hospital_id')),
            "hosName"=>ArrayHelper::getValue($orderInfo, 'hospital_name'),
            "deptCode"=>$scheduleInfo['schedule_info']['second_department_id'] ?? "",
            "deptName"=> ArrayHelper::getValue($orderInfo, 'department_name'),
            "doctorCode"=> $doctorId,
            "doctorName"=> ArrayHelper::getValue($orderInfo, 'doctor_name'),
            "alihealthUserId"=>$aliuserInfo->coo_user_id,
            "patientName"=>ArrayHelper::getValue($orderInfo, 'patient_name'),
            "patientMobile"=>ArrayHelper::getValue($orderInfo, 'mobile'),
            "patientIdCard"=> ArrayHelper::getValue($orderInfo, 'card'),
            "patientIdType"=>"01",
            "patientGender"=>$this->getPatientGender(ArrayHelper::getValue($orderInfo, 'gender')),
            "appointTime"=>ArrayHelper::getValue($orderInfo, 'visit_time'),
            "orderTime"=> date('Y-m-d H:i:s', ArrayHelper::getValue($orderInfo, 'create_time')),
            "status"=>$this->getOrderStatus(ArrayHelper::getValue($orderInfo, 'state'))
        ];
        array_push($requestData['biz_content']['info'], $params);

        $data = json_encode($requestData,JSON_UNESCAPED_UNICODE);

        $param['method'] = "alibaba.alihealth.medical.registration.syncnew";
        $param['app_key'] = $this->key;
        $param['v'] = '2.0';
        $param['timestamp'] = date("Y-m-d H:i:s",time());
        $param['partner_id'] = 'top-apitools';
        $param['sign_method'] = 'md5';
        $param['format'] = 'json';
        $param['save_request'] = $data;
        $aliver = new AliVerification();
        $param['sign'] = $aliver->makeSign($param);
        $str = http_build_query($param);
        $url = $this->domain."?".$str;
        $indexParams = [
          "order_sn" =>$order_sn,
          "doctorName" =>$params['doctorName'],
          "state" =>$params['status'],
        ];
        try {
            $resultData = $this->curl($url,$indexParams,$log_start_time,$param);
            $res = $resultData['alibaba_alihealth_medical_registration_syncnew_response']['result'];
            if($res['success'] == true) {
                return $res;
            }else{
                return $res;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *  请求阿里
     * @param $url
     * @param array $params
     * @param int $timeout
     * @return false|mixed
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-14
     */
    public function curl($url,$indexParams,$log_start_time,$params = [], $timeout = 10)
    {
        $this->log["prefix"] = "AliController";
        $this->log["action"] = 'pushOrderStatus';
        try {
            $client = new Client([
                'base_uri' => $url,
                'timeout' => $timeout,
            ]);
            $response = $client->get($url);

        } catch (\Throwable $e) {
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            $logData = [
                "curlAction" =>'----------------请求第三方接口----------------',
                //"request_type" =>'pushOrderStatus'."-".$indexParams['doctorName']."-".$indexParams['state'],
                "prefix"=>"AliController",
                "index"=> $indexParams['order_sn'],
                "platform" => "302",
                "curlUrl" => $url,
                'curlError' => ['code'=>$e->getCode(),'msg'=>$e->getMessage()],
                'error_msg'=>json_encode($e->getMessage(),JSON_UNESCAPED_UNICODE),
                'log_spend_time' => $log_spend_time,
                'log_code' => 500,
            ];
            $logData['cur_log']['log_code'] = 500;
            $logData['cur_log']['log_spend_time'] = $log_spend_time;
            \Yii::$app->params['DataToAliRequest'] = $logData;
            return false;
        }
        $this->result = $response->getBody()->getContents();
        $log_end_time = microtime(true);
        $log_spend_time = round($log_end_time - $log_start_time, 2);

        $logData = [
            "curlAction" =>'----------------请求第三方接口----------------',
            //"request_type" =>'pushOrderStatus'."-".$indexParams['doctorName']."-".$indexParams['state'],
            "prefix"=>"AliController",
            "index"=> $indexParams['order_sn'],
            "platform" => "302",
            "curlUrl" => $url,
            "curlParams" =>json_encode($params,JSON_UNESCAPED_UNICODE),
            'curlReturnData' => $this->result,
            'log_spend_time' => $log_spend_time,
            'log_code' => 200,
        ];
        $logData['cur_log']['log_spend_time'] = $log_spend_time;
        $logData['cur_log']['log_code'] = 200;
        \Yii::$app->params['DataToAliRequest'] = $logData;
        return json_decode($this->result, true);
    }
    /**
     *  转换性别
     * @param $gender
     * @return string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-03
     */
    public function getPatientGender($gender)
    {
        $genderData = 'UNKNOWN';
        $arr = [
            '1'=>"MALE",
            '2'=>"FEMALE",
        ];
        if (isset($arr[strval($gender)])) {
            $genderData =  $arr[strval($gender)];
        }
        return $genderData;
    }

    /**
     *  转换订单状态
     * @param $orderStatus
     * @return string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-03
     */
    public function getOrderStatus($orderStatus)
    {
        $arr = [
            '0'=>"SUCCESS",
            '1'=>"CANCELED",
            '2'=>"CANCELED",
            '3'=>"SUCCESS",
            '4'=>"SUCCESS",
            '5'=>"BESPEAK_WAITPAY",
            '6'=>"FAIL",
            '7'=>"SUCCESS",
            '8'=>"SUCCESS",
        ];
        return $arr[strval($orderStatus)];

    }
}