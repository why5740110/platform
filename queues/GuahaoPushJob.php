<?php
/**
 * 处理挂号业务异步
 * @file GuahaoPushJob.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/19
 */


namespace queues;


use common\libs\HashUrl;
use common\libs\Log;
use common\models\BaseDoctorHospitals;
use common\models\DoctorModel;
use common\models\GuahaoCooModel;
use common\models\GuahaoOrderModel;
use common\models\GuahaoPlatformModel;
use common\models\GuahaoPlatformRelationHospitalModel;
use common\sdks\BaiduGuahaoSdk;
use common\sdks\AliGuahaoSdk;
use common\sdks\KedaGuahaoSdk;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;

class GuahaoPushJob extends \yii\base\BaseObject  implements JobInterface
{
    /**
     * 业务id
     * @var
     */
    public $id;

    /**
     * 类型  1：医生  2：订单  3：号源
     * @var
     */
    public $type;

    /**
     * 操作  type=1 (add:新增医生  edit:医生修改  del:禁用/删除)
     *      type=2 (1:完成 2:取消 )
     * @var
     */
    public $action;

    /**
     * 失败尝试次数
     * @var int
     */
    public $time=0;

    /**
     * 最多尝试次数
     * @var int
     */
    public $maxTimes = 5;

    const COO_FAIL_CODE = 4001;

    public function execute($queue)
    {
        $platform = "301";
        $request = "DataToBaiDuRequest";
        $return = [];
        switch ($this->type){
            case 1:
                $return = $this->doctor();
                break;
            case 2:
                $platformArr = [1=>"301",2=>"302", 3 => "303"];
                $requestArr = [1=>"DataToBaiDuRequest", 2=>"DataToAliRequest", 3=>"DataToKedaRequest"];
                $orderInfo = GuahaoOrderModel::find()->where(['id' => $this->id])->select('coo_platform')->asArray()->one();
                $platform = $platformArr[(int)$orderInfo['coo_platform']];
                $request = $requestArr[(int)$orderInfo['coo_platform']];
                $return = $this->order();
                break;
            case 3:
                $return = $this->schedule();
                break;
        }

        $str = '';
        if ($return['code'] != self::COO_FAIL_CODE) {
            //记录日志
            $new_job_id = Log::pushLogDataToQueues([
                'platform'=>$platform,
                'index'=> (string)$this->id,
                'request_type'=>(string)$this->type,
                'res'=>$return,
                'cur_log' => ArrayHelper::getValue(\Yii::$app->params,$request),
            ], 'logqueue2');
            $str .= 'job:'.$new_job_id;
        }
        echo $str.'--'.$this->id.'--'.$this->type.'res:'.json_encode($return, 256)."\n";
    }

    /**
     * 医生变更 推送给对外合作第三方
     * @return array
     * @author xiujianying
     * @date 2021/6/22
     */
    public function doctor(){
        try{
            $res = [];
            $docInfo = DoctorModel::find()->where(['doctor_real_plus' => 2, 'doctor_id' => $this->id, 'hospital_type' => 1, 'status' => 1])->select('tp_platform,tp_hospital_code')->asArray()->one();
            $tp_platform = ArrayHelper::getValue($docInfo, 'tp_platform');
            $tp_hospital_code = ArrayHelper::getValue($docInfo, 'tp_hospital_code');
            if (!$tp_platform || !$tp_hospital_code) {
                throw new \Exception('医生不存在或不在合作范围');
            }
            $relationWhere = [];
            $relationWhere['tp_platform'] = $tp_platform;
            $relationWhere['tp_hospital_code'] = $tp_hospital_code;
            $relationWhere['status'] = 1;

            $cooArr = GuahaoPlatformRelationHospitalModel::getCooList($relationWhere);
            if ($cooArr) {
                $flag = false;
                foreach($cooArr as $v){
                    if($v==1){ //百度合作
                        $flag = true;
                        $sdk = BaiduGuahaoSdk::getInstance();
                        $res = $sdk->pushDoctor($this->id,$this->action);
                    }
                }
                if(!$flag){
                    return ['code' => self::COO_FAIL_CODE, 'res' => '无coo合作方'];
                    //throw new \Exception('无coo合作方!');
                }
            }else{
                return ['code' => self::COO_FAIL_CODE, 'res' => '无coo合作方'];
                //throw new \Exception('无coo合作方');
            }

            $msg = $this->id . '--res:' . json_encode($res);
            return ['code' => 1, 'res' => $msg];
        }catch (\Exception $e){
            \Yii::$app->params['DataToBaiDuRequest']['log_code'] = 400;
            return ['code'=>0,'msg'=>$e->getMessage()];
        }

    }

    /**
     * 订单状态变更
     * @return array
     * @author xiujianying
     * @date 2021/6/24
     */
    public function order()
    {
        try {
            $res = [];
            $orderInfo = GuahaoOrderModel::find()->where(['id' => $this->id])->select('tp_platform,order_sn,coo_platform,state')->asArray()->one();
            if (!$orderInfo) {
                throw new \Exception('订单不存在');
            }
            $tp_platform = ArrayHelper::getValue($orderInfo, 'tp_platform');
            $order_sn = ArrayHelper::getValue($orderInfo, 'order_sn');

            $cooArr = GuahaoPlatformModel::getCoo($tp_platform);
            $cooArr = array_unique($cooArr);
            if ($cooArr && $order_sn) {
                $flag = false;
                foreach ($cooArr as $v) {
                    if ($v == 1 && $orderInfo['coo_platform']==1) { //百度合作
                        $flag = true;
                        $action = '';
                        if ($this->action == 1) {
                            $action = 'COMPLETE';
                        } elseif ($this->action == 2) {
                            $action = 'TPCANCELRESULT';
                        } elseif ($this->action == 3) {
                            $action = 'WAITAPPOINT';
                        }
                        $res = BaiduGuahaoSdk::getInstance()->pushOrderStatus($order_sn, $action);
                    }
                    // 阿里合作 预约记录状态(0:下单成功 1:取消 2:停诊 3:已完成 4:爽约 5:待支付,8:待审核)
                    if ($v == 2 && $orderInfo['coo_platform']== 2 and in_array($orderInfo['state'], [0, 1, 2, 3, 4, 5, 8])) {
                        $flag = true;
                        $res = AliGuahaoSdk::getInstance()->pushOrderStatus($order_sn);
                    }
                    //科大讯飞合作
                    if ($v == 3 && $orderInfo['coo_platform']== 3) {
                        $flag = true;
                        $res = KedaGuahaoSdk::getInstance()->pushOrderStatus($order_sn);
                    }
                }
                if (!$flag) {
                    return ['code' => self::COO_FAIL_CODE, 'res' => '无coo合作方'];
                    //throw new \Exception('无coo合作方!');
                }
            } else {
                return ['code' => self::COO_FAIL_CODE, 'res' => '无coo合作方'];
                //throw new \Exception('无coo合作方');
            }

            $msg = $this->id . '--res:' . json_encode($res);
            return ['code' => 1, 'res' => $msg];
        } catch (\Exception $e) {
            \Yii::$app->params['DataToBaiDuRequest']['log_code'] = 400;
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 排班变更
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/26
     */
    public function schedule()
    {
        try {
            $res = [];
            //查询排班
            $paibanSdk = SnisiyaSdk::getInstance();
            $params = [
                'scheduling_id' => $this->id,
                'status' => 'all',
                'pagesize' => 1,
                'startdate' => date('Y-m-d'),
            ];
            $paiban_data = $paibanSdk->getPaibanApi($params);
            $paibanInfo = ArrayHelper::getValue($paiban_data, 'list.0');

            $doctor_id = ArrayHelper::getValue($paibanInfo, 'doctor_id');
            if (!$paibanInfo || !$doctor_id) {
                throw new \Exception('排班不存在');
            }

            $docInfo = DoctorModel::find()->where(['doctor_id' => $doctor_id, 'hospital_type' => 1, 'status' => 1])->select('tp_platform,tp_hospital_code')->asArray()->one();
            $tp_platform = ArrayHelper::getValue($docInfo, 'tp_platform');
            $tp_hospital_code = ArrayHelper::getValue($docInfo, 'tp_hospital_code');
            if (!$tp_platform || !$tp_hospital_code) {
                throw new \Exception('医生不存在或不在合作范围');
            }

            $relationWhere = [];
            $relationWhere['tp_platform'] = $tp_platform;
            $relationWhere['tp_hospital_code'] = $tp_hospital_code;
            $relationWhere['status'] = 1;

            $cooArr = GuahaoPlatformRelationHospitalModel::getCooList($relationWhere);
            if ($cooArr) {
                $flag = false;
                foreach ($cooArr as $v) {
                    if ($v == 1) { //百度合作
                        //非公立医院不推送
                        $hospital_id = ArrayHelper::getValue($paibanInfo, 'hospital_id');
                        if (empty($hospital_id)) {
                            continue;
                        }
                        $hospital_info = BaseDoctorHospitals::HospitalDetail($hospital_id);
                        if (ArrayHelper::getValue($hospital_info, 'kind') != '公立') {
                            continue;
                        }

                        $flag = true;
                        //处理排班数据
                        $doctor_id = ArrayHelper::getValue($paibanInfo, 'doctor_id');
                        $docData = GuahaoCooModel::getBaiduPaibanBaseData($doctor_id);
                        if ($docData) {
                            $paibanData = ArrayHelper::getValue($docData, 'baseData');
                            $paibanBaseData = ArrayHelper::getValue($docData, 'paibanData');
                            $scheduleData = GuahaoCooModel::formatBaiduPaibanData($paibanInfo, $paibanBaseData);
//                            $doctor_id = HashUrl::getIdEncode($doctor_id);
                            $paibanData = array_merge(['expertId' => $doctor_id], $paibanData, $scheduleData);
                            $sdk = BaiduGuahaoSdk::getInstance();
                            $res = $sdk->pushSchedule($paibanData);
                        }
                    }
                }
                if (!$flag) {
                    return ['code' => self::COO_FAIL_CODE, 'res' => '无coo合作方'];
                    //throw new \Exception('无coo合作方!');
                }
            } else {
                return ['code' => self::COO_FAIL_CODE, 'res' => '无coo合作方'];
                //throw new \Exception('无coo合作方');
            }

            $msg = $this->id . '--res:' . json_encode($res);
            return ['code' => 1, 'res' => $msg];
        } catch (\Exception $e) {
            \Yii::$app->params['DataToBaiDuRequest']['log_code'] = 400;
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }
}