<?php
/**
 * 排班计划
 * SchedulePlanController.php
 * @author wanghongying<wanghongying@yuanyinjituan.com>
 * @date 2022-07-19
 */
namespace minyingapi\controllers;

use common\models\SchedulePlanModel;
use common\models\ScheduleClosePlanModel;
use common\models\minying\MinDoctorModel;
use common\models\minying\MinDepartmentModel;
use common\models\minying\ResourceDeadlineModel;
use Yii;
use common\libs\CommonFunc;
use queues\SchedulePlanJob;
use common\models\TbLog;
use common\libs\Idempotent;
use yii\helpers\Html;

class SchedulePlanController extends CommonController
{
    public $department;
    const LIMIT_DAYS = 90;

    public function init()
    {
        parent::init();
        $department = MinDepartmentModel::getAllList();
        foreach ($department as $val) {
            $this->department[$val['min_department_id']] = $val['min_department_name'];
        }
    }

    /**
     * 出诊计划列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-19
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['min_department_id']   = isset($requestParams['min_department_id']) ? trim($requestParams['min_department_id']) : '';//科室id
        $requestParams['keyword']   = isset($requestParams['keyword']) ? trim($requestParams['keyword']) : '';//搜索关键词
        $requestParams['valid_status']   = isset($requestParams['valid_status']) ? trim($requestParams['valid_status']) : '';//有效状态 1 未生效  2 生效中 3 已失效
        $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];
        $time = time();

        $list = SchedulePlanModel::getList($requestParams);
        foreach ($list as &$item) {
            $item['department'] = $this->department[$item['min_department_id']];
            $item['section_type_desc'] = SchedulePlanModel::$sectionType[$item['section_type']];
            $item['visit_cost_desc'] = '￥' . ($item['visit_cost'] / 100);
            $item['valid_date'] = date('Y-m-d', $item['starttime']) . '--' . date('Y-m-d', $item['endtime']);
            if ($item['starttime'] > $time) {
                $item['valid_status'] = "未生效";
            } else if ($item['endtime'] < $time) {
                $item['valid_status'] = "已失效";
            } else {
                $item['valid_status'] = "生效中";
            }
            //出诊情况
            $item['cycle_desc'] = SchedulePlanModel::getFormatCycle($item);
        }
        $totalCount = SchedulePlanModel::getCount($requestParams);
        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];

        return $this->jsonSuccess($result);
    }

    /**
     * 停诊计划列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionCloseList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['min_department_id']   = isset($requestParams['min_department_id']) ? trim($requestParams['min_department_id']) : '';//民营医院科室id
        $requestParams['keyword']   = isset($requestParams['keyword']) ? trim($requestParams['keyword']) : '';//搜索关键词
        $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];

        $list = ScheduleClosePlanModel::getList($requestParams);
        foreach ($list as &$item) {
            //获取医院科室数据
            $item['department'] = ($item['min_department_id'] > 0) ? $this->department[$item['min_department_id']] : "";
            $item['stop_section_type_desc'] = ScheduleClosePlanModel::$stopVisitType[$item['stop_visit_type']];
            $item['section_cycle_type_desc'] = ScheduleClosePlanModel::$sectionCycleType[$item['section_cycle_type']];
            //停诊情况
            $item['cycle_desc'] = ScheduleClosePlanModel::getFormatCycle($item);
        }
        $totalCount = ScheduleClosePlanModel::getCount($requestParams);
        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];

        return $this->jsonSuccess($result);
    }

    /**
     * 添加出诊
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionAddVisitPlan()
    {
        try {
            if (empty($this->user)) {
                return $this->jsonError('请登录');
            }
            $requestParams = Yii::$app->request->post();
            $requestParams['min_doctor_name'] = (isset($requestParams['min_doctor_name']) && !empty($requestParams['min_doctor_name'])) ? $requestParams['min_doctor_name'] : '';
            $requestParams['min_doctor_id'] = (isset($requestParams['min_doctor_id']) && !empty($requestParams['min_doctor_id'])) ? $requestParams['min_doctor_id'] : 0;
            $requestParams['section_type'] = (isset($requestParams['section_type']) && !empty($requestParams['section_type'])) ? $requestParams['section_type'] : 1;
            $requestParams['section_cycle_type'] = (isset($requestParams['section_cycle_type']) && !empty($requestParams['section_cycle_type'])) ? $requestParams['section_cycle_type'] : 1;
            $requestParams['visit_cycle'] = (isset($requestParams['visit_cycle']) && !empty($requestParams['visit_cycle'])) ? $requestParams['visit_cycle'] : '';
            $requestParams['pay_type'] = (isset($requestParams['pay_type']) && !empty($requestParams['pay_type'])) ? $requestParams['pay_type'] : 1;
            $requestParams['visit_cost'] = (isset($requestParams['visit_cost']) && $requestParams['visit_cost'] != '') ? $requestParams['visit_cost'] : '';
            $requestParams['schedule_count'] = (isset($requestParams['schedule_count']) && $requestParams['schedule_count'] != '') ? $requestParams['schedule_count'] : '';
            $requestParams['starttime'] = (isset($requestParams['starttime']) && !empty($requestParams['starttime'])) ? strtotime($requestParams['starttime']) : '';
            $requestParams['endtime'] = (isset($requestParams['endtime']) && !empty($requestParams['endtime'])) ? strtotime($requestParams['endtime']) : '';
            $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];
            $requestParams['min_hospital_name'] = $this->user['min_hospital_name'];
            $requestParams['agency_id'] = $this->user['agency_id'];//代理商登录id

            if (empty($requestParams['visit_cycle']) || ($requestParams['visit_cycle'] == '{}')) {
                return $this->jsonError("出诊时间不可为空");
            }

            if ($requestParams['section_cycle_type'] == 2) {
                $requestParams['visit_cycle'] = $this->formatEmptyCycle($requestParams['visit_cycle']);
                if (empty($requestParams['visit_cycle']) || ($requestParams['visit_cycle'] == '{}')) {
                    return $this->jsonError("出诊时间不可为空");
                }
            }
            $validate = $this->validateAddVisitPlanParams($requestParams);
            if ($validate['code'] == 400) {
                return $this->jsonError($validate['msg']);
            }

            //限制重复操作
            if (!Idempotent::check($requestParams)) {
                return $this->jsonError('操作太频繁了，请稍后重试');
            }

            //获取民营科室id
            $minDoctorInfo = MinDoctorModel::getDetail($requestParams['min_doctor_id']);
            $requestParams['min_department_id'] = $minDoctorInfo['min_department_id'];
            $requestParams['visit_cost'] = $requestParams['visit_cost'] * 100;//单位  分
            $res = SchedulePlanModel::addPlan($requestParams);
            if ($res) {
                //记录操作日志
                $editContent  = "民营医院ID:{$requestParams['min_hospital_id']},创建医生:{$requestParams['min_doctor_name']}的出诊计划";
                TbLog::addLog($editContent, '民营医院创建医生出诊计划', $this->admin_info);
                return $this->jsonSuccess(['plan_id' => $res], "添加成功！");
            } else {
                return $this->jsonError("添加失败！");
            }
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return $this->jsonError("添加失败！");
        }
    }

    /**
     * 添加停诊
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionAddClosePlan()
    {
        try {
            if (empty($this->user)) {
                return $this->jsonError('请登录');
            }
            $requestParams = Yii::$app->request->post();
            $requestParams['stop_visit_type'] = (isset($requestParams['stop_visit_type']) && $requestParams['stop_visit_type'] != '') ? $requestParams['stop_visit_type'] : '';
            $requestParams['section_cycle_type'] = (isset($requestParams['section_cycle_type']) && !empty($requestParams['section_cycle_type'])) ? $requestParams['section_cycle_type'] : 1;
            $requestParams['object_id'] = (isset($requestParams['object_id']) && $requestParams['object_id'] != '') ? $requestParams['object_id'] : '';
            $requestParams['object_name'] = (isset($requestParams['object_name']) && !empty($requestParams['object_name'])) ? $requestParams['object_name'] : '';
            $requestParams['visit_cycle'] = (isset($requestParams['visit_cycle']) && !empty($requestParams['visit_cycle'])) ? $requestParams['visit_cycle'] : '';
            $requestParams['remark'] = (isset($requestParams['remark']) && !empty($requestParams['remark'])) ? Html::encode($requestParams['remark']) : '';
            $requestParams['agency_id'] = $this->user['agency_id'];//代理商登录id
            $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];//民营医院id
            $requestParams['min_hospital_name'] = $this->user['min_hospital_name'];//民营医院名称

            if ($requestParams['stop_visit_type'] == '') {
                return $this->jsonError("停诊范围不可为空");
            }

            if ($requestParams['object_id'] == '') {
                return $this->jsonError("停诊对象不可为空");
            }

            if (empty($requestParams['section_cycle_type']) && $requestParams['section_cycle_type'] <= 0) {
                return $this->jsonError("门诊周期类型不可为空");
            }

            if (empty($requestParams['visit_cycle']) || ($requestParams['visit_cycle'] == '{}')) {
                return $this->jsonError("停诊时间不可为空");
            }

            if ($requestParams['section_cycle_type'] == 1) {
                $requestParams['visit_cycle'] = $this->formatEmptyCycle($requestParams['visit_cycle']);
                if (empty($requestParams['visit_cycle']) || ($requestParams['visit_cycle'] == '{}')) {
                    return $this->jsonError("停诊时间不可为空");
                }
            }

            if ($requestParams['stop_visit_type'] == 1) {//全院
                $requestParams['min_department_id'] = 0;
                $requestParams['object_name'] = $requestParams['min_hospital_name'];
            } else if ($requestParams['stop_visit_type'] == 2) {//科室
                $requestParams['min_department_id'] = $requestParams['object_id'];
            } else if ($requestParams['stop_visit_type'] == 3) {//医生
                //获取民营科室id
                $minDoctorInfo = MinDoctorModel::getDetail($requestParams['object_id']);
                $requestParams['min_department_id'] = $minDoctorInfo['min_department_id'];
            }


            $validate = $this->validateAddClosePlanParams($requestParams);
            if ($validate['code'] == 400) {
                return $this->jsonError($validate['msg']);
            }

            //限制重复操作
            if (!Idempotent::check($requestParams)) {
                return $this->jsonError('操作太频繁了，请稍后重试');
            }

            $res = ScheduleClosePlanModel::addClosePlan($requestParams);
            if ($res) {
                //异步队列执行 停诊
                $queue['id'] = $res;
                $queue['type'] = 2;//停诊计划操作
                \Yii::$app->addclosescheduleplan->delay(10)->push(new SchedulePlanJob($queue));

                //记录操作日志
                $editContent  = "民营医院ID:{$requestParams['min_hospital_id']},创建对象:{$requestParams['object_name']}的停诊计划";
                TbLog::addLog($editContent, '民营医院创建医生停诊计划', $this->admin_info);
                return $this->jsonSuccess(['plan_id' => $res], "添加成功！");
            } else {
                return $this->jsonError("添加失败！");
            }
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return $this->jsonError("添加失败！");
        }
    }

    /**
     * 删除出诊计划
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionDelPlan()
    {
        try {
            $id = Yii::$app->request->post('id', 0);

            if ($id <= 0) {
                return $this->jsonError("出诊计划ID不能为空");
            }

            $info = SchedulePlanModel::getDetail($id);
            if (empty($info)) {
                return $this->jsonError("出诊计划不存在");
            }

            //删除操作
            $data = [
                'is_delete' => 1
            ];
            $res = SchedulePlanModel::updateData($id, $data);

            if (!$res) {
                return $this->jsonError("删除失败！");
            }

            if ($info['is_done'] == 1) {//已执行过计划
                $queue['id'] = $id;
                $queue['type'] = 3;//删除出诊计划操作   已预约的订单发送取消短信
                \Yii::$app->delscheduleplan->delay(10)->push(new SchedulePlanJob($queue));
            }
            //记录操作日志
            $editContent  = "民营医院:{$info['min_hospital_name']} ID:{$info['min_hospital_id']},删除医生:{$info['min_doctor_name']}的出诊计划";
            TbLog::addLog($editContent, '民营医院删除出诊计划', $this->admin_info);
            return $this->jsonSuccess(['data' => $res], "删除成功！");
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return $this->jsonError("删除失败！");
        }
    }

    /**
     * 删除停诊计划
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionDelClosePlan()
    {
        try {
            $id = Yii::$app->request->post('id', 0);

            if ($id <= 0) {
                return $this->jsonError("停诊计划ID不能为空");
            }

            $info = ScheduleClosePlanModel::getDetail($id);
            if (empty($info)) {
                return $this->jsonError("停诊计划不存在");
            }

            //删除操作
            $data = [
                'is_delete' => 1
            ];
            $res = ScheduleClosePlanModel::updateData($id, $data);

            if (!$res) {
                return $this->jsonError("删除失败！");
            }

            //异步队列 执行删除排班数据
            $queue['id'] = $id;
            $queue['type'] = 4;//删除停诊计划操作 已出诊的被停诊了，重新恢复出诊
            \Yii::$app->delschedulecloseplan->delay(10)->push(new SchedulePlanJob($queue));

            //记录操作日志
            $editContent  = "民营医院:{$info['min_hospital_name']} ID:{$info['min_hospital_id']},删除对象:{$info['object_name']}的停诊计划";
            TbLog::addLog($editContent, '民营医院删除停诊计划', $this->admin_info);
            return $this->jsonSuccess(['data' => $res], "删除成功！");
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return $this->jsonError("删除失败！");
        }
    }

    /**
     * 验证出诊参数参数
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function validateAddVisitPlanParams($requestParams)
    {
        if (empty($requestParams['min_doctor_id']) && $requestParams['min_doctor_id'] <= 0) {
            return $this->jsonError("请选择医生");
        }

        if (empty($requestParams['min_doctor_name'])) {
            return $this->jsonError("医生名称不能为空");
        }

        if (empty($requestParams['section_type']) && $requestParams['section_type'] <= 0) {
            return $this->jsonError("门诊类型不可为空");
        }

        if (empty($requestParams['section_cycle_type']) && $requestParams['section_cycle_type'] <= 0) {
            return $this->jsonError("门诊周期类型不可为空");
        }

        if ($requestParams['schedule_count'] == '') {
            return $this->jsonError("号源数量不能为空");
        }

        if (!($requestParams['schedule_count'] >0 && $requestParams['schedule_count'] <= 50)) {
            return $this->jsonError("号源数量输入错误");
        }

        if ($requestParams['visit_cost'] == '') {
            return $this->jsonError("医事服务费不能为空");
        }

        if (!($requestParams['visit_cost'] >=0 && $requestParams['visit_cost'] <= 1500)) {
            return $this->jsonError("医事服务费输入错误");
        }

        if (empty($requestParams['starttime']) || empty($requestParams['endtime'])) {
            return $this->jsonError("请选择生效周期的开始时间或结束时间");
        }

        $stime = date('Y-m-d', $requestParams['starttime']);
        $etime = date('Y-m-d', $requestParams['endtime']);
        $time = time();
        if ($requestParams['starttime'] < $time) {
            return $this->jsonError("选择生效周期的开始时间不能小于和等于当天日期");
        }

        if ($requestParams['starttime'] > $requestParams['endtime']) {
            return $this->jsonError("选择生效周期的开始时间不能大于结束时间");
        }

        //开始时间到结束时间不能超过90天
        $diffCount = CommonFunc::getTimeDiff($stime, $etime);
        if ($diffCount > self::LIMIT_DAYS) {
            return $this->jsonError("生效周期开始时间到结束时间不能超过90天");
        }

        //按日期 选择的时间不能在有效期范围之外
        if ($requestParams['section_cycle_type'] == 2) {
            $cycleArr = json_decode($requestParams['visit_cycle'], true);
            $flag = 0;
            foreach ($cycleArr as $k => $v) {
                $cycle_time = strtotime($k);
                if (!($cycle_time <= $requestParams['endtime'] && $cycle_time >= $requestParams['starttime'])) {
                    $flag ++;
                }
            }
            if ($flag > 0) {
                return $this->jsonError("出诊时间未在生效周期内");
            }
        }

        //存在相同的时间节点交集不能添加 比如2022-07-01 上午 添加多条中包含同一时间的不能添加
        //获取添加出诊计划时间范围
        if ($requestParams['section_cycle_type'] == 2) {
            $requestParams['visit_cycle'] = $this->formatEmptyCycle($requestParams['visit_cycle']);
        }
        $scheduleDate = SchedulePlanModel::getCycleDetail($requestParams);
        //如果是选择按周设置 检测是有相对应的出诊日期
        if (empty($scheduleDate)) {
            return $this->jsonError("生效周期内没有相对应的出诊日期");
        }

        //获取当前医生所有出诊计划的时间范围
        $scheduleDateVisit = SchedulePlanModel::getVisitSchedule($requestParams);
        $insectVisitDate = array_intersect($scheduleDate, $scheduleDateVisit);
        if (!empty($insectVisitDate)) {
            return $this->jsonError("出诊时间设置重复,请重新选择出诊时间");
        }

        //有效时间段最大时间不能超过医院合作最大时间
        $resourceInfo = ResourceDeadlineModel::find()
            ->where(['resource_type' => 1])
            ->andWhere(['resource_id' => $this->user['min_hospital_id']])
            ->asArray()->one();
        $hospitalExpireTime = (!empty($resourceInfo)) ? $resourceInfo['end_time'] : 0;
        if ($requestParams['endtime'] > $hospitalExpireTime) {
            return $this->jsonError("生效周期超过医院合作时间");
        }

        return ['code' => 200];
    }

    /**
     * 验证停诊参数参数
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function validateAddClosePlanParams($requestParams)
    {
        if (empty($requestParams['stop_visit_type']) && $requestParams['stop_visit_type'] <= 0) {
            return $this->jsonError("停诊范围不可为空");
        }

        /*if (empty($requestParams['section_cycle_type']) && $requestParams['section_cycle_type'] <= 0) {
            return $this->jsonError("门诊周期类型不可为空");
        }*/

        if (empty($requestParams['visit_cycle'])) {
            return $this->jsonError("停诊时间范围不可为空");
        }

        //选择停诊时间不能大于医院合作时间
        $resourceInfo = ResourceDeadlineModel::find()
            ->where(['resource_type' => 1])
            ->andWhere(['resource_id' => $this->user['min_hospital_id']])
            ->asArray()->one();
        $hospitalExpireTime = (!empty($resourceInfo)) ? $resourceInfo['end_time'] : 0;

        $towDate = date('Y-m-d', strtotime("+1 day"));
        if ($requestParams['section_cycle_type'] == 2) {
            $visit_cycle = json_decode($requestParams['visit_cycle'], 1);
            list($stime, $etime) = explode('--', current($visit_cycle));
            $diffCount = CommonFunc::getTimeDiff($stime, $etime);
            if ($diffCount > self::LIMIT_DAYS) {
                return $this->jsonError("停诊时间开始时间与结束时间最多间隔90天");
            }

            //选择停诊时间要大于当前日期
            if ($stime < $towDate) {
                return $this->jsonError("停诊时间要大于当前日期");
            }

            //选择停诊时间不能大于医院合作时间
            if (strtotime($etime) > $hospitalExpireTime) {
                return $this->jsonError("停诊时间超过医院合作时间");
            }
        } else {
            $cycleArr = json_decode($requestParams['visit_cycle'], true);
            ksort($cycleArr);
            $cycleArr = array_keys($cycleArr);
            if (count($cycleArr) == 1) {
                $stime = $etime = current($cycleArr);
            } else {
                $stime = array_shift($cycleArr);
                $etime = array_pop($cycleArr);
            }

            //选择停诊时间要大于当前日期
            if ($stime < $towDate) {
                return $this->jsonError("停诊时间要大于当前日期");
            }

            if (strtotime($etime) > $hospitalExpireTime) {
                return $this->jsonError("停诊时间超过医院合作时间");
            }
        }

        //存在相同的时间节点交集不能添加 比如2022-07-01 上午 添加多条中包含同一时间的不能添加
        //获取添加停诊计划时间范围
        $scheduleDate = ScheduleClosePlanModel::getCycleDetail($requestParams);
        //获取当前医生所有停诊计划时间范围
        $scheduleDateClose = ScheduleClosePlanModel::getCloseSchedule($requestParams);
        $insectDate = array_intersect($scheduleDate, $scheduleDateClose);
        if (!empty($insectDate)) {
            return $this->jsonError("停诊时间设置重复,请重新选择停诊时间");
        }

        if (!empty($requestParams['remark'])) {
            $remarkLen = (strlen($requestParams['remark']) + mb_strlen($requestParams['remark'], 'UTF8')) / 2;
            if ($remarkLen > 60) {
                return $this->jsonError("备注内容不能超过30个字符");
            }
        }

        return ['code' => 200];
    }

    public function formatEmptyCycle($visit_cycle)
    {
        $cycleArr = json_decode($visit_cycle, true);
        $patten = '/^\d{4}[\/\-](0?[1-9]|1[012])([\/\-](0?[1-9]|[12][0-9]|3[01]))?$/';
        foreach ($cycleArr as $key => &$value) {
            if (!preg_match ($patten, $key)) {
                unset($cycleArr[$key]);
                continue;
            }
            if (!empty($key) && empty($value)) {
                $value = ['上午','下午'];
            }
        }
        return json_encode($cycleArr, 256);
    }
}