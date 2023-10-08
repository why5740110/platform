<?php

namespace common\models;

use yii\data\Pagination;
use common\libs\CommonFunc;
/**
 * This is the model class for table "tb_guahao_schedule_plan".
 * @property int $id  主键id
 * @property int $agency_id  代理商登录id
 * @property int $min_hospital_id 医院id
 * @property string $min_hospital_name  医院名称
 * @property int $min_doctor_id  医生id
 * @property string $min_doctor_name  医生名称
 * @property int $min_department_id  民营医院科室id
 * @property int $section_type 门诊类型 1 普通 2 专家
 * @property string $section_cycle_type 门诊周期类型 1 按周设置 2 按日期设置
 * @property string $visit_cycle  出诊周期内容
 * @property string $starttime 生效开始时间
 * @property string $endtime  生效结束时间
 * @property int $pay_type 支付类型  1 院内支付  2 线上支付
 * @property int $visit_cost 医事服务费
 * @property int $schedule_count 号源数量
 * @property int $is_delete 是否删除 0 未删除  1 已删除
 * @property int $is_done 计划是否执行 0 未执行  1 已执行
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class SchedulePlanModel extends \yii\db\ActiveRecord
{
    ##门诊类型
    public static $sectionType = [1 => '普通', 2 => '专家'];
    ##支付状态
    public static $payType = [1 => '院内支付', 2 => '线上支付'];
    ##执行状态
    public static $doneType = [0 => '未执行', 1 => '已执行'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_schedule_plan';
    }

    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::conditionWhere($params);
        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time desc')->asArray()->all();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::conditionWhere($params);
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    public static function conditionWhere($params, $field = '*')
    {
        $doctorQuery = self::find()->select($field);
        $doctorQuery->where(['is_delete' => 0]);

        //是否执行
        if (isset($params['is_done']) && $params['is_done'] != '') {
            $doctorQuery->andWhere(['is_done' => intval($params['is_done'])]);
        }

        //民营医院id
        if (isset($params['min_hospital_id']) && !empty($params['min_hospital_id'])) {
            $doctorQuery->andWhere(['min_hospital_id' => $params['min_hospital_id']]);
        }

        //代理商登录id
        if (isset($params['agency_id']) && !empty($params['agency_id'])) {
            $doctorQuery->andWhere(['agency_id' => $params['agency_id']]);
        }

        //民营医院科室id
        if (isset($params['min_department_id']) && !empty($params['min_department_id'])) {
            $doctorQuery->andWhere(['min_department_id' => $params['min_department_id']]);
        }

        //搜索关键词 医生或医院名称
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $keyword = trim($params['keyword']);
            $search_like = ['or', ['like', 'min_doctor_name', $keyword], ['like', 'min_hospital_name', $keyword]];
            $doctorQuery->andWhere($search_like);
        }

        //有效状态 1 未生效  2 生效中 3 已失效
        /*$time = time();
        if (isset($params['valid_status']) && !empty($params['valid_status'])) {
            if ($params['valid_status'] == 1) {
                $doctorQuery->andWhere(['>', 'starttime', $time]);
            } else if ($params['valid_status'] == 2) {
                $doctorQuery->andWhere(['<=', 'starttime', $time]);
                $doctorQuery->andWhere(['>=', 'endtime', $time]);
            } else if ($params['valid_status'] == 3) {
                $doctorQuery->andWhere(['<', 'endtime', $time]);
            }
        }*/
        return $doctorQuery;
    }

    //获取详情
    public static function getDetail($id)
    {
        $info = self::find()->select("*")->where(['id' => $id])->asArray()->one();
        return $info;
    }

    //保存停出诊计划
    public static function addPlan($params)
    {
        $model = new self();
        $model->agency_id  = isset($params['agency_id']) ? $params['agency_id'] : 0;
        $model->min_hospital_id  = isset($params['min_hospital_id']) ? $params['min_hospital_id'] : 0;
        $model->min_hospital_name  = isset($params['min_hospital_name']) ? $params['min_hospital_name'] : '';
        $model->min_doctor_id  = isset($params['min_doctor_id']) ? $params['min_doctor_id'] : 0;
        $model->min_doctor_name  = isset($params['min_doctor_name']) ? $params['min_doctor_name'] : '';
        $model->min_department_id  = isset($params['min_department_id']) ? $params['min_department_id'] : 0;
        $model->section_type  = isset($params['section_type']) ? $params['section_type'] : 1;
        $model->section_cycle_type  = isset($params['section_cycle_type']) ? $params['section_cycle_type'] : 0;
        $model->visit_cycle  = isset($params['visit_cycle']) ? $params['visit_cycle'] : '';
        $model->starttime  = isset($params['starttime']) ? $params['starttime'] : '';
        $model->endtime  = isset($params['endtime']) ? $params['endtime'] : '';
        $model->pay_type  = isset($params['pay_type']) ? $params['pay_type'] : 1;
        $model->visit_cost  = isset($params['visit_cost']) ? $params['visit_cost'] : 0;
        $model->schedule_count  = isset($params['schedule_count']) ? $params['schedule_count'] : 0;
        $model->create_time  = time();

        $res = $model->save();
        if ($res) {
            return $model->attributes['id'];
        } else {
            return 0;
        }
    }

    //更改内容
    public static function updateData($id, $data=[])
    {
        $plan_id = 0;
        $model = self::findOne($id);
        if (!empty($model)) {
            if (isset($data['is_done']) && !empty($data['is_done'])) $model->is_done = $data['is_done'];
            if (isset($data['is_delete']) && !empty($data['is_delete'])) $model->is_delete = $data['is_delete'];
            if (isset($data['min_hospital_name']) && !empty($data['min_hospital_name'])) $model->min_hospital_name = $data['min_hospital_name'];
            if (isset($data['min_doctor_name']) && !empty($data['min_doctor_name'])) $model->min_doctor_name = $data['min_doctor_name'];
            if (isset($data['min_department_id']) && !empty($data['min_department_id'])) $model->min_department_id = $data['min_department_id'];
            $model->update_time = time();
            $model->save();
            $plan_id = $model->id;
        } else {
            return false;
        }
        return $plan_id;
    }

    //获取医院下所有包含某个医生的出诊计划时间段
    public static function getVisitSchedule($requestParams)
    {
        $where = [
            'is_delete' => 0,
            'min_hospital_id' => $requestParams['min_hospital_id'],
            'min_doctor_id' => $requestParams['min_doctor_id']
        ];
        $res = [];
        //获取
        $list = self::find()->where($where)->asArray()->all();
        if (empty($list)) return $res;

        foreach ($list as $val) {
            $res[] = self::getCycleDetail($val);
        }
        $scheduleDate = [];
        if (!empty($res)) {
            foreach ($res as $val) {
                $scheduleDate = array_merge($scheduleDate, $val);
            }
        }
        $scheduleDate = array_unique($scheduleDate);
        return $scheduleDate;
    }

    //获取出诊计划的时间段明细
    public static function getCycleDetail($planInfo)
    {
        $scheduleDate = [];
        if (empty($planInfo)) return $scheduleDate;
        $scheduleDate = [];
        $stime = date('Y-m-d', $planInfo['starttime']);
        $etime = date('Y-m-d', $planInfo['endtime']);
        if ($planInfo['section_cycle_type'] == 1) {//按周期
            //获取生效周期内的星期
            $scheduleDate = CommonFunc::getDateWeekDetail($stime, $etime, $planInfo['visit_cycle']);
        } else if ($planInfo['section_cycle_type'] == 2) {//按日期
            $cycleArr = json_decode($planInfo['visit_cycle'], true);
            foreach ($cycleArr as $key => $value) {
                foreach ($value as $val) {
                    $scheduleDate[] = $key . " " . $val;
                }
            }
        }
        return $scheduleDate;
    }

    //格式化出诊情况
    public static function getFormatCycle($planInfo)
    {
        $cycleArr = json_decode($planInfo['visit_cycle'], true);
        $cycle_desc = "";
        foreach ($cycleArr as $k => $v) {
            $cycle_desc .= ($k . ((count($v) > 1) ? "" : $v[0])) . "<br/>";
        }
        return  $cycle_desc;
    }
}
