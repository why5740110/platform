<?php
/**
 * 挂号相关接口
 * @file GuahaoCooModel.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2021-06-23
 */

namespace common\models;

use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\helpers\ArrayHelper;

class GuahaoCooModel
{

    /**
     * 获取百度排班基础数据
     * @param $doctor_id
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/19
     */
    public static function getBaiduPaibanBaseData($doctor_id)
    {
        $data = [];
        $paibanData = [];
        $baseData = [];
        $doctor_info = DoctorModel::getInfo($doctor_id);
        $hospital_id = ArrayHelper::getValue($doctor_info, 'hospital_id', '');
        $hospInfo = BaseDoctorHospitals::HospitalDetail($hospital_id);
        if (!empty($doctor_info) && !empty($hospInfo)) {
            $baseData['hospitalId'] = HashUrl::getIdDecode(ArrayHelper::getValue($hospInfo, 'id', ''));
            $baseData['hospitalName'] = ArrayHelper::getValue($hospInfo, 'name');
            $baseData['address'] = ArrayHelper::getValue($hospInfo, 'address');
            $baseData['phone'] = ArrayHelper::getValue($hospInfo, 'phone') ? [["value" => ArrayHelper::getValue($hospInfo, 'phone', ''), "type" => "总机"]] : [];
            $baseData['hospitalType'] = ArrayHelper::getValue($hospInfo, 'kind') == '公立' ? 1 : 2;
            $baseData['hospitalLevel'] = ArrayHelper::getValue($hospInfo, 'level');
            if (!in_array($baseData['hospitalLevel'], CommonFunc::$level_list)) {
                $baseData['hospitalLevel'] = '其他';
            }
            $baseData['hospitalNature'] = ArrayHelper::getValue($hospInfo, 'type');
            if ($baseData['hospitalNature'] == '中医院') {
                $baseData['hospitalNature'] = 5;
            } elseif ($baseData['hospitalNature'] == '综合') {
                $baseData['hospitalNature'] = 1;
            } else {
                $baseData['hospitalNature'] = 10;
            }
            $baseData['hospitalImage'] = ArrayHelper::getValue($hospInfo, 'photo');
            $baseData['hospitalGps'] = ['longitude' => ArrayHelper::getValue($hospInfo, 'longitude'), 'latitude' => ArrayHelper::getValue($hospInfo, 'latitude')];
            $baseData['province'] = ArrayHelper::getValue($hospInfo, 'province_name');
            $baseData['city'] = ArrayHelper::getValue($hospInfo, 'city_name');

            $paibanData['tb_third_party_relation'] = ArrayHelper::getValue($hospInfo, 'tb_third_party_relation');
            $paibanData['departmentId'] = ArrayHelper::getValue($doctor_info, 'second_department_id');
            $paibanData['departmentName'] = ArrayHelper::getValue($doctor_info, 'doctor_second_department_name');
            $paibanData['departmentLevel1'] = CommonFunc::getKeshiName(ArrayHelper::getValue($doctor_info, 'miao_frist_department_id'));
            $paibanData['departmentLevel2'] = CommonFunc::getKeshiName(ArrayHelper::getValue($doctor_info, 'miao_second_department_id'));
            $data = ['baseData' => $baseData, 'paibanData' => $paibanData];
        }
        return $data;
    }

    /**
     * 格式化百度排班
     * @param $doctor_id
     * @param $paibanBaseData
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/19
     */
    public static function formatBaiduPaibanData($panban, $paibanBaseData = [])
    {
        $data = [];
        $data['appointId'] = ArrayHelper::getValue($panban, 'scheduling_id');
        $data['appointType'] = ArrayHelper::getValue($panban, 'schedule_type');
        //出诊时间
        if (!empty(ArrayHelper::getValue($panban, 'visit_starttime'))) {
            if (strripos($panban['visit_starttime'], "-") !== false) {
                $times = explode('-', $panban['visit_starttime']);
                $visit_starttime = trim($times[0]);
            } else {
                $visit_starttime = trim($panban['visit_starttime']);
            }
            $data['time'] = strtotime(ArrayHelper::getValue($panban, 'visit_time') . ' ' . $visit_starttime);
        } else {
            //$data['date'] = ArrayHelper::getValue($panban, 'visit_time');
            if (ArrayHelper::getValue($panban, 'visit_nooncode') == 1) {
                $data['time'] = strtotime(ArrayHelper::getValue($panban, 'visit_time') . ' 08:00');
            } else if (ArrayHelper::getValue($panban, 'visit_nooncode') == 2) {
                $data['time'] = strtotime(ArrayHelper::getValue($panban, 'visit_time') . ' 14:00');
            } else if (ArrayHelper::getValue($panban, 'visit_nooncode') == 3) {
                $data['time'] = strtotime(ArrayHelper::getValue($panban, 'visit_time') . ' 20:00');
            }
        }

        //时间段
        if (empty(ArrayHelper::getValue($panban, 'visit_starttime')) && empty(ArrayHelper::getValue($panban, 'visit_endtime'))) {
            if (ArrayHelper::getValue($panban, 'visit_nooncode') == 1) {
                $data['workHours'] = '8:00';
            } else if (ArrayHelper::getValue($panban, 'visit_nooncode') == 2) {
                $data['workHours'] = '14:00';
            } else if (ArrayHelper::getValue($panban, 'visit_nooncode') == 3) {
                $data['workHours'] = '20:00';
            }
        } else {
            if (!empty(ArrayHelper::getValue($panban, 'visit_starttime'))) {
                $data['workHours'] = ArrayHelper::getValue($panban, 'visit_starttime');
            }
            if (!empty(ArrayHelper::getValue($panban, 'visit_endtime'))) {
                if (strripos($data['workHours'], "-") === false) {
                    $data['workHours'] .= '-' . ArrayHelper::getValue($panban, 'visit_endtime');
                }
            }
        }

        //预约截止时间
        if (!empty(ArrayHelper::getValue($panban, 'visit_valid_time'))) {
            $data['stopRegisterDate'] = ArrayHelper::getValue($panban, 'visit_valid_time');
        } else {
            $data['stopRegisterDate'] = strtotime(ArrayHelper::getValue($panban, 'visit_time'));
        }
        //取消截止时间 默认前一天12点
        $tb_third_party_relation = ArrayHelper::getValue($paibanBaseData, 'tb_third_party_relation', []);
        $data['invalidDate'] = strtotime(' -1 day', strtotime(ArrayHelper::getValue($panban, 'visit_time') . ' 12:00:00'));
        if (!empty($tb_third_party_relation)) {
            foreach ($tb_third_party_relation as $value) {
                if ($value['tp_platform'] == $panban['tp_platform'] && $value['tp_hospital_code'] == $panban['tp_scheduleplace_id']) {
                    $data['invalidDate'] = strtotime(' -' . ArrayHelper::getValue($value, 'tp_allowed_cancel_day') . ' day', strtotime(ArrayHelper::getValue($panban, 'visit_time') . ' ' . ArrayHelper::getValue($value, 'tp_allowed_cancel_time')));
                    break;
                }
            }
        }

        $data['type'] = str_replace('门诊', '', ArrayHelper::getValue(CommonFunc::$visit_type, ArrayHelper::getValue($panban, 'visit_type', '8')));
        $data['workPlace'] = ArrayHelper::getValue($panban, 'visit_address');
        $data['price'] = ArrayHelper::getValue($panban, 'visit_cost');
        $data['referralPrice'] = ArrayHelper::getValue($panban, 'referral_visit_cost');
        $data['originalPrice'] = ArrayHelper::getValue($panban, 'visit_cost_original');
        $data['originalReferralPrice'] = ArrayHelper::getValue($panban, 'referral_visit_cost_original');
        $data['status'] = self::formatPaibanStatus(ArrayHelper::getValue($panban, 'status'), '1');
        $data['amount'] = ArrayHelper::getValue($panban, 'schedule_available_count') == '-1' ? '99' : ArrayHelper::getValue($panban, 'schedule_available_count');
        $data['paymode'] = ArrayHelper::getValue($panban, 'pay_mode') == 1 ? 1 : 2;

        $data['departmentId'] = ArrayHelper::getValue($paibanBaseData, 'departmentId');
        $data['departmentLevel1'] = ArrayHelper::getValue($paibanBaseData, 'departmentLevel1');
        $data['departmentLevel2'] = ArrayHelper::getValue($paibanBaseData, 'departmentLevel2');
        $data['departmentName'] = ArrayHelper::getValue($paibanBaseData, 'departmentName');
        $data['collectedFields'] = ['CardNo'];
        return $data;
    }

    /**
     * 格式化百度挂号数据
     * @param $guahaoData
     * @param $aesSecretKey
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/23
     */
    public static function formatBaiduGuahaoData($guahaoData, $aesSecretKey)
    {
        $data = [];
        $data['doctor_id'] = ArrayHelper::getValue($guahaoData, 'appoint_info.expertId');
        $data['scheduling_id'] = ArrayHelper::getValue($guahaoData, 'appoint_info.appointId');
        $data['uid'] = '';
        $data['patient_id'] = '';
        $data['famark_type'] = 1;
        $data['symptom'] = ArrayHelper::getValue($guahaoData, 'order_info.complaint') ?: '无';

        $data['patient_name'] = ArrayHelper::getValue($guahaoData, 'patient_info.name');
        $data['gender'] = ArrayHelper::getValue($guahaoData, 'patient_info.sex');
        $data['age'] = ArrayHelper::getValue($guahaoData, 'patient_info.age');
        $data['card'] = CommonFunc::aesDecode(ArrayHelper::getValue($guahaoData, 'patient_info.cardNo'), $aesSecretKey);
        $data['mobile'] = CommonFunc::aesDecode(ArrayHelper::getValue($guahaoData, 'patient_info.phone'), $aesSecretKey);
        $data['birth_time'] = date('Y-m-d', strtotime(substr($data['card'], 6, 8)));
        //$data['province'] = ArrayHelper::getValue($guahaoData, 'patient_info.province');
        $data['province'] = '';
        //$data['city'] = ArrayHelper::getValue($guahaoData, 'patient_info.city');
        $data['city'] = '';

        $data['coo_patient_id'] = ArrayHelper::getValue($guahaoData, 'patient_info.bdPatientId');
        $data['coo_order_id'] = ArrayHelper::getValue($guahaoData, 'order_info.bdOrderId');
        $data['coo_platform'] = 1;
        $data['ip'] = ArrayHelper::getValue($guahaoData, 'order_info.ip');

        return $data;
    }

    /**
     * 格式化百度返回信息
     * @param $guahaoData
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/23
     */
    public static function formatBaiduGuahaoResultData($guahaoData)
    {
        $data = [];
        $data['order_id'] = ArrayHelper::getValue($guahaoData, 'id');
        $data['appoint_status'] = ArrayHelper::getValue($guahaoData, 'schedule_available_count') == '-1' ? '99' : ArrayHelper::getValue($guahaoData, 'schedule_available_count');
        $data['appoint_amount'] = self::formatPaibanStatus(ArrayHelper::getValue($guahaoData, 'status'), '1');
        $data['appoint_order_number'] = ArrayHelper::getValue($guahaoData, 'visit_number');//取号码
        //过滤特殊情况的字串
        $data['appoint_order_number'] = self::filterVisitNumber($data['appoint_order_number']);

        $visit_time = '';
        if (!empty($data['order_id'])) {
            $orderModel = GuahaoOrderModel::find()->select(['id'])->where(['order_sn' => $data['order_id']])->asArray()->one();
            $orderInfo = GuahaoOrderInfoModel::find()->where(['order_id' => $orderModel['id']])->asArray()->one();
            if (!empty($orderInfo)) {
                if (ArrayHelper::getValue($orderInfo, 'visit_starttime')) {
                    $visit_time .= ArrayHelper::getValue($orderInfo, 'visit_starttime');
                }
                if (ArrayHelper::getValue($orderInfo, 'visit_endtime')) {
                    $visit_time .= "-".ArrayHelper::getValue($orderInfo, 'visit_endtime');
                }
            }
            if (empty($data['appoint_order_number'])) {
                $data['appoint_order_number'] = ArrayHelper::getValue($orderInfo, 'visit_number');
                $data['appoint_order_number'] = self::filterVisitNumber($data['appoint_order_number']);
            }
        }
        $data['workHours'] = $visit_time;//具体到诊时间

        return $data;
    }

    /**
     * 取号码过来特殊字符
     * @param $visit_number
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-06-22
     * @return string
     */
    public static function filterVisitNumber($visit_number)
    {
        if (in_array($visit_number, ['null', 'NULL', '0', 'Array', 'array'])) {
            $visit_number = "";
        }
        return $visit_number;
    }

    /**
     * 格式化排班状态
     * @param $status
     * @param int $coo
     * @return mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/19
     */
    public static function formatPaibanStatus($status, $coo = 1)
    {
        $statusArr = [
            '1' => [
                '-1' => 4,//已取消
                '0' => 2,//约满
                '1' => 1,//可约
                '2' => 3,//停诊
                '3' => 4,//已过期
                '4' => 4,//其他
            ]
        ];
        return ArrayHelper::getValue($statusArr, $coo . '.' . $status, '');
    }

    /**
     * 格式化错误代码
     * @param $code
     * @param int $coo
     * @return mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/19
     */
    public static function formatErrorCode($code, $coo = 1)
    {
        $codeArr = [
            '1' => [
                '200' => 0,//成功
                '404' => 501,//内部错误
                '400' => 402,//第三方返回
                '411' => 301,//排版过期
                '412' => 301,//排版约满
                '413' => 302,//排版获取失败
                '414' => 302,//停诊
                '415' => 301,//超出预约限制
                '416' => 402,//获取价格失败
            ]
        ];
        return ArrayHelper::getValue($codeArr, $coo . '.' . $code, '400');
    }


    /**
     * @param $hospital_id
     * @param $targetUrl  医生跳转链接
     * @param $department 医生科室信息
     * @param $hospInfo 已有医院信息
     * @param bool $flag true:返回全部信息 false：返回pubOffices信息
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/18
     */
    public static function baiduGetInfo($hospital_id, $department = [], $flag = true, $hospInfo = [])
    {
        if (empty($hospitalInfo)) {//已有医院信息
            $hospInfo = BaseDoctorHospitals::HospitalDetail($hospital_id);
            if (empty($hospInfo)) {
                return [];
            }
        }

        $data['hospitalId'] = HashUrl::getIdDecode(ArrayHelper::getValue($hospInfo, 'id', ''));
        $data['hospitalName'] = ArrayHelper::getValue($hospInfo, 'name');
        $data['hospitalInsurance'] = 1;
        $data['hospitalNature'] = ArrayHelper::getValue($hospInfo, 'type');
        if ($data['hospitalNature'] == '中医院') {
            $data['hospitalNature'] = 5;
        } elseif ($data['hospitalNature'] == '综合') {
            $data['hospitalNature'] = 1;
        } else {
            $data['hospitalNature'] = 10;
        }
        $data['hospitalDays'] = ArrayHelper::getValue($hospInfo, 'hospital_open_day');
        $data['hospitalTime'] = ArrayHelper::getValue($hospInfo, 'hospital_open_time');

        $data['hospitalAddress'] = ArrayHelper::getValue($hospInfo, 'address');
        $data['hospitalImage'] = ArrayHelper::getValue($hospInfo, 'photo');
        $data['hospitalGps'] = ['longitude' => ArrayHelper::getValue($hospInfo, 'longitude', 0), 'latitude' => ArrayHelper::getValue($hospInfo, 'latitude', 0)];
        $data['hospitalSpecDeps'] = [];
        $data['hospitalImage'] = ArrayHelper::getValue($hospInfo, 'photo');
        $data['hospitalLevel'] = ArrayHelper::getValue($hospInfo, 'level');
        $data['province'] = ArrayHelper::getValue($hospInfo, 'province_name');
        $data['city'] = ArrayHelper::getValue($hospInfo, 'city_name');

        if ($department) {
            $data = array_merge($data, $department);
        }

        if ($flag) {
            $data['rank'] = 1;
            $data['type'] = 1;
            $data['hospitalType'] = ArrayHelper::getValue($hospInfo, 'kind') == '公立' ? 1 : 2;
            $data['diseaseInfo'] = [];
        } else {
            //unset($data['departmentOfflineL1']);
            $data['hospitalTag'] = [ArrayHelper::getValue($hospInfo, 'level'), ArrayHelper::getValue($hospInfo, 'type') . '医院'];
        }
        return $data;
    }

    //=======阿里对接 数据处理开始==========

    /**
     *  格式化返回给阿里的医院信息
     * @param $hospitalData
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-21
     */
    public static function formatAliHospitalData($hospitalData)
    {
        $hospInfo = BaseDoctorHospitals::getHospitalDetail($hospitalData['hospital_id']);
        if (!$hospInfo) {
            return [];
        }
        $hospitalParams['hospital_id'] = HashUrl::getIdEncode($hospitalData['hospital_id']);
        $hospDetail = SnisiyaSdk::getInstance()->getHospitalDetail($hospitalParams);
        $hospital['hosOrgNo'] = HashUrl::getIdEncode($hospitalData['hospital_id']);
        $hospital['hosDistinctCode'] = HashUrl::getIdEncode($hospitalData['hospital_id']);
        $hospital['areaCode'] = ArrayHelper::getValue($hospInfo, 'district_code') ?? ""; // 这里要调用 基础数据的编码配置
        $hospital['hosName'] = ArrayHelper::getValue($hospDetail, 'name'); // 第三方医院名称
        $hospital['hosType'] = ArrayHelper::getValue($hospInfo, 'kind') == '公立' ? '1' : '0';
        $hospital['hosLevel'] = self::getAliHospitalLevel(ArrayHelper::getValue($hospInfo, 'level'));

        $hospital['hosLogo'] = ArrayHelper::getValue($hospInfo, 'logo');
        $hospital['specialDeptInfo'] = '';
        $hospital['addressInfo'] = ArrayHelper::getValue($hospInfo, 'address');
        $hospital['longitude'] = floatval(ArrayHelper::getValue($hospInfo, 'longitude', 0));
        $hospital['latitude'] = floatval(ArrayHelper::getValue($hospInfo, 'latitude', 0));

        $hospital['introduction'] = ArrayHelper::getValue($hospInfo, 'description');
        $hospital['payType'] = "0" ; // 医院支付类型 (0 现场支付, 1 强制在线支付) 这里待确认 字段及数据

        $hospital['province'] = ArrayHelper::getValue($hospInfo, 'province_code','');
        $hospital['city'] = ArrayHelper::getValue($hospInfo, 'city_code','');
        $hospital['district'] = ArrayHelper::getValue($hospInfo, 'city_name');
        $hospital['medicareFlag'] = 0; //  医保标识 (0 非医保, 1 医保) 待处理
        $supplyTime = $hospDetail['hospital_open_time'] ?? "";
        $supplyTime = !(empty($supplyTime)) ? $supplyTime : '07:00';
        if (strval($supplyTime) == "24:00") {
            $supplyTime = "00:00";
        }
        $hospital['supplyTime'] =$supplyTime;
        $resCycle =$hospDetail['hospital_open_day']; // 30 天内
        $hospital['resCycle'] = !empty($resCycle) ? $resCycle : "30";

        $hospital['hosFlag'] = '0';
        $hosFlag = ArrayHelper::getValue($hospDetail, 'tp_guahao_verify', 0);
        if($hosFlag){
            $hospital['hosFlag'] = strval($hosFlag);
        }
        // 是否需要就诊卡, (0 不需要, 1 需要就诊卡+密码, 2 需要就诊卡)

        $hospOption['tranInfo'] = json_encode(['hosTranInfo'=>$hospInfo['routes']]);
        $hospOption['honorInfo'] = '';
        $hospOption['buildInfo'] = '';
        $hospOption['tel'] = ArrayHelper::getValue($hospDetail, 'fax_num','');
        $hospOption['specialDepart'] ='';
        $hospital['optionAttributes'] = $hospOption;
        return $hospital;
    }

    /**
     * 格式化 数据给阿里
     * @param $list
     * @param $hospitalId
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-23
     */
    public static function formatAliDoctorData($list,$hospitalId)
    {
        $newDoctorList = [];
        foreach ($list as $doctorKey => $doctorData) {
            $doctorInfoPar['hosOrgNo'] = HashUrl::getIdEncode($doctorData['hospital_id']);
            $doctorInfoPar['hosDistinctCode'] = HashUrl::getIdEncode($doctorData['hospital_id']);
            $doctorInfoPar['hosName'] = $doctorData['hospital_name'] ?? "";
            $infos = DoctorModel::getInfo(ArrayHelper::getValue($doctorData, 'doctor_id'));
            // 科室问题 ID 这里待处理转换
            $doctorInfoPar['deptId'] = $doctorData['second_department_id'];
            $doctorInfoPar['deptName'] = ArrayHelper::getValue($doctorData, 'second_department_name') ?? "";
            $doctorInfoPar['parentDeptId'] = "f_" . ArrayHelper::getValue($doctorData, 'frist_department_id') ?? "";
            $doctorInfoPar['parentDeptName'] = ArrayHelper::getValue($doctorData, 'frist_department_name') ?? "";
            $docId = $doctorData['doctor_id'];
            if (intval($doctorData['primary_id'])) {
                $docId = $doctorData['primary_id'];
            }

            $doctorInfoPar['doctorNo'] = HashUrl::getIdEncode(strval($docId));
            $doctorInfoPar['doctorType'] = 3; // 商讨结果 固定 传 3

            $doctorInfoPar['doctorName'] = ArrayHelper::getValue($doctorData, 'realname');
            $doctorTitle = ArrayHelper::getValue($doctorData, 'job_title');
            $doctorInfoPar['doctorTitle'] = !empty($doctorTitle) ? $doctorTitle : "普通";
            $doctorInfoPar['imgUrl'] = ArrayHelper::getValue($infos, 'doctor_avatar');
            $doctorInfoPar['description'] = ArrayHelper::getValue($infos, 'doctor_profile');
            $doctorInfoPar['specialty'] = ArrayHelper::getValue($infos, 'doctor_good_at');
            $doctorInfoPar['sex'] = '0';
            $thirdPartyRelation = ArrayHelper::getValue($infos, 'tb_third_party_relation');
            if (count($thirdPartyRelation) > 1) {
                $doctorInfoPar['multiPracticeFlag'] = true; // 多执业 如果有多执业, 就是true, 没有就是false
                $doctorInfoPar['optionAttributes'] = self::handThirdPartyRelationToAli($thirdPartyRelation, $doctorData['doctor_id']);
            } else {
                $doctorInfoPar['multiPracticeFlag'] = false;
            }
            $newDoctorList[] = $doctorInfoPar;
        }
        return $newDoctorList;

    }
    /**
     * 排班详情助手
     * @param $v
     * @return mixed
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-12
     */
    public static function formatAliScheduleInfo($schedule)
    {
        $docId = $schedule['primary_id'] ?? $schedule['doctor_id'];
        $infos = DoctorModel::getInfo($docId);
        // 此处测试用
        $scheduleInfoPar['doctorNo'] = HashUrl::getIdEncode($docId);
        $scheduleInfoPar['doctorName'] = ArrayHelper::getValue($infos, 'doctor_realname');

        // 科室id 问题待处理
        $scheduleInfoPar['deptId'] = ArrayHelper::getValue($schedule, 'second_department_id');
        $scheduleInfoPar['deptName'] = ArrayHelper::getValue($schedule, 'department_name');

        $scheduleInfoPar['hosNo'] = HashUrl::getIdEncode($schedule['hospital_id']);
        $scheduleInfoPar['hosName'] = ArrayHelper::getValue($schedule, 'scheduleplace_name');
        $scheduleInfoPar['resourceId'] = ArrayHelper::getValue($schedule, 'tp_scheduling_id');
        $scheduleInfoPar['serviceDate'] = ArrayHelper::getValue($schedule, 'visit_time');
        $scheduleInfoPar['resourceType'] = 3; // 商讨结果， 固定传3
        $scheduleInfoPar['leftCount'] = intval(ArrayHelper::getValue($schedule, 'schedule_available_count'));
        $scheduleInfoPar['allCount'] = intval(ArrayHelper::getValue($schedule, 'schedule_available_count'));
        $scheduleInfoPar['registerFee'] = floatval(ArrayHelper::getValue($schedule, 'visit_cost'));
        $scheduleInfoPar['shiftType'] = self::getVisitNooncodeToAli(ArrayHelper::getValue($schedule, 'visit_nooncode'));
        $scheduleInfoPar['scheduleStatus'] = self::getScheduleStatusToAli(ArrayHelper::getValue($schedule, 'status'));
        return $scheduleInfoPar;
    }

    /**
     *  等级转换
     * @param $level
     * @return int|string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-02-28
     */
    public static function getAliHospitalLevel($level)
    {
        //01 特级, 02 三甲,03 三乙, 04 三丙, 041 三级, 05 二甲, 06 二乙, 07 二丙, 071 二级, 08 一甲, 09 一乙,10一丙, 101 一级,99 其他
        $levelNum = 99;
        $aliHospitalLevelArr = [
            '特级'=> '01',
            '三级甲等'=> '02',
            '三级乙等'=> '03',
            '三级丙等'=> '04',
            '三级'=> '041',
            '二级甲等'=> '05',
            '二级乙等'=> '06',
            '二级丙等'=> '07',
            '二级'=> '071',
            '一级甲等'=> '08',
            '一级乙等'=> '09',
            '一级丙等'=> '10',
            '一级'=> '101',
            '其他'=> '99',
        ];
        try {
            return $aliHospitalLevelArr[$level];
        }catch (\Exception  $e) {
            return $levelNum;
        }
    }


    /**
     *  阿里午别
     * @param $visitNooncode
     * @return int
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-02
     */
    public static function getVisitNooncodeToAli($visitNooncode)
    {
        // 午别 1:上午 2：下午 3:晚上 4:其他
        //班次 (1 上午, 2 下午, 3 晚上, 4 中午, 5 傍晚, 6 白天, 7 黑夜, 8 全天)
        $nooncode = 0;
        $arr = [
            1=>1,
            2=>2,
            3=>3,
        ];
        if (isset($arr[$visitNooncode])){
            $nooncode =  $arr[$visitNooncode];
        }
        return $nooncode;
    }
    /**
     *  状态
     * @param $status
     * @return int
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-02
     */
    public static function getScheduleStatusToAli($status)
    {
        // -1:已取消 0约满 1可约 2停诊 3已过期 4其他
        $statusData = 0;
        $arr = [
            0=>2,
            1=>1,
            2=>3,
        ];
        if (isset($arr[$status])){
            $statusData =  $arr[$status];
        }
        return $statusData;
    }

    /**
     * 医生多
     * @param $value
     * @param $doctorId
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-02
     */
    public static function handThirdPartyRelationToAli($value, $doctorId)
    {
        $relationArray = [];
        foreach ($value as $k=>$v){
            $hospInfo = BaseDoctorHospitals::HospitalDetail(ArrayHelper::getValue($v, 'hospital_id'));
            $item['hosOrgNo'] = ArrayHelper::getValue($hospInfo, 'id');
            $item['hosDistinctCode'] = ArrayHelper::getValue($hospInfo, 'id');
            $item['hosName'] = ArrayHelper::getValue($hospInfo, 'name');

            $item['deptId'] = ArrayHelper::getValue($v, 'miao_second_department_id');
            $infos = DoctorModel::getInfo(ArrayHelper::getValue($v, 'doctor_id'));
            $item['doctorNo'] = ArrayHelper::getValue($infos, 'doctor_id');
            if (intval($v['doctor_id']) == intval($doctorId)) {
                $item['firstPlaceFlag'] = 1;
            }else{
                $item['firstPlaceFlag'] = 0;
            }
            array_push($relationArray, $item);
        }
        $data['multiPractice'] = $relationArray;
        return $data;
    }

    //=======阿里对接 数据处理结束==========
}