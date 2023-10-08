<?php
/**
 * 阿里健康 h5 对接 医生直连
 * @file AliController.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-02-28
 */

namespace api\controllers;

use common\libs\AliVerification;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\libs\Log;
use common\libs\MiaoCrypt3Des;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\DoctorModel;
use common\models\GuahaoCooInterrogationModel;
use common\models\GuahaoCooListModel;
use common\models\GuahaoCooModel;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoOrderModel;
use common\models\GuahaoPlatformListModel;
use common\models\GuahaoPlatformRelationHospitalModel;
use common\models\GuahaoScheduleModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\AliGuahaoSdk;
use Matrix\Exception;
use yii\helpers\ArrayHelper;

class AliController extends GuahaoOpenController
{

    public $coo_platform = 2;

    public function init()
    {
        parent::init();

        //验证签名
        $signRes = $this->validatorSign();
        if (!$signRes) {
            $this->returnError();
        }
        //验证来源
        $this->params = $this->getAliData();
        if (empty($this->params)) {
            $this->returnError('params-check-failure');
        } else {
            $this->params['from'] = 'ali';
        }

        $this->preWhere();
    }

    /**
     *  根据王氏医院的ID判断是否给阿里开放了医院
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-03
     */
    public function getOpenHospitalIds($hospitalId)
    {
        $selectData = GuahaoHospitalModel::find()
            ->alias('h')
            ->join('INNER JOIN', ['tpr' => 'tb_guahao_platform_relation_hospital'], 'h.tp_platform=tpr.tp_platform AND h.tp_hospital_code=tpr.tp_hospital_code')
            ->where(['h.hospital_id'=>$hospitalId,'h.status'=>1,'tpr.status'=>1])->asArray()->all();
        if (empty($selectData)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *  医院列表
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-02-28
     */
    public function actionHospitalList()
    {
        \Yii::$app->params['DataToHospitalRequest']['platform'] = '202';
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'HospitalList';
        \Yii::$app->params['DataToHospitalRequest']['unableLog'] = false;
        $log_start_time = microtime(true);
        try {
            $p = ArrayHelper::getValue($this->params, 'pageNum', 1);
            $p = (int)$p ? intval($p) : 1;
            $p = max(1, $p);
            $n = ArrayHelper::getValue($this->params, 'pageSize', 20);
            $n = (int)$n ? (int)$n : 20;
            $n = min($n, 100);

            $hospWhere = $this->joinWhere;
            if (!$hospWhere) {
                throw new \Exception('来源异常');
            }
            // 去除不用的共用条件
            unset($hospWhere['d.doctor_real_plus']);
            unset($hospWhere['d.hospital_type']);
            unset($hospWhere['d.status']);

            $hospWhere['h.status'] = 1;
            $query = GuahaoHospitalModel::find()
                ->alias('h')
                ->join('INNER JOIN', ['tpr' => 'tb_guahao_platform_relation_hospital'], 'h.tp_platform=tpr.tp_platform AND h.tp_hospital_code=tpr.tp_hospital_code')
                ->where($hospWhere);

            $hosOrgNo = ArrayHelper::getValue($this->params, 'hosOrgNo', '');
            if (!empty(trim($hosOrgNo))) {
                $query->andWhere(['h.hospital_id' => trim(HashUrl::getIdDecode($hosOrgNo))]);
            }
            $hosDistinctCode = ArrayHelper::getValue($this->params, 'hosDistinctCode', '');
            if (!empty(trim($hosDistinctCode))) {
                \Yii::$app->params['DataToHospitalRequest']['index'] = trim($hosDistinctCode);
                $query->andWhere(['h.hospital_id' => trim(HashUrl::getIdDecode($hosDistinctCode))]);
            }
            $hosName = ArrayHelper::getValue($this->params, 'hosName', '');
            if (!empty(trim($hosName))) {
                $query->andWhere(['like','h.hospital_name', trim($hosName)]);
            }
            $total = $query->count();
            $offset = ($p-1)*$n;
            $returnList = [];
            if ($total) {
                $list = $query->select(['h.hospital_id',"h.tp_guahao_verify"])->orderBy('h.id')->offset($offset)->limit($n)->asArray()->all();
                if ($list) {
                    foreach ($list as $k=>$v){
                        $hospital = GuahaoCooModel::formatAliHospitalData($v);
                        if (empty($hospital)){
                            continue;
                        }
                        array_push($returnList, $hospital);
                    }
                }
            }

            $data['total'] = intval($total);
            $data['pageNum'] = intval($p);
            $data['pageSize'] = intval($n);
            $data['hosInfo'] = $returnList;

            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonSuccess($data);
        } catch (\Exception $e) {
            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonError($e->getMessage());
        }
    }

    /**
     *  科室列表
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-10
     */

    public function actionDepartmentList()
    {
        \Yii::$app->params['DataToHospitalRequest']['platform'] = '202';
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'DepartmentList';
        \Yii::$app->params['DataToHospitalRequest']['unableLog'] = false;
        $log_start_time = microtime(true);
        try {
            $p = ArrayHelper::getValue($this->params, 'pageNum', 1);
            $n = ArrayHelper::getValue($this->params, 'pageSize', 20);
            // 是否在开放的id 中

            $deptId = ArrayHelper::getValue($this->params, 'deptId', '');
            $deptName = trim(ArrayHelper::getValue($this->params, 'deptName', ''));
            $depSearch = '';
            if (!empty(trim($deptId))) {
                // 获取科室id
                $res = preg_match("/f_/",$deptId);
                if($res){
                    $depArr = explode('_',$deptId);
                    if (isset($depArr['1'])) {
                        $depSearch = $depArr[0];
                    }
                }else{
                    $depSearch = $deptId;
                }
            }
            $hosOrgNo = ArrayHelper::getValue($this->params, 'hosOrgNo', '');
            $hosDistinctCode = ArrayHelper::getValue($this->params, 'hosDistinctCode', '');
            if (empty(trim($hosDistinctCode))) {
                return $this->aliJsonError('院区代码 不能为空');
            }
            if (!empty($hosOrgNo) && !empty($hosDistinctCode)) {
                if (strcmp($hosOrgNo, $hosDistinctCode) !== 0) {
                    return $this->aliJsonError('院区代码 和院区Id 不一致');
                }
            }

            \Yii::$app->params['DataToHospitalRequest']['index'] = $hosDistinctCode."-".$deptId;
            $hosDistinctCode = trim(HashUrl::getIdDecode($hosDistinctCode));
            if (!$hosDistinctCode) {
                return $this->aliJsonError('医院代码有误！');
            }
            $isTureOrFalse = $this->getOpenHospitalIds($hosDistinctCode);
            if (false == $isTureOrFalse) {
                return $this->aliJsonError('医院未开放！');
            }

            $hospInfo = BaseDoctorHospitals::getHospitalDetail($hosDistinctCode);
            //科室
            $sub = HospitalDepartmentRelation::hospitalDepartment($hosDistinctCode);
            $listArr = [];
            foreach($sub as $k=>$v){
                if ($deptName && $deptName !== $v['frist_department_name']) {
                    continue;
                }
                // 一级
                $departmentInfoPar['hosOrgNo'] = HashUrl::getIdEncode($hosDistinctCode);
                $departmentInfoPar['hosDistinctCode'] = HashUrl::getIdEncode($hosDistinctCode);
                $departmentInfoPar['hosName'] = ArrayHelper::getValue($hospInfo, 'name');
                $departmentInfoPar['deptSpecial'] = '';
                $departmentInfoPar['introduction'] = '';
                $departmentInfoPar['deptHeadImg'] = '';
                $departmentInfoPar['optionAttributes'] = (object)[];

                $departmentInfoPar['parentDeptId'] ="0"; // 如果没有父科室， 给 0
                $departmentInfoPar['parentDeptName'] = ""; // 没有父科室，这个也给 ''
                $departmentInfoPar['deptId'] = "f_".$v['frist_department_id'];
                $departmentInfoPar['deptName'] = $v['frist_department_name'];
                if (!empty($depSearch) && $depSearch == "f") {
                    if (strval($departmentInfoPar['deptId']) == $deptId) {
                        $listArr[strval($departmentInfoPar['deptId'])] = $departmentInfoPar;
                    }
                }
                if (empty($depSearch) && $depSearch !== "f_") {
                    $listArr[strval($departmentInfoPar['deptId'])] = $departmentInfoPar;
                }
                // 判断是否有二级
                if (count($v['second_arr'])>0){
                    foreach($v['second_arr'] as $kk=>$vv){
                        $departmentInfoPar['parentDeptId'] ="f_".$v['frist_department_id'];
                        $departmentInfoPar['parentDeptName'] =$v['frist_department_name'];
                        $departmentInfoPar['deptId'] = $vv['second_department_id'];
                        $departmentInfoPar['deptName'] = $vv['second_department_name'];
                        if (!empty($depSearch)) {
                            if (strval($departmentInfoPar['deptId']) == $deptId) {
                                $listArr[strval($departmentInfoPar['deptId'])] = $departmentInfoPar;
                            }
                        }
                        if (empty($depSearch) && $depSearch !== "f") {
                            $listArr[strval($departmentInfoPar['deptId'])] = $departmentInfoPar;
                        }
                    }
                }
            }
            $returnList = [];
            $total = count($listArr);//总条数
            if ($total) {
                $start=($p-1)*$n;//偏移量，当前页-1乘以每页显示条数
                $idList = array_slice($listArr,$start,$n);
                foreach($idList as $item) {
                    array_push($returnList,$item);
                }
            }
            $data['total'] = intval($total);
            $data['pageNum'] = intval($p);
            $data['pageSize'] = intval($n);
            $data['deptInfo'] = $returnList;

            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonSuccess($data);
        } catch (\Exception $e) {
            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonError($e->getMessage());
        }
    }

    /**
     *  医生接口
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-02
     */
    public function actionDoctorList()
    {

        \Yii::$app->params['DataToHospitalRequest']['platform'] = '202';
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'DoctorList';
        \Yii::$app->params['DataToHospitalRequest']['unableLog'] = false;
        $log_start_time = microtime(true);
        try {
            $docWhere = $this->joinWhere;
            if (!$docWhere) {
                throw new \Exception('数据异常');
            }
            // 去除该方法不用的共用条件
            unset($docWhere['d.doctor_real_plus']); // 有号
            $query = DoctorModel::find()
                ->alias('d')
                ->join('INNER JOIN', ['tpr' => 'tb_guahao_platform_relation_hospital'], 'd.tp_platform=tpr.tp_platform AND d.tp_hospital_code=tpr.tp_hospital_code')
                ->where($docWhere);

            $p = ArrayHelper::getValue($this->params, 'pageNum', 1);
            $p = (int)$p ? intval($p) : 1;
            $p = max(1, $p);
            $n = ArrayHelper::getValue($this->params, 'pageSize', 20);
            $n = (int)$n ? (int)$n : 20;
            $n = min($n, 100);

            $hosOrgNo = ArrayHelper::getValue($this->params, 'hosOrgNo', '');
            $hosDistinctCode = ArrayHelper::getValue($this->params, 'hosDistinctCode', '');

            if (empty(trim($hosOrgNo))) {
                return $this->aliJsonError('医院机构ID 不能为空');
            }
            if (empty(trim($hosDistinctCode))) {
                return $this->aliJsonError('院区代码 不能为空');
            }
            if (!empty($hosOrgNo) && !empty($hosDistinctCode)) {
                if (strcmp($hosOrgNo, $hosDistinctCode) !== 0) {
                    return $this->aliJsonError('院区代码 和院区Id 不一致');
                }
            }
            $hospitalId = '';
            if (!empty(trim($hosOrgNo))) {
                $hospitalId = trim(HashUrl::getIdDecode($hosOrgNo));
            }
            if (!empty(trim($hosDistinctCode))) {
                $hospitalId = trim(HashUrl::getIdDecode($hosDistinctCode));
            }
            $query->andWhere(['d.hospital_id' => $hospitalId]);

            $deptId = ArrayHelper::getValue($this->params, 'deptId', '');
            if (!empty(trim($deptId))) {
                $query->andWhere(['d.second_department_id' => trim($deptId)]);
            }
            $doctorName = ArrayHelper::getValue($this->params, 'doctorName', '');
            if (!empty(trim($doctorName))) {
                $query->andWhere(['like','d.realname',trim($doctorName)]);
            }
            $doctorNo = ArrayHelper::getValue($this->params, 'doctorNo', '');
            if (!empty(trim($doctorNo))) {
                $docId = HashUrl::getIdDecode($doctorNo);
                $query->andWhere(['d.doctor_id' => trim($docId)]);
            }

            \Yii::$app->params['DataToHospitalRequest']['index'] = $hosDistinctCode."-".$deptId;
            $returnList = [];
            $total = 0;
            // 判断是否传递的父科室， 如果是父科室直接返回 空， 不再查询数据 (ali :传一级科室id 不要返回信息)
            $res = preg_match("/f_/",$deptId);
            if (!$res) {
                $total = $query->count();
                $offset = ($p-1)*$n;
                if ($total) {
                    $field = "d.job_title,d.realname,d.hospital_id,d.hospital_name,d.doctor_id,d.second_department_id,d.second_department_name,d.frist_department_id,d.frist_department_name,d.primary_id";
                    $list = $query->select($field)->offset($offset)->limit($n)->asArray()->all();
                    if ($list) {
                        $returnList = GuahaoCooModel::formatAliDoctorData($list, $hospitalId);
                    }
                }
            }
            $data['total'] = intval($total);
            $data['pageNum'] = intval($p) ? $p : 1;
            $data['pageSize'] = $n;
            $data['docInfos'] = $returnList;

            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonSuccess($data);
        } catch (\Exception $e) {
            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonError($e->getMessage());
        }
    }

    /**
     *  排班列表 缓存
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-12
     */
    public function actionScheduleList()
    {

        \Yii::$app->params['DataToHospitalRequest']['platform'] = '202';
        \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'ScheduleList';
        \Yii::$app->params['DataToHospitalRequest']['unableLog'] = false;
        $log_start_time = microtime(true);
        try {
            // 判断医院code
            $hosOrgNo = ArrayHelper::getValue($this->params, 'hosOrgNo', '');
            $hosDistinctCode = ArrayHelper::getValue($this->params, 'hosDistinctCode', '');

            if (empty(trim($hosDistinctCode))) {
                return $this->aliJsonError('院区代码 不能为空');
            }
            if (!empty($hosOrgNo) && !empty($hosDistinctCode)) {
                if (strcmp($hosOrgNo, $hosDistinctCode) !== 0) {
                    return $this->aliJsonError('院区代码 和院区Id 不一致');
                }
            }
            $hositalId = HashUrl::getIdDecode($hosDistinctCode);
            $isTureOrFalse = $this->getOpenHospitalIds($hositalId);
            if (false == $isTureOrFalse) {
                return $this->aliJsonError('医院未开放！');
            }
            $deptId = ArrayHelper::getValue($this->params, 'deptId', '');
            $doctorNo = ArrayHelper::getValue($this->params, 'doctorNo', '');

            if (empty($deptId) && empty($doctorNo)) {
                return $this->aliJsonError("科室和医生不能同时为空");
            }
            $deptIdS = '';
            if (!empty(trim($deptId))) {
                $deptIdS = $deptId;
            }
            $startDate = ArrayHelper::getValue($this->params, 'startDate', '');
            $endDate = ArrayHelper::getValue($this->params, 'endDate', '');
            if (empty(trim($startDate))) {
                $startDate = date('Y-m-d', strtotime('+1 days'));
            }
            if (empty(trim($endDate))) {
                $endDate   = date('Y-m-d', strtotime('+31 days'));
            }

            \Yii::$app->params['DataToHospitalRequest']['index'] = $hosDistinctCode."-".$deptId;
            $listArr = [];
            $res = preg_match("/f_/",$deptId); // 只要传的是一级 直接返回 空数据， 不再查询
            $params = [
                "hospital_id"=>$hositalId,
                "second_department_id"=>$deptIdS,
                "primary_id"=>HashUrl::getIdDecode(strval($doctorNo)), // 和 s端预约挂号 拉取排班保持一致
                "startdate"=>$startDate,
                "enddate"=>$endDate,
                "status"=>1,
                "pagesize"=>1000,
            ];
            if (!$res) {
                $paibanSdk = SnisiyaSdk::getInstance();
                $paibanList = $paibanSdk->getPaibanAggApi($params);
                if ($paibanList) {
                    foreach ($paibanList as $pa=>$ba) {
                        $shceduData = GuahaoCooModel::formatAliScheduleInfo($ba);
                        array_push($listArr,$shceduData);
                    }
                }
            }
            $data['resourceList'] = $listArr;

            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonSuccess($data);
        } catch (\Exception $e) {
            //记录请求时长
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_spend_time'] = $log_spend_time;
            return $this->aliJsonError($e->getMessage());
        }
    }

    /**
     *  验证签名
     * @return int
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-08
     */
    public function validatorSign()
    {
        // 获取参数
        $params = \Yii::$app->request->getRawBody();
        $requestArr = parse_url(\Yii::$app->request->getAbsoluteUrl());

        // 请求的url:
        if (!isset($requestArr['query'])) {
            return 0;
        }
        $requestData = $this->convertUrlArray($requestArr['query']);
        if(isset($requestData['sign']) && !empty($requestData['sign'])){
            unset($requestData['r']);
            $remoteSign = $requestData['sign'];
            $requestData['timestamp']= urldecode($requestData['timestamp'] ?? "");
            $this->params = $params;
            $aliver = new AliVerification();
            //验证签名的参数， 获取数据参数， msg 是暂时的请求记录
            $localSign = $aliver->makeSign($requestData,$params);//print_r($localSign);die;
            if (strcmp($remoteSign, $localSign) == 0) {
                return 1;
            } else {

                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     *  获取数据
     * @return array|false|string
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-08
     */
    public function getAliData()
    {
        $data = \Yii::$app->request->getRawBody() ?? [];
        //判断来源是否可用
        if (GuahaoCooListModel::checkCooPlatform($this->coo_platform)) {
            return json_decode($data, true);
        } else {
            $this->logErrorMsg = '阿里健康来源不存在';
        }
        //记录日志
        if ($this->logErrorMsg) {
            \Yii::$app->params['DataToHospitalRequest']['error_msg'] = $this->logErrorMsg;
        }
    }

    public function convertUrlArray($query)
    {
        $queryParts = explode('&', $query);
        if(count($queryParts)<2){
            return [];
        }
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
    /**
     * 返回成功的json数据
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function aliJsonSuccess($data, $msg = '',$code='',$success=true)
    {
        $return["errorCode"] = $code;
        $return["errorMessage"] = $msg;
        $return["success"] = $success;
        $return[$this->content] = json_encode($data,JSON_UNESCAPED_UNICODE);
//        $return[$this->content] = $data;
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @param int $code
     * @return array
     */
    public function aliJsonError($msg = '', $code = '')
    {
        $return["errorMessage"] = $msg;
        $return["errorCode"] = $code;
        $return["success"] = false;
        //记录日志
        \Yii::$app->params['DataToHospitalRequest']['errorMsg'] = $msg;
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;

        return $this->jsonOutputCore($return);
    }

    /**
     * @param string $code
     * @param string $msg
     * @param false $success
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2022/3/15
     */
    public function returnError($code = 'sign-check-failure', $msg = 'Illegal request', $success = false)
    {
        exit(json_encode([
            'errorCode' => $code,
            'success' => $success,
            'errorMessage' => $msg
        ]));
    }
}