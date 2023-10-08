<?php
/**
 * @file BaiduController.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/15
 */


namespace api\controllers;


use common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
use common\models\DoctorModel;
use common\models\GuahaoCooModel;
use common\models\GuahaoOrderModel;
use common\models\GuahaoOrderInfoModel;
use common\sdks\ComplainSdk;
use common\sdks\snisiya\SnisiyaSdk;
use api\validators\GuahaoBaiduValidator;
use common\models\GuahaoPlatformRelationHospitalModel;
use yii\helpers\ArrayHelper;
use Yii;

class BaiduController extends GuahaoOpenController
{

    /**
     * @var string[]
     */
    public $cipher = ['cipherid' => 'id', 'key' => 'key'];

    public $cipherKv = [];

    public $coo_platform = 1;

    public function init()
    {
        parent::init();

        //约定的key
        $this->cipher['cipherid'] = ArrayHelper::getValue(\Yii::$app->params, 'baiduguahao.cipherid');
        $this->key = $this->cipher['key'] = ArrayHelper::getValue(\Yii::$app->params, 'baiduguahao.key');
        $this->encryptKey = ArrayHelper::getValue(\Yii::$app->params, 'baiduguahao.encryptKey');
        $this->cipherKv[$this->cipher['cipherid']] = $this->cipher['key'];

        //验证签名
        $signRes = $this->validatorSign();
        if (!$signRes) {
            $this->returnError();
        }
        $this->preWhere();
    }


    /**
     * 医生列表
     * @return array
     * @author xiujianying
     * @date 2021/6/16
     */
    public function actionDoctorIdList()
    {
        return $this->jsonError('系统维护，挂号失败！');
        try {
            $list = [];
            $docWhere = $this->joinWhere;
            \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'doctorlist';
            \Yii::$app->params['DataToHospitalRequest']['unableLog'] = true;
            if (!$docWhere) {
                throw new \Exception('来源异常');
            }
            $p = ArrayHelper::getValue($this->params, 'p', 0);
            $p = (int)$p;
            $p = max(0, $p);

            $n = ArrayHelper::getValue($this->params, 'n', 20);
            $n = (int)$n;
            $n = min($n, 100);

            $query = DoctorModel::find()
                        ->alias('d')
                        ->join('INNER JOIN', ['tpr' => 'tb_guahao_platform_relation_hospital'], 'd.tp_platform=tpr.tp_platform AND d.tp_hospital_code=tpr.tp_hospital_code')
                        ->where($docWhere);
            //
            $total = $query->count();
            $p = min(intval($total / $n), $p);
            $offset = $p * $n;
            if ($total) {
                $list = $query->select(['d.doctor_id'])->offset($offset)->limit($n)->asArray()->all();
                if ($list) {
                    $list = array_column($list, 'doctor_id');
//                    array_walk($list, function (&$v) {
//                        $v = HashUrl::getIdEncode($v);
//                    });
                }
            }
            $data['sum'] = $total;
            $data['p'] = $p;
            $data['n'] = $n;
            $data['expertIdList'] = $list;

            return $this->jsonSuccess($data);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * 医生信息
     * @return array
     * @author xiujianying
     * @date 2021/6/16
     */
    public function actionDoctorInfo()
    {
        try {
            $docWhere = $this->where;
            $relationWhere = $this->relationWhere;
            if (!$docWhere || !$relationWhere) {
                throw new \Exception('来源异常');
            }
            $id = ArrayHelper::getValue($this->params, 'expert_id');
            \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'doctorinfo';
            \Yii::$app->params['DataToHospitalRequest']['index'] = $id;
            \Yii::$app->params['DataToHospitalRequest']['unableLog'] = true;

            if (!$id) {
                throw new \Exception('医生id不能为空');
            }
//            $doctor_id = HashUrl::getIdDecode($id);
            $doctor_id = $id;

            $docWhere['doctor_id'] = $doctor_id;

            $idInfo = DoctorModel::find()->where($docWhere)->select('doctor_id,primary_id,tp_platform,tp_hospital_code')->asArray()->one();
            $docTpPlatform = ArrayHelper::getValue($idInfo, 'tp_platform');
            $docTpHospitalCode = ArrayHelper::getValue($idInfo, 'tp_hospital_code');
            $tprInfo = [];
            if ($docTpPlatform && $docTpHospitalCode) {
                $relationWhere['tp_platform'] = $docTpPlatform;
                $relationWhere['tp_hospital_code'] = $docTpHospitalCode;
                $tprInfo = GuahaoPlatformRelationHospitalModel::find()->where($relationWhere)->select('id')->asArray()->one();
            }
            if ($idInfo && $tprInfo) {
                $primary = $idInfo['primary_id'] ? $idInfo['primary_id'] : $doctor_id;
                $cacheInfo = DoctorModel::getInfo($doctor_id);
                //print_r($cacheInfo);exit;
                //医生信息
                $docData['id'] = $id;
                $docData['primaryID'] = ArrayHelper::getValue($cacheInfo, 'primary_id')?HashUrl::getIdDecode($cacheInfo['primary_id']):$id;
                $docData['name'] = ArrayHelper::getValue($cacheInfo, 'doctor_realname');
                $docData['title'] = ArrayHelper::getValue($cacheInfo, 'doctor_title');
                $docData['goodat'] = ArrayHelper::getValue($cacheInfo, 'doctor_good_at');
                $docData['intro'] = ArrayHelper::getValue($cacheInfo, 'doctor_profile');
                $docData['headImgUrl'] = ArrayHelper::getValue($cacheInfo, 'doctor_avatar');
                $docData['hospitalId'] = ArrayHelper::getValue($cacheInfo, 'hospital_id');
                $docData['hospitalName'] = ArrayHelper::getValue($cacheInfo, 'hospital_name');

                $docData['serviceApntStatus'] = '1';
                $docData['serviceApntCount'] = '0';

                $docData['departmentId'] = ArrayHelper::getValue($cacheInfo, 'second_department_id');
                $docData['departmentName'] = ArrayHelper::getValue($cacheInfo, 'doctor_second_department_name');
                $docData['departmentLevel1'] = CommonFunc::getKeshiName($cacheInfo['miao_frist_department_id']);
                $docData['departmentLevel2'] = CommonFunc::getKeshiName($cacheInfo['miao_second_department_id']);
                $docData['targetUrl'] = rtrim(\Yii::$app->params['domains']['mobile'], '/') . Url::to(['doctor/home', 'doctor_id' => $primary]);

                $hospData = GuahaoCooModel::baiduGetInfo($docData['hospitalId']);
                if ($hospData) {
                    $docData = ArrayHelper::merge($docData, $hospData);
                }

                if ($idInfo['primary_id']) {
                    $primInfo = DoctorModel::getInfo($idInfo['primary_id']);
                    $tb_third_party_relation = ArrayHelper::getValue($primInfo, 'tb_third_party_relation');
                } else {
                    $tb_third_party_relation = ArrayHelper::getValue($cacheInfo, 'tb_third_party_relation');
                }

                $tmpBaseDoctorHospitals = [];//临时存储医院
                $child = [];
                if ($tb_third_party_relation) {
                    foreach ($tb_third_party_relation as $v) {
                        if(  in_array($v['tp_platform'],$this->tp_platform_arr) ) {
                            $department['departmentId'] = ArrayHelper::getValue($v, 'second_department_id');
                            $department['departmentName'] = ArrayHelper::getValue($v, 'second_department_name');
                            $department['departmentLevel1'] = CommonFunc::getKeshiName($v['miao_frist_department_id']);
                            $department['departmentLevel2'] = CommonFunc::getKeshiName($v['miao_second_department_id']);
                            $department['departmentOfflineL1'] = ArrayHelper::getValue($v, 'frist_department_name');
                            $rel_hosp_id = ArrayHelper::getValue($v, 'hospital_id');
                            //处理过当前医院
                            if (isset($tmpBaseDoctorHospitals[$rel_hosp_id])) {
                                $hospitalInfo = $tmpBaseDoctorHospitals[$rel_hosp_id];
                            } else {
                                //没处理过当前医院
                                $hospitalInfo = $tmpBaseDoctorHospitals[$rel_hosp_id] = BaseDoctorHospitals::HospitalDetail($rel_hosp_id);
                            }
                            $child_hosp_info = GuahaoCooModel::baiduGetInfo($rel_hosp_id, $department, false, $hospitalInfo);
                            if (!empty($child_hosp_info)) {
                                $child[$rel_hosp_id] = $child_hosp_info;
                            }
                        }
                    }
                }
                $docData['cipherid'] = $this->cipher['cipherid'];
                $docData['pubOffices'] = array_values($child);

                return $this->jsonSuccess($docData);
            } else {
                throw new \Exception('医生数据不存在');
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }

    }


    /**
     * 接收百度提供的用户申诉
     * @return  [type]     [description]
     * @version v1.0
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-17
     */
    public function actionAppeal()
    {
        $request       = Yii::$app->request;
        $post          = $request->post();
        if (!$post) {
            return $this->jsonError('数据不能为空！');
        }
        $docWhere = $this->where;
        \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'appeal';
        \Yii::$app->params['DataToHospitalRequest']['unableLog'] = true;
        if (!$docWhere) {
            throw new \Exception('来源异常');
        }
        \Yii::$app->params['DataToHospitalRequest']['index'] = ArrayHelper::getValue($post, 'bd_complain_id', '');
        try {
            $params = [
                'from'           => ArrayHelper::getValue($post, 'from', 'bd'), //标识该请求发自百度，固定值：bd
                'bd_complain_id' => ArrayHelper::getValue($post, 'bd_complain_id', ''), //百度内的申诉ID
                'complain_info'  => ArrayHelper::getValue($post, 'complain_info', ''), //订单申诉信息详情
                'msg_id'         => ArrayHelper::getValue($post, 'msg_id', ''), //唯一消息id标示 ，32位
            ];
            $push_info = ComplainSdk::getInstance()->getAppeal($params);
            ##推送消息给申诉平台获取王氏唯一申诉ID
            $res = [
                'tp_complain_id' => '', ##TP方申诉唯一ID。
            ];
            if ($push_info['errno'] == 0 && $push_info['tp_complain_id']) {
                $res['tp_complain_id'] = $push_info['tp_complain_id'] ?? '';
                return $this->jsonSuccess($res);
            }else{
                $push_info['errno'] ?? 0;
                $push_info['errmsg'] ?? '';
                return $this->jsonError($push_info['errmsg'],$push_info['errno']);
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * 获取医生挂号号源信息
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/18
     */
    public function actionDutySourceList()
    {
        return $this->jsonError('系统维护，挂号失败！');
        try {
            $docWhere = $this->where;
            $relationWhere = $this->relationWhere;
            if (!$docWhere || !$relationWhere) {
                throw new \Exception('数据异常');
            }
            $id = ArrayHelper::getValue($this->params, 'expert_id');
            \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'scheduleList';
            \Yii::$app->params['DataToHospitalRequest']['index'] = $id;
            \Yii::$app->params['DataToHospitalRequest']['unableLog'] = true;
            if (!$id) {
                throw new \Exception('医生id不能为空');
            }
//            $doctor_id = HashUrl::getIdDecode($id);
            $doctor_id = $id;
            $docWhere['doctor_id'] = $doctor_id;

            $idInfo = DoctorModel::find()->where($docWhere)->select('doctor_id,primary_id,tp_platform,tp_hospital_code')->asArray()->one();
            $docTpPlatform = ArrayHelper::getValue($idInfo, 'tp_platform');
            $docTpHospitalCode = ArrayHelper::getValue($idInfo, 'tp_hospital_code');
            $tprInfo = [];
            if ($docTpPlatform && $docTpHospitalCode) {
                $relationWhere['tp_platform'] = $docTpPlatform;
                $relationWhere['tp_hospital_code'] = $docTpHospitalCode;
                $tprInfo = GuahaoPlatformRelationHospitalModel::find()->where($relationWhere)->select('id')->asArray()->one();
            }
            if (!$idInfo || !$tprInfo) {
                throw new \Exception('医生数据不存在');
            }

            $docData = GuahaoCooModel::getBaiduPaibanBaseData($doctor_id);
            if ($docData) {
                //获取排班数据
                $showDay = CommonFunc::SHOW_DAY;
                $startdate = date('Y-m-d', strtotime('+1 days'));
                $enddate = date('Y-m-d', strtotime('+' . $showDay . ' days'));

                $paibanSdk = SnisiyaSdk::getInstance();
                $params = [
                    'doctor_id' => $doctor_id,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'pagesize' => 1000,
                ];
                $paiban_data = $paibanSdk->getPaibanApi($params);
                $paibanList = ArrayHelper::getValue($paiban_data, 'list');

                $data = ArrayHelper::getValue($docData, 'baseData');
                $paibanBaseData = ArrayHelper::getValue($docData, 'paibanData');
                $scheduleList = [];
                if (!empty($paibanList)) {
                    foreach ($paibanList as $panban) {
                        $scheduleList[] = GuahaoCooModel::formatBaiduPaibanData($panban, $paibanBaseData);
                    }
                }
                $data['scheduleList'] = $scheduleList;
                return $this->jsonSuccess($data);
            } else {
                throw new \Exception('医生数据不存在');
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * 创建订单接口
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/22
     */
    public function actionDutySourceLock()
    {
        return $this->jsonError('系统维护，挂号失败！');
        if (!\Yii::$app->request->isPost) {
            return $this->jsonError('请求失败');
        }

        try {
            $docWhere = $this->where;
            if (!$docWhere) {
                throw new \Exception('数据异常');
            }

            \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'createOrder';

            //格式化数据
            $this->params['appoint_info'] = json_decode(ArrayHelper::getValue($this->params, 'appoint_info', ''), true);
            $this->params['order_info'] = json_decode(ArrayHelper::getValue($this->params, 'order_info', ''), true);
            $this->params['patient_info'] = json_decode(ArrayHelper::getValue($this->params, 'patient_info', ''), true);
            $guahaoData = GuahaoCooModel::formatBaiduGuahaoData($this->params, $this->encryptKey);

            \Yii::$app->params['DataToHospitalRequest']['index'] = ArrayHelper::getValue($guahaoData, 'coo_order_id');
            \Yii::$app->params['DataToHospitalRequest']['guahaoData'] = $guahaoData;

            $validator = new GuahaoBaiduValidator();
            $validator->load($guahaoData, '');
            if (!$validator->validate()) {
                $error = array_values($validator->getErrors());
                $error = ArrayHelper::getValue($error, '0.0');
                $error = is_string($error) ? $error : '数据校验失败';
                return $this->jsonError($error, 104);
            }

            //获取用户信息
            $userData = CommonFunc::getGuahaoUserId($guahaoData);
            $guahaoData['uid'] = ArrayHelper::getValue($userData, 'uid');
            $guahaoData['patient_id'] = ArrayHelper::getValue($userData, 'patient_id');
            if (empty($guahaoData['uid']) || empty($guahaoData['patient_id'])) {
                return $this->jsonError('患者信息创建失败', 501);
            }

            //挂号
            $paibanSdk = SnisiyaSdk::getInstance();
            $result = $paibanSdk->guahao($guahaoData);
            $resultCode = ArrayHelper::getValue($result, 'code', '400');
            if ($resultCode == 200) {
                $dataItem = ArrayHelper::getValue($result, 'data');
                //$visit_number = ArrayHelper::getValue($dataItem, 'visit_number');
                //格式化返回信息
                $resultData = GuahaoCooModel::formatBaiduGuahaoResultData($dataItem);
                $order_sn = ArrayHelper::getValue($resultData,'order_id');
                $visit_number = $resultData['appoint_order_number'];
                //就诊取号码不为空
                if($visit_number){
                    //预约成功 发短信
                    CommonFunc::guahaoSendSms('guahao_baidu_success',$order_sn);
                }
                return $this->jsonSuccess($resultData);
            } else {
                //格式化错误信息
                $code = GuahaoCooModel::formatErrorCode($resultCode);
                return $this->jsonError(ArrayHelper::getValue($result, 'msg', ''), $code);
            }

            return $this->jsonSuccess($guahaoData);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }


    /**
     * 百度取消订单
     * @return array
     * @author xiujianying
     * @date 2021/6/26
     */
    public function actionOrderCancel()
    {
        try {
            $orderWhere = $this->where;
            if (!$orderWhere) {
                throw new \Exception('数据异常');
            }

            $tp_order_id = ArrayHelper::getValue($this->params, 'tp_order_id');
            $bd_patient_id = ArrayHelper::getValue($this->params, 'bd_patient_id');
            $reason = ArrayHelper::getValue($this->params, 'reason');

            \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'cancelOrder';
            \Yii::$app->params['DataToHospitalRequest']['index'] = $tp_order_id;

            $orderWhere = [];
            $orderWhere['order_sn'] = $tp_order_id;
            $orderWhere['coo_patient_id'] = $bd_patient_id;
            $orderWhere['coo_platform'] = $this->coo_platform;

            $query = GuahaoOrderModel::find()->where($orderWhere)->one();
            if ($query) {
                //取消订单
                $result = SnisiyaSdk::getInstance()->guahaoCancel(['id' => $tp_order_id, 'canceldesc' => '患者取消', 'state_desc' => '百度方取消订单'.$reason]);
            } else {
                throw new \Exception($tp_order_id . '订单不存在');
            }

            if ($result and $result['code'] == 200) {
                return $this->jsonSuccess([]);
            } else {
                return $this->jsonError($result['msg']);
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * 4.2.2 向TP方同步订单状态的接口
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/8/20
     */
    public function actionOrderStatus()
    {
        try {
            $orderWhere = $this->where;
            if (!$orderWhere) {
                throw new \Exception('数据异常');
            }

            $tp_order_id = ArrayHelper::getValue($this->params, 'tp_order_id');

            \Yii::$app->params['DataToHospitalRequest']['platform'] = '201';
            \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderChange';
            \Yii::$app->params['DataToHospitalRequest']['index'] = $tp_order_id;

            $orderWhere = [];
            $orderWhere['order_sn'] = $tp_order_id;
            $orderWhere['coo_platform'] = $this->coo_platform;

            $query = GuahaoOrderModel::find()->where($orderWhere)->one();
            if ($query) {
                //保存日志
            } else {
                throw new \Exception($tp_order_id . '订单不存在');
            }

            return $this->jsonSuccess([]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

}